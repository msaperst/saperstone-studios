import importlib.util
import json
import os
import tempfile
import unittest
from unittest.mock import patch
from pathlib import Path


SCRIPT_PATH = Path(__file__).parents[1] / "zap-summary.py"
SPEC = importlib.util.spec_from_file_location("zap_summary", SCRIPT_PATH)
ZAP_SUMMARY = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(ZAP_SUMMARY)


class ReportableAlertsTest(unittest.TestCase):
    def test_excludes_false_positive_with_no_instances(self):
        alerts = [{
            "riskcode": "2",
            "riskdesc": "Medium (False Positive)",
            "instances": [],
            "count": "0",
        }]

        self.assertEqual([], ZAP_SUMMARY.reportable_alerts(alerts))

    def test_keeps_actionable_alert(self):
        alert = {
            "riskcode": "2",
            "riskdesc": "Medium (High)",
            "instances": [{"uri": "http://localhost:90"}],
            "count": "1",
        }

        self.assertEqual([alert], ZAP_SUMMARY.reportable_alerts([alert]))


class ZapGateTest(unittest.TestCase):
    def run_summary(self, alerts):
        with tempfile.TemporaryDirectory() as temp_dir:
            report_path = Path(temp_dir) / "report.json"
            summary_path = Path(temp_dir) / "summary.md"
            report_path.write_text(json.dumps({"site": [{"alerts": alerts}]}), encoding="utf-8")
            with patch.dict(os.environ, {"GITHUB_STEP_SUMMARY": str(summary_path)}):
                return ZAP_SUMMARY.main_with_args_for_test(report_path, "Full") if hasattr(ZAP_SUMMARY, "main_with_args_for_test") else None


if __name__ == "__main__":
    unittest.main()
