"""Regression checks for the SQL image's Go toolchain and reviewed CVE exceptions."""

import importlib.util
import json
import re
import unittest
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
DOCKERFILE = ROOT / ".docker/sql/Dockerfile"
ALLOWLIST = ROOT / ".github/container-scan-allowlist.json"
SCAN_SUMMARY = ROOT / ".github/scripts/container-scan-summary.py"


def uses_fixed_gosu_toolchain(text):
    """Require the Go version with the GO-2026-6809 crypto/x509 fix."""
    match = re.findall(r"^FROM\s+golang:1\.26\.(\d+)-bookworm\s+AS\s+gosu-builder\s*$",
                       text, flags=re.MULTILINE)
    return len(match) == 1 and int(match[0]) >= 10


class SqlRuntimeSecurityTests(unittest.TestCase):
    def test_gosu_builder_uses_fixed_go_standard_library(self):
        self.assertTrue(
            uses_fixed_gosu_toolchain(DOCKERFILE.read_text(encoding="utf-8")),
            "gosu must be rebuilt with Go 1.26.10+ for GO-2026-6809",
        )

    def test_rejects_old_or_unpinned_go_base_images(self):
        for image in (
            "golang:1.26.9-bookworm",
            "golang:1.26-bookworm",
            "golang:1.27.2-bookworm",
            "golang:1.26.10-alpine",
        ):
            with self.subTest(image=image):
                self.assertFalse(
                    uses_fixed_gosu_toolchain(f"FROM {image} AS gosu-builder\n")
                )
        self.assertTrue(
            uses_fixed_gosu_toolchain(
                "FROM golang:1.26.10-bookworm AS gosu-builder\n"
            )
        )

    def test_fips_exceptions_are_documented_without_suppressing_go_advisory(self):
        entries = json.loads(ALLOWLIST.read_text(encoding="utf-8"))["entries"]
        reasons = {entry["rule_id"]: entry["reason"] for entry in entries}
        for name in ("openssl", "openssl-libs"):
            key = f"ELSA-2026-500448-{name}"
            with self.subTest(package=name):
                self.assertIn(key, reasons)
                self.assertIn("FIPS", reasons[key])
                self.assertIn("ELSA-2026-77396", reasons[key])

        # GO-2026-6809 is fixed in the rebuilt binary, not exempted.
        self.assertNotIn("GO-2026-6809-stdlib", reasons)

    def test_only_verified_fips_findings_are_exempted(self):
        spec = importlib.util.spec_from_file_location("container_scan_summary", SCAN_SUMMARY)
        scanner = importlib.util.module_from_spec(spec)
        spec.loader.exec_module(scanner)
        allowlist = scanner.load_allowlist(ALLOWLIST)
        findings = [
            {"rule_id": "ELSA-2026-500448-openssl", "severity": "high", "description": "FIPS"},
            {"rule_id": "ELSA-2026-500448-openssl-libs", "severity": "high", "description": "FIPS"},
            {"rule_id": "GO-2026-6809-stdlib", "severity": "low", "description": "Go stdlib"},
        ]
        self.assertEqual(
            [findings[2]],
            scanner.actionable_fixable_findings(findings, allowlist),
        )


if __name__ == "__main__":
    unittest.main()
