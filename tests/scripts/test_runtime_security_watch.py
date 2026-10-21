"""Regression tests for published container security and PHP release monitoring."""

import importlib.util
import json
import tempfile
import unittest
from pathlib import Path


def load_script(name, path):
    root = Path(__file__).resolve().parents[2]
    spec = importlib.util.spec_from_file_location(name, root / path)
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    return module


watch = load_script("runtime_security_watch", ".github/scripts/runtime-security-watch.py")


class RuntimeSecurityWatchTests(unittest.TestCase):
    def test_php_versions_compare_numerically(self):
        self.assertEqual((8, 4, 27), watch.php_version("8.4.27\n"))
        self.assertTrue(watch.php_version("8.4.28") > watch.php_version("8.4.27"))
        with self.assertRaises(ValueError):
            watch.php_version("8.4.27-rc1")

    def test_detects_new_high_critical_and_escalated_findings(self):
        current = {"CVE-new": "high", "CVE-escalated": "critical", "CVE-old": "critical"}
        previous = {"schema": 1, "findings": {"CVE-escalated": "high", "CVE-old": "critical"}}
        status = watch.evaluate("8.4.27", "8.4.27", current, previous)
        self.assertEqual(
            {"CVE-escalated": "critical", "CVE-new": "high"}, status["new"]
        )
        self.assertTrue(status["alert"])

    def test_no_alert_for_unchanged_findings_and_patched_image(self):
        previous = {"schema": 1, "findings": {"CVE-old": "high"}}
        status = watch.evaluate("8.4.27", "8.4.27", previous["findings"], previous)
        self.assertFalse(status["alert"])
        self.assertFalse(status["outdated"])

    def test_php_release_gap_alerts_even_when_grype_has_no_finding(self):
        status = watch.evaluate("8.4.27", "8.4.28", {}, {"schema": 1, "findings": {}})
        self.assertTrue(status["alert"])
        self.assertTrue(status["outdated"])
        self.assertEqual({}, status["new"])

    def test_first_run_requires_review_instead_of_silently_approving_baseline(self):
        status = watch.evaluate("8.4.27", "8.4.27", {"CVE-old": "high"}, None)
        self.assertTrue(status["alert"])
        self.assertTrue(status["initial_baseline"])
        self.assertIn(
            "Baseline review required",
            watch.report("image", "sha256:a", "8.4.27", "8.4.27", {}, status),
        )

    def test_rejects_wrong_runtime_branch_and_invalid_snapshot(self):
        with self.assertRaises(ValueError):
            watch.evaluate("8.5.12", "8.4.27", {}, None)
        with self.assertRaises(ValueError):
            watch.evaluate("8.4.27", "8.4.27", {}, {"schema": 2})
        with self.assertRaises(ValueError):
            watch.evaluate("8.4.27", "8.4.27", {}, {"schema": 1, "findings": []})

    def test_sarif_severity_and_reviewed_false_positive_filtering(self):
        sarif = {"runs": [{"tool": {"driver": {"rules": [
            {"id": "CVE-a", "properties": {"security-severity": "9.8"}},
            {"id": "CVE-b", "properties": {"security-severity": "7.4"}},
            {"id": "CVE-c", "properties": {"security-severity": "4.0"}},
        ]}}, "results": [
            {"ruleId": "CVE-a", "level": "error"},
            {"ruleId": "CVE-b", "level": "error"},
            {"ruleId": "CVE-c", "level": "warning"},
        ]}]}
        self.assertEqual(
            {"CVE-b": "high"},
            watch.high_severity_findings(sarif, {"CVE-a": "reviewed"}),
        )
        with self.assertRaises(ValueError):
            watch.high_severity_findings({}, {})

    def test_duplicate_cve_preserves_critical_severity(self):
        sarif = {"runs": [{"tool": {"driver": {"rules": [
            {"id": "CVE-duplicate"}
        ]}}, "results": [
            {"ruleId": "CVE-duplicate", "properties": {"severity": "critical"}},
            {"ruleId": "CVE-duplicate", "properties": {"severity": "high"}},
        ]}]}
        self.assertEqual(
            {"CVE-duplicate": "critical"},
            watch.high_severity_findings(sarif, {}),
        )

    def test_sql_findings_are_namespaced_from_php(self):
        combined = {"php:CVE-shared": "high", "sql:CVE-shared": "critical"}
        previous = {"schema": 1, "findings": {"php:CVE-shared": "high"}}
        status = watch.evaluate("8.4.27", "8.4.27", combined, previous)
        self.assertEqual({"sql:CVE-shared": "critical"}, status["new"])

    def test_equal_patch_versions_do_not_alert(self):
        for patch in (27, 28, 100):
            with self.subTest(patch=patch):
                version = f"8.4.{patch}"
                result = watch.evaluate(
                    version, version, {}, {"schema": 1, "findings": {}}
                )
                self.assertFalse(result["alert"])

    def test_creates_snapshot_report_and_alert_marker(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            (root / "scan.sarif").write_text(
                json.dumps({"runs": [{"tool": {"driver": {"rules": []}}, "results": []}]}),
                encoding="utf-8",
            )
            (root / "release.json").write_text('{"version": "8.4.28"}', encoding="utf-8")
            (root / "version.txt").write_text("8.4.27\n", encoding="utf-8")
            (root / "image.txt").write_text("sha256:abc\n", encoding="utf-8")
            (root / "allow.json").write_text('{"entries": []}', encoding="utf-8")
            args = type("Args", (), {
                "scan": root / "scan.sarif", "sql_scan": None, "sql_image": None,
                "sql_image_id": None, "previous": None,
                "current_version": root / "version.txt",
                "upstream_release": root / "release.json",
                "allowlist": root / "allow.json", "image": "registry/php:develop",
                "image_id": root / "image.txt", "snapshot": root / "snapshot.json",
                "report": root / "report.md", "alert": root / "alert.txt",
            })()
            watch.run(args)
            self.assertEqual("true\n", (root / "alert.txt").read_text())
            self.assertEqual(
                {}, json.loads((root / "snapshot.json").read_text())["findings"]
            )
            self.assertIn("PHP update required", (root / "report.md").read_text())
