<div
    class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6"
    x-data="{ formDialogOpen: @js((bool) $editingId), formDialogLoading: false, createStep: @js($editingId ? 'form' : 'type'), copied: null }"
    x-on:landing-page-saved.window="formDialogOpen = false"
    x-on:landing-page-editor-ready.window="formDialogLoading = false"
>
    @once
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:400,500,600,700,800|manrope:400,500,600,700,800|nunito:400,500,600,700,800|lora:400,500,600,700" rel="stylesheet">
        <script>
            window.mlhubLandingBlockSorter = function (wire) {
                return {
                    sortable: null,
                    init() {
                        this.$nextTick(() => this.mountSortable());
                    },
                    mountSortable() {
                        const list = this.$refs.blocksList;

                        if (!list) {
                            return;
                        }

                        const start = () => {
                            if (!window.Sortable || !this.$refs.blocksList) {
                                return;
                            }

                            this.sortable?.destroy();
                            this.sortable = window.Sortable.create(this.$refs.blocksList, {
                                handle: '.landing-block-drag-handle',
                                animation: 180,
                                ghostClass: 'landing-block-ghost',
                                chosenClass: 'landing-block-chosen',
                                onEnd: () => {
                                    const order = Array.from(this.$refs.blocksList.querySelectorAll('[data-block-id]'))
                                        .map((item) => item.dataset.blockId)
                                        .filter(Boolean);

                                    wire.reorderLandingBlocks(order);
                                },
                            });
                        };

                        if (window.Sortable) {
                            start();
                            return;
                        }

                        let script = document.getElementById('sortablejs-cdn');

                        if (!script) {
                            script = document.createElement('script');
                            script.id = 'sortablejs-cdn';
                            script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js';
                            script.onload = start;
                            document.head.appendChild(script);
                        } else {
                            script.addEventListener('load', start, { once: true });
                        }
                    },
                };
            };
        </script>
        <style>
            .landing-block-drag-handle{cursor:grab}.landing-block-drag-handle:active{cursor:grabbing}.landing-block-ghost{opacity:.4}.landing-block-chosen{border-color:var(--theme-accent)!important;box-shadow:0 0 0 1px rgba(var(--theme-accent-rgb),.35)}
        </style>
    @endonce

    @php
        $pageCopyUrl = route('portal.ai-content', array_filter([
            'business_id' => (string) optional($businesses->first())->id,
            'type' => 'landing_page_copy',
            'goal' => 'Create copy for a local campaign landing page that converts visitors into leads, bookings, coupon claims, feedback, or review clicks.',
            'offer' => 'Local campaign offer or service',
            'target_customer' => 'Local customers',
            'details' => 'Generate headline, subheadline, benefits, CTA, FAQ, terms, and thank you message for a MLHUB landing page.',
            'source_type' => 'landing_page',
        ], fn ($value) => filled($value)));
    @endphp

    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif

    <section class="overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.12), transparent 36%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-6 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-md border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-browser"></i>{{ __('Marketing Assets') }}
                </div>
                <h1 class="mt-4 max-w-3xl text-[2.15rem] font-semibold leading-[1.05] tracking-[-0.055em] sm:text-[2.85rem]" style="color: var(--theme-header-text-color);">{{ __('Landing Pages') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ __('Manage public campaign pages for review requests, bookings, coupon claims, feedback, and lead capture. Landing Pages stay tied to local growth tools, QR codes, and reporting.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button type="button" size="lg" x-on:click="createStep = 'type'; formDialogLoading = true; formDialogOpen = true; $wire.create()">
                        <i class="fa-light fa-plus"></i>{{ __('Create Landing Page') }}
                    </x-ui.button>
                    <x-ui.button href="{{ $pageCopyUrl }}" wire:navigate variant="outline" size="lg">
                        <i class="fa-light fa-wand-magic-sparkles"></i>{{ __('Generate Page Copy') }}
                    </x-ui.button>
                </div>
            </div>

            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Campaign page health') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Visits and conversions from public pages') }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-chart-line"></i>
                    </span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($stats['published']) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Published') }}</p>
                    </div>
                    <div class="border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ format_percent_locale($stats['conversion_rate']) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Conversion Rate') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
        @foreach ([
            ['label' => __('Total Pages'), 'value' => $stats['total'], 'description' => __('Campaign pages'), 'icon' => 'fa-light fa-browser'],
            ['label' => __('Published'), 'value' => $stats['published'], 'description' => __('Live public pages'), 'icon' => 'fa-light fa-circle-check'],
            ['label' => __('Draft'), 'value' => $stats['draft'], 'description' => __('Not public yet'), 'icon' => 'fa-light fa-pen-to-square'],
            ['label' => __('Visits'), 'value' => $stats['visits'], 'description' => __('Tracked page views'), 'icon' => 'fa-light fa-eye'],
            ['label' => __('Conversions'), 'value' => $stats['conversions'], 'description' => __('Forms and CTA actions'), 'icon' => 'fa-light fa-bullseye-arrow'],
            ['label' => __('Conversion Rate'), 'value' => format_percent_locale($stats['conversion_rate']), 'description' => __('Conversions / visits'), 'icon' => 'fa-light fa-percent'],
        ] as $metric)
            <article class="relative overflow-hidden rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background: linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.07), transparent 44%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <span class="absolute inset-x-0 top-0 h-1" style="background-color: var(--theme-warning-color);"></span>
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[1.75rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ is_numeric($metric['value']) ? format_number_locale($metric['value']) : $metric['value'] }}</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $metric['description'] }}</p>
                    </div>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="{{ $metric['icon'] }}"></i>
                    </span>
                </div>
            </article>
        @endforeach
    </section>

    <section class="overflow-visible rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="flex flex-col gap-4 border-b px-5 py-4 xl:flex-row xl:items-end xl:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <div>
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Local campaign landing pages') }}</p>
                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Filter by business, page type, and status. These are public campaign pages, not generic bio pages.') }}</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-4">
                <x-ui.select wire:model.live="businessFilter" name="business_filter">
                    <option value="all">{{ __('All businesses') }}</option>
                    @foreach($businesses as $business)
                        <option value="{{ $business->id }}">{{ $business->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select wire:model.live="typeFilter" name="type_filter">
                    <option value="all">{{ __('All types') }}</option>
                    @foreach($pageTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select wire:model.live="statusFilter" name="status_filter">
                    <option value="all">{{ __('All statuses') }}</option>
                    <option value="published">{{ __('Published') }}</option>
                    <option value="draft">{{ __('Draft') }}</option>
                </x-ui.select>
                <x-ui.select wire:model.live="perPage" name="landing_pages_per_page">
                    <option value="10">{{ __('10 / page') }}</option>
                    <option value="25">{{ __('25 / page') }}</option>
                    <option value="50">{{ __('50 / page') }}</option>
                </x-ui.select>
            </div>
        </div>

        @if ($pages->count() > 0)
            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full text-left text-sm">
                    <thead style="color: var(--theme-muted-text-color);">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Page') }}</th>
                            <th class="px-3 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Business') }}</th>
                            <th class="px-3 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Type') }}</th>
                            <th class="px-3 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Campaign') }}</th>
                            <th class="px-3 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Visits') }}</th>
                            <th class="px-3 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Conversions') }}</th>
                            <th class="px-3 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        @foreach ($pages as $page)
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $page->title }}</p>
                                    <p class="mt-1 max-w-xs truncate text-xs" style="color: var(--theme-muted-text-color);">{{ data_get($page->content, 'headline') }}</p>
                                    <p class="mt-1 max-w-xs truncate text-[11px]" style="color: var(--theme-muted-text-color);">{{ $page->slug }}</p>
                                </td>
                                <td class="px-3 py-4" style="color: var(--theme-muted-text-color);">{{ $page->business?->name ?: __('No business') }}</td>
                                <td class="px-3 py-4"><x-ui.badge variant="neutral">{{ $pageTypes[$page->type] ?? str($page->type)->headline() }}</x-ui.badge></td>
                                <td class="px-3 py-4" style="color: var(--theme-muted-text-color);">
                                    @if ($page->campaign)
                                        {{ str($page->campaign->type)->headline() }}: {{ $page->campaign->name }}
                                    @else
                                        {{ __('Standalone') }}
                                    @endif
                                </td>
                                <td class="px-3 py-4 font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($page->visits_count) }}</td>
                                <td class="px-3 py-4 font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($page->conversions_count) }}</td>
                                <td class="px-3 py-4">
                                    <x-ui.badge :variant="$page->status === 'published' ? 'success' : 'neutral'">{{ str($page->status)->headline() }}</x-ui.badge>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center justify-end gap-2">
                                        <a href="{{ $page->publicUrl() }}" target="_blank" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border transition hover:opacity-80" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color);" title="{{ __('View Page') }}">
                                            <i class="fa-light fa-arrow-up-right"></i>
                                        </a>
                                        <button type="button" x-on:click="navigator.clipboard.writeText($el.dataset.copy || ''); copied = {{ $page->id }}; setTimeout(() => copied = null, 1500)" data-copy="{{ e($page->publicUrl()) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border transition hover:opacity-80" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color);" title="{{ __('Copy Link') }}">
                                            <i x-show="copied !== {{ $page->id }}" class="fa-light fa-copy"></i>
                                            <i x-cloak x-show="copied === {{ $page->id }}" class="fa-light fa-check"></i>
                                        </button>
                                        <x-ui.dropdown-menu align="right" width="auto">
                                            <x-slot:trigger>
                                                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border transition hover:opacity-80" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color);" title="{{ __('QR downloads') }}">
                                                    <i class="fa-light fa-qrcode"></i>
                                                </button>
                                            </x-slot:trigger>
                                            <x-ui.dropdown-menu-item :href="$page->qrPngUrl()" icon="fa-light fa-file-image">
                                                {{ __('Download PNG') }}
                                            </x-ui.dropdown-menu-item>
                                            <x-ui.dropdown-menu-item :href="$page->qrUrl()" icon="fa-light fa-code">
                                                {{ __('Download SVG') }}
                                            </x-ui.dropdown-menu-item>
                                        </x-ui.dropdown-menu>
                                        <button type="button" wire:click="edit({{ $page->id }})" x-on:click="createStep = 'form'; formDialogLoading = true; formDialogOpen = true" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border transition hover:opacity-80" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-accent);" title="{{ __('Edit') }}">
                                            <i class="fa-light fa-pen"></i>
                                        </button>

                                        <x-ui.dropdown-menu align="right" width="auto">
                                            <x-slot:trigger>
                                                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border transition hover:opacity-80" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-muted-text-color);" title="{{ __('More actions') }}">
                                                    <i class="fa-light fa-ellipsis"></i>
                                                </button>
                                            </x-slot:trigger>
                                            <x-ui.dropdown-menu-item :href="route('portal.reports')" icon="fa-light fa-chart-line" wire:navigate>
                                                {{ __('Analytics') }}
                                            </x-ui.dropdown-menu-item>
                                            <x-ui.dropdown-menu-item icon="fa-light fa-copy" wire:click="duplicate({{ $page->id }})">
                                                {{ __('Duplicate') }}
                                            </x-ui.dropdown-menu-item>
                                            <x-ui.dropdown-menu-item icon="{{ $page->status === 'published' ? 'fa-light fa-file-pen' : 'fa-light fa-circle-check' }}" wire:click="toggleStatus({{ $page->id }})">
                                                {{ $page->status === 'published' ? __('Move to draft') : __('Publish') }}
                                            </x-ui.dropdown-menu-item>
                                            <x-ui.dropdown-menu-item icon="fa-light fa-trash" variant="danger" wire:click="delete({{ $page->id }})" wire:confirm="{{ __('Delete this landing page?') }}">
                                                {{ __('Delete') }}
                                            </x-ui.dropdown-menu-item>
                                        </x-ui.dropdown-menu>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                <p class="text-sm" style="color: var(--theme-muted-text-color);">
                    {{ __('Showing') }}
                    <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($pages->firstItem()) }}</span>
                    -
                    <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($pages->lastItem()) }}</span>
                    {{ __('of') }}
                    <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($pages->total()) }}</span>
                    {{ __('landing pages') }}
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="previousPage" @disabled($pages->onFirstPage()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">
                        <i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}
                    </button>
                    <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">
                        {{ __('Page') }} {{ format_number_locale($pages->currentPage()) }} / {{ format_number_locale($pages->lastPage()) }}
                    </span>
                    <button type="button" wire:click="nextPage" @disabled(! $pages->hasMorePages()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">
                        {{ __('Next') }}<i class="fa-light fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        @else
            <div class="px-6 py-12">
                <x-ui.empty icon="fa-light fa-browser" :title="__('No landing pages yet')" :description="__('Create a local campaign page for a review request, coupon claim, booking, feedback, or lead capture flow.')" />
            </div>
        @endif
    </section>

    <template x-teleport="body">
        <div
            x-cloak
            x-show="formDialogOpen"
            class="fixed bottom-0 right-0 top-0 z-[130] bg-white transition-[left] duration-[420ms] ease-[cubic-bezier(0.16,1,0.3,1)] dark:bg-slate-950"
            x-bind:style="typeof viewportWidth !== 'undefined' && viewportWidth >= 1024 ? `left: ${sidebarCollapsed ? '70px' : '14.75rem'}` : 'left: 0px'"
            x-on:keydown.escape.window="formDialogOpen = false"
        >
            <div class="relative h-full">
                <div
                    x-cloak
                    x-show="formDialogOpen && formDialogLoading"
                    x-transition.opacity
                    class="absolute inset-0 z-[175] grid place-items-center bg-slate-50/95 backdrop-blur-sm"
                >
                    <div class="flex flex-col items-center gap-3 rounded-2xl border border-slate-200 bg-white px-6 py-5 text-center shadow-xl">
                        <span class="h-9 w-9 animate-spin rounded-full border-2 border-slate-200 border-t-[var(--theme-accent)]"></span>
                        <span class="text-sm font-semibold text-slate-700">{{ __('Loading editor...') }}</span>
                    </div>
                </div>
                <form
                    wire:submit="save"
                    wire:key="landing-page-builder-{{ $editingId ?: 'new' }}-{{ $type }}-{{ $template }}"
                    x-show="formDialogOpen"
                    x-transition.opacity
                    x-data="{
                        selectedBusinessId: @js((string) $business_id),
                        businesses: @js($businesses->map(fn ($business) => [
                            'id' => (string) $business->id,
                            'name' => $business->name,
                            'address' => $business->address,
                            'phone' => $business->phone,
                            'website' => $business->website,
                        ])->values()),
                        typeValue: @js($type),
                        templateValue: @js($template),
                        templatePresets: @js(collect($templateCatalog)->map(fn ($preset, $key) => [
                            'key' => $key,
                            'name' => $preset['name'],
                            'type' => $preset['type'],
                            'primary' => $preset['primary'],
                            'background' => $preset['background'],
                            'accent' => $preset['accent'],
                            'layout' => $preset['layout'] ?? \Modules\AppLandingPages\Support\PageTemplateCatalog::designFor($key)['layout_style'],
                            'backgroundType' => $preset['background_type'] ?? 'gradient',
                            'font' => $preset['font_style'] ?? 'modern',
                            'button' => $preset['button_style'] ?? 'pill',
                            'card' => $preset['card_style'] ?? 'soft',
                            'logoShape' => $preset['logo_shape'] ?? 'circle',
                        ])->values()),
                        layoutStyleValue: @js(\Modules\AppLandingPages\Support\PageTemplateCatalog::designFor($template)['layout_style']),
                        titleValue: @js($title),
                        headlineValue: @js($headline),
                        subheadlineValue: @js($subheadline),
                        descriptionValue: @js($description),
                        benefitsValue: @js($benefits),
                        ctaValue: @js($cta_text),
                        couponTitleValue: @js($coupon_title),
                        discountValue: @js($discount),
                        expiryValue: @js($expiry),
                        termsValue: @js($terms),
                        serviceValue: @js($service),
                        durationValue: @js($duration),
                        priceValue: @js($price),
                        primaryColorValue: @js($primary_color),
                        backgroundColorValue: @js($background_color),
                        backgroundTypeValue: @js($background_type),
                        buttonStyleValue: @js($button_style),
                        cardStyleValue: @js($card_style),
                        fontStyleValue: @js($font_style),
                        thankYouMessageValue: @js($thank_you_message),
                        reviewUrlValue: @js($review_url),
                        availableSlotsValue: @js($available_slots),
                        logoUrlValue: @js($logo_url),
                        logoShapeValue: @js($logo_shape),
                        coverImageValue: @js($cover_image),
                        showLogoValue: @js((bool) $show_logo),
                        showBenefitsValue: @js((bool) $show_benefits),
                        showTermsValue: @js((bool) $show_terms),
                        showBusinessInfoValue: @js((bool) $show_business_info),
                        showSocialLinksValue: @js((bool) $show_social_links),
                        showFaqValue: @js((bool) $show_faq),
                        templatePickerOpen: false,
                        previewSource: 'draft',
                        previewMode: 'desktop',
                        previewCopied: false,
                        publicUrlValue: @js($public_url),
                        previewFrameUrl: 'about:blank',
                        nextPreviewFrameUrl: '',
                        previewFrameBusy: true,
                        previewReady: false,
                        previewRefreshTimer: null,
                        get selectedBusiness() {
                            return this.businesses.find((business) => String(business.id) === String(this.selectedBusinessId)) || {};
                        },
                        get businessName() {
                            return this.selectedBusiness.name || @js(__('Local business'));
                        },
                        get businessInitials() {
                            return this.businessName.replace(/[^A-Za-z0-9 ]/g, '').split(' ').filter(Boolean).slice(0, 2).map((word) => word[0]).join('').toUpperCase() || 'LB';
                        },
                        get typeLabel() {
                            return String(this.typeValue || 'campaign').replace(/[_-]/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
                        },
                        get benefitsList() {
                            return String(this.benefitsValue || '').split(/\r\n|\r|\n/).map((line) => line.trim()).filter(Boolean);
                        },
                        get buttonRadius() {
                            return this.buttonStyleValue === 'square' ? '8px' : (this.buttonStyleValue === 'rounded' ? '14px' : '999px');
                        },
                        get cardRadius() {
                            return this.cardStyleValue === 'flat' ? '12px' : (this.cardStyleValue === 'bordered' ? '18px' : '24px');
                        },
                        get fontFamily() {
                            if (this.fontStyleValue === 'classic') return 'Lora, Georgia, Cambria, Times New Roman, serif';
                            if (this.fontStyleValue === 'elegant') return 'Manrope, Be Vietnam Pro, ui-sans-serif, system-ui, sans-serif';
                            if (this.fontStyleValue === 'friendly') return 'Nunito, Be Vietnam Pro, ui-sans-serif, system-ui, sans-serif';
                            return 'Be Vietnam Pro, Inter, ui-sans-serif, system-ui, sans-serif';
                        },
                        get previewBackground() {
                            if (this.layoutStyleValue === 'centered') {
                                return `radial-gradient(circle at 50% 0%, ${this.primaryColorValue}30, transparent 34%), ${this.backgroundColorValue}`;
                            }

                            if (this.layoutStyleValue === 'poster') {
                                return `linear-gradient(180deg, ${this.primaryColorValue} 0 22%, ${this.backgroundColorValue} 22% 100%)`;
                            }

                            if (this.layoutStyleValue === 'editorial') {
                                return `linear-gradient(90deg, ${this.primaryColorValue}18, transparent 46%), ${this.backgroundColorValue}`;
                            }

                            return `linear-gradient(135deg, ${this.primaryColorValue}22, transparent 34%), radial-gradient(circle at 86% 10%, ${this.primaryColorValue}2e, transparent 28%), ${this.backgroundColorValue}`;
                        },
                        get coverBackground() {
                            return this.coverImageValue ? `url('${this.coverImageValue}') center/cover` : `linear-gradient(135deg, ${this.primaryColorValue}, ${this.primaryColorValue}88)`;
                        },
                        get selectedTemplatePreset() {
                            return this.templatePresets.find((item) => item.key === this.templateValue) || this.templatePresets[0] || {};
                        },
                        syncTypeFromTemplate() {
                            const presetType = this.selectedTemplatePreset.type;

                            if (presetType && presetType !== this.typeValue) {
                                this.typeValue = presetType;
                                this.applyTypeDefaultsToPreview(presetType);
                            }
                        },
                        typeDefaults(type) {
                            const defaults = {
                                review: { title: 'Google review request page', headline: 'How was your visit?', subheadline: 'Your rating helps us improve and helps other local customers choose us.', cta: 'Continue', benefits: 'Fast response\nFriendly local team\nSimple next step' },
                                booking: { title: 'Book your appointment', headline: 'Book a time that works for you', subheadline: 'Choose a service, pick a slot, and we will confirm your appointment.', cta: 'Request booking', benefits: 'Choose a service\nPick an available slot\nGet confirmation from the team' },
                                coupon: { title: 'Claim your local offer', headline: 'Get 20% off your next visit', subheadline: 'Claim this limited-time offer and show your code in-store.', cta: 'Claim coupon', benefits: 'Fast response\nFriendly local team\nSimple next step', couponTitle: 'LOCAL20', discount: '20% off' },
                                promotion: { title: 'Local promotion page', headline: 'A special offer for local customers', subheadline: 'Leave your details and our team will help you claim it.', cta: 'Get offer', benefits: 'Limited-time offer\nEasy claim\nLocal team follow-up', couponTitle: 'LOCAL20', discount: 'Special offer' },
                                feedback: { title: 'Share your feedback', headline: 'Tell us how we did', subheadline: 'Your private feedback helps our team improve the next visit.', cta: 'Send feedback', benefits: 'Private feedback\nTeam follow-up\nQuick response' },
                                custom: { title: 'Custom campaign page', headline: 'A local campaign built for your audience', subheadline: 'Share the offer, collect interest, and track results.', cta: 'Send request', benefits: 'Flexible campaign\nSimple next step\nTracked responses' },
                                lead: { title: 'Lead capture page', headline: 'Request a free consultation', subheadline: 'Tell us what you need and our local team will follow up.', cta: 'Send request', benefits: 'Fast response\nFriendly local team\nSimple next step' },
                            };

                            return defaults[type] || defaults.lead;
                        },
                        applyTypeDefaultsToPreview(type) {
                            const defaults = this.typeDefaults(type || this.typeValue);
                            this.titleValue = defaults.title;
                            this.headlineValue = defaults.headline;
                            this.subheadlineValue = defaults.subheadline;
                            this.ctaValue = defaults.cta;
                            this.benefitsValue = defaults.benefits;
                            this.descriptionValue = '';
                            this.thankYouMessageValue = 'Thank you. We have received your request.';
                            this.couponTitleValue = defaults.couponTitle || '';
                            this.discountValue = defaults.discount || '';
                            this.expiryValue = ['coupon', 'promotion'].includes(type || this.typeValue) ? new Date(Date.now() + 14 * 86400000).toISOString().slice(0, 10) : '';
                            this.termsValue = ['coupon', 'promotion'].includes(type || this.typeValue) ? 'Valid for one customer. Cannot be combined with other offers.' : '';
                            this.reviewUrlValue = '';
                            this.serviceValue = (type || this.typeValue) === 'booking' ? 'Appointment' : '';
                            this.durationValue = (type || this.typeValue) === 'booking' ? '60 minutes' : '';
                            this.priceValue = (type || this.typeValue) === 'booking' ? 'Ask us' : '';
                            this.availableSlotsValue = (type || this.typeValue) === 'booking' ? '09:00\n10:00\n14:00\n15:00' : '';
                        },
                        applyTemplatePreset(key) {
                            const preset = this.templatePresets.find((item) => item.key === key);

                            if (!preset) {
                                return;
                            }

                            this.templateValue = key;
                            this.syncTypeFromTemplate();
                            this.primaryColorValue = preset.primary;
                            this.backgroundColorValue = preset.background;
                            this.layoutStyleValue = preset.layout;
                            this.fontStyleValue = preset.font || this.fontStyleValue;
                            this.buttonStyleValue = preset.button || this.buttonStyleValue;
                            this.cardStyleValue = preset.card || this.cardStyleValue;
                            this.logoShapeValue = preset.logoShape || this.logoShapeValue;
                        },
                        draftPreviewUrl() {
                            const params = new URLSearchParams({
                                type: this.typeValue || 'lead',
                                business_id: this.selectedBusinessId || '',
                                template: this.templateValue || '',
                                headline: this.headlineValue || '',
                                subheadline: this.subheadlineValue || '',
                                description: this.descriptionValue || '',
                                cta: this.ctaValue || '',
                                benefits: this.benefitsValue || '',
                                thank_you_message: this.thankYouMessageValue || '',
                                coupon_title: this.couponTitleValue || '',
                                discount: this.discountValue || '',
                                expiry: this.expiryValue || '',
                                terms: this.termsValue || '',
                                service: this.serviceValue || '',
                                duration: this.durationValue || '',
                                price: this.priceValue || '',
                                available_slots: this.availableSlotsValue || '',
                                review_url: this.reviewUrlValue || '',
                                primary_color: this.primaryColorValue || '',
                                background_color: this.backgroundColorValue || '',
                                background_type: this.backgroundTypeValue || '',
                                font_style: this.fontStyleValue || '',
                                button_style: this.buttonStyleValue || '',
                                card_style: this.cardStyleValue || '',
                                logo_url: this.logoUrlValue || '',
                                logo_shape: this.logoShapeValue || 'circle',
                                cover_image: this.coverImageValue || '',
                                show_logo: this.showLogoValue ? '1' : '0',
                                show_benefits: this.showBenefitsValue ? '1' : '0',
                                show_terms: this.showTermsValue ? '1' : '0',
                                show_business_info: this.showBusinessInfoValue ? '1' : '0',
                                show_social_links: this.showSocialLinksValue ? '1' : '0',
                                show_faq: this.showFaqValue ? '1' : '0',
                            });

                            return @js(route('landing-pages.preview')) + '?' + params.toString();
                        },
                        schedulePreviewRefresh(delay = 180) {
                            clearTimeout(this.previewRefreshTimer);
                            this.previewRefreshTimer = setTimeout(() => this.refreshPreview(), delay);
                        },
                        refreshPreview() {
                            const nextUrl = this.draftPreviewUrl();

                            if (nextUrl === this.previewFrameUrl || nextUrl === this.nextPreviewFrameUrl) {
                                return;
                            }

                            this.previewFrameBusy = true;
                            this.previewReady = false;
                            this.nextPreviewFrameUrl = nextUrl;
                        },
                        promotePreviewFrame() {
                            this.previewFrameUrl = this.nextPreviewFrameUrl || this.previewFrameUrl;
                            this.nextPreviewFrameUrl = '';
                        },
                        handlePrimaryPreviewLoad() {
                            if (this.previewFrameUrl === 'about:blank') {
                                return;
                            }

                            this.previewFrameBusy = false;
                            this.previewReady = true;
                        },
                    }"
                    x-init="$nextTick(() => { previewFrameBusy = true; previewReady = false; previewFrameUrl = draftPreviewUrl(); })"
                    x-effect="typeValue; templateValue; selectedBusinessId; titleValue; headlineValue; subheadlineValue; descriptionValue; ctaValue; benefitsValue; thankYouMessageValue; couponTitleValue; discountValue; expiryValue; termsValue; serviceValue; durationValue; priceValue; availableSlotsValue; reviewUrlValue; primaryColorValue; backgroundColorValue; backgroundTypeValue; fontStyleValue; buttonStyleValue; cardStyleValue; logoUrlValue; logoShapeValue; coverImageValue; showLogoValue; showBenefitsValue; showTermsValue; showBusinessInfoValue; showSocialLinksValue; showFaqValue; schedulePreviewRefresh();"
                    x-on:image-picker:change.window="if ($event.detail?.name === 'logo_url') logoUrlValue = $event.detail?.previewUrl || $event.detail?.value || ''; if ($event.detail?.name === 'cover_image') coverImageValue = $event.detail?.previewUrl || $event.detail?.value || ''; schedulePreviewRefresh(60);"
                    class="relative flex h-screen w-full flex-col overflow-hidden"
                    style="background-color: var(--theme-surface-overlay);"
                >
                    <div class="shrink-0 flex items-start justify-between gap-4 border-b px-5 py-4 sm:px-7" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <div>
                            <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $editingId ? __('Edit Landing Page') : __('Create Landing Page') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Edit content and design while the public page preview updates beside it.') }}</p>
                        </div>
                        <button type="button" x-on:click="formDialogOpen = false" class="flex h-10 w-10 items-center justify-center rounded-xl" style="color: var(--theme-muted-text-color);">
                            <i class="fa-light fa-xmark"></i>
                        </button>
                    </div>

                    <div x-show="createStep === 'type' && !@js((bool) $editingId)" class="grid max-h-[72vh] gap-4 overflow-y-auto p-5">
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Choose page type') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Start from the campaign goal so the page captures the right conversion data.') }}</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ([
                                'lead' => ['label' => __('Lead Capture Page'), 'desc' => __('Capture name, email, phone, message, and interested service.'), 'icon' => 'fa-light fa-address-card'],
                                'coupon' => ['label' => __('Coupon Claim Page'), 'desc' => __('Let customers claim an offer and generate a redemption lead.'), 'icon' => 'fa-light fa-ticket'],
                                'booking' => ['label' => __('Booking Page'), 'desc' => __('Collect service, date, time, and contact details.'), 'icon' => 'fa-light fa-calendar-check'],
                                'feedback' => ['label' => __('Feedback Page'), 'desc' => __('Collect private rating, topic, and feedback.'), 'icon' => 'fa-light fa-message-lines'],
                                'review' => ['label' => __('Review Page'), 'desc' => __('Route high ratings to public review and low ratings to private feedback.'), 'icon' => 'fa-light fa-star'],
                                'custom' => ['label' => __('Custom Campaign Page'), 'desc' => __('Use a flexible local campaign page without drag and drop blocks.'), 'icon' => 'fa-light fa-bullseye-arrow'],
                            ] as $typeValue => $option)
                                <button type="button" wire:click="selectPageType('{{ $typeValue }}')" x-on:click="typeValue = @js($typeValue); applyTypeDefaultsToPreview(@js($typeValue)); createStep = 'template'" class="group rounded-[0.9rem] border p-4 text-left transition hover:-translate-y-px" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="{{ $option['icon'] }}"></i></span>
                                    <span class="mt-3 block text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $option['label'] }}</span>
                                    <span class="mt-1 block text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $option['desc'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="createStep === 'template' && !@js((bool) $editingId)" class="grid min-h-0 flex-1 grid-rows-[auto_minmax(0,1fr)] gap-3 overflow-hidden p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Choose template') }}</p>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Pick the starting layout and visual preset before editing content.') }}</p>
                            </div>
                            <x-ui.button type="button" variant="outline" size="sm" x-on:click="createStep = 'type'">
                                <i class="fa-light fa-arrow-left"></i>{{ __('Back') }}
                            </x-ui.button>
                        </div>

                        <div class="grid min-h-0 content-start gap-2 overflow-y-auto pr-1 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6">
                            @foreach($templateOptionsForType as $value => $label)
                                @php
                                    $preset = $templateCatalog[$value] ?? null;
                                @endphp
                                <button
                                    type="button"
                                    wire:click="selectTemplate('{{ $value }}')"
                                    x-on:click="applyTemplatePreset(@js($value)); applyTypeDefaultsToPreview(typeValue); createStep = 'form'"
                                    class="group rounded-[0.8rem] border p-2 text-left transition hover:-translate-y-0.5"
                                    x-bind:class="templateValue === @js($value) ? 'shadow-[0_0_0_1px_var(--theme-accent)]' : ''"
                                    x-bind:style="`border-color: ${templateValue === @js($value) ? 'var(--theme-accent)' : 'rgba(var(--theme-border-color-rgb), .62)'}; background-color: ${templateValue === @js($value) ? 'rgba(var(--theme-accent-rgb), .055)' : 'color-mix(in srgb, var(--theme-surface-base) 92%, transparent)'};`"
                                >
                                    <span class="block h-24 overflow-hidden rounded-lg border p-1" style="border-color: rgba(var(--theme-border-color-rgb), .5); background: {{ $preset ? $preset['background'] : '#f8fafc' }};">
                                        <span class="grid h-full gap-1 {{ ($preset['layout'] ?? 'split') === 'sidebar' ? 'grid-cols-[.36fr_1fr]' : '' }} {{ ($preset['layout'] ?? 'split') === 'stacked' ? 'grid-rows-[.42fr_1fr]' : '' }} {{ ($preset['layout'] ?? 'split') === 'poster' ? 'grid-rows-[.64fr_1fr]' : '' }} {{ ($preset['layout'] ?? 'split') === 'centered' ? 'place-items-center' : '' }}">
                                            <span class="block rounded-lg {{ ($preset['layout'] ?? 'split') === 'centered' ? 'h-12 w-12 rounded-full' : '' }}" style="background: linear-gradient(135deg, {{ $preset['primary'] ?? '#0f766e' }}, {{ $preset['accent'] ?? '#ccfbf1' }});"></span>
                                            <span class="grid content-center gap-1.5 rounded-lg bg-white/85 p-2 {{ ($preset['layout'] ?? 'split') === 'centered' ? 'w-full' : '' }}">
                                                <span class="h-1.5 w-2/3 rounded-full" style="background-color: {{ $preset['primary'] ?? '#0f766e' }};"></span>
                                                <span class="h-1.5 w-full rounded-full bg-slate-200"></span>
                                                <span class="h-1.5 w-1/2 rounded-full bg-slate-200"></span>
                                            </span>
                                        </span>
                                    </span>
                                    <span class="mt-2 flex items-center justify-between gap-2">
                                        <span class="min-w-0">
                                            <span class="block truncate text-xs font-semibold" style="color: var(--theme-header-text-color);">{{ $label }}</span>
                                            <span class="mt-1 block text-[10px] font-black uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ str($preset['layout'] ?? 'split')->headline() }}</span>
                                            <span class="mt-0.5 block truncate text-[10px]" style="color: var(--theme-muted-text-color);">{{ str($preset['font_style'] ?? 'modern')->headline() }} · {{ str($preset['card_style'] ?? 'soft')->headline() }}</span>
                                        </span>
                                        <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full border" x-show="templateValue === @js($value)" style="border-color: rgba(var(--theme-accent-rgb), .32); background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                                            <i class="fa-light fa-check text-[10px]"></i>
                                        </span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="createStep === 'form' || @js((bool) $editingId)" class="grid min-h-0 flex-1 overflow-hidden xl:grid-cols-[minmax(0,1fr)_minmax(390px,42vw)]">
                        <div class="min-h-0 overflow-y-auto border-r p-5 sm:p-6" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            <div class="grid gap-5 min-[1500px]:grid-cols-[minmax(0,1fr)_22rem] min-[1500px]:items-start">
                        <div class="grid gap-4 min-w-0">
                        @if ($editingId)
                            <div class="grid gap-4 rounded-[0.9rem] border p-4 md:grid-cols-2" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent);">
                                <x-ui.input wire:model="slug" name="slug_display" :label="__('Slug')" readonly />
                                <x-ui.input wire:model="public_url" name="public_url_display" :label="__('Public URL')" readonly />
                            </div>
                        @endif
                        <div class="grid gap-4 md:grid-cols-2">
                            <x-ui.select wire:model.change="business_id" x-model="selectedBusinessId" name="business_id" :label="__('Business')" :error="$errors->first('business_id')">
                                @foreach($businesses as $business)
                                    <option value="{{ $business->id }}">{{ $business->name }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.select wire:model.change="type" x-model="typeValue" x-on:change="applyTypeDefaultsToPreview(typeValue)" name="type" :label="__('Page Type')" :error="$errors->first('type')">
                                @foreach($pageTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.select wire:model.change="campaign_id" name="campaign_id" :label="__('Campaign')">
                                <option value="">{{ __('Standalone custom campaign') }}</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->name }} &middot; {{ str($campaign->type)->headline() }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.input wire:model.blur="title" x-model="titleValue" name="title" :label="__('Page title')" :error="$errors->first('title')" />
                            <x-ui.select wire:model.change="status" name="status" :label="__('Status')" :error="$errors->first('status')">
                                <option value="published">{{ __('Published') }}</option>
                                <option value="draft">{{ __('Draft') }}</option>
                            </x-ui.select>
                        </div>

                        <x-ui.input wire:model.blur="headline" x-model="headlineValue" name="headline" :label="__('Headline')" :error="$errors->first('headline')" />
                        <x-ui.input wire:model.blur="subheadline" x-model="subheadlineValue" name="subheadline" :label="__('Subheadline')" :error="$errors->first('subheadline')" />
                        <x-ui.textarea wire:model.blur="description" x-model="descriptionValue" name="description" :label="__('Offer / description')" rows="3" :error="$errors->first('description')">{{ $description }}</x-ui.textarea>
                        <x-ui.textarea wire:model.blur="benefits" x-model="benefitsValue" name="benefits" :label="__('Benefits')" rows="3" :help="__('One benefit per line.')" :error="$errors->first('benefits')">{{ $benefits }}</x-ui.textarea>

                        <div class="grid gap-4 md:grid-cols-2">
                            <x-ui.input wire:model.blur="cta_text" x-model="ctaValue" name="cta_text" :label="__('CTA button')" :error="$errors->first('cta_text')" />
                            <x-ui.input wire:model.blur="thank_you_message" x-model="thankYouMessageValue" name="thank_you_message" :label="__('Thank you message')" :error="$errors->first('thank_you_message')" />
                        </div>

                        @if ($type === 'review')
                            <x-ui.input wire:model.blur="review_url" x-model="reviewUrlValue" name="review_url" :label="__('Google / Facebook review URL')" :help="__('4-5 star visitors redirect here. 1-3 star visitors can leave private feedback.')" :error="$errors->first('review_url')" />
                        @endif

                        @if (in_array($type, ['coupon', 'promotion'], true))
                            <div class="grid gap-4 md:grid-cols-2">
                                <x-ui.input wire:model.blur="coupon_title" x-model="couponTitleValue" name="coupon_title" :label="__('Coupon title')" :error="$errors->first('coupon_title')" />
                                <x-ui.input wire:model.blur="discount" x-model="discountValue" name="discount" :label="__('Discount')" :error="$errors->first('discount')" />
                                <x-ui.date-picker
                                    wire:model.change="expiry"
                                    x-model="expiryValue"
                                    name="expiry"
                                    :label="__('Expiry')"
                                    :value="$expiry"
                                    :placeholder="__('Choose expiry date')"
                                    :error="$errors->first('expiry')"
                                />
                                <x-ui.input wire:model.blur="terms" x-model="termsValue" name="terms" :label="__('Terms')" :error="$errors->first('terms')" />
                            </div>
                        @endif

                        @if ($type === 'booking')
                            <div class="grid gap-4 md:grid-cols-2">
                                <x-ui.input wire:model.blur="service" x-model="serviceValue" name="service" :label="__('Service')" :error="$errors->first('service')" />
                                <x-ui.input wire:model.blur="duration" x-model="durationValue" name="duration" :label="__('Duration')" :error="$errors->first('duration')" />
                                <x-ui.input wire:model.blur="price" x-model="priceValue" name="price" :label="__('Price')" :error="$errors->first('price')" />
                                <x-ui.textarea wire:model.blur="available_slots" x-model="availableSlotsValue" name="available_slots" :label="__('Available slots')" rows="3" :help="__('One slot per line.')" :error="$errors->first('available_slots')">{{ $available_slots }}</x-ui.textarea>
                            </div>
                        @endif

                        </div>

                        <aside class="grid min-w-0 gap-4 min-[1500px]:sticky min-[1500px]:top-0">
                            <div class="rounded-[1rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb), .58); background:
                                linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.06), transparent 42%),
                                color-mix(in srgb, var(--theme-surface-base) 92%, transparent);">
                                <div class="flex items-center justify-between gap-3 px-1">
                                    <div>
                                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Design Settings') }}</p>
                                        <p class="mt-0.5 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Template, colors, and visual style.') }}</p>
                                    </div>
                                    <span class="grid h-9 w-9 place-items-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb), .1); color: var(--theme-accent);">
                                        <i class="fa-light fa-sliders"></i>
                                    </span>
                                </div>

                                <div class="mt-3 grid gap-4">
                                    <section class="rounded-[0.85rem] border p-2.5" style="border-color: rgba(var(--theme-border-color-rgb), .5); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
                                        <div class="grid gap-2.5">
                                            <div class="flex min-w-0 items-center gap-3">
                                                <span class="block h-11 w-12 shrink-0 overflow-hidden rounded-xl border" style="border-color: rgba(var(--theme-border-color-rgb), .5);" x-bind:style="`background: ${selectedTemplatePreset.background || '#f8fafc'};`">
                                                    <span class="block h-5" x-bind:style="`background: linear-gradient(135deg, ${selectedTemplatePreset.primary || '#0f766e'}, ${selectedTemplatePreset.accent || '#ccfbf1'});`"></span>
                                                    <span class="grid gap-1 p-1.5 pt-1">
                                                        <span class="h-1.5 w-4/5 rounded-full" x-bind:style="`background-color: ${selectedTemplatePreset.primary || '#0f766e'};`"></span>
                                                        <span class="h-1.5 w-3/5 rounded-full bg-slate-200"></span>
                                                    </span>
                                                </span>
                                                <span class="min-w-0">
                                                    <span class="block text-sm font-semibold" style="color: var(--theme-header-text-color);" x-text="selectedTemplatePreset.name || @js(__('Template'))">{{ $templateOptionsForType[$template] ?? __('Template') }}</span>
                                                    <span class="mt-0.5 block text-[10px] font-black uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);" x-text="`${selectedTemplatePreset.layout || 'split'} layout`">{{ __('Template preset') }}</span>
                                                    <span class="mt-0.5 block truncate text-[11px]" style="color: var(--theme-muted-text-color);" x-text="`${selectedTemplatePreset.font || 'modern'} · ${selectedTemplatePreset.button || 'pill'} · ${selectedTemplatePreset.card || 'soft'}`"></span>
                                                </span>
                                            </div>
                                            <x-ui.button type="button" variant="outline" size="sm" class="w-full justify-center rounded-xl" x-on:click="templatePickerOpen = true">
                                                <i class="fa-light fa-grid-2"></i>{{ __('Change template') }}
                                            </x-ui.button>
                                        </div>

                                        <template x-teleport="body">
                                            <div
                                                x-cloak
                                                x-show="templatePickerOpen"
                                                x-transition.opacity
                                                class="fixed inset-0 z-[9999] grid place-items-center bg-slate-950/45 p-4"
                                                x-on:keydown.escape.window="templatePickerOpen = false"
                                            >
                                                <div class="max-h-[88vh] w-[min(92rem,96vw)] overflow-hidden rounded-[1.1rem] border shadow-2xl" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: var(--theme-surface-overlay);" x-on:click.stop>
                                                <div class="flex items-start justify-between gap-4 border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .62);">
                                                    <div>
                                                        <p class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Choose template') }}</p>
                                                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Pick a layout and visual preset for this public page.') }}</p>
                                                    </div>
                                                    <button type="button" x-on:click="templatePickerOpen = false" class="grid h-9 w-9 place-items-center rounded-xl" style="color: var(--theme-muted-text-color);">
                                                        <i class="fa-light fa-xmark"></i>
                                                    </button>
                                                </div>
                                                <div class="grid max-h-[calc(88vh-5rem)] gap-3 overflow-y-auto p-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                                                    @foreach($templateOptionsForType as $value => $label)
                                                        @php
                                                            $preset = $templateCatalog[$value] ?? null;
                                                        @endphp
                                                        <button
                                                            type="button"
                                                            wire:click="selectTemplate('{{ $value }}')"
                                                            x-on:click="typeValue = @js($preset['type'] ?? $type); applyTemplatePreset(@js($value)); applyTypeDefaultsToPreview(typeValue); templatePickerOpen = false"
                                                            class="group flex items-center gap-3 rounded-[0.85rem] border p-3 text-left transition hover:-translate-y-0.5"
                                                            x-bind:class="templateValue === @js($value) ? 'shadow-[0_0_0_1px_var(--theme-accent)]' : ''"
                                                            x-bind:style="`border-color: ${templateValue === @js($value) ? 'var(--theme-accent)' : 'rgba(var(--theme-border-color-rgb), .62)'}; background-color: ${templateValue === @js($value) ? 'rgba(var(--theme-accent-rgb), .055)' : 'color-mix(in srgb, var(--theme-surface-base) 92%, transparent)'};`"
                                                        >
                                                            <span class="block h-14 w-16 shrink-0 overflow-hidden rounded-xl border p-1" style="border-color: rgba(var(--theme-border-color-rgb), .5); background: {{ $preset ? $preset['background'] : '#f8fafc' }};">
                                                                <span class="grid h-full gap-1 {{ ($preset['layout'] ?? 'split') === 'sidebar' ? 'grid-cols-[.35fr_1fr]' : '' }} {{ ($preset['layout'] ?? 'split') === 'stacked' ? 'grid-rows-[.42fr_1fr]' : '' }} {{ ($preset['layout'] ?? 'split') === 'poster' ? 'grid-rows-[.62fr_1fr]' : '' }}">
                                                                    <span class="rounded-lg" style="background: linear-gradient(135deg, {{ $preset['primary'] ?? '#0f766e' }}, {{ $preset['accent'] ?? '#ccfbf1' }});"></span>
                                                                    <span class="grid content-center gap-1 rounded-lg bg-white/80 p-1">
                                                                        <span class="h-1 w-3/4 rounded-full" style="background-color: {{ $preset['primary'] ?? '#0f766e' }};"></span>
                                                                        <span class="h-1 w-full rounded-full bg-slate-200"></span>
                                                                    </span>
                                                                </span>
                                                            </span>
                                                            <span class="flex min-w-0 flex-1 items-center justify-between gap-2">
                                                                <span class="min-w-0">
                                                                    <span class="block truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $label }}</span>
                                                                    <span class="mt-1 block text-[10px] font-black uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ str($preset['layout'] ?? 'split')->headline() }} / {{ str($preset['font_style'] ?? 'modern')->headline() }}</span>
                                                                    <span class="mt-0.5 block text-[11px]" style="color: var(--theme-muted-text-color);">{{ str($preset['button_style'] ?? 'pill')->headline() }} button · {{ str($preset['card_style'] ?? 'soft')->headline() }} card</span>
                                                                </span>
                                                                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full border" x-show="templateValue === @js($value)" style="border-color: rgba(var(--theme-accent-rgb), .32); background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                                                                    <i class="fa-light fa-check text-xs"></i>
                                                                </span>
                                                            </span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                                </div>
                                            </div>
                                        </template>
                                        @if($errors->first('template'))
                                            <p class="mt-3 text-sm font-medium" style="color: var(--theme-danger-color);">{{ $errors->first('template') }}</p>
                                        @endif
                                    </section>

                                    <div class="grid grid-cols-2 gap-3">
                                        <x-ui.color-picker
                                            wire:model.change="primary_color"
                                            x-model="primaryColorValue"
                                            name="primary_color"
                                            :label="__('Primary')"
                                            :value="$primary_color"
                                            :error="$errors->first('primary_color')"
                                        />
                                        <x-ui.color-picker
                                            wire:model.change="background_color"
                                            x-model="backgroundColorValue"
                                            name="background_color"
                                            :label="__('Background')"
                                            :value="$background_color"
                                            :error="$errors->first('background_color')"
                                        />
                                    </div>
                                    <x-ui.select wire:model.change="background_type" x-model="backgroundTypeValue" name="background_type" :label="__('Background style')" :error="$errors->first('background_type')">
                                        <option value="gradient">{{ __('Gradient') }}</option>
                                        <option value="solid">{{ __('Solid') }}</option>
                                        <option value="image">{{ __('Image') }}</option>
                                    </x-ui.select>
                                    <x-ui.select wire:model.change="font_style" x-model="fontStyleValue" name="font_style" :label="__('Font style')" :error="$errors->first('font_style')">
                                        <option value="modern">{{ __('Modern') }}</option>
                                        <option value="classic">{{ __('Classic') }}</option>
                                        <option value="elegant">{{ __('Elegant') }}</option>
                                        <option value="friendly">{{ __('Friendly') }}</option>
                                    </x-ui.select>
                                    <div class="grid grid-cols-2 gap-3">
                                        <x-ui.select wire:model.change="button_style" x-model="buttonStyleValue" name="button_style" :label="__('Button')" :error="$errors->first('button_style')">
                                            <option value="pill">{{ __('Pill') }}</option>
                                            <option value="rounded">{{ __('Rounded') }}</option>
                                            <option value="square">{{ __('Square') }}</option>
                                        </x-ui.select>
                                        <x-ui.select wire:model.change="card_style" x-model="cardStyleValue" name="card_style" :label="__('Card')" :error="$errors->first('card_style')">
                                            <option value="soft">{{ __('Soft') }}</option>
                                            <option value="bordered">{{ __('Bordered') }}</option>
                                            <option value="flat">{{ __('Flat') }}</option>
                                        </x-ui.select>
                                    </div>
                                    <x-ui.image-picker
                                        wire:model.change="logo_url"
                                        name="logo_url"
                                        :label="__('Logo')"
                                        :value="$logo_url"
                                        :preview="$logo_url"
                                        context="portal"
                                        layout="compact"
                                        :button-label="__('Choose logo')"
                                        :empty-label="__('No logo selected')"
                                        :dialog-title="__('Choose logo image')"
                                        :dialog-description="__('Select or upload a logo from your file library.')"
                                        :error="$errors->first('logo_url')"
                                    />
                                    <x-ui.select wire:model.change="logo_shape" x-model="logoShapeValue" name="logo_shape" :label="__('Logo shape')" :error="$errors->first('logo_shape')">
                                        <option value="circle">{{ __('Circle') }}</option>
                                        <option value="square">{{ __('Square') }}</option>
                                    </x-ui.select>
                                    <x-ui.image-picker
                                        wire:model.change="cover_image"
                                        name="cover_image"
                                        :label="__('Cover image')"
                                        :value="$cover_image"
                                        :preview="$cover_image"
                                        context="portal"
                                        layout="compact"
                                        :button-label="__('Choose cover')"
                                        :empty-label="__('No cover selected')"
                                        :dialog-title="__('Choose cover image')"
                                        :dialog-description="__('Select or upload a cover image for the public page hero.')"
                                        :error="$errors->first('cover_image')"
                                    />
                                    <div class="grid gap-2 rounded-[0.85rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                        <p class="text-xs font-bold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Core visibility') }}</p>
                                        <div class="grid gap-2 sm:grid-cols-2">
                                            <label class="inline-flex items-center gap-2 text-sm" style="color: var(--theme-header-text-color);">
                                                <input type="checkbox" wire:model.change="show_logo" x-model="showLogoValue" class="rounded border">{{ __('Logo') }}
                                            </label>
                                            <label class="inline-flex items-center gap-2 text-sm" style="color: var(--theme-header-text-color);">
                                                <input type="checkbox" wire:model.change="show_benefits" x-model="showBenefitsValue" class="rounded border">{{ __('Benefits') }}
                                            </label>
                                            <label class="inline-flex items-center gap-2 text-sm" style="color: var(--theme-header-text-color);">
                                                <input type="checkbox" wire:model.change="show_terms" x-model="showTermsValue" class="rounded border">{{ __('Terms') }}
                                            </label>
                                            <label class="inline-flex items-center gap-2 text-sm" style="color: var(--theme-header-text-color);">
                                                <input type="checkbox" wire:model.change="show_business_info" x-model="showBusinessInfoValue" class="rounded border">{{ __('Business info') }}
                                            </label>
                                            <label class="inline-flex items-center gap-2 text-sm" style="color: var(--theme-header-text-color);">
                                                <input type="checkbox" wire:model.change="show_social_links" x-model="showSocialLinksValue" class="rounded border">{{ __('Social links') }}
                                            </label>
                                            <label class="inline-flex items-center gap-2 text-sm" style="color: var(--theme-header-text-color);">
                                                <input type="checkbox" wire:model.change="show_faq" x-model="showFaqValue" class="rounded border">{{ __('FAQ') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </aside>
                            </div>
                        </div>

                        <aside class="hidden min-h-0 overflow-y-auto bg-slate-100 p-4 xl:block">
                            @php
                                $selectedBusiness = $businesses->firstWhere('id', (int) $business_id);
                                $previewBenefits = collect(preg_split('/\r\n|\r|\n/', (string) $benefits))->map(fn ($line) => trim($line))->filter()->values();
                                $previewBlocks = collect($landing_blocks)->filter(fn ($block) => ($block['visible'] ?? true) !== false)->values();
                                $previewStructureBlocks = $previewBlocks->reject(fn ($block) => ($block['type'] ?? 'hero') === 'hero')->values();
                                $previewSlots = collect(preg_split('/\r\n|\r|\n/', (string) $available_slots))->map(fn ($line) => trim($line))->filter()->values();
                                $previewButtonRadius = match ($button_style) { 'square' => '8px', 'rounded' => '14px', default => '999px' };
                                $previewCardRadius = match ($card_style) { 'flat' => '12px', 'bordered' => '18px', default => '24px' };
                                $previewFont = match ($font_style) {
                                    'classic' => 'Lora, Georgia, Cambria, Times New Roman, serif',
                                    'elegant' => 'Manrope, Be Vietnam Pro, ui-sans-serif, system-ui, sans-serif',
                                    'friendly' => 'Nunito, Be Vietnam Pro, ui-sans-serif, system-ui, sans-serif',
                                    default => 'Be Vietnam Pro, Inter, ui-sans-serif, system-ui, sans-serif',
                                };
                            @endphp
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-950">{{ __('Live public preview') }}</p>
                                    <p class="text-xs text-slate-500">{{ __('Live draft updates while editing.') }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="inline-flex rounded-xl border bg-white p-1" style="border-color: rgba(var(--theme-border-color-rgb), .62);">
                                        <button type="button" class="h-8 rounded-lg px-3 text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">{{ __('Draft') }}</button>
                                    </div>
                                    <div class="inline-flex rounded-xl border bg-white p-1" style="border-color: rgba(var(--theme-border-color-rgb), .62);">
                                        <button type="button" class="grid h-8 w-8 place-items-center rounded-lg text-xs" x-bind:style="previewMode === 'desktop' ? 'background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);' : 'color: #64748b;'" x-on:click="previewMode = 'desktop'" title="{{ __('Desktop') }}"><i class="fa-light fa-desktop"></i></button>
                                        <button type="button" class="grid h-8 w-8 place-items-center rounded-lg text-xs" x-bind:style="previewMode === 'mobile' ? 'background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);' : 'color: #64748b;'" x-on:click="previewMode = 'mobile'" title="{{ __('Mobile') }}"><i class="fa-light fa-mobile"></i></button>
                                    </div>
                                    @if($public_url)
                                        <a href="{{ $public_url }}" target="_blank" class="grid h-9 w-9 place-items-center rounded-xl border bg-white text-xs text-slate-600 transition hover:text-slate-950" style="border-color: rgba(var(--theme-border-color-rgb), .62);" title="{{ __('Open public page') }}"><i class="fa-light fa-arrow-up-right"></i></a>
                                        <button type="button" class="grid h-9 w-9 place-items-center rounded-xl border bg-white text-xs text-slate-600 transition hover:text-slate-950" style="border-color: rgba(var(--theme-border-color-rgb), .62);" title="{{ __('Copy link') }}" x-on:click="navigator.clipboard?.writeText(publicUrlValue); previewCopied = true; setTimeout(() => previewCopied = false, 1400)">
                                            <i class="fa-light" x-bind:class="previewCopied ? 'fa-check' : 'fa-copy'"></i>
                                        </button>
                                    @endif
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600" x-text="typeLabel">{{ str($type)->headline() }}</span>
                                </div>
                            </div>

                            <div
                                class="relative mx-auto min-h-[calc(100vh-9.5rem)] overflow-hidden rounded-[1.25rem] border border-slate-200 bg-white shadow-xl"
                                x-bind:class="previewMode === 'mobile' ? 'max-w-[24rem]' : 'w-full max-w-[76rem]'"
                            >
                                <iframe
                                    x-ref="draftPreviewFrame"
                                    class="absolute inset-0 h-[calc(100vh-9.5rem)] w-full bg-white"
                                    x-bind:src="previewFrameUrl"
                                    x-on:load="handlePrimaryPreviewLoad()"
                                    src="about:blank"
                                    title="{{ __('Draft landing page preview') }}"
                                ></iframe>
                                <iframe
                                    class="absolute inset-0 h-[calc(100vh-9.5rem)] w-full bg-white opacity-0"
                                    x-bind:src="nextPreviewFrameUrl || 'about:blank'"
                                    x-show="nextPreviewFrameUrl"
                                    x-on:load="promotePreviewFrame()"
                                    title="{{ __('Draft landing page preview loading') }}"
                                ></iframe>
                                <div x-cloak x-show="previewFrameBusy || !previewReady" x-transition.opacity class="absolute inset-0 z-10 grid place-items-center bg-slate-50/95 backdrop-blur-sm">
                                    <div class="flex flex-col items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 text-center shadow-lg">
                                        <span class="h-8 w-8 animate-spin rounded-full border-2 border-slate-200 border-t-[var(--theme-accent)]"></span>
                                        <span class="text-sm font-semibold text-slate-700">{{ __('Loading preview...') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="mx-auto min-h-[calc(100vh-9.5rem)] overflow-hidden rounded-[1.25rem] border border-slate-200 bg-white shadow-xl"
                                x-bind:class="previewMode === 'mobile' ? 'max-w-[24rem]' : (layoutStyleValue === 'clean' ? 'max-w-[38rem]' : 'max-w-[46rem]')"
                                x-show="previewSource === '__legacy_draft'"
                            >
                                <div x-bind:style="`font-family: ${fontFamily}; background: ${previewBackground}; color: #111827;`" style="font-family: {{ $previewFont }}; background: linear-gradient(135deg, {{ $primary_color }}22, transparent 34%), radial-gradient(circle at 86% 10%, {{ $primary_color }}2e, transparent 28%), {{ $background_color }}; color: #111827;" class="min-h-[calc(100vh-9.5rem)] p-4">
                                    <div
                                        class="grid gap-4"
                                        x-bind:class="{
                                            'xl:grid-cols-[minmax(0,1.05fr)_minmax(18rem,.95fr)]': previewMode !== 'mobile' && layoutStyleValue === 'split',
                                            'xl:grid-cols-1': previewMode !== 'mobile' && (layoutStyleValue === 'centered' || layoutStyleValue === 'stacked'),
                                            'xl:grid-cols-[minmax(18rem,.82fr)_minmax(0,1.18fr)]': previewMode !== 'mobile' && layoutStyleValue === 'poster',
                                            'xl:grid-cols-[minmax(18rem,.75fr)_minmax(0,1.25fr)]': previewMode !== 'mobile' && layoutStyleValue === 'sidebar',
                                            'xl:grid-cols-[minmax(0,1.3fr)_minmax(18rem,.7fr)]': previewMode !== 'mobile' && layoutStyleValue === 'editorial'
                                        }"
                                    >
                                        <div class="overflow-hidden border border-slate-200 bg-white/90 shadow-sm" x-bind:style="`border-radius: ${cardRadius};`" x-bind:class="{
                                            'text-center': layoutStyleValue === 'centered',
                                            'grid xl:grid-cols-[15rem_minmax(0,1fr)]': previewMode !== 'mobile' && layoutStyleValue === 'stacked',
                                            'xl:order-2': previewMode !== 'mobile' && layoutStyleValue === 'poster',
                                            'xl:order-1': previewMode !== 'mobile' && layoutStyleValue !== 'poster'
                                        }" style="border-radius: {{ $previewCardRadius }};">
                                            <div class="h-36" x-bind:class="{
                                                'xl:h-full': previewMode !== 'mobile' && layoutStyleValue === 'stacked',
                                                'h-52': layoutStyleValue === 'poster',
                                                'h-28': layoutStyleValue === 'centered'
                                            }" x-bind:style="`background: ${coverBackground};`" style="background: {{ filled($cover_image) ? 'url('.$cover_image.') center/cover' : 'linear-gradient(135deg, '.$primary_color.', '.$primary_color.'88)' }};"></div>
                                            <div class="p-5" x-bind:class="{ 'px-8 py-7': previewMode !== 'mobile' && layoutStyleValue === 'editorial' }">
                                                <div class="flex items-center gap-3" x-bind:class="{ 'justify-center': layoutStyleValue === 'centered' }">
                                                    @if(true || $show_logo)
                                                        <div x-show="showLogoValue" class="grid place-items-center overflow-hidden font-black" x-bind:class="logoUrlValue ? 'h-10 w-16 rounded-2xl p-1' : 'h-12 w-12 rounded-2xl p-1.5'" x-bind:style="`background-color: ${primaryColorValue}16; color: ${primaryColorValue};`" style="background-color: {{ $primary_color }}16; color: {{ $primary_color }};">
                                                            @if(filled($logo_url))
                                                                <img src="{{ $logo_url }}" x-bind:src="logoUrlValue" x-show="logoUrlValue" alt="" class="h-full w-full object-contain object-center">
                                                            @else
                                                                <img x-bind:src="logoUrlValue" x-show="logoUrlValue" alt="" class="h-full w-full object-contain object-center">
                                                                <span x-show="!logoUrlValue" x-text="businessInitials">{{ str($selectedBusiness?->name ?: 'LB')->substr(0, 2)->upper() }}</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500" x-text="businessName">{{ $selectedBusiness?->name ?: __('Local business') }}</p>
                                                        <span class="mt-1 inline-flex rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.08em]" x-bind:style="`background-color: ${primaryColorValue}14; color: ${primaryColorValue};`" style="background-color: {{ $primary_color }}14; color: {{ $primary_color }};" x-text="typeLabel">{{ str($type)->headline() }}</span>
                                                    </div>
                                                </div>
                                                <h2 class="mt-5 font-semibold leading-none tracking-[-0.055em] text-slate-950" x-bind:class="previewMode === 'mobile' ? 'text-[2rem]' : (layoutStyleValue === 'editorial' ? 'text-[3.35rem]' : 'text-[2.35rem]')" x-text="headlineValue || @js(__('Campaign headline'))">{{ $headline ?: __('Campaign headline') }}</h2>
                                                <p class="mt-4 text-[15px] leading-7 text-slate-600" x-text="subheadlineValue || @js(__('Public page subheadline appears here.'))">{{ $subheadline ?: __('Public page subheadline appears here.') }}</p>
                                                <p class="mt-3 text-sm leading-6 text-slate-500" x-show="descriptionValue" x-text="descriptionValue">{{ $description }}</p>
                                                @if(false && $show_benefits && $previewBenefits->isNotEmpty())
                                                    <div class="mt-5 grid gap-2">
                                                        @foreach($previewBenefits as $benefit)
                                                            <div class="flex items-center gap-2 text-sm font-semibold text-slate-700"><span class="grid h-6 w-6 place-items-center rounded-full text-xs" style="background-color: {{ $primary_color }}14; color: {{ $primary_color }};">✓</span>{{ $benefit }}</div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                <div class="mt-5 grid gap-2" x-show="showBenefitsValue && benefitsList.length" x-bind:class="{ 'mx-auto max-w-sm': layoutStyleValue === 'centered' }">
                                                    <template x-for="benefit in benefitsList" :key="benefit">
                                                        <div class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                                            <span class="grid h-6 w-6 place-items-center rounded-full text-xs" x-bind:style="`background-color: ${primaryColorValue}14; color: ${primaryColorValue};`">✓</span>
                                                            <span x-text="benefit"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="space-y-4" x-bind:class="{
                                            'grid gap-4 xl:grid-cols-2': previewMode !== 'mobile' && layoutStyleValue === 'centered',
                                            'xl:order-1': previewMode !== 'mobile' && layoutStyleValue === 'poster',
                                            'xl:order-2': previewMode !== 'mobile' && layoutStyleValue !== 'poster',
                                            'grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(16rem,.72fr)]': previewMode !== 'mobile' && layoutStyleValue === 'stacked'
                                        }">
                                            @if($previewStructureBlocks->isNotEmpty())
                                                <div class="border border-slate-200 bg-white/95 p-5 shadow-sm" x-bind:style="`border-radius: ${cardRadius};`" style="border-radius: {{ $previewCardRadius }};">
                                                    <div class="mb-4 flex items-center justify-between gap-3">
                                                        <h3 class="text-xl font-semibold tracking-[-0.035em] text-slate-950">{{ __('Page sections') }}</h3>
                                                        <span class="rounded-full px-3 py-1 text-xs font-bold" x-bind:style="`background-color: ${primaryColorValue}14; color: ${primaryColorValue};`" style="background-color: {{ $primary_color }}14; color: {{ $primary_color }};">{{ format_number_locale($previewStructureBlocks->count()) }}</span>
                                                    </div>
                                                    <div class="grid gap-3">
                                                        @foreach($previewStructureBlocks as $block)
                                                            @php
                                                                $blockType = (string) ($block['type'] ?? 'custom_html');
                                                                $blockSettings = (array) ($block['settings'] ?? []);
                                                                $previewTitle = match ($blockType) {
                                                                    'benefits' => $blockSettings['headline'] ?? __('Why choose us'),
                                                                    'form' => $blockSettings['form_title'] ?? __('Send your details'),
                                                                    'offer', 'coupon_details' => $blockSettings['offer_title'] ?? $blockSettings['headline'] ?? __('Offer details'),
                                                                    'booking_services' => $blockSettings['service_title'] ?? __('Booking services'),
                                                                    'faq' => $blockSettings['question'] ?? $blockSettings['headline'] ?? __('FAQ'),
                                                                    'testimonials' => $blockSettings['author'] ?? __('Customer story'),
                                                                    'map' => $blockSettings['map_title'] ?? __('Location'),
                                                                    'opening_hours' => __('Opening hours'),
                                                                    'thank_you' => __('Thank you'),
                                                                    default => $blockSettings['headline'] ?? ($block['title'] ?? __('Section')),
                                                                };
                                                                $previewBody = match ($blockType) {
                                                                    'benefits' => $blockSettings['items'] ?? '',
                                                                    'form' => $blockSettings['success_message'] ?? '',
                                                                    'offer', 'coupon_details' => trim(collect([$blockSettings['discount'] ?? '', $blockSettings['expiry'] ?? '', $blockSettings['terms'] ?? ''])->filter()->implode(' | ')),
                                                                    'booking_services' => trim(collect([$blockSettings['duration'] ?? '', $blockSettings['price'] ?? '', $blockSettings['description'] ?? ''])->filter()->implode(' | ')),
                                                                    'business_info' => __('Business address, phone, website, and hours toggles.'),
                                                                    'social_links' => collect([$blockSettings['facebook_url'] ?? '', $blockSettings['instagram_url'] ?? '', $blockSettings['website_url'] ?? ''])->filter()->implode(' | '),
                                                                    'faq' => $blockSettings['answer'] ?? $blockSettings['body'] ?? '',
                                                                    'testimonials' => $blockSettings['quote'] ?? '',
                                                                    'map' => $blockSettings['map_text'] ?? '',
                                                                    'opening_hours' => $blockSettings['hours_text'] ?? '',
                                                                    'thank_you' => $blockSettings['message'] ?? '',
                                                                    'custom_html' => strip_tags((string) ($blockSettings['html'] ?? '')),
                                                                    default => $blockSettings['body'] ?? '',
                                                                };
                                                                $previewCta = $blockSettings['cta'] ?? $blockSettings['submit_button'] ?? '';
                                                            @endphp
                                                            <div class="rounded-2xl border border-slate-200 p-3">
                                                                <p class="text-[11px] font-black uppercase tracking-[0.12em] text-slate-400">{{ $blockTypeOptions[$blockType] ?? str($blockType)->headline() }}</p>
                                                                <p class="mt-1 font-semibold text-slate-950">{{ $previewTitle }}</p>
                                                                @if(filled($previewBody))
                                                                    <p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-500">{{ $previewBody }}</p>
                                                                @endif
                                                                @if(filled($previewCta))
                                                                    <span class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-bold text-white" x-bind:style="`background-color: ${primaryColorValue};`" style="background-color: {{ $primary_color }};">{{ $previewCta }}</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="border border-slate-200 bg-white/95 p-5 shadow-sm" x-bind:style="`border-radius: ${cardRadius};`" style="border-radius: {{ $previewCardRadius }};">
                                                <div class="mb-4 flex items-center justify-between gap-3">
                                                    <h3 class="text-xl font-semibold tracking-[-0.035em] text-slate-950" x-text="['coupon', 'promotion'].includes(typeValue) ? @js(__('Claim your offer')) : @js(__('Send your details'))">{{ in_array($type, ['coupon', 'promotion'], true) ? __('Claim your offer') : __('Send your details') }}</h3>
                                                    <span class="rounded-full px-3 py-1 text-xs font-bold" x-bind:style="`background-color: ${primaryColorValue}14; color: ${primaryColorValue};`" style="background-color: {{ $primary_color }}14; color: {{ $primary_color }};">{{ __('Tracked') }}</span>
                                                </div>
                                                <div class="mb-4 grid grid-cols-2 gap-2" x-show="['coupon', 'promotion'].includes(typeValue)">
                                                    <div class="rounded-2xl border border-slate-200 p-3"><strong class="block text-slate-950" x-text="discountValue || @js(__('Offer'))">{{ $discount ?: __('Offer') }}</strong><span class="text-xs text-slate-500" x-text="couponTitleValue || @js(__('Limited-time local offer'))">{{ $coupon_title ?: __('Limited-time local offer') }}</span></div>
                                                    <div class="rounded-2xl border border-slate-200 p-3"><strong class="block text-slate-950">{{ __('Valid until') }}</strong><span class="text-xs text-slate-500" x-text="expiryValue || @js(__('Ask staff'))">{{ $expiry ?: __('Ask staff') }}</span></div>
                                                </div>
                                                <p class="mb-4 border-t border-slate-200 pt-3 text-xs text-slate-500" x-show="['coupon', 'promotion'].includes(typeValue) && showTermsValue && termsValue" x-text="termsValue">{{ $terms }}</p>
                                                <div class="grid gap-3">
                                                    @if($type === 'review')
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Choose your rating') }}</label><div class="grid grid-cols-5 gap-2">@for($i = 1; $i <= 5; $i++)<span class="grid h-11 place-items-center rounded-xl border border-slate-200 font-black text-amber-600">{{ $i }}</span>@endfor</div></div>
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Private feedback') }}</label><div class="h-24 rounded-xl border border-slate-200 bg-white"></div></div>
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Name') }}</label><div class="h-11 rounded-xl border border-slate-200"></div></div>
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Email') }}</label><div class="h-11 rounded-xl border border-slate-200"></div></div>
                                                    @elseif($type === 'booking')
                                                        <div class="grid grid-cols-2 gap-2"><div class="rounded-xl border border-slate-200 p-3 text-sm"><strong x-text="serviceValue || @js(__('Service'))">{{ $service ?: __('Service') }}</strong><br><span class="text-slate-500" x-text="durationValue || @js(__('Duration varies'))">{{ $duration ?: __('Duration varies') }}</span></div><div class="rounded-xl border border-slate-200 p-3 text-sm"><strong>{{ __('Price') }}</strong><br><span class="text-slate-500" x-text="priceValue || @js(__('Ask us'))">{{ $price ?: __('Ask us') }}</span></div></div>
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Date') }}</label><div class="h-11 rounded-xl border border-slate-200"></div></div>
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Available time') }}</label><div class="h-11 rounded-xl border border-slate-200"></div></div>
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Name *') }}</label><div class="h-11 rounded-xl border border-slate-200"></div></div>
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Phone *') }}</label><div class="h-11 rounded-xl border border-slate-200"></div></div>
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Email') }}</label><div class="h-11 rounded-xl border border-slate-200"></div></div>
                                                    @elseif($type === 'feedback')
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Rating') }}</label><div class="h-11 rounded-xl border border-slate-200"></div></div>
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Topic') }}</label><div class="h-11 rounded-xl border border-slate-200"></div></div>
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Feedback') }}</label><div class="h-24 rounded-xl border border-slate-200 bg-white"></div></div>
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Name') }}</label><div class="h-11 rounded-xl border border-slate-200"></div></div>
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Email') }}</label><div class="h-11 rounded-xl border border-slate-200"></div></div>
                                                    @else
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Name') }}</label><div class="h-11 rounded-xl border border-slate-200"></div></div>
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Email') }}</label><div class="h-11 rounded-xl border border-slate-200"></div></div>
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Phone') }}</label><div class="h-11 rounded-xl border border-slate-200"></div></div>
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Interested service') }}</label><div class="h-11 rounded-xl border border-slate-200"></div></div>
                                                        <div><label class="mb-2 block text-xs font-bold text-slate-600">{{ __('Message') }}</label><div class="h-24 rounded-xl border border-slate-200 bg-white"></div></div>
                                                    @endif
                                                    <span class="inline-flex h-12 items-center justify-center text-sm font-black text-white shadow-sm" x-bind:style="`border-radius: ${buttonRadius}; background-color: ${primaryColorValue};`" style="border-radius: {{ $previewButtonRadius }}; background-color: {{ $primary_color }};" x-text="ctaValue || @js(__('Submit'))">{{ $cta_text ?: __('Submit') }}</span>
                                                </div>
                                            </div>

                                            @if($show_business_info && $selectedBusiness)
                                                <div x-show="showBusinessInfoValue" class="border border-slate-200 bg-white/95 p-5 shadow-sm" x-bind:style="`border-radius: ${cardRadius};`" style="border-radius: {{ $previewCardRadius }};">
                                                    <h3 class="font-semibold text-slate-950">{{ __('Business info') }}</h3>
                                                    <div class="mt-3 space-y-2 text-sm text-slate-600">
                                                        <p x-show="selectedBusiness.address" x-text="selectedBusiness.address">{{ $selectedBusiness->address }}</p>
                                                        <p x-show="selectedBusiness.phone" x-text="selectedBusiness.phone">{{ $selectedBusiness->phone }}</p>
                                                        <p x-show="selectedBusiness.website" x-text="selectedBusiness.website">{{ parse_url($selectedBusiness->website, PHP_URL_HOST) ?: $selectedBusiness->website }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($show_faq)
                                                <div x-show="showFaqValue" class="border border-slate-200 bg-white/95 p-5 shadow-sm" x-bind:style="`border-radius: ${cardRadius};`" style="border-radius: {{ $previewCardRadius }};">
                                                    <h3 class="font-semibold text-slate-950">{{ __('What happens next?') }}</h3>
                                                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ __('Your submission is sent directly to the local team and tracked for follow-up.') }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </aside>
                    </div>

                    <div class="shrink-0 flex items-center justify-end gap-3 border-t px-5 py-4 sm:px-7" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <x-ui.button type="button" variant="outline" x-on:click="formDialogOpen = false">{{ __('Cancel') }}</x-ui.button>
                        <template x-if="!@js((bool) $editingId) && createStep === 'type'">
                            <x-ui.button type="button" x-on:click="createStep = 'template'">{{ __('Continue') }}<i class="fa-light fa-arrow-right"></i></x-ui.button>
                        </template>
                        <template x-if="!@js((bool) $editingId) && createStep === 'template'">
                            <x-ui.button type="button" x-on:click="createStep = 'form'">{{ __('Continue') }}<i class="fa-light fa-arrow-right"></i></x-ui.button>
                        </template>
                        <template x-if="@js((bool) $editingId) || createStep === 'form'">
                            <x-ui.button type="submit"><i class="fa-light fa-floppy-disk"></i>{{ $editingId ? __('Save changes') : __('Publish page') }}</x-ui.button>
                        </template>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
