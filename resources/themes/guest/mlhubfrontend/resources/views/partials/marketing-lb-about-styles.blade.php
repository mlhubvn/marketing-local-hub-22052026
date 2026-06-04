<style>
    .lb-sales {
        --lb-ink: #15201b;
        --lb-muted: #63736b;
        --lb-paper: #f7faf6;
        --lb-line: #dfe9df;
        --lb-lime: #ffb347;
        --lb-red: #ff5f5f;
        --lb-text-xs: 0.75rem;
        --lb-text-sm: 0.875rem;
        --lb-text-base: 1rem;
        --lb-text-lg: 1.125rem;
        --lb-text-xl: 1.25rem;
        --lb-text-h2: clamp(1.625rem, 1.6vw + 1.1rem, 2.375rem);
        background:
            radial-gradient(circle at 78% 9%, rgba(184, 218, 22, .28), transparent 26rem),
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

    .lb-body {
        font-size: var(--lb-text-base);
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
        padding-block: clamp(3rem, 6vw, 5rem);
    }

    .lb-about-panel {
        scroll-margin-top: 7rem;
    }

    html[data-theme-resolved='dark'] .lb-sales {
        --lb-ink: #e8eef7;
        --lb-muted: #94a3b8;
        --lb-paper: #07111f;
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

    html[data-theme-resolved='dark'] .lb-card {
        border-color: rgba(96, 165, 250, .22) !important;
        background: linear-gradient(180deg, rgba(15, 23, 42, .92), rgba(11, 21, 38, .86)) !important;
    }

    html[data-theme-resolved='dark'] .lb-pill {
        border-color: rgba(184, 218, 22, .24) !important;
        background: rgba(184, 218, 22, .14) !important;
        color: #d9f75d !important;
    }
</style>
