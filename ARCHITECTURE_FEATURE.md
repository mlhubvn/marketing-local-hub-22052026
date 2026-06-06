# MLHUB AI — Bản đồ Tính năng & Độ sẵn sàng Production

Tài liệu đánh giá **hệ thống lõi**, **5 growth tool** (engine `lb_campaigns`), **AI Campaign**, và **4 module portal mở rộng** sau cập nhật upstream (CRM, Email automation, Loyalty/Referral, Báo cáo). Dùng làm **lộ trình chuẩn bị phát hành**. Mỗi mục gồm: Tổng quan → Luồng kỹ thuật → Ma trận độ sẵn sàng → Việc cần làm.

> **Quy ước trạng thái:**
>
> - ✅ **Ready** — chạy ổn, đã đủ logic, chỉ cần kiểm thử cuối.
> - 🟡 **Needs Testing** — logic đầy đủ nhưng cần test/QA thực tế trước khi mở.
> - 🟠 **Needs Audit** — có rủi ro/edge case cần rà soát trước production.
> - 🔴 **Contains Bugs / Incomplete** — thiếu hoặc nghi ngờ lỗi, cần xử lý.
>
> Đánh giá dựa trên quét tĩnh mã nguồn — **không thay cho QA chạy thật**.
>
> Quy ước maintainability: với các chỉnh sửa tính năng, ưu tiên cập nhật trực tiếp module/file hiện có để giữ diff gọn khi so sánh upstream. Chỉ tạo file mới cho data seed/doc khi thật sự cần.

---

## 0. Kiến trúc dùng chung của 5 "Growth Tool"

Review Booster, Booking, Coupons, Feedback, Lead Forms **không độc lập hoàn toàn** — chúng dùng chung một engine:

- **Model trung tâm:** `Modules\AppQRCampaigns\Models\QrCampaign` (bảng `lb_campaigns`) với cột `type` ∈ {`review`, `booking`, `coupon`, `feedback`, `lead`, `url`} + JSON `settings`.
- **Trang công khai:** `QrCampaignPublicController::show()` ghi nhận scan rồi rẽ nhánh theo `type` (hoặc dùng `LandingPage` đã publish).
- **Chốt chặn gói:** `App\Support\Plans\PlanLimitGuard` (`ensureCampaignCanBeCreated`, `ensureLandingPageCanBeCreated`...).
- **Thu khách hàng:** `Modules\AppCustomers\Support\CustomerUpserter::fromCampaign()` — mỗi submit công khai tạo/cập nhật khách.
- **Thông báo:** `App\Support\GrowthToolNotifier` (`leadCreated`, `couponClaimed`, `feedbackCreated`, `bookingCreated`).
- **Quản trị:** mỗi tool là một Livewire `*Index` trong portal, đăng ký sidebar nhóm `growth-tools`.

**Rủi ro production chung cho cả 5 tool:**

- ✅ **Rate-limit ĐÃ THÊM** (`throttle:10,1`) trên cả 5 route POST công khai — chặn flood cơ bản (10 lần/phút/IP).
- 🟠 **Captcha CHƯA gắn vào 5 form công khai** — captcha (Cloudflare Turnstile mặc định + reCAPTCHA v2) hiện mới bảo vệ trang auth. Đây là lớp chống bot mạnh hơn rate-limit; xem **§14 Backlog bảo mật** để biết cách gắn (đã có helper sẵn).
- 🟠 `**QrCampaignPublicController::recordScan()` ghi mọi lượt truy cập** (kể cả bot/crawler) → số liệu scan có thể bị thổi phồng. Cần lọc bot hoặc dùng hàng đợi.
- 🟡 Trang công khai dùng session để chống đếm trùng lượt xem landing page — kiểm tra hành vi khi tắt cookie.

---

## 1. Hệ thống lõi (Core: Users, Plans, Business, QR, Landing, Payments)

**Bootstrap production:** không còn demo/faker. DB trống → `php artisan mlhub:install` (module `CustomMLHUB`) tạo super admin từ `MLHUB_FIRST_USER_*`, seed gói/plan/AI templates/site options VN. Portal bắt đầu **trống** — dữ liệu growth tool do khách tự tạo. Xem `.env.example`, `ARCHITECTURE_PROMPT.md` §4.1.

**Tổng quan:** Nền tảng SaaS đa người dùng: đăng ký/đăng nhập (Fortify + 2FA), gói cước & hạn mức, hồ sơ doanh nghiệp địa phương (`LocalBusiness`), engine campaign/QR, landing page, thanh toán đa cổng, team/workspace, tín dụng AI, theme/white-label, đa ngôn ngữ.

**Luồng kỹ thuật:**

- Auth: `app/Livewire/Auth/*` + `FortifyServiceProvider` + `Modules\AdminUser\Actions\Fortify\*`.
- Người dùng/quyền: `Modules\AdminUser\Models\User`, `EnsureAdminAccess`, `AdminPermissionCatalog`.
- Gói & hạn mức: `Modules\AdminPlans` (`AdminPlan`, `Pricing` facade), `App\Support\Plans\PlanLimitGuard`, middleware `ResolveUserPlanState`.
- Workspace: `Modules\AppTeams\Support\TeamWorkspaceAccess`.
- Thanh toán: `Modules\AppPayments` + **14** module `Payment*` + `AdminPaymentSubscriptions`.

**Ma trận độ sẵn sàng:**


| Hạng mục                        | Trạng thái       | Ghi chú                                                                             |
| ------------------------------- | ---------------- | ----------------------------------------------------------------------------------- |
| Auth + 2FA                      | ✅ Ready          | Fortify chuẩn, có verify email                                                      |
| Plan & limit guard              | ✅ Ready          | `PlanLimitGuard` dò động module qua `class_exists`/`Schema::hasColumn`              |
| Đa người dùng (user_id scoping) | 🟡 Needs Testing | Cần test chéo: user A không truy cập được dữ liệu user B                            |
| Team/Workspace permissions      | 🟠 Needs Audit   | Logic phân quyền phức tạp, có xử lý "legacy permissions" — cần test ma trận vai trò |
| Cổng thanh toán                 | 🟠 Needs Audit   | Phải cấu hình + test webhook **thật** cho từng cổng dùng; còn lại nên tắt           |
| Demo mode guard                 | 🟡 Needs Testing | Xác nhận tắt hẳn trên production                                                    |


**Việc cần làm:**

- Test cô lập dữ liệu giữa các tenant (IDOR): thử truy cập `findOrFail` bằng ID của user khác.
- Test end-to-end ít nhất 1 cổng thanh toán thật + xử lý webhook thất bại/hoàn tiền.
- Rà soát ma trận quyền team (owner/member, module bật/tắt).
- `.env` production: `APP_DEBUG=false`, `APP_URL=https://mlhub.vn`, `SESSION_DOMAIN=.mlhub.vn`, `TRUSTED_PROXIES`, queue worker (một nguồn), S3/mail thật; sau deploy user **Ctrl+F5** nếu Livewire 419.

---

## 2. Review Booster (`AppReviewBooster`)

**Tổng quan:** Tạo trang "xin đánh giá" thông minh: khách hài lòng (rating ≥ ngưỡng) được điều hướng tới Google/Facebook review; khách không hài lòng được chuyển sang form phản hồi riêng tư (chặn review xấu công khai).

**Luồng kỹ thuật:**

- Quản trị: `Livewire\ReviewBoosterIndex` (CRUD campaign `type=review`, thiết kế landing qua trait `ManagesGrowthToolPageDesign`).
- Lưu trữ: `QrCampaign` (settings: `google_review_url`, `positive_threshold`, `thank_you_message`...). Phản hồi riêng tư: model `ReviewFeedback` (`lb_review_feedbacks`).
- Công khai: view `appreviewbooster::public.show`; submit phản hồi qua `ReviewFeedbackController::store` (route `review-booster.feedback`).

**Ma trận độ sẵn sàng:**


| Hạng mục                                     | Trạng thái            |
| -------------------------------------------- | --------------------- |
| CRUD campaign + plan guard + slug duy nhất   | ✅ Ready               |
| Thiết kế landing page (template/màu/logo)    | ✅ Ready               |
| Định tuyến review tích cực/tiêu cực          | 🟡 Needs Testing      |
| Form phản hồi riêng tư công khai             | 🟠 Needs Audit (spam) |
| Thống kê (public clicks vs private feedback) | 🟡 Needs Testing      |


**Việc cần làm:**

- Test logic ngưỡng (`positive_threshold` 3–5) và điều hướng đúng Google/Facebook.
- Thêm rate-limit/captcha cho `review-booster.feedback`.
- Kiểm tra escape nội dung phản hồi khi hiển thị trong admin (chống XSS).

---

## 3. Booking (`AppBookingPages`)

**Tổng quan:** Trang đặt lịch công khai: cấu hình dịch vụ, giờ làm việc theo tuần, slot, buffer trước/sau, giới hạn số booking/slot.

**Luồng kỹ thuật:**

- Quản trị: `Livewire\BookingPageIndex`; model `Booking` (`lb_bookings`), `BookingService`.
- Logic slot: `Support\BookingAvailability` (chuẩn hóa giờ tuần, sinh slot, loại slot đã đầy, hỗ trợ định dạng giờ legacy).
- Công khai: `appbookingpages::public.show`; submit qua `BookingSubmissionController::store` — **có kiểm tra slot còn trống** trước khi tạo.

**Ma trận độ sẵn sàng:**


| Hạng mục                           | Trạng thái       |
| ---------------------------------- | ---------------- |
| Sinh slot + buffer + giới hạn/slot | ✅ Ready          |
| Chống double-booking               | 🟠 Needs Audit   |
| Chuẩn hóa giờ legacy               | ✅ Ready          |
| Submit công khai + validate        | 🟡 Needs Testing |
| Múi giờ (timezone)                 | 🟠 Needs Audit   |


**Việc cần làm:**

- **Race condition double-booking:** hai người đặt cùng slot đồng thời — kiểm tra `removeBookedSlots` có chịu được tải đồng thời; cân nhắc khóa DB/unique constraint.
- Xác minh slot được tính theo **múi giờ doanh nghiệp**, không phải giờ server.
- Test ngày đóng cửa, qua nửa đêm, dịch vụ dùng giờ riêng vs giờ doanh nghiệp.
- Thêm rate-limit cho endpoint đặt lịch.

---

## 4. Coupons (`AppCouponCampaigns`)

**Tổng quan:** Chiến dịch mã giảm giá: khách điền thông tin để nhận mã duy nhất; hỗ trợ hạn dùng, ngày hết hạn, giới hạn lượt phát.

**Luồng kỹ thuật:**

- Quản trị: `Livewire\CouponCampaignIndex`; model `CouponRedemption` (`lb_coupon_redemptions`).
- Công khai: `appcouponcampaigns::public.show`; nhận mã qua `CouponClaimController::store`:
  - Kiểm tra `expiry_date`, kiểm tra `usage_limit`, sinh mã duy nhất (`uniqueCode` với prefix + random, loop chống trùng).

**Ma trận độ sẵn sàng:**


| Hạng mục                         | Trạng thái            |
| -------------------------------- | --------------------- |
| Sinh mã duy nhất                 | ✅ Ready               |
| Kiểm tra hết hạn + giới hạn lượt | ✅ Ready               |
| Submit công khai                 | 🟠 Needs Audit (spam) |
| Theo dõi/đối soát mã đã dùng     | 🔴 Incomplete?        |


**Việc cần làm:**

- Kiểm tra race condition khi nhiều người nhận cùng lúc gần `usage_limit` (đếm rồi tạo — có thể vượt nhẹ).
- Xác nhận có luồng **đánh dấu mã đã sử dụng/đổi thưởng** (redeem) tại quầy, không chỉ "đã phát".
- Rate-limit + captcha cho `CouponClaimController`.

---

## 5. Feedback (`AppFeedbackForms`)

**Tổng quan:** Form thu thập phản hồi riêng tư (rating + tin nhắn + liên hệ tùy chọn), tách biệt review công khai.

**Luồng kỹ thuật:**

- Quản trị: `Livewire\FeedbackFormIndex`; model `FeedbackResponse` (`lb_feedback_responses`).
- Công khai: `appfeedbackforms::public.show`; submit qua `FeedbackSubmissionController::store` với rule động (`rating_required`, `contact_required`).

**Ma trận độ sẵn sàng:**


| Hạng mục                       | Trạng thái            |
| ------------------------------ | --------------------- |
| Form + rule validate động      | 🟡 Needs Testing      |
| Bắt buộc liên hệ (phone/email) | 🟠 Needs Audit        |
| Submit công khai               | 🟠 Needs Audit (spam) |


**Việc cần làm:**

- Rà soát rule liên hệ: khi `contact_required` bật, logic `required_without` giữa phone/email cần test kỹ cả 4 tổ hợp (có/không phone × email).
- Rate-limit/captcha + chống XSS khi hiển thị `message` trong admin.
- Kiểm tra xuất/đọc phản hồi trong dashboard.

---

## 6. Lead Generation (`AppLeadForms`)

**Tổng quan:** Form thu thập lead (tên, sđt, email, lời nhắn), tự đẩy vào CRM khách hàng + thông báo cho chủ doanh nghiệp.

**Luồng kỹ thuật:**

- Quản trị: `Livewire\LeadFormIndex`; model `LeadSubmission` (`lb_lead_submissions`).
- Công khai: `appleadforms::public.show`; submit qua `LeadSubmissionController::store` → `CustomerUpserter` + `GrowthToolNotifier::leadCreated`.

**Ma trận độ sẵn sàng:**


| Hạng mục                         | Trạng thái            |
| -------------------------------- | --------------------- |
| Submit + lưu lead                | ✅ Ready               |
| Đẩy vào CRM (`CustomerUpserter`) | 🟡 Needs Testing      |
| Thông báo chủ doanh nghiệp       | 🟡 Needs Testing      |
| Submit công khai                 | 🟠 Needs Audit (spam) |


**Việc cần làm:**

- Là form ít rào cản nhất → **bắt buộc** rate-limit/captcha; cân nhắc honeypot.
- Test `GrowthToolNotifier` gửi đúng kênh (email/notification) và không chặn response (nên qua queue).
- Xác nhận lead trùng được gộp đúng trong CRM.

---

## 7. AI Campaign (`AppAIContent` + engine `AppAIStudio`)

**Tổng quan:** "Content Writer" sinh nội dung marketing địa phương bằng AI (review request, coupon message, booking reminder, social post, WhatsApp/SMS...), có tinh chỉnh (ngắn hơn/dài hơn/thân thiện), lưu lịch sử, tính credit.

**Luồng kỹ thuật:**

- Livewire `AppAIContent\Livewire\AIContentIndex` (trait `AIStudioAccess`).
- Engine: `Modules\AppAIStudio\Support\AiContentStudioService`; lịch sử: `AIPromptHistory` (scope theo `workspaceOwnerUserId`).
- Credit: `credit_service()->ensureCanConsume()` trước, `consume_credits()` sau khi thành công.
- **Có fallback mạnh:** lỗi AI → sinh nội dung dự phòng cục bộ (`fallbackContent`) thay vì vỡ UI.

**Ma trận độ sẵn sàng:**


| Hạng mục                         | Trạng thái       |
| -------------------------------- | ---------------- |
| Sinh nội dung + fallback         | ✅ Ready          |
| Tính/charge credit               | 🟡 Needs Testing |
| Lưu & nạp lại lịch sử            | ✅ Ready          |
| Feature gate + plan check        | ✅ Ready          |
| Đa ngôn ngữ (normalize language) | ✅ Ready          |


**Việc cần làm:**

- Cấu hình provider AI thật (`laravel/ai`/Prism) + khóa API qua `.env`/Admin → AI; test khi key sai/hết quota.
- Xác minh credit **chỉ trừ khi AI thành công** (đang đúng theo code) và hiển thị `creditPreview` khớp thực tế.
- Test giới hạn rate khi gọi LLM hàng loạt (makeShorter/Longer gọi `generate` lại).
- Lưu ý: AI Campaign phụ thuộc `AppAIStudio` — đảm bảo module này được cài/bật.

---

## 8. QR Code (`AppQRCampaigns`)

**Tổng quan:** Engine nền cho mọi growth tool + công cụ QR độc lập: tạo QR trỏ tới landing/URL, render SVG/PNG tải về, theo dõi lượt scan và phân tích.

**Luồng kỹ thuật:**

- Quản trị: `Livewire\QrCampaignIndex`, `Livewire\QrCampaignAnalytics`; model `QrCampaign`, `QrScan` (`lb_qr_scans`).
- Công khai: `QrCampaignPublicController` (`show` rẽ nhánh theo type, `qr`/`png` render qua `BusinessQrRenderer`).
- URL công khai hỗ trợ custom domain qua `AppCustomDomain` (dò động `class_exists`).

**Ma trận độ sẵn sàng:**


| Hạng mục                  | Trạng thái       |
| ------------------------- | ---------------- |
| Tạo QR + render SVG/PNG   | ✅ Ready          |
| Rẽ nhánh public theo type | ✅ Ready          |
| Ghi nhận scan + analytics | 🟠 Needs Audit   |
| Custom domain             | 🟡 Needs Testing |


**Việc cần làm:**

- **Lọc bot/crawler trong `recordScan`** để analytics không bị thổi phồng; cân nhắc đẩy ghi scan qua queue để giảm độ trễ redirect.
- Test render PNG (phụ thuộc thư viện ảnh) trên môi trường production.
- Test custom domain end-to-end nếu bật `AppCustomDomain`.

---

## 9. Advanced CRM (`AppAdvancedCustomerCrm`)

**Tổng quan:** CRM nâng cao trên `AppCustomers`: tag, phân khúc, ghi chú, task, activity timeline, điểm/LTV, automation, merge khách.

**Luồng kỹ thuật:**

- Portal: prefix `portal/crm`; plan feature `advanced_crm`.
- Bảng: mở rộng `lb_customers` + `lb_customer_*`, `lb_crm_automations`, `lb_crm_automation_logs`, `lb_crm_automation_jobs`, `lb_customer_merge_logs` (migration module).
- Artisan: `crm:process-automations`, `crm:cleanup-activities`, `crm:lifecycle`, `advanced-crm:seed-demo`.

**Ma trận độ sẵn sàng:**


| Hạng mục               | Trạng thái                                  |
| ---------------------- | ------------------------------------------- |
| CRUD + scope `user_id` | 🟡 Needs Testing                            |
| Automation queue/cron  | 🟠 Needs Audit                              |
| Demo seed              | 🟡 Needs Testing (`advanced-crm:seed-demo`) |


**Việc cần làm:**

- Bật scheduled task Coolify cho các lệnh `crm:*` nếu dùng automation.
- Test merge khách + IDOR trên task/note/tag.
- Production: không còn Admin Faker — dùng `advanced-crm:seed-demo` trên staging hoặc dữ liệu thật từ portal.

---

## 10. Email Automation (`AppEmailAutomation`)

**Tổng quan:** Rule gửi email theo sự kiện (template + automation + log).

**Luồng kỹ thuật:**

- Portal: `portal/email-automation`; plan feature `email_automation`.
- Bảng: `lb_email_templates`, `lb_email_automations`, `lb_email_automation_logs`.
- Phụ thuộc SMTP (`MAIL_*` trên Coolify) và queue worker.

**Ma trận độ sẵn sàng:**


| Hạng mục                           | Trạng thái       |
| ---------------------------------- | ---------------- |
| CRUD automation/template           | 🟡 Needs Testing |
| Gửi mail thật + queue              | 🟠 Needs Audit   |
| Plan limit `max_email_automations` | 🟡 Needs Testing |


**Việc cần làm:**

- Test trigger event + log khi SMTP/queue lỗi.
- Demo: `MLHUBDemoExtrasSeeder` (nếu có) — không nằm trong Admin Faker chính.

---

## 11. Loyalty & Referral (`AppLoyaltyStampCards`)

**Tổng quan:** Thẻ tích điểm (stamp card) + chiến dịch giới thiệu; **không** dùng engine `QrCampaign`.

**Luồng kỹ thuật:**

- Portal: `portal/loyalty-cards`.
- Public: `/loyalty/{slug}`, `/referral/{slug}`, `/r/{code}` — POST stamp/link/convert.
- Bảng: `lb_loyalty_*`, `lb_referral_*`; provider marketplace: `bootstrap/providers.marketplace.php`.

**Ma trận độ sẵn sàng:**


| Hạng mục                           | Trạng thái                                |
| ---------------------------------- | ----------------------------------------- |
| CRUD thẻ + referral                | 🟡 Needs Testing                          |
| Public stamp/convert               | 🟠 Needs Audit (spam — **chưa throttle**) |
| Plan feature `loyalty_stamp_cards` | 🟡 Needs Testing                          |


**Việc cần làm:**

- Thêm `throttle:10,1` + captcha cho POST public (`loyalty-cards.stamp`, `referral-campaigns.link`, `referral-links.convert`).
- Test anti-abuse trong `settings` thẻ.

---

## 12. Local Analytics / Reports (`AppLocalAnalytics`)

**Tổng quan:** Dashboard báo cáo tổng hợp (QR, landing, growth metrics) — **không** thêm bảng mới; đọc dữ liệu module khác.

**Luồng kỹ thuật:**

- Portal: `portal/reports`; widget admin dashboard (snapshot).

**Ma trận độ sẵn sàng:**


| Hạng mục                      | Trạng thái       |
| ----------------------------- | ---------------- |
| Tổng hợp số liệu              | 🟡 Needs Testing |
| Đồng bộ sau deploy module mới | 🟡 Needs Testing |


**Việc cần làm:**

- So sánh số với từng module nguồn (QR analytics, campaigns).
- Kiểm tra performance truy vấn trên tài khoản nhiều dữ liệu.

---

## 13. Tổng kết ưu tiên trước khi mở cho người dùng thật

**P0 — Bảo mật/lạm dụng (chặn phát hành):**

- ✅ **SMTP qua Emailit** đã cấu hình (Coolify env) — welcome + verify email chạy thật.
- ✅ **Rate-limit `throttle:10,1`** trên cả 5 endpoint submit công khai.
- (Còn lại) Gắn **captcha** vào 5 form công khai để chống bot mạnh hơn — xem §14.
- Test cô lập tenant (IDOR) trên mọi `findOrFail`/truy vấn dữ liệu người dùng.
- ✅ Tắt demo mode (`APP_DEMO=false`); `APP_DEBUG=false`.

**P1 — Tính đúng đắn nghiệp vụ:**

- Race condition: double-booking (Booking) và vượt `usage_limit` (Coupon).
- Webhook thanh toán thật + xử lý subscription hết hạn/hoàn tiền.
- Credit AI trừ đúng; provider AI cấu hình thật.
- Múi giờ cho Booking (lưu ý `APP_TIMEZONE=Asia/Ho_Chi_Minh`).

**P2 — Vận hành & chất lượng dữ liệu:**

- Lọc bot khỏi QR scan analytics; đẩy notify/scan qua queue.
- Chống XSS khi hiển thị nội dung do khách nhập (feedback/review/lead) trong admin.
- ✅ Session/cache/queue dùng **Redis**; mail SMTP đã cấu hình. Cần đảm bảo queue worker (`queue:work redis`) + Redis luôn sống; cân nhắc S3 (đang `FILESYSTEM_DISK=public`).
- Điền `DB_HOST`/`DB_PASSWORD` + `REDIS_PASSWORD` thật (Coolify env); migrate `failed_jobs`/`job_batches`.
- `php artisan migrate --force` rồi `config:cache route:cache view:cache`.

> Khi sửa các mục trên: bám đúng phong cách module gốc (xem `.cursorrules`), sửa **surgical**, và scope mọi truy vấn theo `auth()->id()`/workspace owner.

---

## 14. Backlog bảo mật (giải thích dễ hiểu — làm dần)

Phần này liệt kê các việc bảo mật còn lại bằng ngôn ngữ dễ hiểu, kèm "phải làm gì". Không gấp như P0 nhưng nên xử lý trước khi mở rộng nhiều khách.

### 14.1 Gắn Captcha vào 5 form công khai 🟠

- **Vấn đề:** Hiện form Booking/Coupon/Feedback/Lead/Review công khai mới có rate-limit (chặn theo số lần/phút). Bot tinh vi đổi IP vẫn có thể spam. Captcha (bạn đã bật Cloudflare Turnstile) chặn tốt hơn nhưng **chưa được gắn vào các form này** — mới gắn ở trang đăng nhập/đăng ký.
- **Đã có sẵn gì:** module `AdminCaptcha` cung cấp helper dùng lại ngay:
  - `captcha_render()` — in widget captcha vào form (Blade).
  - `with_captcha_validation([...])` / `captcha_verify(request())` — kiểm tra ở phía server.
- **Phải làm (mỗi form 2 bước nhỏ):**
  1. Thêm `{!! captcha_render() !!}` vào view công khai (`*::public.show`) — ngay trong thẻ `<form>`.
  2. Trong controller submit, bọc rule: đổi `$request->validate([...])` thành `$request->validate(with_captcha_validation([...]))`.
- **Ghi chú:** captcha chỉ kích hoạt khi đã bật trong Admin → Captcha (đã làm). Nếu tắt thì `captcha_verify` tự cho qua → an toàn cho môi trường dev.

### 14.2 Double-booking (Booking) 🟠 — đụng migration, cần plan trước

- **Vấn đề:** Hai người đặt cùng khung giờ gần như đồng thời có thể cùng được nhận (do code kiểm-tra-rồi-tạo, chưa có khóa).
- **Phải làm:** bọc trong `DB::transaction` + `lockForUpdate` khi đếm slot; cân nhắc thêm unique index khi `max_bookings_per_slot = 1`. **Theo Checklist §3.1: trình bày kế hoạch + duyệt trước khi viết migration.**
- **Lợi thế mới:** đã có **Redis** → có thể dùng khóa nguyên tử `Cache::lock("booking:{service_id}:{date}:{time}")->get(...)` để chặn 2 request đồng thời mà không cần khóa hàng DB; đây thường là cách gọn và an toàn nhất cho slot booking.

### 14.3 Giới hạn lượt nhận Coupon (vượt `usage_limit`) 🟡

- **Vấn đề:** Khi nhiều người nhận cùng lúc gần ngưỡng giới hạn, có thể vượt nhẹ (đếm rồi tạo không nguyên tử). Mã coupon vẫn duy nhất (đã có unique index trên `code`), nên rủi ro chủ yếu là phát dư vài mã.
- **Phải làm:** đưa bước đếm + tạo vào transaction có khóa, hoặc kiểm tra lại sau khi tạo.

### 14.4 Audit cô lập dữ liệu (IDOR) 🟡

- **Vấn đề:** Hệ thống dùng chung 1 database, cô lập bằng `where('user_id', ...)`. Hiện code đã làm đúng, nhưng cần **kiểm thử** để chắc chắn không có chỗ rò rỉ.
- **Phải làm:** thử đăng nhập user A, gọi sửa/xem bản ghi bằng ID của user B → phải bị chặn (404). Viết vài test tự động cho các module growth-tool.

### 14.5 Chống XSS khi hiển thị nội dung khách nhập 🟡

- **Vấn đề:** Nội dung khách gửi (feedback/review/lead) hiển thị trong trang admin — cần đảm bảo Blade escape (`{{ }}`), không dùng `{!! !!}` cho dữ liệu khách.
- **Phải làm:** rà các view admin hiển thị các trường này.

### 14.6 Lọc bot khỏi thống kê QR scan 🟡

- **Vấn đề:** Mọi lượt truy cập (kể cả bot) đều được tính là 1 scan → số liệu phân tích bị thổi phồng.
- **Phải làm:** lọc user-agent bot trong `recordScan`, hoặc đẩy việc ghi scan qua queue để không làm chậm redirect.

### 14.7 Rate-limit Loyalty/Referral public 🟠

- **Vấn đề:** POST `/loyalty/.../stamp`, `/referral/.../link`, `/r/.../convert` chưa có `throttle` (khác 5 growth tool).
- **Phải làm:** thêm `throttle:10,1` (và captcha nếu cần) giống `AppBookingPages/Routes/web.php`.

> Thứ tự gợi ý: **14.1 (captcha growth) → 14.7 (loyalty throttle) → 14.4 (IDOR) → 14.2/14.3 → 14.5/14.6**. Mỗi mục một task — prompt mẫu trong `ARCHITECTURE_PROMPT.md`.

