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
