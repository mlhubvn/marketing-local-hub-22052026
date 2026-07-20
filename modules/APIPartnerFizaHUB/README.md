# APIPartnerFizaHUB

Partner API module that bridges **FizaHUB** (upstream identity/onboarding/app) with **MLHUB** (growth CRM portal at `mlhub.vn`).

API machine codes remain English. Public docs (`/api-fizahub`, `/api-fizahub/help-test`) include Vietnamese labels for statuses and errors.

## Goal

Expose a fixed, authenticated surface so FizaHUB can:

1. Provision a mapped MLHUB workspace/business (always Free package: `free` / alias `base` → plan `mlhub-free-da-nang`)
2. Read package summary and Limited dashboard metrics
3. Bridge support tickets/conversation (polling, not realtime)
4. Issue a single-use one-time login into the MLHUB portal (never admin impersonation)

Prefix: `/api/v1/partners/fizahub`

## Onboarding flow (quan trọng)

`POST /onboarding-requests` **luôn** tạo ngay `User + Team + Business + Integration` với gói Free và đặt trạng thái mặc định `awaiting_consultant`.

**FizaHUB gửi yêu cầu → MLHUB tạo ngay tài khoản Free → Chờ tư vấn viên liên hệ → Admin/tư vấn viên xử lý → MLHUB sync trạng thái về FizaHUB qua webhook.**

- `requested_package_code` được lưu riêng (ví dụ `base`); `package_code` hiệu lực = `free`.
- Trùng email → tạo tài khoản với email tạm (provisional) + trạng thái `needs_review`.
- Trùng mã số thuế/GPKD → vẫn tạo tài khoản + `needs_review`.
- Danh tính chưa xác minh **vẫn** tạo tài khoản (`awaiting_consultant`) — không còn cổng `pending_verification`.
- Ticket onboarding nội bộ được tạo **một lần** sau khi provision.
- One-time login chỉ mở khi onboarding mới nhất ở trạng thái `ready` hoặc `completed`; nếu chưa → `409 onboarding_not_ready` với thông điệp "Tài khoản đang chờ tư vấn viên MLHUB hoàn tất cấu hình.".

MLHUB gửi **3 webhook** về FizaHUB (khi cấu hình `FIZAHUB_WEBHOOK_BASE_URL`): `onboarding-status`, `campaign-metrics`, `support-events` — có chữ ký `X-MLHUB-Signature: sha256=...` và `X-Dedupe-Key`.

## Auth headers

Every partner API request must send:

| Header | Value |
|--------|--------|
| `Authorization` | `Bearer {partner_token}` (`FIZAHUB_PARTNER_TOKEN`) |
| `X-Partner` | `fizahub` |
| `X-Request-Id` | UUID v4 (echoed on every response) |
| `Accept` | `application/json` |

`Idempotency-Key` is **required** for:

- `POST /onboarding-requests`
- `POST /businesses/{external_business_id}/support-tickets`
- `POST /support-tickets/{ticket_id}/messages`

The Postman collection also sends `Idempotency-Key` on a few other write endpoints (confirm/cancel/one-time-login/reopen/attachment) out of habit — it is accepted there but not required; only the 3 endpoints above reject the request when it's missing.

## Response format

Success:

```json
{
  "success": true,
  "data": {},
  "meta": { "request_id": "..." },
  "error": null
}
```

Error:

```json
{
  "success": false,
  "data": null,
  "meta": { "request_id": "..." },
  "error": {
    "code": "validation_failed",
    "message": "The given data was invalid.",
    "details": {}
  }
}
```

Support message create/detail conversation items use field **`body`** (not `message`) in the response payload. The create-message request body still sends `{ "message": "..." }`.

## 24 Core API endpoints

Shipped in one unified Postman collection, split into two folders: **MVP v1** (10 endpoints — the happy-path partners must implement first) and **Extended Beta** (14 endpoints — optional, ship later). See [Postman](#postman) below.

| # | Set | Method | Path |
|---|-----|--------|------|
| 1 | MVP | GET | `/health` |
| 2 | Extended | POST | `/partner/sso/verify` |
| 3 | Extended | GET | `/packages` |
| 4 | MVP | POST | `/onboarding-requests` |
| 5 | MVP | GET | `/onboarding-requests/{request_id}` |
| 6 | Extended | POST | `/onboarding-requests/{request_id}/confirm` |
| 7 | Extended | POST | `/onboarding-requests/{request_id}/cancel` |
| 8 | MVP | GET | `/businesses/{external_business_id}/integration-status` |
| 9 | MVP | PATCH | `/businesses/{external_business_id}/profile` |
| 10 | Extended | POST | `/businesses/{external_business_id}/one-time-login` |
| 11 | MVP | GET | `/businesses/{external_business_id}/package` |
| 12 | MVP | GET | `/businesses/{external_business_id}/dashboard` |
| 13 | Extended | GET | `/businesses/{external_business_id}/insights` |
| 14 | Extended | GET | `/businesses/{external_business_id}/recommendations` |
| 15 | Extended | GET | `/businesses/{external_business_id}/campaigns` |
| 16 | Extended | GET | `/businesses/{external_business_id}/campaigns/{campaign_id}` |
| 17 | Extended | GET | `/businesses/{external_business_id}/support-summary` |
| 18 | MVP | POST | `/businesses/{external_business_id}/support-tickets` |
| 19 | Extended | GET | `/businesses/{external_business_id}/support-tickets` |
| 20 | MVP | GET | `/support-tickets/{ticket_id}?external_business_id={id}` |
| 21 | Extended | POST | `/support-tickets/{ticket_id}/messages?external_business_id={id}` |
| 22 | MVP | POST | `/support-tickets/{ticket_id}/attachments?external_business_id={id}` |
| 23 | Extended | PATCH | `/support-tickets/{ticket_id}/close?external_business_id={id}` |
| 24 | Extended | POST | `/support-tickets/{ticket_id}/reopen?external_business_id={id}` |

Plus one **web** consume route (not counted in the 24 Core API):

- `GET /partners/fizahub/one-time-login/{token}` (Laravel temporary signed URL) → `partner.fizahub.login.consume`

### Support detail / message scope

Support detail/message routes **require** query:

`?external_business_id={{external_business_id}}`

Missing query → `422 validation_failed`. Poll every **15–30 seconds** (`next_poll_after_seconds` typically `15`). Not realtime.

## Status labels (Vietnamese documentation)

API codes stay English. Docs expose Vietnamese labels:

### Onboarding `status`

| Code | Tiếng Việt | Khi nào | Dev FizaHUB cần làm gì |
|------|------------|---------|------------------------|
| `awaiting_consultant` | Chờ tư vấn viên liên hệ | Vừa tạo tài khoản Free, chờ tư vấn viên | Chờ MLHUB liên hệ; không tạo lại |
| `needs_review` | Cần kiểm tra | Trùng email/MST/GPKD (tài khoản vẫn được tạo) | Không spam tạo lại; báo MLHUB xử lý ticket |
| `consulting` | Đang tư vấn | Tư vấn viên đang làm việc với chủ cửa hàng | Chờ cập nhật |
| `configuring` | Đang cấu hình | MLHUB đang dựng chiến dịch/marketing | Chờ cập nhật |
| `ready` | Sẵn sàng sử dụng | Cấu hình xong, có thể one-time login | Mở one-time login cho người dùng |
| `completed` | Hoàn tất | Đã bàn giao và hoàn tất | Sử dụng bình thường |
| `cancelled` | Đã hủy | Hủy bởi đối tác hoặc admin | Tạo yêu cầu mới nếu cần |

### Onboarding `current_step`

| Code | Tiếng Việt |
|------|------------|
| `consultant_contact` | Chờ tư vấn viên liên hệ |
| `needs_review` | Đang rà soát trùng dữ liệu |
| `ready` | Sẵn sàng sử dụng |
| `completed` | Hoàn tất |

### Support ticket `status`

| Code | Tiếng Việt |
|------|------------|
| `open` | Đang mở |
| `resolved` | Đã xử lý |
| `closed` | Đã đóng |

### Package `status`

| Code | Tiếng Việt |
|------|------------|
| `active` | Đang hoạt động |
| `inactive` | Tạm ngừng |
| `expired` | Hết hạn |
| `none` | Chưa có gói |

### Error `code`

| Code | Tiếng Việt | Cách xử lý |
|------|------------|------------|
| `invalid_partner_header` | Header đối tác không hợp lệ | Kiểm tra `X-Partner` và `X-Request-Id` |
| `invalid_partner_token` | Token đối tác không hợp lệ | Kiểm tra `partner_token` |
| `validation_failed` | Dữ liệu không hợp lệ | Xem `error.details` để sửa Body/Params |
| `onboarding_request_not_found` | Không tìm thấy yêu cầu onboarding này | `next_action=create_onboarding_request`: tạo yêu cầu onboarding mới |
| `integration_not_found` | Chưa có mapping MLHUB cho business này | `next_action=create_onboarding_request`: chạy onboarding trước hoặc kiểm tra `external_business_id` |
| `campaign_not_found` | Không tìm thấy chiến dịch này | `next_action=list_campaigns_first`: gọi GET Campaigns để lấy `campaign_id` thật |
| `ticket_not_found` | Không tìm thấy phiếu hỗ trợ này | `next_action=create_support_ticket`: tạo ticket mới hoặc kiểm tra `ticket_id` |
| `default_plan_not_found` | Hệ thống chưa sẵn sàng để tạo tài khoản (plan mặc định chưa được seed) | `next_action=retry_later`: báo MLHUB kiểm tra deploy/seed, không phải lỗi phía FizaHUB |
| `partner_schema_not_ready` | Hệ thống chưa sẵn sàng (migration chưa chạy đủ) | `next_action=retry_later`: báo MLHUB kiểm tra deploy, không phải lỗi phía FizaHUB |
| `resource_not_found` | Không tìm thấy dữ liệu (loại chưa được phân loại riêng) | Kiểm tra `request_id` / `ticket_id` / `external_business_id` |
| `idempotency_conflict` | Idempotency-Key bị dùng lại với body khác | Tạo Idempotency-Key mới |
| `idempotency_in_progress` | Request cùng Idempotency-Key đang xử lý | Đợi rồi thử lại |
| `ticket_not_open` | Ticket đã đóng hoặc đã xử lý | Không gửi message mới |
| `onboarding_not_ready` | Tài khoản đang chờ tư vấn viên MLHUB hoàn tất cấu hình | Chờ trạng thái `ready`/`completed` rồi gọi lại one-time login |
| `rate_limit_exceeded` | Gọi API quá nhiều | Đợi khoảng 1 phút |
| `partner_api_error` | Lỗi hệ thống không xác định | Báo MLHUB kèm `request_id`; log server đã có exception class/message để tra cứu |

Package codes `free` và `base` đều map tới plan slug `mlhub-free-da-nang` (mặc định `free`). Industry alias `restaurant_food` → `restaurant_eatery`.

Identity documents: **no CCCD upload** — prohibited keys are rejected.

Dashboard `from`/`to` are report dates only — **not** package duration. Monthly 1/3/6/12 package sell/renew is **not** in this MVP API.

## Package API

Safe summary only:

- `package_code`, `package_name`, `plan_slug`, `status`, `starts_at`, `expires_at`, `is_trial`, `integration_status`
- `limits` whitelist: `max_businesses`, `max_campaigns`, `max_landing_pages`, `max_qr_codes`, `max_team_members`

Does **not** return price, payment/subscription, credit balance, or full plan permissions JSON.

## One-time login

- Only for mapped `PartnerIntegration` with `mlhub_business_id`
- Token: 256-bit random; DB stores **SHA-256 hash only**
- TTL: `FIZAHUB_ONE_TIME_LOGIN_TTL_MINUTES` (default **5**)
- Single-use (`used_at`)
- Consume via signed web route
- Raw login URL is redacted in logs (`url` → `[REDACTED]`)

## Dashboard metrics

Timezone: `Asia/Ho_Chi_Minh`. Default range: last 30 inclusive days.

- **new_reviews definition (MVP):** internal ReviewFeedback with `rating >= 4` — **not** live Google Reviews.
- **returning_customers estimated:** phone/email identities appearing in ≥ 2 events in the period.

## Log redaction

Partner API logs redact nested keys matching:

`authorization`, `token`, `password`, `cccd`, `identity_card`, `identity_image`, `identity_document`, `business_license_file`, `business_license_image`, `url`

Oversized payloads are capped (~64 KiB) with a SHA-256 digest.

## Environment

```env
FIZAHUB_PARTNER_TOKEN=
FIZAHUB_RATE_LIMIT_PER_MINUTE=60
FIZAHUB_ONE_TIME_LOGIN_TTL_MINUTES=5
FIZAHUB_DEFAULT_PACKAGE=free
FIZAHUB_PROVISIONAL_EMAIL_DOMAIN=provisional.mlhub.vn
FIZAHUB_WEBHOOK_BASE_URL=
FIZAHUB_WEBHOOK_SECRET=
FIZAHUB_DASHBOARD_CACHE_TTL_MINUTES=45
```

Set `FIZAHUB_PARTNER_TOKEN` (and `FIZAHUB_WEBHOOK_BASE_URL` / `FIZAHUB_WEBHOOK_SECRET` for outgoing webhooks) in Coolify for production.

## Installation

Module auto-discovery loads `APIPartnerFizaHUBServiceProvider` (priority 30). Migrations:

- `modules/APIPartnerFizaHUB/Database/Migrations/2026_07_13_000000_create_fizahub_partner_api_tables.php`
- `modules/APIPartnerFizaHUB/Database/Migrations/2026_07_17_120000_extend_fizahub_partner_onboarding_tables.php`

Tables: `partner_integrations`, `partner_onboarding_requests`, `partner_api_logs`, `partner_one_time_logins`, `partner_onboarding_status_histories`, `partner_package_assignments`, `partner_support_presets`, `partner_support_ticket_contexts`, `partner_support_attachments`, `partner_webhook_outbox`.

## Readiness & diagnostics

`GET /health` is a **readiness probe**, not just a liveness ping. It runs 4 checks and returns `200 ok` only if all pass, otherwise `503` with `data.status=degraded` and a per-check reason in `data.checks`:

- `database` — the configured DB connection is reachable.
- `partner_schema` — all 10 FizaHUB tables exist, plus `requested_package_code`/`approved_package_code`/`admin_status` on `partner_onboarding_requests` (the exact columns the 2026_07_17 extension migration adds — missing them was the root cause of the production onboarding 500).
- `default_plan` — the `AdminPlan` (`plans` table) mapped from `FIZAHUB_DEFAULT_PACKAGE` (default `mlhub-free-da-nang`) exists and `status=true`.
- `support_tables` — the AdminSupport tables (`support_tickets`, `support_comments`) this module's support-ticket bridge writes to.

For a human-readable version with token/migration/route checks added, run on the server:

```bash
php artisan fizahub:doctor
```

It prints one `[PASS]`/`[FAIL]` line per check (`token`, `migrations`, `database`, `partner_schema`, `default_plan`, `support_tables`, `routes`) and exits `0` only if everything passes — safe to wire into a deploy health-check step.

## Postman

One unified collection — import once:

- [`docs/FizaHUB-Partner-API.postman_collection.json`](docs/FizaHUB-Partner-API.postman_collection.json) — 24 Core API endpoints in 2 folders:
  - **A. MVP** (10 endpoints, required happy path)
  - **B. Extended Beta** (14 endpoints, optional)

The collection ships with `partner_token=replace-with-token` (not a real secret) and empty `onboarding_request_id`/`ticket_id`/`campaign_id` variables — no fake IDs. A collection-level pre-request script recomputes `from`/`to` to the last 30 days on every send, and the `POST Onboarding` / `POST Create Support Ticket` requests have test scripts that auto-save `data.request_id` / `data.ticket_id` into collection variables for the next requests. Replace `partner_token` with the real token MLHUB issues before going live.

Public documentation:

- Partner tech spec: `GET /api-fizahub`
- Postman download (unified): `GET /api-fizahub/postman`
- Legacy Extended URL (same file): `GET /api-fizahub/postman/extended`
- Step-by-step Postman help: `GET /api-fizahub/help-test`

## MVP exclusions

- Customer CRUD API
- Chat AI API / AI Studio API
- Campaign / Coupon / Landing Page creation API
- **Google Business không expose trực tiếp** (scheduler unchanged)
- Revenue / cost / credit API
- CCCD / GPKD file upload (**no CCCD upload**)
