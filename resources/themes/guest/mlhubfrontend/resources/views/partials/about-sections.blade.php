@php
    $demoHref = auth()->check() ? route('portal.dashboard') : route('register');
    $missionPillars = [
        ['fa-chart-line', __('Continuously attract guests and grow revenue.')],
        ['fa-graduation-cap', __('Continuously train and grow digital-ready staff.')],
        ['fa-heart', __('Continuously care for and retain loyal customers.')],
        ['fa-list-check', __('Continuously standardize and build SOP processes.')],
    ];
    $barriers = [
        ['fa-coins', __('Finance'), __('Cannot afford expensive software systems.')],
        ['fa-users', __('People'), __('No dedicated online marketing team.')],
        ['fa-laptop', __('Technology'), __('Fear complex interfaces and tedious steps.')],
        ['fa-map-location-dot', __('Customers'), __('Invisible on Google Maps—watching tourists walk into rival stores.')],
    ];
    $solutions = [
        ['fa-globe', __('Create and manage domains'), __('Launch credible digital identity and trust.')],
        ['fa-map-location-dot', __('Google Maps coverage'), __('Deploy omnichannel sync to capture walk-in search demand near your store.')],
        ['fa-qrcode', __('Automation funnel'), __('Closed loop: scan QR for instant voucher → AI nurture messages → customers return to buy.')],
    ];
    $benefits = [
        ['fa-robot', __('Hands-free operations'), __('AI handles repetitive work and frees you from manual bookkeeping.')],
        ['fa-display', __('One-screen simplicity'), __('Everything on a single screen—even older users navigate smoothly.')],
        ['fa-person-walking', __('Real foot traffic'), __('Turn online search instantly into people walking through your door.')],
        ['fa-tags', __('Right-sized cost'), __('Start with a tiny investment; upgrade (Starter, Growth, Pro) when cash flow grows.')],
    ];
@endphp

{{-- Hero --}}
<section id="about" class="lb-wrap lb-section lb-about scroll-mt-28">
    <div class="grid items-center gap-12 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)]">
        <div>
            <span class="lb-pill inline-flex items-center rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('About MLHUB') }}</span>
            <h1 class="lb-serif lb-hero-title mt-6">{{ __('Making Local HUB for Vietnamese local businesses') }}</h1>
            <p class="lb-lead mt-5 max-w-xl" style="color: var(--lb-muted);">
                {{ __('MLHUB is a Marketing Automation platform tailored for household businesses (SOHO), SMEs, and retail chains. Instead of feature overload, we deliver the most minimal, easy-to-use ecosystem. Every process to find, engage, and nurture customers is now fully automated.') }}
            </p>
            <div class="mt-8 flex flex-wrap gap-2">
                @foreach ([__('Making Local HUB'), __('Marketing Automation'), __('Local O2O'), __('Review Booster')] as $badge)
                    <span class="rounded-full border bg-white/90 px-3 py-2 text-xs font-black" style="border-color: var(--lb-line); color: var(--lb-muted);">{{ $badge }}</span>
                @endforeach
            </div>
        </div>

        <div class="lb-glow relative">
            <div class="lb-window relative z-10 overflow-hidden rounded-2xl">
                <div class="flex items-center justify-between border-b px-5 py-4" style="border-color: var(--lb-line); background: var(--lb-soft);">
                    <div class="flex items-center gap-1.5">
                        <span class="lb-dot bg-red-400"></span>
                        <span class="lb-dot bg-amber-400"></span>
                        <span class="lb-dot bg-lime-500"></span>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-black" style="background: color-mix(in srgb, var(--lb-lime) 30%, #fff); color: #ff5f5f;">{{ __('Live dashboard') }}</span>
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
                    <main class="p-5">
                        <p class="text-xs font-black uppercase tracking-[0.18em]" style="color: var(--lb-muted);">{{ __('Mission: Making Local HUB') }}</p>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ($missionPillars as $pillar)
                                <div class="rounded-xl border bg-white p-3" style="border-color: var(--lb-line);">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg" style="background: color-mix(in srgb, var(--lb-lime) 28%, #fff); color: #ff5f5f;">
                                        <i class="fa-light {{ $pillar[0] }}"></i>
                                    </span>
                                    <p class="mt-3 text-xs font-bold leading-snug" style="color: var(--lb-muted);">{{ $pillar[1] }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 rounded-xl border bg-white p-4" style="border-color: var(--lb-line);">
                            <div class="flex items-end gap-1.5 h-16">
                                @foreach ([48, 72, 58, 88, 64, 96, 78, 84] as $bar)
                                    <span class="lb-bar flex-1 rounded-t-lg" style="--lb-bar-delay: {{ $loop->index * 140 }}ms; height: {{ $bar }}%; background: {{ $loop->even ? 'var(--lb-red)' : 'var(--lb-lime)' }};"></span>
                                @endforeach
                            </div>
                        </div>
                    </main>
                </div>
            </div>
            <div class="lb-card lb-float absolute -left-4 bottom-16 z-20 hidden rounded-xl p-4 shadow-xl md:block" style="--lb-delay: 180ms;">
                <p class="text-xs font-black">{{ __('Vision: close the technology gap') }}</p>
                <p class="mt-1 text-xs" style="color: var(--lb-muted);">{{ __('Growth engine') }}</p>
            </div>
            <div class="lb-card lb-float absolute -right-3 top-12 z-20 hidden w-44 rounded-xl p-3 shadow-xl md:block" style="--lb-delay: 360ms;">
                <div class="flex gap-2.5">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-sm text-white" style="background: var(--lb-red);">
                        <i class="fa-light fa-store" aria-hidden="true"></i>
                    </span>
                    <p class="text-[11px] font-bold leading-snug" style="color: var(--lb-muted);">{{ __('Help every small spa or eatery operate with discipline that rivals large corporations.') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 01 + 02 --}}
<section class="lb-wrap lb-section pt-0">
    <div class="grid gap-6 lg:grid-cols-2">
        <article id="about-what" class="lb-card lb-feature-hero lb-about-panel lb-hover rounded-2xl p-6 sm:p-8">
            <p class="lb-section-index">01</p>
            <div class="mt-4 flex items-start justify-between gap-4">
                <h2 class="lb-card-title">{{ __('What is MLHUB?') }}</h2>
                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-white" style="background: var(--lb-red);">
                    <i class="fa-light fa-bolt"></i>
                </span>
            </div>
            <p class="lb-body mt-5" style="color: var(--lb-muted);">{{ __('MLHUB is a Marketing Automation platform tailored for household businesses (SOHO), SMEs, and retail chains. Instead of feature overload, we deliver the most minimal, easy-to-use ecosystem. Every process to find, engage, and nurture customers is now fully automated.') }}</p>
        </article>

        <article id="about-vision" class="lb-card lb-about-panel lb-hover rounded-2xl p-6 sm:p-8">
            <p class="lb-section-index">02</p>
            <h2 class="lb-card-title mt-4">{{ __('Vision & Mission') }}</h2>
            <p class="lb-body mt-4 font-bold" style="color: var(--lb-ink);">{{ __('Vision: close the technology gap') }}</p>
            <p class="lb-caption mt-2" style="color: var(--lb-muted);">{{ __('Help every small spa or eatery operate with discipline that rivals large corporations.') }}</p>
            <p class="lb-body mt-5 font-bold" style="color: var(--lb-ink);">{{ __('Mission "Making Local HUB":') }}</p>
            <p class="lb-caption mt-2" style="color: var(--lb-muted);">{{ __('Build grassroots strength for the local economy through four solid pillars:') }}</p>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                @foreach ($missionPillars as $pillar)
                    <div class="relative z-10 flex gap-3 rounded-xl border bg-white/80 p-3" style="border-color: var(--lb-line);">
                        <span class="lb-feature-icon inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--lb-lime) 30%, #fff); color: #ff5f5f;">
                            <i class="fa-light {{ $pillar[0] }}"></i>
                        </span>
                        <p class="text-xs font-bold leading-snug" style="color: var(--lb-muted);">{{ $pillar[1] }}</p>
                    </div>
                @endforeach
            </div>
        </article>
    </div>
</section>

{{-- 03 Market challenges --}}
<section id="about-pain" class="lb-workflow-band lb-about-panel scroll-mt-28">
    <div class="lb-wrap">
        <div class="text-center">
            <span class="lb-pill inline-flex rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('Market challenges') }}</span>
            <p class="lb-section-index mt-6">03</p>
            <h2 class="lb-serif lb-heading mx-auto mt-3 max-w-2xl">{{ __('Market challenges') }}</h2>
            <p class="lb-body mx-auto mt-4 max-w-2xl" style="color: var(--lb-muted);">{{ __('Hundreds of thousands of local merchants miss opportunities because of four major barriers:') }}</p>
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
    <div class="mt-10 grid gap-4 md:grid-cols-2">
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
                <p class="lb-body mt-4" style="color: var(--lb-muted);">{{ __('MLHUB does not just sell tools; we give you a standard operating process (SOP). Combining automation with the "Making Local" philosophy, we turn your retail store into a systematic business engine—optimizing productivity end to end and ready to replicate revenue at any time.') }}</p>
                <a href="{{ $demoHref }}" class="mt-8 inline-flex items-center justify-center gap-2 rounded-full px-6 py-4 text-sm font-black text-white transition hover:opacity-90" style="background: var(--lb-red); box-shadow: inset 0 1px 0 rgba(255,255,255,.22), 0 22px 48px -30px rgba(255,95,95,.9);">
                    <i class="fa-light fa-rocket"></i>
                    {{ __('Start your free trial') }}
                </a>
            </div>
            <div class="lb-window relative z-10 overflow-hidden rounded-2xl">
                <div class="border-b px-5 py-4" style="border-color: var(--lb-line);">
                    <p class="text-xs font-black uppercase tracking-[0.18em]" style="color: var(--lb-muted);">{{ __('Campaign workflow') }}</p>
                </div>
                <div class="p-5">
                    <div class="grid gap-2 sm:grid-cols-4">
                        @foreach ([['fa-bullseye-arrow', __('Choose your campaign goal')], ['fa-sparkles', __('AI Campaign Builder')], ['fa-qrcode', __('Create QR codes')], ['fa-chart-line', __('Reports')]] as $flow)
                            <div class="flex flex-col items-center gap-2 rounded-xl border bg-white p-3 text-center" style="border-color: var(--lb-line);">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--lb-lime) 26%, #fff); color: #ff5f5f;">
                                    <i class="fa-light {{ $flow[0] }}"></i>
                                </span>
                                <span class="line-clamp-2 text-[10px] font-bold leading-tight">{{ $flow[1] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 rounded-xl border bg-white p-4" style="border-color: var(--lb-line);">
                        <div class="flex items-end gap-1.5 h-20">
                            @foreach ([36, 58, 44, 72, 54, 88, 68, 92, 76, 100] as $bar)
                                <span class="lb-bar flex-1 rounded-t-lg" style="--lb-bar-delay: {{ $loop->index * 120 }}ms; height: {{ $bar }}%; background: {{ $loop->even ? 'var(--lb-red)' : 'var(--lb-lime)' }};"></span>
                            @endforeach
                        </div>
                        <p class="mt-4 text-center text-xs font-bold" style="color: var(--lb-muted);">{{ __('One campaign hub') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
