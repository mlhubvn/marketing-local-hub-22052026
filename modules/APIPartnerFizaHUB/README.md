# APIPartnerFizaHUB

Partner API module that bridges **FizaHUB** (upstream identity/onboarding/app) with **MLHUB** (growth CRM portal at `mlhub.vn`).

## Goal

Expose a fixed, authenticated MVP surface so FizaHUB can:

1. Provision a mapped MLHUB workspace/business (`base` package → plan `mlhub-free-da-nang`)
2. Read package summary and Limited dashboard metrics
3. Bridge support tickets/conversation (polling, not realtime)
4. Issue a single-use one-time login into the MLHUB portal (never admin impersonation)

Prefix: `/api/v1/partners/fizahub`

## Auth headers

Every partner API request must send:

| Header | Value |
|--------|--------|
| `Authorization` | `Bearer {partner_token}` (`FIZAHUB_PARTNER_TOKEN`) |
| `X-Partner` | `fizahub` |
| `X-Request-Id` | UUID v4 (echoed on every response) |
| `Accept` | `application/json` |

`Idempotency-Key` (UUID) is **required** for:

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

Common codes: `invalid_partner_header`, `invalid_partner_token`, `resource_not_found`, `integration_not_found`, `idempotency_conflict`, `idempotency_in_progress`, `validation_failed`, `rate_limit_exceeded`, `ticket_not_open`, `partner_api_error`.

## 10 MVP API endpoints

| # | Method | Path |
|---|--------|------|
| 1 | GET | `/health` |
| 2 | GET | `/businesses/{external_business_id}/package` |
| 3 | POST | `/onboarding-requests` |
| 4 | GET | `/onboarding-requests/{request_id}` |
| 5 | POST | `/businesses/{external_business_id}/one-time-login` |
| 6 | GET | `/businesses/{external_business_id}/dashboard` |
| 7 | POST | `/businesses/{external_business_id}/support-tickets` |
| 8 | GET | `/businesses/{external_business_id}/support-tickets` |
| 9 | GET | `/support-tickets/{ticket_id}?external_business_id={id}` |
| 10 | POST | `/support-tickets/{ticket_id}/messages?external_business_id={id}` |

Plus one **web** consume route (not counted in the 10 API MVP):

- `GET /partners/fizahub/one-time-login/{token}` (Laravel temporary signed URL) → `partner.fizahub.login.consume`

### Support detail / message scope

Support conversation MVP does **not** nest tickets under the business path for detail/message. Those routes currently **require** query:

`?external_business_id={{external_business_id}}`

without it the API returns `422 validation_failed`. Document this in Postman and integrators. Polling is not realtime — FizaHUB should poll every **15–30 seconds** (`next_poll_after_seconds` is typically `15`).

## Onboarding status

| `status` | `current_step` | Meaning |
|----------|----------------|---------|
| `pending_verification` | `verification` | Upstream identity not verified yet; support ticket opened |
| `needs_review` | `duplicate_review` | Email/tax/license collision; never auto-attach unmapped email |
| `completed` | `ready` | User + team + business + `PartnerIntegration` mapped |

Package code `base` maps to plan slug `mlhub-free-da-nang`. Industry alias `restaurant_food` → `restaurant_eatery`.

Identity documents: **no CCCD upload** and no GPKD/CCCD raw file fields — those keys are rejected.

## Package API

Safe summary only:

- `package_code`, `package_name`, `plan_slug`, `status`, `starts_at`, `expires_at`, `is_trial`, `integration_status`
- `limits` whitelist: `max_businesses`, `max_campaigns`, `max_landing_pages`, `max_qr_codes`, `max_team_members`

Does **not** return price, payment/subscription, credit balance, or full plan permissions JSON.

## One-time login

- Only for mapped/`PartnerIntegration` with `mlhub_business_id`
- Token: 256-bit random; DB stores **SHA-256 hash only**
- TTL: `FIZAHUB_ONE_TIME_LOGIN_TTL_MINUTES` (default **5**)
- Single-use (`used_at`); expired/used/invalid signature/unknown → no login
- Consume via signed web route; regenerates session; redirects to portal dashboard
- Does **not** grant admin / does not impersonate super-admin
- Raw login URL is redacted in audit + partner API logs (`url` → `[REDACTED]`)

## Dashboard metrics

Timezone: `Asia/Ho_Chi_Minh`. Default range: last 30 inclusive days. Reject `from > to` and ranges over 366 days.

Required metrics (always present, including zero data — no fake production numbers):

`businesses`, `campaigns`, `active_campaigns`, `qr_scans`, `new_leads`, `new_reviews`, `coupon_claims`, `coupon_used`, `bookings`, `feedback`, `returning_customers`, `conversion_rate`

Definitions:

- **new_reviews definition (MVP):** internal ReviewFeedback with `rating >= 4` — **not** live Google Reviews.
- **returning_customers estimated:** normalized phone/email identities appearing in ≥ 2 lead/booking/coupon/review/feedback events in the selected period.
- `coupon_used`: redemptions with non-null `used_at`
- `feedback`: feedback form responses + ReviewFeedback with `rating <= 3`
- `conversion_rate`: `(new_leads + new_reviews + coupon_claims + bookings + feedback) / qr_scans * 100` (2 decimals; `0` when scans are `0`)

Also returns daily `trend`, up to 10 `campaigns`, deterministic `insights`, and `suggested_actions` (rule codes — no AI text).

Scoped by `external_business_id` → `mlhub_business_id` (tenant isolation).

## Log redaction

Partner API logs and audit metadata redact nested keys matching: `authorization`, `token`, `password`, `cccd`, `identity_document`, `business_license_file`, and one-time login `url`. Oversized payloads are capped (~64 KiB) with a SHA-256 digest.

## Environment

See `.env.example`:

```env
FIZAHUB_PARTNER_TOKEN=
FIZAHUB_RATE_LIMIT_PER_MINUTE=60
FIZAHUB_ONE_TIME_LOGIN_TTL_MINUTES=5
```

Set `FIZAHUB_PARTNER_TOKEN` in Coolify for production. Module config is under `config('modules.apipartnerfizahub.*')`.

## Installation

Module auto-discovery loads `APIPartnerFizaHUBServiceProvider` (priority 30). Migration:

`modules/APIPartnerFizaHUB/Database/Migrations/2026_07_13_000000_create_fizahub_partner_api_tables.php`

Tables: `partner_integrations`, `partner_onboarding_requests`, `partner_api_logs`, `partner_one_time_logins`.

## Postman

Import: [`docs/FizaHUB-Partner-API.postman_collection.json`](docs/FizaHUB-Partner-API.postman_collection.json)

Public documentation page:

- `GET /api-fizahub`
- Postman download: `GET /api-fizahub/postman`

## MVP exclusions

Not in this MVP (do not call / do not expect):

- Customer CRUD API
- Chat AI API
- AI Studio API
- Campaign / Coupon / Landing Page creation API
- **Google Business không expose trực tiếp** (no Google Business direct API; scheduler unchanged)
- Revenue / cost / credit API
- CCCD / GPKD file upload (**no CCCD upload**)

Limited dashboard metrics only — not a full finance or Google Reviews product surface.
