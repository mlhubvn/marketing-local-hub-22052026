@php
    $pricing = \Pricing::plansWithFeatures();
    $planTypes = collect(\Plan::getTypes())->filter(fn ($label, $key) => ! empty($pricing[$key] ?? []));
    $defaultType = (int) ($planTypes->keys()->first() ?? 1);
    $signupEnabled = auth_signup_enabled();
    $visiblePlanLimit = 3;
    $planCountsByType = $planTypes->mapWithKeys(
        fn ($label, $key): array => [$key => count($pricing[$key] ?? [])]
    )->all();
@endphp

@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    <style>
        .lb-pricing-hero {
            position: relative;
            isolation: isolate;
        }

        .lb-pricing-hero::before {
            content: "";
            position: absolute;
            inset: 4rem -4rem auto auto;
            z-index: -1;
            width: 28rem;
            height: 28rem;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(225, 235, 22, .22), transparent 68%);
            filter: blur(12px);
        }

        .lb-pricing-card {
            overflow: hidden;
            border-radius: 1.5rem;
        }

        .lb-pricing-card::before {
            content: "";
            position: absolute;
            inset-inline: 0;
            top: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--lb-red), var(--lb-lime));
            opacity: .18;
        }

        .lb-pricing-card.is-featured::before {
            opacity: 1;
        }

        .lb-pricing-card.lb-shimmer::after {
            display: none !important;
        }

        .lb-plan-desc {
            min-height: 3.75rem;
            line-height: 1.45;
        }

        .lb-plan-price {
            letter-spacing: -0.045em;
        }

        .lb-plan-feature {
            min-height: 1.75rem;
        }

        .lb-feature-value {
            background: color-mix(in srgb, var(--lb-lime) 28%, #fff);
            color: #506807;
        }

        .lb-plan-toggle {
            background: rgba(255, 255, 252, .9);
        }

        .lb-limit-panel {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 12% 18%, rgba(15, 118, 110, .11), transparent 18rem),
                radial-gradient(circle at 88% 80%, rgba(225, 235, 22, .22), transparent 18rem),
                rgba(255, 255, 252, .92);
        }

        .lb-limit-row {
            border-bottom: 1px solid var(--lb-line);
        }

        .lb-limit-row:last-child {
            border-bottom: 0;
        }

        .lb-limit-meter {
            position: relative;
            overflow: hidden;
        }

        .lb-limit-meter::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .7), transparent);
            transform: translateX(-120%);
            animation: lb-pricing-scan 3s ease-in-out infinite;
        }

        @keyframes lb-pricing-scan {
            0%, 42% { transform: translateX(-120%); }
            64%, 100% { transform: translateX(120%); }
        }

        @media (prefers-reduced-motion: reduce) {
            .lb-limit-meter::after {
                animation: none !important;
            }
        }

        html[data-theme-resolved='dark'] .lb-pricing-hero::before {
            opacity: .42;
            background: radial-gradient(circle, rgba(20, 163, 153, .18), transparent 70%);
        }

        html[data-theme-resolved='dark'] .lb-plan-toggle,
        html[data-theme-resolved='dark'] .lb-limit-panel,
        html[data-theme-resolved='dark'] .lb-pricing-card,
        html[data-theme-resolved='dark'] .lb-pricing-card [style*="255,255,255"],
        html[data-theme-resolved='dark'] .lb-pricing-card [style*="255, 255, 255"],
        html[data-theme-resolved='dark'] .lb-limit-panel [class*="bg-white"],
        html[data-theme-resolved='dark'] .lb-limit-panel [style*="#fff"] {
            border-color: rgba(96, 165, 250, .22) !important;
            background:
                linear-gradient(180deg, rgba(15, 23, 42, .92), rgba(11, 21, 38, .86)) !important;
            color: #e8eef7 !important;
            box-shadow: 0 28px 80px -58px rgba(0, 0, 0, .82) !important;
        }

        html[data-theme-resolved='dark'] .lb-feature-value,
        html[data-theme-resolved='dark'] .lb-pricing-card [style*="color: #506807"],
        html[data-theme-resolved='dark'] .lb-pricing-card [style*="color:#506807"] {
            background: rgba(184, 218, 22, .14) !important;
            color: #d9f75d !important;
        }

        html[data-theme-resolved='dark'] .lb-limit-meter::after {
            background: linear-gradient(90deg, transparent, rgba(94, 234, 212, .26), transparent);
        }
    </style>

    <div class="lb-page">
        <section
            class="lb-wrap lb-pricing-hero pb-16 pt-16 lg:pb-24 lg:pt-20"
            x-data="{
                type: {{ $defaultType }},
                showAllPlans: false,
                visibleLimit: {{ $visiblePlanLimit }},
                planCounts: @js($planCountsByType),
            }"
        >
            <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_26rem] lg:items-end">
                <div class="lb-reveal">
                    <span class="lb-pill inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-bold uppercase tracking-[0.18em]">
                        <i class="fa-light fa-credit-card"></i>
                        {{ __('Pricing') }}
                    </span>
                    <h1 class="lb-serif lb-hero-title mt-7 max-w-4xl">{{ __('Simple plans for growing local businesses') }}</h1>
                    <p class="lb-copy mt-6 max-w-2xl text-lg">{{ __('Choose the workspace size that fits your team. Manage campaign pages, QR codes, reviews, bookings, coupons, leads, AI credits and reporting from one connected platform.') }}</p>
                </div>

                @if ($planTypes->isNotEmpty())
                    <div class="lb-card lb-plan-toggle lb-reveal w-max max-w-full justify-self-start rounded-full p-1.5 lg:justify-self-end" style="--lb-delay: 120ms;">
                        <div class="inline-flex max-w-full flex-wrap gap-1">
                            @foreach ($planTypes as $typeKey => $typeLabel)
                                <button type="button" x-on:click="type = {{ $typeKey }}; showAllPlans = false" class="rounded-full px-5 py-3 text-sm font-black transition" x-bind:class="type === {{ $typeKey }} ? 'text-white' : 'text-neutral-500 hover:bg-neutral-100'" x-bind:style="type === {{ $typeKey }} ? 'background:#ff5f5f;' : ''">
                                    {{ $typeLabel }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-10 grid gap-5 lg:grid-cols-3">
                @foreach ($planTypes as $typeKey => $typeLabel)
                    @foreach (collect($pricing[$typeKey] ?? []) as $planIndex => $plan)
                        @php
                            $isFreePlan = (bool) ($plan['free_plan'] ?? false);
                            $planTarget = $plan['model']->slug ?? $plan['id'];
                            $outerFeatureKeys = \Modules\AdminPlans\Support\PlanFeatureOrder::outerFeatureKeys($plan['features'] ?? []);
                            $promotedSubFeatures = \Modules\AdminPlans\Support\PlanFeatureOrder::promotedSubFeatures($plan['features'] ?? [], $outerFeatureKeys);
                            $visibleFeatureKeys = \Modules\AdminPlans\Support\PlanFeatureOrder::visibleFeatureKeys($plan['features'] ?? [], $promotedSubFeatures);
                            $visibleSubFeatureCount = \Modules\AdminPlans\Support\PlanFeatureOrder::visibleSubFeatureCountResolver($visibleFeatureKeys);
                            $featureItems = \Modules\AdminPlans\Support\PlanFeatureOrder::orderedPublicFeatures(
                                $plan['features'] ?? [],
                                $promotedSubFeatures,
                                $visibleSubFeatureCount,
                            );
                        @endphp

                        <article x-cloak class="lb-card lb-pricing-card lb-hover lb-reveal relative flex h-full min-h-[34rem] flex-col p-6 {{ $plan['featured'] ? 'lb-shimmer is-featured' : '' }}" style="--lb-delay: {{ 80 * $planIndex }}ms;" x-show="type === {{ $typeKey }} && (showAllPlans || {{ $planIndex }} < visibleLimit)" x-transition>
                            @if ($plan['featured'])
                                <span class="absolute right-5 top-5 rounded-full px-3 py-1 text-xs font-black uppercase tracking-[0.14em]" style="background: var(--lb-lime); color: #334408;">{{ __('Featured') }}</span>
                            @endif

                            <p class="text-xs font-black uppercase tracking-[0.18em]" style="color: var(--lb-muted);">{{ \Modules\AdminPlans\Support\CatalogLocalization::resolve($plan['name'] ?? '-') }}</p>
                            <p class="lb-copy lb-plan-desc mt-5 text-sm">{{ $plan['desc'] ? \Modules\AdminPlans\Support\CatalogLocalization::resolve($plan['desc']) : __('A practical plan for local campaign pages, QR campaigns, AI copy, reports, and team usage.') }}</p>
                            <div class="mt-5">
                                <span class="lb-serif lb-plan-price text-4xl">
                                    {{ $isFreePlan ? format_money(0, $plan['currency'] ?? null) : format_money((float) ($plan['price'] ?? 0), $plan['currency'] ?? null) }}
                                </span>
                                <span class="text-sm font-bold" style="color: var(--lb-muted);">/{{ strtolower($typeLabel) }}</span>
                            </div>
                            <p class="mt-2 text-sm font-bold" style="color: var(--lb-muted);">{{ match((int) ($plan['type'] ?? 1)) { 2 => __('Billed yearly'), 3 => __('Pay once, use forever'), default => __('Billed monthly') } }}</p>

                            <a href="{{ $isFreePlan ? (auth()->check() ? route('portal.dashboard') : ($signupEnabled ? route('register') : route('login'))) : route('payment.index', $planTarget) }}" class="{{ $plan['featured'] ? 'lb-button' : 'lb-button-soft' }} mt-7 inline-flex w-full items-center justify-center px-5 py-4 text-sm font-black">
                                {{ $isFreePlan ? __('Start for Free') : __('Choose Plan') }}
                            </a>

                            <div class="mt-8 rounded-[1.25rem] border px-4 py-4" style="border-color: var(--lb-line); background-color: rgba(255,255,255,0.72);">
                                <p class="text-sm font-black">{{ __('Included in this package') }}</p>
                                <p class="mt-2 text-xs leading-6" style="color: var(--lb-muted);">{{ __('Feature access, usage limits, permissions, and controls configured for this tier.') }}</p>
                            </div>

                            <div class="mt-6 flex-1 space-y-3">
                                @foreach ($featureItems as $feature)
                                    <div class="rounded-[1rem] border px-4 py-3.5" style="border-color: var(--lb-line); background-color: rgba(255,255,255,0.78);">
                                        <div class="flex items-center justify-between gap-4">
                                            <div class="flex min-w-0 items-center gap-3">
                                                <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full" style="background: {{ ($feature['check'] ?? true) ? 'rgba(16,185,129,0.10)' : 'rgba(107,114,128,0.12)' }}; color: {{ ($feature['check'] ?? true) ? 'var(--lb-red)' : 'var(--lb-muted)' }};">
                                                    <i class="fa-light {{ ($feature['check'] ?? true) ? 'fa-check' : 'fa-minus' }} text-[11px]"></i>
                                                </span>
                                                <span class="truncate text-sm font-bold">{{ $feature['label'] }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                @if(($feature['display'] ?? null) !== null && ($feature['display'] ?? '') !== '')
                                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-black" style="background: color-mix(in srgb, var(--lb-lime) 28%, #fff); color: #506807;">
                                                        {{ $feature['display'] }}
                                                    </span>
                                                @endif
                                                @include('adminplans::components.mlhub-ai-feature-info', ['feature' => $feature, 'tone' => 'guest'])
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                @endforeach
            </div>

            <div class="mt-8 flex justify-center" x-cloak x-show="(planCounts[type] || 0) > visibleLimit">
                <button
                    type="button"
                    class="lb-button-soft inline-flex items-center gap-2 rounded-full px-6 py-3.5 text-sm font-black transition hover:opacity-90"
                    x-on:click="showAllPlans = ! showAllPlans"
                    x-bind:aria-expanded="showAllPlans"
                >
                    <span x-text="showAllPlans ? @js(__('Show fewer plans')) : @js(__('View all plans'))"></span>
                    <i class="fa-light text-xs transition" x-bind:class="showAllPlans ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>
            </div>

            <div class="lb-card lb-limit-panel lb-reveal mt-12 rounded-3xl p-6 sm:p-8" style="--lb-delay: 240ms;">
                <div class="grid gap-8 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
                    <div>
                        <span class="lb-pill inline-flex rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('Plan controls') }}</span>
                        <h2 class="lb-serif mt-5 text-4xl leading-none sm:text-5xl">{{ __('Scale usage without changing tools') }}</h2>
                        <p class="lb-copy mt-5 text-base">{{ __('Plans control the limits that matter for local marketing teams: businesses, campaigns, landing pages, QR codes, AI usage, team seats and branding.') }}</p>
                    </div>
                    <div class="rounded-2xl border bg-white/80 p-5" style="border-color: var(--lb-line);">
                        @foreach ([
                            ['fa-store', __('Businesses'), __('Locations and client workspaces'), '78%'],
                            ['fa-browser', __('Campaign pages'), __('Review, booking, coupon, feedback and lead pages'), '88%'],
                            ['fa-sparkles', __('AI credits'), __('Campaign copy, review replies and content writing'), '64%'],
                            ['fa-users', __('Team members'), __('Invite staff and manage shared workflows'), '72%'],
                            ['fa-badge-check', __('Branding control'), __('Remove branding on higher plans'), '54%'],
                        ] as $limit)
                            <div class="lb-limit-row py-4 first:pt-0 last:pb-0">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--lb-red) 9%, #fff); color: var(--lb-red);">
                                        <i class="fa-light {{ $limit[0] }}"></i>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="truncate text-sm font-black">{{ $limit[1] }}</p>
                                            <span class="text-xs font-black" style="color: var(--lb-muted);">{{ $limit[3] }}</span>
                                        </div>
                                        <p class="mt-1 text-xs" style="color: var(--lb-muted);">{{ $limit[2] }}</p>
                                        <span class="lb-limit-meter mt-3 block h-2 rounded-full" style="width: {{ $limit[3] }}; background: linear-gradient(90deg, var(--lb-red), var(--lb-lime));"></span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>
@endcomponent
