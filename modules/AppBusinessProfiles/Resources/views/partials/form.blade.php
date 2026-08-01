<div
    class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_23rem]"
    x-data="{
        form: {
            name: @js($name),
            type: @js($type),
            phone: @js($phone),
            email: @js($email),
            website: @js($website),
            address: @js($address),
            google_maps_url: @js($google_maps_url),
        },
        taxonomy: @js($industryTaxonomy),
        goalLabels: @js(\Modules\AppBusinessProfiles\Support\BusinessTypeCatalog::campaignGoalLabels()),
        signalLabels: @js(\Modules\AppBusinessProfiles\Support\BusinessTypeCatalog::alternativeDataSignalLabels()),
        showAll: false,
        industrySearch: '',
        selectedGroup: @js($industry_group_code),
        selectedCategory: @js($industry_category_code),
        activePickerGroup: @js($industry_group_code ?: ''),

        init() {
            if (this.selectedGroup) {
                this.activePickerGroup = this.selectedGroup;
            }
        },

        filled(value) {
            return String(value || '').trim() !== '';
        },

        fold(value) {
            return String(value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd')
                .replace(/Đ/g, 'd')
                .toLowerCase()
                .trim();
        },

        pretty(code) {
            return String(code || '').replace(/_/g, ' ');
        },

        goalLabel(code) {
            return this.goalLabels[code] || this.pretty(code);
        },

        signalLabel(code) {
            return this.signalLabels[code] || this.pretty(code);
        },

        get completionPercent() {
            let completed = 0;
            if (this.filled(this.form.name)) completed++;
            if (this.filled(this.form.phone) || this.filled(this.form.email)) completed++;
            if (this.filled(this.form.website)) completed++;
            if (this.filled(this.form.address)) completed++;
            if (this.filled(this.form.google_maps_url)) completed++;

            return Math.round((completed / 5) * 100);
        },

        initials() {
            const value = this.filled(this.form.name) ? this.form.name : 'LB';
            return String(value).trim().slice(0, 2).toUpperCase();
        },

        get visibleGroups() {
            if (this.showAll) {
                return this.taxonomy;
            }

            return this.featuredGroups;
        },

        get featuredGroups() {
            return this.taxonomy
                .filter((group) => group.is_priority)
                .sort((a, b) => (a.priority_order ?? 99) - (b.priority_order ?? 99))
                .slice(0, 8);
        },

        get activePickerGroupData() {
            return this.activePickerGroup ? this.groupByCode(this.activePickerGroup) : null;
        },

        get activePickerGroupLabel() {
            return this.activePickerGroupData?.label || '';
        },

        get activePickerCategories() {
            return this.activePickerGroupData?.categories || [];
        },

        get isSearchingIndustry() {
            return this.fold(this.industrySearch) !== '';
        },

        get industryResults() {
            const q = this.fold(this.industrySearch);
            if (! q) {
                return [];
            }

            const out = [];
            for (const group of this.taxonomy) {
                for (const category of group.categories) {
                    const haystack = this.fold(category.label) + ' '
                        + this.fold(category.aliases) + ' '
                        + this.fold(category.code) + ' '
                        + this.fold(group.label);

                    if (haystack.includes(q)) {
                        out.push({ ...category, group_code: group.code, group_label: group.label });
                    }
                }
            }

            return out.slice(0, 30);
        },

        groupByCode(code) {
            return this.taxonomy.find((group) => group.code === code) || null;
        },

        categoryByCode(code) {
            for (const group of this.taxonomy) {
                const category = group.categories.find((item) => item.code === code);
                if (category) {
                    return { ...category, group_code: group.code, group_label: group.label };
                }
            }
            return null;
        },

        get selectedCategoryData() {
            return this.selectedCategory ? this.categoryByCode(this.selectedCategory) : null;
        },

        get selectedGroupLabel() {
            const group = this.groupByCode(this.selectedGroup);
            return group ? group.label : '';
        },

        get selectedCategoryLabel() {
            const category = this.selectedCategoryData;
            return category ? category.label : '';
        },

        get complianceSensitive() {
            const category = this.selectedCategoryData;
            return category ? !! category.compliance_sensitive : false;
        },

        get recommendedGoals() {
            const group = this.groupByCode(this.selectedGroup);
            return group ? (group.default_campaign_goals || []) : [];
        },

        get recommendedSignals() {
            const group = this.groupByCode(this.selectedGroup);
            return group ? (group.signals || []) : [];
        },

        selectGroup(groupCode) {
            this.activePickerGroup = this.activePickerGroup === groupCode ? '' : groupCode;
        },

        chooseIndustry(groupCode, categoryCode) {
            this.selectedGroup = groupCode;
            this.selectedCategory = categoryCode;
            this.activePickerGroup = groupCode;
            this.industrySearch = '';

            $wire.set('industry_group_code', groupCode, false);
            $wire.set('industry_category_code', categoryCode, false);

            const category = this.categoryByCode(categoryCode);
            this.form.type = category ? category.legacy_type : '';
        },
    }"
>
    <form id="business-profile-form" wire:submit="save" class="space-y-5">
        <section class="overflow-visible rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
            <div class="rounded-t-[1.25rem] flex flex-col gap-4 border-b px-5 py-5 md:flex-row md:items-center md:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.08), transparent 40%);">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-store"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Business identity') }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('The public-facing name and category used across every QR experience.') }}</p>
                    </div>
                </div>
                <x-ui.badge variant="primary">{{ __('Step 1') }}</x-ui.badge>
            </div>
            <div class="space-y-5 p-5">
                <x-ui.input x-model="form.name" wire:model="name" name="name" :label="__('Business name')" :error="$errors->first('name')" />

                {{-- Industry picker: priority groups → view all → expandable sub-industries + search --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium" style="color: var(--theme-header-text-color);">
                            {{ __('Main business industry') }}
                        </label>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">
                            {{ __('Choose the industry that best matches your main activity. MKT will suggest the right templates, campaigns, and reports for you.') }}
                        </p>
                        @error('industry_category_code')
                            <p class="mt-1 text-xs font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Search --}}
                    <div class="relative">
                        <i class="fa-light fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm" style="color: var(--theme-muted-text-color);"></i>
                        <input
                            x-model="industrySearch"
                            x-on:keydown.escape.prevent="industrySearch = ''"
                            x-on:keydown.enter.prevent="industryResults.length ? chooseIndustry(industryResults[0].group_code, industryResults[0].code) : null"
                            class="h-11 w-full rounded-xl border pl-10 pr-4 text-sm outline-none transition focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
                            style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                            placeholder="{{ __('Search industry...') }}"
                            autocomplete="off"
                        >
                    </div>

                    {{-- Search results --}}
                    <div x-show="isSearchingIndustry" x-cloak class="overflow-hidden rounded-2xl border" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background-color: color-mix(in srgb, var(--theme-surface-overlay) 99%, transparent);">
                        <template x-if="industryResults.length">
                            <div class="max-h-80 overflow-y-auto p-2">
                                <template x-for="item in industryResults" :key="item.group_code + '::' + item.code">
                                    <button
                                        type="button"
                                        class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.08)]"
                                        style="color: var(--theme-header-text-color);"
                                        x-on:click="chooseIndustry(item.group_code, item.code)"
                                    >
                                        <span>
                                            <span x-text="item.label"></span>
                                            <span class="ml-1 text-[11px]" style="color: var(--theme-muted-text-color);">(<span x-text="item.group_label"></span>)</span>
                                        </span>
                                        <i class="fa-light fa-check text-xs shrink-0" style="color: var(--theme-accent);" x-show="selectedCategory === item.code"></i>
                                    </button>
                                </template>
                            </div>
                        </template>
                        <template x-if="! industryResults.length">
                            <div class="px-4 py-5 text-center text-sm" style="color: var(--theme-muted-text-color);">
                                <i class="fa-light fa-circle-question mb-2 block text-2xl" style="color: var(--theme-accent);"></i>
                                {{ __('No results found') }}
                                <p class="mt-1 text-xs">{{ __('If unsure, select Other. MKT can help you classify later.') }}</p>
                            </div>
                        </template>
                    </div>

                    {{-- Industry groups: icon grid + sub-categories panel --}}
                    <div x-show="! isSearchingIndustry" x-cloak class="space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">
                                    <span x-show="! showAll">{{ __('Popular industries') }}</span>
                                    <span x-show="showAll" x-cloak>{{ __('Main industry group') }}</span>
                                </p>
                                <p x-show="! showAll" class="mt-1 text-[11px]" style="color: var(--theme-accent);">
                                    <i class="fa-light fa-location-dot mr-1"></i>{{ __('Da Nang - Quang Nam priority') }}
                                </p>
                            </div>
                            <button
                                type="button"
                                x-on:click="showAll = ! showAll; if (! showAll && activePickerGroup && ! featuredGroups.some((g) => g.code === activePickerGroup)) { activePickerGroup = ''; }"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-[11px] font-semibold transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.08)]"
                                style="border-color: rgba(var(--theme-accent-rgb),0.25); color: var(--theme-accent);"
                            >
                                <i class="fa-light text-[10px]" x-bind:class="showAll ? 'fa-chevron-up' : 'fa-layer-group'"></i>
                                <span x-show="! showAll">{{ __('View all industries') }}</span>
                                <span x-show="showAll" x-cloak>{{ __('Show popular industries') }}</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <template x-for="group in visibleGroups" :key="group.code">
                                <button
                                    type="button"
                                    x-on:click="selectGroup(group.code)"
                                    x-bind:class="activePickerGroup === group.code
                                        ? 'ring-2 ring-[color:var(--theme-accent)] border-[color:rgba(var(--theme-accent-rgb),0.45)] bg-[color:rgba(var(--theme-accent-rgb),0.08)]'
                                        : (selectedGroup === group.code && selectedCategory ? 'border-[color:rgba(var(--theme-accent-rgb),0.35)]' : '')"
                                    class="group relative flex flex-col items-center gap-2.5 rounded-2xl border px-2 py-4 text-center transition hover:border-[color:rgba(var(--theme-accent-rgb),0.4)] hover:bg-[color:rgba(var(--theme-accent-rgb),0.05)]"
                                    style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"
                                >
                                    <span
                                        class="relative flex h-14 w-14 items-center justify-center rounded-2xl transition group-hover:scale-[1.03]"
                                        style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"
                                        x-bind:style="activePickerGroup === group.code ? 'background-color: rgba(var(--theme-accent-rgb),0.18); color: var(--theme-accent);' : ''"
                                    >
                                        <i class="fa-light text-[1.65rem]" x-bind:class="group.icon"></i>
                                        <span
                                            x-show="group.compliance_sensitive"
                                            x-cloak
                                            class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full border"
                                            style="border-color: rgba(var(--theme-warning-color-rgb),0.45); background-color: rgba(var(--theme-warning-color-rgb),0.12); color: var(--theme-warning-color);"
                                        >
                                            <i class="fa-light fa-shield-halved text-[9px]"></i>
                                        </span>
                                        <span
                                            x-show="selectedGroup === group.code && selectedCategory"
                                            x-cloak
                                            class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full"
                                            style="background-color: var(--theme-accent); color: white;"
                                        >
                                            <i class="fa-light fa-check text-[9px]"></i>
                                        </span>
                                    </span>
                                    <span class="min-h-[2.5rem] px-1 text-[11px] font-semibold leading-snug line-clamp-2" x-text="group.label"></span>
                                </button>
                            </template>
                        </div>

                        {{-- Sub-categories for selected group --}}
                        <div
                            x-show="activePickerGroup && activePickerCategories.length"
                            x-collapse
                            x-cloak
                            class="overflow-hidden rounded-2xl border"
                            style="border-color: rgba(var(--theme-accent-rgb),0.22); background-color: color-mix(in srgb, var(--theme-surface-overlay) 96%, rgba(var(--theme-accent-rgb),0.04));"
                        >
                            <div class="border-b px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.42);">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Specific industry') }}</p>
                                <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);" x-text="activePickerGroupLabel"></p>
                            </div>
                            <div class="grid grid-cols-1 gap-2 p-3 sm:grid-cols-2 lg:grid-cols-3">
                                <template x-for="category in activePickerCategories" :key="category.code">
                                    <button
                                        type="button"
                                        x-on:click="chooseIndustry(activePickerGroup, category.code)"
                                        x-bind:class="selectedCategory === category.code ? 'ring-2 ring-[color:var(--theme-accent)] border-[color:rgba(var(--theme-accent-rgb),0.45)] bg-[color:rgba(var(--theme-accent-rgb),0.08)]' : ''"
                                        class="flex items-center gap-2 rounded-xl border px-3 py-2.5 text-left text-xs font-medium transition hover:border-[color:rgba(var(--theme-accent-rgb),0.4)] hover:bg-[color:rgba(var(--theme-accent-rgb),0.06)]"
                                        style="border-color: rgba(var(--theme-border-color-rgb),0.5); background-color: var(--theme-surface-base); color: var(--theme-header-text-color);"
                                    >
                                        <span class="leading-4" x-text="category.label"></span>
                                        <i class="fa-light fa-check ml-auto text-xs shrink-0" style="color: var(--theme-accent);" x-show="selectedCategory === category.code"></i>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Selected industry summary --}}
                    <div x-show="selectedCategory" x-cloak class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-accent-rgb),0.22); background-color: rgba(var(--theme-accent-rgb),0.06);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Selected industry') }}</p>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-semibold" style="border-color: rgba(var(--theme-accent-rgb),0.25); background-color: var(--theme-surface-overlay); color: var(--theme-accent);">
                                <i class="fa-light fa-check text-[10px]"></i>
                                <span x-text="selectedGroupLabel"></span>
                                <span class="opacity-60">·</span>
                                <span x-text="selectedCategoryLabel"></span>
                            </span>
                        </div>

                        <div x-show="complianceSensitive" x-cloak class="mt-3 flex items-start gap-2 rounded-xl border px-3 py-2 text-[11px] leading-5" style="border-color: rgba(var(--theme-warning-color-rgb),0.3); background-color: rgba(var(--theme-warning-color-rgb),0.08); color: var(--theme-muted-text-color);">
                            <i class="fa-light fa-shield-halved mt-0.5" style="color: var(--theme-warning-color);"></i>
                            <span>{{ __('MKT avoids medical or treatment claims for this industry.') }}</span>
                        </div>

                        <div class="mt-3 space-y-3" x-show="recommendedGoals.length || recommendedSignals.length">
                            <div x-show="recommendedGoals.length">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Recommended setup') }}</p>
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    <template x-for="goal in recommendedGoals" :key="goal">
                                        <span class="rounded-md border px-2 py-0.5 text-[10px]" style="border-color: rgba(var(--theme-border-color-rgb),0.5); color: var(--theme-header-text-color);" x-text="goalLabel(goal)"></span>
                                    </template>
                                </div>
                            </div>
                            <div x-show="recommendedSignals.length">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Alternative data signals') }}</p>
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    <template x-for="signal in recommendedSignals" :key="signal">
                                        <span class="rounded-md border px-2 py-0.5 text-[10px]" style="border-color: rgba(var(--theme-border-color-rgb),0.5); color: var(--theme-muted-text-color);" x-text="signalLabel(signal)"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-[11px] leading-4" style="color: var(--theme-muted-text-color);">
                        {{ __('If unsure, select Other. MKT can help you classify later.') }}
                    </p>
                </div>
            </div>
        </section>

        <section class="overflow-visible rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
            <div class="rounded-t-[1.25rem] flex flex-col gap-4 border-b px-5 py-5 md:flex-row md:items-center md:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-address-card"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Contact channels') }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Used for booking confirmations, lead follow-up, and customer-facing landing pages.') }}</p>
                    </div>
                </div>
                <x-ui.badge variant="success">{{ __('Recommended') }}</x-ui.badge>
            </div>
            <div class="grid gap-4 p-5 md:grid-cols-2">
                <x-ui.input x-model="form.phone" wire:model="phone" name="phone" :label="__('Phone')" :error="$errors->first('phone')" />
                <x-ui.input x-model="form.email" wire:model="email" name="email" :label="__('Email')" :error="$errors->first('email')" />
                <div class="md:col-span-2">
                    <x-ui.input x-model="form.website" wire:model="website" name="website" :label="__('Website')" placeholder="https://example.com" :error="$errors->first('website')" />
                </div>
            </div>
        </section>

        <section class="overflow-visible rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
            <div class="rounded-t-[1.25rem] flex flex-col gap-4 border-b px-5 py-5 md:flex-row md:items-center md:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-clock"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Business opening hours') }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Booking services use these hours by default so customers only see valid appointment slots.') }}</p>
                    </div>
                </div>
                <x-ui.badge variant="primary">{{ __('Booking ready') }}</x-ui.badge>
            </div>
            <div class="grid gap-3 p-5">
                @foreach ([
                    'mon' => __('Monday'),
                    'tue' => __('Tuesday'),
                    'wed' => __('Wednesday'),
                    'thu' => __('Thursday'),
                    'fri' => __('Friday'),
                    'sat' => __('Saturday'),
                    'sun' => __('Sunday'),
                ] as $day => $label)
                    <div
                        x-data="{ closed: @entangle('opening_hours.'.$day.'.is_closed').live }"
                        class="grid gap-3 rounded-2xl border p-3 transition md:grid-cols-[1fr_9rem_9rem]"
                        x-bind:style="closed
                            ? 'border-color: rgba(var(--theme-border-color-rgb),0.48); background-color: color-mix(in srgb, var(--theme-surface-soft) 88%, transparent);'
                            : 'border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);'"
                    >
                        <div class="flex flex-col gap-2 sm:justify-center">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $label }}</p>
                                <span
                                    x-show="closed"
                                    x-cloak
                                    class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.12em]"
                                    style="border-color: rgba(var(--theme-border-color-rgb), .58); color: var(--theme-muted-text-color); background-color: var(--theme-surface-overlay);"
                                >{{ __('Closed') }}</span>
                            </div>
                            <x-ui.checkbox wire:model.live="opening_hours.{{ $day }}.is_closed" :label="__('Closed all day')" minimal />
                        </div>
                        <div x-bind:class="closed ? 'pointer-events-none opacity-45' : ''">
                            <x-ui.time-picker
                                wire:model="opening_hours.{{ $day }}.open_time"
                                name="opening_hours_{{ $day }}_open_time"
                                :label="__('Open')"
                                :value="$opening_hours[$day]['open_time'] ?? '09:00'"
                                picker-align="auto"
                                picker-position="auto"
                                :error="$errors->first('opening_hours.'.$day.'.open_time')"
                            />
                        </div>
                        <div x-bind:class="closed ? 'pointer-events-none opacity-45' : ''">
                            <x-ui.time-picker
                                wire:model="opening_hours.{{ $day }}.close_time"
                                name="opening_hours_{{ $day }}_close_time"
                                :label="__('Close')"
                                :value="$opening_hours[$day]['close_time'] ?? '18:00'"
                                picker-align="auto"
                                picker-position="auto"
                                :error="$errors->first('opening_hours.'.$day.'.close_time')"
                            />
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="overflow-visible rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
            <div class="rounded-t-[1.25rem] flex flex-col gap-4 border-b px-5 py-5 md:flex-row md:items-center md:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-warning-color-rgb),0.14); color: var(--theme-warning-color);">
                        <i class="fa-light fa-location-dot"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Primary address') }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Use this for the main business profile, review pages, booking pages, QR pages, and local SEO.') }}</p>
                    </div>
                </div>
                <x-ui.badge>{{ __('Local SEO') }}</x-ui.badge>
            </div>
            <div class="space-y-4 p-5">
                <div class="rounded-2xl border px-4 py-3 text-sm leading-6" style="border-color: rgba(var(--theme-accent-rgb),0.16); background-color: rgba(var(--theme-accent-rgb),0.07); color: var(--theme-muted-text-color);">
                    <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Note') }}:</span>
                    {{ __('This is the primary address for the business. If the business has multiple branches or service areas, add them separately in Locations.') }}
                </div>
                <x-ui.textarea x-model="form.address" wire:model="address" name="address" :label="__('Primary business address')" rows="4" :error="$errors->first('address')">{{ $address }}</x-ui.textarea>
                <x-ui.input x-model="form.google_maps_url" wire:model="google_maps_url" name="google_maps_url" :label="__('Primary Google Maps URL')" placeholder="https://maps.google.com/..." :error="$errors->first('google_maps_url')" />
            </div>
        </section>
    </form>

    <aside class="space-y-4 xl:sticky xl:top-4 xl:self-start">
        <section class="overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background: linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.10), transparent 44%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
            <div class="p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Launch readiness') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Profile completion') }}</p>
                    </div>
                    <div class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);" x-text="completionPercent + '%'"></div>
                </div>
                <div class="mt-4 h-2 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb),0.35);">
                    <div class="h-full rounded-full transition-all duration-300" x-bind:style="'width: ' + completionPercent + '%; background-color: var(--theme-accent);'"></div>
                </div>
                <div class="mt-5 space-y-3">
                    @foreach ([
                        ['done' => 'filled(form.name)', 'label' => __('Business name')],
                        ['done' => 'filled(form.phone) || filled(form.email)', 'label' => __('Phone or email')],
                        ['done' => 'filled(form.website)', 'label' => __('Website')],
                        ['done' => 'filled(form.address)', 'label' => __('Address')],
                        ['done' => 'filled(form.google_maps_url)', 'label' => __('Google Maps URL')],
                    ] as $item)
                        <div class="flex items-center gap-3 text-sm">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg" x-bind:style="{{ $item['done'] }} ? 'background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);' : 'background-color: rgba(var(--theme-border-color-rgb),0.18); color: var(--theme-muted-text-color);'">
                                <i class="fa-light text-xs" x-bind:class="{{ $item['done'] }} ? 'fa-check' : 'fa-minus'"></i>
                            </span>
                            <span style="color: var(--theme-header-text-color);">{{ $item['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Public preview') }}</p>
            <div class="mt-4 rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background-color: color-mix(in srgb, var(--theme-surface-base) 92%, transparent);">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl border text-sm font-semibold uppercase" style="border-color: rgba(var(--theme-accent-rgb),0.18); background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                        <span x-text="initials()"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);" x-text="filled(form.name) ? form.name : @js(__('Business name'))"></p>
                        <p class="mt-1 text-xs uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);" x-text="selectedCategoryLabel || @js(__('Business type'))"></p>
                    </div>
                </div>
                <div class="mt-4 space-y-2 text-xs" style="color: var(--theme-muted-text-color);">
                    <p class="truncate"><i class="fa-light fa-phone mr-2"></i><span x-text="filled(form.phone) ? form.phone : @js(__('Phone not set'))"></span></p>
                    <p class="truncate"><i class="fa-light fa-globe mr-2"></i><span x-text="filled(form.website) ? form.website : @js(__('Website not set'))"></span></p>
                    <p class="line-clamp-2"><i class="fa-light fa-location-dot mr-2"></i><span x-text="filled(form.address) ? form.address : @js(__('Address not set'))"></span></p>
                </div>
            </div>
        </section>

        <section class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('What this powers') }}</p>
            <div class="mt-4 space-y-3 text-sm" style="color: var(--theme-muted-text-color);">
                <p class="flex items-center gap-3"><i class="fa-light fa-star w-5" style="color: var(--theme-accent);"></i>{{ __('Review booster public pages') }}</p>
                <p class="flex items-center gap-3"><i class="fa-light fa-calendar-check w-5" style="color: var(--theme-accent);"></i>{{ __('Booking and lead forms') }}</p>
                <p class="flex items-center gap-3"><i class="fa-light fa-qrcode w-5" style="color: var(--theme-accent);"></i>{{ __('Trackable QR campaigns') }}</p>
            </div>
        </section>

        <div class="flex flex-col gap-3">
            <x-ui.button type="submit" form="business-profile-form" size="lg" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save" class="inline-flex items-center gap-2">
                    <i class="fa-light fa-floppy-disk"></i>{{ __('Save business') }}
                </span>
                <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                    <i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Saving...') }}
                </span>
            </x-ui.button>
            <x-ui.button href="{{ route('portal.businesses') }}" wire:navigate variant="outline" size="lg">
                <i class="fa-light fa-arrow-left"></i>{{ __('Back to businesses') }}
            </x-ui.button>
        </div>
    </aside>
</div>
