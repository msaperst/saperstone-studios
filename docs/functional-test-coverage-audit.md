# Functional Test Coverage Audit

This audit follows the Behat migration completed before issue #240. Its purpose is to map important current site behavior to the automated layer that best protects it, identify meaningful gaps, and avoid duplicating behavior that is already well covered at a lower layer.

## Test layers

- **Unit/integration:** PHP business logic, persistence, sessions/cookies, files, and helpers.
- **API:** endpoint contracts, methods, authorization, validation, security behavior, and side effects.
- **Page/HTTP:** server-rendered pages, access rules, redirects, navigation, markup, and protected-image behavior that does not require JavaScript.
- **Jest:** JavaScript/DOM behavior, AJAX success/failure handling, galleries, albums, blog administration, contracts, users, cookies, and state transitions.
- **Behat:** important multi-step browser workflows where the integration of page markup, JavaScript, APIs, persistence, and navigation is the behavior being protected.

## Coverage map

| Functional area | Unit / integration | API | Page / HTTP | Jest | Behat | Audit result |
| --- | --- | --- | --- | --- | --- | --- |
| Authentication / logout / password reset / remember me | Yes | Yes | Partial | Yes | Yes | Strong coverage |
| Registration / profile / password changes | Yes | Yes | Yes | Yes | Yes | Strong coverage |
| Album discovery / access / browsing | Yes | Yes | Yes | Yes | Yes | Strong coverage |
| Album favorites / submissions / downloads | Yes | Yes | Partial | Yes | Yes | Strong coverage |
| Album administration / uploader workflows | Yes | Yes | Yes | Yes | Yes | Strong coverage |
| Album access / download / share permissions | Yes | Yes | Partial | Yes | Yes | Strong coverage |
| Protected album image rendering | Yes | Yes | Yes | Yes | Yes | Strong coverage; preserve protected src/background behavior |
| Public galleries / reviews | Yes | Yes | Yes | Yes | Yes for galleries | Appropriate coverage; static/review rendering does not need duplicate browser tests |
| Contact form | Yes | Yes | Yes | Limited | Yes | Strong coverage including validation and timing rejection |
| Public blog / search / categories / comments | Yes | Yes | Yes | Yes | Yes | Strong coverage |
| Blog administration | Yes | Yes | Yes | Yes | **No** | **Gap: add focused browser workflow coverage** |
| Public contract signing | Yes | Yes | Yes | Yes | Yes | Strong coverage |
| Contract administration | Yes | Yes | Yes | Yes | **No** | **Gap: add focused browser workflow coverage** |
| User administration | Yes | Yes | Yes | Yes | **No** | **Gap: add focused browser workflow coverage** |
| Navigation / announcements / cookie preferences / FAQ | Yes where applicable | Yes where applicable | Yes | Yes | Yes | Strong coverage |
| Retouch interaction | N/A | N/A | Yes | Yes | Yes | Appropriate coverage |
| Static/service informational pages | N/A | N/A | Yes | N/A | N/A | HTTP coverage is sufficient; browser duplication is not useful |

## Meaningful gaps

### 1. User administration

The admin users page is protected by HTTP tests, its endpoints have API coverage, and `user.js` has Jest coverage. There is no browser test proving those pieces work together.

Add focused Behat coverage for the highest-value workflows:

- admin can load and use the user-management table;
- admin can create a user through the UI and the user is persisted;
- admin can edit an existing user through the UI and the change is persisted;
- role/active-state behavior should be included where it materially changes permissions;
- avoid duplicating field-by-field API validation already covered below the browser layer.

### 2. Contract administration

Public signing is covered end-to-end, but creating/editing a contract as an administrator is only protected in separate Page, Jest, API, and integration tests.

Add focused Behat coverage for:

- admin can create a contract through the UI;
- the created contract appears in the management table and is persisted;
- admin can edit an existing contract and the update is persisted;
- browser coverage should focus on the integration of the dynamic contract form and save flow, not repeat every API validation case.

### 3. Blog administration

Public blog behavior is covered end-to-end and admin pages are covered by HTTP tests. Blog editing JavaScript and APIs have extensive lower-layer coverage, but no browser workflow proves the complete admin authoring flow.

Add focused Behat coverage for:

- admin can create a draft post through the full editor;
- admin can edit an existing post and preserve/update its content;
- publish/schedule behavior should be covered where the browser transition is materially different from the already-tested API behavior;
- quick-edit behavior only needs Behat coverage if it exercises a distinct user workflow not sufficiently protected by Jest/API tests.

## Intentional non-gaps

The following should not receive Behat tests solely to increase test counts:

- static informational/service pages already covered by Page/HTTP tests;
- individual API validation and HTTP-method cases already covered by API tests;
- isolated JavaScript error callbacks and DOM transformations already covered by Jest;
- database/helper behavior already covered by unit/integration tests;
- every visual variant of galleries, reviews, or service pages when the same shared rendering contract is already exercised.

## Implementation order

1. User administration — relatively contained CRUD/permission workflow and currently no browser coverage.
2. Contract administration — important multi-step dynamic form workflow.
3. Blog administration — important but broader editor workflow; add only scenarios that prove integration not already established by Jest/API tests.
4. Re-run the complete Behat suite after each feature area, then the broader relevant CI suites.
5. Review coverage and static-analysis results for newly added test/support code and address legitimate findings.

## Completion criteria

Issue #240 is complete when the three browser-level gaps above have focused, deterministic coverage; the full Behat suite remains green; relevant API/Page/Jest/PHP suites remain green; and any gap intentionally left without browser automation is documented here with its rationale.
