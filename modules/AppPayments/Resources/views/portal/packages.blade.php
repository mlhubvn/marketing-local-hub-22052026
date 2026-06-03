@component(theme_view('layouts.app', 'app'), ['title' => __('Packages')])
    <div class="space-y-6">
        <x-ui.sub-header
            :eyebrow="__('User portal')"
            :title="__('Packages')"
            :description="__('Browse available plans, compare included features, and choose the option that fits your needs.')"
        >
            <x-slot:actions>
                <x-ui.button href="{{ route('portal.billing') }}" variant="outline" wire:navigate>
                    {{ __('Open billing') }}
                </x-ui.button>
            </x-slot:actions>
        </x-ui.sub-header>

        <x-ui.metric-strip
            :items="[
                ['label' => __('Plans'), 'value' => format_number_locale($summary['total']), 'description' => __('Packages currently available in the subscription catalog.'), 'progress' => 100, 'tone' => 'var(--theme-accent)'],
                ['label' => __('Featured'), 'value' => format_number_locale($summary['featured']), 'description' => __('Plans highlighted as the recommended upgrade path.'), 'progress' => $summary['total'] > 0 ? (int) round(($summary['featured'] / $summary['total']) * 100) : 0, 'tone' => 'var(--theme-success-color)'],
                ['label' => __('Starting at'), 'value' => $summary['startingAt'] !== null ? format_money((float) $summary['startingAt'], $user?->plan?->currency) : __('N/A'), 'description' => __('Lowest paid package price currently available.'), 'progress' => 100, 'tone' => 'var(--theme-warning-color)'],
            ]"
            :show-icons="false"
            columns="md:grid-cols-3"
        />

        <x-ui.card class="overflow-visible p-0">
            <div x-data="{ type: {{ $defaultType }} }">
                <div class="border-b px-6 py-5 sm:px-8" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-3xl">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Plan catalog') }}</p>
                            <h2 class="mt-2 text-[1.8rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Choose the package that fits your needs') }}</h2>
                            <p class="mt-3 text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('All plans use the same pricing setup, so feature limits, availability, and checkout details stay consistent.') }}</p>
                        </div>

                        @if ($planTypes->isNotEmpty())
                            <div class="inline-flex items-center gap-1 rounded-full border p-1.5 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.72);">
                                @foreach ($planTypes as $typeKey => $typeLabel)
                                    <button
                                        type="button"
                                        x-on:click="type = {{ $typeKey }}"
                                        x-bind:class="type === {{ $typeKey }} ? 'text-white shadow-[0_12px_28px_-18px_rgba(var(--theme-accent-rgb),0.58)]' : ''"
                                        x-bind:style="type === {{ $typeKey }}
                                            ? 'background-color: var(--theme-accent);'
                                            : 'color: var(--theme-muted-text-color);'"
                                        class="rounded-full px-5 py-2.5 text-sm font-semibold transition"
                                    >
                                        {{ $typeLabel }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="px-6 py-6 sm:px-8">
                    <div class="space-y-6">
                        @foreach ($planTypes as $typeKey => $typeLabel)
                            @php
                                $plansForType = collect($pricing[$typeKey] ?? []);
                            @endphp
                            <div x-cloak x-show="type === {{ $typeKey }}" x-transition.opacity.duration.200ms style="display: none;">
                                <div class="grid gap-5 xl:grid-cols-3">
                                    @foreach ($plansForType as $plan)
                                        @php
                                            $planTarget = $plan['model']->slug ?? $plan['id'];
                                            $isCurrentPlan = (int) ($user?->plan_id ?? 0) === (int) $plan['id'];
                                            $ctaLabel = $isCurrentPlan
                                                ? __('Current plan')
                                                : ($plan['free_plan'] ? __('Start for Free') : __('Choose Plan'));
                                        @endphp
                                        <article class="relative flex h-full flex-col overflow-visible rounded-[1.8rem] border p-6 {{ $plan['featured'] ? 'shadow-[0_28px_72px_-44px_rgba(var(--theme-accent-rgb),0.4)]' : 'shadow-[0_22px_50px_-38px_rgba(15,23,42,0.16)]' }}" style="border-color: {{ $plan['featured'] ? 'rgba(var(--theme-accent-rgb),0.22)' : 'rgba(var(--theme-border-color-rgb),0.68)' }}; background: {{ $plan['featured'] ? 'linear-gradient(180deg, rgba(var(--theme-accent-rgb),0.10) 0%, rgba(var(--theme-surface-base-rgb,255,255,255),0.96) 36%)' : 'rgba(var(--theme-surface-base-rgb,255,255,255),0.96)' }};">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="background-color: {{ $plan['featured'] ? 'rgba(var(--theme-accent-rgb),0.12)' : 'rgba(var(--theme-border-color-rgb),0.10)' }}; color: {{ $plan['featured'] ? 'var(--theme-accent)' : 'var(--theme-muted-text-color)' }};">
                                                    {{ \Modules\AdminPlans\Support\CatalogLocalization::resolve($plan['name']) }}
                                                </span>
                                                @if (!($plan['free_plan'] ?? false) && (int) ($plan['trial_day'] ?? 0) > 0)
                                                    <x-ui.badge variant="warning">{{ __(':days-day trial', ['days' => (int) $plan['trial_day']]) }}</x-ui.badge>
                                                @endif
                                            </div>
                                            <div class="flex flex-wrap items-center justify-end gap-2">
                                                @if ($plan['featured'])
                                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb),0.18); background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                                                        {{ __('Most popular') }}
                                                    </span>
                                                @elseif ($isCurrentPlan)
                                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(16,185,129,0.22); background-color: rgba(16,185,129,0.08); color: var(--theme-success-color);">
                                                        {{ __('Active') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <p class="mt-6 text-sm leading-7" style="color: var(--theme-muted-text-color);">
                                            {{ $plan['desc'] ? \Modules\AdminPlans\Support\CatalogLocalization::resolve($plan['desc']) : __('A practical plan with the core features, limits, and access controls for this tier.') }}
                                        </p>

                                        <div class="mt-8">
                                            <p class="text-[3rem] font-semibold leading-none tracking-[-0.07em]" style="color: var(--theme-header-text-color);">
                                                {{ $plan['free_plan'] ? format_money(0, $plan['currency']) : format_money((float) $plan['price'], $plan['currency']) }}
                                                <span class="text-lg font-semibold tracking-normal" style="color: var(--theme-muted-text-color);">/{{ strtolower($typeLabel) }}</span>
                                            </p>
                                            <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ $plan['currency'] }} · {{ $plan['currency_name'] }}</p>
                                            <p class="mt-3 text-sm font-medium" style="color: var(--theme-muted-text-color);">{{ match((int) $plan['type']) {2 => __('Billed yearly'), 3 => __('Pay once, use forever'), default => __('Billed monthly')} }}</p>
                                        </div>

                                        <div class="mt-8">
                                            @if ($isCurrentPlan)
                                                <x-ui.button href="{{ route('portal.billing') }}" variant="outline" class="w-full justify-center" wire:navigate>{{ $ctaLabel }}</x-ui.button>
                                            @else
                                                <x-ui.button href="{{ $plan['free_plan'] ? route('portal.dashboard') : route('payment.index', $planTarget) }}" class="w-full justify-center">{{ $ctaLabel }}</x-ui.button>
                                            @endif
                                        </div>

                                        <div class="mt-8 rounded-[1.25rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.72);">
                                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Included in this package') }}</p>
                                            <p class="mt-2 text-xs leading-6" style="color: var(--theme-muted-text-color);">{{ __('Feature access, usage limits, permissions, and controls configured for this tier.') }}</p>
                                        </div>

                                        @php
                                            $outerFeatureLabels = collect($plan['features'] ?? [])
                                                ->map(fn ($item) => strtolower(trim((string) ($item['label'] ?? $item))))
                                                ->filter()
                                                ->all();
                                            $promotedSubFeatureLabels = [
                                                'max. bio pages',
                                                'remove link bio branding',
                                                'credits',
                                                'max qr codes',
                                                'monthly qr scans',
                                                'dynamic rules per qr',
                                                'qr bulk generation',
                                                'max bulk rows',
                                                'short link quota',
                                                'monthly clicks',
                                                'bulk import',
                                                'bulk rows per import',
                                                'analytics retention',
                                                'custom short codes',
                                                'password links',
                                                'expiration and click caps',
                                                'advanced routing',
                                                'api and webhooks',
                                                'api keys',
                                                'webhooks',
                                            ];
                                            $promotedSubFeatures = collect($plan['features'] ?? [])
                                                ->flatMap(fn ($item) => collect($item['subfeature'] ?? [])->flatMap(fn ($group) => $group['items'] ?? []))
                                                ->filter(function ($sub) use ($outerFeatureLabels, $promotedSubFeatureLabels) {
                                                    $label = strtolower(trim((string) ($sub['label'] ?? '')));
                                                    return in_array($label, $promotedSubFeatureLabels, true) && ! in_array($label, $outerFeatureLabels, true);
                                                })
                                                ->values();
                                            $visibleFeatureLabels = array_values(array_unique(array_merge(
                                                $outerFeatureLabels,
                                                $promotedSubFeatures->map(fn ($item) => strtolower(trim((string) ($item['label'] ?? ''))))->all()
                                            )));
                                            $visibleSubFeatureCount = function (array $item) use (&$visibleFeatureLabels): int {
                                                return collect($item['subfeature'] ?? [])
                                                    ->flatMap(fn ($group) => $group['items'] ?? [])
                                                    ->filter(function ($sub) use ($visibleFeatureLabels) {
                                                        $label = strtolower(trim((string) ($sub['label'] ?? '')));

                                                        return $label !== ''
                                                            && ! in_array($label, $visibleFeatureLabels, true)
                                                            && stripos($label, 'approval') === false
                                                            && stripos($label, 'approvals') === false;
                                                    })
                                                    ->count();
                                            };
                                            $featureOrder = [
                                                'access features' => 10,
                                                'localboost ai' => 11,
                                                'businesses' => 12,
                                                'campaigns' => 13,
                                                'landing pages' => 14,
                                                'qr codes' => 15,
                                                'templates' => 16,
                                                'remove branding' => 17,
                                                'link bio' => 20,
                                                'max. bio pages' => 21,
                                                'link bio templates' => 22,
                                                'ai bio assistant' => 23,
                                                'link bio analytics' => 24,
                                                'link bio a/b testing' => 25,
                                                'remove link bio branding' => 26,
                                                'advanced qr codes' => 30,
                                                'max qr codes' => 31,
                                                'monthly qr scans' => 32,
                                                'dynamic rules per qr' => 33,
                                                'qr bulk generation' => 34,
                                                'max bulk rows' => 35,
                                                'pre-printed qr' => 36,
                                                'ai qr codes' => 37,
                                                'credits per ai qr request' => 38,
                                                'brand kit' => 50,
                                                'custom domains' => 51,
                                                'tracking pixels' => 52,
                                                'utm presets' => 53,
                                                'short links' => 54,
                                                'monthly clicks' => 55,
                                                'bulk import' => 56,
                                                'bulk rows per import' => 57,
                                                'api and webhooks' => 58,
                                                'advanced routing' => 59,
                                                'ai studio' => 60,
                                                'url shortener' => 61,
                                                'team member' => 70,
                                                'credits' => 80,
                                                'premium support' => 90,
                                            ];
                                            $orderedPlanFeatures = collect($plan['features'] ?? [])
                                                ->merge($promotedSubFeatures)
                                                ->filter(function ($item) use ($visibleSubFeatureCount) {
                                                    $label = strtolower(trim((string) ($item['label'] ?? $item)));
                                                    if ($label === '' || stripos($label, 'approval') !== false || stripos($label, 'approvals') !== false) {
                                                        return false;
                                                    }

                                                    if ($label === 'url shortener') {
                                                        return false;
                                                    }

                                                    if (($item['key'] ?? null) === 'access_feature' && $visibleSubFeatureCount($item) === 0) {
                                                        return false;
                                                    }

                                                    if (($item['type'] ?? null) === 'group' && $visibleSubFeatureCount($item) === 0) {
                                                        return false;
                                                    }

                                                    return true;
                                                })
                                                ->sortBy(fn ($item) => $featureOrder[strtolower(trim((string) ($item['label'] ?? $item)))] ?? 80)
                                                ->values();
                                        @endphp

                                        <div class="mt-6 flex-1 space-y-3">
                                            @foreach ($orderedPlanFeatures as $feature)
                                                @continue(is_string($feature['label'] ?? null) && (stripos($feature['label'], 'approval') !== false || stripos($feature['label'], 'approvals') !== false))
                                                <div class="flex items-center justify-between gap-4 rounded-[1rem] border px-4 py-3.5" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                                                    <div class="flex min-w-0 items-center gap-3">
                                                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $feature['check'] ? '' : '' }}" style="background-color: {{ $feature['check'] ? 'rgba(16,185,129,0.10)' : 'rgba(var(--theme-border-color-rgb),0.12)' }}; color: {{ $feature['check'] ? 'var(--theme-success-color)' : 'var(--theme-muted-text-color)' }};">
                                                            <i class="fa-light {{ $feature['check'] ? 'fa-check' : 'fa-minus' }} text-[11px]"></i>
                                                        </span>
                                                        <span class="truncate text-sm font-medium" style="color: var(--theme-header-text-color);">{{ $feature['label'] }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        @if(($feature['display'] ?? null) !== null && ($feature['display'] ?? '') !== '')
                                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold" style="background-color: rgba(var(--theme-border-color-rgb),0.12); color: var(--theme-muted-text-color);">
                                                                {{ $feature['display'] }}
                                                            </span>
                                                        @endif
                                                        @if (!empty($feature['subfeature']))
                                                            <div x-data="{ open: false }" class="relative">
                                                                <button type="button" x-on:mouseenter="open = true" x-on:mouseleave="open = false" class="inline-flex h-7 w-7 items-center justify-center rounded-full transition" style="background-color: rgba(var(--theme-border-color-rgb),0.12); color: var(--theme-muted-text-color);">
                                                                    <i class="fa-light fa-info text-[11px]"></i>
                                                                </button>
                                                                <div x-show="open" x-transition.opacity.duration.150ms x-on:mouseenter="open = true" x-on:mouseleave="open = false" class="nova-scrollbar absolute right-0 top-full z-30 mt-3 max-h-[28rem] w-[20rem] overflow-y-auto overflow-x-hidden rounded-[1.15rem] border p-4 pr-3 shadow-[0_30px_80px_-40px_rgba(15,23,42,0.45)]" style="display: none; border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-overlay); scrollbar-gutter: stable;">
                                                                    @foreach (($feature['subfeature'] ?? []) as $tabGroup)
                                                                        @php
                                                                            $visibleSubItems = collect($tabGroup['items'] ?? [])
                                                                                ->filter(function ($sub) use ($visibleFeatureLabels) {
                                                                                    $label = strtolower(trim((string) ($sub['label'] ?? '')));
                                                                                    return $label !== ''
                                                                                        && ! in_array($label, $visibleFeatureLabels, true)
                                                                                        && stripos($label, 'approval') === false
                                                                                        && stripos($label, 'approvals') === false;
                                                                                });
                                                                        @endphp
                                                                        @continue($visibleSubItems->isEmpty())
                                                                        <div class="mb-4 last:mb-0">
                                                                            <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ $tabGroup['tab_name'] }}</p>
                                                                            <div class="space-y-2">
                                                                                @foreach ($visibleSubItems as $sub)
                                                                                    <div class="flex items-center justify-between gap-3 rounded-[0.9rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                                                                                        <div class="flex min-w-0 items-center gap-2">
                                                                                            <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full" style="background-color: {{ $sub['check'] ? 'rgba(16,185,129,0.10)' : 'rgba(var(--theme-border-color-rgb),0.12)' }}; color: {{ $sub['check'] ? 'var(--theme-success-color)' : 'var(--theme-muted-text-color)' }};">
                                                                                                <i class="fa-light {{ $sub['check'] ? 'fa-check' : 'fa-minus' }} text-[10px]"></i>
                                                                                            </span>
                                                                                            <span class="truncate text-xs font-medium" style="color: var(--theme-header-text-color);">{{ $sub['label'] }}</span>
                                                                                        </div>
                                                                                        @if(($sub['display'] ?? null) !== null && ($sub['display'] ?? '') !== '')
                                                                                            <span class="inline-flex items-center rounded-full px-2 py-1 text-[10px] font-semibold" style="background-color: rgba(var(--theme-border-color-rgb),0.12); color: var(--theme-muted-text-color);">{{ $sub['display'] }}</span>
                                                                                        @endif
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </article>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>
@endcomponent
