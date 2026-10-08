import unittest
from pathlib import Path


REPO_ROOT = Path(__file__).resolve().parents[2]


def read_properties(path):
    properties = {}
    for raw_line in path.read_text(encoding="utf-8").splitlines():
        line = raw_line.strip()
        if not line or line.startswith("#"):
            continue
        key, separator, value = line.partition("=")
        if separator:
            properties[key.strip()] = value.strip()
    return properties


def csv_property(properties, key):
    return {
        value.strip()
        for value in properties.get(key, "").split(",")
        if value.strip()
    }


class StaticAnalysisScopeTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.properties = read_properties(REPO_ROOT / "sonar-project.properties")
        cls.sonar_exclusions = csv_property(cls.properties, "sonar.exclusions")
        cls.coverage_exclusions = csv_property(
            cls.properties,
            "sonar.coverage.exclusions",
        )
        cls.js_test_script = (REPO_ROOT / "bin/test-js.sh").read_text(
            encoding="utf-8",
        )

    def test_pristine_vendor_assets_are_excluded_from_sonar(self):
        expected = {
            "public/js/jqBootstrapValidation.js",
            "public/js/jquery.form.min.js",
            "public/css/uploadfile.css",
        }

        self.assertTrue(expected.issubset(self.sonar_exclusions))

    def test_locally_modified_vendor_forks_remain_in_sonar(self):
        owned_forks = {
            "public/js/jquery.uploadfile.js",
            "public/js/site-consent.js",
            "public/css/hover-effect.css",
            "public/css/modern-business.css",
        }

        self.assertTrue(owned_forks.isdisjoint(self.sonar_exclusions))

    def test_upload_plugin_fork_has_explicit_coverage_exception(self):
        upload_plugin = "public/js/jquery.uploadfile.js"

        self.assertIn(upload_plugin, self.coverage_exclusions)
        self.assertIn(
            "--test-coverage-exclude='public/js/jquery.uploadfile.js'",
            self.js_test_script,
        )

    def test_pristine_vendor_javascript_is_excluded_from_node_coverage(self):
        for asset in (
            "public/js/jqBootstrapValidation.js",
            "public/js/jquery.form.min.js",
        ):
            self.assertIn(
                f"--test-coverage-exclude='{asset}'",
                self.js_test_script,
            )

    def test_mysql_sql_is_not_assigned_to_plsql(self):
        plsql_suffixes = csv_property(
            self.properties,
            "sonar.plsql.file.suffixes",
        )

        self.assertNotIn("sql", plsql_suffixes)


if __name__ == "__main__":
    unittest.main()
