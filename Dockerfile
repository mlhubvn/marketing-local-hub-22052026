# MLHUB — production image (Laravel 13, PHP 8.3, Apache).
# Biến môi trường thật (DB, Redis, APP_KEY, mail, license…) chỉ cấu hình trên Coolify — KHÔNG ghi secret vào Dockerfile.

# -----------------------------------------------------------------------------
# Stage 1: Composer dependencies (cached layer on composer.json / composer.lock)
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
# Stage 2: Production runtime (Apache + PHP 8.3)
# -----------------------------------------------------------------------------
FROM php:8.3-apache-bookworm AS production

LABEL maintainer="MLHUB"
LABEL description="Laravel 13 + Livewire 4 application — Apache, PHP 8.3, MySQL/Redis ready"

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV PHP_OPCACHE_ENABLE=1

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        unzip \
        zip \
        default-libmysqlclient-dev \
        libpq-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libicu-dev \
        libmagic-dev \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        mysqli \
        pdo_pgsql \
        pgsql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && a2enmod rewrite headers \
    && sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && printf '%s\n' \
        '<Directory /var/www/html/public>' \
        '    Options -Indexes +FollowSymLinks' \
        '    AllowOverride All' \
        '    Require all granted' \
        '</Directory>' \
        > /etc/apache2/conf-available/laravel-public.conf \
    && a2enconf laravel-public \
    && apt-get purge -y --auto-remove git unzip \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.save_comments=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

RUN echo "upload_max_filesize = 50M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && php -r "\$required = ['ctype','curl','fileinfo','filter','gd','hash','json','mbstring','openssl','pdo','pdo_mysql','tokenizer','xml','zip']; foreach (\$required as \$e) { if (! extension_loaded(\$e)) { fwrite(STDERR, \"Missing PHP extension: \$e\\n\"); exit(1); } }"

COPY --chown=www-data:www-data . /var/www/html
COPY --from=build --chown=www-data:www-data /app/vendor /var/www/html/vendor

COPY --chmod=755 entrypoint.sh /usr/local/bin/entrypoint.sh

RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
        public/livewire \
    && chown -R www-data:www-data storage bootstrap/cache public/livewire \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
