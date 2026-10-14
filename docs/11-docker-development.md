# Docker development and team setup

This is a local development/testing environment, not a production server.
It preserves your existing local `.env`, Gmail credentials, and XAMPP database.
PHP 8.5 runs the committed Laravel/Composer lockfile (locked Symfony packages
require PHP >=8.4.1). Assets are plain CSS/JavaScript in `public`; this project
has no package.json or Vite configuration, so no Node dependencies are added.

## Architecture

Compose project: `homeservices-docker`.

| Service | Image/runtime | Access |
| --- | --- | --- |
| app | `homeservices:0.1.0`, PHP 8.5 CLI development server | http://localhost:8000 |
| db | MySQL 8.4 LTS | `db:3306`, only on the Compose network |
| mailpit | Official `axllent/mailpit:v1.27` | http://localhost:8025; SMTP `mailpit:1025` |

Default container names are `homeservices-docker-app-1`,
`homeservices-docker-db-1`, and `homeservices-docker-mailpit-1`.
Compose creates a shared default network. Inside app, localhost means app itself;
service names resolve the other containers. Browser ports bind to 127.0.0.1 only.
The named volumes `homeservices-docker_mysql_data` and
`homeservices-docker_app_storage` preserve the database, uploads, file sessions,
and cache when containers are recreated. Mailpit inbox contents are temporary.

## Prerequisites

Ubuntu: Docker Engine and Compose plugin, with permission to access Docker.
Windows: Docker Desktop running Linux containers (WSL2 backend recommended),
Git, and PowerShell. Run commands from the cloned repository in either Bash
or PowerShell. No host PHP, Composer, MySQL, or Node installation is required.
Use a recent Compose supporting optional `env_file` (2.24+).

## First-time setup

1. Clone your team's GitHub repository and enter its directory.
2. Build the application image:

```sh
docker compose build app
```

3. Generate a Docker-specific environment using container PHP:

```sh
docker compose run --rm --no-deps --entrypoint php --volume "./:/setup" app docker/init.php /setup/.env.docker
```

This writes `.env.docker` on your computer with a cryptographically random
application key, app database password, and separate root password. It never
prints values, never reads your local `.env`, and refuses to overwrite an
existing Docker environment. On Ubuntu, this file may be owned by root; restore
your user ownership with `sudo chown "$(id -u):$(id -g)" .env.docker` if necessary.
Keep the file private and backed up, especially the application key. Do not
regenerate it for an existing environment. It is excluded from Git and the image.
The example at `docker/environment.example` contains no usable app/database keys.
Do not start services with an uninitialized environment.

4. Start the three services:

```sh
docker compose up -d --build --wait
```

5. Initialize only the Docker database:

```sh
docker compose exec --user www-data app php artisan migrate --force
docker compose exec --user www-data app php artisan db:seed --class=ServiceCategorySeeder --force
```

These connect to `db:3306` with `.env.docker`, not XAMPP. Categories are needed
for provider registration. DemoDataSeeder is optional and is not run automatically;
review its example users and behavior before using it.

6. Open http://localhost:8000 and http://localhost:8025.

## Daily development

```sh
docker compose up -d --wait
docker compose ps
docker compose logs --tail=100 app db mailpit
docker compose stop
```

`stop` leaves containers and volumes in place. `docker compose down` removes
containers/network while preserving named volumes. Do not append `--volumes`:
that deletes the Docker database and uploads.

Application source is baked into the image; after edits run
`docker compose up -d --build --wait`. No host source mount is used, which keeps
the local `.env`, vendor directory, and uploads separate and works on Windows.
If editing Docker scripts on Windows, keep LF line endings (`.gitattributes`
enforces this in Git). For Laravel logs, inspect privately with:

```sh
docker compose exec --user www-data app tail -n 100 storage/logs/laravel.log
```

Avoid sharing full logs, resolved `docker compose config`, environment dumps,
or container inspection output: runtime configuration can contain secrets.
Validate safely with `docker compose config --quiet`.

## Mailpit and OTP verification

Mailpit captures SMTP emails locally instead of delivering to real inboxes.
SMTP 1025 is not published to the host. The development transport disables TLS
and uses dummy credentials accepted by Mailpit to satisfy existing OTP SMTP
configuration validation. This is only for the isolated development network.
`config/mail.php` still requires TLS by default, preserving local Gmail behavior.
No authentication controllers, OTP logic, or verification rules are changed.

Check both roles with different test email addresses:

1. Register a customer, then a provider with a seeded service category.
2. Confirm registration redirects to the code verification page.
3. Open Mailpit, read the six-digit code, and enter it in HomeServices.
4. Confirm the customer/provider dashboard opens for the corresponding role.
5. Log out and log in again: already verified accounts should need only the
   password and should not generate another OTP email.
6. Check an incorrect code, wrong password, and unauthorized role dashboard.

No real recipient or Gmail app password is needed. Mailpit stores OTPs in its
local inbox, so use test accounts and keep the web interface bound to localhost.

## Automated tests and diagnostics

Compose injects MySQL settings, so explicitly override them for the feature suite
to keep its database in memory:

```sh
docker compose exec --user www-data -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: -e SESSION_DRIVER=array -e CACHE_STORE=array app php artisan test
docker compose exec --user www-data app composer check-platform-reqs
docker compose exec --user www-data app php artisan migrate:status
```

A fresh database is intentionally not migrated automatically on startup. Until
initialization, database-backed pages can fail; `/login` is the app health probe.
For startup issues inspect `docker compose ps` and service logs. Do not change
database passwords after MySQL volume initialization without changing them in
MySQL too: image initialization variables only apply to an empty database.

## Resetting the Docker database (destructive; optional)

Do not run this as routine setup. It drops ALL TABLES in the Docker development
database and recreates them, removing Docker users and verification records:

```sh
docker compose exec --user www-data app php artisan migrate:fresh --seed --force
```

First check `.env.docker` privately: DB_HOST must be `db`, DB_DATABASE must be
`homeservices_docker`, and DB_CONNECTION must be `mysql`. Back up anything you
need. This command is documented only; it is not executed by the setup or tests.
Never use volume pruning or change this setup to point at your XAMPP database.

## Future Docker Hub workflow (nothing published yet)

After local validation and approval, tag the built image with your real Docker
Hub username/repository and a version, for example `USERNAME/homeservices:0.1.0`.
Publish only after inspecting the image for accidental sensitive files. Do not
share `.env.docker`; each teammate generates their own environment.

Run this against the bare image, without Compose environment injection or mounts:

```sh
docker run --rm --entrypoint php homeservices:0.1.0 docker/inspect-image.php
```

It checks known secret/configuration paths, persisted data directories, and
runtime secret variables without printing values. Review the Dockerfile and
build context too; this targeted check is not a general secret scanner.

To use a published image, create an untracked override `compose.hub.yaml`:

```yaml
services:
  app:
    image: USERNAME/homeservices:0.1.0
```

Then pull and generate your environment using that image, without a build:

```sh
docker compose -f compose.yaml -f compose.hub.yaml pull
docker compose -f compose.yaml -f compose.hub.yaml run --rm --no-deps --no-build --entrypoint php --volume "./:/setup" app docker/init.php /setup/.env.docker
docker compose -f compose.yaml -f compose.hub.yaml up -d --no-build --wait
```

Run the migration/category seeder commands above with the same two `-f` options.
The repository supplies Compose and documentation; Docker Hub supplies the
built application image. Rebuild/publish a new version to distribute code changes.
The image includes development tools and tests and is for development only.

## Validation record (2026-10-08)

- Compose configuration validation: passed (`docker compose config --quiet`).
- PHP and entrypoint shell syntax checks: passed.
- Local Composer platform requirements: passed under PHP 8.5.
- Existing Laravel suite plus TLS configuration regression: 76 tests passed,
  754 assertions, using in-memory SQLite.
- Environment initializer: generated a valid 32-byte application key, matching
  application/MySQL user passwords, a separate root password, and refused to
  overwrite an existing environment. No values were printed.
- `.env.docker` was initialized locally with mode 0600 and is ignored by Git.
- Docker image build/pulls were attempted but registry connectivity was unstable:
  IPv6 network errors, authentication TLS timeout, and stalled layer transfers.
  Containers were not started; Docker MySQL migrations, live OTP delivery,
  website/Mailpit accessibility, container tests, and final image inspection
  remain unverified until image downloads and the build complete.
- No XAMPP data, local `.env`, real Gmail credentials, or authentication logic
  were changed. No database resets, commits, pushes, logins, or publishing ran.

Retry `docker compose build app` and `docker compose pull db mailpit` after Docker
Hub connectivity is stable, then follow initialization/testing above. The local
`.env.docker` already exists; do not run its generator again on this checkout.
