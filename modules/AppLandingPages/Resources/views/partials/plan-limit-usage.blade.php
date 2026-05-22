@php
    $usage = $usage ?? [];
    $title = $title ?? __('Plan usage');
    $description = $description ?? __('Limits update as you create Growth Tools, public pages, QR codes, and templates.');
    $compact = (bool) ($compact ?? false);
@endphp

@php
    $limitedItems = collect($usage)->filter(fn ($item) => ! ($item['unlimited'] ?? false));
    $totalUsed = (int) $limitedItems->sum('used');
    $totalLimit = (int) $limitedItems->sum('limit');
    $overallPercent = $totalLimit > 0 ? min(100, (int) round(($totalUsed / $totalLimit) * 100)) : 0;
    $fullCount = (int) collect($usage)->where('is_full', true)->count();
@endphp

@if ($compact)
    <section class="rounded-xl border p-3" style="border-color: {{ $fullCount > 0 ? 'rgba(var(--theme-danger-color-rgb), .30)' : 'rgba(var(--theme-border-color-rgb), .62)' }}; background-color: {{ $fullCount > 0 ? 'rgba(var(--theme-danger-color-rgb), .035)' : 'color-mix(in srgb, var(--theme-surface-base) 80%, transparent)' }};">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(15,118,110,0.22); background: rgba(15,118,110,0.08); color: #0f766e;">
                        <i class="fa-light fa-gauge-high"></i>{{ __('Limits') }}
                    </span>
                    @if ($fullCount > 0)
                        <span class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-danger-color-rgb),0.24); background: rgba(var(--theme-danger-color-rgb),0.09); color: var(--theme-danger-color);">
                            <i class="fa-light fa-triangle-exclamation"></i>{{ __(':count full', ['count' => $fullCount]) }}
                        </span>
                    @endif
                </div>
                <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $title }}</p>
                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $description }}</p>
            </div>
            <div class="shrink-0 rounded-xl border px-3 py-2 text-right" style="border-color: rgba(var(--theme-border-color-rgb), .54); background-color: var(--theme-surface-overlay);">
                <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Usage') }}</p>
                <p class="mt-1 text-lg font-semibold leading-none" style="color: var(--theme-header-text-color);">{{ $overallPercent }}%</p>
            </div>
        </div>

        <div class="mt-3 grid gap-2 sm:grid-cols-2">
            @foreach ($usage as $item)
                @php
                    $isFull = (bool) ($item['is_full'] ?? false);
                    $unlimited = (bool) ($item['unlimited'] ?? false);
                    $percent = (int) ($item['percent'] ?? 0);
                @endphp
                <div class="rounded-lg border px-3 py-2" style="border-color: {{ $isFull ? 'rgba(var(--theme-danger-color-rgb), .30)' : 'rgba(var(--theme-border-color-rgb), .46)' }}; background-color: var(--theme-surface-overlay);">
                    <div class="flex items-center justify-between gap-2">
                        <span class="truncate text-[11px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ $item['label'] }}</span>
                        @if ($isFull)
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold" style="background-color: rgba(var(--theme-danger-color-rgb), .10); color: var(--theme-danger-color);">{{ __('Full') }}</span>
                        @elseif (! $unlimited)
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold" style="background-color: rgba(var(--theme-success-color-rgb), .10); color: var(--theme-success-color);">{{ __(':count left', ['count' => number_format((int) $item['remaining'])]) }}</span>
                        @endif
                    </div>
                    <div class="mt-1 flex items-baseline justify-between gap-2">
                        <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $item['used']) }}</span>
                        <span class="text-xs" style="color: var(--theme-muted-text-color);">/ {{ $unlimited ? __('Unlimited') : number_format((int) $item['limit']) }}</span>
                    </div>
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), .35);">
                        <div class="h-full rounded-full" style="width: {{ $unlimited ? 100 : max(3, $percent) }}%; background-color: {{ $isFull ? 'var(--theme-danger-color)' : 'var(--theme-accent)' }};"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @php return; @endphp
@endif

<section class="overflow-hidden rounded-[1.25rem] border bg-white shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.7);">
    <div class="grid gap-5 px-5 py-5 lg:grid-cols-[minmax(0,1fr)_16rem] lg:items-center sm:px-6">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(15,118,110,0.22); background: rgba(15,118,110,0.08); color: #0f766e;">
                    <i class="fa-light fa-gauge-high"></i>
                    {{ __('Plan limits') }}
                </span>
                @if (auth()->user()?->plan)
                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: #fff; color: var(--theme-muted-text-color);">
                        {{ auth()->user()->plan->name }}
                    </span>
                @endif
                @if ($fullCount > 0)
                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-danger-color-rgb),0.24); background: rgba(var(--theme-danger-color-rgb),0.09); color: var(--theme-danger-color);">
                        <i class="fa-light fa-triangle-exclamation"></i>
                        {{ __(':count full', ['count' => $fullCount]) }}
                    </span>
                @endif
            </div>
            <h2 class="mt-3 text-xl font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">{{ $title }}</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $description }}</p>
        </div>
        <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background: rgba(var(--theme-surface-bg-rgb),0.55);">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Usage') }}</p>
                    <p class="mt-1 text-3xl font-semibold tracking-[-0.055em]" style="color: var(--theme-header-text-color);">{{ $overallPercent }}%</p>
                </div>
                <p class="text-sm font-semibold" style="color: var(--theme-muted-text-color);">{{ number_format($totalUsed) }}/{{ number_format($totalLimit) }}</p>
            </div>
            <div class="mt-4 h-2 overflow-hidden rounded-full" style="background-color: rgba(15,118,110,0.12);">
                <div class="h-full rounded-full transition-all" style="width: {{ max(4, $overallPercent) }}%; background: linear-gradient(90deg, #0f766e, #14b8a6);"></div>
            </div>
        </div>
    </div>

    <div class="grid border-t sm:grid-cols-2 xl:grid-cols-5" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
        @foreach ($usage as $item)
            @php
                $isFull = (bool) ($item['is_full'] ?? false);
                $unlimited = (bool) ($item['unlimited'] ?? false);
                $percent = (int) ($item['percent'] ?? 0);
            @endphp
            <div class="min-h-[7.25rem] border-b p-4 sm:border-r xl:border-b-0" style="border-color: {{ $isFull ? 'rgba(var(--theme-danger-color-rgb), .34)' : 'rgba(var(--theme-border-color-rgb), .52)' }}; background-color: {{ $isFull ? 'rgba(var(--theme-danger-color-rgb), .025)' : 'transparent' }};">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-xs font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ $item['label'] }}</p>
                        <p class="mt-1 text-lg font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">
                            {{ number_format((int) $item['used']) }}
                            <span class="text-xs font-medium" style="color: var(--theme-muted-text-color);">/ {{ $unlimited ? __('Unlimited') : number_format((int) $item['limit']) }}</span>
                        </p>
                    </div>
                    @if ($isFull)
                        <span class="inline-flex rounded-full px-2 py-1 text-[10px] font-semibold" style="background-color: rgba(var(--theme-danger-color-rgb), .10); color: var(--theme-danger-color);">{{ __('Full') }}</span>
                    @elseif (! $unlimited)
                        <span class="inline-flex rounded-full px-2 py-1 text-[10px] font-semibold" style="background-color: rgba(var(--theme-success-color-rgb), .10); color: var(--theme-success-color);">{{ __(':count left', ['count' => number_format((int) $item['remaining'])]) }}</span>
                    @endif
                </div>
                <div class="mt-3 h-1.5 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), .35);">
                    <div class="h-full rounded-full" style="width: {{ $unlimited ? 100 : max(3, $percent) }}%; background-color: {{ $isFull ? 'var(--theme-danger-color)' : 'var(--theme-accent)' }};"></div>
                </div>
            </div>
        @endforeach
    </div>
</section>
