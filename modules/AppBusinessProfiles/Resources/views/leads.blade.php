<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 36%),
        linear-gradient(35deg, rgba(var(--theme-success-color-rgb),0.09), transparent 38%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-5 px-5 py-5 lg:grid-cols-[minmax(0,1fr)_21rem] lg:items-center sm:px-6">
            <div>
                <a href="{{ route('portal.businesses.show', $business) }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold" style="color: var(--theme-muted-text-color);">
                    <i class="fa-light fa-arrow-left"></i>{{ $business->name }}
                </a>
                <div class="mt-4 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-accent-rgb),0.18); background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                    <i class="fa-light fa-users"></i>{{ __('Business Leads') }}
                </div>
                <h1 class="mt-3 max-w-2xl text-[2rem] font-semibold leading-[1.05] tracking-[-0.05em] sm:text-[2.55rem]" style="color: var(--theme-header-text-color);">{{ __('Capture and qualify every local lead') }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6" style="color: var(--theme-muted-text-color);">
                    {{ __('Contacts captured from lead forms, bookings, coupons, feedback, and campaign pages for this business.') }}
                </p>
            </div>

            <div class="rounded-[1rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Lead sources') }}</p>
                        <p class="mt-0.5 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Where contacts entered this business.') }}</p>
                    </div>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.11); color: var(--theme-accent);">
                        <i class="fa-light fa-filter-list"></i>
                    </span>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    @foreach ([
                        ['label' => 'Lead Form', 'icon' => 'fa-light fa-user-plus'],
                        ['label' => 'Booking Page', 'icon' => 'fa-light fa-calendar-check'],
                        ['label' => 'Coupon', 'icon' => 'fa-light fa-ticket'],
                        ['label' => 'Feedback', 'icon' => 'fa-light fa-message-lines'],
                        ['label' => 'Landing Page', 'icon' => 'fa-light fa-file-lines'],
                    ] as $source)
                        <div class="flex items-center justify-between gap-2 rounded-lg border px-2.5 py-2" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                            <div class="flex min-w-0 items-center gap-2">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);"><i class="{{ $source['icon'] }} text-[11px]"></i></span>
                                <p class="truncate text-xs font-semibold" style="color: var(--theme-header-text-color);">{{ __($source['label']) }}</p>
                            </div>
                            <p class="text-sm font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($sourceCounts[$source['label']] ?? 0) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    @include('appbusinessprofiles::partials.business-tabs', ['business' => $business, 'active' => 'leads'])

    <section class="grid gap-3 md:grid-cols-3">
        @foreach ([
            ['label' => __('Total Leads'), 'value' => $totalLeads, 'description' => __('All captured contacts'), 'icon' => 'fa-light fa-address-book'],
            ['label' => __('Lead Form Leads'), 'value' => $sourceCounts['Lead Form'] ?? 0, 'description' => __('Direct lead form submissions'), 'icon' => 'fa-light fa-clipboard-list-check'],
            ['label' => __('Other Source Leads'), 'value' => $totalLeads - ($sourceCounts['Lead Form'] ?? 0), 'description' => __('Bookings, coupons & feedback'), 'icon' => 'fa-light fa-bolt'],
        ] as $metric)
            <article class="group relative overflow-hidden rounded-[1.15rem] border p-4 transition hover:-translate-y-0.5 hover:shadow-[0_22px_60px_-42px_rgba(15,23,42,0.42)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background: linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.075), transparent 44%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <span class="absolute inset-x-0 top-0 h-1 opacity-80" style="background-color: var(--theme-accent);"></span>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[2rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($metric['value']) }}</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $metric['description'] }}</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl transition group-hover:scale-105" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="{{ $metric['icon'] }}"></i>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: linear-gradient(90deg, rgba(var(--theme-accent-rgb),0.045), transparent);">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Lead inbox') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('People who submitted details through any growth flow.') }}</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-[minmax(0,18rem)_8rem]">
                    <div class="relative">
                        <i class="fa-light fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm" style="color: var(--theme-muted-text-color);"></i>
                        <input type="search" wire:model.live.debounce.300ms="search" class="h-11 w-full rounded-xl border pl-10 pr-4 text-sm outline-none transition focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Search leads...') }}">
                    </div>
                    <x-ui.select wire:model.live="perPage">
                        <option value="10">{{ __('10 / page') }}</option>
                        <option value="25">{{ __('25 / page') }}</option>
                        <option value="50">{{ __('50 / page') }}</option>
                    </x-ui.select>
                </div>
            </div>
        </div>

        @if ($leads->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Lead') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Source') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Campaign') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Note') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Created') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        @foreach ($leads as $lead)
                            <tr class="transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)]">
                                <td class="px-5 py-4">
                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $lead['name'] }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $lead['phone'] ?: $lead['email'] ?: __('No contact') }}</p>
                                </td>
                                <td class="px-5 py-4"><x-ui.badge>{{ $lead['source'] }}</x-ui.badge></td>
                                <td class="max-w-[16rem] px-5 py-4"><p class="truncate" style="color: var(--theme-muted-text-color);">{{ $lead['campaign'] }}</p></td>
                                <td class="px-5 py-4"><x-ui.badge variant="success">{{ str($lead['status'])->headline() }}</x-ui.badge></td>
                                <td class="max-w-[22rem] px-5 py-4"><p class="line-clamp-2" style="color: var(--theme-muted-text-color);">{{ $lead['note'] ?: __('No note') }}</p></td>
                                <td class="px-5 py-4 text-xs" style="color: var(--theme-muted-text-color);">{{ format_date_locale($lead['created_at']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Showing') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($leads->firstItem()) }}</span> - <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($leads->lastItem()) }}</span> {{ __('of') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($leads->total()) }}</span></p>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="previousPage" @disabled($leads->onFirstPage()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"><i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}</button>
                    <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">{{ __('Page') }} {{ format_number_locale($leads->currentPage()) }} / {{ format_number_locale($leads->lastPage()) }}</span>
                    <button type="button" wire:click="nextPage" @disabled(! $leads->hasMorePages()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">{{ __('Next') }}<i class="fa-light fa-arrow-right"></i></button>
                </div>
            </div>
        @else
            <div class="p-8">
                <div class="rounded-[1.25rem] border px-6 py-10 text-center" style="border-color: rgba(var(--theme-border-color-rgb), .58); background: linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.07), transparent 56%), color-mix(in srgb, var(--theme-surface-base) 82%, transparent);">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: var(--theme-surface-overlay); color: var(--theme-accent);">
                        <i class="fa-light fa-address-book text-xl"></i>
                    </span>
                    <h3 class="mt-5 text-lg font-semibold tracking-[-0.025em]" style="color: var(--theme-header-text-color);">{{ __('No leads yet') }}</h3>
                    <p class="mx-auto mt-2 max-w-xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Lead form, booking, coupon, and feedback submissions for this business will appear here once customers respond to a campaign.') }}</p>
                <div class="mt-5 flex flex-wrap justify-center gap-3">
                    @if (($campaignCount ?? 0) <= 0)
                        <x-ui.button href="{{ route('portal.businesses.campaigns.create', ['business' => $business, 'type' => 'lead']) }}" wire:navigate size="sm">
                            <i class="fa-light fa-plus"></i>{{ __('Create Campaign') }}
                        </x-ui.button>
                    @else
                        <x-ui.button href="{{ route('portal.businesses.campaigns.index', $business) }}" wire:navigate size="sm">
                            <i class="fa-light fa-share-nodes"></i>{{ __('Share Campaign') }}
                        </x-ui.button>
                        <x-ui.button href="{{ route('portal.businesses.campaigns.create', ['business' => $business, 'type' => 'lead']) }}" wire:navigate size="sm" variant="secondary">
                            <i class="fa-light fa-user-plus"></i>{{ __('Create Lead Form') }}
                        </x-ui.button>
                    @endif
                </div>
                </div>
            </div>
        @endif
    </section>
</div>
