# =============================================================================
# Stage 1: Frontend build (Node)
# =============================================================================
FROM node:20-alpine AS frontend-builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY frontend ./frontend
COPY cevi-themes ./cevi-themes
COPY npm_run ./npm_run
COPY .eslintrc .stylelintrc ./

RUN npm run build

# =============================================================================
# Stage 2: Composer / PHP dependencies
# =============================================================================
FROM composer:2 AS composer-builder

WORKDIR /app

# Copy PHP deps manifest for layer caching
COPY drupal/composer.json drupal/composer.lock ./
COPY drupal/scripts ./scripts

# ScriptHandler::copyCeviFiles resolves paths relative to drupalRoot (web/../../).
# With WORKDIR=/app, drupalRoot=/app/web, so it looks for /cevi-modules and /cevi-themes.
COPY cevi-modules /cevi-modules
COPY cevi-themes /cevi-themes

# copyCeviFiles requires settings/settings.current.php to exist at build time.
# Use the docker settings file as a placeholder; production settings.php replaces it in stage 3.
COPY settings /settings
COPY docker/php/settings.docker.php /settings/settings.current.php

# Install PHP deps; post-install-cmd runs copyCeviFiles into web/
RUN composer install \
    --no-dev \
    --no-interaction \
    --optimize-autoloader \
    --no-progress

# Overlay frontend-built CSS/JS assets into the cevi source, then re-sync into web/
COPY --from=frontend-builder /app/cevi-themes /cevi-themes
RUN composer run-script post-install-cmd

# Copy drupal config
COPY drupal/config ./drupal/config

# =============================================================================
# Stage 3: Production PHP-FPM image
# =============================================================================
FROM php:8.3-fpm-alpine AS production

# Install build dependencies, compile PHP extensions, then remove build tools
RUN apk add --no-cache \
    freetype \
    icu-libs \
    libjpeg-turbo \
    libpng \
    libwebp \
    libzip \
    mariadb-client \
    && apk add --no-cache --virtual .build-deps \
        autoconf \
        freetype-dev \
        g++ \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        make \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        gd \
        intl \
        opcache \
        pdo_mysql \
        zip \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apk del .build-deps

# PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/drupal.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copy application from builder stages
WORKDIR /var/www/html

COPY --from=composer-builder /app/web ./web
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=composer-builder /app/drupal/config ./config

# Copy settings (credentials come from .env at runtime)
COPY settings/settings.php ./web/sites/default/settings.php
COPY docker/php/settings.docker.php ./web/sites/default/settings.current.php

# Ensure correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/web \
    && mkdir -p /var/www/html/web/sites/default/files \
    && chown -R www-data:www-data /var/www/html/web/sites/default/files

COPY scripts/entrypoint.sh /entrypoint.sh
COPY drupal/cevi-install.sh /cevi-install.sh
RUN chmod +x /entrypoint.sh /cevi-install.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
