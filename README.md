# Home Services Booking Platform

University Software Engineering mini-project using Laravel 13, PHP 8.3+, MySQL, and Blade. No Node build step is needed for this increment.

## Current increment

Implemented: existing public pages and secure role-based authentication, database-driven categories, provider registration with multiple categories, provider profile/dashboard editing, category provider directory, and Admin category creation/editing/activation. Existing Blade design and dependencies are preserved. No invented providers, ratings or job counts are displayed.

Not implemented: bookings, messages, reviews, complaints, password reset, email verification, and admin provisioning. Existing admin accounts can access category management; public registration cannot create Admin accounts.

**Verification (2026-10-02):** SQLite feature suite passes 24 tests with 183 assertions. Production MySQL migrations and manual browser checks are to be run locally. Existing database records have not been changed by this implementation.

See [provider foundation implementation and viva report](docs/09-provider-foundation.md).

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
php artisan db:seed --class=ServiceCategorySeeder
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

There is no public administrator registration and no seeded administrator password. Use an existing trusted Admin account for category management; account provisioning remains outside this increment. Tests create admin accounts only in their isolated database.

## Learning and planning

- [Requirements and scope](docs/01-requirements.md)
- [Architecture](docs/02-architecture.md)
- [Database and ER design](docs/03-database-design.md)
- [Authentication: flow, files, tests, common errors, viva](docs/04-authentication.md)
- [Design system](docs/05-design-system.md)
- [Test records](docs/06-test-plan.md)

Bookings and other modules remain planned so this foundation can be reviewed independently.
