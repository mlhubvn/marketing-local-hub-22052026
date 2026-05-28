<link rel="preconnect" href="https://fonts.bunny.net">
<link href="{{ \Modules\CustomFont\Support\VietnameseFont::BUNNY_STYLESHEET }}" rel="stylesheet">
<style id="customfont-vietnamese">
    :root {
        --font: {!! \Modules\CustomFont\Support\VietnameseFont::STACK !!} !important;
        --font-sans: var(--font) !important;
        --theme-font-sans: var(--font) !important;
    }

    body,
    .form-card,
    .form-card * {
        font-family: {!! \Modules\CustomFont\Support\VietnameseFont::STACK !!} !important;
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
    }
</style>
