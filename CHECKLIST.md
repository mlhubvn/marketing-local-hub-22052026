# How to Add a Feature or Customize Logic Safely

Use this checklist for **every** customization so author updates can be applied without merge conflicts.

> **Golden rule:** never edit anything under `app/`, `modules/Admin*`, `modules/App*`, `modules/Payment*`, `routes/web.php`, `bootstrap/app.php`, `bootstrap/providers.php`, `config/*.php`, `database/migrations/<author>`, or the two core themes (`app/default`, `guest/localboostai`). All custom code lives in `modules/Custom*`, `app/Custom/`, `routes/custom.php`, custom themes — wired through **`bootstrap/providers.marketplace.php`** (which the core already requires).

---

## Phase 0 — Plan (before writing code)

- [ ] **Name the outcome.** One sentence: portal page, admin tool, payment tweak, branding, automation, …
- [ ] **Locate core behavior.** Grep route name, helper (`register_*`), or Livewire class under `modules/`.
- [ ] **Pick the smallest extension** (in this order):
  - [ ] Registry hook only (sidebar, header, dashboard, plan, credit, cron, pricing, integration)
  - [ ] New `modules/Custom{Feature}/` module (auto-discovered)
  - [ ] `app/Custom/` + provider registered in `bootstrap/providers.marketplace.php`
  - [ ] Container rebind + child class
  - [ ] Theme clone (`resources/themes/{area}/custom/`)
- [ ] **Confirm zero core edits** in the plan.

---

## Phase 1 — Check hooks first (mandatory)

Search for an existing extension point before writing classes:

- [ ] Navigation: `register_user_sidebar_item` / `register_sidebar_item` / `register_*_sidebar_section`
- [ ] Header: `register_header_item`, `add_to_header`
- [ ] Dashboard: `register_user_dashboard_item`, `register_admin_dashboard_item`
- [ ] Settings nav: `register_setting_item`
- [ ] Plans: `register_plan_permission`, `\Pricing::add()`, `\Pricing::addSubFeatures()`
- [ ] Credits (AI): `register_credit_action`, `consume_credits`, `credit_service()`
- [ ] Payments: `PaymentGatewayDefinition` + `PaymentGatewaySettingsRegistry::register()`
- [ ] Cron: `afterResolving(SystemCronRegistry::class, …)`
- [ ] Options/settings storage: `Modules\AdminSettings\Support\OptionStore`
- [ ] Menu Builder (admin UI): label/order changes without touching Blade
- [ ] Integration cards: `IntegrationCatalog`
- [ ] Publisher palette: `publishing_provider_tone`, `publishing_provider_chip_style`

If a hook covers it → implement inside your provider only; skip Phase 2.

---

## Phase 2 — Scaffold custom code

### Option A — New module (feature-sized work; preferred)

- [ ] Create `modules/Custom{Feature}/`:
  - [ ] `module.json` — `{ "name": "...", "providers": ["Modules\\Custom{Feature}\\Providers\\Custom{Feature}ServiceProvider"], "priority": 100 }`
  - [ ] `Providers/Custom{Feature}ServiceProvider.php`
  - [ ] `Routes/web.php`
  - [ ] `Livewire/` and/or `Http/Controllers/`
  - [ ] `Resources/views/`
  - [ ] `config/config.php` (route prefix, flags)
  - [ ] `Database/Migrations/` (only tables you own)
- [ ] Provider `register()`: `mergeConfigFrom(..., 'modules.custom{feature}')` + container bindings.
- [ ] Provider `boot()`: `loadRoutesFrom`, `loadViewsFrom(..., 'custom{feature}')`, `loadMigrationsFrom`, optional `loadJsonTranslationsFrom`, registry calls.
- [ ] `composer dump-autoload` if autoload errors appear.
- [ ] `php artisan migrate`.

> The module is auto-discovered by `bootstrap/providers.php` — **no `marketplace.php` edit required** for this option.

### Option B — `app/Custom/` (smaller global tweak)

- [ ] Create `app/Custom/Providers/CustomServiceProvider.php` with `register()` (bindings) + `boot()` (`loadRoutesFrom(base_path('routes/custom.php'))`, view composers, observers, etc.).
- [ ] Create `routes/custom.php` for any new routes.
- [ ] Append the provider to `bootstrap/providers.marketplace.php`:
  ```php
  <?php
  return [
      \App\Custom\Providers\CustomServiceProvider::class,
  ];
  ```
- [ ] **Do NOT** edit `bootstrap/providers.php` — it already `require`s `providers.marketplace.php` and merges the result.
- [ ] `composer dump-autoload` if you added new namespaces.

### Option C — Theme-only branding

- [ ] Copy `resources/themes/app/default` → `resources/themes/app/custom` and/or `guest/localboostai` → `guest/custom`.
- [ ] Update `theme.json` (`name`, `order`, colors, `supports_dark_mode`).
- [ ] Activate in **Admin → Themes** (or set `OptionStore` keys `backend_theme` / `frontend_theme`).
- [ ] Build Vite assets into `public/build/themes/{area}/custom/`.
- [ ] Smoke-test light/dark, mobile, and every area you cloned.

---

## Phase 3 — Routes & Livewire

- [ ] New routes go in `modules/Custom*/Routes/web.php` **or** `routes/custom.php` — never elsewhere.
- [ ] Named routes use dot notation: `portal.custom-*`, `admin.custom-*`.
- [ ] Middleware:
  - Portal: `['web', 'auth', 'verified']`
  - Admin: `['web', 'auth', EnsureAdminAccess::class]` (mirror sibling `Admin*` modules)
- [ ] Pages prefer `Route::livewire('/path', SomeIndex::class)->name('portal....')`.
- [ ] When overriding behavior, prefer a **new** route name + new sidebar link over hijacking an existing name.
- [ ] Run `php artisan route:list | grep custom` to verify.

---

## Phase 4 — Extend classes (when registry hooks are not enough)

- [ ] Create child class under `app/Custom/` or `modules/Custom*/`.
- [ ] Override only the methods you must; call `parent::` to preserve author behavior.
- [ ] Bind in your provider:
  ```php
  $this->app->bind(\Modules\X\Service::class, \App\Custom\Services\MyService::class);
  ```
- [ ] Confirm resolution: `php artisan tinker --execute="dump(get_class(app(\Modules\X\Service::class)));"`.
- [ ] Document the binding in `README.custom.md` — author class renames will break it silently.

---

## Phase 5 — Data & permissions

- [ ] New tables → migrations in `modules/Custom*/Database/Migrations/` or `app/Custom/database/migrations/`.
- [ ] **Never** alter author tables. If unavoidable, add a nullable column in a new migration and document the rollback.
- [ ] Plan-gated features → `register_plan_permission([...])` + `\Pricing::add([...])` inside `$this->app->booted()`.
- [ ] Enforce limits with `auth()->user()?->canUsePlanFeature('key')` and `PlanLimitGuard`.
- [ ] Custom seed data → custom seeder in your module, invoked manually (do **not** edit `database/seeders/DatabaseSeeder.php`).

---

## Phase 6 — Frontend / views

- [ ] All user-visible strings wrapped in `__()` / `@lang`.
- [ ] Reuse `<x-ui.*>` and `<x-shared.*>` components — do not re-implement.
- [ ] Module views: `customfeature::view-name`.
- [ ] Themed layouts: `@extends(theme_view('layouts.app'))`.
- [ ] **Do not** edit `modules/*/Resources/views` or `resources/themes/{app/default,guest/localboostai,shared}`.
- [ ] Try **Admin → Themes → Custom CSS / Custom JS** before forking a theme.
- [ ] Theme-aware colors: `var(--theme-accent-rgb)` etc., not hex codes.

---

## Phase 7 — Config & environment

- [ ] Secrets in `.env`; never commit `.env`.
- [ ] Merge custom config via `mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.{alias}')`.
- [ ] **Do not** edit `config/*.php`. If you need an env key core doesn't already read, expose it through your custom config.
- [ ] Document new env vars in `README.custom.md`.

### Production-only notes

- `APP_INSTALLED=true` once installer finishes — otherwise the installer middleware will redirect every request.
- `APP_DEBUG=false` in production hides stack traces; check `storage/logs/laravel.log` instead.
- `SESSION_DRIVER` choice matters: with `database`, clearing the `sessions` table mid-request invalidates the current Livewire session; prefer the CLI or session driver `file` if you script this.

---

## Phase 8 — Quality gate

- [ ] `vendor/bin/pint app/Custom modules/Custom*` — lint **custom paths only**.
- [ ] `php artisan test` for any custom tests you added.
- [ ] `php artisan route:list` — new routes registered, no duplicates.
- [ ] `php artisan migrate:status` — clean.
- [ ] Manual smoke: guest + portal + admin (whichever apply).
- [ ] Demo mode: verify write actions still respect `DemoModeActionGuard`.
- [ ] Plan limits: test allowed and denied roles.

---

## Phase 9 — Update-safety review (before commit / deploy)

- [ ] `git diff --name-only` shows **zero** changes under:
  - `vendor/`
  - `modules/Admin*`, `modules/App*`, `modules/Payment*`
  - `app/` (except `app/Custom/`)
  - `resources/themes/{app/default, guest/localboostai, shared}`
  - `routes/web.php`, `routes/console.php`, `routes/settings.php`, `routes/public-storage.php`, `app/Installer/routes/**`
  - `bootstrap/app.php`, `bootstrap/providers.php`
  - `config/*.php`
  - `database/migrations/<author files>`, `database/seeders/`
  - `Dockerfile`, `entrypoint.sh`, `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, `artisan`, `index.php`
- [ ] All custom code is committed in `app/Custom/`, `modules/Custom*/`, `routes/custom.php`, `bootstrap/providers.marketplace.php`, custom themes.
- [ ] Container bindings are listed in `README.custom.md`.
- [ ] Migrations are additive and reversible.

---

## Phase 10 — After the author releases an update

1. Back up `app/Custom/`, `modules/Custom*/`, `routes/custom.php`, `bootstrap/providers.marketplace.php`, custom themes (or keep them in your own git branch).
2. Apply the author update over core paths.
3. Re-run:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan optimize:clear
   php artisan optimize
   ```
4. Re-verify container bindings — class renames in core will silently break them.
5. Re-run Vite build inside any custom theme if entry points changed in the author docs.
6. Smoke-test demo-mode actions, plan gates, and any rebind targets.

---

## Quick decision tree

```
Need UI only?               → Theme clone or Admin Themes Custom CSS/JS
Need a new page?            → Custom module + Route::livewire + register_sidebar_*
Need to change core logic?  → Child class + app->bind() in CustomServiceProvider
Need a new DB table?        → Migration inside your custom module
Need menu label/order only? → Admin Menu Builder (writes to OptionStore)
Need a new payment gateway? → modules/CustomPayment{Name}/ with PaymentGatewayDefinition
```

---

## File touch matrix

| Path | Touch |
|------|-------|
| `modules/Custom*/**` | ✓ |
| `app/Custom/**` | ✓ |
| `routes/custom.php` | ✓ |
| `bootstrap/providers.marketplace.php` | ✓ (append-only) |
| `resources/themes/*/custom/**` | ✓ |
| `config/custom.php` | ✓ (created by you) |
| `modules/Admin*/**`, `modules/App*/**`, `modules/Payment*/**` | ✗ |
| `app/**` (everything except `app/Custom/`) | ✗ |
| `routes/web.php`, `routes/console.php`, `routes/settings.php`, `routes/public-storage.php` | ✗ |
| `bootstrap/app.php`, `bootstrap/providers.php` | ✗ |
| `config/*.php` (core) | ✗ |
| `resources/themes/app/default/**`, `guest/localboostai/**`, `shared/**` | ✗ |
| `database/migrations/<author>`, `database/seeders/**` | ✗ |
| `Dockerfile`, `entrypoint.sh`, `composer.*`, `package.*`, `artisan`, `index.php` | ✗ |

---

See **`.cursorrules`** for agent-enforced rules, **`ARCHITECTURE_BACKEND.md`** for the PHP structure, and **`ARCHITECTURE_FRONTEND.md`** for Blade/theme/Vite details.
