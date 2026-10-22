"""Run and protect the production-image security test entrypoint."""

import os
import subprocess
import tempfile
import unittest
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
RUNNER = ROOT / "tests/container/run-php-runtime-security.sh"
IN_CONTAINER = ROOT / "tests/container/php-runtime-security.sh"


class ProductionPhpRuntimeTestRunnerTests(unittest.TestCase):
    def test_shell_scripts_have_valid_syntax(self):
        for script in (RUNNER, IN_CONTAINER):
            with self.subTest(script=script.name):
                result = subprocess.run(
                    ["sh", "-n", str(script)], capture_output=True, text=True,
                    check=False,
                )
                self.assertEqual(0, result.returncode, result.stderr)

    def invoke(self, arguments, docker_exit=0):
        with tempfile.TemporaryDirectory() as directory:
            capture = Path(directory) / "docker-args"
            fake_docker = Path(directory) / "docker"
            fake_docker.write_text(
                "#!/bin/sh\n"
                'printf "%s\\n" "$@" > "$TEST_CAPTURE"\n'
                'exit "$TEST_DOCKER_EXIT"\n',
                encoding="utf-8",
            )
            fake_docker.chmod(0o755)
            env = {
                **os.environ,
                "PATH": f"{directory}:{os.environ.get('PATH', '')}",
                "TEST_CAPTURE": str(capture),
                "TEST_DOCKER_EXIT": str(docker_exit),
            }
            proc = subprocess.run(
                ["sh", str(RUNNER), *arguments], capture_output=True,
                text=True, env=env, check=False,
            )
            return proc, capture.read_text().splitlines() if capture.exists() else []

    def test_invokes_test_script_in_real_image_as_unprivileged_user(self):
        proc, arguments = self.invoke(["saperstone-studios/php:latest"])
        self.assertEqual(0, proc.returncode, proc.stderr)
        self.assertEqual(["run", "--rm", "--user", "www-data", "--entrypoint", "/bin/sh"],
                         arguments[:6])
        self.assertIn("saperstone-studios/php:latest", arguments)
        self.assertEqual("/tests/php-runtime-security.sh", arguments[-1])
        mounts = [arguments[i + 1] for i, item in enumerate(arguments[:-1])
                  if item == "--mount"]
        self.assertEqual(2, len(mounts))
        self.assertTrue(all(mount.endswith(",readonly") for mount in mounts))
        self.assertTrue(any("target=/fixtures" in mount for mount in mounts))
        self.assertTrue(any("target=/tests" in mount for mount in mounts))

    def test_docker_test_failure_is_propagated(self):
        result, _ = self.invoke(["saperstone-studios/php:latest"], docker_exit=9)
        self.assertEqual(9, result.returncode)

    def test_image_argument_is_required(self):
        result, arguments = self.invoke([])
        self.assertNotEqual(0, result.returncode)
        self.assertEqual([], arguments)


if __name__ == "__main__":
    unittest.main()
