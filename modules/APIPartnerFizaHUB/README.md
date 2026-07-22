# APIPartnerFizaHUB

API tích hợp **FizaHUB × MLHUB** cho **15 màn hình** Marketing đã được Product duyệt. Đây là **breaking cutover API v1** với đúng **22 endpoint** dưới prefix `/api/v1/partners/fizahub` và Support hoàn toàn **text-only**.

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

## 22 endpoint chính thức

| # | Method | Path | Mô tả |
|---:|:---:|---|---|
| 1 | GET | `/health` | Readiness API, schema, plan và dependency. |
| 2 | POST | `/partner/sso/verify` | Xác minh partner token. |
| 3 | GET | `/marketing-catalog` | Mục tiêu Marketing, ngành nghề và package. |
| 4 | POST | `/onboarding-requests` | Atomic onboarding và provision Free workspace. |
| 5 | GET | `/onboarding-requests/{request_id}` | Trạng thái và timeline onboarding. |
| 6 | GET | `/businesses/{external_business_id}/marketing-status` | Activation, capability và navigation links. |
| 7 | PATCH | `/businesses/{external_business_id}/profile` | Cập nhật profile được phép. |
| 8 | PATCH | `/businesses/{external_business_id}/marketing-preferences` | Lưu goals và gói quan tâm. |
| 9 | GET | `/businesses/{external_business_id}/dashboard` | KPI/trend; zero-data vẫn 200. |
| 10 | GET | `/businesses/{external_business_id}/growth-insights` | Score, sources, highlights, recommendations. |
| 11 | GET | `/businesses/{external_business_id}/campaigns` | List/filter/search/cursor. |
| 12 | GET | `/businesses/{external_business_id}/campaigns/{campaign_id}` | Campaign detail. |
| 13 | POST | `/businesses/{external_business_id}/campaigns/{campaign_id}/approval` | Duyệt hoặc yêu cầu chỉnh sửa. |
| 14 | GET | `/businesses/{external_business_id}/package` | Effective/requested/approved package. |
| 15 | GET | `/businesses/{external_business_id}/support-presets` | Preset SOP. |
| 16 | POST | `/businesses/{external_business_id}/support-tickets` | Tạo ticket text. |
| 17 | GET | `/businesses/{external_business_id}/support-tickets` | Summary và list cursor. |
| 18 | GET | `/businesses/{external_business_id}/support-tickets/{ticket_id}` | Detail và hội thoại. |
| 19 | POST | `/businesses/{external_business_id}/support-tickets/{ticket_id}/messages` | Gửi message. |
| 20 | POST | `/businesses/{external_business_id}/support-tickets/{ticket_id}/close` | Đóng ticket. |
| 21 | POST | `/businesses/{external_business_id}/support-tickets/{ticket_id}/reopen` | Mở lại ticket. |
| 22 | POST | `/businesses/{external_business_id}/crm-login-links` | Link CRM dùng một lần. |

## Mapping 15 màn hình UI

| Màn hình | Endpoint |
|---|---|
| 01 — Trang chủ chưa kích hoạt | `GET marketing-status` |
| 02 — Chọn giải pháp Marketing | `GET marketing-catalog`; sau onboarding dùng `PATCH marketing-preferences` |
| 03 — Thông tin đăng ký | `POST onboarding-requests` |
| 04 — Tạo tài khoản thành công | Response onboarding và `GET marketing-status` |
| 05 — Theo dõi Onboarding | `GET onboarding-requests/{request_id}` |
| 06 — Trang chủ đã kích hoạt | `GET dashboard` |
| 07 — Tổng quan tăng trưởng | `GET dashboard?range=7d|30d|90d|custom` |
| 08 — Phân tích tăng trưởng | `GET growth-insights` |
| 09 — Danh sách chiến dịch | `GET campaigns` |
| 10 — Quản lý gói | `GET package`; `PATCH marketing-preferences` để lưu gói quan tâm |
| 11 — Chi tiết chiến dịch | `GET campaigns/{campaign_id}`; `POST approval` |
| 12 — Trung tâm hỗ trợ | `GET support-presets`; `GET support-tickets` |
| 13 — Tạo yêu cầu hỗ trợ | `POST support-tickets` |
| 14 — Chi tiết và hội thoại | `GET detail`; `POST messages`; `POST close`; `POST reopen` |
| 15 — Truy cập CRM MLHUB | `POST crm-login-links` khi ready/completed |

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

Luồng đầy đủ Create → List → Detail → Message → Close → Reopen. Mọi operation resolve business từ path; business khác nhận 404. Detail chỉ có `messages[]` text và `next_poll_after_seconds`.

## CRM Login Link

- Chỉ cấp khi onboarding mới nhất là `ready` hoặc `completed`.
- Link gắn đúng integration, mapped user và workspace; không nâng quyền admin.
- Token 256-bit; DB lưu SHA-256 và ciphertext mã hóa cho secure replay.
- Link có TTL, dùng một lần. Link hết hạn/đã dùng yêu cầu Idempotency-Key mới.
- URL nhạy cảm bị redact khỏi partner API log.

## Admin onboarding board

Route `admin-fizahub.onboarding`: admin có thể set **bất kỳ** public status (kể cả kích hoạt lại từ `cancelled` / `completed`), áp gói `free`|`base`, và **xóa sạch** dữ liệu partner onboarding (request/history/integration/ticket context/webhook) — **không** xóa user MLHUB (xóa user ở Admin → Users). Partner API vẫn giữ máy trạng thái chặt; board gọi `adminSetStatus` / `adminAssignPackage` / `adminPurgeOnboarding`.

## Migrations

- `2026_07_13_000000_create_fizahub_partner_api_tables.php`
- `2026_07_17_120000_extend_fizahub_partner_onboarding_tables.php`
- `2026_07_20_000000_add_crm_login_idempotency_to_partner_one_time_logins.php`

Không có destructive migration. `SupportTicketBridge` và bảng `support_tickets` được giữ nguyên.

## Postman và diagnostics

Import [`docs/FizaHUB-Partner-API.postman_collection.json`](docs/FizaHUB-Partner-API.postman_collection.json): System 2, Onboarding 6, Growth 6, Support 7, CRM 1.

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

| URL | Nội dung |
|---|---|
| `/api-fizahub` | Contract + quick start |
| `/api-fizahub/help-test` | Hướng dẫn click từng bước (người chưa biết Postman) |
| `/api-fizahub/postman` | Tải JSON đã cấu hình sẵn token |

## Breaking cutover checklist

1. Deploy code và additive migrations của 22 endpoint.
2. Deploy đồng thời Postman, README, public docs và endpoint matrix.
3. Dev FizaHUB xóa collection cũ và import collection mới.
4. Chạy staging smoke/Newman và full UI happy path.
5. Cutover production; không bật alias public route cũ.
6. Theo dõi `meta.request_id`, audit log và FizaHUB Doctor.
