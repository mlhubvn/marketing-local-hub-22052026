<x-ui.dialog :title="__('Delete this location?')" :description="__('This permanently removes the branch from your location directory.')" width="sm" dismissible>
    <x-slot:trigger>
        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border text-sm transition hover:-translate-y-px" style="border-color: rgba(var(--theme-danger-color-rgb),0.28); color: var(--theme-danger-color); background-color: var(--theme-surface-base);" title="{{ __('Delete') }}">
            <i class="fa-light fa-trash"></i>
        </button>
    </x-slot:trigger>

    <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-danger-color-rgb),0.18); background: linear-gradient(135deg, rgba(var(--theme-danger-color-rgb),0.08), transparent 70%);">
        <div class="flex items-center gap-3">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-danger-color-rgb),0.12); color: var(--theme-danger-color);">
                <i class="fa-light fa-triangle-exclamation"></i>
            </span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $location->name }}</p>
                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Campaigns using this location may lose their branch context.') }}</p>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <div class="flex items-center justify-end gap-3">
            <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
            <x-ui.button type="button" variant="danger" wire:click="delete({{ $location->id }})" x-on:click="open = false">
                <i class="fa-light fa-trash"></i>{{ __('Delete location') }}
            </x-ui.button>
        </div>
    </x-slot:footer>
</x-ui.dialog>
