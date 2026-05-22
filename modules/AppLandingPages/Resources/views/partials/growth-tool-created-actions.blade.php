@if (! empty($createdGrowthToolActions))
    <section
        class="rounded-[1rem] border p-4"
        style="border-color: rgba(var(--theme-success-color-rgb), .28); background: linear-gradient(135deg, rgba(var(--theme-success-color-rgb), .10), transparent 44%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);"
        x-data="{ copied: false }"
    >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">
                    <i class="fa-light fa-circle-check mr-2" style="color: var(--theme-success-color);"></i>{{ __('Growth tool created') }}
                </p>
                <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $createdGrowthToolActions['message'] }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ $createdGrowthToolActions['public_url'] }}" target="_blank" class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);">
                    <i class="fa-light fa-arrow-up-right"></i>{{ __('View Page') }}
                </a>
                @if (! empty($createdGrowthToolActions['edit_url']))
                    <a href="{{ $createdGrowthToolActions['edit_url'] }}" wire:navigate class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .30); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .08);">
                        <i class="fa-light fa-pen"></i>{{ __('Edit Design') }}
                    </a>
                @endif
                <button type="button" x-on:click="navigator.clipboard?.writeText(@js($createdGrowthToolActions['public_url'])); copied = true; setTimeout(() => copied = false, 1400)" class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);">
                    <i class="fa-light" x-bind:class="copied ? 'fa-check' : 'fa-copy'"></i><span x-text="copied ? @js(__('Copied')) : @js(__('Copy Link'))"></span>
                </button>
                <a href="{{ $createdGrowthToolActions['qr_png_url'] }}" class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);">
                    <i class="fa-light fa-qrcode"></i>{{ __('Download QR') }}
                </a>
                <button type="button" wire:click="dismissCreatedGrowthToolActions" class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color); background-color: var(--theme-surface-overlay);">
                    <i class="fa-light fa-list"></i>{{ __('Back to list') }}
                </button>
            </div>
        </div>
    </section>
@endif
