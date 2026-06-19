@php
    $pricing = \Pricing::plansWithFeatures();
    $planTypes = \Modules\AdminPlans\Facades\Plan::getTypes();
    $minCol = 3;
@endphp

<section x-data="{ type: {{ array_key_first($planTypes) }} }" class="pt-24 pb-32 bg-blueGray-50 overflow-visible relative z-20">
    <div class="container px-4 mx-auto mb-10">
        <h2 class="mb-6 text-6xl md:text-8xl xl:text-10xl font-bold font-heading tracking-px-n leading-none">
            {{ __("Pricing") }}
        </h2>
        <div class="mb-16 flex flex-wrap justify-between -m-4">
            <div class="w-auto p-4">
                <div class="md:max-w-md">
                    <p class="text-lg text-gray-900 font-medium leading-relaxed">
                        {{ __("Choose an affordable plan packed with top features to engage your audience, create loyalty, and boost sales.") }}
                    </p>
                </div>
            </div>
            {{-- Toggle button group --}}
            <div class="w-auto p-4">
                <div class="inline-flex items-center max-w-max gap-2">
                    @foreach($planTypes as $typeKey => $typeLabel)
                        <button 
                            type="button"
                            class="px-4 py-1 mx-1 rounded-full font-semibold transition text-gray-600"
                            :class="type == {{ $typeKey }} ? 'bg-indigo-600 text-white' : 'bg-gray-200 hover:bg-indigo-100'"
                            x-on:click="type={{ $typeKey }}"
                        >
                            {{ __($typeLabel) }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="overflow-hidden border border-blueGray-200 rounded-3xl bg-white bg-opacity-90">
            <div class="mlhub-pricing-grid flex flex-wrap">
                @foreach($planTypes as $typeKey => $typeLabel)
                    @php
                        $plans = $pricing[$typeKey] ?? [];
                        $planCount = count($plans);
                    @endphp

                    @foreach($plans as $index => $plan)

                        @php
                            $isFreePlan = $plan['free_plan'];
                        @endphp

                        <div class="w-full lg:w-1/3 lg:flex-1"
                             x-show="type == {{ $typeKey }}"
                             x-transition
                             style="display: none; z-index: {{ 10 - $index }}">

                            <div class="mlhub-pricing-card relative h-full px-9 pt-8 pb-11 {{ !empty($plan['featured']) ? 'is-featured' : '' }}" style="backdrop-filter: blur(46px);">

                                {{-- Ribbon Featured --}}
                                @if(!empty($plan['featured']))
                                    <div class="overflow-hidden absolute right-0 w-40 h-40 top-0">
                                        <div class="absolute top-6 -right-10 rotate-45">
                                            <span class="bg-indigo-600 text-white px-12 py-1 text-xs font-bold shadow-md uppercase">
                                                {{ __('Featured') }}
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                <span class="mb-3 inline-block text-sm text-indigo-600 font-semibold uppercase tracking-px leading-snug">
                                    {{ \Modules\AdminPlans\Support\CatalogLocalization::resolve($plan['name'] ?? '-') }}
                                </span>
                                @if(!($plan['free_plan'] ?? false) && (int) ($plan['trial_day'] ?? 0) > 0)
                                    <div class="mb-3">
                                        <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-amber-700">
                                            {{ __(':days-day trial', ['days' => (int) $plan['trial_day']]) }}
                                        </span>
                                    </div>
                                @endif
                                <p class="mb-6 text-gray-500 font-medium leading-relaxed">
                                    {{ \Modules\AdminPlans\Support\CatalogLocalization::resolve($plan['desc'] ?? '') }}
                                </p>
                                <h3 class="mb-1 text-4xl text-gray-900 font-bold leading-tight">
                                    @if($isFreePlan)
                                        {{ price(0) }}
                                    @else
                                        {{ price($plan['price'] ?? 0) }}
                                    @endif
                                    <span class="text-gray-400">/{{ strtolower($typeLabel) }}</span>
                                </h3>
                                <p class="mb-8 text-sm text-gray-500 font-medium leading-relaxed">
                                    {{ __("Billed") }} {{ $typeLabel }}
                                </p>

                                @if($isFreePlan)
                                    @php
                                        $planTarget = $plan['model']->slug ?? $plan['id'];
                                    @endphp
                                    <a href="{{ route('payment.index', $planTarget) }}" class="mb-9 py-4 px-9 w-full font-semibold rounded-xl text-indigo-600 bg-white hover:bg-indigo-200 border border-indigo-600 hover:text-white transition ease-in-out duration-200 text-center block">
	                                    {{ __("Start for Free") }}
	                                </a>
                                @else
                                    @php
                                        $planTarget = $plan['model']->slug ?? $plan['id'];
                                    @endphp
                                    <a href="{{ route('payment.index', $planTarget) }}" class="mb-9 py-4 px-9 w-full font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition ease-in-out duration-200 text-center block">
	                                    {{ __("Choose Plan") }}
	                                </a>
                                @endif
                                @php
                                    $outerFeatureKeys = \Modules\AdminPlans\Support\PlanFeatureOrder::outerFeatureKeys($plan['features'] ?? []);
                                    $promotedSubFeatures = \Modules\AdminPlans\Support\PlanFeatureOrder::promotedSubFeatures($plan['features'] ?? [], $outerFeatureKeys);
                                    $visibleFeatureKeys = \Modules\AdminPlans\Support\PlanFeatureOrder::visibleFeatureKeys($plan['features'] ?? [], $promotedSubFeatures);
                                    $visibleSubFeatureCount = \Modules\AdminPlans\Support\PlanFeatureOrder::visibleSubFeatureCountResolver($visibleFeatureKeys);
                                    $orderedPlanFeatures = \Modules\AdminPlans\Support\PlanFeatureOrder::orderedPublicFeatures(
                                        $plan['features'] ?? [],
                                        $promotedSubFeatures,
                                        $visibleSubFeatureCount,
                                        hideUrlShortener: true,
                                    );
                                @endphp
                                <ul>
                                    @foreach($orderedPlanFeatures as $feature)
                                        @php
                                            $featureLabel = $feature['label'] ?? $feature;
                                        @endphp
                                        <li class="mb-4 flex items-start gap-2">
                                            <i class="fa-regular fa-check {{ $feature['check'] ? 'text-green-600' : 'text-gray-500' }}"></i>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center justify-between gap-2 min-w-0">
                                                    <p class="font-semibold leading-normal min-w-0">{{ $featureLabel }}</p>
                                                    <div class="flex shrink-0 items-center gap-2">
                                                        @if(($feature['display'] ?? null) !== null && ($feature['display'] ?? '') !== '')
                                                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                                                                {{ $feature['display'] }}
                                                            </span>
                                                        @endif
                                                        @include('adminplans::components.mlhub-ai-feature-info', ['feature' => $feature, 'tone' => 'guest'])
                                                    </div>
                                                </div>
                                            </div>

                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                    @for($i = $planCount; $i < $minCol; $i++)
                        <div class="hidden lg:block lg:w-1/3 lg:flex-1"
                             x-show="type == {{ $typeKey }}"
                             style="display: none;"></div>
                    @endfor
                @endforeach
            </div>
        </div>
    </div>
    <p class="mb-4 text-sm text-gray-500 text-center font-medium leading-relaxed">
        {{ __("Trusted by secure payment service") }}
    </p>
    <div class="flex flex-wrap gap-2 justify-center">
        <div class="w-auto">
            <a href="#">
                <img class="h-24" src="{{ theme_public_asset('logos/brands/stripe.svg') }}" alt="Stripe">
            </a>
        </div>
        <div class="w-auto">
            <a href="#">
                <img class="h-24" src="{{ theme_public_asset('logos/brands/amex.svg') }}" alt="Amex">
            </a>
        </div>
        <div class="w-auto">
            <a href="#">
                <img class="h-24" src="{{ theme_public_asset('logos/brands/mastercard.svg') }}" alt="Mastercard">
            </a>
        </div>
        <div class="w-auto">
            <img class="h-24" src="{{ theme_public_asset('logos/brands/paypal.svg') }}" alt="Paypal">
        </div>
        <div class="w-auto">
            <a href="#">
                <img class="h-24" src="{{ theme_public_asset('logos/brands/visa.svg') }}" alt="Visa">
            </a>
        </div>
        <div class="w-auto">
            <a href="#">
                <img class="h-24" src="{{ theme_public_asset('logos/brands/apple-pay.svg') }}" alt="Apple Pay">
            </a>
        </div>
    </div>
</section>
