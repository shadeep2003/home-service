# Test plan and execution record

Automated source: `tests/Feature/AuthenticationTest.php`. On 2026-10-02, `composer test` passed all 12 tests (106 assertions) on PHP 8.5 using in-memory SQLite. Public page tests are in `tests/Feature/PublicPagesTest.php`. Manual scenarios remain pending.

| ID | Requirement | Scenario / input | Precondition | Expected | Actual | Status |
|---|---|---|---|---|---|---|
| AUTH-01 | FR-01/NFR-01 | Valid customer registration | Empty users | Hashed password, customer account, authenticated redirect | Expected assertions passed | Passed |
| AUTH-02 | FR-01/NFR-01 | role=admin | Guest | Role error, zero accounts | Expected assertions passed | Passed |
| AUTH-03 | FR-01/FR-25 | Different password confirmation | Guest | Password error, guest retained | Expected assertions passed | Passed |
| AUTH-04 | FR-01/NFR-05 | Existing email | Existing account | Email error, no duplicate | Expected assertions passed | Passed |
| AUTH-05 | FR-01/FR-23 | Provider registration | Guest | Provider dashboard access | Expected assertions passed | Passed |
| AUTH-06 | FR-02/NFR-01 | Wrong password | Existing account | Generic credential error, guest | Expected assertions passed | Passed |
| AUTH-07 | FR-02 | Correct login then POST logout | Existing account | Authenticated then guest | Expected assertions passed | Passed |
| AUTH-08 | FR-23/NFR-01 | Cross-role URL and guest URL | Each role | Own 200, other 403, guest redirect | Expected assertions passed | Passed |
| AUTH-09 | NFR-01 | Seven login submissions | Fresh limiter | Seventh response 429 | Expected assertions passed | Passed |
| MAN-01 | NFR-02 | Missing CSRF token | Browser session | HTTP 419 | Not executed | Not run |
| MAN-02 | NFR-03 | Script-like user name | Valid account | Name displayed as escaped text | Not executed | Not run |
| MAN-03 | NFR-07/08 | 375/768/1440px, keyboard | Running app | Readable layout, usable controls/focus | Not executed | Not run |
| MAN-04 | NFR-05/14 | MySQL migration and account workflow | Configured MySQL | Schema and flow succeed | Not executed | Not run |

Laravel tests disable CSRF by default, so MAN-01 is separate. SQLite tests cannot prove MySQL schema portability: MAN-04 is required. Future modules add positive, negative, ownership, lifecycle, and concurrency cases.

## Environment limitation record

ENV-01: Composer create-project failed with curl error 6 resolving repo.packagist.org. Resolved during this review with network-enabled installation. Framework boot and automated tests now pass. Missing Mockery caused nine authentication-test errors; adding Mockery and Collision fixed the tests and restored `php artisan test`.

ENV-02: Local MySQL at `127.0.0.1:3306` returns connection refused. XAMPP is installed at `/opt/lampp` with PHP 8.2.12. Start its database and use the separate PHP 8.3+ runtime as described in `08-xampp-setup.md`. MySQL migrations have not been run in this review.

Defect records should include ID, requirement/test link, reproducible steps, expected/actual result, severity, fix, and retest evidence. Never record a planned test as passed.

## Provider foundation verification (2026-10-02)

`php artisan test`: 24 tests, 183 assertions passed against isolated in-memory SQLite. Authentication tests remain; provider success input now supplies required professional fields/category. Public-page tests now use migrations and seeded real categories instead of assuming all GETs are database independent.

`ServiceProviderFoundationTest` covers required provider data, invalid/inactive/duplicate category IDs, multi-category registration, customer payload isolation, active category filtering, matching-provider discovery as a customer, empty states, profile creation/update and category replacement, invalid edit preservation, role authorization, Admin create/edit/deactivation and unique slug validation, and repeatable seeding that preserves Admin edits.

MySQL application of the new migration, CSRF browser submission, responsive layouts and keyboard behavior still need manual verification. Earlier connection-refused notes describe historical checks, not the user's currently working connection.
