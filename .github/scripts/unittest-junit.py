#!/usr/bin/env python3

import argparse
import sys
import time
import unittest
import xml.etree.ElementTree as ET
from pathlib import Path


def iter_tests(suite):
    for test in suite:
        if isinstance(test, unittest.TestSuite):
            yield from iter_tests(test)
        else:
            yield test


def test_identity(test):
    test_id = test.id()
    classname, separator, name = test_id.rpartition(".")
    if not separator:
        return "", test_id
    return classname, name


def write_junit(path, tests, result, elapsed):
    failures = {test.id(): detail for test, detail in result.failures}
    errors = {test.id(): detail for test, detail in result.errors}
    skipped = {test.id(): reason for test, reason in result.skipped}
    expected_failures = {test.id(): detail for test, detail in result.expectedFailures}
    unexpected_successes = {test.id() for test in result.unexpectedSuccesses}

    testsuite = ET.Element(
        "testsuite",
        {
            "tests": str(result.testsRun),
            "failures": str(len(result.failures) + len(result.unexpectedSuccesses)),
            "errors": str(len(result.errors)),
            "skipped": str(len(result.skipped) + len(result.expectedFailures)),
            "time": f"{elapsed:.6f}",
        },
    )

    for test in tests:
        test_id = test.id()
        classname, name = test_identity(test)
        testcase = ET.SubElement(
            testsuite,
            "testcase",
            {"classname": classname, "name": name},
        )

        if test_id in failures:
            child = ET.SubElement(testcase, "failure")
            child.text = failures[test_id]
        elif test_id in errors:
            child = ET.SubElement(testcase, "error")
            child.text = errors[test_id]
        elif test_id in skipped:
            ET.SubElement(testcase, "skipped", {"message": skipped[test_id]})
        elif test_id in expected_failures:
            ET.SubElement(testcase, "skipped", {"message": "expected failure"})
        elif test_id in unexpected_successes:
            ET.SubElement(
                testcase,
                "failure",
                {"message": "unexpected success"},
            )

    output = Path(path)
    output.parent.mkdir(parents=True, exist_ok=True)
    tree = ET.ElementTree(testsuite)
    ET.indent(tree, space="  ")
    tree.write(output, encoding="utf-8", xml_declaration=True)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--start-directory", required=True)
    parser.add_argument("--pattern", default="test*.py")
    parser.add_argument("--junit", required=True)
    args = parser.parse_args()

    suite = unittest.defaultTestLoader.discover(
        args.start_directory,
        pattern=args.pattern,
    )
    tests = list(iter_tests(suite))

    started = time.monotonic()
    result = unittest.TextTestRunner(verbosity=2).run(suite)
    elapsed = time.monotonic() - started
    write_junit(args.junit, tests, result, elapsed)

    return 0 if result.wasSuccessful() else 1


if __name__ == "__main__":
    sys.exit(main())
