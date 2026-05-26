# LocalBoost AI — Backend Architecture

This document maps how the Laravel 13 backend is structured and how to customize it **without modifying core files**.

---

## 1. High-level shape

LocalBoost AI is a **modular monolith**:

- **`app/`** — thin application shell (auth pages, guest marketing, installer, global registries, middleware).
- **`modules/`** — ~70 feature modules (`Admin*`, `App*`, `Payment*`) containing most models, Livewire UI, routes, and business logic.
- **`resources/themes/`** — presentation layer (see `ARCHITECTURE_FRONTEND.md`).
- **`bootstrap/providers.php`** — auto-discovers every `modules/*/module.json` and registers providers (sorted by `priority`).

There is **no classic Repository layer**. Data access uses **Eloquent models**; orchestration uses **`Support/`** and **`Services/`** classes plus **registry singletons**.

---

## 2. Directory map

### 2.1 Core `app/` (protect — extend, do not edit)

| Path | Role |
|------|------|
| `app/Providers/AppServiceProvider.php` | Singleton registries, Livewire hooks, shared Blade paths |
| `app/Providers/FortifyServiceProvider.php` | Auth views & Fortify bindings (actions live in `AdminUser`) |
| `app/Installer/` | First-run installer (middleware prepended in `bootstrap/app.php`) |
| `app/Http/Middleware/` | `EnsureAdminAccess`, `ResolveUserPlanState`, demo mode guard |
| `app/Http/Controllers/` | Guest marketing & static pages, public storage |
| `app/Livewire/Auth/`, `app/Livewire/Portal/` | Login/register/reset, portal dashboard stub |
| `app/Support/` | **Extension hubs**: `SidebarRegistry`, `HeaderRegistry`, `AdminDashboardRegistry`, `UserDashboardRegistry`, `PlanPermissionRegistry`, storage helpers |
| `app/Support/helpers.php` | Global helpers: `register_sidebar_*`, `register_plan_permission`, credits, dashboards |
| `app/Concerns/` | Shared traits (`HasLocalizedAttributes`, password rules) |

### 2.2 Core `modules/` (protect — add sibling `Custom*` module instead)

**Naming prefixes:**

| Prefix | Audience | Examples |
|--------|----------|----------|
| `Admin*` | Super-admin / settings | `AdminUser`, `AdminPlans`, `AdminThemes`, `AdminMarketplace` |
| `App*` | Customer portal (LocalBoost) | `AppBusinessProfiles`, `AppPayments`, `AppAIStudio` |
| `Payment*` | Payment gateway plugins | `PaymentStripe`, `PaymentRazorpay` |

**Typical module anatomy** (example: `AppBusinessProfiles`):

```
modules/AppBusinessProfiles/
  module.json
  Providers/AppBusinessProfilesServiceProvider.php
  Routes/web.php
  Http/Controllers/          # thin HTTP (QR, downloads)
  Livewire/                # primary UI (BusinessIndex, BusinessEdit, …)
  Models/                  # LocalBusiness, …
  Support/                 # catalogs, renderers, module helpers
  Resources/views/         # Blade; namespace appsupport → appbusinessprofiles
  config/config.php        # route_prefix, feature flags
  Database/Migrations/     # module-owned tables
```

**Discovery & boot** (`bootstrap/providers.php`):

1. Scans `modules/*`.
2. Reads `module.json` → `providers`, `files`, `priority`.
3. Falls back to `Modules\{Name}\Providers\{Name}ServiceProvider`.
4. `require_once` on `Support/helpers.php` if present.
5. Merges `bootstrap/providers.marketplace.php` for licensed add-ons.

### 2.3 Routes (core)

| File | Contents |
|------|----------|
| `routes/web.php` | Home, guest pages, social auth, `portal/dashboard` Livewire |
| `routes/settings.php` | (loaded; settings routes mostly in modules) |
| `routes/public-storage.php` | Signed public file access |
| `modules/*/Routes/web.php` | Majority of admin + portal routes |

Portal routes commonly use:

```php
Route::middleware(['web', 'auth', 'verified'])
    ->prefix('portal/...')
    ->group(function (): void {
        Route::livewire('/', SomeIndex::class)->name('portal....');
    });
```

### 2.4 Database (core)

| Path | Role |
|------|------|
| `database/migrations/2026_04_18_110000_create_database.php` | Monolithic baseline schema |
| `modules/*/Database/Migrations/` | Per-module incremental migrations |
| `database/seeders/` | Demo/plan/AI template seeders |

**User model:** `Modules\AdminUser\Models\User` (not `App\Models\User`).

### 2.5 Config (core)

Key files: `config/themes.php`, `config/app.php`, `config/fortify.php`, plus per-module `config/config.php` merged as `config('modules.{alias}.*')`.

Settings persistence: `Modules\AdminSettings\Support\OptionStore` (DB-backed options).

---

## 3. Core vs custom — decision table

| Need | Core (author) | Custom (you) |
|------|---------------|--------------|
| New portal feature | — | `modules/Custom{Feature}/` **or** `app/Custom/` |
| Tweak sidebar/menu | — | `register_*_sidebar_*` in your provider |
| Override a service | — | Child class + `$this->app->bind()` in `CustomServiceProvider` |
| New payment gateway | `Payment*` pattern | New `modules/CustomPayment*` registering `PaymentGatewayDefinition` |
| New admin page | `Admin*` module | `modules/CustomAdmin*` + `register_sidebar_item` |
| DB column on author table | Avoid altering core migrations | New migration in custom module; accessor or JSON column if possible |
| Auth behavior | `AdminUser` Fortify actions | Subclass + rebind in `app/Custom/Providers` |

---

## 4. Design patterns in use

### 4.1 Registry pattern (preferred extension)

Singletons registered in `AppServiceProvider` and populated from module `boot()`:

| Registry | Helper(s) | Purpose |
|----------|-----------|---------|
| `SidebarRegistry` | `register_sidebar_section`, `register_sidebar_item`, `register_user_sidebar_*` | Admin & portal navigation |
| `HeaderRegistry` | `register_header_item`, `add_to_header` | Admin header slots (Blade partials) |
| `AdminDashboardRegistry` | `register_admin_dashboard_item` | Admin dashboard widgets |
| `UserDashboardRegistry` | `register_user_dashboard_item` | Portal dashboard widgets |
| `PlanPermissionRegistry` | `register_plan_permission` | Plan feature matrix / limits |
| `SettingsPageRegistry` | `register_setting_item` | Admin settings navigation |
| `CreditActionRegistry` | `register_credit_action` | Billable AI actions |
| `PaymentGatewaySettingsRegistry` | `->register()` in Payment modules | Gateway config UI |
| `SystemCronRegistry` | `afterResolving` callback | Scheduled task definitions |
| `IntegrationCatalog`, `ThemeRegistry`, etc. | Module-specific | Integrations & themes |

Menu label overrides are stored via `OptionStore` (`admin_menu_sidebar_overrides`)—use admin Menu Builder instead of editing Blade when possible.

### 4.2 Service / Support classes

- **`Support/`** — stateless helpers, catalogs, registries, facades' backends (`BusinessQrRenderer`, `PlanLimitGuard`).
- **`Services/`** — transactional workflows (`MarketplacePackageService`, `PaymentLifecycleService`, `NotificationService`).
- **Facades** — e.g. `Modules\AdminPlans\Facades\Pricing` with `PricingService`; modules call `\Pricing::add([...])` inside `$this->app->booted()`.

### 4.3 Livewire-first controllers

Most “pages” are **Livewire 4** components registered with `Route::livewire()`. Controllers remain for downloads, webhooks, redirects, and public embeds.

### 4.4 Payment plugin pattern

`modules/PaymentStripe/Providers/...` registers:

1. `PaymentGatewayDefinition` (metadata, capabilities).
2. Concrete `*PaymentGateway` class binding.
3. `PaymentGatewaySettingsRegistry::register('stripe', [...])`.

Copy this tripartite pattern for custom gateways in `modules/CustomPayment*`.

### 4.5 Middleware pipeline (`bootstrap/app.php`)

Order matters: `PrepareInstallation` → web stack → `SetLocale` → `SetThemeContext` → `CaptureAffiliateReferral` → `ResolveUserPlanState` → `EnsureAdminAccess` → `PreventDemoModeWriteOperations`.

Custom global middleware: register in `app/Custom/Providers/CustomServiceProvider` via `Route::middlewareGroup` or append in a **user-owned** bootstrap hook (avoid editing `bootstrap/app.php` when possible; use `$this->app->booted()` + `Middleware::appendToGroup` if Laravel API allows, or document a single approved line).

---

## 5. Injecting custom logic (recipes)

### 5.1 New feature module (recommended)

1. Create `modules/CustomReports/` with `module.json` and `CustomReportsServiceProvider`.
2. In `boot()`:
   - `loadRoutesFrom`, `loadViewsFrom`, `loadMigrationsFrom`, `mergeConfigFrom`.
   - `register_user_sidebar_item(...)` (portal) or `register_sidebar_item(...)` (admin).
   - `register_plan_permission([...])` if plan-gated.
3. Implement `Livewire/ReportIndex.php` + `Resources/views/index.blade.php`.
4. Run `php artisan migrate`.

No core files touched; survives author updates.

### 5.2 Override a core controller or service

**Do not edit** `modules/AppBusinessProfiles/Http/Controllers/BusinessQrController.php`.

```php
// app/Custom/Http/Controllers/BusinessQrController.php
namespace App\Custom\Http\Controllers;

class BusinessQrController extends \Modules\AppBusinessProfiles\Http\Controllers\BusinessQrController
{
    public function svg(/* ... */) {
        // custom logic
        return parent::svg(/* ... */);
    }
}
```

```php
// app/Custom/Providers/CustomServiceProvider.php — register()
$this->app->bind(
    \Modules\AppBusinessProfiles\Http\Controllers\BusinessQrController::class,
    \App\Custom\Http\Controllers\BusinessQrController::class
);
```

If routes are bound to the class explicitly, your binding applies. If routes use invokable array syntax with the original class name, add a **new route** in `routes/custom.php` with higher precedence or the same name in a service provider `booted()` callback (prefer new route name to avoid conflicts).

### 5.3 Override routes safely

**File:** `routes/custom.php` (loaded only from your provider):

```php
Route::middleware(['web', 'auth', 'verified'])
    ->prefix('portal/custom-reports')
    ->group(function (): void {
        Route::livewire('/', \App\Custom\Livewire\CustomReportIndex::class)
            ->name('portal.custom-reports');
    });
```

Register provider in `bootstrap/providers.custom.php`.

### 5.4 Extend registries only

In `CustomServiceProvider::boot()`:

```php
register_user_sidebar_item('analytics', [
    'label' => 'Custom KPIs',
    'route_name' => 'portal.custom-reports',
    'active_when' => ['portal.custom-reports'],
    'icon' => 'fa-light fa-chart-mixed',
    'order' => 50,
    'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('localboost') ?? false,
]);
```

### 5.5 Marketplace / licensed modules

Installed add-ons append providers via `bootstrap/providers.marketplace.php`:

```php
<?php
return [
    // Modules\SomeAddon\Providers\SomeAddonServiceProvider::class,
];
```

Do not edit core marketplace installer code; use admin Marketplace UI + this file.

### 5.6 Plans & limits

- Register features: `register_plan_permission([ 'key' => 'my_feature', 'type' => 'toggle', ... ])`.
- Enforce in Livewire: `PlanLimitGuard`, `$user->canUsePlanFeature('my_feature')`, config fields on existing keys (`max_businesses`, etc.).
- Pricing table UI: `\Pricing::add([...])` and `\Pricing::addSubFeatures([...])` inside `$this->app->booted()`.

### 5.7 Cron / background work

Use `afterResolving(SystemCronRegistry::class, function ($registry) { $registry->register([...]); });` or a custom Artisan command in your module scheduled from your provider (mirror `AdminMarketplace`).

---

## 6. `app/Custom/` namespace layout

Recommended structure for project-specific code that is not large enough for its own module:

```
app/Custom/
  Providers/CustomServiceProvider.php
  Http/Controllers/
  Livewire/
  Support/
  Models/              # tables you own only
  database/migrations/
  resources/views/
```

Register in `composer.json` is **not** required (PSR-4 `App\` already maps to `app/` → `App\Custom\...`).

---

## 7. Authentication & authorization

- **Laravel Fortify** + **Livewire** auth pages under `app/Livewire/Auth/`.
- User actions: `Modules\AdminUser\Actions\Fortify\*`.
- Admin gate: `EnsureAdminAccess` middleware + permission helpers on `User`.
- Team/workspace: `Modules\AppTeams\Support\TeamWorkspaceAccess`.

Custom auth: subclass Fortify actions, rebind in `CustomServiceProvider`, optionally override views via guest theme clone.

---

## 8. AI & credits

- Laravel AI package (`laravel/ai`) used from AI modules.
- Credits: `credit_service()`, `consume_credits()`, `register_credit_action()` (`AppCredits` module).

Custom AI features should register credit actions and plan permissions, not patch `AdminAI` core.

---

## 9. Update safety checklist

- [ ] Zero files changed under `modules/Admin*`, `modules/App*`, `modules/Payment*` (unless installing author update).
- [ ] Custom code only in `app/Custom/`, `modules/Custom*`, `routes/custom.php`, custom themes.
- [ ] Migrations additive only.
- [ ] Container bindings documented (re-verify after author update).
- [ ] `composer dump-autoload` if new module namespace added.

---

## 10. Quick reference — important classes

| Concern | Class / location |
|---------|------------------|
| Current user | `Modules\AdminUser\Models\User` |
| Options/settings | `Modules\AdminSettings\Support\OptionStore` |
| Sidebar | `App\Support\Navigation\SidebarRegistry` |
| Plans | `Modules\AdminPlans\Support\PlanService`, `PricingService` |
| Payments | `Modules\AppPayments\Support\PaymentManager` |
| Themes | `Modules\AdminThemes\Support\ThemeManager` |
| Demo mode | `App\Http\Middleware\PreventDemoModeWriteOperations` |

For frontend integration of the same features, see **`ARCHITECTURE_FRONTEND.md`**. For step-by-step workflows, see **`CHECKLIST.md`**.
