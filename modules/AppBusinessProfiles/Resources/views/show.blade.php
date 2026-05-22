<div
    x-data="{ businessQrDesignOpen: false }"
    x-on:business-qr-design-saved.window="businessQrDesignOpen = false"
    class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6"
>
    <section class="relative overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.10), transparent 34%),
        linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 99%, transparent), color-mix(in srgb, var(--theme-surface-base) 94%, transparent));">
        <div class="grid gap-6 px-5 py-5 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,22rem)] lg:items-end sm:px-6 xl:px-7">
            <div class="min-w-0">
                <a href="{{ route('portal.businesses') }}" wire:navigate class="inline-flex items-center gap-2 text-xs font-semibold transition hover:opacity-80" style="color: var(--theme-muted-text-color);">
                    <i class="fa-light fa-arrow-left"></i>{{ __('Businesses') }}
                </a>

                <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl border text-xl font-semibold uppercase shadow-[0_18px_36px_-30px_rgba(var(--theme-accent-rgb),0.9)]" style="border-color: rgba(var(--theme-accent-rgb), 0.24); background-color: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent);">
                        {{ str($business->name)->substr(0, 2)->upper() }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em]" style="border-color: rgba(var(--theme-success-color-rgb),0.28); background-color: rgba(var(--theme-success-color-rgb),0.10); color: var(--theme-success-color);">
                                <span class="h-1.5 w-1.5 rounded-full" style="background-color: var(--theme-success-color);"></span>{{ __('Active') }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em]" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 82%, transparent); color: var(--theme-muted-text-color);">
                                <i class="fa-light fa-briefcase text-[10px]"></i>{{ str($business->type)->headline() }}
                            </span>
                        </div>
                        <h1 class="mt-3 truncate text-[2rem] font-semibold leading-none tracking-[-0.045em] sm:text-[2.55rem]" style="color: var(--theme-header-text-color);">{{ $business->name }}</h1>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if ($business->phone)
                                <span class="inline-flex max-w-full items-center gap-2 rounded-lg border px-3 py-2 text-xs font-medium" style="border-color: rgba(var(--theme-border-color-rgb),0.52); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent);"><i class="fa-light fa-phone shrink-0"></i><span class="truncate">{{ $business->phone }}</span></span>
                            @endif
                            @if ($business->email)
                                <span class="inline-flex max-w-full items-center gap-2 rounded-lg border px-3 py-2 text-xs font-medium" style="border-color: rgba(var(--theme-border-color-rgb),0.52); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent);"><i class="fa-light fa-envelope shrink-0"></i><span class="truncate">{{ $business->email }}</span></span>
                            @endif
                            @if ($business->website)
                                <a href="{{ $business->website }}" target="_blank" class="inline-flex max-w-full items-center gap-2 rounded-lg border px-3 py-2 text-xs font-medium transition hover:border-[color:rgba(var(--theme-accent-rgb),0.34)] hover:text-[var(--theme-accent)]" style="border-color: rgba(var(--theme-border-color-rgb),0.52); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent);"><i class="fa-light fa-globe shrink-0"></i><span class="truncate">{{ parse_url($business->website, PHP_URL_HOST) ?: $business->website }}</span></a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
                    <x-ui.button href="{{ route('portal.businesses.edit', $business) }}" wire:navigate size="lg">
                        <i class="fa-light fa-pen"></i>{{ __('Edit profile') }}
                    </x-ui.button>
                    <x-ui.button href="{{ route('portal.businesses.campaigns.index', $business) }}" wire:navigate variant="secondary" size="lg">
                        <i class="fa-light fa-qrcode"></i>{{ __('Create campaign') }}
                    </x-ui.button>
                    <x-ui.button type="button" variant="secondary" size="lg" x-on:click="businessQrDesignOpen = true">
                        <i class="fa-light fa-sliders"></i>{{ __('Design business QR') }}
                    </x-ui.button>
                    <x-ui.button href="{{ route('portal.businesses.qr.svg', $business) }}" variant="secondary" size="lg">
                        <i class="fa-light fa-download"></i>{{ __('Download business QR') }}
                    </x-ui.button>
                </div>
                <div class="rounded-xl border px-3 py-3 text-xs leading-5" style="border-color: rgba(var(--theme-accent-rgb),0.12); background-color: rgba(var(--theme-accent-rgb),0.06); color: var(--theme-muted-text-color);">
                    {{ __('Use this profile as the source for review, booking, coupon, feedback, and lead capture flows.') }}
                </div>
            </div>
        </div>
    </section>

    @include('appbusinessprofiles::partials.business-tabs', ['business' => $business, 'active' => 'overview'])

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['label' => __('Locations'), 'value' => $metrics['locations'], 'description' => __('Branches and service areas'), 'icon' => 'fa-light fa-location-dot', 'tone' => 'accent'],
            ['label' => __('Campaigns'), 'value' => $metrics['campaigns'], 'description' => __('Growth tools launched'), 'icon' => 'fa-light fa-bullhorn', 'tone' => 'accent'],
            ['label' => __('QR scans'), 'value' => $metrics['scans'], 'description' => __('Tracked visits'), 'icon' => 'fa-light fa-qrcode', 'tone' => 'success'],
            ['label' => __('Bookings'), 'value' => $metrics['bookings'], 'description' => __('Appointment requests'), 'icon' => 'fa-light fa-calendar-check', 'tone' => 'success'],
            ['label' => __('Coupons'), 'value' => $metrics['coupons'], 'description' => __('Offer claims'), 'icon' => 'fa-light fa-ticket', 'tone' => 'warning'],
        ] as $metric)
            @php
                $toneColor = match ($metric['tone']) {
                    'success' => 'var(--theme-success-color)',
                    'warning' => 'var(--theme-warning-color)',
                    default => 'var(--theme-accent)',
                };
                $toneRgb = match ($metric['tone']) {
                    'success' => 'var(--theme-success-color-rgb)',
                    'warning' => 'var(--theme-warning-color-rgb)',
                    default => 'var(--theme-accent-rgb)',
                };
            @endphp
            <article class="relative overflow-hidden rounded-[1.1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <span class="absolute inset-x-0 top-0 h-1" style="background-color: {{ $toneColor }};"></span>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[2rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ number_format($metric['value']) }}</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $metric['description'] }}</p>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba({{ $toneRgb }},0.12); color: {{ $toneColor }};">
                        <i class="{{ $metric['icon'] }}"></i>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="grid gap-5 xl:grid-cols-[24rem_minmax(0,1fr)]">
        <aside class="space-y-5">
            <div class="overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <div class="flex items-center justify-between gap-3 border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.58);">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Profile details') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Shown on public QR pages') }}</p>
                    </div>
                    <x-ui.badge>{{ __('Public') }}</x-ui.badge>
                </div>
                <div class="space-y-2 p-3">
                    @foreach ([
                        ['icon' => 'fa-light fa-phone', 'label' => __('Phone'), 'value' => $business->phone, 'href' => $business->phone ? 'tel:'.$business->phone : null],
                        ['icon' => 'fa-light fa-envelope', 'label' => __('Email'), 'value' => $business->email, 'href' => $business->email ? 'mailto:'.$business->email : null],
                        ['icon' => 'fa-light fa-globe', 'label' => __('Website'), 'value' => $business->website ? (parse_url($business->website, PHP_URL_HOST) ?: $business->website) : null, 'href' => $business->website ?: null],
                        ['icon' => 'fa-light fa-location-dot', 'label' => __('Address'), 'value' => $business->address, 'href' => $business->google_maps_url ?: null],
                    ] as $detail)
                        @php($hasValue = filled($detail['value']))
                        <div class="group relative flex items-center gap-3 rounded-2xl border px-3 py-3 transition hover:-translate-y-0.5 hover:shadow-[0_18px_45px_-34px_rgba(var(--theme-accent-rgb),0.75)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border" style="border-color: {{ $hasValue ? 'rgba(var(--theme-accent-rgb),0.16)' : 'rgba(var(--theme-border-color-rgb),0.48)' }}; background: {{ $hasValue ? 'linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.14), rgba(var(--theme-success-color-rgb),0.08))' : 'rgba(var(--theme-border-color-rgb),0.12)' }}; color: {{ $hasValue ? 'var(--theme-accent)' : 'var(--theme-muted-text-color)' }};">
                                <i class="{{ $detail['icon'] }}"></i>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-[10px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ $detail['label'] }}</span>
                                @if ($hasValue && $detail['href'])
                                    <a href="{{ $detail['href'] }}" target="{{ str_starts_with((string) $detail['href'], 'http') ? '_blank' : '_self' }}" class="mt-1 block truncate text-sm font-semibold" style="color: var(--theme-header-text-color);" title="{{ $detail['href'] }}">{{ $detail['value'] }}</a>
                                @else
                                    <span class="mt-1 block truncate text-sm font-semibold" style="color: {{ $hasValue ? 'var(--theme-header-text-color)' : 'var(--theme-muted-text-color)' }};">{{ $detail['value'] ?: __('Not set') }}</span>
                                @endif
                            </span>
                            @if ($hasValue && $detail['href'])
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl opacity-80 transition group-hover:opacity-100" style="background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                                    <i class="fa-light fa-arrow-up-right text-xs"></i>
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[1.25rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <p class="px-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Next actions') }}</p>
                <div class="mt-4 grid gap-3">
                    @foreach ([
                        ['route' => route('portal.businesses.locations', $business), 'icon' => 'fa-light fa-location-dot', 'label' => __('Manage locations'), 'hint' => __('Branches and service areas')],
                        ['route' => route('portal.businesses.customers.index', $business), 'icon' => 'fa-light fa-address-book', 'label' => __('View customers'), 'hint' => __('Contacts and campaign leads')],
                        ['route' => route('portal.review-booster'), 'icon' => 'fa-light fa-star', 'label' => __('Launch review booster'), 'hint' => __('Grow Google reviews')],
                    ] as $action)
                        <a href="{{ $action['route'] }}" wire:navigate class="group flex items-center gap-3 rounded-2xl border p-3 transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); color: var(--theme-header-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 92%, transparent);">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="{{ $action['icon'] }}"></i></span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold">{{ $action['label'] }}</span>
                                <span class="mt-0.5 block truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $action['hint'] }}</span>
                            </span>
                            <i class="fa-light fa-arrow-right text-xs opacity-0 transition group-hover:opacity-100" style="color: var(--theme-accent);"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        </aside>

        <div id="campaigns" class="overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
            <div class="flex flex-col gap-3 border-b px-5 py-4 md:flex-row md:items-center md:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Campaign workspace') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Recent QR funnels connected to this business.') }}</p>
                </div>
                <x-ui.button href="{{ route('portal.businesses.campaigns.index', $business) }}" wire:navigate size="sm">
                    <i class="fa-light fa-plus"></i>{{ __('New campaign') }}
                </x-ui.button>
            </div>
            <div class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                @forelse ($campaigns as $campaign)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[minmax(0,1fr)_8rem_8rem] md:items-center">
                        <div>
                            <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $campaign->name }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ str($campaign->type)->headline() }}</p>
                        </div>
                        <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ number_format($campaign->scans_count) }} {{ __('scans') }}</p>
                        <a href="{{ $campaign->publicUrl() }}" target="_blank" class="text-sm font-semibold" style="color: var(--theme-accent);">{{ __('Open') }}</a>
                    </div>
                @empty
                    <div class="p-6">
                        <div class="grid gap-6 rounded-[1.1rem] border p-6 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-center" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background:
                            linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.08), transparent 44%),
                            color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                            <div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-accent-rgb), 0.18); background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                                    <i class="fa-light fa-bullhorn text-xl"></i>
                                </div>
                                <h2 class="mt-5 text-xl font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">{{ __('No campaigns yet') }}</h2>
                                <p class="mt-3 max-w-xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Create a QR campaign, review booster, booking page, coupon, feedback form, or lead form for this business.') }}</p>
                            </div>
                            <div class="grid gap-2">
                                @foreach ([__('Review booster'), __('Booking page'), __('Coupon campaign')] as $label)
                                    <div class="rounded-xl border px-3 py-2 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); color: var(--theme-header-text-color); background-color: color-mix(in srgb, var(--theme-surface-overlay) 82%, transparent);">{{ $label }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <div
        x-cloak
        x-show="businessQrDesignOpen"
        x-transition.opacity
        class="fixed inset-0 z-[90] flex items-center justify-center bg-black/45 px-4 py-6"
        x-on:keydown.escape.window="businessQrDesignOpen = false"
    >
        <div class="absolute inset-0" x-on:click="businessQrDesignOpen = false"></div>
        <section
            x-show="businessQrDesignOpen"
            x-transition
            class="relative flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-[1.35rem] border shadow-2xl"
            style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: var(--theme-surface-overlay);"
        >
            <div class="flex items-start justify-between gap-4 border-b px-5 py-4 sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                <div>
                    <h3 class="text-[1.05rem] font-semibold" style="color: var(--theme-header-text-color);">{{ __('Design business QR code') }}</h3>
                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Customize the default QR print style for this business and its QR campaigns.') }}</p>
                </div>
                <button type="button" class="transition" style="color: var(--theme-muted-text-color);" x-on:click="businessQrDesignOpen = false">
                    <i class="fa-light fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 py-4 sm:px-6 sm:py-5">
                <form id="business-qr-design-form" wire:submit="saveQrDesign" class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_19rem]">
                    <div class="space-y-5">
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Template') }}</label>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach ($qrStyleTemplates as $key => $template)
                                    <button type="button" wire:click="applyQrTemplate('{{ $key }}')" class="rounded-xl border p-3 text-left transition hover:-translate-y-0.5" style="border-color: {{ ($qrDesign['template'] ?? '') === $key ? 'rgba(var(--theme-accent-rgb), .42)' : 'rgba(var(--theme-border-color-rgb), .62)' }}; background-color: {{ ($qrDesign['template'] ?? '') === $key ? 'rgba(var(--theme-accent-rgb), .08)' : 'var(--theme-surface-base)' }};">
                                        <span class="flex items-center gap-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">
                                            <i class="fa-light {{ ($qrDesign['template'] ?? '') === $key ? 'fa-check' : 'fa-qrcode' }} text-xs" style="color: {{ $template['foreground'] }};"></i>{{ $template['label'] }}
                                        </span>
                                        <span class="mt-3 flex gap-1">
                                            @foreach ([$template['foreground'], $template['accent'], $template['surface']] as $color)
                                                <span class="h-5 flex-1 rounded-md" style="background-color: {{ $color }};"></span>
                                            @endforeach
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input label="{{ __('Label') }}" wire:model.live="qrDesign.label" />
                            <x-ui.select label="{{ __('Frame style') }}" wire:model.live="qrDesign.frame_style">
                                <option value="card">{{ __('Card') }}</option>
                                <option value="label">{{ __('Label') }}</option>
                                <option value="minimal">{{ __('Minimal QR only') }}</option>
                            </x-ui.select>
                            <x-ui.color-picker
                                :label="__('Foreground')"
                                wire:model.live="qrDesign.foreground_color"
                                :value="$qrDesign['foreground_color'] ?? '#0f766e'"
                                :error="$errors->first('qrDesign.foreground_color')"
                                :presets="['#0f172a', '#0f766e', '#15803d', '#0f4f87', '#334155', '#ffffff', '#14b8a6', '#f97316']"
                            />
                            <x-ui.color-picker
                                :label="__('Background')"
                                wire:model.live="qrDesign.background_color"
                                :value="$qrDesign['background_color'] ?? '#ffffff'"
                                :error="$errors->first('qrDesign.background_color')"
                                :presets="['#ffffff', '#f8fafc', '#ecfeff', '#eff6ff', '#f0fdf4', '#ecfdf5', '#e2e8f0', '#0f172a']"
                            />
                            <x-ui.color-picker
                                :label="__('Accent')"
                                wire:model.live="qrDesign.accent_color"
                                :value="$qrDesign['accent_color'] ?? '#14b8a6'"
                                :error="$errors->first('qrDesign.accent_color')"
                                :presets="['#14b8a6', '#0f766e', '#16a34a', '#65a30d', '#f97316', '#0f4f87', '#64748b', '#dc2626']"
                            />
                            <x-ui.color-picker
                                :label="__('Surface')"
                                wire:model.live="qrDesign.surface_color"
                                :value="$qrDesign['surface_color'] ?? '#ecfeff'"
                                :error="$errors->first('qrDesign.surface_color')"
                                :presets="['#ecfeff', '#f8fafc', '#eff6ff', '#f0fdf4', '#ecfdf5', '#dcfce7', '#e2e8f0', '#ffffff']"
                            />
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <x-ui.checkbox
                                id="business-qr-show-business-name"
                                wire:click="toggleQrDesignFlag('show_business_name')"
                                :checked="(bool) ($qrDesign['show_business_name'] ?? true)"
                                :label="__('Show business name')"
                                class="rounded-xl border border-[color:rgba(var(--theme-border-color-rgb),.58)] bg-[var(--theme-surface-base)] px-4 py-3 text-sm font-semibold"
                            />
                            <x-ui.checkbox
                                id="business-qr-show-address"
                                wire:click="toggleQrDesignFlag('show_address')"
                                :checked="(bool) ($qrDesign['show_address'] ?? true)"
                                :label="__('Show address')"
                                class="rounded-xl border border-[color:rgba(var(--theme-border-color-rgb),.58)] bg-[var(--theme-surface-base)] px-4 py-3 text-sm font-semibold"
                            />
                        </div>

                        <x-ui.card padding="none" class="overflow-hidden" x-data>
                            <div class="flex items-center justify-between gap-4 border-b px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb), .10); color: var(--theme-accent);">
                                        <i class="fa-light fa-image"></i>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Brand image') }}</span>
                                        <span class="mt-0.5 block truncate text-xs" style="color: var(--theme-muted-text-color);">{{ __('Optional logo for QR center and mosaic direction.') }}</span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-semibold" style="color: var(--theme-accent);">
                                        {{ filled($qrDesign['logo_path'] ?? null) || $qrLogoUpload ? __('Selected') : __('Optional') }}
                                    </span>
                                    <x-ui.switch
                                        wire:click="toggleQrDesignFlag('logo_enabled')"
                                        :checked="(bool) ($qrDesign['logo_enabled'] ?? true)"
                                        :label="__('Show')"
                                    />
                                </div>
                            </div>

                            <div class="p-4">
                                <x-ui.field :label="__('Brand image source')" :error="$errors->first('qrLogoUpload')">
                                <div class="rounded-2xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: var(--theme-surface-overlay);">
                                    <div class="flex flex-wrap items-center gap-4">
                                        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: var(--theme-surface-base);">
                                            @if ($logoPreviewSrc = $this->qrLogoPreviewSrc())
                                                <img src="{{ $logoPreviewSrc }}" alt="{{ __('Brand image') }}" class="h-full w-full object-cover">
                                            @else
                                                <i class="fa-light fa-image text-lg" style="color: var(--theme-muted-text-color);"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Brand image source') }}</p>
                                            <div class="mt-2 flex flex-wrap items-center gap-3">
                                                <input x-ref="qrLogoInput" type="file" wire:model="qrLogoUpload" accept="image/png,image/jpeg,image/webp,image/gif" class="sr-only">
                                                <x-ui.button type="button" variant="outline" x-on:click="$refs.qrLogoInput.click()">
                                                    {{ __('Choose image') }}
                                                </x-ui.button>
                                                @if (filled($qrDesign['logo_path'] ?? null) || $qrLogoUpload)
                                                    <x-ui.button type="button" variant="ghost" wire:click="removeQrLogo">
                                                        {{ __('Remove image') }}
                                                    </x-ui.button>
                                                @endif
                                                <span wire:loading wire:target="qrLogoUpload" class="inline-flex items-center gap-2 text-xs font-semibold" style="color: var(--theme-muted-text-color);">
                                                    <i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Uploading...') }}
                                                </span>
                                            </div>
                                            @if ($qrLogoUpload)
                                                <p class="mt-2 text-xs font-semibold" style="color: var(--theme-success-color);">{{ __('Logo ready. Save to apply.') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                </x-ui.field>
                            </div>
                        </x-ui.card>
                    </div>

                    <aside class="rounded-[1.15rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: var(--theme-surface-base);">
                        <div class="rounded-xl border bg-white p-3 [&>svg]:mx-auto [&>svg]:h-auto [&>svg]:w-full [&>svg]:max-w-[17rem]" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            {!! app(\Modules\AppBusinessProfiles\Support\BusinessQrRenderer::class)->render($business, $this->qrPreviewDesign()) !!}
                        </div>
                        <a href="{{ route('portal.businesses.qr.svg', $business) }}" class="mt-4 inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border px-4 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);">
                            <i class="fa-light fa-download"></i>{{ __('Download SVG') }}
                        </a>
                    </aside>
                </form>
            </div>

            <div class="flex items-center justify-end gap-3 border-t px-5 py-4 sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                <x-ui.button type="button" variant="outline" x-on:click="businessQrDesignOpen = false">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button type="submit" form="business-qr-design-form" wire:loading.attr="disabled" wire:target="saveQrDesign">
                    <span wire:loading.remove wire:target="saveQrDesign" class="inline-flex items-center gap-2"><i class="fa-light fa-floppy-disk"></i>{{ __('Save QR design') }}</span>
                    <span wire:loading wire:target="saveQrDesign" class="inline-flex items-center gap-2"><i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Saving...') }}</span>
                </x-ui.button>
            </div>
        </section>
    </div>
</div>
