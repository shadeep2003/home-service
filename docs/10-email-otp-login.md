# Email OTP login

Credentials are validated without signing in. A six-digit email code is then required on every login, including accounts whose email was previously verified. Registration creates the account and redirects to login; it no longer grants a session automatically. Successful verification retains the intended destination, or redirects through `/dashboard` to the user's role dashboard.

## Local configuration

The project uses Laravel's bundled Symfony SMTP mailer; no new dependency, Node build, queue worker, public callback, or deployed domain is required. A localhost app can connect to your provider's remote SMTP server.

Add the following variables to your existing **untracked** `.env` (placeholders are in `.env.example`):

| Variable | Setting |
| --- | --- |
| `MAIL_MAILER` | `smtp` (required; log/array/failover delivery is rejected) |
| `MAIL_SCHEME` | `smtp` for STARTTLS, usually port 587; `smtps` for implicit TLS, usually port 465 |
| `MAIL_HOST` | Your provider's SMTP hostname |
| `MAIL_PORT` | Provider's port, normally 587 or 465 |
| `MAIL_USERNAME` | SMTP username supplied by the provider |
| `MAIL_PASSWORD` | SMTP password or app password; quote values containing spaces or `#` |
| `MAIL_FROM_ADDRESS` | An email/sender authorized by your provider |
| `MAIL_FROM_NAME` | Display name, e.g. `"Home Services"` |
| `MAIL_EHLO_DOMAIN` | `localhost` locally, or a hostname required by the provider |

TLS is required, including on localhost's outbound SMTP connection. Do not disable certificate validation. Use the provider's instructions for sender verification and app passwords. Ordinary account passwords may not work. The implementation requires authenticated SMTP; unauthenticated local mail catchers are not treated as real delivery.

Keep the existing `APP_KEY`, database settings, session settings, and `CACHE_STORE=file`. File cache persists throttles and supports locks; do not use the ephemeral array store outside tests. Do not regenerate an existing app key. `APP_DEBUG=false` is recommended when handling real credentials; never share debug pages or SMTP diagnostics containing secrets.

After editing `.env`:

```bash
php artisan config:clear
php artisan migrate
php artisan serve
```

Restart any existing server after changing configuration. Run migrations against your configured MySQL database; migration adds `users.email_verified_at` and `login_challenges`. It preserves account records, passwords, roles, and existing session protections. Existing authenticated sessions retain their current lifetime; all new logins require OTP.

## Manual end-to-end check

1. Open `http://localhost:8000/login`. Use an account with an inbox you control (new accounts must log in after registration).
2. Submit an incorrect password: the response is the same for an unknown email and no email is sent.
3. Submit correct credentials: the verification screen appears. Check the inbox (and spam). Visiting a protected dashboard or posting to a protected endpoint before verification must redirect to login, or return 401 for JSON requests.
4. Paste the six-digit code and submit. The app signs you in and redirects to the intended page or appropriate customer/provider/admin dashboard. Logout and login again: a new code is required even if `email_verified_at` is already set.
5. Try an incorrect code: remaining attempts decrease. Five wrong six-digit codes lock the challenge, including resend. Return to login to restart.
6. Wait five minutes: the old code expires. Resend becomes available after 60 seconds. The previous code must fail; only the newly emailed code succeeds. Attempts are not reset by resend.
7. Refresh the verification page: the challenge and deadlines survive without sending again. After 15 minutes, the pending login ends and requires credentials again. Return to login explicitly cancels the challenge.
8. Missing or rejected SMTP settings show a delivery error and never create an authenticated session. Actual resend delivery failures invalidate the previous code; a blocked rate-limited request retains an existing valid code. Restore settings, restart the server, and retry after cooldown.
9. Repeated requests show a 429 page with the retry time. Do not clear the cache to work around normal cooldowns.

The app cannot guarantee an inbox delivery just because an SMTP server accepts a message. Confirm actual receipt with the inbox you control. No code is printed to application logs, exposed in HTTP responses, or persisted as plaintext; do not add mail logging or message-body tracing.

## Security and implementation

- `random_int` generates six digits (including leading zeroes), stored only as a salted password hash. The code exists briefly in memory for synchronous email delivery; it is never queued or flashed as old input.
- A random browser-session binding is hashed in the database. Each UUID challenge references its user and a keyed fingerprint of the account email/password. Account suspension or email/password changes invalidate pending challenges.
- Code lifetime is five minutes; pending login lifetime is fifteen minutes. Successful verification removes the hash and marks the challenge consumed. Database row locks serialize verification/resend; session locks serialize same-browser POST requests.
- At most five incorrect six-digit guesses per challenge. Resend preserves the attempt count and cannot revive a locked challenge. A permitted resend generates a different code and invalidates the previous hash, including on actual delivery failure. A rate-limited request that does not attempt replacement preserves the current code.
- Login: six requests/minute per IP and normalized email. Verification: ten requests/minute per IP and pending challenge. Resend: three requests/minute per IP and challenge, plus a strict 60-second challenge cooldown. Delivery also permits one send/minute and ten/hour per user across browser sessions/IPs.
- Password validation uses Laravel's authentication provider and retains password rehashing. Authentication occurs only after consuming the code. Session ID and CSRF token rotate on successful login; existing HttpOnly, SameSite, role checks and account suspension middleware remain in place.
- `email_verified_at` records first successful verification independently of the per-login challenge. Changing an account email clears this timestamp.
- SMTP errors are caught without reporting their potentially sensitive exception body. Logs contain only predefined failure reasons and missing environment variable names; never exception objects/messages, recipients, code values, or credential values. No log transport fallback is configured. Test doubles exist only in automated tests.
- HTTP countdowns use server deadlines; client-side edits cannot change server enforcement. One labelled numeric text input supports complete-code paste, autofill, and normal keyboard navigation. It includes submit/loading, delivery, invalid-code, expiry, lockout and resend feedback.

Run the full suite:

```bash
php artisan test
```

Tests use in-memory SQLite and `Mail::fake()` (or explicit delivery failure mocks); they do **not** verify delivery to an inbox. Coverage includes success, roles, generic credential errors, hashed storage, expiry, reuse, session binding, account changes, resend invalidation, delivery failures, cooldowns, rate limits, pending refresh/cancellation, and protected HTML/JSON endpoints.

Old challenges can be removed with `php artisan auth:prune-login-challenges`. A daily scheduler entry is included; run Laravel's scheduler in deployed environments, or run that command manually locally. Only records whose pending lifetime ended more than a day ago are removed.

Implementation references: [Laravel SMTP configuration](https://github.com/laravel/laravel/blob/13.x/config/mail.php), [Laravel rate limiting](https://github.com/laravel/docs/blob/13.x/rate-limiting.md).

## Validation status

Full feature suite: **65 tests passed, 602 assertions**. PHP and JavaScript syntax checks, Blade compilation, and diff whitespace checks passed. Automated tests use test delivery only. Real Gmail SMTP connection, negotiated TLS, authentication, and acceptance of both a simple test email and the existing OTP Mailable have been verified. Actual inbox receipt remains a manual check. The diagnostic OTP challenge was cancelled after submission; start a fresh login for a usable code. The OTP migration was subsequently applied successfully to the local MySQL/MariaDB database outside the sandbox. Required deadline columns use DATETIME for compatibility with strict timestamp defaults; migration retry also handles an email verification column left by a partial DDL failure. Perform the manual inbox flow after configuring SMTP.


### Safe delivery diagnosis

The current project's `.env` must contain the mail setting names listed above. Settings in `.env.example`, another checkout, or another environment file do not configure this running app. After editing the correct file locally, clear configuration and restart the server. No credential values should be pasted into chat or diagnostics.

Safe log reasons include `smtp_configuration_missing` (with missing names only), `smtp_mailer_required`, `delivery_rate_limited`, `smtp_authentication_rejected`, `smtp_tls_failed`, `smtp_timeout`, `smtp_dns_failed`, and `smtp_connection_failed`, and `smtp_scheme_unsupported`. Other errors receive a generic fixed reason. The logs deliberately omit SMTP replies and message contents.

SMTP compatibility fix: legacy `MAIL_SCHEME=tls` and `ssl` labels are normalized in `config/mail.php` to Symfony transport schemes `smtp` and `smtps`. When omitted, port 465 selects `smtps`; other ports select `smtp`. TLS enforcement remains enabled. No credential or `.env` changes are needed for this compatibility fix.


### OTP state transition fix

Duplicate valid credential submissions in the same pending session retain an already delivered, unexpired challenge instead of cancelling it before hitting the send cooldown. Resend checks account send limits before replacing the current code. Successful login/resend clears stale validation errors. The salted code hash and expiry are saved under the existing transaction/row lock before mail is sent; a write failure therefore cannot send an unusable code. Transport failures still clear the replacement hash and prevent authentication. The transaction commits the replacement only after delivery returns successfully. SMTP configuration is unchanged.

Regression coverage includes active page state after delivery/resend, duplicate-login preservation, rate-limited resend preservation, hash persistence before mail, failed persistence before mail, stale-error clearing, expiry, single-use verification, and role dashboards.
