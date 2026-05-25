# Hướng dẫn deploy LinkQR Pro / LocalBoost AI lên Coolify (từng bước)

Tài liệu này hướng dẫn đưa codebase Laravel 13 lên **Coolify** qua **GitHub**, build bằng **Dockerfile** (theo mô hình [AZDIGI – Laravel Coolify CI/CD](https://azdigi.com/blog/kien-thuc-website/laravel-coolify-ci-cd)).

**Đã có sẵn trong repo:**

- `Dockerfile` — PHP 8.3 + Apache, MySQL + PostgreSQL
- `entrypoint.sh` — quyền thư mục, migrate, cache, khởi động Apache

---

## Mục lục

1. [Chuẩn bị trước khi deploy](#1-chuẩn-bị-trước-khi-deploy)
2. [Đẩy code lên GitHub](#2-đẩy-code-lên-github)
3. [Cài Coolify trên VPS](#3-cài-coolify-trên-vps)
4. [Tạo MySQL trên Coolify](#4-tạo-mysql-trên-coolify)
5. [Tạo Application từ GitHub](#5-tạo-application-từ-github)
6. [Cấu hình Build (Dockerfile)](#6-cấu-hình-build-dockerfile)
7. [Biến môi trường (.env trên Coolify)](#7-biến-môi-trường-env-trên-coolify)
8. [Volume & Health check](#8-volume--health-check)
9. [Deploy lần đầu](#9-deploy-lần-đầu)
10. [Chạy Web Installer](#10-chạy-web-installer)
11. [Gắn domain & SSL](#11-gắn-domain--ssl)
12. [Bật CI/CD (auto deploy khi push GitHub)](#12-bật-cicd-auto-deploy-khi-push-github)
13. [Xử lý lỗi thường gặp](#13-xử-lý-lỗi-thường-gặp)
14. [Checklist sau khi lên production](#14-checklist-sau-khi-lên-production)

---

## 1. Chuẩn bị trước khi deploy

### Yêu cầu kỹ thuật

| Hạng mục | Giá trị |
|----------|---------|
| PHP | **8.3+** (theo `composer.json`) |
| Framework | Laravel **13** |
| Web server trong container | Apache, document root `public/` |
| Cổng container | **80** |
| Database (theo `.env` của bạn) | **MySQL** |

### Lưu ý bảo mật (quan trọng)

- **Không commit** file `.env` lên GitHub.
- Bạn đã chia sẻ `APP_KEY` trong chat — nên **tạo key mới** trên server production:

  ```bash
  php artisan key:generate --show
  ```

  Dán giá trị mới vào Coolify → Environment Variables → `APP_KEY`.

- `APP_DEBUG` trên production phải là **`false`**.

### Luồng cài đặt ứng dụng

Với `APP_INSTALLED=false`, lần đầu truy cập site sẽ vào **trình cài đặt web** (`/installer`), không phải dashboard ngay. Sau khi hoàn tất installer, hệ thống ghi `APP_INSTALLED=true` vào `.env` và bảng `options`.

---

## 2. Đẩy code lên GitHub

### Bước 2.1 — Kiểm tra file Docker

Đảm bảo repo có:

```
Dockerfile
entrypoint.sh   (chmod +x)
```

Trên Windows (Git Bash / WSL):

```bash
chmod +x entrypoint.sh
git update-index --chmod=+x entrypoint.sh
```

### Bước 2.2 — Tạo `.gitignore` (nếu chưa có)

Đảm bảo **không** track:

```
.env
.env.*
/vendor
/node_modules
/storage/*.key
/storage/logs/*
```

### Bước 2.3 — Push lên GitHub

```bash
git init
git add .
git commit -m "Add Coolify Dockerfile and deployment docs"
git branch -M main
git remote add origin https://github.com/<user>/<repo>.git
git push -u origin main
```

- Repo **private**: kết nối GitHub với Coolify (Settings → Git Providers).
- Repo **public**: chỉ cần dán URL repo khi tạo app.

---

## 3. Cài Coolify trên VPS

1. Thuê VPS (Ubuntu 22.04/24.04 khuyến nghị, tối thiểu **2 GB RAM** cho Laravel + MySQL).
2. Trỏ domain (tuỳ chọn) `coolify.tenban.com` → IP VPS.
3. Cài Coolify theo tài liệu chính thức: [https://coolify.io/docs](https://coolify.io/docs)
4. Mở panel Coolify, tạo tài khoản admin.

---

## 4. Tạo MySQL trên Coolify

Ứng dụng của bạn dùng `DB_CONNECTION=mysql`. Tạo database **trên cùng server Coolify** (không dùng `localhost` trong container app).

### Bước 4.1 — Tạo Database service

1. Vào **Project** → **+ New** → **Database** → **MySQL** (hoặc MariaDB).
2. Đặt tên, ví dụ: `linkqr-mysql`.
3. Ghi lại:
   - **Root password** / user password
   - **Database name** (tạo mới, ví dụ: `linkqr_pro`)
   - **Internal hostname** (Coolify hiển thị sau khi tạo), thường dạng:  
     `linkqr-mysql` hoặc tên service trong mạng Docker.

### Bước 4.2 — User & quyền

- User: ví dụ `linkqr_user`
- Password: mật khẩu mạnh
- Database: `linkqr_pro`
- Quyền: full trên database đó

> **Lưu ý:** Trong container Laravel, `DB_HOST=localhost` **không** trỏ tới MySQL Coolify. Phải dùng **hostname nội bộ** do Coolify cấp cho service MySQL.

---

## 5. Tạo Application từ GitHub

1. **Dashboard** → chọn **Project** (tạo mới nếu chưa có).
2. **+ New Resource** → **Application**.
3. Chọn nguồn:
   - **Public Repository**: dán URL GitHub  
   - **Private Repository**: chọn repo sau khi đã kết nối GitHub
4. Chọn **Branch**: `main` (hoặc branch bạn deploy).
5. Chọn **Server** chạy ứng dụng.

---

## 6. Cấu hình Build (Dockerfile)

Không dùng Nixpacks — chọn **Dockerfile** (giống bài AZDIGI).

| Trường | Giá trị |
|--------|---------|
| Build Pack | **Dockerfile** |
| Dockerfile location | `Dockerfile` (root) |
| Build context | `/` (root repo) |
| Port Exposes | **80** |
| Port Mappings | `80` (Coolify proxy HTTPS ra ngoài) |

### Pre-deploy (tuỳ chọn)

Nếu sau này có nhiều replica, có thể tắt `migrate` trong `entrypoint.sh` và chỉ chạy migrate ở đây:

```bash
php artisan migrate --force
```

Lần đầu: để `entrypoint.sh` chạy migrate là đủ.

---

## 7. Biến môi trường (.env trên Coolify)

Vào Application → **Environment Variables** → **Production** (hoặc môi trường bạn deploy).

**Không upload file `.env` lên Git.** Copy từng biến dưới đây (đã chỉnh cho production / Coolify).

### 7.1 — Application

```env
APP_NAME="LinkQR Pro"
APP_ENV=production
APP_KEY=base64:XXXXXXXX
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_INSTALLED=false
APP_DEMO=false

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
APP_TIMEZONE=UTC

BCRYPT_ROUNDS=12
```

| Biến | Ghi chú |
|------|---------|
| `APP_URL` | **Bắt buộc** đổi thành URL thật (https://domain hoặc URL sslip.io của Coolify). Không dùng `http://127.0.0.1`. |
| `APP_KEY` | Dùng key mới generate; có `base64:` ở đầu. |
| `APP_INSTALLED` | Để `false` lần đầu → chạy wizard `/installer`. |

### 7.2 — Database (MySQL trên Coolify)

Thay bằng thông tin service MySQL ở [mục 4](#4-tạo-mysql-trên-coolify):

```env
DB_CONNECTION=mysql
DB_HOST=linkqr-mysql
DB_PORT=3306
DB_DATABASE=linkqr_pro
DB_USERNAME=linkqr_user
DB_PASSWORD=your_strong_password
```

> Coolify đôi khi có nút **"Connect to Database"** / inject biến — ưu tiên dùng hostname nội bộ Coolify gợi ý.

### 7.3 — Session & cache (trước khi cài xong installer)

Giữ như local lúc chưa cài:

```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_COOKIE=stackposts_installer_session
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=null

CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
```

Sau khi **hoàn tất Web Installer**, installer có thể chuyển session/cache sang `database` (theo `app/Installer/config/installer.php`). Không cần sửa tay nếu wizard chạy thành công.

### 7.4 — Log, mail, Redis

```env
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

BROADCAST_CONNECTION=log

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS=hello@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MEMCACHED_HOST=127.0.0.1
```

Production nên cấu hình SMTP thật (Resend, Mailgun, v.v.) sau khi site chạy ổn.

### 7.5 — AWS (tuỳ chọn)

```env
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### 7.6 — SEO / site meta (theo file env của bạn)

```env
VITE_APP_NAME="${APP_NAME}"

SITE_TITLE="LinkQR Pro - Bio Link Builder, Dynamic QR Codes & Campaign Analytics SaaS"
SITE_DESCRIPTION="LinkQR Pro is a SaaS platform for creating branded bio link pages, dynamic QR codes, short links, custom domains, and campaign analytics for creators, teams, and businesses."
SITE_KEYWORDS="bio link builder, link in bio, dynamic QR codes, QR code generator, campaign analytics, short links, custom domains, branded links, QR analytics, LinkQR Pro, SaaS"
```

### 7.7 — Nếu dùng Supabase (PostgreSQL) thay MySQL

Đổi nhóm DB:

```env
DB_CONNECTION=pgsql
DB_HOST=db.xxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_password
DB_SSLMODE=require
```

`Dockerfile` hiện đã cài cả `pdo_mysql` và `pdo_pgsql`.

---

## 8. Volume & Health check

### Persistent storage (khuyến nghị)

Mount volume để upload / log không mất khi redeploy:

| Path trong container | Mục đích |
|----------------------|----------|
| `/var/www/html/storage` | Upload, log, cache file |
| `/var/www/html/bootstrap/cache` | (tuỳ chọn) cache config |

Trong Coolify: **Storages** → Add Volume → map path trên.

### Health check

| Trường | Giá trị |
|--------|---------|
| Path | `/up` |
| Port | `80` |
| Method | GET |

Laravel khai báo route health tại `bootstrap/app.php`.

---

## 9. Deploy lần đầu

1. Kiểm tra lại **Environment Variables** và **Port 80**.
2. Bấm **Deploy** (hoặc **Redeploy**).
3. Mở tab **Deployments** — đợi build Docker:
   - Stage Composer: `composer install --no-dev`
   - Stage PHP-Apache: cài extension, copy code
4. Khi **Running**, mở tab **Logs** (runtime):
   - `php artisan migrate --force`
   - `php artisan optimize`
   - Apache `apache2-foreground`

### URL tạm

Coolify cấp URL dạng: `http://xxxxx.IP-SERVER.sslip.io` — dùng làm `APP_URL` tạm cho đến khi gắn domain.

---

## 10. Chạy Web Installer

1. Mở trình duyệt: `https://<APP_URL>/installer`
2. Làm lần lượt các bước wizard:
   - Kiểm tra PHP extensions (Dockerfile đã cài đủ)
   - Kết nối database (MySQL Coolify)
   - **Purchase code** (bắt buộc theo config — marketplace StackPosts)
   - Tạo tài khoản admin
   - Hoàn tất cài đặt
3. Sau khi xong: `APP_INSTALLED=true` trong `.env` container và có bản ghi `installer_completed_at` trong DB.

Nếu bị redirect vòng lặp:

- Kiểm tra `APP_URL` đúng scheme `https://`
- Kiểm tra `DB_*` và migrate đã chạy (bảng `options` tồn tại)
- Xem log: `storage/logs/laravel.log` (trong volume)

---

## 11. Gắn domain & SSL

1. Application → **Domains** → thêm domain, ví dụ `app.linkqr.pro`.
2. DNS: record **A** (hoặc CNAME) trỏ về IP VPS Coolify.
3. Bật **HTTPS** / Let's Encrypt trên Coolify.
4. Cập nhật biến môi trường:

   ```env
   APP_URL=https://app.linkqr.pro
   ```

5. **Redeploy** hoặc chạy trong container:

   ```bash
   php artisan optimize:clear
   php artisan optimize
   ```

---

## 12. Bật CI/CD (auto deploy khi push GitHub)

Theo [hướng dẫn AZDIGI](https://azdigi.com/blog/kien-thuc-website/laravel-coolify-ci-cd):

1. Application → **Webhooks** / **Auto Deploy** → bật deploy khi push.
2. Trên GitHub → repo → **Settings** → **Webhooks** → thêm URL Coolify (nếu Coolify yêu cầu secret, copy từ panel).
3. Chọn branch `main` (hoặc branch production).
4. Quy trình sau mỗi lần `git push`:
   - GitHub báo Coolify
   - Coolify clone → build Dockerfile → chạy container mới
   - `entrypoint.sh` chạy migrate + optimize

**Khuyến nghị:** không commit `.env`; chỉ đổi biến trên Coolify UI.

---

## 13. Xử lý lỗi thường gặp

| Triệu chứng | Nguyên nhân | Cách xử lý |
|-------------|-------------|------------|
| HTTP 500 ngay sau deploy | Thiếu / sai `APP_KEY` | Thêm `APP_KEY` hợp lệ trên Coolify, redeploy |
| 500 / không kết nối DB | `DB_HOST=localhost` trong container | Đổi `DB_HOST` = hostname MySQL Coolify |
| Build fail Composer | Thiếu `composer.lock` | Commit `composer.lock` lên repo |
| Permission denied `storage` | Quyền thư mục | Mount volume; `entrypoint.sh` đã `chmod 775` |
| Trang trắng / 404 route | `mod_rewrite` / document root | Dockerfile đã trỏ `public/` và bật rewrite |
| Installer không mở | `APP_INSTALLED=true` nhầm | Đặt `false` cho đến khi cài xong |
| Migrate lỗi | DB chưa sẵn sàng | Đợi MySQL Running; kiểm tra user/password |
| Session / CSRF lỗi sau HTTPS | `APP_URL` http vs https | Đồng bộ `APP_URL` với domain SSL |
| Upload mất sau redeploy | Không mount volume | Gắn volume `storage/` |

### Xem log

- Coolify → Application → **Logs** (build + runtime)
- Trong container:

  ```bash
  tail -f storage/logs/laravel.log
  ```

---

## 14. Checklist sau khi lên production

- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] `APP_KEY` mới, không lộ trên GitHub
- [ ] `APP_URL` = domain HTTPS thật
- [ ] MySQL Coolify: host nội bộ, DB/user/password đúng
- [ ] Volume `storage` đã mount
- [ ] Health check `/up` pass
- [ ] Web Installer hoàn tất, `APP_INSTALLED=true`
- [ ] Đăng nhập admin OK
- [ ] SMTP cấu hình (nếu cần gửi mail)
- [ ] Backup database định kỳ (Coolify / provider)
- [ ] (Tuỳ chọn) Worker queue: service riêng `php artisan queue:work` nếu đổi `QUEUE_CONNECTION` khỏi `sync`

---

## Tóm tắt quy trình (1 trang)

```
Local code → git push GitHub
    → Coolify: New App (GitHub, Dockerfile, port 80)
    → Coolify: MySQL service + env DB_*
    → Coolify: env APP_* (URL, KEY, INSTALLED=false)
    → Deploy → mở /installer → hoàn tất cài đặt
    → Gắn domain + SSL → cập nhật APP_URL → redeploy
    → Bật webhook → push code = auto deploy
```

---

## Tham khảo

- [AZDIGI: Deploy Laravel lên Coolify CI/CD](https://azdigi.com/blog/kien-thuc-website/laravel-coolify-ci-cd)
- [Coolify Documentation](https://coolify.io/docs)
- File trong repo: `Dockerfile`, `entrypoint.sh`, `.cursorrules`, `ARCHITECTURE_BACKEND.md`

*Nếu cần deploy bằng Supabase thay MySQL, chỉ đổi nhóm biến `DB_*` sang `pgsql` (mục 7.7).*
