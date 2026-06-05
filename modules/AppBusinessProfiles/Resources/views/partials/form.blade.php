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
        filled(value) {
            return String(value || '').trim() !== '';
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
            <div class="grid gap-4 p-5 md:grid-cols-[minmax(0,1fr)_20rem]">
                <x-ui.input x-model="form.name" wire:model="name" name="name" :label="__('Business name')" :error="$errors->first('name')" />
                <x-ui.field :label="__('Business type')" :error="$errors->first('type')" :help="__('Search an industry or type a custom value.')">
                    <div
                        class="relative"
                        x-data="{
                            open: false,
                            search: form.type,
                            options: @js(array_values($typeOptions)),
                            get filtered() {
                                const query = String(this.search || '').toLowerCase().trim();

                                if (! query) {
                                    return this.options.slice(0, 12);
                                }

                                const exact = this.options.find((option) => option.toLowerCase() === query);

                                if (exact) {
                                    return [
                                        exact,
                                        ...this.options.filter((option) => option !== exact),
                                    ].slice(0, 12);
                                }

                                return this.options
                                    .filter((option) => option.toLowerCase().includes(query))
                                    .slice(0, 12);
                            },
                            choose(option) {
                                this.search = option;
                                form.type = option;
                                $wire.set('type', option, false);
                                this.open = false;
                            },
                        }"
                        x-on:click.outside="open = false"
                    >
                        <div class="relative">
                            <i class="fa-light fa-briefcase pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm" style="color: var(--theme-muted-text-color);"></i>
                            <input
                                x-model="search"
                                wire:model="type"
                                x-on:focus="open = true"
                                x-on:input="form.type = search; $wire.set('type', search, false); open = true"
                                x-on:keydown.escape.prevent="open = false"
                                x-on:keydown.enter.prevent="filtered.length ? choose(filtered[0]) : open = false"
                                name="type"
                                class="h-11 w-full rounded-xl border pl-10 pr-10 text-sm outline-none transition focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
                                style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                                placeholder="{{ __('Search business type...') }}"
                                autocomplete="off"
                            >
                            <button
                                type="button"
                                class="absolute right-2 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg transition"
                                style="color: var(--theme-muted-text-color);"
                                x-on:click="open = ! open"
                            >
                                <i class="fa-light fa-chevron-down text-xs transition" x-bind:class="open ? 'rotate-180' : ''"></i>
                            </button>
                        </div>

                        <div
                            x-cloak
                            x-show="open"
                            x-transition:enter="transition ease-out duration-120"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="absolute z-50 mt-2 max-h-72 w-full overflow-hidden rounded-2xl border shadow-[0_24px_70px_-38px_rgba(var(--theme-border-color-rgb),0.95)]"
                            style="border-color: rgba(var(--theme-border-color-rgb),0.72); background-color: color-mix(in srgb, var(--theme-surface-overlay) 99%, transparent);"
                        >
                            <div class="border-b px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb),0.56);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Industries') }}</p>
                            </div>
                            <div class="max-h-60 overflow-y-auto p-2">
                                <template x-if="filtered.length">
                                    <div class="space-y-1">
                                        <template x-for="option in filtered" :key="option">
                                            <button
                                                type="button"
                                                class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.08)]"
                                                style="color: var(--theme-header-text-color);"
                                                x-on:click="choose(option)"
                                            >
                                                <span x-text="option"></span>
                                                <i class="fa-light fa-check text-xs" style="color: var(--theme-accent);" x-show="String(search || '').toLowerCase() === option.toLowerCase()"></i>
                                            </button>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="! filtered.length">
                                    <button
                                        type="button"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-sm font-medium"
                                        style="color: var(--theme-header-text-color); background-color: rgba(var(--theme-accent-rgb),0.08);"
                                        x-on:click="form.type = search; $wire.set('type', search, false); open = false"
                                    >
                                        <i class="fa-light fa-plus" style="color: var(--theme-accent);"></i>
                                        <span>{{ __('Use custom type') }}: <strong x-text="search"></strong></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </x-ui.field>
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
                        <div class="flex items-center justify-between gap-3">
                            <x-ui.checkbox wire:model.live="opening_hours.{{ $day }}.is_closed" :label="$label.' '.__('closed')" />
                            <span x-show="closed" class="rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]" style="border-color: rgba(var(--theme-border-color-rgb), .58); color: var(--theme-muted-text-color); background-color: var(--theme-surface-overlay);">{{ __('Closed') }}</span>
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
                        <p class="mt-1 text-xs uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);" x-text="form.type || @js(__('Business type'))"></p>
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
