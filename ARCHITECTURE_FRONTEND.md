# LocalBoost AI — Frontend Architecture

How UI is built, themed, and compiled—and how to customize appearance **without editing core theme or module view files**.

---

## 1. Stack summary

| Layer | Technology | Notes |
|-------|------------|-------|
| Templates | **Blade** | Primary markup; module views + theme layouts |
| Interactivity | **Livewire 4** | Full-page components via `Route::livewire()` |
| Sprinkles | **Alpine.js** | Inline `x-data`, `$dispatch`, modals in Blade |
| CSS | **Tailwind CSS v4** | `@import` + `@theme` in theme `assets/css/app.css` |
| Icons | **Font Awesome** (`fa-light`) | Loaded from shared theme plugins |
| Charts / editors | Shared JS | `resources/themes/shared/js/highcharts.js`, `image-editor.js` |
| Build | **Vite** (Laravel `Illuminate\Foundation\Vite`) | Per-theme entry via `theme_vite()` helper |
| SPA frameworks | **None** | No React/Vue app shell in this codebase |

There is **no root `package.json`** in the distributed tree; theme assets are built with Vite conventions and manifests under `public/build/themes/`. Composer `setup` script references `npm run build`—add a **project-level** `package.json` only in your custom theme workflow if you rebuild assets locally.

---

## 2. Theme system

### 2.1 Directory layout

```
resources/themes/
  app/default/              # CORE backend theme (do not edit)
  guest/localboostai/       # CORE marketing/auth theme (do not edit)
  shared/                   # CORE shared CSS, JS, plugins (do not edit)
    css/theme-base.css
    js/
    plugins/                # codemirror, fontawesome, flags, …
    views/components/       # <x-shared.*> namespace
```

Each theme folder contains:

```
resources/themes/{area}/{name}/
  theme.json                # metadata, color settings schema, order
  assets/
    css/app.css             # Tailwind entry (@import theme-base)
    js/app.js               # typically imports app.css
  resources/views/
    layouts/                # app.blade.php, auth.blade.php
    components/             # x-ui.* anonymous components
    pages/                  # marketing/auth pages (guest)
    partials/               # head.blade.php (vite tags)
    livewire/               # optional Livewire view overrides
```

### 2.2 Theme areas (`config/themes.php`)

| Area | Fallback | Used for |
|------|----------|----------|
| `guest` | `localboostai` | Home, pricing, blogs, login/register (route name based) |
| `app` | `default` | Portal, admin, settings (`app`, `dashboard`, `settings` prefixes) |

Active theme per area is stored in DB options (`frontend_theme`, `backend_theme`) via **Admin → Themes**.

### 2.3 Runtime resolution

`Modules\AdminThemes\Http\Middleware\SetThemeContext`:

1. Resolves area with `ThemeAreaResolver` from current route.
2. `View::replaceNamespace("theme-{area}", $theme->viewsPath())`.
3. Registers Blade anonymous component paths for `x-ui`, `x-layout`, `x-theme`, `x-ai`, layouts.
4. Shares `$currentTheme` to all views.

**View naming:**

```blade
@extends(theme_view('layouts.app'))
{{-- resolves to theme-app::layouts.app --}}
```

**Assets:**

```blade
{!! theme_vite('app', ['assets/js/app.js', 'resources/themes/shared/js/highcharts.js']) !!}
```

Helpers live in `modules/AdminThemes/Support/helpers.php` (`theme_asset`, `theme_setting`, `theme_font_stack`, etc.).

### 2.4 Styling model

- Global design tokens exposed as CSS variables: `--theme-accent-rgb`, `--theme-border-color-rgb`, `--theme-muted-text-color`, etc.
- Module Blade uses inline `style="color: var(--theme-header-text-color)"` for theme-aware colors.
- Tailwind utility classes (`rounded-[1.35rem]`, `sm:px-5`) mixed with variables.
- Dark mode: controlled by theme settings (`supports_dark_mode`, `default_appearance`).

Admin **Themes** UI can inject **custom CSS/JS** per active theme (`custom_css`, `custom_js` in `theme.json` schema)—prefer this for minor branding before forking a theme.

---

## 3. UI component layers

### 3.1 `x-ui.*` (backend shell)

Defined under `resources/themes/app/default/resources/views/components/ui/`:

- Examples: `<x-ui.button>`, `<x-ui.card>`, `<x-ui.shell>`, `<x-ui.modal>`, `<x-ui.table>`.
- Registered as anonymous components from the active **app** theme path.
- Module views (e.g. `modules/AppBusinessProfiles/Resources/views/index.blade.php`) depend heavily on these.

### 3.2 `x-shared.*`

From `resources/themes/shared/views/components/` (registered in `AppServiceProvider` with prefix `shared`).

### 3.3 `x-layout.*`, `x-theme.*`, `x-ai.*`

Subfolders under theme `components/` registered with namespaces in `SetThemeContext`.

### 3.4 Module views

Loaded via `loadViewsFrom(..., 'appbusinessprofiles')`:

```blade
{{-- in Livewire render() --}}
return view('appbusinessprofiles::index');
```

**Namespace:** lowercase module alias from provider (`appsupport`, `adminmarketplace`, …).

---

## 4. Livewire + Blade integration

- Livewire class: `modules/.../Livewire/FooIndex.php`.
- Default view: `modules/.../Resources/views/...` **or** theme `livewire/` override if same relative path exists in active theme (project-specific).
- Layout: many pages use `<x-ui.shell>` in `theme-app::layouts.app`.
- File uploads: CSRF exceptions for `livewire/upload-file` in `bootstrap/app.php`.
- Demo mode: `App\Livewire\DemoModeActionGuard` component hook blocks mutating actions.

**Browser events:** Livewire `$this->dispatch('event-name')` and Alpine `$dispatch('modal-close')` for local UI—not Laravel domain events.

---

## 5. Guest / marketing frontend

- Theme: `resources/themes/guest/localboostai/`.
- Pages: `resources/views/pages/*.blade.php` inside theme (pricing, blogs, contact).
- Layout: `layouts/app.blade.php` with `theme_vite('guest/default')` or area-specific entry.
- Auth: Fortify views pointed to theme auth templates (`resources/themes/guest/.../auth/`).

Customize by cloning to `resources/themes/guest/custom/` and selecting **custom** in admin theme settings (after registering `theme.json`).

---

## 6. Safe override strategies

### 6.1 Clone a theme (recommended for visual rebrand)

1. Copy `resources/themes/app/default` → `resources/themes/app/custom` (or new name).
2. Copy `resources/themes/guest/localboostai` → `resources/themes/guest/custom` if needed.
3. Edit `theme.json` (`name`, `order`, colors).
4. Set `THEME_BACKEND=custom` / `THEME_FRONTEND=custom` in `.env` or activate in admin UI.
5. Rebuild Vite manifest into `public/build/themes/{area}/{name}/` for your entry points.

**Never** edit `default` or `localboostai` folders in place.

### 6.2 Override a single Blade view (module)

**Option A — Module custom view (isolated feature):**

Put `Resources/views/index.blade.php` in `modules/CustomFoo/` and point Livewire `render()` to `customfoo::index`.

**Option B — Theme override (visual only):**

If Livewire uses `view('appbusinessprofiles::index')`, you cannot override via theme namespace unless you change the Livewire class (custom module). Prefer **copy view to custom module** and swap Livewire binding via custom route.

**Option C — View composer in `CustomServiceProvider`:**

```php
View::composer('appbusinessprofiles::index', function ($view) {
    $view->with('customBanner', true);
});
```

Non-destructive augmentation only.

### 6.3 Override `x-ui` components

Copy `components/ui/button.blade.php` (etc.) into **your custom app theme** `resources/views/components/ui/`. Anonymous component path registration picks the active theme directory first.

### 6.4 Custom CSS/JS without rebuild

1. Admin → Themes → select active theme → **Custom CSS / Custom JS** fields.
2. Or add `assets/css/overrides.css` in custom theme and import from `app.css`:

```css
@import "../../../../../themes/shared/css/theme-base.css";
@import "./overrides.css";
```

### 6.5 Shared plugins

`resources/themes/shared/plugins/` (CodeMirror, Font Awesome, flags) are **core**. Do not modify; reference via `theme_shared_asset('plugins/...')` or add parallel assets under your custom theme `assets/`.

---

## 7. Vite / asset build notes

- Config reference: `config/themes.php` → `vite.entry_points`, `build_root` = `build/themes`.
- Helper `theme_vite()` maps entry paths to `resources/themes/{area}/{name}/...`.
- Hot file: `public/hot` for dev.
- Manifest example: `public/build/themes/guest/default/manifest.json`.
- Non-standard docroot: `theme_vite()` adjusts asset prefix when `DOCUMENT_ROOT !== public_path()`.

**Avoid compilation conflicts:**

- Do not add a second global Vite config that collides with theme manifests.
- Keep custom entries **inside your custom theme folder** only.
- When importing shared JS, use full paths accepted by `theme_vite()` (see `partials/head.blade.php` in default theme).

---

## 8. Landing pages & public embeds

`AppLandingPages` serves public campaign pages with minimal layouts (`public/templates/*-shell.blade.php`) calling `theme_vite('app', ['assets/js/app.js'])`. Customize shells by:

- Custom module views registered on new routes, or
- Cloned templates in `modules/CustomLanding/` (do not edit `AppLandingPages` core views).

---

## 9. Localization

- JSON translations: `lang/{locale}.json` at project root.
- Custom strings: add `lang/en.json` keys in a **new** file or `app/Custom/lang/` loaded via provider (do not merge-edit core `lang/ar.json` etc. in place—copy keys you need).

Use `__()` in all custom Blade/Livewire.

---

## 10. What is core vs custom (frontend)

| Core (immutable) | Custom (your work) |
|------------------|-------------------|
| `resources/themes/app/default/**` | `resources/themes/app/custom/**` |
| `resources/themes/guest/localboostai/**` | `resources/themes/guest/custom/**` |
| `resources/themes/shared/**` | Your theme-only assets (don't patch shared) |
| `modules/*/Resources/views/**` | `modules/Custom*/Resources/views/**` |
| `app/Installer/resources/views/**` | — |
| `public/build/themes/**` (author builds) | `public/build/themes/app/custom/**` after local build |

---

## 11. Testing UI changes

1. Clear view cache: `php artisan view:clear`.
2. Confirm active theme in admin (backend + frontend).
3. Hard-refresh browser (Vite manifest/hot).
4. Verify both light/dark if enabled.
5. Check portal + guest routes (different theme areas).

See **`CHECKLIST.md`** for end-to-end feature workflow. See **`ARCHITECTURE_BACKEND.md`** for routes, Livewire registration, and registries.
