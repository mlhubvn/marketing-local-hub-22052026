<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6" x-data="{ reportTab: 'overview' }">
    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 36%),
        linear-gradient(35deg, rgba(var(--theme-success-color-rgb),0.09), transparent 38%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-6 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <a href="{{ route('portal.businesses.show', $business) }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold" style="color: var(--theme-muted-text-color);">
                    <i class="fa-light fa-arrow-left"></i>{{ $business->name }}
                </a>
                <h1 class="mt-4 text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('Business Reports') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ __('One business-level report for campaign performance, visits, leads, bookings, coupons, feedback, reviews, and customer growth.') }}
                </p>
            </div>

            <div class="rounded-[1.15rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Conversion rate') }}</p>
                <p class="mt-4 text-5xl font-semibold tracking-[-0.06em]" style="color: var(--theme-header-text-color);">{{ $totals['conversion_rate'] }}%</p>
                <p class="mt-2 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Total conversions divided by visits or QR scans.') }}</p>
            </div>
        </div>
    </section>

    @include('appbusinessprofiles::partials.business-tabs', ['business' => $business, 'active' => 'reports'])

    <section class="rounded-[1.15rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-3 md:grid-cols-3">
            <x-ui.select wire:model.live="dateRange" :label="__('Date range')">
                <option value="7">{{ __('Last 7 days') }}</option>
                <option value="30">{{ __('Last 30 days') }}</option>
                <option value="90">{{ __('Last 90 days') }}</option>
                <option value="all">{{ __('All time') }}</option>
            </x-ui.select>
            <x-ui.select wire:model.live="campaignType" :label="__('Campaign type')">
                <option value="all">{{ __('All campaign types') }}</option>
                <option value="review">{{ __('Review Booster') }}</option>
                <option value="booking">{{ __('Booking Page') }}</option>
                <option value="coupon">{{ __('Coupon') }}</option>
                <option value="feedback">{{ __('Feedback Form') }}</option>
                <option value="lead">{{ __('Lead Form') }}</option>
            </x-ui.select>
            <x-ui.select wire:model.live="campaignFilter" :label="__('Campaign')">
                <option value="all">{{ __('All campaigns') }}</option>
                @foreach ($campaignOptions as $campaign)
                    <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                @endforeach
            </x-ui.select>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => __('Campaigns'), 'value' => $totals['campaigns'], 'icon' => 'fa-light fa-bullhorn'],
            ['label' => __('Review Clicks'), 'value' => $totals['review_clicks'], 'icon' => 'fa-light fa-star'],
            ['label' => __('Leads'), 'value' => $totals['leads'], 'icon' => 'fa-light fa-address-book'],
            ['label' => __('Bookings'), 'value' => $totals['bookings'], 'icon' => 'fa-light fa-calendar-check'],
            ['label' => __('Coupons'), 'value' => $totals['coupons'], 'icon' => 'fa-light fa-ticket'],
            ['label' => __('Feedback'), 'value' => $totals['feedback'], 'icon' => 'fa-light fa-message-lines'],
            ['label' => __('QR Scans'), 'value' => $totals['qr_scans'], 'icon' => 'fa-light fa-qrcode'],
            ['label' => __('Conversion Rate'), 'value' => $totals['conversion_rate'].'%', 'icon' => 'fa-light fa-chart-line'],
        ] as $metric)
            <article class="rounded-[1.1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background:
                linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.075), transparent 42%),
                color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[1.85rem] font-semibold tracking-[-0.055em]" style="color: var(--theme-header-text-color);">{{ is_numeric($metric['value']) ? number_format($metric['value']) : $metric['value'] }}</p>
                        <p class="mt-2 text-sm font-semibold leading-5" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="{{ $metric['icon'] }}"></i>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="flex gap-2 overflow-x-auto border-b p-2" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            @foreach ([
                'overview' => __('Overview'),
                'reviews' => __('Reviews'),
                'leads' => __('Leads'),
                'bookings' => __('Bookings'),
                'coupons' => __('Coupons'),
                'feedback' => __('Feedback'),
                'customers' => __('Customers'),
            ] as $key => $label)
                <button type="button" x-on:click="reportTab = '{{ $key }}'" class="whitespace-nowrap rounded-xl border px-4 py-2 text-sm font-semibold transition" x-bind:style="reportTab === '{{ $key }}'
                    ? 'border-color: rgba(var(--theme-accent-rgb),0.16); background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);'
                    : 'border-color: transparent; color: var(--theme-muted-text-color);'">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="p-5">
            <div x-show="reportTab === 'overview'" class="space-y-5">
                <div class="grid gap-5 xl:grid-cols-2">
                    <x-ui.chart
                        :title="__('Conversion Funnel')"
                        :description="__('From visits to measurable outcomes.')"
                        type="bar"
                        height="320"
                        :categories="collect($funnel)->pluck('label')->all()"
                        :series="[
                            [
                                'name' => __('Conversions'),
                                'data' => collect($funnel)->pluck('value')->map(fn ($value) => (int) $value)->all(),
                            ],
                        ]"
                        :legend="false"
                        :options="[
                            'chart' => ['type' => 'bar'],
                            'xAxis' => ['categories' => collect($funnel)->pluck('label')->all()],
                            'yAxis' => ['min' => 0],
                            'legend' => ['enabled' => false],
                            'tooltip' => ['pointFormat' => '<b>{point.y}</b>'],
                            'series' => [[
                                'name' => __('Conversions'),
                                'data' => collect($funnel)->pluck('value')->map(fn ($value) => (int) $value)->all(),
                            ]],
                        ]"
                    />

                    @if ($dailyActivity->sum(fn ($day) => $day['scans'] + $day['leads'] + $day['bookings'] + $day['coupons'] + $day['feedback'] + $day['review_clicks']) > 0)
                        <x-ui.chart
                            :title="__('Daily Activity')"
                            :description="__('Scans, leads, bookings, coupons, feedback, and review clicks.')"
                            type="areaspline"
                            height="320"
                            :categories="$dailyActivity->map(fn ($day) => \Carbon\Carbon::parse($day['day'])->format('M d'))->all()"
                            :series="[
                                ['name' => __('QR Scans'), 'data' => $dailyActivity->pluck('scans')->map(fn ($value) => (int) $value)->all()],
                                ['name' => __('Leads'), 'data' => $dailyActivity->pluck('leads')->map(fn ($value) => (int) $value)->all()],
                                ['name' => __('Bookings'), 'data' => $dailyActivity->pluck('bookings')->map(fn ($value) => (int) $value)->all()],
                                ['name' => __('Coupons'), 'data' => $dailyActivity->pluck('coupons')->map(fn ($value) => (int) $value)->all()],
                                ['name' => __('Feedback'), 'data' => $dailyActivity->pluck('feedback')->map(fn ($value) => (int) $value)->all()],
                                ['name' => __('Review Clicks'), 'data' => $dailyActivity->pluck('review_clicks')->map(fn ($value) => (int) $value)->all()],
                            ]"
                            :legend="true"
                        />
                    @else
                        <x-report-card :title="__('Daily Activity')" :subtitle="__('Scans, leads, bookings, coupons, feedback, and review clicks.')">
                            <div class="space-y-3">
                                <x-report-empty :title="__('No campaign activity yet')" :description="__('Launch your first campaign to start collecting reports.')" />
                                <div class="mt-4 text-center">
                                    <x-ui.button href="{{ route('portal.businesses.campaigns.index', $business) }}" wire:navigate size="sm">
                                        <i class="fa-light fa-plus"></i>{{ __('Create Campaign') }}
                                    </x-ui.button>
                                </div>
                            </div>
                        </x-report-card>
                    @endif
                </div>

                <div class="grid gap-5 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,.8fr)]">
                    <x-report-card :title="__('Top Campaigns')" :subtitle="__('Campaign performance by visits, conversions, and selected ranking.')">
                        <div class="mb-4 max-w-xs">
                            <x-ui.select wire:model.live="topSort" :label="__('Sort')">
                                <option value="scans">{{ __('Top by Scans') }}</option>
                                <option value="leads">{{ __('Top by Leads') }}</option>
                                <option value="bookings">{{ __('Top by Bookings') }}</option>
                                <option value="coupons">{{ __('Top by Coupons') }}</option>
                                <option value="reviews">{{ __('Top by Review Clicks') }}</option>
                                <option value="conversion_rate">{{ __('Top by Conversion Rate') }}</option>
                            </x-ui.select>
                        </div>
                        @if ($topCampaigns->count() > 0)
                            <x-ui.chart
                                class="mb-5"
                                :title="null"
                                :description="null"
                                type="column"
                                height="260"
                                :categories="$topCampaigns->map(fn ($row) => str($row['campaign']->name)->limit(18)->toString())->all()"
                                :series="[
                                    ['name' => __('Scans'), 'data' => $topCampaigns->map(fn ($row) => (int) $row['campaign']->scans_count)->all()],
                                    ['name' => __('Leads'), 'data' => $topCampaigns->pluck('leads')->map(fn ($value) => (int) $value)->all()],
                                    ['name' => __('Bookings'), 'data' => $topCampaigns->pluck('bookings')->map(fn ($value) => (int) $value)->all()],
                                    ['name' => __('Coupons'), 'data' => $topCampaigns->pluck('coupons')->map(fn ($value) => (int) $value)->all()],
                                    ['name' => __('Review Clicks'), 'data' => $topCampaigns->pluck('review_clicks')->map(fn ($value) => (int) $value)->all()],
                                ]"
                                :legend="true"
                            />
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead style="color: var(--theme-muted-text-color);">
                                        <tr>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Campaign') }}</th>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Scans') }}</th>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Leads') }}</th>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Bookings') }}</th>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Coupons') }}</th>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Reviews') }}</th>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Feedback') }}</th>
                                            <th class="py-2 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Conv. Rate') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                        @foreach ($topCampaigns as $row)
                                            <tr>
                                                <td class="max-w-[16rem] py-3 pr-4">
                                                    <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $row['campaign']->name }}</p>
                                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ str($row['campaign']->type)->headline() }}</p>
                                                </td>
                                                <td class="py-3 pr-4">{{ number_format($row['campaign']->scans_count) }}</td>
                                                <td class="py-3 pr-4">{{ number_format($row['leads']) }}</td>
                                                <td class="py-3 pr-4">{{ number_format($row['bookings']) }}</td>
                                                <td class="py-3 pr-4">{{ number_format($row['coupons']) }}</td>
                                                <td class="py-3 pr-4">{{ number_format($row['review_clicks']) }}</td>
                                                <td class="py-3 pr-4">{{ number_format($row['feedback']) }}</td>
                                                <td class="py-3">{{ $row['conversion_rate'] }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <x-report-empty :title="__('No campaign data yet')" :description="__('Top campaigns will appear after this business starts collecting scans.')" />
                            <div class="mt-4 text-center">
                                <x-ui.button href="{{ route('portal.businesses.campaigns.index', $business) }}" wire:navigate size="sm">
                                    <i class="fa-light fa-plus"></i>{{ __('Create Campaign') }}
                                </x-ui.button>
                            </div>
                        @endif
                    </x-report-card>

                    <x-report-card :title="__('Recent Conversions')" :subtitle="__('Latest customer actions across all campaign types.')">
                        @if ($recentConversions->count() > 0)
                            <div class="space-y-3">
                                @foreach ($recentConversions as $item)
                                    <div class="rounded-2xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $item['customer'] }} {{ $item['action'] }}</p>
                                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $item['campaign'] }} · {{ $item['time']?->diffForHumans() }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <x-report-empty :title="__('No conversions yet')" :description="__('Customer actions will appear after leads, bookings, coupons, reviews, or feedback are submitted.')" />
                            <div class="mt-4 text-center">
                                <x-ui.button href="{{ route('portal.businesses.campaigns.index', $business) }}" wire:navigate size="sm">
                                    <i class="fa-light fa-plus"></i>{{ __('Create Campaign') }}
                                </x-ui.button>
                            </div>
                        @endif
                    </x-report-card>
                </div>
            </div>

            <div x-show="reportTab === 'reviews'" x-cloak>
                <x-report-card :title="__('Review Performance')" :subtitle="__('Review Booster visits, ratings, and review intent.')">
                    <x-report-grid :items="[
                        ['label' => __('Review visits'), 'value' => $reviewPerformance['visits']],
                        ['label' => __('Positive ratings'), 'value' => $reviewPerformance['positive']],
                        ['label' => __('Negative feedback'), 'value' => $reviewPerformance['negative']],
                        ['label' => __('Google Review clicks'), 'value' => $reviewPerformance['review_clicks']],
                        ['label' => __('Average rating'), 'value' => $reviewPerformance['average_rating']],
                        ['label' => __('Review conversion'), 'value' => $reviewPerformance['conversion_rate'].'%'],
                    ]" />
                </x-report-card>
            </div>

            <div x-show="reportTab === 'leads'" x-cloak>
                <x-report-card :title="__('Lead Sources')" :subtitle="__('Lead source by campaign and type.')">
                    @if ($leadSources->count() > 0)
                        <div class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            @foreach ($leadSources as $source)
                                <div class="grid gap-3 py-3 md:grid-cols-[minmax(0,1fr)_8rem_8rem_10rem] md:items-center">
                                    <div>
                                        <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $source['campaign'] }}</p>
                                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $source['source'] }}</p>
                                    </div>
                                    <p>{{ number_format($source['leads']) }} {{ __('leads') }}</p>
                                    <p>{{ $source['conversion_rate'] }}%</p>
                                    <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ $source['last_lead'] ? \Carbon\Carbon::parse($source['last_lead'])->diffForHumans() : __('No lead') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <x-report-empty :title="__('No leads yet')" :description="__('Lead data will appear after customers submit your lead forms.')" />
                    @endif
                </x-report-card>
            </div>

            <div x-show="reportTab === 'bookings'" x-cloak>
                <x-report-card :title="__('Booking Performance')" :subtitle="__('Appointment requests and status breakdown.')">
                    @if ($bookingPerformance['total'] > 0)
                        <x-report-grid :items="[
                            ['label' => __('Total bookings'), 'value' => $bookingPerformance['total']],
                            ['label' => __('Pending'), 'value' => $bookingPerformance['pending']],
                            ['label' => __('Confirmed'), 'value' => $bookingPerformance['confirmed']],
                            ['label' => __('Cancelled'), 'value' => $bookingPerformance['cancelled']],
                            ['label' => __('Completed'), 'value' => $bookingPerformance['completed']],
                            ['label' => __('Booking conversion'), 'value' => $bookingPerformance['conversion_rate'].'%'],
                        ]" />
                    @else
                        <x-report-empty :title="__('No booking performance yet')" :description="__('Booking performance will appear after your first booking campaign.')" />
                    @endif
                </x-report-card>
            </div>

            <div x-show="reportTab === 'coupons'" x-cloak>
                <x-report-card :title="__('Coupon Performance')" :subtitle="__('Coupon claims, usage, and redemption rate.')">
                    @if ($couponPerformance['claims'] > 0)
                        <x-report-grid :items="[
                            ['label' => __('Coupon claims'), 'value' => $couponPerformance['claims']],
                            ['label' => __('Coupons used'), 'value' => $couponPerformance['used']],
                            ['label' => __('Expired coupons'), 'value' => $couponPerformance['expired']],
                            ['label' => __('Redemption rate'), 'value' => $couponPerformance['redemption_rate'].'%'],
                            ['label' => __('Coupon conversion'), 'value' => $couponPerformance['conversion_rate'].'%'],
                        ]" />
                    @else
                        <x-report-empty :title="__('No coupon performance yet')" :description="__('Coupon performance will appear after your first coupon campaign.')" />
                    @endif
                </x-report-card>
            </div>

            <div x-show="reportTab === 'feedback'" x-cloak>
                <x-report-card :title="__('Feedback Summary')" :subtitle="__('Customer feedback quality and unresolved issues.')">
                    @if ($feedbackSummary['total'] > 0)
                        <x-report-grid :items="[
                            ['label' => __('Total feedback'), 'value' => $feedbackSummary['total']],
                            ['label' => __('Positive feedback'), 'value' => $feedbackSummary['positive']],
                            ['label' => __('Negative feedback'), 'value' => $feedbackSummary['negative']],
                            ['label' => __('Resolved'), 'value' => $feedbackSummary['resolved']],
                            ['label' => __('Unresolved'), 'value' => $feedbackSummary['unresolved']],
                            ['label' => __('Average rating'), 'value' => $feedbackSummary['average_rating']],
                        ]" />
                    @else
                        <x-report-empty :title="__('No feedback yet')" :description="__('Feedback summary will appear after customers send feedback from campaigns.')" />
                    @endif
                </x-report-card>
            </div>

            <div x-show="reportTab === 'customers'" x-cloak>
                <x-report-card :title="__('Customer Growth')" :subtitle="__('Customer records connected to this business.')">
                    @if ($customerGrowth['total'] > 0)
                        <x-report-grid :items="[
                            ['label' => __('Total customers'), 'value' => $customerGrowth['total']],
                            ['label' => __('New customers'), 'value' => $customerGrowth['new']],
                            ['label' => __('Returning customers'), 'value' => $customerGrowth['returning']],
                            ['label' => __('Last activity'), 'value' => $customerGrowth['last_activity'] ? \Carbon\Carbon::parse($customerGrowth['last_activity'])->diffForHumans() : __('No activity')],
                        ]" />
                    @else
                        <x-report-empty :title="__('No customers yet')" :description="__('Customer growth will appear after contacts are captured or added manually.')" />
                    @endif
                </x-report-card>
            </div>
        </div>
    </section>
</div>
