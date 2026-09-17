# CI and Testing

This document describes the automated checks currently defined in `.github/workflows` and the corresponding Composer test commands.

## Pull request checks

Pull requests targeting `develop` run several independent workflows. Keeping them separate makes it easier to see whether a failure is in application behavior, browser behavior, dependencies, or security scanning.

### Unit and integration tests

`.github/workflows/code-test.yml` runs both PHPUnit suites on PHP 8.4.

The unit suite runs:

```bash
composer unit-test
```

It produces JUnit, HTML TestDox, and coverage reports under `reports/`. GitHub publishes the test result and uploads the HTML/coverage output as workflow artifacts.

The integration suite creates a test environment, starts a temporary SQL container and Mailpit, initializes the database, and then runs:

```bash
composer integration-pre-test
composer integration-test
```

It likewise publishes JUnit results and uploads the HTML and coverage reports. After both suites complete, their coverage artifacts are combined and sent to SonarCloud for analysis.

`code-test.yml` runs for pull requests to `develop` and also for pushes to `develop`.

### API tests

`.github/workflows/api-test.yml` runs for pull requests to `develop`. It builds and launches the application with Docker Compose, waits for the HTTP endpoint to become available, and runs:

```bash
composer api-test
```

The suite uses `phpunit-api.xml`. Its JUnit results are published as a GitHub test report and its HTML report is uploaded as a workflow artifact.

### UI tests

`.github/workflows/ui-test.yml` runs two browser-oriented jobs for pull requests to `develop`.

**Page Testing** launches the Docker Compose application, starts ChromeDriver, and runs:

```bash
composer ui-page-test
```

This maps to the PHPUnit UI configuration and runs headlessly in CI. JUnit, HTML, and custom report artifacts are retained by the workflow.

**Behat Testing** uses the same basic application/browser setup and runs:

```bash
composer ui-behat-test
```

The Behat report directory is uploaded as a workflow artifact.

These UI jobs currently configure Gmail test credentials because portions of the browser-level behavior exercise email-related application flows.

## Security checks

Security checks are intentionally separate from the functional test suites.

### CodeQL

`.github/workflows/codeql-analysis.yml` runs GitHub CodeQL analysis for JavaScript on pull requests and pushes to `develop`, plus a weekly scheduled run.

### Dependency analysis

`.github/workflows/dependency-checks.yml` runs OWASP Dependency-Check against `composer.lock` on pull requests and weekly. The workflow is configured to fail on vulnerabilities with CVSS 8 or higher and uploads both SARIF and report artifacts.

### Container scanning

`.github/workflows/container-scan.yml` builds the PHP and SQL Docker images and scans each with Anchore. The workflow runs on pull requests and weekly, fails for findings at the configured `high` severity cutoff, and uploads SARIF results to GitHub.

### ZAP scans

`.github/workflows/zap_scans.yml` defines OWASP ZAP baseline and full web application scans for pull requests and weekly scheduled execution. The workflow launches a local Docker Compose application before scanning it.

## Build and deployment

`.github/workflows/build-and-deploy.yml` is separate from the pull request validation workflows. It runs when changes are pushed to `develop`.

The PHP and SQL jobs run on GitHub-hosted Ubuntu runners. They build multi-platform images for `linux/amd64` and `linux/arm64` and publish branch and commit-SHA tags to GHCR. The PHP build also regenerates the site map before creating the image.

Only after both image jobs succeed does the deployment job run. Deployment is sent to the repository-scoped `saperstone-production` self-hosted runner, which pulls the newly published images and applies the Compose configuration on production.

For runner installation and recovery details, see [Self-Hosted Production Runner](self-hosted-runner.md).

## Running tests locally

Install the PHP dependencies first:

```bash
composer install
```

The primary test commands defined in `composer.json` are:

| Suite | Command |
| --- | --- |
| Unit | `composer unit-test` |
| Integration setup | `composer integration-pre-test` |
| Integration | `composer integration-test` |
| Integration cleanup | `composer integration-post-test` |
| Coverage | `composer coverage-test` |
| API | `composer api-test` |
| UI page tests | `composer ui-page-test` |
| Behat UI tests | `composer ui-behat-test` |

Some suites require Docker, environment variables from `.env`, ChromeDriver, or supporting services. The GitHub workflow for a suite is the best reference for the complete CI environment used to execute it.

## Test reports

PHPUnit configurations write their results beneath `reports/`. Depending on the suite, CI publishes JUnit results directly into GitHub and retains HTML, coverage, or custom reports as workflow artifacts.

When diagnosing a CI failure, start with the failing job's log and GitHub test report. Use the uploaded artifacts when additional TestDox, coverage, or browser-test detail is needed.
