#!/bin/sh
set -e

cd /var/www/html

# -----------------------------------------------------------------------------
# Permissions (Laravel storage + bootstrap/cache)
# -----------------------------------------------------------------------------
mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    public/livewire

chown -R www-data:www-data storage bootstrap/cache public/livewire 2>/dev/null || true
chmod -R 775 storage bootstrap/cache

# -----------------------------------------------------------------------------
# Assets skipped by composer --no-scripts in Docker build
# -----------------------------------------------------------------------------
if [ -f vendor/livewire/livewire/dist/livewire.js ]; then
    cp -f vendor/livewire/livewire/dist/livewire.js public/livewire/livewire.js
    chown www-data:www-data public/livewire/livewire.js 2>/dev/null || true
fi

# -----------------------------------------------------------------------------
# Laravel bootstrap (requires env from Coolify: APP_KEY, DB_*, etc.)
# -----------------------------------------------------------------------------
php artisan package:discover --ansi

php artisan storage:link --force 2>/dev/null || true

php artisan migrate --force --ansi

php artisan optimize:clear --ansi
php artisan optimize --ansi

# -----------------------------------------------------------------------------
# Hand off to web server (foreground)
# -----------------------------------------------------------------------------
exec "$@"
