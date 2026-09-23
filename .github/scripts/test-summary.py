#!/usr/bin/env python3

import argparse
import sys
import xml.etree.ElementTree as ET
from pathlib import Path


def local_name(tag):
    return tag.rsplit("}", 1)[-1]


def percentage(covered, total):
    if total == 0:
        return 100.0
    return covered * 100.0 / total


def parse_junit(path):
    root = ET.parse(path).getroot()
    testcases = [element for element in root.iter() if local_name(element.tag) == "testcase"]

    failed = 0
    errors = 0
    skipped = 0
    failed_names = []

    for testcase in testcases:
        children = {local_name(child.tag) for child in testcase}
        name = testcase.get("name", "Unnamed test")
        classname = testcase.get("classname", "")

        if "failure" in children:
            failed += 1
            failed_names.append(f"{classname}::{name}" if classname else name)
        elif "error" in children:
            errors += 1
            failed_names.append(f"{classname}::{name}" if classname else name)
        elif "skipped" in children:
            skipped += 1

    total = len(testcases)
    passed = total - failed - errors - skipped

    return {
        "total": total,
        "passed": passed,
        "failed": failed,
        "errors": errors,
        "skipped": skipped,
        "failed_names": failed_names,
    }


def parse_clover(path):
    root = ET.parse(path).getroot()
    project = next((element for element in root.iter() if local_name(element.tag) == "project"), None)

    metrics = None
    if project is not None:
        metrics = next(
            (child for child in project if local_name(child.tag) == "metrics"),
            None,
        )

    if metrics is not None and metrics.get("statements") is not None:
        statements = int(metrics.get("statements", 0))
        covered_statements = int(metrics.get("coveredstatements", 0))
        methods = int(metrics.get("methods", 0))
        covered_methods = int(metrics.get("coveredmethods", 0))
    else:
        statements = 0
        covered_statements = 0
        methods = 0
        covered_methods = 0
        for file_element in (element for element in root.iter() if local_name(element.tag) == "file"):
            file_metrics = next(
                (child for child in file_element if local_name(child.tag) == "metrics"),
                None,
            )
            if file_metrics is None:
                continue
            statements += int(file_metrics.get("statements", 0))
            covered_statements += int(file_metrics.get("coveredstatements", 0))
            methods += int(file_metrics.get("methods", 0))
            covered_methods += int(file_metrics.get("coveredmethods", 0))

    return {
        "line": percentage(covered_statements, statements),
        "covered_lines": covered_statements,
        "lines": statements,
        "function": percentage(covered_methods, methods),
        "covered_functions": covered_methods,
        "functions": methods,
    }


def parse_lcov(path):
    totals = {
        "lines": 0,
        "covered_lines": 0,
        "branches": 0,
        "covered_branches": 0,
        "functions": 0,
        "covered_functions": 0,
    }

    for line in Path(path).read_text(encoding="utf-8").splitlines():
        key, separator, value = line.partition(":")
        if not separator:
            continue
        if key == "LF":
            totals["lines"] += int(value)
        elif key == "LH":
            totals["covered_lines"] += int(value)
        elif key == "BRF":
            totals["branches"] += int(value)
        elif key == "BRH":
            totals["covered_branches"] += int(value)
        elif key == "FNF":
            totals["functions"] += int(value)
        elif key == "FNH":
            totals["covered_functions"] += int(value)

    return {
        **totals,
        "line": percentage(totals["covered_lines"], totals["lines"]),
        "branch": percentage(totals["covered_branches"], totals["branches"]),
        "function": percentage(totals["covered_functions"], totals["functions"]),
    }


def format_percent(value):
    return f"{value:.1f}%"


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--title", required=True)
    parser.add_argument("--junit", required=True)
    coverage_group = parser.add_mutually_exclusive_group(required=True)
    coverage_group.add_argument("--clover")
    coverage_group.add_argument("--lcov")
    args = parser.parse_args()

    junit_path = Path(args.junit)
    if not junit_path.exists():
        print(f"### {args.title}\n")
        print(f"⚠️ Test report not found: `{args.junit}`")
        return 0

    results = parse_junit(junit_path)

    print(f"### {args.title}\n")
    print("| Metric | Result |")
    print("| --- | ---: |")
    print(f"| Tests run | **{results['total']}** |")
    print(f"| Passed | **{results['passed']}** |")
    print(f"| Failed | **{results['failed'] + results['errors']}** |")
    if results["errors"]:
        print(f"| Errors | **{results['errors']}** |")
    print(f"| Skipped | **{results['skipped']}** |")

    coverage_path = Path(args.clover or args.lcov)
    if coverage_path.exists():
        if args.clover:
            coverage = parse_clover(coverage_path)
            print(
                f"| Coverage | **{format_percent(coverage['line'])}** "
                f"({coverage['covered_lines']}/{coverage['lines']} statements) |"
            )
            if coverage["functions"]:
                print(
                    f"| Method coverage | **{format_percent(coverage['function'])}** "
                    f"({coverage['covered_functions']}/{coverage['functions']}) |"
                )
        else:
            coverage = parse_lcov(coverage_path)
            print(
                f"| Line coverage | **{format_percent(coverage['line'])}** "
                f"({coverage['covered_lines']}/{coverage['lines']}) |"
            )
            print(
                f"| Branch coverage | **{format_percent(coverage['branch'])}** "
                f"({coverage['covered_branches']}/{coverage['branches']}) |"
            )
            print(
                f"| Function coverage | **{format_percent(coverage['function'])}** "
                f"({coverage['covered_functions']}/{coverage['functions']}) |"
            )
    else:
        print(f"| Coverage | ⚠️ Report not found: `{coverage_path}` |")

    if results["failed_names"]:
        print("\n<details>")
        print("<summary>Failed tests</summary>\n")
        for name in results["failed_names"]:
            print(f"- `{name}`")
        print("\n</details>")

    return 0


if __name__ == "__main__":
    sys.exit(main())
