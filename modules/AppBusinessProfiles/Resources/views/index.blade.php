<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.14), transparent 34%),
        linear-gradient(35deg, rgba(var(--theme-success-color-rgb),0.08), transparent 38%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-7 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_23rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-store"></i>
                    {{ __('Local Businesses') }}
                </div>
                <h1 class="mt-4 max-w-3xl text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('Turn every local business into a QR-powered growth hub') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ __('Manage the business profiles that power QR campaigns, review funnels, booking pages, coupons, and local analytics.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button href="{{ route('portal.businesses.create') }}" wire:navigate size="lg">
                        <i class="fa-light fa-plus"></i>{{ __('Create business') }}
                    </x-ui.button>
                    <x-ui.button href="{{ route('portal.reports') }}" wire:navigate variant="outline" size="lg">
                        <i class="fa-light fa-chart-line"></i>{{ __('View analytics') }}
                    </x-ui.button>
                </div>
            </div>

            <div class="rounded-[1.2rem] border p-4 shadow-[0_24px_70px_-48px_rgba(var(--theme-border-color-rgb),0.9)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Launch readiness') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Business profile checklist') }}</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-rocket-launch"></i>
                    </div>
                </div>
                <div class="mt-5 space-y-3">
                    @foreach ([__('Profile details'), __('Location and contact'), __('Campaign-ready assets')] as $item)
                        <div class="flex items-center gap-3 rounded-xl border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 74%, transparent);">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-success-color-rgb),0.12); color: var(--theme-success-color);">
                                <i class="fa-light fa-check text-xs"></i>
                            </span>
                            <span class="text-sm font-medium" style="color: var(--theme-header-text-color);">{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['label' => __('Businesses'), 'value' => $totals['businesses'], 'description' => __('Profiles ready for campaigns'), 'icon' => 'fa-light fa-store', 'tone' => 'accent'],
            ['label' => __('Campaigns'), 'value' => $totals['campaigns'], 'description' => __('Live marketing funnels'), 'icon' => 'fa-light fa-bullhorn', 'tone' => 'accent'],
            ['label' => __('QR scans'), 'value' => $totals['scans'], 'description' => __('Tracked public visits'), 'icon' => 'fa-light fa-qrcode', 'tone' => 'success'],
            ['label' => __('Bookings'), 'value' => $totals['bookings'], 'description' => __('Appointment requests'), 'icon' => 'fa-light fa-calendar-check', 'tone' => 'success'],
            ['label' => __('Coupons'), 'value' => $totals['coupons'], 'description' => __('Claimed offers'), 'icon' => 'fa-light fa-ticket', 'tone' => 'warning'],
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
            <article class="relative overflow-hidden rounded-[1.15rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <span class="absolute inset-x-0 top-0 h-1" style="background-color: {{ $toneColor }};"></span>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[2rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($metric['value']) }}</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $metric['description'] }}</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba({{ $toneRgb }},0.12); color: {{ $toneColor }};">
                        <i class="{{ $metric['icon'] }}"></i>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="flex flex-col gap-4 border-b px-5 py-4 lg:flex-row lg:items-center lg:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
            <div>
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Business directory') }}</p>
                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Search, inspect, and manage every local business workspace.') }}</p>
            </div>
            <div class="relative w-full lg:max-w-sm">
                <i class="fa-light fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm" style="color: var(--theme-muted-text-color);"></i>
                <input
                    type="search"
                    wire:model.live.debounce.250ms="search"
                    class="h-11 w-full rounded-xl border pl-10 pr-4 text-sm outline-none transition focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
                    style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                    placeholder="{{ __('Search business, type, phone, address...') }}"
                >
            </div>
        </div>

        <div class="grid gap-4 p-4 xl:grid-cols-2 2xl:grid-cols-3">
            @forelse ($businesses as $business)
                @php
                    $websiteHost = $business->website ? (parse_url($business->website, PHP_URL_HOST) ?: $business->website) : null;
                    $detailRows = [
                        ['icon' => 'fa-light fa-phone', 'value' => $business->phone ?: __('No phone'), 'filled' => filled($business->phone)],
                        ['icon' => 'fa-light fa-envelope', 'value' => $business->email ?: __('No email'), 'filled' => filled($business->email)],
                        ['icon' => 'fa-light fa-globe', 'value' => $websiteHost ?: __('No website'), 'filled' => filled($websiteHost)],
                        ['icon' => 'fa-light fa-location-dot', 'value' => $business->address ?: __('No address'), 'filled' => filled($business->address)],
                    ];
                    $readyCount = collect($detailRows)->where('filled', true)->count();
                    $readyPercent = (int) round(($readyCount / count($detailRows)) * 100);
                @endphp
                <article class="group overflow-hidden rounded-[1.25rem] border transition duration-200 hover:-translate-y-1 hover:shadow-[0_28px_70px_-46px_rgba(var(--theme-accent-rgb),0.72)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
                    <div class="relative px-4 pb-4 pt-4" style="background:
                        linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.085), transparent 44%),
                        color-mix(in srgb, var(--theme-surface-overlay) 72%, transparent);">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.15rem] border text-base font-semibold uppercase shadow-[0_16px_34px_-28px_rgba(var(--theme-accent-rgb),0.9)]" style="border-color: rgba(var(--theme-accent-rgb), 0.2); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.14), rgba(var(--theme-success-color-rgb),0.10)); color: var(--theme-accent);">
                                    {{ str($business->name)->substr(0, 2)->upper() }}
                                    <span class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full border-2" style="border-color: var(--theme-surface-base); background-color: var(--theme-success-color);"></span>
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('portal.businesses.show', $business) }}" wire:navigate class="block truncate text-lg font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">{{ $business->name }}</a>
                                    <p class="mt-1 truncate text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ str($business->type)->headline() }}</p>
                                </div>
                            </div>
                            <x-ui.badge variant="success">{{ __('Active') }}</x-ui.badge>
                        </div>

                        <div class="mt-4 rounded-[1rem] border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-base) 82%, transparent);">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold" style="color: var(--theme-header-text-color);">{{ __('Workspace readiness') }}</p>
                                    <p class="mt-1 text-[11px]" style="color: var(--theme-muted-text-color);">
                                        {{ trans_choice('{0} No campaigns yet|{1} :count campaign|[2,*] :count campaigns', $business->campaigns_count, ['count' => format_number_locale($business->campaigns_count)]) }}
                                    </p>
                                </div>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold" style="background-color: rgba(var(--theme-success-color-rgb),0.12); color: var(--theme-success-color);">{{ $readyPercent }}%</span>
                            </div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb),0.26);">
                                <div class="h-full rounded-full transition-all" style="width: {{ $readyPercent }}%; background: linear-gradient(90deg, var(--theme-accent), var(--theme-success-color));"></div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 px-4 py-4">
                        @foreach ($detailRows as $row)
                            <div class="flex items-center gap-2 rounded-2xl px-2.5 py-2 transition group-hover:bg-[color:rgba(var(--theme-accent-rgb),0.045)]">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl" style="background-color: {{ $row['filled'] ? 'rgba(var(--theme-accent-rgb),0.09)' : 'rgba(var(--theme-border-color-rgb),0.16)' }}; color: {{ $row['filled'] ? 'var(--theme-accent)' : 'var(--theme-muted-text-color)' }};">
                                    <i class="{{ $row['icon'] }} text-xs"></i>
                                </span>
                                <span class="min-w-0 flex-1 truncate text-sm font-medium" style="color: {{ $row['filled'] ? 'var(--theme-header-text-color)' : 'var(--theme-muted-text-color)' }};">{{ $row['value'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.55); background-color: color-mix(in srgb, var(--theme-surface-overlay) 74%, transparent);">
                        <a href="{{ route('portal.businesses.show', $business) }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition hover:-translate-y-px" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                            {{ __('Open workspace') }} <i class="fa-light fa-arrow-right text-xs"></i>
                        </a>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('portal.businesses.edit', $business) }}" wire:navigate class="inline-flex h-9 w-9 items-center justify-center rounded-xl border text-sm transition hover:-translate-y-px" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); color: var(--theme-header-text-color); background-color: var(--theme-surface-base);" title="{{ __('Edit') }}"><i class="fa-light fa-pen"></i></a>
                            <x-ui.dialog :title="__('Delete this business?')" :description="__('This permanently removes the business profile and disconnects it from local growth tools.')" width="sm" dismissible>
                                <x-slot:trigger>
                                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border text-sm transition hover:-translate-y-px" style="border-color: rgba(var(--theme-danger-color-rgb),0.28); color: var(--theme-danger-color); background-color: var(--theme-surface-base);" title="{{ __('Delete') }}">
                                        <i class="fa-light fa-trash"></i>
                                    </button>
                                </x-slot:trigger>

                                <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-danger-color-rgb),0.18); background: linear-gradient(135deg, rgba(var(--theme-danger-color-rgb),0.08), transparent 70%);">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-danger-color-rgb),0.12); color: var(--theme-danger-color);">
                                            <i class="fa-light fa-triangle-exclamation"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $business->name }}</p>
                                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Campaigns and analytics for this profile may no longer have a business source.') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <x-slot:footer>
                                    <div class="flex items-center justify-end gap-3">
                                        <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                        <x-ui.button type="button" variant="danger" wire:click="delete({{ $business->id }})" x-on:click="open = false">
                                            <i class="fa-light fa-trash"></i>{{ __('Delete business') }}
                                        </x-ui.button>
                                    </div>
                                </x-slot:footer>
                            </x-ui.dialog>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full overflow-hidden rounded-[1.1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background:
                    linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.08), transparent 44%),
                    color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                    <div class="grid gap-6 px-6 py-8 lg:grid-cols-[minmax(0,1fr)_23rem] lg:items-center">
                        <div>
                            <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-accent-rgb), 0.18); background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                                <i class="fa-light fa-store text-xl"></i>
                            </div>
                            <h2 class="mt-5 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Create your first growth-ready business') }}</h2>
                            <p class="mt-3 max-w-xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Add the business profile once, then reuse it across review booster QR codes, booking pages, coupons, landing pages, lead forms, and analytics.') }}</p>
                            <div class="mt-6">
                                <x-ui.button href="{{ route('portal.businesses.create') }}" wire:navigate size="lg">
                                    <i class="fa-light fa-plus"></i>{{ __('Create business') }}
                                </x-ui.button>
                            </div>
                        </div>
                        <div class="grid gap-3">
                            @foreach ([['icon' => 'fa-light fa-star', 'label' => __('Collect better reviews')], ['icon' => 'fa-light fa-calendar-check', 'label' => __('Accept bookings from QR')], ['icon' => 'fa-light fa-ticket', 'label' => __('Launch coupon campaigns')]] as $step)
                                <div class="flex items-center gap-3 rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background-color: color-mix(in srgb, var(--theme-surface-overlay) 80%, transparent);">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="{{ $step['icon'] }}"></i></span>
                                    <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $step['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </section>
</div>
