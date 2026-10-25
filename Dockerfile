# ==============================================================================
# Stage 1: Composer dependencies
# ==============================================================================
FROM composer:2.7 AS composer_builder

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-scripts \
    --no-autoloader \
    --ignore-platform-req=ext-gd \
    --ignore-platform-req=ext-exif


# ==============================================================================
# Stage 2: Frontend build (Laravel Mix + npm)
# ==============================================================================
FROM node:20-alpine AS frontend_builder

WORKDIR /app

COPY package.json package-lock.json webpack.mix.js tailwind.config.js artisan ./

COPY resources ./resources
COPY public ./public

RUN npm ci \
    && npm run prod


# ==============================================================================
# Stage 3: Production PHP-FPM container
# ==============================================================================
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    su-exec \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    icu-dev \
    oniguruma-dev \
    curl \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php/opcache.ini "$PHP_INI_DIR/conf.d/opcache.ini"
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

COPY --chown=www-data:www-data . .

COPY --from=composer_builder \
    /usr/bin/composer \
    /usr/bin/composer

COPY --from=composer_builder \
    --chown=www-data:www-data \
    /app/vendor \
    ./vendor

COPY --from=frontend_builder \
    --chown=www-data:www-data \
    /app/public \
    ./public

RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    /var/www/html/public-shared \
    && chown -R www-data:www-data storage bootstrap/cache /var/www/html/public-shared \
    && chmod -R 775 storage bootstrap/cache

RUN [ -f .env ] || cp .env.example .env

RUN composer dump-autoload \
    --no-dev \
    --optimize \
    && chown www-data:www-data vendor/composer/autoload_*.php vendor/composer/ClassLoader.php vendor/autoload.php

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]

CMD ["php-fpm"]
