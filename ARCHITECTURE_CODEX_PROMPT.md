# MLHUB — Prompt Codex: Rà soát Bảo mật & Hiệu suất

> Dùng với **Codex CLI** hoặc Codex trong IDE. Copy prompt bên dưới, điền phạm vi `@file`, **không** quét toàn repo.
>
> Đọc kèm: `.cursorrules`, `ARCHITECTURE_CHECKLIST.md`, `ARCHITECTURE_BACKEND.md`, `ARCHITECTURE_MODULE.md` (route/bảng/model/plan từng module), `ARCHITECTURE_FEATURE.md`, `ARCHITECTURE_PROMPT.md` §10.
>
> **Quy tắc cố định:** Codex chỉ sửa local — không `git commit`, không `git push`, không SSH server, không `migrate:fresh`/`db:wipe` trên production.

---

## 1. Khi nào dùng prompt nào

| Tình huống | Prompt |
|------------|--------|
| Rà soát tổng thể trước release / sau merge lớn | **§2 MASTER — Audit tổng hợp** |
| Chỉ bảo mật (IDOR, public route, admin) | **§3 Security** |
| Chỉ hiệu suất (query, cache, Livewire) | **§4 Performance** |
| Chỉ hạ tầng (Redis, session, deploy) | **§5 Infra** |
| Một module cụ thể | **§6 Module scoped** |
| Sau khi Cursor đã sửa — review diff | **§7 Code review** |
| Đã có báo cáo audit — triển khai fix | **§8 Fix sau duyệt** |

---

## 2. MASTER PROMPT — Audit tổng hợp (Bảo mật + Hiệu suất + Tối ưu)

**Dùng khi:** chuẩn bị production, sau nâng cấp upstream, hoặc rà soát định kỳ. **Chưa code** cho đến khi bạn gõ **Duyệt**.

```text
Bạn là Staff Engineer / Security Reviewer cho MLHUB (Laravel 13 + Livewire 4, modular monolith, production live tại mlhub.vn).

MLHUB | Codex Full Audit | Bảo mật + Hiệu suất + Tối ưu | CHƯA CODE.

=== BỐI CẢNH BẮT BUỘC ===
Đọc và bám theo:
- .cursorrules (§0 production, §3 vibecode, tenant scope, Redis/Livewire)
- ARCHITECTURE_CHECKLIST.md (§2.D rủi ro, §3 plan trước)
- ARCHITECTURE_BACKEND.md
- ARCHITECTURE_MODULE.md (route/bảng/model/plan/public endpoint từng module)
- ARCHITECTURE_FEATURE.md (§14 backlog bảo mật, growth tools, loyalty)

Stack: PHP 8.3, Eloquent trực tiếp (không Repository), Blade+Livewire+Alpine, Tailwind v4.
Deploy: GitHub → Coolify → docker/entrypoint.sh. Không SSH vá tay server.

=== PHẠM VI AUDIT (điền trước khi chạy) ===
Lớp: <Hạ tầng | Backend | Frontend | Full-stack cụm>
Quy mô: <Lớn | Vừa | Nhỏ>
Files/modules được đọc (bắt buộc khoanh hẹp):
- @<file hoặc module 1>
- @<file hoặc module 2>
- @<file hoặc module 3>

Không quét toàn repo trừ khi bị block. Không sửa file. Không chạy migration.

=== PHASE 1 — BẢO MẬT (P0 trước) ===

A. Tenant / IDOR
- Mọi query portal có scope auth()->id() / workspaceOwnerUserId() chưa?
- findOrFail($id) có thiếu owner scope không?
- Livewire edit/delete/toggle có validate quyền sở hữu không?
- Bulk update/delete có where theo owner không?

B. Auth & Admin
- Super-admin chỉ từ is_super_admin=true hoặc role super-admin (không suy từ username)?
- Username đặc quyền (admin, administrator, root, system) bị chặn ở profile công khai?
- Admin route có auth + EnsureAdminAccess/permission đúng?

C. Public endpoints & Spam
- 5 growth tool POST: đã throttle:10,1? (booking, coupon, feedback, lead, review)
- Loyalty/Referral public POST: đã throttle? (backlog §14.7)
- Captcha: auth đã có; 5 form growth công khai chưa? (backlog §14.1)
- Validation đầy đủ trên mọi public submit?

D. XSS / Output / Secrets
- Input khách hiển thị trong admin/portal có escape đúng không?
- Log/webhook có lộ card, token, password, APP_KEY không?
- Secret có hard-code trong repo thay vì Coolify env không?
- Log có bị copy ra public/ không? (AdminLog là đúng)

E. Plan / Payment / Credit
- Feature gate qua canUsePlanFeature() / PlanLimitGuard?
- URL addon trực tiếp → 403 khi không có quyền?
- Credit chỉ consume sau khi tác vụ thành công?
- Webhook payment idempotent, không log nhạy cảm?

=== PHASE 2 — HIỆU SUẤT ===

A. Database & Query
- N+1 trong Livewire render / dashboard?
- count()/scan bảng lớn không index (lb_qr_scans, lb_campaigns, …)?
- findOrFail trần gây leak + query thừa?

B. Cache (Redis DB1)
- Cache::remember() chỉ lưu scalar/array (không serialize Eloquent)?
- Key cache có user_id / locale khi cần?
- forget() đúng suffix sau scan/conversion (PortalGrowthDashboardMetrics)?

C. Livewire / Portal
- Dashboard: một wire:init (loadDashboardSections), không 3 request song song?
- Sau deploy: entrypoint sync Livewire JS + optimize?
- Nguy cơ 419/504: SESSION_DOMAIN, APP_URL, TRUSTED_PROXIES?

D. Queue / Response time
- GrowthToolNotifier / recordScan có block response không? Cần queue?
- Đúng một queue worker (RUN_QUEUE_WORKER hoặc service riêng, không cả hai)?

=== PHASE 3 — HẠ TẦNG & CONFIG ===

Kiểm tra (nếu nằm trong phạm vi):
- Redis: queue/default DB0, cache+lock DB1, session DB2
- REDIS_URL không mã hóa DB path sai
- SESSION_DRIVER=redis, CACHE_STORE=redis
- APP_DEBUG=false, LOG_LEVEL=warning trên production
- docker/entrypoint.sh: migrate --force, optimize, không migrate:fresh
- .env.example khớp Coolify (không commit secret)

=== PHASE 4 — OUTPUT BẮT BUỘC (tiếng Việt, dễ hiểu cho chủ dự án) ===

1. **Tóm tắt 5 dòng** — phạm vi đã đọc, mức rủi ro tổng thể.
2. **Bảng phát hiện** — cột: ID | P0/P1/P2 | Lớp (Security/Perf/Infra) | File:method | Mô tả rủi ro | Fix tối thiểu đề xuất | Effort (S/M/L).
3. **P0 — phải sửa trước khi release** (nếu có).
4. **Backlog đã biết** — đối chiếu ARCHITECTURE_FEATURE.md §14; đánh dấu đã fix / chưa fix.
5. **Kế hoạch sửa** (writing-plans) — thứ tự ưu tiên, file cần đổi, migration/env nếu có.
6. **Verification checklist** — pint, test, manual check URL, Coolify env.
7. **Không sửa code.** Chờ tôi gõ "Duyệt" mới triển khai.
```

---

## 3. Security — Rà soát bảo mật (chuyên sâu)

```text
MLHUB | Codex Security Audit | CHƯA CODE.

Phạm vi:
- @<module Livewire/Controller/Route>
- @<Model>
- @<Routes/web.php nếu public>

Mục tiêu: user A không xem/sửa/xóa dữ liệu user B; public route không bị lạm dụng.

Checklist (bắt buộc từng mục — ghi PASS/FAIL/NA + bằng chứng file:dòng):
1. Tenant scope: auth()->id() / workspaceOwnerUserId() trên mọi query ghi/đọc portal
2. findOrFail thiếu owner scope
3. Livewire actions: save/delete/publish/export
4. Public route: validation, throttle, captcha (so với backlog §14)
5. XSS: {!! !!} với input khách; Blade echo user content
6. Mass assignment: $guarded trên model nhạy cảm
7. Admin: middleware, permission, super-admin rule
8. File upload: mime/size/path traversal
9. SQL: raw query có binding; không concat user input
10. Secrets trong code/log/response

Output:
- Bảng rủi ro P0/P1/P2 (file, method, mô tả, fix surgical)
- Test case Pest/manual cần thêm
- Không refactor ngoài security. Không commit/push.
```

---

## 4. Performance — Rà soát hiệu suất

```text
MLHUB | Codex Performance Audit | CHƯA CODE.

Triệu chứng (nếu có):
<URL chậm / timeout / số bản ghi / log query>

Phạm vi:
- @app/Livewire/Portal/Dashboard.php
- @app/Support/Portal/PortalGrowthDashboardMetrics.php
- @<module/query nghi vấn>

Yêu cầu:
1. Liệt kê query/hàm nặng — N+1, COUNT(*), thiếu index, load full collection
2. Cache hiện tại: key pattern, TTL, invalidate, có serialize model không?
3. Livewire: số request mỗi page load, wire:init trùng
4. Đề xuất patch nhỏ (eager load, aggregate, cache scalar, chunk) — không đổi nghiệp vụ
5. Index DB: nếu cần → plan migration nullable/add index, không migrate:fresh
6. Cách đo lại sau fix (query count, thời gian, log)

Output: bảng bottleneck + P0/P1/P2 + plan fix. Chưa code.
```

---

## 5. Infra — Redis / Session / Deploy

```text
MLHUB | Codex Infra Audit | Không SSH server | CHƯA CODE.

Phạm vi:
- @.env.example
- @docker-compose.yaml
- @docker/entrypoint.sh
- @bootstrap/app.php
- @config/database.php @config/cache.php @config/session.php
- @modules/AdminCache/Support/RedisConnectionResolver.php

Kiểm tra:
- Redis DB0/1/2 tách đúng (queue, cache+lock, session)
- REDIS_URL / REDIS_HOST / FLUSHDB an toàn
- TRUSTED_PROXIES, SESSION_DOMAIN=.mlhub.vn, APP_URL=https
- Single queue worker; QUEUE_WORKER_TIMEOUT < REDIS_QUEUE_RETRY_AFTER
- entrypoint: migrate --force, Livewire JS sync, optimize
- LOG_CHANNEL=stack, LOG_STACK=stderr,daily, LOG_LEVEL=warning

Output: P0/P1/P2 + env Coolify tab cần kiểm tra + checklist sau redeploy. Chưa code.
```

---

## 6. Module scoped — Audit một module

```text
MLHUB | Codex Module Audit | @modules/<TênModule>/ | CHƯA CODE.

Module: <AppReviewBooster | AppBookingPages | AppBilling | …>
Tra trước ARCHITECTURE_MODULE.md (§3/§12/§13/§14) cho route prefix, bảng, plan key, public endpoint của module.

Đọc toàn bộ trong module (không quét repo khác trừ import/shared):
- Routes/web.php (portal + public)
- Livewire/*
- Http/Controllers/*
- Models/*
- Support/* Services/*

Audit song song:
- Security: tenant, public throttle/captcha, XSS
- Performance: N+1, cache, heavy loops
- Vibecode: đúng phong cách .cursorrules §3

Output:
1. Sơ đồ luồng module
2. Bảng rủi ro P0/P1/P2
3. So sánh ARCHITECTURE_FEATURE.md mục tương ứng
4. Plan fix tối thiểu — chờ "Duyệt"
```

---

## 7. Code review — Sau khi Cursor/Codex đã sửa

```text
MLHUB | Codex Code Review | Không code trừ khi tôi yêu cầu.

Diff/files cần review:
<git diff hoặc liệt kê @file>

Review theo:
- Logic & regression
- Tenant scope / IDOR
- Plan/credit gate
- Migration/env/deploy impact
- i18n en.json + vi.json
- Performance (N+1, cache)
- MLHUB/mlhub casing (cấm Mlhub/MLHub)
- Surgical — không file thừa

Verdict: Approve | Approve with comments | Request changes
+ P0/P1/P2 + file:line + fix tối thiểu + test thiếu + commit message gợi ý
```

---

## 8. Fix sau duyệt — Triển khai từ báo cáo audit

```text
Duyệt. MLHUB | Codex Execute Audit Fixes.

Báo cáo audit: <dán P0/P1 cần sửa hoặc @file báo cáo trước>

Quy tắc:
- Chỉ sửa mục đã duyệt trong báo cáo
- Surgical, bám vibecode file hiện có
- Không refactor ngoài phạm vi
- Migration chỉ nếu đã có trong plan đã duyệt
- Không commit/push/deploy

Sau khi sửa:
1. vendor/bin/pint --dirty
2. php artisan test (hoặc --filter=<nhóm>)
3. Task Completion Report (ARCHITECTURE_PROMPT.md §2.9)
4. Commit message gợi ý
```

---

## 9. Prompt ngắn hằng ngày (copy nhanh)

```text
MLHUB | Codex audit surgical | <Security|Performance|Both> | CHƯA CODE.

Scope: @<file1> @<file2>
Đọc .cursorrules + ARCHITECTURE_CHECKLIST.md §2.D + ARCHITECTURE_FEATURE.md §14.
Trả bảng P0/P1/P2 + fix tối thiểu. Không quét cả repo. Không commit.
```

---

## 10. Thang độ ưu tiên (chuẩn MLHUB)

| Mức | Ý nghĩa | Ví dụ |
|-----|---------|-------|
| **P0** | Lỗ hổng / mất dữ liệu / chặn release | IDOR, secret lộ, admin bypass, payment sai tiền |
| **P1** | Rủi ro production / UX nghiêm trọng | Thiếu throttle public, N+1 timeout dashboard, 419 hàng loạt |
| **P2** | Cải thiện / backlog đã biết | Captcha growth form, loyalty throttle, tối ưu query nhỏ |

---

## 11. Sau khi Codex báo xong (chủ dự án)

1. Đọc bảng P0 — quyết định sửa ngay hay lên lịch.
2. Gõ **Duyệt** + prompt §8 nếu muốn Codex sửa.
3. Commit + push thủ công → Coolify redeploy.
4. **Ctrl+F5** portal/admin sau deploy.
5. Ghi note vào `ARCHITECTURE_PROMPT.md` §7 nếu gặp cạm bẫy mới.
