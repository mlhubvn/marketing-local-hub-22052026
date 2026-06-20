@php
    $selectedIndustry = (string) ($filters['industry'] ?? '');
    $searchQuery = (string) ($filters['q'] ?? '');
    $priorityCodes = collect($industryPickerGroups)->where('is_priority', true)->pluck('code')->all();
    $selectedIsNonPriority = $selectedIndustry !== '' && ! in_array($selectedIndustry, $priorityCodes, true);
@endphp

<div
    x-data="{
        showAll: @js($selectedIsNonPriority),
        groups: @js($industryPickerGroups),
        selected: @js($selectedIndustry),
        searchQuery: @js($searchQuery),
        directoryUrl: @js(route('guest.directory')),
        get visibleGroups() {
            if (this.showAll) {
                return this.groups;
            }

            return this.groups.filter((group) => group.is_priority);
        },
        filterUrl(code) {
            const params = new URLSearchParams();
            const searchInput = document.getElementById('directory-search');
            const query = searchInput ? searchInput.value.trim() : this.searchQuery.trim();

            if (query !== '') {
                params.set('q', query);
            }

            if (this.selected !== code) {
                params.set('industry', code);
            }

            const queryString = params.toString();

            return queryString === '' ? this.directoryUrl : `${this.directoryUrl}?${queryString}`;
        },
    }"
    class="space-y-3 border-t pt-4"
    style="border-color: var(--lb-line);"
>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="text-[11px] font-black uppercase tracking-[0.14em]" style="color: var(--lb-muted);">
            <span x-show="! showAll">{{ __('Popular industries') }}</span>
            <span x-show="showAll" x-cloak>{{ __('Main industry group') }}</span>
        </p>
        <button
            type="button"
            x-on:click="showAll = ! showAll"
            class="inline-flex shrink-0 items-center gap-1 rounded-full border px-2.5 py-1 text-[10px] font-bold transition hover:bg-white"
            style="border-color: var(--lb-line); color: #ff5f5f;"
        >
            <i class="fa-light text-[9px]" x-bind:class="showAll ? 'fa-chevron-up' : 'fa-layer-group'"></i>
            <span x-show="! showAll">{{ __('View all industries') }}</span>
            <span x-show="showAll" x-cloak>{{ __('Show popular industries') }}</span>
        </button>
    </div>

    <div class="grid grid-cols-3 gap-2">
        <template x-for="group in visibleGroups" x-bind:key="group.code">
            <a
                x-bind:href="filterUrl(group.code)"
                x-bind:class="selected === group.code
                    ? 'ring-2 ring-[#ff5f5f] border-[color:rgba(255,95,95,0.45)] bg-[color:rgba(255,95,95,0.08)]'
                    : ''"
                class="group flex flex-col items-center gap-2 rounded-xl border px-1.5 py-3 text-center transition hover:border-[color:rgba(255,95,95,0.35)] hover:bg-[color:rgba(255,95,95,0.05)]"
                style="border-color: var(--lb-line); background: #fff; color: var(--lb-ink);"
                x-bind:title="group.label"
            >
                <span
                    class="relative flex h-11 w-11 items-center justify-center rounded-xl transition group-hover:scale-[1.03]"
                    style="background: color-mix(in srgb, #ff5f5f 10%, #fff); color: #ff5f5f;"
                    x-bind:style="selected === group.code ? 'background: color-mix(in srgb, #ff5f5f 18%, #fff); color: #ff5f5f;' : ''"
                >
                    <i class="fa-light text-lg" x-bind:class="group.icon"></i>
                    <span
                        x-show="selected === group.code"
                        x-cloak
                        class="absolute -bottom-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full text-white"
                        style="background: #ff5f5f;"
                    >
                        <i class="fa-light fa-check text-[8px]"></i>
                    </span>
                </span>
                <span class="min-h-[2rem] px-0.5 text-[10px] font-bold leading-snug line-clamp-2" x-text="group.label"></span>
            </a>
        </template>
    </div>

    @if ($selectedIndustry !== '')
        <a
            href="{{ route('guest.directory', array_filter(['q' => $searchQuery !== '' ? $searchQuery : null])) }}"
            class="inline-flex w-full items-center justify-center gap-1.5 rounded-full border px-3 py-2 text-[11px] font-bold transition hover:bg-white"
            style="border-color: var(--lb-line); color: var(--lb-muted);"
        >
            <i class="fa-light fa-xmark text-[10px]"></i>{{ __('All industries') }}
        </a>
    @endif
</div>
