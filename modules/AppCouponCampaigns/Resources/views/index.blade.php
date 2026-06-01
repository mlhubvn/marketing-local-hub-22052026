<div
    class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6"
    x-data="{ couponDialogOpen: false }"
    x-on:coupon-campaign-saved.window="couponDialogOpen = false"
>
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif
    @include('applandingpages::partials.growth-tool-created-actions')

    @php
        $promoContentUrl = route('portal.ai-content', array_filter([
            'business_id' => $business_id ?: (string) optional($businesses->first())->id,
            'type' => 'coupon_message',
            'goal' => 'Promote a local coupon campaign and drive customer claims.',
            'offer' => '20% off next visit this weekend',
            'target_customer' => 'New and returning local customers',
            'details' => 'Write short Facebook, WhatsApp, SMS, and email-friendly copy for a coupon offer.',
            'source_type' => 'coupon',
        ], fn ($value) => filled($value)));
    @endphp

    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 34%),
        linear-gradient(35deg, rgba(var(--theme-warning-color-rgb),0.10), transparent 38%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-6 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-md border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-ticket"></i>{{ __('Growth Tools') }}
                </div>
                <h1 class="mt-4 max-w-3xl text-[2.25rem] font-semibold leading-[1.04] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('Coupons') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ __('Create local offers, collect customer claims, issue unique coupon codes, and track in-store redemptions.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button type="button" size="lg" x-on:click="couponDialogOpen = true">
                        <i class="fa-light fa-plus"></i>{{ __('Create coupon campaign') }}
                    </x-ui.button>
                    <x-ui.button href="{{ $promoContentUrl }}" wire:navigate variant="outline" size="lg">
                        <i class="fa-light fa-pen-nib"></i>{{ __('Generate promo message') }}
                    </x-ui.button>
                    @if ($campaigns->isNotEmpty())
                        <x-ui.button href="{{ $campaigns->first()->publicUrl() }}" target="_blank" variant="outline" size="lg">
                            <i class="fa-light fa-arrow-up-right"></i>{{ __('Open latest coupon') }}
                        </x-ui.button>
                    @endif
                </div>
            </div>

            <div class="rounded-[1.15rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Coupon performance') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Claims converted into redemptions') }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-chart-simple"></i>
                    </span>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-2">
                    @foreach ([
                        ['label' => __('Claimed'), 'value' => $stats['claimed']],
                        ['label' => __('Used'), 'value' => $stats['used']],
            ['label' => __('Rate'), 'value' => $stats['redemption_rate'].'%'],
                    ] as $item)
                        <div class="rounded-lg border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                            <p class="text-xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $item['value'] }}</p>
                            <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $item['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['label' => __('Coupon Campaigns'), 'value' => $stats['campaigns'], 'description' => __('Published offers'), 'icon' => 'fa-light fa-bullhorn'],
            ['label' => __('Visits'), 'value' => $stats['visits'], 'description' => __('QR scans & link visits'), 'icon' => 'fa-light fa-chart-line'],
            ['label' => __('Coupon Claims'), 'value' => $stats['claims'], 'description' => __('Customers claimed codes'), 'icon' => 'fa-light fa-ticket'],
            ['label' => __('Coupons Used'), 'value' => $stats['used'], 'description' => __('Marked redeemed'), 'icon' => 'fa-light fa-badge-check'],
            ['label' => __('Redemption Rate'), 'value' => $stats['redemption_rate'].'%', 'description' => __('Used / claims'), 'icon' => 'fa-light fa-percent'],
        ] as $metric)
            <article class="relative overflow-hidden rounded-[1.1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background: linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.07), transparent 44%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <span class="absolute inset-x-0 top-0 h-1" style="background-color: var(--theme-accent);"></span>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[2rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ is_numeric($metric['value']) ? format_number_locale((float) $metric['value']) : $metric['value'] }}</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $metric['description'] }}</p>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="{{ $metric['icon'] }}"></i>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section
        x-data="{ couponTab: 'campaigns' }"
        class="overflow-visible rounded-[1.25rem] border"
        style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);"
    >
        <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <div>
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Coupon workspace') }}</p>
                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Manage offers, claims, redemption workflow, and reusable offer ideas in one place.') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2 overflow-x-auto border-b p-2" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            @foreach ([
                'campaigns' => __('Campaigns'),
                'claims' => __('Claims'),
                'workflow' => __('Workflow'),
            ] as $tabKey => $tabLabel)
                <button
                    type="button"
                    x-on:click="couponTab = @js($tabKey)"
                    class="whitespace-nowrap rounded-xl border px-4 py-2 text-sm font-semibold transition"
                    :style="couponTab === @js($tabKey)
                        ? 'border-color: rgba(var(--theme-accent-rgb),0.16); background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);'
                        : 'border-color: transparent; color: var(--theme-muted-text-color);'"
                >{{ $tabLabel }}</button>
            @endforeach
        </div>

        <div x-show="couponTab === 'campaigns'">
            <div class="flex flex-col gap-3 border-b px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Coupon campaigns') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Offers customers can claim from public coupon pages.') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <x-ui.select wire:model.live="campaignsPerPage" name="coupon_campaigns_per_page">
                        <option value="10">{{ __('10 / page') }}</option>
                        <option value="25">{{ __('25 / page') }}</option>
                        <option value="50">{{ __('50 / page') }}</option>
                    </x-ui.select>
                    <x-ui.button type="button" size="sm" x-on:click="couponDialogOpen = true">
                        <i class="fa-light fa-plus"></i>{{ __('New coupon campaign') }}
                    </x-ui.button>
                </div>
            </div>

            @if ($campaigns->count() > 0)
                <div class="hidden overflow-x-auto lg:block">
                    <table class="min-w-full text-left text-sm">
                        <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Coupon') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Business') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Offer') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Code') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Expires') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            @foreach ($campaigns as $campaign)
                                @php
                                    $campaignPromoContentUrl = route('portal.ai-content', array_filter([
                                        'business_id' => $campaign->business_id,
                                        'type' => 'coupon_message',
                                        'goal' => 'Promote this coupon campaign and drive customer claims.',
                                        'offer' => trim((string) data_get($campaign->settings, 'discount_value', '')),
                                        'target_customer' => 'New and returning local customers',
                                        'details' => trim(implode("\n", array_filter([
                                            'Coupon: '.$campaign->name,
                                            data_get($campaign->settings, 'expiry_date') ? 'Expiry: '.data_get($campaign->settings, 'expiry_date') : null,
                                            data_get($campaign->settings, 'terms') ? 'Terms: '.data_get($campaign->settings, 'terms') : null,
                                        ]))),
                                        'source_type' => 'coupon',
                                        'source_id' => $campaign->id,
                                        'campaign_id' => $campaign->id,
                                    ], fn ($value) => filled($value)));
                                @endphp
                                <tr class="transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)]">
                                    <td class="max-w-[22rem] px-5 py-4">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-warning-color-rgb),0.20); background-color: rgba(var(--theme-warning-color-rgb),0.12); color: var(--theme-warning-color);"><i class="fa-light fa-ticket"></i></span>
                                            <div class="min-w-0">
                                                <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $campaign->name }}</p>
                                                <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $campaign->slug }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-medium" style="color: var(--theme-header-text-color);">{{ $campaign->business?->name ?: __('Business removed') }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ data_get($campaign->settings, 'discount_value', '-') }}</p>
                                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ str(data_get($campaign->settings, 'discount_type', 'coupon'))->headline() }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-lg border px-2.5 py-1.5 font-semibold tracking-[0.08em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color);">{{ data_get($campaign->settings, 'coupon_code') ?: __('Auto') }}</span>
                                    </td>
                                    <td class="px-5 py-4" style="color: var(--theme-muted-text-color);">{{ data_get($campaign->settings, 'expiry_date') ?: __('No expiry') }}</td>
                                    <td class="px-5 py-4">
                                        <x-ui.badge :variant="$campaign->published_at ? 'success' : 'neutral'">{{ $campaign->published_at ? __('Live') : __('Draft') }}</x-ui.badge>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ $campaign->publicUrl() }}" target="_blank" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('Open') }}"><i class="fa-light fa-arrow-up-right"></i></a>
                                            @if ($campaign->landingPage)
                                                <a href="{{ route('portal.landing-pages', ['edit' => $campaign->landingPage->id, 'return' => request()->fullUrl()]) }}" wire:navigate class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-accent-rgb), .30); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .06);" title="{{ __('Edit design') }}"><i class="fa-light fa-palette"></i></a>
                                            @endif
                                            <x-ui.dropdown-menu align="right" width="auto">
                                                <x-slot:trigger>
                                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('QR code') }}"><i class="fa-light fa-qrcode"></i></button>
                                                </x-slot:trigger>
                                                <x-ui.dropdown-menu-item icon="fa-light fa-copy" onclick="navigator.clipboard && navigator.clipboard.writeText(this.dataset.copy || '')" data-copy="{{ e($campaign->publicUrl()) }}">{{ __('Copy QR link') }}</x-ui.dropdown-menu-item>
                                                <x-ui.dropdown-menu-item :href="route('qr-campaigns.png', ['campaign' => $campaign->slug])" icon="fa-light fa-file-image">{{ __('Download PNG') }}</x-ui.dropdown-menu-item>
                                                <x-ui.dropdown-menu-item :href="route('qr-campaigns.svg', ['campaign' => $campaign->slug])" icon="fa-light fa-code">{{ __('Download SVG') }}</x-ui.dropdown-menu-item>
                                                @if (\Illuminate\Support\Facades\Route::has('portal.qr-codes.analytics'))
                                                    <x-ui.dropdown-menu-item :href="route('portal.qr-codes.analytics', ['campaign' => $campaign->slug])" icon="fa-light fa-chart-line" wire:navigate>{{ __('View Analytics') }}</x-ui.dropdown-menu-item>
                                                @endif
                                            </x-ui.dropdown-menu>
                                            <a href="{{ $campaignPromoContentUrl }}" wire:navigate class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-accent); background-color: var(--theme-surface-overlay);" title="{{ __('Generate content') }}"><i class="fa-light fa-pen-nib"></i></a>
                                            <button type="button" wire:click="togglePublish({{ $campaign->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ $campaign->published_at ? __('Move to draft') : __('Publish') }}"><i class="fa-light {{ $campaign->published_at ? 'fa-pause' : 'fa-play' }}"></i></button>
                                            <x-ui.dialog :title="__('Delete coupon campaign')" :description="__('This removes the coupon page, QR asset, scans, and coupon claims connected to it.')" width="sm" dismissible>
                                                <x-slot:trigger>
                                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color); background-color: rgba(var(--theme-danger-color-rgb), .05);" title="{{ __('Delete') }}"><i class="fa-light fa-trash"></i></button>
                                                </x-slot:trigger>
                                                <x-slot:footer>
                                                    <div class="flex justify-end gap-3">
                                                        <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                                        <x-ui.button type="button" variant="danger" wire:click="delete({{ $campaign->id }})" x-on:click="open = false">{{ __('Delete') }}</x-ui.button>
                                                    </div>
                                                </x-slot:footer>
                                            </x-ui.dialog>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="grid gap-3 p-4 lg:hidden">
                    @foreach ($campaigns as $campaign)
                        <article class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $campaign->name }}</p>
                                    <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $campaign->business?->name ?: __('Business removed') }}</p>
                                </div>
                                <x-ui.badge :variant="$campaign->published_at ? 'success' : 'neutral'">{{ $campaign->published_at ? __('Live') : __('Draft') }}</x-ui.badge>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);" href="{{ $campaign->publicUrl() }}" target="_blank">{{ __('Open') }} <i class="fa-light fa-arrow-up-right text-xs"></i></a>
                                @if ($campaign->landingPage)
                                    <a class="inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .30); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .06);" href="{{ route('portal.landing-pages', ['edit' => $campaign->landingPage->id, 'return' => request()->fullUrl()]) }}" wire:navigate><i class="fa-light fa-palette"></i>{{ __('Edit design') }}</a>
                                @endif
                                <button type="button" class="inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color);" wire:click="togglePublish({{ $campaign->id }})"><i class="fa-light {{ $campaign->published_at ? 'fa-pause' : 'fa-play' }}"></i>{{ $campaign->published_at ? __('Pause') : __('Publish') }}</button>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    <p class="text-sm" style="color: var(--theme-muted-text-color);">
                        {{ __('Showing') }}
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($campaigns->firstItem()) }}</span>
                        -
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($campaigns->lastItem()) }}</span>
                        {{ __('of') }}
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($campaigns->total()) }}</span>
                        {{ __('coupon campaigns') }}
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="previousPage('campaignsPage')" @disabled($campaigns->onFirstPage()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"><i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}</button>
                        <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .25); background-color: rgba(var(--theme-accent-rgb), .10); color: var(--theme-accent);">{{ __('Page :page / :pages', ['page' => $campaigns->currentPage(), 'pages' => max(1, $campaigns->lastPage())]) }}</span>
                        <button type="button" wire:click="nextPage('campaignsPage')" @disabled(! $campaigns->hasMorePages()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">{{ __('Next') }}<i class="fa-light fa-arrow-right"></i></button>
                    </div>
                </div>
            @else
                <div class="p-8">
                    <x-ui.empty icon="fa-light fa-ticket" :title="__('No coupon campaigns yet')" :description="__('Create your first local offer to collect claims and issue unique coupon codes.')" />
                </div>
            @endif
        </div>

        <div x-cloak x-show="couponTab === 'claims'" class="p-4">
            <section class="overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <div class="flex flex-col gap-3 border-b px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recent claims') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Customer coupon codes and redemption status.') }}</p>
                    </div>
                    <x-ui.select wire:model.live="claimsPerPage" name="coupon_claims_per_page">
                        <option value="10">{{ __('10 / page') }}</option>
                        <option value="25">{{ __('25 / page') }}</option>
                        <option value="50">{{ __('50 / page') }}</option>
                    </x-ui.select>
                </div>
                @if ($claims->count() > 0)
                    <div class="hidden overflow-x-auto lg:block">
                        <table class="min-w-full text-left text-sm">
                            <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                                <tr>
                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Customer') }}</th>
                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Business') }}</th>
                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Code') }}</th>
                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Campaign') }}</th>
                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Claimed At') }}</th>
                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                @foreach ($claims as $claim)
                                    @php($campaign = $campaignLookup->get($claim->campaign_id))
                                    <tr>
                                        <td class="px-5 py-4">
                                            <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $claim->customer_name }}</p>
                                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $claim->customer_phone ?: $claim->customer_email ?: __('No contact') }}</p>
                                        </td>
                                        <td class="px-5 py-4">{{ $campaign?->business?->name ?: __('Business removed') }}</td>
                                        <td class="px-5 py-4">
                                            <span class="rounded-lg border px-2.5 py-1.5 font-semibold tracking-[0.08em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color);">{{ $claim->code }}</span>
                                        </td>
                                        <td class="px-5 py-4">{{ $campaign?->name ?: __('Campaign removed') }}</td>
                                        <td class="px-5 py-4" style="color: var(--theme-muted-text-color);">{{ format_date_locale($claim->created_at) }}</td>
                                        <td class="px-5 py-4"><x-ui.badge :variant="$claim->status === 'used' ? 'success' : ($claim->status === 'cancelled' ? 'danger' : 'warning')">{{ str($claim->status)->headline() }}</x-ui.badge></td>
                                        <td class="px-5 py-4 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <button type="button" onclick="navigator.clipboard && navigator.clipboard.writeText(this.dataset.copy || '')" data-copy="{{ e($claim->code) }}" class="rounded-lg border px-3 py-2 text-xs font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color);">{{ __('Copy') }}</button>
                                                @if ($claim->status === 'used')
                                                    <button type="button" wire:click="markClaimed({{ $claim->id }})" class="rounded-lg border px-3 py-2 text-xs font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color);">{{ __('Undo') }}</button>
                                                @else
                                                    <button type="button" wire:click="markUsed({{ $claim->id }})" class="rounded-lg border px-3 py-2 text-xs font-semibold" style="border-color: rgba(var(--theme-success-color-rgb), .28); color: var(--theme-success-color);">{{ __('Mark used') }}</button>
                                                    <button type="button" wire:click="cancelClaim({{ $claim->id }})" class="rounded-lg border px-3 py-2 text-xs font-semibold" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color);">{{ __('Cancel') }}</button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                        <p class="text-sm" style="color: var(--theme-muted-text-color);">
                            {{ __('Showing') }}
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($claims->firstItem()) }}</span>
                            -
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($claims->lastItem()) }}</span>
                            {{ __('of') }}
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($claims->total()) }}</span>
                            {{ __('coupon claims') }}
                        </p>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="previousPage('claimsPage')" @disabled($claims->onFirstPage()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"><i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}</button>
                            <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .25); background-color: rgba(var(--theme-accent-rgb), .10); color: var(--theme-accent);">{{ __('Page :page / :pages', ['page' => $claims->currentPage(), 'pages' => max(1, $claims->lastPage())]) }}</span>
                            <button type="button" wire:click="nextPage('claimsPage')" @disabled(! $claims->hasMorePages()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">{{ __('Next') }}<i class="fa-light fa-arrow-right"></i></button>
                        </div>
                    </div>
                @else
                    <div class="p-8">
                        <x-ui.empty icon="fa-light fa-ticket" :title="__('No coupon claims yet')" :description="__('Claims will appear here after customers submit their contact details on a coupon page.')" />
                    </div>
                @endif
            </section>
        </div>

        <div x-cloak x-show="couponTab === 'workflow'" class="grid gap-4 p-4 lg:grid-cols-2">
            <section class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Redemption workflow') }}</p>
                <div class="mt-4 space-y-3">
                    @foreach ([__('Customer scans or opens coupon page'), __('Customer claims a unique coupon code'), __('Staff validates code at checkout'), __('Mark coupon as used for reporting')] as $item)
                        <div class="flex gap-3 text-sm">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="fa-light fa-check text-xs"></i></span>
                            <span style="color: var(--theme-muted-text-color);">{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Offer ideas') }}</p>
                <div class="mt-4 grid gap-2">
                    @foreach ([__('20% off next visit'), __('Free consultation'), __('Buy 1 get 1 trial'), __('Weekend limited offer')] as $idea)
                        <button type="button" x-on:click="couponDialogOpen = true; $wire.set('name', @js($idea)); $wire.set('terms', @js(__('Valid for one customer. Cannot be combined with other offers.')))" class="flex items-center justify-between gap-3 rounded-lg border px-3 py-2 text-left text-sm font-semibold transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.08)]" style="border-color: rgba(var(--theme-border-color-rgb), .52); color: var(--theme-header-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                            <span>{{ $idea }}</span>
                            <span class="text-xs" style="color: var(--theme-accent);">{{ __('Use idea') }}</span>
                        </button>
                    @endforeach
                </div>
            </section>
        </div>
    </section>

    <template x-teleport="body">
        <div x-cloak x-show="couponDialogOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="couponDialogOpen = false">
            <div class="fixed inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="couponDialogOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <form wire:submit="save" x-show="couponDialogOpen" x-transition.opacity.scale.95 class="relative w-full max-w-2xl overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.72); background-color: var(--theme-surface-overlay);">
                    <div class="flex items-start justify-between gap-4 border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <div>
                            <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Create coupon campaign') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Create a public offer page with a unique code for every claim.') }}</p>
                        </div>
                        <button type="button" x-on:click="couponDialogOpen = false" class="flex h-10 w-10 items-center justify-center rounded-xl" style="color: var(--theme-muted-text-color);">
                            <i class="fa-light fa-xmark"></i>
                        </button>
                    </div>

                    <div class="grid gap-4 p-5">
                        <x-ui.select wire:model="business_id" name="business_id" :label="__('Business')" :error="$errors->first('business_id')">
                            <option value="">{{ __('Select business') }}</option>
                            @foreach ($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.input wire:model="name" name="name" :label="__('Coupon name')" :placeholder="__('20% off next visit')" :error="$errors->first('name')" />
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.select wire:model="discount_type" name="discount_type" :label="__('Discount type')" :error="$errors->first('discount_type')">
                                <option value="percentage">{{ __('Percentage') }}</option>
                                <option value="fixed">{{ __('Fixed amount') }}</option>
                                <option value="free_item">{{ __('Free item') }}</option>
                                <option value="custom">{{ __('Custom offer') }}</option>
                            </x-ui.select>
                            <x-ui.input wire:model="discount_value" name="discount_value" :label="__('Discount value')" :placeholder="__('20%')" :error="$errors->first('discount_value')" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input wire:model="coupon_code" name="coupon_code" :label="__('Coupon code prefix')" :placeholder="__('Auto-generate')" :error="$errors->first('coupon_code')" />
                            <x-ui.input wire:model="usage_limit" name="usage_limit" type="number" :label="__('Usage limit')" :placeholder="__('Unlimited')" :error="$errors->first('usage_limit')" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.date-picker
                                wire:model="expiry_date"
                                name="expiry_date"
                                :label="__('Expiry date')"
                                :value="$expiry_date"
                                :error="$errors->first('expiry_date')"
                                :placeholder="__('Choose expiry date')"
                                placement="top"
                            />
                            <x-ui.select wire:model="status" name="status" :label="__('Status')" :error="$errors->first('status')">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="draft">{{ __('Draft') }}</option>
                            </x-ui.select>
                        </div>
                        <x-ui.textarea wire:model="terms" name="terms" :label="__('Terms')" rows="3" :placeholder="__('Valid for one customer. Cannot be combined with other offers.')" :error="$errors->first('terms')">{{ $terms }}</x-ui.textarea>
                        @include('applandingpages::partials.growth-tool-page-design', ['type' => 'coupon', 'templates' => $couponTemplates])
                    </div>

                    <div class="flex justify-end gap-3 border-t px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <x-ui.button type="button" variant="outline" x-on:click="couponDialogOpen = false">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="submit">
                            <span wire:loading.remove wire:target="save"><i class="fa-light fa-floppy-disk"></i>{{ __('Create coupon campaign') }}</span>
                            <span wire:loading wire:target="save"><i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Saving...') }}</span>
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
