# Home Services Booking Platform

University Software Engineering mini-project using Laravel 13, PHP 8.3+, MySQL, and Blade. No Node build step is needed for this increment.

## Current increment

Source prepared: customer/provider registration, login/logout, hashed passwords, role middleware, protected role dashboard shells, reusable layout/navigation/footer/input, initial design tokens, and authentication feature tests.

Not implemented: provider profiles, discovery, bookings, messages, reviews, complaints, administrative management, full homepage, password reset, email verification, and deployment automation. Dashboard shells intentionally show no invented statistics.

**Verification:** PHP syntax and Composer manifest validation can be checked without dependencies. Laravel boot, migrations, browser rendering, and feature tests have NOT been verified: Packagist DNS resolution fails in the development environment. This repository has no installed `vendor` directory or generated lockfile yet.

## Local setup

Run these commands from the project directory on a machine with internet access, PHP 8.3+ (including PDO MySQL), Composer, and MySQL:

```bash
composer install
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

Commit the generated `composer.lock` after the first successful installation so teammates install the same versions. The framework dependency is constrained to Laravel 13; inspect the resolved versions before release.

There is no public administrator registration and no seeded administrator password. A controlled admin provisioning command will be added with the admin module. Tests create admin accounts only in their isolated database.

## Learning and planning

- [Requirements and scope](docs/01-requirements.md)
- [Architecture](docs/02-architecture.md)
- [Database and ER design](docs/03-database-design.md)
- [Authentication: flow, files, tests, common errors, viva](docs/04-authentication.md)
- [Design system](docs/05-design-system.md)
- [Test records](docs/06-test-plan.md)

We stop at this increment before implementing further modules, so the group can review the foundation.
