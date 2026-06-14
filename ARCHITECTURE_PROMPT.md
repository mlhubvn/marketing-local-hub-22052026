# MLHUB AI — Bộ Prompt & Sổ tay lệnh (Vibecode)

Nơi lưu **prompt mẫu** để làm việc với Cursor (kèm plugin **Superpowers**) và **cheatsheet lệnh** (Laravel / Docker / Coolify) cho dự án MLHUB. Mở file này mỗi khi bắt đầu phiên làm việc, chọn luồng §2 rồi copy prompt §3.

> **Workspace mới (Codex / code gốc):** copy prompt trong `ARCHITECTURE_VIBECODE_BOOTSTRAP_PROMPT.md` — AI tự quét repo và sinh `.cursorrules` + bộ `ARCHITECTURE_*.md`.
>
> Đọc kèm: `.cursorrules`, `ARCHITECTURE_CHECKLIST.md`, `ARCHITECTURE_BACKEND.md`, `ARCHITECTURE_FRONTEND.md`, `ARCHITECTURE_FEATURE.md`.
> Mọi prompt nên đính kèm `@file` đúng chỗ thay vì `@Codebase` để tiết kiệm tài nguyên.
> **Commit / push / redeploy Coolify do chủ dự án làm thủ công** — AI chỉ sửa local + soạn commit message.

---

## 1. Mẹo dùng prompt hiệu quả

- **Khoanh vùng hẹp:** `@modules/AppBookingPages/...` thay vì cả dự án.
- **Nói rõ loại việc:** "sửa lỗi" (surgical) hay "tính năng mới" (ưu tiên extension point).
- **Nhắc ngữ cảnh chuẩn:** "theo `.cursorrules` và `ARCHITECTURE_CHECKLIST.md`".
- **Bật Superpowers:** gõ `/` trong chat → chọn skill (hoặc nói rõ skill — tra **§2.3** quy mô + lớp).
- **Việc rủi ro:** luôn **kế hoạch trước → duyệt → code** (`writing-plans` → bạn gõ **Duyệt** → `executing-plans`).
- **Một task một mục tiêu:** đừng gộp nhiều việc không liên quan vào một prompt.

---

## 2. Superpowers + quy trình Vibecode (MLHUB)

Plugin **Superpowers** trong Cursor cung cấp **skills** (quy trình bắt buộc) và **subagent** (rà soát chuyên sâu). Dùng để quản lý dự án, fix lỗi production và nâng cấp phiên bản mà không “nhảy cóc” vào code.

### 2.1 Cách gọi trong Cursor


| Cách         | Ví dụ                                                |
| ------------ | ---------------------------------------------------- |
| Gõ `/`       | `/brainstorming`, hoặc tìm tên skill trong danh sách |
| Trong prompt | “Dùng skill `systematic-debugging` cho lỗi 419”      |
| Subagent     | “Chạy code-reviewer sau khi xong dashboard”          |


> Lệnh cũ `/brainstorm`, `/write-plan`, `/execute-plan` **đã deprecated** — dùng skill cùng tên thay thế.

### 2.2 Luồng chuẩn theo loại việc

```mermaid
flowchart TD
  A[Bắt đầu phiên] --> B{Loại việc?}
  B -->|Tính năng / đổi lớn| C[brainstorming]
  C --> D[writing-plans]
  D --> E{Chủ dự án: Duyệt}
  E --> F[executing-plans]
  F --> G[verification-before-completion]
  G --> H[requesting-code-review / code-reviewer]
  B -->|Bug / 419 / 504 / 500| I[systematic-debugging]
  I --> J{Sửa xong?}
  J --> K[verification-before-completion]
  K --> H
  B -->|Nâng cấp upstream / module mới| L[brainstorming + writing-plans]
  L --> E
  E --> F
  F --> M[Cập nhật ARCHITECTURE_* + .cursorrules]
  B -->|Nhiều việc độc lập| N[dispatching-parallel-agents]
```



### 2.3 Bảng skill Superpowers — MLHUB (tra nhanh)

**Chọn skill trong 3 bước:** (1) quy mô lớn/vừa/nhỏ → (2) lớp hạ tầng/backend/frontend → (3) gọi tên skill trong prompt hoặc gõ `/tên-skill`.

**Cách gọi:** gõ `/` → chọn skill (vd `/systematic-debugging`), hoặc viết: `Dùng skill systematic-debugging cho lỗi Redis`. Lệnh cũ `/brainstorm`, `/write-plan` **không dùng** — thay bằng skill cùng tên.

**Thứ tự khi nhiều skill cùng lúc:** brainstorming hoặc systematic-debugging → writing-plans → *(bạn gõ **Duyệt**)* → executing-plans → verification-before-completion → code-reviewer.

```mermaid
flowchart LR
  subgraph scale [1. Quy mô]
    L[Lớn] --> BP[brainstorming + writing-plans]
    M[Vừa] --> SD[systematic-debugging]
    S[Nhỏ] --> SUR[surgical + @file]
  end
  subgraph layer [2. Lớp]
    INF[Hạ tầng] --> INFex[entrypoint / Coolify]
    BE[Backend] --> BEx[modules / migration]
    FE[Frontend] --> FEx[theme / Livewire / i18n]
  end
  scale --> layer
  layer --> DONE[Gõ skill trong prompt]
```



---

#### Bước 1 — Chọn theo **quy mô** (lớn → nhỏ)


| Quy mô  | Ví dụ trên MLHUB                                                                                                          | Skill (theo thứ tự)                                                                                                               | Không cần                                     |
| ------- | ------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------- |
| **Lớn** | Tính năng mới, addon marketplace, nâng cấp upstream, đổi `docker-compose` / `docker/entrypoint.sh`, refactor nhiều module | `brainstorming` → `writing-plans` → *(bạn gõ **Duyệt**)* → `executing-plans` → `verification-before-completion` → `code-reviewer` | Bỏ qua plan khi chưa brainstorm               |
| **Vừa** | 419/504 Livewire, Redis/queue fail, migration thiếu bảng, bug nhiều file, auth/reset password                             | `systematic-debugging` → sửa → `verification-before-completion`                                                                   | `brainstorming` (trừ khi đổi hướng kiến trúc) |
| **Nhỏ** | Sửa 1 Blade, 1 key `lang/vi.json`, typo nhãn, format tiền/ngày 1 chỗ                                                      | Nói rõ file `@modules/...` — AI làm surgical; thêm `verification-before-completion` nếu đụng logic                                | `writing-plans`, `executing-plans`            |


**Gợi ý nói trong prompt:** `Quy mô: Lớn — thêm module CRM public API` / `Quy mô: Nhỏ — sửa nhãn invoices.blade.php`.

---

#### Bước 2 — Chọn theo **lớp** (hạ tầng → backend → frontend)


| Lớp                  | Phạm vi MLHUB                                                                  | Ví dụ `@file`                                                           | Skill hay dùng                                                                                                    |
| -------------------- | ------------------------------------------------------------------------------ | ----------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------- |
| **Hạ tầng & deploy** | Coolify, Docker, Redis, MySQL, queue worker, `.env.example`                    | `@docker/entrypoint.sh`, `@docker-compose.yaml`, `@.env.example`        | Lớn: `writing-plans`. Lỗi: `systematic-debugging` (log deploy / `laravel.log`)                                    |
| **Backend**          | `modules/`*, migration, Fortify, plan limit, queue job, API/webhook            | `@modules/AppBilling/...`, `@config/mlhub.php`, `@database/migrations/` | Bug: `systematic-debugging`. Logic mới: `test-driven-development` (tùy chọn). Xong cụm: `code-reviewer`           |
| **Frontend**         | Theme `mlhubfrontend` / `mlhubbackend`, Livewire portal, i18n, marketing guest | `@resources/themes/...`, `@lang/vi.json`, `@app/Livewire/`              | Đổi copy: nhỏ, nhắc đồng bộ `en.json` + `vi.json` (`.cursorrules` §3.7). UI lỗi hydration: `systematic-debugging` |
| **Quy trình / Git**  | Nhiều task song song, tách nhánh, gộp PR                                       | —                                                                       | `dispatching-parallel-agents`, `using-git-worktrees`, `finishing-a-development-branch`                            |


**Gợi ý nói trong prompt:** `Lớp: Frontend — trang reset password Livewire` / `Lớp: Hạ tầng — container restart 10 lần`.

---

#### Ma trận nhanh — quy mô × lớp (ô đầu tiên = skill mở đầu)


|         | **Hạ tầng**                     | **Backend**                   | **Frontend**                            |
| ------- | ------------------------------- | ----------------------------- | --------------------------------------- |
| **Lớn** | writing-plans → executing-plans | brainstorming → writing-plans | brainstorming (+ i18n §3.7 trong plan)  |
| **Vừa** | systematic-debugging            | systematic-debugging          | systematic-debugging (419/hydration)    |
| **Nhỏ** | — (tránh sửa tay trên server)   | @file surgical                | @file + `lang/en.json` + `lang/vi.json` |


Sau mọi ô **Lớn / Vừa** có sửa code: thêm **verification-before-completion**. Epic xong: **code-reviewer**.

---

#### Bước 3 — Bảng đầy đủ từng skill


| Skill                              | Là gì (1 câu)                                   | Dùng khi                                             | Ví dụ MLHUB                                                                 | Không dùng                          |
| ---------------------------------- | ----------------------------------------------- | ---------------------------------------------------- | --------------------------------------------------------------------------- | ----------------------------------- |
| **using-superpowers**              | Cách bật và chọn skill trong Cursor             | Phiên đầu, chưa quen `/`                             | “Đọc skill using-superpowers rồi hướng dẫn tôi chọn skill cho task hôm nay” | Đã chọn skill ở Bước 1–2            |
| **brainstorming**                  | Làm rõ yêu cầu trước khi code                   | Tính năng mới, showcase gói free, đổi luồng đăng ký  | “Brainstorm: user plan_id null dùng full addon với hạn mức”                 | Sửa lỗi đã có log rõ                |
| **writing-plans**                  | Viết kế hoạch từng bước, **chưa code**          | Sau brainstorm; trước đụng `Dockerfile` / migration  | “writing-plans cho mlhub:install + bỏ demo”                                 | Đã có plan trong chat, chỉ cần code |
| **executing-plans**                | Thực hiện plan đã **Duyệt**                     | Bạn gõ **Duyệt** sau `writing-plans`                 | “executing-plans theo plan ở trên”                                          | Chưa duyệt kế hoạch                 |
| **systematic-debugging**           | Tìm **nguyên nhân gốc**, cấm đoán mò            | 419, 504, Redis DNS, bảng thiếu, reset password fail | “systematic-debugging + log dòng ERROR Redis”                               | Đổi 1 câu tiếng Việt trong JSON     |
| **test-driven-development**        | Viết test fail trước, rồi code                  | Guard plan, credit, upsert khách — logic dễ tái hiện | “TDD cho PlanLimitGuard ensureCampaign”                                     | Chỉ sửa Blade/CSS                   |
| **verification-before-completion** | Chứng minh đã chạy pint/test trước khi báo xong | Trước mọi lần bạn commit                             | “verification-before-completion trên file vừa sửa”                          | Chưa sửa file nào                   |
| **requesting-code-review**         | Soạn checklist review cho người/AI              | Xong epic (billing, auth, CRM)                       | “requesting-code-review cụm no_plan env”                                    | Diff 5 dòng                         |
| **code-reviewer** (subagent)       | Agent rà soát theo plan + `.cursorrules`        | Sau dashboard, auth, deploy, addon mới               | “Chạy code-reviewer sau fix reset password”                                 | Mỗi chỉnh màu nút                   |
| **receiving-code-review**          | Phân tích feedback PR, không sửa mù             | Có comment PR / review cần verify                    | “receiving-code-review: comment về tenant scope”                            | —                                   |
| **finishing-a-development-branch** | Merge, PR, dọn nhánh                            | Nhánh feature xong, cần gộp                          | “finishing-a-development-branch nhánh showcase-env”                         | Đang code dở                        |
| **dispatching-parallel-agents**    | Chạy song song 2+ việc độc lập                  | Vừa sửa i18n marketing + migration blog              | “parallel: frontend home + backend migration”                               | Một bug một file                    |
| **using-git-worktrees**            | Clone nhánh riêng, không đụng working tree      | Thử nghiệm lớn song song production                  | “worktree thử refactor AdminPlans”                                          | Hotfix 1 file trên nhánh hiện tại   |


---

#### Ghép nhanh — copy vào prompt

```
MLHUB | Quy mô: Vừa | Lớp: Backend
Dùng skill systematic-debugging. Evidence: @storage/logs/laravel.log (dòng ERROR).
Theo .cursorrules — surgical fix. verification-before-completion trước khi báo xong.
```

```
MLHUB | Quy mô: Lớn | Lớp: Frontend + Backend
brainstorming → writing-plans → chờ "Duyệt" → executing-plans.
Phạm vi: @modules/AppBilling @resources/themes/guest/mlhubfrontend
```

### 2.4 MASTER PROMPT — Ổn định workspace / nâng cấp (copy nguyên khối)

Dùng khi vừa đổi nhiều file, chuẩn bị production, hoặc sau khi merge upstream. **Không code** cho đến khi bạn **Duyệt** kế hoạch.

```
Act as Staff Engineer for MLHUB (Laravel 13 + Livewire 4, modular monolith, production mlhub.vn).

Use Superpowers skills in order: brainstorming → writing-plans → (wait for my "Duyệt") → executing-plans → verification-before-completion → code-reviewer.

PHASE 1 — Read and internalize:
- .cursorrules
- ARCHITECTURE_CHECKLIST.md, ARCHITECTURE_BACKEND.md, ARCHITECTURE_FRONTEND.md, ARCHITECTURE_FEATURE.md, ARCHITECTURE_PROMPT.md
Propose .cursorrules updates only if ARCHITECTURE_* and code diverge.

PHASE 2 — Infra (local mirrors production intent):
Inspect .env.example, docker-compose.yaml, Dockerfile, docker/entrypoint.sh.
Focus: Redis session/cache/queue, TRUSTED_PROXIES, SESSION_DOMAIN, single queue worker, Livewire deploy cache, 419/504 causes.

PHASE 3 — Code health:
Review recent changes: @app/Livewire/Portal/Dashboard.php, @app/Providers/AppServiceProvider.php, @bootstrap/app.php, @modules/AdminLog, middleware, seeders.
Cross-check ARCHITECTURE_* and .cursorrules. List P0/P1/P2 — no code yet.

PHASE 4 — Output a step-by-step plan (writing-plans): rules → infra → code fixes → verification checklist.
Wait for my approval "Duyệt" before implementing.
```

### 2.5 MASTER PROMPT — Sửa lỗi production (419 / 504 / 500)

```
MLHUB production bug. Use skill systematic-debugging — do NOT patch until root cause is identified.

Symptom: <mô tả, vd Livewire 419 on /portal/dashboard>
Evidence: @storage/logs/laravel.log OR Admin → Settings → Logs (paste ERROR lines)
Repro: <URL, browser, after deploy? yes/no>

Read .cursorrules (Livewire 419/504 section) and @ARCHITECTURE_CHECKLIST.md.

Checklist to investigate:
- Deploy snapshot stale? (Ctrl+F5, entrypoint Livewire JS sync)
- SESSION_DOMAIN / APP_URL on Coolify
- Parallel wire:init (portal dashboard must use loadDashboardSections once)
- Redis session down / APP_KEY rotated
- Heavy QrScan count / missing migration index

Surgical fix only. After fix: verification-before-completion + list Coolify env if any.
Suggest commit message. Do not git push.
```

### 2.6 MASTER PROMPT — Nâng cấp phiên bản tác giả / module marketplace

```
MLHUB upstream/marketplace upgrade. Skills: brainstorming → writing-plans → wait "Duyệt" → executing-plans.

What changed: <phiên bản / module tên>
Attach: @modules/<ModuleName>/ (if known)

Before coding:
1. Diff scope vs .cursorrules (prefer edit existing files, modules/Custom* for new logic)
2. List new migrations under modules/*/Database/Migrations
3. Plan: migrate on deploy (entrypoint), portal smoke routes, update ARCHITECTURE_* counts (Admin*/App*/Payment*)

After deploy checklist:
- Coolify log: migrate OK, "Livewire JS synced"
- php artisan optimize:clear then optimize (or rely on entrypoint)
- Portal: CRM, email-automation, loyalty, reports — no 500
- Fresh DB (staging only): set MLHUB_FIRST_USER_* → php artisan mlhub:install

Do not run migrate:fresh on production. Document new env keys in .env.example only.
```

### 2.8 MASTER PROMPT — Cài code gốc lên Coolify

Dùng khi có **code gốc từ dev**, chưa tùy biến — chỉ cần AI quét repo và hướng dẫn/cấu hình deploy Coolify (App + MySQL + Redis riêng).

Copy khối prompt trong `ARCHITECTURE_FRESH_START_MASTER_PROMPT.md`.

### 2.9 Sau khi AI báo "xong" (chủ dự án)

1. Đọc tóm tắt file đổi + **commit message gợi ý**.
2. Coolify **Environment Variables** (nếu AI liệt kê).
3. Commit + push → đợi build.
4. Trên site: **Ctrl+F5** trang portal/admin (tránh Livewire 419 tab cũ).
5. Ghi note vào §8 (sổ tay cá nhân) nếu gặp cạm bẫy mới.

---

## 3. Bộ Prompt mẫu (copy & điền)

### 3.1 Khởi động phiên làm việc (skill: brainstorming)

```
Bối cảnh: MLHUB (Laravel 13 + Livewire 4, production mlhub.vn). Dùng skill brainstorming.
Đọc lướt .cursorrules và ARCHITECTURE_CHECKLIST.md (và ARCHITECTURE_PROMPT.md §2 nếu cần).
Mục tiêu hôm nay: <mô tả>.
Xác nhận đã nắm quy trình → đề xuất bước tiếp theo (có cần writing-plans không). CHƯA code.
```

### 3.2 Sửa lỗi (Bug fix — skill: systematic-debugging)

```
Sửa lỗi trong @<đường-dẫn-file>. 
Hiện tượng: <mô tả lỗi + cách tái hiện>. 
Kỳ vọng: <hành vi đúng>. 
Yêu cầu: sửa tối thiểu, bám đúng phong cách file (vibecode §3), scope theo auth()->id(), 
không refactor ngoài phạm vi. Giải thích nguyên nhân gốc trước khi sửa.
```

### 3.3 Tính năng mới (skills: brainstorming → writing-plans)

```
Thêm tính năng: <mô tả>. 
Đây là tính năng MỚI nên ưu tiên điểm mở rộng (modules/Custom* + providers.marketplace.php) 
theo ARCHITECTURE_BACKEND.md §5, KHÔNG sửa core nếu không cần. 
Hãy trình bày kế hoạch (file/route/model/migration/permission/env mới) để tôi duyệt, rồi mới code.
```

### 3.4 Một growth tool mới (theo engine lb_campaigns)

```
Tôi muốn thêm growth tool kiểu <tên>. 
Hãy theo đúng mẫu engine dùng chung: model QrCampaign (lb_campaigns) với type='<key>' + settings JSON, 
public qua QrCampaignPublicController, PlanLimitGuard, CustomerUpserter, GrowthToolNotifier 
(xem ARCHITECTURE_FEATURE.md §0). Trình bày kế hoạch trước.
```

### 3.5 Giao diện / Theme (White-label)

```
Chỉnh giao diện <mô tả> ở khu <guest/portal/admin>. 
Lưu ý theme guest `mlhubfrontend`, backend `mlhubbackend` (ARCHITECTURE_FRONTEND.md §2.2). 
Dùng lại <x-ui.*>/<x-shared.*>, màu dùng token var(--theme-*), không hard-code màu. 
Nếu chỉ là branding nhỏ, gợi ý dùng Admin → Themes → Custom CSS/JS thay vì sửa file.
```

### 3.6 Tính năng AI (có credit)

```
Thêm/sửa tính năng AI: <mô tả>. 
Bắt buộc: feature gate → credit_service()->ensureCanConsume() trước khi gọi LLM → 
try/catch (Throwable) có fallback → consume_credits() khi thành công (mẫu AppAIContent). 
Scope lịch sử theo workspaceOwnerUserId().
```

### 3.7 Database migration (🔴 skill: writing-plans)

```
Tôi cần thay đổi schema: <mô tả>. 
ĐỪNG code ngay. Hãy trình bày kế hoạch: bảng/cột (lb_*), kiểu dữ liệu, index, ràng buộc, 
chỉ thêm mới/nullable hay có đổi/xóa, phương án rollback, và cách chạy qua pipeline 
(docker/entrypoint.sh / migrate --force), tuyệt đối không migrate:fresh trên production.
```

### 3.8 Payment / Subscription / Credit (🔴 plan trước)

```
Làm việc với cổng thanh toán <PaymentXxx>: <mô tả>. 
Trình bày kế hoạch trước: contract/luồng tiền (checkout→webhook→subscription/credit), 
ca lỗi/hoàn tiền/hết hạn, webhook URL & env cần cấu hình, đảm bảo không log dữ liệu nhạy cảm. 
Test sandbox trước.
```

### 3.9 Rà soát / Audit trước production (subagent: code-reviewer)

```
Audit module @<module> theo ARCHITECTURE_FEATURE.md. 
Kiểm tra: IDOR (scope tenant), thiếu rate-limit/captcha ở endpoint public, XSS khi hiển thị input khách, 
race condition, và xử lý lỗi. Liệt kê rủi ro theo mức P0/P1/P2 + đề xuất fix, CHƯA sửa.
```

### 3.10 Cập nhật tài liệu / sau khi nâng cấp upstream

```
Tôi vừa cập nhật phiên bản tác giả và/hoặc cài module mới.
Dùng luồng ARCHITECTURE_PROMPT.md §2.6 (upstream upgrade): brainstorming → writing-plans → chờ Duyệt.
Quét surgical: modules/*, migration mới, routes public → cập nhật .cursorrules, Dockerfile, docker/entrypoint.sh, .env.example, ARCHITECTURE_*.md.
```

```
Tôi vừa thay đổi <mô tả>. Hãy cập nhật các file ARCHITECTURE_*.md / .cursorrules liên quan 
cho khớp thực tế (surgical, không viết lại toàn bộ) để Cursor sau này không phải quét lại dự án.
```

### 3.11 Chuẩn bị commit (skill: verification-before-completion)

```
Tóm tắt thay đổi của phiên này và soạn commit message đúng style repo. 
Liệt kê file đổi, migration/route/permission/env mới. 
Nhắc tôi nếu có biến .env cần cập nhật trên Coolify. Chưa push cho tới khi tôi đồng ý.
Chạy verification-before-completion: pint trên file đã sửa, nêu test/manual check cần làm.
```

### 3.12 Livewire 419 / "page expired" (rút gọn)

```
Lỗi Livewire 419 trên <URL>. Skill systematic-debugging.
@bootstrap/app.php @app/Livewire/Portal/Dashboard.php @resources/themes/app/mlhubbackend/resources/views/livewire/portal/dashboard.blade.php
Đã Ctrl+F5 chưa: <có/không>. Vừa deploy: <có/không>.
Sửa surgical; nhắc Coolify: APP_URL, SESSION_DOMAIN, TRUSTED_PROXIES. Không SSH vá tay server.
```

### 3.13 Duyệt kế hoạch rồi triển khai (một câu)

```
Duyệt. Triển khai kế hoạch vừa lập theo executing-plans — từng bước, surgical, cập nhật ARCHITECTURE_* nếu cần.
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

> MLHUB **đã gỡ Web Installer** và **gỡ Admin Faker / demo volume**. Bootstrap qua **migrate** (tự động mỗi deploy) + `**mlhub:install`** (một lần, DB trống) + env Coolify.
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
| `MLHUB_STARTING_ID`           | `147123468` (AUTO_INCREMENT sau seed)                  |
| `MLHUB_LICENSE_PURCHASE_CODE` | Mã license Stackposts (Coolify)                        |
| `MLHUB_LICENSE_DOMAIN`        | `mlhub.vn`                                             |
| `MLHUB_ALLOW_RESET_DEMO`      | `false` (chặn `db:wipe` trên production)               |
| `RUN_QUEUE_WORKER`            | `true` (hoặc `false` nếu worker Coolify riêng)         |
| `MAIL_PASSWORD`               | SMTP API key (seed ghi `options.smtp_password` nếu có) |


**Module `CustomMLHUB`:** site options VN (`format_date` `d/m/Y`, VND, timezone), seeder admin, email/template packs hệ thống. Script dev Việt hóa AI templates: `modules/CustomMLHUB/Scripts/`.

#### A. Hai lệnh MLHUB (trong container app)


| Lệnh                                 | Khi dùng                                    | Hành vi                                                                                                                                                                                                                                |
| ------------------------------------ | ------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `php artisan mlhub:install`          | DB mới hoặc muốn **xóa sạch** cài lại       | `migrate:fresh` (xóa toàn bộ bảng) → seed từ ID `147123468` → `optimize`. Hỏi xác nhận; `--force` bỏ qua hỏi. Production: tạm `MLHUB_ALLOW_RESET_DEMO=true`.                                                                           |
| `php artisan mlhub:update`           | Đã có dữ liệu, muốn **cập nhật** sau deploy | `migrate` (migration mới) → seed đồng bộ catalog official `mlhub-*`, giữ nguyên plan legacy/custom và không xóa user/campaign → bản ghi seed **mới** nối ID sau max hiện có → `IdSequence::apply()` → `optimize`. |
| `php artisan mlhub:sync-env-options` | Sau khi đổi env Coolify                     | Ghi env có giá trị → **Admin → Cài đặt** (`options`). Tự chạy sau `migrate` mỗi deploy (`docker/entrypoint.sh`).                                                                                                                       |


**Admin Settings ↔ Coolify:** Mật khẩu SMTP, captcha, Stripe, Google OAuth, license… đặt trong **Environment Variables** (xem `.env.example` mục *Admin Settings ↔ Coolify*). Map chi tiết: `modules/CustomMLHUB/config/env_options.php`. Sau `mlhub:install` (xóa DB), cấu hình **khôi phục từ env** — không cần nhập lại tay nếu đã copy đủ biến lên Coolify.

```bash
docker exec -it <container_app> sh
cd /var/www/html
php artisan mlhub:install    # cài sạch — XÓA HẾT dữ liệu
php artisan mlhub:update     # cập nhật — GIỮ dữ liệu cũ
```

**Không** seed dữ liệu demo / QR volume / business mẫu.

#### B. Deploy thường (đã cài xong)

Push GitHub → Coolify redeploy → `docker/entrypoint.sh` chỉ **migrate** + optimize. Không tự chạy seed. Chạy thủ công `mlhub:update` khi cần đồng bộ seed/migration mới mà giữ dữ liệu khách.

#### C. DB trống từ SQL (khi cần)

```sql
DROP DATABASE default;
CREATE DATABASE default CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Sau đó: `php artisan mlhub:install` (hoặc import SQL đã chuẩn hóa ID rồi `mlhub:update` để đồng bộ seed).

#### D. Sau cài — kiểm tra

- Đăng nhập bằng `MLHUB_FIRST_USER_EMAIL` / mật khẩu đã đặt → **Admin** + **Portal**.
- Portal trống (chưa có business/campaign) — đúng production.
- Admin → Cài đặt: ngày `dd/mm/yyyy`, tiền `₫` (từ `mlhub_site_options.php`).

> **Không** import dump SQL cũ có `demo@mlhub.vn` / volume faker. Logo upload thủ công qua Admin hoặc storage volume.

### 4.2 Backup production an toàn

- **Code:** GitHub là source of truth; dùng tag/commit và Coolify rollback, không tải source từ thư mục đang chạy.
- **Database:** dùng backup/snapshot riêng của resource MySQL trong Coolify.
- **Uploads/log cần giữ:** dùng snapshot/backup private của Persistent Storage `mlhub-storage`.
- **Cấm:** tạo archive source, log hoặc storage trong `public/`; không cung cấp URL tải backup qua `mlhub.vn`.
- Restore phải thực hiện qua quy trình Coolify đã duyệt, có snapshot trước và kiểm tra trên staging.

---

## 5. Cheatsheet Docker / SSH Coolify (CHỈ để CHẨN ĐOÁN — đọc log/kiểm tra)

> 🔴 **Quy tắc cốt lõi (ARCHITECTURE_CHECKLIST.md §1):** KHÔNG dùng các lệnh này để **thay đổi** hạ tầng/code/config trên server. Mọi thay đổi đi qua local → GitHub → Coolify build. Các lệnh dưới đây chỉ để **xem trạng thái, đọc log, debug**.

Tham chiếu container (từ `docker-compose.yaml`): service `app`, chạy port 80 nội bộ, không publish host port, volume `mlhub-storage:/var/www/html/storage`, domain `mlhub.vn`.

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
5. Nếu cần chạy migration khi deploy: đảm bảo nằm trong `docker/entrypoint.sh`/pipeline.

### 5.1 Xem & tải `laravel.log` an toàn (module AdminLog)

> ✅ **Dùng trang admin, KHÔNG copy log ra `public/`.** Xem/tải log tại **Admin → Cài đặt → Logs** (`https://mlhub.vn/admin/settings/log`). Trang này nằm sau `auth` + `EnsureAdminAccess` (chỉ admin), tránh phơi log ra Internet.
>
> 🔴 **TUYỆT ĐỐI KHÔNG** copy `storage/logs/*.log` vào `public/` để tải qua URL công khai — log chứa stack trace, DB host, user ID, email, token; ai có URL (kể cả Google) đều đọc được.

Tại trang đó có thể: chọn file log, xem nhanh phần cuối (tail), **tải xuống**, **xoá nội dung** (clear) hoặc **xoá file**. Code: `modules/AdminLog`.

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
| Rate-limit          | `throttle:10,1` trên 5 form public (booking/coupon/feedback/lead/review)                                         |
| Storage             | disk `public` (S3 trống)                                                                                         |
| Theme active        | guest = `mlhubfrontend`, backend = `mlhubbackend` (env `THEME_FRONTEND` / `THEME_BACKEND`)                       |
| Session / URL prod  | `SESSION_DOMAIN=.mlhub.vn`, `APP_URL=https://mlhub.vn`, `TRUSTED_PROXIES=`*; không publish host port             |
| Log production      | `LOG_CHANNEL=stack`, `LOG_STACK=stderr,daily`, `LOG_LEVEL=warning`                                               |
| Deploy              | Coolify + Traefik (HTTP→HTTPS, Let's Encrypt)                                                                    |


---

## 7. Sổ tay cá nhân (tự ghi note mỗi lần code)

> Khu vực để bạn tự bổ sung. Gợi ý ghi theo định dạng ngày + việc + lệnh/đường dẫn liên quan.

### DD-MM-YYYY — <tiêu đề việc>

- Bối cảnh / mục tiêu:
- File/module liên quan: `@...`
- Lệnh đã dùng:
- Kết quả / lưu ý / cạm bẫy gặp phải:
- Việc cần làm tiếp:

## 8. Lựa chọn mô hình AI trong Cursor

### 1. Giữ nguyên **Opus 4.8 High (Core)**

- **Khi nào dùng:** Dành cho các tác vụ cốt lõi mà chúng ta vừa bàn tới (Cấu hình luồng Email/SMTP, Viết middleware Rate-limit/Captcha chống Spam, hoặc xử lý Race-condition cho Booking).
- **Lý do:** Dòng Opus luôn là "nhà vô địch" trong việc đọc hiểu ngữ cảnh dài. Nó sẽ nuốt trọn bộ `ARCHITECTURE_*.md` của bạn, nhớ rất kỹ các quy tắc an toàn (không dùng `migrate:fresh` trên production, luôn scope theo `auth()->id()`), và đưa ra kế hoạch (Plan) cực kỳ sắc bén trước khi code.

### 2. Dùng **Composer 2.5 Fast (Giải pháp)**

- **Khi nào dùng:** Khi bạn đã thảo luận xong giải pháp với Opus và chốt được phương án, hãy dùng tính năng Composer (phím tắt thường là `Cmd/Ctrl + I` hoặc `Cmd/Ctrl + K` trên toàn dự án) để AI tự động áp dụng các thay đổi đó vào nhiều file cùng lúc (ví dụ: chèn throttle vào hàng loạt file `Routes/web.php` của các module).
- **Lý do:** Tốc độ thực thi cực nhanh và gõ code trực tiếp vào file (surgical edits) rất tốt.

### 3. Dùng **Sonnet 4.6 Medium** hoặc **GPT-5.5 Medium (Theme)**

- **Khi nào dùng:** Tác vụ nhẹ: chỉnh CSS Tailwind trên theme `mlhubfrontend` / `mlhubbackend`, bổ sung `lang/vi.json`, regex validation.
- **Luôn kèm:** `.cursorrules` + `@file` blade cụ thể; không thay thế bước `writing-plans` cho tính năng lớn.
- **Lý do:** Tiết kiệm "Premium credits" của bạn, tốc độ phản hồi nhanh hơn Opus, và dư sức xử lý các file đơn lẻ.

# MLHUB AI — Bộ Prompt & Sổ tay lệnh (Vibecode)

Nơi lưu **prompt mẫu** để làm việc với Cursor (kèm plugin **Superpowers**) và **cheatsheet lệnh** (Laravel / Docker / Coolify) cho dự án MLHUB. Mở file này mỗi khi bắt đầu phiên làm việc, chọn luồng §2 rồi copy prompt §3.

> **Workspace mới (Codex / code gốc):** copy prompt trong `ARCHITECTURE_VIBECODE_BOOTSTRAP_PROMPT.md` — AI tự quét repo và sinh `.cursorrules` + bộ `ARCHITECTURE_*.md`.
>
> Đọc kèm: `.cursorrules`, `ARCHITECTURE_CHECKLIST.md`, `ARCHITECTURE_BACKEND.md`, `ARCHITECTURE_FRONTEND.md`, `ARCHITECTURE_FEATURE.md`. Mọi prompt nên đính kèm `@file` đúng chỗ thay vì `@Codebase` để tiết kiệm tài nguyên. **Commit / push / redeploy Coolify do chủ dự án làm thủ công** — AI chỉ sửa local + soạn commit message.

---

## 1. Mẹo dùng prompt hiệu quả

- **Khoanh vùng hẹp:** `@modules/AppBookingPages/...` thay vì cả dự án.
- **Nói rõ loại việc:** "sửa lỗi" (surgical) hay "tính năng mới" (ưu tiên extension point).
- **Nhắc ngữ cảnh chuẩn:** "theo `.cursorrules` và `ARCHITECTURE_CHECKLIST.md`".
- **Bật Superpowers:** gõ `/` trong chat → chọn skill (hoặc nói rõ skill — tra **§2.3** quy mô + lớp).
- **Việc rủi ro:** luôn **kế hoạch trước → duyệt → code** (`writing-plans` → bạn gõ **Duyệt** → `executing-plans`).
- **Một task một mục tiêu:** đừng gộp nhiều việc không liên quan vào một prompt.

---

## 2. Superpowers + quy trình Vibecode (MLHUB)

Plugin **Superpowers** trong Cursor cung cấp **skills** (quy trình bắt buộc) và **subagent** (rà soát chuyên sâu). Dùng để quản lý dự án, fix lỗi production và nâng cấp phiên bản mà không “nhảy cóc” vào code.

### 2.1 Cách gọi trong Cursor


| Cách         | Ví dụ                                                |
| ------------ | ---------------------------------------------------- |
| Gõ `/`       | `/brainstorming`, hoặc tìm tên skill trong danh sách |
| Trong prompt | “Dùng skill `systematic-debugging` cho lỗi 419”      |
| Subagent     | “Chạy code-reviewer sau khi xong dashboard”          |


> Lệnh cũ `/brainstorm`, `/write-plan`, `/execute-plan` **đã deprecated** — dùng skill cùng tên thay thế.

### 2.2 Luồng chuẩn theo loại việc

```mermaid
flowchart TD
  A[Bắt đầu phiên] --> B{Loại việc?}
  B -->|Tính năng / đổi lớn| C[brainstorming]
  C --> D[writing-plans]
  D --> E{Chủ dự án: Duyệt}
  E --> F[executing-plans]
  F --> G[verification-before-completion]
  G --> H[requesting-code-review / code-reviewer]
  B -->|Bug / 419 / 504 / 500| I[systematic-debugging]
  I --> J{Sửa xong?}
  J --> K[verification-before-completion]
  K --> H
  B -->|Nâng cấp upstream / module mới| L[brainstorming + writing-plans]
  L --> E
  E --> F
  F --> M[Cập nhật ARCHITECTURE_* + .cursorrules]
  B -->|Nhiều việc độc lập| N[dispatching-parallel-agents]
```



### 2.3 Bảng skill Superpowers — MLHUB (tra nhanh)

**Chọn skill trong 3 bước:** (1) quy mô lớn/vừa/nhỏ → (2) lớp hạ tầng/backend/frontend → (3) gọi tên skill trong prompt hoặc gõ `/tên-skill`.

**Cách gọi:** gõ `/` → chọn skill (vd `/systematic-debugging`), hoặc viết: `Dùng skill systematic-debugging cho lỗi Redis`. Lệnh cũ `/brainstorm`, `/write-plan` **không dùng** — thay bằng skill cùng tên.

**Thứ tự khi nhiều skill cùng lúc:** brainstorming hoặc systematic-debugging → writing-plans → *(bạn gõ **Duyệt**)* → executing-plans → verification-before-completion → code-reviewer.

```mermaid
flowchart LR
  subgraph scale [1. Quy mô]
    L[Lớn] --> BP[brainstorming + writing-plans]
    M[Vừa] --> SD[systematic-debugging]
    S[Nhỏ] --> SUR[surgical + @file]
  end
  subgraph layer [2. Lớp]
    INF[Hạ tầng] --> INFex[entrypoint / Coolify]
    BE[Backend] --> BEx[modules / migration]
    FE[Frontend] --> FEx[theme / Livewire / i18n]
  end
  scale --> layer
  layer --> DONE[Gõ skill trong prompt]
```



---

#### Bước 1 — Chọn theo **quy mô** (lớn → nhỏ)


| Quy mô  | Ví dụ trên MLHUB                                                                                                          | Skill (theo thứ tự)                                                                                                               | Không cần                                     |
| ------- | ------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------- |
| **Lớn** | Tính năng mới, addon marketplace, nâng cấp upstream, đổi `docker-compose` / `docker/entrypoint.sh`, refactor nhiều module | `brainstorming` → `writing-plans` → *(bạn gõ **Duyệt**)* → `executing-plans` → `verification-before-completion` → `code-reviewer` | Bỏ qua plan khi chưa brainstorm               |
| **Vừa** | 419/504 Livewire, Redis/queue fail, migration thiếu bảng, bug nhiều file, auth/reset password                             | `systematic-debugging` → sửa → `verification-before-completion`                                                                   | `brainstorming` (trừ khi đổi hướng kiến trúc) |
| **Nhỏ** | Sửa 1 Blade, 1 key `lang/vi.json`, typo nhãn, format tiền/ngày 1 chỗ                                                      | Nói rõ file `@modules/...` — AI làm surgical; thêm `verification-before-completion` nếu đụng logic                                | `writing-plans`, `executing-plans`            |


**Gợi ý nói trong prompt:** `Quy mô: Lớn — thêm module CRM public API` / `Quy mô: Nhỏ — sửa nhãn invoices.blade.php`.

---

#### Bước 2 — Chọn theo **lớp** (hạ tầng → backend → frontend)


| Lớp                  | Phạm vi MLHUB                                                                  | Ví dụ `@file`                                                           | Skill hay dùng                                                                                                    |
| -------------------- | ------------------------------------------------------------------------------ | ----------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------- |
| **Hạ tầng & deploy** | Coolify, Docker, Redis, MySQL, queue worker, `.env.example`                    | `@docker/entrypoint.sh`, `@docker-compose.yaml`, `@.env.example`        | Lớn: `writing-plans`. Lỗi: `systematic-debugging` (log deploy / `laravel.log`)                                    |
| **Backend**          | `modules/`*, migration, Fortify, plan limit, queue job, API/webhook            | `@modules/AppBilling/...`, `@config/mlhub.php`, `@database/migrations/` | Bug: `systematic-debugging`. Logic mới: `test-driven-development` (tùy chọn). Xong cụm: `code-reviewer`           |
| **Frontend**         | Theme `mlhubfrontend` / `mlhubbackend`, Livewire portal, i18n, marketing guest | `@resources/themes/...`, `@lang/vi.json`, `@app/Livewire/`              | Đổi copy: nhỏ, nhắc đồng bộ `en.json` + `vi.json` (`.cursorrules` §3.7). UI lỗi hydration: `systematic-debugging` |
| **Quy trình / Git**  | Nhiều task song song, tách nhánh, gộp PR                                       | —                                                                       | `dispatching-parallel-agents`, `using-git-worktrees`, `finishing-a-development-branch`                            |


**Gợi ý nói trong prompt:** `Lớp: Frontend — trang reset password Livewire` / `Lớp: Hạ tầng — container restart 10 lần`.

---

#### Ma trận nhanh — quy mô × lớp (ô đầu tiên = skill mở đầu)


|         | **Hạ tầng**                     | **Backend**                   | **Frontend**                            |
| ------- | ------------------------------- | ----------------------------- | --------------------------------------- |
| **Lớn** | writing-plans → executing-plans | brainstorming → writing-plans | brainstorming (+ i18n §3.7 trong plan)  |
| **Vừa** | systematic-debugging            | systematic-debugging          | systematic-debugging (419/hydration)    |
| **Nhỏ** | — (tránh sửa tay trên server)   | @file surgical                | @file + `lang/en.json` + `lang/vi.json` |


Sau mọi ô **Lớn / Vừa** có sửa code: thêm **verification-before-completion**. Epic xong: **code-reviewer**.

---

#### Bước 3 — Bảng đầy đủ từng skill


| Skill                              | Là gì (1 câu)                                   | Dùng khi                                             | Ví dụ MLHUB                                                                 | Không dùng                          |
| ---------------------------------- | ----------------------------------------------- | ---------------------------------------------------- | --------------------------------------------------------------------------- | ----------------------------------- |
| **using-superpowers**              | Cách bật và chọn skill trong Cursor             | Phiên đầu, chưa quen `/`                             | “Đọc skill using-superpowers rồi hướng dẫn tôi chọn skill cho task hôm nay” | Đã chọn skill ở Bước 1–2            |
| **brainstorming**                  | Làm rõ yêu cầu trước khi code                   | Tính năng mới, showcase gói free, đổi luồng đăng ký  | “Brainstorm: user plan_id null dùng full addon với hạn mức”                 | Sửa lỗi đã có log rõ                |
| **writing-plans**                  | Viết kế hoạch từng bước, **chưa code**          | Sau brainstorm; trước đụng `Dockerfile` / migration  | “writing-plans cho mlhub:install + bỏ demo”                                 | Đã có plan trong chat, chỉ cần code |
| **executing-plans**                | Thực hiện plan đã **Duyệt**                     | Bạn gõ **Duyệt** sau `writing-plans`                 | “executing-plans theo plan ở trên”                                          | Chưa duyệt kế hoạch                 |
| **systematic-debugging**           | Tìm **nguyên nhân gốc**, cấm đoán mò            | 419, 504, Redis DNS, bảng thiếu, reset password fail | “systematic-debugging + log dòng ERROR Redis”                               | Đổi 1 câu tiếng Việt trong JSON     |
| **test-driven-development**        | Viết test fail trước, rồi code                  | Guard plan, credit, upsert khách — logic dễ tái hiện | “TDD cho PlanLimitGuard ensureCampaign”                                     | Chỉ sửa Blade/CSS                   |
| **verification-before-completion** | Chứng minh đã chạy pint/test trước khi báo xong | Trước mọi lần bạn commit                             | “verification-before-completion trên file vừa sửa”                          | Chưa sửa file nào                   |
| **requesting-code-review**         | Soạn checklist review cho người/AI              | Xong epic (billing, auth, CRM)                       | “requesting-code-review cụm no_plan env”                                    | Diff 5 dòng                         |
| **code-reviewer** (subagent)       | Agent rà soát theo plan + `.cursorrules`        | Sau dashboard, auth, deploy, addon mới               | “Chạy code-reviewer sau fix reset password”                                 | Mỗi chỉnh màu nút                   |
| **receiving-code-review**          | Phân tích feedback PR, không sửa mù             | Có comment PR / review cần verify                    | “receiving-code-review: comment về tenant scope”                            | —                                   |
| **finishing-a-development-branch** | Merge, PR, dọn nhánh                            | Nhánh feature xong, cần gộp                          | “finishing-a-development-branch nhánh showcase-env”                         | Đang code dở                        |
| **dispatching-parallel-agents**    | Chạy song song 2+ việc độc lập                  | Vừa sửa i18n marketing + migration blog              | “parallel: frontend home + backend migration”                               | Một bug một file                    |
| **using-git-worktrees**            | Clone nhánh riêng, không đụng working tree      | Thử nghiệm lớn song song production                  | “worktree thử refactor AdminPlans”                                          | Hotfix 1 file trên nhánh hiện tại   |


---

#### Ghép nhanh — copy vào prompt

```
MLHUB | Quy mô: Vừa | Lớp: Backend
Dùng skill systematic-debugging. Evidence: @storage/logs/laravel.log (dòng ERROR).
Theo .cursorrules — surgical fix. verification-before-completion trước khi báo xong.
```

```
MLHUB | Quy mô: Lớn | Lớp: Frontend + Backend
brainstorming → writing-plans → chờ "Duyệt" → executing-plans.
Phạm vi: @modules/AppBilling @resources/themes/guest/mlhubfrontend
```

### 2.4 MASTER PROMPT — Ổn định workspace / nâng cấp (copy nguyên khối)

Dùng khi vừa đổi nhiều file, chuẩn bị production, hoặc sau khi merge upstream. **Không code** cho đến khi bạn **Duyệt** kế hoạch.

```
Act as Staff Engineer for MLHUB (Laravel 13 + Livewire 4, modular monolith, production mlhub.vn).

Use Superpowers skills in order: brainstorming → writing-plans → (wait for my "Duyệt") → executing-plans → verification-before-completion → code-reviewer.

PHASE 1 — Read and internalize:
- .cursorrules
- ARCHITECTURE_CHECKLIST.md, ARCHITECTURE_BACKEND.md, ARCHITECTURE_FRONTEND.md, ARCHITECTURE_FEATURE.md, ARCHITECTURE_PROMPT.md
Propose .cursorrules updates only if ARCHITECTURE_* and code diverge.

PHASE 2 — Infra (local mirrors production intent):
Inspect .env.example, docker-compose.yaml, Dockerfile, docker/entrypoint.sh.
Focus: Redis session/cache/queue, TRUSTED_PROXIES, SESSION_DOMAIN, single queue worker, Livewire deploy cache, 419/504 causes.

PHASE 3 — Code health:
Review recent changes: @app/Livewire/Portal/Dashboard.php, @app/Providers/AppServiceProvider.php, @bootstrap/app.php, @modules/AdminLog, middleware, seeders.
Cross-check ARCHITECTURE_* and .cursorrules. List P0/P1/P2 — no code yet.

PHASE 4 — Output a step-by-step plan (writing-plans): rules → infra → code fixes → verification checklist.
Wait for my approval "Duyệt" before implementing.
```

### 2.5 MASTER PROMPT — Sửa lỗi production (419 / 504 / 500)

```
MLHUB production bug. Use skill systematic-debugging — do NOT patch until root cause is identified.

Symptom: <mô tả, vd Livewire 419 on /portal/dashboard>
Evidence: @storage/logs/laravel.log OR Admin → Settings → Logs (paste ERROR lines)
Repro: <URL, browser, after deploy? yes/no>

Read .cursorrules (Livewire 419/504 section) and @ARCHITECTURE_CHECKLIST.md.

Checklist to investigate:
- Deploy snapshot stale? (Ctrl+F5, entrypoint Livewire JS sync)
- SESSION_DOMAIN / APP_URL on Coolify
- Parallel wire:init (portal dashboard must use loadDashboardSections once)
- Redis session down / APP_KEY rotated
- Heavy QrScan count / missing migration index

Surgical fix only. After fix: verification-before-completion + list Coolify env if any.
Suggest commit message. Do not git push.
```

### 2.6 MASTER PROMPT — Nâng cấp phiên bản tác giả / module marketplace

```
MLHUB upstream/marketplace upgrade. Skills: brainstorming → writing-plans → wait "Duyệt" → executing-plans.

What changed: <phiên bản / module tên>
Attach: @modules/<ModuleName>/ (if known)

Before coding:
1. Diff scope vs .cursorrules (prefer edit existing files, modules/Custom* for new logic)
2. List new migrations under modules/*/Database/Migrations
3. Plan: migrate on deploy (entrypoint), portal smoke routes, update ARCHITECTURE_* counts (Admin*/App*/Payment*)

After deploy checklist:
- Coolify log: migrate OK, "Livewire JS synced"
- php artisan optimize:clear then optimize (or rely on entrypoint)
- Portal: CRM, email-automation, loyalty, reports — no 500
- Fresh DB (staging only): set MLHUB_FIRST_USER_* → php artisan mlhub:install

Do not run migrate:fresh on production. Document new env keys in .env.example only.
```

### 2.8 MASTER PROMPT — Cài code gốc lên Coolify

Dùng khi có **code gốc từ dev**, chưa tùy biến — chỉ cần AI quét repo và hướng dẫn/cấu hình deploy Coolify (App + MySQL + Redis riêng).

Copy khối prompt trong `ARCHITECTURE_FRESH_START_MASTER_PROMPT.md`.

### 2.9 Sau khi AI báo "xong" (chủ dự án)

1. Đọc tóm tắt file đổi + **commit message gợi ý**.
2. Coolify **Environment Variables** (nếu AI liệt kê).
3. Commit + push → đợi build.
4. Trên site: **Ctrl+F5** trang portal/admin (tránh Livewire 419 tab cũ).
5. Ghi note vào §8 (sổ tay cá nhân) nếu gặp cạm bẫy mới.

---

## 3. Bộ Prompt mẫu (copy & điền)

### 3.1 Khởi động phiên làm việc (skill: brainstorming)

```
Bối cảnh: MLHUB (Laravel 13 + Livewire 4, production mlhub.vn). Dùng skill brainstorming.
Đọc lướt .cursorrules và ARCHITECTURE_CHECKLIST.md (và ARCHITECTURE_PROMPT.md §2 nếu cần).
Mục tiêu hôm nay: <mô tả>.
Xác nhận đã nắm quy trình → đề xuất bước tiếp theo (có cần writing-plans không). CHƯA code.
```

### 3.2 Sửa lỗi (Bug fix — skill: systematic-debugging)

```
Sửa lỗi trong @<đường-dẫn-file>. 
Hiện tượng: <mô tả lỗi + cách tái hiện>. 
Kỳ vọng: <hành vi đúng>. 
Yêu cầu: sửa tối thiểu, bám đúng phong cách file (vibecode §3), scope theo auth()->id(), 
không refactor ngoài phạm vi. Giải thích nguyên nhân gốc trước khi sửa.
```

### 3.3 Tính năng mới (skills: brainstorming → writing-plans)

```
Thêm tính năng: <mô tả>. 
Đây là tính năng MỚI nên ưu tiên điểm mở rộng (modules/Custom* + providers.marketplace.php) 
theo ARCHITECTURE_BACKEND.md §5, KHÔNG sửa core nếu không cần. 
Hãy trình bày kế hoạch (file/route/model/migration/permission/env mới) để tôi duyệt, rồi mới code.
```

### 3.4 Một growth tool mới (theo engine lb_campaigns)

```
Tôi muốn thêm growth tool kiểu <tên>. 
Hãy theo đúng mẫu engine dùng chung: model QrCampaign (lb_campaigns) với type='<key>' + settings JSON, 
public qua QrCampaignPublicController, PlanLimitGuard, CustomerUpserter, GrowthToolNotifier 
(xem ARCHITECTURE_FEATURE.md §0). Trình bày kế hoạch trước.
```

### 3.5 Giao diện / Theme (White-label)

```
Chỉnh giao diện <mô tả> ở khu <guest/portal/admin>. 
Lưu ý theme guest `mlhubfrontend`, backend `mlhubbackend` (ARCHITECTURE_FRONTEND.md §2.2). 
Dùng lại <x-ui.*>/<x-shared.*>, màu dùng token var(--theme-*), không hard-code màu. 
Nếu chỉ là branding nhỏ, gợi ý dùng Admin → Themes → Custom CSS/JS thay vì sửa file.
```

### 3.6 Tính năng AI (có credit)

```
Thêm/sửa tính năng AI: <mô tả>. 
Bắt buộc: feature gate → credit_service()->ensureCanConsume() trước khi gọi LLM → 
try/catch (Throwable) có fallback → consume_credits() khi thành công (mẫu AppAIContent). 
Scope lịch sử theo workspaceOwnerUserId().
```

### 3.7 Database migration (🔴 skill: writing-plans)

```
Tôi cần thay đổi schema: <mô tả>. 
ĐỪNG code ngay. Hãy trình bày kế hoạch: bảng/cột (lb_*), kiểu dữ liệu, index, ràng buộc, 
chỉ thêm mới/nullable hay có đổi/xóa, phương án rollback, và cách chạy qua pipeline 
(docker/entrypoint.sh / migrate --force), tuyệt đối không migrate:fresh trên production.
```

### 3.8 Payment / Subscription / Credit (🔴 plan trước)

```
Làm việc với cổng thanh toán <PaymentXxx>: <mô tả>. 
Trình bày kế hoạch trước: contract/luồng tiền (checkout→webhook→subscription/credit), 
ca lỗi/hoàn tiền/hết hạn, webhook URL & env cần cấu hình, đảm bảo không log dữ liệu nhạy cảm. 
Test sandbox trước.
```

### 3.9 Rà soát / Audit trước production (subagent: code-reviewer)

```
Audit module @<module> theo ARCHITECTURE_FEATURE.md. 
Kiểm tra: IDOR (scope tenant), thiếu rate-limit/captcha ở endpoint public, XSS khi hiển thị input khách, 
race condition, và xử lý lỗi. Liệt kê rủi ro theo mức P0/P1/P2 + đề xuất fix, CHƯA sửa.
```

### 3.10 Cập nhật tài liệu / sau khi nâng cấp upstream

```
Tôi vừa cập nhật phiên bản tác giả và/hoặc cài module mới.
Dùng luồng ARCHITECTURE_PROMPT.md §2.6 (upstream upgrade): brainstorming → writing-plans → chờ Duyệt.
Quét surgical: modules/*, migration mới, routes public → cập nhật .cursorrules, Dockerfile, docker/entrypoint.sh, .env.example, ARCHITECTURE_*.md.
```

```
Tôi vừa thay đổi <mô tả>. Hãy cập nhật các file ARCHITECTURE_*.md / .cursorrules liên quan 
cho khớp thực tế (surgical, không viết lại toàn bộ) để Cursor sau này không phải quét lại dự án.
```

### 3.11 Chuẩn bị commit (skill: verification-before-completion)

```
Tóm tắt thay đổi của phiên này và soạn commit message đúng style repo. 
Liệt kê file đổi, migration/route/permission/env mới. 
Nhắc tôi nếu có biến .env cần cập nhật trên Coolify. Chưa push cho tới khi tôi đồng ý.
Chạy verification-before-completion: pint trên file đã sửa, nêu test/manual check cần làm.
```

### 3.12 Livewire 419 / "page expired" (rút gọn)

```
Lỗi Livewire 419 trên <URL>. Skill systematic-debugging.
@bootstrap/app.php @app/Livewire/Portal/Dashboard.php @resources/themes/app/mlhubbackend/resources/views/livewire/portal/dashboard.blade.php
Đã Ctrl+F5 chưa: <có/không>. Vừa deploy: <có/không>.
Sửa surgical; nhắc Coolify: APP_URL, SESSION_DOMAIN, TRUSTED_PROXIES. Không SSH vá tay server.
```

### 3.13 Duyệt kế hoạch rồi triển khai (một câu)

```
Duyệt. Triển khai kế hoạch vừa lập theo executing-plans — từng bước, surgical, cập nhật ARCHITECTURE_* nếu cần.
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

**Coolify (tab Environment Variables) — bắt buộc trước** `mlhub:install`**:**


| Biến                          | Giá trị                                                |
| ----------------------------- | ------------------------------------------------------ |
| `APP_INSTALLED`               | `true` (entrypoint chạy `migrate --force`)             |
| `MLHUB_FIRST_USER_EMAIL`      | Email super admin (vd `you@mlhub.vn`)                  |
| `MLHUB_FIRST_USER_PASSWORD`   | Mật khẩu mạnh (Coolify — không commit)                 |
| `MLHUB_FIRST_USER_NAME`       | Tên hiển thị (tuỳ chọn)                                |
| `MLHUB_CONTACT_EMAIL`         | Email liên hệ site (thường trùng admin)                |
| `MLHUB_ADMIN_PLAN_SLUG`       | `mlhub-partner-lifetime`                               |
| `MLHUB_STARTING_ID`           | `147123468` (AUTO_INCREMENT sau seed)                  |
| `MLHUB_LICENSE_PURCHASE_CODE` | Mã license Stackposts (Coolify)                        |
| `MLHUB_LICENSE_DOMAIN`        | `mlhub.vn`                                             |
| `MLHUB_ALLOW_RESET_DEMO`      | `false` (chặn `db:wipe` trên production)               |
| `RUN_QUEUE_WORKER`            | `true` (hoặc `false` nếu worker Coolify riêng)         |
| `MAIL_PASSWORD`               | SMTP API key (seed ghi `options.smtp_password` nếu có) |


**Module** `CustomMLHUB`**:** site options VN (`format_date` `d/m/Y`, VND, timezone), seeder admin, email/template packs hệ thống. Script dev Việt hóa AI templates: `modules/CustomMLHUB/Scripts/`.

#### A. Hai lệnh MLHUB (trong container app)


| Lệnh                                 | Khi dùng                                    | Hành vi                                                                                                                                                                                                                                |
| ------------------------------------ | ------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `php artisan mlhub:install`          | DB mới hoặc muốn **xóa sạch** cài lại       | `migrate:fresh` (xóa toàn bộ bảng) → seed từ ID `147123468` → `optimize`. Hỏi xác nhận; `--force` bỏ qua hỏi. Production: tạm `MLHUB_ALLOW_RESET_DEMO=true`.                                                                           |
| `php artisan mlhub:update`           | Đã có dữ liệu, muốn **cập nhật** sau deploy | `migrate` (migration mới) → seed đồng bộ catalog official `mlhub-*`, giữ nguyên plan legacy/custom và không xóa user/campaign → bản ghi seed **mới** nối ID sau max hiện có → `IdSequence::apply()` → `optimize`. |
| `php artisan mlhub:sync-env-options` | Sau khi đổi env Coolify                     | Ghi env có giá trị → **Admin → Cài đặt** (`options`). Tự chạy sau `migrate` mỗi deploy (`docker/entrypoint.sh`).                                                                                                                       |


**Admin Settings ↔ Coolify:** Mật khẩu SMTP, captcha, Stripe, Google OAuth, license… đặt trong **Environment Variables** (xem `.env.example` mục *Admin Settings ↔ Coolify*). Map chi tiết: `modules/CustomMLHUB/config/env_options.php`. Sau `mlhub:install` (xóa DB), cấu hình **khôi phục từ env** — không cần nhập lại tay nếu đã copy đủ biến lên Coolify.

```bash
docker exec -it <container_app> sh
cd /var/www/html
php artisan mlhub:install    # cài sạch — XÓA HẾT dữ liệu
php artisan mlhub:update     # cập nhật — GIỮ dữ liệu cũ
```

**Không** seed dữ liệu demo / QR volume / business mẫu.

#### B. Deploy thường (đã cài xong)

Push GitHub → Coolify redeploy → `docker/entrypoint.sh` chỉ **migrate** + optimize. Không tự chạy seed. Chạy thủ công `mlhub:update` khi cần đồng bộ seed/migration mới mà giữ dữ liệu khách.

#### C. DB trống từ SQL (khi cần)

```sql
DROP DATABASE default;
CREATE DATABASE default CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Sau đó: `php artisan mlhub:install` (hoặc import SQL đã chuẩn hóa ID rồi `mlhub:update` để đồng bộ seed).

#### D. Sau cài — kiểm tra

- Đăng nhập bằng `MLHUB_FIRST_USER_EMAIL` / mật khẩu đã đặt → **Admin** + **Portal**.
- Portal trống (chưa có business/campaign) — đúng production.
- Admin → Cài đặt: ngày `dd/mm/yyyy`, tiền `₫` (từ `mlhub_site_options.php`).

> **Không** import dump SQL cũ có `demo@mlhub.vn` / volume faker. Logo upload thủ công qua Admin hoặc storage volume.

### 4.2 Backup production an toàn

- **Code:** GitHub là source of truth; dùng tag/commit và Coolify rollback, không tải source từ thư mục đang chạy.
- **Database:** dùng backup/snapshot riêng của resource MySQL trong Coolify.
- **Uploads/log cần giữ:** dùng snapshot/backup private của Persistent Storage `mlhub-storage`.
- **Cấm:** tạo archive source, log hoặc storage trong `public/`; không cung cấp URL tải backup qua `mlhub.vn`.
- Restore phải thực hiện qua quy trình Coolify đã duyệt, có snapshot trước và kiểm tra trên staging.

---

## 5. Cheatsheet Docker / SSH Coolify (CHỈ để CHẨN ĐOÁN — đọc log/kiểm tra)

> 🔴 **Quy tắc cốt lõi (ARCHITECTURE_CHECKLIST.md §1):** KHÔNG dùng các lệnh này để **thay đổi** hạ tầng/code/config trên server. Mọi thay đổi đi qua local → GitHub → Coolify build. Các lệnh dưới đây chỉ để **xem trạng thái, đọc log, debug**.

Tham chiếu container (từ `docker-compose.yaml`): service `app`, chạy port 80 nội bộ, không publish host port, volume `mlhub-storage:/var/www/html/storage`, domain `mlhub.vn`.

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
5. Nếu cần chạy migration khi deploy: đảm bảo nằm trong `docker/entrypoint.sh`/pipeline.

### 5.1 Xem & tải `laravel.log` an toàn (module AdminLog)

> ✅ **Dùng trang admin, KHÔNG copy log ra** `public/`**.** Xem/tải log tại **Admin → Cài đặt → Logs** (`https://mlhub.vn/admin/settings/log`). Trang này nằm sau `auth` + `EnsureAdminAccess` (chỉ admin), tránh phơi log ra Internet.
>
> 🔴 **TUYỆT ĐỐI KHÔNG** copy `storage/logs/*.log` vào `public/` để tải qua URL công khai — log chứa stack trace, DB host, user ID, email, token; ai có URL (kể cả Google) đều đọc được.

Tại trang đó có thể: chọn file log, xem nhanh phần cuối (tail), **tải xuống**, **xoá nội dung** (clear) hoặc **xoá file**. Code: `modules/AdminLog`.

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
| Rate-limit          | `throttle:10,1` trên 5 form public (booking/coupon/feedback/lead/review)                                         |
| Storage             | disk `public` (S3 trống)                                                                                         |
| Theme active        | guest = `mlhubfrontend`, backend = `mlhubbackend` (env `THEME_FRONTEND` / `THEME_BACKEND`)                       |
| Session / URL prod  | `SESSION_DOMAIN=.mlhub.vn`, `APP_URL=https://mlhub.vn`, `TRUSTED_PROXIES=`*; không publish host port             |
| Log production      | `LOG_CHANNEL=stack`, `LOG_STACK=stderr,daily`, `LOG_LEVEL=warning`                                               |
| Deploy              | Coolify + Traefik (HTTP→HTTPS, Let's Encrypt)                                                                    |


---

## 7. Sổ tay cá nhân (tự ghi note mỗi lần code)

> Khu vực để bạn tự bổ sung. Gợi ý ghi theo định dạng ngày + việc + lệnh/đường dẫn liên quan.

### DD-MM-YYYY — <tiêu đề việc>

- Bối cảnh / mục tiêu:
- File/module liên quan: `@...`
- Lệnh đã dùng:
- Kết quả / lưu ý / cạm bẫy gặp phải:
- Việc cần làm tiếp:

## 8. Lựa chọn mô hình AI trong Cursor

### 1. Giữ nguyên **Opus 4.8 High (Core)**

- **Khi nào dùng:** Dành cho các tác vụ cốt lõi mà chúng ta vừa bàn tới (Cấu hình luồng Email/SMTP, Viết middleware Rate-limit/Captcha chống Spam, hoặc xử lý Race-condition cho Booking).
- **Lý do:** Dòng Opus luôn là "nhà vô địch" trong việc đọc hiểu ngữ cảnh dài. Nó sẽ nuốt trọn bộ `ARCHITECTURE_*.md` của bạn, nhớ rất kỹ các quy tắc an toàn (không dùng `migrate:fresh` trên production, luôn scope theo `auth()->id()`), và đưa ra kế hoạch (Plan) cực kỳ sắc bén trước khi code.

### 2. Dùng **Composer 2.5 Fast (Giải pháp)**

- **Khi nào dùng:** Khi bạn đã thảo luận xong giải pháp với Opus và chốt được phương án, hãy dùng tính năng Composer (phím tắt thường là `Cmd/Ctrl + I` hoặc `Cmd/Ctrl + K` trên toàn dự án) để AI tự động áp dụng các thay đổi đó vào nhiều file cùng lúc (ví dụ: chèn throttle vào hàng loạt file `Routes/web.php` của các module).
- **Lý do:** Tốc độ thực thi cực nhanh và gõ code trực tiếp vào file (surgical edits) rất tốt.

### 3. Dùng **Sonnet 4.6 Medium** hoặc **GPT-5.5 Medium (Theme)**

- **Khi nào dùng:** Tác vụ nhẹ: chỉnh CSS Tailwind trên theme `mlhubfrontend` / `mlhubbackend`, bổ sung `lang/vi.json`, regex validation.
- **Luôn kèm:** `.cursorrules` + `@file` blade cụ thể; không thay thế bước `writing-plans` cho tính năng lớn.
- **Lý do:** Tiết kiệm "Premium credits" của bạn, tốc độ phản hồi nhanh hơn Opus, và dư sức xử lý các file đơn lẻ.

---

## 3.9 Bộ Prompt mẫu cho Codex — MLHUB

> **Audit bảo mật + hiệu suất (copy nhanh):** `ARCHITECTURE_CODEX_PROMPT.md` — MASTER §2 + biến thể Security/Performance/Infra/Module.

Dùng khi làm việc với Codex CLI / Codex trong IDE để xử lý các việc khó hơn Cursor: audit luồng, debug root cause, refactor có biên, tối ưu hiệu suất, review bảo mật, kiểm tra subscription/plan/tenant.

> Nguyên tắc dùng Codex:
>
> - Không dùng prompt mơ hồ kiểu “đọc toàn bộ project rồi tối ưu”.
> - Luôn giới hạn phạm vi bằng file/module cụ thể.
> - Với task rủi ro: yêu cầu Codex phân tích → lập plan → chờ duyệt rồi mới sửa.
> - Không để Codex tự commit, push, deploy, SSH server hoặc chạy lệnh tay trên production.
> - Sau khi sửa phải xuất: file đã đổi, lý do đổi, verification đã chạy, rủi ro còn lại, commit message gợi ý.

### 3.9.1 MASTER PROMPT — Khởi động Codex trong repo MLHUB

```text
Bạn là Staff Engineer phụ trách MLHUB AI.

Bối cảnh kỹ thuật:
- Laravel 13 + Livewire 4.
- Modular monolith: logic chính nằm trong modules/*.
- Không có Repository layer; dùng Eloquent trực tiếp.
- Frontend dùng Blade + Livewire + Alpine + Tailwind CSS v4.
- Không thêm React/Vue/SPA framework.
- Production đang live tại mlhub.vn, deploy qua GitHub → Coolify.
- AI chỉ sửa code/config local, không git commit, không git push, không redeploy.

Trước khi làm task:
1. Đọc nhanh:
   - .cursorrules
   - ARCHITECTURE_CHECKLIST.md
   - ARCHITECTURE_BACKEND.md
   - ARCHITECTURE_FRONTEND.md
   - ARCHITECTURE_FEATURE.md
   - ARCHITECTURE_PROMPT.md
2. Tóm tắt lại trong 10 dòng:
   - module liên quan
   - file có khả năng bị ảnh hưởng
   - mức rủi ro: thấp / vừa / cao
   - có cần plan trước không
3. Không sửa code cho đến khi tôi đưa task cụ thể.
```

### 3.9.2 Codex — Audit nhanh trước khi sửa một luồng

```text
MLHUB | Codex Audit | Chưa code.

Mục tiêu audit:
<ghi rõ luồng: ví dụ đăng ký user mới → gán Free/no-plan access → dashboard/feature gate>

Phạm vi được đọc:
- @<file/module 1>
- @<file/module 2>
- @<file/module 3>

Không quét toàn repo nếu chưa cần.
Không sửa file.
Không chạy migration.
Không đổi schema.

Hãy trả về:
1. Sơ đồ luồng hiện tại.
2. Các class/file đang tham gia.
3. Điểm có thể gây lỗi production.
4. Điểm cần test thủ công.
5. Đề xuất patch tối thiểu nếu có.
6. Danh sách file cần sửa, nhưng chưa sửa.
```

### 3.9.3 Codex — Sửa bug production theo root cause

```text
MLHUB production bug. Dùng systematic debugging. Không đoán mò, không patch trước khi xác định root cause.

Triệu chứng:
<mô tả lỗi người dùng thấy>

Log/evidence:
<paste log từ Coolify / laravel.log / browser console>

Repro:
- URL:
- User role:
- Browser:
- Xảy ra sau deploy không:
- Có Ctrl+F5 chưa:

Phạm vi nghi liên quan:
- @<file/module>
- @<file/module>

Yêu cầu:
1. Xác định nguyên nhân gốc.
2. Chỉ rõ bằng chứng trong code/log.
3. Đề xuất sửa tối thiểu.
4. Nếu cần sửa, chỉ sửa trong phạm vi liên quan.
5. Không refactor ngoài task.
6. Không git commit/push/deploy.
7. Sau khi sửa, chạy verification phù hợp:
   - vendor/bin/pint --dirty
   - php artisan test hoặc test liên quan
   - php artisan view:clear/config:clear nếu chỉ phù hợp local
8. Xuất báo cáo cuối:
   - Root cause
   - Files changed
   - Verification
   - Risk còn lại
   - Commit message gợi ý
```

### 3.9.4 Codex — Debug deploy Coolify / container restart

```text
MLHUB | Codex Debug Deploy | Không SSH server | Không chạy lệnh production.

Triệu chứng deploy:
<container exited / build failed / 419 / 504 / Redis DNS / migration failed>

Log Coolify:
<paste đoạn cuối trước khi container thoát, nhất là dòng ERROR hoặc === entrypoint:>

Phạm vi được kiểm tra:
- @Dockerfile
- @docker-compose.yaml
- @docker/entrypoint.sh
- @.env.example
- @bootstrap/app.php
- @config/*.php nếu cần

Quy tắc:
- Mọi thay đổi hạ tầng phải nằm trong code/config local.
- Không đề xuất sửa trực tiếp file trên container.
- Không đề xuất migrate:fresh/db:wipe/rollback trên production.
- Nếu cần env production, hãy liệt kê tab Coolify → Environment Variables cần chỉnh.
- Nếu cần lệnh artisan khi deploy, phải đưa vào entrypoint/build flow.

Hãy trả về:
1. Nguyên nhân khả dĩ theo mức P0/P1/P2.
2. Bằng chứng từ log/code.
3. Patch tối thiểu.
4. Env cần kiểm tra trên Coolify.
5. Checklist sau redeploy.
6. Commit message gợi ý.
```

### 3.9.5 Codex — Thêm/tùy biến gói Free mặc định hoặc no-plan access

```text
MLHUB | Feature Plan/Free Tier | Plan trước, chưa code.

Mục tiêu:
<ví dụ: user mới đăng ký được trải nghiệm Free Đà Nẵng / hoặc dùng no-plan access theo env>

Phạm vi:
- @config/mlhub.php
- @.env.example
- @Modules/AdminPlans
- @Modules/AdminUser
- @app/Http/Middleware/ResolveUserPlanState.php
- @app/Support/Plans
- @modules/CustomMLHUB nếu có seed/update

Ràng buộc:
- Không phá luồng user hiện có.
- Không xóa dữ liệu plan/subscription cũ.
- Không đổi schema nếu có thể dùng env/seed/config hiện có.
- Nếu cần migration, phải plan trước và chỉ thêm cột nullable/index an toàn.
- Feature gate phải đi qua canUsePlanFeature()/planLimit()/PlanLimitGuard.
- User không có quyền không được thấy sidebar addon và vào URL trực tiếp phải 403.

Hãy làm:
1. Đọc luồng plan hiện tại.
2. Phân biệt phương án:
   A. no-plan access bằng env
   B. tạo AdminPlan Free thật
3. So sánh ưu/nhược cho production.
4. Đề xuất phương án tối thiểu cho MLHUB.
5. Viết plan từng bước.
6. Chờ tôi duyệt mới code.
```

### 3.9.6 Codex — Tạo 12 gói dịch vụ / pricing package

```text
MLHUB | Pricing Packages | Plan trước, chưa code.

Mục tiêu:
Tạo/cập nhật bộ gói dịch vụ:
- Free Đà Nẵng
- Starter
- Growth
- Pro
- Partner
- Các biến thể/thang giá còn lại theo cấu hình tôi cung cấp

Phạm vi được đọc:
- @Modules/AdminPlans
- @modules/CustomMLHUB
- @config/mlhub.php
- @.env.example
- @ARCHITECTURE_FEATURE.md
- @lang/en.json
- @lang/vi.json
- @resources/themes/guest/mlhubfrontend nếu cần pricing UI

Ràng buộc:
- Ưu tiên seed/update idempotent qua CustomMLHUB hoặc seeder hiện có.
- Không làm mất plan hiện có của user.
- Không hard-code tiếng Việt trong Blade nếu đang dùng __().
- Đồng bộ en.json và vi.json nếu thêm/chỉnh label.
- Không sửa payment gateway nếu chỉ là catalog gói.
- Nếu chạm subscription/payment thì dừng lại lập plan riêng.

Output:
1. Mapping từng gói → permissions → limits.
2. File cần sửa.
3. Rủi ro migration/payment/i18n.
4. Plan triển khai.
5. Chờ tôi duyệt mới code.
```

### 3.9.7 Codex — Review bảo mật tenant / IDOR

```text
MLHUB | Security Audit | Tenant scope / IDOR | Chưa code.

Phạm vi audit:
- @<module App*/Admin*/Payment* cụ thể>
- @<Livewire component>
- @<Controller/API route>
- @<Model liên quan>

Mục tiêu:
Tìm rủi ro user A xem/sửa/xóa dữ liệu user B.

Checklist:
1. Mọi query portal có scope user_id / workspaceOwnerUserId / team access chưa?
2. findOrFail($id) có bị thiếu owner scope không?
3. Livewire action edit/delete/togglePublished có validate quyền không?
4. Public route có leak dữ liệu campaign/business không?
5. Admin route có middleware EnsureAdminAccess/permission đúng không?
6. Bulk delete/update có where theo owner không?

Output:
- Bảng rủi ro: file / method / risk / severity / proposed fix.
- Patch tối thiểu cho lỗi P0/P1 nếu được phép.
- Test case cần thêm.
- Không refactor ngoài security fix.
```

### 3.9.8 Codex — Tối ưu performance dashboard / query chậm

```text
MLHUB | Performance Debug | Dashboard/API/query chậm.

Triệu chứng:
<trang chậm, API chậm, timeout, số lượng dữ liệu, log nếu có>

Phạm vi:
- @app/Livewire/Portal/Dashboard.php
- @app/Support/Portal/PortalGrowthDashboardMetrics.php
- @Modules/AppQRCampaigns
- @Modules/AppCustomers
- @<file nghi vấn>

Yêu cầu:
1. Xác định query/hàm có khả năng N+1 hoặc scan bảng lớn.
2. Kiểm tra cache hiện có, không cache Eloquent object; chỉ cache scalar/array an toàn.
3. Không đổi hành vi nghiệp vụ.
4. Nếu cần index DB, lập kế hoạch migration an toàn trước.
5. Ưu tiên patch nhỏ:
   - eager loading đúng chỗ
   - aggregate query
   - cache key có user + locale nếu dính UI
   - invalidate cache đúng event

Sau khi sửa:
- Chạy pint/test phù hợp.
- Nêu cách đo lại: log/query count/page load.
- Commit message gợi ý.
```

### 3.9.9 Codex — Tối ưu / fix 5 Growth Tool

```text
MLHUB | Growth Tools Debug | Review/Booking/Coupon/Feedback/Lead.

Tool đang xử lý:
<review | booking | coupon | feedback | lead>

Triệu chứng/mục tiêu:
<mô tả>

Phạm vi:
- @Modules/AppQRCampaigns
- @Modules/AppReviewBooster hoặc @Modules/AppBookingPages hoặc @Modules/AppCouponCampaigns hoặc @Modules/AppFeedbackForms hoặc @Modules/AppLeadForms
- @Modules/AppCustomers
- @app/Support/Plans/PlanLimitGuard.php
- @app/Support/GrowthToolNotifier.php nếu có

Checklist bắt buộc:
1. Campaign phải scope đúng user/workspace.
2. Public submit phải có validation + rate-limit/captcha nếu phù hợp.
3. CustomerUpserter không tạo trùng sai.
4. GrowthToolNotifier không làm chậm response; cân nhắc queue nếu cần.
5. PlanLimitGuard chặn đúng quota.
6. Không làm vỡ các tool khác vì 5 tool dùng chung engine lb_campaigns.

Output:
- Luồng hiện tại.
- Root cause/rủi ro.
- Patch tối thiểu.
- Test thủ công cho tool đó.
- Test hồi quy cho tool còn lại.
```

### 3.9.10 Codex — Fix thanh toán / subscription / credit

```text
MLHUB | Payment/Subscription/Credit | Rủi ro cao | Plan trước, chưa code.

Mục tiêu:
<mô tả: webhook không cập nhật plan, credit bị trừ sai, subscription hết hạn vẫn active...>

Phạm vi:
- @Modules/AppPayments
- @Modules/AppBilling
- @Modules/AdminPaymentSubscriptions
- @Modules/AdminPlans
- @PaymentStripe hoặc @PaymentPaypal hoặc @Payment2Checkout nếu liên quan
- @app/Support/Plans
- @Modules/AppCredits nếu liên quan credit

Ràng buộc:
- Không log card/secret/token nhạy cảm.
- Không thay đổi dữ liệu subscription hàng loạt nếu chưa có where rõ.
- Không chạy migrate:fresh/rollback/db:wipe.
- Webhook phải idempotent nếu có thể.
- Credit chỉ consume sau khi tác vụ thành công.
- Sandbox trước production.

Hãy trả về:
1. Luồng tiền/credit hiện tại.
2. Root cause hoặc điểm nghi vấn.
3. Plan sửa an toàn.
4. Migration/env/webhook URL nếu cần.
5. Test sandbox cần chạy.
6. Chờ tôi duyệt mới code.
```

### 3.9.11 Codex — Review frontend Livewire/Blade/i18n

```text
MLHUB | Frontend Review | Blade + Livewire + Alpine | Không thêm React/Vue.

Mục tiêu:
<Việt hóa UI / sửa hydration / sửa layout / đổi branding>

Phạm vi:
- @resources/themes/guest/mlhubfrontend
- @resources/themes/app/mlhubbackend
- @modules/<Module>/Resources/views
- @app/Livewire hoặc @modules/<Module>/Livewire
- @lang/en.json
- @lang/vi.json

Ràng buộc:
- Dùng component sẵn: x-ui.*, x-layout.*, x-theme.*, x-ai.*, x-shared.*.
- Không tạo component/view mới nếu chỉ đổi copy/layout nhỏ.
- Chuỗi hiển thị phải đi qua __()/@lang.
- Nếu sửa text, đồng bộ en.json và vi.json.
- Livewire state là nguồn sự thật; Alpine chỉ giữ state UI tạm.
- Không thêm SPA framework.

Output:
1. File cần sửa.
2. Patch tối thiểu.
3. i18n keys thêm/sửa/xóa.
4. Kiểm tra view: php artisan view:clear nếu cần.
5. Rủi ro Livewire 419/hydration nếu có.
```

### 3.9.12 Codex — Code review sau khi Cursor đã sửa

```text
MLHUB | Codex Code Review | Không code trừ khi tôi yêu cầu.

Diff/branch cần review:
<paste git diff hoặc nêu file đã đổi>

Mục tiêu review:
- Bắt lỗi logic.
- Bắt lỗi tenant scope/IDOR.
- Bắt lỗi plan/credit gate.
- Bắt lỗi migration/env/deploy.
- Bắt lỗi i18n/Livewire.
- Bắt lỗi performance.

Context:
- Production đang live.
- Không tự commit/push/deploy.
- Ưu tiên giữ cấu trúc gốc, không tạo file mới nếu không cần.
- MLHUB/mlhub đúng casing; không dùng Mlhub/MLHub.

Output bắt buộc:
1. Verdict: Approve / Approve with comments / Request changes.
2. P0/P1/P2 issues.
3. File + dòng/method liên quan.
4. Cách sửa tối thiểu.
5. Test/verification còn thiếu.
6. Commit message gợi ý nếu đạt.
```

### 3.9.13 Codex — Sinh test cho logic rủi ro

```text
MLHUB | Test Generation | Pest/PHPUnit | Logic rủi ro.

Logic cần test:
<mô tả PlanLimitGuard / CustomerUpserter / BookingAvailability / credit consume / tenant scope>

Phạm vi:
- @<class đang test>
- @<model liên quan>
- @tests nếu có pattern test hiện tại>

Yêu cầu:
1. Đọc style test hiện có trước.
2. Viết test nhỏ, có fixture tối thiểu.
3. Ưu tiên test business rule, không snapshot UI.
4. Test phải fail nếu logic sai.
5. Không thay đổi production code nếu chỉ được yêu cầu viết test.
6. Nếu cần factory/seed, dùng pattern hiện có.

Output:
- Test file thêm/sửa.
- Case đã cover.
- Lệnh chạy test cụ thể.
- Nếu test đang fail do bug thật, chỉ ra bug và chờ tôi duyệt sửa code.
```

### 3.9.14 Codex — Kết thúc task / bàn giao cho chủ dự án

```text
MLHUB | Task Completion Report.

Hãy tổng kết task theo format:

1. Mục tiêu đã xử lý
2. Files changed
3. Logic thay đổi
4. Migration/env/deploy impact
5. Verification đã chạy
6. Verification chưa chạy được và lý do
7. Rủi ro còn lại
8. Checklist cho tôi sau khi commit/push/Coolify redeploy
9. Commit message gợi ý

Không nói đã deploy nếu chưa deploy.
Không nói đã test nếu chưa chạy test.
Không che giấu phần chưa chắc.
```

### 3.9.15 Prompt ngắn dùng hằng ngày với Codex

```text
MLHUB | Codex surgical task.

Task:
<mô tả 1 mục tiêu duy nhất>

Scope:
- @<file 1>
- @<file 2>
- @<module nếu cần>

Rules:
- Read .cursorrules + ARCHITECTURE_CHECKLIST.md first.
- Do not scan whole repo unless blocked.
- Do not create new files unless justified.
- Do not change schema/payment/deploy config without plan first.
- Do not commit/push/deploy.
- Keep patch minimal.
- After changes: pint/test relevant, then summary + commit message.
```
