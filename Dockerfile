# MLHUB — production image (Laravel 13, PHP 8.3, Apache + Supervisor).
# Runtime secrets (DB, Redis, APP_KEY, mail, OAuth…) chỉ qua Coolify Environment Variables.

# -----------------------------------------------------------------------------
# Stage 1: Composer dependencies
# -----------------------------------------------------------------------------
FROM composer:2 AS build

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts \
    --no-interaction \
    --prefer-dist

# -----------------------------------------------------------------------------
# Stage 2: Production runtime
# -----------------------------------------------------------------------------
FROM php:8.3-apache-bookworm AS production

LABEL maintainer="MLHUB"
LABEL description="Laravel 13 + Livewire 4 — Apache, PHP 8.3, Supervisor (queue + scheduler)"

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    COMPOSER_ALLOW_SUPERUSER=1 \
    MAKEFLAGS=-j1

RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        supervisor \
        default-libmysqlclient-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j1 \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        mysqli \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && a2enmod rewrite headers expires \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/app.conf
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/docker-entrypoint

WORKDIR /var/www/html

COPY --chown=www-data:www-data . /var/www/html
COPY --from=build --chown=www-data:www-data /app/vendor /var/www/html/vendor

RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        storage/app/public \
        bootstrap/cache \
        public/livewire \
    && chown -R www-data:www-data storage bootstrap/cache public/livewire \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD curl --fail --silent http://127.0.0.1/up >/dev/null || exit 1

ENTRYPOINT ["docker-entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/app.conf"]
