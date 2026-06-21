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
- **4 module KHÔNG có `module.json*`* — nạp bằng **convention fallback** (`Modules\{Name}\Providers\{Name}ServiceProvider`): `AdminAffiliate`, `AdminMenuBuilder`, `AdminSettings`, `AppAffiliate`.
- 4 module có `module.json` **nhưng không khai `providers[]`** (cũng dùng convention fallback): `AdminCache`, `AdminLanguages`, `AdminLog`, `AdminSystemInformation`.
- **Auto-discovery** (`bootstrap/providers.php`): quét `modules/*` → đọc `module.json` (hoặc convention) → `require_once Support/helpers.php` nếu có → sắp theo `priority` tăng dần rồi theo tên → gộp `bootstrap/providers.marketplace.php`. **KHÔNG sửa `bootstrap/providers.php`.**
- **Marketplace providers** (`bootstrap/providers.marketplace.php`): `CustomMLHUB`, `AppLoyaltyStampCards`.
- **Cấu trúc điển hình một module:** `module.json`, `Providers/`, `Routes/web.php`, `Http/Controllers/` (chỉ public form/webhook/download), `Livewire/` (trang full-page), `Models/`, `Support/` (helper không trạng thái), `Services/` (workflow), `Resources/views/` (`loadViewsFrom(..., 'aliasthuong')`), `Database/Migrations/`, `config/config.php` (gộp `config('modules.aliasthuong.*')`).
- **Route prefix:** đa số hard-code trong `Routes/web.php`. Chỉ `AdminDashboard` (`admin/dashboard`) và `AdminUser` (`admin/users`) khai `route_prefix` trong `config/config.php`.

> **Lưu ý bảng DB:** tiền tố `lb_` chỉ áp dụng cho **engine MLHUB growth + business/customer** (`lb_campaigns`, `lb_businesses`, `lb_customers`, `lb_loyalty_*`, `lb_crm_*`, `lb_email_*`, `lb_google_*`…). **Nhiều bảng KHÔNG dùng `lb_`**: `users`, `plans`, `teams`, `admin_roles`, `audit_logs`, `coupons`, `blogs`, `faqs`, `languages`, `support_tickets`, `notifications`, `options`, `payment_*`, `payment_subscriptions`, `marketplace_packages`, `affiliate_*`, `credit_*`, `files`, `custom_domains`, `ai_templates`, `ai_usage_logs`, `ai_studio_*`, `ai_prompt_histories`, `ai_content_plans`, `ai_image_jobs`. Khi viết query/migration **kiểm tra tên bảng thật trong model** (`protected $table`).

---

## 3. Module catalog tổng hợp (72)

> `mj` = có `module.json`. `prio` = priority. Trạng thái = sẵn sàng production.
>
> **Lưu ý route:** Một số module có legacy alias route dạng `settings/*`; xem phần chi tiết từng module để biết route đầy đủ.

### 3.1 Admin* (31) — khu quản trị, prefix `admin/`


| Module                    | mj  | prio | Route prefix chính                                                           | Model → bảng                                                                                                                 | Tính năng                                      | Trạng thái |
| ------------------------- | --- | ---- | ---------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------- | ---------- |
| AdminDashboard            | ✓   | 0    | `admin/dashboard`                                                            | —                                                                                                                            | Trang chủ super-admin + layout                 | ✅          |
| AdminUser                 | ✓   | —    | `admin/users`, `admin/user-roles`, `admin/user-teams`, `admin/settings/auth` | `User→users`, `AdminRole→admin_roles`, `Team→teams`, `AuditLog→audit_logs`                                                   | User/role/team, impersonate, auth rules, audit | 🟡         |
| AdminPlans                | ✓   | —    | `admin/plans`                                                                | `AdminPlan→plans`                                                                                                            | CRUD gói + `Plan`/`Pricing` facade             | ✅          |
| AdminCredits              | ✓   | 0    | `admin/credits`                                                              | *(dùng AppCredits)*                                                                                                          | Gói credit, ledger, usage                      | 🟡         |
| AdminCoupons              | ✓   | —    | `admin/coupons`                                                              | `Coupon→coupons`                                                                                                             | Mã giảm giá billing                            | 🟡         |
| AdminManualPayments       | ✓   | —    | `admin/manual-payments`                                                      | `ManualPayment→payment_manual`                                                                                               | Duyệt thanh toán thủ công                      | 🟡         |
| AdminPaymentManualConfig  | ✓   | —    | redirect → `admin/settings/payment-gateways`                                 | —                                                                                                                            | Shim redirect (legacy) sang AppPayments        | 🟠         |
| AdminPaymentHistory       | ✓   | —    | `admin/payment-history`                                                      | `PaymentHistory→payment_history`                                                                                             | Lịch sử giao dịch                              | 🟡         |
| AdminPaymentSubscriptions | ✓   | —    | `admin/payment-subscriptions`                                                | `PaymentSubscription→payment_subscriptions`                                                                                  | Quản lý subscription                           | 🟠         |
| AdminPaymentReport        | ✓   | —    | `admin/payment-report`                                                       | *(query, no model)*                                                                                                          | Báo cáo thanh toán                             | 🟡         |
| AdminMarketplace          | ✓   | 0    | `admin/marketplace`                                                          | `MarketplacePackage→marketplace_packages`                                                                                    | Marketplace module + license                   | 🟡         |
| AdminThemes               | ✓   | 0    | `admin/themes`                                                               | *(file-based)*                                                                                                               | Theme, Custom CSS/JS                           | ✅          |
| AdminSettings             | ✗   | —    | `admin/settings/{general,analytics,embed-code,static-pages,security}`        | *(OptionStore→options)*                                                                                                      | Cài đặt lõi + static pages                     | ✅          |
| AdminCaptcha              | ✓   | 0    | `admin/settings/captcha`                                                     | *(options)*                                                                                                                  | Turnstile / reCAPTCHA v2                       | ✅          |
| AdminMailServer           | ✓   | —    | `admin/settings/mail-server`                                                 | *(options)*                                                                                                                  | Cấu hình SMTP/sender                           | ✅          |
| AdminCache                | ✓   | 0    | `admin/settings/cache`                                                       | *(CacheActionRegistry)*                                                                                                      | Xóa cache/session                              | ✅          |
| AdminCrons                | ✓   | 0    | `admin/settings/crons`, public `cron/{task}`                                 | *(SystemCronRegistry)*                                                                                                       | Registry scheduler + secure cron URL           | 🟡         |
| AdminLog                  | ✓   | —    | `admin/settings/log`                                                         | *(LogManager)*                                                                                                               | Xem/tải/xóa `laravel.log`                      | ✅          |
| AdminSystemInformation    | ✓   | —    | `admin/settings/system-information`                                          | —                                                                                                                            | Chẩn đoán PHP/Laravel/env                      | ✅          |
| AdminMenuBuilder          | ✗   | —    | `admin/menu-builder`                                                         | *(options)*                                                                                                                  | Kéo-thả sắp xếp sidebar                        | 🟡         |
| AdminLanguages            | ✓   | 0    | `admin/languages`, public `lang/{locale}`                                    | `Language→languages`, `LanguageTranslation→language_translations`                                                            | Locale + override dịch DB                      | 🟡         |
| AdminBlogs                | ✓   | —    | `admin/blogs`                                                                | `Blog→blogs`, `BlogRssSource→blog_rss_sources`, `BlogRssImport→blog_rss_imports`                                             | Blog + AI content + RSS import                 | 🟡         |
| AdminBlogCategories       | ✓   | —    | `admin/blog-categories`                                                      | `BlogCategory→blog_categories`                                                                                               | Danh mục blog (sidebar dưới AdminBlogs)        | 🟡         |
| AdminBlogTags             | ✓   | —    | `admin/blog-tags`                                                            | `BlogTag→blog_tags`                                                                                                          | Tag blog (sidebar dưới AdminBlogs)             | 🟡         |
| AdminFaqs                 | ✓   | —    | `admin/faqs`                                                                 | `Faq→faqs`                                                                                                                   | Quản lý FAQ                                    | 🟡         |
| AdminSupport              | ✓   | —    | `admin/support`                                                              | `SupportTicket→support_tickets` (+ category/label/type/comment)                                                              | Help desk ticket + taxonomy                    | 🟡         |
| AdminNotifications        | ✓   | —    | `admin/notifications`                                                        | `Notification→notifications`, `NotificationManual→notification_manual`, `NotificationManualState→notification_manual_states` | Broadcast + panel in-app                       | 🟡         |
| AdminAI                   | ✓   | —    | `admin/settings/ai-config`, `admin/ai-usage-logs`, `admin/ai-report`         | `AiUsageLog→ai_usage_logs`                                                                                                   | Cấu hình provider AI, usage, report            | 🟠         |
| AdminAITemplate           | ✓   | —    | `admin/ai-templates`                                                         | `AiTemplate→ai_templates`                                                                                                    | CRUD prompt template AI                        | 🟡         |
| AdminAITemplateCategories | ✓   | —    | `admin/ai-template-categories`                                               | `AiTemplateCategory→ai_template_categories`                                                                                  | Taxonomy template AI                           | 🟡         |
| AdminAffiliate            | ✗   | —    | `admin/affiliate`                                                            | *(dùng AppAffiliate)*                                                                                                        | Quản trị affiliate, commission, withdrawal     | 🟠         |


### 3.2 App* (37) — portal khách hàng, prefix `portal/`


| Module                  | mj  | prio | Route prefix chính                                                     | Model → bảng                                                                                                                                                                    | Plan key                           | Public?                                               | Trạng thái  |
| ----------------------- | --- | ---- | ---------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------- | ----------------------------------------------------- | ----------- |
| AppBusinessProfiles     | ✓   | 20   | `portal/businesses`                                                    | `LocalBusiness→lb_businesses`                                                                                                                                                   | `localboost`                       | GET `/b/{business}`                                   | 🟡          |
| AppBusinessLocations    | ✓   | 21   | nested `.../locations`                                                 | `BusinessLocation→lb_locations`                                                                                                                                                 | *(localboost)*                     | GET `/l/{location}`                                   | 🟡          |
| AppCustomers            | ✓   | 22   | `portal/customers`                                                     | `Customer→lb_customers`                                                                                                                                                         | *(localboost)*                     | —                                                     | 🟡          |
| AppQRCampaigns          | ✓   | 21   | `portal/qr-campaigns`                                                  | `QrCampaign→lb_campaigns`, `QrScan→lb_qr_scans`                                                                                                                                 | *(localboost)*                     | GET `/qr/{slug}`(+svg/png)                            | 🟠          |
| AppReviewBooster        | ✓   | 22   | `portal/review-booster`                                                | `ReviewFeedback→lb_review_feedbacks`                                                                                                                                            | *(localboost)*                     | POST feedback (throttle)                              | 🟡          |
| AppBookingPages         | ✓   | 23   | `portal/booking-pages`                                                 | `Booking→lb_bookings`, `BookingService→lb_booking_services`                                                                                                                     | *(localboost)*                     | POST booking (throttle)                               | 🟠          |
| AppCouponCampaigns      | ✓   | 24   | `portal/coupon-campaigns`                                              | `CouponRedemption→lb_coupon_redemptions`                                                                                                                                        | *(localboost)*                     | POST coupon (throttle)                                | 🟠          |
| AppFeedbackForms        | ✓   | 25   | `portal/feedback-forms`                                                | `FeedbackResponse→lb_feedback_responses`                                                                                                                                        | *(localboost)*                     | POST feedback-form (throttle)                         | 🟠          |
| AppLeadForms            | ✓   | 26   | `portal/lead-forms`                                                    | `LeadSubmission→lb_lead_submissions`                                                                                                                                            | *(localboost)*                     | POST lead (throttle)                                  | 🟡          |
| AppLandingPages         | ✓   | 27   | `portal/landing-pages`                                                 | `LandingPage→lb_landing_pages`                                                                                                                                                  | *(localboost)* `max_landing_pages` | GET `/lp/{slug}`, POST submit (**no throttle**)       | 🟠          |
| AppMarketingTemplates   | ✓   | 28   | `portal/marketing-templates`                                           | `MarketingTemplate→lb_marketing_templates`                                                                                                                                      | *(localboost)* `max_templates`     | —                                                     | 🟡          |
| AppAdvancedCustomerCrm  | ✓   | 31   | `portal/crm`                                                           | `lb_customer_*`, `lb_crm_*` (10 model)                                                                                                                                          | `advanced_crm`                     | —                                                     | 🟡          |
| AppEmailAutomation      | ✓   | 0    | `portal/email-automation`                                              | `lb_email_automations`, `lb_email_templates`, `lb_email_automation_logs`                                                                                                        | `email_automation`                 | —                                                     | 🟠          |
| AppWebhookAutomation    | ✓   | 0    | `portal/webhook-automation`                                            | `lb_webhook_automations`, `lb_webhook_automation_logs`                                                                                                                          | `webhook_automation`               | —                                                     | 🟡          |
| AppWhatsAppNotification | ✓   | 0    | `portal/whatsapp-notification`                                         | `lb_whatsapp_notifications`, `lb_whatsapp_templates`, `lb_whatsapp_notification_logs`                                                                                           | `whatsapp_notification`            | —                                                     | 🟡          |
| AppLoyaltyStampCards    | ✓   | 29   | `portal/loyalty-cards`                                                 | `lb_loyalty_*`, `lb_referral_*` (8 model)                                                                                                                                       | `loyalty_stamp_cards`              | `/loyalty/*`, `/referral/*`, `/r/*` (**no throttle**) | 🟠          |
| AppLocalAnalytics       | ✓   | 25   | `portal/reports`                                                       | *(aggregate, no model)*                                                                                                                                                         | *(localboost)*                     | —                                                     | 🟡          |
| AppGoogleBusiness       | ✓   | 0    | `portal/integrations/google-business`                                  | `lb_google_*` (7 model)                                                                                                                                                         | `google_business`                  | OAuth callback (auth-gated)                           | 🟠          |
| AppCustomDomain         | ✓   | 0    | `portal/brand/custom-domains`, `portal/qr-codes/domains`               | `AppCustomDomain→custom_domains`                                                                                                                                                | `qr_custom_domains`                | —                                                     | 🟡          |
| AppAIStudio             | ✓   | 0    | `portal/ai-studio`                                                     | `ai_studio_user_settings`, `ai_studio_workspace_settings`, `ai_prompt_histories`                                                                                                | `ai_studio`                        | —                                                     | ✅           |
| AppAIContent            | ✓   | 0    | `portal/ai-studio/ai-content`                                          | —                                                                                                                                                                               | `ai_studio_caption_generator`      | —                                                     | ✅           |
| AppAIContentPlanner     | ✓   | 0    | `portal/ai-studio/planner`                                             | `AIContentPlan→ai_content_plans`                                                                                                                                                | `ai_studio_content_planner`        | —                                                     | 🟡          |
| AppAIRepurpose          | ✓   | 0    | `portal/ai-studio/repurpose`                                           | —                                                                                                                                                                               | `ai_studio_repurpose`              | —                                                     | 🟡          |
| AppAIImage              | ✓   | 0    | `portal/ai-studio/image`                                               | `AIImageJob→ai_image_jobs`                                                                                                                                                      | `ai_studio_image`                  | —                                                     | 🟡          |
| AppAIVideo              | ✓   | 0    | *(config `portal/ai-studio/video` — **routes KHÔNG nạp**)*             | `AIVideoJob→ai_video_jobs`                                                                                                                                                      | — *(credit chưa register)*         | —                                                     | 🔴 Disabled |
| AppAIReview             | ✓   | 0    | *(config `portal/ai-studio/review` — **routes KHÔNG nạp**)*            | —                                                                                                                                                                               | — *(credit chưa register)*         | —                                                     | 🔴 Disabled |
| AppAIBestTime           | ✓   | 0    | *(config `portal/ai-studio/timing` — **routes KHÔNG nạp**)*            | —                                                                                                                                                                               | — *(credit chưa register)*         | —                                                     | 🔴 Disabled |
| AppAISemanticSearch     | ✓   | 0    | *(config `portal/ai-studio/search` — **routes KHÔNG nạp**)*            | —                                                                                                                                                                               | — *(credit chưa register)*         | —                                                     | 🔴 Disabled |
| AppBilling              | ✓   | 0    | `portal/billing`, `portal/invoices`                                    | *(dùng model billing/subscription)*                                                                                                                                             | —                                  | —                                                     | 🟡          |
| AppPayments             | ✓   | 0    | `portal/packages`, `payment/{plan}`, `admin/settings/payment-gateways` | —                                                                                                                                                                               | —                                  | POST `/payment/webhook/{gateway}`                     | 🟠          |
| AppCredits              | ✓   | 0    | `portal/credits`, `payment/credits/{pack}`                             | `CreditPack→credit_packs`, `CreditTopupLedger→credit_topup_ledgers`, `CreditUsageLog→credit_usage_logs`                                                                         | —                                  | —                                                     | 🟡          |
| AppTeams                | ✓   | 0    | `portal/teams`                                                         | `TeamInvitation→team_invitations`, `TeamConversation→team_conversations`, `TeamMessage→team_messages`, `TeamPostComment→team_post_comments`, `TeamPostReview→team_post_reviews` | `teams`                            | —                                                     | 🟠          |
| AppFiles                | ✓   | 0    | `portal/files`, `admin/files`                                          | `AppFile→files`                                                                                                                                                                 | `files`                            | signed preview + OAuth picker callback                | 🟡          |
| AppProfile              | ✓   | 0    | `portal/profile`                                                       | *(dùng User)*                                                                                                                                                                   | —                                  | —                                                     | ✅           |
| AppSupport              | ✓   | 0    | `portal/support`                                                       | *(dùng AdminSupport models)*                                                                                                                                                    | `support`                          | —                                                     | 🟡          |
| AppAffiliate            | ✗   | 0    | `portal/affiliate`, `admin/settings/affiliate`                         | `AffiliateProfile→affiliate_profiles`, `AffiliateCommission→affiliate_commissions`, `AffiliateWithdrawal→affiliate_withdrawals`                                                 | `affiliate`                        | — *(capture qua middleware)*                          | 🟠          |
| AppIntegrations         | ✓   | 0    | `admin/integrations` *(admin-only)*                                    | —                                                                                                                                                                               | —                                  | —                                                     | 🟡          |


### 3.3 Payment* (3) + manual


| Module                   | mj  | Gateway key                  | Webhook                           | Trạng thái     |
| ------------------------ | --- | ---------------------------- | --------------------------------- | -------------- |
| PaymentStripe            | ✓   | `stripe`, `stripe_recurring` | POST `/payment/webhook/stripe`    | 🟠 Needs Audit |
| PaymentPaypal            | ✓   | `paypal`, `paypal_recurring` | POST `/payment/webhook/paypal`    | 🟠 Needs Audit |
| Payment2Checkout         | ✓   | `2checkout`                  | POST `/payment/webhook/2checkout` | 🟠 Needs Audit |
| *(AppPayments — manual)* | ✓   | `manual`                     | —                                 | 🟠             |


> **Đã gỡ (không còn module trong code):** các cổng Ấn Độ/Phi/Thổ/Nga (vd `PaymentRazorpay`, `PaymentPaystack`…). Sẽ tích hợp VNPay/MoMo sau qua module mới — không có dấu vết code hiện tại.

### 3.4 Custom* (1)


| Module      | mj  | prio | Vai trò                                                                                                                    |
| ----------- | --- | ---- | -------------------------------------------------------------------------------------------------------------------------- |
| CustomMLHUB | ✓   | 25   | Bootstrap VN: `mlhub:install`/`mlhub:update`/`mlhub:sync-env-options`, seeder admin/extras, site options, env→options sync, **MLHUB AI chat** (`portal/chatmlhubai`) |


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
  - **Industry taxonomy (Alternative Data):** `Support/BusinessTypeCatalog` là source of truth (versioned, `TAXONOMY_VERSION`): 18 nhóm (9 ưu tiên Đà Nẵng–Quảng Nam), full cây ngành con + alias search (có dấu/không dấu/EN), legacy mapping → `lb_businesses.type`, signals/recommended_modules/campaign_goals/dashboard_preset/template_pack/`compliance_sensitive` (nhóm health = true, không gợi ý claim y tế). Onboarding lưu `industry_group_code` + `industry_category_code` + `taxonomy_version`; `industry_metadata` chỉ là **snapshot** tại thời điểm lưu. Business cũ chỉ có `type` được **suy ra** group/category khi edit (`inferFromLegacyType`), không ép migrate.
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


| Tool           | Module                | type       | Portal route                 | Model kết quả                        |
| -------------- | --------------------- | ---------- | ---------------------------- | ------------------------------------ |
| Review Booster | AppReviewBooster      | `review`   | `portal.review-booster`      | `lb_review_feedbacks`                |
| Booking        | AppBookingPages       | `booking`  | `portal.booking-pages`       | `lb_bookings`, `lb_booking_services` |
| Coupons        | AppCouponCampaigns    | `coupon`   | `portal.coupon-campaigns`    | `lb_coupon_redemptions`              |
| Feedback       | AppFeedbackForms      | `feedback` | `portal.feedback-forms`      | `lb_feedback_responses`              |
| Lead           | AppLeadForms          | `lead`     | `portal.lead-forms`          | `lb_lead_submissions`                |
| QR/URL         | AppQRCampaigns        | `url`      | `portal.qr-campaigns`        | `lb_qr_scans`                        |
| Landing        | AppLandingPages       | —          | `portal.landing-pages`       | `lb_landing_pages`                   |
| Templates      | AppMarketingTemplates | —          | `portal.marketing-templates` | `lb_marketing_templates` (+ packs)   |


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


| Module              | Route                         | Credit action (cost)                            | Trạng thái  |
| ------------------- | ----------------------------- | ----------------------------------------------- | ----------- |
| AppAIStudio         | `portal/ai-studio`            | `ai_studio_review_reply` (1)                    | ✅           |
| AppAIContent        | `portal/ai-studio/ai-content` | `ai_studio_generate_captions` (1)               | ✅           |
| AppAIContentPlanner | `portal/ai-studio/planner`    | `ai_studio_plan_calendar` (1)                   | 🟡          |
| AppAIRepurpose      | `portal/ai-studio/repurpose`  | `ai_studio_repurpose_content` (1)               | 🟡          |
| AppAIImage          | `portal/ai-studio/image`      | `ai_studio_generate_image` (3)                  | 🟡          |
| AppAIVideo          | *(routes không nạp)*          | `ai_studio_generate_video` — **chưa register**  | 🔴 Disabled |
| AppAIReview         | *(routes không nạp)*          | `ai_studio_review_content` — **chưa register**  | 🔴 Disabled |
| AppAIBestTime       | *(routes không nạp)*          | `ai_studio_best_time` — **chưa register**       | 🔴 Disabled |
| AppAISemanticSearch | *(routes không nạp)*          | `ai_studio_semantic_search` — **chưa register** | 🔴 Disabled |


- **Credit action register:** chỉ 5 key đầu được `register_credit_action` trong `AppAIStudioServiceProvider` (cost theo `credit_cost_*`). 4 key sau dùng trong code module dormant nhưng **chưa đăng ký**.
- **Provider/API key AI:** lưu trong **Admin Settings (OptionStore)**, **KHÔNG** trong `.env` (không có `OPENAI_*`/`ANTHROPIC_*`/`PRISM_*` trong `config/` hoặc `modules/`).
- **4 module dormant** (`AppAIVideo`, `AppAIReview`, `AppAIBestTime`, `AppAISemanticSearch`): còn trên đĩa nhưng `boot()` không `loadRoutesFrom` → không truy cập được. Quyết định gỡ hẳn hay hoàn thiện → backlog `ARCHITECTURE_FEATURE.md`.

---

## 9. Payment modules (chi tiết)

Mẫu plugin: mỗi `Payment*ServiceProvider` (1) khai `PaymentGatewayDefinition` (key/nhãn/năng lực/tiền tệ), (2) bind gateway vào contract `Modules\AppPayments\Contracts\PaymentGateway`, (3) gọi `PaymentGatewaySettingsRegistry::register('key', [...])` để sinh UI cấu hình ở Admin.


| Module               | Gateway key                  | Settings registry | Webhook / callback                                                           |
| -------------------- | ---------------------------- | ----------------- | ---------------------------------------------------------------------------- |
| PaymentStripe        | `stripe`, `stripe_recurring` | `stripe`          | `/payment/webhook/stripe`, success/cancel `/payment/{success,cancel}/stripe` |
| PaymentPaypal        | `paypal`, `paypal_recurring` | `paypal`          | `/payment/webhook/paypal` (+ success/cancel)                                 |
| Payment2Checkout     | `2checkout`                  | `2checkout`       | `/payment/webhook/2checkout`, checkout `payment.2checkout.checkout`          |
| AppPayments (manual) | `manual`                     | —                 | — (duyệt qua AdminManualPayments)                                            |


- **Credential:** lưu trong **OptionStore** (`stripe_*`, `paypal_*`, `2checkout_*`); riêng `STRIPE_*` có thể seed từ env qua `MLHUBEnvOptionsSync`. `PAYPAL_*`/`2CHECKOUT_*` chỉ ở OptionStore, **không** đọc env.
- **Webhook chung:** `POST /payment/webhook/{gateway}` (route `payment.webhook`) — **không** throttle ở route (backlog: kiểm tra idempotency).

---

## 10. CustomMLHUB


| Thành phần   | Chi tiết                                                                                                                                                                                                                          |
| ------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Commands     | `mlhub:install` (migrate:fresh + seed từ ID 147123468 + optimize; hỏi xác nhận / `--force`), `mlhub:update` (migrate + seed đồng bộ catalog `mlhub-*`, giữ user/campaign/plan legacy), `mlhub:sync-env-options` (env → `options`) |
| Seeders      | `MLHUBAdminSeeder`, `MLHUBSystemExtrasSeeder` (chain qua `config/mlhub.php` → `default_seeders`); `PlanSeeder` (catalog 13 gói)                                                                                                   |
| Config       | `modules/CustomMLHUB/config/config.php`, `config/env_options.php` (map env → option), app `config/mlhub.php`                                                                                                                      |
| Portal AI    | Route `portal/chatmlhubai` (`portal.chatmlhubai`), Livewire `ChatMLHUBAI` + widget dashboard `MLHUBAIDashboardPanel`; service `Support/MLHUBAIAssistant/*` (fallback số liệu thật + lớp OpenAI/Gemini qua `ai_chat_*`); credit `mlhub_ai_chat` / `credit_cost_mlhub_ai_chat`. Ma trận intent/context/định vị Chat vs Studio → `ARCHITECTURE_MLHUBAI.md`. |
| Site options | `Database/data/mlhub_site_options.php` (format ngày `d/m/Y`, VND `₫`, timezone)                                                                                                                                                   |
| Env đọc      | `MLHUB_STARTING_ID`, `MLHUB_FIRST_USER_*`, `MLHUB_ALLOW_RESET_DEMO`, `MLHUB_SYNC_ENV_OPTIONS` + toàn bộ key trong `env_options.php`                                                                                               |
| Flow         | DB trống → `mlhub:install`; giữ dữ liệu → `mlhub:update`; mỗi deploy entrypoint chạy `migrate --force` + `mlhub:sync-env-options`                                                                                                 |


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


| Route name                     | Method | Path                             | Throttle    | Captcha | Module               |
| ------------------------------ | ------ | -------------------------------- | ----------- | ------- | -------------------- |
| `qr-campaigns.public`          | GET    | `/qr/{campaign:slug}`            | —           | —       | AppQRCampaigns       |
| `qr-campaigns.svg` / `.png`    | GET    | `/qr/{slug}/qr.{svg,png}`        | —           | —       | AppQRCampaigns       |
| `review-booster.feedback`      | POST   | `/qr/{slug}/feedback`            | ✅           | ✗       | AppReviewBooster     |
| `booking-pages.submit`         | POST   | `/qr/{slug}/booking`             | ✅           | ✗       | AppBookingPages      |
| `coupon-campaigns.claim`       | POST   | `/qr/{slug}/coupon`              | ✅           | ✗       | AppCouponCampaigns   |
| `feedback-forms.submit`        | POST   | `/qr/{slug}/feedback-form`       | ✅           | ✗       | AppFeedbackForms     |
| `lead-forms.submit`            | POST   | `/qr/{slug}/lead`                | ✅           | ✗       | AppLeadForms         |
| `landing-pages.public`         | GET    | `/lp/{slug}`                     | —           | —       | AppLandingPages      |
| `landing-pages.submit`         | POST   | `/lp/{slug}`                     | 🔴 **chưa** | ✗       | AppLandingPages      |
| `landing-pages.qr` / `.qr.png` | GET    | `/lp/{slug}/qr.{svg,png}`        | —           | —       | AppLandingPages      |
| `loyalty-cards.public`         | GET    | `/loyalty/{card:slug}`           | —           | —       | AppLoyaltyStampCards |
| `loyalty-cards.stamp`          | POST   | `/loyalty/{card:slug}/stamp`     | 🔴 **chưa** | ✗       | AppLoyaltyStampCards |
| `referral-campaigns.public`    | GET    | `/referral/{campaign:slug}`      | —           | —       | AppLoyaltyStampCards |
| `referral-campaigns.link`      | POST   | `/referral/{campaign:slug}/link` | 🔴 **chưa** | ✗       | AppLoyaltyStampCards |
| `referral-links.public`        | GET    | `/r/{link:code}`                 | —           | —       | AppLoyaltyStampCards |
| `referral-links.convert`       | POST   | `/r/{link:code}/convert`         | 🔴 **chưa** | ✗       | AppLoyaltyStampCards |
| `businesses.public`            | GET    | `/b/{business}`                  | —           | —       | AppBusinessProfiles  |
| `locations.public`             | GET    | `/l/{location}`                  | —           | —       | AppBusinessLocations |
| `payment.webhook`              | POST   | `/payment/webhook/{gateway}`     | —           | —       | AppPayments          |
| `cron.run`                     | GET    | `/cron/{task}`                   | secure key  | —       | AdminCrons           |
| `*.public` lang                | GET    | `/lang/{locale}`                 | —           | —       | AdminLanguages       |


> **Backlog (P1/P2):** `landing-pages.submit` + 4 route POST loyalty/referral **chưa throttle**; captcha **chưa gắn** vào form growth/loyalty công khai. Xem `ARCHITECTURE_FEATURE.md` §14.

---

## 13. Table map theo module

> Migration: baseline `database/migrations/2026_*_create_database.php` + `modules/*/Database/Migrations/`.


| Module                  | Bảng                                                                                                                                                                                   | Notes                                 |
| ----------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------- |
| AdminUser               | `users`, `admin_roles`, `teams`, `audit_logs`                                                                                                                                          | Không `lb_`                           |
| AdminPlans              | `plans`                                                                                                                                                                                | —                                     |
| AdminCoupons            | `coupons`                                                                                                                                                                              | Billing coupon (khác coupon campaign) |
| Admin payments          | `payment_manual`, `payment_history`, `payment_subscriptions`                                                                                                                           | —                                     |
| AdminMarketplace        | `marketplace_packages`                                                                                                                                                                 | —                                     |
| AdminBlogs(+cat/tag)    | `blogs`, `blog_categories`, `blog_tags`, `blog_rss_sources`, `blog_rss_imports`                                                                                                        | —                                     |
| AdminFaqs               | `faqs`                                                                                                                                                                                 | —                                     |
| AdminSupport            | `support_tickets`, `support_categories`, `support_labels`, `support_types`, `support_comments`                                                                                         | —                                     |
| AdminNotifications      | `notifications`, `notification_manual`, `notification_manual_states`                                                                                                                   | —                                     |
| AdminLanguages          | `languages`, `language_translations`                                                                                                                                                   | —                                     |
| AdminSettings           | `options`                                                                                                                                                                              | OptionStore key/value                 |
| AdminAI(+template)      | `ai_usage_logs`, `ai_templates`, `ai_template_categories`                                                                                                                              | —                                     |
| AppBusinessProfiles     | `lb_businesses` (+ taxonomy cols: `industry_group_code`, `industry_category_code`, `industry_metadata` json snapshot, `taxonomy_version`; legacy `type` giữ làm fallback)              | Industry taxonomy ở `Support/BusinessTypeCatalog` |
| AppBusinessLocations    | `lb_locations`                                                                                                                                                                         | —                                     |
| AppCustomers            | `lb_customers`                                                                                                                                                                         | Mở rộng bởi CRM                       |
| Growth engine           | `lb_campaigns`, `lb_qr_scans`, `lb_review_feedbacks`, `lb_bookings`, `lb_booking_services`, `lb_coupon_redemptions`, `lb_feedback_responses`, `lb_lead_submissions`                    | —                                     |
| AppLandingPages         | `lb_landing_pages`                                                                                                                                                                     | —                                     |
| AppMarketingTemplates   | `lb_marketing_templates`, `lb_template_packs`, `lb_template_pack_items`, `lb_template_usages`, `lb_template_imports`, `lb_template_ratings`                                            | —                                     |
| AppAdvancedCustomerCrm  | `lb_customer_{activities,tags,tag_maps,notes,tasks,segments,score_logs,merge_logs}`, `lb_crm_automations`, `lb_crm_automation_logs`, `lb_crm_automation_jobs`                          | Mở rộng `lb_customers`                |
| AppEmailAutomation      | `lb_email_templates`, `lb_email_automations`, `lb_email_automation_logs`                                                                                                               | —                                     |
| AppWebhookAutomation    | `lb_webhook_automations`, `lb_webhook_automation_logs`                                                                                                                                 | —                                     |
| AppWhatsAppNotification | `lb_whatsapp_templates`, `lb_whatsapp_notifications`, `lb_whatsapp_notification_logs`                                                                                                  | —                                     |
| AppLoyaltyStampCards    | `lb_loyalty_{cards,customers,stamps,rewards}`, `lb_referral_{campaigns,links}`, `lb_referrals`, `lb_referral_rewards`                                                                  | —                                     |
| AppGoogleBusiness       | `lb_google_business_connections`, `lb_google_business_locations`, `lb_google_reviews`, `lb_google_auto_reply_{rules,logs}`, `lb_google_business_posts`, `lb_google_business_post_logs` | —                                     |
| AppCustomDomain         | `custom_domains`                                                                                                                                                                       | Không `lb_`                           |
| AppTeams                | `team_invitations`, `team_conversations`, `team_messages`, `team_post_comments`, `team_post_reviews`                                                                                   | Không `lb_`                           |
| AppCredits/AdminCredits | `credit_packs`, `credit_topup_ledgers`, `credit_usage_logs`                                                                                                                            | Không `lb_`                           |
| AppFiles                | `files`                                                                                                                                                                                | Không `lb_`                           |
| AppAffiliate            | `affiliate_profiles`, `affiliate_commissions`, `affiliate_withdrawals`                                                                                                                 | Không `lb_`                           |
| AI Studio               | `ai_studio_user_settings`, `ai_studio_workspace_settings`, `ai_prompt_histories`, `ai_content_plans`, `ai_image_jobs`, `ai_video_jobs`                                                 | Không `lb_`                           |


> Session/cache/queue chạy **Redis** (không cần bảng `sessions`/`cache`/`jobs` MySQL); riêng `failed_jobs`, `job_batches` vẫn ở MySQL.

---

## 14. Plan / permission map theo module


| Plan key                | Module đăng ký          | Limit keys chính                                                                                           | Env fallback (no-plan)                                    |
| ----------------------- | ----------------------- | ---------------------------------------------------------------------------------------------------------- | --------------------------------------------------------- |
| `mlhub`            | AppBusinessProfiles     | `max_businesses`, `max_campaigns`, `max_landing_pages`, `max_qr_codes`, `max_templates`, `remove_branding` | `MLHUB_NO_PLAN_MLHUB` (+ legacy `MLHUB_NO_PLAN_LOCALBOOST`) |
| `credits_usage`         | (AdminAI sub-feature)   | `credits_usage_limit`, `credit_cost_*`                                                                     | `MLHUB_NO_PLAN_CREDITS_*`                                 |
| `files`                 | AppFiles                | `max_storage_size_mb`, `max_file_size_mb`, `file_picker`, `image_editor`, `search_media_online`            | `MLHUB_NO_PLAN_FILES`, `..._MAX_STORAGE_MB`, `..._FILE_*` |
| `ai_studio`             | AppAIStudio             | `ai_studio_caption_generator`, `ai_studio_repurpose`, `ai_studio_content_planner`, `ai_studio_image`       | `MLHUB_NO_PLAN_AI_*`                                      |
| `teams`                 | AppTeams                | `max_team_members`                                                                                         | `MLHUB_NO_PLAN_TEAMS`, `..._MAX_TEAM_MEMBERS`             |
| `advanced_crm`          | AppAdvancedCustomerCrm  | `customer_tags`, `customer_segments`, `customer_tasks`, `crm_automations`, `crm_activity_retention_days`   | `MLHUB_NO_PLAN_CRM_*`                                     |
| `google_business`       | AppGoogleBusiness       | `max_google_business_connections/locations`, `google_review_sync/reply`, `google_insights`, `google_posts` | `MLHUB_NO_PLAN_GOOGLE_*`                                  |
| `email_automation`      | AppEmailAutomation      | `max_email_automations/templates`, `emails_per_month`, `automation_delay/conditions`                       | `MLHUB_NO_PLAN_EMAIL_*`                                   |
| `whatsapp_notification` | AppWhatsAppNotification | `max_whatsapp_*`, `whatsapp_messages_per_month`, `whatsapp_cloud_api`, `whatsapp_template_msg`             | `MLHUB_NO_PLAN_WHATSAPP*`                                 |
| `webhook_automation`    | AppWebhookAutomation    | `max_webhook_automations`, `webhooks_per_month`, `webhook_custom_headers`, `webhook_retry`                 | `MLHUB_NO_PLAN_WEBHOOK*`                                  |
| `loyalty_stamp_cards`   | AppLoyaltyStampCards    | `max_loyalty_cards/customers`, `max_referral_campaigns`, `loyalty_rewards`, `loyalty_staff_redeem`         | `MLHUB_NO_PLAN_LOYALTY*`                                  |
| `qr_custom_domains`     | AppCustomDomain         | `max_custom_domains`                                                                                       | `MLHUB_NO_PLAN_CUSTOM_DOMAINS`, `..._MAX_CUSTOM_DOMAINS`  |
| `affiliate`             | AppAffiliate            | toggle                                                                                                     | `MLHUB_NO_PLAN_AFFILIATE`                                 |
| `support`               | AppSupport              | toggle                                                                                                     | `MLHUB_NO_PLAN_SUPPORT`                                   |


- **Enforcement:** `App\Support\Plans\PlanLimitGuard` (`ensureBusinessCanBeCreated`, `ensureCampaignCanBeCreated`, …) ném `ValidationException::withMessages(['plan' => …])` khi vượt.
- **No-plan fallback:** user `plan_id=null` → `MLHUB_NO_PLAN_*` env → `config/mlhub.php` → `NoPlanAccess::permissionsFromEnv()`. Sidebar addon chỉ hiện khi cờ tương ứng `true`; vào URL trực tiếp khi tắt → `403`. Ma trận đầy đủ: `ARCHITECTURE_FEATURE.md` §1.1, env: `.env.example`.

---

## 15. Production readiness theo module


| Nhóm             | Module                                          | Trạng thái  | Rủi ro chính cần test                                      |
| ---------------- | ----------------------------------------------- | ----------- | ---------------------------------------------------------- |
| Auth/Plan        | AdminUser, AdminPlans, AppProfile               | ✅/🟡        | IDOR tenant, ma trận role                                  |
| Growth public    | Review/Booking/Coupon/Feedback/Lead             | 🟡/🟠       | P1 race (booking double, coupon overshoot), P2 captcha/XSS |
| QR               | AppQRCampaigns                                  | 🟠          | P2 lọc bot trong `recordScan`                              |
| Landing          | AppLandingPages                                 | 🟠          | P1 `landing-pages.submit` chưa throttle                    |
| Loyalty/Referral | AppLoyaltyStampCards                            | 🟠          | P1 public POST chưa throttle                               |
| Payment          | Stripe/Paypal/2Checkout/manual                  | 🟠          | P0 webhook thật + idempotency + hoàn tiền                  |
| CRM/Automation   | CRM, Email, Webhook, WhatsApp                   | 🟡/🟠       | queue + SMTP + trigger event                               |
| Google Business  | AppGoogleBusiness                               | 🟠          | OAuth bộ 2, sync review/post                               |
| AI active        | AIStudio, AIContent, Planner, Repurpose, Image  | ✅/🟡        | provider key thật, credit trừ đúng                         |
| AI dormant       | AIVideo, AIReview, AIBestTime, AISemanticSearch | 🔴 Disabled | Quyết định gỡ/hoàn thiện (credit chưa register)            |


Chi tiết ma trận + việc cần làm: `ARCHITECTURE_FEATURE.md`.

---

## 16. Checklist khi thêm module mới

1. `**module.json`**: `{ name, providers[], priority }`. Growth tool đặt `priority > 20`. (Hoặc dùng convention fallback — nhưng nên khai rõ.)
2. **Provider**: `{Name}ServiceProvider` — `register()` chỉ `mergeConfigFrom`; `boot()` `loadRoutesFrom/loadViewsFrom/loadMigrationsFrom` + registry helper.
3. **Routes**: `Routes/web.php` — portal sau `['web','auth','verified']`; public sau `web` (+ `throttle` nếu form POST; cân nhắc captcha).
4. **Migrations**: `Database/Migrations/` — chỉ **thêm mới / cột nullable** với bảng đang có dữ liệu (xem `ARCHITECTURE_CHECKLIST.md` §3.1). Kiểm tra tên bảng (`lb_` chỉ cho growth/business/CRM).
5. **Views**: `Resources/views/` + `loadViewsFrom(..., 'aliasthuong')`; `@extends(theme_view('layouts.app'))` + `<x-ui.*>`.
6. **Sidebar**: `register_user_sidebar_section/item` (portal) hoặc `register_sidebar_item`/`register_setting_item` (admin); `visible` gate theo `canUsePlanFeature()`.
7. **Plan permission**: `register_plan_permission('key', [...])` + limit keys; thêm env `MLHUB_NO_PLAN_`* tương ứng nếu cần fallback.
8. **Credit** (nếu AI): `register_credit_action('key', plan_key, default_cost)`; gọi `ensureCanConsume` → `consume_credits`.
9. **Env**: bổ sung `.env.example` (placeholder + comment) nếu đọc env mới.
10. **i18n**: chuỗi user-facing bọc `__()`; đồng bộ `lang/en.json` + `lang/vi.json`.
11. **Marketplace**: nếu là add-on → thêm provider vào `bootstrap/providers.marketplace.php` (KHÔNG sửa `bootstrap/providers.php`).
12. **Cập nhật tài liệu**: thêm dòng vào **file này** (§3 catalog + §12 public + §13 table + §14 plan) trong cùng commit; cập nhật `ARCHITECTURE_FEATURE.md` nếu liên quan readiness.
13. **Smoke test**: `php artisan route:list --name=portal` (local); vào route mới không 500; `vendor/bin/pint` file đã sửa.

---

*File này là nguồn sự thật về module. Khi thêm/bớt module hoặc đổi route/bảng/permission → cập nhật §3, §12, §13, §14 trong cùng commit. Kiến trúc hệ thống: `ARCHITECTURE_BACKEND.md`. Độ sẵn sàng & backlog: `ARCHITECTURE_FEATURE.md`.*



---

## 17. Lộ trình kiểm thử module theo nhóm

> Mục tiêu của phần này: chia toàn bộ module thành các cụm kiểm thử nhỏ để mỗi ngày có thể xử lý dứt điểm một nhóm, tránh test dàn trải 72 module cùng lúc.
> Nguyên tắc kiểm thử: đi theo hành trình thật của user trước, sau đó mới kiểm admin, billing, payment, AI nâng cao và các module phụ trợ.

---

### 17.1. Nguyên tắc chung khi test module

Mỗi module khi test phải có đủ 6 đầu ra:

1. **Route vào được không 500**
  - Kiểm tra route portal/admin/public.
  - Kiểm tra middleware `auth`, `verified`, `plan`, `public`, `throttle`.
2. **CRUD hoặc action chính chạy được**
  - Tạo mới.
  - Sửa.
  - Xóa/hủy nếu có.
  - Xem danh sách/detail.
3. **Dữ liệu ghi đúng bảng**
  - Kiểm tra model.
  - Kiểm tra `protected $table`.
  - Kiểm tra dữ liệu có scope đúng theo user/workspace/business không.
4. **Plan/permission hoạt động đúng**
  - User không có gói có bị chặn đúng không.
  - User có gói có dùng được không.
  - Limit có chặn khi vượt số lượng không.
5. **User-facing text đã chuẩn**
  - Không lẫn tiếng Anh/Vietnamese sai.
  - Không dùng thuật ngữ lệch định vị MLHUB/FizaMKT.
  - Không hiện module disabled cho user.
6. **Ghi nhận trạng thái sau test**
  - `PASS`: chạy ổn.
  - `FIXED`: đã phát hiện lỗi và sửa.
  - `BLOCKED`: cần dữ liệu/API/quyết định nghiệp vụ.
  - `SKIP`: chưa test vì module disabled hoặc chưa dùng trong MVP.

---

### 17.2. Nhóm A — Luồng nền tảng user/account/workspace

**Mục tiêu:** đảm bảo user đăng nhập, có profile, có plan, có workspace/team cơ bản, vào portal ổn.

**Module cần test:**


| Module       | Việc cần test                                   | Trạng thái mong muốn |
| ------------ | ----------------------------------------------- | -------------------- |
| AppProfile   | Hồ sơ cá nhân, đổi mật khẩu, 2FA card           | PASS                 |
| AdminUser    | User, role, team, impersonate, audit            | PASS/FIXED           |
| AdminPlans   | CRUD gói, plan permission, pricing facade       | PASS                 |
| AppBilling   | Gói hiện tại, subscription, invoice             | PASS/FIXED           |
| AppTeams     | Workspace, mời thành viên, role, chat, review   | FIXED hoặc BLOCKED   |
| AppSupport   | Ticket support trong portal                     | PASS/FIXED           |
| AdminSupport | Quản trị ticket, category, label, type, comment | PASS/FIXED           |


**Checklist test:**

- Tạo user mới.
- Gán plan cho user.
- Đăng nhập portal.
- Vào profile.
- Kiểm tra sidebar hiện đúng theo plan.
- Tạo team/workspace nếu module bật.
- Tạo ticket support thử.
- Admin đọc và phản hồi ticket.
- Kiểm tra user không xem được dữ liệu của user khác.

**Ngày gợi ý:** Day 1.

---

### 17.3. Nhóm B — Business core: hồ sơ kinh doanh, địa điểm, khách hàng

**Mục tiêu:** đây là lõi của MLHUB. User phải tạo được doanh nghiệp, địa điểm và bắt đầu lưu khách hàng.

**Module cần test:**


| Module                 | Việc cần test                                      | Trạng thái mong muốn |
| ---------------------- | -------------------------------------------------- | -------------------- |
| AppBusinessProfiles    | Tạo/sửa/xóa business, public `/b/{business}`       | PASS/FIXED           |
| AppBusinessLocations   | Tạo/sửa/xóa location, public `/l/{location}`       | PASS/FIXED           |
| AppCustomers           | Tạo/sửa/import/search khách hàng                   | PASS/FIXED           |
| AppAdvancedCustomerCrm | Tag, segment, task, note, activity, score          | PASS/FIXED           |
| AppLocalAnalytics      | Đọc dữ liệu tổng hợp từ business/customer/campaign | PASS/FIXED           |


**Checklist test:**

- Tạo business mẫu.
- Tạo 1–3 địa điểm.
- Tạo 20 khách hàng mẫu.
- Gắn tag cho khách.
- Tạo segment khách hàng.
- Tạo task chăm sóc khách.
- Kiểm tra dashboard/report có đọc đúng dữ liệu.
- Kiểm tra public profile business/location có hiện đúng và không lộ dữ liệu nhạy cảm.
- Kiểm tra limit `max_businesses` theo plan.

**Ngày gợi ý:** Day 2.

---

### 17.4. Nhóm C — Growth engine: QR campaign và 5 công cụ tăng trưởng

**Mục tiêu:** test xương sống FizaMKT: tạo chiến dịch, khách quét QR, submit form, hệ thống ghi customer và đo kết quả.

**Module cần test:**


| Module             | Type/Flow  | Việc cần test                            | Trạng thái mong muốn |
| ------------------ | ---------- | ---------------------------------------- | -------------------- |
| AppQRCampaigns     | `url` / QR | Tạo QR, tải SVG/PNG, public `/qr/{slug}` | FIXED                |
| AppReviewBooster   | `review`   | Khách gửi feedback/review                | PASS/FIXED           |
| AppBookingPages    | `booking`  | Đặt lịch, chống double booking           | FIXED                |
| AppCouponCampaigns | `coupon`   | Claim coupon, chống overshoot số lượng   | FIXED                |
| AppFeedbackForms   | `feedback` | Gửi khảo sát/phản hồi                    | FIXED                |
| AppLeadForms       | `lead`     | Gửi lead, tạo customer                   | PASS/FIXED           |


**Checklist test:**

- Tạo 1 QR campaign từng loại.
- Mở public link ở chế độ chưa đăng nhập.
- Submit từng form.
- Kiểm tra dữ liệu ghi vào bảng tương ứng.
- Kiểm tra `CustomerUpserter` có tạo/cập nhật khách hàng không.
- Kiểm tra tag/source/campaign attribution.
- Kiểm tra notification/dashboard metrics.
- Kiểm tra throttle public POST.
- Kiểm tra captcha nếu đã bật.
- Kiểm tra chống spam submit.
- Kiểm tra lỗi race condition với booking/coupon.

**Điểm cần sửa ưu tiên:**

- Lọc bot khi `recordScan`.
- Kiểm tra booking double.
- Kiểm tra coupon overshoot.
- Chuẩn hóa thông báo lỗi tiếng Việt.
- Đảm bảo public submit không tạo dữ liệu rác quá dễ.

**Ngày gợi ý:** Day 3–4.

---

### 17.5. Nhóm D — Landing page và marketing template

**Mục tiêu:** user tạo được landing page/campaign asset, có template mẫu để triển khai nhanh theo ngành.

**Module cần test:**


| Module                | Việc cần test                                             | Trạng thái mong muốn |
| --------------------- | --------------------------------------------------------- | -------------------- |
| AppLandingPages       | Tạo/sửa/publish landing, public `/lp/{slug}`, submit form | FIXED                |
| AppMarketingTemplates | Template, pack, import, usage, rating                     | PASS/FIXED           |
| AdminBlogs            | Blog, AI content, RSS import                              | PASS/FIXED           |
| AdminBlogCategories   | Danh mục blog                                             | PASS                 |
| AdminBlogTags         | Tag blog                                                  | PASS                 |
| AdminFaqs             | FAQ                                                       | PASS                 |


**Checklist test:**

- Tạo landing page mẫu cho F&B/Spa/Bán lẻ.
- Publish landing.
- Mở public link.
- Submit form landing.
- Kiểm tra `landing-pages.submit` đã có throttle.
- Kiểm tra XSS ở input public.
- Tạo template marketing mẫu.
- Tạo template pack theo ngành.
- Import/dùng template trong campaign.
- Kiểm tra user vượt `max_landing_pages` hoặc `max_templates` có bị chặn không.

**Điểm cần sửa ưu tiên:**

- Bổ sung throttle cho `POST /lp/{slug}`.
- Bổ sung captcha tùy cấu hình.
- Seed sẵn template mẫu theo ngành.
- Empty state: user mới chưa có landing/template thì gợi ý tạo từ mẫu.

**Ngày gợi ý:** Day 5.

---

### 17.6. Nhóm E — Loyalty, referral, affiliate

**Mục tiêu:** test vòng lặp khách quay lại và giới thiệu khách mới. Đây là nhóm quan trọng cho FizaMKT nhưng cần kiểm public endpoint kỹ.

**Module cần test:**


| Module               | Việc cần test                               | Trạng thái mong muốn |
| -------------------- | ------------------------------------------- | -------------------- |
| AppLoyaltyStampCards | Thẻ tích điểm, stamp, reward, referral      | FIXED                |
| AppAffiliate         | Referral/commission/withdrawal trong portal | FIXED                |
| AdminAffiliate       | Quản trị affiliate, commission, withdrawal  | FIXED                |


**Checklist test:**

- Tạo loyalty card.
- Khách mở public loyalty link.
- Ghi stamp cho khách.
- Đổi reward.
- Tạo referral campaign.
- Tạo referral link.
- Convert referral.
- Kiểm tra public POST loyalty/referral có throttle.
- Kiểm tra không tự spam stamp/reward.
- Kiểm tra affiliate capture qua middleware.
- Kiểm tra commission được ghi đúng.
- Kiểm tra withdrawal flow.

**Điểm cần sửa ưu tiên:**

- Bổ sung throttle cho:
  - `POST /loyalty/{card:slug}/stamp`
  - `POST /referral/{campaign:slug}/link`
  - `POST /r/{link:code}/convert`
- Bổ sung captcha cho public form nếu cần.
- Tách rõ loyalty/referral của HKD với affiliate bán gói phần mềm.
- Không cho user tự ghi reward sai quyền.

**Ngày gợi ý:** Day 6.

---

### 17.7. Nhóm F — Automation: CRM, Email, Webhook, WhatsApp

**Mục tiêu:** kiểm tra hệ thống chăm sóc lại sau khi khách tạo lead, booking, coupon, feedback, review.

**Module cần test:**


| Module                  | Việc cần test                            | Trạng thái mong muốn |
| ----------------------- | ---------------------------------------- | -------------------- |
| AppAdvancedCustomerCrm  | Automation, lifecycle, cleanup, activity | PASS/FIXED           |
| AppEmailAutomation      | Rule gửi email theo event                | FIXED                |
| AppWebhookAutomation    | Gửi webhook outbound                     | PASS/FIXED           |
| AppWhatsAppNotification | WhatsApp Cloud API, template, log        | PASS/FIXED           |
| AdminMailServer         | SMTP/sender                              | PASS                 |
| AdminCrons              | Scheduler/cron secure URL                | PASS/FIXED           |


**Checklist test:**

- Tạo automation khi khách mới được tạo.
- Tạo automation khi booking được tạo.
- Tạo automation khi coupon được claim.
- Tạo automation khi feedback/review được gửi.
- Kiểm tra queue chạy.
- Kiểm tra log automation.
- Kiểm tra retry/fail.
- Kiểm tra SMTP thật.
- Kiểm tra webhook gửi đúng payload.
- Kiểm tra WhatsApp không gửi khi thiếu credential.
- Kiểm tra cron command:
  - `crm:process-automations`
  - `crm:cleanup-activities`
  - `crm:lifecycle`

**Điểm cần sửa ưu tiên:**

- Không cho automation gửi trùng.
- Có log rõ ràng cho từng event.
- Khi thiếu SMTP/API key phải báo lỗi thân thiện.
- Thêm dữ liệu mẫu để demo “khách quét QR → hệ thống tự chăm sóc lại”.

**Ngày gợi ý:** Day 7–8.

---

### 17.8. Nhóm G — Google Business Profile và local presence

**Mục tiêu:** kiểm tra tích hợp Google Business, sync review, post và auto-reply. Đây là module quan trọng nhưng phụ thuộc API thật.

**Module cần test:**


| Module            | Việc cần test                                         | Trạng thái mong muốn |
| ----------------- | ----------------------------------------------------- | -------------------- |
| AppGoogleBusiness | OAuth, connection, location, review, post, auto-reply | BLOCKED/PASS         |
| AppLocalAnalytics | Đọc insight/review/post nếu có dữ liệu                | PASS/FIXED           |
| AdminAI           | AI config dùng cho auto-reply nếu có                  | FIXED                |


**Checklist test:**

- Cấu hình OAuth.
- Kết nối tài khoản Google Business thật/test.
- Sync location.
- Sync review.
- Tạo rule auto-reply.
- Publish scheduled post.
- Kiểm tra command:
  - `google-business:sync-reviews`
  - `google-business:publish-scheduled-posts`
- Kiểm tra quyền OAuth bị revoke thì hệ thống xử lý ra sao.
- Kiểm tra không tự reply bừa khi chưa bật rule.

**Điểm cần lưu ý:**

- Nếu chưa có Basic API Access thì đánh dấu `BLOCKED`.
- Không demo live nếu chưa có credential thật.
- Cần mock/demo data để trình bày cho COO/BOD khi Google API chưa sẵn sàng.

**Ngày gợi ý:** Day 9.

---

### 17.9. Nhóm H — AI Studio và credit

**Mục tiêu:** kiểm tra AI dùng được thật, trừ credit đúng, không để lộ module disabled.

**Module cần test:**


| Module                    | Việc cần test                          | Trạng thái mong muốn |
| ------------------------- | -------------------------------------- | -------------------- |
| AppAIStudio               | Review reply, settings, prompt history | PASS                 |
| AppAIContent              | Generate caption                       | PASS                 |
| AppAIContentPlanner       | Lập kế hoạch nội dung                  | PASS/FIXED           |
| AppAIRepurpose            | Tái sử dụng nội dung                   | PASS/FIXED           |
| AppAIImage                | Generate image, job log                | PASS/FIXED           |
| AppCredits                | Credit pack, ledger, usage log         | PASS/FIXED           |
| AdminCredits              | Quản trị credit, ledger, usage         | PASS/FIXED           |
| AdminAI                   | AI provider config, usage log, report  | FIXED                |
| AdminAITemplate           | Prompt template                        | PASS/FIXED           |
| AdminAITemplateCategories | Taxonomy template AI                   | PASS/FIXED           |


**Module disabled cần xử lý:**


| Module              | Hành động                                 |
| ------------------- | ----------------------------------------- |
| AppAIVideo          | Ẩn khỏi UI hoặc gỡ khỏi sidebar/backlog   |
| AppAIReview         | Ẩn khỏi UI hoặc hoàn thiện route + credit |
| AppAIBestTime       | Ẩn khỏi UI hoặc hoàn thiện route + credit |
| AppAISemanticSearch | Ẩn khỏi UI hoặc hoàn thiện route + credit |


**Checklist test:**

- Cấu hình provider AI trong Admin Settings.
- User có credit thì gọi AI được.
- User hết credit thì bị chặn đúng.
- Mỗi action trừ đúng cost.
- Usage log ghi đúng.
- Prompt history scope đúng theo workspace owner.
- API lỗi thì có fallback/error message thân thiện.
- Không có `OPENAI_`*, `ANTHROPIC_*`, `PRISM_*` hard-code sai trong `.env` nếu kiến trúc đang dùng OptionStore.
- Module disabled không hiện trong menu user.

**Ngày gợi ý:** Day 10–11.

---

### 17.10. Nhóm I — File manager, custom domain, integration hub

**Mục tiêu:** kiểm tra các tiện ích nâng cao phục vụ branding, file/media và tích hợp.

**Module cần test:**


| Module           | Việc cần test                                              | Trạng thái mong muốn |
| ---------------- | ---------------------------------------------------------- | -------------------- |
| AppFiles         | File manager portal/admin, signed preview, picker callback | PASS/FIXED           |
| AppCustomDomain  | Custom domain cho QR/campaign                              | PASS/FIXED           |
| AppIntegrations  | Hub tích hợp admin-only                                    | PASS/FIXED           |
| AdminMarketplace | Marketplace module/license                                 | PASS/FIXED           |
| AdminThemes      | Theme, custom CSS/JS                                       | PASS                 |


**Checklist test:**

- Upload file.
- Xem preview signed URL.
- Kiểm tra user không đọc được file user khác.
- Kiểm tra dung lượng theo plan.
- Kiểm tra max file size.
- Cấu hình custom domain.
- Kiểm tra domain chưa verify không được active.
- Kiểm tra admin integration hub không lộ sang portal user.
- Kiểm tra theme/custom CSS không phá layout.

**Ngày gợi ý:** Day 12.

---

### 17.11. Nhóm J — Billing, payment, coupon thanh toán

**Mục tiêu:** kiểm tra bán gói, thanh toán, subscription, manual payment và webhook. Chỉ cho chạy production sau khi audit.

**Module cần test:**


| Module                    | Việc cần test                            | Trạng thái mong muốn |
| ------------------------- | ---------------------------------------- | -------------------- |
| AppPayments               | Checkout, gateway orchestration, webhook | FIXED/AUDITED        |
| PaymentStripe             | Stripe one-time/recurring/webhook        | AUDITED              |
| PaymentPaypal             | PayPal one-time/recurring/webhook        | AUDITED              |
| Payment2Checkout          | 2Checkout checkout/webhook               | AUDITED              |
| AdminManualPayments       | Duyệt thanh toán thủ công                | PASS/FIXED           |
| AdminCoupons              | Mã giảm giá billing                      | PASS/FIXED           |
| AdminPaymentHistory       | Lịch sử giao dịch                        | PASS/FIXED           |
| AdminPaymentSubscriptions | Quản lý subscription                     | FIXED                |
| AdminPaymentReport        | Báo cáo thanh toán                       | PASS/FIXED           |
| AdminPaymentManualConfig  | Redirect legacy sang payment gateways    | SKIP/FIXED           |


**Checklist test:**

- User chọn gói.
- Áp coupon billing.
- Tạo manual payment.
- Admin duyệt manual payment.
- Plan user được active sau khi duyệt.
- Payment history ghi đúng.
- Subscription hiển thị đúng.
- Webhook gateway có xác thực chữ ký.
- Webhook có idempotency.
- Không tạo subscription trùng.
- Hoàn tiền/hủy gói có trạng thái rõ.
- Gateway chưa cấu hình phải báo lỗi thân thiện.

**Điểm cần lưu ý:**

- Chưa ưu tiên payment quốc tế cho thị trường Việt Nam.
- Trước MVP có thể dùng manual payment/trial/internal plan.
- Sau MVP mới tích hợp VNPay/MoMo/SePay nếu cần.

**Ngày gợi ý:** Day 13–14.

---

### 17.12. Nhóm K — Admin settings, hệ thống, ngôn ngữ, bảo trì

**Mục tiêu:** đảm bảo hệ thống vận hành ổn, có setting, log, cache, captcha, mail, ngôn ngữ.

**Module cần test:**


| Module                 | Việc cần test                                     | Trạng thái mong muốn |
| ---------------------- | ------------------------------------------------- | -------------------- |
| AdminSettings          | General, analytics, embed, static pages, security | PASS                 |
| AdminCaptcha           | Turnstile/reCAPTCHA v2                            | PASS                 |
| AdminMailServer        | SMTP/sender                                       | PASS                 |
| AdminCache             | Clear cache/session                               | PASS                 |
| AdminCrons             | Registry scheduler + secure cron URL              | PASS/FIXED           |
| AdminLog               | Xem/tải/xóa log                                   | PASS                 |
| AdminSystemInformation | PHP/Laravel/env diagnostics                       | PASS                 |
| AdminMenuBuilder       | Kéo-thả sidebar                                   | PASS/FIXED           |
| AdminLanguages         | Locale, DB override, public `lang/{locale}`       | PASS/FIXED           |
| AdminNotifications     | Broadcast, in-app notification                    | PASS/FIXED           |


**Checklist test:**

- Lưu general settings.
- Lưu security settings.
- Bật/tắt captcha.
- Test SMTP.
- Clear cache.
- Xem log.
- Xóa log.
- Xem system information.
- Đổi ngôn ngữ.
- Override bản dịch.
- Tạo notification manual.
- Kiểm tra notification user nhận được.
- Kiểm tra menu builder không làm mất route quan trọng.

**Ngày gợi ý:** Day 15.

---

### 17.13. Nhóm L — CustomMLHUB và demo data

**Mục tiêu:** đảm bảo quá trình cài đặt/update/seed dữ liệu demo chạy được, phục vụ demo cho user mới và COO/BOD.

**Module cần test:**


| Module                 | Việc cần test                                                               | Trạng thái mong muốn |
| ---------------------- | --------------------------------------------------------------------------- | -------------------- |
| CustomMLHUB            | install/update/sync-env-options/seed admin/extras/site options/plan catalog | PASS                 |
| AppAdvancedCustomerCrm | `crm:seed-demo`                                                             | PASS/FIXED           |
| Demo Workspace mới     | Seed data theo ngành F&B/Spa/Bán lẻ                                         | CẦN BỔ SUNG          |


**Command cần kiểm tra:**

```bash
php artisan mlhub:install --force
php artisan mlhub:update
php artisan mlhub:sync-env-options
php artisan crm:seed-demo

```

**Đề xuất bổ sung command mới:**

```bash
php artisan mlhub:seed-demo-workspace --user={id} --industry=fnb
php artisan mlhub:seed-demo-workspace --user={id} --industry=spa
php artisan mlhub:seed-demo-workspace --user={id} --industry=retail
php artisan mlhub:clear-demo-workspace --user={id}

```

**Demo data cần có:**


| Pack       | Dữ liệu cần seed                                                                                           |
| ---------- | ---------------------------------------------------------------------------------------------------------- |
| F&B        | Business, location, customers, QR nhận voucher, coupon, feedback, review, CRM tag, automation chăm sóc lại |
| Spa/Beauty | Booking service, khách liệu trình, nhắc lịch, coupon quay lại, feedback sau dịch vụ                        |
| Bán lẻ     | Lead form, coupon đơn tiếp theo, landing khuyến mãi, CRM segment, campaign chăm sóc nhóm khách             |


**Cột/flag nên có cho demo data:**

```text
is_demo = true
demo_pack = fnb | spa | retail
demo_seeded_at = timestamp

```

**Ngày gợi ý:** Day 16.

---

### 17.14. Lộ trình test 16 ngày đề xuất


| Ngày   | Nhóm   | Mục tiêu                                          |
| ------ | ------ | ------------------------------------------------- |
| Day 1  | Nhóm A | Account, profile, plan, team, support             |
| Day 2  | Nhóm B | Business, location, customer, CRM core            |
| Day 3  | Nhóm C | QR, lead, review                                  |
| Day 4  | Nhóm C | Booking, coupon, feedback, race condition         |
| Day 5  | Nhóm D | Landing page, marketing template, blog/FAQ        |
| Day 6  | Nhóm E | Loyalty, referral, affiliate                      |
| Day 7  | Nhóm F | CRM automation, event trigger                     |
| Day 8  | Nhóm F | Email, webhook, WhatsApp, cron                    |
| Day 9  | Nhóm G | Google Business/OAuth/sync                        |
| Day 10 | Nhóm H | AI Studio, AI Content, Planner                    |
| Day 11 | Nhóm H | AI Image, credit, disabled AI cleanup             |
| Day 12 | Nhóm I | Files, custom domain, integrations, theme         |
| Day 13 | Nhóm J | Manual payment, coupon billing, billing           |
| Day 14 | Nhóm J | Stripe/PayPal/2Checkout webhook audit             |
| Day 15 | Nhóm K | Settings, captcha, mail, cache, log, language     |
| Day 16 | Nhóm L | CustomMLHUB, seed demo workspace, clean demo data |


---

### 17.15. Mẫu log kết quả test mỗi ngày

Mỗi ngày sau khi test, cập nhật vào bảng này:


| Ngày   | Nhóm test | Module đã test                                        | PASS | FIXED | BLOCKED | SKIP | Ghi chú |
| ------ | --------- | ----------------------------------------------------- | ---- | ----- | ------- | ---- | ------- |
| Day 1  | A         | AppProfile, AdminUser, AdminPlans                     |      |       |         |      |         |
| Day 2  | B         | AppBusinessProfiles, AppCustomers                     |      |       |         |      |         |
| Day 3  | C         | AppQRCampaigns, AppLeadForms, AppReviewBooster        |      |       |         |      |         |
| Day 4  | C         | AppBookingPages, AppCouponCampaigns, AppFeedbackForms |      |       |         |      |         |
| Day 5  | D         | AppLandingPages, AppMarketingTemplates                |      |       |         |      |         |
| Day 6  | E         | AppLoyaltyStampCards, AppAffiliate                    |      |       |         |      |         |
| Day 7  | F         | AppAdvancedCustomerCrm automation                     |      |       |         |      |         |
| Day 8  | F         | Email/Webhook/WhatsApp                                |      |       |         |      |         |
| Day 9  | G         | AppGoogleBusiness                                     |      |       |         |      |         |
| Day 10 | H         | AI Studio/Content/Planner                             |      |       |         |      |         |
| Day 11 | H         | AI Image/Credits/Disabled AI                          |      |       |         |      |         |
| Day 12 | I         | Files/CustomDomain/Integrations                       |      |       |         |      |         |
| Day 13 | J         | Manual Payment/Billing/Coupons                        |      |       |         |      |         |
| Day 14 | J         | Payment Gateway Webhooks                              |      |       |         |      |         |
| Day 15 | K         | Settings/System/Language                              |      |       |         |      |         |
| Day 16 | L         | CustomMLHUB/Demo Seed                                 |      |       |         |      |         |


---

### 17.16. Definition of Done cho từng module

Một module chỉ được đổi trạng thái lên `Ready` khi đạt đủ các điều kiện sau:

- Route chính vào được.
- Action chính chạy được.
- Không lỗi 500 trong happy path.
- Không lộ dữ liệu tenant khác.
- Có kiểm tra plan/permission nếu module thuộc gói.
- Public POST có throttle nếu nhận dữ liệu từ người ngoài.
- Public form có captcha nếu module dễ bị spam.
- Dữ liệu ghi đúng bảng/model.
- Có empty state cho user mới.
- Có thông báo lỗi thân thiện.
- Có bản dịch tiếng Việt/tiếng Anh cho text user-facing.
- Có log hoặc audit ở các action nhạy cảm.
- Nếu là payment/webhook thì có idempotency.
- Nếu là AI thì trừ credit đúng.
- Nếu là automation thì không gửi trùng.
- Nếu là demo data thì có cách xóa demo data.

---

### 17.17. Ưu tiên xử lý trước MVP

Trước khi demo cho user thật hoặc COO/BOD, ưu tiên xử lý theo thứ tự:

1. Business/Profile/Customer chạy ổn.
2. QR + Lead + Coupon + Booking + Feedback + Review chạy được.
3. Public POST có throttle.
4. Landing submit có throttle.
5. Loyalty/referral public POST có throttle.
6. Booking chống double booking.
7. Coupon chống overshoot.
8. CRM lưu tag/segment/task/activity đúng.
9. Report đọc được dữ liệu growth.
10. Có demo data theo ngành.
11. AI disabled không hiện trên UI.
12. Payment quốc tế để sau, trước mắt dùng manual/trial/internal plan.

---

### 17.18. Ghi chú định hướng sản phẩm

Không test theo kiểu “module nào có thì bật hết”.
Phải test theo hành trình sử dụng thật của HKD:

```text
Tạo hồ sơ kinh doanh
→ tạo chiến dịch QR/landing/form
→ khách quét QR và để lại dữ liệu
→ hệ thống lưu customer
→ CRM phân nhóm khách
→ automation chăm sóc lại
→ báo cáo đo hiệu quả
→ nâng cấp gói khi cần thêm giới hạn/tính năng

```

Đây là luồng lõi của MLHUB AI / FizaMKT. Các module khác như Payment, Google Business, AI nâng cao, Loyalty, Affiliate, Files, Custom Domain là lớp mở rộng sau khi luồng lõi chạy ổn.

---
