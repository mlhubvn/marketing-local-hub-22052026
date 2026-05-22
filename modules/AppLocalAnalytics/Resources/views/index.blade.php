<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.15), transparent 34%),
        linear-gradient(35deg, rgba(var(--theme-success-color-rgb),0.08), transparent 42%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-7 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-chart-line"></i>{{ __('Reports') }}
                </div>
                <h1 class="mt-4 max-w-3xl text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('All-business growth overview') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ __('Global reporting across every business in this account. Filter by business, date range, and campaign type without leaving the report workspace.') }}
                </p>
            </div>

            <div class="rounded-[1.2rem] border p-4 shadow-[0_24px_70px_-48px_rgba(var(--theme-border-color-rgb),0.9)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Account coverage') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Filtered global totals') }}</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-chart-mixed"></i>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ number_format($totals['businesses']) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Businesses') }}</p>
                    </div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ number_format($totals['campaigns']) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Campaigns') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="relative overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-4 border-b px-5 py-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.9fr)] lg:items-end" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <div>
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Report workspace') }}</p>
                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Overview, leads, and reviews use the same global filters.') }}</p>
            </div>
            <div class="grid gap-3 md:grid-cols-3">
                <x-ui.combobox
                    :label="__('Business')"
                    :options="collect([['value' => 'all', 'label' => __('All businesses'), 'meta' => __('All report data'), 'icon' => 'fa-layer-group']])
                        ->merge($businesses->map(fn ($business) => [
                            'value' => (string) $business->id,
                            'label' => $business->name,
                            'meta' => str($business->type ?: __('Business'))->headline()->toString(),
                            'icon' => 'fa-store',
                        ]))
                        ->values()
                        ->all()"
                    :selected="$businessFilter"
                    model="businessFilter"
                    :placeholder="__('All businesses')"
                    :search-placeholder="__('Search business...')"
                    :empty-text="__('No businesses found')"
                    icon="fa-light fa-store"
                />
                <x-ui.select wire:model.live="dateRange" :label="__('Date range')">
                    <option value="7">{{ __('Last 7 days') }}</option>
                    <option value="30">{{ __('Last 30 days') }}</option>
                    <option value="90">{{ __('Last 90 days') }}</option>
                    <option value="all">{{ __('All time') }}</option>
                </x-ui.select>
                <x-ui.combobox
                    :label="__('Campaign type')"
                    :options="[
                        ['value' => 'all', 'label' => __('All campaign types'), 'meta' => __('Every growth tool'), 'icon' => 'fa-layer-group'],
                        ['value' => 'review', 'label' => __('Review Booster'), 'meta' => __('Ratings and review clicks'), 'icon' => 'fa-star'],
                        ['value' => 'booking', 'label' => __('Booking Page'), 'meta' => __('Appointments'), 'icon' => 'fa-calendar-check'],
                        ['value' => 'coupon', 'label' => __('Coupon'), 'meta' => __('Claims and redemptions'), 'icon' => 'fa-ticket'],
                        ['value' => 'feedback', 'label' => __('Feedback Form'), 'meta' => __('Private responses'), 'icon' => 'fa-message-lines'],
                        ['value' => 'lead', 'label' => __('Lead Form'), 'meta' => __('Lead submissions'), 'icon' => 'fa-user-plus'],
                    ]"
                    :selected="$campaignType"
                    model="campaignType"
                    :placeholder="__('All campaign types')"
                    :search-placeholder="__('Search campaign type...')"
                    :empty-text="__('No campaign types found')"
                    icon="fa-light fa-bullhorn"
                />
            </div>
        </div>

        <div class="flex items-center gap-2 overflow-x-auto border-b p-2" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            @foreach ([
                'overview' => __('Overview'),
                'leads' => __('Leads'),
                'reviews' => __('Reviews'),
            ] as $key => $label)
                <button type="button" wire:click="setTab('{{ $key }}')" wire:loading.attr="disabled" wire:target="setTab,businessFilter,dateRange,campaignType" class="whitespace-nowrap rounded-xl border px-4 py-2 text-sm font-semibold transition disabled:cursor-wait disabled:opacity-70" style="{{ $tab === $key
                    ? 'border-color: rgba(var(--theme-accent-rgb),0.16); background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);'
                    : 'border-color: transparent; color: var(--theme-muted-text-color);'
                }}">
                    {{ $label }}
                </button>
            @endforeach
            <div wire:loading.flex wire:target="setTab,businessFilter,dateRange,campaignType" class="ml-auto hidden items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold" style="border-color: rgba(var(--theme-accent-rgb),0.18); background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                <i class="fa-light fa-spinner-third animate-spin"></i>
                <span>{{ __('Updating reports...') }}</span>
            </div>
            <button type="button" wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv" class="ml-auto inline-flex h-10 shrink-0 items-center gap-2 rounded-xl border px-4 text-sm font-semibold shadow-sm transition hover:-translate-y-px disabled:pointer-events-none disabled:opacity-60" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 92%, transparent);">
                <i class="fa-light fa-spinner-third animate-spin" wire:loading wire:target="exportCsv"></i>
                <i class="fa-light fa-file-csv" wire:loading.remove wire:target="exportCsv"></i>
                <span>{{ __('Export CSV') }}</span>
            </button>
        </div>

        <div class="relative p-5">
            <div wire:loading.flex wire:target="setTab,businessFilter,dateRange,campaignType" class="absolute inset-0 z-40 hidden items-start justify-center rounded-b-[1.25rem] px-5 pt-8 backdrop-blur-[2px]" style="background-color: color-mix(in srgb, var(--theme-surface-overlay) 72%, transparent);">
                <div class="flex items-center gap-3 rounded-2xl border px-4 py-3 shadow-[0_20px_60px_-32px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent); color: var(--theme-header-text-color);">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-spinner-third animate-spin"></i>
                    </span>
                    <div>
                        <p class="text-sm font-semibold">{{ __('Updating reports') }}</p>
                        <p class="mt-0.5 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Applying filters and refreshing dashboard data...') }}</p>
                    </div>
                </div>
            </div>
            <div class="{{ $tab === 'overview' ? 'space-y-5' : 'hidden' }}">
                <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['label' => __('Businesses'), 'value' => $totals['businesses'], 'description' => __('Profiles'), 'icon' => 'fa-light fa-store'],
                        ['label' => __('Campaigns'), 'value' => $totals['campaigns'], 'description' => __('Growth funnels'), 'icon' => 'fa-light fa-bullhorn'],
                        ['label' => __('Visits'), 'value' => $totals['visits'], 'description' => __('QR scans & link visits'), 'icon' => 'fa-light fa-chart-line'],
                        ['label' => __('Review Clicks'), 'value' => $totals['review_clicks'], 'description' => __('Sent to public review'), 'icon' => 'fa-light fa-star'],
                        ['label' => __('Leads'), 'value' => $totals['leads'], 'description' => __('Lead submissions'), 'icon' => 'fa-light fa-user-plus'],
                        ['label' => __('Bookings'), 'value' => $totals['bookings'], 'description' => __('Appointments'), 'icon' => 'fa-light fa-calendar-check'],
                        ['label' => __('Coupons'), 'value' => $totals['coupons'], 'description' => __('Claims'), 'icon' => 'fa-light fa-ticket'],
                        ['label' => __('Feedback'), 'value' => $totals['feedbacks'] + $totals['form_feedbacks'], 'description' => __('Private responses'), 'icon' => 'fa-light fa-message-lines'],
                        ['label' => __('Conversion Rate'), 'value' => $totals['conversion_rate'], 'suffix' => '%', 'description' => __('Conversions / visits'), 'icon' => 'fa-light fa-chart-simple'],
                    ] as $metric)
                        <article class="rounded-[1.1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background:
                            linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.075), transparent 42%),
                            color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[2rem] font-semibold tracking-[-0.055em]" style="color: var(--theme-header-text-color);">{{ is_numeric($metric['value']) ? number_format((float) $metric['value'], ($metric['suffix'] ?? '') === '%' ? 1 : 0) : $metric['value'] }}{{ $metric['suffix'] ?? '' }}</p>
                                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p>
                                    <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $metric['description'] }}</p>
                                </div>
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                                    <i class="{{ $metric['icon'] }}"></i>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>

                <div class="grid gap-5 xl:grid-cols-3">
                    <x-report-card :title="__('Booking performance')" :subtitle="__('Appointment status quality across selected campaigns.')">
                        <div class="grid grid-cols-2 gap-3">
                            @foreach ([
                                __('Pending') => $bookingStats['pending'],
                                __('Confirmed') => $bookingStats['confirmed'],
                                __('Completed') => $bookingStats['completed'],
                                __('Cancelled') => $bookingStats['cancelled'],
                            ] as $label => $value)
                                <div class="rounded-2xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                    <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($value) }}</p>
                                    <p class="mt-1 text-xs font-semibold" style="color: var(--theme-muted-text-color);">{{ $label }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 rounded-2xl border p-4" style="border-color: rgba(var(--theme-accent-rgb), .2); background-color: rgba(var(--theme-accent-rgb), .07);">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Completion rate') }}</span>
                                <span class="text-lg font-semibold" style="color: var(--theme-accent);">{{ number_format($bookingStats['completion_rate'], 1) }}%</span>
                            </div>
                        </div>
                    </x-report-card>

                    <x-report-card :title="__('Coupon performance')" :subtitle="__('Claim quality and redemption progress.')">
                        <div class="grid grid-cols-2 gap-3">
                            @foreach ([
                                __('Claims') => $couponStats['claims'],
                                __('Used') => $couponStats['used'],
                                __('Unused') => $couponStats['unused'],
                                __('Expired campaigns') => $couponStats['expired_campaigns'],
                            ] as $label => $value)
                                <div class="rounded-2xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                    <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($value) }}</p>
                                    <p class="mt-1 text-xs font-semibold" style="color: var(--theme-muted-text-color);">{{ $label }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 rounded-2xl border p-4" style="border-color: rgba(var(--theme-accent-rgb), .2); background-color: rgba(var(--theme-accent-rgb), .07);">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Redemption rate') }}</span>
                                <span class="text-lg font-semibold" style="color: var(--theme-accent);">{{ number_format($couponStats['redemption_rate'], 1) }}%</span>
                            </div>
                        </div>
                    </x-report-card>

                    <x-report-card :title="__('Review quality')" :subtitle="__('Positive routing versus private recovery demand.')">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($reviewStats['avg_rating'], 1) }}/5</p>
                                <p class="mt-1 text-xs font-semibold" style="color: var(--theme-muted-text-color);">{{ __('Average rating') }}</p>
                            </div>
                            <div class="rounded-2xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($reviewStats['total_ratings']) }}</p>
                                <p class="mt-1 text-xs font-semibold" style="color: var(--theme-muted-text-color);">{{ __('Ratings') }}</p>
                            </div>
                            <div class="rounded-2xl border p-3" style="border-color: rgba(var(--theme-success-color-rgb), .2); background-color: rgba(var(--theme-success-color-rgb), .07);">
                                <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($reviewStats['positive_ratings']) }}</p>
                                <p class="mt-1 text-xs font-semibold" style="color: var(--theme-muted-text-color);">{{ __('4-5 star') }}</p>
                            </div>
                            <div class="rounded-2xl border p-3" style="border-color: rgba(var(--theme-warning-color-rgb), .24); background-color: rgba(var(--theme-warning-color-rgb), .08);">
                                <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($reviewStats['low_score_feedback']) }}</p>
                                <p class="mt-1 text-xs font-semibold" style="color: var(--theme-muted-text-color);">{{ __('Low-score') }}</p>
                            </div>
                        </div>
                    </x-report-card>
                </div>

                <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_24rem]">
                    @if ($dailyScans->count() > 0)
                        <x-ui.chart
                            :title="__('Visit Activity')"
                            :description="__('QR scans and campaign link visits across selected businesses.')"
                            type="areaspline"
                            height="320"
                            :categories="$dailyScans->map(fn ($day) => \Carbon\Carbon::parse($day->day)->format('M d'))->all()"
                            :series="[
                                ['name' => __('Visits'), 'data' => $dailyScans->pluck('total')->map(fn ($value) => (int) $value)->all()],
                            ]"
                            :legend="false"
                        />
                    @else
                        <x-report-card :title="__('Visit Activity')" :subtitle="__('QR scans and campaign link visits across selected businesses.')">
                            <x-report-empty :title="__('No visit activity yet')" :description="__('Launch campaigns across your businesses to start collecting global reports.')" />
                            <div class="mt-4 text-center">
                                @if (($totals['businesses'] ?? 0) <= 0)
                                    <x-ui.button href="{{ route('portal.businesses.create') }}" wire:navigate size="sm">
                                        <i class="fa-light fa-plus"></i>{{ __('Create Business') }}
                                    </x-ui.button>
                                @else
                                    <x-ui.button href="{{ route('portal.review-booster') }}" wire:navigate size="sm">
                                        <i class="fa-light fa-plus"></i>{{ __('Create Campaign') }}
                                    </x-ui.button>
                                @endif
                            </div>
                        </x-report-card>
                    @endif

                    <x-report-card :title="__('Report scope')" :subtitle="__('Current filters')">
                        <div class="space-y-3">
                            @foreach ([
                                __('Business') => $businessFilter === 'all' ? __('All businesses') : optional($businesses->firstWhere('id', (int) $businessFilter))->name,
                                __('Date range') => $dateRange === 'all' ? __('All time') : __('Last :days days', ['days' => $dateRange]),
                                __('Campaign type') => $campaignType === 'all' ? __('All campaign types') : str($campaignType)->headline(),
                            ] as $label => $value)
                                <div class="flex items-center justify-between gap-3 rounded-2xl border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                    <span class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $label }}</span>
                                    <span class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </x-report-card>
                </div>

                <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_24rem]">
                    <x-report-card :title="__('Top Businesses')" :subtitle="__('Businesses ranked by selected report performance.')">
                        @if ($topBusinesses->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead style="color: var(--theme-muted-text-color);">
                                        <tr>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Business') }}</th>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Visits') }}</th>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Leads') }}</th>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Review Clicks') }}</th>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Bookings') }}</th>
                                            <th class="py-2 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Conv.') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                        @foreach ($topBusinesses as $row)
                                            <tr>
                                                <td class="py-3 pr-4">
                                                    <a href="{{ route('portal.businesses.show', $row['business']) }}" wire:navigate class="font-semibold" style="color: var(--theme-header-text-color);">{{ $row['business']->name }}</a>
                                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ number_format($row['campaigns']) }} {{ __('campaigns') }}</p>
                                                </td>
                                                <td class="py-3 pr-4">{{ number_format($row['visits']) }}</td>
                                                <td class="py-3 pr-4">{{ number_format($row['leads']) }}</td>
                                                <td class="py-3 pr-4">{{ number_format($row['review_clicks']) }}</td>
                                                <td class="py-3 pr-4">{{ number_format($row['bookings']) }}</td>
                                                <td class="py-3">{{ $row['conversion_rate'] }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <x-report-empty :title="__('No business performance yet')" :description="__('Business rankings will appear after campaigns collect visits and conversions.')" />
                        @endif
                    </x-report-card>

                    <x-report-card :title="__('Recent Activity')" :subtitle="__('Latest conversion signals across all businesses.')">
                        @if ($recentActivity->count() > 0)
                            <div class="space-y-3">
                                @foreach ($recentActivity as $item)
                                    <div class="rounded-2xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $item['customer'] }} {{ $item['action'] }}</p>
                                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $item['business'] }} - {{ $item['campaign'] }} - {{ $item['time']?->diffForHumans() }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <x-report-empty :title="__('No recent activity yet')" :description="__('Lead, booking, coupon, review, and feedback activity will appear here.')" />
                        @endif
                    </x-report-card>
                </div>

                <div class="grid gap-5 xl:grid-cols-2">
                    <x-report-card :title="__('Top Campaigns')" :subtitle="__('Campaigns ranked by visits and conversion signals.')">
                        @if ($topCampaigns->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead style="color: var(--theme-muted-text-color);">
                                        <tr>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Campaign') }}</th>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Business') }}</th>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Visits') }}</th>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Leads') }}</th>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Review Clicks') }}</th>
                                            <th class="py-2 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Conv.') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                        @foreach ($topCampaigns as $row)
                                            <tr>
                                                <td class="max-w-[14rem] py-3 pr-4">
                                                    <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $row['campaign']->name }}</p>
                                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ str($row['campaign']->type)->headline() }}</p>
                                                </td>
                                                <td class="py-3 pr-4">{{ $row['campaign']->business?->name ?: __('No business') }}</td>
                                                <td class="py-3 pr-4">{{ number_format($row['campaign']->scans_count) }}</td>
                                                <td class="py-3 pr-4">{{ number_format($row['leads']) }}</td>
                                                <td class="py-3 pr-4">{{ number_format($row['review_clicks']) }}</td>
                                                <td class="py-3">{{ $row['conversion_rate'] }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <x-report-empty :title="__('No campaign performance yet')" :description="__('Top campaign data will appear after campaigns collect visits and conversions.')" />
                        @endif
                    </x-report-card>

                    <x-report-card :title="__('Campaign Type Performance')" :subtitle="__('Which growth tools are creating results.')">
                        <x-ui.chart
                            :title="null"
                            :description="null"
                            type="column"
                            height="300"
                            :categories="$campaignTypePerformance->pluck('type')->all()"
                            :series="[
                                ['name' => __('Visits'), 'data' => $campaignTypePerformance->pluck('visits')->map(fn ($value) => (int) $value)->all()],
                                ['name' => __('Leads'), 'data' => $campaignTypePerformance->pluck('leads')->map(fn ($value) => (int) $value)->all()],
                                ['name' => __('Review Clicks'), 'data' => $campaignTypePerformance->pluck('review_clicks')->map(fn ($value) => (int) $value)->all()],
                                ['name' => __('Bookings'), 'data' => $campaignTypePerformance->pluck('bookings')->map(fn ($value) => (int) $value)->all()],
                                ['name' => __('Coupons'), 'data' => $campaignTypePerformance->pluck('coupons')->map(fn ($value) => (int) $value)->all()],
                            ]"
                            :legend="true"
                        />
                    </x-report-card>
                </div>
            </div>

            <section id="leads" class="{{ $tab === 'leads' ? 'block' : 'hidden' }} space-y-5">
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                    @foreach ([
                        ['label' => __('Total Leads'), 'value' => $leadStats['total'], 'description' => __('All captured contacts'), 'icon' => 'fa-light fa-users'],
                        ['label' => __('New Leads'), 'value' => $leadStats['new'], 'description' => __('Awaiting follow-up'), 'icon' => 'fa-light fa-user-plus'],
                        ['label' => __('Contacted'), 'value' => $leadStats['contacted'], 'description' => __('Status updates'), 'icon' => 'fa-light fa-phone'],
                        ['label' => __('Converted'), 'value' => $leadStats['converted'], 'description' => __('Successfully converted customers'), 'icon' => 'fa-light fa-badge-check'],
                        ['label' => __('Lost'), 'value' => $leadStats['lost'], 'description' => __('Closed without conversion'), 'icon' => 'fa-light fa-circle-xmark'],
                        ['label' => __('Lead Rate'), 'value' => $leadStats['conversion_rate'], 'description' => __('Leads / visits'), 'icon' => 'fa-light fa-chart-simple', 'suffix' => '%'],
                    ] as $metric)
                        <article class="rounded-[1.1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background: linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.07), transparent 44%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[1.75rem] font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ is_numeric($metric['value']) ? number_format((float) $metric['value'], ($metric['suffix'] ?? '') === '%' ? 1 : 0) : $metric['value'] }}{{ $metric['suffix'] ?? '' }}</p>
                                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p>
                                    <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $metric['description'] }}</p>
                                </div>
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                                    <i class="{{ $metric['icon'] }}"></i>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="grid gap-5 xl:grid-cols-[minmax(0,25rem)_1fr]">
                    <x-report-card :title="__('Lead Sources')" :subtitle="__('Where captured contacts are coming from.')">
                        <div class="space-y-3">
                            @foreach ($leadSources as $source)
                                <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62);">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb), .1); color: var(--theme-accent);">
                                            <i class="{{ $source['icon'] }}"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-3">
                                                <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $source['type'] }}</p>
                                                <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($source['count']) }}</span>
                                            </div>
                                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Conversion') }} {{ number_format($source['conversion_rate'], 1) }}% - {{ __('Last lead') }} {{ $source['last'] ? \Carbon\Carbon::parse($source['last'])->diffForHumans() : '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-report-card>

                    <x-report-card :title="__('Lead Inbox')" :subtitle="__('Customer leads from lead forms, bookings, coupons, and feedback forms.')">
                        @if ($leadInbox->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="text-xs uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">
                                        <tr>
                                            <th class="pb-3 pr-4">{{ __('Customer') }}</th>
                                            <th class="pb-3 pr-4">{{ __('Business') }}</th>
                                            <th class="pb-3 pr-4">{{ __('Campaign') }}</th>
                                            <th class="pb-3 pr-4">{{ __('Source') }}</th>
                                            <th class="pb-3 pr-4">{{ __('Status') }}</th>
                                            <th class="pb-3">{{ __('Created') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .62);">
                                        @foreach ($leadInbox as $lead)
                                            <tr>
                                                <td class="py-4 pr-4">
                                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $lead['name'] }}</p>
                                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $lead['phone'] ?: $lead['email'] ?: __('No contact') }}</p>
                                                </td>
                                                <td class="py-4 pr-4">
                                                    @if ($lead['business_id'])
                                                        <a href="{{ route('portal.businesses.show', $lead['business_id']) }}" wire:navigate class="font-semibold" style="color: var(--theme-accent);">{{ $lead['business'] }}</a>
                                                    @else
                                                        {{ $lead['business'] }}
                                                    @endif
                                                </td>
                                                <td class="py-4 pr-4">{{ $lead['campaign'] }}</td>
                                                <td class="py-4 pr-4">{{ $lead['source'] }}</td>
                                                <td class="py-4 pr-4">
                                                    <x-ui.badge :variant="strtolower($lead['status']) === 'completed' || strtolower($lead['status']) === 'used' ? 'success' : 'neutral'">{{ $lead['status'] }}</x-ui.badge>
                                                </td>
                                                <td class="py-4 text-xs" style="color: var(--theme-muted-text-color);">{{ $lead['created_at']?->format('M d, Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="rounded-2xl border p-8 text-center" style="border-color: rgba(var(--theme-border-color-rgb), .62); background: linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.07), transparent 52%);">
                                <x-report-empty :title="__('No leads yet')" :description="__('Leads will appear here when customers submit lead forms, claim coupons, book appointments, or send feedback.')" />
                                <div class="mt-5">
                                    @if ($businesses->count() > 0)
                                        <x-ui.button href="{{ route('portal.lead-forms') }}" wire:navigate size="sm">
                                            <i class="fa-light fa-user-plus"></i>{{ __('Create Lead Form') }}
                                        </x-ui.button>
                                    @else
                                        <x-ui.button href="{{ route('portal.businesses.create') }}" wire:navigate size="sm">
                                            <i class="fa-light fa-plus"></i>{{ __('Create Business') }}
                                        </x-ui.button>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </x-report-card>
                </div>
            </section>

            <section id="reviews" class="{{ $tab === 'reviews' ? 'block' : 'hidden' }} space-y-5">
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                    @foreach ([
                        ['label' => __('Total Ratings'), 'value' => $reviewStats['total_ratings'], 'description' => __('Ratings captured'), 'icon' => 'fa-light fa-star'],
                        ['label' => __('Avg Rating'), 'value' => $reviewStats['avg_rating'], 'description' => __('Across review boosters'), 'icon' => 'fa-light fa-star-half-stroke', 'suffix' => '/5'],
                        ['label' => __('Public Review Clicks'), 'value' => $reviewStats['google_review_clicks'], 'description' => __('Sent to Google Review'), 'icon' => 'fa-brands fa-google'],
                        ['label' => __('Low-score Feedback'), 'value' => $reviewStats['low_score_feedback'], 'description' => __('Needs attention'), 'icon' => 'fa-light fa-message-exclamation'],
                        ['label' => __('Resolved'), 'value' => $reviewStats['resolved_feedback'], 'description' => __('Closed feedback'), 'icon' => 'fa-light fa-circle-check'],
                    ] as $metric)
                        <article class="rounded-[1.1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background: linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.07), transparent 44%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[1.85rem] font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ is_numeric($metric['value']) ? number_format((float) $metric['value'], ($metric['label'] === __('Avg Rating')) ? 1 : 0) : $metric['value'] }}{{ $metric['suffix'] ?? '' }}</p>
                                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p>
                                    <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $metric['description'] }}</p>
                                </div>
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                                    <i class="{{ $metric['icon'] }}"></i>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="grid gap-5 xl:grid-cols-[minmax(0,26rem)_1fr]">
                    <x-report-card :title="__('Rating distribution')" :subtitle="__('How customers rated experiences across all businesses.')">
                        <div class="space-y-4">
                            @foreach ($ratingDistribution as $row)
                                <div>
                                    <div class="mb-2 flex items-center justify-between text-sm">
                                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ $row['rating'] }} {{ __('stars') }}</span>
                                        <span style="color: var(--theme-muted-text-color);">{{ number_format($row['total']) }}</span>
                                    </div>
                                    <div class="h-2 rounded-full" style="background-color: rgba(var(--theme-border-color-rgb),0.45);">
                                        <div class="h-2 rounded-full" style="width: {{ $row['percent'] }}%; background-color: var(--theme-accent);"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-report-card>

                    <x-report-card :title="__('Review health')" :subtitle="__('Positive routing versus private recovery workload.')">
                        <div class="grid gap-3 md:grid-cols-3">
                            <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: rgba(var(--theme-accent-rgb), .06);">
                                <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($reviewStats['positive_ratings']) }}</p>
                                <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Positive ratings') }}</p>
                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('4-5 star responses') }}</p>
                            </div>
                            <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-warning-color-rgb), .24); background-color: rgba(var(--theme-warning-color-rgb), .08);">
                                <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($reviewStats['negative_ratings']) }}</p>
                                <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Low-score ratings') }}</p>
                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('1-3 star feedback') }}</p>
                            </div>
                            <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62);">
                                <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $reviewStats['total_ratings'] > 0 ? number_format(($reviewStats['positive_ratings'] / max(1, $reviewStats['total_ratings'])) * 100, 1) : 0 }}%</p>
                                <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Positive rate') }}</p>
                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Positive / total ratings') }}</p>
                            </div>
                        </div>
                    </x-report-card>
                </div>

                <div class="grid gap-5 xl:grid-cols-2">
                    <x-report-card :title="__('Low-score feedback')" :subtitle="__('Private feedback that needs fast follow-up.')">
                        @if ($lowScoreFeedback->count() > 0)
                            <div class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .62);">
                                @foreach ($lowScoreFeedback as $feedback)
                                    <div class="flex gap-3 py-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-sm font-semibold" style="background-color: rgba(var(--theme-warning-color-rgb), .12); color: var(--theme-warning-color);">{{ $feedback->rating }}/5</div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $feedback->customer_name ?: __('Guest') }}</p>
                                                <x-ui.badge variant="warning">{{ __('Needs reply') }}</x-ui.badge>
                                            </div>
                                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $feedback->campaign?->business?->name ?: __('No business') }} - {{ $feedback->campaign?->name ?: __('Campaign removed') }}</p>
                                            <p class="mt-2 line-clamp-2 text-sm" style="color: var(--theme-muted-text-color);">{{ $feedback->message ?: __('No message provided.') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <x-report-empty :title="__('No low-score feedback')" :description="__('Private recovery feedback from 1-3 star ratings will appear here.')" />
                        @endif
                    </x-report-card>

                    <x-report-card :title="__('Top businesses by reviews')" :subtitle="__('Businesses generating the most rating activity.')">
                        @if ($topReviewBusinesses->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="text-xs uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">
                                        <tr>
                                            <th class="pb-3 pr-4">{{ __('Business') }}</th>
                                            <th class="pb-3 pr-4">{{ __('Ratings') }}</th>
                                            <th class="pb-3 pr-4">{{ __('Avg') }}</th>
                                            <th class="pb-3 pr-4">{{ __('Google') }}</th>
                                            <th class="pb-3">{{ __('Low-score') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .62);">
                                        @foreach ($topReviewBusinesses as $row)
                                            <tr>
                                                <td class="py-3 pr-4 font-semibold" style="color: var(--theme-header-text-color);">{{ $row['business']->name }}</td>
                                                <td class="py-3 pr-4">{{ number_format($row['ratings']) }}</td>
                                                <td class="py-3 pr-4">{{ number_format($row['avg_rating'], 1) }}/5</td>
                                                <td class="py-3 pr-4">{{ number_format($row['google_clicks']) }}</td>
                                                <td class="py-3">{{ number_format($row['low_score']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <x-report-empty :title="__('No review ranking yet')" :description="__('Businesses will appear here after Review Booster captures ratings.')" />
                        @endif
                    </x-report-card>
                </div>

                <x-report-card :title="__('All Reviews')" :subtitle="__('Global rating and feedback inbox across every business.')">
                    @if ($reviewInbox->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="text-xs uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">
                                    <tr>
                                        <th class="pb-3 pr-4">{{ __('Customer') }}</th>
                                        <th class="pb-3 pr-4">{{ __('Business') }}</th>
                                        <th class="pb-3 pr-4">{{ __('Campaign') }}</th>
                                        <th class="pb-3 pr-4">{{ __('Rating') }}</th>
                                        <th class="pb-3 pr-4">{{ __('Route') }}</th>
                                        <th class="pb-3 pr-4">{{ __('Status') }}</th>
                                        <th class="pb-3 pr-4">{{ __('Date') }}</th>
                                        <th class="pb-3 text-right">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .62);">
                                    @foreach ($reviewInbox as $feedback)
                                        <tr>
                                            <td class="py-4 pr-4">
                                                <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $feedback->customer_name ?: __('Guest') }}</p>
                                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $feedback->customer_phone ?: $feedback->customer_email ?: __('No contact') }}</p>
                                            </td>
                                            <td class="py-4 pr-4">{{ $feedback->campaign?->business?->name ?: __('No business') }}</td>
                                            <td class="py-4 pr-4">{{ $feedback->campaign?->name ?: __('Campaign removed') }}</td>
                                            <td class="py-4 pr-4 font-semibold">{{ $feedback->rating }}/5</td>
                                            <td class="py-4 pr-4">{{ $feedback->rating >= 4 ? __('Sent to Google') : __('Private feedback') }}</td>
                                            <td class="py-4 pr-4">
                                                <x-ui.badge :variant="$feedback->rating <= 3 ? 'warning' : 'success'">{{ $feedback->rating <= 3 ? __('Needs reply') : __('Positive') }}</x-ui.badge>
                                            </td>
                                            <td class="py-4 pr-4 text-xs" style="color: var(--theme-muted-text-color);">{{ $feedback->created_at?->format('M d, Y') }}</td>
                                            <td class="py-4 text-right">
                                                <x-ui.button
                                                    href="{{ route('portal.ai-studio.review-reply', ['feedback_id' => $feedback->id]) }}"
                                                    wire:navigate
                                                    size="sm"
                                                    variant="secondary"
                                                >
                                                    <i class="fa-light fa-wand-magic-sparkles"></i>{{ __('AI Reply') }}
                                                </x-ui.button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="rounded-2xl border p-8 text-center" style="border-color: rgba(var(--theme-border-color-rgb), .62); background: linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.07), transparent 52%);">
                            <x-report-empty :title="__('No review data yet')" :description="__('Review Booster results will appear here after customers choose a rating, continue to Google Review, or submit private feedback.')" />
                            <div class="mt-5">
                                <x-ui.button href="{{ route('portal.review-booster') }}" wire:navigate size="sm">
                                    <i class="fa-light fa-star"></i>{{ __('Create Review Booster') }}
                                </x-ui.button>
                            </div>
                        </div>
                    @endif
                </x-report-card>
            </section>
        </div>
    </section>
</div>
