#!/usr/bin/env python3

import argparse
import copy
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


def severity_from_text(value):
    match = re.search(
        r"\b(critical|high|medium|low|negligible|unknown)\s+vulnerability\b",
        str(value or ""),
        re.I,
    )
    return match.group(1).lower() if match else None


def severity_from_tags(tags):
    for tag in tags or []:
        match = re.search(
            r"(?:severity[:=]?)?\s*(critical|high|medium|low|negligible)",
            str(tag),
            re.I,
        )
        if match:
            return match.group(1).lower()
    return None


def result_severity(result, rule):
    for properties in (result.get("properties", {}), rule.get("properties", {})):
        severity = normalize_severity(properties.get("severity"))
        if severity:
            return severity

    for text in (
        result.get("message", {}).get("text"),
        rule.get("shortDescription", {}).get("text"),
        rule.get("fullDescription", {}).get("text"),
    ):
        severity = severity_from_text(text)
        if severity:
            return severity

    for properties in (result.get("properties", {}), rule.get("properties", {})):
        severity = severity_from_tags(properties.get("tags"))
        if severity:
            return severity

    # Generic SARIF producers may expose only a numeric security score. Grype
    # also emits this field, but its distro-aware severity is present in the
    # result/rule text above and takes precedence.
    for properties in (result.get("properties", {}), rule.get("properties", {})):
        severity = severity_from_score(properties.get("security-severity"))
        if severity:
            return severity

    return {
        "error": "high",
        "warning": "medium",
        "note": "low",
        "none": "unknown",
    }.get(str(result.get("level", "")).lower(), "unknown")


def load_sarif(path):
    return json.loads(Path(path).read_text(encoding="utf-8"))


def findings_from_sarif(data):
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
                result.get("message", {}).get("text")
                or rule.get("shortDescription", {}).get("text")
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


def parse_sarif(path):
    return findings_from_sarif(load_sarif(path))


def load_allowlist(path):
    if path is None:
        return {}

    data = json.loads(Path(path).read_text(encoding="utf-8"))
    allowlist = {}
    for entry in data.get("entries", []):
        rule_id = str(entry.get("rule_id", "")).strip()
        reason = str(entry.get("reason", "")).strip()
        if not rule_id or not reason:
            raise ValueError("Container scan allowlist entries require rule_id and reason")
        if rule_id in allowlist:
            raise ValueError(f"Duplicate container scan allowlist entry: {rule_id}")
        allowlist[rule_id] = reason

    return allowlist


def reviewed_findings(findings, allowlist):
    return [
        {**finding, "reason": allowlist[finding["rule_id"]]}
        for finding in findings
        if finding["rule_id"] in allowlist
    ]


def actionable_fixable_findings(findings, allowlist):
    return [
        finding
        for finding in findings
        if finding["rule_id"] not in allowlist
    ]


def severity_counts(findings):
    return Counter(item["severity"] for item in findings)


def has_actionable_fixable_findings(findings, allowlist):
    return bool(actionable_fixable_findings(findings, allowlist))


def write_filtered_sarif(data, allowlist, output_path):
    filtered = copy.deepcopy(data)

    for run in filtered.get("runs", []):
        results = [
            result
            for result in run.get("results", [])
            if result.get("ruleId") not in allowlist
        ]
        run["results"] = results

        used_rule_ids = {
            result.get("ruleId")
            for result in results
            if result.get("ruleId")
        }
        driver = run.get("tool", {}).get("driver", {})
        if "rules" in driver:
            driver["rules"] = [
                rule
                for rule in driver.get("rules", [])
                if rule.get("id") in used_rule_ids
            ]

    Path(output_path).write_text(
        json.dumps(filtered, indent=2) + "\n",
        encoding="utf-8",
    )


def write_summary(image_name, all_findings, fixable_findings, allowlist):
    reviewed = reviewed_findings(all_findings, allowlist)
    actionable_fixable = actionable_fixable_findings(fixable_findings, allowlist)

    all_counts = severity_counts(all_findings)
    fixable_counts = severity_counts(fixable_findings)
    reviewed_counts = severity_counts(reviewed)
    actionable_counts = severity_counts(actionable_fixable)

    print(f"### {image_name} Container Security Scan\n")
    print(
        "| Severity | Detected | Fixable | Reviewed exceptions | "
        "Actionable fixable |"
    )
    print("| --- | ---: | ---: | ---: | ---: |")
    for severity in SEVERITIES:
        print(
            f"| {severity.capitalize()} | **{all_counts[severity]}** | "
            f"**{fixable_counts[severity]}** | **{reviewed_counts[severity]}** | "
            f"**{actionable_counts[severity]}** |"
        )
    print(
        f"| Total | **{len(all_findings)}** | **{len(fixable_findings)}** | "
        f"**{len(reviewed)}** | **{len(actionable_fixable)}** |"
    )

    if actionable_fixable:
        print("\n### Actionable fixable findings\n")
        for finding in actionable_fixable[:20]:
            print(
                f"- **{finding['severity'].capitalize()}** — "
                f"`{finding['rule_id']}` — {finding['description']}"
            )
        if len(actionable_fixable) > 20:
            print(f"- …and {len(actionable_fixable) - 20} more")
    else:
        print("\nNo actionable fixable vulnerabilities were reported.")

    if reviewed:
        print("\n<details>")
        print("<summary>Reviewed exceptions</summary>\n")
        for finding in reviewed[:30]:
            print(
                f"- **{finding['severity'].capitalize()}** — "
                f"`{finding['rule_id']}` — {finding['reason']}"
            )
        if len(reviewed) > 30:
            print(f"- …and {len(reviewed) - 30} more")
        print("\n</details>")

    actionable_fixable_keys = {
        (item["rule_id"], item["description"]) for item in actionable_fixable
    }
    unresolved = [
        finding
        for finding in all_findings
        if finding["rule_id"] not in allowlist
        and finding["severity"] in {"critical", "high"}
        and (finding["rule_id"], finding["description"]) not in actionable_fixable_keys
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

    print(
        "\n**Merge gate:** any fixable vulnerability that is not covered by "
        "a documented reviewed exception fails the container scan. Raw reports "
        "retain every detected finding."
    )


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--image", required=True)
    parser.add_argument("--all", dest="all_report", required=True)
    parser.add_argument("--fixable", required=True)
    parser.add_argument("--allowlist")
    parser.add_argument("--filtered-sarif")
    args = parser.parse_args()

    try:
        all_data = load_sarif(args.all_report)
        all_findings = findings_from_sarif(all_data)
        fixable_findings = parse_sarif(args.fixable)
        allowlist = load_allowlist(args.allowlist)
    except (OSError, json.JSONDecodeError, ValueError) as error:
        print(f"### {args.image} Container Security Scan\n")
        print(f"⚠️ Unable to read container scan data: {error}")
        return 2

    if args.filtered_sarif:
        write_filtered_sarif(all_data, allowlist, args.filtered_sarif)

    write_summary(args.image, all_findings, fixable_findings, allowlist)
    return 1 if has_actionable_fixable_findings(fixable_findings, allowlist) else 0


if __name__ == "__main__":
    sys.exit(main())
