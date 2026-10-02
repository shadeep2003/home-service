# Test plan and execution record

Automated source: `tests/Feature/AuthenticationTest.php`. All runtime scenarios are **Not run** because dependencies cannot be downloaded here.

| ID | Requirement | Scenario / input | Precondition | Expected | Actual | Status |
|---|---|---|---|---|---|---|
| AUTH-01 | FR-01/NFR-01 | Valid customer registration | Empty users | Hashed password, customer account, authenticated redirect | Not executed | Not run |
| AUTH-02 | FR-01/NFR-01 | role=admin | Guest | Role error, zero accounts | Not executed | Not run |
| AUTH-03 | FR-01/FR-25 | Different password confirmation | Guest | Password error, guest retained | Not executed | Not run |
| AUTH-04 | FR-01/NFR-05 | Existing email | Existing account | Email error, no duplicate | Not executed | Not run |
| AUTH-05 | FR-01/FR-23 | Provider registration | Guest | Provider dashboard access | Not executed | Not run |
| AUTH-06 | FR-02/NFR-01 | Wrong password | Existing account | Generic credential error, guest | Not executed | Not run |
| AUTH-07 | FR-02 | Correct login then POST logout | Existing account | Authenticated then guest | Not executed | Not run |
| AUTH-08 | FR-23/NFR-01 | Cross-role URL and guest URL | Each role | Own 200, other 403, guest redirect | Not executed | Not run |
| AUTH-09 | NFR-01 | Seven login submissions | Fresh limiter | Seventh response 429 | Not executed | Not run |
| MAN-01 | NFR-02 | Missing CSRF token | Browser session | HTTP 419 | Not executed | Not run |
| MAN-02 | NFR-03 | Script-like user name | Valid account | Name displayed as escaped text | Not executed | Not run |
| MAN-03 | NFR-07/08 | 375/768/1440px, keyboard | Running app | Readable layout, usable controls/focus | Not executed | Not run |
| MAN-04 | NFR-05/14 | MySQL migration and account workflow | Configured MySQL | Schema and flow succeed | Not executed | Not run |

Laravel tests disable CSRF by default, so MAN-01 is separate. SQLite tests cannot prove MySQL schema portability: MAN-04 is required. Future modules add positive, negative, ownership, lifecycle, and concurrency cases.

## Environment limitation record

ENV-01: Composer create-project failed with curl error 6 resolving repo.packagist.org. Dependency installation, framework boot, and runtime verification are blocked. This is an environment limitation, not a demonstrated application defect. Retry installation in a network-enabled environment.

Defect records should include ID, requirement/test link, reproducible steps, expected/actual result, severity, fix, and retest evidence. Never record a planned test as passed.
