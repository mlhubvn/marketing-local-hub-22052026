<div
    class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6"
    x-data="{ reviewDialogOpen: false }"
    x-on:review-booster-saved.window="reviewDialogOpen = false"
>
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif
    @include('applandingpages::partials.growth-tool-created-actions')

    @php
        $hasActiveFilters = trim($search) !== '' || $businessFilter !== 'all';
        $contentWriterBusinessId = $businessFilter !== 'all' ? (string) $businessFilter : (string) optional($businesses->first())->id;
        $reviewRequestContentUrl = route('portal.ai-content', array_filter([
            'business_id' => $contentWriterBusinessId,
            'type' => 'review_request',
            'goal' => 'Ask happy customers to leave a public review after visiting.',
            'target_customer' => 'Happy recent customers',
            'details' => 'Write a review request message for a Review Booster campaign.',
            'source_type' => 'review_booster',
        ], fn ($value) => filled($value)));
        $thankYouContentUrl = route('portal.ai-content', array_filter([
            'business_id' => $contentWriterBusinessId,
            'type' => 'thank_you',
            'goal' => 'Thank customers after they leave a review or private feedback.',
            'target_customer' => 'Recent customers',
            'details' => 'Write a warm thank you message connected to a Review Booster flow.',
            'source_type' => 'review_booster',
        ], fn ($value) => filled($value)));
    @endphp

    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 34%),
        linear-gradient(35deg, rgba(var(--theme-warning-color-rgb),0.10), transparent 38%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-7 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-star"></i>
                    {{ __('Growth Tools') }}
                </div>
                <h1 class="mt-4 max-w-3xl text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('Review Booster') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ __('Send happy customers to Google or Facebook reviews, while routing low-score feedback into a private recovery form.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button type="button" size="lg" x-on:click="reviewDialogOpen = true; $wire.create()">
                        <i class="fa-light fa-plus"></i>{{ __('Create review booster') }}
                    </x-ui.button>
                    <x-ui.button href="{{ $reviewRequestContentUrl }}" wire:navigate variant="outline" size="lg">
                        <i class="fa-light fa-pen-nib"></i>{{ __('Generate review request') }}
                    </x-ui.button>
                    <x-ui.button href="{{ $thankYouContentUrl }}" wire:navigate variant="outline" size="lg">
                        <i class="fa-light fa-message-heart"></i>{{ __('Generate thank you') }}
                    </x-ui.button>
                </div>
            </div>

            <div class="rounded-[1.2rem] border p-4 shadow-[0_24px_70px_-48px_rgba(var(--theme-border-color-rgb),0.9)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Review booster health') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Boosters ready to collect reviews') }}</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-sparkles"></i>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-3 gap-3">
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ number_format($totalCampaigns) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Boosters') }}</p>
                    </div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ number_format($totalScans) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Visits') }}</p>
                    </div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ number_format($feedbackCount) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Feedback') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid divide-y sm:grid-cols-2 sm:divide-x sm:divide-y-0 xl:grid-cols-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.58);">
            @foreach ([
                ['label' => __('Active boosters'), 'value' => $activeCampaigns, 'description' => __('Published review funnels'), 'icon' => 'fa-light fa-star', 'tone' => 'accent'],
                ['label' => __('Review visits'), 'value' => $totalScans, 'description' => __('QR scans & link visits'), 'icon' => 'fa-light fa-chart-line', 'tone' => 'success'],
                ['label' => __('Public Review Clicks'), 'value' => $publicReviewClicks, 'description' => __('Sent to Google/Facebook'), 'icon' => 'fa-light fa-arrow-up-right-from-square', 'tone' => 'accent'],
                ['label' => __('Private feedback'), 'value' => $feedbackCount, 'description' => __('Low-score responses captured'), 'icon' => 'fa-light fa-message-lines', 'tone' => 'warning'],
            ] as $metric)
                @php
                    $toneColor = match ($metric['tone']) {
                        'success' => 'var(--theme-success-color)',
                        'warning' => 'var(--theme-warning-color)',
                        default => 'var(--theme-accent)',
                    };
                    $toneRgb = match ($metric['tone']) {
                        'success' => 'var(--theme-success-color-rgb)',
                        'warning' => 'var(--theme-warning-color-rgb)',
                        default => 'var(--theme-accent-rgb)',
                    };
                @endphp
                <article class="group flex min-h-[8.25rem] items-center gap-4 px-5 py-4 transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)]">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition group-hover:scale-[1.03]" style="background-color: rgba({{ $toneRgb }}, 0.11); color: {{ $toneColor }};">
                        <i class="{{ $metric['icon'] }}"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p>
                            <p class="text-[1.75rem] font-semibold leading-none tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ number_format($metric['value']) }}</p>
                        </div>
                        <p class="mt-2 truncate text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $metric['description'] }}</p>
                        <div class="mt-3 h-1.5 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), 0.35);">
                            <div class="h-full rounded-full" style="width: {{ (int) min(100, max(8, $metric['value'] > 0 ? 68 : 8)) }}%; background-color: {{ $toneColor }};"></div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="overflow-visible rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Review boosters') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Manage review routing, recovery messages, and public links.') }}</p>
                </div>
                <div class="grid gap-3 md:grid-cols-[minmax(0,18rem)_12rem_8rem]">
                    <div class="relative">
                        <i class="fa-light fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm" style="color: var(--theme-muted-text-color);"></i>
                        <input type="search" wire:model.live.debounce.300ms="search" class="h-11 w-full rounded-xl border pl-10 pr-10 text-sm outline-none transition focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Search booster or business...') }}">
                        @if (trim($search) !== '')
                            <button type="button" wire:click="$set('search', '')" class="absolute right-3 top-1/2 -translate-y-1/2" style="color: var(--theme-muted-text-color);">
                                <i class="fa-light fa-xmark"></i>
                            </button>
                        @endif
                    </div>
                    <x-ui.select wire:model.live="businessFilter">
                        <option value="all">{{ __('All businesses') }}</option>
                        @foreach ($businesses as $business)
                            <option value="{{ $business->id }}">{{ $business->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select wire:model.live="perPage">
                        <option value="10">{{ __('10 / page') }}</option>
                        <option value="25">{{ __('25 / page') }}</option>
                        <option value="50">{{ __('50 / page') }}</option>
                    </x-ui.select>
                </div>
            </div>
        </div>

        @if ($campaigns->count() > 0)
            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full text-left text-sm">
                    <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Campaign') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Business') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Routing') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Visits') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        @foreach ($campaigns as $campaign)
                            @php
                                $threshold = (int) data_get($campaign->settings, 'positive_threshold', 4);
                                $destination = (string) data_get($campaign->settings, 'preferred_destination', 'google');
                                $campaignReviewContentUrl = route('portal.ai-content', array_filter([
                                    'business_id' => $campaign->business_id,
                                    'type' => 'review_request',
                                    'goal' => 'Ask happy customers to leave a public review after visiting.',
                                    'target_customer' => 'Happy recent customers',
                                    'details' => 'Write review request and thank you copy for '.$campaign->name.'.',
                                    'source_type' => 'review_booster',
                                    'source_id' => $campaign->id,
                                    'campaign_id' => $campaign->id,
                                ], fn ($value) => filled($value)));
                            @endphp
                            <tr class="transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)]">
                                <td class="max-w-[22rem] px-5 py-4">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-warning-color-rgb),0.20); background-color: rgba(var(--theme-warning-color-rgb),0.12); color: var(--theme-warning-color);"><i class="fa-light fa-star"></i></span>
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
                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ __(':stars+ stars', ['stars' => $threshold]) }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Send to :destination first', ['destination' => str($destination)->headline()]) }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($campaign->scans_count) }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Public visits') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <x-ui.badge :variant="$campaign->published_at ? 'success' : 'neutral'">{{ $campaign->published_at ? __('Active') : __('Paused') }}</x-ui.badge>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" href="{{ $campaign->publicUrl() }}" target="_blank" title="{{ __('Open') }}"><i class="fa-light fa-arrow-up-right"></i></a>
                                        @if ($campaign->landingPage)
                                            <a class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-accent-rgb), .30); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .06);" href="{{ route('portal.landing-pages', ['edit' => $campaign->landingPage->id, 'return' => request()->fullUrl()]) }}" wire:navigate title="{{ __('Edit design') }}"><i class="fa-light fa-palette"></i></a>
                                        @endif
                                        <x-ui.dropdown-menu align="right" width="auto">
                                            <x-slot:trigger>
                                                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('QR code') }}">
                                                    <i class="fa-light fa-qrcode"></i>
                                                </button>
                                            </x-slot:trigger>
                                            <x-ui.dropdown-menu-item
                                                icon="fa-light fa-copy"
                                                onclick="navigator.clipboard && navigator.clipboard.writeText(this.dataset.copy || '')"
                                                data-copy="{{ e($campaign->publicUrl()) }}"
                                            >
                                                {{ __('Copy QR link') }}
                                            </x-ui.dropdown-menu-item>
                                            <x-ui.dropdown-menu-item :href="route('qr-campaigns.png', ['campaign' => $campaign->slug])" icon="fa-light fa-file-image">
                                                {{ __('Download PNG') }}
                                            </x-ui.dropdown-menu-item>
                                            <x-ui.dropdown-menu-item :href="route('qr-campaigns.svg', ['campaign' => $campaign->slug])" icon="fa-light fa-code">
                                                {{ __('Download SVG') }}
                                            </x-ui.dropdown-menu-item>
                                            @if (\Illuminate\Support\Facades\Route::has('portal.qr-codes.analytics'))
                                                <x-ui.dropdown-menu-item :href="route('portal.qr-codes.analytics', ['campaign' => $campaign->slug])" icon="fa-light fa-chart-line" wire:navigate>
                                                    {{ __('View Analytics') }}
                                                </x-ui.dropdown-menu-item>
                                            @endif
                                        </x-ui.dropdown-menu>
                                        <a class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-accent); background-color: var(--theme-surface-overlay);" href="{{ $campaignReviewContentUrl }}" wire:navigate title="{{ __('Generate content') }}"><i class="fa-light fa-pen-nib"></i></a>
                                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('Edit') }}" x-on:click="reviewDialogOpen = true; $wire.edit({{ $campaign->id }})"><i class="fa-light fa-pen"></i></button>
                                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('Duplicate') }}" wire:click="duplicate({{ $campaign->id }})"><i class="fa-light fa-copy"></i></button>
                                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ $campaign->published_at ? __('Pause') : __('Activate') }}" wire:click="togglePublished({{ $campaign->id }})"><i class="fa-light {{ $campaign->published_at ? 'fa-pause' : 'fa-play' }}"></i></button>
                                        <x-ui.dialog :title="__('Delete review booster')" :description="__('This removes the review funnel, QR campaign, scans, and private feedback connected to it.')" width="sm" dismissible>
                                            <x-slot:trigger>
                                                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color); background-color: rgba(var(--theme-danger-color-rgb), .05);" title="{{ __('Delete') }}">
                                                    <i class="fa-light fa-trash"></i>
                                                </button>
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
                                <p class="mt-1 truncate text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $campaign->business?->name ?: __('Business removed') }}</p>
                            </div>
                            <x-ui.badge :variant="$campaign->published_at ? 'success' : 'neutral'">{{ $campaign->published_at ? __('Active') : __('Paused') }}</x-ui.badge>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);" href="{{ $campaign->publicUrl() }}" target="_blank">{{ __('Open') }} <i class="fa-light fa-arrow-up-right text-xs"></i></a>
                            @if ($campaign->landingPage)
                                <a class="inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .30); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .06);" href="{{ route('portal.landing-pages', ['edit' => $campaign->landingPage->id, 'return' => request()->fullUrl()]) }}" wire:navigate><i class="fa-light fa-palette"></i>{{ __('Edit design') }}</a>
                            @endif
                            <button type="button" class="inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color);" x-on:click="reviewDialogOpen = true; $wire.edit({{ $campaign->id }})"><i class="fa-light fa-pen"></i>{{ __('Edit') }}</button>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                <p class="text-sm" style="color: var(--theme-muted-text-color);">
                    {{ __('Showing') }}
                    <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($campaigns->firstItem()) }}</span>
                    -
                    <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($campaigns->lastItem()) }}</span>
                    {{ __('of') }}
                    <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($campaigns->total()) }}</span>
                    {{ __('review boosters') }}
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="previousPage" @disabled($campaigns->onFirstPage()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"><i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}</button>
                    <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">{{ __('Page') }} {{ number_format($campaigns->currentPage()) }} / {{ number_format($campaigns->lastPage()) }}</span>
                    <button type="button" wire:click="nextPage" @disabled(! $campaigns->hasMorePages()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">{{ __('Next') }}<i class="fa-light fa-arrow-right"></i></button>
                </div>
            </div>
        @else
            <div class="p-4">
                <div class="relative overflow-hidden rounded-[1.15rem] border p-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background:
                    radial-gradient(circle at top right, rgba(var(--theme-warning-color-rgb),0.13), transparent 34%),
                    linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.075), transparent 46%),
                    color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-center">
                        <div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-warning-color-rgb), 0.2); background-color: rgba(var(--theme-warning-color-rgb),0.12); color: var(--theme-warning-color);">
                                <i class="fa-light {{ $hasActiveFilters ? 'fa-magnifying-glass' : 'fa-star' }} text-xl"></i>
                            </div>
                            <h2 class="mt-5 text-xl font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">{{ $hasActiveFilters ? __('No matching review boosters') : __('No review boosters yet') }}</h2>
                            <p class="mt-3 max-w-xl text-sm leading-7" style="color: var(--theme-muted-text-color);">
                                {{ $hasActiveFilters ? __('Try a different search term or switch back to all businesses.') : __('Create a review booster to send happy customers to public reviews and capture low-score feedback privately.') }}
                            </p>
                            <div class="mt-5 flex flex-wrap gap-3">
                                @if ($hasActiveFilters)
                                    <x-ui.button type="button" variant="outline" wire:click="$set('search', '')" size="sm"><i class="fa-light fa-xmark"></i>{{ __('Clear search') }}</x-ui.button>
                                    <x-ui.button type="button" variant="outline" wire:click="$set('businessFilter', 'all')" size="sm"><i class="fa-light fa-layer-group"></i>{{ __('All businesses') }}</x-ui.button>
                                @else
                                    <x-ui.button type="button" size="sm" x-on:click="reviewDialogOpen = true; $wire.create()"><i class="fa-light fa-plus"></i>{{ __('Create review booster') }}</x-ui.button>
                                @endif
                            </div>
                        </div>
                        <div class="grid gap-2">
                            @foreach ([__('1-3 stars stay private'), __('4-5 stars go public'), __('QR code included')] as $label)
                                <div class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); color: var(--theme-header-text-color); background-color: color-mix(in srgb, var(--theme-surface-overlay) 82%, transparent);">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="fa-light fa-check text-xs"></i></span>
                                    {{ $label }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>

    <template x-teleport="body">
        <div x-cloak x-show="reviewDialogOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="reviewDialogOpen = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="reviewDialogOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <div x-show="reviewDialogOpen" x-transition.opacity.scale.90 class="relative w-full max-w-6xl">
                    <form
                        wire:submit.prevent="save"
                        class="relative flex max-h-[calc(100vh-3.5rem)] min-h-0 flex-col overflow-hidden rounded-[1.15rem] border shadow-[0_32px_80px_-34px_rgba(15,23,42,0.32)]"
                        style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent); background-color: var(--theme-surface-overlay);"
                        x-data="{
                            threshold: @entangle('positive_threshold').live,
                            destination: @entangle('preferred_destination').live,
                            name: @entangle('name').live,
                            thankYou: @entangle('thank_you_message').live,
                            negative: @entangle('negative_feedback_message').live,
                        }"
                    >
                        <div class="shrink-0 flex items-start justify-between gap-4 border-b px-5 py-4 sm:px-6 sm:py-5" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent);">
                            <div>
                                <h3 class="text-[1.05rem] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ $editingId ? __('Edit review booster') : __('Create review booster') }}</h3>
                                <p class="mt-2 text-[15px] leading-7" style="color: var(--theme-muted-text-color);">{{ __('Configure where happy customers go and what low-score customers see before submitting private feedback.') }}</p>
                            </div>
                            <button type="button" class="transition" style="color: var(--theme-muted-text-color);" x-on:click="reviewDialogOpen = false">
                                <i class="fa-light fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 py-4 sm:px-6 sm:py-5">
                            @if ($errors->any())
                                <div class="mb-4">
                                    <x-ui.alert
                                        inline
                                        variant="danger"
                                        :title="__('Review booster was not saved')"
                                        :description="$errors->first()"
                                    />
                                </div>
                            @endif

                            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_22rem] xl:items-start">
                                <div class="space-y-5">
                                    <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                        <div class="grid gap-4 md:grid-cols-2">
                                            <x-ui.select wire:model="business_id" name="business_id" :label="__('Business')" :error="$errors->first('business_id')">
                                                <option value="">{{ __('Select business') }}</option>
                                                @foreach ($businesses as $business)
                                                    <option value="{{ $business->id }}">{{ $business->name }}</option>
                                                @endforeach
                                            </x-ui.select>
                                            <x-ui.input x-model="name" wire:model.live="name" name="name" :label="__('Campaign name')" :placeholder="__('Get More Google Reviews')" :error="$errors->first('name')" />
                                            <x-ui.input wire:model="google_review_url" name="google_review_url" :label="__('Google review URL')" :placeholder="__('https://g.page/r/...')" :error="$errors->first('google_review_url')" />
                                            <x-ui.input wire:model="facebook_review_url" name="facebook_review_url" :label="__('Facebook review URL')" :placeholder="__('https://facebook.com/.../reviews')" :error="$errors->first('facebook_review_url')" />
                                        </div>
                                        <div class="mt-4">
                                            @include('applandingpages::partials.growth-tool-page-design', ['type' => 'review', 'templates' => $reviewTemplates])
                                        </div>
                                    </div>

                                    <div class="grid gap-5 lg:grid-cols-2">
                                        <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Routing rules') }}</p>
                                            <div class="mt-4 grid gap-4">
                                                <x-ui.select x-model.number="threshold" wire:model.live="positive_threshold" name="positive_threshold" :label="__('Public review threshold')" :error="$errors->first('positive_threshold')">
                                                    <option value="3">{{ __('3+ stars') }}</option>
                                                    <option value="4">{{ __('4+ stars') }}</option>
                                                    <option value="5">{{ __('5 stars only') }}</option>
                                                </x-ui.select>
                                                <x-ui.select x-model="destination" wire:model.live="preferred_destination" name="preferred_destination" :label="__('Preferred review destination')" :error="$errors->first('preferred_destination')">
                                                    <option value="google">{{ __('Google first') }}</option>
                                                    <option value="facebook">{{ __('Facebook first') }}</option>
                                                </x-ui.select>
                                            </div>
                                        </div>

                                        <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Message templates') }}</p>
                                            <div class="mt-4 space-y-4">
                                                <x-ui.textarea x-model="thankYou" wire:model.live="thank_you_message" name="thank_you_message" :label="__('Thank you message')" rows="3" :error="$errors->first('thank_you_message')">{{ $thank_you_message }}</x-ui.textarea>
                                                <x-ui.textarea x-model="negative" wire:model.live="negative_feedback_message" name="negative_feedback_message" :label="__('Private feedback message')" rows="3" :error="$errors->first('negative_feedback_message')">{{ $negative_feedback_message }}</x-ui.textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <aside class="rounded-2xl border p-4 xl:sticky xl:top-0" style="border-color: rgba(var(--theme-border-color-rgb), .55); background:
                                    linear-gradient(145deg, rgba(var(--theme-warning-color-rgb),0.10), transparent 38%),
                                    color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Live funnel preview') }}</p>
                                    <div class="mt-4 rounded-[1.2rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .52); background-color: color-mix(in srgb, var(--theme-surface-overlay) 92%, transparent);">
                                        <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Customer sees') }}</p>
                                        <h4 class="mt-2 text-lg font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);" x-text="name || @js(__('Review campaign'))"></h4>
                                        <div class="mt-4 grid grid-cols-5 gap-1.5">
                                            <template x-for="rating in [1,2,3,4,5]" :key="rating">
                                                <div class="rounded-xl border px-2 py-2 text-center text-sm font-semibold" :style="rating >= threshold ? 'border-color: rgba(var(--theme-success-color-rgb), .26); background-color: rgba(var(--theme-success-color-rgb), .10); color: var(--theme-success-color);' : 'border-color: rgba(var(--theme-warning-color-rgb), .26); background-color: rgba(var(--theme-warning-color-rgb), .10); color: var(--theme-warning-color);'">
                                                    <span x-text="rating"></span><i class="fa-solid fa-star ml-0.5 text-[10px]"></i>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="mt-4 grid gap-2 text-xs">
                                            <div class="rounded-xl px-3 py-2" style="background-color: rgba(var(--theme-success-color-rgb), .10); color: var(--theme-success-color);">
                                                <i class="fa-light fa-arrow-up-right mr-1"></i>
                                                <span x-text="`${threshold}+ stars -> ${destination === 'google' ? 'Google Review' : 'Facebook Review'}`"></span>
                                            </div>
                                            <div class="rounded-xl px-3 py-2" style="background-color: rgba(var(--theme-warning-color-rgb), .10); color: var(--theme-warning-color);">
                                                <i class="fa-light fa-inbox mr-1"></i>
                                                <span x-text="`Below ${threshold} stars -> private feedback`"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 rounded-[1.2rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .52); background-color: color-mix(in srgb, var(--theme-surface-overlay) 92%, transparent);">
                                        <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Private feedback copy') }}</p>
                                        <p class="mt-3 text-sm leading-6" style="color: var(--theme-muted-text-color);" x-text="negative"></p>
                                    </div>
                                </aside>
                            </div>
                        </div>

                        <div class="shrink-0 border-t px-5 py-4 sm:px-6" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent); background-color: color-mix(in srgb, var(--theme-surface-soft) 88%, transparent);">
                            <div class="flex items-center justify-end gap-3">
                                <x-ui.button type="button" variant="outline" x-on:click="reviewDialogOpen = false">{{ __('Cancel') }}</x-ui.button>
                                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="save">
                                    <span wire:loading.remove wire:target="save" class="inline-flex items-center gap-2">
                                        <i class="fa-light fa-floppy-disk"></i>{{ $editingId ? __('Save changes') : __('Create review QR') }}
                                    </span>
                                    <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                                        <i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Saving...') }}
                                    </span>
                                </x-ui.button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
