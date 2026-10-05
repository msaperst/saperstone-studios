import gzip
import importlib.util
import os
import sys
import tempfile
import types
import unittest
from pathlib import Path


REPO_ROOT = Path(__file__).resolve().parents[2]


class FakeRequestException(Exception):
    pass


fake_requests = types.SimpleNamespace(
    head=lambda *args, **kwargs: None,
    RequestException=FakeRequestException,
)
sys.modules["requests"] = fake_requests

spec = importlib.util.spec_from_file_location(
    "create_site_map",
    REPO_ROOT / "bin/create-site-map.py",
)
create_site_map = importlib.util.module_from_spec(spec)
assert spec.loader is not None
spec.loader.exec_module(create_site_map)


class CreateSiteMapScriptTests(unittest.TestCase):
    def test_priority_decreases_with_path_depth(self):
        self.assertEqual("1.0", create_site_map.calculate_priority("index.php"))
        self.assertEqual("0.8", create_site_map.calculate_priority("portrait/details.php"))
        self.assertEqual("0.2", create_site_map.calculate_priority("a/b/c/d/e/page.php"))

    def test_build_sitemap_includes_public_php_and_skips_private_folders(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory) / "public"
            root.mkdir()
            (root / "index.php").write_text("home", encoding="utf-8")
            (root / "portrait").mkdir()
            (root / "portrait" / "details.php").write_text("portrait", encoding="utf-8")
            (root / "api").mkdir()
            (root / "api" / "secret.php").write_text("api", encoding="utf-8")
            (root / "user").mkdir()
            (root / "user" / "private.php").write_text("user", encoding="utf-8")
            (root / "notes.txt").write_text("ignore", encoding="utf-8")

            output = Path(directory) / "sitemap.xml"
            gzip_output = Path(directory) / "sitemap.xml.gz"

            original = {
                "ROOT_DIR": create_site_map.ROOT_DIR,
                "OUTPUT_FILE": create_site_map.OUTPUT_FILE,
                "GZIP_FILE": create_site_map.GZIP_FILE,
                "SITE_URL": create_site_map.SITE_URL,
                "is_url_ok": create_site_map.is_url_ok,
            }
            try:
                create_site_map.ROOT_DIR = str(root)
                create_site_map.OUTPUT_FILE = str(output)
                create_site_map.GZIP_FILE = str(gzip_output)
                create_site_map.SITE_URL = "https://example.test/"
                create_site_map.is_url_ok = lambda url: True

                create_site_map.build_sitemap()
            finally:
                for name, value in original.items():
                    setattr(create_site_map, name, value)

            xml = output.read_text(encoding="utf-8")
            self.assertIn("<loc>https://example.test/</loc>", xml)
            self.assertIn("<loc>https://example.test/portrait/details.php</loc>", xml)
            self.assertNotIn("/api/secret.php", xml)
            self.assertNotIn("/user/private.php", xml)
            self.assertNotIn("notes.txt", xml)

            with gzip.open(gzip_output, "rt", encoding="utf-8") as handle:
                self.assertEqual(xml, handle.read())

    def test_is_url_ok_handles_success_failure_and_request_errors(self):
        original_head = create_site_map.requests.head
        try:
            create_site_map.requests.head = lambda *args, **kwargs: types.SimpleNamespace(status_code=200)
            self.assertTrue(create_site_map.is_url_ok("https://example.test/"))

            create_site_map.requests.head = lambda *args, **kwargs: types.SimpleNamespace(status_code=404)
            self.assertFalse(create_site_map.is_url_ok("https://example.test/missing"))

            def fail(*args, **kwargs):
                raise FakeRequestException("network down")

            create_site_map.requests.head = fail
            self.assertFalse(create_site_map.is_url_ok("https://example.test/"))
        finally:
            create_site_map.requests.head = original_head


if __name__ == "__main__":
    unittest.main()
