# Connect this project to XAMPP

## Choose the compatible setup

This project uses Laravel 13 and requires PHP 8.3+. The XAMPP installed at `/opt/lampp` includes PHP 8.2.12. Use XAMPP's MariaDB (labelled MySQL) and phpMyAdmin, with the separate PHP 8.3+ CLI running Laravel. You do not need to move the project into `htdocs`.

Requests go from the browser to Laravel at port 8000, then Laravel reads/writes XAMPP's database at port 3306. phpMyAdmin lets you inspect the same database.

## 1. Start XAMPP

On this Linux machine, run in your terminal:

```bash
sudo /opt/lampp/lampp startmysql
sudo /opt/lampp/lampp startapache
```

Apache is used here for phpMyAdmin. On Windows, start Apache and MySQL in the XAMPP Control Panel. If either reports a port conflict, check the existing service before stopping it or choose another port and update the configuration below.

Open http://localhost/phpmyadmin. Create a database named `home_services` with collation `utf8mb4_unicode_ci`. No SQL dump is needed: Laravel migrations create the tables.

## 2. Configure Laravel

From the project directory:

```bash
php -v
php -r 'print_r(PDO::getAvailableDrivers());'
composer install
```

PHP must be at least 8.3 and PDO drivers must include `mysql`. Do not use `/opt/lampp/bin/php` for Laravel. On Windows, ensure your separate PHP 8.3+ installation is selected in PATH for both PHP and Composer.

For a fresh checkout only, copy `.env.example` to `.env` and run `php artisan key:generate`. Keep an existing application key when the app is already configured.

Edit your existing `.env`:

```dotenv
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=home_services
DB_USERNAME=root
DB_PASSWORD=
```

Use the actual database username/password and port if you changed XAMPP defaults. The blank root password is only an example for local development. Keep `.env` private. For a dedicated database account, grant it access to `home_services` and use its credentials here.

```bash
php artisan config:clear
php artisan migrate:status
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8000
```

On a new database, `migrate:status` may report that the migration table does not exist; run `migrate` next. Back up an existing database before schema changes. Avoid `migrate:fresh`, which deletes tables.

## 3. Confirm the connection works

1. Open http://localhost:8000 and register a customer.
2. In phpMyAdmin, select `home_services`, then browse `users`. The new account should appear with a hashed password.
3. Log out and log in again. Register a provider using a different email and verify its dashboard.
4. Run `composer test`. These tests use isolated SQLite; they do not modify the XAMPP database.

Only authentication currently stores user data. Discovery, bookings, messages, reviews, and administration remain future modules; connecting XAMPP does not implement them.

## Troubleshooting

| Error | Check |
|---|---|
| PHP version/platform error | Run `php -v`; select PHP 8.3+ rather than XAMPP PHP 8.2. |
| Could not find driver | Enable/install PDO MySQL in the PHP runtime serving Laravel; inspect `php --ini`. |
| Connection refused / SQLSTATE 2002 | Start XAMPP MySQL; confirm its TCP port matches `.env`. |
| Access denied / SQLSTATE 1045 | Correct database username/password and account access. |
| Unknown database / SQLSTATE 1049 | Create `home_services` in phpMyAdmin. |
| Users table missing | Run `php artisan migrate` with the correct database selected. |
| Settings appear unchanged | Run `php artisan config:clear`, then restart the Laravel server. |
| Apache will not start | Check whether another server already uses port 80/443; phpMyAdmin's URL must match Apache's configured port. |
| Permission denied writing Laravel cache | The PHP process needs write access to `storage` and `bootstrap/cache`; avoid blanket `chmod 777`. |

## Serving Laravel through XAMPP Apache instead

This requires Apache to execute PHP 8.3+ too; changing only your terminal's PHP does not upgrade Apache's PHP module. With the installed PHP 8.2.12, use the port-8000 setup above. If Apache is later configured with compatible PHP, set its virtual host document root to this project's `public` directory, allow `.htaccess` overrides, and enable `mod_rewrite`. Never expose the repository root through Apache, since it contains `.env` and other private files.

References: [Laravel 13 PHP requirements](https://github.com/laravel/docs/blob/13.x/releases.md), [Laravel web server configuration](https://laravel.com/framework/docs/deployment), [XAMPP Linux FAQ](https://www.apachefriends.org/faq_linux.html), [XAMPP Windows FAQ](https://www.apachefriends.org/faq_windows.html).
