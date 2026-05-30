<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
@php
    $gaOptions = app(\Modules\AdminSettings\Support\OptionStore::class);
    $gaEnabled = (string) $gaOptions->get('google_analytics_status', '0') === '1';
    $gaMeasurementId = trim((string) $gaOptions->get('google_analytics_measurement_id', ''));
    $gaTrackApp = (string) $gaOptions->get('google_analytics_track_app', '0') === '1';
    $siteFavicon = url((string) $gaOptions->get('website_favicon', 'img/favicon.png'));
@endphp

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

@if ($gaEnabled && $gaTrackApp && $gaMeasurementId !== '')
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaMeasurementId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', @js($gaMeasurementId));
    </script>
@endif

@include(theme_view('partials.embed-code-head', 'app'))

<link rel="icon" href="{{ $siteFavicon }}" type="image/png">
<link rel="apple-touch-icon" href="{{ $siteFavicon }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|instrument-sans:400,500,600,700|plus-jakarta-sans:400,500,600,700,800|manrope:400,500,600,700,800|outfit:400,500,600,700,800|sora:400,500,600,700,800|space-grotesk:400,500,600,700|public-sans:400,500,600,700,800|ibm-plex-sans:400,500,600,700|dm-sans:400,500,600,700,800" rel="stylesheet" />
@php
    $cardRadius = theme_setting('card_radius', 'app', 14);
    $inputRadius = theme_setting('input_radius', 'app', 10);
    $buttonRadius = theme_setting('button_radius', 'app', 12);
    $pageMaxWidth = theme_setting('page_max_width', 'app', '90rem');
    $sectionSpacing = theme_setting('section_spacing', 'app', '1.5rem');
    $lightAccent = theme_setting('accent_color', 'app', '#ff5f5f');
    $lightAccentRgb = theme_color_rgb('accent_color', 'app', '#ff5f5f');
    $lightSidebarBg = theme_setting('sidebar_bg_color', 'app', '#fffbf8');
    $lightSidebarBgRgb = theme_color_rgb('sidebar_bg_color', 'app', '#fffbf8');
    $lightHeaderBg = theme_setting('header_bg_color', 'app', '#ffffff');
    $lightHeaderBgRgb = theme_color_rgb('header_bg_color', 'app', '#ffffff');
    $lightHeaderActive = theme_setting('header_active_color', 'app', '#ff8c42');
    $lightHeaderActiveRgb = theme_color_rgb('header_active_color', 'app', '#ff8c42');
    $lightLink = theme_setting('link_color', 'app', '#ff5f5f');
    $lightLinkHover = theme_setting('link_hover_color', 'app', '#ff8c42');
    $lightLinkHoverRgb = theme_color_rgb('link_hover_color', 'app', '#ff8c42');
    $lightBorder = theme_setting('border_color', 'app', '#ffe5dc');
    $lightBorderRgb = theme_color_rgb('border_color', 'app', '#ffe5dc');
    $lightMuted = theme_setting('muted_text_color', 'app', '#8a7068');
    $lightSidebarText = theme_setting('sidebar_text_color', 'app', '#2d1810');
    $lightHeaderText = theme_setting('header_text_color', 'app', '#2d1810');
    $lightSuccess = theme_setting('success_color', 'app', '#ff8c42');
    $lightSuccessRgb = theme_color_rgb('success_color', 'app', '#ff8c42');
    $lightWarning = theme_setting('warning_color', 'app', '#ffb347');
    $lightWarningRgb = theme_color_rgb('warning_color', 'app', '#ffb347');
    $lightDanger = theme_setting('danger_color', 'app', '#dc2626');
    $lightDangerRgb = theme_color_rgb('danger_color', 'app', '#dc2626');
    $darkAccent = theme_setting('dark_accent_color', 'app', $lightAccent);
    $darkAccentRgb = theme_color_rgb('dark_accent_color', 'app', $lightAccent);
    $darkSidebarBg = theme_setting('dark_sidebar_bg_color', 'app', '#1f110c');
    $darkSidebarBgRgb = theme_color_rgb('dark_sidebar_bg_color', 'app', '#1f110c');
    $darkHeaderBg = theme_setting('dark_header_bg_color', 'app', '#251610');
    $darkHeaderBgRgb = theme_color_rgb('dark_header_bg_color', 'app', '#251610');
    $darkHeaderActive = theme_setting('dark_header_active_color', 'app', '#ff8c42');
    $darkHeaderActiveRgb = theme_color_rgb('dark_header_active_color', 'app', '#ff8c42');
    $darkLink = theme_setting('dark_link_color', 'app', $darkAccent);
    $darkLinkHover = theme_setting('dark_link_hover_color', 'app', '#ff8c42');
    $darkLinkHoverRgb = theme_color_rgb('dark_link_hover_color', 'app', '#ff8c42');
    $darkBorder = theme_setting('dark_border_color', 'app', '#4a3028');
    $darkBorderRgb = theme_color_rgb('dark_border_color', 'app', '#4a3028');
    $darkMuted = theme_setting('dark_muted_text_color', 'app', '#b89a90');
    $darkSidebarText = theme_setting('dark_sidebar_text_color', 'app', '#ffe5dc');
    $darkHeaderText = theme_setting('dark_header_text_color', 'app', '#fffbf8');
    $darkSuccess = theme_setting('dark_success_color', 'app', '#ff8c42');
    $darkSuccessRgb = theme_color_rgb('dark_success_color', 'app', '#ff8c42');
    $darkWarning = theme_setting('dark_warning_color', 'app', '#ffb347');
    $darkWarningRgb = theme_color_rgb('dark_warning_color', 'app', '#ffb347');
    $darkDanger = theme_setting('dark_danger_color', 'app', '#f87171');
    $darkDangerRgb = theme_color_rgb('dark_danger_color', 'app', '#f87171');
@endphp
<style>
    :root {
        --theme-accent: {{ $lightAccent }};
        --theme-accent-rgb: {{ $lightAccentRgb }};
        --theme-brand-primary: #ff5f5f;
        --theme-brand-secondary: #ff8c42;
        --theme-brand-highlight: #ffb347;
        --theme-brand-highlight-rgb: 255, 179, 71;
        --theme-brand-gradient: linear-gradient(135deg, #ff5f5f 0%, #ff8c42 100%);
        --theme-brand-gradient-soft: linear-gradient(135deg, rgba(var(--theme-accent-rgb), 0.14) 0%, rgba(var(--theme-link-hover-rgb), 0.08) 100%);
        --theme-sidebar-bg: {{ $lightSidebarBg }};
        --theme-sidebar-bg-rgb: {{ $lightSidebarBgRgb }};
        --theme-header-bg: {{ $lightHeaderBg }};
        --theme-header-bg-rgb: {{ $lightHeaderBgRgb }};
        --theme-header-surface: {{ $lightHeaderBg }};
        --theme-header-surface-rgb: {{ $lightHeaderBgRgb }};
        --theme-header-active: {{ $lightHeaderActive }};
        --theme-header-active-rgb: {{ $lightHeaderActiveRgb }};
        --theme-link-color: {{ $lightLink }};
        --theme-link-hover-color: {{ $lightLinkHover }};
        --theme-link-hover-rgb: {{ $lightLinkHoverRgb }};
        --theme-border-color-raw: {{ $lightBorder }};
        --theme-border-color-rgb: {{ $lightBorderRgb }};
        --theme-border-color: {{ $lightBorder }};
        --theme-shell-border-color: {{ $lightBorder }};
        --theme-muted-text-color-raw: {{ $lightMuted }};
        --theme-muted-text-color: {{ $lightMuted }};
        --theme-sidebar-text-color-raw: {{ $lightSidebarText }};
        --theme-sidebar-text-color: {{ $lightSidebarText }};
        --theme-header-text-color-raw: {{ $lightHeaderText }};
        --theme-header-text-color: {{ $lightHeaderText }};
        --theme-success-color: {{ $lightSuccess }};
        --theme-success-color-rgb: {{ $lightSuccessRgb }};
        --theme-warning-color: {{ $lightWarning }};
        --theme-warning-color-rgb: {{ $lightWarningRgb }};
        --theme-danger-color: {{ $lightDanger }};
        --theme-danger-color-rgb: {{ $lightDangerRgb }};
        --theme-surface-base: #ffffff;
        --theme-surface-base-rgb: 255, 255, 255;
        --theme-surface-soft: #fff5f0;
        --theme-surface-soft-rgb: 255, 245, 240;
        --theme-surface-overlay: #ffffff;
        --theme-surface-overlay-rgb: 255, 255, 255;
        --theme-surface-subtle: #fff0eb;
        --theme-surface-subtle-rgb: 255, 240, 235;
        --theme-input-surface: #ffffff;
        --theme-input-surface-rgb: 255, 255, 255;
        --theme-input-text: #2d1810;
        --theme-input-placeholder: #b89a90;
        --theme-button-soft-bg: #fff0eb;
        --theme-button-soft-hover: #ffe5dc;
        --theme-button-soft-text: #2d1810;
        --theme-button-outline-hover: #fff5f0;
        --theme-table-head-bg: rgba(255, 245, 240, 0.92);
        --theme-table-row-hover-bg: rgba(255, 245, 240, 0.75);
        --theme-empty-bg: rgba(255, 245, 240, 0.72);
        --theme-empty-icon-bg: #ffffff;
        --theme-chart-surface: linear-gradient(180deg, rgba(255, 251, 248, 0.98), rgba(255, 245, 240, 0.95));
        --theme-chart-grid: rgba(255, 229, 220, 0.65);
        --theme-card-contrast-bg: #0f172a;
        --theme-card-contrast-text: #ffffff;
        --theme-card-radius: {{ is_numeric($cardRadius) ? $cardRadius.'px' : $cardRadius }};
        --theme-input-radius: {{ is_numeric($inputRadius) ? $inputRadius.'px' : $inputRadius }};
        --theme-button-radius: {{ is_numeric($buttonRadius) ? $buttonRadius.'px' : $buttonRadius }};
        --theme-page-max-width: {{ $pageMaxWidth }};
        --theme-section-spacing: {{ $sectionSpacing }};
        --theme-font-sans: {!! theme_font_stack('app') !!};
        /* Drive Tailwind's base font token so the selected font applies site-wide. */
        --font-sans: var(--theme-font-sans) !important;
    }

    html,
    body,
    button,
    input,
    optgroup,
    select,
    textarea {
        font-family: var(--theme-font-sans);
    }

    button,
    [type="button"],
    [type="submit"],
    [type="reset"],
    [role="button"] {
        cursor: pointer;
    }

    button:disabled,
    [type="button"]:disabled,
    [type="submit"]:disabled,
    [type="reset"]:disabled,
    [role="button"][aria-disabled="true"] {
        cursor: not-allowed;
    }

    .dark {
        --theme-accent: {{ $darkAccent }};
        --theme-accent-rgb: {{ $darkAccentRgb }};
        --theme-brand-primary: #ff5f5f;
        --theme-brand-secondary: #ff8c42;
        --theme-brand-highlight: #ffb347;
        --theme-brand-highlight-rgb: 255, 179, 71;
        --theme-brand-gradient: linear-gradient(135deg, #ff5f5f 0%, #ff8c42 100%);
        --theme-brand-gradient-soft: linear-gradient(135deg, rgba(var(--theme-accent-rgb), 0.18) 0%, rgba(var(--theme-brand-highlight-rgb), 0.1) 100%);
        --theme-sidebar-bg: {{ $darkSidebarBg }};
        --theme-sidebar-bg-rgb: {{ $darkSidebarBgRgb }};
        --theme-header-bg: {{ $darkHeaderBg }};
        --theme-header-bg-rgb: {{ $darkHeaderBgRgb }};
        --theme-header-surface: {{ $darkHeaderBg }};
        --theme-header-surface-rgb: {{ $darkHeaderBgRgb }};
        --theme-header-active: {{ $darkHeaderActive }};
        --theme-header-active-rgb: {{ $darkHeaderActiveRgb }};
        --theme-link-color: {{ $darkLink }};
        --theme-link-hover-color: {{ $darkLinkHover }};
        --theme-link-hover-rgb: {{ $darkLinkHoverRgb }};
        --theme-border-color-raw: {{ $darkBorder }};
        --theme-border-color-rgb: {{ $darkBorderRgb }};
        --theme-border-color: {{ $darkBorder }};
        --theme-shell-border-color: {{ $darkBorder }};
        --theme-muted-text-color-raw: {{ $darkMuted }};
        --theme-muted-text-color: {{ $darkMuted }};
        --theme-sidebar-text-color-raw: {{ $darkSidebarText }};
        --theme-sidebar-text-color: {{ $darkSidebarText }};
        --theme-header-text-color-raw: {{ $darkHeaderText }};
        --theme-header-text-color: {{ $darkHeaderText }};
        --theme-success-color: {{ $darkSuccess }};
        --theme-success-color-rgb: {{ $darkSuccessRgb }};
        --theme-warning-color: {{ $darkWarning }};
        --theme-warning-color-rgb: {{ $darkWarningRgb }};
        --theme-danger-color: {{ $darkDanger }};
        --theme-danger-color-rgb: {{ $darkDangerRgb }};
        --theme-surface-base: #2a1814;
        --theme-surface-base-rgb: 42, 24, 20;
        --theme-surface-soft: #1f110c;
        --theme-surface-soft-rgb: 31, 17, 12;
        --theme-surface-overlay: #251610;
        --theme-surface-overlay-rgb: 37, 22, 16;
        --theme-surface-subtle: #1a0f0b;
        --theme-surface-subtle-rgb: 26, 15, 11;
        --theme-input-surface: #1a0f0b;
        --theme-input-surface-rgb: 26, 15, 11;
        --theme-input-text: #f8fafc;
        --theme-input-placeholder: #64748b;
        --theme-button-soft-bg: #1a2436;
        --theme-button-soft-hover: #222f46;
        --theme-button-soft-text: #f8fafc;
        --theme-button-outline-hover: #162033;
        --theme-table-head-bg: rgba(2, 6, 23, 0.72);
        --theme-table-row-hover-bg: rgba(2, 6, 23, 0.55);
        --theme-empty-bg: rgba(2, 6, 23, 0.42);
        --theme-empty-icon-bg: #0f172a;
        --theme-chart-surface: linear-gradient(180deg, rgba(17,24,39,0.96), rgba(15,23,42,0.88));
        --theme-chart-grid: rgba(71, 85, 105, 0.34);
        --theme-card-contrast-bg: #ffffff;
        --theme-card-contrast-text: #0f172a;
    }

    .app-theme-content a.theme-link {
        color: var(--theme-link-color);
    }

    .app-theme-content a.theme-link:hover {
        color: var(--theme-link-hover-color);
    }

    .app-theme-shell {
        max-width: var(--theme-page-max-width);
    }

    .app-theme-stack {
        gap: var(--theme-section-spacing);
    }

    html {
        scrollbar-width: thin;
        scrollbar-color: rgba(var(--theme-border-color-rgb), 0.9) color-mix(in srgb, var(--theme-surface-soft) 88%, transparent);
    }

    *::-webkit-scrollbar {
        width: 12px;
        height: 12px;
    }

    *::-webkit-scrollbar-track {
        background: color-mix(in srgb, var(--theme-surface-soft) 88%, transparent);
    }

    *::-webkit-scrollbar-thumb {
        border: 3px solid color-mix(in srgb, var(--theme-surface-soft) 88%, transparent);
        border-radius: 999px;
        background: color-mix(in srgb, var(--theme-border-color) 82%, var(--theme-surface-base) 18%);
    }

    *::-webkit-scrollbar-thumb:hover {
        background: color-mix(in srgb, var(--theme-accent) 28%, var(--theme-border-color) 72%);
    }

    *::-webkit-scrollbar-corner {
        background: color-mix(in srgb, var(--theme-surface-soft) 88%, transparent);
    }

    [class*="backdrop-blur"],
    [style*="backdrop-filter"],
    [style*="-webkit-backdrop-filter"] {
        -webkit-backdrop-filter: none !important;
        backdrop-filter: none !important;
    }

    ::backdrop {
        -webkit-backdrop-filter: none !important;
        backdrop-filter: none !important;
    }

    html[data-theme-resolved='dark'] .portal-dashboard .bg-white,
    html[data-theme-resolved='dark'] .portal-dashboard .bg-white\/80,
    html[data-theme-resolved='dark'] .portal-dashboard .bg-white\/90,
    html[data-theme-resolved='dark'] .portal-dashboard .bg-white\/95,
    html[data-theme-resolved='dark'] .portal-dashboard [class*="bg-white"],
    html[data-theme-resolved='dark'] .portal-dashboard [style*="background: #fff"],
    html[data-theme-resolved='dark'] .portal-dashboard [style*="background:#fff"],
    html[data-theme-resolved='dark'] .portal-dashboard [style*="background-color: #fff"],
    html[data-theme-resolved='dark'] .portal-dashboard [style*="background-color:#fff"],
    html[data-theme-resolved='dark'] .portal-dashboard [style*="255,255,255"],
    html[data-theme-resolved='dark'] .portal-dashboard [style*="255, 255, 255"] {
        background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent) !important;
        background-image: none !important;
        border-color: rgba(var(--theme-border-color-rgb), 0.68) !important;
    }

    html[data-theme-resolved='dark'] .portal-dashboard section,
    html[data-theme-resolved='dark'] .portal-dashboard article,
    html[data-theme-resolved='dark'] .portal-dashboard aside,
    html[data-theme-resolved='dark'] .portal-dashboard table,
    html[data-theme-resolved='dark'] .portal-dashboard tbody,
    html[data-theme-resolved='dark'] .portal-dashboard tr {
        border-color: rgba(var(--theme-border-color-rgb), 0.62) !important;
    }

    html[data-theme-resolved='dark'] .portal-dashboard .text-slate-950,
    html[data-theme-resolved='dark'] .portal-dashboard .text-slate-900,
    html[data-theme-resolved='dark'] .portal-dashboard .text-gray-950,
    html[data-theme-resolved='dark'] .portal-dashboard .text-gray-900,
    html[data-theme-resolved='dark'] .portal-dashboard [style*="color:#0f172a"],
    html[data-theme-resolved='dark'] .portal-dashboard [style*="color: #0f172a"] {
        color: var(--theme-header-text-color) !important;
    }

    html[data-theme-resolved='dark'] .portal-dashboard .text-slate-700,
    html[data-theme-resolved='dark'] .portal-dashboard .text-slate-600,
    html[data-theme-resolved='dark'] .portal-dashboard .text-gray-700,
    html[data-theme-resolved='dark'] .portal-dashboard .text-gray-600 {
        color: var(--theme-muted-text-color) !important;
    }

    html[data-theme-resolved='dark'] .portal-dashboard .shadow-sm,
    html[data-theme-resolved='dark'] .portal-dashboard [class*="shadow-"] {
        box-shadow: 0 22px 70px -52px rgba(0, 0, 0, 0.82) !important;
    }

    .theme-brand-gradient {
        background: var(--theme-brand-gradient);
    }

    .theme-brand-gradient-text {
        background: var(--theme-brand-gradient);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
</style>
@if (filled(theme_setting('custom_css', 'app')))
    <style>{!! theme_setting('custom_css', 'app') !!}</style>
@endif
<script>
    (() => {
        const storageKey = 'appearance';
        const media = window.matchMedia('(prefers-color-scheme: dark)');
        const supportsDark = @js((string) theme_setting('supports_dark_mode', 'app', '1')) !== '0';
        const allowToggle = @js((string) theme_setting('allow_user_appearance_toggle', 'app', '1')) !== '0';
        const defaultMode = @js(theme_setting('default_appearance', 'app', theme_setting('appearance', 'app', 'system')));

        const normalizeMode = (mode) => {
            const allowedModes = supportsDark ? ['light', 'dark', 'system'] : ['light'];
            const fallback = supportsDark ? (defaultMode || 'system') : 'light';

            return allowedModes.includes(mode) ? mode : fallback;
        };

        const getMode = () => {
            if (!allowToggle) {
                return normalizeMode(defaultMode || 'system');
            }

            return normalizeMode(localStorage.getItem(storageKey) || defaultMode || 'system');
        };

        const resolve = (mode) => {
            const normalized = normalizeMode(mode);

            if (!supportsDark) {
                return false;
            }

            return normalized === 'dark' || (normalized === 'system' && media.matches);
        };

        const apply = (mode = getMode()) => {
            const normalized = getMode() === mode ? getMode() : normalizeMode(mode);
            const resolved = resolve(normalized) ? 'dark' : 'light';
            document.documentElement.classList.toggle('dark', resolved === 'dark');
            document.documentElement.dataset.themeMode = normalized;
            document.documentElement.dataset.themeResolved = resolved;

            return { mode: normalized, resolved };
        };

        window.themeMode = {
            getMode,
            getResolved: () => document.documentElement.dataset.themeResolved || (resolve(getMode()) ? 'dark' : 'light'),
            supportsDark: () => supportsDark,
            allowToggle: () => allowToggle && supportsDark,
            setMode(mode) {
                const normalized = normalizeMode(mode);

                if (allowToggle && supportsDark) {
                    localStorage.setItem(storageKey, normalized);
                }

                const state = apply(normalized);
                window.dispatchEvent(new CustomEvent('theme-mode-changed', { detail: state }));

                return state;
            },
            apply,
        };

        apply();

        media.addEventListener?.('change', () => {
            if (getMode() !== 'system') {
                return;
            }

            const state = apply('system');
            window.dispatchEvent(new CustomEvent('theme-mode-changed', { detail: state }));
        });

        document.addEventListener('livewire:navigated', () => {
            const state = apply();
            window.dispatchEvent(new CustomEvent('theme-mode-changed', { detail: state }));
        });
    })();
</script>
@if (filled(theme_setting('custom_js', 'app')))
    <script>{!! theme_setting('custom_js', 'app') !!}</script>
@endif
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/fontawesome.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/brands.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/light.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/regular.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/solid.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/thin.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/duotone.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/duotone-light.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/duotone-regular.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/duotone-thin.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/sharp-light.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/sharp-regular.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/sharp-solid.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/sharp-thin.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/sharp-duotone-light.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/sharp-duotone-regular.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/sharp-duotone-solid.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/sharp-duotone-thin.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/chisel-regular.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/etch-solid.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/graphite-thin.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/jelly-regular.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/jelly-fill-regular.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/jelly-duo-regular.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/notdog-solid.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/notdog-duo-solid.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/slab-regular.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/slab-press-regular.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/thumbprint-light.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/utility-semibold.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/utility-fill-semibold.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/utility-duo-semibold.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/whiteboard-semibold.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/flags/flag-icon.css') }}">
@livewireStyles
<script src="{{ theme_shared_asset('plugins/highcharts/highcharts.js') }}"></script>
<script>
    (() => {
        if (window.StackpostsHighcharts || !window.Highcharts) {
            return;
        }

        const cssVar = (name, fallback) => getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;

        window.StackpostsHighcharts = {
            lib: window.Highcharts,
            render: (elOrId, options = {}) => {
                const element = typeof elOrId === 'string' ? document.getElementById(elOrId) : elOrId;

                if (!element) {
                    return null;
                }

                return window.Highcharts.chart(element, window.Highcharts.merge({
                    chart: {
                        backgroundColor: 'transparent',
                        style: {
                            fontFamily: cssVar('--theme-font-sans', 'Inter, sans-serif'),
                        },
                    },
                    title: { text: null },
                    credits: { enabled: false },
                    xAxis: {
                        labels: {
                            style: {
                                color: cssVar('--theme-muted-text-color', '#64748b'),
                            },
                        },
                    },
                    yAxis: {
                        title: { text: null },
                        labels: {
                            style: {
                                color: cssVar('--theme-muted-text-color', '#64748b'),
                            },
                        },
                    },
                    legend: {
                        itemStyle: {
                            color: cssVar('--theme-header-text-color', '#0f172a'),
                        },
                    },
                }, options));
            },
        };

        window.dispatchEvent(new CustomEvent('stackposts:highcharts-ready'));
    })();
</script>

{!! theme_vite('app', ['assets/js/app.js', 'resources/themes/shared/js/highcharts.js', 'resources/themes/shared/js/image-editor.js']) !!}
