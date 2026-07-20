# FizaHUB Partner API — Endpoint Contract Matrix

Sinh trong quá trình hardening 2026-07-20. Đối chiếu route thực tế (`Routes/api.php`), Postman
collection (`FizaHUB-Partner-API.postman_collection.json`), README, `/api-fizahub`,
`/api-fizahub/help-test` và test hiện có tại `tests/Feature/APIPartnerFizaHUB`.

Envelope chung cho mọi endpoint (xem `Support/PartnerApiResponse.php`):

```json
{ "success": true, "data": {}, "meta": { "request_id": "uuid" }, "error": null }
```

```json
{ "success": false, "data": null, "meta": { "request_id": "uuid" }, "error": { "code": "...", "message": "...", "details": { "next_action": "..." } } }
```

Middleware chung cho tất cả 24 route: `api` → `VerifyPartnerToken` (400 `invalid_partner_header`,
401 `invalid_partner_token`) → `HandlePartnerRequest` (idempotency + logging + exception render) →
`throttle:fizahub-partner` (429 `rate_limit_exceeded`).

## A. Luồng chính (MVP)

### 1. GET /health
- Controller: `HealthController::__invoke`
- Form Request: none · Service: `PartnerReadinessChecker::check()`
- Model/table: kiểm tra sự tồn tại của 10 bảng partner_* + 3 cột mở rộng onboarding, `plans`, `support_tickets`/`support_comments`
- Precondition: none (endpoint công khai theo token partner)
- Success: 200 `status=ok`; Degraded: **503** `data.status=degraded` (vẫn `success:true`, đây là readiness probe, không phải lỗi partner)
- Response fields: `status`, `checks[]`, `api_version`, `app_version`
- Idempotency: N/A (GET) · Tenant isolation: N/A
- Test: `ModuleRoutingTest`, `ReadinessCheckTest` (đầy đủ 4 trạng thái schema)
- Gap: không có `build_commit`/`deployed_at` trong response — mục VIII.A yêu cầu bổ sung (theo dõi ở Block 6/backlog, không có build info pipeline sẵn để lấy commit hash an toàn).

### 2. POST /onboarding-requests
- Controller: `OnboardingController::store` · Form Request: `UpsertOnboardingRequest` · Service: `OnboardingService::upsert`
- Model/table: `partner_onboarding_requests`, `partner_integrations`, `users`, `teams`, `lb_businesses`, `partner_onboarding_status_histories`, `partner_package_assignments`, `support_tickets`
- Precondition: `Idempotency-Key` header bắt buộc (422 nếu thiếu); plan `mlhub-free-da-nang` phải active (503 `default_plan_not_found` nếu thiếu)
- Success: **201** (created, awaiting_consultant) / **202** (needs_review hoặc legacy pending_verification) / 200 (upsert lại record đã tồn tại và đã map)
- Response fields: `request_id`, `status`, `status_label`, `current_step`, `requested_package_code`, `approved_package_code`, `package_code`, `duplicate_check[]`, `support_ticket_id`, `mlhub_user_id/workspace_id/business_id`
- Error codes: 422 `validation_failed`; 503 `default_plan_not_found`; 409 `integration_broken` (mapping có FK đã bị null hoá do xoá user/business — nay tự khôi phục thay vì lỗi); 409 `integration_mapping_invalid` (chưa có `mlhub_user_id`/team hợp lệ ngay lúc tạo support ticket onboarding — `next_action: retry_onboarding`); (đã fix trong Block 1) không còn 500 do username trùng
- Idempotency: unique theo `(partner_code, method, endpoint, idempotency_key)`; onboarding còn dùng `X-Request-Id` làm `request_id` idempotent riêng (lockForUpdate theo request_id)
- Tenant isolation: N/A (endpoint tạo mapping)
- Test: `OnboardingApiTest` (15 case), `RootCauseInvestigationTest` (7 case, gồm 3 root-cause 500 đã fix + 1 test rollback), `IntegrationBrokenMappingTest` (2 case, self-heal + 409 typed cho package lookup), `SupportTicketApiTest` (ticket onboarding luôn có uid/open_by hợp lệ)
- **ROOT CAUSE mới đã fix (2026-07-20, sau khi FizaHUB báo 500 ở bước 2 khi test lại với cùng `external_business_id` demo mặc định)**: `partner_integrations.mlhub_user_id/mlhub_workspace_id/mlhub_business_id` đều `nullOnDelete()`. Nếu admin xoá tay `users`/`lb_businesses` demo mà không xoá luôn mapping, dòng `partner_integrations` vẫn còn với FK null, chiếm unique index `(partner_code, external_business_id)`. Lần onboard lại tiếp theo rơi vào `provision()` (vì FK null nên không vào `updateExistingMapping()`), và `PartnerIntegration::create()` cũ sẽ vi phạm unique constraint → 500 không rõ nguyên nhân. Đã đổi sang `updateOrCreate()` để tự khôi phục (heal) dòng cũ. Xem `IntegrationBrokenMappingTest.php`.
- **ROOT CAUSE mới đã fix (2026-07-20, production log `SQLSTATE[23000]`/MySQL 1452 trên `support_tickets`)**: `support_tickets.uid`/`open_by` có FK thật tới `users.id`. `SupportTicketBridge::createOnboardingReviewTicket()` từng insert `uid=0, open_by=0` (ticket "chưa gán user") rồi mới `UPDATE` lại uid/open_by thật ở `OnboardingService::ensureOnboardingTicket()` — nhưng INSERT đầu tiên đã vi phạm FK nên `DB::transaction()` trong `upsert()` rollback toàn bộ (user/team/business/integration/onboarding đều mất), trả về 500 `partner_api_error` trần. Đã sửa: `createOnboardingReviewTicket()` nhận thêm `PartnerIntegration $integration`, validate mapping (`mlhub_user_id` tồn tại trong `users`, `mlhub_workspace_id` tồn tại trong `teams` nếu có) trước khi insert, dùng uid/open_by/team_id **thật ngay từ đầu** (không còn `uid=0`), và nếu mapping hỏng thì trả 409 `integration_mapping_invalid` (`next_action: retry_onboarding`) thay vì để `QueryException` rơi thành 500. Xem `RootCauseInvestigationTest::onboarding creates review ticket using the provisioned user under production foreign keys` (test FK thật, RED trước fix) và test rollback kế bên (không để lại record dở dang, idempotency log không kẹt ở "in progress").

### 3. GET /onboarding-requests/{request_id}
- Controller: `OnboardingController::show` · Service: `OnboardingService::find`
- Precondition: request_id phải tồn tại
- Success: 200 · Error: 404 `onboarding_request_not_found`
- Test: `OnboardingApiTest` (bao gồm `request_id` rỗng/literal placeholder/malformed — đóng ở Block 2)

### 4. GET /businesses/{external_business_id}/integration-status
- Controller: `BusinessProfileController::status` · Service: `IntegrationProfileService::status`
- Model/table: `partner_integrations`
- Success: 200 · Error: 404 `integration_not_found`
- Test: `IntegrationStatusTest` (unmapped 404, mapped + onboarding status, tenant isolation — đóng ở Block 2)

### 5. GET /businesses/{external_business_id}/package
- Controller: `PackageController::show` · Service: `PackageService::forBusiness`
- Success: 200 (whitelist limits, không lộ giá/credit/permissions) · Error: 404 `integration_not_found`; 409 `integration_broken` nếu mapping trỏ tới `mlhub_user_id` không còn tồn tại (không tin tưởng mù quáng vào FK id — phòng hờ dữ liệu bị sửa tay sai)
- Test: `PackageApiTest` (đầy đủ), `IntegrationBrokenMappingTest` (case 409 `integration_broken`)

### 6. GET /businesses/{external_business_id}/dashboard
- Controller: `DashboardController::show` · Form Request: `DashboardRequest` · Service: `DashboardService::summarize`
- Precondition: `from`/`to` tùy chọn, mặc định 30 ngày, tối đa 366 ngày, timezone Asia/Ho_Chi_Minh
- Success: 200 (zero-data vẫn 200) · Error: 422 date range, 404 integration
- Test: `DashboardApiTest` (4 case, có tenant isolation)

### 7. PATCH /businesses/{external_business_id}/profile
- Controller: `BusinessProfileController::update` · Form Request: `UpdateBusinessProfileRequest` · Service: `IntegrationProfileService::updateProfile`
- Precondition: hỗ trợ cả nested `{owner,business}` (canonical) và flat body cũ (deprecated, có `deprecation_notice`)
- Success: 200 · Error: 404 integration, 422 validation
- Test: `BusinessProfileApiTest` (nested + legacy + 404)

### 8. POST /businesses/{external_business_id}/support-tickets
- Controller: `SupportTicketController::store` · Form Request: `CreateSupportTicketRequest` · Service: `SupportTicketBridge::createForBusiness`
- Precondition: Idempotency-Key bắt buộc; integration phải tồn tại
- Success: 201 · Error: 404 integration, 422
- Test: `SupportTicketApiTest`

### 9. GET /support-tickets/{ticket_id}?external_business_id=...
- Controller: `SupportTicketController::show` · Service: `SupportTicketBridge::detail`
- Precondition: `external_business_id` query bắt buộc để scope tenant
- Success: 200 (hỗ trợ `since` polling) · Error: 404 `ticket_not_found` (bao gồm cross-tenant), 422 thiếu query
- Test: `SupportTicketApiTest` (bao gồm tenant isolation)

### 10. POST /support-tickets/{ticket_id}/attachments?external_business_id=...
- Controller: `SupportAttachmentController::store` · Service: `SupportTicketBridge::storeAttachment`
- Precondition: multipart `file` bắt buộc, MIME whitelist, giới hạn dung lượng (`support_max_attachment_size_mb`), ticket phải `open`
- Success: 201 · Error: 404, 409 `ticket_not_open`, 422 file/MIME/size, 503 storage
- Test: `SupportLifecycleAttachmentTest` (upload thành công, MIME sai, quá size, ticket đã đóng, thiếu file, tenant isolation, bảng thiếu → 503 — đóng ở Block 4)

## B. Extended Beta

### 11. POST /partner/sso/verify
- Controller: `SsoController::verify` · Service: `PartnerMappingService::partnerCode`
- Không validate body; auth hoàn toàn qua middleware
- Success: 200 `{partner, authenticated, server_time}` — không lộ secret
- Test: `SsoVerifyTest` (auth thành công, token sai → 401, không log secret — đóng ở Block 5)

### 12. GET /packages
- Controller: `PackageCatalogController::index` · Service: `PackageService::list`
- Success: 200 (free/base → mlhub-free-da-nang, có cờ default/free)
- Test: `PackageApiTest`

### 13. POST /onboarding-requests/{request_id}/confirm
- Controller: `OnboardingController::confirm` · Service: `OnboardingService::confirm`
- Success: 200 (set `partner_confirmed_at`, không đổi `status`) · Error: 404
- Test: `OnboardingApiTest`

### 14. POST /onboarding-requests/{request_id}/cancel
- Controller: `OnboardingController::cancel` · Service: `OnboardingService::cancel` → `OnboardingAdminService::transition`
- Success: 200 · Error: 404, 422 `invalid_status_transition` (nếu state hiện tại không cho cancel)
- Test: `OnboardingApiTest` (bao gồm cancel lặp lại là idempotent + cancel/confirm 404 cho request_id không tồn tại — đóng ở Block 2)

### 15. POST /businesses/{external_business_id}/one-time-login
- Controller: `OneTimeLoginController::store` · Service: `OneTimeLoginService::issue`
- Precondition: onboarding phải `ready`/`completed` (hoặc không có onboarding row = legacy allowed)
- Success: 201 (token plaintext trả 1 lần, TTL mặc định 5 phút) · Error: 404, 409 `onboarding_not_ready`
- Test: `OneTimeLoginApiTest` (đầy đủ, gồm consume/lock/reuse)

### 16. GET /businesses/{external_business_id}/insights
### 17. GET /businesses/{external_business_id}/recommendations
### 18. GET /businesses/{external_business_id}/campaigns
### 19. GET /businesses/{external_business_id}/campaigns/{campaign_id}
- Controller: `DashboardController::insights/recommendations/campaigns/campaignShow` · Form Request: `DashboardRequest` cho date range
- Success: 200 (list rỗng khi không có, zero-data insights/recommendations) · Error: 404 (`campaign_not_found` nếu id không thuộc business), 422 date range
- Test: `InsightsRecommendationsCampaignsTest` (zero-data, 404 unmapped, date range xấu, tenant isolation cho list + detail, campaign_id không numeric/rỗng → 404 typed — đóng ở Block 3)

### 20. GET /businesses/{external_business_id}/support-summary
- Controller: `SupportTicketController::summary`
- Success: 200 · Error: 404
- Test: `SupportLifecycleAttachmentTest` (đếm + metadata form, 404 unmapped — đóng ở Block 4)

### 21. GET /businesses/{external_business_id}/support-tickets
- Controller: `SupportTicketController::index`
- Success: 200 (scoped, tenant isolation) · Error: 404
- Test: `SupportTicketApiTest` (list scoped)

### 22. POST /support-tickets/{ticket_id}/messages?external_business_id=...
- Controller: `SupportMessageController::store` · Form Request: `CreateSupportMessageRequest` · Service: `SupportTicketBridge::addMessage`
- Precondition: Idempotency-Key bắt buộc; ticket phải `open`; message 1–5000 ký tự, HTML bị strip
- Success: 201 · Error: 404, 409 `ticket_not_open`, 422
- Test: `SupportTicketApiTest` (message strip HTML + closed ticket)

### 23. PATCH /support-tickets/{ticket_id}/close?external_business_id=...
- Controller: `SupportTicketController::close`
- Success: 200 · Error: 404, 409 `ticket_already_closed` (đã closed là conflict, không idempotent-200 — xác nhận đúng theo state machine)
- Test: `SupportLifecycleAttachmentTest` (close thành công, 409 double-close, 404 unknown/cross-tenant — đóng ở Block 4)

### 24. POST /support-tickets/{ticket_id}/reopen?external_business_id=...
- Controller: `SupportTicketController::reopen`
- Success: 200 · Error: 404, 409 `ticket_not_closed` (reopen chỉ hợp lệ từ `closed`)
- Test: `SupportLifecycleAttachmentTest` (reopen thành công, 409 khi đã open, 404 unknown/cross-tenant — đóng ở Block 4)

## Web route liên quan (không thuộc 24 endpoint API, vẫn phải ổn định)

### GET /partners/fizahub/one-time-login/{token}
- Middleware: `web`, `signed` · Controller: `ConsumeOneTimeLoginController` · Service: `OneTimeLoginService::consume`
- Success: redirect `portal.dashboard` sau khi `Auth::login` + session regenerate
- Error: **403** HttpException (dùng error page web thông thường, không phải partner JSON envelope — đúng vì đây là web route cho end-user, không phải server-to-server)
- Test: `OneTimeLoginApiTest::consume...`

## Tổng hợp khoảng trống test (đã đóng hết qua Block 2-6)
- Block 2 (đóng): integration-status (`IntegrationStatusTest`), onboarding cancel-lặp-lại + cancel/confirm 404 + request_id rỗng/literal/malformed (`OnboardingApiTest`).
- Block 3 (đóng): insights, recommendations, campaigns list + detail + cross-tenant 404 (`InsightsRecommendationsCampaignsTest`).
- Block 4 (đóng): support-summary, close, reopen, attachments MIME/size/tenant (`SupportLifecycleAttachmentTest`).
- Block 5 (đóng): SSO verify hành vi riêng + redaction (`SsoVerifyTest`); one-time-login edge cases đã có sẵn đủ ở `OneTimeLoginApiTest`.
- Block 6 (đóng): `FullPartnerHappyPathTest` chạy toàn chuỗi 24 endpoint + web one-time-login trên schema thật (migration thật + host schema production-shaped), không có bước nào trả 500.

## Contract drift đã phát hiện — ĐÃ ĐỒNG BỘ ở Block 6
1. README dòng ~82: đã sửa từ "2 Postman collection" → "1 Postman collection thống nhất, chia 2 folder MVP/Extended".
2. `provisional_email_domain`: README đã sửa từ `provisional.fizahub.mlhub.local` → `provisional.mlhub.vn` khớp code default.
3. Idempotency-Key: README đã bổ sung ghi chú rằng Postman còn gửi kèm header này ở confirm/cancel/one-time-login/reopen/attachment ngoài 3 endpoint bắt buộc, tránh hiểu nhầm.
4. `partner_token`: theo yêu cầu chủ dự án (giai đoạn đang cấp API cho FizaHUB test), đã đồng bộ **cùng một giá trị thật** giữa `.env.example` (`FIZAHUB_PARTNER_TOKEN`) và biến `partner_token` trong Postman collection để đối tác chạy được ngay không cần tự thay token. Đây là đánh đổi bảo mật tạm thời đã được chủ dự án chấp nhận (xem `.cursorrules` §8) — **phải đổi token khác** (và cập nhật lại cả 2 nơi) trước khi chạy chính thức hoặc khi ngừng hợp tác với FizaHUB.
5. `from`/`to` mặc định: đã sửa pre-request script Postman để tính theo UTC+7 (Asia/Ho_Chi_Minh) khớp `DashboardRequest::resolvedRange()`, không còn lệch biên ngày.
6. `PartnerPersistenceTest` vẫn chỉ chạy migration đầu — nhưng `RootCauseInvestigationTest`/`FullPartnerHappyPathTest`/`SupportLifecycleAttachmentTest` đều đã chạy migration mở rộng thật qua `FizaHubTestHelpers::runRealFizaHubMigrations()`/`bootProductionLikeSchema()`, nên regress cột/bảng mở rộng vẫn được bắt.
