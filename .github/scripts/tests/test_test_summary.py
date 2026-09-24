import importlib.util
import io
import sys
import tempfile
import unittest
from contextlib import redirect_stdout
from pathlib import Path
from unittest.mock import patch


SCRIPT_PATH = Path(__file__).parents[1] / "test-summary.py"
SPEC = importlib.util.spec_from_file_location("test_summary", SCRIPT_PATH)
TEST_SUMMARY = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(TEST_SUMMARY)


JUNIT_XML = """<?xml version="1.0" encoding="UTF-8"?>
<testsuite name="API Tests" tests="4" failures="1" errors="1" skipped="1">
    <testcase classname="api\\PassingTest" name="testPasses"/>
    <testcase classname="api\\FailingTest" name="testFails">
        <failure message="failure"/>
    </testcase>
    <testcase classname="api\\ErrorTest" name="testErrors">
        <error message="error"/>
    </testcase>
    <testcase classname="api\\SkippedTest" name="testSkipped">
        <skipped/>
    </testcase>
</testsuite>
"""


class TestSummaryTest(unittest.TestCase):
    def write_junit(self):
        handle = tempfile.NamedTemporaryFile(mode="w", suffix=".xml", delete=False)
        self.addCleanup(Path(handle.name).unlink, missing_ok=True)
        handle.write(JUNIT_XML)
        handle.close()
        return Path(handle.name)

    def test_parse_junit_counts_results_and_failures(self):
        results = TEST_SUMMARY.parse_junit(self.write_junit())

        self.assertEqual(4, results["total"])
        self.assertEqual(1, results["passed"])
        self.assertEqual(1, results["failed"])
        self.assertEqual(1, results["errors"])
        self.assertEqual(1, results["skipped"])
        self.assertEqual(
            ["api\\FailingTest::testFails", "api\\ErrorTest::testErrors"],
            results["failed_names"],
        )

    def test_summary_can_render_without_coverage_report(self):
        junit = self.write_junit()
        output = io.StringIO()

        with patch.object(
            sys,
            "argv",
            ["test-summary.py", "--title", "API Tests", "--junit", str(junit)],
        ), redirect_stdout(output):
            result = TEST_SUMMARY.main()

        rendered = output.getvalue()
        self.assertEqual(0, result)
        self.assertIn("### API Tests", rendered)
        self.assertIn("| Tests run | **4** |", rendered)
        self.assertIn("| Passed | **1** |", rendered)
        self.assertIn("| Failed | **2** |", rendered)
        self.assertIn("| Errors | **1** |", rendered)
        self.assertIn("| Skipped | **1** |", rendered)
        self.assertIn("api\\FailingTest::testFails", rendered)
        self.assertNotIn("| Coverage |", rendered)


if __name__ == "__main__":
    unittest.main()
