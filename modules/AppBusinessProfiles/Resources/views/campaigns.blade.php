<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6" x-data="{ createOpen: false }">
    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 36%),
        linear-gradient(35deg, rgba(var(--theme-success-color-rgb),0.09), transparent 38%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-6 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <a href="{{ route('portal.businesses.show', $business) }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold" style="color: var(--theme-muted-text-color);">
                    <i class="fa-light fa-arrow-left"></i>{{ $business->name }}
                </a>
                <h1 class="mt-4 max-w-3xl text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('Business campaigns') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ __('Manage every growth campaign, QR page, public link, and conversion signal for this business workspace.') }}
                </p>
            </div>

            <div class="rounded-[1.15rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent);">
                <x-ui.button type="button" size="lg" class="w-full" x-on:click="createOpen = true">
                    <i class="fa-light fa-plus"></i>{{ __('New campaign') }}
                </x-ui.button>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ number_format($totalCampaigns) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Campaigns') }}</p>
                    </div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ number_format($totalScans) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Scans') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('appbusinessprofiles::partials.business-tabs', ['business' => $business, 'active' => 'campaigns'])

    <section class="overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Campaign directory') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('All campaign types scoped to this business.') }}</p>
                </div>
                <div class="grid gap-3 md:grid-cols-[minmax(0,18rem)_12rem_8rem]">
                    <div class="relative">
                        <i class="fa-light fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm" style="color: var(--theme-muted-text-color);"></i>
                        <input type="search" wire:model.live.debounce.300ms="search" class="h-11 w-full rounded-xl border pl-10 pr-4 text-sm outline-none transition focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Search campaign...') }}">
                    </div>
                    <x-ui.select wire:model.live="typeFilter">
                        @foreach ($typeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
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
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Campaign') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Type') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('QR') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Scans') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Conversions') }}</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        @foreach ($campaigns as $campaign)
                            <tr class="transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)]">
                                <td class="max-w-[22rem] px-5 py-4">
                                    <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $campaign->name }}</p>
                                    <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $campaign->slug }}</p>
                                </td>
                                <td class="px-5 py-4"><x-ui.badge>{{ str($campaign->type)->headline() }}</x-ui.badge></td>
                                <td class="px-5 py-4"><x-ui.badge :variant="$campaign->published_at ? 'success' : 'neutral'">{{ $campaign->published_at ? __('Active') : __('Draft') }}</x-ui.badge></td>
                                <td class="px-5 py-4">
                                    <img src="{{ route('qr-campaigns.svg', ['campaign' => $campaign->slug]) }}" alt="" class="h-12 w-12 rounded-lg border bg-white p-1" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                                </td>
                                <td class="px-5 py-4 font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($campaign->scans_count) }}</td>
                                <td class="px-5 py-4 font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($conversionCounts[$campaign->id] ?? 0) }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('portal.businesses.campaigns.show', [$business, $campaign]) }}" target="_blank" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color);" title="{{ __('View') }}"><i class="fa-light fa-arrow-up-right"></i></a>
                                        <a href="{{ route('portal.businesses.campaigns.edit', [$business, $campaign]) }}" wire:navigate class="inline-flex h-10 w-10 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color);" title="{{ __('Edit') }}"><i class="fa-light fa-pen"></i></a>
                                        <a href="{{ route('portal.reports') }}" wire:navigate class="inline-flex h-10 w-10 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color);" title="{{ __('Reports') }}"><i class="fa-light fa-chart-line"></i></a>
                                        <button type="button" onclick="navigator.clipboard && navigator.clipboard.writeText(this.dataset.copy || '')" data-copy="{{ e($campaign->publicUrl()) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color);" title="{{ __('Copy link') }}"><i class="fa-light fa-link"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                <p class="text-sm" style="color: var(--theme-muted-text-color);">
                    {{ __('Showing') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($campaigns->firstItem()) }}</span> - <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($campaigns->lastItem()) }}</span> {{ __('of') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($campaigns->total()) }}</span>
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="previousPage" @disabled($campaigns->onFirstPage()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"><i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}</button>
                    <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">{{ __('Page') }} {{ number_format($campaigns->currentPage()) }} / {{ number_format($campaigns->lastPage()) }}</span>
                    <button type="button" wire:click="nextPage" @disabled(! $campaigns->hasMorePages()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">{{ __('Next') }}<i class="fa-light fa-arrow-right"></i></button>
                </div>
            </div>
        @else
            <div class="p-8">
                <x-ui.empty icon="fa-light fa-bullhorn" :title="__('No campaigns yet')" :description="__('Create a review booster, booking page, coupon, feedback form, lead form, or QR campaign for this business.')" />
            </div>
        @endif
    </section>

    <template x-teleport="body">
        <div x-cloak x-show="createOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="createOpen = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="createOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <div x-show="createOpen" x-transition.opacity.scale.90 class="relative w-full max-w-3xl overflow-hidden rounded-[1.15rem] border p-5 shadow-[0_32px_80px_-34px_rgba(15,23,42,0.32)]" style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent); background-color: var(--theme-surface-overlay);">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">{{ __('What do you want to create?') }}</h3>
                            <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Choose a growth goal for :business.', ['business' => $business->name]) }}</p>
                        </div>
                        <button type="button" style="color: var(--theme-muted-text-color);" x-on:click="createOpen = false"><i class="fa-light fa-xmark text-lg"></i></button>
                    </div>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach ([
                            ['type' => 'review', 'label' => __('Review Booster'), 'icon' => 'fa-light fa-star'],
                            ['type' => 'booking', 'label' => __('Booking Page'), 'icon' => 'fa-light fa-calendar-check'],
                            ['type' => 'coupon', 'label' => __('Coupon Campaign'), 'icon' => 'fa-light fa-ticket'],
                            ['type' => 'feedback', 'label' => __('Feedback Form'), 'icon' => 'fa-light fa-message-lines'],
                            ['type' => 'lead', 'label' => __('Lead Form'), 'icon' => 'fa-light fa-clipboard-list-check'],
                            ['type' => 'qr', 'label' => __('QR Campaign'), 'icon' => 'fa-light fa-qrcode'],
                        ] as $item)
                            <a href="{{ route('portal.businesses.campaigns.create', ['business' => $business, 'type' => $item['type']]) }}" wire:navigate class="group flex items-center gap-3 rounded-2xl border p-4 transition hover:shadow-[0_18px_40px_-34px_rgba(var(--theme-accent-rgb),0.9)]" style="border-color: rgba(var(--theme-border-color-rgb), .58); color: var(--theme-header-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 92%, transparent);" onmouseover="this.style.borderColor='rgba(var(--theme-accent-rgb),0.22)'; this.style.backgroundColor='rgba(var(--theme-accent-rgb),0.07)'" onmouseout="this.style.borderColor='rgba(var(--theme-border-color-rgb), .58)'; this.style.backgroundColor='color-mix(in srgb, var(--theme-surface-base) 92%, transparent)'">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="{{ $item['icon'] }}"></i></span>
                                <span class="font-semibold">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
