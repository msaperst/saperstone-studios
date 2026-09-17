# Testing

This document covers running the Saperstone Studios automated test suites locally. For the GitHub Actions workflows that execute these checks automatically, see [Continuous Integration](ci.md).

All test commands are managed through Composer. Install dependencies before running the suites:

```bash
composer install
```

## Unit tests

Run the unit tests with:

```bash
composer unit-test
```

This runs the PHPUnit unit configuration and generates:

- JUnit: `reports/ut-junit.xml`
- TestDox: `reports/ut-results.html`
- Clover coverage: `reports/ut-coverage/index.html`

## Integration tests

The integration suite requires its supporting test environment. Set it up with:

```bash
composer integration-pre-test
```

This starts the SQL and Mailpit services used by the integration tests and initializes the database.

Run the tests with:

```bash
composer integration-test
```

The suite generates:

- JUnit: `reports/it-junit.xml`
- TestDox: `reports/it-results.html`
- Clover coverage: `reports/it-coverage/index.html`

When finished, the supporting containers can be removed with:

```bash
composer integration-post-test
```

## Combined coverage

Run the unit and integration coverage workflow with:

```bash
composer coverage-test
```

This runs the unit and integration suites and merges their Clover coverage output.

## API tests

The API tests exercise a running application. Start the application first:

```bash
docker compose up --build -d
```

Load the local environment and point database access at the locally exposed database:

```bash
set -a
source .env
set +a
export DB_HOST=localhost
```

Ensure the Composer dependencies are clean and installed:

```bash
composer clean
composer install --prefer-dist --no-progress --no-suggest
```

Verify the application is available. The CI environment uses port 90:

```bash
curl --retry 50 -f --retry-all-errors --retry-delay 5 -s -o /dev/null "http://localhost:90/"
```

Then run:

```bash
COMPOSER_PROCESS_TIMEOUT=1200 composer api-test
```

The API suite writes its JUnit and HTML results beneath `reports/`.

## UI page tests

The UI page tests require a running application and ChromeDriver. On Debian/Ubuntu, ChromeDriver can be installed with:

```bash
sudo apt install chromium-chromedriver
```

Start ChromeDriver:

```bash
chromedriver --port=4444
```

With the Docker Compose application running, execute:

```bash
composer ui-page-test
```

The CI version of this suite runs headlessly and writes its reports beneath `reports/`.

## Behat tests

The Behat browser tests use the same running application and ChromeDriver prerequisites as the UI page tests. Run them with:

```bash
composer ui-behat-test
```

## Test command reference

| Suite | Command |
| --- | --- |
| Unit | `composer unit-test` |
| Integration setup | `composer integration-pre-test` |
| Integration | `composer integration-test` |
| Integration cleanup | `composer integration-post-test` |
| Combined coverage | `composer coverage-test` |
| API | `composer api-test` |
| UI page tests | `composer ui-page-test` |
| Behat UI tests | `composer ui-behat-test` |

Some browser and email-related tests require additional environment variables or credentials. The corresponding workflow under `.github/workflows/` is the canonical reference for the complete environment used in CI.
