# MLHUB — Quy trình làm việc & Checklist (Vibecode)

Tài liệu quy trình vận hành chuẩn cho dự án **MLHUB** (LocalBoost AI / Stackposts) khi làm việc với Cursor. Mục tiêu: code **ổn định, đúng phong cách lập trình viên gốc, an toàn cho production, và tiết kiệm tài nguyên đọc lại dự án**.

> Đọc kèm: `.cursorrules` (luật cứng), `ARCHITECTURE_BACKEND.md`, `ARCHITECTURE_FRONTEND.md`, `ARCHITECTURE_FEATURE.md`, `ARCHITECTURE_PROMPT.md` (bộ prompt + cheatsheet lệnh).
> **Bắt buộc:** đọc lướt file này trước khi bắt tay vào bất kỳ Task / tính năng mới nào.

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
> **Phân chia trách nhiệm:** AI chỉ **sửa code/config tại local** + soạn **commit message gợi ý**. Bước **`git commit` / `git push` / Redeploy là do chủ dự án tự làm thủ công** (để kịp copy log khi lỗi). AI **không** tự commit/push/deploy. Nếu cần cấu hình Coolify, AI hướng dẫn theo từng tab (General, Environment Variables, Scheduled Tasks, …). Khi phát hiện rủi ro bảo mật/UX (kể cả ngoài task) → **báo ngay**.

### ⛔ Quy tắc bất di bất dịch về hạ tầng

- **TUYỆT ĐỐI KHÔNG** đề xuất can thiệp thủ công bằng dòng lệnh trực tiếp trên server Coolify (SSH, sửa file trên container, chạy lệnh tay…).
- **Mọi thay đổi hạ tầng phải thể hiện bằng code/config tại local**: sửa `docker-compose.yaml`, `Dockerfile`, `entrypoint.sh`, biến trong `.env`/`.env.example`, hoặc migration trong repo → commit → push → để Coolify tự build.
- Lệnh artisan cần chạy khi deploy (vd `migrate --force`, `config:cache`) phải nằm trong `entrypoint.sh` / quy trình build, **không** chạy tay trên server.
- Thay đổi biến môi trường production: cập nhật trong **Coolify UI (env)** đồng thời phản ánh khóa tương ứng vào `.env.example` ở repo để tài liệu hóa — **không** sửa `.env` trực tiếp trên container.

> Tham chiếu môi trường thật: `.env.example` (MLHUB, locale `vi`, MySQL, session/queue/cache = `redis`, mail `smtp` qua Emailit, theme `mlhubtheme`/`default`).

---

## 2. Checklist cho từng Task

Thực hiện tuần tự cho **mỗi** task. Bước nào không áp dụng thì ghi rõ "N/A".

### Giai đoạn A — Phân tích & Khoanh vùng (tiết kiệm tài nguyên)

- [ ] Xác định loại công việc: **Sửa lỗi (bug fix)** hay **Tính năng mới (feature)**?
- [ ] Khoanh vùng module theo tiền tố: khu khách → `App*`, quản trị → `Admin*`, thanh toán → `Payment*`.
- [ ] Đính kèm ngữ cảnh **hẹp** bằng `@file` đúng module/file liên quan (vd `@modules/AppBookingPages/Support/BookingAvailability.php`).
- [ ] **Tránh** dùng `@Codebase`/quét toàn dự án trừ khi thật sự cần — ưu tiên đọc `ARCHITECTURE_*.md` đã có.
- [ ] Đọc lướt `ARCHITECTURE_CHECKLIST.md` + phần liên quan trong `ARCHITECTURE_BACKEND/FRONTEND/FEATURE.md`.
- [ ] Nếu việc thuộc nhóm rủi ro (DB migration / Payment / xóa dữ liệu / refactor lớn) → chuyển sang **Mục 3 (Plan trước)**.

### Giai đoạn B — Thực thi code (surgical + vibecode)

- [ ] Sửa **tối thiểu, đúng trọng tâm** (surgical) — không refactor ngoài phạm vi yêu cầu.
- [ ] **Bắt chước đúng phong cách file đang sửa** (xem §3 "Vibecode" trong `.cursorrules`):
  - [ ] Thụt lề 4 space; **không** thêm `declare(strict_types=1)` nếu file gốc không có.
  - [ ] Khai báo kiểu trả về + typed property; dùng `match()`, spread `...$payload`.
  - [ ] Eloquent bắt đầu bằng `Model::query()->...`; đọc JSON bằng `data_get()`.
  - [ ] Comment tối thiểu — chỉ giải thích "tại sao", không kể lể từng dòng.
- [ ] **Đa người dùng:** mọi truy vấn dữ liệu scope theo `auth()->id()` / `workspaceOwnerUserId()`.
- [ ] **Gói & tài nguyên:** gọi `PlanLimitGuard::ensureXxxCanBeCreated()` trước khi tạo; tính năng AI gọi `credit_service()->ensureCanConsume()` → `consume_credits()`.
- [ ] **Xử lý lỗi đúng mẫu:** `abort_unless(...,404)` cho public; `ValidationException::withMessages(['plan'=>...])` cho limit; `try/catch (Throwable)` + fallback cho dịch vụ ngoài/AI.
- [ ] **Đa ngôn ngữ:** mọi chuỗi hiển thị bọc `__()`; bổ sung bản dịch vào `lang/vi.json` (app mặc định locale `vi`).
- [ ] **Giao diện:** dùng lại `<x-ui.*>` / `<x-shared.*>`; màu dùng token `var(--theme-*)`; theme guest đang active là `mlhubtheme`.
- [ ] **Tính năng mới** thì ưu tiên điểm mở rộng: `modules/Custom*` + `bootstrap/providers.marketplace.php` (không sửa core nếu không cần).

### Giai đoạn C — Định dạng & Kiểm thử

- [ ] Chạy format chỉ trên file đã sửa: `vendor/bin/pint <đường-dẫn-file>` (hoặc `vendor/bin/pint --dirty`).
- [ ] Kiểm tra linter trong Cursor — sửa lỗi do mình tạo ra.
- [ ] Chạy test liên quan: `php artisan test` (hoặc Pest filter cho phần vừa đổi).
- [ ] Nếu có migration mới: `php artisan migrate` trên **local** (DB MySQL) để xác nhận chạy được; **không** test trên production.
- [ ] Nếu đổi Blade: `php artisan view:clear`, kiểm tra cả light/dark + các area bị ảnh hưởng (guest/portal/admin).

### Giai đoạn D — Rà soát rủi ro (trước khi commit)

- [ ] **IDOR / cô lập tenant:** không có `findOrFail` "trần" — đã scope theo chủ sở hữu chưa?
- [ ] **Spam:** endpoint công khai mới đã có `throttle`/captcha chưa?
- [ ] **Demo mode:** action ghi Livewire mới còn tương thích `DemoModeActionGuard`?
- [ ] **Bí mật:** không commit `.env`, khóa API, file trong `storage/`.
- [ ] Liệt kê rõ cho người dùng: route mới / permission mới / migration mới / **biến `.env` mới** (kèm cập nhật `.env.example`).

### Giai đoạn E — Commit & Đẩy lên pipeline

- [ ] Commit message rõ ràng, đúng style repo; chỉ commit khi người dùng yêu cầu.
- [ ] `git push` lên GitHub (nhánh chính / nhánh feature theo thỏa thuận).
- [ ] Nếu thay đổi ảnh hưởng deploy (env/migration/asset) → nêu rõ để theo dõi Coolify auto-build; **không** can thiệp tay trên server.
- [ ] Sau deploy: xác nhận trên `mlhub.vn` (qua HTTPS sau Traefik) tính năng hoạt động.

---

## 3. Quy tắc an toàn — BẮT BUỘC trình bày kế hoạch trước

Với các nhóm việc dưới đây, AI **PHẢI dừng lại, trình bày kế hoạch chi tiết và chờ người dùng duyệt** trước khi viết/đổi bất kỳ dòng code nào:

### 3.1 🔴 Database Migration / thay đổi schema
- [ ] Mô tả: bảng/cột nào thêm/sửa/xóa, kiểu dữ liệu, index, ràng buộc.
- [ ] Khẳng định **chỉ thêm mới hoặc cột nullable** với bảng `lb_*` đang có dữ liệu; nêu rõ nếu phải đổi/xóa cột.
- [ ] **TUYỆT ĐỐI** không đề xuất `migrate:fresh`, `migrate:rollback`, `db:wipe` trên môi trường có dữ liệu thật.
- [ ] Nêu kế hoạch chạy migration qua pipeline (`entrypoint.sh` / `migrate --force` khi build), **không** chạy tay trên Coolify.
- [ ] Có phương án rollback an toàn.

### 3.2 🔴 Payment (cổng thanh toán / subscription / credit)
- [ ] Nêu rõ cổng (`Payment*`) và contract bị ảnh hưởng.
- [ ] Mô tả luồng tiền: checkout → webhook → cập nhật subscription/credit; trường hợp lỗi/hoàn tiền/hết hạn.
- [ ] Khẳng định không log dữ liệu nhạy cảm (số thẻ, secret).
- [ ] Liệt kê webhook URL / biến env cần cấu hình; test trên **sandbox** trước.

### 3.3 🔴 Xóa dữ liệu / thao tác hàng loạt
- [ ] Liệt kê chính xác bản ghi/bảng bị ảnh hưởng và phạm vi (`where`).
- [ ] Xác nhận có scope chủ sở hữu, không xóa nhầm tenant khác.
- [ ] Ưu tiên soft-delete nếu mô hình hỗ trợ; cân nhắc backup trước.

### 3.4 🟠 Refactor lớn / đổi kiến trúc / đụng core
- [ ] Sửa `bootstrap/app.php`, `bootstrap/providers.php`, `composer.json`, `package.json`, `Dockerfile`, `entrypoint.sh`, `docker-compose.yaml`, `config/*.php` → cần duyệt trước.
- [ ] Trình bày phương án thay thế bằng điểm mở rộng (`modules/Custom*`, `providers.marketplace.php`, `routes/custom.php`) nếu có.

> Khi không chắc thuộc nhóm nào → mặc định **hỏi trước, code sau**.

---

## 4. Tóm tắt 6 bước Vibecode (bản rút gọn để nhớ nhanh)

1. **Phân tích hẹp** — `@file` đúng chỗ, đọc `ARCHITECTURE_*.md`, không quét toàn dự án.
2. **Bám tiền tố module** — `App*` / `Admin*` / `Payment*`.
3. **Phân loại việc** — bug fix (sửa thẳng, surgical) vs feature mới (ưu tiên extension point).
4. **Code đúng vibe** — theo `.cursorrules` §3, scope tenant, guard plan/credit.
5. **Format + test** — `pint` file đã sửa → `php artisan test` → rà checklist §2.D.
6. **Việc rủi ro: Plan trước** — migration/payment/xóa dữ liệu/đụng core → trình bày kế hoạch, chờ duyệt.

---

## 5. Lệnh hay dùng (chạy tại local, không chạy trên server)

```bash
vendor/bin/pint --dirty            # format file vừa đổi
php artisan test                   # chạy test (Pest)
php artisan migrate                # áp migration trên DB local (MySQL)
php artisan view:clear             # xóa cache Blade sau khi sửa view
php artisan config:clear           # khi đổi config/env lúc dev
```

> Trên production: các lệnh tương ứng (`migrate --force`, `config:cache`…) do pipeline Coolify/`entrypoint.sh` đảm nhiệm — không gõ tay trên server.
