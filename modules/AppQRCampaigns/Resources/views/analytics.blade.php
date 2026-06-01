@php
    $maxDailyScans = max(1, collect($dailyScans)->max('total') ?: 1);
@endphp

<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 34%),
        linear-gradient(35deg, rgba(var(--theme-success-color-rgb),0.08), transparent 38%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-7 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <a href="{{ route('portal.qr-campaigns') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold" style="color: var(--theme-muted-text-color);">
                    <i class="fa-light fa-arrow-left"></i>{{ __('QR Asset Library') }}
                </a>
                <div class="mt-4 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-chart-line"></i>
                    {{ __('QR Analytics') }}
                </div>
                <h1 class="mt-4 max-w-3xl text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ $campaign->name }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ $campaign->business?->name }} - {{ $sourceLabel }} - {{ __('Destination') }}:
                    <a href="{{ $destinationUrl }}" target="_blank" class="font-semibold hover:underline" style="color: var(--theme-accent);">{{ $destinationUrl }}</a>
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button href="{{ $campaign->publicUrl() }}" target="_blank" size="lg" variant="outline">
                        <i class="fa-light fa-arrow-up-right"></i>{{ __('Open QR Link') }}
                    </x-ui.button>
                    @if ($sourceCampaignUrl)
                        <x-ui.button href="{{ $sourceCampaignUrl }}" wire:navigate size="lg">
                            <i class="fa-light fa-bullhorn"></i>{{ __('View Source Campaign') }}
                        </x-ui.button>
                    @endif
                </div>
            </div>

            <div class="rounded-[1.2rem] border p-4 shadow-[0_24px_70px_-48px_rgba(var(--theme-border-color-rgb),0.9)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Tracking entry point') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Created from: :source', ['source' => $createdFrom]) }}</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-qrcode"></i>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($metrics['total_scans']) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Scans') }}</p>
                    </div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ $metrics['last_scanned'] ? $metrics['last_scanned']->diffForHumans() : __('Never') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Last scanned') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => __('Total scans'), 'value' => format_number_locale($metrics['total_scans']), 'description' => __('All tracked QR opens'), 'icon' => 'fa-light fa-qrcode', 'tone' => 'accent'],
            ['label' => __('Unique visitors'), 'value' => format_number_locale($metrics['unique_visitors']), 'description' => __('Distinct IP addresses'), 'icon' => 'fa-light fa-users', 'tone' => 'success'],
            ['label' => __('Conversions'), 'value' => format_number_locale($metrics['conversions']), 'description' => __('Submitted actions from this QR'), 'icon' => 'fa-light fa-bullseye-pointer', 'tone' => 'warning'],
            ['label' => __('Conversion rate'), 'value' => $metrics['conversion_rate'].'%', 'description' => __('Conversions divided by scans'), 'icon' => 'fa-light fa-percent', 'tone' => 'accent'],
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
            <article class="relative overflow-hidden rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background: linear-gradient(145deg, rgba({{ $toneRgb }},0.07), transparent 44%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <span class="absolute inset-x-0 top-0 h-1" style="background-color: {{ $toneColor }};"></span>
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[1.75rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ $metric['value'] }}</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $metric['description'] }}</p>
                    </div>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background-color: rgba({{ $toneRgb }},0.12); color: {{ $toneColor }};">
                        <i class="{{ $metric['icon'] }}"></i>
                    </span>
                </div>
            </article>
        @endforeach
    </section>

    <section class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_24rem]">
        <div class="rounded-[1rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Daily scans') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Last 14 days of QR scan activity.') }}</p>
                </div>
            </div>
            <div class="mt-6 flex h-64 items-end gap-2">
                @foreach ($dailyScans as $day)
                    <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                        <div class="flex h-48 w-full items-end rounded-t-xl" style="background-color: rgba(var(--theme-border-color-rgb), .20);">
                            <div class="w-full rounded-t-xl" style="height: {{ max(4, (int) round(($day['total'] / $maxDailyScans) * 100)) }}%; background: linear-gradient(180deg, var(--theme-accent), var(--theme-success-color));"></div>
                        </div>
                        <p class="truncate text-[10px]" style="color: var(--theme-muted-text-color);">{{ $day['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-5">
            @foreach ([__('Device') => $deviceBreakdown, __('Browser') => $browserBreakdown, __('Location') => $locationBreakdown] as $title => $rows)
                <div class="rounded-[1rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $title }}</p>
                    <div class="mt-4 space-y-3">
                        @forelse ($rows as $row)
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="truncate" style="color: var(--theme-muted-text-color);">{{ str($row->label)->headline() }}</span>
                                <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale((int) $row->total) }}</span>
                            </div>
                        @empty
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No data yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="overflow-hidden rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recent scan activity') }}</p>
            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Latest scan events recorded for this QR asset.') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                <thead>
                    <tr class="text-left text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">
                        <th class="px-5 py-3">{{ __('Time') }}</th>
                        <th class="px-5 py-3">{{ __('Device') }}</th>
                        <th class="px-5 py-3">{{ __('Browser') }}</th>
                        <th class="px-5 py-3">{{ __('Location') }}</th>
                        <th class="px-5 py-3">{{ __('IP') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    @forelse ($recentScans as $scan)
                        <tr>
                            <td class="px-5 py-4 text-sm" style="color: var(--theme-header-text-color);">{{ $scan->created_at ? \Illuminate\Support\Carbon::parse($scan->created_at)->format('Y-m-d H:i') : __('Unknown') }}</td>
                            <td class="px-5 py-4 text-sm" style="color: var(--theme-muted-text-color);">{{ str($scan->device ?: __('Unknown'))->headline() }}</td>
                            <td class="px-5 py-4 text-sm" style="color: var(--theme-muted-text-color);">{{ $this->browserName((string) $scan->user_agent) }}</td>
                            <td class="px-5 py-4 text-sm" style="color: var(--theme-muted-text-color);">{{ trim(($scan->city ?: __('Unknown city')).', '.($scan->country ?: __('Unknown country'))) }}</td>
                            <td class="px-5 py-4 text-sm" style="color: var(--theme-muted-text-color);">{{ $scan->ip_address ?: __('Unknown') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8">
                                <x-ui.empty icon="fa-light fa-chart-line" :title="__('No scan activity yet')" :description="__('Scan events will appear here after customers open this QR code.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
