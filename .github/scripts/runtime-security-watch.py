#!/usr/bin/env python3
"""Monitor published runtime images and the upstream PHP patch release."""

import argparse
import importlib.util
import json
import re
from pathlib import Path


MODULE_PATH = Path(__file__).with_name("container-scan-summary.py")
spec = importlib.util.spec_from_file_location("container_scan_summary", MODULE_PATH)
container_scan = importlib.util.module_from_spec(spec)
spec.loader.exec_module(container_scan)


def php_version(value):
    match = re.fullmatch(r"(\d+)\.(\d+)\.(\d+)", value.strip())
    if not match:
        raise ValueError(f"Invalid PHP version: {value!r}")
    return tuple(map(int, match.groups()))


def high_severity_findings(sarif, reviewed):
    if not isinstance(sarif.get("runs"), list) or not sarif["runs"]:
        raise ValueError("Missing SARIF scan runs")
    findings = {}
    for finding in container_scan.findings_from_sarif(sarif):
        rule_id = finding["rule_id"]
        if finding["severity"] in {"critical", "high"} and rule_id not in reviewed:
            findings[rule_id] = finding["severity"]
    return dict(sorted(findings.items()))


def evaluate(current, latest, findings, previous):
    installed = php_version(current)
    upstream = php_version(latest)
    if installed[:2] != (8, 4) or upstream[:2] != (8, 4):
        raise ValueError("Expected PHP 8.4 for published and upstream releases")
    if previous is not None and previous.get("schema") != 1:
        raise ValueError("Unrecognized previous vulnerability snapshot")
    prior = (previous or {}).get("findings", {})
    if not isinstance(prior, dict):
        raise ValueError("Invalid previous vulnerability snapshot")

    new = {
        rule: level for rule, level in findings.items()
        if rule not in prior or (level == "critical" and prior[rule] != "critical")
    }
    first_run = previous is None
    outdated = installed < upstream
    return {
        "initial_baseline": first_run,
        "outdated": outdated,
        "new": new,
        "alert": bool(first_run or outdated or new),
    }


def report(image, image_id, installed, latest, findings, evaluation):
    lines = [
        "## Published image security watch", "",
        f"- PHP image: `{image}`",
        f"- PHP image ID: `{image_id}`",
        f"- Published PHP version: `{installed}`",
        f"- Latest official PHP 8.4 release: `{latest}`",
        f"- Unreviewed high/critical findings: **{len(findings)}**",
        f"- New or escalated high/critical findings: **{len(evaluation['new'])}**", "",
    ]
    if evaluation["initial_baseline"]:
        lines += [
            "**Baseline review required.** First scan; existing findings are not approved or ignored.", ""
        ]
    if evaluation["outdated"]:
        lines += [
            "**PHP update required.** Published GHCR PHP is behind its upstream release. "
            "Update/test, publish, and redeploy.", ""
        ]
    if evaluation["new"]:
        lines += ["### New or escalated vulnerabilities", ""]
        lines += [
            f"- **{severity.upper()}** `{identifier}`"
            for identifier, severity in list(evaluation["new"].items())[:40]
        ]
        if len(evaluation["new"]) > 40:
            lines += [f"- …and {len(evaluation['new']) - 40} more"]
        lines += [""]
    if not evaluation["alert"]:
        lines += ["No new high/critical findings or PHP release gap detected.", ""]
    lines += [
        "This scans published GHCR images, **not the Pi's running containers**. "
        "Verify the production deployment separately."
    ]
    return "\n".join(lines) + "\n"


def run(args):
    if bool(args.sql_scan) != bool(args.sql_image) or bool(args.sql_scan) != bool(args.sql_image_id):
        raise ValueError("SQL scan, image, and image ID must be supplied together")

    reviewed = container_scan.load_allowlist(Path(args.allowlist))
    php_scan = json.loads(Path(args.scan).read_text(encoding="utf-8"))
    findings = {
        f"php:{rule}": severity
        for rule, severity in high_severity_findings(php_scan, reviewed).items()
    }
    sql_image_id = None
    if args.sql_scan:
        sql_scan = json.loads(Path(args.sql_scan).read_text(encoding="utf-8"))
        findings.update({
            f"sql:{rule}": severity
            for rule, severity in high_severity_findings(sql_scan, reviewed).items()
        })
        sql_image_id = Path(args.sql_image_id).read_text(encoding="utf-8").strip()
        if not sql_image_id.startswith("sha256:"):
            raise ValueError("Invalid SQL Docker image ID")

    previous = None
    if args.previous and Path(args.previous).exists():
        previous = json.loads(Path(args.previous).read_text(encoding="utf-8"))

    current = Path(args.current_version).read_text(encoding="utf-8").strip()
    metadata = json.loads(Path(args.upstream_release).read_text(encoding="utf-8"))
    latest = metadata.get("version", "")
    if not isinstance(latest, str):
        raise ValueError("Missing official PHP release version")
    status = evaluate(current, latest, findings, previous)

    image_id = Path(args.image_id).read_text(encoding="utf-8").strip()
    if not image_id.startswith("sha256:"):
        raise ValueError("Invalid Docker image ID")

    snapshot = {
        "schema": 1, "image": args.image, "php_version": current, "findings": findings,
    }
    Path(args.snapshot).write_text(
        json.dumps(snapshot, indent=2, sort_keys=True) + "\n", encoding="utf-8"
    )
    markdown = report(args.image, image_id, current, latest, findings, status)
    if sql_image_id:
        markdown += f"\n- SQL image: `{args.sql_image}`\n- SQL image ID: `{sql_image_id}`\n"
    Path(args.report).write_text(markdown, encoding="utf-8")
    Path(args.alert).write_text("true\n" if status["alert"] else "false\n", encoding="utf-8")
    print(markdown)


def main():
    parser = argparse.ArgumentParser()
    flags = (
        "scan", "sql-scan", "sql-image", "sql-image-id", "previous",
        "current-version", "upstream-release", "allowlist", "image",
        "image-id", "snapshot", "report", "alert",
    )
    optional = {"previous", "sql-scan", "sql-image", "sql-image-id"}
    for flag in flags:
        parser.add_argument(f"--{flag}", required=flag not in optional)
    run(parser.parse_args())


if __name__ == "__main__":
    main()
