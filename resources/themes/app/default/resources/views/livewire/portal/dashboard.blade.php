@php
    $widthClasses = [
        'compact' => 'lg:col-span-4',
        'half' => 'lg:col-span-6',
        'wide' => 'lg:col-span-8',
        'full' => 'lg:col-span-12',
    ];

    $portalUser = auth()->user();
    $portalName = $portalUser?->name ?: $portalUser?->username ?: __('Operator');
    $growthMetrics = $growthDashboard['metrics'] ?? [];
    $metricsLoaded = (bool) ($this->metricsLoaded ?? false);
    $recentActivity = $this->recentActivity ?? [];
    $topCampaigns = $this->topCampaigns ?? [];
    $onboardingSteps = $onboarding['steps'] ?? [];
    $onboardingPercent = (int) ($onboarding['percent'] ?? 0);
    $onboardingComplete = (bool) ($onboarding['is_complete'] ?? false);
    $planUsage = $planUsage ?? [];
@endphp

<div
    class="portal-dashboard min-w-0 max-w-full space-y-6"
    x-data="{
        draggingId: null,
        saveTimeout: null,
        saving: false,
        saved: false,
        startDrag(event) {
            const card = event.currentTarget;
            this.draggingId = card.dataset.dashboardId;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', this.draggingId);
            card.classList.add('opacity-60');
        },
        dragOver(event) {
            const target = event.currentTarget;
            const sourceId = this.draggingId;

            if (!sourceId || sourceId === target.dataset.dashboardId) {
                return;
            }

            const board = this.$refs.board;
            const source = board.querySelector(`[data-dashboard-id='${sourceId}']`);

            if (!source || !target || source === target) {
                return;
            }

            const rect = target.getBoundingClientRect();
            const before = event.clientY < rect.top + rect.height / 2;

            if (before) {
                board.insertBefore(source, target);
            } else {
                board.insertBefore(source, target.nextSibling);
            }
        },
        endDrag(event) {
            event.currentTarget.classList.remove('opacity-60');
            this.draggingId = null;
            this.persist();
        },
        persist() {
            clearTimeout(this.saveTimeout);

            this.saveTimeout = setTimeout(async () => {
                const itemIds = Array.from(this.$refs.board.querySelectorAll('[data-dashboard-id]'))
                    .map((element) => element.dataset.dashboardId);

                this.saving = true;
                this.saved = false;

                await this.$wire.saveLayout(itemIds);

                this.saving = false;
                this.saved = true;

                setTimeout(() => {
                    this.saved = false;
                }, 1600);
            }, 180);
        },
    }"
>
    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background:
        linear-gradient(135deg, rgba(15,118,110,0.12), transparent 36%),
        linear-gradient(35deg, rgba(217,119,6,0.07), transparent 44%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-7 px-5 py-6 sm:px-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-center xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb),0.62); color: var(--theme-muted-text-color);">
                    <i class="fa-light fa-chart-line"></i>
                    {{ __('Growth Dashboard') }}
                </div>
                <h1 class="mt-4 max-w-4xl text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">
                    {{ __('LocalBoost growth overview') }}
                </h1>
                <p class="mt-4 max-w-3xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ __('Track visits, review clicks, leads, bookings, coupon claims, feedback, conversion rate, recent activity, and top campaigns from one workspace.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button href="{{ route('portal.reports') }}" wire:navigate>
                        <i class="fa-light fa-chart-line"></i>
                        {{ __('Open reports') }}
                    </x-ui.button>
                    <x-ui.button href="{{ route('portal.businesses') }}" variant="outline" wire:navigate>
                        <i class="fa-light fa-store"></i>
                        {{ __('View businesses') }}
                    </x-ui.button>
                </div>
            </div>

            <div class="rounded-[1.2rem] border p-4 shadow-[0_24px_70px_-48px_rgba(var(--theme-border-color-rgb),0.9)]" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Growth health') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Captured from public campaign pages') }}</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl" style="background-color: rgba(15,118,110,0.12); color: #0f766e;">
                        <i class="fa-light fa-bullseye-pointer"></i>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-3 gap-3">
                    @foreach ([
                        [__('Bookings'), $growthMetrics['bookings'] ?? 0],
                        [__('Coupons'), $growthMetrics['coupon_claims'] ?? 0],
                        [__('Feedback'), $growthMetrics['feedback'] ?? 0],
                    ] as $summary)
                        <div class="rounded-2xl border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                            <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ format_number_locale((int) $summary[1]) }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $summary[0] }}</p>
                        </div>
                    @endforeach
                </div>

                <span
                    x-cloak
                    x-show="saving || saved"
                    class="mt-5 inline-flex items-center rounded-md px-3 py-2 text-xs font-semibold"
                    style="background: rgba(var(--theme-accent-rgb,37,99,235),0.1); color: var(--theme-accent,#2563eb);"
                >
                    <span x-show="saving">{{ __('Saving layout...') }}</span>
                    <span x-show="saved">{{ __('Layout saved') }}</span>
                </span>
            </div>
        </div>
    </section>

    @include('applandingpages::partials.plan-limit-usage', [
        'usage' => $planUsage,
        'title' => __('Current plan limits'),
        'description' => __('See how many LocalBoost assets are used before creating more campaigns, pages, QR codes, or templates.'),
    ])

    <section id="onboarding" class="scroll-mt-28 overflow-hidden rounded-[1.25rem] border bg-white shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.7);">
        <div class="grid gap-5 px-5 py-5 lg:grid-cols-[minmax(0,1fr)_16rem] lg:items-center sm:px-6">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(15,118,110,0.22); background: rgba(15,118,110,0.08); color: #0f766e;">
                        <i class="fa-light fa-route"></i>
                        {{ __('Onboarding') }}
                    </span>
                    @if ($onboardingComplete)
                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(16,185,129,0.25); background: rgba(16,185,129,0.09); color: #047857;">
                            <i class="fa-light fa-check"></i>
                            {{ __('Ready') }}
                        </span>
                    @endif
                </div>
                <h2 class="mt-3 text-xl font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">
                    {{ __('Launch your first local growth flow') }}
                </h2>
                <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">
                    {{ __('Follow the path from business setup to campaign launch, QR/link sharing, and performance tracking.') }}
                </p>
            </div>
            <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background: rgba(var(--theme-surface-bg-rgb),0.55);">
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Progress') }}</p>
                        <p class="mt-1 text-3xl font-semibold tracking-[-0.055em]" style="color: var(--theme-header-text-color);">{{ $onboardingPercent }}%</p>
                    </div>
                    <p class="text-sm font-semibold" style="color: var(--theme-muted-text-color);">{{ (int) ($onboarding['completed'] ?? 0) }}/{{ (int) ($onboarding['total'] ?? count($onboardingSteps)) }}</p>
                </div>
                <div class="mt-4 h-2 overflow-hidden rounded-full" style="background-color: rgba(15,118,110,0.12);">
                    <div class="h-full rounded-full transition-all" style="width: {{ max(4, $onboardingPercent) }}%; background: var(--theme-brand-gradient);"></div>
                </div>
            </div>
        </div>

        <div class="grid border-t sm:grid-cols-2 xl:grid-cols-5" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
            @foreach ($onboardingSteps as $index => $step)
                <article class="relative flex min-h-[12rem] flex-col justify-between gap-5 border-b p-5 sm:border-r xl:border-b-0" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl" style="background: {{ $step['complete'] ? 'rgba(15,118,110,0.12)' : 'rgba(var(--theme-surface-bg-rgb),0.86)' }}; color: {{ $step['complete'] ? '#0f766e' : 'var(--theme-muted-text-color)' }};">
                                <i class="{{ $step['icon'] }}"></i>
                            </span>
                            <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full border px-2 text-xs font-semibold" style="border-color: {{ $step['complete'] ? 'rgba(15,118,110,0.26)' : 'rgba(var(--theme-border-color-rgb),0.7)' }}; background: {{ $step['complete'] ? 'rgba(15,118,110,0.08)' : '#fff' }}; color: {{ $step['complete'] ? '#0f766e' : 'var(--theme-muted-text-color)' }};">
                                @if ($step['complete'])
                                    <i class="fa-light fa-check"></i>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </span>
                        </div>
                        <h3 class="mt-4 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $step['description'] }}</p>
                    </div>
                    <x-ui.button href="{{ $step['href'] }}" variant="{{ $step['complete'] ? 'outline' : 'primary' }}" size="sm" class="w-full justify-center" wire:navigate>
                        {{ $step['action'] }}
                        <i class="fa-light fa-arrow-right"></i>
                    </x-ui.button>
                </article>
            @endforeach
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5" wire:init="loadDashboardSections">
        @if (! $metricsLoaded)
            <article class="rounded-[1rem] border bg-white p-4 shadow-sm sm:col-span-2 xl:col-span-5" style="border-color: rgba(var(--theme-border-color-rgb),0.72);">
                <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Loading dashboard metrics...') }}</p>
            </article>
        @endif
        @foreach ([
            ['label' => __('Businesses'), 'value' => $growthMetrics['businesses'] ?? 0, 'description' => __('Local profiles'), 'icon' => 'fa-light fa-store', 'accent' => '#0f766e'],
            ['label' => __('Active Campaigns'), 'value' => $growthMetrics['active_campaigns'] ?? 0, 'description' => __('Published funnels'), 'icon' => 'fa-light fa-bullhorn', 'accent' => '#0f766e'],
            ['label' => __('Visits'), 'value' => $growthMetrics['visits'] ?? 0, 'description' => __('Tracked page views'), 'icon' => 'fa-light fa-eye', 'accent' => '#0f766e'],
            ['label' => __('Review Clicks'), 'value' => $growthMetrics['review_clicks'] ?? 0, 'description' => __('Public review actions'), 'icon' => 'fa-light fa-star', 'accent' => '#d97706'],
            ['label' => __('Conversion Rate'), 'value' => ($growthMetrics['conversion_rate'] ?? 0).'%', 'description' => __('Conversions / visits'), 'icon' => 'fa-light fa-chart-simple', 'accent' => '#0f766e'],
            ['label' => __('Leads'), 'value' => $growthMetrics['leads'] ?? 0, 'description' => __('Lead forms'), 'icon' => 'fa-light fa-user-plus', 'accent' => '#0f766e'],
            ['label' => __('Bookings'), 'value' => $growthMetrics['bookings'] ?? 0, 'description' => __('Appointment requests'), 'icon' => 'fa-light fa-calendar-check', 'accent' => '#0f766e'],
            ['label' => __('Coupon Claims'), 'value' => $growthMetrics['coupon_claims'] ?? 0, 'description' => __('Claimed offers'), 'icon' => 'fa-light fa-ticket', 'accent' => '#d97706'],
            ['label' => __('Feedback'), 'value' => $growthMetrics['feedback'] ?? 0, 'description' => __('Private responses'), 'icon' => 'fa-light fa-message-lines', 'accent' => '#d97706'],
            ['label' => __('Recent Activity'), 'value' => count($recentActivity), 'description' => __('Latest signals'), 'icon' => 'fa-light fa-clock-rotate-left', 'accent' => '#0f766e'],
        ] as $metric)
            <article class="rounded-[1rem] border bg-white p-4 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.72); border-top: 4px solid {{ $metric['accent'] }};">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-2xl font-semibold tracking-[-0.055em]" style="color: var(--theme-header-text-color);">{{ is_numeric($metric['value']) ? format_number_locale((float) $metric['value']) : $metric['value'] }}</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $metric['description'] }}</p>
                    </div>
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" style="background-color: {{ $metric['accent'] }}14; color: {{ $metric['accent'] }};">
                        <i class="{{ $metric['icon'] }}"></i>
                    </span>
                </div>
            </article>
        @endforeach
    </section>

    <section class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_26rem]">
        <div class="overflow-hidden rounded-[1.25rem] border bg-white shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.7);">
            <div class="flex items-center justify-between gap-3 border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.7);">
                <div>
                    <h2 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Top Campaigns') }}</h2>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Campaigns ranked by visits and conversion signals.') }}</p>
                </div>
                <x-ui.button href="{{ route('portal.reports') }}" variant="outline" size="sm" wire:navigate>{{ __('Open Reports') }}</x-ui.button>
            </div>
            <div>
                <div class="px-5 py-8" wire:loading wire:target="loadDashboardSections,loadTopCampaigns">
                    <div style="color: var(--theme-muted-text-color);">{{ __('Loading top campaigns...') }}</div>
                </div>

                <div wire:loading.remove wire:target="loadDashboardSections,loadTopCampaigns">
                    @if (count($topCampaigns) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead style="color: var(--theme-muted-text-color);">
                                    <tr>
                                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Campaign') }}</th>
                                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Business') }}</th>
                                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Visits') }}</th>
                                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Conversions') }}</th>
                                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Rate') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb),0.58);">
                                    @foreach ($topCampaigns as $row)
                                        <tr>
                                            <td class="px-5 py-4">
                                                <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $row['campaign']->name }}</p>
                                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ str($row['campaign']->type)->headline() }}</p>
                                            </td>
                                            <td class="px-5 py-4" style="color: var(--theme-muted-text-color);">{{ $row['campaign']->business?->name ?: __('No business') }}</td>
                                            <td class="px-5 py-4 font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($row['visits']) }}</td>
                                            <td class="px-5 py-4 font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($row['conversions']) }}</td>
                                            <td class="px-5 py-4 font-semibold" style="color: var(--theme-header-text-color);">{{ $row['conversion_rate'] }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="px-5 py-8">
                            <x-ui.empty :title="__('No campaign performance yet')" :description="__('Create a review, booking, coupon, feedback, or lead campaign to start seeing top campaigns.')" />
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-[1.25rem] border bg-white shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.7);">
            <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.7);">
                <h2 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recent Activity') }}</h2>
                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Latest growth events across campaign pages.') }}</p>
            </div>
            <div>
                <div class="grid gap-3 p-5" wire:loading wire:target="loadDashboardSections,loadRecentActivity,loadMoreRecentActivity">
                    <div style="color: var(--theme-muted-text-color);">{{ __('Loading recent activity...') }}</div>
                </div>

                <div wire:loading.remove wire:target="loadDashboardSections,loadRecentActivity,loadMoreRecentActivity">
                    <div class="grid gap-3 p-5">
                        @forelse ($recentActivity as $item)
                            <div class="flex gap-3 rounded-xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: rgba(var(--theme-surface-bg-rgb),0.55);">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background: rgba(15,118,110,0.1); color: #0f766e;">
                                    <i class="fa-light {{ $item['icon'] }}"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $item['customer'] }} {{ $item['action'] }}</p>
                                    <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $item['business'] }} · {{ $item['campaign'] }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $item['time']?->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <x-ui.empty :title="__('No recent activity yet')" :description="__('Lead, booking, coupon, review, and feedback events will appear here.')" />
                        @endforelse
                    </div>

                    @if ($recentActivityHasMore && $recentActivityLimit < 64)
                        <div class="px-5 pb-5">
                            <x-ui.button
                                type="button"
                                variant="outline"
                                size="sm"
                                wire:click="loadMoreRecentActivity"
                                wire:loading.attr="disabled"
                            >
                                {{ __('Load more') }}
                            </x-ui.button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if (($welcomeItems ?? []) !== [])
        <div class="min-w-0 max-w-full space-y-5">
            @foreach ($welcomeItems as $item)
                <section wire:key="dashboard-welcome-{{ $item['id'] }}" class="min-w-0 max-w-full">
                    {!! $item['content'] !!}
                </section>
            @endforeach
        </div>
    @endif

    @if ($dashboardItems === [])
        <x-ui.empty
            :title="__('No dashboard items registered')"
            :description="__('Start by registering widgets from user-facing modules with register_user_dashboard_item().')"
        />
    @else
        <div
            x-ref="board"
            class="min-w-0 max-w-full grid gap-5 lg:grid-cols-12"
        >
            @foreach ($dashboardItems as $item)
                <section
                    draggable="true"
                    wire:key="dashboard-item-{{ $item['id'] }}"
                    data-dashboard-id="{{ $item['id'] }}"
                    class="group relative min-w-0 max-w-full {{ $widthClasses[$item['width'] ?? 'half'] ?? $widthClasses['half'] }}"
                    x-on:dragstart="startDrag($event)"
                    x-on:dragover.prevent="dragOver($event)"
                    x-on:dragend="endDrag($event)"
                >
                    <div class="pointer-events-none absolute right-3 top-3 z-10 inline-flex h-8 w-8 items-center justify-center rounded-md border bg-white/95 text-slate-400 opacity-0 shadow-sm transition group-hover:opacity-100" style="border-color: var(--theme-border-color);">
                        <i class="fa-light fa-grip-dots text-sm"></i>
                    </div>

                    {!! $item['content'] !!}
                </section>
            @endforeach
        </div>
    @endif
</div>
