FROM php:8.5-cli-bookworm AS runtime

WORKDIR /var/www/html
RUN apt-get update && apt-get install -y --no-install-recommends \
    gosu unzip libonig-dev libzip-dev libsqlite3-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j2 mbstring pdo_mysql pdo_sqlite bcmath pcntl gd zip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2.8 /usr/bin/composer /usr/local/bin/composer

FROM runtime AS dependencies
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-progress --no-scripts --no-autoloader

FROM runtime AS development
COPY . .
COPY --from=dependencies /var/www/html/vendor ./vendor
RUN mkdir -p bootstrap/cache storage/app/public storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs \
    && composer dump-autoload --optimize --no-interaction \
    && chmod +x docker/entrypoint.sh \
    && chown -R www-data:www-data storage bootstrap/cache
EXPOSE 8000
ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--no-reload"]
