<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
@php
    $options = app(\Modules\AdminSettings\Support\OptionStore::class);
    $gaEnabled = (string) $options->get('google_analytics_status', '0') === '1';
    $gaMeasurementId = trim((string) $options->get('google_analytics_measurement_id', ''));
    $gaTrackGuest = (string) $options->get('google_analytics_track_guest', '1') === '1';
    $siteFavicon = url((string) $options->get('website_favicon', 'img/favicon.png'));
    $siteTitle = trim((string) $options->get('website_title', ''));
    $siteTitle = $siteTitle !== '' ? $siteTitle : 'MKT';
    $cardRadius = theme_setting('card_radius', 'guest', 18);
    $inputRadius = theme_setting('input_radius', 'guest', 14);
    $buttonRadius = theme_setting('button_radius', 'guest', 14);
    $pageMaxWidth = theme_setting('page_max_width', 'guest', '86rem');
    $sectionSpacing = theme_setting('section_spacing', 'guest', '5rem');
@endphp

<title>{{ filled($title ?? null) ? $title.' - '.$siteTitle : $siteTitle }}</title>

@if ($gaEnabled && $gaTrackGuest && $gaMeasurementId !== '')
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaMeasurementId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', @js($gaMeasurementId));
    </script>
@endif

@include(theme_view('partials.embed-code-head', 'guest'))

<link rel="icon" href="{{ $siteFavicon }}" type="image/png">
<link rel="apple-touch-icon" href="{{ $siteFavicon }}">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|instrument-sans:400,500,600,700|plus-jakarta-sans:400,500,600,700,800|manrope:400,500,600,700,800|outfit:400,500,600,700,800" rel="stylesheet" />
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/fontawesome.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/brands.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/light.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/regular.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/solid.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/flags/flag-icon.css') }}">

<style>
    :root,
    html[data-theme-resolved='light'],
    html[data-theme-resolved='dark'] {
        --theme-accent: {{ theme_setting('accent_color', 'guest', '#2454E8') }};
        --theme-accent-rgb: {{ theme_accent_rgb('guest') }};
        --theme-body-bg: {{ theme_setting('body_bg_color', 'guest', '#F7FAFC') }};
        --theme-body-bg-rgb: {{ theme_color_rgb('body_bg_color', 'guest', '#F7FAFC') }};
        --theme-surface-bg: {{ theme_setting('surface_bg_color', 'guest', '#FFFFFF') }};
        --theme-surface-bg-rgb: {{ theme_color_rgb('surface_bg_color', 'guest', '#FFFFFF') }};
        --theme-header-bg: {{ theme_setting('header_bg_color', 'guest', '#FFFFFF') }};
        --theme-header-bg-rgb: {{ theme_color_rgb('header_bg_color', 'guest', '#FFFFFF') }};
        --theme-header-text-color: {{ theme_setting('header_text_color', 'guest', '#0F172A') }};
        --theme-link-color: {{ theme_setting('link_color', 'guest', '#2454E8') }};
        --theme-link-hover-color: {{ theme_setting('link_hover_color', 'guest', '#0F766E') }};
        --theme-border-color: {{ theme_setting('border_color', 'guest', '#DCE6F3') }};
        --theme-border-color-rgb: {{ theme_color_rgb('border_color', 'guest', '#DCE6F3') }};
        --theme-muted-text-color: {{ theme_setting('muted_text_color', 'guest', '#64748B') }};
        --theme-success-color: {{ theme_setting('success_color', 'guest', '#059669') }};
        --theme-warning-color: {{ theme_setting('warning_color', 'guest', '#D97706') }};
        --theme-danger-color: {{ theme_setting('danger_color', 'guest', '#DC2626') }};
        --theme-card-radius: {{ is_numeric($cardRadius) ? $cardRadius.'px' : $cardRadius }};
        --theme-input-radius: {{ is_numeric($inputRadius) ? $inputRadius.'px' : $inputRadius }};
        --theme-button-radius: {{ is_numeric($buttonRadius) ? $buttonRadius.'px' : $buttonRadius }};
        --theme-page-max-width: {{ $pageMaxWidth }};
        --theme-section-spacing: {{ $sectionSpacing }};
        --theme-font-sans: {!! theme_font_stack('guest') !!};
        /* Drive Tailwind's base font token so the selected font applies site-wide. */
        --font-sans: var(--theme-font-sans) !important;
    }

    html {
        background: #f7fafc;
        color: #0f172a;
        max-width: 100%;
        overflow-x: clip;
        font-family: var(--theme-font-sans);
    }

    body,
    button,
    input,
    optgroup,
    select,
    textarea {
        font-family: var(--theme-font-sans);
    }

    body {
        max-width: 100%;
        overflow-x: clip;
        background:
            linear-gradient(rgba(36, 84, 232, 0.035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(36, 84, 232, 0.035) 1px, transparent 1px),
            linear-gradient(180deg, #f8fbff 0%, #f7fafc 42%, #eef5fb 100%);
        background-size: 36px 36px, 36px 36px, auto;
    }

    .localboost-shell {
        width: min(calc(100% - 1.5rem), var(--theme-page-max-width));
        margin-left: auto;
        margin-right: auto;
    }

    @media (min-width: 1024px) {
        .localboost-shell {
            width: min(calc(100% - 3rem), var(--theme-page-max-width));
        }
    }

    .localboost-section {
        padding-top: var(--theme-section-spacing);
        padding-bottom: var(--theme-section-spacing);
    }

    .lb-page,
    .lb-auth-page {
        --lb-ink: #242320;
        --lb-muted: #77746d;
        --lb-paper: #fbfaf5;
        --lb-soft: #f4f3ee;
        --lb-line: #e8e5dc;
        --lb-lime: #b4d513;
        --lb-red: #0f766e;
        --lb-moss: #ff5f5f;
        background: var(--lb-paper);
        color: var(--lb-ink);
    }

    .lb-wrap {
        width: min(1120px, calc(100% - 40px));
        margin-inline: auto;
    }

    /* Auth already has outer horizontal padding — do not subtract another 40px or
       Turnstile (~300px) and form controls overflow/clip on narrow phones. */
    .lb-auth-page .lb-wrap {
        width: 100%;
        max-width: 1120px;
    }

    .lb-auth-page .lb-window {
        max-width: 100%;
    }

    .lb-auth-page iframe,
    .lb-auth-page [x-ref="turnstile"],
    .lb-auth-page [x-ref="recaptcha"] {
        max-width: 100%;
    }

    @media (min-width: 1024px) {
        .lb-sidebar-panel {
            position: sticky;
            top: 7rem;
            align-self: start;
            max-height: calc(100vh - 7rem);
            overflow-y: auto;
            overscroll-behavior: contain;
        }
    }

    .lb-serif {
        font-family: Georgia, "Times New Roman", serif;
        letter-spacing: -0.045em;
    }

    .lb-hero-title {
        font-size: clamp(3.1rem, 8vw, 6rem);
        line-height: 0.95;
    }

    .lb-heading {
        font-size: clamp(2.6rem, 5vw, 4rem);
        line-height: 0.96;
    }

    .lb-copy {
        color: var(--lb-muted);
        line-height: 1.8;
    }

    .lb-card,
    .lb-window {
        border: 1px solid var(--lb-line);
        background: #fffefb;
        box-shadow: 0 28px 80px -60px rgba(36, 35, 32, 0.45);
    }

    .lb-window {
        box-shadow: 0 18px 55px -45px rgba(36, 35, 32, 0.5);
    }

    .lb-pill {
        border: 1px solid rgba(95, 127, 7, 0.18);
        background: color-mix(in srgb, var(--lb-lime) 24%, #fff);
        color: #ff5f5f;
    }

    .lb-button {
        background: var(--lb-red);
        color: #fff;
        border-radius: 999px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.22), 0 18px 42px -26px rgba(15,118,110,0.74);
        transition: transform .22s ease, box-shadow .22s ease, background .22s ease;
    }

    .lb-button:hover {
        transform: translateY(-2px);
        background: #0d665f;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.22), 0 22px 52px -28px rgba(15,118,110,0.88);
    }

    .lb-button-soft {
        background: color-mix(in srgb, var(--lb-lime) 28%, #fff);
        color: #324706;
        border-radius: 999px;
        transition: transform .22s ease, background .22s ease;
    }

    .lb-button-soft:hover {
        transform: translateY(-2px);
        background: color-mix(in srgb, var(--lb-lime) 44%, #fff);
    }

    .lb-dot {
        width: 9px;
        height: 9px;
        border-radius: 999px;
        display: inline-block;
    }

    .lb-reveal {
        animation: lb-rise .72s cubic-bezier(.16, 1, .3, 1) both;
        animation-delay: var(--lb-delay, 0ms);
    }

    .lb-float {
        animation: lb-float 6.5s ease-in-out infinite;
        animation-delay: var(--lb-delay, 0ms);
    }

    .lb-shimmer {
        position: relative;
        overflow: hidden;
    }

    .lb-shimmer::after {
        content: "";
        position: absolute;
        inset: 0;
        transform: translateX(-120%);
        background: linear-gradient(110deg, transparent 0%, rgba(255,255,255,.58) 45%, transparent 70%);
        animation: lb-shimmer 4.6s ease-in-out infinite;
    }

    .lb-hover {
        transition: transform .24s ease, box-shadow .24s ease, border-color .24s ease;
    }

    .lb-hover:hover {
        transform: translateY(-5px);
        border-color: color-mix(in srgb, var(--lb-lime) 46%, var(--lb-line));
        box-shadow: 0 34px 92px -62px rgba(36, 35, 32, 0.56);
    }

    .lb-marquee {
        overflow: hidden;
        mask-image: linear-gradient(90deg, transparent, #000 10%, #000 90%, transparent);
    }

    .lb-marquee-track {
        width: max-content;
        animation: lb-marquee 28s linear infinite;
    }

    @keyframes lb-rise {
        from { opacity: 0; transform: translateY(24px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes lb-float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-12px); }
    }

    @keyframes lb-shimmer {
        0%, 62% { transform: translateX(-120%); }
        100% { transform: translateX(120%); }
    }

    @keyframes lb-marquee {
        from { transform: translateX(0); }
        to { transform: translateX(-50%); }
    }

    @media (prefers-reduced-motion: reduce) {
        .lb-reveal,
        .lb-float,
        .lb-shimmer::after,
        .lb-marquee-track {
            animation: none !important;
        }
    }

    .localboost-hero-stage {
        position: relative;
        overflow: hidden;
        isolation: isolate;
        border: 1px solid rgba(var(--theme-border-color-rgb), 0.88);
        background:
            linear-gradient(135deg, rgba(36,84,232,0.11) 0%, rgba(255,255,255,0.88) 34%, rgba(20,184,166,0.12) 68%, rgba(245,158,11,0.14) 100%),
            linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,251,255,0.94));
        box-shadow: 0 38px 110px -78px rgba(15,23,42,0.5);
    }

    .localboost-hero-stage::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -2;
        background:
            linear-gradient(rgba(36,84,232,0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(36,84,232,0.05) 1px, transparent 1px);
        background-size: 28px 28px;
        mask-image: linear-gradient(180deg, black, transparent 86%);
        animation: localboost-grid-drift 22s linear infinite;
    }

    .localboost-hero-stage::after {
        content: "";
        position: absolute;
        inset: auto 0 0 0;
        z-index: -1;
        height: 34%;
        background: linear-gradient(90deg, rgba(36,84,232,0.12), rgba(20,184,166,0.12), rgba(245,158,11,0.10));
        clip-path: polygon(0 34%, 100% 0, 100% 100%, 0 100%);
        opacity: 0.8;
    }

    .localboost-hero-stat {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(var(--theme-border-color-rgb), 0.84);
        background: rgba(255,255,255,0.82);
        box-shadow: 0 18px 48px -38px rgba(15,23,42,0.35);
        animation: localboost-hero-breathe 5.8s ease-in-out infinite;
        animation-delay: var(--hero-delay, 0ms);
    }

    .localboost-hero-stat::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: var(--hero-accent, #2454E8);
    }

    .localboost-hero-chip {
        border: 1px solid color-mix(in srgb, var(--hero-accent, #2454E8) 24%, rgba(var(--theme-border-color-rgb),0.78));
        background:
            linear-gradient(135deg, color-mix(in srgb, var(--hero-accent, #2454E8) 12%, white), rgba(255,255,255,0.86));
        box-shadow: 0 14px 38px -32px rgba(15,23,42,0.32);
        animation: localboost-hero-chip-drift 6.4s ease-in-out infinite;
        animation-delay: var(--hero-delay, 0ms);
    }

    .localboost-hero-dashboard {
        animation: localboost-hero-float 7.2s ease-in-out infinite;
        transform-origin: center;
    }

    .localboost-hero-float-card {
        animation: localboost-hero-float 5.8s ease-in-out infinite;
        animation-delay: var(--hero-delay, 0ms);
    }

    .localboost-hero-qr-dot {
        animation: localboost-qr-pulse 2.7s ease-in-out infinite;
        animation-delay: var(--hero-delay, 0ms);
    }

    .localboost-case-tile {
        position: relative;
        min-height: 18rem;
        overflow: hidden;
        isolation: isolate;
        border: 1px solid rgba(var(--theme-border-color-rgb), 0.82);
        background:
            linear-gradient(135deg, color-mix(in srgb, var(--case-accent, #2454E8) 18%, #ffffff), rgba(255,255,255,0.74)),
            linear-gradient(180deg, #f8fbff, #eef6ff);
        box-shadow: 0 30px 86px -58px rgba(15,23,42,0.45);
    }

    .localboost-case-tile::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -2;
        background:
            linear-gradient(rgba(36,84,232,0.07) 1px, transparent 1px),
            linear-gradient(90deg, rgba(36,84,232,0.07) 1px, transparent 1px);
        background-size: 28px 28px;
        animation: localboost-grid-drift 20s linear infinite;
    }

    .localboost-case-tile::after {
        content: "";
        position: absolute;
        inset: auto 0 0 0;
        z-index: -1;
        height: 58%;
        background: linear-gradient(180deg, transparent 0%, rgba(15,23,42,0.18) 24%, rgba(15,23,42,0.68) 100%);
    }

    .localboost-visual-copy {
        text-shadow: 0 3px 18px rgba(15, 23, 42, 0.62), 0 1px 2px rgba(15, 23, 42, 0.72);
    }

    .localboost-case-window {
        border: 1px solid rgba(255,255,255,0.74);
        background: rgba(255,255,255,0.82);
        box-shadow: 0 22px 52px -38px rgba(15,23,42,0.45);
        backdrop-filter: blur(16px);
        animation: localboost-hero-float 6.8s ease-in-out infinite;
    }

    .localboost-case-metric {
        border: 1px solid rgba(var(--theme-border-color-rgb),0.74);
        background: rgba(255,255,255,0.9);
    }

    .localboost-marquee {
        overflow: hidden;
        mask-image: linear-gradient(90deg, transparent, black 12%, black 88%, transparent);
    }

    .localboost-marquee-track {
        display: flex;
        width: max-content;
        gap: 1rem;
        animation: localboost-marquee 30s linear infinite;
    }

    .localboost-marquee-track.is-reverse {
        animation-direction: reverse;
        animation-duration: 36s;
    }

    .localboost-marquee-card {
        min-width: 15rem;
        border: 1px solid rgba(var(--theme-border-color-rgb),0.76);
        background: rgba(255,255,255,0.86);
        box-shadow: 0 20px 52px -42px rgba(15,23,42,0.36);
        backdrop-filter: blur(16px);
    }

    .localboost-workflow-section {
        margin-top: clamp(-2.5rem, -3vw, -1rem);
    }

    .localboost-card {
        border: 1px solid rgba(var(--theme-border-color-rgb), 0.78);
        background: rgba(255, 255, 255, 0.86);
        box-shadow: 0 24px 70px -48px rgba(15, 23, 42, 0.24);
        backdrop-filter: blur(18px);
    }

    .localboost-soft {
        border: 1px solid rgba(var(--theme-border-color-rgb), 0.72);
        background: linear-gradient(180deg, rgba(255,255,255,0.82), rgba(248,250,252,0.72));
    }

    .localboost-premium {
        position: relative;
        overflow: hidden;
        isolation: isolate;
    }

    .localboost-premium::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background:
            linear-gradient(135deg, rgba(36,84,232,0.10), transparent 32%),
            linear-gradient(315deg, rgba(20,184,166,0.10), transparent 34%),
            linear-gradient(180deg, rgba(255,255,255,0.96), rgba(248,250,252,0.82));
    }

    .localboost-hover-lift {
        transition:
            transform 260ms cubic-bezier(0.22, 1, 0.36, 1),
            box-shadow 260ms cubic-bezier(0.22, 1, 0.36, 1),
            border-color 260ms ease;
    }

    .localboost-hover-lift:hover {
        transform: translateY(-6px);
        border-color: rgba(var(--theme-accent-rgb), 0.34);
        box-shadow: 0 34px 90px -56px rgba(15, 23, 42, 0.34);
    }

    .localboost-pricing-grid > :not([hidden]) + :not([hidden]) {
        box-shadow: inset 1px 0 0 rgba(var(--theme-border-color-rgb), 0.42);
    }

    .localboost-operation-row {
        background: rgba(255, 255, 255, 0.72);
        box-shadow: 0 18px 42px -38px rgba(15, 23, 42, 0.28);
    }

    .localboost-operation-row:hover {
        background: rgba(248, 250, 252, 0.92);
        transform: translateX(4px);
        border-color: rgba(var(--theme-accent-rgb), 0.32) !important;
    }

    .localboost-image-frame {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at 20% 18%, rgba(36,84,232,0.18), transparent 28%),
            radial-gradient(circle at 78% 28%, rgba(20,184,166,0.16), transparent 30%),
            linear-gradient(135deg, #eaf2ff, #dcefff 48%, #eef7f8);
        isolation: isolate;
    }

    .localboost-image-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        color: transparent;
        transform: scale(1.01);
        transition: transform 700ms cubic-bezier(0.22, 1, 0.36, 1), filter 700ms ease;
    }

    .localboost-image-frame::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 1;
        background:
            linear-gradient(180deg, rgba(15,23,42,0.02), rgba(15,23,42,0.46)),
            linear-gradient(90deg, rgba(36,84,232,0.16), transparent 42%);
        pointer-events: none;
    }

    .localboost-hover-lift:hover .localboost-image-frame img {
        transform: scale(1.07);
        filter: saturate(1.08) contrast(1.03);
    }

    .localboost-glass-badge {
        border: 1px solid rgba(255, 255, 255, 0.42);
        background: rgba(255, 255, 255, 0.86);
        box-shadow: 0 14px 30px -22px rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(14px);
    }

    .localboost-visual {
        position: relative;
        min-height: 100%;
        overflow: hidden;
        isolation: isolate;
        background:
            linear-gradient(135deg, rgba(36,84,232,0.10), rgba(20,184,166,0.10)),
            linear-gradient(180deg, #f8fbff, #eaf2fb);
    }

    .localboost-visual-bio,
    .localboost-visual-retail {
        background:
            linear-gradient(135deg, rgba(36,84,232,0.18), rgba(20,184,166,0.16)),
            linear-gradient(180deg, #f8fbff, #dff4f1);
    }

    .localboost-visual-qr,
    .localboost-visual-rules {
        background:
            linear-gradient(135deg, rgba(124,58,237,0.16), rgba(36,84,232,0.18)),
            linear-gradient(180deg, #f8fbff, #e8edff);
    }

    .localboost-visual-analytics,
    .localboost-visual-alerts {
        background:
            linear-gradient(135deg, rgba(245,158,11,0.18), rgba(36,84,232,0.16)),
            linear-gradient(180deg, #fffaf0, #eaf2ff);
    }

    .localboost-visual-domain {
        background:
            linear-gradient(135deg, rgba(14,165,233,0.18), rgba(5,150,105,0.16)),
            linear-gradient(180deg, #f0f9ff, #def7ec);
    }

    .localboost-visual-utm {
        background:
            linear-gradient(135deg, rgba(217,119,6,0.18), rgba(236,72,153,0.14)),
            linear-gradient(180deg, #fff7ed, #fdf2f8);
    }

    .localboost-visual-team,
    .localboost-visual-workspace {
        background:
            linear-gradient(135deg, rgba(124,58,237,0.16), rgba(20,184,166,0.14)),
            linear-gradient(180deg, #f5f3ff, #ecfeff);
    }

    .localboost-visual::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 1;
        background: linear-gradient(180deg, transparent 32%, rgba(15,23,42,0.14) 48%, rgba(15,23,42,0.72) 100%);
        pointer-events: none;
    }

    .localboost-visual-grid {
        position: absolute;
        inset: 0;
        opacity: 0.66;
        background:
            linear-gradient(rgba(36,84,232,0.07) 1px, transparent 1px),
            linear-gradient(90deg, rgba(36,84,232,0.07) 1px, transparent 1px);
        background-size: 26px 26px;
        animation: localboost-grid-drift 18s linear infinite;
    }

    .localboost-phone,
    .localboost-chart-card,
    .localboost-route-map,
    .localboost-dashboard-stack {
        position: absolute;
        inset: 1.25rem;
        z-index: 2;
    }

    .localboost-phone {
        width: 11rem;
        max-width: 48%;
        border: 1px solid rgba(var(--theme-border-color-rgb),0.9);
        border-radius: 1.4rem;
        background: rgba(255,255,255,0.86);
        padding: 1rem;
        box-shadow: 0 24px 60px -42px rgba(15,23,42,0.44);
        animation: localboost-float 5.6s ease-in-out infinite;
    }

    .localboost-phone > span {
        display: block;
        margin: 0 auto 0.9rem;
        width: 2.25rem;
        height: 0.3rem;
        border-radius: 999px;
        background: #dbe6f3;
    }

    .localboost-avatar {
        width: 3rem;
        height: 3rem;
        border-radius: 999px;
        background: linear-gradient(135deg, #0f766e, #b8da16);
    }

    .localboost-line,
    .localboost-button-line {
        height: 0.55rem;
        border-radius: 999px;
        background: #dce6f3;
    }

    .localboost-line {
        margin-top: 0.6rem;
    }

    .localboost-button-line {
        background: linear-gradient(90deg, rgba(15,118,110,0.18), rgba(184,218,22,0.24));
    }

    .localboost-floating-qr,
    .localboost-route-qr {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        grid-template-rows: repeat(7, minmax(0, 1fr));
        gap: 0.18rem;
        border-radius: 1rem;
        background: #fff;
        padding: 0.9rem;
        box-shadow: 0 24px 60px -42px rgba(15,23,42,0.5);
    }

    .localboost-floating-qr {
        position: absolute;
        right: 1.2rem;
        top: 2.4rem;
        width: 8.5rem;
        height: 8.5rem;
        animation: localboost-float 6.4s ease-in-out infinite reverse;
    }

    .localboost-floating-qr span,
    .localboost-route-qr span {
        border-radius: 0.16rem;
        background: transparent;
    }

    .localboost-floating-qr span.is-on,
    .localboost-route-qr span.is-on {
        background: #0f172a;
        animation: localboost-qr-pulse 2.8s ease-in-out infinite;
    }

    .localboost-chart-card {
        border: 1px solid rgba(var(--theme-border-color-rgb),0.84);
        border-radius: 1.25rem;
        background: rgba(255,255,255,0.9);
        padding: 1.25rem;
        box-shadow: 0 24px 60px -42px rgba(15,23,42,0.45);
    }

    .localboost-visual-bar {
        flex: 1;
        border-radius: 999px 999px 0.4rem 0.4rem;
        background: linear-gradient(180deg, #0f766e, #b8da16);
        transform-origin: bottom;
        animation: localboost-bar-rise 3.2s ease-in-out infinite;
    }

    .localboost-visual-stroke,
    .localboost-route-path {
        stroke: #0f766e;
        stroke-width: 4;
        stroke-linecap: round;
        stroke-dasharray: 18 12;
        animation: localboost-dash 2.8s linear infinite;
    }

    .localboost-route-qr {
        position: absolute;
        left: 1.2rem;
        top: 50%;
        width: 8.25rem;
        height: 8.25rem;
        transform: translateY(-50%);
        z-index: 3;
    }

    .localboost-route-node {
        position: absolute;
        z-index: 4;
        transform: translate(-50%, -50%);
        border: 1px solid rgba(var(--theme-border-color-rgb),0.92);
        border-radius: 999px;
        background: rgba(255,255,255,0.92);
        padding: 0.55rem 0.75rem;
        color: #0f172a;
        font-size: 0.72rem;
        font-weight: 800;
        box-shadow: 0 16px 36px -26px rgba(15,23,42,0.5);
    }

    .localboost-mini-window {
        position: absolute;
        inset: 1.25rem;
        border: 1px solid rgba(var(--theme-border-color-rgb),0.86);
        border-radius: 1.25rem;
        background: rgba(255,255,255,0.92);
        padding: 1rem;
        box-shadow: 0 24px 60px -42px rgba(15,23,42,0.44);
        animation: localboost-float 6s ease-in-out infinite;
    }

    .localboost-domain-visual,
    .localboost-utm-visual,
    .localboost-team-visual {
        position: absolute;
        inset: 1.25rem;
        z-index: 2;
    }

    .localboost-domain-card,
    .localboost-utm-card,
    .localboost-team-card {
        border: 1px solid rgba(var(--theme-border-color-rgb),0.86);
        border-radius: 1.15rem;
        background: rgba(255,255,255,0.9);
        box-shadow: 0 24px 60px -42px rgba(15,23,42,0.44);
    }

    .localboost-domain-card {
        position: absolute;
        left: 0.6rem;
        right: 1rem;
        top: 2.6rem;
        padding: 1rem;
        animation: localboost-float 5.8s ease-in-out infinite;
    }

    .localboost-domain-node {
        position: absolute;
        right: 1.2rem;
        bottom: 1.4rem;
        border-radius: 999px;
        background: #0f766e;
        color: white;
        padding: 0.65rem 0.85rem;
        font-size: 0.72rem;
        font-weight: 800;
        box-shadow: 0 18px 40px -26px rgba(15,23,42,0.55);
    }

    .localboost-utm-card {
        position: absolute;
        left: 0.75rem;
        top: 1.35rem;
        width: 72%;
        padding: 1rem;
        animation: localboost-float 6.2s ease-in-out infinite;
    }

    .localboost-utm-pill {
        display: inline-flex;
        margin: 0.2rem;
        border-radius: 999px;
        background: linear-gradient(90deg, rgba(245,158,11,0.18), rgba(236,72,153,0.18));
        padding: 0.48rem 0.65rem;
        color: #7c2d12;
        font-size: 0.68rem;
        font-weight: 800;
    }

    .localboost-team-card {
        position: absolute;
        inset: 1rem 0.9rem auto 0.9rem;
        padding: 1rem;
        animation: localboost-float 5.9s ease-in-out infinite;
    }

    .localboost-member-row {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        border-radius: 0.85rem;
        background: rgba(248,250,252,0.9);
        padding: 0.55rem;
    }

    .localboost-member-avatar {
        width: 1.8rem;
        height: 1.8rem;
        border-radius: 999px;
        background: linear-gradient(135deg, #7c3aed, #14b8a6);
    }

    .localboost-sheen {
        position: relative;
        overflow: hidden;
    }

    .localboost-sheen::after {
        content: "";
        position: absolute;
        inset: -120% auto -120% -40%;
        width: 32%;
        transform: rotate(18deg) translateX(-240%);
        background: linear-gradient(180deg, transparent, rgba(255,255,255,0.28), transparent);
        opacity: 0;
        pointer-events: none;
    }

    .localboost-sheen:hover::after {
        opacity: 1;
        animation: localboost-sheen 1.1s ease;
    }

    .localboost-flow-line {
        position: relative;
    }

    .localboost-flow-line::before {
        content: "";
        position: absolute;
        left: 1.35rem;
        top: 3.4rem;
        bottom: 1.2rem;
        width: 1px;
        background: linear-gradient(180deg, rgba(36,84,232,0.32), rgba(20,184,166,0.18));
    }

    .localboost-data-bars span {
        display: block;
        height: 0.65rem;
        border-radius: 999px;
        background: linear-gradient(90deg, rgba(36,84,232,0.92), rgba(20,184,166,0.88));
        transform-origin: left center;
        animation: localboost-bar 3.8s ease-in-out infinite;
    }

    .localboost-button-primary {
        background: #0f766e;
        color: #ffffff;
        box-shadow: 0 18px 38px -24px rgba(15, 118, 110, 0.62);
    }

    .localboost-auth-primary {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(var(--theme-accent-rgb), 0.58);
        background:
            linear-gradient(135deg, #0f766e 0%, #128a7f 48%, #b8da16 100%);
        color: #ffffff;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.2),
            0 22px 44px -28px rgba(15, 118, 110, 0.62);
    }

    .localboost-auth-primary::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.22), transparent);
        transform: translateX(-120%);
        transition: transform 520ms ease;
    }

    .localboost-auth-primary:hover {
        transform: translateY(-1px);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.24),
            0 26px 58px -30px rgba(20, 184, 166, 0.72);
    }

    .localboost-auth-primary:hover::after {
        transform: translateX(120%);
    }

    .localboost-auth-primary > span {
        position: relative;
        z-index: 1;
    }

    .localboost-auth-social {
        border-color: rgba(var(--theme-border-color-rgb), 0.9);
        background: rgba(255, 255, 255, 0.78);
        color: #0f172a;
        box-shadow: 0 14px 34px -30px rgba(15, 23, 42, 0.32);
    }

    .localboost-auth-social:hover {
        transform: translateY(-1px);
        border-color: rgba(var(--theme-accent-rgb), 0.34);
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 20px 46px -34px rgba(15, 23, 42, 0.38);
    }

    .localboost-button-secondary {
        border: 1px solid rgba(var(--theme-border-color-rgb), 0.86);
        background: rgba(255,255,255,0.82);
        color: #0f172a;
        box-shadow: 0 14px 28px -24px rgba(15, 23, 42, 0.28);
    }

    .localboost-gradient-text {
        background: linear-gradient(90deg, #2454e8 0%, #0f766e 52%, #f59e0b 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .localboost-reveal {
        --localboost-reveal-delay: 0ms;
        opacity: 0;
        transform: translate3d(0, 46px, 0) scale(0.975);
        filter: blur(12px);
        transition:
            opacity 860ms cubic-bezier(0.22, 1, 0.36, 1),
            transform 860ms cubic-bezier(0.22, 1, 0.36, 1),
            filter 860ms cubic-bezier(0.22, 1, 0.36, 1);
        transition-delay: var(--localboost-reveal-delay);
        will-change: transform, opacity, filter;
    }

    .localboost-reveal.is-visible {
        opacity: 1;
        transform: translate3d(0, 0, 0);
        filter: blur(0);
    }

    @keyframes localboost-sheen {
        0% { transform: rotate(18deg) translateX(-240%); }
        100% { transform: rotate(18deg) translateX(520%); }
    }

    @keyframes localboost-bar {
        0%, 100% { transform: scaleX(0.72); opacity: 0.72; }
        50% { transform: scaleX(1); opacity: 1; }
    }

    @keyframes localboost-grid-drift {
        0% { background-position: 0 0, 0 0; }
        100% { background-position: 52px 52px, 52px 52px; }
    }

    @keyframes localboost-float {
        0%, 100% { transform: translate3d(0, 0, 0); }
        50% { transform: translate3d(0, -10px, 0); }
    }

    @keyframes localboost-hero-float {
        0%, 100% { transform: translate3d(0, 0, 0) rotate(0deg); }
        50% { transform: translate3d(0, -8px, 0) rotate(0.18deg); }
    }

    @keyframes localboost-hero-breathe {
        0%, 100% { transform: translate3d(0, 0, 0); box-shadow: 0 18px 48px -38px rgba(15,23,42,0.35); }
        50% { transform: translate3d(0, -3px, 0); box-shadow: 0 24px 58px -40px rgba(15,23,42,0.42); }
    }

    @keyframes localboost-hero-chip-drift {
        0%, 100% { transform: translate3d(0, 0, 0); }
        50% { transform: translate3d(0, -4px, 0); }
    }

    @keyframes localboost-qr-pulse {
        0%, 100% { opacity: 0.72; transform: scale(0.96); }
        50% { opacity: 1; transform: scale(1); }
    }

    @keyframes localboost-bar-rise {
        0%, 100% { transform: scaleY(0.72); opacity: 0.74; }
        50% { transform: scaleY(1); opacity: 1; }
    }

    @keyframes localboost-dash {
        to { stroke-dashoffset: -60; }
    }

    @keyframes localboost-marquee {
        from { transform: translate3d(0, 0, 0); }
        to { transform: translate3d(-50%, 0, 0); }
    }

    @media (prefers-reduced-motion: reduce) {
        .localboost-reveal,
        .localboost-hover-lift,
        .localboost-data-bars span,
        .localboost-sheen::after,
        .localboost-visual-grid,
        .localboost-phone,
        .localboost-floating-qr,
        .localboost-mini-window,
        .localboost-visual-bar,
        .localboost-visual-stroke,
        .localboost-route-path,
        .localboost-hero-stage::before,
        .localboost-hero-stat,
        .localboost-hero-chip,
        .localboost-hero-dashboard,
        .localboost-hero-float-card,
        .localboost-hero-qr-dot,
        .localboost-marquee-track {
            animation: none !important;
            transition: none !important;
            transform: none !important;
            filter: none !important;
            opacity: 1 !important;
        }
    }

    ::selection {
        background: rgba(var(--theme-accent-rgb), 0.22);
        color: #0f172a;
    }

    html[data-theme-resolved='dark'] {
        --theme-body-bg: #07111F;
        --theme-body-bg-rgb: 7, 17, 31;
        --theme-surface-bg: #0B1526;
        --theme-surface-bg-rgb: 11, 21, 38;
        --theme-header-bg: #07111F;
        --theme-header-bg-rgb: 7, 17, 31;
        --theme-header-text-color: #E8EEF7;
        --theme-border-color: #25364D;
        --theme-border-color-rgb: 37, 54, 77;
        --theme-muted-text-color: #94A3B8;
        background: #07111f;
        color: #e8eef7;
    }

    html[data-theme-resolved='dark'] body {
        background:
            linear-gradient(rgba(96, 165, 250, 0.055) 1px, transparent 1px),
            linear-gradient(90deg, rgba(96, 165, 250, 0.055) 1px, transparent 1px),
            radial-gradient(circle at 20% 8%, rgba(37, 99, 235, 0.22), transparent 28%),
            radial-gradient(circle at 82% 22%, rgba(20, 184, 166, 0.18), transparent 26%),
            linear-gradient(180deg, #07111f 0%, #0b1526 48%, #07111f 100%);
        background-size: 36px 36px, 36px 36px, auto, auto, auto;
    }

    html[data-theme-resolved='dark'] .localboost-card,
    html[data-theme-resolved='dark'] .localboost-soft,
    html[data-theme-resolved='dark'] .localboost-hero-stage,
    html[data-theme-resolved='dark'] .localboost-case-tile,
    html[data-theme-resolved='dark'] .localboost-marquee-card,
    html[data-theme-resolved='dark'] footer,
    html[data-theme-resolved='dark'] header > div {
        border-color: rgba(96, 165, 250, 0.22) !important;
        background: rgba(11, 21, 38, 0.82) !important;
        box-shadow: 0 34px 100px -72px rgba(0, 0, 0, 0.8);
    }

    html[data-theme-resolved='dark'] .localboost-hero-stage::before,
    html[data-theme-resolved='dark'] .localboost-case-tile::before {
        background:
            linear-gradient(rgba(96,165,250,0.07) 1px, transparent 1px),
            linear-gradient(90deg, rgba(96,165,250,0.07) 1px, transparent 1px);
    }

    html[data-theme-resolved='dark'] .localboost-main-hero {
        overflow: hidden;
        border-color: transparent !important;
        background:
            radial-gradient(circle at 16% 22%, rgba(37, 99, 235, 0.18), transparent 34%),
            radial-gradient(circle at 76% 42%, rgba(20, 184, 166, 0.14), transparent 32%) !important;
        box-shadow: none !important;
    }

    html[data-theme-resolved='dark'] .localboost-main-hero::before {
        inset: -1.5rem;
        opacity: 0.55;
        mask-image: radial-gradient(circle at center, black 0%, transparent 76%);
    }

    html[data-theme-resolved='dark'] .localboost-main-hero::after {
        opacity: 0.28;
        height: 26%;
        filter: blur(12px);
    }

    html[data-theme-resolved='dark'] .bg-white,
    html[data-theme-resolved='dark'] .bg-white\/70,
    html[data-theme-resolved='dark'] .bg-white\/72,
    html[data-theme-resolved='dark'] .bg-white\/78,
    html[data-theme-resolved='dark'] .bg-white\/82,
    html[data-theme-resolved='dark'] .bg-white\/86,
    html[data-theme-resolved='dark'] .bg-white\/88,
    html[data-theme-resolved='dark'] .bg-white\/92,
    html[data-theme-resolved='dark'] .bg-white\/95 {
        background-color: rgba(15, 23, 42, 0.82) !important;
    }

    html[data-theme-resolved='dark'] .bg-slate-50,
    html[data-theme-resolved='dark'] .bg-slate-50\/80,
    html[data-theme-resolved='dark'] .bg-slate-50\/90,
    html[data-theme-resolved='dark'] .bg-slate-100 {
        background-color: rgba(30, 41, 59, 0.84) !important;
    }

    html[data-theme-resolved='dark'] .text-slate-950,
    html[data-theme-resolved='dark'] .text-slate-900,
    html[data-theme-resolved='dark'] .text-slate-800 {
        color: #f8fafc !important;
    }

    html[data-theme-resolved='dark'] .text-slate-700,
    html[data-theme-resolved='dark'] .text-slate-600 {
        color: #cbd5e1 !important;
    }

    html[data-theme-resolved='dark'] .text-slate-500,
    html[data-theme-resolved='dark'] .text-slate-400 {
        color: #94a3b8 !important;
    }

    html[data-theme-resolved='dark'] .border,
    html[data-theme-resolved='dark'] .border-t,
    html[data-theme-resolved='dark'] .border-b {
        border-color: rgba(96, 165, 250, 0.22) !important;
    }

    html[data-theme-resolved='dark'] [class*="divide-"] > :not([hidden]) ~ :not([hidden]),
    html[data-theme-resolved='dark'] .divide-slate-200 > :not([hidden]) ~ :not([hidden]),
    html[data-theme-resolved='dark'] .divide-blueGray-200 > :not([hidden]) ~ :not([hidden]),
    html[data-theme-resolved='dark'] .divide-gray-200 > :not([hidden]) ~ :not([hidden]) {
        border-color: rgba(96, 165, 250, 0.14) !important;
        border-left-color: rgba(96, 165, 250, 0.14) !important;
        border-top-color: rgba(96, 165, 250, 0.14) !important;
        border-right-color: rgba(96, 165, 250, 0.14) !important;
        border-bottom-color: rgba(96, 165, 250, 0.14) !important;
    }

    html[data-theme-resolved='dark'] .localboost-pricing-grid > :not([hidden]) + :not([hidden]) {
        box-shadow: inset 1px 0 0 rgba(96, 165, 250, 0.08) !important;
    }

    html[data-theme-resolved='dark'] .localboost-pricing-card.is-featured {
        background:
            linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(20, 184, 166, 0.08) 48%, rgba(15, 23, 42, 0.9)),
            rgba(15, 23, 42, 0.9) !important;
    }

    html[data-theme-resolved='dark'] .localboost-operation-row {
        background:
            linear-gradient(135deg, rgba(15, 23, 42, 0.94), rgba(11, 21, 38, 0.88)),
            rgba(15, 23, 42, 0.9) !important;
        border-color: rgba(96, 165, 250, 0.2) !important;
        box-shadow: 0 20px 64px -54px rgba(0, 0, 0, 0.9);
    }

    html[data-theme-resolved='dark'] .localboost-operation-row:hover {
        background:
            linear-gradient(135deg, rgba(37, 99, 235, 0.14), rgba(15, 23, 42, 0.94) 46%, rgba(20, 184, 166, 0.08)),
            rgba(15, 23, 42, 0.94) !important;
        border-color: rgba(96, 165, 250, 0.34) !important;
    }

    html[data-theme-resolved='dark'] .localboost-button-secondary {
        background: rgba(15, 23, 42, 0.7);
        color: #e8eef7;
        border-color: rgba(96, 165, 250, 0.26);
    }

    html[data-theme-resolved='dark'] .localboost-auth-primary {
        border-color: rgba(34, 211, 238, 0.28);
        background:
            linear-gradient(135deg, #0f766e 0%, #128a7f 52%, #b8da16 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.16),
            0 22px 58px -32px rgba(20, 184, 166, 0.82),
            0 0 0 1px rgba(96, 165, 250, 0.08);
    }

    html[data-theme-resolved='dark'] .localboost-auth-primary:hover {
        filter: saturate(1.06) brightness(1.04);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.18),
            0 28px 70px -34px rgba(37, 99, 235, 0.88),
            0 0 0 1px rgba(34, 211, 238, 0.16);
    }

    html[data-theme-resolved='dark'] .localboost-auth-social {
        border-color: rgba(96, 165, 250, 0.22);
        background:
            linear-gradient(180deg, rgba(15, 23, 42, 0.82), rgba(11, 21, 38, 0.72));
        color: #e8eef7;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.035);
    }

    html[data-theme-resolved='dark'] .localboost-auth-social:hover {
        border-color: rgba(34, 211, 238, 0.32);
        background:
            linear-gradient(180deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.78));
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.055),
            0 18px 46px -36px rgba(20, 184, 166, 0.6);
    }

    html[data-theme-resolved='dark'] header a:not(.localboost-nav-link):hover,
    html[data-theme-resolved='dark'] header button:hover {
        background-color: rgba(30, 41, 59, 0.78) !important;
        color: #f8fafc !important;
    }

    html[data-theme-resolved='dark'] header .localboost-nav-link:hover {
        background: transparent !important;
        color: #f8fafc !important;
        text-shadow: 0 0 22px rgba(96, 165, 250, 0.36);
    }

    html[data-theme-resolved='dark'] header .localboost-button-primary:hover {
        background: #0f766e !important;
        color: #fff !important;
    }

    html[data-theme-resolved='dark'] .localboost-glass-badge,
    html[data-theme-resolved='dark'] .localboost-case-metric,
    html[data-theme-resolved='dark'] .localboost-case-window {
        background: rgba(15, 23, 42, 0.78);
        border-color: rgba(96, 165, 250, 0.24);
    }

    html[data-theme-resolved='dark'] .localboost-premium::before {
        background:
            linear-gradient(135deg, rgba(37,99,235,0.18), transparent 36%),
            linear-gradient(315deg, rgba(20,184,166,0.16), transparent 38%),
            linear-gradient(180deg, rgba(15,23,42,0.94), rgba(11,21,38,0.86));
    }

    html[data-theme-resolved='dark'] .localboost-hero-stat,
    html[data-theme-resolved='dark'] .localboost-hero-chip {
        background: rgba(15, 23, 42, 0.78) !important;
        border-color: rgba(96, 165, 250, 0.28) !important;
    }

    html[data-theme-resolved='dark'] .localboost-visual,
    html[data-theme-resolved='dark'] .localboost-visual-bio,
    html[data-theme-resolved='dark'] .localboost-visual-retail,
    html[data-theme-resolved='dark'] .localboost-visual-qr,
    html[data-theme-resolved='dark'] .localboost-visual-rules,
    html[data-theme-resolved='dark'] .localboost-visual-analytics,
    html[data-theme-resolved='dark'] .localboost-visual-alerts,
    html[data-theme-resolved='dark'] .localboost-visual-domain,
    html[data-theme-resolved='dark'] .localboost-visual-utm,
    html[data-theme-resolved='dark'] .localboost-visual-team,
    html[data-theme-resolved='dark'] .localboost-visual-workspace {
        background:
            linear-gradient(135deg, rgba(37,99,235,0.28), rgba(20,184,166,0.18)),
            linear-gradient(180deg, #101d32, #0b1526) !important;
    }

    html[data-theme-resolved='dark'] .localboost-phone,
    html[data-theme-resolved='dark'] .localboost-chart-card,
    html[data-theme-resolved='dark'] .localboost-mini-window,
    html[data-theme-resolved='dark'] .localboost-domain-card,
    html[data-theme-resolved='dark'] .localboost-utm-card,
    html[data-theme-resolved='dark'] .localboost-team-card {
        background: rgba(15, 23, 42, 0.78) !important;
        border-color: rgba(96, 165, 250, 0.26) !important;
        box-shadow: 0 24px 70px -46px rgba(0, 0, 0, 0.82);
    }

    html[data-theme-resolved='dark'] .localboost-floating-qr,
    html[data-theme-resolved='dark'] .localboost-route-qr {
        background: rgba(15, 23, 42, 0.88) !important;
        border: 1px solid rgba(96, 165, 250, 0.28);
        box-shadow: 0 24px 70px -44px rgba(0, 0, 0, 0.86);
    }

    html[data-theme-resolved='dark'] .localboost-floating-qr span.is-on,
    html[data-theme-resolved='dark'] .localboost-route-qr span.is-on {
        background: #e2e8f0;
    }

    html[data-theme-resolved='dark'] .localboost-floating-qr span:not(.is-on),
    html[data-theme-resolved='dark'] .localboost-route-qr span:not(.is-on) {
        background: rgba(96, 165, 250, 0.08);
    }

    html[data-theme-resolved='dark'] .localboost-visual::after {
        background: linear-gradient(180deg, transparent 32%, rgba(2,6,23,0.16) 48%, rgba(2,6,23,0.76));
    }

    html[data-theme-resolved='dark'] .localboost-line {
        background: rgba(148, 163, 184, 0.34);
    }

    html[data-theme-resolved='dark'] .localboost-button-line {
        background: linear-gradient(90deg, rgba(37,99,235,0.42), rgba(20,184,166,0.46));
    }

    html[data-theme-resolved='dark'] .localboost-avatar,
    html[data-theme-resolved='dark'] .localboost-member-avatar {
        box-shadow: 0 0 0 8px rgba(96, 165, 250, 0.08);
    }

    html[data-theme-resolved='dark'] .localboost-utm-pill {
        background: rgba(245, 158, 11, 0.14);
        color: #fed7aa;
    }

    html[data-theme-resolved='dark'] .localboost-member-row {
        background: rgba(30, 41, 59, 0.7);
    }

    html[data-theme-resolved='dark'] .localboost-image-frame {
        background:
            linear-gradient(135deg, rgba(37,99,235,0.22), rgba(20,184,166,0.14)),
            #101d32;
    }

    html[data-theme-resolved='dark'] .localboost-image-frame::after {
        background:
            linear-gradient(180deg, rgba(2,6,23,0.02), rgba(2,6,23,0.62)),
            linear-gradient(90deg, rgba(37,99,235,0.18), transparent 42%);
    }

    html[data-theme-resolved='dark'] article[class*="from-blue-50"],
    html[data-theme-resolved='dark'] div[class*="from-blue-50"],
    html[data-theme-resolved='dark'] [class*="to-teal-50"] {
        background: linear-gradient(180deg, rgba(30,41,59,0.96), rgba(15,23,42,0.9)) !important;
    }

    html[data-theme-resolved='dark'] .bg-blue-50,
    html[data-theme-resolved='dark'] .bg-emerald-50,
    html[data-theme-resolved='dark'] .bg-amber-50,
    html[data-theme-resolved='dark'] .bg-violet-50 {
        background-color: rgba(30, 41, 59, 0.9) !important;
    }

    html[data-theme-resolved='dark'] ::selection {
        background: rgba(96, 165, 250, 0.32);
        color: #f8fafc;
    }

    html[data-theme-resolved='dark'] body {
        background: #07111f !important;
        color: #e8eef7 !important;
    }

    html[data-theme-resolved='dark'] header > div,
    html[data-theme-resolved='dark'] footer,
    html[data-theme-resolved='dark'] footer > div,
    html[data-theme-resolved='dark'] .localboost-cookie-banner {
        border-color: rgba(96, 165, 250, 0.22) !important;
        background:
            linear-gradient(180deg, rgba(15, 23, 42, 0.94), rgba(11, 21, 38, 0.9)) !important;
        color: #e8eef7 !important;
    }

    html[data-theme-resolved='dark'] header .bg-neutral-100,
    html[data-theme-resolved='dark'] header .bg-white,
    html[data-theme-resolved='dark'] header .bg-white\/74,
    html[data-theme-resolved='dark'] header .bg-white\/82,
    html[data-theme-resolved='dark'] header .bg-white\/94,
    html[data-theme-resolved='dark'] footer .bg-white,
    html[data-theme-resolved='dark'] footer .bg-\[\#fbfaf5\],
    html[data-theme-resolved='dark'] .localboost-cookie-banner .bg-white,
    html[data-theme-resolved='dark'] .localboost-cookie-banner [style*="255,255,255"] {
        background-color: rgba(15, 23, 42, 0.78) !important;
        background-image: none !important;
        border-color: rgba(96, 165, 250, 0.22) !important;
    }

    html[data-theme-resolved='dark'] .localboost-cookie-decline {
        color: #dbeafe !important;
        background: rgba(15, 23, 42, 0.78) !important;
        border-color: rgba(96, 165, 250, 0.36) !important;
    }

    html[data-theme-resolved='dark'] .localboost-cookie-decline:hover {
        color: #ffffff !important;
        background: rgba(30, 41, 59, 0.94) !important;
        border-color: rgba(94, 234, 212, 0.45) !important;
    }

    html[data-theme-resolved='dark'] .localboost-cookie-accept {
        color: #ffffff !important;
        background: #0f8f83 !important;
        border-color: #14b8a6 !important;
    }

    html[data-theme-resolved='dark'] header .text-neutral-950,
    html[data-theme-resolved='dark'] header .text-neutral-900,
    html[data-theme-resolved='dark'] footer .text-neutral-950,
    html[data-theme-resolved='dark'] footer .text-neutral-900 {
        color: #f8fafc !important;
    }

    html[data-theme-resolved='dark'] header .text-neutral-700,
    html[data-theme-resolved='dark'] header .text-neutral-600,
    html[data-theme-resolved='dark'] header .text-neutral-500,
    html[data-theme-resolved='dark'] footer .text-neutral-700,
    html[data-theme-resolved='dark'] footer .text-neutral-600,
    html[data-theme-resolved='dark'] footer .text-neutral-500 {
        color: #cbd5e1 !important;
    }

    html[data-theme-resolved='dark'] header .text-neutral-400,
    html[data-theme-resolved='dark'] footer .text-neutral-400,
    html[data-theme-resolved='dark'] footer .text-neutral-100 {
        color: #64748b !important;
    }

    html[data-theme-resolved='dark'] header a:hover,
    html[data-theme-resolved='dark'] footer a:hover {
        color: #f8fafc !important;
    }

    html[data-theme-resolved='dark'] .lb-page,
    html[data-theme-resolved='dark'] .lb-auth-page {
        --lb-ink: #e8eef7;
        --lb-muted: #9aa8ba;
        --lb-paper: #07111f;
        --lb-soft: #101d32;
        --lb-line: #25364d;
        --lb-lime: #b8da16;
        --lb-red: #ff5f5f;
        --lb-moss: #9fc22e;
        background:
            linear-gradient(rgba(96, 165, 250, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(96, 165, 250, 0.05) 1px, transparent 1px),
            radial-gradient(circle at 18% 10%, rgba(20, 163, 153, 0.15), transparent 28rem),
            radial-gradient(circle at 82% 22%, rgba(184, 218, 22, 0.1), transparent 30rem),
            linear-gradient(180deg, #07111f 0%, #0b1526 52%, #07111f 100%) !important;
        background-size: 36px 36px, 36px 36px, auto, auto, auto !important;
        color: var(--lb-ink) !important;
    }

    html[data-theme-resolved='dark'] .lb-card,
    html[data-theme-resolved='dark'] .lb-window,
    html[data-theme-resolved='dark'] .lb-blog-visual,
    html[data-theme-resolved='dark'] .lb-blog-cover,
    html[data-theme-resolved='dark'] .lb-blog-topic-panel,
    html[data-theme-resolved='dark'] .lb-limit-panel,
    html[data-theme-resolved='dark'] .lb-plan-toggle,
    html[data-theme-resolved='dark'] .lb-page-flow,
    html[data-theme-resolved='dark'] .lb-page-card,
    html[data-theme-resolved='dark'] .lb-proof-node,
    html[data-theme-resolved='dark'] .lb-mini-page,
    html[data-theme-resolved='dark'] .lb-final-preview,
    html[data-theme-resolved='dark'] .lb-auth-page form,
    html[data-theme-resolved='dark'] .lb-auth-page [class*="bg-white"] {
        border-color: rgba(96, 165, 250, 0.22) !important;
        background:
            linear-gradient(180deg, rgba(15, 23, 42, 0.92), rgba(11, 21, 38, 0.86)) !important;
        color: #e8eef7 !important;
        box-shadow: 0 28px 80px -58px rgba(0, 0, 0, 0.82) !important;
    }

    html[data-theme-resolved='dark'] .lb-pricing-hero::before,
    html[data-theme-resolved='dark'] .lb-blog-hero::before,
    html[data-theme-resolved='dark'] .lb-glow::before {
        opacity: 0.45 !important;
        background: radial-gradient(circle, rgba(20, 163, 153, 0.18), transparent 70%) !important;
    }

    html[data-theme-resolved='dark'] .lb-pill,
    html[data-theme-resolved='dark'] .lb-button-soft,
    html[data-theme-resolved='dark'] .lb-feature-value,
    html[data-theme-resolved='dark'] .lb-page-link,
    html[data-theme-resolved='dark'] [style*="color:#506807"],
    html[data-theme-resolved='dark'] [style*="color: #506807"],
    html[data-theme-resolved='dark'] [style*="color:#ff5f5f"],
    html[data-theme-resolved='dark'] [style*="color: #ff5f5f"] {
        border-color: rgba(184, 218, 22, 0.22) !important;
        background: rgba(184, 218, 22, 0.12) !important;
        color: #d9f75d !important;
    }

    html[data-theme-resolved='dark'] .lb-page-link.is-active {
        border-color: #ff5f5f !important;
        background: #0f766e !important;
        color: #ffffff !important;
    }

    html[data-theme-resolved='dark'] input,
    html[data-theme-resolved='dark'] textarea,
    html[data-theme-resolved='dark'] select {
        border-color: rgba(96, 165, 250, 0.24) !important;
        background: rgba(15, 23, 42, 0.82) !important;
        color: #e8eef7 !important;
    }

    html[data-theme-resolved='dark'] input::placeholder,
    html[data-theme-resolved='dark'] textarea::placeholder {
        color: #64748b !important;
    }

    html[data-theme-resolved='dark'] .lb-page [class*="bg-white"],
    html[data-theme-resolved='dark'] .lb-auth-page [class*="bg-white"],
    html[data-theme-resolved='dark'] .lb-page [style*="255,255,255"],
    html[data-theme-resolved='dark'] .lb-page [style*="255, 255, 255"],
    html[data-theme-resolved='dark'] .lb-page [style*="#fff"],
    html[data-theme-resolved='dark'] .lb-page [style*="white"],
    html[data-theme-resolved='dark'] .lb-auth-page [style*="255,255,255"],
    html[data-theme-resolved='dark'] .lb-auth-page [style*="255, 255, 255"],
    html[data-theme-resolved='dark'] .lb-auth-page [style*="#fff"],
    html[data-theme-resolved='dark'] .lb-auth-page [style*="white"] {
        background-color: rgba(15, 23, 42, 0.78) !important;
        background-image: none !important;
        border-color: rgba(96, 165, 250, 0.22) !important;
    }

    .theme-logo-light {
        display: none !important;
    }

    .theme-logo-dark {
        display: inline-block !important;
    }

    html[data-theme-resolved='dark'] .theme-logo-dark {
        display: none !important;
    }

    html[data-theme-resolved='dark'] .theme-logo-light {
        display: inline-block !important;
    }

    html[data-theme-resolved='dark'] .lb-page [style*="#fbfaf5"],
    html[data-theme-resolved='dark'] .lb-page [style*="#fbfaf5"],
    html[data-theme-resolved='dark'] .lb-page [style*="#fffefb"],
    html[data-theme-resolved='dark'] .lb-auth-page [style*="#fbfaf5"],
    html[data-theme-resolved='dark'] .lb-auth-page [style*="#fbfaf5"],
    html[data-theme-resolved='dark'] .lb-auth-page [style*="#fffefb"] {
        background: rgba(15, 23, 42, 0.78) !important;
        border-color: rgba(96, 165, 250, 0.22) !important;
    }

    html[data-theme-resolved='dark'] .lb-page .text-neutral-950,
    html[data-theme-resolved='dark'] .lb-page .text-neutral-900,
    html[data-theme-resolved='dark'] .lb-page .text-slate-950,
    html[data-theme-resolved='dark'] .lb-page .text-slate-900,
    html[data-theme-resolved='dark'] .lb-auth-page .text-neutral-950,
    html[data-theme-resolved='dark'] .lb-auth-page .text-neutral-900,
    html[data-theme-resolved='dark'] .lb-auth-page .text-slate-950,
    html[data-theme-resolved='dark'] .lb-auth-page .text-slate-900 {
        color: #f8fafc !important;
    }

    html[data-theme-resolved='dark'] .lb-page .text-neutral-700,
    html[data-theme-resolved='dark'] .lb-page .text-neutral-600,
    html[data-theme-resolved='dark'] .lb-page .text-slate-700,
    html[data-theme-resolved='dark'] .lb-page .text-slate-600,
    html[data-theme-resolved='dark'] .lb-auth-page .text-neutral-700,
    html[data-theme-resolved='dark'] .lb-auth-page .text-neutral-600,
    html[data-theme-resolved='dark'] .lb-auth-page .text-slate-700,
    html[data-theme-resolved='dark'] .lb-auth-page .text-slate-600 {
        color: #cbd5e1 !important;
    }

    html[data-theme-resolved='dark'] .lb-page .text-neutral-500,
    html[data-theme-resolved='dark'] .lb-page .text-neutral-400,
    html[data-theme-resolved='dark'] .lb-page .text-slate-500,
    html[data-theme-resolved='dark'] .lb-page .text-slate-400,
    html[data-theme-resolved='dark'] .lb-auth-page .text-neutral-500,
    html[data-theme-resolved='dark'] .lb-auth-page .text-neutral-400,
    html[data-theme-resolved='dark'] .lb-auth-page .text-slate-500,
    html[data-theme-resolved='dark'] .lb-auth-page .text-slate-400,
    html[data-theme-resolved='dark'] .lb-copy {
        color: #9aa8ba !important;
    }

    html[data-theme-resolved='dark'] .lb-limit-meter::after,
    html[data-theme-resolved='dark'] .lb-page-cta::after {
        background: linear-gradient(90deg, transparent, rgba(94, 234, 212, 0.26), transparent) !important;
    }

    html[data-theme-resolved='dark'] .lb-page-flow-step:not(:last-child)::after,
    html[data-theme-resolved='dark'] .lb-limit-row,
    html[data-theme-resolved='dark'] .lb-pricing-grid > :not([hidden]) + :not([hidden]) {
        border-color: rgba(96, 165, 250, 0.14) !important;
    }

    html[data-theme-resolved='dark'] .lb-page-link.is-disabled {
        background: rgba(15, 23, 42, 0.42) !important;
        color: #64748b !important;
    }

    .lb-rich-content {
        color: #475569;
        font-size: 1rem;
        line-height: 1.8;
        max-width: none;
    }

    .lb-rich-content p {
        margin: 0 0 1.15em;
    }

    .lb-rich-content p:last-child {
        margin-bottom: 0;
    }

    .lb-rich-content h2 {
        margin: 2.25rem 0 0.85rem;
        font-size: clamp(1.35rem, 2.4vw, 1.75rem);
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: -0.03em;
        color: #0f172a;
    }

    .lb-rich-content h2:first-child {
        margin-top: 0;
    }

    .lb-rich-content h3 {
        margin: 1.75rem 0 0.65rem;
        font-size: clamp(1.15rem, 2vw, 1.35rem);
        font-weight: 800;
        line-height: 1.3;
        letter-spacing: -0.025em;
        color: #0f172a;
    }

    .lb-rich-content ul,
    .lb-rich-content ol {
        margin: 0 0 1.25em;
        padding-left: 1.35em;
    }

    .lb-rich-content ul {
        list-style: disc;
    }

    .lb-rich-content ol {
        list-style: decimal;
    }

    .lb-rich-content li {
        margin: 0.45em 0;
        padding-left: 0.2em;
    }

    .lb-rich-content li::marker {
        color: var(--lb-red, #0f766e);
    }

    .lb-rich-content a {
        color: var(--lb-red, #0f766e);
        font-weight: 700;
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .lb-rich-content a:hover {
        opacity: 0.85;
    }

    .lb-rich-content strong {
        font-weight: 800;
        color: #1e293b;
    }

    .lb-rich-content em {
        font-style: italic;
    }

    .lb-rich-content blockquote {
        margin: 1.5em 0;
        border-left: 3px solid var(--lb-red, #0f766e);
        padding: 0.25em 0 0.25em 1em;
        color: #64748b;
        font-style: italic;
    }

    .lb-rich-content img {
        display: block;
        max-width: 100%;
        height: auto;
        margin: 1.5em 0;
        border-radius: 1rem;
    }

    html[data-theme-resolved='dark'] .lb-rich-content,
    html[data-theme-resolved='dark'] .lb-rich-content p,
    html[data-theme-resolved='dark'] .lb-rich-content li,
    html[data-theme-resolved='dark'] .lb-rich-content blockquote {
        color: #cbd5e1 !important;
    }

    html[data-theme-resolved='dark'] .lb-rich-content h2,
    html[data-theme-resolved='dark'] .lb-rich-content h3,
    html[data-theme-resolved='dark'] .lb-rich-content strong {
        color: #f8fafc !important;
    }

    html[data-theme-resolved='dark'] .lb-rich-content a {
        color: #ffb347 !important;
    }

    html[data-theme-resolved='dark'] .prose,
    html[data-theme-resolved='dark'] .prose :where(p, li, blockquote, td, th):not(:where([class~="not-prose"], [class~="not-prose"] *)) {
        color: #cbd5e1 !important;
    }

    html[data-theme-resolved='dark'] .prose :where(h1, h2, h3, h4, strong):not(:where([class~="not-prose"], [class~="not-prose"] *)) {
        color: #f8fafc !important;
    }

    html[data-theme-resolved='dark'] .prose :where(a):not(:where([class~="not-prose"], [class~="not-prose"] *)) {
        color: #ffb347 !important;
    }
</style>

@if (filled(theme_setting('custom_css', 'guest')))
    <style>{!! theme_setting('custom_css', 'guest') !!}</style>
@endif

<script>
    (() => {
        const storageKey = 'localboost-theme-mode';
        const supportsDark = @js((string) theme_setting('supports_dark_mode', 'guest', '1')) !== '0';
        const allowToggle = @js((string) theme_setting('allow_user_appearance_toggle', 'guest', '1')) !== '0';
        const defaultMode = @js(theme_setting('default_appearance', 'guest', 'light'));
        const normalize = (mode) => supportsDark && mode === 'dark' ? 'dark' : 'light';
        const stored = () => normalize(localStorage.getItem(storageKey) || defaultMode || 'light');
        const apply = (mode, notify = true) => {
            const nextMode = normalize(mode);
            const resolved = nextMode;
            document.documentElement.dataset.themeMode = nextMode;
            document.documentElement.dataset.themeResolved = resolved;
            document.documentElement.classList.toggle('dark', resolved === 'dark');

            const state = { mode: nextMode, resolved };

            if (notify) {
                window.dispatchEvent(new CustomEvent('theme-mode-changed', { detail: state }));
            }

            return state;
        };
        const restore = (notify = true) => apply(stored(), notify);
        const storedMode = stored();
        window.themeMode = {
            getMode: () => document.documentElement.dataset.themeMode || stored(),
            getResolved: () => document.documentElement.dataset.themeResolved || stored(),
            supportsDark: () => supportsDark,
            allowToggle: () => allowToggle,
            setMode: (mode) => {
                const nextMode = normalize(mode);
                localStorage.setItem(storageKey, nextMode);
                return apply(nextMode);
            },
            toggle: () => {
                const nextMode = (document.documentElement.dataset.themeResolved || storedMode) === 'dark' ? 'light' : 'dark';
                localStorage.setItem(storageKey, nextMode);
                return apply(nextMode);
            },
            restore,
            apply,
        };
        apply(storedMode, false);
        document.addEventListener('livewire:navigating', () => restore(false));
        document.addEventListener('livewire:navigated', () => requestAnimationFrame(() => restore(true)));
        window.addEventListener('pageshow', () => restore(false));
    })();
</script>
<script>
    (() => {
        const initReveal = () => {
            const nodes = document.querySelectorAll('[data-reveal]');

            if (!nodes.length) {
                return;
            }

            if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                nodes.forEach((node) => node.classList.add('localboost-reveal', 'is-visible'));
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            }, { threshold: 0.14 });

            nodes.forEach((node, index) => {
                node.classList.add('localboost-reveal');
                node.style.setProperty('--localboost-reveal-delay', `${Math.min((index % 6) * 70, 280)}ms`);
                observer.observe(node);
            });
        };

        document.addEventListener('DOMContentLoaded', initReveal);
        document.addEventListener('livewire:navigated', initReveal);
    })();
</script>
@if (filled(theme_setting('custom_js', 'guest')))
    <script>{!! theme_setting('custom_js', 'guest') !!}</script>
@endif
{!! theme_vite('guest', ['assets/js/app.js', 'resources/themes/shared/js/highcharts.js', 'resources/themes/shared/js/image-editor.js']) !!}
