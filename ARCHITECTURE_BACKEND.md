# MLHUB AI — Kiến trúc Backend

Tài liệu mô tả cách backend Laravel 13 được tổ chức, các add-on/module tích hợp vào core ra sao, logic SaaS/đa người dùng, và luồng API/middleware. Nội dung dựa trên **mã nguồn thực tế** của dự án.

---

## 1. Tổng quan kiến trúc

LocalBoost AI là một **Modular Monolith** (khối nguyên một process nhưng chia module):

- `app/` — **lớp vỏ (shell) mỏng**: auth, trang marketing khách, bootstrap MLHUB (`config/mlhub.php`, `mlhub:install`), các registry toàn cục, middleware.
- `modules/` — **79 module** (29 `Admin`*, 36 `App*`, 14 `Payment*`) chứa hầu hết Model, Livewire, Route, Service.
- `resources/themes/` — tầng trình bày (xem `ARCHITECTURE_FRONTEND.md`).
- `bootstrap/providers.php` — **tự động phát hiện** mọi module và nạp Service Provider của chúng.

Nguyên tắc cốt lõi:

- **KHÔNG có tầng Repository.** Truy cập dữ liệu = Eloquent trực tiếp.
- Điều phối nghiệp vụ chia làm 2 loại thư mục: `Support/` (helper/catalog/registry không trạng thái) và `Services/` (workflow có giao dịch).
- Các mối quan tâm xuyên suốt (sidebar, dashboard, plan, credit, payment…) được expose qua **Registry singleton**, đăng ký trong `App\Providers\AppServiceProvider`.

---

## 2. Bản đồ thư mục Laravel core

### 2.1 `app/` — lớp vỏ ứng dụng


| Đường dẫn                                                                                                              | Vai trò                                                                                                                                                                                                                                                                                                 |
| ---------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `app/Providers/AppServiceProvider.php`                                                                                 | Đăng ký singleton: `SidebarRegistry`, `HeaderRegistry`, `AdminDashboardRegistry`, `UserDashboardRegistry`, `PlanPermissionRegistry`, `StorageDriverManager`, `SocialAvatarStore`. Đặt `CarbonImmutable` mặc định, Livewire component hook, đường dẫn Blade component dùng chung, ép HTTPS ở production. |
| `app/Providers/FortifyServiceProvider.php`                                                                             | Gắn view của Fortify vào các trang Livewire auth.                                                                                                                                                                                                                                                       |
| `config/mlhub.php`                                                                                                     | Bootstrap MLHUB (seed stack, theme/site mặc định) — **không còn** Web Installer. Biến env: `.env.example`.                                                                                                                                                                                               |
| `modules/CustomMLHUB/`                                                                                               | Gói thị trường VN: `mlhub:install`, super admin từ env, `mlhub_site_options.php`, script Việt hóa AI templates, `STARTING_ID=147123468`.                                                                                                                                                                |
| `app/Http/Middleware/EnsureAdminAccess.php`                                                                            | Cổng kiểm soát truy cập khu admin.                                                                                                                                                                                                                                                                      |
| `app/Http/Middleware/ResolveUserPlanState.php`                                                                         | Nạp ngữ cảnh gói (plan) cho mỗi request.                                                                                                                                                                                                                                                                |
| `app/Http/Middleware/PreventDemoModeWriteOperations.php`                                                               | Chặn thao tác ghi khi bật chế độ demo.                                                                                                                                                                                                                                                                  |
| `app/Http/Controllers/GuestMarketingController.php`, `GuestStaticPageController.php`, `Auth/SocialLoginController.php` | Trang marketing công khai, trang tĩnh, đăng nhập mạng xã hội.                                                                                                                                                                                                                                           |
| `app/Livewire/Auth/`*, `app/Livewire/Portal/Dashboard.php`                                                             | Trang login/register/reset, dashboard portal (lazy `loadDashboardSections`).                                                                                                                                                                                                                            |
| `app/Support/Portal/PortalGrowthDashboardMetrics.php`                                                                  | Metrics/top campaigns/recent activity portal (Redis cache **v2 = mảng scalar**, không cache Eloquent; `forget()` xóa key đủ suffix; `visits` đếm theo `campaign_id` của user; sau scan/conversion/review qua `GrowthToolNotifier` / `recordScan`).                                                      |
| `app/Support/Mail/AuthMailMessageBuilder.php`                                                                          | Mail reset password / verify email (locale user, `toMailUsing`).                                                                                                                                                                                                                                        |
| `app/Livewire/DemoModeActionGuard.php`                                                                                 | Chặn Livewire write khi `APP_DEMO=true`.                                                                                                                                                                                                                                                                |
| `app/Support/Navigation/`                                                                                              | `SidebarRegistry`, `HeaderRegistry`.                                                                                                                                                                                                                                                                    |
| `app/Support/Dashboard/`                                                                                               | `AdminDashboardRegistry`, `UserDashboardRegistry`.                                                                                                                                                                                                                                                      |
| `app/Support/Plans/`                                                                                                   | `PlanPermissionRegistry`, `**PlanLimitGuard`** (kiểm soát hạn mức tạo bản ghi).                                                                                                                                                                                                                         |
| `app/Support/Storage/`                                                                                                 | `StorageDriverManager`, `SocialAvatarStore`.                                                                                                                                                                                                                                                            |
| `app/Support/helpers.php`                                                                                              | Helper toàn cục (auto-load qua `composer.json`). Định dạng VN: `format_number_locale`, `format_money`, `format_date_locale`, `format_datetime_locale`, `format_carbon_display` (map pattern `Y-m-d` cũ → locale), `platform_format_settings()`; JS: `platform_format_config()` → `window.MLHUB_FORMAT`. Rà soát: `ARCHITECTURE_CHECKLIST.md` §5.1. |
| `app/Concerns/`                                                                                                        | Trait `HasLocalizedAttributes`, `PasswordValidationRules`.                                                                                                                                                                                                                                              |
| `app/Exceptions/DemoModeRestrictedException.php`                                                                       | Được render thành JSON (Livewire/AJAX) hoặc `back()` (web) trong `bootstrap/app.php`.                                                                                                                                                                                                                   |


### 2.2 `modules/` — nơi chứa nghiệp vụ chính

**Thương hiệu MLHUB (đặt tên file/code):** chỉ `MLHUB` hoặc `mlhub` — không `Mlhub`/`MLHub`. Ví dụ: module `CustomMLHUB`, config `config/mlhub.php`, command `mlhub:install`.

**Tiền tố tên module:**


| Tiền tố    | Đối tượng                       | Ví dụ                                                                                                                                                                                                                                                                                                                                                                                                       |
| ---------- | ------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `Admin`*   | Super-admin / cấu hình          | `AdminUser`, `AdminPlans`, `AdminThemes`, `AdminSettings`, `AdminLanguages`, `AdminMarketplace`, `AdminCrons`, `AdminCoupons`, `AdminPayment`*, `AdminCredits`, `AdminAI*`, `AdminCache`, `AdminLog` (xem/tải/xoá log tại `admin/settings/log`, route `admin-log.index`, chỉ admin)                                                                                                                         |
| `App*`     | Portal khách hàng               | Growth: `AppQRCampaigns`, `AppReviewBooster`, `AppBookingPages`, `AppCouponCampaigns`, `AppFeedbackForms`, `AppLeadForms`. Mở rộng: `AppAdvancedCustomerCrm`, `AppEmailAutomation`, `AppLoyaltyStampCards`, `AppLocalAnalytics`. Core: `AppBusinessProfiles`, `AppCustomers`, `AppLandingPages`, `AppTeams`, `AppCredits`, `AppPayments`, `AppBilling`, `AppAI*`, `AppGoogleBusiness`, `AppIntegrations`, … |
| `Payment*` | Plugin cổng thanh toán (**14**) | `PaymentStripe`, `PaymentPaypal`, `PaymentRazorpay`, `PaymentPaystack`, `PaymentFlutterwave`, `PaymentInstamojo`, `PaymentIyzico`, `Payment2Checkout`, `PaymentCCAvenue`, `PaymentSslCommerz`, `PaymentYooMoney`, `PaymentPaytm`, `PaymentPayU`, `PaymentPayTR`                                                                                                                                             |


**Cấu trúc điển hình một module** (vd `modules/AppReviewBooster/`):

```
module.json                         # { name, providers[], priority }
config/config.php                   # gộp thành config('modules.appreviewbooster.*')
Providers/AppReviewBoosterServiceProvider.php
Routes/web.php
Http/Controllers/                   # chỉ cho public form / webhook / download
Livewire/                           # các trang full-page
Models/                             # Eloquent (bảng có tiền tố lb_)
Support/                            # catalog, helper không trạng thái
Services/                           # workflow (một số module)
Resources/views/                    # loadViewsFrom(..., 'appreviewbooster')
Database/Migrations/                # migration riêng của module
```

---

## 3. Cách các add-on/module tích hợp vào core (cơ chế quan trọng nhất)

### 3.1 Tự động phát hiện qua `bootstrap/providers.php`

Mỗi lần boot, file này thực hiện:

1. Quét toàn bộ thư mục `modules/*` (`glob` + `sort`).
2. Với mỗi module, đọc `module.json` → lấy `providers[]`, `files[]` (tùy chọn), `priority` (mặc định `0`).
3. Nếu `providers` rỗng → fallback theo quy ước `Modules\{Name}\Providers\{Name}ServiceProvider`.
4. `require_once` `Support/helpers.php` của module nếu tồn tại (auto-load helper).
5. Sắp xếp theo `priority` tăng dần, rồi theo tên.
6. Gộp thêm danh sách provider từ `bootstrap/providers.marketplace.php` (hiện: `AppLoyaltyStampCards`).

```php
return array_values(array_unique(array_merge(
    $baseProviders,        // AppServiceProvider, FortifyServiceProvider
    $moduleProviders,      // tự động từ modules/*
    $marketplaceProviders  // từ providers.marketplace.php (add-on/marketplace)
)));
```

> **Hệ quả:** Để thêm một add-on, chỉ cần (a) thả thư mục module có `module.json` vào `modules/` (tự nạp), hoặc (b) thêm provider vào `bootstrap/providers.marketplace.php` nếu marketplace yêu cầu. **Không bao giờ sửa `bootstrap/providers.php`.** Sau cập nhật upstream: chạy deploy → `migrate --force` trong `entrypoint.sh` áp migration module mới (`lb_email_`*, `lb_loyalty_`*, `lb_crm_*`, …).

### 3.2 Service Provider của module làm gì

Mẫu chuẩn (trích `AppReviewBoosterServiceProvider`):

```php
public function register(): void
{
    $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appreviewbooster');
}

public function boot(): void
{
    $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
    $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appreviewbooster');
    $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

    register_user_sidebar_item('growth-tools', [
        'label'      => 'Review Booster',
        'route_name' => 'portal.review-booster',
        'icon'       => 'fa-light fa-star',
        'order'      => 10,
        'visible'    => fn (): bool => auth()->user()?->canUsePlanFeature('localboost') ?? true,
    ]);
}
```

- `register()` chỉ gộp config + bind container.
- `boot()` nạp route/view/migration + gọi helper registry để "ghim" mình vào UI/plan/cron.
- `priority` trong `module.json` quyết định thứ tự — module priority cao boot sau, ghi đè registry sau cùng (vd các growth tool có `priority: 22`).

### 3.3 Mẫu plugin thanh toán (`Payment*`)

Mỗi `Payment*ServiceProvider` làm 3 việc:

1. Khai báo `PaymentGatewayDefinition` (key, nhãn, năng lực, tiền tệ).
2. Bind class gateway cụ thể vào contract.
3. Gọi `PaymentGatewaySettingsRegistry::register('key', [...])` để sinh UI cấu hình trong Admin.

Đây là khuôn mẫu cho mọi cổng thanh toán tùy biến mới.

---

## 4. Logic SaaS / Đa người dùng (Multi-tenancy)

Hệ thống **không** dùng tách database; tenant được cô lập bằng **scoping theo `user_id`** + lớp **workspace/team**.

### 4.1 Người dùng & quyền

- Model người dùng: `**Modules\AdminUser\Models\User`** (`extends Authenticatable`, implements `MustVerifyEmail`, `HasLocalePreference`, dùng `TwoFactorAuthenticatable`).
- Phân biệt 2 thế giới:
  - **Quản trị**: `is_super_admin` / `role_id` → `canAccessAdmin()`, `hasPermission()` (kiểm tra theo `role->permissions`, hỗ trợ wildcard `*` và `prefix.`*). Cổng: middleware `EnsureAdminAccess`.
  - **Khách hàng (portal)**: scope theo `user_id`.

### 4.2 Gói dịch vụ (Plan) & hạn mức

- Gói nằm ở `Modules\AdminPlans\Models\AdminPlan`; user gắn `plan_id`, `plan_started_at`, `plan_expires_at`, `next_plan_id`.
- **Hiển thị catalog (portal Packages / guest pricing):** `PlanSeeder` lưu `name`/`desc` bằng **chuỗi tiếng Anh** (key `__()`); bản dịch VI trong `lang/vi.json`. `Modules\AdminPlans\Support\CatalogLocalization::resolve()` + `PricingService::render()` dịch lúc render (kể cả label đã bị “đóng băng” tiếng Việt lúc boot `__('…')` trên locale mặc định).
- **Sidebar portal (addon):** menu module marketplace chỉ `visible` khi `User::canUsePlanFeature(...)` (cần quyền trong gói **hoặc** `config/mlhub.php` → `no_plan_access.permissions`). User chưa gán gói **không** thấy CRM / Google Business / Loyalty / Custom Domains; vào URL trực tiếp → `403`.
- **Chưa chọn gói nhưng dùng ngay (free tier):** `MLHUB_NO_PLAN_ACCESS_ENABLED=true` (mặc định). Hạn mức trong `config/mlhub.php` `no_plan_access.permissions` (+ env `MLHUB_NO_PLAN_`*). `User::planLimit()` / `canUsePlanFeature('localboost')` / dashboard **Current plan limits** dùng các số này thay vì Unlimited. Sidebar hiển thị **MLHUB Free** / badge **Free**.
- **Cache dashboard plan limits:** `PlanLimitGuard::planUsageCacheKey()` → `portal.plan_usage.v0|v1.{userId}.{locale}`; nhãn usage resolve qua `CatalogLocalization` lúc build (không cache nhầm ngôn ngữ). `forgetPlanUsageCache()` xóa v0/v1/v2 theo `en`/`vi`.
- **Sidebar portal/admin:** `SidebarRegistry::sections()` resolve nhãn section/item qua `CatalogLocalization::resolve()` sau menu overrides (tránh nhãn `__('…')` bị “đóng băng” tiếng Việt lúc boot). Blade `sidebar-menu` in trực tiếp nhãn đã resolve, không `__()` lại.
- Kiểm tra quyền tính năng:
  - `$user->hasActivePlan()` — còn hạn không.
  - `$user->canUsePlanFeature('localboost')` — gói còn hạn **và** bật cờ tính năng.
  - `$user->planLimit('max_campaigns', -1)` — hạn mức (-1 = không giới hạn).
- `**App\Support\Plans\PlanLimitGuard`** là chốt chặn trước khi tạo bản ghi:
  - `ensureBusinessCanBeCreated`, `ensureCampaignCanBeCreated`, `ensureLandingPageCanBeCreated`, `ensureQrCodeCanBeCreated`, `ensureTemplateCanBeCreated`.
  - Vượt hạn mức → ném `ValidationException::withMessages(['plan' => ...])` (hiện lên form).
  - `usageSummary()` tổng hợp mức dùng (businesses, campaigns, landing pages, QR, templates, credits, email, Google…), **dò động** module có tồn tại bằng `class_exists()` + `Schema::hasColumn()` để không vỡ khi thiếu add-on.

### 4.3 Workspace / Team (`AppTeams`)

- `Modules\AppTeams\Support\TeamWorkspaceAccess` quản lý ngữ cảnh team:
  - `activeTeam($user)` đọc `session('portal_team_id')` rồi xác thực quyền sở hữu/thành viên.
  - `**workspaceOwnerUserId($user)`** — trả về ID chủ workspace (chủ team nếu đang trong team, ngược lại chính user). Dữ liệu dùng chung của workspace (vd lịch sử AI) scope theo ID này.
  - `enabledModules`, `teamHasModule`, `permissionsForUser`, `hasPermission`, `managedAccountIds` — phân quyền chi tiết theo từng thành viên team.
- Chủ team **bỏ qua** mọi giới hạn workspace (`userBypassesWorkspaceRestrictions`).

### 4.4 Thanh toán & đăng ký (Subscription)

- `Modules\AppPayments` (checkout) + `Modules\AdminPaymentSubscriptions` (`PaymentSubscription`, FK `uid` → user).
- Cổng thanh toán: các module `Payment`* (xem §3.3).
- Tín dụng AI: `Modules\AppCredits` + `CreditService` (`consume_credits()`, `credit_service()->ensureCanConsume()`).

### 4.5 Tín hiệu cô lập dữ liệu cần lưu ý

- Hầu hết bảng có cột `user_id`; một số module (Google Business) dùng `team_id`; CustomDomain dùng `owner_user_id`. Khi viết truy vấn mới, **kiểm tra đúng cột chủ sở hữu** của bảng đó.

---

## 5. Cấu trúc Route & luồng Middleware

### 5.1 Route core


| File                        | Nội dung                                                                                                                                  |
| --------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------- |
| `routes/web.php`            | `/`, `/pricing`, `/faqs`, `/blogs`, `/contact`, callback social auth, `portal/dashboard`. Nạp thêm `settings.php` + `public-storage.php`. |
| `routes/console.php`        | Tổng hợp scheduler.                                                                                                                       |
| `routes/settings.php`       | Route phần Cài đặt.                                                                                                                       |
| `routes/public-storage.php` | Phân phối file công khai có chữ ký.                                                                                                       |
| `modules/*/Routes/web.php`  | **Phần lớn** route admin & portal.                                                                                                        |


### 5.2 Mẫu route portal (Livewire-first)

```php
Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appreviewbooster.route_prefix', 'portal/review-booster'))
    ->group(function (): void {
        Route::livewire('/', ReviewBoosterIndex::class)->name('portal.review-booster');
    });

// Endpoint công khai (form submit) — chỉ middleware 'web'
Route::middleware('web')
    ->post('/qr/{campaign:slug}/feedback', [ReviewFeedbackController::class, 'store'])
    ->name('review-booster.feedback');
```

- Trang đăng nhập = `Route::livewire(...)`; controller chỉ dành cho public form/webhook/download.
- Route công khai dùng **route model binding theo slug** (`{campaign:slug}`).

### 5.3 Pipeline middleware (web group — `bootstrap/app.php`)

Thứ tự rất quan trọng:

```
(web stack chuẩn của Laravel)
  → SetLocale            (Modules\AdminLanguages)
  → SetThemeContext      (Modules\AdminThemes)
  → CaptureAffiliateReferral (Modules\AppAffiliate)
  → ResolveUserPlanState (App\Http\Middleware)
  → EnsureAdminAccess    (App\Http\Middleware)
  → PreventDemoModeWriteOperations   (HTTP POST; Livewire ghi → DemoModeActionGuard)
```

- CSRF loại trừ: `livewire/upload-file`, `livewire-*/upload-file`.
- Trust proxy theo `TRUSTED_PROXIES` env (mặc định `*` ở production) — vì chạy sau Traefik/Coolify, app thấy HTTP nhưng phải sinh signed URL dạng HTTPS.
- Exception handler: `DemoModeRestrictedException` → JSON 403 cho Livewire/AJAX; log `warning` khi Livewire trả 419 (release token / CSRF / snapshot).

### 5.4 "API" của hệ thống

- Đây là ứng dụng **web-first**: không có `routes/api.php` riêng dạng REST công khai. "API" thực chất là:
  - **Livewire update endpoint** (`/livewire/update`) — kênh chính cho tương tác động.
  - **Public form endpoints** (POST trong từng module `Routes/web.php`).
  - **Webhook thanh toán** (callback của từng `Payment*`).
- Khi cần API JSON mới → tạo trong module custom, tự thêm middleware xác thực phù hợp.

---

## 6. Database


| Đường dẫn                                        | Vai trò                                                                                                                                   |
| ------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------- |
| `database/migrations/2026_*_create_database.php` | Schema nền (baseline) — **bảng dùng tiền tố `lb_`**.                                                                                      |
| `modules/*/Database/Migrations/`                 | Migration tăng dần riêng từng module.                                                                                                     |
| `database/seeders/`                              | `DatabaseSeeder`, `PlanSeeder`, `MLHUBBootstrapSeeder`, `MLHUBMarketplaceSeeder`, `AITemplateCategorySeeder`, `AITemplateSeeder` (data ở `database/seeders/data/`). Admin + extras: `modules/CustomMLHUB/Database/Seeders/`. |


- **Settings hệ thống:** `Modules\AdminSettings\Support\OptionStore` (key/value lưu DB).
- **Engine campaign dùng chung:** bảng `lb_campaigns` (model `QrCampaign`) phục vụ tất cả growth tool qua cột `type` + JSON `settings`. Các bảng vệ tinh: `lb_review_feedbacks`, `lb_bookings`, `lb_coupon_redemptions`, `lb_feedback_responses`, `lb_lead_submissions`, `lb_qr_scans`…
- **Engine DB thực tế (từ `.env.example`):** `DB_CONNECTION=mysql` (cổng 3306). Viết migration/raw query theo cú pháp **MySQL**. Bảng nghiệp vụ dùng tiền tố `lb_*`. **Session/cache/queue chạy trên Redis** (không cần bảng `sessions`/`cache`/`cache_locks`/`jobs` trong MySQL); riêng `failed_jobs` và `job_batches` vẫn nằm ở MySQL theo mặc định của Laravel.

### 6.1 Môi trường runtime & triển khai (chốt từ `.env.example` + `docker-compose.yaml`)


| Khía cạnh           | Cấu hình                                                         | Tác động                                                                                                                                                                                                                                                                                 |
| ------------------- | ---------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Locale              | `APP_LOCALE=vi`, fallback `vi`, `APP_TIMEZONE=Asia/Ho_Chi_Minh`  | App mặc định Tiếng Việt; chuỗi mới vẫn dùng key tiếng Anh trong `__()` rồi dịch ở `lang/vi.json`                                                                                                                                                                                         |
| Session/Queue/Cache | đều `redis` (phpredis)                                           | Chạy trên Redis (`REDIS_HOST` = service nội bộ Coolify, password trong Coolify env). **Bắt buộc** chạy queue worker + đảm bảo Redis sống                                                                                                                                                 |
| Mail                | `MAIL_MAILER=smtp` (Emailit)                                     | ✅ Đã cấu hình `smtp.emailit.com:587`, user `emailit`, password = API key (đặt trong Coolify env). Dùng cho verify email, reset mật khẩu, email automation. **Lưu ý:** thông báo growth-tool (Lead/Feedback/Booking/Coupon) là **in-app** qua `NotificationService`, không phụ thuộc mail |
| Storage             | `FILESYSTEM_DISK=public` (S3 trống)                              | Upload nằm ở disk `public`; bật S3 nếu cần scale                                                                                                                                                                                                                                         |
| Cookie              | `SESSION_SECURE_COOKIE=true`                                     | Chỉ chạy đúng dưới HTTPS                                                                                                                                                                                                                                                                 |
| Triển khai          | Coolify + Traefik (HTTP→HTTPS, Let's Encrypt), domain `mlhub.vn` | App thấy HTTP sau proxy → `TRUSTED_PROXIES` đã xử lý trong `bootstrap/app.php`; storage gắn volume `mlhub-storage`                                                                                                                                                                       |
| Tích hợp            | `GOOGLE_BUSINESS_CLIENT_ID/SECRET`                               | Google Business bật (module `AppGoogleBusiness`)                                                                                                                                                                                                                                         |


---

## 7. Xác thực & phân quyền

- **Laravel Fortify** + trang Livewire (`app/Livewire/Auth/*`), gắn view trong `App\Providers\FortifyServiceProvider`.
- Action cụ thể: `Modules\AdminUser\Actions\Fortify\{CreateNewUser, UpdateUserProfileInformation, UpdateUserPassword, ResetUserPassword}`.
- 2FA qua `TwoFactorAuthenticatable` + `pragmarx/google2fa`.
- Cổng admin: `EnsureAdminAccess`; quyền chi tiết theo `role->permissions` + `AdminPermissionCatalog::permissionForRoute()`.
- Mạo danh (impersonate): `canImpersonate()` / `isImpersonating()` (session `impersonator_id`).

---

## 8. AI & Credits

- Dùng `laravel/ai` + `prism-php/prism` từ các module `AdminAI`, `AppAI*`.
- Mọi tính năng AI **bắt buộc**:
  1. Kiểm tra cờ tính năng/feature gate.
  2. `credit_service()->ensureCanConsume($planOwner, 'action_key')` trước khi gọi LLM.
  3. Bọc `try/catch (Throwable)` và **có fallback** (xem `AppAIContent\Livewire\AIContentIndex`).
  4. `consume_credits($planOwner, 'action_key', [...])` sau khi thành công.

---

## 9. Lưu ý chuẩn bị production (backend)

Đã cấu hình sẵn trong `.env.example`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY`, `APP_DEMO=false`, MySQL, `SESSION_SECURE_COOKIE=true`. Đã xử lý: ✅ **SMTP qua Emailit** (Coolify env), ✅ **Captcha** (reCAPTCHA v2 + Cloudflare Turnstile, mặc định Turnstile, cấu hình ở Admin → Captcha), ✅ **Rate-limit `throttle:10,1`** trên 5 route form công khai. Còn lại cần xử lý:

- Điền `DB_HOST`/`DB_PASSWORD` thật (đang trống trong mẫu).
- (Backlog bảo mật) Gắn captcha vào 5 form growth-tool công khai — xem `ARCHITECTURE_FEATURE.md`.
- Queue worker chạy — `entrypoint.sh` tự start `php artisan queue:work` (chạy nền, user `www-data`, vòng lặp tự restart) khi `APP_INSTALLED=true`; tắt bằng `RUN_QUEUE_WORKER=false` nếu dùng worker service Coolify riêng.
- Redis sống & `REDIS_PASSWORD` đặt đúng trong Coolify; chỉ cần migrate `failed_jobs`/`job_batches` (không cần `sessions`/`cache`/`jobs`).
- Scheduler/cron đã bật (xem `AdminCrons` + `routes/console.php`). CRM: cân nhắc lịch `crm:process-automations`, `crm:cleanup-activities`, `crm:lifecycle` (module `AppAdvancedCustomerCrm`).
- Cấu hình cổng thanh toán + webhook URL thật cho từng `Payment*` đang dùng.
- Cân nhắc bật S3 (`FILESYSTEM_DISK`/`AWS_*`) nếu cần scale; kiểm tra signed URL hoạt động sau Traefik.
- `php artisan migrate --force` (KHÔNG dùng `migrate:fresh` trên production).
- `php artisan config:cache route:cache view:cache` sau khi cấu hình ổn định.

Xem `ARCHITECTURE_FRONTEND.md` cho tầng giao diện và `ARCHITECTURE_FEATURE.md` cho đánh giá độ sẵn sàng từng module.