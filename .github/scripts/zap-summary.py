#!/usr/bin/env python3
"""Write a compact OWASP ZAP JSON report summary to GitHub Actions."""

import json
import os
import sys
from collections import Counter
from pathlib import Path


RISK_NAMES = {
    "0": "Informational",
    "1": "Low",
    "2": "Medium",
    "3": "High",
}


def reportable_alerts(alerts: list[dict]) -> list[dict]:
    """Return alerts that still have findings and are not accepted false positives."""
    return [
        alert
        for alert in alerts
        if alert.get("instances")
        and "false positive" not in str(alert.get("riskdesc", "")).lower()
    ]


def has_actionable_findings(alerts: list[dict]) -> bool:
    """Return whether any reportable alert is above Informational severity."""
    return any(str(alert.get("riskcode", "0")) in {"1", "2", "3"} for alert in alerts)


def main() -> int:
    if len(sys.argv) != 3:
        print("Usage: zap-summary.py <report-json> <scan-name>", file=sys.stderr)
        return 2

    report_path = Path(sys.argv[1])
    scan_name = sys.argv[2].capitalize()
    summary_path = os.environ.get("GITHUB_STEP_SUMMARY")

    if not summary_path:
        print("GITHUB_STEP_SUMMARY is not set; skipping job summary.")
        return 0

    if not report_path.exists():
        with open(summary_path, "a", encoding="utf-8") as summary:
            summary.write(f"## ZAP {scan_name} Security Scan\n\n")
            summary.write("ZAP did not produce `report_json.json`; see the scan log for details.\n")
        return 0

    with report_path.open(encoding="utf-8") as report_file:
        report = json.load(report_file)

    alerts = []
    for site in report.get("site", []):
        alerts.extend(site.get("alerts", []))
    alerts = reportable_alerts(alerts)

    counts = Counter(str(alert.get("riskcode", "0")) for alert in alerts)

    with open(summary_path, "a", encoding="utf-8") as summary:
        summary.write(f"## ZAP {scan_name} Security Scan\n\n")
        summary.write("| Severity | Alert types |\n")
        summary.write("| --- | ---: |\n")
        for riskcode in ("3", "2", "1", "0"):
            summary.write(f"| {RISK_NAMES[riskcode]} | {counts[riskcode]} |\n")

        medium_or_high = [
            alert for alert in alerts if str(alert.get("riskcode", "0")) in {"2", "3"}
        ]
        if medium_or_high:
            summary.write("\n### Medium and High findings\n\n")
            for alert in sorted(
                medium_or_high,
                key=lambda item: int(str(item.get("riskcode", "0"))),
                reverse=True,
            ):
                riskcode = str(alert.get("riskcode", "0"))
                name = alert.get("name") or alert.get("alert") or "Unnamed ZAP alert"
                instances = len(alert.get("instances", []))
                summary.write(
                    f"- **{RISK_NAMES.get(riskcode, 'Unknown')}** — {name} "
                    f"({instances} instance{'s' if instances != 1 else ''})\n"
                )
        else:
            summary.write("\nNo Medium or High findings were reported.\n")

        summary.write(
            "\nDetailed HTML, JSON, and Markdown reports are available in the ZAP workflow artifact.\n"
        )

    if has_actionable_findings(alerts):
        actionable_count = sum(
            str(alert.get("riskcode", "0")) in {"1", "2", "3"} for alert in alerts
        )
        print(
            f"ZAP found {actionable_count} alert type(s) above Informational severity.",
            file=sys.stderr,
        )
        return 1

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
