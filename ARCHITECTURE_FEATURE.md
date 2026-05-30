# LocalBoost AI (Stackposts) — Bản đồ Tính năng & Độ sẵn sàng Production

Tài liệu đánh giá Hệ thống lõi và 6 module mở rộng, dùng làm **lộ trình chuẩn bị phát hành**. Mỗi mục gồm: Tổng quan tính năng → Luồng kỹ thuật → Ma trận độ sẵn sàng → Việc cần làm.

> **Quy ước trạng thái:**
> - ✅ **Ready** — chạy ổn, đã đủ logic, chỉ cần kiểm thử cuối.
> - 🟡 **Needs Testing** — logic đầy đủ nhưng cần test/QA thực tế trước khi mở.
> - 🟠 **Needs Audit** — có rủi ro/edge case cần rà soát trước production.
> - 🔴 **Contains Bugs / Incomplete** — thiếu hoặc nghi ngờ lỗi, cần xử lý.
>
> Đánh giá dựa trên quét tĩnh mã nguồn — **không thay cho QA chạy thật**.

---

## 0. Kiến trúc dùng chung của 5 "Growth Tool"

Review Booster, Booking, Coupons, Feedback, Lead Forms **không độc lập hoàn toàn** — chúng dùng chung một engine:

- **Model trung tâm:** `Modules\AppQRCampaigns\Models\QrCampaign` (bảng `lb_campaigns`) với cột `type` ∈ {`review`, `booking`, `coupon`, `feedback`, `lead`, `url`} + JSON `settings`.
- **Trang công khai:** `QrCampaignPublicController::show()` ghi nhận scan rồi rẽ nhánh theo `type` (hoặc dùng `LandingPage` đã publish).
- **Chốt chặn gói:** `App\Support\Plans\PlanLimitGuard` (`ensureCampaignCanBeCreated`, `ensureLandingPageCanBeCreated`...).
- **Thu khách hàng:** `Modules\AppCustomers\Support\CustomerUpserter::fromCampaign()` — mỗi submit công khai tạo/cập nhật khách.
- **Thông báo:** `App\Support\GrowthToolNotifier` (`leadCreated`, `couponClaimed`, `feedbackCreated`, `bookingCreated`).
- **Quản trị:** mỗi tool là một Livewire `*Index` trong portal, đăng ký sidebar nhóm `growth-tools`.

**Rủi ro production chung cho cả 5 tool (ưu tiên cao):**
- 🟠 **Endpoint submit công khai chưa thấy rate-limit / captcha** → nguy cơ spam, đầy bảng khách hàng và campaign. Cần thêm `throttle` middleware + (tùy chọn) tích hợp `AdminCaptcha`.
- 🟠 **`QrCampaignPublicController::recordScan()` ghi mọi lượt truy cập** (kể cả bot/crawler) → số liệu scan có thể bị thổi phồng. Cần lọc bot hoặc dùng hàng đợi.
- 🟡 Trang công khai dùng session để chống đếm trùng lượt xem landing page — kiểm tra hành vi khi tắt cookie.

---

## 1. Hệ thống lõi (Core: Users, Plans, Business, QR, Landing, Payments)

**Tổng quan:** Nền tảng SaaS đa người dùng: đăng ký/đăng nhập (Fortify + 2FA), gói cước & hạn mức, hồ sơ doanh nghiệp địa phương (`LocalBusiness`), engine campaign/QR, landing page, thanh toán đa cổng, team/workspace, tín dụng AI, theme/white-label, đa ngôn ngữ.

**Luồng kỹ thuật:**
- Auth: `app/Livewire/Auth/*` + `FortifyServiceProvider` + `Modules\AdminUser\Actions\Fortify\*`.
- Người dùng/quyền: `Modules\AdminUser\Models\User`, `EnsureAdminAccess`, `AdminPermissionCatalog`.
- Gói & hạn mức: `Modules\AdminPlans` (`AdminPlan`, `Pricing` facade), `App\Support\Plans\PlanLimitGuard`, middleware `ResolveUserPlanState`.
- Workspace: `Modules\AppTeams\Support\TeamWorkspaceAccess`.
- Thanh toán: `Modules\AppPayments` + 15 module `Payment*` + `AdminPaymentSubscriptions`.

**Ma trận độ sẵn sàng:**

| Hạng mục | Trạng thái | Ghi chú |
|----------|:----------:|---------|
| Auth + 2FA | ✅ Ready | Fortify chuẩn, có verify email |
| Plan & limit guard | ✅ Ready | `PlanLimitGuard` dò động module qua `class_exists`/`Schema::hasColumn` |
| Đa người dùng (user_id scoping) | 🟡 Needs Testing | Cần test chéo: user A không truy cập được dữ liệu user B |
| Team/Workspace permissions | 🟠 Needs Audit | Logic phân quyền phức tạp, có xử lý "legacy permissions" — cần test ma trận vai trò |
| Cổng thanh toán | 🟠 Needs Audit | Phải cấu hình + test webhook **thật** cho từng cổng dùng; còn lại nên tắt |
| Demo mode guard | 🟡 Needs Testing | Xác nhận tắt hẳn trên production |

**Việc cần làm:**
- [ ] Test cô lập dữ liệu giữa các tenant (IDOR): thử truy cập `findOrFail` bằng ID của user khác.
- [ ] Test end-to-end ít nhất 1 cổng thanh toán thật + xử lý webhook thất bại/hoàn tiền.
- [ ] Rà soát ma trận quyền team (owner/member, module bật/tắt).
- [ ] `.env` production: `APP_DEBUG=false`, queue worker, scheduler, `TRUSTED_PROXIES`, S3/mail thật.

---

## 2. Review Booster (`AppReviewBooster`)

**Tổng quan:** Tạo trang "xin đánh giá" thông minh: khách hài lòng (rating ≥ ngưỡng) được điều hướng tới Google/Facebook review; khách không hài lòng được chuyển sang form phản hồi riêng tư (chặn review xấu công khai).

**Luồng kỹ thuật:**
- Quản trị: `Livewire\ReviewBoosterIndex` (CRUD campaign `type=review`, thiết kế landing qua trait `ManagesGrowthToolPageDesign`).
- Lưu trữ: `QrCampaign` (settings: `google_review_url`, `positive_threshold`, `thank_you_message`...). Phản hồi riêng tư: model `ReviewFeedback` (`lb_review_feedbacks`).
- Công khai: view `appreviewbooster::public.show`; submit phản hồi qua `ReviewFeedbackController::store` (route `review-booster.feedback`).

**Ma trận độ sẵn sàng:**

| Hạng mục | Trạng thái |
|----------|:----------:|
| CRUD campaign + plan guard + slug duy nhất | ✅ Ready |
| Thiết kế landing page (template/màu/logo) | ✅ Ready |
| Định tuyến review tích cực/tiêu cực | 🟡 Needs Testing |
| Form phản hồi riêng tư công khai | 🟠 Needs Audit (spam) |
| Thống kê (public clicks vs private feedback) | 🟡 Needs Testing |

**Việc cần làm:**
- [ ] Test logic ngưỡng (`positive_threshold` 3–5) và điều hướng đúng Google/Facebook.
- [ ] Thêm rate-limit/captcha cho `review-booster.feedback`.
- [ ] Kiểm tra escape nội dung phản hồi khi hiển thị trong admin (chống XSS).

---

## 3. Booking (`AppBookingPages`)

**Tổng quan:** Trang đặt lịch công khai: cấu hình dịch vụ, giờ làm việc theo tuần, slot, buffer trước/sau, giới hạn số booking/slot.

**Luồng kỹ thuật:**
- Quản trị: `Livewire\BookingPageIndex`; model `Booking` (`lb_bookings`), `BookingService`.
- Logic slot: `Support\BookingAvailability` (chuẩn hóa giờ tuần, sinh slot, loại slot đã đầy, hỗ trợ định dạng giờ legacy).
- Công khai: `appbookingpages::public.show`; submit qua `BookingSubmissionController::store` — **có kiểm tra slot còn trống** trước khi tạo.

**Ma trận độ sẵn sàng:**

| Hạng mục | Trạng thái |
|----------|:----------:|
| Sinh slot + buffer + giới hạn/slot | ✅ Ready |
| Chống double-booking | 🟠 Needs Audit |
| Chuẩn hóa giờ legacy | ✅ Ready |
| Submit công khai + validate | 🟡 Needs Testing |
| Múi giờ (timezone) | 🟠 Needs Audit |

**Việc cần làm:**
- [ ] **Race condition double-booking:** hai người đặt cùng slot đồng thời — kiểm tra `removeBookedSlots` có chịu được tải đồng thời; cân nhắc khóa DB/unique constraint.
- [ ] Xác minh slot được tính theo **múi giờ doanh nghiệp**, không phải giờ server.
- [ ] Test ngày đóng cửa, qua nửa đêm, dịch vụ dùng giờ riêng vs giờ doanh nghiệp.
- [ ] Thêm rate-limit cho endpoint đặt lịch.

---

## 4. Coupons (`AppCouponCampaigns`)

**Tổng quan:** Chiến dịch mã giảm giá: khách điền thông tin để nhận mã duy nhất; hỗ trợ hạn dùng, ngày hết hạn, giới hạn lượt phát.

**Luồng kỹ thuật:**
- Quản trị: `Livewire\CouponCampaignIndex`; model `CouponRedemption` (`lb_coupon_redemptions`).
- Công khai: `appcouponcampaigns::public.show`; nhận mã qua `CouponClaimController::store`:
  - Kiểm tra `expiry_date`, kiểm tra `usage_limit`, sinh mã duy nhất (`uniqueCode` với prefix + random, loop chống trùng).

**Ma trận độ sẵn sàng:**

| Hạng mục | Trạng thái |
|----------|:----------:|
| Sinh mã duy nhất | ✅ Ready |
| Kiểm tra hết hạn + giới hạn lượt | ✅ Ready |
| Submit công khai | 🟠 Needs Audit (spam) |
| Theo dõi/đối soát mã đã dùng | 🔴 Incomplete? |

**Việc cần làm:**
- [ ] Kiểm tra race condition khi nhiều người nhận cùng lúc gần `usage_limit` (đếm rồi tạo — có thể vượt nhẹ).
- [ ] Xác nhận có luồng **đánh dấu mã đã sử dụng/đổi thưởng** (redeem) tại quầy, không chỉ "đã phát".
- [ ] Rate-limit + captcha cho `CouponClaimController`.

---

## 5. Feedback (`AppFeedbackForms`)

**Tổng quan:** Form thu thập phản hồi riêng tư (rating + tin nhắn + liên hệ tùy chọn), tách biệt review công khai.

**Luồng kỹ thuật:**
- Quản trị: `Livewire\FeedbackFormIndex`; model `FeedbackResponse` (`lb_feedback_responses`).
- Công khai: `appfeedbackforms::public.show`; submit qua `FeedbackSubmissionController::store` với rule động (`rating_required`, `contact_required`).

**Ma trận độ sẵn sàng:**

| Hạng mục | Trạng thái |
|----------|:----------:|
| Form + rule validate động | 🟡 Needs Testing |
| Bắt buộc liên hệ (phone/email) | 🟠 Needs Audit |
| Submit công khai | 🟠 Needs Audit (spam) |

**Việc cần làm:**
- [ ] Rà soát rule liên hệ: khi `contact_required` bật, logic `required_without` giữa phone/email cần test kỹ cả 4 tổ hợp (có/không phone × email).
- [ ] Rate-limit/captcha + chống XSS khi hiển thị `message` trong admin.
- [ ] Kiểm tra xuất/đọc phản hồi trong dashboard.

---

## 6. Lead Generation (`AppLeadForms`)

**Tổng quan:** Form thu thập lead (tên, sđt, email, lời nhắn), tự đẩy vào CRM khách hàng + thông báo cho chủ doanh nghiệp.

**Luồng kỹ thuật:**
- Quản trị: `Livewire\LeadFormIndex`; model `LeadSubmission` (`lb_lead_submissions`).
- Công khai: `appleadforms::public.show`; submit qua `LeadSubmissionController::store` → `CustomerUpserter` + `GrowthToolNotifier::leadCreated`.

**Ma trận độ sẵn sàng:**

| Hạng mục | Trạng thái |
|----------|:----------:|
| Submit + lưu lead | ✅ Ready |
| Đẩy vào CRM (`CustomerUpserter`) | 🟡 Needs Testing |
| Thông báo chủ doanh nghiệp | 🟡 Needs Testing |
| Submit công khai | 🟠 Needs Audit (spam) |

**Việc cần làm:**
- [ ] Là form ít rào cản nhất → **bắt buộc** rate-limit/captcha; cân nhắc honeypot.
- [ ] Test `GrowthToolNotifier` gửi đúng kênh (email/notification) và không chặn response (nên qua queue).
- [ ] Xác nhận lead trùng được gộp đúng trong CRM.

---

## 7. AI Campaign (`AppAIContent` + engine `AppAIStudio`)

**Tổng quan:** "Content Writer" sinh nội dung marketing địa phương bằng AI (review request, coupon message, booking reminder, social post, WhatsApp/SMS...), có tinh chỉnh (ngắn hơn/dài hơn/thân thiện), lưu lịch sử, tính credit.

**Luồng kỹ thuật:**
- Livewire `AppAIContent\Livewire\AIContentIndex` (trait `AIStudioAccess`).
- Engine: `Modules\AppAIStudio\Support\AiContentStudioService`; lịch sử: `AIPromptHistory` (scope theo `workspaceOwnerUserId`).
- Credit: `credit_service()->ensureCanConsume()` trước, `consume_credits()` sau khi thành công.
- **Có fallback mạnh:** lỗi AI → sinh nội dung dự phòng cục bộ (`fallbackContent`) thay vì vỡ UI.

**Ma trận độ sẵn sàng:**

| Hạng mục | Trạng thái |
|----------|:----------:|
| Sinh nội dung + fallback | ✅ Ready |
| Tính/charge credit | 🟡 Needs Testing |
| Lưu & nạp lại lịch sử | ✅ Ready |
| Feature gate + plan check | ✅ Ready |
| Đa ngôn ngữ (normalize language) | ✅ Ready |

**Việc cần làm:**
- [ ] Cấu hình provider AI thật (`laravel/ai`/Prism) + khóa API qua `.env`/Admin → AI; test khi key sai/hết quota.
- [ ] Xác minh credit **chỉ trừ khi AI thành công** (đang đúng theo code) và hiển thị `creditPreview` khớp thực tế.
- [ ] Test giới hạn rate khi gọi LLM hàng loạt (makeShorter/Longer gọi `generate` lại).
- [ ] Lưu ý: AI Campaign phụ thuộc `AppAIStudio` — đảm bảo module này được cài/bật.

---

## 8. QR Code (`AppQRCampaigns`)

**Tổng quan:** Engine nền cho mọi growth tool + công cụ QR độc lập: tạo QR trỏ tới landing/URL, render SVG/PNG tải về, theo dõi lượt scan và phân tích.

**Luồng kỹ thuật:**
- Quản trị: `Livewire\QrCampaignIndex`, `Livewire\QrCampaignAnalytics`; model `QrCampaign`, `QrScan` (`lb_qr_scans`).
- Công khai: `QrCampaignPublicController` (`show` rẽ nhánh theo type, `qr`/`png` render qua `BusinessQrRenderer`).
- URL công khai hỗ trợ custom domain qua `AppCustomDomain` (dò động `class_exists`).

**Ma trận độ sẵn sàng:**

| Hạng mục | Trạng thái |
|----------|:----------:|
| Tạo QR + render SVG/PNG | ✅ Ready |
| Rẽ nhánh public theo type | ✅ Ready |
| Ghi nhận scan + analytics | 🟠 Needs Audit |
| Custom domain | 🟡 Needs Testing |

**Việc cần làm:**
- [ ] **Lọc bot/crawler trong `recordScan`** để analytics không bị thổi phồng; cân nhắc đẩy ghi scan qua queue để giảm độ trễ redirect.
- [ ] Test render PNG (phụ thuộc thư viện ảnh) trên môi trường production.
- [ ] Test custom domain end-to-end nếu bật `AppCustomDomain`.

---

## 9. Tổng kết ưu tiên trước khi mở cho người dùng thật

**P0 — Bảo mật/lạm dụng (chặn phát hành):**
- [ ] Rate-limit + captcha/honeypot cho **tất cả** endpoint submit công khai (Review feedback, Booking, Coupon, Feedback, Lead).
- [ ] Test cô lập tenant (IDOR) trên mọi `findOrFail`/truy vấn dữ liệu người dùng.
- [ ] Tắt demo mode; `APP_DEBUG=false`; che giấu thông tin lỗi.

**P1 — Tính đúng đắn nghiệp vụ:**
- [ ] ⚠️ **`MAIL_MAILER=log`** (theo `.env.example`) → mọi thông báo email (Lead/Feedback/Booking qua `GrowthToolNotifier`, welcome user, reset mật khẩu) **không gửi thật**. Đổi sang SMTP và test trước khi mở.
- [ ] Race condition: double-booking (Booking) và vượt `usage_limit` (Coupon).
- [ ] Webhook thanh toán thật + xử lý subscription hết hạn/hoàn tiền.
- [ ] Credit AI trừ đúng; provider AI cấu hình thật.
- [ ] Múi giờ cho Booking (lưu ý `APP_TIMEZONE=Asia/Ho_Chi_Minh`).

**P2 — Vận hành & chất lượng dữ liệu:**
- [ ] Lọc bot khỏi QR scan analytics; đẩy notify/scan qua queue.
- [ ] Chống XSS khi hiển thị nội dung do khách nhập (feedback/review/lead) trong admin.
- [ ] Cấu hình queue worker (driver `database`) + scheduler + mail SMTP; cân nhắc S3 (đang `FILESYSTEM_DISK=public`).
- [ ] Điền `DB_HOST`/`DB_PASSWORD` thật; migrate đủ bảng `sessions`/`jobs`/`cache`.
- [ ] `php artisan migrate --force` rồi `config:cache route:cache view:cache`.

> Khi sửa các mục trên: bám đúng phong cách module gốc (xem `.cursorrules`), sửa **surgical**, và scope mọi truy vấn theo `auth()->id()`/workspace owner.
