@php
    $storageBytes = (int) ($metrics['storage_bytes'] ?? 0);
    $limitMb = (int) ($metrics['limit_mb'] ?? 0);
    $limitBytes = $limitMb > 0 ? $limitMb * 1024 * 1024 : 0;
    $usagePercent = $limitBytes > 0 ? min(100, (int) round(($storageBytes / $limitBytes) * 100)) : 0;
    $averageFileBytes = (int) ($metrics['average_file_bytes'] ?? 0);

    $formatBytes = static function (int $bytes): string {
        return $bytes >= 1073741824
            ? format_number_locale($bytes / 1073741824, 2).' GB'
            : ($bytes >= 1048576
                ? format_number_locale($bytes / 1048576, 2).' MB'
                : ($bytes >= 1024
                    ? format_number_locale($bytes / 1024, 2).' KB'
                    : $bytes.' B'));
    };

    $storageLabel = $formatBytes($storageBytes);
    $averageFileLabel = $formatBytes($averageFileBytes);
    $limitLabel = $limitMb > 0 ? format_number_locale($limitMb).' MB' : __('No storage limit');
    $categoryPalette = [
        'image' => '#0f766e',
        'video' => '#16a34a',
        'audio' => '#d97706',
        'document' => '#4f46e5',
        'spreadsheet' => '#0369a1',
        'pdf' => '#dc2626',
        'archive' => '#7c3aed',
        'other' => '#64748b',
    ];
    $categoryLabels = [
        'image' => __('Images'),
        'video' => __('Videos'),
        'audio' => __('Audio'),
        'document' => __('Documents'),
        'spreadsheet' => __('Sheets'),
        'pdf' => __('PDF'),
        'archive' => __('Archives'),
        'other' => __('Other'),
    ];
    $totalCategorizedBytes = collect($storageByCategory ?? [])->sum();
    $dominantCategory = collect($storageByCategory ?? [])->sortDesc()->keys()->first();
    $dominantCategoryBytes = $dominantCategory ? (int) (($storageByCategory[$dominantCategory] ?? 0)) : 0;
    $dominantCategoryShare = $totalCategorizedBytes > 0 && $dominantCategoryBytes > 0
        ? (int) round(($dominantCategoryBytes / $totalCategorizedBytes) * 100)
        : 0;
    $dominantCategoryLabel = $dominantCategory ? ($categoryLabels[$dominantCategory] ?? ucfirst((string) $dominantCategory)) : __('No assets yet');
    $dominantCategoryTitle = match ($dominantCategory) {
        'image' => __('Image-heavy library'),
        'video' => __('Video-heavy library'),
        'audio' => __('Audio-heavy library'),
        'document', 'spreadsheet', 'pdf' => __('Document-led library'),
        'archive' => __('Archive-heavy library'),
        'other' => __('Mixed asset library'),
        default => __('No assets yet'),
    };
@endphp

<section class="overflow-hidden rounded-[1.25rem] border bg-white shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.7);">
    <div class="grid gap-5 px-5 py-5 lg:grid-cols-[minmax(0,1fr)_16rem] lg:items-center sm:px-6">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(15,118,110,0.22); background: rgba(15,118,110,0.08); color: #0f766e;">
                    <i class="fa-light fa-folders"></i>
                    {{ __('Storage health') }}
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(16,185,129,0.25); background: rgba(16,185,129,0.09); color: #047857;">
                    <i class="fa-light fa-chart-pie"></i>
                    {{ __('Library snapshot') }}
                </span>
            </div>
            <h2 class="mt-3 text-xl font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">{{ __('See storage usage, asset mix, and library pressure quickly') }}</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Review total footprint, image-heavy usage, and current file-library balance before opening the full manager.') }}</p>
        </div>

        <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background: rgba(var(--theme-surface-bg-rgb),0.55);">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Usage') }}</p>
                    <p class="mt-1 text-3xl font-semibold tracking-[-0.055em]" style="color: var(--theme-header-text-color);">{{ $usagePercent }}%</p>
                </div>
                <p class="text-sm font-semibold" style="color: var(--theme-muted-text-color);">{{ $storageLabel }}</p>
            </div>
            <div class="mt-4 h-2 overflow-hidden rounded-full" style="background-color: rgba(15,118,110,0.12);">
                <div class="h-full rounded-full transition-all" style="width: {{ max(4, $usagePercent) }}%; background: var(--theme-brand-gradient);"></div>
            </div>
            <x-ui.button :href="$item['route'] ?? route('portal.files.index')" size="sm" class="mt-4 w-full justify-center" wire:navigate>
                {{ __('Open files') }}
                <i class="fa-light fa-arrow-right"></i>
            </x-ui.button>
        </div>
    </div>

    <div class="grid border-t lg:grid-cols-[minmax(0,1fr)_28rem]" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
        <div class="p-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Storage usage') }}</p>
                    <p class="mt-2 text-[2rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ $storageLabel }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">
                        {{ $limitBytes > 0 ? __(':used of :limit used across your file library.', ['used' => $storageLabel, 'limit' => $limitLabel]) : __('This plan does not currently enforce a storage ceiling.') }}
                    </p>
                </div>
                <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background: rgba(var(--theme-surface-bg-rgb),0.55);">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Capacity') }}</p>
                    <p class="mt-1 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $limitLabel }}</p>
                </div>
            </div>

            <div class="mt-5 h-2 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb),0.35);">
                <div class="h-full rounded-full transition-all" style="width: {{ max(4, $usagePercent) }}%; background: var(--theme-brand-gradient);"></div>
            </div>

            <div class="mt-5 grid border sm:grid-cols-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                @foreach ([
                    [__('All entries'), format_number_locale((int) ($metrics['total'] ?? 0))],
                    [__('Images'), format_number_locale((int) ($metrics['images'] ?? 0))],
                    [__('Folders'), format_number_locale((int) ($metrics['folders'] ?? 0))],
                    [__('Avg file size'), $averageFileLabel],
                ] as $stat)
                    <div class="border-b p-4 sm:border-r sm:border-b-0" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ $stat[0] }}</p>
                        <p class="mt-2 text-[1.45rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $stat[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <aside class="border-t p-5 lg:border-l lg:border-t-0" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Asset mix') }}</p>
            <h3 class="mt-3 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $dominantCategoryTitle }}</h3>
            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">
                @if ($dominantCategory)
                    {{ __(':type currently accounts for about :share% of the occupied storage in your library.', ['type' => Str::lower($dominantCategoryLabel), 'share' => $dominantCategoryShare]) }}
                @else
                    {{ __('A quick split of the file types currently consuming space in your library.') }}
                @endif
            </p>

            <div class="mt-5 space-y-3">
                @forelse (collect($storageByCategory ?? [])->sortDesc()->take(4) as $category => $bytes)
                    @php($categoryShare = $totalCategorizedBytes > 0 ? max(3, (int) round(($bytes / $totalCategorizedBytes) * 100)) : 0)
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $categoryPalette[$category] ?? $categoryPalette['other'] }};"></span>
                                <span class="font-medium" style="color: var(--theme-header-text-color);">{{ $categoryLabels[$category] ?? ucfirst((string) $category) }}</span>
                            </div>
                            <span style="color: var(--theme-muted-text-color);">{{ $formatBytes((int) $bytes) }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full" style="background: rgba(var(--theme-border-color-rgb),0.28);">
                            <div class="h-full rounded-full" style="width: {{ $categoryShare }}%; background: {{ $categoryPalette[$category] ?? $categoryPalette['other'] }};"></div>
                        </div>
                    </div>
                @empty
                    <x-ui.empty icon="fa-light fa-folder-open" :title="__('No files stored yet')" :description="__('Type distribution will appear here once assets are uploaded into the library.')" />
                @endforelse
            </div>
        </aside>
    </div>

    <div class="grid border-t sm:grid-cols-3" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
        @foreach ([
            [__('File assets'), format_number_locale((int) ($metrics['files'] ?? 0)), __('Uploaded assets excluding folders.')],
            [__('Non-image assets'), format_number_locale((int) ($metrics['other_assets'] ?? 0)), __('Documents, videos, archives, and other stored files.')],
            [__('Storage footprint'), $storageLabel, __('Current occupied space across all stored assets.')],
        ] as $metric)
            <div class="min-h-[8.5rem] border-b p-5 sm:border-r sm:border-b-0" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ $metric[0] }}</p>
                <p class="mt-2 text-[1.65rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $metric[1] }}</p>
                <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $metric[2] }}</p>
            </div>
        @endforeach
    </div>
</section>
