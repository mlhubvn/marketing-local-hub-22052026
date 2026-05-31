@props([
    'label' => null,
    'help' => null,
    'error' => null,
    'options' => [],
    'selected' => null,
    'model' => null,
    'name' => null,
    'placeholder' => null,
    'searchPlaceholder' => null,
    'emptyText' => null,
    'icon' => null,
    'live' => true,
])

@php
    $normalizedOptions = collect($options)
        ->map(fn ($option) => [
            'value' => (string) data_get($option, 'value', ''),
            'label' => (string) data_get($option, 'label', ''),
            'meta' => data_get($option, 'meta'),
            'icon' => data_get($option, 'icon'),
        ])
        ->values()
        ->all();
    $comboboxKey = 'combobox-'.md5(($name ?: $model ?: 'field').'|'.(string) $selected.'|'.json_encode($normalizedOptions));
@endphp

<x-ui.field :label="$label" :help="$help" :error="$error" {{ $attributes->only('class') }}>
    <div
        wire:key="{{ $comboboxKey }}"
        class="relative"
        x-data="{
            open: false,
            search: '',
            selected: @js((string) $selected),
            options: @js($normalizedOptions),
            panelStyle: '',
            get selectedOption() {
                return this.options.find((option) => String(option.value) === String(this.selected));
            },
            get selectedLabel() {
                return this.selectedOption?.label || @js($placeholder ?: __('Select option'));
            },
            get filtered() {
                const query = String(this.search || '').toLowerCase().trim();

                if (! query) {
                    return this.options;
                }

                return this.options.filter((option) => {
                    return String(option.label || '').toLowerCase().includes(query)
                        || String(option.meta || '').toLowerCase().includes(query);
                });
            },
            choose(option) {
                this.selected = String(option.value);
                this.open = false;
                this.search = '';

                @if ($model)
                    $wire.set(@js($model), option.value, @js((bool) $live));
                @endif
            },
            toggleOpen() {
                this.open = ! this.open;

                if (this.open) {
                    this.$nextTick(() => this.updatePlacement());
                }
            },
            updatePlacement() {
                const trigger = this.$refs.trigger;

                if (! trigger) {
                    return;
                }

                const gap = 8;
                const rect = trigger.getBoundingClientRect();
                const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
                const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
                const width = Math.min(rect.width, viewportWidth - 24);
                const left = Math.max(12, Math.min(rect.left, viewportWidth - width - 12));
                const maxHeight = Math.max(220, Math.min(360, viewportHeight - rect.bottom - 24));
                const top = rect.bottom + gap;

                this.panelStyle = `position: fixed; top: ${top}px; left: ${left}px; width: ${width}px; max-height: ${maxHeight}px; border-color: rgba(var(--theme-border-color-rgb),0.72); background-color: color-mix(in srgb, var(--theme-surface-overlay) 99%, transparent);`;
            },
        }"
        x-on:resize.window="if (open) updatePlacement()"
        x-on:scroll.window="if (open) updatePlacement()"
        x-on:click.outside="open = false"
    >
        @if ($name)
            <input type="hidden" name="{{ $name }}" x-bind:value="selected">
        @endif

        <button
            x-ref="trigger"
            type="button"
            class="flex h-11 w-full items-center gap-3 rounded-xl border px-3 text-left text-sm font-medium shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition duration-200 focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
            style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
            x-on:click="toggleOpen()"
            x-bind:aria-expanded="open.toString()"
        >
            @if ($icon)
                <i class="{{ $icon }} shrink-0 text-sm" style="color: var(--theme-muted-text-color);"></i>
            @endif
            <span class="min-w-0 flex-1 truncate" x-text="selectedLabel"></span>
            <i class="fa-light fa-chevron-down shrink-0 text-xs transition" style="color: var(--theme-muted-text-color);" x-bind:class="open ? 'rotate-180' : ''"></i>
        </button>

        <div
            x-cloak
            x-show="open"
            x-transition:enter="transition ease-out duration-120"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="z-[9999] overflow-hidden rounded-2xl border shadow-[0_24px_70px_-38px_rgba(var(--theme-border-color-rgb),0.95)]"
            x-bind:style="panelStyle"
        >
            <div class="border-b p-2" style="border-color: rgba(var(--theme-border-color-rgb),0.56);">
                <div class="relative">
                    <i class="fa-light fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color: var(--theme-muted-text-color);"></i>
                    <input
                        type="search"
                        x-model="search"
                        class="h-9 w-full rounded-xl border pl-9 pr-3 text-sm outline-none transition focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
                        style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                        placeholder="{{ $searchPlaceholder ?: __('Search...') }}"
                        x-on:keydown.escape.prevent="open = false"
                        x-on:keydown.enter.prevent="filtered.length ? choose(filtered[0]) : null"
                    >
                </div>
            </div>

            <div class="overflow-y-auto p-2" style="max-height: min(18rem, calc(100vh - 12rem));">
                <template x-if="filtered.length">
                    <div class="space-y-1">
                        <template x-for="option in filtered" :key="option.value">
                            <button
                                type="button"
                                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.08)]"
                                style="color: var(--theme-header-text-color);"
                                x-on:click="choose(option)"
                            >
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                                    <i class="fa-light text-xs" x-bind:class="option.icon || @js($icon ?: 'fa-layer-group')"></i>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate" x-text="option.label"></span>
                                    <span class="mt-0.5 block truncate text-xs font-normal" style="color: var(--theme-muted-text-color);" x-show="option.meta" x-text="option.meta"></span>
                                </span>
                                <i class="fa-light fa-check text-xs" style="color: var(--theme-accent);" x-show="String(selected) === String(option.value)"></i>
                            </button>
                        </template>
                    </div>
                </template>
                <template x-if="! filtered.length">
                    <div class="px-3 py-4 text-sm" style="color: var(--theme-muted-text-color);">
                        {{ $emptyText ?: __('No options found') }}
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-ui.field>
