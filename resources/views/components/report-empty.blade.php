@props([
    'title',
    'description',
])

<div class="rounded-[1.05rem] border p-6 text-center" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background:
    linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.06), transparent 48%),
    color-mix(in srgb, var(--theme-surface-base) 92%, transparent);">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-accent-rgb),0.16); background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
        <i class="fa-light fa-chart-simple"></i>
    </div>
    <p class="mt-4 font-semibold" style="color: var(--theme-header-text-color);">{{ $title }}</p>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $description }}</p>
</div>
