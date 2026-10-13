import os
import stat
import subprocess
import tempfile
import unittest
from pathlib import Path


REPO_ROOT = Path(__file__).resolve().parents[2]


class PrepareLocalContentScriptTests(unittest.TestCase):
    def test_creates_writable_local_content_directories(self):
        expected = [
            "content",
            "content/commercial",
            "content/portrait",
            "content/wedding",
            "content/b-nai-mitzvah",
            "content/main",
            "content/reviews",
            "content/albums",
            "content/blog",
            "content/contracts",
            "logs",
            "public/tmp",
        ]

        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            subprocess.run(
                [
                    "bash",
                    str(REPO_ROOT / "bin/prepare-local-content.sh"),
                    str(root),
                ],
                check=True,
                env={**os.environ, "CONTENT_WRITE_GROUP": str(os.getgid())},
            )

            for relative_path in expected:
                path = root / relative_path
                self.assertTrue(path.is_dir(), f"{relative_path} was not created")
                self.assertEqual(
                    0o2775,
                    stat.S_IMODE(path.stat().st_mode),
                    f"{relative_path} should be owner/group writable, not world-writable",
                )
                self.assertEqual(os.getgid(), path.stat().st_gid)


if __name__ == "__main__":
    unittest.main()
