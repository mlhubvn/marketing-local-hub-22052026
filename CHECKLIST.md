# How to Add a Feature or Customize Logic Safely

Use this checklist for **every** customization so author updates can be applied without merge conflicts.

---

## Phase 0 — Plan (before writing code)

- [ ] **Name the outcome** (portal page, admin tool, payment tweak, branding, automation).
- [ ] **Locate core behavior** (grep route name, `register_*` helper, or Livewire class under `modules/`).
- [ ] **Choose extension type** (fill one):
  - [ ] Registry hook only (sidebar, header, dashboard, plan, credit, cron, pricing)
  - [ ] New `modules/Custom{Feature}/` module
  - [ ] `app/Custom/` + `routes/custom.php`
  - [ ] Container rebind + child class
  - [ ] Theme clone (`resources/themes/{area}/custom/`)
- [ ] **Confirm**: no files under core paths will be edited (see `.cursorrules`).

---

## Phase 1 — Check hooks first (mandatory)

Search for existing extension points **before** subclassing or copying views.

- [ ] **Navigation**: Can `register_user_sidebar_item` / `register_sidebar_item` expose the feature?
- [ ] **Header**: Can `register_header_item` inject a Blade partial?
- [ ] **Dashboard**: Can `register_user_dashboard_item` / `register_admin_dashboard_item` add a widget?
- [ ] **Settings**: Can `register_setting_item` add an admin settings link?
- [ ] **Plans**: Can `register_plan_permission` + `\Pricing::add()` express limits?
- [ ] **Credits** (AI): Can `register_credit_action` define billing?
- [ ] **Payments**: Can a new `PaymentGatewayDefinition` plugin module suffice?
- [ ] **Cron**: Can `SystemCronRegistry` + Artisan command replace core edits?
- [ ] **Options**: Can `OptionStore` / admin settings store config without schema changes?
- [ ] **Menu Builder**: Can admin UI reorder/hide items instead of Blade changes?

If **yes** → implement in `CustomServiceProvider` or `modules/Custom*/Providers/*` only.

---

## Phase 2 — Scaffold custom code

### Option A — New module (feature-sized work)

- [ ] Create `modules/Custom{Feature}/` with:
  - [ ] `module.json` (`providers`, optional `priority` > 0)
  - [ ] `Providers/Custom{Feature}ServiceProvider.php`
  - [ ] `Routes/web.php`
  - [ ] `Livewire/` and/or `Http/Controllers/`
  - [ ] `Resources/views/`
  - [ ] `config/config.php` (route prefix, flags)
  - [ ] `Database/Migrations/` (only tables you own)
- [ ] Provider `boot()`:
  - [ ] `loadRoutesFrom`
  - [ ] `loadViewsFrom(..., 'customfeature')`
  - [ ] `loadMigrationsFrom`
  - [ ] `mergeConfigFrom`
  - [ ] Registry calls (`register_*`)
- [ ] Run `composer dump-autoload` if autoload issues appear.
- [ ] Run `php artisan migrate`.

### Option B — `app/Custom/` (smaller patches)

- [ ] Create `app/Custom/Providers/CustomServiceProvider.php`
- [ ] Create `routes/custom.php` and `loadRoutesFrom` in provider
- [ ] Add `bootstrap/providers.custom.php` returning `[CustomServiceProvider::class]`
- [ ] User adds **one line** to end of `bootstrap/providers.php`:  
      `return array_merge(require __DIR__.'/providers.php', require __DIR__.'/providers.custom.php');`  
      *(Only if not already merged—document for deploy)*
- [ ] Register bindings in `register()` method.

### Option C — Theme-only branding

- [ ] Copy core theme → `resources/themes/app/custom` and/or `guest/custom`
- [ ] Update `theme.json`
- [ ] Point `.env` or admin Themes UI to **custom**
- [ ] Build assets to `public/build/themes/.../custom/`
- [ ] Test light/dark + mobile layouts

---

## Phase 3 — Routes & Livewire

- [ ] Add routes in **`modules/Custom*/Routes/web.php`** or **`routes/custom.php`** only.
- [ ] Use named routes (`portal.custom-*`, `admin.custom-*`).
- [ ] Apply correct middleware: `['web','auth','verified']` for portal; admin routes follow sibling `Admin*` modules.
- [ ] Prefer `Route::livewire(...)` for pages (matches core style).
- [ ] **Do not** edit `routes/web.php` or module core `Routes/web.php`.
- [ ] If overriding behavior: prefer **new route name** + sidebar link; avoid stealing existing names.

---

## Phase 4 — Extend classes (when hooks are insufficient)

- [ ] Create child class under `app/Custom/` or `modules/Custom*/`.
- [ ] Override only methods you must; call `parent::` where possible.
- [ ] Bind in `CustomServiceProvider::register()`:
  ```php
  $this->app->bind(CoreClass::class, CustomClass::class);
  ```
- [ ] Verify route/controller resolution uses the binding (test with `php artisan route:list`).
- [ ] Document binding in project README (author updates may rename core classes).

---

## Phase 5 — Data & permissions

- [ ] New tables → migrations in **custom** module only.
- [ ] Avoid altering author tables; if unavoidable, use additive columns + backup plan.
- [ ] Register `register_plan_permission` when feature is plan-gated.
- [ ] Enforce limits with `canUsePlanFeature()` / `PlanLimitGuard` patterns from core modules.
- [ ] Seed data: custom seeder in `app/Custom` or module, not editing `database/seeders/DatabaseSeeder.php` (call seeder from provider if needed).

---

## Phase 6 — Frontend / views

- [ ] Use `__()` for strings; add lang keys in new JSON files if needed.
- [ ] Use `<x-ui.*>` components for portal/admin consistency.
- [ ] Module views: `customfeature::view-name`.
- [ ] Themed layouts: `@extends(theme_view('layouts.app'))`.
- [ ] **Do not** edit `modules/*/Resources/views` or core themes.
- [ ] For UI tweaks: try theme **Custom CSS/JS** in admin first.
- [ ] Livewire: match conventions (`wire:navigate`, demo-safe actions).

---

## Phase 7 — Config & environment

- [ ] Put secrets in `.env`; never commit `.env`.
- [ ] Merge config from custom provider (`config/custom.php`).
- [ ] **Do not** edit core `config/*.php` unless deploying env-specific values via `.env` keys already read by core.
- [ ] Document new env vars for deploy.

---

## Phase 8 — Quality gate

- [ ] Run `composer lint` on custom paths (Pint).
- [ ] Run `php artisan test` if tests exist for your code.
- [ ] `php artisan route:list` — confirm routes registered.
- [ ] `php artisan migrate:status` — confirm migrations.
- [ ] Manual test: guest + portal + admin (if applicable).
- [ ] Demo mode: verify write actions respect demo guard.
- [ ] Plan limits: test allowed/denied roles.

---

## Phase 9 — Update safety review (before merge/deploy)

- [ ] `git diff` contains **zero** changes under:
  - `modules/Admin*`, `modules/App*`, `modules/Payment*`
  - `app/` except `app/Custom/`
  - `resources/themes/app/default`, `guest/localboostai`, `shared`
  - `routes/web.php`, `bootstrap/app.php`, `bootstrap/providers.php` (except approved custom merge line)
  - `vendor/`
- [ ] Custom module + `app/Custom` tracked in **your** git branch.
- [ ] Container bindings & `providers.custom.php` documented for re-application after author update.
- [ ] Database migrations are backward-compatible.

---

## Phase 10 — After author releases an update

- [ ] Apply author package/files over core paths.
- [ ] **Do not** overwrite `app/Custom/`, `modules/Custom*/`, `routes/custom.php`, custom themes.
- [ ] Re-run `composer install`, `php artisan migrate`, `php artisan view:clear`.
- [ ] Re-verify provider merge line in `bootstrap/providers.php`.
- [ ] Re-test bindings (class renames in core may break overrides).
- [ ] Rebuild custom theme assets if Vite entries changed in author docs.

---

## Quick decision tree

```
Need UI only? → Theme clone or Custom CSS/JS
Need new page? → Custom module + Route::livewire + register_sidebar_*
Need to change core service logic? → Child class + app->bind()
Need new DB table? → Custom module migration
Need menu label order only? → Admin Menu Builder / OptionStore overrides
```

---

## File touch matrix (allowed ✓ / forbidden ✗)

| File / area | Touch |
|-------------|-------|
| `modules/Custom*/**` | ✓ |
| `app/Custom/**` | ✓ |
| `routes/custom.php` | ✓ |
| `bootstrap/providers.custom.php` | ✓ |
| `resources/themes/*/custom/**` | ✓ |
| `modules/App*/**` | ✗ |
| `resources/themes/app/default/**` | ✗ |
| `app/Providers/AppServiceProvider.php` | ✗ |

---

Refer to **`.cursorrules`** for agent-enforced rules, **`ARCHITECTURE_BACKEND.md`** for PHP structure, and **`ARCHITECTURE_FRONTEND.md`** for Blade/theme/Vite details.
