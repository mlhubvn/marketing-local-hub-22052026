# FizaHUB × MLHUB — Public API v1 Endpoint Matrix

Contract này là breaking cutover đã được Product duyệt ngày 2026-07-20, mở rộng ngày 2026-07-31
để bổ sung đính kèm tệp cho Support. Public route set dưới `/api/v1/partners/fizahub` có chính
xác 25 request; không có compatibility alias.

## Response envelope

Mọi response thành công:

```json
{
  "success": true,
  "data": {},
  "meta": { "request_id": "uuid" },
  "error": null
}
```

Mọi response lỗi:

```json
{
  "success": false,
  "data": null,
  "meta": { "request_id": "uuid" },
  "error": {
    "code": "machine_readable_code",
    "message": "Thông báo tiếng Việt.",
    "details": {}
  }
}
```

Tất cả request cần `Authorization`, `X-Partner: fizahub`, `X-Request-Id`. Mọi request thay đổi
trạng thái cần `Idempotency-Key`, ngoại trừ `POST partner/sso/verify` là thao tác xác minh chỉ đọc.

## 25 public requests

| # | Method | Path | Route name | Handler | Mô tả ngắn |
|---:|:---:|---|---|---|---|
| 1 | GET | `/api/v1/partners/fizahub/health` | `partner.fizahub.health` | `HealthController` | Kiểm tra API, schema, plan và dependency sẵn sàng. |
| 2 | POST | `/api/v1/partners/fizahub/partner/sso/verify` | `partner.fizahub.sso.verify` | `SsoController::verify` | Xác minh partner token; không thay đổi dữ liệu. |
| 3 | GET | `/api/v1/partners/fizahub/businesses/{external_business_id}/marketing-status` | `partner.fizahub.businesses.marketing-status` | `BusinessProfileController::status` | Trạng thái kích hoạt, onboarding, package và capability của business. |
| 4 | GET | `/api/v1/partners/fizahub/marketing-catalog` | `partner.fizahub.marketing-catalog` | `PackageCatalogController::index` | Catalog mục tiêu marketing, ngành nghề và gói dịch vụ. |
| 5 | POST | `/api/v1/partners/fizahub/onboarding-requests` | `partner.fizahub.onboarding.store` | `OnboardingController::store` | Atomic onboarding, provision tài khoản/business và ticket tiếp nhận. |
| 6 | GET | `/api/v1/partners/fizahub/onboarding-requests/{request_id}` | `partner.fizahub.onboarding.show` | `OnboardingController::show` | Chi tiết trạng thái và timeline onboarding. |
| 7 | PATCH | `/api/v1/partners/fizahub/businesses/{external_business_id}/profile` | `partner.fizahub.businesses.profile.update` | `BusinessProfileController::update` | Cập nhật owner name và business profile được phép. |
| 8 | PATCH | `/api/v1/partners/fizahub/businesses/{external_business_id}/marketing-preferences` | `partner.fizahub.businesses.marketing-preferences.update` | `BusinessProfileController::updatePreferences` | Lưu tối đa ba mục tiêu và gói quan tâm, không tự kích hoạt gói. |
| 9 | GET | `/api/v1/partners/fizahub/businesses/{external_business_id}/dashboard` | `partner.fizahub.businesses.dashboard` | `DashboardController::show` | KPI, xu hướng và độ mới dữ liệu; zero-data vẫn HTTP 200. |
| 10 | GET | `/api/v1/partners/fizahub/businesses/{external_business_id}/growth-insights` | `partner.fizahub.businesses.growth-insights` | `DashboardController::growthInsights` | Growth score, nguồn khách, highlights và suggested actions. |
| 11 | GET | `/api/v1/partners/fizahub/businesses/{external_business_id}/campaigns` | `partner.fizahub.businesses.campaigns.index` | `DashboardController::campaigns` | Danh sách chiến dịch có filter, cursor và zero-data. |
| 12 | GET | `/api/v1/partners/fizahub/businesses/{external_business_id}/campaigns/{campaign_id}` | `partner.fizahub.businesses.campaigns.show` | `DashboardController::campaignShow` | Chi tiết, metrics, recommendations và trạng thái duyệt chiến dịch. |
| 13 | POST | `/api/v1/partners/fizahub/businesses/{external_business_id}/campaigns/{campaign_id}/approval` | `partner.fizahub.businesses.campaigns.approval` | `DashboardController::approveCampaign` | Ghi một quyết định duyệt/yêu cầu chỉnh sửa có audit. |
| 14 | GET | `/api/v1/partners/fizahub/businesses/{external_business_id}/package` | `partner.fizahub.businesses.package` | `PackageController::show` | Gói effective/requested/approved, limits và thời hạn. |
| 15 | GET | `/api/v1/partners/fizahub/businesses/{external_business_id}/support-presets` | `partner.fizahub.businesses.support-presets` | `SupportTicketController::presets` | Preset hỗ trợ theo SOP, package và campaign context. |
| 16 | GET | `/api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets` | `partner.fizahub.businesses.support-tickets.index` | `SupportTicketController::index` | Summary và danh sách ticket tenant-scoped có cursor/search/filter. |
| 17 | POST | `/api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets` | `partner.fizahub.businesses.support-tickets.store` | `SupportTicketController::store` | Tạo ticket text-only từ preset hoặc nội dung tự nhập. |
| 18 | GET | `/api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}` | `partner.fizahub.businesses.support-tickets.show` | `SupportTicketController::show` | Chi tiết ticket và hội thoại text. |
| 19 | POST | `/api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}/messages` | `partner.fizahub.businesses.support-tickets.messages.store` | `SupportMessageController::store` | Gửi một tin nhắn text idempotent. |
| 20 | POST | `/api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}/close` | `partner.fizahub.businesses.support-tickets.close` | `SupportTicketController::close` | Đóng ticket an toàn và tenant-scoped. |
| 21 | POST | `/api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}/reopen` | `partner.fizahub.businesses.support-tickets.reopen` | `SupportTicketController::reopen` | Mở lại ticket đã đóng an toàn và tenant-scoped. |
| 22 | GET | `/api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}/attachments` | `partner.fizahub.businesses.support-tickets.attachments.index` | `SupportAttachmentController::index` | Danh sách tệp đính kèm của ticket (business + admin). |
| 23 | POST | `/api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}/attachments` | `partner.fizahub.businesses.support-tickets.attachments.store` | `SupportAttachmentController::store` | Tải lên 1 tệp đính kèm (ảnh/video/zip/văn bản) cho ticket. |
| 24 | GET | `/api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}/attachments/{attachment_id}` | `partner.fizahub.businesses.support-tickets.attachments.show` | `SupportAttachmentController::show` | Tải xuống một tệp đính kèm đã lưu, tenant-scoped. |
| 25 | POST | `/api/v1/partners/fizahub/businesses/{external_business_id}/crm-login-links` | `partner.fizahub.businesses.crm-login-links.store` | `OneTimeLoginController::store` | Tạo link CRM dùng một lần cho onboarding ready/completed. |

## Mapping 15 màn hình UI

| Screen | API sử dụng |
|---|---|
| 01 — Trang chủ chưa kích hoạt | `GET marketing-status` |
| 02 — Chọn giải pháp Marketing | `GET marketing-catalog`; `PATCH marketing-preferences` nếu đã onboarding |
| 03 — Thông tin đăng ký | `POST onboarding-requests` |
| 04 — Tạo tài khoản thành công | Response onboarding; `GET marketing-status` |
| 05 — Theo dõi Onboarding | `GET onboarding-requests/{request_id}` |
| 06 — Trang chủ đã kích hoạt | `GET dashboard` |
| 07 — Tổng quan tăng trưởng | `GET dashboard?range=7d|30d|90d|custom` |
| 08 — Phân tích tăng trưởng | `GET growth-insights` |
| 09 — Danh sách chiến dịch | `GET campaigns` |
| 10 — Quản lý gói | `GET package`; `PATCH marketing-preferences` để lưu gói quan tâm |
| 11 — Chi tiết chiến dịch | `GET campaigns/{campaign_id}`; `POST approval` |
| 12 — Trung tâm hỗ trợ | `GET support-presets`; `GET support-tickets` |
| 13 — Tạo yêu cầu hỗ trợ | `GET support-presets`; `POST support-tickets` |
| 14 — Chi tiết và hội thoại | `GET detail`; `POST messages`; `POST close`; `POST reopen`; `GET/POST attachments`; `GET attachments/{attachment_id}` |
| 15 — Truy cập CRM MLHUB | `POST crm-login-links` |

`POST partner/sso/verify` là endpoint hệ thống, không gắn với màn hình người dùng.

## Enum và collection contract

- `activation_status`: `inactive`, `onboarding`, `active`, `suspended`.
- `onboarding_status`: `awaiting_consultant`, `needs_review`, `in_consultation`, `configuring`, `ready`, `completed`, `cancelled`.
- Timeline step status: `completed`, `current`, `pending`, `blocked`.
- Cursor pagination: `items[]` và `pagination {next_cursor, has_more, per_page}`.
- Date range mặc định `30d`; `custom` cần `from`, `to`, `from <= to`, tối đa 366 ngày.

## Breaking cutover

Các public alias v1 cũ đã bị loại bỏ: `integration-status`, `packages`, `insights`,
`recommendations`, `one-time-login`, onboarding `confirm/cancel`, `support-summary` riêng và
support ticket scope bằng query string.

Web route consume signed login link không thuộc 25 public API request và vẫn được giữ để hoàn tất
one-time login.

## Đính kèm tệp cho Support (bổ sung 2026-07-31)

Support không còn text-only: cho phép đính kèm ảnh, video, file nén (zip) và văn bản đời thường
qua 3 request `GET/POST attachments` và `GET attachments/{attachment_id}` (`SupportAttachmentController`).

- **Upload:** `multipart/form-data`, field `file`. MIME được server tự dò (không tin theo
  `Content-Type` client gửi) và đối chiếu song song với đuôi file — cả hai phải khớp danh sách
  cho phép, chặn kiểu đổi tên file nguy hiểm thành đuôi vô hại.
- **Loại được hỗ trợ:** ảnh (`jpg/jpeg/png/webp/gif`), video (`mp4/mov/webm/avi`), nén (`zip`),
  văn bản (`pdf/txt/csv/doc/docx/xls/xlsx/ppt/pptx`).
- **Giới hạn dung lượng:** mặc định 25MB cho ảnh/zip/văn bản, 100MB riêng cho video (cấu hình qua
  `FIZAHUB_SUPPORT_MAX_ATTACHMENT_SIZE_MB` / `FIZAHUB_SUPPORT_MAX_VIDEO_ATTACHMENT_SIZE_MB`).
- **Lưu trữ:** disk `local` (không public), theo thư mục riêng từng ticket
  (`partner-fizahub/support/{ticket_id}/...`); tải xuống luôn qua endpoint `attachments/{attachment_id}`
  có xác thực partner token + tenant scope, không có URL public đoán được.
- **Preview ảnh:** list/upload trả thêm `image_url` khi `mime_type` là `image/*` (cùng URL với
  `download_url`, vẫn cần Bearer); GET ảnh trả `Content-Disposition: inline`, tệp khác `attachment`.
- **Hai chiều:** tệp do FizaHUB tải lên và tệp admin MLHUB đính kèm khi trả lời đều nằm trong cùng
  danh sách `GET attachments`, phân biệt bằng `sender_type` (`business` | `admin`).
- **Vòng đời:** khi xóa user, ticket support và toàn bộ tệp đính kèm liên quan (kể cả file vật lý
  trên disk) đều bị xóa theo (xem `DeleteUser`), tránh rác file mồ côi.
- **Lỗi thường gặp:** `422 attachment_type_not_allowed`, `422 attachment_too_large`,
  `404 attachment_not_found`, `409 ticket_not_open`, `503 support_attachments_unavailable`.
