# MLHUB AI — Bộ Prompt & Sổ tay lệnh (Vibecode)

Nơi lưu **prompt mẫu** để làm việc với Cursor và **cheatsheet lệnh** (Laravel / Docker / Coolify) cho dự án MLHUB. Mở file này mỗi khi bắt đầu phiên, copy prompt phù hợp ở **§2 (dùng chay)** hoặc **§3 (theo tình huống)**.

> **Workflow chính: dùng chay Cursor (không cần plugin).** Superpowers chỉ là **tùy chọn** (§9). Prompt audit/diff cho **Codex** nằm ở `ARCHITECTURE_CODEX_PROMPT.md`.
>
> Đọc kèm: `.cursorrules`, `ARCHITECTURE_CHECKLIST.md`, `ARCHITECTURE_BACKEND.md`, `ARCHITECTURE_FRONTEND.md`, `ARCHITECTURE_MODULE.md` (route/bảng/model/plan từng module), `ARCHITECTURE_FEATURE.md` (độ sẵn sàng/backlog).
> Mọi prompt nên đính kèm `@file` đúng chỗ thay vì `@Codebase` để tiết kiệm tài nguyên.
> **Commit / push / redeploy Coolify do chủ dự án làm thủ công** — AI chỉ sửa local + soạn commit message.

---

## 1. Mẹo dùng prompt hiệu quả

- **Khoanh vùng hẹp:** `@modules/AppBookingPages/...` thay vì cả dự án. Tra route/bảng/model module trong `ARCHITECTURE_MODULE.md` trước.
- **Nói rõ loại việc:** "sửa lỗi" (surgical) hay "tính năng mới" (ưu tiên extension point).
- **Nhắc ngữ cảnh chuẩn:** "theo `.cursorrules` và `ARCHITECTURE_CHECKLIST.md`".
- **Việc rủi ro:** luôn **Plan → Duyệt → Code** (trình bày kế hoạch, chờ "Duyệt", rồi mới viết code).
- **Một task một mục tiêu:** đừng gộp nhiều việc không liên quan vào một prompt.

---

## 2. Quy trình dùng chay (không Superpowers)

Đây là **workflow mặc định**. Mỗi bước có prompt copy sẵn. Việc lớn/rủi ro đi đủ chuỗi **Plan → Duyệt → Code → Verify → Review**; bug đi **Evidence → Root cause → Fix → Verify**; việc nhỏ làm surgical ngay.

### 2.1 Mở task mới

```
MLHUB (Laravel 13 + Livewire 4, production mlhub.vn). Đọc lướt .cursorrules + ARCHITECTURE_CHECKLIST.md;
tra ARCHITECTURE_MODULE.md cho module liên quan.
Mục tiêu: <mô tả ngắn 1 mục tiêu>.
Phạm vi: @<file/module liên quan>.
Phân loại giúp tôi: việc nhỏ (làm ngay) hay lớn/rủi ro (cần plan trước)? CHƯA code.
```

### 2.2 Plan trước (việc lớn / rủi ro)

```
Lập kế hoạch cho: <mô tả>. CHƯA code.
Trình bày: (1) file/module sẽ đụng, (2) route/model/migration/permission/env mới,
(3) ảnh hưởng schema/payment/deploy, (4) rủi ro + cách giảm, (5) cách verify.
Tính năng mới → ưu tiên extension point (modules/Custom* + providers.marketplace.php), không sửa core nếu không cần.
Chờ tôi gõ "Duyệt".
```

### 2.3 Sau khi gõ Duyệt (thực thi)

```
Duyệt. Triển khai kế hoạch vừa lập — từng bước, surgical, bám vibecode (.cursorrules §3).
Scope tenant theo auth()->id()/workspaceOwnerUserId(); guard plan/credit nếu đụng tính năng trả phí.
Cập nhật ARCHITECTURE_MODULE.md + .env.example nếu thêm module/route/bảng/permission/env.
Chưa commit/push.
```

### 2.4 Verify (trước khi báo xong)

```
Verify thay đổi vừa làm:
- vendor/bin/pint --dirty trên file đã sửa
- php artisan test (hoặc --filter=<nhóm>) nếu đụng logic
- php artisan view:clear nếu đổi Blade
Báo rõ test nào đã chạy, test nào CHƯA chạy được và lý do. Không nói đã test nếu chưa chạy.
```

### 2.5 Review diff

```
Tự review diff phiên này theo checklist: logic/regression, tenant scope/IDOR, plan/credit gate,
migration/env/deploy, i18n (en.json + vi.json), performance (N+1/cache), casing MLHUB/mlhub, file thừa.
Verdict: Approve / Approve with comments / Request changes + danh sách P0/P1/P2.
(Đánh giá độc lập sâu hơn → dùng subagent bugbot/security-review hoặc Codex: ARCHITECTURE_CODEX_PROMPT.md.)
```

### 2.6 Bug — systematic debugging (không plugin)

```
MLHUB production bug. KHÔNG đoán mò, KHÔNG patch trước khi xác định root cause.
Triệu chứng: <mô tả người dùng thấy>
Evidence/log: <paste dòng ERROR từ Admin → Logs hoặc Coolify Logs>
Repro: <URL | role | browser | sau deploy? | đã Ctrl+F5?>
Phạm vi nghi: @<file/module>
Yêu cầu: (1) chỉ ra root cause + bằng chứng trong code/log, (2) fix tối thiểu trong phạm vi,
(3) không refactor ngoài task, (4) verify (§2.4), (5) báo cáo: root cause / files changed / verification / rủi ro / commit message.
```

### 2.7 Quét module (module scan)

```
Quét module @modules/<TênModule>/ — CHƯA code.
Đọc Routes/web.php, Livewire/*, Http/Controllers/*, Models/*, Support/*, Services/*.
Trả về: (1) chức năng, (2) route prefix + public endpoint (throttle/captcha?), (3) model→bảng,
(4) plan permission/credit, (5) phụ thuộc module khác, (6) rủi ro P0/P1/P2.
Đối chiếu ARCHITECTURE_MODULE.md — nếu khác thực tế, chỉ ra để cập nhật.
```

### 2.8 Docs refresh (sau khi đổi code/module)

```
Tôi vừa <mô tả thay đổi: thêm module / đổi route / thêm env / sửa permission>.
Cập nhật surgical (không viết lại toàn bộ) các file bị ảnh hưởng:
.cursorrules, .env.example, ARCHITECTURE_MODULE.md (§3/§12/§13/§14), ARCHITECTURE_BACKEND/FRONTEND/FEATURE.md.
Giữ khớp thực tế code để Cursor sau này không phải quét lại dự án.
```

### 2.9 Báo cáo hoàn thành task

```
Tổng kết task theo format:
1. Mục tiêu đã xử lý  2. Files changed  3. Logic thay đổi (có/không)
4. Migration/env/deploy impact  5. Verification đã chạy  6. Verification CHƯA chạy + lý do
7. Rủi ro còn lại  8. Checklist cho tôi sau commit/push/Coolify  9. Commit message gợi ý
Không nói đã deploy nếu chưa deploy. Không nói đã test nếu chưa chạy test.
```

---

## 3. Bộ Prompt mẫu theo tình huống (copy & điền)

### 3.1 Một growth tool mới (engine `lb_campaigns`)

```
Thêm growth tool kiểu <tên>. Theo đúng mẫu engine dùng chung: model QrCampaign (lb_campaigns)
với type='<key>' + settings JSON, public qua QrCampaignPublicController, PlanLimitGuard, CustomerUpserter,
GrowthToolNotifier (xem ARCHITECTURE_FEATURE.md §0, ARCHITECTURE_MODULE.md §6). Public POST phải throttle:10,1.
Trình bày kế hoạch trước (§2.2).
```

### 3.2 Giao diện / Theme (White-label)

```
Chỉnh giao diện <mô tả> ở khu <guest/portal/admin>.
Theme guest `mlhubfrontend`, backend `mlhubbackend` (ARCHITECTURE_FRONTEND.md §2.2).
Dùng lại <x-ui.*>/<x-shared.*>, màu dùng token var(--theme-*), không hard-code màu.
Branding nhỏ → gợi ý Admin → Themes → Custom CSS/JS thay vì sửa file. Đồng bộ en.json + vi.json nếu đổi copy.
```

### 3.3 Tính năng AI (có credit)

```
Thêm/sửa tính năng AI: <mô tả>. Bắt buộc: feature gate → credit_service()->ensureCanConsume() trước khi gọi LLM →
try/catch (Throwable) có fallback → consume_credits() khi thành công (mẫu AppAIContent).
Credit action đăng ký trong AppAIStudio. Scope lịch sử theo workspaceOwnerUserId().
```

### 3.4 Database migration (🔴 plan trước)

```
Tôi cần đổi schema: <mô tả>. ĐỪNG code ngay. Trình bày kế hoạch: bảng/cột (kiểm tra tên thật ở
ARCHITECTURE_MODULE.md §13), kiểu dữ liệu, index, ràng buộc, chỉ thêm mới/nullable hay đổi/xóa,
phương án rollback, cách chạy qua pipeline (docker/entrypoint.sh / migrate --force).
TUYỆT ĐỐI không migrate:fresh/db:wipe trên production.
```

### 3.5 Payment / Subscription / Credit (🔴 plan trước)

```
Làm việc với cổng thanh toán <PaymentStripe|PaymentPaypal|Payment2Checkout|manual>: <mô tả>.
Trình bày kế hoạch: contract/luồng tiền (checkout→webhook→subscription/credit), ca lỗi/hoàn tiền/hết hạn,
webhook URL & env cần cấu hình, webhook idempotent, không log dữ liệu nhạy cảm. Test sandbox trước.
```

### 3.6 Backlog bảo mật (theo ARCHITECTURE_FEATURE.md §14)

```
Xử lý backlog §<14.x> trong ARCHITECTURE_FEATURE.md: <captcha growth | loyalty/landing throttle | IDOR | XSS | bot scan>.
Phạm vi: @<module liên quan>. Surgical, dùng helper sẵn có (captcha_render/with_captcha_validation; throttle:10,1).
Verify + báo cáo. Không refactor ngoài backlog.
```

---

## 4. Cheatsheet lệnh Laravel (chạy ở LOCAL trên Cursor)

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

### 4.1 Cài đặt production lần đầu (không còn Web Installer / Admin Faker)

> MLHUB **đã gỡ Web Installer** và **gỡ Admin Faker / demo volume**. Bootstrap qua **migrate** (tự động mỗi deploy) + `mlhub:install` (một lần, DB trống) + env Coolify.
>
> **Giải thích từng biến env:** file `.env.example` (comment tiếng Việt từng nhóm). Checklist go-live: `ARCHITECTURE_CHECKLIST.md` §1.3.

**Super admin:** lấy từ env `MLHUB_FIRST_USER_EMAIL` / `MLHUB_FIRST_USER_PASSWORD` (không hard-code trong repo).

**Coolify (tab Environment Variables) — bắt buộc trước `mlhub:install`:**

| Biến                          | Giá trị                                                |
| ----------------------------- | ------------------------------------------------------ |
| `APP_INSTALLED`               | `true` (entrypoint chạy `migrate --force`)             |
| `MLHUB_FIRST_USER_EMAIL`      | Email super admin (vd `you@mlhub.vn`)                  |
| `MLHUB_FIRST_USER_PASSWORD`   | Mật khẩu mạnh (Coolify — không commit)                 |
| `MLHUB_FIRST_USER_NAME`       | Tên hiển thị (tuỳ chọn)                                |
| `MLHUB_CONTACT_EMAIL`         | Email liên hệ site (thường trùng admin)                |
| `MLHUB_ADMIN_PLAN_SLUG`       | `mlhub-partner-lifetime`                               |
| `MLHUB_STARTING_ID`           | `147123468` (AUTO_INCREMENT mọi bảng, kể cả `options` + `migrations`; migration `2026_06_20_120000_mlhub_align_options_and_migrations_id_sequence` remap ID cũ < giá trị này) |
| `MLHUB_LICENSE_PURCHASE_CODE` | Mã license Stackposts (Coolify)                        |
| `MLHUB_LICENSE_DOMAIN`        | `mlhub.vn`                                             |
| `MLHUB_ALLOW_RESET_DEMO`      | `false` (chặn `db:wipe` trên production)               |
| `RUN_QUEUE_WORKER`            | `true` (hoặc `false` nếu worker Coolify riêng)         |
| `MAIL_PASSWORD`               | SMTP API key (seed ghi `options.smtp_password` nếu có) |

**Module `CustomMLHUB`:** site options VN (`format_date` `d/m/Y`, VND, timezone), seeder admin, email/template packs hệ thống.

#### A. Ba lệnh MLHUB (trong container app)

| Lệnh                                 | Khi dùng                                    | Hành vi                                                                                                                                                                                                          |
| ------------------------------------ | ------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `php artisan mlhub:install`          | DB mới hoặc muốn **xóa sạch** cài lại       | `migrate:fresh` (xóa toàn bộ bảng) → seed từ ID `147123468` → `optimize`. Hỏi xác nhận; `--force` bỏ qua hỏi. Production: tạm `MLHUB_ALLOW_RESET_DEMO=true`.                                                       |
| `php artisan mlhub:update`           | Đã có dữ liệu, muốn **cập nhật** sau deploy | `migrate` (migration mới) → seed đồng bộ catalog official `mlhub-*`, giữ plan legacy/custom, không xóa user/campaign → bản ghi seed mới nối ID sau max hiện có → `IdSequence::apply()` → `optimize`.               |
| `php artisan mlhub:sync-env-options` | Sau khi đổi env Coolify                     | Ghi env có giá trị → **Admin → Cài đặt** (`options`). Tự chạy sau `migrate` mỗi deploy (`docker/entrypoint.sh`).                                                                                                  |

**Admin Settings ↔ Coolify:** SMTP, captcha, Stripe, Google OAuth, license… đặt trong **Environment Variables** (xem `.env.example`). Map chi tiết: `modules/CustomMLHUB/config/env_options.php`. Sau `mlhub:install`, cấu hình **khôi phục từ env**.

```bash
docker exec -it <container_app> sh
cd /var/www/html
php artisan mlhub:install    # cài sạch — XÓA HẾT dữ liệu
php artisan mlhub:update     # cập nhật — GIỮ dữ liệu cũ
```

**Không** seed dữ liệu demo / QR volume / business mẫu.

#### B. Deploy thường (đã cài xong)

Push GitHub → Coolify redeploy → `docker/entrypoint.sh` chỉ **migrate** + optimize. Không tự chạy seed. Chạy thủ công `mlhub:update` khi cần đồng bộ seed/migration mới mà giữ dữ liệu khách.

#### C. Sau cài — kiểm tra

- Đăng nhập `MLHUB_FIRST_USER_EMAIL` → **Admin** + **Portal**.
- Portal trống (chưa có business/campaign) — đúng production.
- Admin → Cài đặt: ngày `dd/mm/yyyy`, tiền `₫` (từ `mlhub_site_options.php`).

### 4.2 Backup production an toàn

- **Code:** GitHub là source of truth; dùng tag/commit + Coolify rollback.
- **Database:** backup/snapshot riêng của resource MySQL trong Coolify.
- **Uploads/log:** snapshot/backup private của Persistent Storage `mlhub-storage`.
- **Cấm:** tạo archive source/log/storage trong `public/`; không cung cấp URL tải backup qua `mlhub.vn`.

---

## 5. Cheatsheet Docker / Coolify (CHỈ để CHẨN ĐOÁN — đọc log/kiểm tra)

> 🔴 **Quy tắc cốt lõi (ARCHITECTURE_CHECKLIST.md §1):** KHÔNG dùng các lệnh này để **thay đổi** hạ tầng/code/config trên server. Mọi thay đổi đi qua local → GitHub → Coolify build. Dưới đây chỉ để **xem trạng thái, đọc log, debug**.

```bash
# Xem container đang chạy
docker ps --filter "name=mlhub"

# Đọc log ứng dụng (chẩn đoán lỗi deploy/runtime)
docker logs --tail=200 -f <container_id_or_name>

# Vào shell container để XEM (không sửa)
docker exec -it <container_id_or_name> sh
php artisan about
php artisan migrate:status
tail -n 200 storage/logs/laravel.log

# Tài nguyên / dung lượng
docker stats --no-stream
df -h
```

### Khi gặp sự cố trên production → làm gì?

1. **Đọc log** (`docker logs`, `storage/logs/laravel.log`, Admin → Logs) để xác định nguyên nhân.
2. **Tái hiện ở local**, sửa bằng code/config, test, commit, push.
3. Để **Coolify auto-build** lại. KHÔNG vá tay trên container.
4. Biến môi trường: sửa trong **Coolify UI (env)** + cập nhật `.env.example` ở repo cho khớp.
5. Migration khi deploy: đảm bảo nằm trong `docker/entrypoint.sh`/pipeline.

### 5.1 Xem & tải `laravel.log` an toàn (module AdminLog)

> ✅ Xem/tải log tại **Admin → Cài đặt → Logs** (`https://mlhub.vn/admin/settings/log`), nằm sau `auth` + `EnsureAdminAccess`.
>
> 🔴 **TUYỆT ĐỐI KHÔNG** copy `storage/logs/*.log` vào `public/` để tải qua URL công khai — log chứa stack trace, DB host, user ID, email, token.

---

## 6. Tham chiếu nhanh môi trường MLHUB

| Hạng mục            | Giá trị                                                                                                          |
| ------------------- | ---------------------------------------------------------------------------------------------------------------- |
| Brand / domain      | MLHUB / `mlhub.vn` (+ `www`)                                                                                     |
| Locale / timezone   | `vi` / `Asia/Ho_Chi_Minh`                                                                                        |
| DB                  | MySQL (3306)                                                                                                     |
| Session/Queue/Cache | `redis` (phpredis): queue/default DB0, cache + lock DB1, session DB2; đúng một queue worker                      |
| Mail                | ✅ `smtp` qua Emailit (`smtp.emailit.com:587`, secret trong Coolify env)                                          |
| Captcha             | Cloudflare Turnstile (mặc định) + reCAPTCHA v2 — Admin → Captcha (OptionStore); gắn ở auth, chưa gắn form public |
| Rate-limit          | `throttle:10,1` trên 5 form public (booking/coupon/feedback/lead/review); loyalty/landing **chưa** (backlog)     |
| Storage             | disk `public` (S3/Wasabi/Contabo trống)                                                                          |
| Theme active        | guest = `mlhubfrontend`, backend = `mlhubbackend` (env `THEME_FRONTEND` / `THEME_BACKEND`)                       |
| Session / URL prod  | `SESSION_DOMAIN=.mlhub.vn`, `APP_URL=https://mlhub.vn`, `TRUSTED_PROXIES=*`; không publish host port             |
| Log production      | `LOG_CHANNEL=stack`, `LOG_STACK=stderr,daily`, `LOG_LEVEL=warning`                                               |
| Deploy              | Coolify + Traefik (HTTP→HTTPS, Let's Encrypt)                                                                    |
| Module              | 72 (31 Admin, 37 App, 3 Payment, 1 Custom) — chi tiết `ARCHITECTURE_MODULE.md`                                   |

---

## 7. Sổ tay cá nhân (tự ghi note mỗi lần code)

> Khu vực để bạn tự bổ sung. Gợi ý ghi theo định dạng ngày + việc + lệnh/đường dẫn liên quan.

### DD-MM-YYYY — <tiêu đề việc>

- Bối cảnh / mục tiêu:
- File/module liên quan: `@...`
- Lệnh đã dùng:
- Kết quả / lưu ý / cạm bẫy gặp phải:
- Việc cần làm tiếp:

---

## 8. Lựa chọn mô hình AI trong Cursor

- **Opus (Core):** task cốt lõi cần đọc ngữ cảnh dài + lập kế hoạch sắc bén (email/SMTP, rate-limit/captcha, race-condition booking). Nuốt trọn `ARCHITECTURE_*.md`, nhớ kỹ quy tắc an toàn.
- **Composer Fast (áp dụng nhanh):** khi đã chốt phương án → áp thay đổi vào nhiều file (vd chèn `throttle` vào hàng loạt `Routes/web.php`). Surgical edits nhanh.
- **Sonnet / GPT Medium (Theme):** task nhẹ — Tailwind theme `mlhubfrontend`/`mlhubbackend`, bổ sung `lang/vi.json`, regex validation. Luôn kèm `.cursorrules` + `@file`; không thay bước plan cho tính năng lớn.

---

## 9. (Tùy chọn) Superpowers — nếu bật plugin

> **Không bắt buộc.** Workflow §2 (dùng chay) là chính. Nếu đã bật Superpowers, có thể map các bước sang skill:

| Bước dùng chay (§2) | Skill Superpowers tương đương |
| --- | --- |
| Mở task / làm rõ yêu cầu (lớn) | `brainstorming` |
| Plan trước (§2.2) | `writing-plans` |
| Sau "Duyệt" (§2.3) | `executing-plans` |
| Bug debugging (§2.6) | `systematic-debugging` |
| Verify (§2.4) | `verification-before-completion` |
| Review diff (§2.5) | `requesting-code-review` / subagent `code-reviewer` |
| Nhiều việc song song | `dispatching-parallel-agents` / `using-git-worktrees` |

Thứ tự khi dùng nhiều skill: `brainstorming`/`systematic-debugging` → `writing-plans` → *(gõ **Duyệt**)* → `executing-plans` → `verification-before-completion` → `code-reviewer`. Lệnh cũ `/brainstorm`, `/write-plan` đã deprecated — dùng skill cùng tên.

---

## 10. Codex (audit / diff review)

Prompt cho **Codex** (security/performance/infra audit, module scoped, review diff sau Cursor, fix sau duyệt) nằm ở `ARCHITECTURE_CODEX_PROMPT.md`. Nguyên tắc: không quét toàn repo nếu không cần, không code trước khi duyệt, không commit/push/deploy, không SSH server, không `migrate:fresh`/`db:wipe` production.
