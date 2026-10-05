import importlib.util
import types
import json
import tempfile
import unittest
from pathlib import Path


REPO_ROOT = Path(__file__).resolve().parents[2]


def load_script(name, relative_path):
    spec = importlib.util.spec_from_file_location(name, REPO_ROOT / relative_path)
    module = importlib.util.module_from_spec(spec)
    assert spec.loader is not None
    spec.loader.exec_module(module)
    return module


test_summary = load_script("test_summary", ".github/scripts/test-summary.py")
unittest_junit = load_script("unittest_junit", ".github/scripts/unittest-junit.py")
zap_summary = load_script("zap_summary", ".github/scripts/zap-summary.py")
composer_audit_summary = load_script(
    "composer_audit_summary",
    ".github/scripts/composer-audit-summary.py",
)
container_scan_summary = load_script(
    "container_scan_summary",
    ".github/scripts/container-scan-summary.py",
)


class TestSummaryScriptTests(unittest.TestCase):
    def test_parse_junit_counts_failure_error_and_skip(self):
        xml = """<?xml version="1.0"?>
        <testsuite>
          <testcase classname="Example" name="passes"/>
          <testcase classname="Example" name="fails"><failure/></testcase>
          <testcase classname="Example" name="errors"><error/></testcase>
          <testcase classname="Example" name="skips"><skipped/></testcase>
        </testsuite>
        """
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / "junit.xml"
            path.write_text(xml, encoding="utf-8")

            result = test_summary.parse_junit(path)

        self.assertEqual(
            {
                "total": 4,
                "passed": 1,
                "failed": 1,
                "errors": 1,
                "skipped": 1,
                "failed_names": ["Example::fails", "Example::errors"],
            },
            result,
        )

    def test_parse_lcov_aggregates_multiple_files(self):
        lcov = """SF:first.js
FNF:2
FNH:1
BRF:4
BRH:3
LF:10
LH:8
end_of_record
SF:second.js
FNF:1
FNH:1
BRF:2
BRH:1
LF:5
LH:5
end_of_record
"""
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / "coverage.info"
            path.write_text(lcov, encoding="utf-8")

            result = test_summary.parse_lcov(path)

        self.assertEqual(15, result["lines"])
        self.assertEqual(13, result["covered_lines"])
        self.assertEqual(6, result["branches"])
        self.assertEqual(4, result["covered_branches"])
        self.assertEqual(3, result["functions"])
        self.assertEqual(2, result["covered_functions"])
        self.assertAlmostEqual(86.6666667, result["line"])
        self.assertAlmostEqual(66.6666667, result["branch"])
        self.assertAlmostEqual(66.6666667, result["function"])


class ComposerAuditSummaryScriptTests(unittest.TestCase):
    def test_flattens_advisories_and_blocks_high_severity(self):
        report = {
            "advisories": {
                "vendor/high": [
                    {
                        "advisoryId": "PKSA-high",
                        "title": "High issue",
                        "severity": "high",
                    }
                ],
                "vendor/low": [
                    {
                        "advisoryId": "PKSA-low",
                        "title": "Low issue",
                        "severity": "low",
                    }
                ],
            }
        }

        advisories = composer_audit_summary.flatten_advisories(report)

        self.assertEqual(2, len(advisories))
        self.assertEqual("vendor/high", advisories[0]["packageName"])
        self.assertTrue(composer_audit_summary.has_blocking_advisories(advisories))

    def test_medium_and_low_advisories_do_not_block(self):
        advisories = composer_audit_summary.flatten_advisories(
            {
                "advisories": {
                    "vendor/example": [
                        {"severity": "medium"},
                        {"severity": "low"},
                    ]
                }
            }
        )

        self.assertFalse(composer_audit_summary.has_blocking_advisories(advisories))

    def test_dependency_count_includes_runtime_and_dev_packages_once(self):
        lock = {
            "packages": [{"name": "vendor/runtime"}],
            "packages-dev": [
                {"name": "vendor/dev"},
                {"name": "vendor/runtime"},
            ],
        }

        self.assertEqual(2, composer_audit_summary.dependency_count(lock))

    def test_sarif_keeps_existing_dependency_check_tool_identity(self):
        advisories = composer_audit_summary.flatten_advisories(
            {
                "advisories": {
                    "vendor/example": [
                        {
                            "advisoryId": "PKSA-example",
                            "title": "Example issue",
                            "severity": "critical",
                            "link": "https://example.invalid/advisory",
                        }
                    ]
                }
            }
        )

        sarif = composer_audit_summary.build_sarif(advisories)
        run = sarif["runs"][0]

        self.assertEqual("dependency-check", run["tool"]["driver"]["name"])
        self.assertEqual("9.5", run["tool"]["driver"]["rules"][0]["properties"]["security-severity"])
        self.assertEqual("error", run["results"][0]["level"])


class UnittestJunitScriptTests(unittest.TestCase):
    def test_write_junit_is_compatible_with_test_summary(self):
        class DummyTest:
            def __init__(self, test_id):
                self.test_id = test_id

            def id(self):
                return self.test_id

        passing = DummyTest("Example.test_passes")
        failing = DummyTest("Example.test_fails")
        skipped = DummyTest("Example.test_skips")
        result = types.SimpleNamespace(
            testsRun=3,
            failures=[(failing, "expected failure details")],
            errors=[],
            skipped=[(skipped, "expected skip")],
            expectedFailures=[],
            unexpectedSuccesses=[],
        )

        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / "script-junit.xml"
            unittest_junit.write_junit(
                path,
                [passing, failing, skipped],
                result,
                0.25,
            )
            parsed = test_summary.parse_junit(path)

        self.assertEqual(3, parsed["total"])
        self.assertEqual(1, parsed["passed"])
        self.assertEqual(1, parsed["failed"])
        self.assertEqual(0, parsed["errors"])
        self.assertEqual(1, parsed["skipped"])


class ContainerScanSummaryScriptTests(unittest.TestCase):
    def test_parse_sarif_uses_security_score_and_tags(self):
        sarif = {
            "runs": [
                {
                    "tool": {
                        "driver": {
                            "rules": [
                                {
                                    "id": "CVE-critical",
                                    "shortDescription": {"text": "Critical issue"},
                                    "properties": {"security-severity": "9.8"},
                                },
                                {
                                    "id": "CVE-medium",
                                    "shortDescription": {"text": "Medium issue"},
                                    "properties": {"tags": ["severity: medium"]},
                                },
                            ]
                        }
                    },
                    "results": [
                        {"ruleId": "CVE-critical", "level": "error"},
                        {"ruleId": "CVE-medium", "level": "warning"},
                    ],
                }
            ]
        }

        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / "scan.sarif"
            path.write_text(json.dumps(sarif), encoding="utf-8")
            findings = container_scan_summary.parse_sarif(path)

        self.assertEqual("critical", findings[0]["severity"])
        self.assertEqual("medium", findings[1]["severity"])

    def test_severity_counts_include_fixable_findings(self):
        findings = [
            {"severity": "high"},
            {"severity": "high"},
            {"severity": "low"},
        ]

        counts = container_scan_summary.severity_counts(findings)

        self.assertEqual(2, counts["high"])
        self.assertEqual(1, counts["low"])
        self.assertEqual(0, counts["critical"])


class ZapSummaryScriptTests(unittest.TestCase):
    def test_reportable_alerts_filters_empty_and_false_positive_findings(self):
        alerts = [
            {"riskcode": "2", "riskdesc": "Medium", "instances": [{"uri": "/"}]},
            {"riskcode": "1", "riskdesc": "Low (False Positive)", "instances": [{"uri": "/"}]},
            {"riskcode": "3", "riskdesc": "High", "instances": []},
        ]

        self.assertEqual([alerts[0]], zap_summary.reportable_alerts(alerts))

    def test_actionable_findings_ignore_informational_alerts(self):
        informational = [{"riskcode": "0", "instances": [{"uri": "/"}]}]
        low = [{"riskcode": "1", "instances": [{"uri": "/"}]}]

        self.assertFalse(zap_summary.has_actionable_findings(informational))
        self.assertTrue(zap_summary.has_actionable_findings(low))


if __name__ == "__main__":
    unittest.main()
