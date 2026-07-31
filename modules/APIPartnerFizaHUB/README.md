# APIPartnerFizaHUB

API tích hợp **FizaHUB × MLHUB** cho **15 màn hình** Marketing đã được Product duyệt. Đây là **breaking cutover API v1** với đúng **25 endpoint** dưới prefix `/api/v1/partners/fizahub`. Support hỗ trợ cả tin nhắn text và **đính kèm tệp** (ảnh/video/zip/văn bản).

## Header và response envelope

Mọi request gửi `Authorization: Bearer {partner_token}`, `X-Partner: fizahub`, `X-Request-Id: UUID`, `Accept: application/json`. Mọi write request gửi `Idempotency-Key`; riêng `POST partner/sso/verify` là xác minh chỉ đọc nên được miễn.

```json
{
  "success": true,
  "data": {},
  "meta": { "request_id": "uuid" },
  "error": null
}
```

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

Idempotency semantics:

- Cùng key và cùng payload: trả cùng kết quả, không tạo dữ liệu lần hai.
- Cùng key nhưng payload khác: HTTP 409 `idempotency_conflict`.
- Send Message và Campaign Approval không ghi trùng.
- Close/Reopen lặp trả `200` với trạng thái hiện tại (idempotent), không 409/500.
- CRM cùng key trả cùng link khi còn hiệu lực/chưa dùng; nếu đã dùng hoặc hết hạn trả `crm_login_link_not_reusable`, `next_action=new_idempotency_key`.



## 25 endpoint chính thức


| #   | Method | Path                                                                                         | Mô tả                                          |
| --- | ------ | -------------------------------------------------------------------------------------------- | ---------------------------------------------- |
| 1   | GET    | `/health`                                                                                    | Readiness API, schema, plan và dependency.     |
| 2   | POST   | `/partner/sso/verify`                                                                        | Xác minh partner token.                        |
| 3   | GET    | `/marketing-catalog`                                                                         | Mục tiêu Marketing, ngành nghề và package.     |
| 4   | POST   | `/onboarding-requests`                                                                       | Atomic onboarding và provision Free workspace. |
| 5   | GET    | `/onboarding-requests/{request_id}`                                                          | Trạng thái và timeline onboarding.             |
| 6   | GET    | `/businesses/{external_business_id}/marketing-status`                                        | Activation, capability và navigation links.    |
| 7   | PATCH  | `/businesses/{external_business_id}/profile`                                                 | Cập nhật profile được phép.                    |
| 8   | PATCH  | `/businesses/{external_business_id}/marketing-preferences`                                   | Lưu goals và gói quan tâm.                     |
| 9   | GET    | `/businesses/{external_business_id}/dashboard`                                               | KPI/trend; zero-data vẫn 200.                  |
| 10  | GET    | `/businesses/{external_business_id}/growth-insights`                                         | Score, sources, highlights, recommendations.   |
| 11  | GET    | `/businesses/{external_business_id}/campaigns`                                               | List/filter/search/cursor.                     |
| 12  | GET    | `/businesses/{external_business_id}/campaigns/{campaign_id}`                                 | Campaign detail.                               |
| 13  | POST   | `/businesses/{external_business_id}/campaigns/{campaign_id}/approval`                        | Duyệt hoặc yêu cầu chỉnh sửa.                  |
| 14  | GET    | `/businesses/{external_business_id}/package`                                                 | Effective/requested/approved package.          |
| 15  | GET    | `/businesses/{external_business_id}/support-presets`                                         | Preset SOP.                                    |
| 16  | POST   | `/businesses/{external_business_id}/support-tickets`                                         | Tạo ticket text.                               |
| 17  | GET    | `/businesses/{external_business_id}/support-tickets`                                         | Summary và list cursor.                        |
| 18  | GET    | `/businesses/{external_business_id}/support-tickets/{ticket_id}`                             | Detail và hội thoại.                           |
| 19  | POST   | `/businesses/{external_business_id}/support-tickets/{ticket_id}/messages`                    | Gửi message.                                   |
| 20  | POST   | `/businesses/{external_business_id}/support-tickets/{ticket_id}/close`                       | Đóng ticket.                                   |
| 21  | POST   | `/businesses/{external_business_id}/support-tickets/{ticket_id}/reopen`                      | Mở lại ticket.                                 |
| 22  | GET    | `/businesses/{external_business_id}/support-tickets/{ticket_id}/attachments`                 | Danh sách đính kèm (business + admin).         |
| 23  | POST   | `/businesses/{external_business_id}/support-tickets/{ticket_id}/attachments`                 | Tải lên 1 tệp đính kèm.                        |
| 24  | GET    | `/businesses/{external_business_id}/support-tickets/{ticket_id}/attachments/{attachment_id}` | Tải xuống một tệp đính kèm.                    |
| 25  | POST   | `/businesses/{external_business_id}/crm-login-links`                                         | Link CRM dùng một lần.                         |




## Mapping 15 màn hình UI


| Màn hình                      | Endpoint                                                                               |
| ----------------------------- | -------------------------------------------------------------------------------------- |
| 01 — Trang chủ chưa kích hoạt | `GET marketing-status`                                                                 |
| 02 — Chọn giải pháp Marketing | `GET marketing-catalog`; sau onboarding dùng `PATCH marketing-preferences`             |
| 03 — Thông tin đăng ký        | `POST onboarding-requests`                                                             |
| 04 — Tạo tài khoản thành công | Response onboarding và `GET marketing-status`                                          |
| 05 — Theo dõi Onboarding      | `GET onboarding-requests/{request_id}`                                                 |
| 06 — Trang chủ đã kích hoạt   | `GET dashboard`                                                                        |
| 07 — Tổng quan tăng trưởng    | `GET dashboard?range=7d                                                                |
| 08 — Phân tích tăng trưởng    | `GET growth-insights`                                                                  |
| 09 — Danh sách chiến dịch     | `GET campaigns`                                                                        |
| 10 — Quản lý gói              | `GET package`; `PATCH marketing-preferences` để lưu gói quan tâm                       |
| 11 — Chi tiết chiến dịch      | `GET campaigns/{campaign_id}`; `POST approval`                                         |
| 12 — Trung tâm hỗ trợ         | `GET support-presets`; `GET support-tickets`                                           |
| 13 — Tạo yêu cầu hỗ trợ       | `POST support-tickets`                                                                 |
| 14 — Chi tiết và hội thoại    | `GET detail`; `POST messages`; `POST close`; `POST reopen`; `GET/POST/GET attachments` |
| 15 — Truy cập CRM MLHUB       | `POST crm-login-links` khi ready/completed                                             |


`partner/sso/verify` là endpoint hệ thống, không gắn với màn hình người dùng.

## Onboarding contract

Payload canonical không yêu cầu `owner.phone`, verification, tax code hoặc business license number:

```json
{
  "external_business_id": "fiza-business-001",
  "external_user_id": "fiza-user-001",
  "package_code": "base",
  "owner": {"name": "Đoàn Văn Khoa", "email": "van-khoa.lqd123@gmail.com"},
  "business": {
    "name": "Fiza Store",
    "industry": "restaurant_food",
    "phone": "0901234888",
    "email": "contact@fizastore.vn",
    "website": "https://fizastore.vn",
    "address": "888 Lê Duẩn, Đà Nẵng"
  }
}
```

HTTP outcomes:

- `201`: tạo mới, `awaiting_consultant`.
- `202`: tạo mới nhưng `needs_review`; đây là trạng thái blocked tại bước tiếp nhận, không phải timeline step thứ sáu.
- `200`: business/email đã mapping, `already_registered=true`, trả request/ticket cũ.
- `409 email_already_registered`: email thuộc tài khoản MLHUB khác.
- `409 onboarding_email_mismatch`: business đã mapping bằng email khác.
- `422 validation_failed`: payload sai.
- `503`: schema, plan hoặc dependency chưa sẵn sàng; transaction không để lại record dở dang.

Username sinh từ local-part email: lowercase, `Str::ascii()`, bỏ ký tự ngoài `[a-z0-9]`, giới hạn theo cột; collision dùng suffix hash ổn định. Lần đầu tạo đúng một user/team/business/integration/package assignment/request/onboarding ticket. Lần hai không tăng count và trả `support_ticket_id` cũ.

## Enum, timeline, pagination và ngày

- `activation_status`: `inactive`, `onboarding`, `active`, `suspended`.
- `onboarding_status`: `awaiting_consultant`, `needs_review`, `in_consultation`, `configuring`, `ready`, `completed`, `cancelled`.
- Timeline step status: `completed`, `current`, `pending`, `blocked`.
- Cursor: `items[]`, `pagination {next_cursor, has_more, per_page}`.
- Date range mặc định `30d`; hỗ trợ `today`, `7d`, `30d`, `90d`, `custom`.
- Custom cần `from`, `to`, `from <= to`, tối đa **366** ngày; sai trả `422 validation_failed`.



## Support lifecycle

Preset public: `qr_scan_not_recorded`, `growth_recommendation`, `campaign_request`, `package_upgrade`. Ticket onboarding nội bộ vẫn xuất hiện trong list.

Luồng đầy đủ Create → List → Detail → Message → Close → Reopen → Attachments. Mọi operation resolve business từ path; business khác nhận 404. Detail có `messages[]` text và `next_poll_after_seconds`; đính kèm tệp nằm ở nhóm endpoint `attachments` riêng (không lẫn vào `messages[]`).

**Đính kèm tệp** (`SupportAttachmentController`, bảng `partner_support_attachments`):

- Chấp nhận ảnh (`jpg/jpeg/png/webp/gif`), video (`mp4/mov/webm/avi`), nén (`zip`), văn bản (`pdf/txt/csv/doc(x)/xls(x)/ppt(x)`).
- MIME được server tự dò theo nội dung (`UploadedFile::getMimeType()`) và đối chiếu song song với đuôi file — cả hai phải khớp `support_allowed_attachment_types` / `support_allowed_attachment_extensions` trong `config/config.php`.
- Trần dung lượng cấu hình qua `FIZAHUB_SUPPORT_MAX_ATTACHMENT_SIZE_MB` (mặc định 25MB) và riêng `FIZAHUB_SUPPORT_MAX_VIDEO_ATTACHMENT_SIZE_MB` (mặc định 100MB) cho video.
- Lưu trên disk `local` (không public) theo từng ticket; tải xuống luôn qua endpoint có xác thực + tenant scope, không có URL đoán được.
- Hai chiều: `sender_type` trong response phân biệt `business` (FizaHUB tải lên) và `admin` (MLHUB đính kèm khi trả lời trong `/admin/support`).
- Sai `attachment_id` hoặc file vật lý không còn trên disk → `404 attachment_not_found` (không phải `route_not_found`).
- Idempotency-Key cho request tải lên tính theo nội dung tệp thật (SHA-256), không chỉ tên field: gửi 2 tệp khác nhau cùng key → `409 idempotency_conflict` đúng chuẩn, không âm thầm phát lại kết quả tệp đầu tiên.
- Khi xóa user MLHUB (`Admin → Users → Delete`), ticket support và toàn bộ tệp đính kèm (kể cả file vật lý trên disk) bị xóa theo, không để lại rác.



## CRM Login Link

- Chỉ cấp khi onboarding mới nhất là `ready` hoặc `completed`.
- Link gắn đúng integration, mapped user và workspace; không nâng quyền admin.
- Token 256-bit; DB lưu SHA-256 và ciphertext mã hóa cho secure replay.
- Link có TTL, dùng một lần. Link hết hạn/đã dùng yêu cầu Idempotency-Key mới.
- URL nhạy cảm bị redact khỏi partner API log.
- GET link chỉ hiển thị trang xác nhận (không tiêu token) — trình duyệt thật tự động submit form POST (JS) để thực sự đăng nhập. Mục đích: bot quét preview link của app chat (Zalo/Messenger/Telegram) chỉ GET, không chạy JS, nên không thể tiêu token trước khi người dùng thật bấm vào.



## Admin onboarding board

Route `admin-fizahub.onboarding`: admin có thể set **bất kỳ** public status (kể cả kích hoạt lại từ `cancelled` / `completed`), áp gói `free`|`base`|`biz`|`plus`, và **xóa sạch** dữ liệu partner onboarding (request/history/integration/ticket context/webhook) — **không** xóa user MLHUB (xóa user ở Admin → Users). Partner API vẫn giữ máy trạng thái chặt; board gọi `adminSetStatus` / `adminAssignPackage` / `adminPurgeOnboarding`.

## Partner Reporting Portal (cổng báo cáo view-only cho lãnh đạo FizaHUB)

Cổng riêng cho **lãnh đạo FizaHUB** đăng nhập bằng tài khoản MLHUB có sẵn để xem báo cáo onboarding/HKD của luồng FizaHUB — hoàn toàn tách biệt với 25 endpoint API partner ở trên (đó là API cho **app FizaHUB** gọi; portal này là **giao diện web** cho **người** của FizaHUB xem).

### Domain và ENV


| Biến             | Ý nghĩa                                                                                                                                                                                                                                   |
| ---------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `FIZAHUB_DOMAIN` | Domain riêng chạy portal, ví dụ `fzh.vmo.com.vn`. Route domain-group chỉ đăng ký khi biến này không rỗng.                                                                                                                                 |
| `FIZAHUB_ADMIN`  | Danh sách `users.id` (MLHUB) được phép xem portal, cách nhau dấu phẩy; cho phép khoảng trắng thừa, tự loại giá trị rỗng/không hợp lệ/≤0, tự dedupe. Rỗng hoặc toàn giá trị không hợp lệ ⇒ **không ai** được truy cập (kể cả super-admin). |


Logic parse `FIZAHUB_ADMIN` nằm ở `Support/PartnerReportingAdminIds::parse()` (unit test riêng tại `tests/Unit/APIPartnerFizaHUB/PartnerReportingAdminIdsTest.php`), đọc một lần trong `config/config.php` (namespace `modules.apipartnerfizahub`, giống mọi config khác của module) thành `config('modules.apipartnerfizahub.partner_reporting_domain')` và `config('modules.apipartnerfizahub.partner_reporting_admin_ids')` — không gọi `env()` ngoài file config nên tương thích `config:cache`.

### Luồng truy cập

1. Người dùng mở `https://{FIZAHUB_DOMAIN}/` → chưa đăng nhập thì Fortify chuyển tới trang login (route Fortify mặc định, không đổi core), login xong quay lại dashboard.
2. `EnsureFizaHubPartnerAccess` (áp trên toàn bộ route domain-group) kiểm tra `auth()->id()` có trong `partner_reporting_admin_ids` không — không thì `abort(403)` kèm trang lỗi rõ ràng; user bị xoá/khoá thì middleware `auth` của Fortify đã chặn từ trước.
3. `RestrictFizaHubDomainHost` (đăng ký global trong `APIPartnerFizaHUBServiceProvider`) chặn mọi route MLHUB khác khi `Host` là `FIZAHUB_DOMAIN`: route không nằm trong allowlist (login/logout, dashboard, chi tiết HKD, asset Livewire/Vite, health) → 404 nếu là guest, hoặc redirect về dashboard nếu đã đăng nhập hợp lệ (tránh lộ menu/tính năng MLHUB, không tạo redirect loop vì chính dashboard/login luôn nằm trong allowlist).
4. Session trên `FIZAHUB_DOMAIN` là **host-only, tách biệt hoàn toàn** khỏi session `mlhub.vn`: `APIPartnerFizaHUBServiceProvider::configurePartnerReportingSessionIsolation()` so khớp `Host` request lúc boot (trước `StartSession`) và nếu khớp `FIZAHUB_DOMAIN` thì ghi đè `session.domain = null` (bỏ attribute `domain` trên cookie ⇒ trình duyệt chỉ gửi đúng host này) và đổi tên cookie thành `fizahub_partner_session` (khác hẳn `mlhub_session` của domain chính, không thể trùng/ghi đè lẫn nhau) — chỉ request đúng `Host` này bị ảnh hưởng, mọi request khác (kể cả toàn bộ `mlhub.vn`) giữ nguyên config session hiện tại.



### Route (domain-scoped, chỉ tồn tại khi `FIZAHUB_DOMAIN` khác rỗng)


| Method | Path                                | Middleware                                                 | Mô tả               |
| ------ | ----------------------------------- | ---------------------------------------------------------- | ------------------- |
| GET    | `/`                                 | `web`, `auth`, `verified`, `ensure.fizahub-partner-access` | Dashboard tổng quan |
| GET    | `/onboarding/{onboardingRequestId}` | như trên                                                   | Chi tiết một HKD    |


Đăng nhập/đăng xuất/2FA dùng nguyên route Fortify hiện có (không tạo route auth riêng); các route MLHUB khác bị `RestrictFizaHubDomainHost` chặn như mô tả ở trên.

### Dữ liệu và cô lập theo partner

`Services/PartnerReportingService.php` là nơi duy nhất tổng hợp số liệu cho portal, mọi query lọc cứng `partner_code = PartnerMappingService::partnerCode()` (defense-in-depth, không chỉ dựa vào 1 điều kiện) trước khi trả về:

- **Thẻ số liệu & phân bổ trạng thái**: đếm trực tiếp trên `partner_onboarding_requests` theo `status` (enum onboarding có sẵn, không tự bịa trạng thái mới) và trên `partner_integrations` (`mlhub_business_id is not null` = đã có tài khoản/liên kết); vé hỗ trợ đang mở đếm qua `partner_support_ticket_contexts` join `support_tickets` (đúng quan hệ hiện có, không suy diễn).
- **Tăng trưởng 30 ngày/12 tháng**: group theo `created_at` của `partner_onboarding_requests`, bucket theo timezone ứng dụng (`config('app.timezone')`), ngày/tháng không phát sinh dữ liệu vẫn trả `0`.
- **Danh sách HKD**: `businessListQuery()` phân trang bằng `paginate()` (không tải hết vào mảng rồi lọc tay), `with(['user:...', 'user.plan:...', 'business:...'])` để tránh N+1, hỗ trợ tìm kiếm theo tên HKD/chủ tài khoản/email/số điện thoại, lọc theo trạng thái và gói, sắp xếp theo `created_at desc`.
- **Chi tiết HKD**: tái sử dụng `DashboardService::summarizeCached()` sẵn có cho chỉ số hoạt động (QR scan, lead mới, đánh giá, ưu đãi, đặt lịch, tỷ lệ chuyển đổi, xu hướng 30 ngày) và `SupportTicketBridge` cho danh sách/nội dung vé hỗ trợ chỉ-đọc (không có nút trả lời/đóng/mở lại/xoá trên view).
- **Chỉ số chưa có dữ liệu** (ví dụ HKD chưa từng có `PartnerIntegration` liên kết `lb_businesses`, hoặc chưa phát sinh growth data) hiển thị nguyên trạng "Chưa có dữ liệu" từ các service gốc — portal không tự chế số liệu giả.



### An toàn

Chỉ có route `GET` (không có route mutation nào trong domain-group); view dùng Blade escape mặc định; tìm kiếm HKD dùng query builder tham số hoá (`where(...)->orWhere(...)`, không nối chuỗi SQL thô); lỗi runtime hiển thị qua trang lỗi chuẩn của Laravel (không bật debug riêng cho domain này); domain/user ID không được ghi vào log ngoài log request chuẩn của framework.

### Test

`tests/Feature/APIPartnerFizaHUB/PartnerReportingPortalTest.php` (16 kịch bản: guest redirect, allowlist cho phép/từ chối, ENV parsing rỗng, cô lập dữ liệu theo partner_code, chặn xem chéo HKD khác/partner khác qua đổi ID trên URL, không có route mutation, domain MLHUB không đổi hành vi, route MLHUB khác không lọt qua domain phụ, dashboard/detail không lỗi khi trắng dữ liệu, N+1) và `tests/Unit/APIPartnerFizaHUB/PartnerReportingAdminIdsTest.php` (9 kịch bản parse `FIZAHUB_ADMIN`).

## Migrations

- `2026_07_13_000000_create_fizahub_partner_api_tables.php`
- `2026_07_17_120000_extend_fizahub_partner_onboarding_tables.php`
- `2026_07_20_000000_add_crm_login_idempotency_to_partner_one_time_logins.php`

Không có destructive migration. `SupportTicketBridge` và bảng `support_tickets` được giữ nguyên.

## Postman và diagnostics

Import `[docs/FizaHUB-Partner-API.postman_collection.json](docs/FizaHUB-Partner-API.postman_collection.json)`: System 2, Onboarding 6, Growth 6, Support 10, CRM 1.

Giai đoạn thử nghiệm: `base_url` + `partner_token` đã điền sẵn (trùng `.env.example`). Collection tự sinh external IDs, lưu onboarding/ticket/campaign IDs, skip request phụ thuộc khi thiếu ID. Mỗi request có Params/Body đầy đủ và Examples cho các HTTP status chính.

Regenerate collection sau khi sửa contract:

```bash
php modules/APIPartnerFizaHUB/docs/_build_postman.php
```

```bash
php artisan test tests/Feature/APIPartnerFizaHUB
vendor/bin/pint --test modules/APIPartnerFizaHUB tests/Feature/APIPartnerFizaHUB
php artisan fizahub:doctor
```

Public pages (gửi cho dev FizaHUB):


| URL                      | Nội dung                                            |
| ------------------------ | --------------------------------------------------- |
| `/api-fizahub`           | Contract + quick start                              |
| `/api-fizahub/help-test` | Hướng dẫn click từng bước (người chưa biết Postman) |
| `/api-fizahub/postman`   | Tải JSON đã cấu hình sẵn token                      |




## Breaking cutover checklist

1. Deploy code và additive migrations của 25 endpoint.
2. Deploy đồng thời Postman, README, public docs và endpoint matrix.
3. Dev FizaHUB xóa collection cũ và import collection mới.
4. Chạy staging smoke/Newman và full UI happy path.
5. Cutover production; không bật alias public route cũ.
6. Theo dõi `meta.request_id`, audit log và FizaHUB Doctor.

