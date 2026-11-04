#!/bin/sh
set -eu

if [ "${1:-}" = "apache2-foreground" ]; then
    if [ -z "${APP_KEY:-}" ]; then
        echo "APP_KEY wajib diberikan melalui environment saat container dijalankan." >&2
        exit 1
    fi

    mkdir -p storage/database storage/logs storage/framework/cache/data \
        storage/framework/sessions storage/framework/views bootstrap/cache

    if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
        case "${DB_DATABASE:-}" in
            /*) ;;
            *) echo "DB_DATABASE harus berupa path absolut untuk SQLite." >&2; exit 1 ;;
        esac
        mkdir -p "$(dirname "$DB_DATABASE")"
        touch "$DB_DATABASE"
        chown www-data:www-data "$(dirname "$DB_DATABASE")" "$DB_DATABASE"
    fi

    chown -R www-data:www-data storage bootstrap/cache
    if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
        php artisan migrate --force --no-interaction
    fi
fi

exec docker-php-entrypoint "$@"
