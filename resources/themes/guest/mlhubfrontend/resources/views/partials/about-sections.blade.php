<section id="about" class="lb-wrap lb-section lb-about">
    <div class="mb-10 text-center">
        <span class="lb-pill inline-flex items-center rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('About MLHUB') }}</span>
        <h1 class="lb-serif lb-heading mx-auto mt-5 max-w-3xl">{{ __('Making Local HUB for Vietnamese local businesses') }}</h1>
    </div>

    <div class="grid gap-6">
        <article id="about-what" class="lb-card lb-about-panel rounded-2xl p-6 sm:p-8">
            <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">01</p>
            <h2 class="lb-card-title mt-3">{{ __('What is MLHUB?') }}</h2>
            <p class="lb-body mt-4" style="color: var(--lb-muted);">{{ __('MLHUB is a Marketing Automation platform tailored for household businesses (SOHO), SMEs, and retail chains. Instead of feature overload, we deliver the most minimal, easy-to-use ecosystem. Every process to find, engage, and nurture customers is now fully automated.') }}</p>
        </article>

        <article id="about-vision" class="lb-card lb-about-panel rounded-2xl p-6 sm:p-8">
            <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">02</p>
            <h2 class="lb-card-title mt-3">{{ __('Vision & Mission') }}</h2>
            <p class="lb-body mt-4 font-bold" style="color: var(--lb-ink);">{{ __('Vision: close the technology gap') }}</p>
            <p class="lb-body mt-2" style="color: var(--lb-muted);">{{ __('Help every small spa or eatery operate with discipline that rivals large corporations.') }}</p>
            <p class="lb-body mt-5 font-bold" style="color: var(--lb-ink);">{{ __('Mission "Making Local HUB":') }}</p>
            <p class="lb-body mt-2" style="color: var(--lb-muted);">{{ __('Build grassroots strength for the local economy through four solid pillars:') }}</p>
            <ul class="lb-body mt-4 grid gap-2" style="color: var(--lb-muted);">
                <li class="flex gap-2"><i class="fa-light fa-circle-small mt-2 shrink-0 text-[#ff5f5f]"></i><span>{{ __('Continuously attract guests and grow revenue.') }}</span></li>
                <li class="flex gap-2"><i class="fa-light fa-circle-small mt-2 shrink-0 text-[#ff5f5f]"></i><span>{{ __('Continuously train and grow digital-ready staff.') }}</span></li>
                <li class="flex gap-2"><i class="fa-light fa-circle-small mt-2 shrink-0 text-[#ff5f5f]"></i><span>{{ __('Continuously care for and retain loyal customers.') }}</span></li>
                <li class="flex gap-2"><i class="fa-light fa-circle-small mt-2 shrink-0 text-[#ff5f5f]"></i><span>{{ __('Continuously standardize and build SOP processes.') }}</span></li>
            </ul>
        </article>

        <article id="about-pain" class="lb-card lb-about-panel rounded-2xl p-6 sm:p-8">
            <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">03</p>
            <h2 class="lb-card-title mt-3">{{ __('Market challenges') }}</h2>
            <p class="lb-body mt-4" style="color: var(--lb-muted);">{{ __('Hundreds of thousands of local merchants miss opportunities because of four major barriers:') }}</p>
            <ul class="lb-body mt-4 grid gap-3 sm:grid-cols-2" style="color: var(--lb-muted);">
                @foreach ([
                    [__('Finance'), __('Cannot afford expensive software systems.')],
                    [__('People'), __('No dedicated online marketing team.')],
                    [__('Technology'), __('Fear complex interfaces and tedious steps.')],
                    [__('Customers'), __('Invisible on Google Maps—watching tourists walk into rival stores.')],
                ] as $barrier)
                    <li class="rounded-xl border bg-white/80 p-4" style="border-color: var(--lb-line);">
                        <p class="text-sm font-black" style="color: var(--lb-ink);">{{ $barrier[0] }}</p>
                        <p class="lb-caption mt-2">{{ $barrier[1] }}</p>
                    </li>
                @endforeach
            </ul>
        </article>

        <article id="about-solutions" class="lb-card lb-about-panel rounded-2xl p-6 sm:p-8">
            <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">04</p>
            <h2 class="lb-card-title mt-3">{{ __('Comprehensive solutions') }}</h2>
            <p class="lb-body mt-4" style="color: var(--lb-muted);">{{ __('MLHUB focuses on solving root problems with three battle-ready tools:') }}</p>
            <ul class="mt-5 grid gap-4">
                @foreach ([
                    ['fa-globe', __('Create and manage domains'), __('Launch credible digital identity and trust.')],
                    ['fa-map-location-dot', __('Google Maps coverage'), __('Deploy omnichannel sync to capture walk-in search demand near your store.')],
                    ['fa-qrcode', __('Automation funnel'), __('Closed loop: scan QR for instant voucher → AI nurture messages → customers return to buy.')],
                ] as $item)
                    <li class="flex gap-4 rounded-xl border bg-white/80 p-4" style="border-color: var(--lb-line);">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--lb-lime) 28%, #fff); color: #ff5f5f;">
                            <i class="fa-light {{ $item[0] }}"></i>
                        </span>
                        <div>
                            <p class="text-sm font-black">{{ $item[1] }}</p>
                            <p class="lb-caption mt-1" style="color: var(--lb-muted);">{{ $item[2] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </article>

        <article id="about-benefits" class="lb-card lb-about-panel rounded-2xl p-6 sm:p-8">
            <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">05</p>
            <h2 class="lb-card-title mt-3">{{ __('Productivity digitization') }}</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                @foreach ([
                    [__('Hands-free operations'), __('AI handles repetitive work and frees you from manual bookkeeping.')],
                    [__('One-screen simplicity'), __('Everything on a single screen—even older users navigate smoothly.')],
                    [__('Real foot traffic'), __('Turn online search instantly into people walking through your door.')],
                    [__('Right-sized cost'), __('Start with a tiny investment; upgrade (Starter, Growth, Pro) when cash flow grows.')],
                ] as $benefit)
                    <div class="rounded-xl border bg-white/80 p-4" style="border-color: var(--lb-line);">
                        <p class="text-sm font-black">{{ $benefit[0] }}</p>
                        <p class="lb-caption mt-2" style="color: var(--lb-muted);">{{ $benefit[1] }}</p>
                    </div>
                @endforeach
            </div>
        </article>

        <article id="about-journey" class="lb-card lb-about-panel rounded-2xl p-6 sm:p-8">
            <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">06</p>
            <h2 class="lb-card-title mt-3">{{ __('Revenue replication') }}</h2>
            <div class="mt-5 space-y-5">
                <p class="lb-body" style="color: var(--lb-muted);">{{ __('Digital transformation is not about how much software you buy—it is about how you change the way you operate.') }}</p>
                <p class="lb-body" style="color: var(--lb-muted);">{{ __('MLHUB does not just sell tools; we give you a standard operating process (SOP). Combining automation with the "Making Local" philosophy, we turn your retail store into a systematic business engine—optimizing productivity end to end and ready to replicate revenue at any time.') }}</p>
            </div>
        </article>
    </div>
</section>
