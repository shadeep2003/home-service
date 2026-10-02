# Authentication increment: tutor guide

## Feature

Register as customer/provider, login/logout, and enforce role workspace access. We need this foundation so later booking actions can identify the actor.

## Exact files and responsibilities

All files are new because the workspace was empty.

- `composer.json`: framework and test dependencies, PSR-4 autoloading.
- `artisan`, `public/index.php`, `bootstrap/app.php`, `bootstrap/providers.php`: console/web entry points and middleware registration.
- `config/*.php`, `.env.example`: application, MySQL, sessions, cache, authentication, logging settings.
- `routes/web.php`: public/auth routes and protected role routes.
- `app/Enums/Role.php`: fixed customer/provider/admin values.
- `app/Models/User.php`: authenticatable Eloquent model, hidden secrets, password hashing cast.
- `app/Http/Requests/RegisterRequest.php`: input validation and normalized email.
- `app/Http/Requests/LoginRequest.php`: credential shape validation.
- `app/Http/Controllers/AuthController.php`: account persistence, authentication, session lifecycle.
- `app/Http/Middleware/EnsureRole.php`: reject mismatched roles with HTTP 403.
- `database/migrations/2026_10_02_000001_create_users_table.php`: versioned account schema.
- `resources/views/layouts/app.blade.php`: shared document structure using `@yield`.
- `resources/views/components/{navbar,footer,input}.blade.php`: reusable interface parts.
- `resources/views/auth/{register,login}.blade.php`: forms using `@csrf` and field errors.
- `resources/views/dashboard/{customer,provider,admin}.blade.php`: distinct role shells, not completed dashboards.
- `public/css/app.css`: shared design tokens and responsive rules.
- `tests/Feature/AuthenticationTest.php`: authentication/security integration test source.

## Database and relationships

Only users is used. There are no implemented cross-table relationships in this increment. The enum restricts role values; the unique email index handles duplicate records. Future profile and booking relationships are in the ER design.

## How it works

Browser POST /register → web/guest/throttle middleware → RegisterRequest → AuthController → User → users table → login and session regeneration → dashboard redirect → role-specific protected route → Blade → browser.

`validated()` returns only fields that passed rules. `$fillable` permits mass-assignment of name/email/password, not role. The controller sets the role explicitly from validated customer/provider input. `casts()` converts role strings to enums and hashes passwords. `Auth::attempt()` checks a stored hash through Laravel; never compare plaintext passwords manually.

`@extends` selects a layout; `@section` supplies its content; `@yield` inserts it. `<x-input>` reuses a component. `{{ }}` escapes output, including user names. `@error` displays validation feedback. Do not replace user output with raw `{!! !!}`.

## Security

POST logout avoids changing session state with a link/GET. Login regenerates session ID to resist fixation; logout invalidates session and regenerates CSRF token. Public role input rejects admin. Dashboard role middleware checks the server-side account. Authentication submissions allow six requests per minute per route/IP; this simple limit may affect users sharing an IP and can be refined later. Invalid credentials receive a generic error.

Laravel CSRF protection remains enabled. Form Requests enforce rules even if browser validation is bypassed. Eloquent avoids string-built SQL. Passwords and remember tokens are hidden from serialization. Password reset, verification, suspension, and admin provisioning are planned, not provided.

## How to test

Use README setup, then `php artisan test`. Manually register two roles, verify their redirect, attempt the other role's URL, log out, and try protected URLs again. Inspect the database to confirm passwords are hashes. Submit a forged admin role, mismatched password confirmation, and duplicate email. Remove the hidden `_token` using browser tools and submit: expect CSRF rejection. Check forms at mobile/tablet/desktop sizes and by keyboard.

## Common errors

- Missing vendor/autoload.php: `composer install` has not succeeded.
- No application encryption key: run `php artisan key:generate` after copying `.env`.
- Database connection error: create database and correct `.env`, then clear cached configuration if needed.
- Table users missing: run migrations.
- HTTP 419: refresh an expired form and check session persistence; never disable CSRF.
- HTTP 403: the signed-in account has the wrong role.
- HTTP 429: too many authentication submissions; wait for the limit window.

## Viva questions

1. Why hash passwords? A database leak must not expose plaintext credentials; hashing is one-way verification.
2. Authentication vs authorization? Identity vs permission to perform an action.
3. Why validate role if middleware exists? Registration must prevent privilege creation; middleware governs later access.
4. Why an enum? Central allowed values and less typo-prone comparisons.
5. Why regenerate sessions? Avoid retaining a potentially known pre-login session ID.
6. Why both database uniqueness and validation? Friendly errors plus protection against concurrent inserts.
7. Why policies later? A role alone does not establish ownership of a particular booking.
8. What do tests prove today? Nothing at runtime until installed and executed; currently they specify expected behavior.
