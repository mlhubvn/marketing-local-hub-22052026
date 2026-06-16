# MLHUB AI — Bản đồ Module (Single Source of Truth)

> File này là **nguồn sự thật duy nhất** về toàn bộ module trong `modules/`: mỗi module làm gì, route nào, bảng/model nào, tính năng gì, plan key nào, public endpoint nào, trạng thái production ra sao. Khi thêm/bớt/đổi module → **cập nhật file này trong cùng commit**.
>
> Dựa trên **quét trực tiếp codebase** (2026-06). Phần kiến trúc cấp hệ thống ở `ARCHITECTURE_BACKEND.md`; độ sẵn sàng & backlog chi tiết ở `ARCHITECTURE_FEATURE.md`; engine growth `lb_campaigns` ở `ARCHITECTURE_FEATURE.md` §0.

---

## 1. Mục đích file

Dùng để tra nhanh khi làm việc với một module mà **không cần quét lại codebase**:

- Module này thuộc loại nào (Admin / App / Payment / Custom), route prefix ra sao.
- Model & bảng DB nào thuộc về nó.
- Livewire/Controller chính, tính năng chính.
- Plan permission / credit action gắn với nó.
- Public endpoint (và đã có throttle/captcha chưa).
- Trạng thái production (Ready / Needs Testing / Needs Audit / Incomplete) + rủi ro.

> **Quy ước trạng thái:** ✅ Ready · 🟡 Needs Testing · 🟠 Needs Audit · 🔴 Incomplete/Disabled. Đánh giá từ quét tĩnh — không thay QA chạy thật.

---

## 2. Cơ chế module system

- **Tổng số: 72 thư mục module** trong `modules/`: **31 `Admin*`**, **37 `App*`**, **3 `Payment*`**, **1 `Custom*`**.
- Mỗi module *thường* có `module.json`: `{ "name", "providers": [...], "priority": <int> }`.
- **4 module KHÔNG có `module.json`** — nạp bằng **convention fallback** (`Modules\{Name}\Providers\{Name}ServiceProvider`): `AdminAffiliate`, `AdminMenuBuilder`, `AdminSettings`, `AppAffiliate`.
- 4 module có `module.json` **nhưng không khai `providers[]`** (cũng dùng convention fallback): `AdminCache`, `AdminLanguages`, `AdminLog`, `AdminSystemInformation`.
- **Auto-discovery** (`bootstrap/providers.php`): quét `modules/*` → đọc `module.json` (hoặc convention) → `require_once Support/helpers.php` nếu có → sắp theo `priority` tăng dần rồi theo tên → gộp `bootstrap/providers.marketplace.php`. **KHÔNG sửa `bootstrap/providers.php`.**
- **Marketplace providers** (`bootstrap/providers.marketplace.php`): `CustomMLHUB`, `AppLoyaltyStampCards`.
- **Cấu trúc điển hình một module:** `module.json`, `Providers/`, `Routes/web.php`, `Http/Controllers/` (chỉ public form/webhook/download), `Livewire/` (trang full-page), `Models/`, `Support/` (helper không trạng thái), `Services/` (workflow), `Resources/views/` (`loadViewsFrom(..., 'aliasthuong')`), `Database/Migrations/`, `config/config.php` (gộp `config('modules.aliasthuong.*')`).
- **Route prefix:** đa số hard-code trong `Routes/web.php`. Chỉ `AdminDashboard` (`admin/dashboard`) và `AdminUser` (`admin/users`) khai `route_prefix` trong `config/config.php`.

> **Lưu ý bảng DB:** tiền tố `lb_` chỉ áp dụng cho **engine LocalBoost growth + business/customer** (`lb_campaigns`, `lb_businesses`, `lb_customers`, `lb_loyalty_*`, `lb_crm_*`, `lb_email_*`, `lb_google_*`…). **Nhiều bảng KHÔNG dùng `lb_`**: `users`, `plans`, `teams`, `admin_roles`, `audit_logs`, `coupons`, `blogs`, `faqs`, `languages`, `support_tickets`, `notifications`, `options`, `payment_*`, `payment_subscriptions`, `marketplace_packages`, `affiliate_*`, `credit_*`, `files`, `custom_domains`, `ai_templates`, `ai_usage_logs`, `ai_studio_*`, `ai_prompt_histories`, `ai_content_plans`, `ai_image_jobs`. Khi viết query/migration **kiểm tra tên bảng thật trong model** (`protected $table`).

---

## 3. Module catalog tổng hợp (72)

> `mj` = có `module.json`. `prio` = priority. Trạng thái = sẵn sàng production.
>
> **Lưu ý route:** Một số module có legacy alias route dạng `settings/*`; xem phần chi tiết từng module để biết route đầy đủ.

### 3.1 Admin* (31) — khu quản trị, prefix `admin/`

| Module | mj | prio | Route prefix chính | Model → bảng | Tính năng | Trạng thái |
| --- | --- | --- | --- | --- | --- | --- |
| AdminDashboard | ✓ | 0 | `admin/dashboard` | — | Trang chủ super-admin + layout | ✅ |
| AdminUser | ✓ | — | `admin/users`, `admin/user-roles`, `admin/user-teams`, `admin/settings/auth` | `User→users`, `AdminRole→admin_roles`, `Team→teams`, `AuditLog→audit_logs` | User/role/team, impersonate, auth rules, audit | 🟡 |
| AdminPlans | ✓ | — | `admin/plans` | `AdminPlan→plans` | CRUD gói + `Plan`/`Pricing` facade | ✅ |
| AdminCredits | ✓ | 0 | `admin/credits` | *(dùng AppCredits)* | Gói credit, ledger, usage | 🟡 |
| AdminCoupons | ✓ | — | `admin/coupons` | `Coupon→coupons` | Mã giảm giá billing | 🟡 |
| AdminManualPayments | ✓ | — | `admin/manual-payments` | `ManualPayment→payment_manual` | Duyệt thanh toán thủ công | 🟡 |
| AdminPaymentManualConfig | ✓ | — | redirect → `admin/settings/payment-gateways` | — | Shim redirect (legacy) sang AppPayments | 🟠 |
| AdminPaymentHistory | ✓ | — | `admin/payment-history` | `PaymentHistory→payment_history` | Lịch sử giao dịch | 🟡 |
| AdminPaymentSubscriptions | ✓ | — | `admin/payment-subscriptions` | `PaymentSubscription→payment_subscriptions` | Quản lý subscription | 🟠 |
| AdminPaymentReport | ✓ | — | `admin/payment-report` | *(query, no model)* | Báo cáo thanh toán | 🟡 |
| AdminMarketplace | ✓ | 0 | `admin/marketplace` | `MarketplacePackage→marketplace_packages` | Marketplace module + license | 🟡 |
| AdminThemes | ✓ | 0 | `admin/themes` | *(file-based)* | Theme, Custom CSS/JS | ✅ |
| AdminSettings | ✗ | — | `admin/settings/{general,analytics,embed-code,static-pages,security}` | *(OptionStore→options)* | Cài đặt lõi + static pages | ✅ |
| AdminCaptcha | ✓ | 0 | `admin/settings/captcha` | *(options)* | Turnstile / reCAPTCHA v2 | ✅ |
| AdminMailServer | ✓ | — | `admin/settings/mail-server` | *(options)* | Cấu hình SMTP/sender | ✅ |
| AdminCache | ✓ | 0 | `admin/settings/cache` | *(CacheActionRegistry)* | Xóa cache/session | ✅ |
| AdminCrons | ✓ | 0 | `admin/settings/crons`, public `cron/{task}` | *(SystemCronRegistry)* | Registry scheduler + secure cron URL | 🟡 |
| AdminLog | ✓ | — | `admin/settings/log` | *(LogManager)* | Xem/tải/xóa `laravel.log` | ✅ |
| AdminSystemInformation | ✓ | — | `admin/settings/system-information` | — | Chẩn đoán PHP/Laravel/env | ✅ |
| AdminMenuBuilder | ✗ | — | `admin/menu-builder` | *(options)* | Kéo-thả sắp xếp sidebar | 🟡 |
| AdminLanguages | ✓ | 0 | `admin/languages`, public `lang/{locale}` | `Language→languages`, `LanguageTranslation→language_translations` | Locale + override dịch DB | 🟡 |
| AdminBlogs | ✓ | — | `admin/blogs` | `Blog→blogs`, `BlogRssSource→blog_rss_sources`, `BlogRssImport→blog_rss_imports` | Blog + AI content + RSS import | 🟡 |
| AdminBlogCategories | ✓ | — | `admin/blog-categories` | `BlogCategory→blog_categories` | Danh mục blog (sidebar dưới AdminBlogs) | 🟡 |
| AdminBlogTags | ✓ | — | `admin/blog-tags` | `BlogTag→blog_tags` | Tag blog (sidebar dưới AdminBlogs) | 🟡 |
| AdminFaqs | ✓ | — | `admin/faqs` | `Faq→faqs` | Quản lý FAQ | 🟡 |
| AdminSupport | ✓ | — | `admin/support` | `SupportTicket→support_tickets` (+ category/label/type/comment) | Help desk ticket + taxonomy | 🟡 |
| AdminNotifications | ✓ | — | `admin/notifications` | `Notification→notifications`, `NotificationManual→notification_manual`, `NotificationManualState→notification_manual_states` | Broadcast + panel in-app | 🟡 |
| AdminAI | ✓ | — | `admin/settings/ai-config`, `admin/ai-usage-logs`, `admin/ai-report` | `AiUsageLog→ai_usage_logs` | Cấu hình provider AI, usage, report | 🟠 |
| AdminAITemplate | ✓ | — | `admin/ai-templates` | `AiTemplate→ai_templates` | CRUD prompt template AI | 🟡 |
| AdminAITemplateCategories | ✓ | — | `admin/ai-template-categories` | `AiTemplateCategory→ai_template_categories` | Taxonomy template AI | 🟡 |
| AdminAffiliate | ✗ | — | `admin/affiliate` | *(dùng AppAffiliate)* | Quản trị affiliate, commission, withdrawal | 🟠 |

### 3.2 App* (37) — portal khách hàng, prefix `portal/`

| Module | mj | prio | Route prefix chính | Model → bảng | Plan key | Public? | Trạng thái |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AppBusinessProfiles | ✓ | 20 | `portal/businesses` | `LocalBusiness→lb_businesses` | `localboost` | GET `/b/{business}` | 🟡 |
| AppBusinessLocations | ✓ | 21 | nested `.../locations` | `BusinessLocation→lb_locations` | *(localboost)* | GET `/l/{location}` | 🟡 |
| AppCustomers | ✓ | 22 | `portal/customers` | `Customer→lb_customers` | *(localboost)* | — | 🟡 |
| AppQRCampaigns | ✓ | 21 | `portal/qr-campaigns` | `QrCampaign→lb_campaigns`, `QrScan→lb_qr_scans` | *(localboost)* | GET `/qr/{slug}`(+svg/png) | 🟠 |
| AppReviewBooster | ✓ | 22 | `portal/review-booster` | `ReviewFeedback→lb_review_feedbacks` | *(localboost)* | POST feedback (throttle) | 🟡 |
| AppBookingPages | ✓ | 23 | `portal/booking-pages` | `Booking→lb_bookings`, `BookingService→lb_booking_services` | *(localboost)* | POST booking (throttle) | 🟠 |
| AppCouponCampaigns | ✓ | 24 | `portal/coupon-campaigns` | `CouponRedemption→lb_coupon_redemptions` | *(localboost)* | POST coupon (throttle) | 🟠 |
| AppFeedbackForms | ✓ | 25 | `portal/feedback-forms` | `FeedbackResponse→lb_feedback_responses` | *(localboost)* | POST feedback-form (throttle) | 🟠 |
| AppLeadForms | ✓ | 26 | `portal/lead-forms` | `LeadSubmission→lb_lead_submissions` | *(localboost)* | POST lead (throttle) | 🟡 |
| AppLandingPages | ✓ | 27 | `portal/landing-pages` | `LandingPage→lb_landing_pages` | *(localboost)* `max_landing_pages` | GET `/lp/{slug}`, POST submit (**no throttle**) | 🟠 |
| AppMarketingTemplates | ✓ | 28 | `portal/marketing-templates` | `MarketingTemplate→lb_marketing_templates` | *(localboost)* `max_templates` | — | 🟡 |
| AppAdvancedCustomerCrm | ✓ | 31 | `portal/crm` | `lb_customer_*`, `lb_crm_*` (10 model) | `advanced_crm` | — | 🟡 |
| AppEmailAutomation | ✓ | 0 | `portal/email-automation` | `lb_email_automations`, `lb_email_templates`, `lb_email_automation_logs` | `email_automation` | — | 🟠 |
| AppWebhookAutomation | ✓ | 0 | `portal/webhook-automation` | `lb_webhook_automations`, `lb_webhook_automation_logs` | `webhook_automation` | — | 🟡 |
| AppWhatsAppNotification | ✓ | 0 | `portal/whatsapp-notification` | `lb_whatsapp_notifications`, `lb_whatsapp_templates`, `lb_whatsapp_notification_logs` | `whatsapp_notification` | — | 🟡 |
| AppLoyaltyStampCards | ✓ | 29 | `portal/loyalty-cards` | `lb_loyalty_*`, `lb_referral_*` (8 model) | `loyalty_stamp_cards` | `/loyalty/*`, `/referral/*`, `/r/*` (**no throttle**) | 🟠 |
| AppLocalAnalytics | ✓ | 25 | `portal/reports` | *(aggregate, no model)* | *(localboost)* | — | 🟡 |
| AppGoogleBusiness | ✓ | 0 | `portal/integrations/google-business` | `lb_google_*` (7 model) | `google_business` | OAuth callback (auth-gated) | 🟠 |
| AppCustomDomain | ✓ | 0 | `portal/brand/custom-domains`, `portal/qr-codes/domains` | `AppCustomDomain→custom_domains` | `qr_custom_domains` | — | 🟡 |
| AppAIStudio | ✓ | 0 | `portal/ai-studio` | `ai_studio_user_settings`, `ai_studio_workspace_settings`, `ai_prompt_histories` | `ai_studio` | — | ✅ |
| AppAIContent | ✓ | 0 | `portal/ai-studio/ai-content` | — | `ai_studio_caption_generator` | — | ✅ |
| AppAIContentPlanner | ✓ | 0 | `portal/ai-studio/planner` | `AIContentPlan→ai_content_plans` | `ai_studio_content_planner` | — | 🟡 |
| AppAIRepurpose | ✓ | 0 | `portal/ai-studio/repurpose` | — | `ai_studio_repurpose` | — | 🟡 |
| AppAIImage | ✓ | 0 | `portal/ai-studio/image` | `AIImageJob→ai_image_jobs` | `ai_studio_image` | — | 🟡 |
| AppAIVideo | ✓ | 0 | *(config `portal/ai-studio/video` — **routes KHÔNG nạp**)* | `AIVideoJob→ai_video_jobs` | — *(credit chưa register)* | — | 🔴 Disabled |
| AppAIReview | ✓ | 0 | *(config `portal/ai-studio/review` — **routes KHÔNG nạp**)* | — | — *(credit chưa register)* | — | 🔴 Disabled |
| AppAIBestTime | ✓ | 0 | *(config `portal/ai-studio/timing` — **routes KHÔNG nạp**)* | — | — *(credit chưa register)* | — | 🔴 Disabled |
| AppAISemanticSearch | ✓ | 0 | *(config `portal/ai-studio/search` — **routes KHÔNG nạp**)* | — | — *(credit chưa register)* | — | 🔴 Disabled |
| AppBilling | ✓ | 0 | `portal/billing`, `portal/invoices` | *(dùng model billing/subscription)* | — | — | 🟡 |
| AppPayments | ✓ | 0 | `portal/packages`, `payment/{plan}`, `admin/settings/payment-gateways` | — | — | POST `/payment/webhook/{gateway}` | 🟠 |
| AppCredits | ✓ | 0 | `portal/credits`, `payment/credits/{pack}` | `CreditPack→credit_packs`, `CreditTopupLedger→credit_topup_ledgers`, `CreditUsageLog→credit_usage_logs` | — | — | 🟡 |
| AppTeams | ✓ | 0 | `portal/teams` | `TeamInvitation→team_invitations`, `TeamConversation→team_conversations`, `TeamMessage→team_messages`, `TeamPostComment→team_post_comments`, `TeamPostReview→team_post_reviews` | `teams` | — | 🟠 |
| AppFiles | ✓ | 0 | `portal/files`, `admin/files` | `AppFile→files` | `files` | signed preview + OAuth picker callback | 🟡 |
| AppProfile | ✓ | 0 | `portal/profile` | *(dùng User)* | — | — | ✅ |
| AppSupport | ✓ | 0 | `portal/support` | *(dùng AdminSupport models)* | `support` | — | 🟡 |
| AppAffiliate | ✗ | 0 | `portal/affiliate`, `admin/settings/affiliate` | `AffiliateProfile→affiliate_profiles`, `AffiliateCommission→affiliate_commissions`, `AffiliateWithdrawal→affiliate_withdrawals` | `affiliate` | — *(capture qua middleware)* | 🟠 |
| AppIntegrations | ✓ | 0 | `admin/integrations` *(admin-only)* | — | — | — | 🟡 |

### 3.3 Payment* (3) + manual

| Module | mj | Gateway key | Webhook | Trạng thái |
| --- | --- | --- | --- | --- |
| PaymentStripe | ✓ | `stripe`, `stripe_recurring` | POST `/payment/webhook/stripe` | 🟠 Needs Audit |
| PaymentPaypal | ✓ | `paypal`, `paypal_recurring` | POST `/payment/webhook/paypal` | 🟠 Needs Audit |
| Payment2Checkout | ✓ | `2checkout` | POST `/payment/webhook/2checkout` | 🟠 Needs Audit |
| *(AppPayments — manual)* | ✓ | `manual` | — | 🟠 |

> **Đã gỡ (không còn module trong code):** các cổng Ấn Độ/Phi/Thổ/Nga (vd `PaymentRazorpay`, `PaymentPaystack`…). Sẽ tích hợp VNPay/MoMo sau qua module mới — không có dấu vết code hiện tại.

### 3.4 Custom* (1)

| Module | mj | prio | Vai trò |
| --- | --- | --- | --- |
| CustomMLHUB | ✓ | 25 | Bootstrap VN: `mlhub:install`/`mlhub:update`/`mlhub:sync-env-options`, seeder admin/extras, site options, env→options sync |

---

## 4. Admin modules (chi tiết)

**Nhóm "Settings hub"** (`admin/settings/*` + `register_setting_item`, không sidebar riêng — hiện dưới sidebar **AdminSettings**): AdminCache, AdminCaptcha, AdminCrons, AdminLog, AdminMailServer, AdminSystemInformation, AdminAI (một phần), AdminUser (`settings.auth`).

**Admin-side của App module (không có model riêng):** AdminAffiliate → AppAffiliate; AdminCredits → AppCredits; AdminPaymentManualConfig → bị thay bởi AppPayments (chỉ còn route redirect).

**Blog tách 3 module:** AdminBlogs sở hữu sidebar; AdminBlogCategories + AdminBlogTags là module riêng, sidebar children do AdminBlogs đăng ký.

**Dashboard widget** (`register_admin_dashboard_item`): AdminAI, AdminBlogs, AdminManualPayments, AdminMarketplace, AdminNotifications, AdminPaymentSubscriptions, AdminPlans, AdminSupport, AdminUser.

**Permission/Credit:** chỉ AdminPlans gọi `register_plan_permission` (định nghĩa mặc định hiện rỗng — permission do từng App module tự đăng ký). AdminAI gọi `Pricing::addSubFeatures('credits_usage_limit')`. Không Admin module nào gọi `register_credit_action`.

**Route alias kép (legacy + admin):** AdminAI, AdminCache, AdminCaptcha, AdminCrons, AdminMailServer, AdminSettings, AdminSystemInformation expose cả `settings/...` lẫn `admin/settings/...`.

**Route không-admin nằm trong Admin module:** `lang/{locale}` (AdminLanguages), `cron/{task}` (AdminCrons), `impersonation/leave` + `portal/activity` (AdminUser).

---

## 5. App core modules (chi tiết)

- **AppBusinessProfiles** — hub định tuyến: nest `AppBusinessLocations` + `AppCustomers` dưới `portal/businesses/{business}/…`. Đăng ký **sections sidebar portal** chính (`local-businesses`, `growth-tools`, `ai-tools`, `marketing-assets`, `analytics`, `team-billing`). Là **nơi duy nhất** `register_plan_permission('localboost')` với limit `max_businesses/max_campaigns/max_landing_pages/max_qr_codes/max_templates/remove_branding`.
- **AppBusinessLocations** — địa điểm vật lý per business; `portal/locations` chỉ redirect về `portal.businesses`.
- **AppCustomers** — danh sách khách CRM cơ bản; vào được qua `portal/customers` và per-business.
- **AppTeams** — workspace: mời thành viên, vai trò, chat, duyệt. `TeamWorkspaceAccess::workspaceOwnerUserId()` là khóa scope dữ liệu workspace/AI.
- **AppBilling** — quản lý subscription + tải invoice; không có model riêng. Đăng ký item **Plans** (`portal.packages`) + **Billing**.
- **AppPayments** — orchestration cổng thanh toán, checkout gói, webhook; UI cấu hình gateway ở `admin/settings/payment-gateways`.
- **AppCredits** — gói credit AI, ledger sử dụng, top-up checkout.
- **AppFiles** — file manager portal + admin, picker (Drive/Dropbox/OneDrive), image editor.
- **AppProfile** — cài đặt tài khoản, đổi mật khẩu, 2FA card.
- **AppSupport** — ticket hỗ trợ trong portal (dùng model của AdminSupport); plan key `support`, **không** hiện trong sidebar MVP.
- **AppAffiliate** — referral/commission/withdrawal; plan key `affiliate`, **không** sidebar MVP; capture referral qua middleware global `CaptureAffiliateReferral`.
- **AppIntegrations** — hub tích hợp API, **admin-only** (`admin/integrations`).

---

## 6. App growth modules (chi tiết)

5 growth tool + QR + Landing + Templates dùng chung **engine `lb_campaigns`** (model `QrCampaign`, cột `type` ∈ {review, booking, coupon, feedback, lead, url} + JSON `settings`). Public đi qua `QrCampaignPublicController::show()` → `recordScan()` → redirect/landing/view theo `type`. Chi tiết settings/flow: `ARCHITECTURE_FEATURE.md` §0.

| Tool | Module | type | Portal route | Model kết quả |
| --- | --- | --- | --- | --- |
| Review Booster | AppReviewBooster | `review` | `portal.review-booster` | `lb_review_feedbacks` |
| Booking | AppBookingPages | `booking` | `portal.booking-pages` | `lb_bookings`, `lb_booking_services` |
| Coupons | AppCouponCampaigns | `coupon` | `portal.coupon-campaigns` | `lb_coupon_redemptions` |
| Feedback | AppFeedbackForms | `feedback` | `portal.feedback-forms` | `lb_feedback_responses` |
| Lead | AppLeadForms | `lead` | `portal.lead-forms` | `lb_lead_submissions` |
| QR/URL | AppQRCampaigns | `url` | `portal.qr-campaigns` | `lb_qr_scans` |
| Landing | AppLandingPages | — | `portal.landing-pages` | `lb_landing_pages` |
| Templates | AppMarketingTemplates | — | `portal.marketing-templates` | `lb_marketing_templates` (+ packs) |

Mỗi submit công khai → `CustomerUpserter::fromCampaign()` (gắn khách + tags) → `GrowthToolNotifier` (thông báo in-app + `PortalGrowthDashboardMetrics::forget()`).

---

## 7. App CRM / automation modules (chi tiết)

- **AppAdvancedCustomerCrm** (`portal/crm`, plan `advanced_crm`) — mở rộng `lb_customers`: tag/segment/task/note/activity/score/merge + 3 bảng automation (`lb_crm_automations`, `lb_crm_automation_jobs`, `lb_crm_automation_logs`). Artisan: `crm:process-automations`, `crm:cleanup-activities`, `crm:lifecycle`, `crm:seed-demo`.
- **AppEmailAutomation** (`portal/email-automation`, plan `email_automation`) — rule gửi email theo event; phụ thuộc SMTP + queue.
- **AppWebhookAutomation** (`portal/webhook-automation`, plan `webhook_automation`) — webhook/Zapier outbound theo event.
- **AppWhatsAppNotification** (`portal/whatsapp-notification`, plan `whatsapp_notification`) — WhatsApp Cloud API theo event.
- **AppLoyaltyStampCards** (`portal/loyalty-cards`, plan `loyalty_stamp_cards`) — thẻ tích điểm + referral; **không** dùng engine `QrCampaign`; có public route riêng (`/loyalty/*`, `/referral/*`, `/r/*`). Đăng ký qua `providers.marketplace.php`.
- **AppLocalAnalytics** (`portal/reports`) — dashboard báo cáo tổng hợp, **không** thêm bảng; đọc dữ liệu module khác.
- **AppGoogleBusiness** (`portal/integrations/google-business`, plan `google_business`) — OAuth GBP, sync review, post, auto-reply (`lb_google_*`). Artisan: `google-business:sync-reviews`, `google-business:publish-scheduled-posts`.
- **AppCustomDomain** (plan `qr_custom_domains`) — domain tùy biến cho link QR/campaign.

**Trigger automation (Eloquent observer):** `Booking`/`CouponRedemption`/`FeedbackResponse`/`ReviewFeedback`/`Customer` created/updated → bắn event (`booking.*`, `coupon.*`, `feedback.*`, `review.*`, `customer.created`). Ma trận đầy đủ: `ARCHITECTURE_SOP.md` §14.

---

## 8. App AI modules (chi tiết)

Engine: `Modules\AppAIStudio\Support\AiContentStudioService` (dùng `laravel/ai` + Prism). Lịch sử `AIPromptHistory` scope theo `workspaceOwnerUserId()`. Bắt buộc: feature gate → `credit_service()->ensureCanConsume()` → `try/catch` + fallback → `consume_credits()`.

| Module | Route | Credit action (cost) | Trạng thái |
| --- | --- | --- | --- |
| AppAIStudio | `portal/ai-studio` | `ai_studio_review_reply` (1) | ✅ |
| AppAIContent | `portal/ai-studio/ai-content` | `ai_studio_generate_captions` (1) | ✅ |
| AppAIContentPlanner | `portal/ai-studio/planner` | `ai_studio_plan_calendar` (1) | 🟡 |
| AppAIRepurpose | `portal/ai-studio/repurpose` | `ai_studio_repurpose_content` (1) | 🟡 |
| AppAIImage | `portal/ai-studio/image` | `ai_studio_generate_image` (3) | 🟡 |
| AppAIVideo | *(routes không nạp)* | `ai_studio_generate_video` — **chưa register** | 🔴 Disabled |
| AppAIReview | *(routes không nạp)* | `ai_studio_review_content` — **chưa register** | 🔴 Disabled |
| AppAIBestTime | *(routes không nạp)* | `ai_studio_best_time` — **chưa register** | 🔴 Disabled |
| AppAISemanticSearch | *(routes không nạp)* | `ai_studio_semantic_search` — **chưa register** | 🔴 Disabled |

- **Credit action register:** chỉ 5 key đầu được `register_credit_action` trong `AppAIStudioServiceProvider` (cost theo `credit_cost_*`). 4 key sau dùng trong code module dormant nhưng **chưa đăng ký**.
- **Provider/API key AI:** lưu trong **Admin Settings (OptionStore)**, **KHÔNG** trong `.env` (không có `OPENAI_*`/`ANTHROPIC_*`/`PRISM_*` trong `config/` hoặc `modules/`).
- **4 module dormant** (`AppAIVideo`, `AppAIReview`, `AppAIBestTime`, `AppAISemanticSearch`): còn trên đĩa nhưng `boot()` không `loadRoutesFrom` → không truy cập được. Quyết định gỡ hẳn hay hoàn thiện → backlog `ARCHITECTURE_FEATURE.md`.

---

## 9. Payment modules (chi tiết)

Mẫu plugin: mỗi `Payment*ServiceProvider` (1) khai `PaymentGatewayDefinition` (key/nhãn/năng lực/tiền tệ), (2) bind gateway vào contract `Modules\AppPayments\Contracts\PaymentGateway`, (3) gọi `PaymentGatewaySettingsRegistry::register('key', [...])` để sinh UI cấu hình ở Admin.

| Module | Gateway key | Settings registry | Webhook / callback |
| --- | --- | --- | --- |
| PaymentStripe | `stripe`, `stripe_recurring` | `stripe` | `/payment/webhook/stripe`, success/cancel `/payment/{success,cancel}/stripe` |
| PaymentPaypal | `paypal`, `paypal_recurring` | `paypal` | `/payment/webhook/paypal` (+ success/cancel) |
| Payment2Checkout | `2checkout` | `2checkout` | `/payment/webhook/2checkout`, checkout `payment.2checkout.checkout` |
| AppPayments (manual) | `manual` | — | — (duyệt qua AdminManualPayments) |

- **Credential:** lưu trong **OptionStore** (`stripe_*`, `paypal_*`, `2checkout_*`); riêng `STRIPE_*` có thể seed từ env qua `MLHUBEnvOptionsSync`. `PAYPAL_*`/`2CHECKOUT_*` chỉ ở OptionStore, **không** đọc env.
- **Webhook chung:** `POST /payment/webhook/{gateway}` (route `payment.webhook`) — **không** throttle ở route (backlog: kiểm tra idempotency).

---

## 10. CustomMLHUB

| Thành phần | Chi tiết |
| --- | --- |
| Commands | `mlhub:install` (migrate:fresh + seed từ ID 147123468 + optimize; hỏi xác nhận / `--force`), `mlhub:update` (migrate + seed đồng bộ catalog `mlhub-*`, giữ user/campaign/plan legacy), `mlhub:sync-env-options` (env → `options`) |
| Seeders | `MLHUBAdminSeeder`, `MLHUBSystemExtrasSeeder` (chain qua `config/mlhub.php` → `default_seeders`); `PlanSeeder` (catalog 13 gói) |
| Config | `modules/CustomMLHUB/config/config.php`, `config/env_options.php` (map env → option), app `config/mlhub.php` |
| Site options | `Database/data/mlhub_site_options.php` (format ngày `d/m/Y`, VND `₫`, timezone) |
| Env đọc | `MLHUB_STARTING_ID`, `MLHUB_FIRST_USER_*`, `MLHUB_ALLOW_RESET_DEMO`, `MLHUB_SYNC_ENV_OPTIONS` + toàn bộ key trong `env_options.php` |
| Flow | DB trống → `mlhub:install`; giữ dữ liệu → `mlhub:update`; mỗi deploy entrypoint chạy `migrate --force` + `mlhub:sync-env-options` |

---

## 11. Cross-module dependency map

- **Growth tools** (Review/Booking/Coupon/Feedback/Lead) → `AppQRCampaigns` (`QrCampaign`/`lb_campaigns`) → `AppCustomers` (`CustomerUpserter`) → `GrowthToolNotifier` → `PortalGrowthDashboardMetrics` (dashboard) + observer → Email/Webhook/WhatsApp/CRM automation.
- **AppLandingPages** ← phục vụ public cho growth tool (`QrCampaignPublicController` rẽ sang `LandingPage` đã publish).
- **AppAdvancedCustomerCrm** ⊃ `AppCustomers` (mở rộng `Customer`/`lb_customers` + counters).
- **AppLoyaltyStampCards** → counters trong `lb_customers` (loyalty stamps/referrals) → CRM.
- **AppGoogleBusiness** → review/post/reply (`lb_google_*`) → trigger `google_review_synced` → CRM/automation.
- **AI modules** → `AppAIStudio` (engine + credit register) → `AppCredits` (`credit_service`) → plan `ai_studio` + sub-feature.
- **PlanLimitGuard** (`app/Support/Plans/`) → chốt chặn tạo bản ghi cho `localboost` (businesses/campaigns/landing/qr/templates) + dò động addon qua `class_exists()`/`Schema::hasColumn()`.
- **Payment*** → `AppPayments` (contract + checkout/webhook) → `AdminPaymentSubscriptions`/`AppBilling` (subscription) + `AppCredits` (top-up).
- **AppTeams** → `TeamWorkspaceAccess::workspaceOwnerUserId()` → scope dữ liệu workspace của AI/CRM.

---

## 12. Public endpoint map

> Tất cả ở middleware `web`. "Throttle" = `throttle:10,1`. Captcha: **chưa gắn** cho mọi form growth/loyalty (mới ở trang auth).

| Route name | Method | Path | Throttle | Captcha | Module |
| --- | --- | --- | --- | --- | --- |
| `qr-campaigns.public` | GET | `/qr/{campaign:slug}` | — | — | AppQRCampaigns |
| `qr-campaigns.svg` / `.png` | GET | `/qr/{slug}/qr.{svg,png}` | — | — | AppQRCampaigns |
| `review-booster.feedback` | POST | `/qr/{slug}/feedback` | ✅ | ✗ | AppReviewBooster |
| `booking-pages.submit` | POST | `/qr/{slug}/booking` | ✅ | ✗ | AppBookingPages |
| `coupon-campaigns.claim` | POST | `/qr/{slug}/coupon` | ✅ | ✗ | AppCouponCampaigns |
| `feedback-forms.submit` | POST | `/qr/{slug}/feedback-form` | ✅ | ✗ | AppFeedbackForms |
| `lead-forms.submit` | POST | `/qr/{slug}/lead` | ✅ | ✗ | AppLeadForms |
| `landing-pages.public` | GET | `/lp/{slug}` | — | — | AppLandingPages |
| `landing-pages.submit` | POST | `/lp/{slug}` | 🔴 **chưa** | ✗ | AppLandingPages |
| `landing-pages.qr` / `.qr.png` | GET | `/lp/{slug}/qr.{svg,png}` | — | — | AppLandingPages |
| `loyalty-cards.public` | GET | `/loyalty/{card:slug}` | — | — | AppLoyaltyStampCards |
| `loyalty-cards.stamp` | POST | `/loyalty/{card:slug}/stamp` | 🔴 **chưa** | ✗ | AppLoyaltyStampCards |
| `referral-campaigns.public` | GET | `/referral/{campaign:slug}` | — | — | AppLoyaltyStampCards |
| `referral-campaigns.link` | POST | `/referral/{campaign:slug}/link` | 🔴 **chưa** | ✗ | AppLoyaltyStampCards |
| `referral-links.public` | GET | `/r/{link:code}` | — | — | AppLoyaltyStampCards |
| `referral-links.convert` | POST | `/r/{link:code}/convert` | 🔴 **chưa** | ✗ | AppLoyaltyStampCards |
| `businesses.public` | GET | `/b/{business}` | — | — | AppBusinessProfiles |
| `locations.public` | GET | `/l/{location}` | — | — | AppBusinessLocations |
| `payment.webhook` | POST | `/payment/webhook/{gateway}` | — | — | AppPayments |
| `cron.run` | GET | `/cron/{task}` | secure key | — | AdminCrons |
| `*.public` lang | GET | `/lang/{locale}` | — | — | AdminLanguages |

> **Backlog (P1/P2):** `landing-pages.submit` + 4 route POST loyalty/referral **chưa throttle**; captcha **chưa gắn** vào form growth/loyalty công khai. Xem `ARCHITECTURE_FEATURE.md` §14.

---

## 13. Table map theo module

> Migration: baseline `database/migrations/2026_*_create_database.php` + `modules/*/Database/Migrations/`.

| Module | Bảng | Notes |
| --- | --- | --- |
| AdminUser | `users`, `admin_roles`, `teams`, `audit_logs` | Không `lb_` |
| AdminPlans | `plans` | — |
| AdminCoupons | `coupons` | Billing coupon (khác coupon campaign) |
| Admin payments | `payment_manual`, `payment_history`, `payment_subscriptions` | — |
| AdminMarketplace | `marketplace_packages` | — |
| AdminBlogs(+cat/tag) | `blogs`, `blog_categories`, `blog_tags`, `blog_rss_sources`, `blog_rss_imports` | — |
| AdminFaqs | `faqs` | — |
| AdminSupport | `support_tickets`, `support_categories`, `support_labels`, `support_types`, `support_comments` | — |
| AdminNotifications | `notifications`, `notification_manual`, `notification_manual_states` | — |
| AdminLanguages | `languages`, `language_translations` | — |
| AdminSettings | `options` | OptionStore key/value |
| AdminAI(+template) | `ai_usage_logs`, `ai_templates`, `ai_template_categories` | — |
| AppBusinessProfiles | `lb_businesses` | — |
| AppBusinessLocations | `lb_locations` | — |
| AppCustomers | `lb_customers` | Mở rộng bởi CRM |
| Growth engine | `lb_campaigns`, `lb_qr_scans`, `lb_review_feedbacks`, `lb_bookings`, `lb_booking_services`, `lb_coupon_redemptions`, `lb_feedback_responses`, `lb_lead_submissions` | — |
| AppLandingPages | `lb_landing_pages` | — |
| AppMarketingTemplates | `lb_marketing_templates`, `lb_template_packs`, `lb_template_pack_items`, `lb_template_usages`, `lb_template_imports`, `lb_template_ratings` | — |
| AppAdvancedCustomerCrm | `lb_customer_{activities,tags,tag_maps,notes,tasks,segments,score_logs,merge_logs}`, `lb_crm_automations`, `lb_crm_automation_logs`, `lb_crm_automation_jobs` | Mở rộng `lb_customers` |
| AppEmailAutomation | `lb_email_templates`, `lb_email_automations`, `lb_email_automation_logs` | — |
| AppWebhookAutomation | `lb_webhook_automations`, `lb_webhook_automation_logs` | — |
| AppWhatsAppNotification | `lb_whatsapp_templates`, `lb_whatsapp_notifications`, `lb_whatsapp_notification_logs` | — |
| AppLoyaltyStampCards | `lb_loyalty_{cards,customers,stamps,rewards}`, `lb_referral_{campaigns,links}`, `lb_referrals`, `lb_referral_rewards` | — |
| AppGoogleBusiness | `lb_google_business_connections`, `lb_google_business_locations`, `lb_google_reviews`, `lb_google_auto_reply_{rules,logs}`, `lb_google_business_posts`, `lb_google_business_post_logs` | — |
| AppCustomDomain | `custom_domains` | Không `lb_` |
| AppTeams | `team_invitations`, `team_conversations`, `team_messages`, `team_post_comments`, `team_post_reviews` | Không `lb_` |
| AppCredits/AdminCredits | `credit_packs`, `credit_topup_ledgers`, `credit_usage_logs` | Không `lb_` |
| AppFiles | `files` | Không `lb_` |
| AppAffiliate | `affiliate_profiles`, `affiliate_commissions`, `affiliate_withdrawals` | Không `lb_` |
| AI Studio | `ai_studio_user_settings`, `ai_studio_workspace_settings`, `ai_prompt_histories`, `ai_content_plans`, `ai_image_jobs`, `ai_video_jobs` | Không `lb_` |

> Session/cache/queue chạy **Redis** (không cần bảng `sessions`/`cache`/`jobs` MySQL); riêng `failed_jobs`, `job_batches` vẫn ở MySQL.

---

## 14. Plan / permission map theo module

| Plan key | Module đăng ký | Limit keys chính | Env fallback (no-plan) |
| --- | --- | --- | --- |
| `localboost` | AppBusinessProfiles | `max_businesses`, `max_campaigns`, `max_landing_pages`, `max_qr_codes`, `max_templates`, `remove_branding` | `MLHUB_NO_PLAN_LOCALBOOST` + `MLHUB_NO_PLAN_MAX_*` |
| `credits_usage` | (AdminAI sub-feature) | `credits_usage_limit`, `credit_cost_*` | `MLHUB_NO_PLAN_CREDITS_*` |
| `files` | AppFiles | `max_storage_size_mb`, `max_file_size_mb`, `file_picker`, `image_editor`, `search_media_online` | `MLHUB_NO_PLAN_FILES`, `..._MAX_STORAGE_MB`, `..._FILE_*` |
| `ai_studio` | AppAIStudio | `ai_studio_caption_generator`, `ai_studio_repurpose`, `ai_studio_content_planner`, `ai_studio_image` | `MLHUB_NO_PLAN_AI_*` |
| `teams` | AppTeams | `max_team_members` | `MLHUB_NO_PLAN_TEAMS`, `..._MAX_TEAM_MEMBERS` |
| `advanced_crm` | AppAdvancedCustomerCrm | `customer_tags`, `customer_segments`, `customer_tasks`, `crm_automations`, `crm_activity_retention_days` | `MLHUB_NO_PLAN_CRM_*` |
| `google_business` | AppGoogleBusiness | `max_google_business_connections/locations`, `google_review_sync/reply`, `google_insights`, `google_posts` | `MLHUB_NO_PLAN_GOOGLE_*` |
| `email_automation` | AppEmailAutomation | `max_email_automations/templates`, `emails_per_month`, `automation_delay/conditions` | `MLHUB_NO_PLAN_EMAIL_*` |
| `whatsapp_notification` | AppWhatsAppNotification | `max_whatsapp_*`, `whatsapp_messages_per_month`, `whatsapp_cloud_api`, `whatsapp_template_msg` | `MLHUB_NO_PLAN_WHATSAPP*` |
| `webhook_automation` | AppWebhookAutomation | `max_webhook_automations`, `webhooks_per_month`, `webhook_custom_headers`, `webhook_retry` | `MLHUB_NO_PLAN_WEBHOOK*` |
| `loyalty_stamp_cards` | AppLoyaltyStampCards | `max_loyalty_cards/customers`, `max_referral_campaigns`, `loyalty_rewards`, `loyalty_staff_redeem` | `MLHUB_NO_PLAN_LOYALTY*` |
| `qr_custom_domains` | AppCustomDomain | `max_custom_domains` | `MLHUB_NO_PLAN_CUSTOM_DOMAINS`, `..._MAX_CUSTOM_DOMAINS` |
| `affiliate` | AppAffiliate | toggle | `MLHUB_NO_PLAN_AFFILIATE` |
| `support` | AppSupport | toggle | `MLHUB_NO_PLAN_SUPPORT` |

- **Enforcement:** `App\Support\Plans\PlanLimitGuard` (`ensureBusinessCanBeCreated`, `ensureCampaignCanBeCreated`, …) ném `ValidationException::withMessages(['plan' => …])` khi vượt.
- **No-plan fallback:** user `plan_id=null` → `MLHUB_NO_PLAN_*` env → `config/mlhub.php` → `NoPlanAccess::permissionsFromEnv()`. Sidebar addon chỉ hiện khi cờ tương ứng `true`; vào URL trực tiếp khi tắt → `403`. Ma trận đầy đủ: `ARCHITECTURE_FEATURE.md` §1.1, env: `.env.example`.

---

## 15. Production readiness theo module

| Nhóm | Module | Trạng thái | Rủi ro chính cần test |
| --- | --- | --- | --- |
| Auth/Plan | AdminUser, AdminPlans, AppProfile | ✅/🟡 | IDOR tenant, ma trận role |
| Growth public | Review/Booking/Coupon/Feedback/Lead | 🟡/🟠 | P1 race (booking double, coupon overshoot), P2 captcha/XSS |
| QR | AppQRCampaigns | 🟠 | P2 lọc bot trong `recordScan` |
| Landing | AppLandingPages | 🟠 | P1 `landing-pages.submit` chưa throttle |
| Loyalty/Referral | AppLoyaltyStampCards | 🟠 | P1 public POST chưa throttle |
| Payment | Stripe/Paypal/2Checkout/manual | 🟠 | P0 webhook thật + idempotency + hoàn tiền |
| CRM/Automation | CRM, Email, Webhook, WhatsApp | 🟡/🟠 | queue + SMTP + trigger event |
| Google Business | AppGoogleBusiness | 🟠 | OAuth bộ 2, sync review/post |
| AI active | AIStudio, AIContent, Planner, Repurpose, Image | ✅/🟡 | provider key thật, credit trừ đúng |
| AI dormant | AIVideo, AIReview, AIBestTime, AISemanticSearch | 🔴 Disabled | Quyết định gỡ/hoàn thiện (credit chưa register) |

Chi tiết ma trận + việc cần làm: `ARCHITECTURE_FEATURE.md`.

---

## 16. Checklist khi thêm module mới

1. **`module.json`**: `{ name, providers[], priority }`. Growth tool đặt `priority > 20`. (Hoặc dùng convention fallback — nhưng nên khai rõ.)
2. **Provider**: `{Name}ServiceProvider` — `register()` chỉ `mergeConfigFrom`; `boot()` `loadRoutesFrom/loadViewsFrom/loadMigrationsFrom` + registry helper.
3. **Routes**: `Routes/web.php` — portal sau `['web','auth','verified']`; public sau `web` (+ `throttle` nếu form POST; cân nhắc captcha).
4. **Migrations**: `Database/Migrations/` — chỉ **thêm mới / cột nullable** với bảng đang có dữ liệu (xem `ARCHITECTURE_CHECKLIST.md` §3.1). Kiểm tra tên bảng (`lb_` chỉ cho growth/business/CRM).
5. **Views**: `Resources/views/` + `loadViewsFrom(..., 'aliasthuong')`; `@extends(theme_view('layouts.app'))` + `<x-ui.*>`.
6. **Sidebar**: `register_user_sidebar_section/item` (portal) hoặc `register_sidebar_item`/`register_setting_item` (admin); `visible` gate theo `canUsePlanFeature()`.
7. **Plan permission**: `register_plan_permission('key', [...])` + limit keys; thêm env `MLHUB_NO_PLAN_*` tương ứng nếu cần fallback.
8. **Credit** (nếu AI): `register_credit_action('key', plan_key, default_cost)`; gọi `ensureCanConsume` → `consume_credits`.
9. **Env**: bổ sung `.env.example` (placeholder + comment) nếu đọc env mới.
10. **i18n**: chuỗi user-facing bọc `__()`; đồng bộ `lang/en.json` + `lang/vi.json`.
11. **Marketplace**: nếu là add-on → thêm provider vào `bootstrap/providers.marketplace.php` (KHÔNG sửa `bootstrap/providers.php`).
12. **Cập nhật tài liệu**: thêm dòng vào **file này** (§3 catalog + §12 public + §13 table + §14 plan) trong cùng commit; cập nhật `ARCHITECTURE_FEATURE.md` nếu liên quan readiness.
13. **Smoke test**: `php artisan route:list --name=portal` (local); vào route mới không 500; `vendor/bin/pint` file đã sửa.

---

*File này là nguồn sự thật về module. Khi thêm/bớt module hoặc đổi route/bảng/permission → cập nhật §3, §12, §13, §14 trong cùng commit. Kiến trúc hệ thống: `ARCHITECTURE_BACKEND.md`. Độ sẵn sàng & backlog: `ARCHITECTURE_FEATURE.md`.*
