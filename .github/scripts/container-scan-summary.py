#!/usr/bin/env python3

import argparse
import json
import re
import sys
from collections import Counter
from pathlib import Path

SEVERITIES = ("critical", "high", "medium", "low", "negligible", "unknown")


def severity_from_score(value):
    try:
        score = float(value)
    except (TypeError, ValueError):
        return None
    if score >= 9.0:
        return "critical"
    if score >= 7.0:
        return "high"
    if score >= 4.0:
        return "medium"
    if score > 0.0:
        return "low"
    return "negligible"


def normalize_severity(value):
    severity = str(value or "").strip().lower()
    return severity if severity in SEVERITIES else None


def severity_from_tags(tags):
    for tag in tags or []:
        match = re.search(r"(?:severity[:=]?)?\s*(critical|high|medium|low|negligible)", str(tag), re.I)
        if match:
            return match.group(1).lower()
    return None


def result_severity(result, rule):
    for properties in (result.get("properties", {}), rule.get("properties", {})):
        severity = normalize_severity(properties.get("severity"))
        if severity:
            return severity
        severity = severity_from_score(properties.get("security-severity"))
        if severity:
            return severity
        severity = severity_from_tags(properties.get("tags"))
        if severity:
            return severity

    return {
        "error": "high",
        "warning": "medium",
        "note": "low",
        "none": "unknown",
    }.get(str(result.get("level", "")).lower(), "unknown")


def parse_sarif(path):
    data = json.loads(Path(path).read_text(encoding="utf-8"))
    findings = []

    for run in data.get("runs", []):
        rules = {
            rule.get("id"): rule
            for rule in run.get("tool", {}).get("driver", {}).get("rules", [])
            if rule.get("id")
        }
        for result in run.get("results", []):
            rule_id = result.get("ruleId") or "unknown vulnerability"
            rule = rules.get(rule_id, {})
            description = (
                rule.get("shortDescription", {}).get("text")
                or result.get("message", {}).get("text")
                or rule_id
            )
            findings.append(
                {
                    "rule_id": rule_id,
                    "description": str(description),
                    "severity": result_severity(result, rule),
                }
            )

    return findings


def severity_counts(findings):
    return Counter(item["severity"] for item in findings)


def write_summary(image_name, all_findings, fixable_findings):
    all_counts = severity_counts(all_findings)
    fixable_counts = severity_counts(fixable_findings)

    print(f"### {image_name} Container Security Scan\n")
    print("| Severity | All findings | Fixable findings |")
    print("| --- | ---: | ---: |")
    for severity in SEVERITIES:
        print(
            f"| {severity.capitalize()} | **{all_counts[severity]}** | "
            f"**{fixable_counts[severity]}** |"
        )
    print(
        f"| Total | **{len(all_findings)}** | **{len(fixable_findings)}** |"
    )

    blocking = [
        finding
        for finding in fixable_findings
        if finding["severity"] in {"critical", "high"}
    ]
    if blocking:
        print("\n### Fixable High/Critical findings\n")
        for finding in blocking[:20]:
            print(
                f"- **{finding['severity'].capitalize()}** — "
                f"`{finding['rule_id']}` — {finding['description']}"
            )
        if len(blocking) > 20:
            print(f"- …and {len(blocking) - 20} more")
    else:
        print("\nNo fixable High or Critical vulnerabilities were reported.")

    unresolved = [
        finding
        for finding in all_findings
        if finding["severity"] in {"critical", "high"}
        and finding["rule_id"] not in {item["rule_id"] for item in fixable_findings}
    ]
    if unresolved:
        print("\n<details>")
        print("<summary>High/Critical findings without a current fix</summary>\n")
        for finding in unresolved[:20]:
            print(
                f"- **{finding['severity'].capitalize()}** — "
                f"`{finding['rule_id']}` — {finding['description']}"
            )
        if len(unresolved) > 20:
            print(f"- …and {len(unresolved) - 20} more")
        print("\n</details>")

    print("\n**Merge gate:** only fixable High or Critical vulnerabilities fail the container scan. The complete report remains visible for risk review and future remediation.")


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--image", required=True)
    parser.add_argument("--all", dest="all_report", required=True)
    parser.add_argument("--fixable", required=True)
    args = parser.parse_args()

    try:
        all_findings = parse_sarif(args.all_report)
        fixable_findings = parse_sarif(args.fixable)
    except (OSError, json.JSONDecodeError) as error:
        print(f"### {args.image} Container Security Scan\n")
        print(f"⚠️ Unable to read container scan report: {error}")
        return 2

    write_summary(args.image, all_findings, fixable_findings)
    return 0


if __name__ == "__main__":
    sys.exit(main())
