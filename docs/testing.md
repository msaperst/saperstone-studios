# Testing

This document covers running the Saperstone Studios automated test suites locally. For the GitHub Actions workflows that execute these checks automatically, see [Continuous Integration](ci.md).

## Testing philosophy

Testing is critical and a first-class engineering requirement. The goal is comprehensive automated protection that gives us confidence to change the application safely. Continually improve the test suite when gaps are discovered. When a bug escapes, ask **"What automated test or pipeline check should have caught this?"** and add that protection when practical.

### Unit tests and coverage

**Unit tests are required for new or changed testable logic.** Bug fixes should include regression tests whenever practical, preferably reproducing the failure before fixing it.

**Code coverage is important. Aim for full meaningful coverage whenever reasonably possible.** The 80% quality gate is a minimum, not the target. Do not add meaningless tests solely to increase coverage; test behavior and contracts rather than implementation details.

### Choose the lowest sensible test layer

Use the least expensive, most deterministic layer that proves the behavior:

- **Unit:** isolated PHP logic, validation, helpers, and business/security rules.
- **Integration:** database, filesystem, configuration, and multi-component behavior.
- **API:** real endpoint contracts including methods, auth/authz, CSRF, validation, errors, status/body/headers, and side effects.
- **Page/HTTP:** pages, navigation, access rules, redirects, forms, rendered content, and server behavior that does not require JavaScript.
- **Jest:** JavaScript/DOM behavior, AJAX success/failure, galleries, lazy loading, favorites, downloads, cookies, errors, and state transitions.
- **Behat:** important real-browser/user workflows requiring JavaScript or multi-step interaction.

Avoid expensive Behat coverage when a lower-level test proves the same behavior reliably.

### New features and regression coverage

Every new feature requires appropriate automated tests. At minimum, add unit tests for testable logic, API/integration/Jest coverage where applicable, and **new or updated Page/HTTP and/or Behat tests for user-facing behavior**. New pages need Page/HTTP coverage; important interactive workflows need Behat coverage.

Do not test only happy paths. Consider failures, malformed or missing input, boundaries, authentication/authorization, guest/user/admin differences, CSRF, HTTP methods, empty states, transitions, AJAX/network failures, side effects, and security behavior.

Tests must be deterministic and isolated. Avoid order dependencies, stale/shared data, arbitrary sleeps, production data, and unstable implementation-specific assertions.

When legacy tests fail, determine the behavior they intended to protect. Update them for the current contract, move them to the proper layer, or remove obsolete assertions with a reason. Never restore obsolete behavior solely to satisfy a stale test.

All functional test commands are managed through Composer. Install dependencies before running the suites:

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

## Full-stack test environment

API, HTTP Page, UI/Behat, and ZAP CI jobs use the same full Docker Compose application. The local composite GitHub Action at `.github/actions/setup-full-stack/action.yml` is the canonical CI setup. It writes the common `.env`, prepares writable test folders, disables the production Let's Encrypt certificate reference, launches the application with Docker Compose, and waits for HTTP port 90 to become available.

The shared stack uses fixed local-only CI credentials for MySQL and Mailpit, so standing up the application does not depend on repository secrets. Tests that require external services, such as Gmail-related browser behavior, configure those credentials separately in their workflow.

For manual local testing, create a suitable local `.env`, prepare the required content/log/tmp directories, disable the production certificate reference when necessary, and launch the application with:

```bash
docker compose up --build -d
```

The CI application is available over HTTP on port 90. Functional suites then run through their Composer commands; only browser-based suites such as Behat additionally start ChromeDriver. ZAP scans the same running application directly.

## API tests

With the full-stack test environment running, ensure Composer dependencies are clean and installed:

```bash
composer clean
composer install --prefer-dist --no-progress --no-suggest
```

Then run:

```bash
COMPOSER_PROCESS_TIMEOUT=1200 composer api-test
```

The API suite writes its JUnit and HTML results beneath `reports/`.

## HTTP page tests

The page suite exercises the running full-stack application over HTTP without a browser. It uses Guzzle for requests and DOM/XPath parsing for rendered HTML assertions, so Chrome and ChromeDriver are not required.

Run it with:

```bash
composer http-page-test
```

The suite uses `phpunit-http-page.xml`, reads the application host and port from the test environment, and writes JUnit and TestDox HTML results beneath `reports/`.

Use this suite for HTTP status codes, redirects, authorization behavior, and server-rendered content. Browser/JavaScript interactions belong in the Behat suite rather than the HTTP page suite.

## Behat tests

The Behat tests exercise browser and JavaScript behavior and therefore still require ChromeDriver. Run them with:

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
| HTTP page tests | `composer http-page-test` |
| Behat UI tests | `composer ui-behat-test` |

Some browser and email-related tests require additional credentials. The workflows under `.github/workflows/` define those suite-specific additions; `.github/actions/setup-full-stack/action.yml` defines the common full-stack CI application environment.
