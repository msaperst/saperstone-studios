#!/usr/bin/env python3

import argparse
import json
import re
import sys
from collections import Counter
from pathlib import Path

SEVERITIES = ("critical", "high", "medium", "low", "unknown")
FAIL_SEVERITIES = {"critical", "high"}
SECURITY_SCORES = {
    "critical": "9.5",
    "high": "8.0",
    "medium": "5.5",
    "low": "2.0",
    "unknown": "0.0",
}
SARIF_LEVELS = {
    "critical": "error",
    "high": "error",
    "medium": "warning",
    "low": "note",
    "unknown": "none",
}


def normalize_severity(value):
    severity = str(value or "unknown").lower()
    return severity if severity in SEVERITIES else "unknown"


def flatten_advisories(report):
    advisories = report.get("advisories", {})
    flattened = []

    if isinstance(advisories, dict):
        groups = advisories.items()
    elif isinstance(advisories, list):
        groups = ((None, advisories),)
    else:
        groups = ()

    for package, items in groups:
        if isinstance(items, dict):
            items = [items]
        if not isinstance(items, list):
            continue
        for advisory in items:
            if not isinstance(advisory, dict):
                continue
            item = dict(advisory)
            item.setdefault("packageName", package or "unknown package")
            item["severity"] = normalize_severity(item.get("severity"))
            flattened.append(item)

    return flattened


def dependency_count(lock):
    names = set()
    for key in ("packages", "packages-dev"):
        for package in lock.get(key, []):
            name = package.get("name") if isinstance(package, dict) else None
            if name:
                names.add(name)
    return len(names)


def abandoned_packages(report):
    abandoned = report.get("abandoned", {})
    if isinstance(abandoned, dict):
        return abandoned
    if isinstance(abandoned, list):
        return {str(name): None for name in abandoned}
    return {}


def has_blocking_advisories(advisories):
    return any(item["severity"] in FAIL_SEVERITIES for item in advisories)


def sarif_rule_id(advisory, index):
    raw = advisory.get("advisoryId") or advisory.get("cve") or f"composer-advisory-{index}"
    return re.sub(r"[^A-Za-z0-9_.-]+", "-", str(raw)).strip("-") or f"composer-advisory-{index}"


def build_sarif(advisories):
    rules = []
    results = []
    used_ids = set()

    for index, advisory in enumerate(advisories, start=1):
        rule_id = sarif_rule_id(advisory, index)
        if rule_id in used_ids:
            rule_id = f"{rule_id}-{index}"
        used_ids.add(rule_id)

        severity = advisory["severity"]
        title = advisory.get("title") or advisory.get("cve") or "Composer security advisory"
        package = advisory.get("packageName") or "unknown package"
        affected = advisory.get("affectedVersions") or "affected versions not reported"
        link = advisory.get("link")

        rule = {
            "id": rule_id,
            "shortDescription": {"text": str(title)},
            "properties": {
                "security-severity": SECURITY_SCORES[severity],
                "tags": ["security", severity],
            },
        }
        if link:
            rule["helpUri"] = str(link)
        rules.append(rule)

        results.append(
            {
                "ruleId": rule_id,
                "level": SARIF_LEVELS[severity],
                "message": {
                    "text": f"{package}: {title} ({affected})",
                },
                "locations": [
                    {
                        "physicalLocation": {
                            "artifactLocation": {"uri": "composer.lock"},
                            "region": {"startLine": 1},
                        }
                    }
                ],
            }
        )

    return {
        "$schema": "https://json.schemastore.org/sarif-2.1.0.json",
        "version": "2.1.0",
        "runs": [
            {
                "tool": {
                    "driver": {
                        # Keep the existing tool identity so GitHub's current
                        # dependency-check code-scanning check remains stable.
                        "name": "dependency-check",
                        "informationUri": "https://getcomposer.org/doc/03-cli.md#audit",
                        "rules": rules,
                    }
                },
                "results": results,
            }
        ],
    }


def write_summary(advisories, abandoned, audited_count):
    counts = Counter(item["severity"] for item in advisories)

    print("### Composer Dependency Audit\n")
    print("| Metric | Result |")
    print("| --- | ---: |")
    print(f"| Locked dependencies audited | **{audited_count}** |")
    for severity in SEVERITIES:
        print(f"| {severity.capitalize()} advisories | **{counts[severity]}** |")
    print(f"| Abandoned packages | **{len(abandoned)}** |")

    if advisories:
        print("\n<details>")
        print("<summary>Security advisories</summary>\n")
        for advisory in sorted(
            advisories,
            key=lambda item: SEVERITIES.index(item["severity"]),
        ):
            severity = advisory["severity"].capitalize()
            package = advisory.get("packageName") or "unknown package"
            title = advisory.get("title") or advisory.get("cve") or "Composer security advisory"
            cve = advisory.get("cve")
            suffix = f" ({cve})" if cve and cve not in str(title) else ""
            print(f"- **{severity}** — `{package}` — {title}{suffix}")
        print("\n</details>")
    else:
        print("\nNo Composer security advisories were reported.")

    if abandoned:
        print("\n<details>")
        print("<summary>Abandoned packages</summary>\n")
        for package, replacement in sorted(abandoned.items()):
            if replacement:
                print(f"- `{package}` — suggested replacement: `{replacement}`")
            else:
                print(f"- `{package}`")
        print("\n</details>")

    print("\n**Merge gate:** Critical or High Composer advisories fail this job. Medium/Low advisories and abandoned packages remain visible for review but do not fail the gate.")


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--report", required=True)
    parser.add_argument("--lock", required=True)
    parser.add_argument("--sarif")
    args = parser.parse_args()

    report_path = Path(args.report)
    lock_path = Path(args.lock)

    try:
        report = json.loads(report_path.read_text(encoding="utf-8"))
        lock = json.loads(lock_path.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as error:
        print("### Composer Dependency Audit\n")
        print(f"⚠️ Unable to read Composer audit data: {error}")
        return 2

    advisories = flatten_advisories(report)
    abandoned = abandoned_packages(report)

    if args.sarif:
        sarif_path = Path(args.sarif)
        sarif_path.parent.mkdir(parents=True, exist_ok=True)
        sarif_path.write_text(
            json.dumps(build_sarif(advisories), indent=2) + "\n",
            encoding="utf-8",
        )

    write_summary(advisories, abandoned, dependency_count(lock))
    return 1 if has_blocking_advisories(advisories) else 0


if __name__ == "__main__":
    sys.exit(main())
