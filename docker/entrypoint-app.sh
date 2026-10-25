#!/usr/bin/env bash

set -e

cd /var/www/html


# Create env if missing
if [ ! -f .env ]; then
    cp .env.example .env
fi


# Generate app key
if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi


php artisan storage:link || true

if [ "${APP_ENV}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
else
    php artisan config:clear
fi

if [ "${DB_MIGRATE_ON_BOOT:-false}" = "true" ]; then
    php artisan migrate --force
    php artisan app:run-upgrades
    php artisan config:clear
    php artisan queue:restart
fi


# Permissions (mounted volumes reset ownership on start, so fix it up as root here)
chown -R www-data:www-data \
    storage \
    bootstrap/cache


exec "$@"
