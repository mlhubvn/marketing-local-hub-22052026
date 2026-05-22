@props([
    'business',
    'active' => 'overview',
])

<nav class="flex gap-2 overflow-x-auto rounded-[1.1rem] border p-2" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-overlay) 96%, transparent);">
    @foreach ([
        ['key' => 'overview', 'label' => __('Overview'), 'href' => route('portal.businesses.show', $business)],
        ['key' => 'campaigns', 'label' => __('Campaigns'), 'href' => route('portal.businesses.campaigns.index', $business)],
        ['key' => 'locations', 'label' => __('Locations'), 'href' => route('portal.businesses.locations', $business)],
        ['key' => 'customers', 'label' => __('Customers'), 'href' => route('portal.businesses.customers.index', $business)],
        ['key' => 'reviews', 'label' => __('Reviews'), 'href' => route('portal.businesses.reviews', $business)],
        ['key' => 'leads', 'label' => __('Leads'), 'href' => route('portal.businesses.leads', $business)],
        ['key' => 'reports', 'label' => __('Reports'), 'href' => route('portal.businesses.reports', $business)],
        ['key' => 'settings', 'label' => __('Settings'), 'href' => route('portal.businesses.edit', $business)],
    ] as $tab)
        <a
            href="{{ $tab['href'] }}"
            wire:navigate
            class="whitespace-nowrap rounded-xl border px-4 py-2 text-sm font-semibold transition duration-200 hover:shadow-[0_12px_28px_-28px_rgba(var(--theme-accent-rgb),0.8)]"
            style="{{ $active === $tab['key']
                ? 'border-color: rgba(var(--theme-accent-rgb),0.16); background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);'
                : 'border-color: transparent; color: var(--theme-muted-text-color);'
            }}"
            onmouseover="this.style.borderColor='rgba(var(--theme-accent-rgb),0.18)'; this.style.backgroundColor='rgba(var(--theme-accent-rgb),0.08)'; this.style.color='var(--theme-accent)'"
            onmouseout="this.style.borderColor='{{ $active === $tab['key'] ? 'rgba(var(--theme-accent-rgb),0.16)' : 'transparent' }}'; this.style.backgroundColor='{{ $active === $tab['key'] ? 'rgba(var(--theme-accent-rgb),0.12)' : 'transparent' }}'; this.style.color='{{ $active === $tab['key'] ? 'var(--theme-accent)' : 'var(--theme-muted-text-color)' }}'"
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
