@php
    $creditRemainingLabel = $credits['unlimited']
        ? __('Unlimited')
        : format_number_locale((int) ($credits['remaining'] ?? 0));
    $usagePercent = (int) ($credits['usage_percent'] ?? 0);
    $displayName = $user?->name ?: $user?->username ?: __('there');
@endphp

<div class="overflow-hidden rounded-lg border bg-white shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.72);">
    <div class="grid lg:grid-cols-[minmax(0,1fr)_20rem]">
        <section class="p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-md border px-3 py-1.5 text-xs font-semibold uppercase" style="border-color: rgba(37,99,235,0.22); background: rgba(37,99,235,0.07); color: #1d4ed8;">
                        <i class="fa-light fa-headset"></i>
                        {{ __('Support cockpit') }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-md border px-3 py-1.5 text-xs font-semibold uppercase" style="border-color: rgba(16,185,129,0.22); background: rgba(16,185,129,0.08); color: #047857;">
                        <span class="h-2 w-2 rounded-full bg-current"></span>
                        {{ __('Online ready') }}
                    </span>
                </div>

                <div class="rounded-md border px-3 py-2 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.7); background: #f8fafc;">
                    <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ $planName }}</span>
                    <span class="ml-2" style="color: var(--theme-muted-text-color);">{{ $planExpiryLabel }}</span>
                </div>
            </div>

            <div class="mt-8 max-w-4xl">
                <h2 class="text-3xl font-semibold leading-tight sm:text-4xl" style="color: var(--theme-header-text-color);">
                    {{ __('Good to see you, :name', ['name' => $displayName]) }}
                </h2>
                <p class="mt-3 max-w-3xl text-sm leading-7" style="color: var(--theme-muted-text-color);">
                    {{ __('Use this dashboard to prepare customer conversations, manage support assets, and keep automation capacity visible before the full live chat modules are added.') }}
                </p>
            </div>

            <div class="mt-6 grid gap-3 md:grid-cols-3">
                @foreach ([
                    ['label' => __('Current plan'), 'value' => $planName, 'note' => __('Active package'), 'icon' => 'fa-badge-check', 'color' => '#2563eb'],
                    ['label' => __('Access'), 'value' => $user?->isInPlanTrial() ? __('Trial') : __('Live'), 'note' => $user?->isInPlanTrial() ? __('Trial workspace') : $planExpiryLabel, 'icon' => 'fa-signal-stream', 'color' => '#0f766e'],
                    ['label' => __('Support workspace'), 'value' => __('Ready'), 'note' => __('Inbox-first setup'), 'icon' => 'fa-comments', 'color' => '#d97706'],
                ] as $item)
                    <div class="rounded-lg border p-4" style="border-color: {{ $item['color'] }}26; background: {{ $item['color'] }}0d;">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold uppercase" style="color: var(--theme-muted-text-color);">{{ $item['label'] }}</p>
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-md" style="background-color: {{ $item['color'] }}14; color: {{ $item['color'] }};">
                                <i class="fa-light {{ $item['icon'] }}"></i>
                            </span>
                        </div>
                        <p class="mt-3 truncate text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $item['value'] }}</p>
                        <p class="mt-1 truncate text-sm" style="color: var(--theme-muted-text-color);">{{ $item['note'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <aside class="border-t p-5 sm:p-6 lg:border-l lg:border-t-0" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background: linear-gradient(180deg, rgba(255,251,235,0.7), rgba(255,255,255,0.98));">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase" style="color: var(--theme-muted-text-color);">{{ __('Automation credits') }}</p>
                    <p class="mt-2 text-3xl font-semibold" style="color: var(--theme-header-text-color);">{{ $creditRemainingLabel }}</p>
                </div>
                <span class="rounded-md px-2.5 py-1 text-xs font-semibold uppercase" style="background-color: rgba(245,158,11,0.14); color: #c2410c;">
                    {{ $credits['unlimited'] ? __('Unlimited') : __('Balance') }}
                </span>
            </div>

            <p class="mt-4 text-sm leading-6" style="color: var(--theme-muted-text-color);">
                {{ $credits['unlimited']
                    ? __('Automation usage is unrestricted for the current package.')
                    : __('Credits are available for AI replies, summaries, and future support automation.') }}
            </p>

            <div class="mt-5 grid gap-3">
                <div class="rounded-lg border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: rgba(255,255,255,0.82);">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase" style="color: var(--theme-muted-text-color);">{{ __('Used') }}</p>
                            <p class="mt-1 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale((int) $credits['used']) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold uppercase" style="color: var(--theme-muted-text-color);">{{ __('Usage') }}</p>
                            <p class="mt-1 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ $usagePercent }}%</p>
                        </div>
                    </div>
                    <div class="mt-4 h-2 overflow-hidden rounded-full" style="background-color: rgba(245,158,11,0.16);">
                        <div class="h-full rounded-full" style="width: {{ max(4, $usagePercent) }}%; background: linear-gradient(90deg, #2563eb, #0f766e, #f59e0b);"></div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
