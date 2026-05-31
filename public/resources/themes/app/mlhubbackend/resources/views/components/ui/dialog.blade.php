@props([
    'title' => null,
    'description' => null,
    'width' => 'lg',
    'dismissible' => false,
])

@php
    $widths = [
        'sm' => 'max-w-[26rem]',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        '2xl' => 'max-w-5xl',
        '3xl' => 'max-w-6xl',
    ];

    $hasBodyContent = trim((string) $slot) !== '';
@endphp

<div x-data="{ open: false }" {{ $attributes }}>
    <div x-on:click="open = true">
        {{ $trigger ?? '' }}
    </div>

    <template x-teleport="body">
        <div x-cloak x-show="open" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="open = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="open = false"></div>

            <div class="relative flex min-h-full items-start justify-center">
                <div x-show="open" x-transition.opacity.scale.90 class="relative w-full {{ $widths[$width] ?? $widths['lg'] }}">
                    <div class="relative flex max-h-[calc(100vh-2.5rem)] min-h-0 flex-col overflow-hidden rounded-[1.15rem] border shadow-[0_32px_80px_-34px_rgba(15,23,42,0.32)] sm:max-h-[calc(100vh-3.5rem)]" style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent); background-color: var(--theme-surface-overlay);">
                    @if ($dismissible && ! $title && ! $description)
                        <button type="button" class="absolute right-4 top-4 z-10 inline-flex h-9 w-9 items-center justify-center rounded-[0.7rem] border transition hover:bg-black/5 dark:hover:bg-white/10" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color); background-color: var(--theme-surface-overlay);" x-on:click="open = false" title="{{ __('Close') }}">
                            <i class="fa-light fa-xmark text-lg"></i>
                        </button>
                    @endif

                    @if ($title || $description)
                        <div class="shrink-0 flex items-start justify-between gap-4 border-b px-5 py-4 sm:px-6 sm:py-5" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent);">
                            <div class="min-w-0">
                                @if ($title)
                                    <h3 class="text-[1.05rem] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ $title }}</h3>
                                @endif
                                @if ($description)
                                    <p class="mt-2 text-[15px] leading-7" style="color: var(--theme-muted-text-color);">{{ $description }}</p>
                                @endif
                            </div>

                            @if ($dismissible)
                                <button type="button" class="transition" style="color: var(--theme-muted-text-color);" x-on:click="open = false">
                                    <i class="fa-light fa-xmark text-lg"></i>
                                </button>
                            @endif
                        </div>
                    @endif

                    @if ($hasBodyContent)
                        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 py-4 sm:px-6 sm:py-5">
                            {{ $slot }}
                        </div>
                    @endif

                    @if (isset($footer))
                        <div class="shrink-0 border-t px-5 py-4 sm:px-6" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent); background-color: color-mix(in srgb, var(--theme-surface-soft) 88%, transparent);">
                            {{ $footer }}
                        </div>
                    @endif
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
