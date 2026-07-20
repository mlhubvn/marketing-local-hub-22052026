# FizaHUB UI API Contract Design

**Date:** 2026-07-20

**Status:** Approved

**Source of truth:** Figma file `Marketing - FizaHUB`, Page 1, section `FizaMKT × MLHUB — Hybrid Integration Flow (FizaHUB UI)`.

## Goal

Replace the current FizaHUB partner API contract with a UI-driven contract that serves all 15 approved FizaHUB app screens. The contract must support onboarding, Growth Marketing, Support, and CRM without requiring partner developers to guess identifiers or prerequisites.

## Scope

- Expose exactly 22 Core API endpoints under `/api/v1/partners/fizahub`.
- Remove the support attachment route completely.
- Preserve `SupportTicketBridge`, `support_tickets`, all text-message support behavior, and the ticket close/reopen lifecycle.
- Keep existing attachment tables and models dormant for possible future use; do not add a destructive migration.
- Preserve partner authentication, request IDs, idempotency, tenant isolation, and audit behavior.
- Update production code, tests, Postman, README, documentation pages, and endpoint matrix to the same contract.
- Implement every production behavior through a red-green-refactor cycle with a failing test observed before the production change.

## Architectural Approach

Use a UI/resource hybrid API. Each endpoint maps to a stable domain resource or command, while selected screen-level aggregates reduce client round trips:

- `marketing-status` is the bootstrap resource for activation state and known identifiers.
- `marketing-catalog` combines goals, industries, and package choices required by the onboarding selection screen.
- `dashboard` serves both activated home and growth overview through a range query.
- `growth-insights` combines score, sources, highlights, recommendations, and CTA handoff data.
- Support ticket list includes its summary, avoiding a second request for the Support Center.
- Support ticket routes are nested below the external business to make tenant scoping explicit.
- CRM access is represented as creation of a short-lived `crm-login-links` resource.

The module remains an adapter over existing MLHUB users, teams, local businesses, packages, campaigns, and support tickets. It must not duplicate those domain stores.

## Public API Conventions

### Prefix and authentication

All Core endpoints use:

```text
/api/v1/partners/fizahub
```

Required headers:

- `Authorization: Bearer {partner_token}`
- `X-Partner: fizahub`
- `X-Request-Id: {uuid}`
- `Idempotency-Key: {unique key}` for state-changing requests

### Envelope

Successful responses use:

```json
{
  "success": true,
  "data": {},
  "meta": {
    "request_id": "uuid"
  },
  "error": null
}
```

Errors use:

```json
{
  "success": false,
  "data": null,
  "meta": {
    "request_id": "uuid"
  },
  "error": {
    "code": "machine_readable_code",
    "message": "Human-readable Vietnamese message.",
    "details": {}
  }
}
```

Every response, including authentication, validation, not-found, conflict, readiness, and rate-limit errors, echoes the effective `X-Request-Id` as `meta.request_id`.

### Idempotency semantics

Every write endpoint requires `Idempotency-Key` and applies these rules:

- The same key and the same normalized payload return the original HTTP status and response without repeating side effects.
- The same key with a different normalized payload returns HTTP 409 `idempotency_conflict`.
- Sending a support message cannot create a duplicate message.
- Campaign approval cannot record the same decision twice.
- Repeated close or reopen requests never produce an untyped server error; an exact idempotent replay returns the stored response, while a new key is evaluated against the current state machine.
- Reusing a CRM login-link key returns the same link while it remains unused and unexpired. Once used or expired, the client must send a new key.

### Identifier handoff

Creation and bootstrap responses must return identifiers and `links` needed by later steps. Postman tests persist these values automatically. A client must never need to inspect the database or invent an ID.

### Date ranges

Growth read endpoints accept:

- `range=today|7d|30d|90d|custom`
- `from=YYYY-MM-DD` and `to=YYYY-MM-DD` when `range=custom`

The default range is `30d`. A custom range requires both dates, `from` must not be after `to`, and the inclusive range must not exceed 366 days. Invalid ranges return HTTP 422 `validation_failed`. The default timezone is the configured FizaHUB partner timezone.

### Cursor pagination

All cursor-paginated collections use:

```json
{
  "items": [],
  "pagination": {
    "next_cursor": null,
    "has_more": false,
    "per_page": 20
  }
}
```

The server caps `per_page` at the documented maximum and returns HTTP 422 `validation_failed` for malformed cursors.

## Core Endpoint Contract

### System and authentication

#### 1. GET `/health`

Checks module readiness, database availability, required tables, default plan mapping, and relevant configuration. It does not expose secrets.

#### 2. POST `/partner/sso/verify`

Verifies the partner server-to-server credential and returns partner identity plus server time. It does not authenticate an MLHUB end user.

### Bootstrap and onboarding

#### 3. GET `/businesses/{external_business_id}/marketing-status`

This is the first app request. For an unknown business it returns HTTP 404 with `integration_not_found` and `details.next_action=create_onboarding_request`.

For a mapped business it returns:

- `external_business_id`
- `activation_status`
- `onboarding_status`
- `is_ready`
- effective and requested package codes
- `onboarding_request_id`
- onboarding `support_ticket_id`
- MLHUB user, workspace, and business IDs
- `capabilities.dashboard`, `capabilities.support`, and `capabilities.crm`
- links to the current onboarding request and other available resources

#### 4. GET `/marketing-catalog?industry={industry_code}`

Returns one catalog payload for screen 02:

- `max_goal_selection=3`
- `marketing_goals[]` with code, label, description, and icon hint
- `industries[]`
- `default_package_code`
- `packages[]` with name, description, flags, features, recommended goals, and supported industries

The industry query filters or ranks recommendations; it does not create an industry-specific route.

#### 5. POST `/onboarding-requests`

Canonical payload:

```json
{
  "external_business_id": "fiza-business-123",
  "external_user_id": "fiza-user-456",
  "marketing_goal_codes": ["qr_checkin", "customer_retention"],
  "requested_package_code": "base",
  "owner": {
    "name": "Đoàn Văn Khoa",
    "email": "van-khoa.lqd123@gmail.com",
    "phone": "0901234888"
  },
  "business": {
    "name": "Fiza Store",
    "industry": "restaurant_food",
    "phone": "0901234888",
    "email": "contact@fizastore.vn",
    "website": "https://fizastore.vn",
    "address": "888 Lê Duẩn, Đà Nẵng"
  },
  "metadata": {}
}
```

Required fields:

- `external_business_id`
- `requested_package_code`
- one to three valid `marketing_goal_codes`
- `owner.name`
- `owner.email`
- `business.name`
- `business.industry`
- `business.phone`
- `business.email`
- `business.address`

Optional fields include `external_user_id`, `owner.phone`, `business.website`, and `metadata`. Legacy `package_code`, verification, tax, and business-license fields may be accepted but are not required.

First registration provisions exactly one user, personal team/workspace, local business, partner integration, active Free package assignment, canonical onboarding request, and onboarding support ticket in one transaction.

Onboarding HTTP outcomes are fixed:

- HTTP 201 when new resources are created in `awaiting_consultant`.
- HTTP 202 when new resources are created but onboarding is paused in `needs_review`.
- HTTP 200 when the same business and mapped login email were already registered.
- HTTP 409 `email_already_registered` when the login email belongs to another account.
- HTTP 409 `onboarding_email_mismatch` when the business is mapped to another login email.
- HTTP 422 `validation_failed` for an invalid payload.
- HTTP 503 when schema, default plan mapping, or another required dependency is not ready.

The response returns:

- `request_id`
- external IDs
- generated `username`
- resource creation flags
- `already_registered`
- public status, current step, label, and timeline
- requested and effective package codes
- onboarding support ticket ID
- MLHUB user, workspace, and business IDs
- `links.status`, `links.support_ticket`, and available next actions

#### 6. GET `/onboarding-requests/{request_id}`

Returns the onboarding resource and a five-step timeline:

1. `account_created`
2. `awaiting_consultant`
3. `in_consultation`
4. `configuring`
5. `ready`

Each step has `code`, `label`, `status`, `completed_at`, and `description`.

Stable public statuses are `awaiting_consultant`, `needs_review`, `in_consultation`, `configuring`, `ready`, `completed`, and `cancelled`. Existing internal values are mapped to these public values.

`needs_review` is not a sixth timeline step. It blocks the onboarding flow at the intake/review position before `in_consultation`.

Timeline step status is one of `completed`, `current`, `pending`, or `blocked`. A serialized step follows:

```json
{
  "code": "awaiting_consultant",
  "label": "Chờ tư vấn viên liên hệ",
  "status": "current",
  "completed_at": null,
  "description": "Yêu cầu đã vào hàng đợi tư vấn."
}
```

Activation status is one of `inactive`, `onboarding`, `active`, or `suspended`.

#### 7. PATCH `/businesses/{external_business_id}/profile`

Updates `owner.name` and editable business fields. It must reject attempts to modify external IDs, MLHUB IDs, or the login email.

#### 8. PATCH `/businesses/{external_business_id}/marketing-preferences`

Updates one to three marketing goal codes and the requested package code after onboarding. It records interest only; it never changes the effective package automatically.

### Growth Marketing

#### 9. GET `/businesses/{external_business_id}/dashboard`

Serves screens 06 and 07. The response includes:

- resolved period
- headline metrics and comparison changes
- trend series
- tool cards for presence, QR, vouchers, and customers
- active campaign count
- `next_actions[]`
- `last_synced_at`, `generated_at`, `next_refresh_at`, and freshness state

Zero-data businesses receive HTTP 200 with zero values and empty arrays.

#### 10. GET `/businesses/{external_business_id}/growth-insights`

Serves screen 08 in one request. It returns growth score, score change, customer sources, highlights, data period, and recommendations. Every recommendation includes a CTA descriptor with a supported action type such as `create_support_ticket` and its preset/context.

#### 11. GET `/businesses/{external_business_id}/campaigns`

Supports `status`, `q`, and cursor pagination. It returns status counts and campaign cards. No data returns HTTP 200 with an empty `items` array.

#### 12. GET `/businesses/{external_business_id}/campaigns/{campaign_id}`

Returns campaign identity, type, status, objective, period, metrics, data freshness, recommendations, and an optional approval object.

#### 13. POST `/businesses/{external_business_id}/campaigns/{campaign_id}/approval`

Supports the approved UI action for voucher/campaign content awaiting confirmation.

Payload:

```json
{
  "decision": "approved",
  "note": "Đồng ý nội dung và mức ưu đãi."
}
```

Allowed decisions are `approved` and `changes_requested`. The command is valid only when the campaign is in `pending_approval`. A changes request records the decision context and creates or returns the associated support workflow without creating duplicate tickets.

#### 14. GET `/businesses/{external_business_id}/package`

Returns effective, requested, and approved package data, plan slug, status, dates, trial state, UI-safe limits, and mapping status.

### Support

#### 15. GET `/businesses/{external_business_id}/support-presets`

Returns active SOP presets with default/locked subject, description, ticket type, required context, response SLA, supported response channels, and whether a campaign is required.

Required presets include onboarding, QR scan issue, growth recommendation, campaign request, and package upgrade.

#### 16. GET `/businesses/{external_business_id}/support-tickets`

Supports `q`, `status`, and cursor pagination. The response contains:

- `summary.open`
- `summary.resolved`
- `summary.closed`
- `summary.unread_by_business`
- `items[]`
- pagination metadata

The list includes onboarding tickets and all user-created or Growth handoff tickets. No data returns HTTP 200 with an empty array.

#### 17. POST `/businesses/{external_business_id}/support-tickets`

Canonical payload:

```json
{
  "preset_code": "qr_scan_not_recorded",
  "campaign_id": "campaign-123",
  "subject": "QR tại quầy chưa nhận lượt quét",
  "message": "QR đã đặt tại quầy nhưng dashboard chưa cập nhật lượt quét.",
  "response_channel": "in_app",
  "metadata": {
    "screen": "growth_marketing",
    "source": "fizahub"
  }
}
```

Preset validation and canonical subject rules are server-owned. The response returns ticket identity, status, timestamps, poll interval, and a detail link. There is no file or attachment field.

#### 18. GET `/businesses/{external_business_id}/support-tickets/{ticket_id}`

Returns ticket detail and messages. `messages_since` optionally limits polling results. Every response is scoped by the business path and includes `next_poll_after_seconds`.

#### 19. POST `/businesses/{external_business_id}/support-tickets/{ticket_id}/messages`

Adds a text-only business message to an open ticket. HTML is sanitized and the body is limited to the documented maximum.

#### 20. POST `/businesses/{external_business_id}/support-tickets/{ticket_id}/close`

Closes an open ticket through the support state machine.

#### 21. POST `/businesses/{external_business_id}/support-tickets/{ticket_id}/reopen`

Reopens a closed ticket through the support state machine.

### CRM

#### 22. POST `/businesses/{external_business_id}/crm-login-links`

Creates a single-use, short-lived CRM URL for the mapped user and workspace. It is allowed only when onboarding is `ready` or `completed`, never grants admin access, and returns URL, expiry, and lifetime seconds.

## Screen Mapping

| Screen | API |
|---|---|
| 01 — Unactivated home | Marketing Status |
| 02 — Marketing solution selection | Marketing Catalog; Marketing Preferences when the business is already onboarded |
| 03 — Registration information | Create Onboarding |
| 04 — Account created | Create Onboarding response and Marketing Status |
| 05 — Onboarding tracking | Onboarding Detail |
| 06 — Activated home | Dashboard with `range=today` |
| 07 — Growth overview | Dashboard with a selected range |
| 08 — Growth analysis | Growth Insights |
| 09 — Marketing campaigns | Campaign List |
| 10 — Service package | Business Package; Marketing Preferences to save package interest |
| 11 — Campaign detail | Campaign Detail and Campaign Approval when applicable |
| 12 — Support Center | Support Ticket List with embedded summary |
| 13 — Create support request | Support Presets and Create Ticket |
| 14 — Ticket conversation | Ticket Detail, Message, Close, and Reopen |
| 15 — CRM access | Create CRM Login Link |

## Onboarding Identity and Duplicate Rules

### Username generation

`usernameFromEmail(string $email)` performs:

1. trim and lowercase;
2. select the local part before `@`;
3. apply `Str::ascii()`;
4. remove everything except `a-z0-9`;
5. truncate to the database column length;
6. use `user` plus the first eight SHA-256 characters of the normalized email when empty.

`availableUsernameFromEmail(string $email)` returns the base username when free. If another email owns it, it appends a deterministic hash suffix while respecting the column length.

### Transaction order

1. Normalize and validate the payload.
2. Lock the integration by partner code and external business ID.
3. If mapped, compare the normalized login email before updating permitted profile fields.
4. If unmapped, check for an existing user by normalized email.
5. Provision account, workspace, and business only after duplicate checks pass.
6. Create integration and active Free package assignment.
7. Create the canonical onboarding request.
8. Create exactly one onboarding support ticket.
9. Commit and queue post-commit events.

### Duplicate outcomes

- First registration: HTTP 201 or 202, all creation flags true, `already_registered=false`.
- Same business and same mapped login email: HTTP 200, reuse all resources, all creation flags false, `already_registered=true`.
- Email belongs to another account: HTTP 409 `email_already_registered`; create nothing.
- Business is mapped to a different login email: HTTP 409 `onboarding_email_mismatch`; create nothing.

No provisional email is created.

## Onboarding Support Ticket

The first registration creates exactly one ticket with:

- `ticket_type=onboarding`
- `source=fizahub`
- `preset_code=fizahub_onboarding`
- valid MLHUB user IDs in `uid` and `open_by`
- the provisioned workspace/team ID

Repeated onboarding returns the same ticket. If the referenced ticket was genuinely deleted, the bridge recreates it, updates the onboarding reference, and records an audit reason.

## Tenant Isolation

All business resources are resolved from `partner_code + external_business_id`. Nested campaign and support ticket IDs must belong to that integration. Cross-business reads and commands return the same typed 404 as an unknown resource.

## Attachment Removal

- Remove `POST /support-tickets/{ticket_id}/attachments` from routing.
- Remove upload UI descriptions, Postman requests, documentation, Core API tables, and happy paths.
- Do not delete `SupportTicketBridge`, `support_tickets`, attachment models, or attachment tables.
- Do not add a destructive migration.
- Ticket messages remain text-only.

## Outbound Webhooks

The Figma file depicts three MLHUB-to-FizaHUB event families. They are outbound integrations and are not counted among the 22 Core API endpoints:

- onboarding status/package synchronization;
- campaign metrics synchronization;
- support events.

Existing outbox delivery remains responsible for signing, retry, and deduplication.

## Testing Strategy

Tests must cover:

- the exact 22-route contract and absence of the attachment route;
- all 15 screen mappings and response fields;
- username normalization, fallback, truncation, and deterministic collision suffix;
- canonical onboarding validation and persistence;
- first registration resource counts;
- repeated registration resource counts and stable ticket ID;
- both 409 duplicate-email cases with transaction rollback;
- timeline serialization and stable public statuses;
- zero-data dashboard and campaigns;
- campaign approval state validation and tenant isolation;
- support presets, search, summary, create, detail/polling, message, close, and reopen;
- onboarding ticket visibility in support list;
- cross-tenant campaign and support protection;
- CRM login readiness, expiry, one-time use, correct user/workspace, and non-admin authorization;
- Postman variable capture, dependency skipping, and happy-path ordering;
- README, docs pages, Postman, and endpoint matrix contract consistency.

## Documentation and Postman

The Postman collection contains exactly 22 requests grouped by System, Onboarding, Growth, Support, and CRM. It automatically stores onboarding request ID, onboarding ticket ID, created support ticket ID, and the first campaign ID. Dependent requests skip safely when their ID or prerequisite state is missing. Every write uses a fresh idempotency key and every request uses a generated request ID.

The README, public documentation page, test guide, endpoint matrix, and Postman collection use identical route names, payloads, fields, statuses, and examples.

## Migration Policy

No destructive migration is permitted. A new additive migration is allowed only if the approved contract cannot be represented safely by the existing schema. Existing attachment storage remains untouched.

## Acceptance Criteria

- Exactly 22 Core API routes are exposed.
- The attachment route is absent.
- All 15 Figma app screens have sufficient API data and actions.
- Onboarding is atomic and duplicate-safe.
- Username generation is deterministic and email-derived.
- Support lifecycle and onboarding ticket visibility remain complete.
- Dashboard and campaign empty states return HTTP 200.
- Postman can execute the full UI path without guessed identifiers.
- The complete APIPartnerFizaHUB test suite, Pint, and FizaHUB Doctor pass.

## Breaking v1 Cutover

This contract is an intentional breaking replacement of the previous API v1 surface. Public aliases for old routes are not retained because the route set must contain exactly 22 Core endpoints.

Route replacements include:

- `integration-status` to `marketing-status`;
- `packages` to `marketing-catalog`;
- separate `insights` and `recommendations` to `growth-insights`;
- `one-time-login` to `crm-login-links`;
- query-scoped support ticket routes to routes nested beneath the external business.

The cutover is deployed as one coordinated release:

1. Deploy the application code exposing the 22 endpoints.
2. Publish the new Postman collection.
3. Publish the matching README, public documentation, help page, and endpoint matrix.
4. Instruct FizaHUB developers to delete the old collection and import the new collection.
5. Execute the staging smoke test and full UI happy path.
6. Cut over production only after staging evidence is accepted.
