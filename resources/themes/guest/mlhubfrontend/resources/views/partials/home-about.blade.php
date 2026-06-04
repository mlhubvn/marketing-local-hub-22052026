<section id="about" class="lb-wrap lb-section lb-about">
    <div class="mb-10 text-center">
        <span class="lb-pill inline-flex items-center rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('About MLHUB') }}</span>
        <h2 class="lb-serif lb-heading mx-auto mt-5 max-w-3xl">{{ __('Making Local HUB for Vietnamese local businesses') }}</h2>
    </div>

    <div class="grid gap-6">
        <article id="about-what" class="lb-card lb-about-panel scroll-mt-28 rounded-2xl p-6 sm:p-8">
            <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">01</p>
            <h3 class="lb-card-title mt-3">{{ __('What is MLHUB?') }}</h3>
            <p class="lb-body mt-4" style="color: var(--lb-muted);">{{ __('MLHUB is an all-in-one Marketing Automation platform built for household businesses (SOHO), small and medium enterprises (SMEs), and retail chains. We turn complex technology into easy tools so local stores can attract and nurture customers automatically without technical expertise.') }}</p>
        </article>

        <article id="about-vision" class="lb-card lb-about-panel scroll-mt-28 rounded-2xl p-6 sm:p-8">
            <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">02</p>
            <h3 class="lb-card-title mt-3">{{ __('Vision & Mission') }}</h3>
            <p class="lb-body mt-4 font-bold" style="color: var(--lb-ink);">{{ __('Vision') }}</p>
            <p class="lb-body mt-2" style="color: var(--lb-muted);">{{ __("Become Vietnam's leading Marketing Automation platform so every business—even the smallest—can operate with the discipline of a large enterprise.") }}</p>
            <p class="lb-body mt-5 font-bold" style="color: var(--lb-ink);">{{ __('Mission: Making Local HUB') }}</p>
            <p class="lb-body mt-2" style="color: var(--lb-muted);">{{ __('Create lasting value for the local economy through four pillars: continuous revenue growth, digital workforce training, loyal customer retention, and standardized SOP processes.') }}</p>
        </article>

        <article id="about-pain" class="lb-card lb-about-panel scroll-mt-28 rounded-2xl p-6 sm:p-8">
            <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">03</p>
            <h3 class="lb-card-title mt-3">{{ __('Market challenges') }}</h3>
            <p class="lb-body mt-4" style="color: var(--lb-muted);">{{ __('Small merchants face four major barriers:') }}</p>
            <ul class="lb-body mt-4 grid gap-2 sm:grid-cols-2" style="color: var(--lb-muted);">
                <li class="flex gap-2"><i class="fa-light fa-circle-small mt-2 shrink-0 text-[#ff5f5f]"></i><span>{{ __('Limited budget for online marketing.') }}</span></li>
                <li class="flex gap-2"><i class="fa-light fa-circle-small mt-2 shrink-0 text-[#ff5f5f]"></i><span>{{ __('No dedicated marketing or IT staff.') }}</span></li>
                <li class="flex gap-2"><i class="fa-light fa-circle-small mt-2 shrink-0 text-[#ff5f5f]"></i><span>{{ __('Fear of complex software that is hard to use.') }}</span></li>
                <li class="flex gap-2"><i class="fa-light fa-circle-small mt-2 shrink-0 text-[#ff5f5f]"></i><span>{{ __('Missing walk-in and tourist traffic from Google Maps and search.') }}</span></li>
            </ul>
        </article>

        <article id="about-solutions" class="lb-card lb-about-panel scroll-mt-28 rounded-2xl p-6 sm:p-8">
            <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">04</p>
            <h3 class="lb-card-title mt-3">{{ __('Breakthrough solutions from MLHUB') }}</h3>
            <p class="lb-body mt-4" style="color: var(--lb-muted);">{{ __('Instead of feature overload, MLHUB delivers three revenue-ready solutions:') }}</p>
            <ul class="mt-5 grid gap-4">
                @foreach ([
                    ['fa-globe', __('Launch a .vn domain'), __('Build a credible digital storefront (e.g. .biz.vn).')],
                    ['fa-map-location-dot', __('Google Maps & omnichannel presence'), __('Landing pages synced with Facebook and TikTok.')],
                    ['fa-qrcode', __('Marketing automation'), __('QR scan → voucher → reminder messages to nurture and retain customers.')],
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

        <article id="about-benefits" class="lb-card lb-about-panel scroll-mt-28 rounded-2xl p-6 sm:p-8">
            <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">05</p>
            <h3 class="lb-card-title mt-3">{{ __('Core benefits') }}</h3>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                @foreach ([
                    [__('Operations'), __('Automate every touchpoint and free owners from manual tasks.')],
                    [__('Ease of use'), __('Minimal UI on one screen—suitable for every age.')],
                    [__('Instant revenue'), __('Turn map views into in-store visits.')],
                    [__('Flexible pricing'), __('Scale with Starter, Growth, and Pro plans as you grow.')],
                ] as $benefit)
                    <div class="rounded-xl border bg-white/80 p-4" style="border-color: var(--lb-line);">
                        <p class="text-sm font-black">{{ $benefit[0] }}</p>
                        <p class="lb-caption mt-2" style="color: var(--lb-muted);">{{ $benefit[1] }}</p>
                    </div>
                @endforeach
            </div>
        </article>

        <article id="about-journey" class="lb-card lb-about-panel scroll-mt-28 rounded-2xl p-6 sm:p-8">
            <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">06</p>
            <h3 class="lb-card-title mt-3">{{ __('Your MLHUB journey') }}</h3>
            <div class="mt-5 space-y-5">
                <p class="lb-body" style="color: var(--lb-muted);">{{ __('Your store starts invisible online with few walk-ins. Through Business Profiles, MLHUB connects to Google to optimize listings, publish posts, and sync reviews—so tourists find you on the map and walk in naturally. This builds Maps visibility and SEO without extra ad spend.') }}</p>
                <p class="lb-body" style="color: var(--lb-muted);">{{ __('When guests arrive, QR codes at tables plus AI-built coupon campaigns capture names and phone numbers instantly—no manual notes—solving budget and staffing limits with smart O2O marketing.') }}</p>
                <p class="lb-body" style="color: var(--lb-muted);">{{ __('Data flows into CRM for segmentation and tags. Review Booster routes happy guests to Google; Feedback Forms keep issues private. Automation and AI Chatbot send return visits—forming a closed-loop care system that changes how you operate.') }}</p>
                <p class="lb-body" style="color: var(--lb-muted);">{{ __("Finally, Dashboard and Reports show QR conversion, campaign performance, and team progress—so small shops run like enterprises, aligned with Da Nang's digital economy goals.") }}</p>
            </div>
        </article>
    </div>
</section>
