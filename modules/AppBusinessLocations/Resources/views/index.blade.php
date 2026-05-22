<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6" x-data="{ qrDesignOpen: false }" x-on:open-location-qr-design.window="qrDesignOpen = true">
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif

    @php
        $selectedBusiness = $businesses->firstWhere('id', (int) $business_id);
        $isScoped = filled($scopedBusiness);
        $businessFilterOptions = [[
            'value' => 'all',
            'label' => __('All businesses'),
            'meta' => trans_choice('{0} No locations|{1} :count location|[2,*] :count locations', $totalLocations, ['count' => number_format($totalLocations)]),
            'icon' => 'fa-layer-group',
        ]];

        foreach ($businesses as $business) {
            $businessLocationCount = (int) ($locationCountsByBusiness[$business->id] ?? 0);
            $businessFilterOptions[] = [
                'value' => (string) $business->id,
                'label' => $business->name,
                'meta' => trans_choice('{0} No locations|{1} :count location|[2,*] :count locations', $businessLocationCount, ['count' => number_format($businessLocationCount)]),
                'icon' => 'fa-store',
            ];
        }

    @endphp

    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 34%),
        linear-gradient(35deg, rgba(var(--theme-success-color-rgb),0.08), transparent 38%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-7 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_23rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-location-dot"></i>
                    {{ $isScoped ? $scopedBusiness->name : __('Businesses') }}
                </div>
                <h1 class="mt-4 max-w-3xl text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ $isScoped ? __('Business locations') : __('Manage branches and service areas') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ $isScoped ? __('Manage extra branches, service areas, booths, or secondary addresses for this business.') : __('Attach addresses, contact channels, and Google Maps links to each business so QR campaigns feel local and trustworthy.') }}
                </p>
                @if ($isScoped)
                    <div class="mt-5">
                        <a href="{{ route('portal.businesses.show', $scopedBusiness) }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold" style="color: var(--theme-accent);">
                            <i class="fa-light fa-arrow-left"></i>{{ __('Back to business workspace') }}
                        </a>
                    </div>
                @endif
                <div class="mt-5 max-w-2xl rounded-2xl border px-4 py-3 text-sm leading-6" style="border-color: rgba(var(--theme-accent-rgb),0.16); background-color: rgba(var(--theme-accent-rgb),0.07); color: var(--theme-muted-text-color);">
                    <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('When to use Locations') }}:</span>
                    {{ __('Use this page for extra branches, service areas, booths, or secondary locations. The main address still lives in the Business Profile as the primary address.') }}
                </div>
                <div class="mt-6">
                    <x-ui.dialog :title="__('Add location')" :description="__('Create an extra branch, service area, booth, or store address. Keep the main address in Business Profile.')" width="xl" dismissible>
                        <x-slot:trigger>
                            <x-ui.button type="button" size="lg">
                                <i class="fa-light fa-plus"></i>{{ __('Add location') }}
                            </x-ui.button>
                        </x-slot:trigger>

                        <form
                            id="location-create-form"
                            wire:submit="save"
                            class="space-y-5"
                            x-data="{
                                form: {
                                    business_id: @js($business_id),
                                    name: @js($name),
                                    phone: @js($phone),
                                    email: @js($email),
                                    address: @js($address),
                                    google_maps_url: @js($google_maps_url),
                                },
                                businesses: @js($businesses->map(fn ($business) => ['id' => (string) $business->id, 'name' => $business->name])->values()),
                                selectedBusinessName() {
                                    return this.businesses.find((business) => business.id === String(this.form.business_id))?.name || @js(__('Select a business'));
                                },
                                valueOrPlaceholder(value, placeholder) {
                                    return String(value || '').trim() !== '' ? value : placeholder;
                                },
                            }"
                        >
                            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
                                <div class="space-y-5">
                                    <div class="grid gap-4 md:grid-cols-2">
                                        @if ($isScoped)
                                            <div class="space-y-2.5">
                                                <x-ui.label>{{ __('Business') }}</x-ui.label>
                                                <div class="flex h-11 items-center rounded-xl border px-4 text-sm font-semibold" style="border-color: var(--theme-border-color); background-color: color-mix(in srgb, var(--theme-surface-soft) 82%, transparent); color: var(--theme-header-text-color);">
                                                    <i class="fa-light fa-store mr-2" style="color: var(--theme-accent);"></i>{{ $scopedBusiness->name }}
                                                </div>
                                            </div>
                                        @else
                                            <x-ui.select x-model="form.business_id" wire:model="business_id" name="business_id" :label="__('Business')" :error="$errors->first('business_id')">
                                                <option value="">{{ __('Select business') }}</option>
                                                @foreach($businesses as $business)
                                                    <option value="{{ $business->id }}">{{ $business->name }}</option>
                                                @endforeach
                                            </x-ui.select>
                                        @endif
                                        <x-ui.input x-model="form.name" wire:model="name" name="name" :label="__('Location name')" :placeholder="__('Main branch, District 1, Weekend booth...')" :error="$errors->first('name')" />
                                        <x-ui.input x-model="form.phone" wire:model="phone" name="phone" :label="__('Phone')" :placeholder="__('Branch phone')" :error="$errors->first('phone')" />
                                        <x-ui.input x-model="form.email" wire:model="email" name="email" :label="__('Email')" :placeholder="__('branch@example.com')" :error="$errors->first('email')" />
                                    </div>

                                    <x-ui.textarea x-model="form.address" wire:model="address" name="address" :label="__('Address')" rows="4" :placeholder="__('Street, ward, district, city...')" :error="$errors->first('address')">{{ $address }}</x-ui.textarea>
                                    <x-ui.input x-model="form.google_maps_url" wire:model="google_maps_url" name="google_maps_url" :label="__('Google Maps URL')" :placeholder="__('https://maps.google.com/...')" :error="$errors->first('google_maps_url')" />
                                </div>

                                <aside class="rounded-2xl border p-4 lg:sticky lg:top-0" style="border-color: rgba(var(--theme-border-color-rgb), .55); background:
                                    linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.08), transparent 42%),
                                    color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Preview') }}</p>
                                    <div class="mt-3 rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .48); background-color: color-mix(in srgb, var(--theme-surface-overlay) 90%, transparent);">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-success-color-rgb),0.18); background-color: rgba(var(--theme-success-color-rgb),0.12); color: var(--theme-success-color);">
                                                <i class="fa-light fa-store"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);" x-text="valueOrPlaceholder(form.name, @js(__('Location name')))"></p>
                                                <p class="mt-1 truncate text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);" x-text="selectedBusinessName()"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid gap-2 text-xs" style="color: var(--theme-muted-text-color);">
                                        <div class="flex min-w-0 items-center gap-2 rounded-xl px-3 py-2" style="background-color: color-mix(in srgb, var(--theme-surface-soft) 78%, transparent);">
                                            <i class="fa-light fa-phone w-4 shrink-0 text-center"></i>
                                            <span class="truncate" x-text="valueOrPlaceholder(form.phone, @js(__('Phone not set')))"></span>
                                        </div>
                                        <div class="flex min-w-0 items-center gap-2 rounded-xl px-3 py-2" style="background-color: color-mix(in srgb, var(--theme-surface-soft) 78%, transparent);">
                                            <i class="fa-light fa-envelope w-4 shrink-0 text-center"></i>
                                            <span class="truncate" x-text="valueOrPlaceholder(form.email, @js(__('Email not set')))"></span>
                                        </div>
                                        <div class="flex min-w-0 items-center gap-2 rounded-xl px-3 py-2" style="background-color: color-mix(in srgb, var(--theme-surface-soft) 78%, transparent);">
                                            <i class="fa-light fa-location-dot w-4 shrink-0 text-center"></i>
                                            <span class="truncate" x-text="valueOrPlaceholder(form.address, @js(__('Address not set')))"></span>
                                        </div>
                                        <div class="flex min-w-0 items-center gap-2 rounded-xl px-3 py-2" style="background-color: color-mix(in srgb, var(--theme-surface-soft) 78%, transparent);">
                                            <i class="fa-light fa-map w-4 shrink-0 text-center"></i>
                                            <span class="truncate" x-text="valueOrPlaceholder(form.google_maps_url, @js(__('Google Maps URL not set')))"></span>
                                        </div>
                                    </div>
                                </aside>
                            </div>
                        </form>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-3">
                                <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                <x-ui.button type="submit" form="location-create-form" wire:loading.attr="disabled" wire:target="save">
                                    <span wire:loading.remove wire:target="save" class="inline-flex items-center gap-2">
                                        <i class="fa-light fa-floppy-disk"></i>{{ __('Save location') }}
                                    </span>
                                    <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                                        <i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Saving...') }}
                                    </span>
                                </x-ui.button>
                            </div>
                        </x-slot:footer>
                    </x-ui.dialog>
                </div>
            </div>

            <div class="rounded-[1.2rem] border p-4 shadow-[0_24px_70px_-48px_rgba(var(--theme-border-color-rgb),0.9)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Location coverage') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Branches ready for campaigns') }}</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-map-location-dot"></i>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ number_format($totalLocations) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Locations') }}</p>
                    </div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ number_format($isScoped ? 1 : $businesses->count()) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $isScoped ? __('Business') : __('Businesses') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-visible rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Location directory') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Search, filter, and manage high-volume branch data.') }}</p>
                </div>
                <div class="grid gap-3 {{ $isScoped ? 'md:grid-cols-[minmax(0,18rem)_8rem]' : 'md:grid-cols-[minmax(0,18rem)_12rem_8rem]' }}">
                    <div class="relative">
                        <i class="fa-light fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm" style="color: var(--theme-muted-text-color);"></i>
                        <input type="search" wire:model.live.debounce.300ms="search" class="h-11 w-full rounded-xl border pl-10 pr-4 text-sm outline-none transition focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Search name, address, phone...') }}">
                    </div>
                    @unless ($isScoped)
                        <x-ui.combobox
                            :options="$businessFilterOptions"
                            :selected="$businessFilter"
                            model="businessFilter"
                            :placeholder="__('All businesses')"
                            :search-placeholder="__('Search business...')"
                            icon="fa-light fa-store"
                        />
                    @endunless
                    <x-ui.select wire:model.live="perPage">
                        <option value="10">{{ __('10 / page') }}</option>
                        <option value="25">{{ __('25 / page') }}</option>
                        <option value="50">{{ __('50 / page') }}</option>
                    </x-ui.select>
                </div>
            </div>
        </div>

        @if ($locations->count() > 0)
            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full text-left text-sm">
                    <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Location') }}</th>
                            @unless ($isScoped)
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Business') }}</th>
                            @endunless
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Contact') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Map') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        @foreach ($locations as $location)
                            @php
                                $mapHost = $location->google_maps_url ? (parse_url($location->google_maps_url, PHP_URL_HOST) ?: __('Map link')) : null;
                            @endphp
                            <tr class="transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)]">
                                <td class="max-w-[22rem] px-5 py-4">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-accent-rgb),0.18); background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="fa-light fa-location-dot"></i></span>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $location->name }}</p>
                                            <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $location->address ?: __('Address not set') }}</p>
                                        </div>
                                    </div>
                                </td>
                                @unless ($isScoped)
                                    <td class="px-5 py-4">
                                        <p class="font-medium" style="color: var(--theme-header-text-color);">{{ $location->business?->name ?: __('Business removed') }}</p>
                                    </td>
                                @endunless
                                <td class="px-5 py-4">
                                    <p class="truncate" style="color: var(--theme-header-text-color);">{{ $location->phone ?: __('No phone') }}</p>
                                    <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $location->email ?: __('No email') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($location->google_maps_url)
                                        <a href="{{ $location->google_maps_url }}" target="_blank" class="inline-flex items-center gap-2 font-semibold" style="color: var(--theme-accent);">{{ $mapHost }} <i class="fa-light fa-arrow-up-right text-xs"></i></a>
                                    @else
                                        <span style="color: var(--theme-muted-text-color);">{{ __('No map') }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <x-ui.badge :variant="$location->is_active ? 'success' : 'neutral'">{{ $location->is_active ? __('Active') : __('Inactive') }}</x-ui.badge>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('portal.locations.qr.preview', $location) }}" target="_blank" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border text-sm transition hover:-translate-y-px" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('Preview QR') }}">
                                            <i class="fa-light fa-eye"></i>
                                        </a>
                                        <a href="{{ route('portal.locations.qr.svg', $location) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border text-sm transition hover:-translate-y-px" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('Download QR') }}">
                                            <i class="fa-light fa-download"></i>
                                        </a>
                                        <button type="button" wire:click="editQrDesign({{ $location->id }})" x-on:click="$dispatch('open-location-qr-design')" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border text-sm transition hover:-translate-y-px" style="border-color: rgba(var(--theme-accent-rgb), .30); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .06);" title="{{ __('Design QR') }}">
                                            <i class="fa-light fa-sliders"></i>
                                        </button>
                                    </div>
                                    @include('appbusinesslocations::partials.delete-dialog', ['location' => $location])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="grid gap-3 p-4 lg:hidden">
                @foreach ($locations as $location)
                    @php
                        $mapHost = $location->google_maps_url ? (parse_url($location->google_maps_url, PHP_URL_HOST) ?: __('Map link')) : null;
                    @endphp
                    <article class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $location->name }}</p>
                                <p class="mt-1 truncate text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $location->business?->name ?: __('Business removed') }}</p>
                            </div>
                            <x-ui.badge :variant="$location->is_active ? 'success' : 'neutral'">{{ $location->is_active ? __('Active') : __('Inactive') }}</x-ui.badge>
                        </div>
                        <div class="mt-4 space-y-2 text-sm" style="color: var(--theme-muted-text-color);">
                            <p class="truncate"><i class="fa-light fa-phone mr-2"></i>{{ $location->phone ?: __('No phone') }}</p>
                            <p class="truncate"><i class="fa-light fa-envelope mr-2"></i>{{ $location->email ?: __('No email') }}</p>
                            <p class="truncate"><i class="fa-light fa-location-dot mr-2"></i>{{ $location->address ?: __('No address') }}</p>
                        </div>
                        <div class="mt-4 flex items-center justify-between gap-3 border-t pt-3" style="border-color: rgba(var(--theme-border-color-rgb), .55);">
                            @if ($location->google_maps_url)
                                <a href="{{ $location->google_maps_url }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">{{ __('Open map') }} <i class="fa-light fa-arrow-up-right text-xs"></i></a>
                            @else
                                <span class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Map URL not set') }}</span>
                            @endif
                            <div class="flex items-center gap-2">
                                <a href="{{ route('portal.locations.qr.svg', $location) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border text-sm" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('Download QR') }}"><i class="fa-light fa-download"></i></a>
                                <button type="button" wire:click="editQrDesign({{ $location->id }})" x-on:click="$dispatch('open-location-qr-design')" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border text-sm" style="border-color: rgba(var(--theme-accent-rgb), .30); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .06);" title="{{ __('Design QR') }}"><i class="fa-light fa-sliders"></i></button>
                                @include('appbusinesslocations::partials.delete-dialog', ['location' => $location])
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                <p class="text-sm" style="color: var(--theme-muted-text-color);">
                    {{ __('Showing') }}
                    <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($locations->firstItem()) }}</span>
                    -
                    <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($locations->lastItem()) }}</span>
                    {{ __('of') }}
                    <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($locations->total()) }}</span>
                    {{ __('locations') }}
                </p>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        wire:click="previousPage"
                        @disabled($locations->onFirstPage())
                        class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45"
                        style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"
                    >
                        <i class="fa-light fa-arrow-left"></i>
                        {{ __('Previous') }}
                    </button>

                    <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">
                        {{ __('Page') }} {{ number_format($locations->currentPage()) }} / {{ number_format($locations->lastPage()) }}
                    </span>

                    <button
                        type="button"
                        wire:click="nextPage"
                        @disabled(! $locations->hasMorePages())
                        class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45"
                        style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"
                    >
                        {{ __('Next') }}
                        <i class="fa-light fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        @else
            @php
                $hasActiveFilters = trim($search) !== '' || $businessFilter !== 'all';
            @endphp
            <div class="p-4">
                <div class="relative overflow-hidden rounded-[1.15rem] border p-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background:
                    radial-gradient(circle at top right, rgba(var(--theme-accent-rgb),0.12), transparent 34%),
                    linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.075), transparent 46%),
                    color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-center">
                        <div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl border shadow-[0_18px_45px_-36px_rgba(var(--theme-accent-rgb),0.85)]" style="border-color: rgba(var(--theme-accent-rgb), 0.18); background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                                <i class="fa-light {{ $hasActiveFilters ? 'fa-magnifying-glass' : 'fa-location-dot' }} text-xl"></i>
                            </div>
                            <h2 class="mt-5 text-xl font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">{{ $hasActiveFilters ? __('No matching locations') : __('No locations yet') }}</h2>
                            <p class="mt-3 max-w-xl text-sm leading-7" style="color: var(--theme-muted-text-color);">
                                {{ $hasActiveFilters ? __('Try a different search term or switch back to all businesses.') : __('Add a location for each branch, service area, clinic room, shop counter, or event booth.') }}
                            </p>
                            <div class="mt-5 flex flex-wrap gap-3">
                                @if ($hasActiveFilters)
                                    <x-ui.button type="button" variant="outline" wire:click="$set('search', '')" size="sm">
                                        <i class="fa-light fa-xmark"></i>{{ __('Clear search') }}
                                    </x-ui.button>
                                    <x-ui.button type="button" variant="outline" wire:click="$set('businessFilter', 'all')" size="sm">
                                        <i class="fa-light fa-layer-group"></i>{{ __('All businesses') }}
                                    </x-ui.button>
                                @endif
                            </div>
                        </div>
                        <div class="grid gap-2">
                            @foreach ([__('Branch address'), __('Service area'), __('Google Maps URL')] as $label)
                                <div class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); color: var(--theme-header-text-color); background-color: color-mix(in srgb, var(--theme-surface-overlay) 82%, transparent);">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                                        <i class="fa-light fa-check text-xs"></i>
                                    </span>
                                    {{ $label }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>

    <template x-teleport="body">
        <div x-cloak x-show="qrDesignOpen" class="fixed inset-0 z-[130] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="qrDesignOpen = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="qrDesignOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <div x-show="qrDesignOpen" x-transition.opacity.scale.90 class="relative w-full max-w-5xl">
                    <div class="relative flex max-h-[calc(100vh-3rem)] min-h-0 flex-col overflow-hidden rounded-[1.15rem] border shadow-[0_32px_80px_-34px_rgba(15,23,42,0.32)]" style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent); background-color: var(--theme-surface-overlay);">
                        <div class="shrink-0 flex items-start justify-between gap-4 border-b px-5 py-4 sm:px-6 sm:py-5" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent);">
                            <div class="min-w-0">
                                <h3 class="text-[1.05rem] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ __('Design location QR code') }}</h3>
                                <p class="mt-2 text-[15px] leading-7" style="color: var(--theme-muted-text-color);">
                                    {{ $qrDesignLocation ? __('Customize the QR print style for :location.', ['location' => $qrDesignLocation->name]) : __('Choose a location QR style.') }}
                                </p>
                            </div>
                            <button type="button" class="transition" style="color: var(--theme-muted-text-color);" x-on:click="qrDesignOpen = false">
                                <i class="fa-light fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 py-4 sm:px-6 sm:py-5">
                            <div wire:loading.flex wire:target="editQrDesign" class="items-center gap-3 rounded-xl border p-4 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .58); color: var(--theme-muted-text-color); background-color: var(--theme-surface-base);">
                                <i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Loading QR design...') }}
                            </div>

                            @if ($qrDesignLocation)
                                <form id="location-qr-design-form" wire:submit="saveQrDesign" class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_19rem]">
                                    <div class="space-y-5">
                                        <section class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                            <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Template gallery') }}</p>
                                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                                @foreach ($qrStyleTemplates as $key => $template)
                                                    <button type="button" wire:click="applyQrTemplate('{{ $key }}')" class="flex items-center justify-between gap-3 rounded-xl border px-3 py-3 text-left transition hover:-translate-y-px" style="border-color: {{ ($qrDesign['template'] ?? '') === $key ? 'rgba(var(--theme-accent-rgb), .38)' : 'rgba(var(--theme-border-color-rgb), .58)' }}; background-color: {{ ($qrDesign['template'] ?? '') === $key ? 'rgba(var(--theme-accent-rgb), .07)' : 'var(--theme-surface-overlay)' }};">
                                                        <span class="min-w-0">
                                                            <span class="block truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $template['label'] }}</span>
                                                            <span class="mt-1 block truncate text-xs uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ str_replace('_', ' ', $template['pattern']) }}</span>
                                                        </span>
                                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: {{ $template['background'] }};">
                                                            <i class="fa-light {{ ($qrDesign['template'] ?? '') === $key ? 'fa-check' : 'fa-qrcode' }} text-xs" style="color: {{ $template['foreground'] }};"></i>
                                                        </span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </section>

                                        <section class="grid gap-4 rounded-2xl border p-4 sm:grid-cols-2" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                            <x-ui.color-picker wire:model="qrDesign.foreground_color" name="qr_foreground_color" :label="__('QR color')" :value="$qrDesign['foreground_color'] ?? '#0f766e'" :error="$errors->first('qrDesign.foreground_color')" :presets="['#0f766e', '#14b8a6', '#15803d', '#0f4f87', '#334155', '#92400e', '#be185d', '#0f172a']" />
                                            <x-ui.color-picker wire:model="qrDesign.accent_color" name="qr_accent_color" :label="__('Accent color')" :value="$qrDesign['accent_color'] ?? '#14b8a6'" :error="$errors->first('qrDesign.accent_color')" :presets="['#14b8a6', '#16a34a', '#65a30d', '#f97316', '#2563eb', '#7c3aed', '#f59e0b', '#64748b']" />
                                            <x-ui.color-picker wire:model="qrDesign.background_color" name="qr_background_color" :label="__('QR background')" :value="$qrDesign['background_color'] ?? '#ffffff'" :error="$errors->first('qrDesign.background_color')" :presets="['#ffffff', '#f8fafc', '#f0fdfa', '#ecfeff', '#f0fdf4', '#eff6ff', '#fffbeb', '#fff1f8']" />
                                            <x-ui.color-picker wire:model="qrDesign.surface_color" name="qr_surface_color" :label="__('Frame background')" :value="$qrDesign['surface_color'] ?? '#ecfeff'" :error="$errors->first('qrDesign.surface_color')" :presets="['#ecfeff', '#f0fdfa', '#ecfdf5', '#eff6ff', '#f8fafc', '#fffbeb', '#fff1f2', '#ffffff']" />
                                            <x-ui.select wire:model="qrDesign.frame_style" name="qr_frame_style" :label="__('Frame style')" :error="$errors->first('qrDesign.frame_style')">
                                                <option value="card">{{ __('Card') }}</option>
                                                <option value="label">{{ __('Label') }}</option>
                                                <option value="minimal">{{ __('QR only') }}</option>
                                            </x-ui.select>
                                            <x-ui.input wire:model="qrDesign.label" name="qr_label" :label="__('Label')" :placeholder="__('Scan for directions')" :error="$errors->first('qrDesign.label')" />
                                            <x-ui.checkbox wire:model="qrDesign.show_location_name" :checked="(bool) ($qrDesign['show_location_name'] ?? true)" :label="__('Show location name')" :description="__('Print the branch name below the QR.')" />
                                            <x-ui.checkbox wire:model="qrDesign.show_address" :checked="(bool) ($qrDesign['show_address'] ?? true)" :label="__('Show address')" :description="__('Include the address on card-style QR codes.')" />
                                        </section>
                                    </div>

                                    <aside class="space-y-4">
                                        <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                            <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Preview') }}</p>
                                            <div class="mt-3 rounded-2xl border bg-white p-3" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                                <img src="{{ route('portal.locations.qr.preview', $qrDesignLocation) }}?v={{ md5(json_encode($qrDesign)) }}" alt="{{ __('Location QR preview') }}" class="mx-auto w-full max-w-[17rem]">
                                            </div>
                                            <div class="mt-3 grid gap-2 text-xs" style="color: var(--theme-muted-text-color);">
                                                <p class="truncate"><span class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Target') }}:</span> {{ $qrDesignLocation->destinationUrl() }}</p>
                                                <p class="truncate"><span class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Short QR URL') }}:</span> {{ $qrDesignLocation->qrTargetUrl() }}</p>
                                            </div>
                                        </div>

                                        <a href="{{ route('portal.locations.qr.svg', $qrDesignLocation) }}" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border px-4 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);">
                                            <i class="fa-light fa-download"></i>{{ __('Download SVG') }}
                                        </a>
                                    </aside>
                                </form>
                            @endif
                        </div>

                        <div class="shrink-0 border-t px-5 py-4 sm:px-6" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent); background-color: color-mix(in srgb, var(--theme-surface-soft) 88%, transparent);">
                            <div class="flex items-center justify-end gap-3">
                                <x-ui.button type="button" variant="outline" x-on:click="qrDesignOpen = false">{{ __('Cancel') }}</x-ui.button>
                                <x-ui.button type="submit" form="location-qr-design-form" wire:loading.attr="disabled" wire:target="saveQrDesign">
                                    <span wire:loading.remove wire:target="saveQrDesign" class="inline-flex items-center gap-2"><i class="fa-light fa-floppy-disk"></i>{{ __('Save QR design') }}</span>
                                    <span wire:loading wire:target="saveQrDesign" class="inline-flex items-center gap-2"><i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Saving...') }}</span>
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
