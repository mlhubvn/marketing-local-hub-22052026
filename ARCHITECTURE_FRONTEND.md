# LocalBoost AI (Stackposts) — Kiến trúc Frontend

Tài liệu mô tả tầng giao diện & tương tác: stack frontend, luồng biên dịch asset, cách core và các module chia sẻ component/layout, quản lý state/thao tác DOM, và điểm tùy biến cho White-label. Dựa trên **mã nguồn thực tế**.

---

## 1. Tổng quan stack

| Lớp | Công nghệ | Ghi chú |
|-----|-----------|---------|
| Template | **Blade** | View của module + layout của theme |
| Tương tác | **Livewire 4** | Component full-page qua `Route::livewire(...)` |
| Sprinkle | **Alpine.js** | `x-data` inline, `$dispatch`, trigger modal |
| CSS | **Tailwind CSS v4** | `@import "tailwindcss"` + block `@theme` trong `assets/css/app.css` của theme |
| Icon | **Font Awesome** (`fa-light`) | Nạp qua shared plugins |
| Chart/Editor | JS dùng chung | `resources/themes/shared/js/{highcharts,image-editor,…}.js` |
| Build | **Vite** | Mỗi theme tự build, gọi qua helper `theme_vite()` |
| SPA framework | **KHÔNG có** | Không React/Vue. **Đừng** thêm. |

> Không có `package.json` / `vite.config.js` ở **gốc dự án**. Mỗi theme sở hữu input build riêng. Script `composer setup` có gọi `npm run build`, kỳ vọng chạy bên trong một theme có `package.json` (vd khi bạn clone `app/default` → `app/custom`).

---

## 2. Hệ thống Theme

### 2.1 Bố cục thư mục

```
resources/themes/
  app/default/              THEME backend core — KHÔNG sửa
  guest/localboostai/       THEME marketing/auth core — KHÔNG sửa
  guest/mlhubfrontend/       theme guest MLHUB (fork localboostai)
  shared/                   CSS/JS/plugin dùng chung — KHÔNG sửa
    css/theme-base.css
    js/{highcharts,image-editor,fingerprint,…}.js
    plugins/{codemirror5,fontawesome,flags,…}
    views/components/       namespace <x-shared.*>
```

Nội dung mỗi theme:

```
resources/themes/{area}/{name}/
  theme.json                metadata + schema màu/biến + supports_dark_mode + custom_css + custom_js
  assets/
    css/app.css             entry Tailwind v4
    js/app.js
  resources/views/
    layouts/                app.blade.php, auth.blade.php
    components/             ui/, layout/, theme/, ai/  → x-ui.*, x-layout.*, x-theme.*, x-ai.*
    pages/                  trang marketing/auth (khu guest)
    partials/               head.blade.php (thẻ vite)
    livewire/               override view Livewire (tùy chọn)
```

### 2.2 Hai "area" theme

Định nghĩa trong `config/themes.php`:

| Area | Theme mặc định (code) | **Theme đang ACTIVE (env)** | Dùng cho |
|------|----------------|------------------------------|----------|
| `guest` | `localboostai` | **`mlhubfrontend`** (`THEME_FRONTEND`) | Home, pricing, blogs, faqs, contact, login/register/reset |
| `app` | `default` | `default` (`THEME_BACKEND`) | Portal (`/portal/*`), admin (`/admin/*`), settings (`/settings/*`), dashboard |

Theme đang dùng của mỗi area lưu trong `OptionStore` (`frontend_theme`, `backend_theme`), chỉnh ở **Admin → Themes**, và được khởi tạo từ env `THEME_FRONTEND` / `THEME_BACKEND`.

> **Quan trọng cho vibecode:** khu guest đang dùng **`mlhubfrontend`** (`resources/themes/guest/mlhubfrontend/`, fork `localboostai`). Backend portal dùng **`mlhubbackend`** (fork `default`). Gốc upstream vẫn là `localboostai` / `default` để merge update tác giả. App là **MLHUB** (`mlhub.vn`), locale mặc định **`vi`**.

### 2.3 Giải quyết theme khi runtime

Middleware `Modules\AdminThemes\Http\Middleware\SetThemeContext` chạy trong web group:

1. Xác định area bằng `ThemeAreaResolver` dựa trên prefix/tên route hiện tại.
2. `View::replaceNamespace("theme-{area}", $theme->viewsPath())`.
3. Đăng ký đường dẫn Blade anonymous component cho `ui`, `layout`, `theme`, `ai`.
4. Share `$currentTheme` cho mọi view.

**Kế thừa layout & nạp asset:**

```blade
@extends(theme_view('layouts.app'))   {{-- → theme-app::layouts.app của theme đang active --}}

{!! theme_vite('app', [
    'assets/js/app.js',
    'resources/themes/shared/js/highcharts.js',
]) !!}
```

Helper trong `modules/AdminThemes/Support/helpers.php`: `theme_view`, `theme_vite`, `theme_asset`, `theme_shared_asset`, `theme_setting`, `theme_font_stack`, `theme_color_rgb`.

> Trong Livewire, layout được gắn ở `render()`: `->layout(theme_view('layouts.app', 'app'), ['title' => __('...')])`.

---

## 3. Các lớp component UI (chia sẻ giữa core và module)

| Namespace | Nguồn | Được đăng ký bởi |
|-----------|-------|------------------|
| `x-ui.*` | `components/ui/` của theme app đang active | `SetThemeContext` |
| `x-layout.*`, `x-theme.*`, `x-ai.*` | `components/{layout,theme,ai}/` của theme app | `SetThemeContext` |
| `x-shared.*` | `resources/themes/shared/views/components/` | `App\Providers\AppServiceProvider` |
| View module | `modules/*/Resources/views/` | `loadViewsFrom(..., 'aliasthuong')` của từng module |

- **View module dùng namespace = alias viết thường:** `view('appreviewbooster::index')`, `view('appbookingpages::public.show')`.
- **Cách chia sẻ:** mọi module đều `@extends(theme_view('layouts.app'))` và bọc nội dung trong các `<x-ui.*>` của theme đang active. Nhờ vậy đổi theme là toàn bộ module đổi giao diện đồng bộ.
- Building block dùng lại (KHÔNG dựng lại): `<x-ui.shell>`, `<x-ui.card>`, `<x-ui.modal>`, `<x-ui.table>`, `<x-ui.button>`, `<x-ui.input>`, `<x-ui.select>`, `<x-ui.tab>`, `<x-ui.toast>`, `<x-ui.empty-state>`, `<x-shared.icon>`, `<x-shared.flag>`.

---

## 4. Quản lý State & thao tác DOM

Không có store JS tập trung. State sống ở 3 nơi, theo thứ tự ưu tiên:

1. **State Livewire (server-side)** — nguồn sự thật chính. Thuộc tính public của component (`$business_id`, `$editingId`, `$perPage`…). Mọi hành động dữ liệu là method Livewire (`save`, `edit`, `delete`, `togglePublished`).
2. **State Alpine cục bộ (client-side)** — `x-data` inline cho UI tạm thời: mở/đóng modal, tab, dropdown. Không giữ dữ liệu nghiệp vụ.
3. **Sự kiện trình duyệt** — phối hợp UI qua `$this->dispatch('event-name')` (Livewire) và `$dispatch(...)` (Alpine).

Các quy ước thực tế trong code:
- Thông báo người dùng: `$this->dispatch('app-toast', type: 'success', message: __('...'))` + đôi khi `$this->statusMessage`.
- Phát tín hiệu cho UI khác: `$this->dispatch('review-booster-saved')`.
- Phân trang: trait `WithPagination`, gọi `$this->resetPage()` trong các `updatedXxx()`.
- Upload file: dùng cơ chế upload của Livewire (CSRF đã loại trừ `livewire/upload-file` trong `bootstrap/app.php`).
- **Chế độ Demo:** `App\Livewire\DemoModeActionGuard` được đăng ký là Livewire component hook trong `AppServiceProvider`. Mọi action ghi của Livewire phải tương thích guard này (nó soi tên method + tham số). Khi thêm action ghi mới, đảm bảo không vỡ ở demo mode.

> Ưu tiên Livewire/Alpine cho phối hợp client. **Đừng** lạm dụng Laravel Event cho việc điều phối phía trình duyệt.

---

## 5. Frontend khách / marketing (khu guest)

- Theme: `resources/themes/guest/localboostai/`.
- Trang: `resources/views/pages/*.blade.php` **bên trong theme guest đang active** (pricing, blogs, contact, faqs, home).
- Layout: `layouts/app.blade.php` với `theme_vite('guest', [...])`.
- View auth của Fortify được bind trong `App\Providers\FortifyServiceProvider` tới các Livewire component, vốn dùng layout của theme guest.

---

## 6. Luồng biên dịch asset (Vite)

- Build root: `public/build/themes/` (`config/themes.php` → `build_root`).
- `theme_vite($area, $entries)` phân giải tới manifest của theme active tại `public/build/themes/{area}/{name}/manifest.json`.
- Hot file (dev server Vite): `public/hot`.
- Docroot không chuẩn: `theme_vite()` tự thêm tiền tố URL dựa trên `DOCUMENT_ROOT` vs `public_path()` (để chạy được sau proxy/subfolder).

**Tránh xung đột build:**
- Một project Vite cho mỗi theme. **Đừng** thêm `vite.config.js` toàn cục ở gốc.
- Entry tùy biến chỉ nằm trong thư mục theme custom.
- Key entry trong Blade phải khớp key Vite sinh ra trong manifest.

---

## 7. Tùy biến / Theming / White-label (điểm vào để chỉnh giao diện)

Theo thứ tự "ít rủi ro nhất → nhiều nhất":

### 7.1 Custom CSS / JS không cần build lại (nhanh nhất)
**Admin → Themes → Theme đang active → ô Custom CSS / Custom JS** (lưu trong `theme.json`/`OptionStore`, inject mọi trang qua `theme_setting('custom_css')`). Dùng cho chỉnh branding nhỏ, logo, màu phụ.

### 7.2 Token màu qua `theme.json`
`theme.json` của mỗi theme khai báo schema màu (light + dark): `accent_color`, `sidebar_bg_color`, `header_text_color`, `link_color`, `border_color`, `success/warning/danger`, font (`inter`, `manrope`, `sora`…), bo góc, kiểu nút, density… Các giá trị này phát ra **CSS variables** (`--theme-accent-rgb`, `--theme-header-text-color`, `--theme-border-color-rgb`…). Trong Blade, dùng `style="color: var(--theme-header-text-color)"` thay vì màu cứng.

### 7.3 Clone theme (rebrand toàn diện — khuyến nghị)
1. Copy `resources/themes/app/default` → `resources/themes/app/custom` (đổi `name`, `order` trong `theme.json`).
2. Copy `resources/themes/guest/localboostai` → `resources/themes/guest/custom` nếu cần đổi cả marketing (mẫu sẵn có: `guest/mlhubfrontend`).
3. Chạy Vite build trong theme custom → output phải vào `public/build/themes/{area}/custom/`.
4. Kích hoạt ở **Admin → Themes**.
5. **Không** sửa `default`/`localboostai` sau khi fork — bản fork tự mang theo thay đổi.

### 7.4 Override một view Blade
- **A. Module custom (khi cần thêm logic):** đặt `Resources/views/index.blade.php` trong `modules/CustomFoo/`, render từ Livewire custom, route qua `routes/custom.php`.
- **B. View composer (bổ sung, không thay thế):** `View::composer('appreviewbooster::index', fn ($v) => $v->with(...))`.
- **C. Thay `<x-ui.*>`:** copy file Blade component vào theme app đã clone tại `resources/views/components/ui/` — theme active được ưu tiên nên sẽ thắng.

> View namespace của module (`appreviewbooster::*`) **không** themeable trực tiếp — phải dùng cách A hoặc B.

---

## 8. Landing page & nhúng công khai

- `Modules\AppLandingPages` phục vụ trang chiến dịch công khai bằng shell tối giản, nạp `theme_vite('app', ['assets/js/app.js'])`.
- 5 growth tool (Review/Booking/Coupon/Feedback/Lead) chia sẻ engine landing page này: public view rẽ nhánh trong `QrCampaignPublicController::show()` theo `campaign->type` (`appreviewbooster::public.show`, `appbookingpages::public.show`…), hoặc dùng `LandingPage` đã publish nếu có.
- Tùy biến: thêm `modules/CustomLanding*` với shell + route mới, hoặc override partial qua theme. **Đừng** sửa view `AppLandingPages` trực tiếp.

---

## 9. Đa ngôn ngữ (i18n)

- Bản dịch JSON ở gốc: `lang/{locale}.json` (đã có `en.json`, **`vi.json`**).
- Theme có thể có lang riêng: `resources/themes/guest/{name}/lang/{locale}.json`.
- Mọi chuỗi hiển thị **bắt buộc** bọc `__()` / `@lang`.
- Thêm key custom: `app/Custom/lang/{locale}.json` + `loadJsonTranslationsFrom(...)` trong provider của bạn (Laravel merge JSON, không ghi đè).

---

## 10. Ma trận "được phép chạm" (frontend)

| Đường dẫn | Sửa? |
|-----------|:----:|
| `resources/themes/app/default/**` | ✗ |
| `resources/themes/guest/localboostai/**` | ✗ |
| `resources/themes/shared/**` | ✗ |
| `resources/themes/app/custom/**`, `resources/themes/guest/custom/**` (hoặc `mlhubfrontend` / `mlhubbackend`) | ✓ |
| `modules/{Admin,App,Payment}*/Resources/views/**` | ✗ (trừ sửa lỗi production, surgical) |
| `modules/Custom*/Resources/views/**`, `app/Custom/resources/views/**` | ✓ |
| `public/build/themes/app/default/**`, `.../guest/localboostai/**` | ✗ |
| `public/build/themes/{area}/custom/**` | ✓ (artifact build) |

---

## 11. Kiểm thử thay đổi UI

1. `php artisan view:clear` sau khi sửa Blade.
2. Xác nhận theme đang active ở **Admin → Themes**.
3. Hard-refresh trình duyệt (manifest/hot reload của Vite).
4. Kiểm tra cả light + dark nếu theme `supports_dark_mode`.
5. Test ở mọi area bị ảnh hưởng: guest (`/`, `/pricing`), portal (`/portal/*`), admin (`/admin/*`).
6. Xác nhận action Livewire vẫn tôn trọng guard demo-mode.

Xem `ARCHITECTURE_BACKEND.md` cho route/registry/middleware và `ARCHITECTURE_FEATURE.md` cho đánh giá từng tính năng.
