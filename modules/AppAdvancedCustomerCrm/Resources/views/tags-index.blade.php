<div
    class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6"
    x-data="{ tagDialogOpen: false }"
    x-on:crm-tag-saved.window="tagDialogOpen = false"
>
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif

    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb),.13), transparent 34%), linear-gradient(35deg, rgba(var(--theme-warning-color-rgb),.10), transparent 38%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-7 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-tags"></i>{{ __('Advanced CRM') }}
                </div>
                <h1 class="mt-4 text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('CRM Tags') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">{{ __('Manage customer labels used by profiles, segments, reports, and automation rules.') }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button type="button" size="lg" x-on:click="tagDialogOpen = true">
                        <i class="fa-light fa-plus"></i>{{ __('New tag') }}
                    </x-ui.button>
                </div>
            </div>
            <div class="rounded-[1.2rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div><p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Tag library') }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Reusable CRM classifications') }}</p></div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);"><i class="fa-light fa-tag"></i></div>
                </div>
                <div class="mt-5 grid grid-cols-3 gap-3">
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);"><p class="text-2xl font-semibold tracking-[-0.045em]">{{ format_number_locale($tags->total()) }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Tags') }}</p></div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);"><p class="text-2xl font-semibold tracking-[-0.045em]">{{ format_number_locale($tags->where('is_system', true)->count()) }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('System') }}</p></div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);"><p class="text-2xl font-semibold tracking-[-0.045em]">{{ format_number_locale($tags->where('is_system', false)->count()) }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Custom') }}</p></div>
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-[1.25rem] border shadow-[0_12px_30px_-24px_rgba(15,23,42,.18)]" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Tag workspace') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('System tags stay locked. Custom tags remain editable.') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full border px-3 py-1 text-xs font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .58); color: var(--theme-muted-text-color);">{{ format_number_locale($tags->total()) }} {{ __('tags') }}</span>
                    <x-ui.select wire:model.live="perPage">
                        <option value="10">{{ __('10 / page') }}</option>
                        <option value="25">{{ __('25 / page') }}</option>
                        <option value="50">{{ __('50 / page') }}</option>
                    </x-ui.select>
                    <x-ui.button type="button" size="sm" x-on:click="tagDialogOpen = true">
                        <i class="fa-light fa-plus"></i>{{ __('New tag') }}
                    </x-ui.button>
                </div>
            </div>
        </div>

        @if($tags->count())
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                        <tr>
                            <th class="w-[42%] px-6 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Tag') }}</th>
                            <th class="px-6 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Customers') }}</th>
                            <th class="px-6 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Type') }}</th>
                            <th class="w-[10rem] px-6 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        @foreach($tags as $tag)
                            <tr class="transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)]">
                                <td class="px-6 py-4"><div class="flex items-center gap-3"><span class="h-3 w-3 shrink-0 rounded-full" style="background-color: {{ $tag->color }}"></span><div class="min-w-0"><p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $tag->name }}</p><p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $tag->description ?: $tag->slug }}</p></div></div></td>
                                <td class="px-6 py-4">{{ format_number_locale($tag->customers_count) }}</td>
                                <td class="px-6 py-4"><x-ui.badge :variant="$tag->is_system ? 'info' : 'neutral'">{{ $tag->is_system ? __('System') : __('Custom') }}</x-ui.badge></td>
                                <td class="px-6 py-4 text-right">
                                    @if(! $tag->is_system)
                                        <div class="inline-flex items-center gap-2">
                                            <button type="button" wire:click="edit({{ $tag->id }})" x-on:click="tagDialogOpen = true" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent);" title="{{ __('Edit') }}"><i class="fa-light fa-pen"></i></button>
                                            <button type="button" wire:click="delete({{ $tag->id }})" wire:confirm="{{ __('Delete this tag?') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color);" title="{{ __('Delete') }}"><i class="fa-light fa-trash"></i></button>
                                        </div>
                                    @else
                                        <span class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Locked') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Showing') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($tags->firstItem()) }}</span> - <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($tags->lastItem()) }}</span> {{ __('of') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($tags->total()) }}</span> {{ __('tags') }}</p>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="previousPage" @disabled($tags->onFirstPage()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"><i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}</button>
                    <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">{{ __('Page') }} {{ format_number_locale($tags->currentPage()) }} / {{ format_number_locale($tags->lastPage()) }}</span>
                    <button type="button" wire:click="nextPage" @disabled(! $tags->hasMorePages()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">{{ __('Next') }}<i class="fa-light fa-arrow-right"></i></button>
                </div>
            </div>
        @else
            <div class="p-8"><x-ui.empty icon="fa-light fa-tags" :title="__('No CRM tags yet')" /></div>
        @endif
    </section>

    <template x-teleport="body">
        <div x-cloak x-show="tagDialogOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="tagDialogOpen = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="tagDialogOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <form wire:submit="save" x-show="tagDialogOpen" x-transition.opacity.scale.90 class="relative flex max-h-[calc(100vh-3.5rem)] w-full max-w-xl flex-col overflow-hidden rounded-[1.15rem] border shadow-[0_32px_80px_-34px_rgba(15,23,42,.32)]" style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent); background-color: var(--theme-surface-overlay);">
                    <div class="flex items-start justify-between gap-4 border-b px-5 py-4 sm:px-6" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent);">
                        <div><h3 class="text-[1.05rem] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ $editingId ? __('Edit tag') : __('Create tag') }}</h3><p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Create custom labels for segmentation, customer profiles, and CRM automation.') }}</p></div>
                        <button type="button" style="color: var(--theme-muted-text-color);" x-on:click="tagDialogOpen = false"><i class="fa-light fa-xmark text-lg"></i></button>
                    </div>
                    <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4 sm:px-6">
                        <div class="grid gap-4">
                        <x-ui.input wire:model="name" name="name" :label="__('Name')" :error="$errors->first('name')" />
                        <x-ui.textarea wire:model="description" name="description" :label="__('Description')" rows="3">{{ $description }}</x-ui.textarea>
                        <x-ui.color-picker wire:model="color" name="color" :label="__('Color')" :value="$color" :error="$errors->first('color')" />
                        </div>
                    </div>
                    <div class="shrink-0 flex justify-end gap-3 border-t px-5 py-4 sm:px-6" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent); background-color: color-mix(in srgb, var(--theme-surface-soft) 88%, transparent);">
                        <x-ui.button type="button" variant="outline" x-on:click="tagDialogOpen = false">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="submit"><i class="fa-light fa-floppy-disk"></i>{{ $editingId ? __('Save tag') : __('Create tag') }}</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
