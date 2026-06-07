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

    .lb-about-fullpage {
        --lb-about-nav: 5rem;
        width: 100%;
    }

    html:has(.lb-about-fullpage) {
        scroll-snap-type: y proximity;
    }

    .lb-wrap {
        width: min(1160px, calc(100% - clamp(1.25rem, 4vw, 2.5rem)));
        margin-inline: auto;
    }

    .lb-about-screen {
        position: relative;
        isolation: isolate;
        width: 100%;
        min-height: calc(100vh - var(--lb-about-nav));
        min-height: calc(100dvh - var(--lb-about-nav));
        display: flex;
        align-items: center;
        justify-content: center;
        padding: clamp(1rem, 3vw, 2rem) clamp(0.75rem, 2.5vw, 1.25rem);
        scroll-snap-align: start;
        scroll-snap-stop: normal;
        box-sizing: border-box;
    }

    .lb-about-screen--stack {
        align-items: flex-start;
        padding-top: clamp(1.25rem, 4vh, 2rem);
        padding-bottom: clamp(1.5rem, 5vh, 2.5rem);
    }

    .lb-about-screen-inner {
        width: 100%;
        max-width: 100%;
    }

    .lb-about-hero {
        overflow: hidden;
    }

    .lb-about-hero-bg {
        position: absolute;
        inset: 0;
        z-index: 0;
        background:
            linear-gradient(125deg, rgba(255, 95, 95, .09) 0%, transparent 42%),
            linear-gradient(305deg, rgba(184, 218, 22, .16) 0%, transparent 48%),
            radial-gradient(circle at 50% 118%, rgba(255, 179, 71, .18), transparent 52%);
        pointer-events: none;
    }

    .lb-about-hero-grid {
        position: relative;
        z-index: 1;
        display: grid;
        gap: clamp(1.5rem, 4vw, 2.5rem);
        align-items: center;
    }

    @media (min-width: 1024px) {
        .lb-about-hero-grid {
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
        }
    }

    .lb-about-hero-cta i {
        animation: lb-about-bounce 2.2s ease-in-out infinite;
    }

    .lb-about-orbit-stage {
        display: grid;
        place-items: center;
        min-height: clamp(14rem, 38vw, 20rem);
    }

    .lb-about-orbit {
        position: relative;
        width: clamp(14rem, 42vw, 18rem);
        aspect-ratio: 1;
    }

    .lb-about-orbit-ring {
        position: absolute;
        inset: 8%;
        border: 1px dashed color-mix(in srgb, var(--lb-red) 34%, var(--lb-line));
        border-radius: 999px;
        animation: lb-about-orbit-spin 28s linear infinite;
    }

    .lb-about-orbit-ring--outer {
        inset: 0;
        border-color: color-mix(in srgb, var(--lb-lime) 42%, var(--lb-line));
        animation-direction: reverse;
        animation-duration: 36s;
    }

    .lb-about-orbit-core {
        position: absolute;
        inset: 32%;
        display: grid;
        place-items: center;
        border-radius: 1.35rem;
        border: 1px solid var(--lb-line);
        background: rgba(255, 255, 252, .94);
        box-shadow: 0 24px 70px -48px rgba(16, 37, 31, .55);
        padding: 0.85rem;
    }

    .lb-about-orbit-node {
        --lb-orbit-angle: 0deg;
        position: absolute;
        top: 50%;
        left: 50%;
        width: 2.65rem;
        height: 2.65rem;
        margin: -1.325rem;
        display: grid;
        place-items: center;
        border-radius: 0.9rem;
        border: 1px solid var(--lb-line);
        background: #fff;
        color: #ff5f5f;
        box-shadow: 0 16px 40px -30px rgba(16, 37, 31, .55);
        transform: rotate(var(--lb-orbit-angle)) translateY(calc(-1 * clamp(6.8rem, 20vw, 8.6rem))) rotate(calc(-1 * var(--lb-orbit-angle)));
    }

    .lb-about-chapters {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: repeat(6, minmax(8.5rem, 1fr));
        gap: 0.65rem;
        margin-top: clamp(1.25rem, 3vh, 2rem);
        overflow-x: auto;
        overscroll-behavior-x: contain;
        scroll-snap-type: x proximity;
        padding-bottom: 0.25rem;
        -webkit-overflow-scrolling: touch;
    }

    @media (max-width: 1023px) {
        .lb-about-chapters {
            grid-template-columns: none;
            grid-auto-flow: column;
            grid-auto-columns: minmax(8.75rem, 10.5rem);
        }
    }

    .lb-about-chapter {
        scroll-snap-align: start;
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        border: 1px solid var(--lb-line);
        border-radius: 1rem;
        background: rgba(255, 255, 252, .88);
        padding: 0.75rem;
        text-decoration: none;
        color: inherit;
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    }

    .lb-about-chapter:hover {
        transform: translateY(-2px);
        border-color: color-mix(in srgb, var(--lb-red) 28%, var(--lb-line));
        box-shadow: 0 20px 50px -38px rgba(16, 37, 31, .5);
    }

    .lb-about-chapter-no {
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.14em;
        color: var(--lb-muted);
    }

    .lb-about-chapter-icon {
        display: inline-flex;
        height: 2rem;
        width: 2rem;
        align-items: center;
        justify-content: center;
        border-radius: 0.65rem;
        background: color-mix(in srgb, var(--lb-lime) 24%, #fff);
        color: #ff5f5f;
        font-size: 0.9rem;
    }

    .lb-about-chapter-label {
        font-size: 0.72rem;
        font-weight: 800;
        line-height: 1.35;
        color: var(--lb-ink);
    }

    .lb-about-flow-panel {
        box-shadow: 0 20px 60px -44px rgba(16, 37, 31, .45);
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
        min-height: 0;
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
        min-height: 0;
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

    .lb-reveal {
        animation: lb-about-rise .72s cubic-bezier(.16, 1, .3, 1) both;
        animation-delay: var(--lb-delay, 0ms);
    }

    .lb-scroll {
        opacity: 0;
        transform: translateY(34px) scale(.985);
        transition:
            opacity .72s cubic-bezier(.16, 1, .3, 1),
            transform .72s cubic-bezier(.16, 1, .3, 1);
        transition-delay: var(--lb-stagger, 0ms);
    }

    .lb-scroll.is-visible {
        opacity: 1;
        transform: translateY(0) scale(1);
    }

    @keyframes lb-about-rise {
        from {
            opacity: 0;
            transform: translateY(24px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes lb-about-float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }

    @keyframes lb-about-bar-breathe {
        0%, 100% { transform: scaleY(.72); }
        50% { transform: scaleY(1); }
    }

    @keyframes lb-about-orbit-spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @keyframes lb-about-bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(4px); }
    }

    @media (max-width: 767px) {
        .lb-about-screen {
            align-items: flex-start;
            padding-top: clamp(1rem, 3vh, 1.5rem);
            padding-bottom: clamp(1.25rem, 4vh, 2rem);
        }

        .lb-about-screen:not(.lb-about-hero) .lb-heading {
            font-size: clamp(1.45rem, 5.5vw, 1.85rem);
        }

        .lb-about-hero .lb-hero-title {
            font-size: clamp(1.65rem, 7vw, 2.15rem);
        }
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

    html[data-theme-resolved='dark'] .lb-about-hero-bg {
        background:
            linear-gradient(125deg, rgba(255, 95, 95, .12) 0%, transparent 42%),
            linear-gradient(305deg, rgba(184, 218, 22, .1) 0%, transparent 48%),
            radial-gradient(circle at 50% 118%, rgba(255, 179, 71, .08), transparent 52%) !important;
    }

    html[data-theme-resolved='dark'] .lb-about-orbit-core,
    html[data-theme-resolved='dark'] .lb-about-orbit-node,
    html[data-theme-resolved='dark'] .lb-about-chapter {
        border-color: rgba(96, 165, 250, .22) !important;
        background: linear-gradient(180deg, rgba(15, 23, 42, .92), rgba(11, 21, 38, .86)) !important;
    }

    html[data-theme-resolved='dark'] .lb-about-flow-panel {
        background: linear-gradient(180deg, rgba(15, 23, 42, .88), rgba(11, 21, 38, .82)) !important;
        border-color: rgba(96, 165, 250, .22) !important;
    }

    @media (prefers-reduced-motion: reduce) {
        html:has(.lb-about-fullpage) {
            scroll-snap-type: none;
        }

        .lb-reveal,
        .lb-float,
        .lb-bar,
        .lb-about-orbit-ring,
        .lb-about-orbit-node,
        .lb-about-hero-cta i {
            animation: none !important;
        }

        .lb-about-orbit-node {
            transform: rotate(var(--lb-orbit-angle)) translateY(calc(-1 * clamp(6.8rem, 20vw, 8.6rem))) rotate(calc(-1 * var(--lb-orbit-angle))) !important;
        }

        .lb-scroll {
            opacity: 1;
            transform: none;
            transition: none;
        }
    }
</style>
