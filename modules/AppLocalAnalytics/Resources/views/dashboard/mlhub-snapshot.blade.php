@php
    $metrics = $metrics ?? [];
    $topCampaigns = $topCampaigns ?? collect();
    $conversionRate = ($metrics['visits'] ?? 0) > 0
        ? round((($metrics['conversions'] ?? 0) / max(1, $metrics['visits'])) * 100)
        : 0;

    $tiles = [
        ['label' => __('Businesses'), 'value' => $metrics['businesses'] ?? 0, 'icon' => 'fa-store', 'tone' => '#0f766e', 'description' => __('Local profiles')],
        ['label' => __('Campaigns'), 'value' => $metrics['campaigns'] ?? 0, 'icon' => 'fa-bullhorn', 'tone' => '#0d9488', 'description' => __('Growth campaigns')],
        ['label' => __('Landing pages'), 'value' => $metrics['landing_pages'] ?? 0, 'icon' => 'fa-browser', 'tone' => '#84a900', 'description' => __('Public pages')],
        ['label' => __('Visits'), 'value' => $metrics['visits'] ?? 0, 'icon' => 'fa-eye', 'tone' => '#0891b2', 'description' => __('Tracked sessions')],
        ['label' => __('Conversions'), 'value' => $metrics['conversions'] ?? 0, 'icon' => 'fa-bullseye-pointer', 'tone' => '#d97706', 'description' => __('Customer actions')],
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
                            <i class="fa-light fa-chart-network"></i>
                            {{ __('Platform overview') }}
                        </span>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="background: rgba(var(--theme-success-color-rgb),0.10); color: var(--theme-success-color);">
                            {{ __('MKT AI') }}
                        </span>
                    </div>
                    <h2 class="mt-3 text-[1.45rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Local marketing activity across every tenant') }}</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Monitor businesses, campaigns, public pages, visits, and conversions from one admin view.') }}</p>
                </div>

                <div class="rounded-[0.95rem] border px-4 py-3 text-right" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background: color-mix(in srgb, var(--theme-surface-base) 88%, rgba(var(--theme-accent-rgb),0.05));">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Conversion rate') }}</p>
                    <p class="mt-1 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ format_percent_locale($conversionRate) }}</p>
                </div>
            </div>
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-5">
            @foreach ($tiles as $tile)
                <div class="rounded-[1rem] border p-4" style="border-color: {{ $tile['tone'] }}24; background: {{ $tile['tone'] }}0d;">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $tile['label'] }}</p>
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-[0.7rem]" style="background: {{ $tile['tone'] }}12; color: {{ $tile['tone'] }};">
                            <i class="fa-light {{ $tile['icon'] }}"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale((int) $tile['value']) }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $tile['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="border-t px-5 py-5 sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb),0.72);">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Top campaign traffic') }}</h3>
                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Campaigns ranked by QR visits and public page traffic.') }}</p>
            </div>
        </div>

        <div class="mt-4 grid gap-2">
            @forelse ($topCampaigns as $campaign)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-[0.9rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.64); background: color-mix(in srgb, var(--theme-surface-overlay) 92%, transparent);">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $campaign->name }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $campaign->business?->name ?: __('No business') }} · {{ \Illuminate\Support\Str::headline($campaign->type) }}</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold" style="background: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">{{ format_number_locale((int) $campaign->scans_count) }} {{ __('visits') }}</span>
                </div>
            @empty
                <p class="rounded-[0.9rem] border px-4 py-3 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.72); color: var(--theme-muted-text-color);">{{ __('No MKT campaigns yet.') }}</p>
            @endforelse
        </div>
    </div>
</section>
