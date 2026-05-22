@php
    $supportSeries = [[
        'name' => __('Tickets'),
        'data' => [
            (int) $metrics['total'],
            (int) $metrics['open'],
            (int) $metrics['resolved'],
            (int) $metrics['unread'],
        ],
    ]];

    $cards = [
        ['label' => __('All tickets'), 'value' => $metrics['total'], 'icon' => 'fa-inbox', 'tone' => 'var(--theme-accent)', 'description' => __('Total service load')],
        ['label' => __('Open'), 'value' => $metrics['open'], 'icon' => 'fa-circle-dot', 'tone' => 'var(--theme-warning-color)', 'description' => __('Waiting for action')],
        ['label' => __('Resolved'), 'value' => $metrics['resolved'], 'icon' => 'fa-check-circle', 'tone' => 'var(--theme-success-color)', 'description' => __('Completed tickets')],
        ['label' => __('Unread'), 'value' => $metrics['unread'], 'icon' => 'fa-envelope-dot', 'tone' => 'var(--theme-danger-color)', 'description' => __('Need admin review')],
    ];
@endphp

<section class="overflow-hidden rounded-[1.15rem] border shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background: var(--theme-surface-base);">
    <div class="p-5 sm:p-6">
        <div class="rounded-[1rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background:
            radial-gradient(circle at top left, rgba(var(--theme-accent-rgb),0.14), transparent 34%),
            linear-gradient(135deg, color-mix(in srgb, var(--theme-surface-overlay) 96%, transparent), color-mix(in srgb, var(--theme-surface-base) 94%, rgba(var(--theme-accent-rgb),0.04)));">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb),0.18); background: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                            <i class="fa-light fa-headset"></i>
                            {{ __('Support') }}
                        </span>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="background: rgba(var(--theme-success-color-rgb),0.10); color: var(--theme-success-color);">
                            {{ __('Admin queue') }}
                        </span>
                    </div>
                    <h3 class="mt-3 text-[1.45rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Support queue health') }}</h3>
                    <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Track open tickets, unread pressure, and resolved work from the same admin dashboard style.') }}</p>
                </div>

                <x-ui.button :href="$route" size="sm" wire:navigate>
                    <i class="fa-light fa-arrow-up-right"></i>
                    {{ __('Open support queue') }}
                </x-ui.button>
            </div>
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-4">
            @foreach ($cards as $card)
                <div class="rounded-[1rem] border p-4" style="border-color: color-mix(in srgb, {{ $card['tone'] }} 18%, var(--theme-border-color)); background: color-mix(in srgb, {{ $card['tone'] }} 7%, var(--theme-surface-base));">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $card['label'] }}</p>
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-[0.7rem]" style="background: color-mix(in srgb, {{ $card['tone'] }} 12%, transparent); color: {{ $card['tone'] }};">
                            <i class="fa-light {{ $card['icon'] }}"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $card['value']) }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $card['description'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-5 grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
            <div class="rounded-[1rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.64); background: color-mix(in srgb, var(--theme-surface-overlay) 92%, transparent);">
                <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Service focus') }}</p>
                <p class="mt-3 text-3xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $metrics['open']) }}</p>
                <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Tickets still in progress and waiting for response or resolution.') }}</p>
                <div class="mt-5 h-2 overflow-hidden rounded-full" style="background: rgba(var(--theme-border-color-rgb),0.46);">
                    @php($unreadShare = ($metrics['open'] ?? 0) > 0 ? min(100, round(($metrics['unread'] / max(1, $metrics['open'])) * 100)) : 0)
                    <div class="h-full rounded-full" style="width: {{ $unreadShare }}%; background: var(--theme-danger-color);"></div>
                </div>
                <p class="mt-2 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Unread pressure: :rate%', ['rate' => $unreadShare]) }}</p>
            </div>

            <x-ui.chart
                :title="__('Queue distribution')"
                :description="__('A compact view across total, open, resolved, and unread states.')"
                type="column"
                :categories="[__('All'), __('Open'), __('Resolved'), __('Unread')]"
                :series="$supportSeries"
                :height="300"
            />
        </div>
    </div>
</section>
