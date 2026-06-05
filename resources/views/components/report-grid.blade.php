@props([
    'items' => [],
])

<div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
    @foreach ($items as $item)
        <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
            <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ is_numeric($item['value']) ? format_number_locale((float) $item['value']) : $item['value'] }}</p>
            <p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $item['label'] }}</p>
        </div>
    @endforeach
</div>
