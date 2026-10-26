#!/usr/bin/env python3
"""Check the installed SQL image CA package against Oracle's eMudhra fix.

Run against the *built* image in the container scan job, never during
Dockerfile construction. The package minimum comes from ELSA-2026-88459
(October 23, 2026): CVE-2026-114406.
https://linux.oracle.com/errata/ELSA-2026-88459.html
"""

import re
import subprocess
import sys


MINIMUM_VERSION = "2026.2.92_v9.0.319-90.3.el9_8"


def version_components(value):
    """Extract the numeric sequence from an Oracle Linux CA RPM version.

    This is a deliberately narrow version floor for this specific advisory,
    not a general substitute for RPM's package version comparison semantics.
    """
    value = value.strip()
    if not re.match(r"^\d{4}\.\d+\.\d+_v\d+\.\d+\.\d+-\d+\.\d+", value):
        raise ValueError(f"Unrecognized ca-certificates RPM version: {value!r}")
    return tuple(int(part) for part in re.findall(r"\d+", value))


def is_patched(version):
    return version_components(version) >= version_components(MINIMUM_VERSION)


def check_image(image):
    result = subprocess.run(
        [
            "docker", "run", "--rm", "--user", "nobody",
            "--entrypoint", "rpm", image,
            "-q", "--qf", "%{VERSION}-%{RELEASE}", "ca-certificates",
        ],
        capture_output=True,
        text=True,
        check=True,
    )
    version = result.stdout.strip()
    if not is_patched(version):
        raise ValueError(
            f"SQL image ca-certificates {version!r} predates "
            f"ELSA-2026-88459 ({MINIMUM_VERSION}); rebuild with current "
            "Oracle Linux security updates."
        )
    return version


def main(argv):
    if len(argv) != 1 or not argv[0] or argv[0].startswith("-"):
        print("Usage: check_sql_ca_certificates.py IMAGE", file=sys.stderr)
        return 2
    try:
        version = check_image(argv[0])
    except (ValueError, subprocess.CalledProcessError, OSError) as error:
        print(f"SQL image certificate security verification failed: {error}", file=sys.stderr)
        return 1
    print(f"SQL image ca-certificates {version} includes ELSA-2026-88459 fix")
    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
