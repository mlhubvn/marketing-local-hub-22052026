# MLHUB AI — Quy trình làm việc & Checklist (Vibecode)

Tài liệu quy trình vận hành chuẩn cho dự án **MLHUB** khi làm việc với Cursor. Mục tiêu: code **ổn định, đúng phong cách lập trình viên gốc, an toàn cho production, và tiết kiệm tài nguyên đọc lại dự án**.

> Đọc kèm: `.cursorrules` (luật cứng), `ARCHITECTURE_BACKEND.md`, `ARCHITECTURE_FRONTEND.md`, `ARCHITECTURE_MODULE.md` (route/bảng/model/plan từng module), `ARCHITECTURE_FEATURE.md` (độ sẵn sàng/backlog), `ARCHITECTURE_PROMPT.md` (prompt mẫu dùng chay + cheatsheet lệnh).
> **Bắt buộc:** đọc lướt file này trước khi bắt tay vào bất kỳ Task / tính năng mới nào.

> **Workflow chuẩn (dùng chay Cursor):** việc lớn/rủi ro → **Plan → Duyệt → Code → Verify → Review**; bug → **Evidence/log → root cause → surgical fix → verify**; việc nhỏ → surgical ngay với `@file`. Superpowers (nếu bật) chỉ **tùy chọn**, không bắt buộc.

### 0. Phân loại task theo quy mô

| Quy mô | Ví dụ | Quy trình |
| --- | --- | --- |
| **Nhỏ** | 1 chuỗi i18n, 1 format số, sửa 1 Blade, đổi nhãn menu | Surgical ngay với `@file` → format → báo cáo. Không cần plan dài. |
| **Vừa** | Thêm 1 field, 1 Livewire action, 1 validation, 1 view module | Khoanh vùng module → code vibecode → verify §2.C/D → báo cáo. |
| **Lớn / rủi ro** | Migration/schema, payment/credit, xóa dữ liệu, refactor core, module mới | **BẮT BUỘC plan trước + chờ "Duyệt"** (§3) → code → verify → review. |

---

## 1. Nguyên tắc chung — Luồng triển khai (Deployment Pipeline)

```
   [1] Development                [2] Source of Truth            [3] Production / Staging
   ┌───────────────┐  commit/push ┌───────────────┐  webhook   ┌──────────────────────────┐
   │  Cursor IDE   │ ───────────► │    GitHub     │ ─────────► │  Coolify (auto build)     │
   │  (code+config)│              │  (main branch)│            │  docker-compose + .env    │
   └───────────────┘              └───────────────┘            │  + Traefik (HTTP→HTTPS)    │
                                                               └──────────────────────────┘
```

1. **Development**: Viết code & cấu hình **trực tiếp tại local trên Cursor IDE**.
2. **Source of Truth**: Mọi thay đổi **phải** được commit & push lên **GitHub** (nhánh chính). GitHub là nguồn sự thật duy nhất.
3. **Production/Staging**: **Coolify** tự động nhận webhook từ GitHub, build lại môi trường bằng `docker-compose.yaml` + `.env`, định tuyến qua **Traefik** (HTTP→HTTPS, Let's Encrypt), domain `mlhub.vn` / `www.mlhub.vn`.

> ⚠️ **Dự án ĐÃ CHẠY PRODUCTION (live, có người dùng thật).** Ưu tiên tuyệt đối: an toàn dữ liệu, bảo mật, trải nghiệm người dùng.
>
> **Phân chia trách nhiệm:** AI chỉ **sửa code/config tại local** + soạn **commit message gợi ý**. Bước `**git commit` / `git push` / Redeploy là do chủ dự án tự làm thủ công** (để kịp copy log khi lỗi). AI **không** tự commit/push/deploy. Nếu cần cấu hình Coolify, AI hướng dẫn theo từng tab (General, Environment Variables, Scheduled Tasks, …). Khi phát hiện rủi ro bảo mật/UX (kể cả ngoài task) → **báo ngay**.

### 1.1 Quy tắc giữ cấu trúc gốc khi nâng cấp phiên bản

- Mặc định sửa trực tiếp file có sẵn; tránh tạo file/class/module logic mới.
- Khi làm tính năng, ưu tiên mở rộng từ file/module tạo sẵn trước khi nghĩ tới file mới.
- Nếu có file mới phát sinh cho logic, phải tự đánh dấu và lên kế hoạch gộp vào file cũ trước khi bàn giao.
- Nếu buộc phải tạo file logic mới, ưu tiên đặt trong `modules/Custom...` theo đúng khu vực cần fix để dễ quản lý diff.
- File mới chỉ chấp nhận cho dữ liệu seed/doc nội bộ có lý do rõ ràng.
- Thương hiệu code/file: **chỉ** `MLHUB` (class, constant, env, UI) hoặc `mlhub` (path, route, command, domain, config key). **Cấm** `Mlhub` / `MLHub` / mix-case — xem `.cursorrules` §2.0.

### ⛔ Quy tắc bất di bất dịch về hạ tầng

- **TUYỆT ĐỐI KHÔNG** đề xuất can thiệp thủ công bằng dòng lệnh trực tiếp trên server Coolify (SSH, sửa file trên container, chạy lệnh tay…), ngoại trừ lệnh cài đặt một lần `mlhub:install` đã được chủ dự án phê duyệt rõ ràng.
- **Mọi thay đổi hạ tầng phải thể hiện bằng code/config tại local**: sửa `docker-compose.yaml`, `Dockerfile`, `docker/entrypoint.sh`, biến trong `.env`/`.env.example`, hoặc migration trong repo → commit → push → để Coolify tự build.
- Lệnh artisan cần chạy khi deploy (vd `migrate --force`, `config:cache`) phải nằm trong `docker/entrypoint.sh` / quy trình build, **không** chạy tay trên server.
- Thay đổi biến môi trường production: cập nhật trong **Coolify UI (env)** đồng thời phản ánh khóa tương ứng vào `.env.example` ở repo để tài liệu hóa — **không** sửa `.env` trực tiếp trên container.

> Tham chiếu môi trường thật: `.env.example` (MLHUB, locale `vi`, MySQL, session/queue/cache = `redis` tách DB0/1/2, mail `smtp` qua Emailit, theme `mlhubfrontend` / `mlhubbackend`, **72 module**).

### 1.2 Sau khi cập nhật phiên bản tác giả / cài module mới

- Commit/push → Coolify redeploy (để `docker/entrypoint.sh` chạy `migrate --force` — gồm migration trong `modules/*/Database/Migrations`).
- Kiểm tra log deploy: không lỗi migration (`lb_email_`*, `lb_loyalty_*`, `lb_crm_*`, …).
- Admin → Marketplace / Modules: module mới hiển thị và bật (vd `AppLoyaltyStampCards` qua `providers.marketplace.php`).
- Portal: vào menu CRM, Email automation, Loyalty cards, Reports — không 500.
- Cập nhật `ARCHITECTURE_*.md` + `.cursorrules` nếu thêm module/env (đã quét trong lần sync gần nhất).
- Cài lần đầu / cài lại sạch: `php artisan mlhub:install` (xóa toàn bộ DB + seed lại). Cập nhật giữ dữ liệu: `php artisan mlhub:update`. Chi tiết: `ARCHITECTURE_PROMPT.md` §4.1.

### 1.2.2 Container Exited (10x restarts) sau khi deploy

- **Coolify → Logs** (container app): kéo lên dòng **cuối trước khi thoát** — tìm `ERROR:` hoặc `=== entrypoint:`.
- **APP_KEY trống** → `ERROR: APP_KEY chưa đặt` — Coolify env: `APP_KEY=base64:...` (sinh bằng `php artisan key:generate --show`).
- **MySQL** → `Database migration failed` — kiểm tra `DB_HOST` (hostname service MySQL trên Coolify, không phải `127.0.0.1`), `DB_*`, app + MySQL cùng network.
- **Redis** → `WARN: optimize:clear failed` hoặc log `getaddrinfo for … failed: Temporary failure in name resolution` — `REDIS_HOST` trỏ hostname **cũ** (Redis đã tạo lại trên Coolify) hoặc app chưa link Redis service. **Coolify:** mở resource **Redis** → copy **Internal Hostname** mới → app **Environment Variables** → `REDIS_HOST=` (chỉ hostname, không `redis://`, không port). `REDIS_PASSWORD` = mật khẩu Redis (một lần, không prefix `REDIS_PASSWORD=` lặp). App + Redis cùng network. Redeploy. Queue worker (`queue:work`) cần Redis sống — nếu chưa sửa kịp: tạm `CACHE_STORE=file` + `SESSION_DRIVER=file` (không khuyến nghị lâu dài).
- **blog_rss_sources missing** → redeploy (`migrate --force` trong entrypoint). Migration: `2026_06_06_120000_ensure_blog_rss_tables` hoặc `2026_06_08_160000_repair_missing_blog_rss_tables` (tạo bảng nếu thiếu). Cron `blogs:rss-import` có guard — không còn ERROR sau migrate; log chẩn đoán: `blogs:rss-import diagnostics` trong Admin → Logs.
- **public/storage** → `ERROR: public/storage symlink` — redeploy; volume `mlhub-storage` chỉ mount `/storage`, không mount `public/`.
- Sau khi container **Running**: chạy một lần `php artisan mlhub:install` (cài sạch từ đầu).

### 1.2.1 Coolify deploy fail khi clone Git (exit 255, ~17k files)

- **Triệu chứng:** log dừng ở `Updating files: 50–60%` rồi `Deployment failed: exit code 255` trước khi build Docker.
- **Nguyên nhân thường gặp:** `vendor/` bị commit lên GitHub (~14k file) — Coolify clone quá nặng/timeout; trong khi `Dockerfile` đã `composer install`.
- **Cách xử lý (một lần):** repo có `.gitignore` (`/vendor/`) → `git rm -r --cached vendor` → commit + push → redeploy. Sau push, clone chỉ ~2–3k file.
- **Không** commit `vendor/`, `node_modules/`, `.env`, `storage/*.log`, `bootstrap/cache/*.php`.

### 1.2.3 Cài code gốc lên Coolify

> Repo gốc từ dev, chưa custom. Prompt ngắn: `ARCHITECTURE_FRESH_START_MASTER_PROMPT.md`.

Coolify: Application + MySQL + Redis (3 khối riêng) → copy env lên tab Environment Variables → deploy → làm theo lệnh installer trong repo gốc.

### 1.3 Go-live production lần đầu (sau khi gỡ demo/faker)

> Chạy **một lần** khi DB trống. Mọi biến env giải thích trong `.env.example`.

1. **Coolify → Environment Variables:** điền `APP_KEY`, `MLHUB_FIRST_USER_*`, `MLHUB_LICENSE_PURCHASE_CODE`, `DB_*`, `REDIS_*`, `MAIL_PASSWORD`, `SESSION_DOMAIN=.mlhub.vn`, `APP_INSTALLED=true`, `MLHUB_ALLOW_RESET_DEMO=false`.
2. **Commit + push** GitHub → đợi Coolify build xong (log: migrate OK, Livewire JS synced).
3. **Container app** (Coolify Terminal): `cd /var/www/html && php artisan mlhub:install` (xác nhận xóa DB; production cần tạm `MLHUB_ALLOW_RESET_DEMO=true`).
4. **Kiểm tra:** đăng nhập `MLHUB_FIRST_USER_EMAIL` → Admin + Portal; portal trống (không business demo); ngày/tiền format VN.
5. **Sau go-live:** push code → redeploy (entrypoint tự `migrate`). Bổ sung seed/migration mới mà **giữ dữ liệu**: `php artisan mlhub:update`. Chỉ `mlhub:install` khi muốn **xóa sạch** và cài lại.

### 1.4 Khi phát hiện secret từng bị commit

1. Xoay ngay credential tại từng nhà cung cấp/Coolify: DB, Redis, mail, OAuth, captcha, license và tài khoản admin.
2. Với `APP_KEY`, kiểm kê dữ liệu mã hóa/2FA trước khi đổi; không đổi mù trên production.
3. Redeploy qua Coolify, kiểm tra login, queue, mail, OAuth và Admin → Logs.
4. Chỉ viết lại Git history/force-push sau khi credential đã xoay và có phê duyệt riêng; không coi xóa khỏi file hiện tại là đã hết lộ.

---

## 2. Checklist cho từng Task

Thực hiện tuần tự cho **mỗi** task. Bước nào không áp dụng thì ghi rõ "N/A".

### Giai đoạn A — Phân tích & Khoanh vùng (tiết kiệm tài nguyên)

- Xác định loại công việc: **Sửa lỗi (bug fix)** hay **Tính năng mới (feature)**?
- Khoanh vùng module theo tiền tố: khu khách → `App`*, quản trị → `Admin*`, thanh toán → `Payment*`.
- Đính kèm ngữ cảnh **hẹp** bằng `@file` đúng module/file liên quan (vd `@modules/AppBookingPages/Support/BookingAvailability.php`).
- **Tránh** dùng `@Codebase`/quét toàn dự án trừ khi thật sự cần — ưu tiên đọc `ARCHITECTURE_*.md` đã có.
- Đọc lướt `ARCHITECTURE_CHECKLIST.md` + phần liên quan trong `ARCHITECTURE_BACKEND/FRONTEND/FEATURE.md`.
- Nếu việc thuộc nhóm rủi ro (DB migration / Payment / xóa dữ liệu / refactor lớn) → chuyển sang **Mục 3 (Plan trước)**.

### Giai đoạn B — Thực thi code (surgical + vibecode)

- Sửa **tối thiểu, đúng trọng tâm** (surgical) — không refactor ngoài phạm vi yêu cầu.
- **Bắt chước đúng phong cách file đang sửa** (xem §3 "Vibecode" trong `.cursorrules`):
  - Thụt lề 4 space; **không** thêm `declare(strict_types=1)` nếu file gốc không có.
  - Khai báo kiểu trả về + typed property; dùng `match()`, spread `...$payload`.
  - Eloquent bắt đầu bằng `Model::query()->...`; đọc JSON bằng `data_get()`.
  - Comment tối thiểu — chỉ giải thích "tại sao", không kể lể từng dòng.
- **Đa người dùng:** mọi truy vấn dữ liệu scope theo `auth()->id()` / `workspaceOwnerUserId()`.
- **Gói & tài nguyên:** gọi `PlanLimitGuard::ensureXxxCanBeCreated()` trước khi tạo; tính năng AI gọi `credit_service()->ensureCanConsume()` → `consume_credits()`.
- **Xử lý lỗi đúng mẫu:** `abort_unless(...,404)` cho public; `ValidationException::withMessages(['plan'=>...])` cho limit; `try/catch (Throwable)` + fallback cho dịch vụ ngoài/AI.
- **Đa ngôn ngữ (§3.7 `.cursorrules` — bắt buộc khi chỉnh copy):**
  - Blade/Livewire: `__('English key')` — không hard-code VI/EN.
  - **`lang/en.json`:** key + value tiếng Anh (cập nhật/thêm/xóa cùng lúc với Blade).
  - **`lang/vi.json`:** bản dịch tiếng Việt (theo brief chủ dự án); nếu chỉ có VI thì viết EN tương đương trước.
  - Xóa đoạn trong view → xóa key ở **cả hai** JSON; grep key mới — không key mồ côi; validate JSON parse được.
  - Đổi menu/nhãn section → đồng bộ nav + i18n.
- **Giao diện:** dùng lại `<x-ui.*>` / `<x-shared.*>`; màu dùng token `var(--theme-*)`; theme guest `mlhubfrontend`, backend `mlhubbackend`.
- **Tính năng mới** thì ưu tiên điểm mở rộng: `modules/Custom`* + `bootstrap/providers.marketplace.php` (không sửa core nếu không cần).

### Giai đoạn C — Định dạng & Kiểm thử

- Chạy format chỉ trên file đã sửa: `vendor/bin/pint <đường-dẫn-file>` (hoặc `vendor/bin/pint --dirty`).
- Kiểm tra linter trong Cursor — sửa lỗi do mình tạo ra.
- Chạy test liên quan: `php artisan test` (hoặc Pest filter cho phần vừa đổi).
- Nếu có migration mới: `php artisan migrate` trên **local** (DB MySQL) để xác nhận chạy được; **không** test trên production.
- Nếu đổi Blade: `php artisan view:clear`, kiểm tra cả light/dark + các area bị ảnh hưởng (guest/portal/admin).

### Giai đoạn D — Rà soát rủi ro (trước khi commit)

- **IDOR / cô lập tenant:** không có `findOrFail` "trần" — đã scope theo chủ sở hữu chưa?
- **Spam:** endpoint công khai mới đã có `throttle`/captcha chưa?
- **Demo mode:** action ghi Livewire mới còn tương thích `DemoModeActionGuard`?
- **Bí mật:** không commit `.env`, khóa API, file trong `storage/`.
- **Tài liệu:** nếu thêm/đổi module, route, bảng, permission, env → cập nhật `ARCHITECTURE_MODULE.md` (§3/§12/§13/§14) + `ARCHITECTURE_*.md` liên quan **trong cùng commit**.
- Liệt kê rõ cho người dùng: route mới / permission mới / migration mới / **biến `.env` mới** (kèm cập nhật `.env.example`).

### Giai đoạn E — Commit & Đẩy lên pipeline

- Commit message rõ ràng, đúng style repo; chỉ commit khi người dùng yêu cầu.
- `git push` lên GitHub (nhánh chính / nhánh feature theo thỏa thuận).
- Nếu thay đổi ảnh hưởng deploy (env/migration/asset) → nêu rõ để theo dõi Coolify auto-build; **không** can thiệp tay trên server.
- Sau deploy: xác nhận trên `mlhub.vn` (qua HTTPS sau Traefik) tính năng hoạt động.

---

## 3. Quy tắc an toàn — BẮT BUỘC trình bày kế hoạch trước

Với các nhóm việc dưới đây, AI **PHẢI dừng lại, trình bày kế hoạch chi tiết và chờ người dùng duyệt** trước khi viết/đổi bất kỳ dòng code nào:

### 3.1 🔴 Database Migration / thay đổi schema

- Mô tả: bảng/cột nào thêm/sửa/xóa, kiểu dữ liệu, index, ràng buộc.
- Khẳng định **chỉ thêm mới hoặc cột nullable** với **bảng đang có dữ liệu** (cả `lb_*` lẫn bảng hệ thống `users`/`plans`/`payment_*`…); nêu rõ nếu phải đổi/xóa cột. Kiểm tra tên bảng thật: `ARCHITECTURE_MODULE.md` §13.
- **TUYỆT ĐỐI** không đề xuất `migrate:fresh`, `migrate:rollback`, `db:wipe` trên môi trường có dữ liệu thật.
- Nêu kế hoạch chạy migration qua pipeline (`docker/entrypoint.sh` / `migrate --force` khi build), **không** chạy tay trên Coolify.
- Có phương án rollback an toàn.

### 3.2 🔴 Payment (cổng thanh toán / subscription / credit)

- Nêu rõ cổng (`Payment`*) và contract bị ảnh hưởng.
- Mô tả luồng tiền: checkout → webhook → cập nhật subscription/credit; trường hợp lỗi/hoàn tiền/hết hạn.
- Khẳng định không log dữ liệu nhạy cảm (số thẻ, secret).
- Liệt kê webhook URL / biến env cần cấu hình; test trên **sandbox** trước.

### 3.3 🔴 Xóa dữ liệu / thao tác hàng loạt

- Liệt kê chính xác bản ghi/bảng bị ảnh hưởng và phạm vi (`where`).
- Xác nhận có scope chủ sở hữu, không xóa nhầm tenant khác.
- Ưu tiên soft-delete nếu mô hình hỗ trợ; cân nhắc backup trước.

### 3.4 🟠 Refactor lớn / đổi kiến trúc / đụng core

- Sửa `bootstrap/app.php`, `bootstrap/providers.php`, `composer.json`, `package.json`, `Dockerfile`, `docker/entrypoint.sh`, `docker-compose.yaml`, `config/*.php` → cần duyệt trước.
- Trình bày phương án thay thế bằng điểm mở rộng (`modules/Custom`*, `providers.marketplace.php`, `routes/custom.php`) nếu có.

> Khi không chắc thuộc nhóm nào → mặc định **hỏi trước, code sau**.

---

## 4. Tóm tắt luồng Vibecode (dùng chay Cursor)

1. **Phân tích hẹp** — `@file` đúng chỗ, đọc `ARCHITECTURE_MODULE.md` + phần liên quan trong `ARCHITECTURE_*.md`, **không** quét toàn dự án. Bám tiền tố module (`App*`/`Admin*`/`Payment*`/`Custom*`).
2. **Phân loại việc** — nhỏ/vừa (surgical ngay) vs lớn/rủi ro (**Plan → Duyệt → Code** — §3) vs bug (**evidence/log → root cause → surgical fix**).
3. **Code đúng vibe** — `.cursorrules` §3-§5, scope tenant (`auth()->id()`/workspace owner), guard plan/credit, public form có throttle/captcha.
4. **Format + verify** — `vendor/bin/pint --dirty` → `php artisan test` (nếu đụng logic) → `view:clear` (nếu đổi Blade) → rà §2.D (IDOR/spam/demo/secret/docs).
5. **Báo cáo** — file changed, migration/route/permission/env mới (+ cập nhật `.env.example`/`ARCHITECTURE_MODULE.md`), deploy impact, test đã/chưa chạy, rủi ro, **commit message gợi ý**. Không nói đã test/deploy nếu chưa làm.
6. **Review** (cụm lớn) — đọc lại diff; nếu cần đánh giá độc lập dùng subagent `bugbot`/`security-review` hoặc Codex (`ARCHITECTURE_CODEX_PROMPT.md`).
7. **Deploy** — commit/push + Redeploy **do chủ dự án làm**; sau deploy user **Ctrl+F5** nếu Livewire 419.
   - Docker build fail `exit 255` giữa bước `docker-php-ext-install` (log cắt ở intl/opcache): thường **OOM** trên VPS nhỏ — repo đã có `.dockerignore` (bỏ `vendor/` khỏi context) + `Dockerfile` dùng `-j1`/`MAKEFLAGS=-j1`. Nếu vẫn fail: tăng RAM/swap server hoặc bật **Runtime only** cho `APP_ENV` trên Coolify.

> *(Tùy chọn)* Nếu bật Superpowers: bug → `systematic-debugging`; feature → `brainstorming` → `writing-plans` → **Duyệt** → `executing-plans` → `verification-before-completion` → `requesting-code-review`. **Không bắt buộc.**

### 4.1 Triage nhanh lỗi production (419 / 504 / 500)

- **419 Page expired (Livewire):** thường do snapshot cũ sau deploy → user **Ctrl+F5**. Kiểm tra `APP_URL=https://mlhub.vn` + `SESSION_DOMAIN=.mlhub.vn`; `docker/entrypoint.sh` đã sync Livewire JS + `optimize`. Đổi lớn → đặt `LIVEWIRE_RELEASE_TOKEN`.
- **504 Gateway timeout:** request quá lâu (AI/queue đồng bộ, query nặng). Đẩy việc nặng qua queue; kiểm tra Redis/queue worker sống.
- **500 Internal error:** **Admin → Cài đặt → Logs** (module `AdminLog`) hoặc Coolify Logs → tìm dòng `ERROR/CRITICAL` → tìm root cause → surgical fix theo vibecode. Lỗi thoáng qua sau deploy (snapshot) thì nêu trạng thái, không sửa thừa.

---

## 5. Lệnh hay dùng (chạy tại local, không chạy trên server)

```bash
vendor/bin/pint --dirty            # format file vừa đổi
php artisan test                   # chạy test (Pest)
php artisan migrate                # áp migration trên DB local (MySQL)
php artisan view:clear             # xóa cache Blade sau khi sửa view
php artisan config:clear           # khi đổi config/env lúc dev
```

> Trên production: các lệnh tương ứng (`migrate --force`, `config:cache`…) do pipeline Coolify/`docker/entrypoint.sh` đảm nhiệm — không gõ tay trên server.

### 5.1 Rà soát định dạng số / ngày / tiền (chuẩn Việt Nam)

**Helper chuẩn** (trong `app/Support/helpers.php`; cấu hình Admin → Cài đặt → General):

| Hiển thị | Helper |
|----------|--------|
| Số đếm / thống kê | `format_number_locale($n)` hoặc `format_number_locale($n, $decimals)` |
| Tiền VNĐ | `format_money($amount)` |
| Ngày | `format_date_locale($date)` |
| Ngày + giờ | `format_datetime_locale($date)` |
| Model `createdAtFormatted('Y-m-d')` cũ | `format_carbon_display($date, $format)` (đã gắn trong model) |
| Phần trăm | `format_percent_locale($n)` |
| JS chart/table | `window.MLHUB_FORMAT` (inject trong `head.blade.php`) |

**Không dùng** `number_format($x)` trần trong Blade/Livewire hiển thị — mặc định kiểu Mỹ (dấu phẩy ngàn). **Bỏ qua** `number_format` trong `modules/Payment*` (format gửi API cổng thanh toán, không phải UI).

**Rà soát** (Cursor Terminal, thư mục gốc dự án — dùng **Cursor AI grep** hoặc `rg` nếu máy đã cài ripgrep):

```text
# 1. Số format sai (ưu tiên sửa Blade portal + Admin)
rg "number_format\(" modules resources app --glob "*.blade.php"

# 2. Ngày in cứng Y-m-d / M d (đổi sang format_date_locale / format_datetime_locale)
rg "->format\(" modules resources --glob "*.blade.php"

# 3. Ô metric in số thô (hay sót widget health)
rg "\{\{\s*\$[^}]*\['value'\]\s*\}\}" modules --glob "*.blade.php"
```

**Thứ tự sửa:** component dùng chung (`resources/views/components/`, `resources/themes/app/*/components/`) → module `App*` (portal) → `Admin*` → chart JS dùng `MLHUB_FORMAT`.

**Không tạo file script** trong repo cho việc này — chỉ chạy lệnh grep khi cần (tránh file rác quên commit).
