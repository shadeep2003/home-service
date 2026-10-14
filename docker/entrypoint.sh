#!/bin/sh
set -eu

if [ -z "${APP_KEY:-}" ]; then
    echo 'APP_KEY is missing. Run the Docker initialization command in docs/11-docker-development.md.' >&2
    exit 1
fi

mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
# The image has no local .env or cached local configuration.
if [ ! -L public/storage ]; then
    php artisan storage:link --no-interaction
fi
exec gosu www-data "$@"
