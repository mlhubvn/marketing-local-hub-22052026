#!/bin/sh
# MLHUB — container entrypoint (bootstrap) → supervisord (apache + queue + scheduler)

set -eu

cd /var/www/html

log_step() {
    echo ""
    echo "=== entrypoint: $1 ==="
}

log_step "preflight env"
if [ -z "${APP_KEY:-}" ] || [ "$APP_KEY" = "base64:" ] || [ "$APP_KEY" = "(đặt secret trên Coolify — không commit)" ]; then
    echo "ERROR: APP_KEY chưa đặt trên Coolify Environment Variables." >&2
    echo "Sinh key local: php artisan key:generate --show → dán vào Coolify → redeploy." >&2
    exit 1
fi

log_step "writable directories"
mkdir -p \
    bootstrap/cache \
    storage/app/private \
    storage/app/public \
    storage/app/public/files \
    storage/app/temp-storage \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    public/livewire

chown -R www-data:www-data bootstrap/cache storage
chmod -R ug+rwX bootstrap/cache storage

LW_SRC="vendor/livewire/livewire/dist/livewire.js"
LW_DST="public/livewire/livewire.js"
LW_MAP_SRC="vendor/livewire/livewire/dist/livewire.js.map"
LW_MAP_DST="public/livewire/livewire.js.map"

if [ -f "$LW_SRC" ]; then
    cp -f "$LW_SRC" "$LW_DST"
    touch "$LW_DST"
    chown www-data:www-data "$LW_DST" 2>/dev/null || true
    if [ -f "$LW_MAP_SRC" ]; then
        cp -f "$LW_MAP_SRC" "$LW_MAP_DST"
        chown www-data:www-data "$LW_MAP_DST" 2>/dev/null || true
    fi
    LW_HASH=$(md5sum "$LW_DST" 2>/dev/null | awk '{print $1}')
    LW_SIZE=$(wc -c < "$LW_DST" 2>/dev/null | tr -d ' ')
    echo "Livewire JS synced: $LW_DST (size=${LW_SIZE} md5=${LW_HASH})"
else
    echo "Livewire JS source missing at $LW_SRC — skipping."
fi

find storage/framework/views -mindepth 1 -name '*.php' -delete 2>/dev/null || true
find storage/framework/cache/data -mindepth 1 -type d -empty -delete 2>/dev/null || true
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/routes-v7.php bootstrap/cache/config.php bootstrap/cache/events.php
find bootstrap/cache -maxdepth 1 -name 'livewire-*' -delete 2>/dev/null || true

log_step "package:discover"
if ! php artisan package:discover --ansi; then
    echo "ERROR: package:discover failed — kiểm tra vendor/ và modules/CustomMLHUB." >&2
    exit 1
fi

PUBLIC_STORAGE="public/storage"
STORAGE_LINK_REL="../storage/app/public"

storage_link_ok() {
    [ -L "$PUBLIC_STORAGE" ] && [ -e "$PUBLIC_STORAGE" ]
}

if [ -e "$PUBLIC_STORAGE" ] && ! storage_link_ok; then
    echo "Removing invalid public/storage (expected symlink, found file or directory)."
    rm -rf "$PUBLIC_STORAGE"
fi

if ! storage_link_ok; then
    php artisan storage:link --force --ansi 2>/dev/null || php artisan storage:link --ansi 2>/dev/null || true
fi

if ! storage_link_ok; then
    ln -sfn "$STORAGE_LINK_REL" "$PUBLIC_STORAGE"
fi

if ! storage_link_ok; then
    echo "ERROR: public/storage symlink is missing or broken after storage:link." >&2
    exit 1
fi

mkdir -p public/resources
rm -rf public/resources/themes
ln -sfn ../../resources/themes public/resources/themes

THEME_FRONTEND_VALUE="${THEME_FRONTEND:-mlhubfrontend}"
rm -rf public/img
ln -sfn "../resources/themes/guest/${THEME_FRONTEND_VALUE}/assets/img" public/img

chown -h www-data:www-data public/storage public/img 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache

APP_INSTALLED_VALUE="${APP_INSTALLED:-false}"

case "$APP_INSTALLED_VALUE" in
    true|TRUE|1|yes|on)
        log_step "database migrate"
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
        php artisan mlhub:sync-env-options --ansi 2>/dev/null \
            || echo "WARN: mlhub:sync-env-options skipped." >&2
        ;;
    *)
        echo "APP_INSTALLED=${APP_INSTALLED_VALUE} → skipping migrate."
        ;;
esac

is_app_installed() {
    case "${1:-false}" in
        true|TRUE|1|yes|on) return 0 ;;
        *) return 1 ;;
    esac
}

log_step "cache refresh"
if is_app_installed "$APP_INSTALLED_VALUE"; then
    php artisan optimize:clear --ansi \
        || echo "WARN: optimize:clear failed (kiểm tra REDIS_HOST/REDIS_PASSWORD)." >&2
    php artisan view:clear --ansi || true
    php artisan route:clear --ansi || true
    php artisan config:clear --ansi || true
    php artisan event:clear --ansi 2>/dev/null || true
    php artisan optimize --ansi \
        || echo "WARN: optimize failed — app vẫn chạy." >&2
else
    php artisan config:clear --ansi || true
    php artisan route:clear --ansi || true
    php artisan view:clear --ansi || true
    CACHE_STORE=file php artisan cache:clear --ansi || true
fi

log_step "starting ${*:-supervisord}"
exec "$@"
