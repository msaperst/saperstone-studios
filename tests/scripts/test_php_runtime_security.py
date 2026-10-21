"""Prevent regressions to the PHP versions vulnerable to October 2026 RCEs."""

import re
import unittest
from pathlib import Path


REPO_ROOT = Path(__file__).resolve().parents[2]
PHP_DOCKERFILE = REPO_ROOT / ".docker/php/Dockerfile"


def uses_patched_php_84_image(dockerfile):
    """Require a fixed patch release, Apache, and our current Debian variant."""
    images = re.findall(r"^FROM\s+php:(\S+)\s*$", dockerfile, flags=re.MULTILINE)
    if len(images) != 1:
        return False

    match = re.fullmatch(r"8\.4\.(\d+)-apache-bookworm", images[0])
    return match is not None and int(match.group(1)) >= 27


class PhpRuntimeSecurityTests(unittest.TestCase):
    def test_production_php_base_is_patched(self):
        dockerfile = PHP_DOCKERFILE.read_text(encoding="utf-8")
        self.assertTrue(
            uses_patched_php_84_image(dockerfile),
            "Production must use a patched PHP 8.4 Apache image (>=8.4.27).",
        )

    def test_accepts_fixed_php_84_patch_releases(self):
        for patch in (27, 28, 99):
            with self.subTest(patch=patch):
                self.assertTrue(
                    uses_patched_php_84_image(f"FROM php:8.4.{patch}-apache-bookworm\n")
                )

    def test_rejects_vulnerable_or_unpinned_php_images(self):
        for image in (
            "8.4.26-apache-bookworm",
            "8.4.0-apache-bookworm",
            "8.4-apache-bookworm",
            "8.3.36-apache-bookworm",
            "8.5.12-apache-bookworm",
            "8.4.27-fpm-bookworm",
            "8.4.27-apache-trixie",
        ):
            with self.subTest(image=image):
                self.assertFalse(uses_patched_php_84_image(f"FROM php:{image}\n"))


if __name__ == "__main__":
    unittest.main()
