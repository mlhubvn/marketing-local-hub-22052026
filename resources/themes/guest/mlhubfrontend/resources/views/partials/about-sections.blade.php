@php
    $demoHref = auth()->check() ? route('portal.dashboard') : route('register');
    $missionPillars = [
        ['fa-chart-line', __('Continuously attract guests and boost revenue.')],
        ['fa-graduation-cap', __('Continuously train and nurture digital-ready staff.')],
        ['fa-heart', __('Continuously care for and retain loyal customers.')],
        ['fa-list-check', __('Continuously standardize and build SOP processes.')],
    ];
    $barriers = [
        ['fa-bullhorn', __('Wasted ad spend'), __('Burning money on Facebook Ads is expensive—and leads go silent after they ask.')],
        ['fa-qrcode', __('The VietQR trap'), __('Guests pay and leave; you lose all their data with no way to bring them back.')],
        ['fa-message-sms', __('The SMS bottleneck'), __('Asking guests to type a phone number feels like hassle; SMS OTP fees eat into your margins.')],
        ['fa-map-location-dot', __('Invisible on Google Maps'), __('90% of tourists find shops on Maps. Without 5-star reviews, you hand walk-in demand to rivals.')],
    ];
    $solutions = [
        ['fa-globe', __('Create and manage .vn domains'), __('Launch credible digital identity and build a trusted online store (e.g. .biz.vn).')],
        ['fa-map-location-dot', __('Google Maps coverage'), __('Automate 5-star review requests and rank Top 1 in local search to capture tourists near your store.')],
        ['fa-route', __('O2O automation funnel'), __('Closed loop: scan table QR → one-tap Gmail sign-in → instant voucher → AI nurture messages → customers return to buy.')],
    ];
    $benefits = [
        ['fa-robot', __('Hands-free operations'), __('AI handles repetitive tasks (appointment reminders, thank-you notes, review requests) and frees you from manual bookkeeping.')],
        ['fa-fingerprint', __('One-tap operations (Zero-Friction)'), __('Remove friction entirely—guests never type; one Google sign-in and they are done.')],
        ['fa-users-gear', __('Save on staffing'), __('Everything on one screen. Even older shop owners use it smoothly—no extra marketing or IT hire.')],
    ];
    $journeyFlow = [
        ['fa-qrcode', __('Scan table QR')],
        ['fa-envelope', __('One-tap Gmail sign-in')],
        ['fa-ticket', __('Instant voucher')],
        ['fa-sparkles', __('AI nurture messages')],
    ];
    $mlhubAiChatPreview = [
        [__('Any new customers this week?'), __('This week: 12 new customers (+3 vs last week). 8 from Review Booster, 4 from booking pages.')],
        [__('Summarize running campaigns'), __('3 active campaigns: Review Booster (847 scans), Spring coupon (156 claims), Lead form (23 leads).')],
        [__('Are this week\'s reviews good?'), __('4.8★ average from 6 new reviews. Positive sentiment — 2 reviews still need a reply.')],
        [__('What should I do next? / Suggest a new campaign.'), __('Suggest a weekend coupon for repeat guests. I can draft copy and a QR landing page when the assistant launches.')],
    ];
    $mlhubAiPromptChips = [
        __('Any new customers this week?'),
        __('Summarize running campaigns'),
        __('Are this week\'s reviews good?'),
        __('What should I do next? / Suggest a new campaign.'),
    ];
@endphp

{{-- Hero: MLHUB AI chat preview --}}
<section id="mlhub-ai-mcp" class="lb-wrap lb-section lb-about scroll-mt-28 pb-4 lg:pb-8">
    <div class="grid items-center gap-12 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,1fr)]">
        <div class="lb-glow lb-reveal relative" style="--lb-delay: 240ms;">
            <div class="lb-window lb-ai-chat relative z-10 overflow-hidden rounded-2xl">
                <div class="flex items-center justify-between gap-3 border-b px-5 py-4" style="border-color: var(--lb-line); background: var(--lb-soft);">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="lb-dot bg-red-400"></span>
                            <span class="lb-dot bg-amber-400"></span>
                            <span class="lb-dot bg-lime-500"></span>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black">{{ __('LocalBoost AI') }}</p>
                            <p class="truncate text-[11px] font-bold" style="color: var(--lb-muted);">{{ __('Assistant chat preview') }}</p>
                        </div>
                    </div>
                    <span class="shrink-0 rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-[0.12em]" style="background: color-mix(in srgb, var(--lb-lime) 30%, #fff); color: #ff5f5f;">{{ __('Coming soon') }}</span>
                </div>

                <div class="grid gap-0 lg:grid-cols-[5.2rem_minmax(0,1fr)]">
                    <aside class="hidden border-r px-4 py-4 lg:block" style="border-color: var(--lb-line); background: var(--lb-soft);">
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
                            @foreach (['fa-store', 'fa-star', 'fa-qrcode', 'fa-chart-line'] as $index => $icon)
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl {{ $index === 0 ? 'text-white' : '' }}" style="{{ $index === 0 ? 'background: var(--lb-red);' : 'background:#fff; color:#ff5f5f;' }}">
                                    <i class="fa-light {{ $icon }}"></i>
                                </span>
                            @endforeach
                        </div>
                    </aside>

                    <div class="lb-ai-chat__body p-4 sm:p-5">
                        <div class="lb-ai-chat__messages space-y-3">
                            @foreach ($mlhubAiChatPreview as $index => $exchange)
                                <div
                                    class="lb-ai-chat__bubble lb-ai-chat__bubble--user lb-chat-pop ml-auto max-w-[88%] rounded-2xl rounded-br-md px-4 py-3 text-sm font-bold leading-snug"
                                    style="--lb-chat-delay: {{ ($index * 1400) + 300 }}ms;"
                                >
                                    {{ $exchange[0] }}
                                </div>
                                <div
                                    class="lb-ai-chat__bubble lb-ai-chat__bubble--ai lb-chat-pop flex max-w-[92%] gap-3 rounded-2xl rounded-bl-md px-3 py-3 sm:px-4"
                                    style="--lb-chat-delay: {{ ($index * 1400) + 750 }}ms;"
                                >
                                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm text-white" style="background: var(--lb-red);">
                                        <i class="fa-light fa-robot" aria-hidden="true"></i>
                                    </span>
                                    <p class="text-sm font-bold leading-snug" style="color: var(--lb-muted);">{{ $exchange[1] }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="lb-ai-chat__composer mt-5 flex items-center gap-3 rounded-2xl border px-4 py-3" style="border-color: var(--lb-line); background: rgba(255,255,255,.92);">
                            <p class="min-w-0 flex-1 truncate text-sm font-bold" style="color: var(--lb-muted);">{{ __('Type a question…') }}</p>
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-white" style="background: var(--lb-red);">
                                <i class="fa-light fa-paper-plane-top text-sm" aria-hidden="true"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lb-card lb-float absolute -left-4 bottom-10 z-20 hidden rounded-xl p-4 shadow-xl md:block" style="--lb-delay: 180ms;">
                <p class="text-xs font-black">{{ __('Portal Dashboard') }}</p>
                <p class="mt-1 text-xs" style="color: var(--lb-muted);">{{ __('Assistant chat preview') }}</p>
            </div>
            <div class="lb-card lb-float absolute -right-3 top-10 z-20 hidden w-44 rounded-xl p-3 shadow-xl md:block" style="--lb-delay: 360ms;">
                <div class="flex gap-2.5">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-sm text-white" style="background: var(--lb-red);">
                        <i class="fa-light fa-comments" aria-hidden="true"></i>
                    </span>
                    <p class="text-[11px] font-bold leading-snug" style="color: var(--lb-muted);">{{ __('Ask about customers, campaigns, and reviews — right on your dashboard.') }}</p>
                </div>
            </div>
        </div>

        <div>
            <span class="lb-pill lb-reveal inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">
                <i class="fa-light fa-robot"></i>
                {{ __('About MLHUB AI') }}
            </span>
            <h1 class="lb-serif lb-hero-title lb-reveal mt-6" style="--lb-delay: 70ms;">{{ __('Ask MLHUB AI in natural language') }}</h1>
            <p class="lb-lead lb-reveal mt-5 max-w-xl" style="--lb-delay: 140ms; color: var(--lb-muted);">
                {{ __('Talk to your growth data on Portal Dashboard. Ask in plain language — get answers about campaigns, reviews, and bookings. Smart assistant, coming soon.') }}
            </p>
            <div class="lb-reveal mt-8 flex flex-wrap gap-2" style="--lb-delay: 210ms;">
                @foreach ($mlhubAiPromptChips as $chip)
                    <span class="rounded-full border bg-white/90 px-3 py-2 text-xs font-bold leading-snug" style="border-color: var(--lb-line); color: var(--lb-muted);">{{ $chip }}</span>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- 01 What is MLHUB? --}}
<section id="about-what" class="lb-wrap lb-section lb-about-panel scroll-mt-28 pt-0">
    <div class="grid gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
        <div>
            <p class="lb-section-index">01</p>
            <span class="lb-pill mt-4 inline-flex items-center rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('What is MLHUB?') }}</span>
            <h2 class="lb-serif lb-heading mt-5">{{ __('What is MLHUB?') }}</h2>
            <p class="lb-body mt-5" style="color: var(--lb-muted);">{{ __('In today\'s digital economy, most software is too expensive and complex for small shops. MLHUB was built to fill that gap.') }}</p>
            <p class="lb-body mt-4" style="color: var(--lb-muted);">{{ __('MLHUB is a Marketing Automation O2O platform tailored for household businesses (SOHO) and SMEs. Instead of feature overload, we deliver the most minimal ecosystem: turn every table-side QR scan into a fully automated search, engagement, and customer care workflow.') }}</p>
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ([__('Marketing Automation'), __('Local O2O'), __('Review Booster')] as $badge)
                    <span class="rounded-full border bg-white/90 px-3 py-2 text-xs font-black" style="border-color: var(--lb-line); color: var(--lb-muted);">{{ $badge }}</span>
                @endforeach
            </div>
        </div>
        <article class="lb-card lb-feature-hero lb-hover rounded-2xl p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em]" style="color: var(--lb-muted);">{{ __('O2O automation funnel') }}</p>
                    <h3 class="mt-2 text-2xl font-black">{{ __('Making Local HUB') }}</h3>
                </div>
                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-white" style="background: var(--lb-red);">
                    <i class="fa-light fa-bolt"></i>
                </span>
            </div>
            <div class="mt-6 grid gap-3">
                @foreach ($journeyFlow as $flow)
                    <div class="flex items-center gap-3 rounded-xl border bg-white/85 p-3" style="border-color: var(--lb-line);">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--lb-lime) 28%, #fff); color: #ff5f5f;">
                            <i class="fa-light {{ $flow[0] }}"></i>
                        </span>
                        <p class="text-sm font-bold leading-snug" style="color: var(--lb-muted);">{{ $flow[1] }}</p>
                    </div>
                @endforeach
            </div>
        </article>
    </div>
</section>

{{-- 02 Vision & Mission --}}
<section id="about-vision" class="lb-wrap lb-section lb-about-panel scroll-mt-28">
    <div class="text-center">
        <p class="lb-section-index">02</p>
        <span class="lb-pill mt-4 inline-flex items-center rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('Vision & Mission') }}</span>
        <h2 class="lb-serif lb-heading mx-auto mt-5 max-w-2xl">{{ __('Vision & Mission') }}</h2>
    </div>
    <div class="mt-10 grid gap-6 lg:grid-cols-2">
        <article class="lb-card lb-hover rounded-2xl p-6 sm:p-8">
            <div class="flex items-start gap-4">
                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-white" style="background: var(--lb-red);">
                    <i class="fa-light fa-compass"></i>
                </span>
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em]" style="color: var(--lb-muted);">{{ __('Vision: close the technology gap') }}</p>
                    <p class="lb-body mt-4" style="color: var(--lb-muted);">{{ __('Help every small spa or eatery operate with discipline that rivals large corporations.') }}</p>
                </div>
            </div>
        </article>
        <article class="lb-card lb-feature-hero lb-hover rounded-2xl p-6 sm:p-8">
            <div class="flex items-start gap-4">
                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" style="background: color-mix(in srgb, var(--lb-lime) 30%, #fff); color: #ff5f5f;">
                    <i class="fa-light fa-flag"></i>
                </span>
                <div>
                    <p class="lb-body font-bold" style="color: var(--lb-ink);">{{ __('Mission "Making Local HUB":') }}</p>
                    <p class="lb-caption mt-2" style="color: var(--lb-muted);">{{ __('Build grassroots strength for the local economy through four solid pillars:') }}</p>
                </div>
            </div>
        </article>
    </div>
    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($missionPillars as $pillar)
            <article class="lb-card lb-step-card lb-hover rounded-2xl p-5">
                <span class="lb-feature-icon relative z-10 inline-flex h-10 w-10 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--lb-lime) 30%, #fff); color: #ff5f5f;">
                    <i class="fa-light {{ $pillar[0] }}"></i>
                </span>
                <p class="relative z-10 mt-4 text-xs font-bold leading-snug sm:text-sm" style="color: var(--lb-muted);">{{ $pillar[1] }}</p>
            </article>
        @endforeach
    </div>
</section>

{{-- 03 Market challenges --}}
<section id="about-pain" class="lb-workflow-band lb-about-panel scroll-mt-28">
    <div class="lb-wrap">
        <div class="text-center">
            <span class="lb-pill inline-flex rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('Market challenges') }}</span>
            <p class="lb-section-index mt-6">03</p>
            <h2 class="lb-serif lb-heading mx-auto mt-3 max-w-2xl">{{ __('Market challenges') }}</h2>
            <p class="lb-body mx-auto mt-4 max-w-2xl" style="color: var(--lb-muted);">{{ __('Hundreds of thousands of local merchants in Da Nang are missing opportunities because of four major barriers:') }}</p>
        </div>
        <div class="mt-10 grid gap-4 sm:grid-cols-2">
            @foreach ($barriers as $barrier)
                <article class="lb-card lb-feature-row lb-hover rounded-2xl p-5">
                    <div class="relative z-10 flex items-start gap-4">
                        <span class="lb-feature-icon inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" style="background: color-mix(in srgb, var(--lb-red) 10%, #fff); color: var(--lb-red);">
                            <i class="fa-light {{ $barrier[0] }} text-xl"></i>
                        </span>
                        <div>
                            <h3 class="lb-card-title">{{ $barrier[1] }}</h3>
                            <p class="lb-caption mt-2" style="color: var(--lb-muted);">{{ $barrier[2] }}</p>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- 04 Solutions --}}
<section id="about-solutions" class="lb-wrap lb-section lb-about-panel scroll-mt-28">
    <div class="grid gap-10 lg:grid-cols-[0.82fr_1.18fr] lg:items-start">
        <div class="lg:sticky lg:top-28">
            <p class="lb-section-index">04</p>
            <span class="lb-pill mt-4 inline-flex items-center rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('Comprehensive solutions') }}</span>
            <h2 class="lb-serif lb-heading mt-5">{{ __('Comprehensive solutions') }}</h2>
            <p class="lb-body mt-4" style="color: var(--lb-muted);">{{ __('MLHUB focuses on solving root problems with three battle-ready tools:') }}</p>
            <div class="lb-proof-visual mt-8 rounded-2xl border p-5" style="border-color: var(--lb-line);">
                <div class="grid gap-3">
                    @foreach ($solutions as $node)
                        <div class="lb-proof-node flex items-center gap-3 rounded-2xl bg-white p-4">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--lb-red) 9%, #fff); color: var(--lb-red);">
                                <i class="fa-light {{ $node[0] }}"></i>
                            </span>
                            <p class="text-sm font-black">{{ $node[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="grid gap-4">
            @foreach ($solutions as $item)
                <article class="lb-card lb-feature-row lb-hover rounded-2xl p-6">
                    <div class="relative z-10 flex items-start gap-4">
                        <span class="lb-feature-icon inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" style="background: color-mix(in srgb, var(--lb-lime) 30%, #fff); color: #ff5f5f;">
                            <i class="fa-light {{ $item[0] }} text-xl"></i>
                        </span>
                        <div>
                            <h3 class="lb-card-title">{{ $item[1] }}</h3>
                            <p class="lb-caption mt-2" style="color: var(--lb-muted);">{{ $item[2] }}</p>
                        </div>
                    </div>
                </article>
            @endforeach
            <div class="flex justify-end">
                <a href="{{ route('guest.solutions') }}" class="inline-flex items-center gap-2 text-sm font-black transition hover:opacity-80" style="color: var(--lb-red);">
                    {{ __('See more other features') }}
                    <i class="fa-light fa-arrow-right-long" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- 05 Benefits --}}
<section id="about-benefits" class="lb-wrap lb-section lb-about-panel scroll-mt-28">
    <div class="text-center">
        <p class="lb-section-index">05</p>
        <span class="lb-pill mt-4 inline-flex items-center rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('Productivity digitization') }}</span>
        <h2 class="lb-serif lb-heading mx-auto mt-5 max-w-2xl">{{ __('Productivity digitization') }}</h2>
    </div>
    <div class="mt-10 grid gap-4 md:grid-cols-3">
        @foreach ($benefits as $benefit)
            <article class="lb-card lb-step-card lb-hover rounded-2xl p-6">
                <div class="relative z-10 flex items-center justify-between gap-4">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl text-white" style="background: var(--lb-red);">
                        <i class="fa-light {{ $benefit[0] }}"></i>
                    </span>
                    <span class="text-sm font-black" style="color: var(--lb-muted);">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                </div>
                <h3 class="relative z-10 mt-5 lb-card-title">{{ $benefit[1] }}</h3>
                <p class="relative z-10 mt-3 lb-caption" style="color: var(--lb-muted);">{{ $benefit[2] }}</p>
            </article>
        @endforeach
    </div>
</section>

{{-- 06 Journey --}}
<section id="about-journey" class="lb-wrap lb-section lb-about-panel scroll-mt-28 pb-8">
    <div class="lb-card lb-final-cta rounded-3xl p-6 sm:p-10">
        <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
            <div class="relative z-10">
                <p class="lb-section-index">06</p>
                <span class="lb-pill mt-4 inline-flex items-center rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('Revenue replication') }}</span>
                <h2 class="lb-serif lb-heading mt-5">{{ __('Revenue replication') }}</h2>
                <p class="lb-body mt-5" style="color: var(--lb-muted);">{{ __('Digital transformation is not about how much software you buy—it is about how you change the way you operate.') }}</p>
                <p class="lb-body mt-4" style="color: var(--lb-muted);">{{ __('MLHUB does not just sell tools—we give you a standard operating process (SOP). Turn walk-in traffic into owned data, strangers into regulars, and regulars into 5-star ambassadors for your store.') }}</p>
                <p class="lb-body mt-4" style="color: var(--lb-muted);">{{ __('With MLHUB, your small retail shop becomes a systematic business engine—ready to replicate revenue at any time.') }}</p>
                <a href="{{ $demoHref }}" class="mt-8 inline-flex items-center justify-center gap-2 rounded-full px-6 py-4 text-sm font-black text-white transition hover:opacity-90" style="background: var(--lb-red); box-shadow: inset 0 1px 0 rgba(255,255,255,.22), 0 22px 48px -30px rgba(255,95,95,.9);">
                    <i class="fa-light fa-rocket"></i>
                    {{ __('Start your free trial') }}
                </a>
            </div>
            <div class="lb-window relative z-10 overflow-hidden rounded-2xl">
                <div class="border-b px-5 py-4" style="border-color: var(--lb-line);">
                    <p class="text-xs font-black uppercase tracking-[0.18em]" style="color: var(--lb-muted);">{{ __('O2O automation funnel') }}</p>
                </div>
                <div class="p-5">
                    <div class="grid gap-2 sm:grid-cols-4">
                        @foreach ($journeyFlow as $flow)
                            <div class="flex flex-col items-center gap-2 rounded-xl border bg-white p-3 text-center" style="border-color: var(--lb-line);">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--lb-lime) 26%, #fff); color: #ff5f5f;">
                                    <i class="fa-light {{ $flow[0] }}"></i>
                                </span>
                                <span class="line-clamp-2 text-[10px] font-bold leading-tight">{{ $flow[1] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 rounded-xl border bg-white p-4" style="border-color: var(--lb-line);">
                        <div class="flex h-20 items-end gap-1.5">
                            @foreach ([36, 58, 44, 72, 54, 88, 68, 92, 76, 100] as $bar)
                                <span class="lb-bar flex-1 rounded-t-lg" style="--lb-bar-delay: {{ $loop->index * 120 }}ms; height: {{ $bar }}%; background: {{ $loop->even ? 'var(--lb-red)' : 'var(--lb-lime)' }};"></span>
                            @endforeach
                        </div>
                        <p class="mt-4 text-center text-xs font-bold" style="color: var(--lb-muted);">{{ __('Systematic business engine') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
