<div
    class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6"
    x-data="{ serviceDialogOpen: false, pageDialogOpen: false, bookingTab: 'services' }"
    x-on:booking-service-editing.window="serviceDialogOpen = true"
    x-on:booking-service-saved.window="serviceDialogOpen = false"
    x-on:booking-page-saved.window="pageDialogOpen = false; bookingTab = 'pages'"
>
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif
    @include('applandingpages::partials.growth-tool-created-actions')

    @php
        $bookingPromoContentUrl = route('portal.ai-content', array_filter([
            'business_id' => $business_id ?: (string) optional($businesses->first())->id,
            'type' => 'social_post',
            'goal' => 'Promote a booking page for local appointment requests.',
            'offer' => 'Limited appointment slots available this week',
            'target_customer' => 'Local customers ready to book',
            'details' => 'Write a booking promo message that can be reused for social, WhatsApp, or email.',
            'source_type' => 'booking',
        ], fn ($value) => filled($value)));
        $bookingReminderContentUrl = route('portal.ai-content', array_filter([
            'business_id' => $business_id ?: (string) optional($businesses->first())->id,
            'type' => 'booking_reminder',
            'goal' => 'Remind customers about upcoming appointments.',
            'target_customer' => 'Booked customers',
            'details' => 'Write a friendly booking reminder and follow-up message.',
            'source_type' => 'booking',
        ], fn ($value) => filled($value)));
    @endphp

    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 34%),
        linear-gradient(35deg, rgba(var(--theme-success-color-rgb),0.09), transparent 38%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-6 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-md border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-calendar-check"></i>{{ __('Growth Tools') }}
                </div>
                <h1 class="mt-4 max-w-3xl text-[2.25rem] font-semibold leading-[1.04] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('Booking Pages') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ __('Publish booking pages for local services, collect appointment requests, and manage follow-up from one workspace.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button type="button" size="lg" wire:click="createService">
                        <i class="fa-light fa-plus"></i>{{ __('Create booking service') }}
                    </x-ui.button>
                    <x-ui.button type="button" variant="outline" size="lg" x-on:click="pageDialogOpen = true">
                        <i class="fa-light fa-window"></i>{{ __('Create public booking page') }}
                    </x-ui.button>
                    <x-ui.button href="{{ $bookingPromoContentUrl }}" wire:navigate variant="outline" size="lg">
                        <i class="fa-light fa-bullhorn"></i>{{ __('Generate booking promo') }}
                    </x-ui.button>
                    <x-ui.button href="{{ $bookingReminderContentUrl }}" wire:navigate variant="outline" size="lg">
                        <i class="fa-light fa-bell"></i>{{ __('Generate reminder') }}
                    </x-ui.button>
                    @if ($latestBookingCampaign)
                        <x-ui.button href="{{ $latestBookingCampaign->publicUrl() }}" target="_blank" variant="outline" size="lg">
                            <i class="fa-light fa-arrow-up-right"></i>{{ __('Open booking page') }}
                        </x-ui.button>
                    @endif
                </div>
            </div>

            <div class="rounded-[1.15rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Booking pipeline') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Current appointment status') }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-calendar-lines"></i>
                    </span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    @foreach ([
                        ['label' => __('Pending'), 'value' => $stats['pending']],
                        ['label' => __('Confirmed'), 'value' => $stats['confirmed']],
                        ['label' => __('Completed'), 'value' => $stats['completed']],
                        ['label' => __('Cancelled'), 'value' => $stats['cancelled']],
                    ] as $item)
                        <div class="rounded-lg border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                            <p class="text-xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($item['value']) }}</p>
                            <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $item['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
        @foreach ([
            ['label' => __('Bookable services'), 'value' => $stats['services'], 'description' => __('Customer choices'), 'icon' => 'fa-light fa-list-check'],
            ['label' => __('Active Services'), 'value' => $stats['active_services'], 'description' => __('Visible to customers'), 'icon' => 'fa-light fa-toggle-on'],
            ['label' => __('Visits'), 'value' => $stats['visits'], 'description' => __('QR scans & link visits'), 'icon' => 'fa-light fa-chart-line'],
            ['label' => __('Bookings'), 'value' => $stats['bookings'], 'description' => __('Appointment requests'), 'icon' => 'fa-light fa-user-clock'],
            ['label' => __('Pending'), 'value' => $stats['pending'], 'description' => __('Need confirmation'), 'icon' => 'fa-light fa-clock'],
            ['label' => __('Confirmed'), 'value' => $stats['confirmed'], 'description' => __('Scheduled appointments'), 'icon' => 'fa-light fa-circle-check'],
        ] as $metric)
            <article class="relative overflow-hidden rounded-[1.1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background: linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.07), transparent 44%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <span class="absolute inset-x-0 top-0 h-1" style="background-color: var(--theme-accent);"></span>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[2rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ number_format($metric['value']) }}</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $metric['description'] }}</p>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="{{ $metric['icon'] }}"></i>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="flex flex-col gap-4 border-b px-5 py-4 xl:flex-row xl:items-end xl:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <div>
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Booking workspace') }}</p>
                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Manage services, public booking pages, and appointment requests.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.button type="button" size="sm" wire:click="createService">
                    <i class="fa-light fa-plus"></i>{{ __('New service') }}
                </x-ui.button>
                <x-ui.button type="button" size="sm" variant="outline" x-on:click="pageDialogOpen = true">
                    <i class="fa-light fa-window"></i>{{ __('New public page') }}
                </x-ui.button>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 border-b px-5 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            @foreach ([
                ['key' => 'services', 'label' => __('Bookable services'), 'count' => $stats['services']],
                ['key' => 'pages', 'label' => __('Public booking pages'), 'count' => $stats['booking_pages']],
                ['key' => 'bookings', 'label' => __('Bookings'), 'count' => $stats['bookings']],
                ['key' => 'workflow', 'label' => __('Workflow'), 'count' => null],
            ] as $tab)
                <button type="button" x-on:click="bookingTab = '{{ $tab['key'] }}'" class="rounded-[0.8rem] px-4 py-2 text-sm font-semibold transition" x-bind:style="bookingTab === '{{ $tab['key'] }}' ? 'background-color: rgba(var(--theme-accent-rgb), .14); color: var(--theme-accent); border: 1px solid rgba(var(--theme-accent-rgb), .28);' : 'color: var(--theme-muted-text-color); border: 1px solid transparent;'">
                    {{ $tab['label'] }}
                    @if ($tab['count'] !== null)
                        <span class="ml-2 rounded-full px-2 py-0.5 text-xs" style="background-color: rgba(var(--theme-accent-rgb), .10);">{{ number_format($tab['count']) }}</span>
                    @endif
                </button>
            @endforeach
        </div>

        <div x-show="bookingTab === 'services'">
            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Bookable service catalog') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Services customers can select on booking pages.') }}</p>
                </div>
                <x-ui.select wire:model.live="servicesPerPage" name="booking_services_per_page" class="w-full sm:w-36">
                    <option value="10">{{ __('10 / page') }}</option>
                    <option value="25">{{ __('25 / page') }}</option>
                    <option value="50">{{ __('50 / page') }}</option>
                </x-ui.select>
            </div>

            @if ($services->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead style="color: var(--theme-muted-text-color);">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Service') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Business') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Duration') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Price') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            @foreach ($services as $service)
                                @php
                                    $serviceContentUrl = route('portal.ai-content', array_filter([
                                        'business_id' => $service->business_id,
                                        'type' => 'social_post',
                                        'goal' => 'Promote this booking service and drive appointment requests.',
                                        'offer' => $service->price !== null ? 'Service price: '.format_price_locale((float) $service->price) : '',
                                        'target_customer' => 'Local customers ready to book',
                                        'details' => trim(implode("\n", array_filter([
                                            'Service: '.$service->name,
                                            'Duration: '.$service->duration_minutes.' minutes',
                                            $service->description ? 'Description: '.$service->description : null,
                                        ]))),
                                        'source_type' => 'booking_service',
                                        'source_id' => $service->id,
                                    ], fn ($value) => filled($value)));
                                @endphp
                                <tr>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $service->name }}</p>
                                        <p class="mt-1 max-w-sm truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $service->description ?: __('No description') }}</p>
                                    </td>
                                    <td class="px-5 py-4" style="color: var(--theme-muted-text-color);">{{ $businessNames[$service->business_id] ?? __('Business removed') }}</td>
                                    <td class="px-5 py-4 font-semibold" style="color: var(--theme-header-text-color);">{{ $service->duration_minutes }} {{ __('min') }}</td>
                                    <td class="px-5 py-4 font-semibold" style="color: var(--theme-header-text-color);">{{ $service->price !== null ? format_price_locale((float) $service->price) : __('Free') }}</td>
                                    <td class="px-5 py-4"><x-ui.badge :variant="$service->is_active ? 'success' : 'neutral'">{{ $service->is_active ? __('Active') : __('Paused') }}</x-ui.badge></td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ $serviceContentUrl }}" wire:navigate class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-accent); background-color: var(--theme-surface-overlay);" title="{{ __('Generate content') }}"><i class="fa-light fa-pen-nib"></i></a>
                                            <button type="button" wire:click="editService({{ $service->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-accent-rgb), .30); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .06);" title="{{ __('Edit service') }}"><i class="fa-light fa-pencil"></i></button>
                                            <button type="button" wire:click="toggleService({{ $service->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ $service->is_active ? __('Pause') : __('Activate') }}"><i class="fa-light {{ $service->is_active ? 'fa-pause' : 'fa-play' }}"></i></button>
                                            <x-ui.dialog :title="__('Delete booking service')" :description="__('This removes the service from booking pages. Existing bookings will remain for reporting.')" width="sm" dismissible>
                                                <x-slot:trigger>
                                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color); background-color: rgba(var(--theme-danger-color-rgb), .05);" title="{{ __('Delete') }}"><i class="fa-light fa-trash"></i></button>
                                                </x-slot:trigger>
                                                <x-slot:footer>
                                                    <div class="flex justify-end gap-3">
                                                        <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                                        <x-ui.button type="button" variant="danger" wire:click="deleteService({{ $service->id }})" x-on:click="open = false">{{ __('Delete') }}</x-ui.button>
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
                <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Showing') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($services->firstItem()) }}</span> - <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($services->lastItem()) }}</span> {{ __('of') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($services->total()) }}</span> {{ __('services') }}</p>
                    <div class="flex items-center gap-2">
                        <x-ui.button type="button" variant="outline" wire:click="previousPage('servicesPage')" :disabled="$services->onFirstPage()"><i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}</x-ui.button>
                        <span class="rounded-[0.8rem] border px-4 py-2 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .10);">{{ __('Page :page / :pages', ['page' => $services->currentPage(), 'pages' => max(1, $services->lastPage())]) }}</span>
                        <x-ui.button type="button" variant="outline" wire:click="nextPage('servicesPage')" :disabled="! $services->hasMorePages()">{{ __('Next') }}<i class="fa-light fa-arrow-right"></i></x-ui.button>
                    </div>
                </div>
            @else
                <div class="p-8"><x-ui.empty icon="fa-light fa-calendar-plus" :title="__('No services yet')" :description="__('Create your first bookable service so customers can request appointments from a public booking page.')" /></div>
            @endif
        </div>

        <div x-cloak x-show="bookingTab === 'pages'">
            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Booking pages') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Published customer booking links.') }}</p>
                </div>
                <x-ui.select wire:model.live="pagesPerPage" name="booking_pages_per_page" class="w-full sm:w-36">
                    <option value="10">{{ __('10 / page') }}</option>
                    <option value="25">{{ __('25 / page') }}</option>
                    <option value="50">{{ __('50 / page') }}</option>
                </x-ui.select>
            </div>
            @if ($bookingCampaigns->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead style="color: var(--theme-muted-text-color);">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Page') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Business') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Visits') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            @foreach ($bookingCampaigns as $campaign)
                                <tr>
                                    <td class="px-5 py-4"><p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $campaign->name }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $campaign->slug }}</p></td>
                                    <td class="px-5 py-4" style="color: var(--theme-muted-text-color);">{{ $campaign->business?->name ?: __('Business removed') }}</td>
                                    <td class="px-5 py-4 font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($campaign->scans_count) }}</td>
                                    <td class="px-5 py-4"><x-ui.badge :variant="$campaign->published_at ? 'success' : 'neutral'">{{ $campaign->published_at ? __('Live') : __('Paused') }}</x-ui.badge></td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ $campaign->publicUrl() }}" target="_blank" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('Open') }}"><i class="fa-light fa-arrow-up-right"></i></a>
                                            @if ($campaign->landingPage)
                                                <a href="{{ route('portal.landing-pages', ['edit' => $campaign->landingPage->id, 'return' => request()->fullUrl()]) }}" wire:navigate class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-accent-rgb), .30); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .06);" title="{{ __('Edit design') }}"><i class="fa-light fa-palette"></i></a>
                                            @endif
                                            <x-ui.dropdown-menu align="right" width="auto">
                                                <x-slot:trigger><button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('QR code') }}"><i class="fa-light fa-qrcode"></i></button></x-slot:trigger>
                                                <x-ui.dropdown-menu-item icon="fa-light fa-copy" onclick="navigator.clipboard && navigator.clipboard.writeText(this.dataset.copy || '')" data-copy="{{ e($campaign->publicUrl()) }}">{{ __('Copy QR link') }}</x-ui.dropdown-menu-item>
                                                <x-ui.dropdown-menu-item :href="route('qr-campaigns.png', ['campaign' => $campaign->slug])" icon="fa-light fa-file-image">{{ __('Download PNG') }}</x-ui.dropdown-menu-item>
                                                <x-ui.dropdown-menu-item :href="route('qr-campaigns.svg', ['campaign' => $campaign->slug])" icon="fa-light fa-code">{{ __('Download SVG') }}</x-ui.dropdown-menu-item>
                                                @if (\Illuminate\Support\Facades\Route::has('portal.qr-codes.analytics'))
                                                    <x-ui.dropdown-menu-item :href="route('portal.qr-codes.analytics', ['campaign' => $campaign->slug])" icon="fa-light fa-chart-line" wire:navigate>{{ __('View Analytics') }}</x-ui.dropdown-menu-item>
                                                @endif
                                            </x-ui.dropdown-menu>
                                            <button type="button" wire:click="toggleBookingPage({{ $campaign->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ $campaign->published_at ? __('Pause') : __('Publish') }}"><i class="fa-light {{ $campaign->published_at ? 'fa-pause' : 'fa-play' }}"></i></button>
                                            <x-ui.dialog :title="__('Delete booking page')" :description="__('This removes the booking page, QR asset, scans, and booking requests connected to it.')" width="sm" dismissible>
                                                <x-slot:trigger><button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color); background-color: rgba(var(--theme-danger-color-rgb), .05);" title="{{ __('Delete') }}"><i class="fa-light fa-trash"></i></button></x-slot:trigger>
                                                <x-slot:footer><div class="flex justify-end gap-3"><x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button><x-ui.button type="button" variant="danger" wire:click="deleteBookingPage({{ $campaign->id }})" x-on:click="open = false">{{ __('Delete') }}</x-ui.button></div></x-slot:footer>
                                            </x-ui.dialog>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Showing') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($bookingCampaigns->firstItem()) }}</span> - <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($bookingCampaigns->lastItem()) }}</span> {{ __('of') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($bookingCampaigns->total()) }}</span> {{ __('booking pages') }}</p>
                    <div class="flex items-center gap-2">
                        <x-ui.button type="button" variant="outline" wire:click="previousPage('bookingPagesPage')" :disabled="$bookingCampaigns->onFirstPage()"><i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}</x-ui.button>
                        <span class="rounded-[0.8rem] border px-4 py-2 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .10);">{{ __('Page :page / :pages', ['page' => $bookingCampaigns->currentPage(), 'pages' => max(1, $bookingCampaigns->lastPage())]) }}</span>
                        <x-ui.button type="button" variant="outline" wire:click="nextPage('bookingPagesPage')" :disabled="! $bookingCampaigns->hasMorePages()">{{ __('Next') }}<i class="fa-light fa-arrow-right"></i></x-ui.button>
                    </div>
                </div>
            @else
                <div class="p-8"><x-ui.empty icon="fa-light fa-calendar-days" :title="__('No public booking pages yet')" :description="__('Create a public booking page to publish a customer booking link with QR code and design.')" /></div>
            @endif
        </div>

        <div x-cloak x-show="bookingTab === 'bookings'">
            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div><p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recent bookings') }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Incoming appointment requests from booking pages.') }}</p></div>
                <x-ui.select wire:model.live="bookingsPerPage" name="bookings_per_page" class="w-full sm:w-36"><option value="10">{{ __('10 / page') }}</option><option value="25">{{ __('25 / page') }}</option><option value="50">{{ __('50 / page') }}</option></x-ui.select>
            </div>
            @if ($bookings->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead style="color: var(--theme-muted-text-color);"><tr><th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Customer') }}</th><th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Date / Time') }}</th><th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Contact') }}</th><th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th></tr></thead>
                        <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            @foreach ($bookings as $booking)
                                <tr>
                                    <td class="px-5 py-4"><p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $booking->customer_name }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $booking->note ?: __('No note') }}</p></td>
                                    <td class="px-5 py-4"><p class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_date_locale($booking->booking_date) }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $booking->booking_time }}</p></td>
                                    <td class="px-5 py-4"><p>{{ $booking->customer_phone ?: __('No phone') }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $booking->customer_email ?: __('No email') }}</p></td>
                                    <td class="px-5 py-4"><x-ui.badge :variant="$booking->status === 'confirmed' || $booking->status === 'completed' ? 'success' : ($booking->status === 'cancelled' ? 'danger' : 'warning')">{{ str($booking->status)->headline() }}</x-ui.badge></td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="inline-flex gap-2">
                                            <button type="button" wire:click="setBookingStatus({{ $booking->id }}, 'confirmed')" class="rounded-lg border px-3 py-2 text-xs font-semibold" style="border-color: rgba(var(--theme-success-color-rgb), .28); color: var(--theme-success-color);">{{ __('Confirm') }}</button>
                                            <button type="button" wire:click="setBookingStatus({{ $booking->id }}, 'cancelled')" class="rounded-lg border px-3 py-2 text-xs font-semibold" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color);">{{ __('Cancel') }}</button>
                                            <button type="button" wire:click="setBookingStatus({{ $booking->id }}, 'completed')" class="rounded-lg border px-3 py-2 text-xs font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color);">{{ __('Complete') }}</button>
                                            <x-ui.dialog :title="__('Delete booking')" :description="__('This permanently removes the booking request from this workspace.')" width="sm" dismissible>
                                                <x-slot:trigger>
                                                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border text-xs font-semibold" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color); background-color: var(--theme-surface-overlay);" title="{{ __('Delete') }}">
                                                        <i class="fa-light fa-trash"></i>
                                                    </button>
                                                </x-slot:trigger>
                                                <x-slot:footer>
                                                    <div class="flex justify-end gap-3">
                                                        <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                                        <x-ui.button type="button" variant="danger" wire:click="deleteBooking({{ $booking->id }})" x-on:click="open = false">{{ __('Delete') }}</x-ui.button>
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
                <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Showing') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($bookings->firstItem()) }}</span> - <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($bookings->lastItem()) }}</span> {{ __('of') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($bookings->total()) }}</span> {{ __('bookings') }}</p>
                    <div class="flex items-center gap-2"><x-ui.button type="button" variant="outline" wire:click="previousPage('bookingsPage')" :disabled="$bookings->onFirstPage()"><i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}</x-ui.button><span class="rounded-[0.8rem] border px-4 py-2 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .10);">{{ __('Page :page / :pages', ['page' => $bookings->currentPage(), 'pages' => max(1, $bookings->lastPage())]) }}</span><x-ui.button type="button" variant="outline" wire:click="nextPage('bookingsPage')" :disabled="! $bookings->hasMorePages()">{{ __('Next') }}<i class="fa-light fa-arrow-right"></i></x-ui.button></div>
                </div>
            @else
                <div class="p-8"><x-ui.empty icon="fa-light fa-calendar-check" :title="__('No bookings yet')" :description="__('Booking requests will appear here after customers submit an appointment from a public booking page.')" /></div>
            @endif
        </div>

        <div x-cloak x-show="bookingTab === 'workflow'" class="p-5">
            <div class="grid gap-3 md:grid-cols-2">
                @foreach ([__('Bookable services stay separate from public booking pages'), __('Public booking pages carry the landing page, QR code, and design'), __('Booking status actions for follow-up'), __('Visit tracking from QR scans and links')] as $item)
                    <div class="flex gap-3 rounded-[1rem] border p-4 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="fa-light fa-check text-xs"></i></span>
                        <span style="color: var(--theme-muted-text-color);">{{ $item }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <template x-teleport="body">
        <div x-cloak x-show="serviceDialogOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="serviceDialogOpen = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="serviceDialogOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <form wire:submit="saveService" x-show="serviceDialogOpen" x-transition.opacity.scale.95 class="relative w-full max-w-2xl overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.72); background-color: var(--theme-surface-overlay);">
                    <div class="flex items-start justify-between gap-4 border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <div>
                            <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $editing_service_id ? __('Edit booking service') : __('Create booking service') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $editing_service_id ? __('Update service details, price, and availability.') : __('Add a service customers can book from your public booking page.') }}</p>
                        </div>
                        <button type="button" x-on:click="serviceDialogOpen = false" class="flex h-10 w-10 items-center justify-center rounded-xl" style="color: var(--theme-muted-text-color);">
                            <i class="fa-light fa-xmark"></i>
                        </button>
                    </div>

                    <div class="grid gap-4 p-5" x-data="{ useBusinessHours: @entangle('use_business_hours').live }">
                        <x-ui.select wire:model="business_id" name="business_id" :label="__('Business')" :error="$errors->first('business_id')">
                            <option value="">{{ __('Select business') }}</option>
                            @foreach ($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.input wire:model="service_name" name="service_name" :label="__('Service name')" :placeholder="__('Haircut, consultation, massage...')" :error="$errors->first('service_name')" />
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input wire:model="duration_minutes" name="duration_minutes" type="number" :label="__('Duration minutes')" :error="$errors->first('duration_minutes')" />
                            <x-ui.input wire:model="price" name="price" :label="__('Price')" :placeholder="__('Optional')" :error="$errors->first('price')" />
                        </div>
                        <x-ui.textarea wire:model="description" name="description" :label="__('Description')" rows="3" :placeholder="__('What customers should know before booking...')" :error="$errors->first('description')">{{ $description }}</x-ui.textarea>

                        <section class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Availability') }}</p>
                                    <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Slots are generated from opening hours, duration, interval, and buffer time.') }}</p>
                                </div>
                                <x-ui.checkbox wire:model.live="use_business_hours" x-model="useBusinessHours" :label="__('Use business hours')" />
                            </div>

                            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                                <x-ui.select wire:model="slot_interval" name="slot_interval" :label="__('Slot interval')" :error="$errors->first('slot_interval')">
                                    <option value="15">{{ __('15 minutes') }}</option>
                                    <option value="30">{{ __('30 minutes') }}</option>
                                    <option value="60">{{ __('60 minutes') }}</option>
                                </x-ui.select>
                                <x-ui.select wire:model="buffer_before" name="buffer_before" :label="__('Buffer before')" :error="$errors->first('buffer_before')">
                                    @foreach ([0, 5, 10, 15, 30] as $minutes)
                                        <option value="{{ $minutes }}">{{ $minutes }} {{ __('min') }}</option>
                                    @endforeach
                                </x-ui.select>
                                <x-ui.select wire:model="buffer_after" name="buffer_after" :label="__('Buffer after')" :error="$errors->first('buffer_after')">
                                    @foreach ([0, 5, 10, 15, 30] as $minutes)
                                        <option value="{{ $minutes }}">{{ $minutes }} {{ __('min') }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>

                            <div x-cloak x-show="! useBusinessHours" class="mt-4 space-y-2">
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
                                        x-data="{ closed: @entangle('service_hours.'.$day.'.is_closed').live }"
                                        class="grid gap-3 rounded-xl border p-3 transition sm:grid-cols-[1fr_8rem_8rem]"
                                        x-bind:style="closed
                                            ? 'border-color: rgba(var(--theme-border-color-rgb), .46); background-color: color-mix(in srgb, var(--theme-surface-soft) 88%, transparent);'
                                            : 'border-color: rgba(var(--theme-border-color-rgb), .52); background-color: var(--theme-surface-overlay);'"
                                    >
                                        <div class="flex items-center justify-between gap-3">
                                            <x-ui.checkbox wire:model.live="service_hours.{{ $day }}.is_closed" :label="$label.' '.__('closed')" />
                                            <span x-show="closed" class="rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]" style="border-color: rgba(var(--theme-border-color-rgb), .58); color: var(--theme-muted-text-color); background-color: var(--theme-surface-overlay);">{{ __('Closed') }}</span>
                                        </div>
                                        <div x-bind:class="closed ? 'pointer-events-none opacity-45' : ''">
                                            <x-ui.time-picker
                                                wire:model="service_hours.{{ $day }}.open_time"
                                                name="service_hours_{{ $day }}_open_time"
                                                :label="__('Open')"
                                                :value="$service_hours[$day]['open_time'] ?? '09:00'"
                                                picker-align="auto"
                                                picker-position="auto"
                                                :error="$errors->first('service_hours.'.$day.'.open_time')"
                                            />
                                        </div>
                                        <div x-bind:class="closed ? 'pointer-events-none opacity-45' : ''">
                                            <x-ui.time-picker
                                                wire:model="service_hours.{{ $day }}.close_time"
                                                name="service_hours_{{ $day }}_close_time"
                                                :label="__('Close')"
                                                :value="$service_hours[$day]['close_time'] ?? '18:00'"
                                                picker-align="auto"
                                                picker-position="auto"
                                                :error="$errors->first('service_hours.'.$day.'.close_time')"
                                            />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    </div>

                    <div class="flex justify-end gap-3 border-t px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <x-ui.button type="button" variant="outline" x-on:click="serviceDialogOpen = false">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="submit">
                            <span wire:loading.remove wire:target="saveService"><i class="fa-light fa-floppy-disk"></i>{{ $editing_service_id ? __('Update booking service') : __('Save booking service') }}</span>
                            <span wire:loading wire:target="saveService"><i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Saving...') }}</span>
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-cloak x-show="pageDialogOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="pageDialogOpen = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="pageDialogOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <form wire:submit="saveBookingPage" x-show="pageDialogOpen" x-transition.opacity.scale.95 class="relative w-full max-w-2xl overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.72); background-color: var(--theme-surface-overlay);">
                    <div class="flex items-start justify-between gap-4 border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <div>
                            <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Create public booking page') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Publish a booking link with landing page design and QR code.') }}</p>
                        </div>
                        <button type="button" x-on:click="pageDialogOpen = false" class="flex h-10 w-10 items-center justify-center rounded-xl" style="color: var(--theme-muted-text-color);">
                            <i class="fa-light fa-xmark"></i>
                        </button>
                    </div>

                    <div class="grid gap-4 p-5">
                        <x-ui.select wire:model="business_id" name="booking_page_business_id" :label="__('Business')" :error="$errors->first('business_id')">
                            <option value="">{{ __('Select business') }}</option>
                            @foreach ($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.input wire:model="page_name" name="page_name" :label="__('Page name')" :placeholder="__('Weekend spa booking page')" :error="$errors->first('page_name')" />
                        <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb), .10); color: var(--theme-accent);">
                                    <i class="fa-light fa-palette"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Design after create') }}</p>
                                    <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('A default booking page and QR code are created now. You will go to Landing Pages next to edit template, colors, copy, and cover image.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <x-ui.button type="button" variant="outline" x-on:click="pageDialogOpen = false">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="submit">
                            <span wire:loading.remove wire:target="saveBookingPage"><i class="fa-light fa-floppy-disk"></i>{{ __('Create and edit design') }}</span>
                            <span wire:loading wire:target="saveBookingPage"><i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Creating...') }}</span>
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
