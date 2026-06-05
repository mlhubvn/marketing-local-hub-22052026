<section class="overflow-hidden rounded-[1.25rem] border bg-white shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.7);">
    <div class="grid gap-5 px-5 py-5 lg:grid-cols-[minmax(0,1fr)_16rem] lg:items-center sm:px-6">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb),0.22); background: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                    <i class="fa-light fa-clipboard-list-check"></i>
                    {{ __('Recent account activity') }}
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-warning-color-rgb),0.28); background: rgba(var(--theme-warning-color-rgb),0.12); color: var(--theme-link-hover-color);">
                    <i class="fa-light fa-clock-rotate-left"></i>
                    {{ __('Chronological log') }}
                </span>
            </div>
            <h2 class="mt-3 text-xl font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">{{ __('Review what happened across your account, day by day') }}</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Keep an eye on recent actions, portal changes, and logged events without leaving the dashboard.') }}</p>
        </div>

        <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background: rgba(var(--theme-surface-bg-rgb),0.55);">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Entries loaded') }}</p>
                    <p class="mt-1 text-3xl font-semibold tracking-[-0.055em]" style="color: var(--theme-header-text-color);">{{ number_format($metrics['loaded'] ?? 0) }}</p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                    <i class="fa-light fa-list-timeline"></i>
                </span>
            </div>
            <x-ui.button :href="$route" size="sm" class="mt-4 w-full justify-center" wire:navigate>
                {{ __('Open log') }}
                <i class="fa-light fa-arrow-right"></i>
            </x-ui.button>
        </div>
    </div>

    <div class="grid border-t sm:grid-cols-3" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
        @foreach ([
            [__('Today'), number_format($metrics['today'] ?? 0)],
            [__('Last 7 days'), number_format($metrics['week'] ?? 0)],
            [__('Entries loaded'), number_format($metrics['loaded'] ?? 0)],
        ] as $metric)
            <div class="border-b p-4 sm:border-r sm:border-b-0" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ $metric[0] }}</p>
                <p class="mt-2 text-[1.45rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $metric[1] }}</p>
            </div>
        @endforeach
    </div>

    <div class="overflow-x-auto border-t" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
        <x-ui.table class="rounded-none border-0 shadow-none">
            <x-ui.table-head>
                <x-ui.table-cell head class="w-[250px]">{{ __('Event') }}</x-ui.table-cell>
                <x-ui.table-cell head>{{ __('Description') }}</x-ui.table-cell>
                <x-ui.table-cell head class="w-[120px]">{{ __('Area') }}</x-ui.table-cell>
                <x-ui.table-cell head class="w-[180px]">{{ __('Time') }}</x-ui.table-cell>
            </x-ui.table-head>

            <x-ui.table-body>
                @forelse ($logs as $log)
                    <x-ui.table-row>
                        <x-ui.table-cell class="w-[250px] align-top">
                            <div class="flex items-start gap-3">
                                <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $log->area_variant === 'primary' ? 'rgba(var(--theme-accent-rgb), 0.92)' : 'rgba(100,116,139,0.7)' }};"></span>
                                <div class="space-y-1">
                                    <p class="font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ $log->action }}</p>
                                    @if ($log->module && ! in_array($log->module, ['Default Livewire', 'General'], true))
                                        <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ $log->module }}</p>
                                    @endif
                                </div>
                            </div>
                        </x-ui.table-cell>

                        <x-ui.table-cell class="align-top">
                            <div class="space-y-1.5">
                                <p class="font-medium leading-6" style="color: var(--theme-header-text-color);">{{ $log->description }}</p>
                                @if ($log->metadata_summary->isNotEmpty())
                                    <p class="text-sm leading-6" style="color: var(--theme-muted-text-color);">
                                        {{ $log->metadata_summary->take(2)->implode(' · ') }}
                                    </p>
                                @endif
                            </div>
                        </x-ui.table-cell>

                        <x-ui.table-cell class="w-[120px] align-top">
                            <x-ui.badge :variant="$log->area_variant">{{ $log->area_label }}</x-ui.badge>
                        </x-ui.table-cell>

                        <x-ui.table-cell class="w-[180px] align-top">
                            <div class="space-y-1">
                                <p class="font-medium" style="color: var(--theme-header-text-color);">{{ $log->created_at_label }}</p>
                                <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ $log->created_at_relative }}</p>
                            </div>
                        </x-ui.table-cell>
                    </x-ui.table-row>
                @empty
                    <x-ui.table-row>
                        <x-ui.table-cell colspan="4" class="py-10">
                            <x-ui.empty
                                icon="fa-light fa-clipboard-list-check"
                                :title="__('No activity recorded yet')"
                                :description="__('User-facing activity entries will appear here once actions are logged from the portal.')"
                            />
                        </x-ui.table-cell>
                    </x-ui.table-row>
                @endforelse
            </x-ui.table-body>
        </x-ui.table>
    </div>
</section>
