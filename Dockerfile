# syntax=docker/dockerfile:1
# LocalBoost AI — production image (Laravel 13, PHP 8.3, Apache, MySQL/PostgreSQL/Redis ready)
# Designed for Coolify / any Docker host. Composer runs only in the build stage;
# the runtime image carries the prebuilt vendor/ directory and does NOT run composer.

# -----------------------------------------------------------------------------
# Stage 1: Composer dependencies (cached layer on composer.json / composer.lock)
# -----------------------------------------------------------------------------
FROM composer:2 AS build
ARG APP_NAME=MLHUB
ARG APP_URL=https://mlhub.vn
ARG APP_KEY=base64:ZsLIT5SiyQFlitofPwZS+/qfEjyOIVDwYOiZXHPrcIA=
ARG APP_ENV=production
ARG APP_INSTALLED=true
ARG APP_DEBUG=false
ARG APP_FAKER_LOCALE=vi_VN
ARG BCRYPT_ROUNDS=12
ARG LOG_CHANNEL=stack
ARG LOG_STACK=single
ARG APP_LOCALE=vi
ARG APP_FALLBACK_LOCALE=vi
ARG APP_MAINTENANCE_DRIVER=file
ARG LOG_LEVEL=error
ARG DB_CONNECTION=mysql
ARG LOG_DEPRECATIONS_CHANNEL=null
ARG DB_HOST=ve8ff259t5fopbw4yqxix3jg
ARG DB_DATABASE=default
ARG DB_PORT=3306
ARG SESSION_DRIVER=database
ARG DB_USERNAME=mysql
ARG SESSION_LIFETIME=120
ARG SESSION_ENCRYPT=false
ARG SESSION_PATH=/
ARG DB_PASSWORD=GaUP8O3fgvqZE4GX4sqBbgGvql6xwTdGUH9shNvADDpQEjK1vJZ7wZJhd6jnDLXp
ARG SESSION_DOMAIN=null
ARG BROADCAST_CONNECTION=log
ARG QUEUE_CONNECTION=database
ARG CACHE_STORE=database
ARG MEMCACHED_HOST=127.0.0.1
ARG REDIS_CLIENT=phpredis
ARG REDIS_HOST=127.0.0.1
ARG SESSION_COOKIE=mlhub_session
ARG SESSION_SECURE_COOKIE=true
ARG FILESYSTEM_DISK=public
ARG REDIS_PASSWORD=null
ARG REDIS_PORT=6379
ARG MAIL_MAILER=log
ARG MAIL_SCHEME=null
ARG MAIL_HOST=127.0.0.1
ARG MAIL_PORT=2525
ARG MAIL_USERNAME=null
ARG MAIL_PASSWORD=null
ARG MAIL_FROM_ADDRESS=hello@example.com
ARG MAIL_FROM_NAME=${APP_NAME}
ARG SITE_DESCRIPTION=Nền tảng Marketing Automation hỗ trợ tăng đánh giá, đặt lịch, mã ưu đãi, phản hồi & tạo khách hàng tiềm năng.
ARG AWS_ACCESS_KEY_ID=
ARG AWS_SECRET_ACCESS_KEY=
ARG AWS_DEFAULT_REGION=us-east-1
ARG AWS_BUCKET=
ARG AWS_USE_PATH_STYLE_ENDPOINT=false
ARG VITE_APP_NAME=${APP_NAME}
ARG APP_TIMEZONE=Asia/Ho_Chi_Minh
ARG SITE_TITLE=MLHUB
ARG APP_DEMO=false
ARG SITE_KEYWORDS=MLHUB, Marketing Automation, đánh giá, đặt lịch, mã ưu đãi, phản hồi, khách hàng tiềm năng, hộ kinh doanh
ARG COOLIFY_URL=https://mlhub.vn,https//www.mlhub.vn
ARG COOLIFY_FQDN=mlhub.vn,https
ARG COOLIFY_BRANCH=main
ARG COOLIFY_RESOURCE_UUID=mggu45o8q8aso8stbepn622j

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
ARG APP_NAME=MLHUB
ARG APP_URL=https://mlhub.vn
ARG APP_KEY=base64:ZsLIT5SiyQFlitofPwZS+/qfEjyOIVDwYOiZXHPrcIA=
ARG APP_ENV=production
ARG APP_INSTALLED=true
ARG APP_DEBUG=false
ARG APP_FAKER_LOCALE=vi_VN
ARG BCRYPT_ROUNDS=12
ARG LOG_CHANNEL=stack
ARG LOG_STACK=single
ARG APP_LOCALE=vi
ARG APP_FALLBACK_LOCALE=vi
ARG APP_MAINTENANCE_DRIVER=file
ARG LOG_LEVEL=error
ARG DB_CONNECTION=mysql
ARG LOG_DEPRECATIONS_CHANNEL=null
ARG DB_HOST=ve8ff259t5fopbw4yqxix3jg
ARG DB_DATABASE=default
ARG DB_PORT=3306
ARG SESSION_DRIVER=database
ARG DB_USERNAME=mysql
ARG SESSION_LIFETIME=120
ARG SESSION_ENCRYPT=false
ARG SESSION_PATH=/
ARG DB_PASSWORD=GaUP8O3fgvqZE4GX4sqBbgGvql6xwTdGUH9shNvADDpQEjK1vJZ7wZJhd6jnDLXp
ARG SESSION_DOMAIN=null
ARG BROADCAST_CONNECTION=log
ARG QUEUE_CONNECTION=database
ARG CACHE_STORE=database
ARG MEMCACHED_HOST=127.0.0.1
ARG REDIS_CLIENT=phpredis
ARG REDIS_HOST=127.0.0.1
ARG SESSION_COOKIE=mlhub_session
ARG SESSION_SECURE_COOKIE=true
ARG FILESYSTEM_DISK=public
ARG REDIS_PASSWORD=null
ARG REDIS_PORT=6379
ARG MAIL_MAILER=log
ARG MAIL_SCHEME=null
ARG MAIL_HOST=127.0.0.1
ARG MAIL_PORT=2525
ARG MAIL_USERNAME=null
ARG MAIL_PASSWORD=null
ARG MAIL_FROM_ADDRESS=hello@example.com
ARG MAIL_FROM_NAME=${APP_NAME}
ARG SITE_DESCRIPTION=Nền tảng Marketing Automation hỗ trợ tăng đánh giá, đặt lịch, mã ưu đãi, phản hồi & tạo khách hàng tiềm năng.
ARG AWS_ACCESS_KEY_ID=
ARG AWS_SECRET_ACCESS_KEY=
ARG AWS_DEFAULT_REGION=us-east-1
ARG AWS_BUCKET=
ARG AWS_USE_PATH_STYLE_ENDPOINT=false
ARG VITE_APP_NAME=${APP_NAME}
ARG APP_TIMEZONE=Asia/Ho_Chi_Minh
ARG SITE_TITLE=MLHUB
ARG APP_DEMO=false
ARG SITE_KEYWORDS=MLHUB, Marketing Automation, đánh giá, đặt lịch, mã ưu đãi, phản hồi, khách hàng tiềm năng, hộ kinh doanh
ARG COOLIFY_URL=https://mlhub.vn,https//www.mlhub.vn
ARG COOLIFY_FQDN=mlhub.vn,https
ARG COOLIFY_BRANCH=main
ARG COOLIFY_RESOURCE_UUID=mggu45o8q8aso8stbepn622j

LABEL maintainer="LocalBoost AI"
LABEL description="Laravel 13 + Livewire 4 application — Apache, PHP 8.3, MySQL/PostgreSQL/Redis ready"

# Apache document root → Laravel public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# PHP production defaults (override in Coolify if needed)
ENV PHP_OPCACHE_ENABLE=1

WORKDIR /var/www/html

# System libraries + PHP extensions (Laravel + dompdf + AWS + MySQL + PostgreSQL)
RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        unzip \
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

# Optional: tuned opcache for production
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.save_comments=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Upload limits — tránh file bị cắt (MIME/validation fail) và từ chối ảnh logo
RUN echo "upload_max_filesize = 50M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && php -r "\$required = ['ctype','curl','fileinfo','filter','gd','hash','json','mbstring','openssl','pdo','pdo_mysql','tokenizer','xml','zip']; foreach (\$required as \$e) { if (! extension_loaded(\$e)) { fwrite(STDERR, \"Missing PHP extension: \$e\\n\"); exit(1); } }"

# Application code
COPY --chown=www-data:www-data . /var/www/html
COPY --from=build --chown=www-data:www-data /app/vendor /var/www/html/vendor

# Entrypoint
COPY --chmod=755 entrypoint.sh /usr/local/bin/entrypoint.sh

# Writable Laravel paths
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