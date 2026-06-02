# MLHUB — Bộ Prompt & Sổ tay lệnh (Vibecode)

Nơi lưu **prompt mẫu** để làm việc với Cursor và **cheatsheet lệnh** (Laravel / Docker / SSH Coolify) cho dự án MLHUB. Mở file này mỗi khi bắt đầu code, copy prompt phù hợp rồi điền chỗ `<...>`.

> Đọc kèm: `.cursorrules`, `ARCHITECTURE_CHECKLIST.md`, `ARCHITECTURE_BACKEND.md`, `ARCHITECTURE_FRONTEND.md`, `ARCHITECTURE_FEATURE.md`.
> Mọi prompt nên đính kèm `@file` đúng chỗ thay vì `@Codebase` để tiết kiệm tài nguyên.

---

## 1. Mẹo dùng prompt hiệu quả

- **Khoanh vùng hẹp:** `@modules/AppBookingPages/...` thay vì cả dự án.
- **Nói rõ loại việc:** "sửa lỗi" (surgical) hay "tính năng mới" (ưu tiên extension point).
- **Nhắc ngữ cảnh chuẩn:** "theo `.cursorrules` và `ARCHITECTURE_CHECKLIST.md`".
- **Việc rủi ro:** yêu cầu "trình bày kế hoạch trước, chưa code".
- **Một task một mục tiêu:** đừng gộp nhiều việc không liên quan vào một prompt.

---

## 2. Bộ Prompt mẫu (copy & điền)

### 2.1 Khởi động phiên làm việc

```
Bối cảnh: dự án MLHUB (Laravel 13 + Livewire 4, modular monolith). 
Hãy đọc lướt .cursorrules và ARCHITECTURE_CHECKLIST.md trước. 
Hôm nay tôi muốn làm: <mô tả mục tiêu>. 
Hãy xác nhận bạn đã nắm quy trình rồi đề xuất các bước, CHƯA code.
```

### 2.2 Sửa lỗi (Bug fix — surgical)

```
Sửa lỗi trong @<đường-dẫn-file>. 
Hiện tượng: <mô tả lỗi + cách tái hiện>. 
Kỳ vọng: <hành vi đúng>. 
Yêu cầu: sửa tối thiểu, bám đúng phong cách file (vibecode §3), scope theo auth()->id(), 
không refactor ngoài phạm vi. Giải thích nguyên nhân gốc trước khi sửa.
```

### 2.3 Tính năng mới (Feature)

```
Thêm tính năng: <mô tả>. 
Đây là tính năng MỚI nên ưu tiên điểm mở rộng (modules/Custom* + providers.marketplace.php) 
theo ARCHITECTURE_BACKEND.md §5, KHÔNG sửa core nếu không cần. 
Hãy trình bày kế hoạch (file/route/model/migration/permission/env mới) để tôi duyệt, rồi mới code.
```

### 2.4 Một growth tool mới (theo engine lb_campaigns)

```
Tôi muốn thêm growth tool kiểu <tên>. 
Hãy theo đúng mẫu engine dùng chung: model QrCampaign (lb_campaigns) với type='<key>' + settings JSON, 
public qua QrCampaignPublicController, PlanLimitGuard, CustomerUpserter, GrowthToolNotifier 
(xem ARCHITECTURE_FEATURE.md §0). Trình bày kế hoạch trước.
```

### 2.5 Giao diện / Theme (White-label)

```
Chỉnh giao diện <mô tả> ở khu <guest/portal/admin>. 
Lưu ý theme guest `mlhubfrontend`, backend `mlhubbackend` (ARCHITECTURE_FRONTEND.md §2.2). 
Dùng lại <x-ui.*>/<x-shared.*>, màu dùng token var(--theme-*), không hard-code màu. 
Nếu chỉ là branding nhỏ, gợi ý dùng Admin → Themes → Custom CSS/JS thay vì sửa file.
```

### 2.6 Tính năng AI (có credit)

```
Thêm/sửa tính năng AI: <mô tả>. 
Bắt buộc: feature gate → credit_service()->ensureCanConsume() trước khi gọi LLM → 
try/catch (Throwable) có fallback → consume_credits() khi thành công (mẫu AppAIContent). 
Scope lịch sử theo workspaceOwnerUserId().
```

### 2.7 Database migration (🔴 plan trước)

```
Tôi cần thay đổi schema: <mô tả>. 
ĐỪNG code ngay. Hãy trình bày kế hoạch: bảng/cột (lb_*), kiểu dữ liệu, index, ràng buộc, 
chỉ thêm mới/nullable hay có đổi/xóa, phương án rollback, và cách chạy qua pipeline 
(entrypoint.sh / migrate --force), tuyệt đối không migrate:fresh trên production.
```

### 2.8 Payment / Subscription / Credit (🔴 plan trước)

```
Làm việc với cổng thanh toán <PaymentXxx>: <mô tả>. 
Trình bày kế hoạch trước: contract/luồng tiền (checkout→webhook→subscription/credit), 
ca lỗi/hoàn tiền/hết hạn, webhook URL & env cần cấu hình, đảm bảo không log dữ liệu nhạy cảm. 
Test sandbox trước.
```

### 2.9 Rà soát / Audit trước production

```
Audit module @<module> theo ARCHITECTURE_FEATURE.md. 
Kiểm tra: IDOR (scope tenant), thiếu rate-limit/captcha ở endpoint public, XSS khi hiển thị input khách, 
race condition, và xử lý lỗi. Liệt kê rủi ro theo mức P0/P1/P2 + đề xuất fix, CHƯA sửa.
```

### 2.10 Cập nhật tài liệu / sau khi nâng cấp upstream

```
Tôi vừa cập nhật phiên bản tác giả và/hoặc cài module mới. Hãy quét lại codebase (đếm modules/*, 
migration module mới, routes public, env) rồi cập nhật surgical: .cursorrules, Dockerfile, 
docker-compose.yaml, entrypoint.sh, .env.example, ARCHITECTURE_*.md — khớp số liệu thực tế (79 module, 14 Payment*).
```

```
Tôi vừa thay đổi <mô tả>. Hãy cập nhật các file ARCHITECTURE_*.md / .cursorrules liên quan 
cho khớp thực tế (surgical, không viết lại toàn bộ) để Cursor sau này không phải quét lại dự án.
```

### 2.11 Chuẩn bị commit (theo pipeline)

```
Tóm tắt thay đổi của phiên này và soạn commit message đúng style repo. 
Liệt kê file đổi, migration/route/permission/env mới. 
Nhắc tôi nếu có biến .env cần cập nhật trên Coolify. Chưa push cho tới khi tôi đồng ý.
```

---

## 3. Cheatsheet lệnh Laravel (chạy ở LOCAL trên Cursor)

```bash
# Chất lượng code
vendor/bin/pint --dirty              # format file vừa đổi (không đụng core)
vendor/bin/pint <path>               # format file/thư mục cụ thể
php artisan test                     # chạy toàn bộ test (Pest)
php artisan test --filter=<Tên>      # chạy 1 nhóm test

# Dev server (dự án có script gộp)
composer dev                         # serve + queue:listen + vite cùng lúc
php artisan serve                    # chỉ web server
php artisan queue:listen --tries=1   # queue (driver redis)

# Database (MySQL local)
php artisan migrate                  # áp migration
php artisan migrate:status           # xem trạng thái
php artisan migrate --pretend        # xem SQL sẽ chạy, KHÔNG thực thi
php artisan db:seed --class=<Seeder> # seed dữ liệu mẫu

# Cache / cấu hình (khi dev)
php artisan optimize:clear           # xóa mọi cache
php artisan config:clear
php artisan route:clear
php artisan view:clear               # sau khi sửa Blade
php artisan route:list --name=portal # tra route theo tên

# Module / autoload
composer dump-autoload               # sau khi thêm namespace/class mới
php artisan about                    # tổng quan môi trường

# Tinker (thử nghiệm nhanh, đọc dữ liệu)
php artisan tinker
```

> ⚠️ KHÔNG chạy `migrate:fresh`, `migrate:rollback`, `db:wipe` trên DB có dữ liệu thật.

### 3.1 Reset dữ liệu demo (không còn Web Installer)

> 🔴 **Chỉ pilot/staging/local — chưa có khách thật.** MLHUB **đã gỡ Web Installer**; bootstrap qua **seed** + env Coolify.

**Tài khoản duy nhất cần nhớ:** `demo@mlhub.vn` / `123456` — vừa **super admin**, vừa có **demo tăng trưởng** (gói `agency-lifetime`).

**Coolify (tab Environment Variables) — luôn giữ:**


| Biến                          | Giá trị                                                                     |
| ----------------------------- | --------------------------------------------------------------------------- |
| `APP_INSTALLED`               | `true` (bắt buộc — entrypoint mới chạy `migrate`)                           |
| `MLHUB_ADMIN_PLAN_SLUG`       | `agency-lifetime`                                                           |
| `MLHUB_ALLOW_RESET_DEMO`      | `true` (chỉ khi cần chạy lệnh wipe trên pilot; xong có thể đặt lại `false`) |
| `MLHUB_LICENSE_PURCHASE_CODE` | Mã license Stackposts (Coolify — không commit)                              |
| `MLHUB_LICENSE_DOMAIN`        | `mlhub.vn`                                                                  |
| `RUN_QUEUE_WORKER`            | `true` (entrypoint start worker; `false` nếu worker Coolify riêng)           |
| `MAIL_PASSWORD`               | SMTP (không commit vào repo; seed ghi vào `options.smtp_password` nếu có)   |


**License / Marketplace sau reset:** không còn Web Installer (`purchase_verify_url`). Seed `MLHUBMarketplaceSeeder` + `license_`* trong `MLHUBBootstrapSeeder` khôi phục `marketplace_packages` và trạng thái license. **Không** import nguyên `mysql-dump-default-*.sql` (chứa SMTP/captcha secret). Logo đã upload: `MLHUBBrandFilesSeeder` tái tạo bản ghi `files` nếu ảnh còn trên disk `storage/app/public`.

#### A. Combo một lệnh (trong container app — khuyến nghị)

```bash
docker exec -it <container_app> sh
cd /var/www/html
php artisan mlhub:reset-demo --force
redis-cli -h <redis-host> -a '<password>' FLUSHALL
```

Lệnh trên: `db:wipe` → `migrate` → `db:seed` (foundation, license, marketplace, demo VN + volume) → `**admin-faker:refresh**` (demo investor Đà Nẵng SOHO: 10 business, 32 QR campaign, FAQ/blog/support — xem `ARCHITECTURE_ADMINFAKER.md`; không LinkBio/publishing) → `MLHUBDemoExtrasSeeder` (email templates, template packs) → `optimize:clear`. **Không** seed `files` (logo upload tay). Chỉ chạy `db:seed` / reset mà không có bước Admin Faker ≈ dump `mysql-moi.sql` (thiếu ~27 bảng so với `mysql-cu.sql` sau Faker).

#### B. Từng bước (nếu muốn kiểm soát)

```bash
cd /var/www/html
php artisan db:wipe --force --drop-views
php artisan migrate --force
php artisan db:seed --force
php artisan optimize:clear
```

**Redis (bắt buộc sau reset):** `redis-cli FLUSHALL` — session/cache cũ không còn trỏ user ID đã xóa.

#### C. SQL thủ công (khi không vào được artisan)

```sql
DROP DATABASE mlhub;
CREATE DATABASE mlhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Sau đó trong container: `php artisan migrate --force` và `php artisan db:seed --force` (hoặc `mlhub:reset-demo --force`).

#### D. Chỉ seed lại demo (không wipe)

```bash
php artisan db:seed --class=LocalBoostDemoSeeder --force
php artisan optimize:clear
```

#### E. Sau reset — kiểm tra

- Đăng nhập `**demo@mlhub.vn**` / `**123456**` → **Admin** + **Portal** đều được.
- **Tổng quan tăng trưởng:** visits ~1.2k–4.8k/chiến dịch; tỷ lệ chuyển đổi ~8–10%.

**Cấu hình:** `mlhub_adminfaker_dn_soho.php` — **11** cơ sở, `target_qr_visits` **6M** (~60%), `customer_target` **6600**, `volume_scale` **12**, FAQ/blog **150** mỗi loại.

> Quy ước repo: không giữ script one-off/generator trong `database/seeders/scripts` hoặc `database/seeders/data` nếu không cần runtime seed. Ưu tiên chỉnh trực tiếp file data runtime để dễ compare với upstream.

### 3.2 Tải full source từ server (`fullcode.zip`) — pilot / backup

> Dùng khi cần tải **toàn bộ thư mục app đang chạy** trên Coolify (`/var/www/html`) về máy — gồm code, `vendor/`, `modules/`, `storage/` (upload/cache/log trên container), theme, v.v. **Source of truth vẫn là GitHub** — zip chỉ để backup/so sánh tạm, không thay commit/push.

> 🔴 **Bảo mật:** File đặt trong `public/` = **URL công khai**. Ai biết link `https://mlhub.vn/fullcode.zip` đều tải được. **Tạo → tải xong → xóa ngay** (bước 4). Không để qua đêm trên production.

**“Full” nghĩa là gì:** Nén **gần như mọi thứ** trong `/var/www/html`. Chỉ **không** đưa vào zip: (1) chính file `public/fullcode.zip` đang tạo, (2) file `.env` (chứa mật khẩu — bí mật thật nằm tab Environment Variables của Coolify). Các file khác (`vendor`, `storage`, `bootstrap/cache`, `.env.example`, …) **đều nằm trong zip**.

Mỗi lần container khởi động, `entrypoint.sh` đã tạo symlink `public/resources/themes` → `../../resources/themes`. Lệnh zip dùng cờ **`-y`** để không đi theo symlink lặp vô hạn (tránh zip phình 500MB+ vì `themes/themes/...`).

**Bước 1 — Vào container app (Coolify → Terminal hoặc `docker exec`):**

```bash
docker exec -it <container_app> sh
cd /var/www/html
```

Nếu báo `zip: not found`:

```bash
apt-get update && apt-get install -y zip
```

**Bước 2 — Nén full (một lệnh):**

```bash
zip -ry public/fullcode.zip . -x "public/fullcode.zip" -x ".env"
```

- **`-r`**: nén đệ quy toàn bộ thư mục hiện tại.
- **`-y`**: bỏ qua symlink khi ghi zip (theme vẫn đủ vì có `resources/themes/`).
- Kích thước thường **~200MB–1GB+** (có `vendor` + `storage`), tạo **vài phút** — đợi đến khi shell trả về prompt, không thoát giữa chừng.
- Sau khi xong, kiểm tra nhanh:

```bash
ls -lh public/fullcode.zip
```

**Bước 3 — Tải bằng trình duyệt:**

`https://mlhub.vn/fullcode.zip` (hoặc `https://www.mlhub.vn/fullcode.zip`)

**Bước 4 — Xóa file ngay sau khi tải xong (bắt buộc):**

```bash
rm -f /var/www/html/public/fullcode.zip
ls -la /var/www/html/public/fullcode.zip
# phải báo: No such file or directory
```

**Khắc phục khi zip quá lớn bất thường (hàng GB, log có `themes/themes/themes/...`):**

Container lỗi symlink (hiếm nếu đã deploy bản có `entrypoint.sh` mới). **Redeploy** Coolify rồi chạy lại bước 2, hoặc sửa tay:

```bash
rm -rf public/resources/themes
mkdir -p public/resources
ln -sfn ../../resources/themes public/resources/themes
zip -ry public/fullcode.zip . -x "public/fullcode.zip" -x ".env"
```

**Lưu ý:**

- Giải nén trên Windows: chuột phải `fullcode.zip` → Extract All (hoặc 7-Zip). Trên máy dev: `unzip fullcode.zip -d thu-muc-moi`.
- Máy local nếu Git báo hàng chục nghìn file `public/resources/themes/themes/...` sau khi giải nén: **đừng commit** — xóa thư mục lỗi đó; so sánh code dùng bản từ GitHub.
- Không commit `public/fullcode.zip` lên GitHub.
- `storage/` trên Coolify gắn volume `mlhub-storage` — khi zip **trong container** tại `/var/www/html`, phần `storage/` đang mount **vẫn được nén** (upload khách, log, cache…). Backup volume riêng: snapshot Persistent Storage trên Coolify.
- Production thường **không có** `.git/` trong image — zip vẫn đủ để chạy/so sánh; lịch sử git lấy từ repo GitHub.

---

## 4. Cheatsheet Docker / SSH Coolify (CHỈ để CHẨN ĐOÁN — đọc log/kiểm tra)

> 🔴 **Quy tắc cốt lõi (ARCHITECTURE_CHECKLIST.md §1):** KHÔNG dùng các lệnh này để **thay đổi** hạ tầng/code/config trên server. Mọi thay đổi đi qua local → GitHub → Coolify build. Các lệnh dưới đây chỉ để **xem trạng thái, đọc log, debug**.

Tham chiếu container (từ `docker-compose.yaml`): tên dạng `mggu45o8q8aso8stbepn622j-...`, app chạy port 80 nội bộ, volume `mlhub-storage:/var/www/html/storage`, domain `mlhub.vn`.

```bash
# SSH vào server (thực hiện trong Coolify hoặc terminal được cấp quyền)
ssh <user>@<server-ip>

# Xem container đang chạy
docker ps
docker ps --filter "name=mlhub"      # lọc theo tên dự án

# Đọc log ứng dụng (chẩn đoán lỗi deploy/runtime)
docker logs --tail=200 -f <container_id_or_name>

# Vào shell container để XEM (không sửa)
docker exec -it <container_id_or_name> sh      # hoặc bash nếu có
#   bên trong container, dùng để ĐỌC trạng thái:
php artisan about
php artisan migrate:status
tail -n 200 storage/logs/laravel.log
cat .env | grep -v PASSWORD          # xem cấu hình (tránh lộ secret)

# Tài nguyên / dung lượng
docker stats --no-stream
df -h
```

### Khi gặp sự cố trên production → làm gì?

1. **Đọc log** (`docker logs`, `storage/logs/laravel.log`) để xác định nguyên nhân.
2. **Tái hiện ở local**, sửa bằng code/config, test, commit, push.
3. Để **Coolify auto-build** lại. KHÔNG vá tay trên container.
4. Nếu là biến môi trường: sửa trong **Coolify UI (env)** + cập nhật `.env.example` ở repo cho khớp.
5. Nếu cần chạy migration khi deploy: đảm bảo nằm trong `entrypoint.sh`/pipeline.

### 4.1 Xem & tải `laravel.log` an toàn (module AdminLog)

> ✅ **Dùng trang admin, KHÔNG copy log ra `public/`.** Xem/tải log tại **Admin → Cài đặt → Logs** (`https://mlhub.vn/admin/settings/log`). Trang này nằm sau `auth` + `EnsureAdminAccess` (chỉ admin), tránh phơi log ra Internet.
>
> 🔴 **TUYỆT ĐỐI KHÔNG** copy `storage/logs/*.log` vào `public/` để tải qua URL công khai — log chứa stack trace, DB host, user ID, email, token; ai có URL (kể cả Google) đều đọc được.

Tại trang đó có thể: chọn file log, xem nhanh phần cuối (tail), **tải xuống**, **xoá nội dung** (clear) hoặc **xoá file**. Code: `modules/AdminLog`.

---

## 5. Tham chiếu nhanh môi trường MLHUB


| Hạng mục            | Giá trị                                                                                                          |
| ------------------- | ---------------------------------------------------------------------------------------------------------------- |
| Brand / domain      | MLHUB / `mlhub.vn` (+ `www`)                                                                                     |
| Locale / timezone   | `vi` / `Asia/Ho_Chi_Minh`                                                                                        |
| DB                  | MySQL (3306)                                                                                                     |
| Session/Queue/Cache | `redis` (phpredis; cần queue worker + Redis sống)                                                                |
| Mail                | ✅ `smtp` qua Emailit (`smtp.emailit.com:587`, secret trong Coolify env)                                          |
| Captcha             | Cloudflare Turnstile (mặc định) + reCAPTCHA v2 — Admin → Captcha (OptionStore); gắn ở auth, chưa gắn form public |
| Rate-limit          | `throttle:10,1` trên 5 form public (booking/coupon/feedback/lead/review)                                         |
| Storage             | disk `public` (S3 trống)                                                                                         |
| Theme active        | guest = `mlhubtheme`, backend = `default`                                                                        |
| Deploy              | Coolify + Traefik (HTTP→HTTPS, Let's Encrypt)                                                                    |


---

## 6. Sổ tay cá nhân (tự ghi note mỗi lần code)

> Khu vực để bạn tự bổ sung. Gợi ý ghi theo định dạng ngày + việc + lệnh/đường dẫn liên quan.

### DD-MM-YYYY — <tiêu đề việc>

- Bối cảnh / mục tiêu:
- File/module liên quan: `@...`
- Lệnh đã dùng:
- Kết quả / lưu ý / cạm bẫy gặp phải:
- Việc cần làm tiếp:

## 7. Lựa chọn mô hình để vibecode

### 1. Giữ nguyên **Opus 4.8 High (Core)**

- **Khi nào dùng:** Dành cho các tác vụ cốt lõi mà chúng ta vừa bàn tới (Cấu hình luồng Email/SMTP, Viết middleware Rate-limit/Captcha chống Spam, hoặc xử lý Race-condition cho Booking).
- **Lý do:** Dòng Opus luôn là "nhà vô địch" trong việc đọc hiểu ngữ cảnh dài. Nó sẽ nuốt trọn bộ `ARCHITECTURE_*.md` của bạn, nhớ rất kỹ các quy tắc an toàn (không dùng `migrate:fresh` trên production, luôn scope theo `auth()->id()`), và đưa ra kế hoạch (Plan) cực kỳ sắc bén trước khi code.

### 2. Dùng **Composer 2.5 Fast (Giải pháp)**

- **Khi nào dùng:** Khi bạn đã thảo luận xong giải pháp với Opus và chốt được phương án, hãy dùng tính năng Composer (phím tắt thường là `Cmd/Ctrl + I` hoặc `Cmd/Ctrl + K` trên toàn dự án) để AI tự động áp dụng các thay đổi đó vào nhiều file cùng lúc (ví dụ: chèn throttle vào hàng loạt file `Routes/web.php` của các module).
- **Lý do:** Tốc độ thực thi cực nhanh và gõ code trực tiếp vào file (surgical edits) rất tốt.

### 3. Dùng **Sonnet 4.6 Medium** hoặc **GPT-5.5 Medium (Theme)**

- **Khi nào dùng:** Dành cho team của bạn khi làm các tác vụ nhẹ nhàng hơn. Ví dụ: Giang nhờ AI căn chỉnh lại CSS Tailwind trên theme `mlhubtheme`, hoặc nhờ AI viết vài đoạn regex để kiểm tra định dạng số điện thoại.
- **Lý do:** Tiết kiệm "Premium credits" của bạn, tốc độ phản hồi nhanh hơn Opus, và dư sức xử lý các file đơn lẻ.

