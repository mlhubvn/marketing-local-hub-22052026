@component(theme_view('layouts.app', 'app'), ['title' => __('Activity Log')])
    @php
        $formatMetadataValue = static function (mixed $value): string {
            if (is_array($value)) {
                return collect($value)
                    ->map(fn (mixed $nestedValue, mixed $nestedKey) => is_string($nestedKey)
                        ? $nestedKey.': '.(is_scalar($nestedValue) || $nestedValue === null ? (string) $nestedValue : json_encode($nestedValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                        : (is_scalar($nestedValue) || $nestedValue === null ? (string) $nestedValue : json_encode($nestedValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)))
                    ->implode(', ');
            }

            if (is_object($value)) {
                $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                return $json !== false ? $json : '[object]';
            }

            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }

            return $value === null ? '-' : (string) $value;
        };

        $areaLabel = static fn (?string $area): string => filled($area) ? str((string) $area)->headline()->toString() : __('Unknown');
        $latestTime = $latest?->created_at;
    @endphp

    <div class="mx-auto w-full max-w-[88rem] space-y-6">
        <section class="rounded-[1.25rem] border p-6 shadow-sm md:p-7" style="border-color: rgba(var(--theme-border-color-rgb), .58); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), .11), transparent 42%), var(--theme-surface-base);">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-lg border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">
                        <i class="fa-light fa-clipboard-list-check"></i>
                        {{ __('User portal') }}
                    </span>
                    <h1 class="mt-4 text-3xl font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ __('Activity Log') }}</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Review account actions, admin changes, route events, and recent activity from one workspace.') }}</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <x-ui.button href="{{ route('portal.dashboard') }}" variant="outline" wire:navigate>
                        <i class="fa-light fa-arrow-left"></i>
                        {{ __('Back to dashboard') }}
                    </x-ui.button>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => __('Total entries'), 'value' => number_format($totalLogs), 'hint' => __('All activity recorded for this user.'), 'icon' => 'fa-list-check', 'tone' => 'var(--theme-accent)'],
                ['label' => __('Filtered'), 'value' => number_format($filteredCount), 'hint' => __('Rows matching the current view.'), 'icon' => 'fa-filter', 'tone' => '#0ea5e9'],
                ['label' => __('Areas'), 'value' => number_format($areas->count()), 'hint' => __('Portal and admin surfaces represented.'), 'icon' => 'fa-layer-group', 'tone' => '#d97706'],
                ['label' => __('Latest'), 'value' => $latestTime?->diffForHumans() ?? __('No activity'), 'hint' => $latest?->event ?? __('Activity will appear after key actions.'), 'icon' => 'fa-clock-rotate-left', 'tone' => 'var(--theme-success-color)'],
            ] as $card)
                <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ $card['label'] }}</p>
                            <p class="mt-3 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $card['value'] }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: color-mix(in srgb, {{ $card['tone'] }} 10%, white); color: {{ $card['tone'] }};">
                            <i class="fa-light {{ $card['icon'] }}"></i>
                        </div>
                    </div>
                    <p class="mt-3 line-clamp-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $card['hint'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="overflow-hidden rounded-[1.15rem] border shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
            <div class="border-b p-5" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Directory') }}</p>
                        <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Recent activity') }}</h2>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Filter, inspect, and trace chronological actions for your account.') }}</p>
                    </div>

                    <form method="GET" action="{{ route('portal.activity') }}" class="grid w-full gap-3 md:grid-cols-[minmax(16rem,1fr)_10rem_10rem_8rem_auto] xl:max-w-4xl">
                        <x-ui.input name="search" :value="request('search')" :placeholder="__('Search event, description, route...')" />
                        <x-ui.select name="area">
                            <option value="all">{{ __('All areas') }}</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area }}" @selected(request('area', 'all') === $area)>{{ $areaLabel($area) }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.select name="range">
                            <option value="all" @selected(request('range', 'all') === 'all')>{{ __('All time') }}</option>
                            <option value="7" @selected(request('range') === '7')>{{ __('Last 7 days') }}</option>
                            <option value="30" @selected(request('range') === '30')>{{ __('Last 30 days') }}</option>
                            <option value="90" @selected(request('range') === '90')>{{ __('Last 90 days') }}</option>
                        </x-ui.select>
                        <x-ui.select name="per_page">
                            @foreach ([10, 15, 25, 50] as $size)
                                <option value="{{ $size }}" @selected((int) request('per_page', 15) === $size)>{{ $size }}/{{ __('page') }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.button type="submit">
                            <i class="fa-light fa-magnifying-glass"></i>
                            {{ __('Apply') }}
                        </x-ui.button>
                    </form>
                </div>
            </div>

            <div class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                @forelse ($logs as $log)
                    <article class="grid gap-4 px-5 py-4 lg:grid-cols-[minmax(13rem,0.95fr)_minmax(18rem,1.35fr)_8rem_minmax(10rem,0.85fr)] lg:items-center">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb), .1); color: var(--theme-accent);">
                                <i class="fa-light {{ $log->area === 'admin' ? 'fa-shield-check' : 'fa-user-check' }}"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $log->event }}</p>
                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $log->created_at?->diffForHumans() }}</p>
                            </div>
                        </div>

                        <div class="min-w-0">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $log->description ?: '-' }}</p>
                            @if (! empty($log->metadata))
                                <p class="mt-1 line-clamp-2 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ collect($log->metadata)->map(fn ($value, $key) => $key.': '.$formatMetadataValue($value))->implode(' · ') }}</p>
                            @endif
                        </div>

                        <div>
                            <x-ui.badge :variant="$log->area === 'user' ? 'primary' : 'neutral'">{{ strtoupper($log->area ?: 'system') }}</x-ui.badge>
                        </div>

                        <div class="text-left lg:text-right">
                            <p class="font-mono text-xs" style="color: var(--theme-muted-text-color);">{{ $log->route_name ?: '-' }}</p>
                            <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $log->created_at?->format('Y-m-d H:i') }}</p>
                        </div>
                    </article>
                @empty
                    <div class="px-5 py-14 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb), .1); color: var(--theme-accent);">
                            <i class="fa-light fa-clock-rotate-left"></i>
                        </div>
                        <p class="mt-4 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No activity found') }}</p>
                        <p class="mx-auto mt-2 max-w-md text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Try clearing filters or wait for new account actions to be recorded.') }}</p>
                    </div>
                @endforelse
            </div>

            @if ($logs->hasPages())
                <div class="border-t px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    {{ $logs->links() }}
                </div>
            @endif
        </section>
    </div>
@endcomponent
