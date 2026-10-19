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
            )

            for relative_path in expected:
                path = root / relative_path
                self.assertTrue(path.is_dir(), f"{relative_path} was not created")
                self.assertEqual(
                    0o777,
                    stat.S_IMODE(path.stat().st_mode),
                    f"{relative_path} is not writable for local Apache",
                )


if __name__ == "__main__":
    unittest.main()
