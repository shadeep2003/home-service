# One-time email verification

HomeServices uses `users.email_verified_at` as the single verification status. A null value requires verification; an existing timestamp permits password-only login. This is email ownership verification, not two-factor authentication on every login.

## Registration and login

Registration creates an unverified account and immediately starts the existing session-bound email challenge. A branded email contains a secure six-digit code. The user remains a guest until the code is checked successfully. Verification consumes the challenge once, sets `email_verified_at`, rotates the session ID/CSRF token, and redirects through `/dashboard` to the intended page or the user's customer/provider/admin dashboard.

Subsequent valid password logins for verified accounts authenticate directly, without generating a challenge or sending mail. This also works if SMTP is temporarily unavailable. Unverified existing accounts must supply valid credentials before receiving a code. A suitable current pending code is reused to prevent duplicate submissions from discarding an emailed code.

Changing an email through the customer/admin profile or provider profile clears `email_verified_at`. The next request using that unverified authenticated session signs the user out and requires a new password login followed by verification of the new email. Protected JSON requests receive 403. Saving a profile without changing the email preserves verification.

Public registration cannot set its own verified status or create an admin account. Sending mail never marks an account verified.

## Existing accounts and database

No schema migration or data backfill is required: `email_verified_at` and `login_challenges` already exist. Previously verified accounts keep their timestamps. Existing null values remain unverified and complete verification at their next login. No production or development accounts are silently verified. Test factories default to verified fixtures for unrelated authenticated feature tests; `User::factory()->unverified()` explicitly creates an unverified test account. Real registration does not use this factory default. Demo seeders do not use the factory or mark accounts verified.

If setting up a fresh checkout, run all existing migrations. Do not use `migrate:fresh` against a database with accounts to preserve.

## SMTP setup

SMTP and `.env` are unchanged by this refactor. Laravel's bundled Symfony mailer sends synchronously, without a queue, public callback, or deployed domain. Localhost can connect to the provider's remote SMTP server.

Required mail setting names in the existing untracked `.env`:

| Variable | Purpose |
| --- | --- |
| `MAIL_MAILER` | `smtp`; log/array/failover delivery is rejected |
| `MAIL_HOST` | Provider SMTP hostname |
| `MAIL_PORT` | Typically 587 for STARTTLS or 465 for implicit TLS |
| `MAIL_SCHEME` | `smtp` or `smtps`; existing legacy `tls`/`ssl` aliases remain supported |
| `MAIL_USERNAME` | Provider SMTP username |
| `MAIL_PASSWORD` | Provider SMTP/app password; never paste into chat or logs |
| `MAIL_FROM_ADDRESS` | Provider-authorized sender |
| `MAIL_FROM_NAME` | Display name |
| `MAIL_EHLO_DOMAIN` | `localhost` locally, or a provider-required hostname |

Keep the existing application key, database credentials, and session protections. Keep persistent, lock-capable cache outside tests (`CACHE_STORE=file` locally). TLS remains required; certificate validation is not disabled. Configuration examples are in `.env.example`; never commit real credentials.

After locally changing configuration, clear configuration and restart the server. No credential changes are needed for one-time verification.

## Security preserved

- Cryptographically secure six-digit generation and salted hash storage; no plaintext code in database, logs, old input, or queued jobs.
- Five-minute code expiry and fifteen-minute pending attempt expiry.
- Random session binding plus user/email/password fingerprint. Suspension, email changes, or password changes invalidate a pending challenge.
- At most five incorrect code guesses per challenge; resend does not reset attempts. Consumed codes cannot be replayed.
- Sixty-second resend cooldown, per-IP/account/challenge request limits, and per-account send limits across sessions.
- Row locks and session POST locks serialize mutation. Hash/expiry are saved under the transaction before mail is sent. SMTP failure clears the replacement; success commits an active challenge.
- A blocked rate-limited resend preserves an already delivered code; a permitted replacement invalidates the old code. Actual failed delivery cannot grant a session.
- Password checks, password rehashing, HttpOnly/SameSite cookies, session/CSRF rotation, account suspension and role checks remain in place.
- Server checks enforce verified status even for older authenticated sessions. Public responses do not reveal whether login email addresses exist.
- Logs contain predefined error categories and missing variable names only, never SMTP exception bodies, code values, account emails, or secrets.

Challenge route/service/model class names are retained to avoid needless changes: `/login/verify`, `/login/verify/resend`, `/login/verify/cancel`, `LoginVerification`, `LoginChallenge`, and `LoginOtp`. The UI and mail wording now describe email verification.

## Manual test

1. Start Laravel and register a new customer with an inbox you control. Registration immediately opens `/login/verify` and sends **Verify your HomeServices email address**. The account remains unverified and cannot access a dashboard before code verification.
2. Enter an incorrect code and confirm it fails without verifying the account. Enter the newest correct code within five minutes and confirm the correct dashboard opens.
3. Log out. Log in again with the same email/password: the dashboard opens directly, with no new code, email, or verification screen.
4. Repeat registration/verification with a provider. For admin, use an existing admin account; there is no public admin signup. Existing unverified accounts verify on their first valid login.
5. For another unverified account, wait five minutes and confirm expiry. Resend after the sixty-second cooldown; old code fails, latest code succeeds. Refreshing the screen does not send another email.
6. Change the email of a verified account in its profile. On the next protected request it is signed out. Log in using the new email/password and verify the code sent to the new inbox. Future logins again use only credentials.
7. Confirm an unchanged email/profile update preserves verification.

Mail acceptance and inbox delivery are different. Gmail SMTP was verified in earlier diagnostics; this refactor uses automated mail fakes to avoid sending unsolicited messages or exposing live codes. Manually confirm the new wording/subject in your inbox.

## Automated checks

```bash
php artisan test
```

Validation: **72 tests passed with 704 assertions**, plus PHP syntax, Blade compilation, and whitespace checks. Tests use isolated in-memory SQLite and mail fakes. Coverage includes registration sending without verification, successful single-use verification, incorrect/expired codes, resend invalidation, rate limits, binding, storage and delivery failure, protected HTML/JSON routes, legacy unverified sessions, password-only subsequent login, customer/provider/admin redirects, and email-change reset. Existing SMTP scheme tests remain unchanged.

Expired challenges can be pruned with `php artisan auth:prune-login-challenges`; the existing daily schedule remains available. Only challenges whose pending lifetime ended more than one day ago are removed.

## Files changed for the one-time verification refactor

Application:
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/LoginVerificationController.php`
- `app/Http/Middleware/EnsureEmailVerified.php` (new)
- `bootstrap/app.php`

Mail and UI:
- `app/Mail/LoginOtp.php`
- `resources/views/auth/verify-login.blade.php`
- `resources/views/emails/login-otp.blade.php`
- `resources/views/emails/login-otp-text.blade.php`

Tests and fixtures:
- `database/factories/UserFactory.php`
- `tests/Feature/AuthenticationTest.php`
- `tests/Feature/LoginOtpTest.php`
- `tests/Feature/AccountProfileTest.php`
- `tests/Feature/ServiceProviderFoundationTest.php`
- `tests/Feature/EmailVerificationTest.php` (new)

Documentation:
- `README.md`
- `docs/10-email-otp-login.md`

The working OTP service, challenge model, schema/migrations, SMTP configuration, and `.env` were not modified by this refactor.
