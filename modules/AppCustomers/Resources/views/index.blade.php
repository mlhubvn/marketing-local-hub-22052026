<div
    class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6"
    x-data="{ customerDialogOpen: false }"
    x-on:customer-saved.window="customerDialogOpen = false"
>
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif

    @php
        $isScoped = filled($scopedBusiness ?? null);
        $hasActiveFilters = trim($search) !== '' || (! $isScoped && $businessFilter !== 'all');
    @endphp

    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 34%),
        linear-gradient(35deg, rgba(var(--theme-success-color-rgb),0.08), transparent 38%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-7 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                @if ($isScoped)
                    <a href="{{ route('portal.businesses.show', $scopedBusiness) }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold" style="color: var(--theme-muted-text-color);">
                        <i class="fa-light fa-arrow-left"></i>{{ $scopedBusiness->name }}
                    </a>
                @else
                    <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                        <i class="fa-light fa-address-book"></i>
                        {{ __('Local Businesses') }}
                    </div>
                @endif
                <h1 class="mt-4 max-w-3xl text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('Customer directory') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ $isScoped
                        ? __('Customers and contact records connected to this business workspace.')
                        : __('Capture and manage contacts from bookings, coupons, feedback, and lead forms without mixing forms into the directory.')
                    }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button type="button" size="lg" x-on:click="customerDialogOpen = true; $wire.create()">
                        <i class="fa-light fa-plus"></i>{{ __('Add customer') }}
                    </x-ui.button>
                </div>
            </div>

            <div class="rounded-[1.2rem] border p-4 shadow-[0_24px_70px_-48px_rgba(var(--theme-border-color-rgb),0.9)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Contact coverage') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Customers ready for campaigns') }}</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-users"></i>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($totalCustomers) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Customers') }}</p>
                    </div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($isScoped ? 1 : $businesses->count()) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $isScoped ? __('Current business') : __('Businesses') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($isScoped)
        @include('appbusinessprofiles::partials.business-tabs', ['business' => $scopedBusiness, 'active' => 'customers'])
    @endif

    <section class="overflow-visible rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Customers') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Search, filter, edit, and export customer-ready contacts.') }}</p>
                </div>
                <div class="grid gap-3 {{ $isScoped ? 'md:grid-cols-[minmax(0,18rem)_8rem]' : 'md:grid-cols-[minmax(0,18rem)_12rem_8rem]' }}">
                    <div class="relative">
                        <i class="fa-light fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm" style="color: var(--theme-muted-text-color);"></i>
                        <input type="search" wire:model.live.debounce.300ms="search" class="h-11 w-full rounded-xl border pl-10 pr-10 text-sm outline-none transition focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Search name, phone, email...') }}">
                        @if (trim($search) !== '')
                            <button type="button" wire:click="$set('search', '')" class="absolute right-3 top-1/2 -translate-y-1/2" style="color: var(--theme-muted-text-color);">
                                <i class="fa-light fa-xmark"></i>
                            </button>
                        @endif
                    </div>
                    @unless ($isScoped)
                        <x-ui.select wire:model.live="businessFilter">
                            <option value="all">{{ __('All businesses') }}</option>
                            @foreach ($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </x-ui.select>
                    @endunless
                    <x-ui.select wire:model.live="perPage">
                        <option value="10">{{ __('10 / page') }}</option>
                        <option value="25">{{ __('25 / page') }}</option>
                        <option value="50">{{ __('50 / page') }}</option>
                    </x-ui.select>
                </div>
            </div>
        </div>

        @if ($customers->count() > 0)
            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full text-left text-sm">
                    <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Customer') }}</th>
                            @unless ($isScoped)
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Business') }}</th>
                            @endunless
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Contact') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Source') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Last activity') }}</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        @foreach ($customers as $customer)
                            @php
                                $metadata = is_array($customer->metadata) ? $customer->metadata : [];
                                $source = $metadata['source'] ?? __('Manual');
                                $sourceCampaign = $metadata['source_campaign'] ?? null;
                                $status = $metadata['status'] ?? __('Active');
                            @endphp
                            <tr class="transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)]">
                                <td class="max-w-[18rem] px-5 py-4">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border text-xs font-semibold uppercase" style="border-color: rgba(var(--theme-accent-rgb),0.18); background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                                            {{ str($customer->name)->substr(0, 2)->upper() }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $customer->name }}</p>
                                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Customer profile') }}</p>
                                        </div>
                                    </div>
                                </td>
                                @unless ($isScoped)
                                    <td class="px-5 py-4">
                                        <p class="font-medium" style="color: var(--theme-header-text-color);">{{ $customer->business?->name ?: __('No business') }}</p>
                                    </td>
                                @endunless
                                <td class="px-5 py-4">
                                    <p class="truncate" style="color: var(--theme-header-text-color);">{{ $customer->phone ?: __('No phone') }}</p>
                                    <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $customer->email ?: __('No email') }}</p>
                                </td>
                                <td class="max-w-[20rem] px-5 py-4">
                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $source }}</p>
                                    <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $sourceCampaign ?: ($customer->note ?: __('No source campaign')) }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <x-ui.badge variant="success">{{ str($status)->headline() }}</x-ui.badge>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-sm" style="color: var(--theme-header-text-color);">{{ format_date_locale($customer->updated_at) }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Created') }} {{ format_date_locale($customer->created_at) }}</p>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('Edit') }}" x-on:click="customerDialogOpen = true; $wire.edit({{ $customer->id }})">
                                            <i class="fa-light fa-pen"></i>
                                        </button>
                                        <x-ui.dialog :title="__('Delete customer')" :description="__('This contact will be removed from your customer directory.')" width="sm" dismissible>
                                            <x-slot:trigger>
                                                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color); background-color: rgba(var(--theme-danger-color-rgb), .05);" title="{{ __('Delete') }}">
                                                    <i class="fa-light fa-trash"></i>
                                                </button>
                                            </x-slot:trigger>
                                            <x-slot:footer>
                                                <div class="flex justify-end gap-3">
                                                    <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                                    <x-ui.button type="button" variant="danger" wire:click="delete({{ $customer->id }})" x-on:click="open = false">{{ __('Delete') }}</x-ui.button>
                                                </div>
                                            </x-slot:footer>
                                        </x-ui.dialog>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="grid gap-3 p-4 lg:hidden">
                @foreach ($customers as $customer)
                    <article class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $customer->name }}</p>
                                <p class="mt-1 truncate text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $customer->business?->name ?: __('No business') }}</p>
                            </div>
                            <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color);" x-on:click="customerDialogOpen = true; $wire.edit({{ $customer->id }})">
                                <i class="fa-light fa-pen"></i>
                            </button>
                        </div>
                        <div class="mt-4 space-y-2 text-sm" style="color: var(--theme-muted-text-color);">
                            <p class="truncate"><i class="fa-light fa-phone mr-2"></i>{{ $customer->phone ?: __('No phone') }}</p>
                            <p class="truncate"><i class="fa-light fa-envelope mr-2"></i>{{ $customer->email ?: __('No email') }}</p>
                            <p class="truncate"><i class="fa-light fa-note-sticky mr-2"></i>{{ $customer->note ?: __('No note') }}</p>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                <p class="text-sm" style="color: var(--theme-muted-text-color);">
                    {{ __('Showing') }}
                    <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($customers->firstItem()) }}</span>
                    -
                    <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($customers->lastItem()) }}</span>
                    {{ __('of') }}
                    <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($customers->total()) }}</span>
                    {{ __('customers') }}
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="previousPage" @disabled($customers->onFirstPage()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">
                        <i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}
                    </button>
                    <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">
                        {{ __('Page') }} {{ format_number_locale($customers->currentPage()) }} / {{ format_number_locale($customers->lastPage()) }}
                    </span>
                    <button type="button" wire:click="nextPage" @disabled(! $customers->hasMorePages()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">
                        {{ __('Next') }}<i class="fa-light fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        @else
            <div class="p-4">
                <div class="relative overflow-hidden rounded-[1.15rem] border p-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background:
                    radial-gradient(circle at top right, rgba(var(--theme-accent-rgb),0.12), transparent 34%),
                    linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.075), transparent 46%),
                    color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-center">
                        <div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-accent-rgb), 0.18); background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                                <i class="fa-light {{ $hasActiveFilters ? 'fa-magnifying-glass' : 'fa-address-book' }} text-xl"></i>
                            </div>
                            <h2 class="mt-5 text-xl font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">{{ $hasActiveFilters ? __('No matching customers') : __('No customers yet') }}</h2>
                            <p class="mt-3 max-w-xl text-sm leading-7" style="color: var(--theme-muted-text-color);">
                                {{ $hasActiveFilters ? __('Try a different search term or switch back to all businesses.') : __('Contacts captured by campaigns will also appear here. You can add a manual customer when needed.') }}
                            </p>
                            <div class="mt-5 flex flex-wrap gap-3">
                                @if ($hasActiveFilters)
                                    <x-ui.button type="button" variant="outline" wire:click="$set('search', '')" size="sm">
                                        <i class="fa-light fa-xmark"></i>{{ __('Clear search') }}
                                    </x-ui.button>
                                    <x-ui.button type="button" variant="outline" wire:click="$set('businessFilter', 'all')" size="sm">
                                        <i class="fa-light fa-layer-group"></i>{{ __('All businesses') }}
                                    </x-ui.button>
                                @else
                                    <x-ui.button type="button" size="sm" x-on:click="customerDialogOpen = true; $wire.create()">
                                        <i class="fa-light fa-plus"></i>{{ __('Add customer') }}
                                    </x-ui.button>
                                @endif
                            </div>
                        </div>
                        <div class="grid gap-2">
                            @foreach ([__('Booking contacts'), __('Coupon claims'), __('Feedback follow-up')] as $label)
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
        <div x-cloak x-show="customerDialogOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="customerDialogOpen = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="customerDialogOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <div x-show="customerDialogOpen" x-transition.opacity.scale.90 class="relative w-full max-w-4xl">
                    <form wire:submit="save" class="relative flex max-h-[calc(100vh-3.5rem)] min-h-0 flex-col overflow-hidden rounded-[1.15rem] border shadow-[0_32px_80px_-34px_rgba(15,23,42,0.32)]" style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent); background-color: var(--theme-surface-overlay);">
                        <div class="shrink-0 flex items-start justify-between gap-4 border-b px-5 py-4 sm:px-6 sm:py-5" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent);">
                            <div>
                                <h3 class="text-[1.05rem] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ $editingId ? __('Edit customer') : __('Add customer') }}</h3>
                                <p class="mt-2 text-[15px] leading-7" style="color: var(--theme-muted-text-color);">{{ __('Keep contact details ready for booking reminders, coupon follow-up, and lead campaigns.') }}</p>
                            </div>
                            <button type="button" class="transition" style="color: var(--theme-muted-text-color);" x-on:click="customerDialogOpen = false">
                                <i class="fa-light fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 py-4 sm:px-6 sm:py-5">
                            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
                                <div class="space-y-5">
                                    <div class="grid gap-4 md:grid-cols-2">
                                        @if ($isScoped)
                                            <div>
                                                <label class="mb-2 block text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Business') }}</label>
                                                <div class="flex h-11 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: var(--theme-border-color); background-color: color-mix(in srgb, var(--theme-surface-soft) 86%, transparent); color: var(--theme-header-text-color);">
                                                    {{ $scopedBusiness->name }}
                                                </div>
                                            </div>
                                        @else
                                            <x-ui.select wire:model="business_id" name="business_id" :label="__('Business')" :error="$errors->first('business_id')">
                                                <option value="">{{ __('No business') }}</option>
                                                @foreach($businesses as $business)
                                                    <option value="{{ $business->id }}">{{ $business->name }}</option>
                                                @endforeach
                                            </x-ui.select>
                                        @endif
                                        <x-ui.input wire:model="name" name="name" :label="__('Name')" :placeholder="__('Customer name')" :error="$errors->first('name')" />
                                        <x-ui.input wire:model="phone" name="phone" :label="__('Phone')" :placeholder="__('Phone number')" :error="$errors->first('phone')" />
                                        <x-ui.input wire:model="email" name="email" :label="__('Email')" :placeholder="__('customer@example.com')" :error="$errors->first('email')" />
                                    </div>
                                    <x-ui.textarea wire:model="note" name="note" :label="__('Note')" rows="5" :placeholder="__('Preferences, source, follow-up note...')" :error="$errors->first('note')">{{ $note }}</x-ui.textarea>
                                </div>

                                <aside class="rounded-2xl border p-4 lg:sticky lg:top-0" style="border-color: rgba(var(--theme-border-color-rgb), .55); background:
                                    linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.08), transparent 42%),
                                    color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Customer use cases') }}</p>
                                    <div class="mt-4 space-y-3">
                                        @foreach ([__('Booking reminders'), __('Coupon follow-up'), __('Review requests'), __('Lead nurturing')] as $item)
                                            <div class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold" style="background-color: color-mix(in srgb, var(--theme-surface-soft) 78%, transparent); color: var(--theme-header-text-color);">
                                                <span class="flex h-7 w-7 items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                                                    <i class="fa-light fa-check text-xs"></i>
                                                </span>
                                                {{ $item }}
                                            </div>
                                        @endforeach
                                    </div>
                                </aside>
                            </div>
                        </div>

                        <div class="shrink-0 border-t px-5 py-4 sm:px-6" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent); background-color: color-mix(in srgb, var(--theme-surface-soft) 88%, transparent);">
                            <div class="flex items-center justify-end gap-3">
                                <x-ui.button type="button" variant="outline" x-on:click="customerDialogOpen = false">{{ __('Cancel') }}</x-ui.button>
                                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="save">
                                    <span wire:loading.remove wire:target="save" class="inline-flex items-center gap-2">
                                        <i class="fa-light fa-floppy-disk"></i>{{ $editingId ? __('Save changes') : __('Save customer') }}
                                    </span>
                                    <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                                        <i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Saving...') }}
                                    </span>
                                </x-ui.button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
