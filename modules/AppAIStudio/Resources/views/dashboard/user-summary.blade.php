@php
    $creditSummary = $creditSummary ?? ['remaining' => null, 'used' => 0, 'unlimited' => true, 'usage_percent' => 0];
    $creditRemainingLabel = ($creditSummary['unlimited'] ?? false)
        ? __('Unlimited')
        : format_number_locale((int) ($creditSummary['remaining'] ?? 0));
    $creditUsagePercent = (int) ($creditSummary['usage_percent'] ?? 0);
@endphp

<section class="overflow-hidden rounded-[1.25rem] border bg-white shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.7);">
    <div class="grid gap-5 px-5 py-5 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-center sm:px-6">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb),0.22); background: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                    <i class="fa-light fa-sparkles"></i>
                    {{ __('AI toolkit') }}
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-warning-color-rgb),0.28); background: rgba(var(--theme-warning-color-rgb),0.12); color: var(--theme-link-hover-color);">
                    <i class="fa-light fa-check"></i>
                    {{ __('Ready to create') }}
                </span>
            </div>
            <h2 class="mt-3 text-xl font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">{{ __('Jump into the right AI workflow faster') }}</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Keep image, caption, video, and repurpose tools close together so ideation starts here and publishing stays separate.') }}</p>
        </div>

        <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background: rgba(var(--theme-surface-bg-rgb),0.55);">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Credits left') }}</p>
                    <p class="mt-1 text-3xl font-semibold tracking-[-0.055em]" style="color: var(--theme-header-text-color);">{{ $creditRemainingLabel }}</p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                    <i class="fa-light fa-coins"></i>
                </span>
            </div>
            @unless ($creditSummary['unlimited'] ?? false)
                <div class="mt-4 h-2 overflow-hidden rounded-full" style="background-color: rgba(15,118,110,0.12);">
                    <div class="h-full rounded-full transition-all" style="width: {{ max(4, $creditUsagePercent) }}%; background: var(--theme-brand-gradient);"></div>
                </div>
            @endunless
            <x-ui.button :href="$item['route'] ?? route('portal.ai-studio')" size="sm" class="mt-4 w-full justify-center" wire:navigate>
                {{ __('Open AI Studio') }}
                <i class="fa-light fa-arrow-right"></i>
            </x-ui.button>
        </div>
    </div>

    <div class="grid border-t lg:grid-cols-[minmax(0,1fr)_28rem]" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
        <div class="grid sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($tools as $index => $tool)
                @php($tone = ['#2563eb', '#7c3aed', '#0f766e', '#d97706'][$index % 4])
                <a href="{{ $tool['route'] }}" wire:navigate class="flex min-h-[12rem] flex-col border-b p-5 transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)] sm:border-r xl:border-b-0" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl" style="background: {{ $tone }}14; color: {{ $tone }};">
                        <i class="{{ $tool['icon'] }}"></i>
                    </span>
                    <p class="mt-4 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $tool['label'] }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $tool['description'] }}</p>
                    <span class="mt-auto inline-flex items-center gap-2 pt-4 text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: #0f766e;">
                        {{ __('Open tool') }}
                        <i class="fa-light fa-arrow-right"></i>
                    </span>
                </a>
            @endforeach
        </div>

        <aside class="border-t p-5 lg:border-l lg:border-t-0" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Related tools') }}</p>
            <h3 class="mt-3 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('AI adjacent workflows') }}</h3>
            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Shortcuts for the workflows that usually follow AI ideation and generation.') }}</p>

            <div class="mt-5 grid gap-3">
                @foreach ($supportTools as $tool)
                    <a href="{{ $tool['route'] }}" wire:navigate class="flex items-center justify-between gap-3 rounded-xl border px-4 py-3 transition hover:-translate-y-px" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: rgba(var(--theme-surface-bg-rgb),0.45);">
                        <span class="min-w-0 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $tool['label'] }}</span>
                        <span class="inline-flex shrink-0 items-center gap-2 text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-accent);">
                            {{ __('Open') }}
                            <i class="fa-light fa-arrow-right"></i>
                        </span>
                    </a>
                @endforeach
            </div>
        </aside>
    </div>
</section>
