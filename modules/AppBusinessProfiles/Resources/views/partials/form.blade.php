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
        typeLabels: @js($typeOptions),
        filled(value) {
            return String(value || '').trim() !== '';
        },
        typeLabel(type) {
            return this.typeLabels[type] || type || '';
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

                {{-- Two-step industry picker: main group → specific sub-industry --}}
                <div
                    x-data="{
                        selectedType: @js($type),
                        selectedGroup: null,
                        search: '',
                        typeLabels: @js($typeOptions),
                        groupedOptions: @js($groupedTypeOptions),
                        aliasMap: @js(\Modules\AppBusinessProfiles\Support\BusinessTypeCatalog::searchAliases()),

                        init() {
                            this.syncGroupFromType(this.selectedType);
                        },

                        syncGroupFromType(type) {
                            if (! type) {
                                return;
                            }

                            for (const group of this.groupedOptions) {
                                const options = Object.values(group.options || {});
                                if (options.some((item) => item.type === type)) {
                                    this.selectedGroup = group.group;
                                    return;
                                }
                            }
                        },

                        groupForType(type) {
                            for (const group of this.groupedOptions) {
                                const options = Object.values(group.options || {});
                                if (options.some((item) => item.type === type)) {
                                    return group.group;
                                }
                            }

                            return null;
                        },

                        groupLabel(groupCode) {
                            const group = this.groupedOptions.find((item) => item.group === groupCode);

                            return group?.label || '';
                        },

                        get selectedLabel() {
                            return this.typeLabels[this.selectedType] || this.selectedType || '';
                        },

                        get activeGroup() {
                            if (! this.selectedGroup) {
                                return null;
                            }

                            return this.groupedOptions.find((group) => group.group === this.selectedGroup) || null;
                        },

                        get activeGroupLabel() {
                            return this.activeGroup?.label || '';
                        },

                        get subOptions() {
                            if (! this.activeGroup) {
                                return [];
                            }

                            return Object.values(this.activeGroup.options || {});
                        },

                        get isSearching() {
                            return String(this.search || '').trim() !== '';
                        },

                        get searchResults() {
                            const q = String(this.search || '').toLowerCase().trim();
                            if (! q) {
                                return [];
                            }

                            const results = [];
                            const seen = new Set();

                            for (const [type, label] of Object.entries(this.typeLabels)) {
                                if (label.toLowerCase().includes(q) || type.toLowerCase().includes(q)) {
                                    if (! seen.has(type)) {
                                        seen.add(type);
                                        results.push({ type, label, group: this.groupForType(type) });
                                    }
                                }
                            }

                            for (const [alias, type] of Object.entries(this.aliasMap)) {
                                if (alias.toLowerCase().includes(q) && ! seen.has(type)) {
                                    seen.add(type);
                                    results.push({
                                        type,
                                        label: this.typeLabels[type] || type,
                                        group: this.groupForType(type),
                                    });
                                }
                            }

                            return results.slice(0, 12);
                        },

                        selectGroup(groupCode) {
                            this.selectedGroup = groupCode;
                            this.search = '';

                            const stillValid = this.subOptions.some((item) => item.type === this.selectedType);
                            if (! stillValid) {
                                this.selectedType = '';
                                form.type = '';
                                $wire.set('type', '', false);
                            }
                        },

                        choose(type) {
                            this.selectedType = type;
                            this.search = '';
                            this.syncGroupFromType(type);
                            form.type = type;
                            $wire.set('type', type, false);
                        },
                    }"
                >
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium" style="color: var(--theme-header-text-color);">
                                {{ __('Main business industry') }}
                            </label>
                            <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">
                                {{ __('Choose the industry that best matches your main activity. MLHUB will suggest the right templates, campaigns, and reports for you.') }}
                            </p>
                            @error('type')
                                <p class="mt-1 text-xs font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Step 1: main industry group --}}
                        <div>
                            <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Main industry group') }}</p>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                <template x-for="group in groupedOptions" :key="group.group">
                                    <button
                                        type="button"
                                        x-on:click="selectGroup(group.group)"
                                        x-bind:class="selectedGroup === group.group ? 'ring-2 ring-[color:var(--theme-accent)] border-[color:rgba(var(--theme-accent-rgb),0.45)] bg-[color:rgba(var(--theme-accent-rgb),0.08)]' : ''"
                                        class="flex items-center gap-2 rounded-xl border px-3 py-2.5 text-left text-xs font-medium transition hover:border-[color:rgba(var(--theme-accent-rgb),0.4)] hover:bg-[color:rgba(var(--theme-accent-rgb),0.06)]"
                                        style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"
                                    >
                                        <i class="fa-light w-4 shrink-0" x-bind:class="group.icon" style="color: var(--theme-accent);"></i>
                                        <span class="leading-4" x-text="group.label"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Step 2: specific sub-industry within selected group --}}
                        <div x-show="selectedGroup && ! isSearching" x-cloak>
                            <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">
                                {{ __('Specific industry') }}
                                <span class="normal-case tracking-normal font-normal" style="color: var(--theme-muted-text-color);" x-show="activeGroupLabel">
                                    — <span x-text="activeGroupLabel"></span>
                                </span>
                            </p>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                <template x-for="item in subOptions" :key="item.type">
                                    <button
                                        type="button"
                                        x-on:click="choose(item.type)"
                                        x-bind:class="selectedType === item.type ? 'ring-2 ring-[color:var(--theme-accent)] border-[color:rgba(var(--theme-accent-rgb),0.45)] bg-[color:rgba(var(--theme-accent-rgb),0.08)]' : ''"
                                        class="flex items-center gap-2 rounded-xl border px-3 py-2.5 text-left text-xs font-medium transition hover:border-[color:rgba(var(--theme-accent-rgb),0.4)] hover:bg-[color:rgba(var(--theme-accent-rgb),0.06)]"
                                        style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"
                                    >
                                        <i class="fa-light w-4 shrink-0 text-sm" x-bind:class="item.icon" style="color: var(--theme-accent);"></i>
                                        <span class="leading-4" x-text="item.label"></span>
                                        <i class="fa-light fa-check ml-auto text-xs shrink-0" style="color: var(--theme-accent);" x-show="selectedType === item.type"></i>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div x-show="! selectedGroup && ! isSearching" x-cloak class="rounded-xl border px-4 py-3 text-xs leading-5" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: color-mix(in srgb, var(--theme-surface-soft) 92%, transparent); color: var(--theme-muted-text-color);">
                            {{ __('Select a main industry group first, then choose the specific industry below.') }}
                        </div>

                        {{-- Selected industry summary --}}
                        <div x-show="selectedType" x-cloak>
                            <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Selected industry') }}</p>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-semibold" style="border-color: rgba(var(--theme-accent-rgb),0.25); background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                                    <i class="fa-light fa-check text-[10px]"></i>
                                    <span x-text="activeGroupLabel" x-show="activeGroupLabel"></span>
                                    <span x-show="activeGroupLabel && selectedLabel" class="opacity-60">·</span>
                                    <span x-text="selectedLabel"></span>
                                </span>
                            </div>
                        </div>

                        {{-- Optional global search shortcut --}}
                        <div>
                            <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Or search industry') }}</p>
                            <div class="relative">
                                <i class="fa-light fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm" style="color: var(--theme-muted-text-color);"></i>
                                <input
                                    x-model="search"
                                    x-on:keydown.escape.prevent="search = ''"
                                    x-on:keydown.enter.prevent="searchResults.length ? choose(searchResults[0].type) : null"
                                    class="h-11 w-full rounded-xl border pl-10 pr-4 text-sm outline-none transition focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
                                    style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                                    placeholder="{{ __('Search industry...') }}"
                                    autocomplete="off"
                                >
                            </div>

                            <div x-show="isSearching" x-cloak class="mt-2 overflow-hidden rounded-2xl border" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background-color: color-mix(in srgb, var(--theme-surface-overlay) 99%, transparent);">
                                <template x-if="searchResults.length">
                                    <div class="max-h-72 overflow-y-auto p-2">
                                        <template x-for="item in searchResults" :key="item.type">
                                            <button
                                                type="button"
                                                class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.08)]"
                                                style="color: var(--theme-header-text-color);"
                                                x-on:click="choose(item.type)"
                                            >
                                                <span>
                                                    <span x-text="item.label"></span>
                                                    <span class="ml-1 text-[11px]" style="color: var(--theme-muted-text-color);" x-show="item.group">
                                                        (<span x-text="groupLabel(item.group)"></span>)
                                                    </span>
                                                </span>
                                                <i class="fa-light fa-check text-xs shrink-0" style="color: var(--theme-accent);" x-show="selectedType === item.type"></i>
                                            </button>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="! searchResults.length">
                                    <div class="px-4 py-5 text-center text-sm" style="color: var(--theme-muted-text-color);">
                                        <i class="fa-light fa-circle-question mb-2 block text-2xl" style="color: var(--theme-accent);"></i>
                                        {{ __('No results found') }}
                                        <p class="mt-1 text-xs">{{ __('If unsure, select Other. MLHUB can help you classify later.') }}</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <p class="text-[11px] leading-4" style="color: var(--theme-muted-text-color);">
                            {{ __('If unsure, select Other. MLHUB can help you classify later.') }}
                        </p>
                    </div>
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
                        <p class="mt-1 text-xs uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);" x-text="typeLabel(form.type) || @js(__('Business type'))"></p>
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
