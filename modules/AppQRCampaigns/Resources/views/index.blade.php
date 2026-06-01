<div
    class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6"
    x-data="{ qrDialogOpen: false, deleteDialogOpen: false, deleteTargetId: null, deleteTargetName: '' }"
    x-on:qr-code-saved.window="qrDialogOpen = false"
>
    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 34%),
        linear-gradient(35deg, rgba(var(--theme-success-color-rgb),0.08), transparent 38%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-7 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-qrcode"></i>
                    {{ __('QR Codes') }}
                </div>
                <h1 class="mt-4 max-w-3xl text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('QR Asset Library') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ __('Manage QR assets generated from review boosters, booking pages, coupons, feedback forms, lead forms, landing pages, and custom links.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button type="button" size="lg" x-on:click="qrDialogOpen = true">
                        <i class="fa-light fa-plus"></i>{{ __('Create Custom QR') }}
                    </x-ui.button>
                </div>
            </div>

            <div class="rounded-[1.2rem] border p-4 shadow-[0_24px_70px_-48px_rgba(var(--theme-border-color-rgb),0.9)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('QR coverage') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Offline entry points ready to track') }}</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-grid-2"></i>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ format_number_locale((int) $summary['total']) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('QR Codes') }}</p>
                    </div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ format_number_locale((int) $summary['scans']) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Scans') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => __('Total QR Codes'), 'value' => $summary['total'], 'description' => __('Assets across growth tools'), 'icon' => 'fa-light fa-qrcode', 'tone' => 'accent'],
            ['label' => __('Campaign QR'), 'value' => $summary['campaign'], 'description' => __('Generated from campaign pages'), 'icon' => 'fa-light fa-bullhorn', 'tone' => 'success'],
            ['label' => __('Custom QR'), 'value' => $summary['custom'], 'description' => __('Manual destination links'), 'icon' => 'fa-light fa-link', 'tone' => 'warning'],
            ['label' => __('Total Scans'), 'value' => $summary['scans'], 'description' => __('Tracked offline entry points'), 'icon' => 'fa-light fa-chart-line', 'tone' => 'accent'],
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
            <article class="relative overflow-hidden rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background: linear-gradient(145deg, rgba({{ $toneRgb }},0.07), transparent 44%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <span class="absolute inset-x-0 top-0 h-1" style="background-color: var(--theme-warning-color);"></span>
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[1.75rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ format_number_locale((int) $metric['value']) }}</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $metric['description'] }}</p>
                    </div>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background-color: rgba({{ $toneRgb }},0.12); color: {{ $toneColor }};">
                        <i class="{{ $metric['icon'] }}"></i>
                    </span>
                </div>
            </article>
        @endforeach
    </section>

    <section>
        <div class="overflow-visible rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
            <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('QR Asset Library') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Create, preview, copy, download, and track QR entry points from every local growth workflow.') }}</p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_12rem_12rem_8rem_auto] xl:w-[62rem]">
                        <x-ui.input wire:model.live.debounce.300ms="search" name="qr_search" placeholder="{{ __('Search QR or destination...') }}" />
                        <x-ui.select wire:model.live="businessFilter" name="qr_business_filter">
                            <option value="">{{ __('All businesses') }}</option>
                            @foreach ($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.select wire:model.live="sourceFilter" name="qr_source_filter">
                            <option value="">{{ __('All sources') }}</option>
                            @foreach ($sourceOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.select wire:model.live="perPage" name="qr_per_page">
                            <option value="10">{{ __('10 / page') }}</option>
                            <option value="25">{{ __('25 / page') }}</option>
                            <option value="50">{{ __('50 / page') }}</option>
                        </x-ui.select>
                        <x-ui.button type="button" variant="outline" class="justify-center" x-on:click="qrDialogOpen = true">
                            <i class="fa-light fa-plus"></i>{{ __('Custom QR') }}
                        </x-ui.button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                    <thead>
                        <tr class="text-left text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">
                            <th class="px-5 py-3">{{ __('QR Name') }}</th>
                            <th class="px-5 py-3">{{ __('Business') }}</th>
                            <th class="px-5 py-3">{{ __('Source') }}</th>
                            <th class="px-5 py-3">{{ __('Scans') }}</th>
                            <th class="px-5 py-3">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        @forelse ($campaigns as $campaign)
                            @php
                                $sourceKey = data_get($campaign->settings, 'source') === 'landing_page' ? 'landing_page' : $campaign->type;
                                $sourceLabel = $sourceOptions[$sourceKey] ?? str($sourceKey)->headline();
                                $createdFrom = match ((string) data_get($campaign->settings, 'source', '')) {
                                    'manual' => __('Manual'),
                                    'landing_page' => __('Landing Page'),
                                    'template' => __('Template'),
                                    default => $campaign->type === 'url' ? __('Manual') : __('Growth Tool'),
                                };
                                $publicUrl = $campaign->publicUrl();
                                $destination = $campaign->destination_url ?: $publicUrl;
                                $statusLabel = $campaign->published_at ? __('Active') : __('Paused');
                            @endphp
                            <tr class="align-middle">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-white p-1 [&>svg]:h-full [&>svg]:w-full" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                                            {!! $campaign->qr_svg !!}
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ $publicUrl }}" target="_blank" class="block truncate font-semibold hover:underline" style="color: var(--theme-header-text-color);">{{ $campaign->name }}</a>
                                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Created from: :source', ['source' => $createdFrom]) }} - {{ $campaign->created_at?->format('Y-m-d') }}</p>
                                            <a href="{{ $destination }}" target="_blank" class="mt-1 block max-w-[28rem] truncate text-xs font-medium hover:underline" style="color: var(--theme-accent);">{{ $destination }}</a>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-sm" style="color: var(--theme-header-text-color);">{{ $campaign->business?->name ?: __('No business') }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .22); background: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">
                                        {{ $sourceLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale((int) $campaign->scans_count) }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('visits') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em]" style="border-color: rgba(var(--theme-success-color-rgb), .28); background: rgba(var(--theme-success-color-rgb), .08); color: var(--theme-success-color);">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ $publicUrl }}" target="_blank" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border transition hover:opacity-80" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color);" title="{{ __('Open') }}">
                                            <i class="fa-light fa-arrow-up-right"></i>
                                        </a>
                                        <button type="button" onclick="navigator.clipboard && navigator.clipboard.writeText(this.dataset.copy || '')" data-copy="{{ e($publicUrl) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border transition hover:opacity-80" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color);" title="{{ __('Copy Link') }}">
                                            <i class="fa-light fa-copy"></i>
                                        </button>
                                        <x-ui.dropdown-menu align="right" width="auto">
                                            <x-slot:trigger>
                                                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border transition hover:opacity-80" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color);" title="{{ __('Download QR') }}">
                                                    <i class="fa-light fa-download"></i>
                                                </button>
                                            </x-slot:trigger>
                                            <x-ui.dropdown-menu-item :href="route('qr-campaigns.png', ['campaign' => $campaign->slug])" icon="fa-light fa-file-image">
                                                {{ __('Download PNG') }}
                                            </x-ui.dropdown-menu-item>
                                            <x-ui.dropdown-menu-item :href="route('qr-campaigns.svg', ['campaign' => $campaign->slug])" icon="fa-light fa-code">
                                                {{ __('Download SVG') }}
                                            </x-ui.dropdown-menu-item>
                                        </x-ui.dropdown-menu>

                                        <x-ui.dropdown-menu align="right" width="auto">
                                            <x-slot:trigger>
                                                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border transition hover:opacity-80" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-muted-text-color);" title="{{ __('More actions') }}">
                                                    <i class="fa-light fa-ellipsis"></i>
                                                </button>
                                            </x-slot:trigger>
                                            @if (\Illuminate\Support\Facades\Route::has('portal.qr-codes.analytics'))
                                                <x-ui.dropdown-menu-item :href="route('portal.qr-codes.analytics', ['campaign' => $campaign->slug])" icon="fa-light fa-chart-line" wire:navigate>
                                                    {{ __('View Analytics') }}
                                                </x-ui.dropdown-menu-item>
                                            @endif
                                            <button
                                                type="button"
                                                class="flex w-full items-center gap-3 whitespace-nowrap rounded-[0.8rem] px-3 py-2 text-left text-[15px] font-medium leading-6 transition hover:bg-[color:rgba(var(--theme-danger-color-rgb),0.08)]"
                                                style="color: var(--theme-danger-color);"
                                                x-on:click.stop="deleteTargetId = {{ $campaign->id }}; deleteTargetName = @js($campaign->name); deleteDialogOpen = true; open = false"
                                            >
                                                <i class="fa-light fa-trash text-[14px]"></i>
                                                <span class="min-w-0 flex-1 truncate">{{ __('Delete') }}</span>
                                            </button>
                                        </x-ui.dropdown-menu>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8">
                                    <x-ui.empty icon="fa-light fa-qrcode" :title="__('No QR codes found')" :description="__('QR assets generated from growth tools and custom links will appear here for preview, sharing, downloads, and tracking.')" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($campaigns->count() > 0)
                <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    <p class="text-sm" style="color: var(--theme-muted-text-color);">
                        {{ __('Showing') }}
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($campaigns->firstItem()) }}</span>
                        -
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($campaigns->lastItem()) }}</span>
                        {{ __('of') }}
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($campaigns->total()) }}</span>
                        {{ __('QR codes') }}
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="previousPage" @disabled($campaigns->onFirstPage()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">
                            <i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}
                        </button>
                        <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">
                            {{ __('Page') }} {{ format_number_locale($campaigns->currentPage()) }} / {{ format_number_locale($campaigns->lastPage()) }}
                        </span>
                        <button type="button" wire:click="nextPage" @disabled(! $campaigns->hasMorePages()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">
                            {{ __('Next') }}<i class="fa-light fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <template x-teleport="body">
        <div x-cloak x-show="qrDialogOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="qrDialogOpen = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="qrDialogOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <div x-show="qrDialogOpen" x-transition.opacity.scale.90 class="relative w-full max-w-4xl">
                    <form wire:submit="save" class="relative flex max-h-[calc(100vh-3.5rem)] min-h-0 flex-col overflow-hidden rounded-[1.15rem] border shadow-[0_32px_80px_-34px_rgba(15,23,42,0.32)]" style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent); background-color: var(--theme-surface-overlay);">
                        <div class="shrink-0 flex items-start justify-between gap-4 border-b px-5 py-4 sm:px-6 sm:py-5" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent);">
                            <div>
                                <h3 class="text-[1.05rem] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ __('Create Custom QR') }}</h3>
                                <p class="mt-2 text-[15px] leading-7" style="color: var(--theme-muted-text-color);">{{ __('Use custom QR codes for one-off URLs. Campaign, review, booking, coupon, feedback, lead, and landing page QR assets are usually generated automatically.') }}</p>
                            </div>
                            <button type="button" class="transition" style="color: var(--theme-muted-text-color);" x-on:click="qrDialogOpen = false">
                                <i class="fa-light fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 py-4 sm:px-6 sm:py-5">
                            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
                                <div class="space-y-5">
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <x-ui.select wire:model="business_id" name="business_id" :label="__('Business')" :error="$errors->first('business_id')">
                                            <option value="">{{ __('Select business') }}</option>
                                            @foreach ($businesses as $business)
                                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                                            @endforeach
                                        </x-ui.select>
                                        <x-ui.input wire:model="name" name="name" :label="__('QR name')" :error="$errors->first('name')" />
                                        <x-ui.select wire:model="type" name="type" :label="__('QR type')" :error="$errors->first('type')">
                                            @foreach ($typeOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </x-ui.select>
                                        <x-ui.input wire:model="cta_text" name="cta_text" :label="__('Call to action text')" :error="$errors->first('cta_text')" />
                                    </div>

                                    <x-ui.input wire:model="destination_url" name="destination_url" :label="__('Destination URL')" placeholder="https://example.com" :error="$errors->first('destination_url')" />
                                </div>

                                <aside class="rounded-2xl border p-4 lg:sticky lg:top-0" style="border-color: rgba(var(--theme-border-color-rgb), .55); background:
                                    linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.08), transparent 42%),
                                    color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('QR asset role') }}</p>
                                    <div class="mt-4 space-y-3">
                                        @foreach ([__('Share offline touchpoints'), __('Track scans as visits'), __('Route customers to campaigns'), __('Keep manual links separate')] as $item)
                                            <div class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold" style="background-color: color-mix(in srgb, var(--theme-surface-soft) 78%, transparent); color: var(--theme-header-text-color);">
                                                <span class="flex h-7 w-7 items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-success-color-rgb),0.12); color: var(--theme-success-color);">
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
                                <x-ui.button type="button" variant="outline" x-on:click="qrDialogOpen = false">{{ __('Cancel') }}</x-ui.button>
                                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="save">
                                    <span wire:loading.remove wire:target="save" class="inline-flex items-center gap-2">
                                        <i class="fa-light fa-plus"></i>{{ __('Create QR code') }}
                                    </span>
                                    <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                                        <i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Creating...') }}
                                    </span>
                                </x-ui.button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-cloak x-show="deleteDialogOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="deleteDialogOpen = false; deleteTargetId = null; deleteTargetName = ''">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="deleteDialogOpen = false; deleteTargetId = null; deleteTargetName = ''"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <div x-show="deleteDialogOpen" x-transition.opacity.scale.90 class="relative w-full max-w-[26rem]">
                    <div class="relative flex max-h-[calc(100vh-3.5rem)] min-h-0 flex-col overflow-hidden rounded-[1.15rem] border shadow-[0_32px_80px_-34px_rgba(15,23,42,0.32)]" style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent); background-color: var(--theme-surface-overlay);">
                        <div class="shrink-0 flex items-start justify-between gap-4 border-b px-5 py-4 sm:px-6 sm:py-5" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent);">
                            <div class="min-w-0">
                                <h3 class="text-[1.05rem] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ __('Delete this QR code?') }}</h3>
                                <p class="mt-2 text-[15px] leading-7" style="color: var(--theme-muted-text-color);">{{ __('This permanently removes the QR asset and its scan history from the library.') }}</p>
                            </div>
                            <button type="button" class="transition" style="color: var(--theme-muted-text-color);" x-on:click="deleteDialogOpen = false; deleteTargetId = null; deleteTargetName = ''">
                                <i class="fa-light fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 py-4 sm:px-6 sm:py-5">
                            <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-danger-color-rgb),0.18); background: linear-gradient(135deg, rgba(var(--theme-danger-color-rgb),0.08), transparent 70%);">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-danger-color-rgb),0.12); color: var(--theme-danger-color);">
                                        <i class="fa-light fa-triangle-exclamation"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);" x-text="deleteTargetName"></p>
                                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Existing printed QR images may stop working after deletion.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="shrink-0 border-t px-5 py-4 sm:px-6" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent); background-color: color-mix(in srgb, var(--theme-surface-soft) 88%, transparent);">
                            <div class="flex items-center justify-end gap-3">
                                <x-ui.button type="button" variant="outline" x-on:click="deleteDialogOpen = false; deleteTargetId = null; deleteTargetName = ''">{{ __('Cancel') }}</x-ui.button>
                                <x-ui.button type="button" variant="danger" x-bind:disabled="!deleteTargetId" x-on:click="$wire.delete(deleteTargetId); deleteDialogOpen = false; deleteTargetId = null; deleteTargetName = ''">
                                    <i class="fa-light fa-trash"></i>{{ __('Delete QR code') }}
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
