@props([
    'title',
    'subtitle' => null,
])

<section {{ $attributes->merge(['class' => 'rounded-[1.2rem] border p-5']) }} style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
    <div class="mb-5 flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $title }}</p>
            @if ($subtitle)
                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
            <i class="fa-light fa-chart-line"></i>
        </div>
    </div>

    {{ $slot }}
</section>
