# MLHUB AI — Quy chuẩn i18n & Vietnamese Copy

> **Mục đích:** File này là nguồn quy chuẩn cho toàn bộ bản dịch song ngữ của MLHUB, đặc biệt là `lang/en.json` và `lang/vi.json`.  
> File này giúp Cursor/Codex/AI dịch đúng ngữ cảnh sản phẩm, không dịch máy thô, không phá placeholder Laravel, không làm lệch tầm nhìn MLHUB, và biết phần nào cần dịch tiếng Việt rõ ràng, phần nào thuộc admin/system/code setting có thể giữ tiếng Anh.

---

## 1. Vai trò của file này

`ARCHITECTURE_I18N.md` dùng để:

1. Chuẩn hóa thuật ngữ tiếng Việt cho MLHUB.
2. Phân biệt khu vực user-facing và admin/system-facing.
3. Quy định khi nào phải dịch, khi nào nên giữ tiếng Anh.
4. Kiểm soát placeholder Laravel như `:count`, `:name`, `:business`, `:date`, `:time`.
5. Tránh lỗi dịch máy làm sai ý nghĩa sản phẩm.
6. Giữ nhất quán brand: **MLHUB**.
7. Định hướng tone tiếng Việt theo tầm nhìn MLHUB tại thị trường Đà Nẵng - Quảng Nam.
8. Làm checklist trước khi commit các thay đổi liên quan `en.json` / `vi.json`.

---

## 2. Tầm nhìn ngôn ngữ của MLHUB

MLHUB không phải phần mềm dịch thuật chung chung. MLHUB là nền tảng **Local Growth / Marketing Automation / CRM / AI Assistant** cho:

- Hộ kinh doanh.
- SME.
- Chuỗi cửa hàng nhỏ.
- Đối tác triển khai nhiều cơ sở kinh doanh.
- Thị trường địa phương, ưu tiên Đà Nẵng - Quảng Nam.

Vì vậy tiếng Việt trong MLHUB phải:

- Rõ ràng.
- Thực dụng.
- Dễ hiểu cho hộ kinh doanh.
- Không quá kỹ thuật ở phần portal người dùng.
- Không quá quảng cáo sáo rỗng.
- Không dịch máy từng chữ.
- Không làm mất chiều sâu công nghệ của sản phẩm.
- Không dùng từ gây hiểu sai nghiệp vụ.

Câu định hướng:

> MLHUB giúp cơ sở kinh doanh địa phương có khách, giữ khách, bán lại, chăm sóc khách và đo hiệu quả tăng trưởng trên một hệ thống đơn giản, có tự động hóa và AI hỗ trợ.

---

## 3. Phân tầng ngôn ngữ trong MLHUB

Không phải toàn bộ hệ thống đều dịch theo một kiểu. MLHUB có nhiều lớp người dùng và ngữ cảnh khác nhau.

### 3.1. Lớp A — Public / Marketing Website

Đối tượng:

- Người chưa đăng ký.
- Hộ kinh doanh.
- Chủ doanh nghiệp.
- Đối tác quan tâm giải pháp.

Ví dụ khu vực:

- Homepage.
- Pricing.
- About MLHUB.
- Blog/FAQ public.
- Landing marketing.
- Trial/signup CTA.

Nguyên tắc dịch:

- Dịch tự nhiên, có tính thuyết phục.
- Nói rõ lợi ích kinh doanh.
- Hạn chế thuật ngữ kỹ thuật.
- Có thể dùng ngôn ngữ marketing, nhưng không phóng đại.
- Ưu tiên “có khách”, “giữ khách”, “bán lại”, “đo hiệu quả”, “tự động hóa chăm sóc khách”.

Ví dụ:


| English                                       | Vietnamese chuẩn                                                                                                                         |
| --------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| A comprehensive Online-to-Offline solution... | Giải pháp Online-to-Offline giúp hộ kinh doanh thu hút khách, nhận đặt lịch, quản lý đánh giá và theo dõi tăng trưởng trên một màn hình. |
| Track real growth on a single screen.         | Theo dõi tăng trưởng thực tế trên một màn hình.                                                                                          |
| Smart assistant, coming soon.                 | Trợ lý thông minh, sắp ra mắt.                                                                                                           |


---

### 3.2. Lớp B — Portal cho hộ kinh doanh

Đối tượng:

- Chủ hộ kinh doanh.
- Nhân viên cửa hàng.
- Người không rành kỹ thuật.
- Người dùng MLHUB hằng ngày.

Ví dụ khu vực:

- Dashboard.
- Business Profiles.
- QR Campaigns.
- Review Booster.
- Booking Pages.
- Coupon Campaigns.
- Feedback Forms.
- Lead Forms.
- Customers.
- Reports.
- AI Assistant preview.

Nguyên tắc dịch:

- Bắt buộc dịch sang tiếng Việt dễ hiểu.
- Câu ngắn, rõ hành động.
- Tránh dùng từ quá kỹ thuật.
- Tránh tiếng Anh nếu đã có từ Việt dễ hiểu.
- Nếu thuật ngữ tiếng Anh phổ biến, có thể giữ kèm giải thích ngắn.

Ưu tiên cách nói:

- “Cơ sở kinh doanh” thay vì “doanh nghiệp” nếu nói về hộ kinh doanh/shop.
- “Khách hàng” thay vì “người dùng cuối”.
- “Khách tiềm năng” thay vì “lead” ở UI phổ thông.
- “Mã ưu đãi” thay vì “coupon” ở UI phổ thông.
- “Trang đích” thay vì “landing page” nếu không cần giữ tiếng Anh.
- “Bảng điều khiển” hoặc giữ “Dashboard” tùy khu vực.

Ví dụ:


| English                                                                           | Vietnamese chuẩn                                                                       |
| --------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------- |
| Add the local profile that powers campaigns, QR codes, public pages, and reports. | Thêm hồ sơ cơ sở kinh doanh để dùng cho chiến dịch, mã QR, trang công khai và báo cáo. |
| Accept table and appointment requests directly from landing pages.                | Nhận yêu cầu đặt bàn hoặc đặt lịch trực tiếp từ trang đích.                            |
| Ask about customers, campaigns, and reviews — right on your dashboard.            | Hỏi về khách hàng, chiến dịch và đánh giá ngay trên bảng điều khiển.                   |


---

### 3.3. Lớp C — Partner / Agency / Pro user

Đối tượng:

- Đối tác triển khai.
- Agency.
- Nhân sự vận hành nhiều cơ sở kinh doanh.
- Team quản lý nhiều khách hàng.

Ví dụ khu vực:

- Partner plan.
- Team/workspace.
- Client workspace.
- Multiple business management.
- Advanced CRM.
- Automation.
- White-label/branding.

Nguyên tắc dịch:

- Có thể dùng thuật ngữ chuyên môn hơn lớp hộ kinh doanh.
- Vẫn phải rõ và nhất quán.
- Có thể giữ các từ: CRM, automation, workspace, branding nếu UI cần ngắn.
- Nên Việt hóa phần mô tả.

Ví dụ:


| English                                                                             | Vietnamese chuẩn                                                                             |
| ----------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------- |
| High-capacity MLHUB operations for partners managing many businesses and customers. | Năng lực vận hành MLHUB quy mô lớn cho đối tác quản lý nhiều cơ sở kinh doanh và khách hàng. |
| Advanced automation, AI, CRM, and branding controls for professional teams.         | Tự động hóa nâng cao, AI, CRM và kiểm soát nhận diện thương hiệu cho đội ngũ chuyên nghiệp.  |


---

### 3.4. Lớp D — Admin / Super Admin

Đối tượng:

- Người vận hành hệ thống MLHUB.
- Admin kỹ thuật.
- BOD/đội vận hành nội bộ.
- Người cấu hình plan, payment, module, marketplace.

Ví dụ khu vực:

- Admin Dashboard.
- Admin Settings.
- Admin Users.
- Admin Plans.
- Admin Marketplace.
- Admin AI.
- Payment gateway settings.
- Module settings.
- Logs.
- System Information.
- Cache.
- Cron.
- Captcha.
- Mail Server.

Nguyên tắc dịch:

- Không bắt buộc dịch mọi thuật ngữ kỹ thuật.
- Các nhãn phổ thông nên dịch: Users = Người dùng, Plans = Gói, Settings = Cài đặt.
- Các thuật ngữ hệ thống nên giữ tiếng Anh nếu dịch làm khó hiểu: API Key, Client ID, Client Secret, Webhook, Token, Endpoint, Callback URL, Redirect URI, Cron, Cache, Queue, Session, Driver, Disk, Provider.
- Không dịch tên module nếu là tên kỹ thuật: `AppBusinessProfiles`, `AdminPlans`, `PaymentStripe`.
- Không dịch env key: `APP_KEY`, `DB_HOST`, `REDIS_HOST`, `GOOGLE_BUSINESS_CLIENT_ID`.

Ví dụ:


| English            | Nên dịch?                  | Vietnamese chuẩn      |
| ------------------ | -------------------------- | --------------------- |
| Admin Dashboard    | Có thể dịch                | Bảng điều khiển Admin |
| Admin Settings     | Có thể dịch                | Cài đặt Admin         |
| API Key            | Không dịch                 | API Key               |
| Client Secret      | Không dịch                 | Client Secret         |
| Webhook URL        | Không dịch hoàn toàn       | Webhook URL           |
| Cron task          | Giữ mixed                  | Cron task             |
| System Information | Có thể dịch                | Thông tin hệ thống    |
| Cache              | Giữ hoặc dịch tùy ngữ cảnh | Cache / Bộ nhớ đệm    |
| Queue              | Giữ hoặc dịch tùy ngữ cảnh | Queue / Hàng đợi      |
| Provider           | Giữ nếu kỹ thuật           | Provider              |


---

### 3.5. Lớp E — Developer / System / Code Setting

Đối tượng:

- Developer.
- DevOps.
- AI agent.
- Người đọc log/config.
- Người cấu hình kỹ thuật.

Ví dụ khu vực:

- `.env.example`.
- Log.
- Error debug.
- System diagnostics.
- Module catalog.
- Marketplace package.
- API/OAuth config.
- Storage config.
- Payment webhook config.

Nguyên tắc:

- Có thể giữ tiếng Anh gần như nguyên bản.
- Không dịch key kỹ thuật.
- Không dịch code, route, env, class, model, table.
- Không cố Việt hóa thuật ngữ có thể làm sai nghĩa.
- Nếu cần tiếng Việt, chỉ thêm mô tả bên cạnh.

Ví dụ không dịch:

```text
APP_KEY
DB_URL
REDIS_URL
MAIL_URL
GOOGLE_BUSINESS_REDIRECT_URI
Client ID
Client Secret
Webhook Secret
Stripe Webhook Secret
2Checkout Merchant Code
S3 Bucket
Redis Database
Queue Worker

```

---

## 4. Quy tắc tổng quát khi dịch `en.json` → `vi.json`

### 4.1. Key là nguồn định danh, không được đổi

Trong Laravel JSON translation:

```json
{
  "Add customer": "Thêm khách hàng"
}

```

- `"Add customer"` là key.
- `"Thêm khách hàng"` là value.
- Chỉ sửa value trong `vi.json`.
- Không đổi key nếu không sửa code gọi `__()` tương ứng.

Quy tắc:

- Không đổi key.
- Không xóa key.
- Không tự ý reorder toàn file nếu không cần.
- Không thêm key lạ nếu không có trong `en.json`, trừ khi đó là key vendor/default có lý do rõ ràng.
- Nếu `vi.json` có key dư, phải báo cáo trước khi xóa.

---

### 4.2. `en.json` là nguồn key chuẩn

Mặc định:

- `en.json` là source of truth về key.
- `vi.json` phải có đầy đủ key tương ứng.
- Nếu `en.json` thiếu key mà code gọi trực tiếp trong tiếng Việt, cần xem lại code, không tự ý tạo key tùy tiện.

Khi phát hiện lệch:


| Tình huống                                         | Cách xử lý                                                               |
| -------------------------------------------------- | ------------------------------------------------------------------------ |
| Key có trong `en.json` nhưng thiếu trong `vi.json` | Thêm vào `vi.json` với bản dịch phù hợp                                  |
| Key có trong `vi.json` nhưng thiếu trong `en.json` | Báo cáo; không xóa ngay                                                  |
| Value `vi` giống hệt `en`                          | Kiểm tra có phải tên riêng/thuật ngữ kỹ thuật không; nếu không, cần dịch |
| Placeholder mismatch                               | P0/P1 tùy ngữ cảnh, phải sửa trước commit                                |
| HTML mismatch                                      | P0/P1, phải sửa trước commit                                             |


---

## 5. Glossary bắt buộc của MLHUB

### 5.1. Thuật ngữ sản phẩm lõi


| English                 | Vietnamese chuẩn                       | Ghi chú                                                     |
| ----------------------- | -------------------------------------- | ----------------------------------------------------------- |
| MLHUB                   | MLHUB                                  | Không dịch                                                  |
| MLHUB AI                | MLHUB AI                               | Không dịch                                                  |
| Business                | Cơ sở kinh doanh                       | Dùng cho portal/hộ kinh doanh                               |
| Local business          | Cơ sở kinh doanh địa phương            | Dùng khi nhấn mạnh local                                    |
| Household business      | Hộ kinh doanh                          | Dùng cho thị trường HKD                                     |
| SME                     | SME                                    | Có thể giữ                                                  |
| Customer                | Khách hàng                             | Không dịch là “người tiêu dùng” nếu không cần               |
| Lead                    | Khách tiềm năng                        | Portal phổ thông nên dịch                                   |
| Contact                 | Liên hệ                                | CRM/contact list                                            |
| Campaign                | Chiến dịch                             |                                                             |
| QR campaign             | Chiến dịch QR                          |                                                             |
| QR code                 | Mã QR                                  |                                                             |
| Landing page            | Trang đích                             | Có thể giữ “Landing Page” trong admin/template              |
| Public page             | Trang công khai                        |                                                             |
| Dashboard               | Bảng điều khiển                        | Có thể giữ “Dashboard” ở menu nếu UI đang quen              |
| Report                  | Báo cáo                                |                                                             |
| Analytics               | Phân tích / Analytics                  | Tùy ngữ cảnh                                                |
| Growth                  | Tăng trưởng                            |                                                             |
| Local growth            | Tăng trưởng địa phương                 |                                                             |
| Online-to-Offline / O2O | Online-to-Offline / O2O                | Có thể giữ kèm mô tả                                        |
| Review                  | Đánh giá                               |                                                             |
| Feedback                | Phản hồi                               |                                                             |
| Booking                 | Đặt lịch / Đặt chỗ                     | Dịch theo ngành: spa = đặt lịch, nhà hàng = đặt bàn/đặt chỗ |
| Appointment             | Lịch hẹn                               |                                                             |
| Coupon                  | Mã ưu đãi                              | Tránh lặp “mã mã giảm giá”                                  |
| Voucher                 | Phiếu ưu đãi                           |                                                             |
| Discount                | Ưu đãi / Giảm giá                      |                                                             |
| Loyalty                 | Khách thân thiết / Tích điểm           | Tùy ngữ cảnh                                                |
| Referral                | Giới thiệu khách                       |                                                             |
| Retention               | Giữ chân khách / Chăm sóc lại khách cũ |                                                             |
| Repeat visit            | Khách quay lại / Lần ghé lại           |                                                             |
| Repeat purchase         | Mua lại                                |                                                             |
| Automation              | Tự động hóa                            |                                                             |
| Workflow                | Quy trình                              |                                                             |
| Trigger                 | Trigger / Điều kiện kích hoạt          | Admin giữ Trigger được                                      |
| Template                | Mẫu                                    |                                                             |
| Template pack           | Bộ mẫu                                 |                                                             |
| AI template             | Mẫu AI                                 |                                                             |
| Prompt                  | Câu lệnh AI                            | Không dịch là “lời nhắc” trong AI Studio nếu nghe yếu       |
| AI credits              | Tín dụng AI                            |                                                             |
| Credits                 | Tín dụng                               | Trong MLHUB thường là tín dụng AI                           |
| Plan                    | Gói                                    |                                                             |
| Pricing                 | Bảng giá                               |                                                             |
| Billing                 | Thanh toán                             |                                                             |
| Invoice                 | Hóa đơn                                |                                                             |
| Subscription            | Gói đăng ký / Subscription             | Tùy admin/user                                              |
| Workspace               | Không gian làm việc                    |                                                             |
| Team                    | Đội nhóm                               |                                                             |
| Branding                | Nhận diện thương hiệu                  |                                                             |
| Brand                   | Thương hiệu                            |                                                             |
| Custom domain           | Tên miền riêng                         |                                                             |
| File library            | Thư viện tệp                           |                                                             |
| Storage                 | Lưu trữ                                |                                                             |
| Support ticket          | Ticket hỗ trợ / Phiếu hỗ trợ           | Admin có thể giữ Ticket                                     |


---

### 5.2. Thuật ngữ theo tính năng MLHUB

#### Business Profiles


| English              | Vietnamese chuẩn          |
| -------------------- | ------------------------- |
| Business profile     | Hồ sơ cơ sở kinh doanh    |
| Add business         | Thêm cơ sở kinh doanh     |
| Business name        | Tên cơ sở                 |
| Business type        | Loại hình kinh doanh      |
| Industry             | Ngành nghề                |
| Opening hours        | Giờ mở cửa                |
| Location             | Địa điểm / Vị trí         |
| Google Maps URL      | URL Google Maps           |
| Public business page | Trang công khai của cơ sở |
| Managed business     | Cơ sở đang quản lý        |


#### QR Campaigns


| English         | Vietnamese chuẩn   |
| --------------- | ------------------ |
| QR campaign     | Chiến dịch QR      |
| QR scan         | Lượt quét QR       |
| Scan report     | Báo cáo lượt quét  |
| Destination URL | URL đích           |
| Dynamic routing | Điều hướng động    |
| Branded QR      | QR gắn thương hiệu |
| QR sticker      | Tem QR             |
| Public QR page  | Trang QR công khai |


#### Review Booster


| English                | Vietnamese chuẩn                           |
| ---------------------- | ------------------------------------------ |
| Review Booster         | Công cụ xin đánh giá                       |
| Review request         | Yêu cầu đánh giá                           |
| Positive review        | Đánh giá tích cực                          |
| Low-score feedback     | Phản hồi điểm thấp                         |
| Private feedback       | Phản hồi riêng tư                          |
| Google review          | Đánh giá Google                            |
| Facebook review        | Đánh giá Facebook                          |
| Rating threshold       | Ngưỡng đánh giá                            |
| 4-5 stars go public    | 4-5 sao chuyển đến kênh đánh giá công khai |
| 1-3 stars stay private | 1-3 sao được ghi nhận riêng tư             |


#### Booking Pages


| English           | Vietnamese chuẩn  |
| ----------------- | ----------------- |
| Booking page      | Trang đặt lịch    |
| Booking request   | Yêu cầu đặt lịch  |
| Appointment       | Lịch hẹn          |
| Service           | Dịch vụ           |
| Time slot         | Khung giờ         |
| Duration          | Thời lượng        |
| Confirm booking   | Xác nhận đặt lịch |
| Cancel booking    | Hủy lịch          |
| Completed booking | Lịch đã hoàn tất  |


Ghi chú:

- Với nhà hàng/quán ăn: dùng “đặt bàn” hoặc “đặt chỗ”.
- Với spa/salon/phòng khám: dùng “đặt lịch”.
- Với khách sạn/lưu trú: dùng “đặt phòng” nếu ngữ cảnh rõ.

#### Coupon Campaigns


| English         | Vietnamese chuẩn     |
| --------------- | -------------------- |
| Coupon          | Mã ưu đãi            |
| Coupon campaign | Chiến dịch mã ưu đãi |
| Claim coupon    | Nhận mã ưu đãi       |
| Use coupon      | Sử dụng mã           |
| Coupon code     | Mã ưu đãi            |
| Redemption      | Lượt đổi mã          |
| Usage limit     | Giới hạn sử dụng     |
| Expiry date     | Ngày hết hạn         |
| Discount value  | Giá trị ưu đãi       |


Không dùng:

- “mã mã giảm giá”
- “phiếu mua hàng” nếu không đúng ngữ cảnh
- “đổi coupon” nếu UI cho hộ kinh doanh không quen

#### Feedback Forms


| English           | Vietnamese chuẩn           |
| ----------------- | -------------------------- |
| Feedback form     | Form phản hồi              |
| Feedback response | Phản hồi đã gửi            |
| Rating            | Đánh giá / Điểm đánh giá   |
| Message           | Nội dung                   |
| Contact required  | Bắt buộc thông tin liên hệ |
| Thank-you message | Lời cảm ơn                 |


#### Lead Forms


| English         | Vietnamese chuẩn           |
| --------------- | -------------------------- |
| Lead form       | Form thu khách tiềm năng   |
| Lead submission | Khách tiềm năng đã gửi     |
| Capture leads   | Thu khách tiềm năng        |
| Lead status     | Trạng thái khách tiềm năng |
| Follow up       | Theo dõi / Chăm sóc tiếp   |
| Convert lead    | Chuyển đổi khách tiềm năng |


#### CRM / Customers


| English           | Vietnamese chuẩn       |
| ----------------- | ---------------------- |
| Customer          | Khách hàng             |
| Customer profile  | Hồ sơ khách hàng       |
| Customer activity | Hoạt động khách hàng   |
| Timeline          | Dòng thời gian         |
| Segment           | Phân khúc              |
| Tag               | Thẻ                    |
| Note              | Ghi chú                |
| Task              | Công việc              |
| Follow-up task    | Việc cần chăm sóc tiếp |
| Last activity     | Hoạt động gần nhất     |
| Lifetime value    | Giá trị vòng đời       |
| Customer score    | Điểm khách hàng        |


Không dịch “tag” thành “thẻ tag”. Chỉ dùng:

- “Thẻ”
- “Nhãn”
- “Tag” nếu admin/technical

#### Loyalty / Referral


| English           | Vietnamese chuẩn            |
| ----------------- | --------------------------- |
| Loyalty card      | Thẻ tích điểm               |
| Loyalty customer  | Khách tích điểm             |
| Stamp             | Dấu tích / Lượt tích điểm   |
| Reward            | Phần thưởng                 |
| Referral campaign | Chiến dịch giới thiệu khách |
| Referral link     | Link giới thiệu             |
| Referral reward   | Thưởng giới thiệu           |
| Referrer          | Người giới thiệu            |
| Referred customer | Khách được giới thiệu       |


#### Automation


| English               | Vietnamese chuẩn    |
| --------------------- | ------------------- |
| Email automation      | Tự động hóa email   |
| WhatsApp notification | Thông báo WhatsApp  |
| Webhook automation    | Tự động hóa webhook |
| Trigger event         | Sự kiện kích hoạt   |
| Delay                 | Độ trễ              |
| Condition             | Điều kiện           |
| Action                | Hành động           |
| Automation log        | Nhật ký tự động hóa |
| Send to customer      | Gửi cho khách hàng  |
| Send to owner         | Gửi cho chủ cơ sở   |


#### Google Business


| English                 | Vietnamese chuẩn             |
| ----------------------- | ---------------------------- |
| Google Business Profile | Google Business Profile      |
| Google location         | Địa điểm Google              |
| Managed Google location | Địa điểm Google đang quản lý |
| Import location         | Nhập địa điểm                |
| Map location            | Gắn địa điểm                 |
| Sync reviews            | Đồng bộ đánh giá             |
| Reply to review         | Trả lời đánh giá             |
| Google insights         | Chỉ số Google                |
| Google posts            | Bài đăng Google              |


#### AI Studio


| English           | Vietnamese chuẩn      |
| ----------------- | --------------------- |
| AI Studio         | AI Studio             |
| AI assistant      | Trợ lý AI             |
| Content generator | Tạo nội dung          |
| Caption drafts    | Bản nháp caption      |
| Repurpose content | Tái sử dụng nội dung  |
| Content planner   | Lập kế hoạch nội dung |
| Review reply      | Trả lời đánh giá      |
| Best time         | Thời điểm phù hợp     |
| Semantic search   | Tìm kiếm ngữ nghĩa    |
| Prompt            | Câu lệnh AI           |
| Creativity        | Mức sáng tạo          |
| Tone              | Giọng văn             |
| Output            | Kết quả               |
| Credits per run   | Tín dụng mỗi lần chạy |


---

## 6. Thuật ngữ admin/system có thể giữ tiếng Anh

Các thuật ngữ sau không bắt buộc dịch sang tiếng Việt, nhất là trong khu Admin, Settings, System Information, Logs, Integrations, Payment Gateway, Marketplace.

### 6.1. Kỹ thuật hệ thống


| Term          | Quy tắc                                                                      |
| ------------- | ---------------------------------------------------------------------------- |
| API Key       | Giữ nguyên                                                                   |
| Access Key    | Giữ nguyên hoặc “Access Key”                                                 |
| Secret Key    | Giữ nguyên                                                                   |
| Client ID     | Giữ nguyên                                                                   |
| Client Secret | Giữ nguyên                                                                   |
| Token         | Giữ nguyên hoặc “Token”                                                      |
| Access token  | Giữ nguyên hoặc “Access token”                                               |
| Refresh token | Giữ nguyên                                                                   |
| Redirect URI  | Giữ nguyên                                                                   |
| Callback URL  | Giữ nguyên                                                                   |
| Endpoint      | Giữ nguyên                                                                   |
| Webhook       | Giữ nguyên                                                                   |
| Webhook URL   | Giữ nguyên                                                                   |
| Payload       | Không dịch là “trọng tải”; dùng “payload” hoặc “dữ liệu gửi về” tùy ngữ cảnh |
| Provider      | Giữ nguyên nếu nói về hệ thống                                               |
| Driver        | Giữ nguyên                                                                   |
| Disk          | Giữ nguyên nếu storage config                                                |
| Bucket        | Giữ nguyên                                                                   |
| Region        | Giữ nguyên                                                                   |
| Queue         | Giữ nguyên hoặc “hàng đợi”                                                   |
| Cache         | Giữ nguyên hoặc “bộ nhớ đệm”                                                 |
| Session       | Giữ nguyên                                                                   |
| Cron          | Giữ nguyên                                                                   |
| Log           | Giữ nguyên hoặc “nhật ký”                                                    |
| Middleware    | Giữ nguyên                                                                   |
| Guard         | Giữ nguyên                                                                   |
| Model         | Giữ nguyên                                                                   |
| Module        | Giữ nguyên                                                                   |
| Marketplace   | Giữ nguyên                                                                   |
| License       | Giữ nguyên hoặc “giấy phép” tùy ngữ cảnh                                     |


### 6.2. Env/config/code

Không dịch:

```text
APP_KEY
APP_URL
APP_ENV
APP_DEBUG
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
DB_URL
REDIS_HOST
REDIS_PASSWORD
REDIS_URL
MAIL_HOST
MAIL_PORT
MAIL_URL
GOOGLE_BUSINESS_CLIENT_ID
GOOGLE_BUSINESS_CLIENT_SECRET
GOOGLE_BUSINESS_REDIRECT_URI
STRIPE_KEY
STRIPE_SECRET
PAYPAL_CLIENT_ID
PAYPAL_SECRET

```

### 6.3. Tên module/class/table/route

Không dịch:

```text
AppBusinessProfiles
AppQRCampaigns
AppReviewBooster
AppBookingPages
AppCouponCampaigns
AppFeedbackForms
AppLeadForms
AppAdvancedCustomerCrm
AppEmailAutomation
AppWebhookAutomation
AppWhatsAppNotification
AppLoyaltyStampCards
AppGoogleBusiness
AdminPlans
AdminSettings
PaymentStripe
PaymentPaypal
Payment2Checkout
CustomMLHUB
lb_businesses
lb_campaigns
lb_customers
portal/businesses
portal/qr-campaigns

```

---

## 7. Bản dịch cần tránh

### 7.1. Bảng lỗi phổ biến


| English          | Không dùng                | Dùng thay thế                                  |
| ---------------- | ------------------------- | ---------------------------------------------- |
| Accent           | Trọng âm                  | Màu nhấn / Điểm nhấn                           |
| Accent color     | Màu trọng âm              | Màu nhấn                                       |
| Addon            | VIP                       | Tiện ích mở rộng / Module mở rộng              |
| Addons only      | Chỉ VIP                   | Chỉ tiện ích mở rộng / Chỉ module mở rộng      |
| Card             | Thẻ tag                   | Thẻ / Mục / Khối / Ô hiển thị                  |
| Add card         | Thêm thẻ tag              | Thêm thẻ / Thêm mục                            |
| Image card       | Thẻ tag hình ảnh          | Thẻ hình ảnh / Khối hình ảnh                   |
| Payload          | Trọng tải                 | Dữ liệu gửi về / Dữ liệu trả về / Payload      |
| Copy             | Bản sao                   | Nội dung                                       |
| AI copy          | Bản sao AI                | Nội dung AI                                    |
| Account coverage | Bảo hiểm tài khoản        | Mức độ hoàn thiện tài khoản / Độ phủ tài khoản |
| Active share     | Chia sẻ tích cực          | Tỷ trọng đang hoạt động                        |
| Active base      | Căn cứ hoạt động          | Tệp đang hoạt động / Nhóm đang hoạt động       |
| Booster          | Tăng cường                | Công cụ / Chiến dịch / Tăng đánh giá           |
| Review Booster   | Trình tăng cường đánh giá | Công cụ xin đánh giá                           |
| Lead             | Dẫn / Đầu mối             | Khách tiềm năng                                |
| Conversion       | Sự chuyển đổi             | Chuyển đổi                                     |
| Redemption       | Sự cứu chuộc              | Lượt đổi mã                                    |
| Stamp            | Tem                       | Dấu tích / Lượt tích điểm                      |
| Prompt           | Lời nhắc                  | Câu lệnh AI                                    |
| Tone             | Âm điệu                   | Giọng văn                                      |
| Storage disk     | Đĩa lưu trữ               | Disk lưu trữ / Bộ lưu trữ                      |
| Checkout payload | Trọng tải thanh toán      | Dữ liệu trả về từ thanh toán                   |
| Manual payment   | Thanh toán thủ công       | Thanh toán chuyển khoản / Thanh toán thủ công  |
| Plan             | Kế hoạch                  | Gói                                            |
| Plan limits      | Giới hạn kế hoạch         | Hạn mức gói                                    |
| Billing history  | Lịch sử thanh toán        | Lịch sử thanh toán                             |
| Invoice PDF      | PDF hóa đơn               | File PDF hóa đơn                               |
| Recovery codes   | Mã phục hồi               | Mã khôi phục                                   |


---

### 7.2. Quy tắc tránh dịch máy từng chữ

Không dịch theo cấu trúc tiếng Anh nếu tiếng Việt nghe cứng.

Ví dụ:


| English                                                                      | Dịch máy sai                                                                                                     | Dịch chuẩn                                                                                   |
| ---------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------- |
| A compact view across total, open, resolved, and unread states.              | Chế độ xem thu gọn trên các trạng thái tổng, mở, đã giải quyết và chưa đọc.                                      | Tổng quan nhanh theo trạng thái: tổng số, đang mở, đã xử lý và chưa đọc.                     |
| Account, plan, and credits are visible from one panel.                       | Tài khoản, gói và tín dụng được hiển thị từ một bảng điều khiển.                                                 | Xem tài khoản, gói đang dùng và tín dụng AI trong cùng một màn hình.                         |
| Add a branded subdomain to turn public campaign URLs into owned brand links. | Thêm tên miền phụ được gắn thương hiệu để biến URL chiến dịch công khai thành liên kết thương hiệu thuộc sở hữu. | Thêm tên miền phụ riêng để các link chiến dịch công khai mang nhận diện thương hiệu của bạn. |
| The coupon code is invalid or not available for this plan.                   | Mã mã giảm giá không hợp lệ...                                                                                   | Mã ưu đãi không hợp lệ hoặc không áp dụng cho gói này.                                       |
| Add image cards with captions and optional links.                            | Thêm thẻ tag hình ảnh...                                                                                         | Thêm thẻ hình ảnh kèm chú thích và liên kết tùy chọn.                                        |


---

## 8. Quy tắc brand MLHUB / LocalBoost

### 8.1. User-facing luôn dùng MLHUB

Trong giao diện public/portal:

- Dùng **MLHUB**.
- Không thêm text **LocalBoost** mới.
- Nếu key tiếng Anh gốc có `LocalBoost`, value tiếng Việt user-facing nên chuyển thành `MLHUB`.

Ví dụ:


| Key tiếng Anh                                                                             | Value tiếng Việt chuẩn                                                                        |
| ----------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------- |
| A similar LocalBoost business already exists...                                           | Một cơ sở kinh doanh MLHUB tương tự đã tồn tại...                                             |
| Add only the Google locations you want to manage, then map them to LocalBoost businesses. | Chỉ thêm các địa điểm Google muốn quản lý, sau đó gắn chúng với cơ sở kinh doanh trong MLHUB. |


### 8.2. Khi nào có thể giữ LocalBoost

Có thể giữ `LocalBoost` nếu:

- Đó là tên source package.
- Đó là tài liệu kỹ thuật nội bộ.
- Đó là phần mô tả license/source code.
- Đó là route/table/class/module chưa rename và không xuất hiện với user cuối.

Nếu không chắc, mặc định user-facing dùng **MLHUB**.

---

## 9. Quy tắc placeholder Laravel

Placeholder là bắt buộc giữ nguyên.

Ví dụ:

```json
":name submitted a lead through :campaign.": ":name đã gửi khách tiềm năng qua :campaign."

```

Không được:

```json
":name submitted a lead through :campaign.": "Khách hàng đã gửi khách tiềm năng qua chiến dịch."

```

Vì mất `:name` và `:campaign`.

### 9.1. Placeholder phổ biến

```text
:count
:name
:business
:campaign
:date
:time
:email
:phone
:provider
:fields
:location
:rating
:reviewer
:status
:user
:task
:tag
:templates
:used
:limit
:credits
:days
:seconds
:stars
:destination
:actionText
:plan
:topup
:lines
:words
:share

```

### 9.2. Quy tắc bắt buộc

- Placeholder trong value tiếng Việt phải khớp với key tiếng Anh.
- Không đổi tên placeholder.
- Không dịch placeholder.
- Không thêm dấu cách vào trong placeholder.
- Không đổi `:actionText` thành `:actiontext`.
- Không đổi `:business` thành `:doanhnghiep`.
- Có thể đổi vị trí placeholder cho đúng ngữ pháp tiếng Việt.

Ví dụ đúng:

```json
":count customers": ":count khách hàng"

```

Ví dụ đúng:

```json
":name requested a booking for :date at :time.": ":name đã gửi yêu cầu đặt lịch vào :date lúc :time."

```

---

## 10. Quy tắc HTML / Markdown / Entity

Một số value chứa HTML, markdown, entity, dấu đặc biệt. Không được phá cấu trúc.

### 10.1. HTML

Nếu key có:

```html
<div>Custom content</div>

```

Value có thể dịch text bên trong nhưng giữ tag:

```html
<div>Nội dung tùy chỉnh</div>

```

Không được:

```html
<Nội dung tùy chỉnh>

```

### 10.2. Markdown/link

Nếu có:

```markdown
[Learn more](:url)

```

Phải giữ `(:url)`.

### 10.3. Dấu đặc biệt

Giữ nguyên khi cần:

```text
—
…
★
%
/mo
A/AAAA
2FA
A/B
O2O

```

---

## 11. Quy tắc số, tiền, ngày, đơn vị

### 11.1. Tiền tệ

- Nếu đang dùng `$19/mo`, trong tiếng Việt có thể là `$19/tháng` nếu giá USD vẫn giữ.
- Nếu chuyển sang VND thì phải do logic pricing quyết định, không tự sửa trong file dịch.
- Không tự đổi `$` sang `đ`.

### 11.2. Thời gian


| English       | Vietnamese chuẩn          |
| ------------- | ------------------------- |
| 15 minutes    | 15 phút                   |
| 30 minutes    | 30 phút                   |
| 60 minutes    | 60 phút                   |
| 7-day demand  | Nhu cầu trong 7 ngày      |
| 30-day growth | Tăng trưởng trong 30 ngày |


### 11.3. Page size


| English   | Vietnamese chuẩn |
| --------- | ---------------- |
| 10 / page | 10 / trang       |
| 25 / page | 25 / trang       |
| 50 rows   | 50 dòng          |


---

## 12. Quy tắc tone tiếng Việt

### 12.1. Tone mặc định

- Rõ.
- Gọn.
- Tin cậy.
- Có tính hướng dẫn.
- Không quá thân mật.
- Không quá quan liêu.
- Không quá “agency quảng cáo”.

Ví dụ:

Không nên:

> Hãy bùng nổ tăng trưởng với siêu công cụ đỉnh cao.

Nên:

> Tạo chiến dịch QR, thu khách tiềm năng và theo dõi hiệu quả trong một màn hình.

### 12.2. Xưng hô

Tùy khu vực:


| Khu vực                  | Xưng hô                                              |
| ------------------------ | ---------------------------------------------------- |
| Portal phổ thông         | “Bạn” hoặc câu không chủ ngữ                         |
| Onboarding hộ kinh doanh | Có thể dùng “anh/chị” nếu helper text cần thân thiện |
| Admin                    | Câu trung tính                                       |
| System/log               | Không cần xưng hô                                    |
| Marketing page           | Có thể dùng “bạn”                                    |


Ưu tiên câu không chủ ngữ trong UI:

- “Thêm khách hàng”
- “Tạo chiến dịch”
- “Cập nhật cài đặt”
- “Chưa có dữ liệu”
- “Không tìm thấy kết quả”

---

## 13. Phân loại key trước khi dịch

Trước khi sửa một dòng trong `vi.json`, cần phân loại key đó thuộc nhóm nào.

### 13.1. `translate`

Bắt buộc dịch sang tiếng Việt tự nhiên.

Gồm:

- Public marketing.
- Portal dashboard.
- Business profiles.
- Growth tools.
- CRM/customer.
- Booking/coupon/review/feedback/lead.
- User onboarding.
- User billing cơ bản.
- Error message user-facing.

Ví dụ:

```text
Add customer
Create campaign
No customers yet
Accept bookings from QR
Ask about customers, campaigns, and reviews

```

### 13.2. `mixed`

Dịch tiếng Việt nhưng giữ thuật ngữ tiếng Anh cần thiết.

Gồm:

- AI Studio.
- CRM.
- Webhook.
- Google Business.
- Payment gateway.
- Custom domain.
- API/OAuth.
- Technical admin settings nhưng có mô tả user-facing.

Ví dụ:


| English                    | Vietnamese              |
| -------------------------- | ----------------------- |
| Webhook automation         | Tự động hóa webhook     |
| Google Business connection | Kết nối Google Business |
| AI credits                 | Tín dụng AI             |
| Custom domain              | Tên miền riêng          |
| OAuth callback             | OAuth callback          |


### 13.3. `keep`

Giữ tiếng Anh hoặc chỉ dịch nhẹ.

Gồm:

- Tên module.
- Tên provider.
- Tên gateway.
- Env key.
- API key fields.
- Logs.
- System diagnostics.
- Technical code/config labels.

Ví dụ:

```text
API Key
Client Secret
Redirect URI
Webhook URL
APP_KEY
DB_HOST
Redis
Stripe
PayPal
2Checkout
Marketplace

```

### 13.4. `do_not_touch`

Không sửa nếu không có lý do cực rõ.

Gồm:

- Placeholder.
- HTML tag.
- Env key.
- Route.
- Table.
- Class.
- Component name.
- Vendor name.
- License string.
- Payment provider identifier.

---

## 14. Quy trình audit `en.json` / `vi.json`

Trước khi dịch, phải audit.

### 14.1. Checklist audit

1. Đếm key `en.json`.
2. Đếm key `vi.json`.
3. Tìm key thiếu trong `vi.json`.
4. Tìm key dư trong `vi.json`.
5. Tìm placeholder mismatch.
6. Tìm HTML/tag mismatch.
7. Tìm value tiếng Việt giống hệt tiếng Anh.
8. Tìm LocalBoost user-facing còn sót.
9. Tìm thuật ngữ cấm.
10. Tìm cụm dịch máy nghi ngờ.
11. Tìm các dòng thuộc portal/growth nhưng chưa dịch.
12. Tìm các dòng admin/system có nên giữ tiếng Anh không.

### 14.2. Báo cáo audit chuẩn

Mỗi lần audit phải trả:

```text
1. en key count:
2. vi key count:
3. missing_in_vi:
4. extra_in_vi:
5. placeholder_mismatch:
6. html_mismatch:
7. same_as_en_count:
8. LocalBoost leftovers:
9. glossary violations:
10. top suspicious translations:
11. proposed batch plan:

```

---

## 15. Quy trình sửa dịch theo batch

Không dịch toàn bộ `vi.json` trong một lượt lớn. File quá dài, dễ phá placeholder và ngữ cảnh.

### 15.1. Thứ tự batch ưu tiên

#### Batch 1 — Onboarding + Business Profiles + Dashboard

Ưu tiên vì đây là phần đầu tiên hộ kinh doanh nhìn thấy.

Nhóm key:

```text
Business
Business profile
Business type
Location
Opening hours
Dashboard
Overview
Recent activity
Growth overview
Add business
Create your first business

```

#### Batch 2 — Growth Tools

Nhóm key:

```text
QR Campaigns
Review Booster
Booking Pages
Coupon Campaigns
Feedback Forms
Lead Forms
Landing Pages
Marketing Templates

```

#### Batch 3 — CRM + Retention + Automation

Nhóm key:

```text
Customers
Customer activity
Tags
Segments
Tasks
Notes
Loyalty
Referral
Email automation
WhatsApp notification
Webhook automation

```

#### Batch 4 — AI Studio

Nhóm key:

```text
AI Studio
Prompt
Caption
Content planner
Repurpose
Review reply
Best time
Semantic search
Generate image
Generate video
Credits

```

#### Batch 5 — Billing + Plans + Credits + Payments

Nhóm key:

```text
Plans
Billing
Invoice
Payment
Subscription
Credits
Manual payment
Stripe
PayPal
2Checkout

```

#### Batch 6 — Admin + Settings + System

Nhóm key:

```text
Admin
Settings
Users
Roles
Permissions
Modules
Marketplace
System Information
Cache
Cron
Logs
Mail Server
Captcha
Storage
API
Webhook
OAuth

```

Admin/system không cần ép dịch toàn bộ sang tiếng Việt. Ưu tiên không sai nghĩa.

---

## 16. Quy định riêng cho khu Admin

### 16.1. Admin nào nên dịch

Nên dịch các mục admin phổ thông:


| English            | Vietnamese            |
| ------------------ | --------------------- |
| Admin Dashboard    | Bảng điều khiển Admin |
| Users              | Người dùng            |
| Plans              | Gói                   |
| Coupons            | Mã ưu đãi             |
| Payment History    | Lịch sử thanh toán    |
| Settings           | Cài đặt               |
| Languages          | Ngôn ngữ              |
| Themes             | Giao diện             |
| Blogs              | Blog                  |
| FAQs               | Câu hỏi thường gặp    |
| Support Tickets    | Ticket hỗ trợ         |
| Notifications      | Thông báo             |
| Files              | Tệp                   |
| Cache              | Cache / Bộ nhớ đệm    |
| Logs               | Nhật ký               |
| System Information | Thông tin hệ thống    |


### 16.2. Admin nào có thể giữ tiếng Anh

Giữ tiếng Anh/mixed nếu thuộc kỹ thuật:


| English             | Vietnamese rule          |
| ------------------- | ------------------------ |
| API Key             | Giữ nguyên               |
| Secret              | Giữ nguyên               |
| Client ID           | Giữ nguyên               |
| OAuth               | Giữ nguyên               |
| Webhook             | Giữ nguyên               |
| Callback            | Giữ nguyên               |
| Redirect URI        | Giữ nguyên               |
| Provider            | Giữ nguyên               |
| Driver              | Giữ nguyên               |
| Queue               | Giữ nguyên hoặc Hàng đợi |
| Cache Store         | Giữ mixed                |
| Session Driver      | Giữ mixed                |
| Storage Disk        | Giữ mixed                |
| Marketplace Package | Gói Marketplace          |
| License             | License / Giấy phép      |
| Cron                | Cron                     |
| Module ZIP          | Module ZIP               |


### 16.3. Không dịch code-like values

Không dịch:

```text
stripe
paypal
2checkout
manual
smtp
redis
database
file
s3
public
private
active
inactive
draft
published
pending
confirmed
cancelled
completed
claimed
used
expired

```

Lưu ý: Nếu các value này hiển thị trực tiếp cho user cuối thì có thể cần label tiếng Việt riêng ở UI, nhưng không sửa key/status raw nếu code đang phụ thuộc.

---

## 17. Quy tắc cho AI / Assistant copy

MLHUB có hai bề mặt AI user-facing: **Chat MLHUB AI** (`portal.chatmlhubai`) và **Studio AI** (`portal.ai-studio/*`). Dịch phải thể hiện đúng ranh giới — chi tiết kỹ thuật → `ARCHITECTURE_MLHUBAI.md` §16–§17; ma trận scenario theo ngành → `ARCHITECTURE_SOP.md` §E.

### 17.1. Chat MLHUB AI — nguyên tắc copy

- Người dùng hỏi bằng ngôn ngữ tự nhiên về **dữ liệu tăng trưởng** và **việc cần làm hôm nay**.
- Basic AI: nhấn mạnh **không tốn tín dụng AI** / không dùng token (đúng với code).
- Advanced AI: nói rõ cần bật toggle, API key admin, và **có thể trừ tín dụng** (`mlhub_ai_chat`).
- Không hứa tính năng Studio chưa bật route; không bịa số liệu.

### 17.2. Studio AI — nguyên tắc copy

- Nhãn menu: **AI Studio** hoặc tên task cụ thể (Content Writer, Repurpose, Planner, AI Image…).
- Mỗi task **có thể tốn tín dụng AI** — hiển thị cost trước khi chạy nếu UI có hook.
- Không gọi Studio là “Chat MLHUB AI”; không gộp vào toggle Basic/Advanced của chat widget.

### 17.3. Bảng copy chuẩn (Chat widget / full page)


| English | Vietnamese chuẩn |
| --- | --- |
| Introducing MLHUB AI | Giới thiệu MLHUB AI |
| Ask MLHUB AI in natural language | Hỏi MLHUB AI bằng tiếng Việt tự nhiên |
| Talk to your growth data on Portal Dashboard. | Trò chuyện với dữ liệu tăng trưởng ngay trên Portal Dashboard. |
| Ask in plain language — get answers about campaigns, reviews, and bookings. | Hỏi bằng tiếng Việt đời thường — nhận câu trả lời về chiến dịch, đánh giá và đặt lịch. |
| MLHUB AI | MLHUB AI |
| Growth assistant report | Báo cáo trợ lý tăng trưởng |
| Basic AI | Basic AI |
| Advanced AI | Advanced AI |
| Open full chat | Mở chat đầy đủ |
| What should I do next? / Suggest a new campaign. | Tôi nên làm gì tiếp? / Gợi ý một chiến dịch mới. |
| Smart assistant, coming soon. | Trợ lý thông minh, sắp ra mắt. |

**Ghi chú toggle:** giữ nguyên nhãn tiếng Anh **Basic AI** / **Advanced AI** trên UI (thuật ngữ sản phẩm); mô tả phụ có thể Việt hóa trong tooltip/help nếu thêm sau.

### 17.4. Bảng copy chuẩn (Studio — nhãn task)


| English | Vietnamese chuẩn |
| --- | --- |
| AI Studio | AI Studio |
| Campaign Builder | Campaign Builder |
| Content Writer | Content Writer |
| Content Planner | Lập kế hoạch nội dung |
| Repurpose | Tái sử dụng nội dung |
| AI Image | Tạo ảnh AI |
| Review Reply | Trả lời đánh giá |
| Prompt history | Lịch sử prompt |
| AI settings | Cài đặt AI |

Không dùng:

- “trợ lý thần thánh”
- “AI siêu cấp”
- “tự động tăng trưởng chắc chắn”
- “cam kết có khách”

---

## 18. Quy tắc cho Billing / Plan / Credit

### 18.1. Plan


| English      | Vietnamese chuẩn |
| ------------ | ---------------- |
| Plan         | Gói              |
| Free plan    | Gói miễn phí     |
| Starter plan | Gói Starter      |
| Growth plan  | Gói Growth       |
| Pro plan     | Gói Pro          |
| Partner plan | Gói Partner      |
| Monthly      | Theo tháng       |
| Yearly       | Theo năm         |
| Lifetime     | Trọn đời         |
| Trial        | Dùng thử         |
| Plan limits  | Hạn mức gói      |
| Plan usage   | Mức sử dụng gói  |
| Plan expiry  | Ngày hết hạn gói |
| Upgrade plan | Nâng cấp gói     |


Ghi chú:

- Có thể giữ tên gói tiếng Anh: MLHUB Starter, MLHUB Growth, MLHUB Pro.
- Không dịch “Growth” thành “Tăng trưởng” trong tên gói nếu brand package muốn giữ.

### 18.2. Credit


| English         | Vietnamese chuẩn      |
| --------------- | --------------------- |
| Credits         | Tín dụng              |
| AI credits      | Tín dụng AI           |
| Credit pack     | Gói tín dụng          |
| Credits left    | Tín dụng còn lại      |
| Credits per run | Tín dụng mỗi lần chạy |
| Credit balance  | Số dư tín dụng        |


Không dùng:

- “tín chỉ” nếu không phải giáo dục.
- “điểm” nếu đang nói AI credit/billing.

### 18.3. Payment


| English                | Vietnamese chuẩn                              |
| ---------------------- | --------------------------------------------- |
| Payment                | Thanh toán                                    |
| Manual payment         | Thanh toán thủ công / Thanh toán chuyển khoản |
| Payment history        | Lịch sử thanh toán                            |
| Payment gateway        | Cổng thanh toán                               |
| Payment reference      | Mã tham chiếu thanh toán                      |
| Checkout               | Thanh toán / Checkout                         |
| Subscription           | Gói đăng ký                                   |
| Recurring subscription | Đăng ký định kỳ                               |
| Invoice                | Hóa đơn                                       |
| Payout                 | Chi trả                                       |


---

## 19. Quy tắc cho Support / Notifications / Email


| English        | Vietnamese chuẩn      |
| -------------- | --------------------- |
| Support        | Hỗ trợ                |
| Ticket         | Ticket / Phiếu hỗ trợ |
| Support ticket | Ticket hỗ trợ         |
| Comment        | Bình luận / Ghi chú   |
| Label          | Nhãn                  |
| Priority       | Mức ưu tiên           |
| Status         | Trạng thái            |
| Resolved       | Đã xử lý              |
| Open           | Đang mở               |
| Unread         | Chưa đọc              |
| Notification   | Thông báo             |
| Email template | Mẫu email             |
| Sender         | Người gửi             |
| Recipient      | Người nhận            |
| Subject        | Tiêu đề               |
| Body           | Nội dung              |


---

## 20. Quy tắc kiểm LocalBoost

### 20.1. Tìm LocalBoost trong bản dịch

Trước commit, phải tìm:

```bash
grep -R "LocalBoost" lang/vi.json lang/en.json

```

### 20.2. Cách xử lý


| Ngữ cảnh                               | Cách xử lý                              |
| -------------------------------------- | --------------------------------------- |
| User-facing value trong `vi.json`      | Đổi sang MLHUB                          |
| Source key tiếng Anh vẫn là LocalBoost | Có thể giữ key nếu code đang gọi key đó |
| Tài liệu kỹ thuật/source package       | Có thể giữ LocalBoost nếu cần           |
| UI production mới                      | Không thêm LocalBoost mới               |


---

## 21. Quy tắc kiểm “same-as-English”

Không phải value nào giống English cũng sai. Cần phân loại.

### 21.1. Được phép giống English

```text
MLHUB
MLHUB AI
Google
Meta
TikTok
WhatsApp
Stripe
PayPal
2Checkout
OpenAI
Gemini
Laravel
Livewire
Webhook
API
OAuth
CRM
AI
QR
A/B
2FA
S3
Redis

```

### 21.2. Không nên giống English

Nếu các key sau còn nguyên English trong `vi.json`, cần dịch:

```text
Add customer
Create campaign
No results found
Booking request
Coupon claimed
Feedback submitted
Lead form
Business profile
Dashboard overview
Plans & Billing
Payment history
Access control
Account security
Activity log

```

---

## 22. Quy tắc kiểm thuật ngữ cấm

Trước commit, tìm các cụm sau trong `vi.json`:

```text
Trọng âm
VIP
thẻ tag
trọng tải
bản sao AI
bản sao
bảo hiểm tài khoản
Chia sẻ tích cực
Căn cứ hoạt động
tăng cường hoạt động
mã mã giảm giá
lời nhắc
đường dẫn nhập

```

Không phải mọi kết quả đều sai 100%, nhưng phải review thủ công.

Cách sửa ưu tiên:


| Cụm nghi ngờ       | Sửa thường gặp                    |
| ------------------ | --------------------------------- |
| Trọng âm           | Màu nhấn / Điểm nhấn              |
| VIP                | Tiện ích mở rộng / Module mở rộng |
| thẻ tag            | Thẻ / Mục / Khối                  |
| trọng tải          | Dữ liệu gửi về / Dữ liệu trả về   |
| bản sao AI         | Nội dung AI                       |
| bảo hiểm tài khoản | Mức độ hoàn thiện tài khoản       |
| Chia sẻ tích cực   | Tỷ trọng đang hoạt động           |
| Căn cứ hoạt động   | Tệp đang hoạt động                |
| mã mã giảm giá     | Mã ưu đãi                         |
| lời nhắc           | Câu lệnh AI                       |
| đường dẫn nhập     | Quy trình nhập / Luồng nhập       |

> **Lưu ý case-sensitivity khi global replace:** Khi chạy global replace trong `vi.json`, phải kiểm cả biến thể chữ thường và chữ hoa đầu câu. Ví dụ: `thẻ tag` và `Thẻ tag`, `mã mã giảm giá` và `Mã mã giảm giá`, `tăng cường đánh giá` và `Tăng cường đánh giá`. Không chỉ replace lowercase.


---

## 23. Prompt audit i18n

Dùng prompt này cho Cursor/Codex trước khi sửa dịch.

```text
MLHUB | i18n Audit | CHƯA SỬA FILE

Bạn là Senior Laravel i18n Engineer + Vietnamese Product Copywriter cho MLHUB.

Đọc:
- ARCHITECTURE_I18N.md
- lang/en.json
- lang/vi.json

Mục tiêu:
Audit song ngữ en/vi, chưa sửa file.

Yêu cầu:
1. Đếm key en và vi.
2. Liệt kê key thiếu trong vi.
3. Liệt kê key dư trong vi.
4. Kiểm placeholder mismatch.
5. Kiểm HTML/tag mismatch.
6. Liệt kê value vi giống hệt en nhưng cần dịch.
7. Liệt kê LocalBoost user-facing còn sót.
8. Liệt kê glossary violation theo ARCHITECTURE_I18N.md.
9. Liệt kê top 100 dòng dịch máy/sai ngữ cảnh.
10. Chia batch sửa theo:
   - Onboarding + Business Profiles + Dashboard
   - Growth tools
   - CRM + Automation
   - AI Studio
   - Billing + Plans + Credits
   - Admin + System

Không sửa file.
Không đổi key.
Không commit/push/deploy.

Output:
- Tổng quan lỗi.
- Bảng lỗi P0/P1/P2.
- Kế hoạch sửa batch.
- Hỏi tôi gõ “Duyệt” trước khi sửa.

```

---

## 24. Prompt sửa dịch theo batch

```text
Duyệt. MLHUB | i18n Vietnamese Cleanup | APPLY PATCH

Đọc:
- ARCHITECTURE_I18N.md
- lang/en.json
- lang/vi.json

Chỉ sửa:
- lang/vi.json

Không sửa:
- lang/en.json trừ khi cần thêm key thiếu đã được duyệt.
- code nghiệp vụ.
- route/config.
- migration/schema.

Batch cần sửa:
<ghi rõ batch, ví dụ: Onboarding + Business Profiles + Dashboard>

Quy tắc:
1. Không đổi key.
2. Không xóa key.
3. Không phá placeholder.
4. Không phá HTML/entity.
5. Không dịch tên riêng.
6. Không dịch env/code/module/table/route.
7. Dịch theo glossary trong ARCHITECTURE_I18N.md.
8. Tiếng Việt phải tự nhiên, rõ, phù hợp hộ kinh doanh.
9. Admin/system technical terms có thể giữ tiếng Anh nếu dịch làm khó hiểu.
10. Không dùng thuật ngữ cấm.

Sau khi sửa:
- Đếm key.
- Kiểm missing/extra.
- Kiểm placeholder mismatch.
- Kiểm HTML mismatch.
- Kiểm LocalBoost user-facing.
- Kiểm thuật ngữ cấm.
- Báo ví dụ trước/sau.

Không commit/push/deploy.

```

---

## 25. Prompt review sau khi dịch

```text
MLHUB | i18n Review | KHÔNG CODE

Review diff vừa sửa trong lang/vi.json.

Đọc:
- ARCHITECTURE_I18N.md
- lang/en.json
- lang/vi.json
- git diff

Kiểm tra:
1. Có đổi key không?
2. Có xóa key không?
3. Có placeholder mismatch không?
4. Có HTML/tag mismatch không?
5. Có LocalBoost user-facing không?
6. Có thuật ngữ cấm không?
7. Có dịch sai ngữ cảnh MLHUB không?
8. Có phần admin/system bị Việt hóa quá mức làm khó hiểu không?
9. Có phần portal/user-facing còn tiếng Anh không cần thiết không?
10. Có câu nào dài, rối, giống dịch máy không?

Kết luận:
Approve / Approve with comments / Request changes.

Nếu Request changes:
- Chỉ liệt kê patch tối thiểu.
- Không tự sửa nếu chưa được duyệt.

```

---

## 26. Script kiểm tra đề xuất

Có thể tạo script riêng sau này, ví dụ:

```text
scripts/i18n_audit.php

```

hoặc dùng lệnh PHP/Python tạm trong local.

### 26.1. Logic kiểm tra bắt buộc

Script nên kiểm:

- Key count.
- Missing in vi.
- Extra in vi.
- Placeholder mismatch.
- HTML tag mismatch.
- Same-as-English values.
- Forbidden Vietnamese terms.
- LocalBoost leftovers.
- Invalid JSON.

### 26.2. Pseudo check placeholder

```php
preg_match_all('/:([A-Za-z_][A-Za-z0-9_]*)/', $source, $sourceMatches);
preg_match_all('/:([A-Za-z_][A-Za-z0-9_]*)/', $target, $targetMatches);

$sourcePlaceholders = array_unique($sourceMatches[0]);
$targetPlaceholders = array_unique($targetMatches[0]);

if ($sourcePlaceholders !== $targetPlaceholders) {
    // report mismatch
}

```

---

## 27. Checklist trước commit i18n

Trước khi commit thay đổi `lang/en.json` / `lang/vi.json`:

- JSON valid.
- Không đổi key ngoài kế hoạch.
- `en.json` và `vi.json` đã được đếm key.
- Key thiếu/dư đã được báo cáo.
- Không placeholder mismatch.
- Không HTML/tag mismatch.
- Không LocalBoost user-facing còn sót.
- Không thuật ngữ cấm chưa review.
- Không dịch `API Key`, `Client Secret`, env key, route, table, module.
- Portal user-facing không còn tiếng Anh không cần thiết trong batch đã sửa.
- Admin/system không bị dịch quá đà.
- Có ví dụ trước/sau.
- Không sửa code nghiệp vụ.
- Không migration.
- Không commit/push/deploy bởi AI.

---

## 28. Commit message gợi ý

Nếu chỉ tạo/cập nhật rule i18n:

```text
docs: add i18n translation guidelines

```

Nếu sửa batch dịch tiếng Việt:

```text
fix: improve Vietnamese translations for portal growth tools

```

Nếu đồng bộ key en/vi:

```text
fix: sync English and Vietnamese translation keys

```

Nếu vừa audit vừa sửa glossary:

```text
chore: standardize MLHUB i18n glossary

```

---

## 29. Quy tắc cuối cùng

Câu chốt:

> Không dịch `vi.json` như bản dịch máy. Mỗi value phải được hiểu theo ngữ cảnh MLHUB: hộ kinh doanh, local growth, QR, mã ưu đãi, đặt lịch, đánh giá, CRM, tự động hóa, AI và báo cáo tăng trưởng.

Nguyên tắc vận hành:

```text
Audit trước
→ Chốt glossary
→ Sửa theo batch nhỏ
→ Kiểm placeholder
→ Review thuật ngữ
→ Test màn hình thật
→ Commit

```

Không làm:

```text
Dịch lại toàn bộ vi.json một lần
→ Không kiểm placeholder
→ Không review màn hình
→ Commit thẳng

```

---

