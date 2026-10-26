"""Regression tests for Oracle Linux ca-certificates remediation checks."""

import importlib.util
import subprocess
import unittest
from pathlib import Path
from unittest.mock import patch


ROOT = Path(__file__).resolve().parents[2]
SOURCE = ROOT / "tests/container/check_sql_ca_certificates.py"
spec = importlib.util.spec_from_file_location("check_sql_ca_certificates", SOURCE)
certs = importlib.util.module_from_spec(spec)
spec.loader.exec_module(certs)


class SqlCertificateStoreTests(unittest.TestCase):
    def test_oracle_cert_package_security_update_version_floor(self):
        self.assertFalse(certs.is_patched("2025.2.80_v9.0.304-80.2.0.1.el9_8"))
        self.assertFalse(certs.is_patched("2026.2.92_v9.0.319-90.2.el9_8"))
        self.assertTrue(certs.is_patched("2026.2.92_v9.0.319-90.3.el9_8"))
        self.assertTrue(certs.is_patched("2026.2.92_v9.0.319-90.4.el9_8"))
        self.assertTrue(certs.is_patched("2026.2.92_v9.0.319-90.3.0.1.el9_8"))
        self.assertTrue(certs.is_patched("2027.1.1_v9.0.333-91.1.el9_8"))

    def test_rejects_unrecognized_rpm_versions_instead_of_guessing(self):
        for value in ("", "ca-certificates missing", "0", "not installed"):
            with self.subTest(value=value), self.assertRaises(ValueError):
                certs.is_patched(value)

    @patch.object(certs.subprocess, "run")
    def test_queries_rpm_in_finished_image_as_unprivileged_user(self, run):
        run.return_value = subprocess.CompletedProcess(
            args=[], returncode=0,
            stdout="2026.2.92_v9.0.319-90.3.el9_8", stderr="",
        )
        result = certs.check_image("saperstone-studios/sql:latest")
        self.assertEqual("2026.2.92_v9.0.319-90.3.el9_8", result)
        args, kwargs = run.call_args
        self.assertEqual(
            ["docker", "run", "--rm", "--user", "nobody", "--entrypoint",
             "rpm", "saperstone-studios/sql:latest", "-q", "--qf",
             "%{VERSION}-%{RELEASE}", "ca-certificates"],
            args[0],
        )
        self.assertTrue(kwargs["check"])
        self.assertTrue(kwargs["capture_output"])

    @patch.object(certs.subprocess, "run")
    def test_unpatched_image_fails(self, run):
        run.return_value = subprocess.CompletedProcess(
            args=[], returncode=0,
            stdout="2026.2.92_v9.0.319-90.2.el9_8", stderr="",
        )
        with self.assertRaisesRegex(ValueError, "predates ELSA-2026-88459"):
            certs.check_image("saperstone-studios/sql:latest")

    @patch.object(certs.subprocess, "run")
    def test_missing_rpm_or_docker_failure_is_not_ignored(self, run):
        run.side_effect = subprocess.CalledProcessError(1, "docker")
        self.assertEqual(1, certs.main(["saperstone-studios/sql:latest"]))
        self.assertEqual(2, certs.main([]))
        self.assertEqual(2, certs.main(["--privileged"]))

    def test_ci_rebuilds_latest_sql_base_and_checks_actual_package(self):
        source = (ROOT / ".github/workflows/build-and-deploy.yml").read_text()
        sql = source.split("  sql:\n", 1)[1]
        self.assertIn("pull: true", sql)
        checks = (ROOT / ".github/workflows/container-scan.yml").read_text()
        sql_scan = checks.split("  sql:\n", 1)[1]
        self.assertIn("docker build --pull", sql_scan)
        self.assertIn(
            "python3 tests/container/check_sql_ca_certificates.py saperstone-studios/sql:latest",
            sql_scan,
        )


if __name__ == "__main__":
    unittest.main()
