#!/bin/sh
# LocalBoost AI — container entrypoint
# Behavior:
#   1. Ensure writable storage / cache directories with correct ownership.
#   2. Publish Livewire JS to public/ (skipped if vendor not yet installed).
#   3. Run package:discover and storage:link.
#   4. If APP_INSTALLED=true → wait for DB and run `php artisan migrate --force`.
#      If APP_INSTALLED is anything else → skip migrate so the web installer
#      can create the schema itself on first run. This avoids the "database is
#      not empty" failure that occurs when migrate runs before the installer.
#   5. Refresh and warm Laravel caches.
#   6. exec the CMD (apache2-foreground).

set -e

cd /var/www/html

# -----------------------------------------------------------------------------
# 1. Writable directories
# -----------------------------------------------------------------------------
mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/app/public \
    bootstrap/cache \
    public/livewire

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# -----------------------------------------------------------------------------
# 2. Livewire JS (vendor present only after composer install; skip otherwise)
# -----------------------------------------------------------------------------
if [ -f vendor/livewire/livewire/dist/livewire.js ]; then
    cp -f vendor/livewire/livewire/dist/livewire.js public/livewire/livewire.js
    chown www-data:www-data public/livewire/livewire.js 2>/dev/null || true
fi

# -----------------------------------------------------------------------------
# 3. Laravel housekeeping
# -----------------------------------------------------------------------------
php artisan package:discover --ansi

php artisan storage:link --force

# Symlink public/storage → storage/app/public (Laravel standard; URLs are /storage/… not /storage/app/public/…)
if [ ! -L public/storage ] || [ ! -e public/storage ]; then
    echo "ERROR: public/storage symlink is missing or broken after storage:link." >&2
    ls -la public/storage 2>&1 || true
    exit 1
fi

# Uploaded files land here when FILESYSTEM / appfiles disk = public
mkdir -p storage/app/public/files
chown -R www-data:www-data storage/app/public
chmod -R 775 storage/app/public
mkdir -p public/resources
ln -sfn ../../resources/themes public/resources/themes

chown -h www-data:www-data public/storage 2>/dev/null || true
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# -----------------------------------------------------------------------------
# 4. Database migration (gated on APP_INSTALLED)
# -----------------------------------------------------------------------------
APP_INSTALLED_VALUE="${APP_INSTALLED:-false}"

case "$APP_INSTALLED_VALUE" in
    true|TRUE|1|yes|on)
        echo "APP_INSTALLED=${APP_INSTALLED_VALUE} → running migrate."
        attempt=0
        max_attempts=30
        until php artisan migrate --force --ansi; do
            attempt=$((attempt + 1))
            if [ "$attempt" -ge "$max_attempts" ]; then
                echo "Database migration failed after ${max_attempts} attempts." >&2
                exit 1
            fi
            echo "Waiting for database... (${attempt}/${max_attempts})"
            sleep 2
        done
        ;;
    *)
        echo "APP_INSTALLED=${APP_INSTALLED_VALUE} → skipping migrate."
        echo "Open the application URL in a browser to run the installer wizard."
        echo "After install, set APP_INSTALLED=true (and restart) so future deploys auto-migrate."
        ;;
esac

# -----------------------------------------------------------------------------
# 5. Cache refresh
# -----------------------------------------------------------------------------
php artisan optimize:clear --ansi
php artisan optimize --ansi

# -----------------------------------------------------------------------------
# 6. Hand off to Apache (or whatever CMD was supplied)
# -----------------------------------------------------------------------------
exec "$@"
