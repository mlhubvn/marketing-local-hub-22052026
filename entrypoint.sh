#!/bin/sh
set -e

cd /var/www/html

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/app/public \
    bootstrap/cache \
    public/livewire

# 1. Cấp quyền sở hữu và quyền ghi cho các thư mục trọng yếu
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Livewire (bỏ qua khi composer --no-scripts trong Docker build)
if [ -f vendor/livewire/livewire/dist/livewire.js ]; then
    cp -f vendor/livewire/livewire/dist/livewire.js public/livewire/livewire.js
    chown www-data:www-data public/livewire/livewire.js 2>/dev/null || true
fi

php artisan package:discover --ansi

# 2. Tạo symlink để public hình ảnh ra ngoài internet (storage/app/public → public/storage)
php artisan storage:link --force

# Theme/CSS/JS nằm trong resources/themes — Apache chỉ phục vụ public/
mkdir -p public/resources
ln -sfn ../../resources/themes public/resources/themes

chown -h www-data:www-data public/storage 2>/dev/null || true
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 3. Chạy migrate tự động (đợi MySQL Coolify sẵn sàng)
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

# 4. Xóa cache cũ để nhận cấu hình môi trường mới
php artisan optimize:clear --ansi
php artisan optimize --ansi

# 5. Khởi động web server (Apache foreground — từ CMD)
exec "$@"
