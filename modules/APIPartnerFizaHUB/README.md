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

| # | Method | Path |
|---|--------|------|
| 1 | GET | `/health` |
| 2 | POST | `/partner/sso/verify` |
| 3 | GET | `/packages` |
| 4 | POST | `/onboarding-requests` |
| 5 | GET | `/onboarding-requests/{request_id}` |
| 6 | POST | `/onboarding-requests/{request_id}/confirm` |
| 7 | POST | `/onboarding-requests/{request_id}/cancel` |
| 8 | GET | `/businesses/{external_business_id}/integration-status` |
| 9 | PATCH | `/businesses/{external_business_id}/profile` |
| 10 | POST | `/businesses/{external_business_id}/one-time-login` |
| 11 | GET | `/businesses/{external_business_id}/package` |
| 12 | GET | `/businesses/{external_business_id}/dashboard` |
| 13 | GET | `/businesses/{external_business_id}/insights` |
| 14 | GET | `/businesses/{external_business_id}/recommendations` |
| 15 | GET | `/businesses/{external_business_id}/campaigns` |
| 16 | GET | `/businesses/{external_business_id}/campaigns/{campaign_id}` |
| 17 | GET | `/businesses/{external_business_id}/support-summary` |
| 18 | POST | `/businesses/{external_business_id}/support-tickets` |
| 19 | GET | `/businesses/{external_business_id}/support-tickets` |
| 20 | GET | `/support-tickets/{ticket_id}?external_business_id={id}` |
| 21 | POST | `/support-tickets/{ticket_id}/messages?external_business_id={id}` |
| 22 | POST | `/support-tickets/{ticket_id}/attachments?external_business_id={id}` |
| 23 | PATCH | `/support-tickets/{ticket_id}/close?external_business_id={id}` |
| 24 | POST | `/support-tickets/{ticket_id}/reopen?external_business_id={id}` |

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
| `integration_not_found` | Chưa có mapping MLHUB cho business này | Chạy onboarding trước hoặc kiểm tra `external_business_id` |
| `resource_not_found` | Không tìm thấy dữ liệu | Kiểm tra `request_id` / `ticket_id` / `external_business_id` |
| `idempotency_conflict` | Idempotency-Key bị dùng lại với body khác | Tạo Idempotency-Key mới |
| `idempotency_in_progress` | Request cùng Idempotency-Key đang xử lý | Đợi rồi thử lại |
| `ticket_not_open` | Ticket đã đóng hoặc đã xử lý | Không gửi message mới |
| `onboarding_not_ready` | Tài khoản đang chờ tư vấn viên MLHUB hoàn tất cấu hình | Chờ trạng thái `ready`/`completed` rồi gọi lại one-time login |
| `rate_limit_exceeded` | Gọi API quá nhiều | Đợi khoảng 1 phút |
| `partner_api_error` | Lỗi hệ thống API partner | Báo MLHUB kiểm tra log |

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
FIZAHUB_PROVISIONAL_EMAIL_DOMAIN=provisional.fizahub.mlhub.local
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

## Postman

Import: [`docs/FizaHUB-Partner-API.postman_collection.json`](docs/FizaHUB-Partner-API.postman_collection.json)

Collection variable `partner_token` uses placeholder `replace-with-fizahub-partner-token` (paste the real Coolify token locally; do not commit secrets).

Public documentation:

- Partner tech spec: `GET /api-fizahub`
- Postman download: `GET /api-fizahub/postman`
- Step-by-step Postman help: `GET /api-fizahub/help-test`

## MVP exclusions

- Customer CRUD API
- Chat AI API / AI Studio API
- Campaign / Coupon / Landing Page creation API
- **Google Business không expose trực tiếp** (scheduler unchanged)
- Revenue / cost / credit API
- CCCD / GPKD file upload (**no CCCD upload**)
