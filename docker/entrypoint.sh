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


# Only the "app" service owns bootstrap: cache warmup, migrations,
# and publishing assets into the shared volume for nginx. Other
# services (worker/scheduler/reverb) just need the app booted.
if [ "${CONTAINER_ROLE:-app}" = "app" ]; then

    php artisan storage:link || true

    # Publish public/ assets into the shared volume so nginx can serve them
    if [ -d /var/www/html/public-shared ]; then
        cp -r /var/www/html/public/. /var/www/html/public-shared/
    fi

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

fi


# Permissions (mounted volumes reset ownership on start, so fix it up as root here)
chown -R www-data:www-data \
    storage \
    bootstrap/cache


# php-fpm's master process must stay root so it can drop its workers to
# www-data itself (per docker/php/www.conf); everything else (queue worker,
# scheduler, reverb) should run as www-data directly.
if [ "$1" = "php-fpm" ]; then
    exec "$@"
else
    exec su-exec www-data "$@"
fi
