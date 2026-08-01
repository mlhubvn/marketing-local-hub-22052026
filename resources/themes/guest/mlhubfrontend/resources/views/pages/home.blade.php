@extends(theme_view('layouts.marketing', 'guest'))

@section('content')
    @php
        $demoHref = auth()->check() ? route('portal.dashboard') : route('login');
        $featuresHref = '#features';
        $niches = [__('Restaurants'), __('Salons'), __('Clinics'), __('Dentists'), __('Gyms'), __('Local shops'), __('Spas'), __('Agencies')];
        $coreFeatures = [
            ['fa-star', __('Review Booster'), __('Route happy customers to Google Maps or Facebook to boost local SEO rankings.')],
            ['fa-calendar-check', __('Booking Pages'), __('Accept table and appointment requests directly from landing pages and auto-save details to convert viewers into customers.')],
            ['fa-ticket', __('Coupons & Offers'), __('Attract guests with vouchers, drive purchases, and automatically collect new customer data.')],
            ['fa-address-card', __('Lead capture'), __('Capture contacts from campaigns and landing pages to build your customer list.')],
            ['fa-comments', __('Feedback summary'), __('Aggregate guest opinions and satisfaction signals in one view for faster follow-up.')],
            ['fa-sparkles', __('AI campaign assistant'), __('No copywriting skills required. AI creates landing page content, headlines, CTAs, and nurture scripts.')],
            ['fa-browser', __('Landing Pages'), __('Launch professional campaign pages quickly for each goal—offers, leads, or bookings.')],
            ['fa-chart-line', __('Reports & Analytics'), __('See real business health through visits, conversion rates, and returning customers.')],
        ];
        $modules = [
            ['fa-store', __('Store profiles'), __('Centralize and manage location, contact, and brand identity.'), '#ff5f5f'],
            ['fa-star', __('Review Booster'), __('Encourage satisfied customers to leave strong Google Maps reviews.'), '#d09100'],
            ['fa-calendar-check', __('Booking Pages'), __('Accept table or appointment requests directly from professional pages.'), '#2563eb'],
            ['fa-ticket', __('Voucher management'), __('Set up offer campaigns, auto-issue vouchers, and track real redemption rates.'), '#8a5b00'],
            ['fa-comments', __('Feedback collection'), __('Listen to opinions to care for guests and resolve complaints internally.'), '#ff5f5f'],
            ['fa-address-card', __('Customer segmentation'), __('Turn traffic from every channel into a quality customer list.'), '#7c3aed'],
            ['fa-sparkles', __('AI campaign builder'), __('Auto-draft ideas, landing page copy, and calls to action.'), '#ff5f5f'],
            ['fa-reply', __('AI review replies'), __('Draft professional, fast responses for every review.'), '#c2410c'],
            ['fa-browser', __('Landing page designer'), __('Publish conversion-focused pages quickly for each campaign goal.'), '#0891b2'],
            ['fa-qrcode', __('QR management'), __('Create in-store QR codes customers can scan; measure scans easily.'), '#4d7c0f'],
            ['fa-chart-line', __('Analytics reports'), __('Track visits, conversion rates, and performance per campaign.'), '#ff5f5f'],
            ['fa-credit-card', __('Plans & services'), __('Manage usage limits, credits, and staff permissions.'), '#64748b'],
        ];
        $productHighlights = [
            __('Set a clear goal: drive visits, capture leads, or build reputation.'),
            __('Publish a complete, professional landing page.'),
            __('Automatically collect contacts and customer feedback.'),
            __('Measure visits and performance for each campaign accurately.'),
            __('Easily control costs, upgrade plans, and assign team permissions.'),
            __('Use AI to generate landing page content and CTAs.'),
            __('Deploy in the real world with QR codes and short links.'),
            __('Push new customer data straight into your CRM.'),
            __('Manage multiple stores from one workspace.'),
            __('Track bookings, coupons, and leads from one dashboard.'),
            __('Schedule automated email nurture sequences for captured contacts.'),
            __('Draft professional Google Maps review replies with AI.'),
        ];
    @endphp

    <style>
        .lb-sales {
            --lb-ink: #15201b;
            --lb-muted: #63736b;
            --lb-paper: #fbfaf5;
            --lb-soft: #edf5ef;
            --lb-line: #dfe9df;
            --lb-lime: #ffb347;
            --lb-red: #ff5f5f;
            --lb-dark: #10251f;
            --lb-text-xs: 0.75rem;
            --lb-text-sm: 0.875rem;
            --lb-text-base: 1rem;
            --lb-text-lg: 1.125rem;
            --lb-text-xl: 1.25rem;
            --lb-text-2xl: 1.5rem;
            --lb-text-h2: clamp(1.625rem, 1.6vw + 1.1rem, 2.375rem);
            --lb-text-h1: clamp(1.875rem, 2.2vw + 1rem, 2.875rem);
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
            font-family: var(--theme-font-sans);
            letter-spacing: -0.03em;
        }

        .lb-hero-title {
            font-size: var(--lb-text-h1);
            font-weight: 800;
            line-height: 1.3;
            max-width: min(100%, 40rem);
            text-transform: uppercase;
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
            line-height: 1.35;
        }

        .lb-sales .lb-feature-hero h3 {
            font-size: var(--lb-text-2xl);
            font-weight: 800;
            line-height: 1.25;
        }

        .lb-sales .lb-workflow-card p.text-sm {
            font-size: var(--lb-text-sm);
            font-weight: 700;
        }

        .lb-sales .lb-workflow-card p.text-xs {
            font-size: var(--lb-text-xs);
            line-height: 1.4;
        }

        .lb-lead {
            font-size: var(--lb-text-lg);
            line-height: 1.65;
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

        .lb-sales .lb-feature-row h3,
        .lb-sales .lb-step-card h3,
        .lb-sales .lb-page-card h3 {
            font-size: var(--lb-text-xl);
            font-weight: 800;
            line-height: 1.3;
        }

        .lb-sales .lb-suite-card h3 {
            font-size: var(--lb-text-base);
            font-weight: 800;
            line-height: 1.35;
        }

        .lb-sales .lb-workflow-band .lb-heading + p,
        .lb-sales .lb-section > .grid > div > .lb-body,
        .lb-sales .lb-section > .grid > div > p.lb-body {
            font-size: var(--lb-text-base);
            line-height: 1.65;
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

        .lb-pill {
            border: 1px solid rgba(255, 95, 95, .18);
            background: color-mix(in srgb, var(--lb-lime) 26%, #fff);
            color: #ff5f5f;
        }

        .lb-button {
            border-radius: 999px;
            background: var(--lb-red);
            color: #fff;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.22), 0 22px 48px -30px rgba(255,95,95,.9);
            transition: transform .22s ease, box-shadow .22s ease;
        }

        .lb-button:hover,
        .lb-button-soft:hover,
        .lb-hover:hover {
            transform: translateY(-3px);
        }

        .lb-button-soft {
            border-radius: 999px;
            background: color-mix(in srgb, var(--lb-lime) 28%, #fff);
            color: #314708;
            transition: transform .22s ease, background .22s ease;
        }

        .lb-hover {
            transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
        }

        .lb-hover:hover {
            border-color: color-mix(in srgb, var(--lb-red) 30%, var(--lb-line));
            box-shadow: 0 34px 90px -62px rgba(16, 37, 31, .6);
        }

        .lb-dot {
            display: inline-block;
            width: 9px;
            height: 9px;
            border-radius: 999px;
        }

        .lb-section {
            padding-block: clamp(4.5rem, 8vw, 7.5rem);
        }

        .lb-sales [id].scroll-mt-28 {
            scroll-margin-top: 7rem;
        }

        .lb-workflow-band {
            position: relative;
            overflow: hidden;
            padding-block: clamp(4.5rem, 8vw, 7rem);
            background:
                radial-gradient(circle at 18% 28%, rgba(255, 95, 95, .12), transparent 24rem),
                radial-gradient(circle at 76% 42%, rgba(184, 218, 22, .2), transparent 22rem);
            color: var(--lb-ink);
        }

        .lb-workflow-band::before,
        .lb-workflow-band::after {
            content: "";
            position: absolute;
            inset-block: 0;
            z-index: 2;
            width: min(12vw, 12rem);
            pointer-events: none;
        }

        .lb-workflow-band::before {
            left: 0;
            background: linear-gradient(90deg, var(--lb-paper), transparent);
        }

        .lb-workflow-band::after {
            right: 0;
            background: linear-gradient(270deg, var(--lb-paper), transparent);
        }

        .lb-marquee {
            display: flex;
            gap: 1rem;
            width: max-content;
            animation: lb-marquee 34s linear infinite;
        }

        .lb-marquee.reverse {
            animation-direction: reverse;
            animation-duration: 40s;
        }

        .lb-workflow-card {
            width: 18rem;
            border: 1px solid var(--lb-line);
            background: rgba(255, 255, 252, .88);
            box-shadow: 0 22px 58px -48px rgba(16, 37, 31, .5);
            backdrop-filter: blur(8px);
        }

        .lb-workflow-dot {
            width: .55rem;
            height: .55rem;
            border-radius: 999px;
            background: var(--workflow-dot, #ff5f5f);
            box-shadow:
                0 0 0 4px color-mix(in srgb, var(--workflow-dot, #ff5f5f) 13%, transparent),
                0 0 20px color-mix(in srgb, var(--workflow-dot, #ff5f5f) 52%, transparent);
        }

        .lb-reveal {
            animation: lb-rise .72s cubic-bezier(.16,1,.3,1) both;
            animation-delay: var(--lb-delay, 0ms);
        }

        .lb-float {
            animation: lb-float 6s ease-in-out infinite;
            animation-delay: var(--lb-delay, 0ms);
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
            background: radial-gradient(circle at 50% 0%, rgba(184,218,22,.34), transparent 55%);
            filter: blur(18px);
        }

        .lb-scroll {
            opacity: 0;
            transform: translateY(34px) scale(.985);
            transition:
                opacity .72s cubic-bezier(.16,1,.3,1),
                transform .72s cubic-bezier(.16,1,.3,1);
            transition-delay: var(--lb-stagger, 0ms);
        }

        .lb-scroll.is-visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .lb-bar {
            transform-origin: bottom;
            transform: scaleY(.72);
            animation: lb-bar-breathe 2.6s ease-in-out infinite;
            animation-delay: var(--lb-bar-delay, 0ms);
        }

        .lb-flow-line {
            stroke-dasharray: 14 12;
            animation: lb-flow 1.2s linear infinite;
        }

        .lb-steps {
            counter-reset: lb-step;
        }

        .lb-step-card {
            position: relative;
            overflow: hidden;
            min-height: 13rem;
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
            transition: transform .42s cubic-bezier(.16,1,.3,1);
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

        .lb-step-arrow {
            color: color-mix(in srgb, var(--lb-red) 72%, #fff);
        }

        .lb-page-demo {
            position: relative;
            isolation: isolate;
        }

        .lb-page-demo::before {
            content: "";
            position: absolute;
            inset: 4rem 1rem 1rem;
            z-index: -1;
            border-radius: 1.5rem;
            background:
                radial-gradient(circle at 18% 22%, rgba(255, 95, 95, .12), transparent 13rem),
                radial-gradient(circle at 82% 78%, rgba(184, 218, 22, .2), transparent 12rem);
            filter: blur(12px);
        }

        .lb-page-card {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            animation: lb-page-card-in .72s cubic-bezier(.16,1,.3,1) both;
            animation-delay: var(--lb-card-delay, 0ms);
            transition: transform .24s ease, border-color .24s ease, box-shadow .24s ease;
        }

        .lb-page-card:hover {
            transform: translateY(-5px);
            border-color: color-mix(in srgb, var(--lb-red) 28%, var(--lb-line));
            box-shadow: 0 24px 60px -45px rgba(16, 37, 31, .55);
        }

        .lb-page-card::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background: linear-gradient(115deg, transparent 24%, rgba(225, 235, 22, .22) 48%, transparent 68%);
            transform: translateX(-130%);
            animation: lb-card-sheen 5.4s ease-in-out infinite;
            animation-delay: calc(var(--lb-card-delay, 0ms) + 900ms);
        }

        .lb-page-icon {
            animation: lb-icon-bob 3.2s ease-in-out infinite;
            animation-delay: var(--lb-card-delay, 0ms);
        }

        .lb-page-line {
            transform-origin: left;
            animation: lb-line-load .9s cubic-bezier(.16,1,.3,1) both;
            animation-delay: calc(var(--lb-card-delay, 0ms) + 180ms);
        }

        .lb-page-cta {
            position: relative;
            overflow: hidden;
        }

        .lb-page-cta::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .72), transparent);
            transform: translateX(-120%);
            animation: lb-cta-scan 2.8s ease-in-out infinite;
            animation-delay: calc(var(--lb-card-delay, 0ms) + 520ms);
        }

        .lb-page-flow {
            background: color-mix(in srgb, var(--lb-soft) 78%, #fff);
        }

        .lb-page-flow-step {
            position: relative;
        }

        .lb-page-flow-step:not(:last-child)::after {
            content: "";
            position: absolute;
            left: calc(100% + .35rem);
            top: 50%;
            width: .75rem;
            height: 1px;
            background: color-mix(in srgb, var(--lb-red) 42%, var(--lb-line));
        }

        .lb-page-flow-step span:first-child {
            background: color-mix(in srgb, var(--lb-red) 10%, #fff);
            color: var(--lb-red);
        }

        .lb-suite {
            position: relative;
            isolation: isolate;
        }

        .lb-suite::before {
            content: "";
            position: absolute;
            inset: 5rem -1rem -2rem 34%;
            z-index: -1;
            border-radius: 2rem;
            background:
                radial-gradient(circle at 50% 20%, rgba(225, 235, 22, .22), transparent 18rem),
                radial-gradient(circle at 80% 80%, rgba(255, 95, 95, .12), transparent 16rem);
            filter: blur(14px);
        }

        .lb-suite-card {
            position: relative;
            overflow: hidden;
            min-height: 10.5rem;
            transition: transform .24s ease, box-shadow .24s ease, border-color .24s ease;
        }

        .lb-suite-card:hover {
            transform: translateY(-6px);
            border-color: color-mix(in srgb, var(--lb-red) 28%, var(--lb-line));
            box-shadow: 0 28px 72px -54px rgba(16, 37, 31, .62);
        }

        .lb-suite-card::after {
            content: "";
            position: absolute;
            right: -2.5rem;
            bottom: -2.5rem;
            width: 6rem;
            height: 6rem;
            border-radius: 999px;
            background: color-mix(in srgb, var(--module-color, #ff5f5f) 12%, #fff);
        }

        .lb-suite-icon {
            color: var(--module-color, #ff5f5f);
            background: color-mix(in srgb, var(--module-color, #ff5f5f) 10%, #fff);
            box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--module-color, #ff5f5f) 18%, transparent);
        }

        .lb-feature-showcase {
            position: relative;
            isolation: isolate;
        }

        .lb-feature-showcase::before {
            content: "";
            position: absolute;
            inset: 8rem -2rem 3rem -2rem;
            z-index: -1;
            border-radius: 3rem;
            background:
                radial-gradient(circle at 20% 18%, rgba(225, 235, 22, .22), transparent 18rem),
                radial-gradient(circle at 78% 72%, rgba(255, 95, 95, .11), transparent 20rem);
            filter: blur(10px);
        }

        .lb-feature-hero {
            min-height: 100%;
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

        .lb-signal {
            position: relative;
            overflow: hidden;
        }

        .lb-signal::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .78), transparent);
            transform: translateX(-120%);
            animation: lb-cta-scan 3s ease-in-out infinite;
        }

        .lb-proof-panel {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 12% 18%, rgba(255, 95, 95, .13), transparent 18rem),
                radial-gradient(circle at 92% 88%, rgba(225, 235, 22, .22), transparent 16rem),
                rgba(255, 255, 252, .92);
        }

        .lb-proof-item {
            border-bottom: 1px solid var(--lb-line);
        }

        .lb-proof-item:nth-last-child(-n+2) {
            border-bottom: 0;
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

        .lb-final-preview {
            background: linear-gradient(145deg, rgba(255, 95, 95, .08), rgba(255, 235, 22, .16));
        }

        .lb-mini-page {
            background:
                radial-gradient(circle at 78% 12%, rgba(184, 218, 22, .24), transparent 8rem),
                #fffefb;
        }

        .lb-mini-qr {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--lb-ink);
            font-size: 2rem;
        }

        @keyframes lb-rise {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes lb-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        @keyframes lb-dot-pulse {
            0%, 100% { transform: scale(1); opacity: .55; }
            50% { transform: scale(1.35); opacity: 1; }
        }

        @keyframes lb-bar-breathe {
            0%, 100% { transform: scaleY(.68); opacity: .86; }
            45% { transform: scaleY(1); opacity: 1; }
            70% { transform: scaleY(.82); opacity: .94; }
        }

        @keyframes lb-flow {
            to { stroke-dashoffset: -26; }
        }

        @keyframes lb-marquee {
            to { transform: translateX(calc(-50% - .5rem)); }
        }

        @keyframes lb-page-card-in {
            from { opacity: 0; transform: translateY(18px) scale(.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes lb-card-sheen {
            0%, 42% { transform: translateX(-130%); }
            62%, 100% { transform: translateX(130%); }
        }

        @keyframes lb-icon-bob {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-5px) rotate(-3deg); }
        }

        @keyframes lb-line-load {
            from { transform: scaleX(.2); opacity: .45; }
            to { transform: scaleX(1); opacity: 1; }
        }

        @keyframes lb-cta-scan {
            0%, 38% { transform: translateX(-120%); }
            62%, 100% { transform: translateX(120%); }
        }

        @media (prefers-reduced-motion: reduce) {
            .lb-reveal,
            .lb-float,
            .lb-bar,
            .lb-flow-line,
            .lb-step-card::before,
            .lb-marquee,
            .lb-page-card,
            .lb-page-card::after,
            .lb-page-icon,
            .lb-page-line,
            .lb-page-cta::after {
                animation: none !important;
            }

            .lb-scroll {
                opacity: 1;
                transform: none;
                transition: none;
            }
        }

        @media (max-width: 640px) {
            .lb-wrap {
                width: min(100% - 28px, 1160px);
            }
        }

        html[data-theme-resolved='dark'] body {
            background: #07111f !important;
            color: #e8eef7 !important;
        }

        html[data-theme-resolved='dark'] .lb-sales {
            --lb-ink: #e8eef7;
            --lb-muted: #94a3b8;
            --lb-paper: #07111f;
            --lb-soft: #101d32;
            --lb-line: #25364d;
            --lb-lime: #ffb347;
            --lb-red: #ff5f5f;
            --lb-dark: #e8eef7;
            background:
                linear-gradient(rgba(96, 165, 250, .05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(96, 165, 250, .05) 1px, transparent 1px),
                radial-gradient(circle at 78% 9%, rgba(184, 218, 22, .12), transparent 26rem),
                radial-gradient(circle at 7% 18%, rgba(20, 163, 153, .18), transparent 24rem),
                linear-gradient(180deg, #07111f 0%, #0b1526 48%, #07111f 100%) !important;
            background-size: 36px 36px, 36px 36px, auto, auto, auto !important;
            color: var(--lb-ink) !important;
        }

        html[data-theme-resolved='dark'] .lb-workflow-band {
            background:
                radial-gradient(circle at 18% 28%, rgba(20, 163, 153, .18), transparent 24rem),
                radial-gradient(circle at 76% 42%, rgba(184, 218, 22, .1), transparent 22rem),
                #07111f !important;
            color: var(--lb-ink) !important;
        }

        html[data-theme-resolved='dark'] .lb-workflow-band::before {
            background: linear-gradient(90deg, #07111f, transparent) !important;
        }

        html[data-theme-resolved='dark'] .lb-workflow-band::after {
            background: linear-gradient(270deg, #07111f, transparent) !important;
        }

        html[data-theme-resolved='dark'] .lb-card,
        html[data-theme-resolved='dark'] .lb-window,
        html[data-theme-resolved='dark'] .lb-workflow-card,
        html[data-theme-resolved='dark'] .lb-page-demo,
        html[data-theme-resolved='dark'] .lb-proof-visual,
        html[data-theme-resolved='dark'] .lb-final-preview,
        html[data-theme-resolved='dark'] .lb-mini-page {
            border-color: rgba(96, 165, 250, .22) !important;
            background:
                linear-gradient(180deg, rgba(15, 23, 42, .92), rgba(11, 21, 38, .86)) !important;
            box-shadow: 0 30px 90px -62px rgba(0, 0, 0, .86) !important;
        }

        html[data-theme-resolved='dark'] .lb-pill,
        html[data-theme-resolved='dark'] .lb-button-soft {
            border-color: rgba(184, 218, 22, .24) !important;
            background: rgba(184, 218, 22, .14) !important;
            color: #d9f75d !important;
        }

        html[data-theme-resolved='dark'] .lb-sales .bg-white,
        html[data-theme-resolved='dark'] .lb-sales .bg-white\/80,
        html[data-theme-resolved='dark'] .lb-sales .bg-white\/90,
        html[data-theme-resolved='dark'] .lb-sales .bg-white\/95,
        html[data-theme-resolved='dark'] .lb-sales [class*="bg-white"] {
            background-color: rgba(15, 23, 42, .82) !important;
        }

        html[data-theme-resolved='dark'] .lb-sales [style*="#fff"],
        html[data-theme-resolved='dark'] .lb-sales [style*="#fbfaf5"],
        html[data-theme-resolved='dark'] .lb-sales [style*="#fbfaf5"],
        html[data-theme-resolved='dark'] .lb-sales [style*="#fffefb"],
        html[data-theme-resolved='dark'] .lb-sales [style*="white"] {
            background: rgba(15, 23, 42, .76) !important;
            border-color: var(--lb-line) !important;
        }

        html[data-theme-resolved='dark'] .lb-sales [style*="color:#ff5f5f"],
        html[data-theme-resolved='dark'] .lb-sales [style*="color: #ff5f5f"],
        html[data-theme-resolved='dark'] .lb-sales [style*="color:#ff5f5f"],
        html[data-theme-resolved='dark'] .lb-sales [style*="color: var(--lb-red)"] {
            color: #ffb347 !important;
        }

        html[data-theme-resolved='dark'] .lb-suite-card::after,
        html[data-theme-resolved='dark'] .lb-feature-row::after,
        html[data-theme-resolved='dark'] .lb-step-card::after {
            background:
                radial-gradient(circle at 34% 34%, color-mix(in srgb, var(--module-color, var(--lb-red)) 26%, rgba(96, 165, 250, .12)), rgba(15, 23, 42, .72) 68%) !important;
            border: 1px solid rgba(96, 165, 250, .16);
            opacity: .72;
        }

        html[data-theme-resolved='dark'] .lb-suite-icon,
        html[data-theme-resolved='dark'] .lb-feature-icon,
        html[data-theme-resolved='dark'] .lb-page-icon {
            background: color-mix(in srgb, var(--module-color, var(--lb-red)) 18%, rgba(15, 23, 42, .82)) !important;
            color: #ffb347 !important;
            box-shadow: inset 0 0 0 1px rgba(94, 234, 212, .18);
        }

        html[data-theme-resolved='dark'] .lb-sales .text-neutral-950,
        html[data-theme-resolved='dark'] .lb-sales .text-neutral-900,
        html[data-theme-resolved='dark'] .lb-sales .text-slate-950,
        html[data-theme-resolved='dark'] .lb-sales .text-slate-900 {
            color: #f8fafc !important;
        }

        html[data-theme-resolved='dark'] .lb-sales .text-neutral-700,
        html[data-theme-resolved='dark'] .lb-sales .text-neutral-600,
        html[data-theme-resolved='dark'] .lb-sales .text-slate-700,
        html[data-theme-resolved='dark'] .lb-sales .text-slate-600 {
            color: #cbd5e1 !important;
        }

        html[data-theme-resolved='dark'] .lb-sales .text-neutral-500,
        html[data-theme-resolved='dark'] .lb-sales .text-neutral-400,
        html[data-theme-resolved='dark'] .lb-sales .text-slate-500,
        html[data-theme-resolved='dark'] .lb-sales .text-slate-400 {
            color: #94a3b8 !important;
        }
    </style>

    <div class="lb-sales">
        <section id="hero" class="lb-wrap scroll-mt-28 pb-16 pt-16 lg:pb-24 lg:pt-20">
            <div class="grid gap-12 lg:grid-cols-[minmax(0,0.9fr)_minmax(34rem,1.1fr)] lg:items-center">
                <div>
                    <span class="lb-pill lb-reveal inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">
                        <i class="fa-light fa-sparkles"></i>
                        {{ __('O2O AI-powered marketing automation platform') }}
                    </span>
                    <h1 class="lb-serif lb-hero-title lb-reveal mt-7" style="--lb-delay: 70ms;">
                        {{ __('Hero title marketing automation line') }}<br>{{ __('Hero title O2O AI line') }}
                    </h1>
                    <p class="lb-body lb-reveal mt-6 max-w-xl" style="--lb-delay: 140ms; color: var(--lb-muted);">
                        {{ __('A comprehensive Online-to-Offline (O2O) solution that helps household businesses, SMEs, and retail chains automate lead capture, bookings, and review management. Create content with AI and track real growth on a single screen.') }}
                    </p>

                    <div class="lb-reveal mt-8 flex flex-wrap gap-3" style="--lb-delay: 210ms;">
                        <a href="{{ $demoHref }}" class="lb-button inline-flex items-center justify-center gap-2 px-6 py-4 text-sm font-black">
                            <i class="fa-light fa-display"></i>
                            {{ __('Start your free trial') }}
                        </a>
                        <a href="{{ $featuresHref }}" class="lb-button-soft inline-flex items-center justify-center gap-2 px-6 py-4 text-sm font-black">
                            <i class="fa-light fa-grid-2"></i>
                            {{ __('Explore features') }}
                        </a>
                    </div>

                    <div class="lb-reveal mt-7 flex flex-wrap gap-2" style="--lb-delay: 280ms;">
                        @foreach ([__('Making Local HUB'), __('Marketing Automation'), __('Local O2O'), __('Review Booster'), __('Lead Gen & Booking'), __('Coupon claims')] as $badge)
                            <span class="rounded-full border bg-white px-3 py-2 text-xs font-black" style="border-color: var(--lb-line); color: var(--lb-muted);">{{ $badge }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="lb-glow lb-reveal relative" style="--lb-delay: 240ms;">
                    <div class="lb-window relative z-10 overflow-hidden rounded-2xl">
                        <div class="flex items-center justify-between border-b px-5 py-4" style="border-color: var(--lb-line); background: #fbfaf5;">
                            <div class="flex items-center gap-1.5">
                                <span class="lb-dot bg-red-400"></span>
                                <span class="lb-dot bg-amber-400"></span>
                                <span class="lb-dot bg-lime-500"></span>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-black" style="background: color-mix(in srgb, var(--lb-lime) 30%, #fff); color: #ff5f5f;">{{ __('Live dashboard') }}</span>
                        </div>
                        <div class="grid gap-0 lg:grid-cols-[5.2rem_minmax(0,1fr)]">
                            <aside class="hidden border-r px-4 py-3 lg:block" style="border-color: var(--lb-line); background: var(--lb-soft);">
                                <div class="grid justify-items-center gap-3">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white p-1.5" aria-hidden="true">
                                        <img
                                            src="{{ asset(config('mlhub.site.hero_mark', 'img/mlhub-hero-mark.svg')) }}"
                                            alt=""
                                            class="h-full w-full object-contain"
                                            width="40"
                                            height="40"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    </span>
                                    @foreach (['fa-house', 'fa-store', 'fa-star', 'fa-ticket', 'fa-chart-line'] as $index => $icon)
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl {{ $index === 2 ? 'text-white' : '' }}" style="{{ $index === 2 ? 'background: var(--lb-red);' : 'background:#fff; color:#ff5f5f;' }}">
                                            <i class="fa-light {{ $icon }}"></i>
                                        </span>
                                    @endforeach
                                </div>
                            </aside>
                            <main class="p-5">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.18em]" style="color: var(--lb-muted);">{{ __('Growth dashboard') }}</p>
                                        <h3 class="mt-2 text-2xl font-black">{{ __('Campaign Performance') }}</h3>
                                    </div>
                                    <span class="rounded-full px-3 py-1.5 text-xs font-black" style="background: #dcfce7; color: #047857;">{{ __('Active') }}</span>
                                </div>

                                <div class="mt-5 grid gap-3 sm:grid-cols-4">
                                    @foreach ([['Visits', '18'], ['Reviews', '6'], ['Leads', '14'], ['Rate', '28%']] as $metric)
                                        <div class="rounded-xl border bg-white p-3" style="border-color: var(--lb-line);">
                                            <p class="text-2xl font-black">{{ $metric[1] }}</p>
                                            <p class="mt-1 text-xs font-bold" style="color: var(--lb-muted);">{{ __($metric[0]) }}</p>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_minmax(11rem,0.5fr)]">
                                    <div class="rounded-xl border bg-white p-4" style="border-color: var(--lb-line);">
                                        <div class="flex items-end gap-2 h-28">
                                            @foreach ([38, 56, 44, 76, 61, 88, 73, 96, 84, 92, 88, 100] as $bar)
                                                <span class="lb-bar flex-1 rounded-t-lg" style="--lb-bar-delay: {{ $loop->index * 160 }}ms; height: {{ $bar }}%; background: {{ $loop->even ? 'var(--lb-red)' : 'var(--lb-lime)' }};"></span>
                                            @endforeach
                                        </div>
                                        <div class="mt-4 space-y-3 border-t pt-4" style="border-color: var(--lb-line);">
                                            @foreach ([['Hero dash Reviews', 78], ['Hero dash Bookings', 92]] as $progress)
                                                <div>
                                                    <div class="flex items-center justify-between gap-2">
                                                        <p class="min-w-0 text-[10px] font-bold leading-none whitespace-nowrap" style="color: var(--lb-muted);">{{ __($progress[0]) }}</p>
                                                        <p class="shrink-0 text-[10px] font-black tabular-nums" style="color: #ff5f5f;">{{ format_percent_locale($progress[1]) }}</p>
                                                    </div>
                                                    <div class="mt-1.5 h-1.5 overflow-hidden rounded-full" style="background: color-mix(in srgb, var(--lb-line) 55%, #fff);">
                                                        <span class="block h-full rounded-full transition-[width] duration-700" style="width: {{ $progress[1] }}%; background: {{ $loop->even ? 'var(--lb-red)' : 'var(--lb-lime)' }};"></span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @php
                                        $heroModuleStats = [
                                            [__('Hero dash Reviews'), __('Hero dash 4.9 avg')],
                                            [__('Hero dash Bookings'), __('Hero dash 8 new')],
                                            [__('Hero dash Coupons'), __('Hero dash 23 used')],
                                        ];
                                    @endphp
                                    <div class="grid grid-cols-1 gap-2">
                                        @foreach ($heroModuleStats as $item)
                                            <div class="rounded-xl border bg-white p-3" style="border-color: var(--lb-line);">
                                                <p class="text-xs font-black leading-snug line-clamp-2">{{ $item[0] }}</p>
                                                <p class="mt-2 text-sm font-black leading-snug line-clamp-2" style="color: #ff5f5f;">{{ $item[1] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </main>
                        </div>
                    </div>

                    <div class="lb-card lb-float absolute -left-5 bottom-18 z-20 hidden rounded-xl p-4 shadow-xl md:block" style="--lb-delay: 120ms;">
                        <p class="text-xs font-black">{{ __('AI Campaign Builder') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--lb-muted);">{{ __('Built for your goals and purpose') }}</p>
                    </div>
                    <div class="lb-card lb-float absolute -right-4 top-14 z-20 hidden w-[13.5rem] max-w-[calc(100vw-2.5rem)] rounded-xl p-3 shadow-xl md:block" style="--lb-delay: 420ms;">
                        <div class="flex gap-2.5">
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-sm text-white" style="background: var(--lb-red);">
                                <i class="fa-light fa-robot" aria-hidden="true"></i>
                            </span>
                            <p class="text-[11px] font-bold leading-snug" style="color: var(--lb-muted);">
                                {{ __('Hero float AI revenue notification') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="workflow" class="lb-workflow-band scroll-mt-28">
            <div class="lb-wrap relative z-10 text-center">
                <span class="lb-pill inline-flex rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('Connected growth workflow chain') }}</span>
                <h2 class="lb-serif lb-heading mx-auto mt-5 max-w-3xl">
                    {{ __('Digital transformation without barriers from limited finances or technology skills.') }}
                </h2>
                <p class="lb-body mx-auto mt-5 max-w-2xl" style="color: var(--lb-muted);">
                    {{ __('MKT standardized SOP workflows help you create AI campaigns, publish lead capture landing pages, route through QR codes, and auto-update reports without juggling disconnected software. MKT walks with you step by step to transform digital asset management and make daily operations gentler, more affordable, and more effective with ready-made tools.') }}
                </p>
            </div>

            <div class="relative z-10 mt-12 space-y-4">
                @php
                    $workflowCards = [
                        ['fa-store', __('Restaurants'), __('Reviews, coupons, and feedback')],
                        ['fa-spa', __('Spa & beauty'), __('More appointments and repeat visits')],
                        ['fa-stethoscope', __('Clinics'), __('Online consultation sign-ups')],
                        ['fa-dumbbell', __('Gyms'), __('Trial passes and offers')],
                        ['fa-shop', __('Retail stores'), __('QR voucher campaigns')],
                        ['fa-bullhorn', __('For agencies'), __('Client workspaces')],
                        ['fa-star', __('Review Booster'), __('More reviews on Google/Facebook')],
                        ['fa-calendar-check', __('Booking Pages'), __('Appointment intake')],
                        ['fa-ticket', __('Create coupons'), __('Issue and redeem vouchers')],
                    ];
                    $workflowCardsAlt = [
                        ['fa-qrcode', __('Create QR codes'), __('Drive traffic to campaign pages')],
                        ['fa-browser', __('Landing Pages'), __('Standardize your brand presence')],
                        ['fa-comments', __('Feedback Forms'), __('Collect details and opinions')],
                        ['fa-reply', __('AI review replies'), __('Fast, accurate review responses')],
                        ['fa-users', __('Customers'), __('New customer list')],
                        ['fa-address-card', __('Lead Forms'), __('Score and segment leads')],
                        ['fa-chart-line', __('Reports'), __('Unified, visual tracking')],
                        ['fa-sparkles', __('AI Campaign Builder'), __('Built for your goals and purpose')],
                    ];
                    $workflowDotPalettes = [
                        ['#ff5f5f', '#84cc16', '#14b8a6', '#d99b00', '#5b8c04', '#0891b2', '#0d9488', '#a3c30f', '#c08400'],
                        ['#0891b2', '#ff5f5f', '#ffb347', '#65a30d', '#14b8a6', '#c08400', '#4d7c0f', '#0d9488'],
                    ];
                @endphp

                @foreach ([$workflowCards, $workflowCardsAlt] as $rowIndex => $row)
                    <div class="overflow-hidden">
                        <div class="lb-marquee {{ $loop->even ? 'reverse' : '' }}">
                            @foreach (array_merge($row, $row) as $card)
                                @php
                                    $dotPalette = $workflowDotPalettes[$rowIndex] ?? $workflowDotPalettes[0];
                                    $dotColor = $dotPalette[$loop->index % count($dotPalette)];
                                @endphp
                                <div class="lb-workflow-card flex items-center gap-4 rounded-full p-3 pr-5 text-left" style="--workflow-dot: {{ $dotColor }};">
                                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full" style="background: color-mix(in srgb, var(--lb-red) 9%, #fff); color: var(--lb-red);">
                                        <i class="fa-light {{ $card[0] }}"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="truncate text-sm font-black">{{ $card[1] }}</p>
                                            <span class="lb-workflow-dot shrink-0"></span>
                                        </div>
                                        <p class="mt-1 truncate text-xs" style="color: var(--lb-muted);">{{ $card[2] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section id="features" class="lb-wrap lb-section lb-feature-showcase scroll-mt-28">
            <div class="grid gap-10 lg:grid-cols-[0.78fr_1.22fr] lg:items-start">
                <div>
                    <span class="lb-pill inline-flex items-center rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('AI-integrated core features') }}</span>
                    <h2 class="lb-serif lb-heading mt-5">{{ __('Professional presence, local revenue breakthrough') }}</h2>
                    <p class="lb-body mt-5" style="color: var(--lb-muted);">
                        {{ __('A complete feature set for easy online presence—build a credible digital storefront, optimize Google Maps to attract tourists and walk-in customers, and package ready-made marketing tools that are easy to use and highly effective.') }}
                    </p>

                    <div class="lb-card lb-feature-hero mt-8 rounded-2xl p-6">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em]" style="color: var(--lb-muted);">{{ __('Growth engine') }}</p>
                                <h3 class="mt-2 text-2xl font-black">{{ __('One campaign hub') }}</h3>
                            </div>
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl text-white" style="background: var(--lb-red);">
                                <i class="fa-light fa-bolt"></i>
                            </span>
                        </div>

                        <div class="mt-6 grid gap-3 sm:grid-cols-3">
                            @foreach ([['18', __('Visits')], ['6', __('Reviews')], ['28%', __('Rate')]] as $signal)
                                <div class="rounded-xl border bg-white/80 p-4" style="border-color: var(--lb-line);">
                                    <p class="text-2xl font-black">{{ $signal[0] }}</p>
                                    <p class="mt-1 text-xs font-bold" style="color: var(--lb-muted);">{{ $signal[1] }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 rounded-2xl border bg-white p-4" style="border-color: var(--lb-line);">
                            @foreach ([82, 64, 92] as $width)
                                <div class="mb-3 last:mb-0">
                                    <span class="mb-2 block h-2 w-24 rounded-full" style="background: var(--lb-line);"></span>
                                    <span class="lb-signal block h-3 rounded-full" style="width: {{ $width }}%; background: color-mix(in srgb, var(--lb-red) 72%, var(--lb-lime));"></span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div>
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($coreFeatures as $feature)
                            <article class="lb-card lb-feature-row lb-hover rounded-2xl p-5">
                                <div class="relative z-10 flex items-start gap-4">
                                    <span class="lb-feature-icon inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" style="background: color-mix(in srgb, var(--lb-lime) 30%, #fff); color:#ff5f5f;">
                                        <i class="fa-light {{ $feature[0] }} text-xl"></i>
                                    </span>
                                    <div>
                                        <h3 class="lb-card-title">{{ $feature[1] }}</h3>
                                        <p class="lb-caption mt-2" style="color: var(--lb-muted);">{{ $feature[2] }}</p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="mt-4 flex justify-end">
                        <a href="{{ route('guest.solutions') }}" class="inline-flex items-center gap-2 text-sm font-black transition hover:opacity-80" style="color: var(--lb-red);">
                            {{ __('See more other features') }}
                            <i class="fa-light fa-arrow-right-long" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section id="how-it-works" class="lb-wrap lb-section scroll-mt-28">
            <div class="grid gap-10 lg:grid-cols-[0.75fr_1.25fr] lg:items-start">
                <div>
                    <span class="lb-pill inline-flex items-center rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('How it works') }}</span>
                    <h2 class="lb-serif lb-heading mt-5">{{ __('Launch your first automated campaign in just 4 steps') }}</h2>
                    <p class="lb-body mt-5" style="color: var(--lb-muted);">
                        {{ __('From setup to go-live in minutes—AI handles the copy, you place the QR, and MKT tracks visits and leads in real time.') }}
                    </p>
                </div>
                <div class="relative">
                    <div class="lb-steps relative z-10 grid gap-4 md:grid-cols-2">
                    @foreach ([
                        ['1', 'fa-store', __('Launch your digital space'), __('Update your venue details, logo, hero images, and Google Maps link.')],
                        ['2', 'fa-bullseye-arrow', __('Choose your campaign goal'), __('What do you need? Drive foot traffic, build reputation, or capture customer contacts?')],
                        ['3', 'fa-sparkles', __('AI builds your campaign'), __('AI automatically writes compelling copy, calls to action, and guest reply scripts.')],
                        ['4', 'fa-qrcode', __('Print and place QR codes at checkout'), __('Print QR codes for tables or counters. Track visits and incoming customer data.')],
                    ] as $step)
                        <article class="lb-card lb-step-card lb-hover rounded-2xl p-6">
                            <div class="relative z-10 flex items-center justify-between gap-4">
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl text-white" style="background: var(--lb-red);">
                                    <i class="fa-light {{ $step[1] }}"></i>
                                </span>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-black" style="color: var(--lb-muted);">{{ __('Step') }}</span>
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full text-sm font-black" style="background: color-mix(in srgb, var(--lb-lime) 34%, #fff); color: #ff5f5f;">{{ $step[0] }}</span>
                                </div>
                            </div>
                            <h3 class="relative z-10 mt-6 lb-card-title">{{ $step[2] }}</h3>
                            <p class="relative z-10 mt-3 lb-caption" style="color: var(--lb-muted);">{{ $step[3] }}</p>
                            @if (! $loop->last)
                                <span class="lb-step-arrow absolute bottom-5 right-5 z-10 hidden text-xl md:inline-flex">
                                    <i class="fa-light fa-arrow-right-long"></i>
                                </span>
                            @endif
                        </article>
                    @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section id="product-proof" class="lb-wrap lb-section scroll-mt-28">
            <div class="grid gap-8 lg:grid-cols-2 lg:items-center">
                <div class="lb-window lb-page-demo overflow-hidden rounded-2xl">
                    <div class="border-b px-5 py-4" style="border-color: var(--lb-line);">
                        <p class="text-xs font-black uppercase tracking-[0.18em]" style="color: var(--lb-muted);">{{ __('Public campaign pages') }}</p>
                    </div>
                    <div class="relative p-5">
                        <div class="lb-page-flow mb-4 grid gap-2 rounded-2xl border p-3 sm:grid-cols-4" style="border-color: var(--lb-line);">
                            @foreach ([['fa-eye', __('Visit')], ['fa-arrow-pointer', __('CTA')], ['fa-user-plus', __('Customer')], ['fa-chart-line', __('Report')]] as $flow)
                                <div class="lb-page-flow-step flex items-center gap-2 rounded-xl bg-white px-3 py-2">
                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl">
                                        <i class="fa-light {{ $flow[0] }} text-sm"></i>
                                    </span>
                                    <span class="truncate text-xs font-black">{{ $flow[1] }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="relative z-10 grid gap-4 sm:grid-cols-2">
                        @foreach ([['fa-star', __('Review Page')], ['fa-calendar-check', __('Booking Page')], ['fa-ticket', __('Coupon Page')], ['fa-address-card', __('Lead Page')]] as $page)
                            <div class="lb-page-card rounded-xl border bg-white p-5" style="border-color: var(--lb-line); --lb-card-delay: {{ $loop->index * 120 }}ms;">
                                <span class="lb-page-icon inline-flex h-11 w-11 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--lb-red) 9%, #fff); color: #ff5f5f;">
                                    <i class="fa-light {{ $page[0] }} text-2xl"></i>
                                </span>
                                <h3 class="mt-5 text-xl font-black">{{ $page[1] }}</h3>
                                <div class="mt-5 space-y-2">
                                    <span class="lb-page-line block h-3 rounded-full" style="background: var(--lb-line);"></span>
                                    <span class="lb-page-line block h-3 w-2/3 rounded-full" style="background: var(--lb-line);"></span>
                                    <span class="lb-page-cta block h-10 rounded-full" style="background: color-mix(in srgb, var(--lb-lime) 38%, #fff);"></span>
                                </div>
                            </div>
                        @endforeach
                        </div>
                    </div>
                </div>
                <div>
                    <span class="lb-pill inline-flex items-center rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('Master the toolkit') }}</span>
                    <h2 class="lb-serif lb-heading mt-5">{{ __('Every business model can show up and operate professionally') }}</h2>
                    <p class="lb-body mt-5" style="color: var(--lb-muted);">{{ __('Beyond software, MKT delivers a standard process to keep winning new customers, retaining loyal ones, and training the next generation of staff—so household businesses grow sustainably.') }}</p>
                </div>
            </div>
        </section>

        <section id="modules" class="lb-wrap lb-section lb-suite scroll-mt-28">
            <div class="grid gap-10 lg:grid-cols-[0.78fr_1.22fr] lg:items-start">
                <div class="lg:sticky lg:top-28">
                    <span class="lb-pill inline-flex items-center rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('Full toolkit') }}</span>
                    <h2 class="lb-serif lb-heading mt-5">{{ __('Comprehensive marketing solution for your store') }}</h2>
                    <p class="lb-body mt-5" style="color: var(--lb-muted);">{{ __('Control every customer acquisition tool on one unified platform.') }}</p>
                    <div class="mt-8 rounded-2xl border bg-white/80 p-5" style="border-color: var(--lb-line);">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl text-white" style="background: var(--lb-red);">
                                <i class="fa-light fa-diagram-project"></i>
                            </span>
                            <div>
                                <p class="text-sm font-black">{{ __('Seamless management workflow') }}</p>
                                <p class="mt-1 text-xs leading-5" style="color: var(--lb-muted);">{{ __('Offers, QR codes, customer data, and reports stay automatically in sync.') }}</p>
                            </div>
                        </div>
                        <div class="mt-5 grid grid-cols-3 gap-2 text-center">
                            @foreach ([__('Capture'), __('Convert'), __('Track')] as $label)
                                <span class="rounded-full px-3 py-2 text-xs font-black" style="background: color-mix(in srgb, var(--lb-lime) 22%, #fff); color: #ff5f5f;">{{ $label }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($modules as $module)
                        <div class="lb-card lb-suite-card rounded-2xl p-5" style="--module-color: {{ $module[3] }};">
                            <div class="relative z-10 flex items-start justify-between gap-4">
                                <span class="lb-suite-icon inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl">
                                    <i class="fa-light {{ $module[0] }} text-xl"></i>
                                </span>
                                <span class="text-xs font-black" style="color: var(--lb-muted);">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="relative z-10 mt-6">
                                <h3 class="text-base font-black">{{ $module[1] }}</h3>
                                <p class="mt-3 text-sm leading-6" style="color: var(--lb-muted);">{{ $module[2] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="growth-flow" class="lb-wrap lb-section scroll-mt-28">
            <div class="lb-card lb-proof-panel rounded-3xl p-6 sm:p-8">
                <div class="grid gap-10 lg:grid-cols-[0.86fr_1.14fr] lg:items-center">
                    <div class="relative z-10">
                        <span class="lb-pill inline-flex rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('Campaign workflow') }}</span>
                        <h2 class="lb-serif lb-heading">{{ __('Smooth operations from idea to customer touchpoint') }}</h2>
                        <p class="lb-body mt-5" style="color: var(--lb-muted);">{{ __('Simplify every technical step—automate the full conversion flow from campaign pages and QR codes through to measurable reports.') }}</p>
                        <div class="lb-proof-visual mt-8 rounded-2xl border p-5" style="border-color: var(--lb-line);">
                            <div class="grid gap-3 sm:grid-cols-3">
                                @foreach ([['fa-bullhorn', __('Campaign management')], ['fa-browser', __('Landing page management')], ['fa-user-plus', __('Customer management')]] as $node)
                                    <div class="lb-proof-node rounded-2xl bg-white p-4">
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--lb-red) 9%, #fff); color: var(--lb-red);">
                                            <i class="fa-light {{ $node[0] }}"></i>
                                        </span>
                                        <p class="mt-4 text-sm font-black">{{ $node[1] }}</p>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-5 rounded-2xl bg-white p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">{{ __('Growth signals') }}</span>
                                    <span class="rounded-full px-3 py-1 text-xs font-black" style="background:#dcfce7; color:#047857; white-space: nowrap;">{{ __('Tracked') }}</span>
                                </div>
                                <div class="mt-4 flex items-end gap-1.5 h-20">
                                    @foreach ([42, 66, 52, 78, 92, 56, 68, 74, 48, 82, 64, 96] as $bar)
                                        <span class="lb-bar flex-1 rounded-t-lg" style="--lb-bar-delay: {{ $loop->index * 120 }}ms; height: {{ $bar }}%; background: {{ $loop->even ? 'var(--lb-red)' : 'var(--lb-lime)' }};"></span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative z-10">
                        <h3 class="lb-card-title">{{ __('Mode for marketing staff') }}</h3>
                        <div class="mt-4 grid gap-x-6 sm:grid-cols-2">
                            @foreach ($productHighlights as $item)
                                <div class="lb-proof-item flex gap-3 py-4">
                                    <span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full" style="background: color-mix(in srgb, var(--lb-red) 10%, #fff); color: var(--lb-red);">
                                        <i class="fa-light fa-check text-xs"></i>
                                    </span>
                                    <span class="text-sm font-black leading-6">{{ $item }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="get-started" class="lb-wrap scroll-mt-28 pb-20">
            <div class="lb-window lb-final-cta rounded-3xl p-6 sm:p-8">
                <div class="grid gap-8 lg:grid-cols-[1fr_24rem] lg:items-center">
                    <div>
                        <span class="lb-pill inline-flex rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('Choose your industry and start your campaign') }}</span>
                        <h2 class="lb-serif lb-heading mt-5 max-w-3xl">{{ __('Ready to boost revenue and automate customer care?') }}</h2>
                        <p class="lb-body mt-5 max-w-2xl" style="color: var(--lb-muted);">{{ __('Do not miss any potential customer. Start attracting, retaining customers, and building a standard operating process (SPO) for your store right now.') }}</p>
                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <div class="flex-1 min-w-[12rem] rounded-2xl border bg-white p-4" style="border-color: var(--lb-line);">
                                <p class="text-sm font-black">{{ __('Select a standardized SOP') }}</p>
                            </div>
                            <span class="flex-none text-xl font-black" style="color: var(--lb-red);">
                                <i class="fa-light fa-arrow-right-long"></i>
                            </span>
                            <div class="flex-1 min-w-[12rem] rounded-2xl border bg-white p-4" style="border-color: var(--lb-line);">
                                <p class="text-sm font-black">{{ __('AI pushes content') }}</p>
                            </div>
                            <span class="flex-none text-xl font-black" style="color: var(--lb-red);">
                                <i class="fa-light fa-arrow-right-long"></i>
                            </span>
                            <div class="flex-1 min-w-[12rem] rounded-2xl border bg-white p-4" style="border-color: var(--lb-line);">
                                <p class="text-sm font-black">{{ __('View overview dashboard') }}</p>
                            </div>
                        </div>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ $demoHref }}" class="lb-button inline-flex items-center justify-center gap-2 px-6 py-4 text-sm font-black">
                                <i class="fa-light fa-rocket-launch"></i>
                                {{ __('Start your free trial') }}
                            </a>
                            <a href="{{ route('guest.pricing') }}" class="lb-button-soft inline-flex items-center justify-center gap-2 px-6 py-4 text-sm font-black">
                                <i class="fa-light fa-credit-card"></i>
                                {{ __('View Pricing') }}
                            </a>
                        </div>
                    </div>
                    <div class="lb-final-preview rounded-3xl border p-5" style="border-color: var(--lb-line);">
                        <div class="rounded-2xl bg-white p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">{{ __('Live campaign') }}</p>
                                    <p class="mt-1 text-xl font-black">{{ __('Weekend Review Boost') }}</p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-xs font-black" style="background:#dcfce7; color:#047857; white-space: nowrap;">{{ __('Active') }}</span>
                            </div>
                            <div class="mt-5 grid gap-4">
                                <div class="lb-mini-page rounded-2xl border p-4" style="border-color: var(--lb-line);">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-black uppercase tracking-[0.14em]" style="color: var(--lb-muted);">{{ __('Review page') }}</p>
                                            <h3 class="mt-2 text-lg font-black">{{ __('Enjoyed your visit?') }}</h3>
                                        </div>
                                        <div class="lb-mini-qr h-16 w-16 shrink-0 rounded-xl border bg-white" style="border-color: var(--lb-line);" aria-hidden="true">
                                            <i class="fa-light fa-qrcode"></i>
                                        </div>
                                    </div>
                                    <div class="mt-4 space-y-2">
                                        <span class="block h-2.5 w-4/5 rounded-full" style="background: var(--lb-line);"></span>
                                        <span class="block h-2.5 w-2/3 rounded-full" style="background: var(--lb-line);"></span>
                                    </div>
                                    <div class="mt-4 flex items-center justify-between gap-3">
                                        <span class="rounded-full px-4 py-2 text-xs font-black" style="background: color-mix(in srgb, var(--lb-lime) 38%, #fff); color:#ff5f5f;">{{ __('Leave a review') }}</span>
                                        <span class="text-xs font-black" style="color: var(--lb-muted);">{{ __('Demo campaign link path') }}</span>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    @foreach ([['fa-eye', '284', __('Visits')], ['fa-star', '72', __('Review clicks')], ['fa-user-plus', '39', __('New leads')]] as $stat)
                                        <div class="rounded-2xl border bg-white p-3" style="border-color: var(--lb-line);">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--lb-red) 9%, #fff); color: var(--lb-red);">
                                                    <i class="fa-light {{ $stat[0] }} text-sm"></i>
                                                </span>
                                                <span class="text-xl font-black">{{ $stat[1] }}</span>
                                            </div>
                                            <p class="mt-1 text-xs font-bold" style="color: var(--lb-muted);">{{ $stat[2] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        (() => {
            const root = document.querySelector('.lb-sales');

            if (!root || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            const revealTargets = root.querySelectorAll([
                '.lb-section > .grid',
                '.lb-section > .mx-auto',
                '.lb-section > .rounded-2xl',
                '.lb-card',
                '.lb-window',
                '.lb-pill',
                '.lb-workflow-band',
            ].join(','));

            revealTargets.forEach((element, index) => {
                if (element.classList.contains('lb-reveal')) {
                    return;
                }

                element.classList.add('lb-scroll');
                element.style.setProperty('--lb-stagger', `${Math.min(index % 8, 7) * 55}ms`);
            });

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            }, {
                threshold: 0.16,
                rootMargin: '0px 0px -8% 0px',
            });

            root.querySelectorAll('.lb-scroll').forEach((element) => observer.observe(element));
        })();
    </script>
@endsection
