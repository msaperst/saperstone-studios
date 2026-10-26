"""Guard ImageMagick's production policy against accidental exposure expansion."""

import base64
import bz2
import re
import unittest
import xml.etree.ElementTree as ET
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
POLICY = ROOT / ".docker/php/image-processing-policy.xml"
DOCKERFILE = ROOT / ".docker/php/Dockerfile"
CONTAINER_SCAN_WORKFLOW = ROOT / ".github/workflows/container-scan.yml"


class ImageProcessingPolicyTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        root = ET.parse(POLICY).getroot()
        cls.policies = [entry.attrib for entry in root.findall("policy")]

    def test_untrusted_decoder_modules_default_deny_and_explicit_photo_allow(self):
        module_rules = [p for p in self.policies if p.get("domain") == "module"]
        self.assertEqual(
            [
                {"domain": "module", "rights": "none", "pattern": "*"},
                {"domain": "module", "rights": "read | write",
                 "pattern": "{GIF,JPEG,PNG}"},
            ],
            module_rules,
        )

    def test_external_delegates_filters_and_indirect_reads_are_denied(self):
        for domain, pattern in (
            ("delegate", "*"),
            ("filter", "*"),
            ("path", "@*"),
            ("path", "|*"),
        ):
            with self.subTest(domain=domain, pattern=pattern):
                self.assertIn(
                    {"domain": domain, "rights": "none", "pattern": pattern},
                    self.policies,
                )

    def test_bzip2_paths_are_denied_even_when_photo_decoders_are_allowed(self):
        # Transparent BZh blob decompression can happen independently of the
        # decoder module policy. Explicit paths must be blocked too.
        for pattern in ("*.[Bb][Zz]", "*.[Bb][Zz]2"):
            with self.subTest(pattern=pattern):
                self.assertIn(
                    {"domain": "path", "rights": "none", "pattern": pattern},
                    self.policies,
                )

    def test_process_resources_have_bounded_limits_without_global_time_limit(self):
        resources = {p["name"]: p["value"] for p in self.policies
                     if p.get("domain") == "resource"}
        self.assertEqual({
            "thread": "2", "memory": "256MiB", "map": "512MiB",
            "disk": "2GiB", "file": "256"
        }, resources)
        # ImageMagick 6 may terminate a long-lived Apache PHP worker if
        # its elapsed process time exceeds a global time policy, producing
        # "empty reply from server" instead of a catchable Imagick exception.
        self.assertNotIn("time", resources)

    def test_bzip2_security_regression_fixture_is_valid_gif_and_exercised(self):
        script = (ROOT / "tests/container/php-runtime-security.sh").read_text(
            encoding="utf-8"
        )
        # Guard against a bogus/truncated payload that would fail even if
        # ImageMagick were configured to permit BZIP2 decoding.
        match = re.search(r"'(Qlpo[A-Za-z0-9+/=]+)'", script)
        self.assertIsNotNone(match)
        decoded = bz2.decompress(base64.b64decode(match.group(1)))
        self.assertEqual(
            decoded,
            base64.b64decode("R0lGODdhAQABAIEAAP8AAAAAAAAAAAAAACwAAAAAAQABAAAIBAABBAQAOw=="),
        )
        self.assertIn('convert "$source" "$work/bzip-decoded.gif"', script)
        self.assertIn('test -e "$work/bzip-decoded.gif"', script)

    def test_built_image_integration_suite_is_invoked_by_ci(self):
        workflow = CONTAINER_SCAN_WORKFLOW.read_text(encoding="utf-8")
        self.assertIn(
            "run: sh tests/container/run-php-runtime-security.sh saperstone-studios/php:latest",
            workflow,
        )

    def test_production_image_installs_policy_without_running_tests_during_build(self):
        text = DOCKERFILE.read_text(encoding="utf-8")
        self.assertIn(
            "COPY .docker/php/image-processing-policy.xml /etc/ImageMagick-6/policy.xml",
            text,
        )
        for inline_test in ("policy-smoke", "PHP_VERSION_ID", "extension_loaded(",
                            "Strings::assetUrl("):
            with self.subTest(inline_test=inline_test):
                self.assertNotIn(inline_test, text)


if __name__ == "__main__":
    unittest.main()
