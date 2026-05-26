# LocalBoost AI — Backend Architecture

How the Laravel 13 backend is organized and exactly **where to inject custom logic** without touching core files.

---

## 1. High-level shape

LocalBoost AI is a **modular monolith** with a thin shell:

- `app/` — application shell (auth pages, guest marketing, installer, global registries, middleware).
- `modules/` — ~70 feature modules (`Admin*`, `App*`, `Payment*`) holding the bulk of models, Livewire components, routes, services.
- `resources/themes/` — presentation (see `ARCHITECTURE_FRONTEND.md`).
- `bootstrap/providers.php` — auto-discovers every `modules/*/module.json`, sorts providers by `priority`, then merges `bootstrap/providers.marketplace.php`.

There is **no Repository layer**. Data access is via **Eloquent**; orchestration via **`Support/`** (stateless helpers, registries) and **`Services/`** (transactional workflows). Cross-cutting concerns are exposed through **registry singletons** registered in `App\Providers\AppServiceProvider`.

---

## 2. Directory map

### 2.1 Core `app/` — protect, do not edit

| Path | Role |
|------|------|
| `app/Providers/AppServiceProvider.php` | Singletons: `SidebarRegistry`, `HeaderRegistry`, `AdminDashboardRegistry`, `UserDashboardRegistry`, `PlanPermissionRegistry`, `StorageDriverManager`, `SocialAvatarStore`. Also: Livewire hooks, shared Blade component paths, Fortify feature sync, `CarbonImmutable` default, prod URL forcing. |
| `app/Providers/FortifyServiceProvider.php` | Fortify view bindings → Livewire auth pages. Concrete actions live in `Modules\AdminUser\Actions\Fortify\*`. |
| `app/Installer/` | First-run installer (mounted as `PrepareInstallation` middleware in `bootstrap/app.php`). |
| `app/Http/Middleware/EnsureAdminAccess.php` | Admin gate. |
| `app/Http/Middleware/ResolveUserPlanState.php` | Hydrates plan context per request. |
| `app/Http/Middleware/PreventDemoModeWriteOperations.php` | Blocks mutations when demo mode on. |
| `app/Http/Controllers/GuestMarketingController.php`, `GuestStaticPageController.php`, `Auth/SocialLoginController.php` | Public marketing, privacy/terms, social login. |
| `app/Livewire/Auth/*`, `app/Livewire/Portal/Dashboard.php` | Login/register/reset Livewire pages, portal dashboard stub. |
| `app/Livewire/DemoModeActionGuard.php` | Component hook (registered via `Livewire::componentHook` in `AppServiceProvider`). |
| `app/Support/Navigation/` | `SidebarRegistry`, `HeaderRegistry`. |
| `app/Support/Dashboard/` | `AdminDashboardRegistry`, `UserDashboardRegistry`. |
| `app/Support/Plans/` | `PlanPermissionRegistry`. |
| `app/Support/Storage/` | `StorageDriverManager`, `SocialAvatarStore`. |
| `app/Support/{TimezoneCatalog,GrowthToolNotifier}.php` | Stateless helpers. |
| `app/Support/helpers.php` | Global helpers (see §4.1 for full list). |
| `app/Concerns/` | `HasLocalizedAttributes`, `PasswordValidationRules` traits. |
| `app/Console/Commands/` | Author Artisan commands. |
| `app/Exceptions/DemoModeRestrictedException.php` | Rendered to JSON/back() by `bootstrap/app.php` exception handler. |
| `app/Notifications/WelcomeNewUserNotification.php` | Default new-user welcome. |

### 2.2 Core `modules/` — protect, add sibling `Custom*` instead

**Naming prefixes:**

| Prefix | Audience | Examples |
|--------|----------|----------|
| `Admin*` | Super-admin / settings | `AdminUser`, `AdminPlans`, `AdminThemes`, `AdminMarketplace`, `AdminLanguages`, `AdminMailServer`, `AdminMenuBuilder`, `AdminSettings`, `AdminBlogs`, `AdminFaqs`, `AdminCoupons`, `AdminCrons`, `AdminCache`, `AdminCaptcha`, `AdminFaker`, `AdminSupport`, `AdminAffiliate`, `AdminAI`, `AdminAITemplate*`, `AdminPayment*`, `AdminCredits`, `AdminDashboard`, `AdminNotifications`, `AdminSystemInformation`. |
| `App*` | Customer portal | `AppBusinessProfiles`, `AppBusinessLocations`, `AppPayments`, `AppBilling`, `AppCredits`, `AppTeams`, `AppProfile`, `AppSupport`, `AppFiles`, `AppCustomers`, `AppLeadForms`, `AppFeedbackForms`, `AppCouponCampaigns`, `AppQRCampaigns`, `AppReviewBooster`, `AppBookingPages`, `AppLandingPages`, `AppLocalAnalytics`, `AppMarketingTemplates`, `AppCustomDomain`, `AppIntegrations`, `AppAffiliate`, `AppAI*` (Studio, Image, Video, Content, ContentPlanner, Repurpose, Review, BestTime, SemanticSearch). |
| `Payment*` | Gateway plugins | `PaymentStripe`, `PaymentPaypal`, `PaymentRazorpay`, `PaymentPaystack`, `PaymentPaytm`, `PaymentPayU`, `PaymentPayTR`, `PaymentFlutterwave`, `PaymentInstamojo`, `PaymentIyzico`, `Payment2Checkout`, `PaymentCCAvenue`, `PaymentSslCommerz`, `PaymentYooMoney`. |

**Typical anatomy** (e.g. `modules/AppBusinessProfiles/`):

```
module.json                         # { name, providers[], priority }
Providers/AppBusinessProfilesServiceProvider.php
Routes/web.php
Http/Controllers/                   (only when not pure Livewire)
Livewire/
Models/
Support/                            catalogs, renderers, helpers
Services/                           transactional workflows (some modules)
Resources/views/                    loadViewsFrom('appbusinessprofiles')
config/config.php                   merged as config('modules.appbusinessprofiles.*')
Database/Migrations/                module-owned tables
```

### 2.3 Auto-discovery (the most important update-safe contract)

`bootstrap/providers.php` does this on every boot:

1. Scan `modules/*` directories.
2. For each, read `module.json` → collect `providers`, optional `files[]`, `priority` (default `0`).
3. If `providers` empty, fall back to the convention class `Modules\{Name}\Providers\{Name}ServiceProvider`.
4. `require_once` `Support/helpers.php` if present.
5. Sort by ascending `priority`, then by name.
6. Merge `bootstrap/providers.marketplace.php` (returns an array of provider class names).

> **Two zero-edit injection points are already wired:**
> 1. Drop a `modules/Custom{Feature}/` directory with `module.json` — auto-loaded.
> 2. Append a provider class name to `bootstrap/providers.marketplace.php` — already `require`d by core.
>
> You never edit `bootstrap/providers.php` itself.

### 2.4 Routes (core)

| File | Contents |
|------|----------|
| `routes/web.php` | `/`, `/pricing`, `/faqs`, `/blogs`, `/contact`, social auth callbacks, `portal/dashboard`. Requires `settings.php` + `public-storage.php`. |
| `routes/console.php` | Scheduler aggregator. |
| `routes/settings.php` | Settings routes (mostly delegated to `Modules\AdminSettings`). |
| `routes/public-storage.php` | Signed public file delivery. |
| `app/Installer/routes/*` | Installer wizard. |
| `modules/*/Routes/web.php` | The vast majority of admin & portal routes. |

Portal pattern:

```php
Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.{alias}.route_prefix', 'portal/...'))
    ->group(function (): void {
        Route::livewire('/', SomeIndex::class)->name('portal....');
    });
```

### 2.5 Database

| Path | Role |
|------|------|
| `database/migrations/2026_04_18_110000_create_database.php` | Monolithic baseline schema (~86 tables). |
| `modules/*/Database/Migrations/` | Per-module incremental migrations. |
| `database/seeders/` | `DatabaseSeeder`, `LocalBoostDemoSeeder`, `AITemplateCategorySeeder`, `AITemplateSeeder` (file: `database/seeders/data/ai_templates.json` with hard-coded IDs), `PlanSeeder`. |

**User model:** `Modules\AdminUser\Models\User`. **Settings:** `Modules\AdminSettings\Support\OptionStore` (DB-backed key/value).

### 2.6 Bootstrap & middleware (`bootstrap/app.php`)

Order matters. Web group runs:

```
PrepareInstallation  →  (Laravel web stack)  →
SetLocale  →  SetThemeContext  →  CaptureAffiliateReferral  →
ResolveUserPlanState  →  EnsureAdminAccess  →  PreventDemoModeWriteOperations
```

CSRF exclusions: `livewire/upload-file`, `livewire-*/upload-file`. Exception handler renders `DemoModeRestrictedException` as JSON for AJAX/Livewire, otherwise `back()->with('warning', …)`.

---

## 3. Core vs custom — decision table

| Need | Core (author owns) | Where YOU put it |
|------|--------------------|------------------|
| New portal feature | — | `modules/Custom{Feature}/` |
| Small global tweak | — | `app/Custom/` + provider in `bootstrap/providers.marketplace.php` |
| Sidebar / menu tweak | — | `register_*_sidebar_*` from your provider, **or** Admin → Menu Builder UI |
| Override a service or controller | — | Child class in `app/Custom/` + `$this->app->bind()` |
| New payment gateway | `Payment*` pattern | `modules/CustomPayment{Name}/` with `PaymentGatewayDefinition` + `PaymentGatewaySettingsRegistry` |
| New admin page | `Admin*` modules | `modules/CustomAdmin*` + `register_sidebar_item` |
| Add column to author table | Avoid | New migration with nullable column **only** if essential; prefer accessor / JSON column |
| Custom auth flow | `AdminUser` Fortify actions | Subclass each action, rebind in your provider |
| Plan-gated feature | `AdminPlans` | `register_plan_permission` + `\Pricing::add()` in `$this->app->booted()` |

---

## 4. Design patterns in use

### 4.1 Registry pattern (preferred extension)

Singletons registered in `AppServiceProvider`, populated from module `boot()`:

| Registry | Helper(s) — defined in `app/Support/helpers.php` | Purpose |
|----------|---------------------------------------------------|---------|
| `App\Support\Navigation\SidebarRegistry` | `register_sidebar_section`, `register_sidebar_item`, `register_user_sidebar_section`, `register_user_sidebar_item` | Admin & portal nav |
| `App\Support\Navigation\HeaderRegistry` | `register_header_item`, `add_to_header` | Admin/portal header slots |
| `App\Support\Dashboard\AdminDashboardRegistry` | `register_admin_dashboard_item` | Admin dashboard widgets |
| `App\Support\Dashboard\UserDashboardRegistry` | `register_user_dashboard_item` | Portal dashboard widgets |
| `App\Support\Plans\PlanPermissionRegistry` | `register_plan_permission`, `plan_permissions` | Plan feature matrix |
| `Modules\AdminPlans\Facades\Pricing` (`PricingService`) | `\Pricing::add([...])`, `\Pricing::addSubFeatures([...])` | Public pricing table rows |
| `Modules\AppCredits\Support\CreditActionRegistry` | `register_credit_action`, `consume_credits`, `credit_service()`, `credit_settings()`, `credit_topup_service()`, `credit_summary()` | AI credit billing |
| `Modules\AdminSettings\Support\SettingsPageRegistry` | `register_setting_item` | Settings nav |
| `Modules\AppPayments\…\PaymentGatewaySettingsRegistry` | `::register('key', [...])` | Gateway config UI |
| `Modules\AdminCrons\Support\SystemCronRegistry` | `afterResolving(SystemCronRegistry::class, fn ($r) => $r->register([...]))` | Scheduled tasks |
| `Modules\AppIntegrations\Support\IntegrationCatalog` | per-module register | Integration cards |
| `Modules\AdminThemes\Support\ThemeRegistry` | per-module register | Theme metadata |
| `Modules\AppPublishing\Support\PublishingProviderPaletteRegistry` | `publishing_provider_tone`, `publishing_provider_chip_style` | Publisher branding |
| `Modules\AppAffiliate\Support\AffiliateService` | `affiliate_service()`, `affiliate_enabled()` | Affiliate logic |

> Always call helpers with `function_exists` guard if your code might run before a module boots (priorities differ).

### 4.2 Service / Support classes

- `Support/` — stateless helpers, catalogs, registry backends (e.g. `BusinessQrRenderer`, `PlanLimitGuard`, `TimezoneCatalog`, `OptionStore`, `WorldLanguageCatalog`).
- `Services/` — transactional workflows (e.g. `MarketplacePackageService`, `PaymentLifecycleService`, `NotificationService`, `AffiliateService`, `CreditTopupService`).
- **Facades** — `Modules\AdminPlans\Facades\Pricing`, `Modules\AdminNotifications\Facades\*`. Modules call facades inside `$this->app->booted()` to ensure the target singleton is built.

### 4.3 Livewire-first controllers

Most "pages" are Livewire 4 components mounted with `Route::livewire()`. Controllers remain only for downloads (e.g. `BusinessQrController`), webhooks, redirects, and public embeds.

### 4.4 Payment plugin pattern

Each `modules/Payment*/Providers/*ServiceProvider.php` does three things:

1. Registers a `PaymentGatewayDefinition` (key, label, capabilities, currencies).
2. Binds the concrete gateway implementation to the gateway contract.
3. Calls `PaymentGatewaySettingsRegistry::register('stripe', [...])` for the admin config UI.

Mirror this triple exactly for any `modules/CustomPayment*`.

### 4.5 Middleware pipeline

Covered in §2.6. Extend via `app('router')->aliasMiddleware(...)` + per-route in `routes/custom.php`. Avoid editing `bootstrap/app.php`.

---

## 5. Injecting custom logic — recipes

### 5.1 New feature module (preferred)

```
modules/CustomReports/
  module.json              # { "name":"CustomReports", "providers":["Modules\\CustomReports\\Providers\\CustomReportsServiceProvider"], "priority":100 }
  Providers/CustomReportsServiceProvider.php
  Routes/web.php
  Livewire/ReportIndex.php
  Resources/views/index.blade.php
  config/config.php        # ['route_prefix' => 'portal/custom-reports']
  Database/Migrations/2026_..._create_custom_reports_table.php
```

Provider boot:

```php
public function register(): void
{
    $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.customreports');
}

public function boot(): void
{
    $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
    $this->loadViewsFrom(__DIR__.'/../Resources/views', 'customreports');
    $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

    register_user_sidebar_item('analytics', [
        'label' => __('Custom Reports'),
        'route_name' => 'portal.custom-reports',
        'active_when' => ['portal.custom-reports*'],
        'icon' => 'fa-light fa-chart-mixed',
        'order' => 50,
        'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('localboost') ?? false,
    ]);
}
```

The module is **auto-discovered** — no `bootstrap/providers.marketplace.php` edit needed. Run `php artisan migrate` after deploy.

### 5.2 Override a core controller / service

Do **not** edit `modules/AppBusinessProfiles/Http/Controllers/BusinessQrController.php`.

```php
// app/Custom/Http/Controllers/BusinessQrController.php
namespace App\Custom\Http\Controllers;

class BusinessQrController extends \Modules\AppBusinessProfiles\Http\Controllers\BusinessQrController
{
    public function svg(/* ...args */) {
        // your changes
        return parent::svg(/* ... */);
    }
}
```

```php
// app/Custom/Providers/CustomServiceProvider::register()
$this->app->bind(
    \Modules\AppBusinessProfiles\Http\Controllers\BusinessQrController::class,
    \App\Custom\Http\Controllers\BusinessQrController::class,
);
```

Routes resolved through the container (`[Class::class, 'method']`) pick up the binding. For routes that pass an instance directly, define a *new* route with the same name in `routes/custom.php` (later definitions win).

### 5.3 Add custom routes safely

```php
// app/Custom/Providers/CustomServiceProvider::boot()
$this->loadRoutesFrom(base_path('routes/custom.php'));
```

```php
// routes/custom.php
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('portal/custom-reports')
    ->group(function (): void {
        Route::livewire('/', \App\Custom\Livewire\CustomReportIndex::class)
            ->name('portal.custom-reports');
    });
```

Register the provider in `bootstrap/providers.marketplace.php`:

```php
<?php
return [
    \App\Custom\Providers\CustomServiceProvider::class,
];
```

That's the only wiring step — `bootstrap/providers.php` already requires this file.

### 5.4 Marketplace / licensed add-ons

`bootstrap/providers.marketplace.php` is the official append-only hook (also used by the Admin → Marketplace UI). Keep its contents one provider per line. Append; do not reformat existing entries.

### 5.5 Plans, limits, pricing UI

```php
register_plan_permission([
    'key' => 'custom_reports',
    'label' => __('Custom Reports'),
    'type' => 'config',
    'order' => 70,
    'fields' => [
        ['key' => 'max_custom_reports', 'label' => __('Reports limit'), 'type' => 'number', 'default' => 5],
    ],
]);

$this->app->booted(function (): void {
    \Pricing::add([[
        'sort' => 200, 'key' => 'custom_reports', 'label' => __('Custom Reports'),
        'check' => true, 'type' => 'boolean', 'raw' => 0,
    ]]);
});
```

Enforce via `auth()->user()?->canUsePlanFeature('custom_reports')` and `PlanLimitGuard`.

### 5.6 Cron / scheduled work

```php
$this->app->afterResolving(
    \Modules\AdminCrons\Support\SystemCronRegistry::class,
    function ($registry): void {
        $registry->register([
            'key' => 'custom_reports.weekly_email',
            'label' => __('Weekly Custom Reports email'),
            'command' => 'custom-reports:email',
            'expression' => '0 9 * * 1',
        ]);
    }
);
```

Define the Artisan command in your module's `Console/Commands/` and register through your provider.

### 5.7 Eloquent observers

```php
// CustomServiceProvider::boot()
\Modules\AdminUser\Models\User::observe(\App\Custom\Observers\UserObserver::class);
```

---

## 6. `app/Custom/` reference layout

```
app/Custom/
  Providers/CustomServiceProvider.php
  Http/Controllers/
  Livewire/
  Support/
  Services/
  Models/                     (only tables you own)
  Actions/Fortify/            (when subclassing Fortify actions)
  Observers/
  database/migrations/
  resources/views/
  lang/
```

PSR-4 already maps `App\` → `app/`; no `composer.json` change needed.

---

## 7. Authentication & authorization

- **Laravel Fortify** + Livewire auth pages (`app/Livewire/Auth/*`).
- View bindings in `App\Providers\FortifyServiceProvider`.
- Concrete actions: `Modules\AdminUser\Actions\Fortify\{CreateNewUser, UpdateUserProfileInformation, UpdateUserPassword, ResetUserPassword}`.
- Admin gate: `App\Http\Middleware\EnsureAdminAccess`.
- Team scope: `Modules\AppTeams\Support\TeamWorkspaceAccess`.

To override: subclass any action under `App\Custom\Actions\Fortify\…` and rebind in `CustomServiceProvider::register()`:

```php
\Laravel\Fortify\Fortify::createUsersUsing(\App\Custom\Actions\Fortify\CreateNewUser::class);
```

---

## 8. AI & credits

- `laravel/ai` package used from `AdminAI`, `AppAI*` modules.
- Helpers: `credit_service()`, `consume_credits($user, 'action_key', [...])`, `register_credit_action([...])`.
- New AI features must register a credit action and call `consume_credits()` before invoking the LLM.

---

## 9. Update-safety checklist

- [ ] Zero diff under `modules/Admin*`, `modules/App*`, `modules/Payment*`.
- [ ] Zero diff under `app/` outside `app/Custom/`.
- [ ] Zero diff under `routes/web.php`, `bootstrap/app.php`, `bootstrap/providers.php`, `config/*.php`.
- [ ] All custom code lives in `modules/Custom*` or `app/Custom`, registered via `bootstrap/providers.marketplace.php`.
- [ ] Migrations additive only (no `dropColumn` on author tables).
- [ ] Container bindings documented in `README.custom.md` — re-verify after every author update (class renames break bindings silently).
- [ ] `composer dump-autoload` after adding any new namespace.

---

## 10. Quick reference — important classes

| Concern | Class |
|---------|-------|
| Current user | `Modules\AdminUser\Models\User` |
| Options / settings | `Modules\AdminSettings\Support\OptionStore` |
| Sidebar registry | `App\Support\Navigation\SidebarRegistry` |
| Header registry | `App\Support\Navigation\HeaderRegistry` |
| Dashboard registries | `App\Support\Dashboard\{Admin,User}DashboardRegistry` |
| Plan permissions | `App\Support\Plans\PlanPermissionRegistry` |
| Plan service / facade | `Modules\AdminPlans\Support\PlanService`, `Modules\AdminPlans\Facades\Pricing` |
| Payments | `Modules\AppPayments\Support\PaymentManager`, `PaymentGatewaySettingsRegistry` |
| Themes | `Modules\AdminThemes\Support\ThemeManager`, `SetThemeContext` middleware |
| Locale | `Modules\AdminLanguages\Http\Middleware\SetLocale`, `WorldLanguageCatalog` |
| Demo mode | `App\Http\Middleware\PreventDemoModeWriteOperations`, `App\Livewire\DemoModeActionGuard` |
| Audit log | `Modules\AdminUser\Models\AuditLog`, helper `log_activity()` |
| Storage | `App\Support\Storage\StorageDriverManager`, `SocialAvatarStore` |

For frontend integration, see **`ARCHITECTURE_FRONTEND.md`**. For step-by-step workflows, see **`CHECKLIST.md`**.
