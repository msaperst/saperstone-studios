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
- **JavaScript (`node:test`):** DOM behavior, AJAX success/failure, galleries, lazy loading, favorites, downloads, cookies, errors, and state transitions.
- **Behat:** important real-browser/user workflows requiring JavaScript or multi-step interaction.

Avoid expensive Behat coverage when a lower-level test proves the same behavior reliably.

### New features and regression coverage

Every new feature requires appropriate automated tests. At minimum, add unit tests for testable logic, API/integration/JavaScript coverage where applicable, and **new or updated Page/HTTP and/or Behat tests for user-facing behavior**. New pages need Page/HTTP coverage; important interactive workflows need Behat coverage.

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

## JavaScript tests

Run the first-party JavaScript unit/DOM suite with:

```bash
bash bin/test-js.sh
```

The suite uses Node's built-in `node:test` runner and generates:

- JUnit: `reports/js-junit.xml`
- LCOV: `reports/js-lcov.info`

Coverage includes first-party files under `public/js/` and excludes the vendored jQuery validation/form/upload helpers configured in `bin/test-js.sh` and `sonar-project.properties`.

## Script tests

Repository and CI helper scripts have lightweight regression coverage using Python's standard library test runner:

```bash
python3 -m unittest discover -s tests/scripts -p 'test_*.py'
```

These tests protect CI/reporting behavior without introducing another Python test dependency. Operational PHP workers that depend on filesystem or Mailpit behavior are covered in the integration suite.

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

### Behat functional-test design

Behat is the full-stack, user-facing test layer. A scenario should prove behavior through the same browser-visible interface a user exercises rather than using lower-level state as a shortcut.

- **Given steps may establish fixtures directly.** Database, filesystem, Mailpit cleanup, and similar setup are appropriate when they put the application into a deterministic known state without being the behavior under test.
- **When steps must exercise the application through the browser UI.** Do not perform the action by updating the database, calling an API directly, or invoking application internals from test glue. If browser interaction cannot perform the behavior, the Behat scenario is not proving the full-stack workflow.
- **Then steps must verify user-observable behavior through the browser UI.** Do not use database queries, direct API calls, or internal application state as the assertion. Those checks belong in unit, integration, or API tests.
- A narrow exception is allowed for **externally observable outputs the browser cannot directly introspect**, such as delivered email and downloaded files. Those may be verified through the test email sink (Mailpit) or browser download directory because they are real user-facing outcomes of the browser workflow. Even in these cases, derive expected values from the scenario/fixture rather than querying application persistence to discover what should have happened.
- Test the **user outcome**, not an implementation proxy. For example, assert that updated content is visible rather than merely that a modal closed or an AJAX request completed.
- Use explicit browser-visible synchronization instead of arbitrary sleeps. Wait for the state the user would observe: a dialog closes, a control becomes enabled, content appears, navigation completes, or an error is displayed.
- Preserve intentional UI behavior such as lazy loading and protected-image rendering. Interact with it as a user would instead of bypassing it to make a test easier.

For mutations that affect the current page, distinguish two contracts when both matter:

1. **Immediate UI behavior:** after the action completes, the current page reflects the change without an unnecessary reload.
2. **Persistence:** after a reload or later navigation, the changed state is still visible.

These should normally be separate scenarios so a persistence check cannot hide a broken immediate UI update. Not every mutation needs both: if the action itself is the visible change (for example, drag-and-drop reordering), test that interaction and add a reload scenario when persistence is the meaningful additional contract. Likewise, do not invent an immediate UI requirement that the product intentionally does not provide.

Keep lower-layer verification in the appropriate suite. Behat may overlap with Jest/API/Page tests at important workflow boundaries, but its purpose is to prove that the assembled application works from the user's perspective.

## Test command reference

| Suite | Command |
| --- | --- |
| Unit | `composer unit-test` |
| Integration setup | `composer integration-pre-test` |
| Integration | `composer integration-test` |
| Integration cleanup | `composer integration-post-test` |
| Combined coverage | `composer coverage-test` |
| JavaScript | `bash bin/test-js.sh` |
| CI/script helpers | `python3 -m unittest discover -s tests/scripts -p 'test_*.py'` |
| API | `composer api-test` |
| HTTP page tests | `composer http-page-test` |
| Behat UI tests | `composer ui-behat-test` |

Some browser and email-related tests require additional credentials. The workflows under `.github/workflows/` define those suite-specific additions; `.github/actions/setup-full-stack/action.yml` defines the common full-stack CI application environment.
