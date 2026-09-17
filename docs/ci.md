# Continuous Integration

This document describes the automated validation performed by GitHub Actions. It focuses on CI: what runs automatically when code changes, when each workflow is triggered, and what results are produced.

For instructions on running the test suites yourself, see [Testing](testing.md). For image publishing and production deployment, see [Deployment](deployment.md).

## Pull request validation

Pull requests targeting `develop` run several independent workflows. Keeping the checks separate makes it easier to identify whether a failure is in application behavior, browser behavior, dependencies, or security scanning.

API, UI/Page, UI/Behat, and ZAP jobs all exercise the same full Docker Compose application. They use the local composite action at `.github/actions/setup-full-stack/action.yml` to create the common CI `.env`, prepare test folders, disable the production Let's Encrypt certificate reference, launch the Docker Compose stack, and wait for the application to become available on port 90. Suite-specific dependencies and test commands remain in the individual workflows.

The shared stack uses fixed local-only CI credentials for MySQL and Mailpit. These values are not production credentials and do not require GitHub secrets. Tests that communicate with external services still configure their required secrets in their own workflow steps.

### Unit and integration tests

`.github/workflows/code-test.yml` runs both PHPUnit suites on PHP 8.4.

The unit job runs `composer unit-test` and publishes JUnit results plus HTML TestDox and coverage artifacts.

The integration job creates its test environment, starts the supporting SQL and Mailpit services through the Composer setup command, initializes the database, and runs `composer integration-test`. It also publishes JUnit results plus HTML and coverage artifacts.

After both jobs complete, their coverage artifacts are combined and sent to SonarCloud for analysis.

This workflow runs for both pull requests to `develop` and pushes to `develop`.

### API tests

`.github/workflows/api-test.yml` runs for pull requests to `develop`. It uses the shared full-stack CI action and runs `composer api-test` after its Composer dependencies are installed.

JUnit results are published as a GitHub test report and the HTML report is uploaded as a workflow artifact.

### UI tests

`.github/workflows/ui-test.yml` runs two browser-oriented jobs for pull requests to `develop`.

**Page Testing** uses the shared full-stack CI action, installs and starts ChromeDriver, and runs `composer ui-page-test`. JUnit, HTML, and custom report artifacts are retained by the workflow.

**Behat Testing** uses the same application/browser setup and runs `composer ui-behat-test`. The Behat report directory is uploaded as a workflow artifact.

These jobs additionally configure Gmail test credentials because portions of the browser-level behavior exercise email-related application flows. The application itself uses Mailpit in the shared CI stack.

## Security checks

### CodeQL

`.github/workflows/codeql-analysis.yml` runs GitHub CodeQL analysis for JavaScript on pull requests and pushes to `develop`, plus a weekly scheduled run.

### Dependency analysis

`.github/workflows/dependency-checks.yml` runs OWASP Dependency-Check against `composer.lock` on pull requests and weekly. It is configured to fail on vulnerabilities with CVSS 8 or higher and uploads SARIF and report artifacts.

### Container scanning

`.github/workflows/container-scan.yml` builds the PHP and SQL Docker images and scans each with Anchore. It runs on pull requests and weekly, fails for findings at the configured `high` severity cutoff, and uploads SARIF results to GitHub.

### ZAP scans

`.github/workflows/zap-scans.yml` runs the OWASP ZAP baseline scan for pull requests targeting `develop`. The deeper full scan runs weekly and can also be started manually; manual dispatch can select either scan. Both use the shared full-stack CI action and scan the application at `http://localhost:90/`.

Before scanning, the workflow creates an ephemeral admin user in the CI database specifically for ZAP. The checked-in Automation Framework plans under `.zap/` authenticate that user through `/api/login.php`, retain the PHP cookie session, and run the spider and scanner as the authenticated user. The account exists only in the disposable CI database.

Each ZAP job writes a severity summary and its Medium/High alert types directly to the GitHub Actions job summary. Detailed HTML, JSON, and Markdown reports remain available as separate workflow artifacts.

Findings currently do not fail the workflow while authenticated coverage is validated and application-specific findings are classified. Once the initial findings are understood, CI gating will use targeted ZAP rules so actionable Medium-or-higher findings block pull requests without globally suppressing accepted findings.

## Reports and artifacts

The functional workflows publish JUnit results directly into GitHub where configured. HTML, coverage, and custom reports are retained as workflow artifacts so additional detail is available after a run.

When diagnosing a CI failure, start with the failing job log and GitHub test report. ZAP's job summary provides its routine security overview; use the uploaded artifacts when detailed ZAP evidence or other TestDox, coverage, or browser-test output is needed.

## What is not CI

Building release images and deploying them to production are continuous-delivery responsibilities and are intentionally documented separately in [Deployment](deployment.md). Local execution of the test suites is documented in [Testing](testing.md).
