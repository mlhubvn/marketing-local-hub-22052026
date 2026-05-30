#!/bin/sh
# LocalBoost AI — container entrypoint
# Behavior:
#   1. Ensure writable storage / cache directories with correct ownership.
#   2. Publish Livewire JS to public/ with a refreshed mtime so the asset
#      cache-bust hash changes after every deploy (forces browsers to drop
#      stale snapshots which trip nested-component hydration errors).
#   3. Wipe compiled Blade / bootstrap caches from the previous image, then
#      run package:discover and storage:link.
#   4. If APP_INSTALLED=true → wait for DB and run `php artisan migrate --force`.
#      MLHUB không còn Web Installer — luôn giữ APP_INSTALLED=true trên Coolify.
#      Seed dữ liệu mẫu: chạy thủ công `php artisan mlhub:reset-demo --force` (pilot).
#   5. Clear every Laravel cache surface and re-warm against the new code.
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
# 2. Livewire JS — force-sync + refresh mtime so the asset query hash changes
#    after every deploy (browsers must re-fetch instead of replaying stale
#    snapshots that miss new memo keys like "children").
# -----------------------------------------------------------------------------
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

# -----------------------------------------------------------------------------
# 3. Laravel housekeeping
#    Wipe stale compiled state from the PREVIOUS image before regenerating.
#    Without this, Blade views compiled against the old code may keep references
#    to components / props that no longer exist in the new code, which causes
#    runtime errors such as Livewire "Undefined array key 'children'" when the
#    browser replays a snapshot built against the previous component graph.
# -----------------------------------------------------------------------------
find storage/framework/views -mindepth 1 -name '*.php' -delete 2>/dev/null || true
find storage/framework/cache/data -mindepth 1 -type d -empty -delete 2>/dev/null || true
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/routes-v7.php bootstrap/cache/config.php bootstrap/cache/events.php
find bootstrap/cache -maxdepth 1 -name 'livewire-*' -delete 2>/dev/null || true

php artisan package:discover --ansi

# public/storage is often a git placeholder directory (public/storage/.gitignore).
# storage:link refuses to replace a real directory ("link already exists") — remove it first.
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

# Symlink public/storage → storage/app/public (Laravel standard; URLs are /storage/… not /storage/app/public/…)
if ! storage_link_ok; then
    echo "ERROR: public/storage symlink is missing or broken after storage:link." >&2
    ls -la public/ 2>&1 || true
    ls -la "$PUBLIC_STORAGE" 2>&1 || true
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
        echo "Set APP_INSTALLED=true in Coolify Environment Variables, then redeploy."
        ;;
esac

# -----------------------------------------------------------------------------
# 5. Cache refresh
# -----------------------------------------------------------------------------
is_app_installed() {
    case "${1:-false}" in
        true|TRUE|1|yes|on) return 0 ;;
        *) return 1 ;;
    esac
}

if is_app_installed "$APP_INSTALLED_VALUE"; then
    php artisan optimize:clear --ansi
    php artisan view:clear --ansi
    php artisan route:clear --ansi
    php artisan config:clear --ansi
    php artisan event:clear --ansi 2>/dev/null || true
    # Re-warm caches against the NEW codebase. Done after the wipe so no stale
    # compiled artifacts survive across deploys.
    php artisan optimize --ansi
else
    echo "APP_INSTALLED is false: skipping database-backed cache clear. Set APP_INSTALLED=true on Coolify."
    php artisan config:clear --ansi
    php artisan route:clear --ansi
    php artisan view:clear --ansi
    CACHE_STORE=file php artisan cache:clear --ansi
fi

# -----------------------------------------------------------------------------
# 5b. Background queue worker (Redis)
#     QUEUE_CONNECTION=redis → jobs (email, notification, growth-tool notify)
#     cần một worker xử lý, nếu không job sẽ dồn trong Redis và không bao giờ chạy.
#     Chạy bằng www-data, có vòng lặp tự khởi động lại nếu worker thoát.
#     Tắt bằng RUN_QUEUE_WORKER=false (vd khi bạn chạy worker bằng service Coolify riêng).
# -----------------------------------------------------------------------------
RUN_QUEUE_WORKER_VALUE="${RUN_QUEUE_WORKER:-true}"

if is_app_installed "$APP_INSTALLED_VALUE" && is_app_installed "$RUN_QUEUE_WORKER_VALUE"; then
    echo "Starting queue worker (connection=${QUEUE_CONNECTION:-redis})..."
    su -s /bin/sh -c '
        while true; do
            php artisan queue:work --sleep=3 --tries=3 --max-time=3600 --no-interaction || true
            sleep 2
        done
    ' www-data &
else
    echo "Queue worker not started (APP_INSTALLED=${APP_INSTALLED_VALUE}, RUN_QUEUE_WORKER=${RUN_QUEUE_WORKER_VALUE})."
fi

# -----------------------------------------------------------------------------
# 6. Hand off to Apache (or whatever CMD was supplied)
# -----------------------------------------------------------------------------
exec "$@"
