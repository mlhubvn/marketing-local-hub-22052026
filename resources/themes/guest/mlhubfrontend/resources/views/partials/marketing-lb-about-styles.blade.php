<style>
    .lb-sales {
        --lb-ink: #15201b;
        --lb-muted: #63736b;
        --lb-paper: #fbfaf5;
        --lb-soft: #edf5ef;
        --lb-line: #dfe9df;
        --lb-lime: #ffb347;
        --lb-red: #ff5f5f;
        --lb-text-xs: 0.75rem;
        --lb-text-sm: 0.875rem;
        --lb-text-base: 1rem;
        --lb-text-lg: 1.125rem;
        --lb-text-xl: 1.25rem;
        --lb-text-h1: clamp(1.875rem, 2.2vw + 1rem, 2.875rem);
        --lb-text-h2: clamp(1.625rem, 1.6vw + 1.1rem, 2.375rem);
        background:
            radial-gradient(circle at 78% 9%, rgba(225, 235, 22, .22), transparent 26rem),
            radial-gradient(circle at 7% 18%, rgba(255, 95, 95, .13), transparent 24rem),
            var(--lb-paper);
        color: var(--lb-ink);
        font-family: var(--theme-font-sans);
        font-size: var(--lb-text-base);
        line-height: 1.65;
    }

    .lb-wrap {
        width: min(1160px, calc(100% - 40px));
        margin-inline: auto;
    }

    .lb-serif {
        letter-spacing: -0.03em;
    }

    .lb-heading {
        font-size: var(--lb-text-h2);
        font-weight: 800;
        line-height: 1.12;
    }

    .lb-hero-title {
        font-size: var(--lb-text-h1);
        font-weight: 800;
        line-height: 1.2;
        max-width: min(100%, 42rem);
    }

    .lb-sales .lb-pill {
        font-size: var(--lb-text-xs);
        font-weight: 700 !important;
        letter-spacing: 0.06em;
        border: 1px solid rgba(255, 95, 95, .18);
        background: color-mix(in srgb, var(--lb-lime) 26%, #fff);
        color: #ff5f5f;
    }

    .lb-card {
        border: 1px solid var(--lb-line);
        background: rgba(255, 255, 252, .9);
        box-shadow: 0 30px 90px -65px rgba(16, 37, 31, .46);
    }

    .lb-window {
        border: 1px solid var(--lb-line);
        background: #fffefb;
        box-shadow: 0 24px 80px -58px rgba(16, 37, 31, .52);
    }

    .lb-body {
        font-size: var(--lb-text-base);
        line-height: 1.65;
    }

    .lb-lead {
        font-size: var(--lb-text-lg);
        line-height: 1.65;
    }

    .lb-caption {
        font-size: var(--lb-text-sm);
        line-height: 1.55;
    }

    .lb-card-title {
        font-size: var(--lb-text-xl);
        font-weight: 800;
        line-height: 1.3;
    }

    .lb-section {
        padding-block: clamp(3.5rem, 7vw, 6rem);
    }

    .lb-about-panel {
        scroll-margin-top: 7rem;
    }

    .lb-dot {
        display: inline-block;
        width: 9px;
        height: 9px;
        border-radius: 999px;
    }

    .lb-hover {
        transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
    }

    .lb-hover:hover {
        transform: translateY(-3px);
        border-color: color-mix(in srgb, var(--lb-red) 30%, var(--lb-line));
        box-shadow: 0 34px 90px -62px rgba(16, 37, 31, .6);
    }

    .lb-glow {
        position: relative;
        isolation: isolate;
    }

    .lb-glow::before {
        content: "";
        position: absolute;
        inset: -1.5rem;
        z-index: -1;
        border-radius: 2rem;
        background: radial-gradient(circle at 50% 0%, rgba(184, 218, 22, .34), transparent 55%);
        filter: blur(18px);
    }

    .lb-float {
        animation: lb-about-float 6s ease-in-out infinite;
        animation-delay: var(--lb-delay, 0ms);
    }

    .lb-feature-hero {
        background:
            linear-gradient(145deg, rgba(255, 95, 95, .08), rgba(225, 235, 22, .22)),
            #fffefb;
    }

    .lb-feature-row {
        position: relative;
        overflow: hidden;
        min-height: 8.75rem;
    }

    .lb-feature-row::after {
        content: "";
        position: absolute;
        inset: auto -2rem -3.5rem auto;
        width: 7rem;
        height: 7rem;
        border-radius: 999px;
        background: color-mix(in srgb, var(--lb-lime) 18%, #fff);
    }

    .lb-feature-row:hover .lb-feature-icon {
        transform: translateY(-3px) scale(1.04);
    }

    .lb-feature-icon {
        transition: transform .24s ease;
    }

    .lb-step-card {
        position: relative;
        overflow: hidden;
        min-height: 11rem;
    }

    .lb-step-card::before {
        content: "";
        position: absolute;
        inset-inline: 1.5rem;
        top: 0;
        height: 4px;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--lb-red), var(--lb-lime));
        transform: scaleX(.18);
        transform-origin: left;
        transition: transform .42s cubic-bezier(.16, 1, .3, 1);
    }

    .lb-step-card:hover::before {
        transform: scaleX(1);
    }

    .lb-step-card::after {
        content: "";
        position: absolute;
        right: -3rem;
        top: -3rem;
        width: 8rem;
        height: 8rem;
        border-radius: 999px;
        background: color-mix(in srgb, var(--lb-lime) 18%, #fff);
    }

    .lb-workflow-band {
        position: relative;
        overflow: hidden;
        padding-block: clamp(3.5rem, 7vw, 6rem);
        background:
            radial-gradient(circle at 18% 28%, rgba(255, 95, 95, .12), transparent 24rem),
            radial-gradient(circle at 76% 42%, rgba(184, 218, 22, .2), transparent 22rem);
    }

    .lb-proof-panel {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at 12% 18%, rgba(255, 95, 95, .13), transparent 18rem),
            radial-gradient(circle at 92% 88%, rgba(225, 235, 22, .22), transparent 16rem),
            rgba(255, 255, 252, .92);
    }

    .lb-proof-visual {
        background: linear-gradient(145deg, rgba(255, 95, 95, .08), rgba(225, 235, 22, .22));
    }

    .lb-proof-node {
        box-shadow: 0 18px 48px -34px rgba(16, 37, 31, .62);
    }

    .lb-final-cta {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at 16% 12%, rgba(255, 95, 95, .14), transparent 18rem),
            radial-gradient(circle at 84% 70%, rgba(184, 218, 22, .26), transparent 18rem),
            rgba(255, 255, 252, .94);
    }

    .lb-bar {
        transform-origin: bottom;
        transform: scaleY(.72);
        animation: lb-about-bar-breathe 2.6s ease-in-out infinite;
        animation-delay: var(--lb-bar-delay, 0ms);
    }

    .lb-section-index {
        font-size: var(--lb-text-xs);
        font-weight: 800;
        letter-spacing: 0.16em;
        color: var(--lb-muted);
    }

    @keyframes lb-about-float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }

    @keyframes lb-about-bar-breathe {
        0%, 100% { transform: scaleY(.72); }
        50% { transform: scaleY(1); }
    }

    html[data-theme-resolved='dark'] .lb-sales {
        --lb-ink: #e8eef7;
        --lb-muted: #94a3b8;
        --lb-paper: #07111f;
        --lb-soft: #0f172a;
        --lb-line: #25364d;
        background:
            linear-gradient(rgba(96, 165, 250, .05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(96, 165, 250, .05) 1px, transparent 1px),
            radial-gradient(circle at 78% 9%, rgba(184, 218, 22, .12), transparent 26rem),
            radial-gradient(circle at 7% 18%, rgba(20, 163, 153, .18), transparent 24rem),
            linear-gradient(180deg, #07111f 0%, #0b1526 48%, #07111f 100%) !important;
        background-size: 36px 36px, 36px 36px, auto, auto, auto !important;
        color: var(--lb-ink) !important;
    }

    html[data-theme-resolved='dark'] .lb-card,
    html[data-theme-resolved='dark'] .lb-window {
        border-color: rgba(96, 165, 250, .22) !important;
        background: linear-gradient(180deg, rgba(15, 23, 42, .92), rgba(11, 21, 38, .86)) !important;
    }

    html[data-theme-resolved='dark'] .lb-pill {
        border-color: rgba(184, 218, 22, .24) !important;
        background: rgba(184, 218, 22, .14) !important;
        color: #d9f75d !important;
    }

    html[data-theme-resolved='dark'] .lb-feature-row::after,
    html[data-theme-resolved='dark'] .lb-step-card::after {
        background: color-mix(in srgb, var(--lb-lime) 12%, #0f172a) !important;
    }

    html[data-theme-resolved='dark'] .lb-feature-hero,
    html[data-theme-resolved='dark'] .lb-proof-panel,
    html[data-theme-resolved='dark'] .lb-final-cta {
        background:
            linear-gradient(145deg, rgba(255, 95, 95, .1), rgba(184, 218, 22, .08)),
            linear-gradient(180deg, rgba(15, 23, 42, .92), rgba(11, 21, 38, .86)) !important;
    }
</style>
