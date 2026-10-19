# Static Asset and Analysis Ownership

This document records how checked-in browser assets and SQL source are treated by automated analysis. The goal is to keep Sonar and coverage focused on code Saperstone Studios owns without hiding locally maintained forks or real application behavior.

## Policy

Classify checked-in assets by ownership rather than filename or age:

- **First-party:** authored for Saperstone Studios. Analyze and cover normally.
- **Pristine vendor:** copied third-party code with no local semantic changes. Exclude from first-party static analysis and line coverage where appropriate.
- **Vendor-derived fork:** third-party code that Saperstone Studios has changed semantically. Treat it as owned source for static analysis. Test project-specific behavior; do not add meaningless tests solely to cover inherited vendor internals.
- **Generated assets:** analyze the readable source, not generated/minified siblings.
- **Database source:** active SQL remains first-party deployment input even when a scanner does not support its SQL dialect.

Exclusions must stay narrow. Do not exclude a file simply because Sonar reports many findings.

## Checked-in JavaScript

| Asset | Ownership | Evidence / local status | Analysis policy |
| --- | --- | --- | --- |
| `public/js/jqBootstrapValidation.js` | Pristine vendor | jqBootstrapValidation v1.3.6. The only repository change found is formatting/whitespace; behavior is unchanged from the imported version. | Exclude from Sonar and first-party JS line coverage. |
| `public/js/jquery.form.min.js` | Pristine vendor | jQuery Form Plugin v4.3.0, checked in as the upstream minified dependency. | Exclude from Sonar and first-party JS line coverage. |
| `public/js/jquery.uploadfile.js` | Vendor-derived fork | Hayageek jQuery Upload File Plugin v4.0.10 lineage with long-standing Saperstone-specific upload placement/button behavior, later CSP-safe DOM changes, and an error callback change that exposes the underlying XHR to first-party handlers. | **Include in Sonar.** Exclude inherited plugin internals from aggregate line coverage; protect Saperstone-specific behavior through focused Jest/Behat tests. |
| `public/js/site-consent.js` | Vendor-derived fork | Based on Bootstrap GDPR Cookies v1.0 but contains substantial Saperstone-specific consent categories, UI behavior, and integration logic. | Analyze and cover as first-party source. |
| Other `public/js/*.js` | First-party unless documented otherwise | No additional copied-library headers were identified in the #261 audit. | Analyze and cover normally. |

### Upload plugin coverage exception

`jquery.uploadfile.js` is intentionally different from a pristine vendor exclusion.

We maintain semantic changes in the file, so hiding it from static analysis would hide code we own. However, most of the roughly 900-line plugin remains inherited vendor implementation. Requiring line coverage for all inherited internals would distort first-party coverage and encourage low-value tests.

The local behavior is protected at its integration boundaries, including:

- upload-button/container integration used by album/gallery/blog administration;
- CSP-safe generated upload DOM/classes;
- propagation of the upload XHR to first-party error handlers;
- browser-level file-input/upload workflows in Behat.

A future dependency modernization may remove the fork or move the Saperstone-specific behavior into a smaller adapter. Until then, the static-analysis/coverage distinction is intentional.

## Checked-in CSS

| Asset | Ownership | Evidence / local status | Analysis policy |
| --- | --- | --- | --- |
| `public/css/uploadfile.css` | Pristine vendor | Companion stylesheet for the Hayageek upload plugin. It is unchanged byte-for-byte from the repository's original 2017 import and matches the upstream plugin stylesheet lineage. | Exclude from Sonar. |
| `public/css/hover-effect.css` | Vendor-derived fork | Originally based on the credited hover-effect example; Saperstone added responsive behavior. | Analyze as first-party source. |
| `public/css/modern-business.css` | Vendor-derived fork | Based on Start Bootstrap Modern Business; Saperstone added site-specific carousel behavior. | Analyze as first-party source. |
| `public/css/saperstone-studios.css` | First-party | Main application stylesheet. | Analyze normally. |
| `public/css/mpdf.css` | First-party | Small application PDF-layout stylesheet; no external vendor provenance identified. | Analyze normally. |

## Generated production assets

The production image build creates minified siblings for first-party JavaScript and CSS. Those generated files are deployment artifacts, not independent source. Static analysis and coverage should target the readable source files.

`public/js/jquery.form.min.js` is different: it is a checked-in third-party dependency whose distributed form is already minified.

## MySQL source under `bin/sql`

The files under `bin/sql/*.sql` are **active first-party application/deployment inputs**. `bin/setup-database.sh` executes them against MySQL during application/database setup, including production container startup. They are not historical files that can be broadly excluded as dead source.

Sonar's SQL language assignment is the problem: the `.sql` suffix is associated with Oracle PL/SQL by default, while these files contain MySQL syntax such as `INSERT IGNORE`, backtick identifiers, and MySQL string escaping. The #261 audit found that all current Sonar findings in `bin/sql` came from PL/SQL rules, including false/misleading findings caused by that dialect mismatch.

The project therefore removes `sql` from `sonar.plsql.file.suffixes` rather than pretending the MySQL files are Oracle PL/SQL or T-SQL.

This is **not an exclusion of the SQL files from application ownership or testing**. Database initialization/data behavior remains protected through integration/API/Page tests and script/database setup tests where appropriate. If a reliable MySQL-aware static analyzer is adopted later, these files should be evaluated with that tool.

## Externally hosted browser dependencies

The application also uses version-pinned browser dependencies loaded from approved CDNs, including legacy jQuery/Bootstrap-era libraries. They are outside repository source analysis and are constrained with CSP and Subresource Integrity where currently configured.

They are not covered by Composer SCA. Version/support/security lifecycle for these dependencies should be reviewed separately rather than folded into Sonar source ownership or upgraded opportunistically during unrelated cleanup.

## Updating this policy

When adding or changing a third-party asset:

1. Record its upstream project and version when known.
2. Prefer package-managed or reproducibly fetched dependencies when practical.
3. Keep pristine vendor code separate from first-party adapters/customizations.
4. If vendor code is modified, reclassify it as a maintained fork for static analysis.
5. Add targeted tests for local behavior.
6. Update Sonar/coverage configuration and this document together.
7. Do not weaken scanners merely to improve a dashboard metric.
