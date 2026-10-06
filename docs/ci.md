# Continuous Integration

This document describes the automated validation performed by GitHub Actions. It focuses on CI: what runs automatically when code changes, when each workflow is triggered, and what results are produced.

For instructions on running the test suites yourself, see [Testing](testing.md). For image publishing and production deployment, see [Deployment](deployment.md).

## CI philosophy

A change is not complete simply because it works locally. The CI/testing pipeline is a core engineering asset and should continually become more comprehensive.

When a defect, security issue, or regression escapes the pipeline, determine what automated test or pipeline check could have caught it and add that protection when practical. Prefer repeatable automated verification over recurring manual checks.

Investigate CI failures rather than assuming they are unrelated or flaky. Fix stale tests or infrastructure rather than changing production behavior merely to make CI green. Coverage, SonarCloud, CodeQL, dependency/SCA analysis, container scanning, and OWASP ZAP are engineering feedback and acceptance checks, not reports to ignore after implementation.

## Pull request validation

Pull requests targeting `develop` run several independent workflows. Keeping the checks separate makes it easier to identify whether a failure is in application behavior, browser behavior, dependencies, or security scanning.

API, HTTP Page, UI/Behat, and ZAP jobs all exercise the same full Docker Compose application. They use the local composite action at `.github/actions/setup-full-stack/action.yml` to create the common CI `.env`, prepare test folders, disable the production Let's Encrypt certificate reference, launch the Docker Compose stack, and wait for the application to become available on port 90. Suite-specific dependencies and test commands remain in the individual workflows.

The shared stack uses fixed local-only CI credentials for MySQL and Mailpit. These values are not production credentials and do not require GitHub secrets. Tests that communicate with external services still configure their required secrets in their own workflow steps.

### Validation policy

The merge gate should require checks that are deterministic, protect distinct behavior, and are important enough to block a production-bound merge. Scheduled scans provide additional defense in depth but do not retroactively gate an already merged commit.

Representative runtimes below are from the successful October 5, 2026 PR run and are intended as rough operational guidance, not performance budgets.

| Check | PR | Push to `develop` | Weekly | Representative runtime | Merge policy |
| --- | :---: | :---: | :---: | ---: | --- |
| Unit Testing | Yes | Yes | No | ~20s | Required |
| Integration Testing | Yes | Yes | No | ~2m 19s | Required |
| JavaScript Unit Testing | Yes | Yes | No | ~7s | Required |
| Script Testing | Yes | Yes | No | ~5s | Required |
| SonarCloud | Yes | Yes | No | ~1m 12s after coverage jobs | Required quality gate |
| API Testing | Yes | No | No | ~7m 56s | Required |
| HTTP Page Testing | Yes | No | No | ~2m 56s | Required |
| Behat Testing | Yes | No | No | ~25m 49s | Required |
| CodeQL JavaScript analysis | Yes | Yes | Yes | ~1m 4s | Required |
| Composer SCA | Yes | No | Yes | ~1m | Required for Critical/High advisories |
| PHP Container Scan | Yes | No | Yes | ~3m 16s | Required for any fixable finding |
| SQL Container Scan | Yes | No | Yes | ~2m 46s | Required for any fixable finding |
| ZAP baseline/full | Baseline | No | Full | ~3m 48s baseline | Required for reportable Low+ findings |

The production-image workflow runs only after a push reaches `develop`. Its PHP and SQL builds are a deployment gate rather than a pull-request merge gate; production polling should deploy only successfully built images.

The repository ruleset mirrors this policy. Behat is required as a browser-level merge gate. Composer dependency severity is enforced by the `SCA` GitHub Actions job; the separate SARIF upload remains available in GitHub Security under the `composer-audit` tool identity but is not a required status check. SonarQube Cloud's `SonarCloud Code Analysis` status is the required Sonar quality gate. CodeQL's workflow and code-scanning statuses remain intentionally required because CodeQL does not implement a separate severity gate in repository scripts.

External required checks such as GitGuardian remain part of the repository ruleset even though they are not defined in this repository's workflow YAML.

### Unit and integration tests

`.github/workflows/code-test.yml` runs both PHPUnit suites on PHP 8.4.

The unit job runs `composer unit-test` and publishes JUnit results plus HTML TestDox and coverage artifacts.

The integration job creates its test environment, starts the supporting SQL and Mailpit services through the Composer setup command, initializes the database, and runs `composer integration-test`. It also publishes JUnit results plus HTML and coverage artifacts.

The same workflow also runs lightweight Python regression tests for CI/reporting helper scripts under `tests/scripts/`. The script-test job writes JUnit XML, adds the same pass/fail/skip summary used by the other test jobs to the GitHub Actions summary, and uploads the JUnit report as an artifact. SonarCloud waits for the unit, integration, JavaScript, and script-test jobs before analysis.

After the PHP and JavaScript jobs complete, their coverage artifacts are combined and sent to SonarCloud for analysis.

This workflow runs for both pull requests to `develop` and pushes to `develop`.

### API tests

`.github/workflows/api-test.yml` runs for pull requests to `develop`. It uses the shared full-stack CI action and runs `composer api-test` after its Composer dependencies are installed.

JUnit results are published as a GitHub test report and the HTML report is uploaded as a workflow artifact.

### UI tests

`.github/workflows/ui-test.yml` runs two functional jobs for pull requests to `develop`.

**HTTP Page Testing** uses the shared full-stack CI action and runs `composer http-page-test`. It validates HTTP responses, redirects, authorization behavior, and server-rendered HTML without Chrome or ChromeDriver. JUnit and HTML report artifacts are retained by the workflow.

**Behat Testing** remains browser-based. It installs and starts ChromeDriver and runs `composer ui-behat-test` against the same full-stack application. The Behat report directory is uploaded as a workflow artifact.

Browser/JavaScript workflows belong in Behat; page behavior that can be verified from HTTP responses and rendered HTML belongs in the HTTP Page suite. This separation keeps page-level coverage fast while reserving browser automation for behavior that actually requires it.

## Security checks

### CodeQL

`.github/workflows/codeql-analysis.yml` runs GitHub CodeQL analysis for JavaScript on pull requests and pushes to `develop`, plus a weekly scheduled run.

### Dependency analysis

`.github/workflows/software-composition-analysis.yml` runs Composer's native security audit against the locked PHP dependency graph on pull requests and weekly. The job summary reports the number of locked dependencies audited, advisory counts by severity, and abandoned packages. Critical or High advisories fail the SCA job; Medium/Low advisories and abandoned packages remain visible for review. The audit is converted to SARIF for GitHub Security visibility and the JSON/SARIF reports are retained as workflow artifacts.

### Container scanning

`.github/workflows/container-scan.yml` builds the PHP and SQL Docker images and scans each with Anchore/Grype on pull requests and weekly. Grype itself is configured not to hide findings. Each job keeps a raw complete vulnerability report, a raw report containing findings for which a fix is available, and a filtered SARIF report for GitHub Security. The GitHub Actions summary shows **Detected**, **Fixable**, **Reviewed exceptions**, and **Actionable fixable** counts by severity. **Any fixable vulnerability not covered by a documented reviewed exception** fails the merge gate. Reviewed false positives/non-applicable findings are listed in `.github/container-scan-allowlist.json` with an explicit reason; they remain visible in the raw artifacts and job summary rather than disappearing from the scan. Findings without a current fix remain visible for risk review and future remediation.

### ZAP scans

`.github/workflows/zap-scans.yml` runs the OWASP ZAP baseline scan for pull requests targeting `develop`. The deeper full scan runs weekly and can also be started manually; manual dispatch can select either scan. Both use the shared full-stack CI action and scan the application at `http://localhost:90/`.

Before scanning, the workflow creates an ephemeral admin user in the CI database specifically for ZAP. The checked-in Automation Framework plans under `.zap/` authenticate that user through `/api/login.php`, retain the PHP cookie session, and run the spider and scanner as the authenticated user. The account exists only in the disposable CI database.

Each ZAP job writes a severity summary to the GitHub Actions job summary. Detailed HTML, JSON, and Markdown reports remain available as separate workflow artifacts.

ZAP is a security gate. Any reportable **Low, Medium, or High** alert causes the job to fail; Informational alerts do not. This applies to the pull-request baseline scan as well as weekly/manual scans that use the same summary gate.

When ZAP fails, review the alert in the job summary and uploaded reports, reproduce the finding where practical, and fix the underlying issue. Do not suppress or downgrade a finding merely to make CI pass. If a finding is confirmed to be a false positive or an intentionally accepted risk, change its ZAP treatment explicitly in the appropriate checked-in Automation Framework plan under `.zap/`, document the reason in the pull request, and keep the exception as narrow as possible. A clean scan should contain no unsuppressed Low-or-higher findings.

## Reports and artifacts

The functional workflows publish concise GitHub Actions summaries and retain detailed artifacts. PHPUnit Unit and Integration summaries include Clover statement and method coverage; JavaScript summaries include line, branch, and function coverage. SCA, container scanning, and ZAP each provide severity-oriented security summaries while retaining machine-readable reports for deeper investigation.

When diagnosing a CI failure, start with the failing job log and GitHub test report. ZAP's job summary provides its routine security overview; use the uploaded artifacts when detailed ZAP evidence or other TestDox, coverage, or browser-test output is needed.

## What is not CI

Building release images and deploying them to production are continuous-delivery responsibilities and are intentionally documented separately in [Deployment](deployment.md). Local execution of the test suites is documented in [Testing](testing.md).
