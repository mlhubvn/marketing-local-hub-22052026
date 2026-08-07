# FizaHUB Default Data Provisioning Design

**Date:** 2026-08-07

**Status:** Approved in conversation; awaiting written-spec review

## Goal

Give every newly onboarded FizaHUB household business an immediately usable MLHUB account. As soon as FizaHUB submits a valid onboarding request, MLHUB must provision the account, business, first customer, and Vietnamese default content for all requested portal tools before returning a successful response.

The onboarding status workflow remains an administrative review procedure. `awaiting_consultant`, `needs_review`, `in_consultation`, `configuring`, `ready`, and `completed` must not control or delay default-data creation.

## Scope

- Apply only to the authenticated FizaHUB partner onboarding API in `Modules\APIPartnerFizaHUB`.
- Extend first-time onboarding provisioning; do not expose a generic starter-kit feature or a user-facing toggle.
- Create defaults for the provisioned business in Customers, Review Booster, Booking Pages, Coupon Campaigns, Loyalty Cards, Feedback Forms, and Lead Forms.
- Create public landing pages for the review, booking, coupon, feedback, and lead campaigns.
- Publish and activate all generated content so the user can use it immediately after login.
- Preserve the current user, personal workspace, business, integration, package assignment, onboarding request, support ticket, webhook, and admin-status behavior.
- Preserve the existing user-deletion cleanup behavior and rely on existing foreign-key cascades for the new related rows.
- Add no user-selectable configuration and no background queue dependency.

## Architectural Approach

Add a FizaHUB-only provisioning service inside `Modules\APIPartnerFizaHUB\Services`, named `FizaHubDefaultDataProvisioner`. This is an internal part of the privileged FizaHUB onboarding path, not a reusable product starter kit.

`OnboardingService::provisionNewRegistration()` calls the provisioner synchronously after the user, workspace, business, integration, and effective package assignment exist. The call remains inside the existing database transaction and occurs before the API reports success.

`OnboardingService::reuseExistingRegistration()` also calls the provisioner. This makes an idempotent API replay safe and allows an onboarding record created before this change to receive any missing defaults when FizaHUB submits it again.

The provisioner receives the already-provisioned `User`, `Team`, and `LocalBusiness`. It owns only the creation and reconciliation of FizaHUB default data. Existing portal Livewire components remain responsible for interactive user-created content.

Interactive `PlanLimitGuard` checks are not applied to these defaults. The defaults are part of the FizaHUB partner entitlement and are provisioned only after the effective FizaHUB package has been assigned.

## Transaction and Failure Semantics

All default rows are created in the same database transaction as the onboarding account and integration.

- A successful onboarding response means every required default exists and every required public landing page has been synchronized.
- If any required default cannot be created, the entire first-time onboarding transaction rolls back. No partial user, workspace, business, integration, onboarding request, support ticket, or tool set remains.
- A retry starts from a clean state and can create the complete account.
- Webhook dispatch remains post-transaction behavior and must not run before the complete provisioned state commits.

The provisioner must not catch and hide database or factory failures. `OnboardingService` retains its existing typed API exception behavior for known readiness problems and its current handling of unexpected transaction failures.

## Idempotency

The API already uses partner integration identity and request idempotency. Default-data creation adds entity-level protection so replay cannot produce duplicate templates.

- Campaigns store `_system.fizahub_default = "v1"` in `lb_campaigns.settings`; the functional key is user, business, campaign type, and marker.
- The first customer stores `_system.fizahub_default = "v1"` in `lb_customers.metadata`.
- The loyalty card stores `_system.fizahub_default = "v1"` in `lb_loyalty_cards.settings`.
- The booking service has no metadata column, so it is reconciled by user, business, and its fixed default name. An existing matching service is reused.
- `LandingPageFactory::syncFromCampaign()` is reused; it reconciles a page by `campaign_id`.
- Generated campaign and loyalty slugs remain globally unique by using the established slug-plus-counter convention.

The provisioner fills missing generated entities but does not overwrite user edits on existing entities. If a generated campaign exists, its current name, content, status, and design are preserved. A missing landing page is recreated from the existing generated campaign.

## Default Data Contract

### 1. Business

The existing FizaHUB onboarding behavior remains the source of truth. `LocalBusiness` is created from the validated business payload, including name, phone, email, website, address, and resolved industry taxonomy.

### 2. First customer

Create one `Customer` linked to the provisioned user and business:

- `name`: business name;
- `phone`: business phone;
- `email`: business email, falling back to the owner login email only when the business email is absent;
- `note`: `Khách hàng đầu tiên được tạo tự động từ thông tin cơ sở kinh doanh.`;
- `metadata._system.fizahub_default`: `v1`.

### 3. Review Booster

Create one published `QrCampaign` with type `review` and a synchronized public landing page:

- name: `Đánh giá trải nghiệm tại {Tên cơ sở}`;
- Google review URL: `https://www.google.com/maps/search/{Tên+cơ+sở+không+dấu}`;
- Facebook review URL: `https://www.facebook.com/{ten-co-so-khong-dau}`;
- preferred destination: `google`;
- positive threshold: `4`;
- thank-you message: `Cảm ơn bạn đã tin tưởng và sử dụng sản phẩm, dịch vụ của chúng tôi. Đánh giá của bạn giúp chúng tôi phục vụ tốt hơn.`;
- negative-feedback message: `Chúng tôi rất tiếc khi trải nghiệm của bạn chưa như mong đợi. Vui lòng chia sẻ thêm để chúng tôi có thể cải thiện.`;
- page template and design: `PageTemplateCatalog::defaultForType('review')` and its default design.

For the Google URL, normalize the business name with `Str::ascii()`, collapse whitespace, and replace spaces with `+`. For the Facebook URL, use `Str::slug()`.

### 4. Booking Pages

Create one active `BookingService`:

- name: `Tư vấn sản phẩm và dịch vụ`;
- duration: `60` minutes;
- price: `null`;
- description: `Đặt lịch để được tư vấn về sản phẩm, dịch vụ và giải pháp phù hợp với nhu cầu của bạn.`;
- available days: Monday through Saturday;
- time slots: `09:00`, `10:00`, `11:00`, `14:00`, `15:00`, and `16:00`;
- use business hours: enabled;
- slot interval: `30` minutes;
- before/after buffers: `0`;
- active: true.

Create one published `QrCampaign` with type `booking` and a synchronized public landing page:

- name: `Đặt lịch hẹn với {Tên cơ sở}`;
- headline: `Đặt lịch tư vấn cùng {Tên cơ sở}`;
- page template and design: the default booking template and design.

### 5. Coupon Campaigns

Create one published `QrCampaign` with type `coupon` and a synchronized public landing page:

- name: `Ưu đãi chào mừng 10% tại {Tên cơ sở}`;
- discount type: `percentage`;
- discount value: `10`;
- coupon code: `CHAO10`;
- usage limit: no limit;
- expiry date: none;
- terms: `Giảm 10% cho một lần sử dụng sản phẩm hoặc dịch vụ. Không áp dụng đồng thời với chương trình ưu đãi khác.`;
- status: active;
- page template and design: the default coupon template and design.

### 6. Loyalty Cards

Create one active `LoyaltyCard` linked to the provisioned user, workspace, and business:

- name: `Thẻ tích điểm khách hàng thân thiết`;
- required stamps: `10`;
- stamp method: `qr_scan`;
- customer identifier: `phone`;
- reward title: `Ưu đãi 10% cho lần sử dụng tiếp theo`;
- reward type: `discount`;
- reward value: `Giảm 10%`;
- reward expiry: `30` days;
- stamp cooldown: `1440` minutes;
- maximum stamps per day: `1`;
- status: active;
- page template and design: the default loyalty template and design;
- `settings._system.fizahub_default`: `v1`.

### 7. Feedback Forms

Create one published `QrCampaign` with type `feedback` and a synchronized public landing page:

- name: `Đánh giá sản phẩm và dịch vụ tại {Tên cơ sở}`;
- headline: `Bạn đánh giá thế nào về sản phẩm và dịch vụ của chúng tôi?`;
- thank-you message: `Cảm ơn bạn đã dành thời gian đánh giá. Ý kiến của bạn giúp chúng tôi cải thiện chất lượng phục vụ.`;
- rating required: true;
- contact required: false;
- page template and design: the default feedback template and design.

### 8. Lead Forms

Create one published `QrCampaign` with type `lead` and a synchronized public landing page:

- name: `Đăng ký nhận tư vấn từ {Tên cơ sở}`;
- headline: `Để lại thông tin để được tư vấn sản phẩm và dịch vụ phù hợp`;
- page template and design: the default lead template and design.

## Data Flow

1. FizaHUB submits `POST /api/v1/partners/fizahub/onboarding-requests` with valid partner authentication and onboarding data.
2. The existing request class validates and normalizes the payload.
3. `OnboardingService` locks and checks the partner integration and login identity.
4. For a new registration, it creates the user, personal workspace, business, integration, and effective package assignment.
5. `FizaHubDefaultDataProvisioner` creates or reconciles the first customer, five campaigns, five landing pages, one booking service, and one loyalty card.
6. `OnboardingService` creates the onboarding request, history, and support ticket without waiting for an Admin status transition.
7. The transaction commits.
8. The onboarding status webhook is queued with the existing behavior.
9. The FizaHUB user can log in to an account whose portal tools are already populated.

## Admin Status Independence

Admin transitions continue to support consultation, review, package handling, audit history, and partner notifications. They must not create, publish, unlock, or otherwise mutate the default content.

An onboarding request may remain in `awaiting_consultant` or `needs_review` while the provisioned MLHUB data is already usable. The existing CRM login-link readiness rule is outside this change and remains governed by its current API contract.

## Deletion Behavior

No separate cleanup workflow is introduced. Generated rows are owned by the same user/business/team relationships as interactively created rows:

- business deletion cascades campaigns, booking services, loyalty cards, and related child data;
- user deletion cascades user-owned generated rows;
- landing pages follow their existing campaign/business deletion behavior;
- the current FizaHUB Admin user-deletion and partner-mapping purge flow remains the entry point.

Tests must demonstrate that this change does not prevent the existing deletion workflow from completing.

## Testing Strategy

Use feature tests around the real FizaHUB onboarding service and database schema.

Required coverage:

- a new onboarding request creates exactly one user, workspace, business, integration, package assignment, onboarding request, support ticket, first customer, booking service, loyalty card, and one campaign plus landing page for each of review, booking, coupon, feedback, and lead;
- generated records contain the exact Vietnamese copy, 10% coupon, loyalty values, and business-derived URLs defined above;
- generated campaigns and landing pages are published before the API returns;
- the first customer falls back to the owner email when the business email is absent;
- status `awaiting_consultant` and status `needs_review` both receive the complete default data;
- Admin status transitions do not create duplicate defaults or modify generated content;
- an idempotent replay creates no duplicate customer, service, card, campaign, or landing page;
- replay backfills a missing generated entity or landing page without overwriting existing user-edited generated records;
- a forced failure during default provisioning rolls back all first-registration rows;
- existing onboarding identity, duplicate-email, support-ticket, package, webhook, and user-deletion tests continue to pass.

Every production behavior is implemented through a red-green-refactor cycle, with the failing test observed before production code is changed.

## Acceptance Criteria

- A successful first FizaHUB onboarding response guarantees the complete default data set exists.
- The account is populated before the user logs in; no queue worker or Admin action is required.
- Admin onboarding statuses remain procedural and independent of data provisioning.
- Default content is Vietnamese and matches this document.
- Review URLs are derived from the business name in the required Google Maps and Facebook formats.
- Coupon and loyalty defaults both provide a 10% benefit as specified.
- All five growth campaigns have published public landing pages.
- API replay is duplicate-safe and can repair missing generated data.
- Any provisioning failure rolls back the full first-time onboarding transaction.
- Existing deletion, partner mapping, support, package, and webhook behavior remains intact.

## Non-Goals

- No generic starter-kit product feature.
- No self-service toggle for default generation.
- No asynchronous provisioning job.
- No dependency on Admin approval or status changes.
- No redesign of portal creation forms or public landing-page templates.
- No change to FizaHUB authentication, partner-token validation, or onboarding request schema.
