# Home Services Booking Platform

University Software Engineering mini-project using Laravel 13, PHP 8.3+, MySQL, and Blade. No Node build step is needed for this increment.

## Current increment

Implemented: public home/services/about/contact pages, customer/provider registration, login/logout, hashed passwords, role middleware, protected role dashboard shells, reusable Blade components, and authentication/public-page feature tests. Public listings and feedback are disclosed demonstrations.

Not implemented: provider profiles, discovery, bookings, messages, reviews, complaints, administrative management, password reset, email verification, and deployment automation. Dashboard shells intentionally show no invented statistics.

**Verification (2026-10-02):** PHP 8.5; Composer manifest valid; `composer test` passes 12 tests with 106 assertions; Blade templates compile. Missing Mockery and Collision development dependencies have been added and locked. MySQL connection currently returns connection refused on `127.0.0.1:3306`; XAMPP/database migration and manual browser checks remain pending.

## Local setup

Run these commands from the project directory on a machine with internet access, PHP 8.3+ (including PDO MySQL), Composer, and MySQL:

```bash
composer install
# Only for a fresh checkout without an existing .env:
cp .env.example .env
php artisan key:generate
```

Create a MySQL database named `home_services`. Edit `.env` with your database credentials. Never commit `.env`.

```bash
php artisan migrate
php artisan serve
```

Open http://localhost:8000. Register each role using a different email. Test dashboard access by entering another role's URL directly.

```bash
php artisan test
```

Automated feature tests use isolated in-memory SQLite, not your MySQL database. Run migrations and the manual scenarios against MySQL too. CSRF is disabled by Laravel's test environment; check it separately in the browser.

`composer.lock` is included; use `composer install` to reproduce the dependency versions. Development dependencies are required to run tests.

## XAMPP integration

See [step-by-step XAMPP setup and troubleshooting](docs/08-xampp-setup.md). The installed XAMPP PHP is 8.2.12, below this project’s PHP 8.3 minimum. Use your separate PHP 8.3+ CLI to serve Laravel and XAMPP for MariaDB/phpMyAdmin.

There is no public administrator registration and no seeded administrator password. A controlled admin provisioning command will be added with the admin module. Tests create admin accounts only in their isolated database.

## Learning and planning

- [Requirements and scope](docs/01-requirements.md)
- [Architecture](docs/02-architecture.md)
- [Database and ER design](docs/03-database-design.md)
- [Authentication: flow, files, tests, common errors, viva](docs/04-authentication.md)
- [Design system](docs/05-design-system.md)
- [Test records](docs/06-test-plan.md)

We stop at this increment before implementing further modules, so the group can review the foundation.
