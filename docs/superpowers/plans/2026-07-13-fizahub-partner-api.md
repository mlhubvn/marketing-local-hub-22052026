# FizaHUB Partner API Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build an isolated `APIPartnerFizaHUB` adapter module that lets the FizaHUB app onboard a household business, inspect its MLHUB package and marketing dashboard, exchange support-ticket messages, and request a secure one-time portal login without exposing broader MLHUB capabilities.

**Architecture:** Auto-discover one module provider through the repository's existing `module.json` convention. Keep partner authentication, request lifecycle, idempotency, mapping, validation, response formatting, and orchestration inside the new module; call existing `User`, `Team`, `LocalBusiness`, plan, support, and local-growth models/services at explicit boundaries. Use a database-backed integration mapping and single-use hashed login tokens, while treating `teams` as the existing MLHUB workspace concept and `lb_businesses` as the existing business record.

**Tech Stack:** PHP 8.3.32, Laravel 13.2.0, Eloquent ORM, Laravel routing/middleware/rate limiting/signed URLs/session guard, Pest 4.4, PHPUnit 12, SQLite in-memory tests, JSON Postman Collection v2.1.

## Global Constraints

- Create partner endpoints only below `/api/v1/partners/fizahub`; do not expose generic MLHUB CRUD endpoints.
- Do not add customer-management, Chat AI, AI Studio, campaign creation, coupon creation, landing-page creation, Google Business, revenue/cost/credit, or identity-document upload endpoints.
- Do not modify Google Business scheduler behavior or static Privacy Policy / Terms of Use flows.
- Do not store raw CCCD, identity-card images, or business-license files. Persist only verification booleans/timestamps/provider plus normalized tax code or business-license number used for duplicate detection.
- Require `Authorization: Bearer <partner_token>`, `X-Partner: fizahub`, and a valid UUID `X-Request-Id` on every partner API request.
- Require `Idempotency-Key` on onboarding creation, support-ticket creation, and support-message creation; the same key and body must replay the original response, while the same key with a different body returns HTTP 409.
- Read the partner token only from `config('modules.apipartnerfizahub.token')`, populated by `FIZAHUB_PARTNER_TOKEN`; never call `env()` outside the module config file.
- Use constant-time `hash_equals` token comparison, a named per-partner rate limiter, structured JSON responses, and redacted request/response audit logs.
- Keep all integration code in `modules/APIPartnerFizaHUB` except `.env.example`, tests under `tests/Feature/APIPartnerFizaHUB`, and this plan.
- Reuse `Modules\AdminUser\Models\User`, `Modules\AdminUser\Models\Team`, `Modules\AdminUser\Support\PersonalTeamProvisioner`, `Modules\AdminPlans\Models\AdminPlan`, `Modules\AdminPlans\Support\DefaultSignupPlanResolver`, `Modules\AppBusinessProfiles\Models\LocalBusiness`, `Modules\AppBusinessProfiles\Support\BusinessTypeCatalog`, `Modules\AdminSupport\Models\SupportTicket`, and `Modules\AdminSupport\Models\SupportComment`.
- Treat `partner_integrations.mlhub_workspace_id` as `teams.id`; there is no separate workspace model or table in this repository.
- Treat `partner_integrations.mlhub_business_id` as `lb_businesses.id`.
- Use `support_tickets.id_secure` and `support_comments.id_secure` as public partner ticket/message identifiers; never expose numeric support IDs.
- All date filters are inclusive in `Asia/Ho_Chi_Minh`, default to the last 30 calendar days, reject `from > to`, and reject ranges longer than 366 days.
- Run focused RED and GREEN tests for every task, then run formatting and the complete test suite before completion.

## Fixed API Contract

All successful responses use:

```json
{
  "success": true,
  "data": {},
  "meta": {
    "request_id": "018f5a64-b40b-7f60-a925-dea047cf6590"
  },
  "error": null
}
```

All errors use:

```json
{
  "success": false,
  "data": null,
  "meta": {
    "request_id": "018f5a64-b40b-7f60-a925-dea047cf6590"
  },
  "error": {
    "code": "integration_not_found",
    "message": "No MLHUB integration is mapped to this FizaHUB business.",
    "details": {}
  }
}
```

Use these status/code pairs: 400 `invalid_partner_header`, 401 `invalid_partner_token`, 404 `integration_not_found` or `resource_not_found`, 409 `idempotency_conflict` or `idempotency_in_progress`, 422 `validation_failed`, 429 `rate_limit_exceeded`, and 500 `partner_api_error`. Always echo `X-Request-Id` on the response.

Onboarding states are `pending_verification`, `needs_review`, and `completed`. `current_step` is respectively `verification`, `duplicate_review`, or `ready`. A verified request with no collision provisions synchronously. A collision on existing unmapped email, normalized tax code, or normalized business-license number creates a support ticket and returns HTTP 202 with `needs_review`; it never silently attaches the FizaHUB business to an existing MLHUB account.

## MVP Route Matrix

| Method | URI | Route name | Success | Idempotency |
|---|---|---|---|---|
| GET | `/api/v1/partners/fizahub/health` | `partner.fizahub.health` | 200 | Not used |
| GET | `/api/v1/partners/fizahub/businesses/{external_business_id}/package` | `partner.fizahub.businesses.package` | 200 | Not used |
| POST | `/api/v1/partners/fizahub/onboarding-requests` | `partner.fizahub.onboarding.store` | 201/200/202 | Required |
| GET | `/api/v1/partners/fizahub/onboarding-requests/{request_id}` | `partner.fizahub.onboarding.show` | 200 | Not used |
| POST | `/api/v1/partners/fizahub/businesses/{external_business_id}/one-time-login` | `partner.fizahub.businesses.one-time-login` | 201 | Not used; each call creates a new single-use token |
| GET | `/api/v1/partners/fizahub/businesses/{external_business_id}/dashboard` | `partner.fizahub.businesses.dashboard` | 200 | Not used |
| POST | `/api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets` | `partner.fizahub.businesses.support-tickets.store` | 201 | Required |
| GET | `/api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets` | `partner.fizahub.businesses.support-tickets.index` | 200 | Not used |
| GET | `/api/v1/partners/fizahub/support-tickets/{ticket_id}` | `partner.fizahub.support-tickets.show` | 200 | Not used |
| POST | `/api/v1/partners/fizahub/support-tickets/{ticket_id}/messages` | `partner.fizahub.support-tickets.messages.store` | 201 | Required |

The dashboard response places scalar counts in `data.metrics` and uses `data.campaigns` for the campaign list, avoiding a collision between the required campaign count and campaign collection:

```json
{
  "data": {
    "period": { "from": "2026-06-14", "to": "2026-07-13", "timezone": "Asia/Ho_Chi_Minh" },
    "metrics": {
      "businesses": 1,
      "campaigns": 3,
      "active_campaigns": 2,
      "qr_scans": 120,
      "new_leads": 12,
      "new_reviews": 8,
      "coupon_claims": 15,
      "coupon_used": 6,
      "bookings": 4,
      "feedback": 3,
      "returning_customers": 5,
      "conversion_rate": 35.0
    },
    "campaigns": [],
    "trend": [],
    "insights": [],
    "suggested_actions": []
  }
}
```

## Expected File Structure

```text
modules/APIPartnerFizaHUB/
├── module.json
├── README.md
├── config/config.php
├── Providers/APIPartnerFizaHUBServiceProvider.php
├── Routes/api.php
├── Routes/web.php
├── Database/Migrations/2026_07_13_000000_create_fizahub_partner_api_tables.php
├── Models/PartnerIntegration.php
├── Models/PartnerOnboardingRequest.php
├── Models/PartnerApiLog.php
├── Models/PartnerOneTimeLogin.php
├── Http/Controllers/HealthController.php
├── Http/Controllers/OnboardingController.php
├── Http/Controllers/PackageController.php
├── Http/Controllers/DashboardController.php
├── Http/Controllers/SupportTicketController.php
├── Http/Controllers/SupportMessageController.php
├── Http/Controllers/OneTimeLoginController.php
├── Http/Controllers/ConsumeOneTimeLoginController.php
├── Http/Middleware/VerifyPartnerToken.php
├── Http/Middleware/HandlePartnerRequest.php
├── Http/Requests/UpsertOnboardingRequest.php
├── Http/Requests/CreateSupportTicketRequest.php
├── Http/Requests/CreateSupportMessageRequest.php
├── Http/Requests/DashboardRequest.php
├── Support/PartnerApiResponse.php
├── Support/PartnerPayloadRedactor.php
├── Services/PartnerMappingService.php
├── Services/OnboardingService.php
├── Services/SupportTicketBridge.php
├── Services/OneTimeLoginService.php
├── Services/PackageService.php
├── Services/DashboardService.php
└── docs/FizaHUB-Partner-API.postman_collection.json
tests/Feature/APIPartnerFizaHUB/
├── ModuleRoutingTest.php
├── PartnerAuthenticationTest.php
├── PartnerPersistenceTest.php
├── PartnerRequestLifecycleTest.php
├── OnboardingApiTest.php
├── SupportTicketApiTest.php
├── OneTimeLoginApiTest.php
├── PackageApiTest.php
└── DashboardApiTest.php
```

---

### Task 1: Scaffold the module provider, route groups, and health endpoint

**Files:**
- Create: `modules/APIPartnerFizaHUB/module.json`
- Create: `modules/APIPartnerFizaHUB/config/config.php`
- Create: `modules/APIPartnerFizaHUB/Providers/APIPartnerFizaHUBServiceProvider.php`
- Create: `modules/APIPartnerFizaHUB/Routes/api.php`
- Create: `modules/APIPartnerFizaHUB/Routes/web.php`
- Create: `modules/APIPartnerFizaHUB/Http/Controllers/HealthController.php`
- Create: `tests/Feature/APIPartnerFizaHUB/ModuleRoutingTest.php`

**Interfaces:**
- Consumes: provider auto-discovery in `bootstrap/providers.php` and the existing `Modules\\` PSR-4 mapping.
- Produces: provider class `Modules\APIPartnerFizaHUB\Providers\APIPartnerFizaHUBServiceProvider`, route name prefix `partner.fizahub.`, API URI prefix `api/v1/partners/fizahub`, and `GET /health`.

- [ ] **Step 1: Write the failing provider and route test**

Assert the provider is loaded, the route name `partner.fizahub.health` exists, and the unauthenticated health route initially returns the JSON shape from the fixed API contract rather than HTML.

- [ ] **Step 2: Run the route test and verify RED**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/ModuleRoutingTest.php`

Expected: FAIL because the provider and named route do not exist.

- [ ] **Step 3: Add the minimal scaffold**

Set `module.json` to provider priority `30`. In `register()`, merge `config/config.php` under `modules.apipartnerfizahub`. In `boot()`, load `Routes/api.php`, `Routes/web.php`, and `Database/Migrations`. Define the API group with `api` middleware, the fixed prefix, and route names. Return health data exactly as `status=ok`, `partner=fizahub`, `api_version=v1`, and an ISO-8601 `server_time`.

- [ ] **Step 4: Run the route test and verify GREEN**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/ModuleRoutingTest.php`

Expected: PASS with one registered health route and JSON content type.

- [ ] **Step 5: Commit the scaffold**

Run: `git add modules/APIPartnerFizaHUB tests/Feature/APIPartnerFizaHUB/ModuleRoutingTest.php && git commit -m "feat: scaffold FizaHUB partner API module"`

### Task 2: Add config, partner authentication, mandatory headers, and rate limiting

**Files:**
- Modify: `modules/APIPartnerFizaHUB/config/config.php`
- Modify: `modules/APIPartnerFizaHUB/Providers/APIPartnerFizaHUBServiceProvider.php`
- Modify: `modules/APIPartnerFizaHUB/Routes/api.php`
- Create: `modules/APIPartnerFizaHUB/Http/Middleware/VerifyPartnerToken.php`
- Create: `modules/APIPartnerFizaHUB/Http/Middleware/HandlePartnerRequest.php`
- Create: `modules/APIPartnerFizaHUB/Support/PartnerApiResponse.php`
- Modify: `.env.example`
- Create: `tests/Feature/APIPartnerFizaHUB/PartnerAuthenticationTest.php`

**Interfaces:**
- Consumes: `FIZAHUB_PARTNER_TOKEN`, `FIZAHUB_RATE_LIMIT_PER_MINUTE`, `FIZAHUB_ONE_TIME_LOGIN_TTL_MINUTES`, and Laravel `RateLimiter`.
- Produces: `VerifyPartnerToken::handle(Request, Closure): Response`, `HandlePartnerRequest::handle(Request, Closure): Response`, named limiter `fizahub-partner`, and `PartnerApiResponse::success()` / `error()`.

- [ ] **Step 1: Write failing authentication and header tests**

Cover missing/incorrect bearer token (401), wrong `X-Partner` (400), missing or non-UUID `X-Request-Id` (400), valid headers (200), response request-ID echo, and limiter exhaustion (429 JSON, not HTML).

- [ ] **Step 2: Run the authentication test and verify RED**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/PartnerAuthenticationTest.php`

Expected: FAIL because the health route is not protected and error responses are not standardized.

- [ ] **Step 3: Implement configuration and middleware**

Use these config defaults: partner code `fizahub`, rate limit `60` requests/minute, login TTL `5` minutes, timezone `Asia/Ho_Chi_Minh`, default package `base`, package map `base => mlhub-free-da-nang`, and industry alias `restaurant_food => restaurant_eatery`. Reject an empty configured token as unauthorized. Register the rate limiter by `fizahub|<ip>` and attach middleware in this order: `api`, `VerifyPartnerToken`, `HandlePartnerRequest`, `throttle:fizahub-partner`.

- [ ] **Step 4: Document environment keys without secrets**

Append `FIZAHUB_PARTNER_TOKEN=`, `FIZAHUB_RATE_LIMIT_PER_MINUTE=60`, and `FIZAHUB_ONE_TIME_LOGIN_TTL_MINUTES=5` to `.env.example` under a dedicated FizaHUB partner section.

- [ ] **Step 5: Run the authentication test and verify GREEN**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/PartnerAuthenticationTest.php`

Expected: PASS for authentication, header validation, response shape, and rate-limit behavior.

- [ ] **Step 6: Commit authentication**

Run: `git add .env.example modules/APIPartnerFizaHUB tests/Feature/APIPartnerFizaHUB/PartnerAuthenticationTest.php && git commit -m "feat: secure FizaHUB partner endpoints"`

### Task 3: Add partner persistence models and migrations

**Files:**
- Create: `modules/APIPartnerFizaHUB/Database/Migrations/2026_07_13_000000_create_fizahub_partner_api_tables.php`
- Create: `modules/APIPartnerFizaHUB/Models/PartnerIntegration.php`
- Create: `modules/APIPartnerFizaHUB/Models/PartnerOnboardingRequest.php`
- Create: `modules/APIPartnerFizaHUB/Models/PartnerApiLog.php`
- Create: `modules/APIPartnerFizaHUB/Models/PartnerOneTimeLogin.php`
- Create: `tests/Feature/APIPartnerFizaHUB/PartnerPersistenceTest.php`

**Interfaces:**
- Consumes: existing foreign keys `users.id`, `teams.id`, `lb_businesses.id`, and `support_tickets.id`.
- Produces: the three required partner tables plus `partner_one_time_logins` for atomic single-use login.

- [ ] **Step 1: Write failing schema/model tests**

Assert required columns, JSON/datetime casts, relationships, and database constraints. Test unique `partner_onboarding_requests.request_id`, unique `(partner_code, external_business_id)`, unique `partner_one_time_logins.token_hash`, and unique nullable `(partner_code, method, endpoint, idempotency_key)`.

- [ ] **Step 2: Run persistence tests and verify RED**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/PartnerPersistenceTest.php`

Expected: FAIL because the four tables and models do not exist.

- [ ] **Step 3: Create exact schemas**

Create the requested columns for `partner_integrations`, `partner_onboarding_requests`, and `partner_api_logs`. Add foreign keys with `nullOnDelete()` for mapped MLHUB IDs, indexes on `(partner_code, external_user_id)`, `(partner_code, status)`, `(partner_code, request_id)`, and `(partner_code, created_at)`, and a unique key on `(partner_code, external_business_id)`. Add `partner_one_time_logins` with `id`, `partner_integration_id`, `user_id`, `request_id`, `token_hash`, `expires_at`, `used_at`, and timestamps; cascade on integration deletion and user deletion.

- [ ] **Step 4: Add model casts and relationships**

Cast verification/payload/duplicate-check/metadata/request-payload/response-payload to arrays, mapped IDs and status code to integers, and expiry/use timestamps to datetimes. Define integration relationships to user/team/business, onboarding request, and login tokens; define onboarding relationships to mapped records and support ticket.

- [ ] **Step 5: Run persistence tests and verify GREEN**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/PartnerPersistenceTest.php`

Expected: PASS on SQLite and all uniqueness/relationship assertions.

- [ ] **Step 6: Commit persistence**

Run: `git add modules/APIPartnerFizaHUB/Database modules/APIPartnerFizaHUB/Models tests/Feature/APIPartnerFizaHUB/PartnerPersistenceTest.php && git commit -m "feat: persist FizaHUB partner mappings"`

### Task 4: Implement API formatting, redacted audit logging, and idempotency replay

**Files:**
- Modify: `modules/APIPartnerFizaHUB/Http/Middleware/HandlePartnerRequest.php`
- Modify: `modules/APIPartnerFizaHUB/Support/PartnerApiResponse.php`
- Create: `modules/APIPartnerFizaHUB/Support/PartnerPayloadRedactor.php`
- Modify: `modules/APIPartnerFizaHUB/Routes/api.php`
- Create: `tests/Feature/APIPartnerFizaHUB/PartnerRequestLifecycleTest.php`

**Interfaces:**
- Consumes: `PartnerApiLog`, route URI/method, `X-Request-Id`, `Idempotency-Key`, and JSON responses.
- Produces: one audit row per request, recursive redaction, idempotency reservation/replay, and JSON exception conversion.

- [ ] **Step 1: Write failing lifecycle tests**

Test GET logging, POST reservation, completed-response replay, payload-hash conflict, in-progress conflict, exception logging, and recursive redaction of `authorization`, `token`, `password`, `cccd`, `identity_document`, `business_license_file`, and one-time login `url` values.

- [ ] **Step 2: Run lifecycle tests and verify RED**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/PartnerRequestLifecycleTest.php`

Expected: FAIL because requests are not logged or replayed.

- [ ] **Step 3: Implement the lifecycle boundary**

Hash the canonical JSON body with SHA-256. For idempotent routes, atomically insert a log with `status_code=0`; on unique-key collision, compare the stored `_request_hash`, return 409 on mismatch, return 409 when status is still zero, or replay the stored response/status on completion. For all routes, finalize the row with redacted payloads and status. Cap each serialized payload at 64 KiB by replacing oversized content with `{ "truncated": true, "sha256": "<digest>" }`.

- [ ] **Step 4: Convert failures consistently**

Convert validation exceptions to 422, missing model records to 404, throttle exceptions to 429, and unexpected exceptions to 500 while calling `report($exception)`. Do not include stack traces, SQL, configured tokens, raw login URLs, or session cookies in the response or log.

- [ ] **Step 5: Run lifecycle tests and verify GREEN**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/PartnerRequestLifecycleTest.php`

Expected: PASS for audit, replay, conflict, redaction, and error formatting.

- [ ] **Step 6: Commit request lifecycle**

Run: `git add modules/APIPartnerFizaHUB tests/Feature/APIPartnerFizaHUB/PartnerRequestLifecycleTest.php && git commit -m "feat: add partner API audit and idempotency"`

### Task 5: Validate and map onboarding requests

**Files:**
- Create: `modules/APIPartnerFizaHUB/Http/Requests/UpsertOnboardingRequest.php`
- Create: `modules/APIPartnerFizaHUB/Services/PartnerMappingService.php`
- Create: `modules/APIPartnerFizaHUB/Services/OnboardingService.php`
- Create: `modules/APIPartnerFizaHUB/Http/Controllers/OnboardingController.php`
- Modify: `modules/APIPartnerFizaHUB/Routes/api.php`
- Create: `tests/Feature/APIPartnerFizaHUB/OnboardingApiTest.php`

**Interfaces:**
- Consumes: onboarding JSON, `X-Request-Id`, `BusinessTypeCatalog`, package config, and partner models.
- Produces: `OnboardingService::upsert(array $payload, string $requestId): PartnerOnboardingRequest`, `OnboardingService::find(string $requestId): PartnerOnboardingRequest`, POST/GET onboarding routes, and normalized duplicate evidence.

- [ ] **Step 1: Write failing request validation tests**

Require external IDs, supported package, owner name/phone/email, business name/industry, and verification fields. Reject unknown package codes, invalid URLs/emails/dates, and any recursive key matching `cccd`, `identity_card`, `identity_document`, `identity_image`, `business_license_file`, or `business_license_image`. Permit `tax_code` and `business_license_number` as strings up to 80 characters.

- [ ] **Step 2: Write failing mapping and status tests**

Assert `restaurant_food` maps to `restaurant_eatery`, request ID comes from `X-Request-Id`, unverified requests become `pending_verification`, and duplicate email/tax/license requests become `needs_review` with structured `duplicate_check` entries containing only match type and internal numeric ID.

- [ ] **Step 3: Run onboarding tests and verify RED**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/OnboardingApiTest.php`

Expected: FAIL because onboarding routes and services do not exist.

- [ ] **Step 4: Implement validation and normalization**

Normalize external IDs with trim, email with lowercase, phone/tax/license by removing spaces and punctuation, and verification timestamp to UTC for storage. Save the original accepted business/owner fields in `payload`, save only verification status in `verification_status`, and save normalized tax/license plus source industry labels in integration metadata after provisioning.

- [ ] **Step 5: Implement upsert/status endpoints**

Use `request_id` as the upsert key. Return 201 for a first request, 200 for an updated request, and 202 for `pending_verification` or `needs_review`. Return data keys `request_id`, `external_business_id`, `package_code`, `status`, `current_step`, `duplicate_check`, `support_ticket_id`, and mapped MLHUB IDs; expose support ticket secure ID rather than the numeric foreign key.

- [ ] **Step 6: Run onboarding tests and verify GREEN**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/OnboardingApiTest.php`

Expected: PASS for validation, prohibited identity payloads, mappings, duplicates, upsert, and status lookup.

- [ ] **Step 7: Commit onboarding mapping**

Run: `git add modules/APIPartnerFizaHUB tests/Feature/APIPartnerFizaHUB/OnboardingApiTest.php && git commit -m "feat: validate FizaHUB onboarding requests"`

### Task 6: Provision verified onboarding into existing MLHUB user, team, plan, and business models

**Files:**
- Modify: `modules/APIPartnerFizaHUB/Services/OnboardingService.php`
- Modify: `modules/APIPartnerFizaHUB/Services/PartnerMappingService.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/OnboardingApiTest.php`

**Interfaces:**
- Consumes: `DefaultSignupPlanResolver`, `PersonalTeamProvisioner`, `AffiliateService`, `User`, `AdminPlan`, `LocalBusiness`, and `BusinessTypeCatalog::resolveSelection()`.
- Produces: a transactionally consistent completed onboarding and `PartnerIntegration` mapping.

- [ ] **Step 1: Add failing provisioning tests**

Assert a verified request creates exactly one user, personal team, owner membership, local business, onboarding record, and integration. Assert `package_code=base` assigns `mlhub-free-da-nang`, user timezone is `Asia/Ho_Chi_Minh`, locale is `vi`, username is deterministic `fizahub_<12-char hash>`, generated password is not returned, and all mapped IDs are populated.

- [ ] **Step 2: Add failing retry and rollback tests**

Assert updating the same request or submitting the same external business through a new request does not duplicate MLHUB records. Force business creation failure and assert no partial user/team/integration survives the transaction.

- [ ] **Step 3: Run onboarding provisioning tests and verify RED**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/OnboardingApiTest.php --filter=provision`

Expected: FAIL because verified onboarding is not provisioned.

- [ ] **Step 4: Implement transactional provisioning**

Lock the onboarding row and existing integration lookup. Resolve the configured plan slug, create the user with a 64-character random password, set `email_verified_at` to upstream `verified_at` only when `identity_verified=true`, ensure the affiliate profile and personal team, resolve the industry taxonomy, create `lb_businesses`, then write all mapped IDs to both partner records. Set status/current step to `completed`/`ready` only after the transaction succeeds.

- [ ] **Step 5: Implement safe existing-mapping updates**

When the same `(partner_code, external_business_id)` is already mapped, update allowed owner/business profile fields and verification metadata on those mapped records only. Never resolve an unmapped email collision by adopting that user.

- [ ] **Step 6: Run full onboarding tests and verify GREEN**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/OnboardingApiTest.php`

Expected: PASS with no duplicate or partial provisioning records.

- [ ] **Step 7: Commit provisioning**

Run: `git add modules/APIPartnerFizaHUB/Services tests/Feature/APIPartnerFizaHUB/OnboardingApiTest.php && git commit -m "feat: provision MLHUB accounts from FizaHUB"`

### Task 7: Bridge support ticket creation and listing

**Files:**
- Create: `modules/APIPartnerFizaHUB/Http/Requests/CreateSupportTicketRequest.php`
- Create: `modules/APIPartnerFizaHUB/Services/SupportTicketBridge.php`
- Create: `modules/APIPartnerFizaHUB/Http/Controllers/SupportTicketController.php`
- Modify: `modules/APIPartnerFizaHUB/Routes/api.php`
- Modify: `modules/APIPartnerFizaHUB/Services/OnboardingService.php`
- Create: `tests/Feature/APIPartnerFizaHUB/SupportTicketApiTest.php`

**Interfaces:**
- Consumes: a completed `PartnerIntegration`, `SupportTicket`, optional active support category/type IDs, and legacy Unix timestamps.
- Produces: `SupportTicketBridge::create()`, `list()`, `createOnboardingReviewTicket()`, POST/GET business support routes, and onboarding-review tickets.

- [ ] **Step 1: Write failing create/list tests**

Validate `subject` (1-255), `message` (1-5000), optional `category_id`/`type_id`, and require idempotency. Assert creation writes `uid=open_by=mlhub_user_id`, `team_id=mlhub_workspace_id`, `status=1`, `user_read=false`, `admin_read=true`, and Unix `created`/`changed`; listing returns only tickets owned by the mapped user/team and paginates with `per_page` default 20, maximum 100.

- [ ] **Step 2: Run support tests and verify RED**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/SupportTicketApiTest.php --filter="create|list"`

Expected: FAIL because the support bridge and routes do not exist.

- [ ] **Step 3: Implement bridge creation and serialization**

Generate 32-character `id_secure`, reuse existing support status meanings (`1=open`, `2=resolved`, `0=closed`), log activity, and serialize `ticket_id`, `subject`, `status`, `created_at`, `updated_at`, `last_message_at`, and `unread_by_business`. Return 201 for create and 200 for list.

- [ ] **Step 4: Connect onboarding review cases**

For `pending_verification` or `needs_review`, create one support ticket per onboarding request with duplicate/verification evidence, set `partner_onboarding_requests.support_ticket_id`, and reuse it on later updates.

- [ ] **Step 5: Run support create/list tests and verify GREEN**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/SupportTicketApiTest.php --filter="create|list|onboarding"`

Expected: PASS with support records visible to existing admin/support screens.

- [ ] **Step 6: Commit support ticket bridge**

Run: `git add modules/APIPartnerFizaHUB tests/Feature/APIPartnerFizaHUB/SupportTicketApiTest.php && git commit -m "feat: bridge FizaHUB support tickets"`

### Task 8: Expose support ticket detail and polling conversation messages

**Files:**
- Create: `modules/APIPartnerFizaHUB/Http/Requests/CreateSupportMessageRequest.php`
- Create: `modules/APIPartnerFizaHUB/Http/Controllers/SupportMessageController.php`
- Modify: `modules/APIPartnerFizaHUB/Http/Controllers/SupportTicketController.php`
- Modify: `modules/APIPartnerFizaHUB/Services/SupportTicketBridge.php`
- Modify: `modules/APIPartnerFizaHUB/Routes/api.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/SupportTicketApiTest.php`

**Interfaces:**
- Consumes: `SupportTicket::comments()`, `SupportComment`, mapped user identity, and `since` query timestamp for polling.
- Produces: `SupportTicketBridge::detail(string $ticketId, ?CarbonImmutable $since): array`, `addMessage()`, GET detail, and POST messages.

- [ ] **Step 1: Write failing conversation tests**

Assert detail contains a synthetic initial message ID `<ticket_id>:initial` from ticket content plus ordered comments. Assert a comment from the mapped user serializes sender type `business`, an admin comment serializes `admin`, and `since` returns only messages newer than the supplied ISO-8601 timestamp. Assert cross-integration ticket access returns 404.

- [ ] **Step 2: Write failing message tests**

Require `message` up to 5000 characters and idempotency. Assert HTML is stripped to plain text before persistence, a message on closed/resolved tickets returns 409 `ticket_not_open`, and creation updates `admin_read=true`, `user_read=false`, and `changed=time()`.

- [ ] **Step 3: Run conversation tests and verify RED**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/SupportTicketApiTest.php --filter="detail|message|poll"`

Expected: FAIL because detail and message endpoints do not exist.

- [ ] **Step 4: Implement scoped detail and polling**

Resolve tickets only through a FizaHUB integration matching `uid` and, when present, `team_id`. Return ticket state plus `messages`, `next_poll_after_seconds=15`, and `last_message_at`. Do not add websocket, broadcast, or realtime infrastructure.

- [ ] **Step 5: Implement partner messages**

Create `SupportComment` with a random 32-character secure ID, mapped user ID, and Unix timestamps. Log the existing-style activity event `partner.fizahub.support.reply`. Return 201 with the serialized message.

- [ ] **Step 6: Run complete support tests and verify GREEN**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/SupportTicketApiTest.php`

Expected: PASS for creation, listing, detail, polling, admin replies, partner replies, isolation, and idempotency.

- [ ] **Step 7: Commit support conversations**

Run: `git add modules/APIPartnerFizaHUB tests/Feature/APIPartnerFizaHUB/SupportTicketApiTest.php && git commit -m "feat: expose FizaHUB support conversations"`

### Task 9: Implement secure one-time portal login

**Files:**
- Create: `modules/APIPartnerFizaHUB/Services/OneTimeLoginService.php`
- Create: `modules/APIPartnerFizaHUB/Http/Controllers/OneTimeLoginController.php`
- Create: `modules/APIPartnerFizaHUB/Http/Controllers/ConsumeOneTimeLoginController.php`
- Modify: `modules/APIPartnerFizaHUB/Routes/api.php`
- Modify: `modules/APIPartnerFizaHUB/Routes/web.php`
- Create: `tests/Feature/APIPartnerFizaHUB/OneTimeLoginApiTest.php`

**Interfaces:**
- Consumes: completed integration, `partner_one_time_logins`, Laravel temporary signed routes, `Auth::guard('web')`, and session regeneration.
- Produces: `OneTimeLoginService::issue(PartnerIntegration, string $requestId): array`, `consume(string $plainToken, Request): RedirectResponse`, POST API route, and signed web consume route.

- [ ] **Step 1: Write failing issue tests**

Assert POST returns 201 with `url` and `expires_at`, stores only a SHA-256 token hash, uses a 256-bit random token, applies the configured 5-minute TTL, and refuses missing/incomplete integrations. Assert audit logs redact the URL.

- [ ] **Step 2: Write failing consume tests**

Assert valid signed URL logs in the mapped user, regenerates the session, marks `used_at`, and redirects to `portal.dashboard`; second use, expired token, modified signature, or unknown token must fail without login. Assert two concurrent consumers cannot both succeed by testing the locked row's used state.

- [ ] **Step 3: Run login tests and verify RED**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/OneTimeLoginApiTest.php`

Expected: FAIL because issue/consume routes and service do not exist.

- [ ] **Step 4: Implement issuance and atomic consumption**

Generate the plain token with `random_bytes(32)`, store only `hash('sha256', $plainToken)`, and build `URL::temporarySignedRoute('partner.fizahub.login.consume', expires_at, ['token' => $plainToken])`. On consume, use a transaction and `lockForUpdate()`, reject used/expired tokens, mark `used_at` before commit, then authenticate, regenerate session, and record `partner.fizahub.login.consume` activity.

- [ ] **Step 5: Run login tests and verify GREEN**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/OneTimeLoginApiTest.php`

Expected: PASS for issuance, signature, expiry, single use, session fixation protection, and redaction.

- [ ] **Step 6: Commit one-time login**

Run: `git add modules/APIPartnerFizaHUB tests/Feature/APIPartnerFizaHUB/OneTimeLoginApiTest.php && git commit -m "feat: add FizaHUB one-time portal login"`

### Task 10: Expose the mapped MLHUB package

**Files:**
- Create: `modules/APIPartnerFizaHUB/Services/PackageService.php`
- Create: `modules/APIPartnerFizaHUB/Http/Controllers/PackageController.php`
- Modify: `modules/APIPartnerFizaHUB/Routes/api.php`
- Create: `tests/Feature/APIPartnerFizaHUB/PackageApiTest.php`

**Interfaces:**
- Consumes: `PartnerIntegration`, mapped `User::plan`, `AdminPlan`, and configured package slug map.
- Produces: `PackageService::forBusiness(string $externalBusinessId): array` and GET package endpoint.

- [ ] **Step 1: Write failing package tests**

Assert `base` resolves to `mlhub-free-da-nang`; return `package_code`, plan `name`, `slug`, `status`, `starts_at`, `expires_at`, `is_trial`, and a whitelisted `limits` object containing business/campaign/landing-page/QR/team limits. Assert unmapped business is 404 and plan permissions outside the whitelist are absent.

- [ ] **Step 2: Run package tests and verify RED**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/PackageApiTest.php`

Expected: FAIL because the package endpoint does not exist.

- [ ] **Step 3: Implement package serialization**

Resolve only through the integration's mapped user and current plan. Report integration status separately from plan status, use the integration package code as the partner-facing code, and never expose price, payment subscription, credit balance, or the full plan permissions JSON.

- [ ] **Step 4: Run package tests and verify GREEN**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/PackageApiTest.php`

Expected: PASS for mapping, whitelist, dates, and missing integration behavior.

- [ ] **Step 5: Commit package API**

Run: `git add modules/APIPartnerFizaHUB tests/Feature/APIPartnerFizaHUB/PackageApiTest.php && git commit -m "feat: expose FizaHUB package summary"`

### Task 11: Build the date-scoped FizaHUB dashboard service

**Files:**
- Create: `modules/APIPartnerFizaHUB/Http/Requests/DashboardRequest.php`
- Create: `modules/APIPartnerFizaHUB/Services/DashboardService.php`
- Create: `modules/APIPartnerFizaHUB/Http/Controllers/DashboardController.php`
- Modify: `modules/APIPartnerFizaHUB/Routes/api.php`
- Create: `tests/Feature/APIPartnerFizaHUB/DashboardApiTest.php`

**Interfaces:**
- Consumes: `LocalBusiness`, `QrCampaign`, `QrScan`, `LeadSubmission`, `ReviewFeedback`, `CouponRedemption`, `Booking`, `FeedbackResponse`, and event customer phone/email fields.
- Produces: `DashboardService::summarize(PartnerIntegration, CarbonImmutable $from, CarbonImmutable $to): array` and GET dashboard endpoint.

- [ ] **Step 1: Write failing date and isolation tests**

Test default 30 days, inclusive explicit dates, invalid/reversed/over-366-day ranges, `Asia/Ho_Chi_Minh` day boundaries, and exclusion of records belonging to another business or outside the requested range.

- [ ] **Step 2: Write failing metric tests**

Seed one mapped business and assert `data.metrics` has exact keys `businesses`, `campaigns`, `active_campaigns`, `qr_scans`, `new_leads`, `new_reviews`, `coupon_claims`, `coupon_used`, `bookings`, `feedback`, `returning_customers`, and `conversion_rate`, while `data.campaigns` is the campaign collection. Define `new_reviews` as rating >= 4 review-feedback events, `coupon_used` as non-null `used_at`, `feedback` as form responses plus rating <= 3 review feedback, and conversion rate as `(new_leads + new_reviews + coupon_claims + bookings + feedback) / qr_scans * 100`, rounded to two decimals and zero when scans are zero.

- [ ] **Step 3: Write failing collection/insight tests**

Assert `campaigns` list contains at most 10 rows sorted by conversions then scans; `trend` has one row per calendar day with scans/leads/reviews/coupons/bookings/feedback; `insights` contains deterministic metric facts; and `suggested_actions` uses stable action codes `create_campaign`, `publish_campaign`, `share_qr`, `follow_up_leads`, `reply_feedback`, and `improve_conversion`. Count returning customers as normalized non-empty phone/email identities occurring in at least two lead/booking/coupon/review/feedback events in the selected period.

- [ ] **Step 4: Run dashboard tests and verify RED**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/DashboardApiTest.php`

Expected: FAIL because the dashboard service and endpoint do not exist.

- [ ] **Step 5: Implement bounded aggregate queries**

Resolve campaign IDs from the single mapped `mlhub_business_id`, apply the same inclusive date window to every event query, group daily trends in SQL where portable, and finish only small bounded collections in PHP. Do not call Livewire components; reuse their model semantics while keeping an API-specific service.

- [ ] **Step 6: Implement deterministic insights/actions**

Emit facts from current metrics, not AI text. Add `create_campaign` when campaign count is zero, `publish_campaign` when none is active, `share_qr` when active campaigns have zero scans, `follow_up_leads` when leads are positive, `reply_feedback` when low-score feedback exists, and `improve_conversion` when scans are at least 20 and conversion rate is below 5 percent.

- [ ] **Step 7: Run dashboard tests and verify GREEN**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/DashboardApiTest.php`

Expected: PASS for every required metric, date boundary, trend, insight, action, and tenant-isolation assertion.

- [ ] **Step 8: Commit dashboard API**

Run: `git add modules/APIPartnerFizaHUB tests/Feature/APIPartnerFizaHUB/DashboardApiTest.php && git commit -m "feat: add FizaHUB growth dashboard API"`

### Task 12: Add module README and Postman collection

**Files:**
- Create: `modules/APIPartnerFizaHUB/README.md`
- Create: `modules/APIPartnerFizaHUB/docs/FizaHUB-Partner-API.postman_collection.json`
- Create: `tests/Feature/APIPartnerFizaHUB/DocumentationContractTest.php`

**Interfaces:**
- Consumes: all ten endpoint contracts and environment keys.
- Produces: deploy/operator documentation and an importable Postman Collection v2.1.

- [ ] **Step 1: Write failing documentation contract test**

Parse the Postman JSON, assert schema v2.1, assert exactly the ten MVP API requests, assert collection variables `base_url`, `partner_token`, `external_business_id`, `onboarding_request_id`, `ticket_id`, and assert every request sends the mandatory headers. Assert idempotent POST scripts generate UUID request and idempotency values.

- [ ] **Step 2: Run documentation test and verify RED**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/DocumentationContractTest.php`

Expected: FAIL because README and collection do not exist.

- [ ] **Step 3: Write the README**

Document installation through module auto-discovery/migration, environment configuration, authentication, idempotency semantics, all request/response examples, onboarding status transitions, support polling every 15 seconds, login TTL/single-use behavior, dashboard metric definitions, log redaction, and the explicit MVP exclusions.

- [ ] **Step 4: Build the Postman collection**

Include the supplied onboarding example encoded as UTF-8, pre-request scripts using `pm.variables.replaceIn('{{$guid}}')`, response tests for status/request ID/JSON shape, and no real token or production URL.

- [ ] **Step 5: Run documentation test and verify GREEN**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB/DocumentationContractTest.php`

Expected: PASS and JSON parses without warnings.

- [ ] **Step 6: Commit documentation**

Run: `git add modules/APIPartnerFizaHUB/README.md modules/APIPartnerFizaHUB/docs tests/Feature/APIPartnerFizaHUB/DocumentationContractTest.php && git commit -m "docs: document FizaHUB partner API"`

### Task 13: Complete cross-endpoint feature and security coverage

**Files:**
- Modify: `tests/Feature/APIPartnerFizaHUB/PartnerAuthenticationTest.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/PartnerRequestLifecycleTest.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/OnboardingApiTest.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/SupportTicketApiTest.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/OneTimeLoginApiTest.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/PackageApiTest.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/DashboardApiTest.php`

**Interfaces:**
- Consumes: the complete module.
- Produces: regression coverage for the fixed API/security contract and the ten-route MVP boundary.

- [ ] **Step 1: Add a full lifecycle feature test**

Exercise onboarding through completed mapping, package lookup, dashboard lookup, support create/list/detail/message, admin reply retrieval, and one-time login issuance/consumption using one external business. Assert IDs remain consistent across every response.

- [ ] **Step 2: Add negative tenant and scope tests**

For every business/ticket route, assert another external business cannot read or mutate the first business's records. Assert route list contains no partner endpoints for customer CRUD, AI, campaign creation, coupon creation, landing pages, Google Business, finance, or identity upload.

- [ ] **Step 3: Add privacy and secret tests**

Submit nested prohibited identity keys, scan API logs for bearer token/raw login URL/password, and assert none persist. Assert tax/license identifiers appear only in onboarding payload/integration metadata and never in dashboard/package/support responses.

- [ ] **Step 4: Run all module tests**

Run: `php artisan test tests/Feature/APIPartnerFizaHUB`

Expected: all partner API tests pass with zero failures.

- [ ] **Step 5: Commit complete feature coverage**

Run: `git add tests/Feature/APIPartnerFizaHUB && git commit -m "test: cover FizaHUB partner API lifecycle"`

### Task 14: Verify formatting, routes, migrations, and full regression suite

**Files:**
- Verify: `modules/APIPartnerFizaHUB/**`
- Verify: `tests/Feature/APIPartnerFizaHUB/**`
- Verify: `.env.example`

**Interfaces:**
- Consumes: completed implementation and repository scripts.
- Produces: fresh evidence that the module is installable, isolated, formatted, and regression-safe.

- [ ] **Step 1: Run focused tests from a clean application boot**

Run: `php artisan optimize:clear && php artisan test tests/Feature/APIPartnerFizaHUB`

Expected: all FizaHUB partner tests pass after caches are cleared.

- [ ] **Step 2: Verify exactly ten MVP API routes plus one web consume route**

Run: `php artisan route:list --path=partners/fizahub`

Expected: ten `/api/v1/partners/fizahub` routes with the specified methods and URIs.

Run: `php artisan route:list --name=partner.fizahub.login.consume`

Expected: one signed web GET route used only to consume a one-time login token.

- [ ] **Step 3: Verify migration status**

Run: `php artisan migrate:status`

Expected: the FizaHUB partner migration is discovered under the module provider and is marked as run after migration.

- [ ] **Step 4: Run formatting checks**

Run: `composer lint:check`

Expected: no Pint formatting errors.

- [ ] **Step 5: Run the complete test suite**

Run: `php artisan test`

Expected: zero failed tests across the repository.

- [ ] **Step 6: Review implementation scope**

Run: `git diff --name-only HEAD~13..HEAD`

Expected: changes are limited to `modules/APIPartnerFizaHUB`, `tests/Feature/APIPartnerFizaHUB`, `.env.example`, and this plan; no Google Business scheduler or static-page files appear.

- [ ] **Step 7: Record final verification commit if formatting changed files**

Run: `git add modules/APIPartnerFizaHUB tests/Feature/APIPartnerFizaHUB .env.example && git commit -m "chore: verify FizaHUB partner API"`
