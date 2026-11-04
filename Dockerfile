# syntax=docker/dockerfile:1
FROM php:8.5-apache-trixie AS php-base

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev libonig-dev libsqlite3-dev libzip-dev unzip \
    && docker-php-ext-install -j"$(nproc)" bcmath intl mbstring pdo_sqlite zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

FROM composer:2 AS composer-binary

FROM node:24-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json .npmrc ./
RUN npm ci --no-audit --no-fund
COPY . .
RUN npm run build

FROM php-base AS dependencies
WORKDIR /app
COPY --from=composer-binary /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress \
    --no-scripts --no-autoloader
COPY . .
COPY --from=assets /app/public/build ./public/build
RUN mkdir -p bootstrap/cache storage/framework/views \
    && composer dump-autoload --no-dev --classmap-authoritative --no-interaction --no-scripts \
    && php artisan package:discover --ansi --no-interaction \
    && composer check-platform-reqs --no-dev \
    && rm -rf tests docker .npmrc phpunit.xml package.json package-lock.json vite.config.js

FROM php-base AS test
WORKDIR /app
COPY --from=composer-binary /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-interaction --no-progress --no-scripts --no-autoloader
COPY . .
COPY --from=assets /app/public/build ./public/build
RUN mkdir -p bootstrap/cache storage/framework/views \
    && composer dump-autoload --no-interaction --no-scripts \
    && php artisan package:discover --ansi --no-interaction \
    && APP_KEY="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')" \
       php artisan test --compact --no-interaction

FROM php-base AS runtime
WORKDIR /var/www/html
LABEL org.opencontainers.image.title="Taskly" \
    org.opencontainers.image.description="Sistem manajemen tugas Laravel dengan SQLite" \
    org.opencontainers.image.source="https://github.com/AhmadNurHidayat-23/taskly-sistem-manajemen-tugas"
ENV APP_NAME=Taskly APP_ENV=production APP_DEBUG=false APP_URL=http://localhost:8080 \
    LOG_CHANNEL=stderr LOG_LEVEL=warning DB_CONNECTION=sqlite \
    DB_DATABASE=/var/www/html/storage/database/database.sqlite \
    SESSION_DRIVER=file CACHE_STORE=file QUEUE_CONNECTION=sync
COPY --from=dependencies --chown=www-data:www-data /app ./
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/taskly.ini
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/taskly-entrypoint
RUN mkdir -p storage/database storage/logs storage/framework/cache/data \
        storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
EXPOSE 80
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1/up") === false ? 1 : 0);'
ENTRYPOINT ["taskly-entrypoint"]
CMD ["apache2-foreground"]
