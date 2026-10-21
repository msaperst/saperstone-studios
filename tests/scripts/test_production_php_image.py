"""Test production drift verification without running production Docker."""

import os
import subprocess
import tempfile
import unittest
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
SCRIPT = ROOT / "bin/check-production-php-image.sh"


class ProductionPhpImageTests(unittest.TestCase):
    def invoke(self, published, running, pull_ok=True):
        with tempfile.TemporaryDirectory() as directory:
            fake = Path(directory) / "docker"
            fake.write_text(
                "#!/bin/sh\n"
                'case "$1" in\n'
                f'  pull) exit {0 if pull_ok else 1} ;;\n'
                f'  inspect) printf "%s\\n" "{running}" ;;\n'
                f'  image) printf "%s\\n" "{published}" ;;\n'
                '  exec) echo "PHP 8.4.27 (cli)" ;;\n'
                '  *) exit 91 ;;\n'
                'esac\n', encoding="utf-8"
            )
            fake.chmod(0o755)
            env = dict(os.environ, PATH=f"{directory}:{os.environ['PATH']}")
            return subprocess.run(
                ["sh", str(SCRIPT)], capture_output=True,
                text=True, env=env, check=False
            )

    def test_running_image_matches_published_image(self):
        result = self.invoke("sha256:abc", "sha256:abc")
        self.assertEqual(0, result.returncode, result.stderr)
        self.assertIn("matches the published image", result.stdout)

    def test_detects_outdated_running_image(self):
        result = self.invoke("sha256:new", "sha256:old")
        self.assertEqual(1, result.returncode)
        self.assertIn("does not match", result.stderr)

    def test_registry_pull_failure_fails_closed(self):
        result = self.invoke("sha256:abc", "sha256:abc", pull_ok=False)
        self.assertNotEqual(0, result.returncode)
        self.assertNotIn("matches the published image", result.stdout)
