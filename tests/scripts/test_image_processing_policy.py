"""Guard ImageMagick's production policy against accidental exposure expansion."""

import unittest
import xml.etree.ElementTree as ET
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
POLICY = ROOT / ".docker/php/image-processing-policy.xml"
DOCKERFILE = ROOT / ".docker/php/Dockerfile"


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

    def test_process_resources_have_bounded_limits(self):
        resources = {p["name"]: p["value"] for p in self.policies
                     if p.get("domain") == "resource"}
        self.assertEqual({
            "thread": "2", "memory": "256MiB", "map": "512MiB",
            "disk": "2GiB", "time": "180", "file": "256"
        }, resources)

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
