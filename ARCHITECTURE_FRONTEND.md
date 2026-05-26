# LocalBoost AI — Frontend Architecture

How UI is built, themed, and compiled — and exactly **how to customize appearance without editing core theme or module view files**.

---

## 1. Stack summary

| Layer | Technology | Notes |
|-------|------------|-------|
| Templates | **Blade** | Module views + theme layouts |
| Interactivity | **Livewire 4** | Full-page components via `Route::livewire(...)` |
| Sprinkles | **Alpine.js** | Inline `x-data`, `$dispatch`, modal triggers |
| CSS | **Tailwind CSS v4** | `@import "tailwindcss"` + `@theme` block in theme `assets/css/app.css` |
| Icons | **Font Awesome** (`fa-light`) | Loaded by shared theme plugins |
| Charts / editors | Shared JS | `resources/themes/shared/js/{highcharts,image-editor,…}.js` |
| Build | **Vite** via `Illuminate\Foundation\Vite` | Per-theme entry through `theme_vite()` helper |
| SPA frameworks | **None** | No React/Vue shell. Do not introduce one. |

There is **no root `package.json`** in the distributed tree. Each theme owns its own build inputs. The `composer setup` script references `npm run build`, expected to run inside a theme that ships its own `package.json` (e.g. when you clone `app/default` → `app/custom`).

---

## 2. Theme system

### 2.1 Directory layout

```
resources/themes/
  app/default/              CORE backend theme — do not edit
  guest/localboostai/       CORE marketing/auth theme — do not edit
  shared/                   CORE shared CSS, JS, plugins — do not edit
    css/theme-base.css
    js/{highcharts,image-editor,fingerprint,…}.js
    plugins/{codemirror,fontawesome,flags,…}
    views/components/       <x-shared.*> namespace
```

Per-theme contents:

```
resources/themes/{area}/{name}/
  theme.json                metadata, color schema, order, supports_dark_mode, default_appearance, custom_css, custom_js
  assets/
    css/app.css             Tailwind v4 entry
    js/app.js
  resources/views/
    layouts/                app.blade.php, auth.blade.php
    components/             ui/, layout/, theme/, ai/ → x-ui.*, x-layout.*, x-theme.*, x-ai.*
    pages/                  marketing/auth pages (guest area)
    partials/               head.blade.php (vite tags)
    livewire/               optional Livewire view overrides
```

### 2.2 Theme areas

Defined in `config/themes.php`:

| Area | Default theme | Used for |
|------|---------------|----------|
| `guest` | `localboostai` | Home, pricing, blogs, faqs, contact, login/register/reset |
| `app` | `default` | Portal (`/portal/*`), admin (`/admin/*`), settings (`/settings/*`), dashboard |

Active theme per area is stored in `OptionStore` (`frontend_theme`, `backend_theme`) and editable in **Admin → Themes**.

### 2.3 Runtime resolution

`Modules\AdminThemes\Http\Middleware\SetThemeContext` runs in the web group and:

1. Resolves area with `ThemeAreaResolver` from current route prefix / name.
2. Calls `View::replaceNamespace("theme-{area}", $theme->viewsPath())`.
3. Registers Blade anonymous component paths for `ui`, `layout`, `theme`, `ai`.
4. Shares `$currentTheme` to all views.

**Layout extension:**

```blade
@extends(theme_view('layouts.app'))
{{-- resolves to theme-app::layouts.app for active app theme --}}
```

**Asset emission:**

```blade
{!! theme_vite('app', [
    'assets/js/app.js',
    'resources/themes/shared/js/highcharts.js',
]) !!}
```

Helpers in `modules/AdminThemes/Support/helpers.php`: `theme_view`, `theme_vite`, `theme_asset`, `theme_shared_asset`, `theme_setting`, `theme_font_stack`, `theme_color_rgb`.

### 2.4 Styling model

- Design tokens are CSS variables emitted from `theme.json` color settings: `--theme-accent-rgb`, `--theme-header-text-color`, `--theme-border-color-rgb`, `--theme-muted-text-color`, `--theme-card-bg-color-rgb`, …
- Module Blade mixes Tailwind utilities with inline `style="color: var(--theme-header-text-color)"`.
- Dark mode driven by theme `supports_dark_mode` + `default_appearance`.
- Per-active-theme **Custom CSS** and **Custom JS** fields (stored in `OptionStore`) are injected on every page — use this for minor branding before forking.

---

## 3. UI component layers

| Namespace | Source | Registered by |
|-----------|--------|---------------|
| `x-ui.*` | `resources/themes/app/default/resources/views/components/ui/` (or active app theme) | `SetThemeContext` middleware (anonymous component path) |
| `x-layout.*`, `x-theme.*`, `x-ai.*` | active app theme `components/{layout,theme,ai}/` | `SetThemeContext` |
| `x-shared.*` | `resources/themes/shared/views/components/` | `App\Providers\AppServiceProvider` (anonymous component namespace `shared`) |
| Module views | `modules/*/Resources/views/` | each module's `loadViewsFrom(..., 'aliaslowercase')` |

Module views use their lowercase alias as namespace:

```blade
{{-- Livewire render() --}}
return view('appbusinessprofiles::index', [...]);
```

Common building blocks you should reuse (do **not** re-implement):

- `<x-ui.shell>`, `<x-ui.card>`, `<x-ui.modal>`, `<x-ui.table>`, `<x-ui.button>`, `<x-ui.input>`, `<x-ui.select>`, `<x-ui.tab>`, `<x-ui.toast>`, `<x-ui.empty-state>`.
- `<x-shared.icon>`, `<x-shared.flag>`, `<x-shared.timezone-picker>`, `<x-shared.color-picker>`.

---

## 4. Livewire + Blade integration

- Livewire class lives at `modules/.../Livewire/FooIndex.php`.
- Default render path: `modules/.../Resources/views/livewire/...` or the matching module-namespaced view.
- Most pages extend `theme_view('layouts.app')` and wrap content in `<x-ui.shell>`.
- File uploads: `bootstrap/app.php` excludes `livewire/upload-file` and `livewire-*/upload-file` from CSRF.
- Demo mode: `App\Livewire\DemoModeActionGuard` is registered as a Livewire component hook in `AppServiceProvider`. Any custom Livewire write action must remain compatible (it inspects method name + arguments).
- Browser-level events: prefer Livewire `$this->dispatch('event-name')` and Alpine `$dispatch('modal-close')` for UI; do not abuse Laravel events for client coordination.

---

## 5. Guest / marketing frontend

- Theme: `resources/themes/guest/localboostai/`.
- Pages: `resources/views/pages/*.blade.php` *inside the active guest theme* (pricing, blogs, contact, FAQs, home).
- Layout: `layouts/app.blade.php` with `theme_vite('guest', [...])`.
- Fortify auth views are bound in `App\Providers\FortifyServiceProvider` to Livewire components which themselves use the guest theme layout.

Customize by cloning the entire `guest/localboostai` → `guest/custom`, editing `theme.json` (`name`, `order`), then activating in **Admin → Themes**.

---

## 6. Safe override strategies

### 6.1 Clone a theme (recommended for visual rebrand)

1. Copy `resources/themes/app/default` → `resources/themes/app/custom` (rename `theme.json` `name`, `order`).
2. Copy `resources/themes/guest/localboostai` → `resources/themes/guest/custom` if you want the marketing surface.
3. Run Vite build inside the custom theme. Output must land at `public/build/themes/{area}/custom/`. Match the entry points referenced by the theme's layouts.
4. Activate in **Admin → Themes** (or set DB option directly).
5. Never edit `default` or `localboostai` after this — your fork carries forward.

### 6.2 Override a single Blade view

**A. Custom module (preferred when you also need new logic):**

Put `Resources/views/index.blade.php` in `modules/CustomFoo/`, render it from a custom Livewire (`return view('customfoo::index')`), and route to that Livewire from `routes/custom.php`.

**B. View composer (augment, not replace):**

```php
View::composer('appbusinessprofiles::index', function ($view) {
    $view->with('customBanner', true);
});
```

**C. Replace a `<x-ui.*>` component:** copy the Blade file into your cloned app theme at `resources/views/components/ui/`. The active theme path is registered first, so it wins.

> Module view namespaces (`appbusinessprofiles::*`) are **not** themeable directly — you must either swap the Livewire class via custom route (A) or augment via composer (B).

### 6.3 Custom CSS / JS without rebuild

1. **Admin → Themes → Active theme → Custom CSS / Custom JS** fields. Injected via `theme_setting('custom_css')` etc. — no Vite build needed.
2. If you need a real file: inside your cloned theme, add `assets/css/overrides.css` and import from `app.css`:
   ```css
   @import "tailwindcss";
   @import "../../shared/css/theme-base.css";
   @import "./overrides.css";
   ```
   Then run the theme's Vite build.

### 6.4 Shared plugins

`resources/themes/shared/plugins/{codemirror,fontawesome,flags,…}` are **core**. Reference via `theme_shared_asset('plugins/...')`. If a plugin needs replacing, add a parallel asset under your custom theme `assets/` and update layouts to load it instead.

---

## 7. Vite / asset build notes

- Build root: `public/build/themes/` (`config/themes.php` → `build_root`).
- `theme_vite($area, $entries)` resolves to the active theme's manifest at `public/build/themes/{area}/{name}/manifest.json`.
- Hot file: `public/hot` (Vite dev server).
- Non-standard docroot: `theme_vite()` prefixes asset URLs based on `DOCUMENT_ROOT` vs `public_path()`.

**Avoid compilation conflicts:**

- One Vite project per theme. Do not introduce a global `vite.config.js` at project root.
- Custom entries live inside your custom theme folder only.
- Manifest paths inside Blade must match the entry keys produced by Vite.

---

## 8. Landing pages & public embeds

`Modules\AppLandingPages` serves public campaign pages with minimal shells (`public/templates/*-shell.blade.php` or theme-supplied shells) loading `theme_vite('app', ['assets/js/app.js'])`. To customize shells:

- Add a `modules/CustomLanding*` with new shells + new routes.
- Or supply custom shells through theme override of the relevant Blade partials.

Do not edit `AppLandingPages` views directly.

---

## 9. Localization

- JSON translations at project root: `lang/{locale}.json` (core — do not edit in place beyond the supplied locales).
- Custom keys: add `app/Custom/lang/{locale}.json` and load via:
  ```php
  $this->loadJsonTranslationsFrom(__DIR__.'/../lang');
  ```
  in your `CustomServiceProvider::boot()`. Laravel merges JSON sources — your custom file augments, doesn't replace.
- Use `__()` / `@lang` everywhere in custom Blade and Livewire.

---

## 10. Core vs custom — frontend touch matrix

| Path | Touch |
|------|-------|
| `resources/themes/app/default/**` | ✗ |
| `resources/themes/guest/localboostai/**` | ✗ |
| `resources/themes/shared/**` | ✗ |
| `resources/themes/app/custom/**` | ✓ |
| `resources/themes/guest/custom/**` | ✓ |
| `modules/Admin*/Resources/views/**`, `modules/App*/Resources/views/**`, `modules/Payment*/Resources/views/**` | ✗ |
| `modules/Custom*/Resources/views/**` | ✓ |
| `app/Custom/resources/views/**` | ✓ |
| `app/Installer/resources/views/**` | ✗ |
| `public/build/themes/app/default/**`, `public/build/themes/guest/localboostai/**` | ✗ |
| `public/build/themes/app/custom/**`, `public/build/themes/guest/custom/**` | ✓ (built artifacts) |

---

## 11. Testing UI changes

1. `php artisan view:clear` after Blade edits.
2. Confirm active theme in **Admin → Themes** (or `OptionStore::get('backend_theme')`).
3. Hard-refresh browser (Vite manifest / hot reload).
4. Verify light + dark if `supports_dark_mode`.
5. Test in all theme areas the change affects: guest (`/`, `/pricing`), portal (`/portal/...`), admin (`/admin/...`), settings (`/settings/...`).
6. Confirm Livewire actions still respect demo-mode guard.

See **`CHECKLIST.md`** for the end-to-end feature workflow. See **`ARCHITECTURE_BACKEND.md`** for routes, Livewire registration, and registries.
