@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag();
@endphp

<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    @once
        <script>
            window.localBoostTemplateSorter = function (wire, action) {
                return {
                    sortable: null,
                    init() {
                        this.$nextTick(() => this.mountSortable());
                    },
                    mountSortable() {
                        const start = () => {
                            if (!window.Sortable || !this.$refs.sortableItems) {
                                return;
                            }

                            this.sortable?.destroy();
                            this.sortable = window.Sortable.create(this.$refs.sortableItems, {
                                handle: '.template-sortable-handle',
                                animation: 180,
                                ghostClass: 'template-sortable-ghost',
                                chosenClass: 'template-sortable-chosen',
                                onEnd: () => {
                                    const order = Array.from(this.$refs.sortableItems.querySelectorAll('[data-sortable-id]'))
                                        .map((item) => item.dataset.sortableId)
                                        .filter(Boolean);

                                    wire[action](order);
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
            .template-sortable-handle{cursor:grab}.template-sortable-handle:active{cursor:grabbing}.template-sortable-ghost{opacity:.4}.template-sortable-chosen{border-color:var(--theme-accent)!important;box-shadow:0 0 0 1px rgba(var(--theme-accent-rgb),.35)}
        </style>
    @endonce

    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif

    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 34%),
        linear-gradient(35deg, rgba(var(--theme-success-color-rgb),0.08), transparent 38%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-7 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-grid-2"></i>
                    {{ __('Marketing Assets') }}
                </div>
                <h1 class="mt-4 max-w-3xl text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('Templates') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ __('Build campaign, landing page, form, AI content, email, WhatsApp, and automation templates for the whole LocalBoost AI workflow.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button type="button" size="lg" wire:click="openCreateModal" wire:loading.attr="disabled" wire:target="openCreateModal">
                        <i class="fa-light fa-plus" wire:loading.remove wire:target="openCreateModal"></i>
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="openCreateModal"></i>
                        <span wire:loading.remove wire:target="openCreateModal">{{ __('Create template') }}</span>
                        <span wire:loading wire:target="openCreateModal">{{ __('Opening...') }}</span>
                    </x-ui.button>
                    <x-ui.button type="button" size="lg" variant="outline" wire:click="openImportModal" wire:loading.attr="disabled" wire:target="openImportModal">
                        <i class="fa-light fa-file-import" wire:loading.remove wire:target="openImportModal"></i>
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="openImportModal"></i>
                        <span>{{ __('Import') }}</span>
                    </x-ui.button>
                    <x-ui.button type="button" size="lg" variant="outline" wire:click="openAIGenerateModal" wire:loading.attr="disabled" wire:target="openAIGenerateModal">
                        <i class="fa-light fa-wand-magic-sparkles" wire:loading.remove wire:target="openAIGenerateModal"></i>
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="openAIGenerateModal"></i>
                        <span>{{ __('Generate with AI') }}</span>
                    </x-ui.button>
                </div>
            </div>

            <div class="rounded-[1.2rem] border p-4 shadow-[0_24px_70px_-48px_rgba(var(--theme-border-color-rgb),0.9)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Template engine health') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Reusable presets ready for local campaigns') }}</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-wand-magic-sparkles"></i>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-3 gap-3">
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($summary['system']) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('System') }}</p>
                    </div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($summary['custom']) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Custom') }}</p>
                    </div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($summary['active']) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Active') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => __('Total templates'), 'value' => $summary['total'], 'description' => __('Engine presets'), 'icon' => 'fa-light fa-grid-2', 'tone' => 'accent'],
            ['label' => __('System'), 'value' => $summary['system'], 'description' => __('Default library'), 'icon' => 'fa-light fa-shield-check', 'tone' => 'success'],
            ['label' => __('Custom'), 'value' => $summary['custom'], 'description' => __('User templates'), 'icon' => 'fa-light fa-pen-nib', 'tone' => 'warning'],
            ['label' => __('Active'), 'value' => $summary['active'], 'description' => __('Ready to use'), 'icon' => 'fa-light fa-toggle-on', 'tone' => 'accent'],
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
                        <p class="text-[1.75rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($metric['value']) }}</p>
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

    <section class="overflow-visible rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Template library') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Filter reusable presets by type, business category, campaign goal, and origin.') }}</p>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2">
                @foreach ($tabs as $key => $label)
                    <button
                        type="button"
                        wire:click="setTab('{{ $key }}')"
                        wire:loading.attr="disabled"
                        wire:target="setTab('{{ $key }}')"
                        class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition disabled:pointer-events-none disabled:opacity-70"
                        style="{{ $activeTab === $key ? 'border-color: rgba(var(--theme-accent-rgb),.26); background: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);' : 'border-color: rgba(var(--theme-border-color-rgb),.68); color: var(--theme-muted-text-color);' }}"
                    >
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="setTab('{{ $key }}')"></i>
                        <span>{{ $label }}</span>
                    </button>
                @endforeach
            </div>

            <div class="mt-4 grid gap-3 lg:grid-cols-[minmax(0,1.3fr)_repeat(5,minmax(0,.8fr))]">
                <div class="relative">
                    <i class="fa-light fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm" style="color: var(--theme-muted-text-color);"></i>
                    <input type="search" wire:model.live.debounce.350ms="search" name="template_search" class="h-11 w-full rounded-xl border pl-10 pr-10 text-sm outline-none transition focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Search templates...') }}">
                    @if (trim($search) !== '')
                        <button type="button" wire:click="$set('search', '')" class="absolute right-3 top-1/2 -translate-y-1/2" style="color: var(--theme-muted-text-color);">
                            <i class="fa-light fa-xmark"></i>
                        </button>
                    @endif
                </div>
                <x-ui.select wire:model.live="typeFilter" name="template_type_filter">
                    <option value="">{{ __('All types') }}</option>
                    @foreach ($typeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select wire:model.live="categoryFilter" name="template_category_filter">
                    <option value="">{{ __('All categories') }}</option>
                    @foreach ($categoryOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select wire:model.live="goalFilter" name="template_goal_filter">
                    <option value="">{{ __('All goals') }}</option>
                    @foreach ($goalOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select wire:model.live="originFilter" name="template_origin_filter">
                    <option value="">{{ __('All origins') }}</option>
                    <option value="system">{{ __('System') }}</option>
                    <option value="custom">{{ __('Custom') }}</option>
                    <option value="team">{{ __('Team shared') }}</option>
                    <option value="marketplace">{{ __('Marketplace') }}</option>
                </x-ui.select>
                <x-ui.select wire:model.live="statusFilter" name="template_status_filter">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </div>

        <div class="relative p-4">
            <div
                wire:loading.flex
                wire:target="setTab,search,typeFilter,categoryFilter,goalFilter,originFilter,statusFilter,duplicate,delete,shareWithTeam,unshareFromTeam,submitToMarketplace,approveMarketplaceTemplate,rejectMarketplaceTemplate,toggleFeatured,rateTemplate"
                class="absolute right-4 top-4 z-10 items-center gap-2 rounded-full border px-3 py-2 text-xs font-semibold shadow-sm"
                style="border-color: rgba(var(--theme-border-color-rgb), .68); background: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);"
            >
                <i class="fa-light fa-spinner-third fa-spin" style="color: var(--theme-accent);"></i>
                {{ __('Updating...') }}
            </div>
            @if ($activeTab === 'marketplace' && $marketplacePacks->isNotEmpty())
                <div class="mb-5 grid gap-4 lg:grid-cols-2 2xl:grid-cols-3">
                    @foreach ($marketplacePacks as $pack)
                        <article class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: color-mix(in srgb, var(--theme-surface-overlay) 96%, transparent);">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $pack->name }}</p>
                                    <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $pack->description }}</p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold" style="background: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">{{ str($pack->category)->replace('_', ' ')->headline() }}</span>
                            </div>
                            <div class="mt-4 flex items-center justify-between gap-3 text-xs" style="color: var(--theme-muted-text-color);">
                                <span>{{ __('Version :version', ['version' => $pack->version]) }}</span>
                                <span>{{ __(':templates templates | Installed :count times', ['templates' => format_number_locale($pack->templates_count ?? 0), 'count' => format_number_locale($pack->install_count)]) }}</span>
                            </div>
                            <div class="mt-4 flex justify-end gap-2">
                                <x-ui.button type="button" size="sm" variant="outline" wire:click="previewPack({{ $pack->id }})" wire:loading.attr="disabled" wire:target="previewPack({{ $pack->id }})">
                                    <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="previewPack({{ $pack->id }})"></i>
                                    <span wire:loading.remove wire:target="previewPack({{ $pack->id }})">{{ __('Preview Pack') }}</span>
                                    <span wire:loading wire:target="previewPack({{ $pack->id }})">{{ __('Loading...') }}</span>
                                </x-ui.button>
                                <x-ui.button type="button" size="sm" variant="outline" wire:click="exportPack({{ $pack->id }})" wire:loading.attr="disabled" wire:target="exportPack({{ $pack->id }})">
                                    <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="exportPack({{ $pack->id }})"></i>
                                    <span wire:loading.remove wire:target="exportPack({{ $pack->id }})">{{ __('Export Pack') }}</span>
                                    <span wire:loading wire:target="exportPack({{ $pack->id }})">{{ __('Exporting...') }}</span>
                                </x-ui.button>
                                <x-ui.button type="button" size="sm" wire:click="installPack({{ $pack->id }})" wire:loading.attr="disabled" wire:target="installPack({{ $pack->id }})">
                                    <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="installPack({{ $pack->id }})"></i>
                                    <span wire:loading.remove wire:target="installPack({{ $pack->id }})">{{ __('Install Pack') }}</span>
                                    <span wire:loading wire:target="installPack({{ $pack->id }})">{{ __('Installing...') }}</span>
                                </x-ui.button>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            @if ($templates->isEmpty())
                <x-ui.empty icon="fa-light fa-grid-2" :title="__('No templates found')" :description="__('Try changing filters or create a custom reusable template.')" />
            @else
                <div class="grid gap-4 lg:grid-cols-2 2xl:grid-cols-3">
                    @foreach ($templates as $template)
                        @php
                            $content = $template->content ?: [];
                            $settings = $template->settings ?: [];
                            $accent = match ($template->goal) {
                                'review' => '#d09100',
                                'booking' => '#0f766e',
                                'coupon' => '#84a900',
                                'feedback' => '#0891b2',
                                'retention' => '#c2410c',
                                default => '#2563eb',
                            };
                        @endphp
                        <article class="group rounded-[1.15rem] border p-5 transition hover:-translate-y-0.5 hover:shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: var(--theme-surface-base);">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex min-w-0 gap-4">
                                    <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem]" style="background: {{ $accent }}14; color: {{ $accent }};">
                                        <i class="{{ $template->icon ?: 'fa-light fa-grid-2' }} text-lg"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <h3 class="truncate text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $template->name }}</h3>
                                        <p class="mt-2 line-clamp-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $template->description }}</p>
                                    </div>
                                </div>
                                <div class="flex shrink-0 flex-col items-end gap-2">
                                    <x-ui.badge :variant="$template->is_system ? 'primary' : 'success'">{{ $template->originLabel() }}</x-ui.badge>
                                    @if ($template->featured)
                                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold" style="background: rgba(var(--theme-warning-color-rgb),.13); color: var(--theme-warning-color);">{{ __('Featured') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-5 flex flex-wrap gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold" style="background: {{ $accent }}12; color: {{ $accent }};">{{ $template->typeLabel() }}</span>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold" style="background: rgba(var(--theme-border-color-rgb), .38); color: var(--theme-muted-text-color);">{{ $template->goalLabel() }}</span>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold" style="background: rgba(var(--theme-border-color-rgb), .38); color: var(--theme-muted-text-color);">{{ $template->categoryLabel() }}</span>
                            </div>

                            <div class="mt-5 rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background: color-mix(in srgb, var(--theme-surface-overlay) 82%, transparent);">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Template output') }}</p>
                                <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $content['headline'] ?? $content['campaign_name'] ?? __('Ready-to-use local campaign') }}</p>
                                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">
                                    {{ __('Creates page: :page | QR: :qr | Goal: :goal', [
                                        'page' => ! empty($settings['creates_landing_page']) ? __('Yes') : __('No'),
                                        'qr' => ! empty($settings['creates_qr_code']) ? __('Yes') : __('No'),
                                        'goal' => $settings['tracking_goal'] ?? $template->goal,
                                    ]) }}
                                </p>
                            </div>

                            @if (($template->marketplace_status ?? 'none') !== 'none' || $template->rating_count > 0)
                                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs" style="color: var(--theme-muted-text-color);">
                                    @if (($template->marketplace_status ?? 'none') !== 'none')
                                        <span class="rounded-full border px-3 py-1 font-semibold" style="border-color: rgba(var(--theme-border-color-rgb),.58);">{{ __('Marketplace: :status', ['status' => $template->marketplaceStatusLabel()]) }}</span>
                                    @endif
                                    @if ($template->rating_count > 0)
                                        <span class="rounded-full border px-3 py-1 font-semibold" style="border-color: rgba(var(--theme-border-color-rgb),.58);">
                                            <i class="fa-solid fa-star" style="color: var(--theme-warning-color);"></i>
                                            {{ format_number_locale($template->ratingAverage(), 1) }} / 5 · {{ trans_choice(':count rating|:count ratings', $template->rating_count, ['count' => format_number_locale($template->rating_count)]) }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                                <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __(':origin | :visibility | Used :count times', ['origin' => $template->originLabel(), 'visibility' => $template->visibilityLabel(), 'count' => format_number_locale($template->usage_count)]) }}</p>
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.button type="button" size="sm" wire:click="useTemplate({{ $template->id }})" wire:loading.attr="disabled" wire:target="useTemplate({{ $template->id }})">
                                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="useTemplate({{ $template->id }})"></i>
                                        <span wire:loading.remove wire:target="useTemplate({{ $template->id }})">{{ __('Use Template') }}</span>
                                        <span wire:loading wire:target="useTemplate({{ $template->id }})">{{ __('Opening...') }}</span>
                                    </x-ui.button>
                                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border text-sm shadow-sm transition hover:-translate-y-px hover:shadow-[0_14px_28px_-22px_rgba(15,23,42,0.3)] focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:pointer-events-none disabled:opacity-50" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color); background-color: transparent;" wire:click="preview({{ $template->id }})" wire:loading.attr="disabled" wire:target="preview({{ $template->id }})" title="{{ __('Preview') }}" aria-label="{{ __('Preview') }}">
                                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="preview({{ $template->id }})"></i>
                                        <i class="fa-light fa-eye" wire:loading.remove wire:target="preview({{ $template->id }})"></i>
                                        <span class="sr-only">{{ __('Preview') }}</span>
                                    </button>
                                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border text-sm shadow-sm transition hover:-translate-y-px hover:shadow-[0_14px_28px_-22px_rgba(15,23,42,0.3)] focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:pointer-events-none disabled:opacity-50" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color); background-color: transparent;" wire:click="duplicate({{ $template->id }})" wire:loading.attr="disabled" wire:target="duplicate({{ $template->id }})" title="{{ __('Duplicate') }}" aria-label="{{ __('Duplicate') }}">
                                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="duplicate({{ $template->id }})"></i>
                                        <i class="fa-light fa-copy" wire:loading.remove wire:target="duplicate({{ $template->id }})"></i>
                                        <span class="sr-only">{{ __('Duplicate') }}</span>
                                    </button>
                                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border text-sm shadow-sm transition hover:-translate-y-px hover:shadow-[0_14px_28px_-22px_rgba(15,23,42,0.3)] focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:pointer-events-none disabled:opacity-50" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color); background-color: transparent;" wire:click="exportTemplate({{ $template->id }})" wire:loading.attr="disabled" wire:target="exportTemplate({{ $template->id }})" title="{{ __('Export') }}" aria-label="{{ __('Export') }}">
                                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="exportTemplate({{ $template->id }})"></i>
                                        <i class="fa-light fa-file-export" wire:loading.remove wire:target="exportTemplate({{ $template->id }})"></i>
                                        <span class="sr-only">{{ __('Export') }}</span>
                                    </button>
                                    @if ($template->canBeManagedBy(auth()->user()))
                                        @if ($template->visibility === 'team')
                                            <x-ui.button type="button" size="sm" variant="outline" wire:click="unshareFromTeam({{ $template->id }})" wire:loading.attr="disabled" wire:target="unshareFromTeam({{ $template->id }})">
                                                <span>{{ __('Unshare') }}</span>
                                            </x-ui.button>
                                        @else
                                            <x-ui.button type="button" size="sm" variant="outline" wire:click="shareWithTeam({{ $template->id }})" wire:loading.attr="disabled" wire:target="shareWithTeam({{ $template->id }})">
                                                <span>{{ __('Share Team') }}</span>
                                            </x-ui.button>
                                        @endif
                                        @if (! in_array($template->marketplace_status, ['pending', 'approved'], true))
                                            <x-ui.button type="button" size="sm" variant="outline" wire:click="submitToMarketplace({{ $template->id }})" wire:loading.attr="disabled" wire:target="submitToMarketplace({{ $template->id }})">
                                                <span>{{ __('Submit Public') }}</span>
                                            </x-ui.button>
                                        @endif
                                        <x-ui.button type="button" size="sm" variant="outline" wire:click="openEditModal({{ $template->id }})" wire:loading.attr="disabled" wire:target="openEditModal({{ $template->id }})">
                                            <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="openEditModal({{ $template->id }})"></i>
                                            <span wire:loading.remove wire:target="openEditModal({{ $template->id }})">{{ __('Edit') }}</span>
                                            <span wire:loading wire:target="openEditModal({{ $template->id }})">{{ __('Opening...') }}</span>
                                        </x-ui.button>
                                        <x-ui.button type="button" size="sm" variant="danger" wire:click="delete({{ $template->id }})" wire:loading.attr="disabled" wire:target="delete({{ $template->id }})" wire:confirm="{{ __('Delete this template?') }}">
                                            <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="delete({{ $template->id }})"></i>
                                            <span wire:loading.remove wire:target="delete({{ $template->id }})">{{ __('Delete') }}</span>
                                            <span wire:loading wire:target="delete({{ $template->id }})">{{ __('Deleting...') }}</span>
                                        </x-ui.button>
                                    @endif
                                    @if (($template->marketplace_status ?? '') === 'approved')
                                        <div class="inline-flex overflow-hidden rounded-full border" style="border-color: rgba(var(--theme-border-color-rgb),.58);">
                                            @for ($rating = 1; $rating <= 5; $rating++)
                                                <button type="button" wire:click="rateTemplate({{ $template->id }}, {{ $rating }})" class="px-2 py-1 text-xs" style="color: var(--theme-warning-color);" title="{{ __('Rate :rating stars', ['rating' => $rating]) }}">
                                                    <i class="fa-solid fa-star"></i>
                                                </button>
                                            @endfor
                                        </div>
                                    @endif
                                    @if ($canModerateMarketplace && $template->visibility === 'public')
                                        @if (($template->marketplace_status ?? '') === 'pending')
                                            <x-ui.button type="button" size="sm" wire:click="approveMarketplaceTemplate({{ $template->id }})" wire:loading.attr="disabled" wire:target="approveMarketplaceTemplate({{ $template->id }})">{{ __('Approve') }}</x-ui.button>
                                            <x-ui.button type="button" size="sm" variant="danger" wire:click="rejectMarketplaceTemplate({{ $template->id }})" wire:loading.attr="disabled" wire:target="rejectMarketplaceTemplate({{ $template->id }})">{{ __('Reject') }}</x-ui.button>
                                        @endif
                                        @if (($template->marketplace_status ?? '') === 'approved')
                                            <x-ui.button type="button" size="sm" variant="outline" wire:click="toggleFeatured({{ $template->id }})" wire:loading.attr="disabled" wire:target="toggleFeatured({{ $template->id }})">
                                                {{ $template->featured ? __('Unfeature') : __('Feature') }}
                                            </x-ui.button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($templates->hasPages())
                    <div class="mt-5 border-t pt-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                        {{ $templates->links() }}
                    </div>
                @endif
            @endif
        </div>
    </section>

    @if ($importing)
        <div class="fixed inset-0 z-50 grid place-items-center bg-slate-950/55 p-4" wire:click.self="closeModal">
            <form wire:submit="importTemplate" enctype="multipart/form-data" class="max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-[1.25rem] border p-5 shadow-2xl" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: var(--theme-surface-base);">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ __('Import template') }}</h3>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Paste a .localboost-template.json export or a template pack JSON with a templates array.') }}</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="inline-flex h-9 w-9 items-center justify-center rounded-full border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);">
                        <i class="fa-light fa-xmark"></i>
                    </button>
                </div>

                <div class="mt-5 rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, transparent);">
                    <label class="block text-sm font-semibold" style="color: var(--theme-header-text-color);" for="template_import_file">{{ __('Upload JSON file') }}</label>
                    <input id="template_import_file" type="file" wire:model="importFile" accept=".json,application/json,text/plain" class="mt-3 block w-full rounded-xl border px-3 py-2 text-sm" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);">
                    @error('importFile')
                        <p class="mt-2 text-xs" style="color: var(--theme-danger-color);">{{ $message }}</p>
                    @enderror
                    <div wire:loading wire:target="importFile" class="mt-2 text-xs" style="color: var(--theme-muted-text-color);">
                        <i class="fa-light fa-spinner-third fa-spin"></i>
                        {{ __('Reading file...') }}
                    </div>
                </div>

                <div class="mt-5">
                    <x-ui.textarea wire:model="importJson" name="template_import_json" :label="__('Template JSON')" rows="18" :error="$errors->first('importJson')">{{ $importJson }}</x-ui.textarea>
                </div>

                <div class="mt-5 flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline" wire:click="closeModal">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="importTemplate">
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="importTemplate"></i>
                        <span wire:loading.remove wire:target="importTemplate">{{ __('Import template') }}</span>
                        <span wire:loading wire:target="importTemplate">{{ __('Importing...') }}</span>
                    </x-ui.button>
                </div>
            </form>
        </div>
    @endif

    @if ($aiGenerating)
        <div class="fixed inset-0 z-50 grid place-items-center bg-slate-950/55 p-4" wire:click.self="closeModal">
            <form wire:submit="generateTemplateFromAI" class="max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-[1.25rem] border p-5 shadow-2xl" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: var(--theme-surface-base);">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ __('Generate template') }}</h3>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Create a draft template from business category, goal, offer, tone, and language.') }}</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="inline-flex h-9 w-9 items-center justify-center rounded-full border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);">
                        <i class="fa-light fa-xmark"></i>
                    </button>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <x-ui.select wire:model="aiForm.category" name="ai_template_category" :label="__('Business category')" :error="$errors->first('aiForm.category')">
                        @foreach ($categoryOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select wire:model="aiForm.goal" name="ai_template_goal" :label="__('Goal')" :error="$errors->first('aiForm.goal')">
                        @foreach ($goalOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select wire:model="aiForm.campaign_type" name="ai_template_type" :label="__('Template type')" :error="$errors->first('aiForm.campaign_type')">
                        @foreach ($typeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input wire:model="aiForm.offer" name="ai_template_offer" :label="__('Offer')" :error="$errors->first('aiForm.offer')" />
                    <x-ai.tone-field wire:model="aiForm.tone" name="ai_template_tone" :label="__('Tone')" :value="$aiForm['tone'] ?? ''" :options="$aiToneOptions" :error="$errors->first('aiForm.tone')" />
                    <x-ai.language-field wire:model="aiForm.language" name="ai_template_language" :label="__('Language')" :value="$aiForm['language'] ?? ''" :error="$errors->first('aiForm.language')" />
                </div>

                <div class="mt-5 flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline" wire:click="closeModal">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="generateTemplateFromAI">
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="generateTemplateFromAI"></i>
                        <span wire:loading.remove wire:target="generateTemplateFromAI">{{ __('Save Draft Template') }}</span>
                        <span wire:loading wire:target="generateTemplateFromAI">{{ __('Generating...') }}</span>
                    </x-ui.button>
                </div>
            </form>
        </div>
    @endif

    @if ($previewPack)
        <div class="fixed inset-0 z-50 grid place-items-center bg-slate-950/55 p-4" wire:click.self="closeModal">
            <div class="max-h-[92vh] w-full max-w-5xl overflow-y-auto rounded-[1.25rem] border p-5 shadow-2xl" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: var(--theme-surface-base);">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Marketplace Pack') }}</p>
                        <h3 class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ $previewPack->name }}</h3>
                        <p class="mt-2 max-w-2xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $previewPack->description }}</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="inline-flex h-9 w-9 items-center justify-center rounded-full border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);">
                        <i class="fa-light fa-xmark"></i>
                    </button>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    @forelse (($previewPack->templates ?? collect()) as $packTemplate)
                        <article class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, transparent);">
                            <div class="flex items-start gap-3">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                                    <i class="{{ $packTemplate->icon ?: 'fa-light fa-grid-2' }}"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $packTemplate->name }}</p>
                                    <p class="mt-1 line-clamp-2 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $packTemplate->description }}</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold" style="background: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">{{ str($packTemplate->type)->replace('_', ' ')->headline() }}</span>
                                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold" style="background: rgba(var(--theme-border-color-rgb),.35); color: var(--theme-muted-text-color);">{{ str($packTemplate->goal)->replace('_', ' ')->headline() }}</span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <x-ui.empty icon="fa-light fa-box-open" :title="__('No templates in this pack')" :description="__('This pack has no active templates yet.')" />
                    @endforelse
                </div>

                <div class="mt-5 flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline" wire:click="closeModal">{{ __('Close') }}</x-ui.button>
                    <x-ui.button type="button" variant="outline" wire:click="exportPack({{ $previewPack->id }})" wire:loading.attr="disabled" wire:target="exportPack({{ $previewPack->id }})">
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="exportPack({{ $previewPack->id }})"></i>
                        <span wire:loading.remove wire:target="exportPack({{ $previewPack->id }})">{{ __('Export Pack') }}</span>
                        <span wire:loading wire:target="exportPack({{ $previewPack->id }})">{{ __('Exporting...') }}</span>
                    </x-ui.button>
                    <x-ui.button type="button" wire:click="installPack({{ $previewPack->id }})" wire:loading.attr="disabled" wire:target="installPack({{ $previewPack->id }})">
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="installPack({{ $previewPack->id }})"></i>
                        <span wire:loading.remove wire:target="installPack({{ $previewPack->id }})">{{ __('Install Pack') }}</span>
                        <span wire:loading wire:target="installPack({{ $previewPack->id }})">{{ __('Installing...') }}</span>
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif

    @if ($editingId !== null)
        <div class="fixed inset-0 z-50 grid place-items-center bg-slate-950/55 p-4" wire:click.self="closeModal">
            <form wire:submit="save" class="max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-[1.25rem] border p-5 shadow-2xl" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: var(--theme-surface-base);">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ $editingId ? __('Edit template') : __('Create template') }}</h3>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Define the template metadata, engine settings, and default content JSON.') }}</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="inline-flex h-9 w-9 items-center justify-center rounded-full border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);">
                        <i class="fa-light fa-xmark"></i>
                    </button>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <x-ui.input wire:model="form.name" name="template_name" :label="__('Template name')" :error="$errors->first('form.name')" />
                    <x-ui.icon-picker
                        wire:model="form.icon"
                        name="template_icon"
                        :label="__('Icon')"
                        :error="$errors->first('form.icon')"
                        :preview-color="'var(--theme-accent)'"
                        :dialog-title="__('Choose template icon')"
                        :dialog-description="__('Select the Font Awesome icon used for this template card and preview.')"
                        :placeholder="'fa-light fa-grid-2'"
                        :value="$form['icon'] ?? 'fa-light fa-grid-2'"
                        compact
                    />
                    <x-ui.select wire:model="form.type" name="template_type" :label="__('Template type')" :error="$errors->first('form.type')">
                        @foreach ($typeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select wire:model="form.status" name="template_status" :label="__('Status')" :error="$errors->first('form.status')">
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select wire:model="form.source" name="template_source" :label="__('Source')" :error="$errors->first('form.source')">
                        @foreach ($sourceOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select wire:model="form.visibility" name="template_visibility" :label="__('Visibility')" :error="$errors->first('form.visibility')">
                        @foreach ($visibilityOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select wire:model="form.category" name="template_category" :label="__('Business category')" :error="$errors->first('form.category')">
                        @foreach ($categoryOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select wire:model="form.goal" name="template_goal" :label="__('Campaign goal')" :error="$errors->first('form.goal')">
                        @foreach ($goalOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input wire:model="form.version" name="template_version" :label="__('Version')" :error="$errors->first('form.version')" />
                </div>

                <div class="mt-4">
                    <x-ui.textarea wire:model="form.description" name="template_description" :label="__('Description')" rows="3" :error="$errors->first('form.description')">{{ $form['description'] ?? '' }}</x-ui.textarea>
                </div>

                <div class="mt-5 grid gap-4 xl:grid-cols-3">
                    <section class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background: color-mix(in srgb, var(--theme-surface-overlay) 92%, transparent);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Form fields builder') }}</p>
                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Add, reorder, and remove fields without editing JSON.') }}</p>
                            </div>
                            <i class="fa-light fa-list-check" style="color: var(--theme-accent);"></i>
                        </div>

                        <div class="mt-4 space-y-2" x-data="localBoostTemplateSorter($wire, 'reorderBuilderFields')" x-init="init()" x-ref="sortableItems">
                            @forelse (($form['form_fields'] ?? []) as $index => $field)
                                <div
                                    wire:key="template-field-{{ $field['id'] ?? $field['name'] ?? $index }}"
                                    data-sortable-id="{{ $field['id'] ?? $field['name'] ?? $index }}"
                                    class="rounded-[0.85rem] border p-3"
                                    style="border-color: rgba(var(--theme-border-color-rgb), .64); background: var(--theme-surface-base);"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <button type="button" class="template-sortable-handle mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);" title="{{ __('Drag to reorder') }}">
                                            <i class="fa-light fa-grip-lines"></i>
                                        </button>
                                        <div class="min-w-0 flex-1">
                                            <div class="grid gap-2">
                                                <input wire:model.live="form.form_fields.{{ $index }}.label" class="h-9 rounded-lg border px-2 text-xs outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Label') }}">
                                                <div class="grid grid-cols-2 gap-2">
                                                    <input wire:model.live="form.form_fields.{{ $index }}.name" class="h-9 rounded-lg border px-2 text-xs outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Name') }}">
                                                    <select wire:model.live="form.form_fields.{{ $index }}.type" class="h-9 rounded-lg border px-2 text-xs outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);">
                                                        @foreach ($fieldTypeOptions as $value => $label)
                                                            <option value="{{ $value }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <input wire:model.live="form.form_fields.{{ $index }}.placeholder" class="h-9 rounded-lg border px-2 text-xs outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Placeholder') }}">
                                                <div class="grid grid-cols-3 gap-2">
                                                    <input wire:model.live="form.form_fields.{{ $index }}.validation.min" type="number" class="h-9 rounded-lg border px-2 text-xs outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Min') }}">
                                                    <input wire:model.live="form.form_fields.{{ $index }}.validation.max" type="number" class="h-9 rounded-lg border px-2 text-xs outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Max') }}">
                                                    <input wire:model.live="form.form_fields.{{ $index }}.validation.pattern" class="h-9 rounded-lg border px-2 text-xs outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Pattern') }}">
                                                </div>
                                                <label class="inline-flex items-center gap-2 text-xs" style="color: var(--theme-header-text-color);">
                                                    <input type="checkbox" wire:model.live="form.form_fields.{{ $index }}.required" class="rounded border">
                                                    {{ __('Required') }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="flex shrink-0 gap-1">
                                            <button type="button" wire:click="moveBuilderField({{ $index }}, -1)" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);"><i class="fa-light fa-arrow-up"></i></button>
                                            <button type="button" wire:click="moveBuilderField({{ $index }}, 1)" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);"><i class="fa-light fa-arrow-down"></i></button>
                                            <button type="button" wire:click="duplicateBuilderField({{ $index }})" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);"><i class="fa-light fa-copy"></i></button>
                                            <button type="button" wire:click="removeBuilderField({{ $index }})" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border" style="border-color: rgba(var(--theme-danger-color-rgb), .34); color: var(--theme-danger-color);"><i class="fa-light fa-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="rounded-[0.85rem] border p-3 text-xs" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);">{{ __('No fields yet.') }}</p>
                            @endforelse
                        </div>

                        <div class="mt-4 grid gap-2">
                            <input wire:model="builderNewField.label" class="h-10 rounded-xl border px-3 text-sm outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Label') }}">
                            <div class="grid grid-cols-2 gap-2">
                                <input wire:model="builderNewField.name" class="h-10 rounded-xl border px-3 text-sm outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Name') }}">
                                <select wire:model="builderNewField.type" class="h-10 rounded-xl border px-3 text-sm outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);">
                                    @foreach ($fieldTypeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input wire:model="builderNewField.placeholder" class="h-10 rounded-xl border px-3 text-sm outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Placeholder') }}">
                            <input wire:model="builderNewField.options" class="h-10 rounded-xl border px-3 text-sm outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Options, comma separated') }}">
                            <div class="grid grid-cols-3 gap-2">
                                <input wire:model="builderNewField.min" type="number" class="h-10 rounded-xl border px-3 text-sm outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Min') }}">
                                <input wire:model="builderNewField.max" type="number" class="h-10 rounded-xl border px-3 text-sm outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Max') }}">
                                <input wire:model="builderNewField.pattern" class="h-10 rounded-xl border px-3 text-sm outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Pattern') }}">
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm" style="color: var(--theme-header-text-color);">
                                <input type="checkbox" wire:model="builderNewField.required" class="rounded border">
                                {{ __('Required') }}
                            </label>
                            <x-ui.button type="button" size="sm" wire:click="addBuilderField">{{ __('Add Field') }}</x-ui.button>
                        </div>
                    </section>

                    <section class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background: color-mix(in srgb, var(--theme-surface-overlay) 92%, transparent);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Landing page blocks') }}</p>
                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Create page structure with sortable blocks.') }}</p>
                            </div>
                            <i class="fa-light fa-layer-group" style="color: var(--theme-accent);"></i>
                        </div>

                        <div class="mt-4 space-y-2" x-data="localBoostTemplateSorter($wire, 'reorderBuilderBlocks')" x-init="init()" x-ref="sortableItems">
                            @forelse (($form['landing_page_blocks'] ?? []) as $index => $block)
                                <div
                                    wire:key="template-block-{{ $block['id'] ?? $index }}"
                                    data-sortable-id="{{ $block['id'] ?? $index }}"
                                    class="rounded-[0.85rem] border p-3 {{ isset($block['visible']) && ! $block['visible'] ? 'opacity-60' : '' }}"
                                    style="border-color: rgba(var(--theme-border-color-rgb), .64); background: var(--theme-surface-base);"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <button type="button" class="template-sortable-handle mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);" title="{{ __('Drag to reorder') }}">
                                            <i class="fa-light fa-grip-lines"></i>
                                        </button>
                                        <div class="min-w-0 flex-1">
                                            <div class="grid gap-2">
                                                <select wire:model.live="form.landing_page_blocks.{{ $index }}.type" class="h-9 rounded-lg border px-2 text-xs outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);">
                                                    @foreach ($blockTypeOptions as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <input wire:model.live="form.landing_page_blocks.{{ $index }}.title" class="h-9 rounded-lg border px-2 text-xs outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Title') }}">
                                                <input wire:model.live="form.landing_page_blocks.{{ $index }}.settings.headline" class="h-9 rounded-lg border px-2 text-xs outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Headline') }}">
                                                <input wire:model.live="form.landing_page_blocks.{{ $index }}.settings.cta" class="h-9 rounded-lg border px-2 text-xs outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('CTA') }}">
                                                <p class="text-[11px]" style="color: var(--theme-muted-text-color);">{{ isset($block['visible']) && ! $block['visible'] ? __('Hidden') : __('Visible') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex shrink-0 gap-1">
                                            <button type="button" wire:click="moveBuilderBlock({{ $index }}, -1)" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);"><i class="fa-light fa-arrow-up"></i></button>
                                            <button type="button" wire:click="moveBuilderBlock({{ $index }}, 1)" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);"><i class="fa-light fa-arrow-down"></i></button>
                                            <button type="button" wire:click="toggleBuilderBlockVisibility({{ $index }})" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);"><i class="fa-light {{ isset($block['visible']) && ! $block['visible'] ? 'fa-eye-slash' : 'fa-eye' }}"></i></button>
                                            <button type="button" wire:click="duplicateBuilderBlock({{ $index }})" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);"><i class="fa-light fa-copy"></i></button>
                                            <button type="button" wire:click="removeBuilderBlock({{ $index }})" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border" style="border-color: rgba(var(--theme-danger-color-rgb), .34); color: var(--theme-danger-color);"><i class="fa-light fa-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="rounded-[0.85rem] border p-3 text-xs" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);">{{ __('No blocks yet.') }}</p>
                            @endforelse
                        </div>

                        <div class="mt-4 grid gap-2">
                            <select wire:model="builderNewBlock.type" class="h-10 rounded-xl border px-3 text-sm outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);">
                                @foreach ($blockTypeOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <input wire:model="builderNewBlock.title" class="h-10 rounded-xl border px-3 text-sm outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Block title') }}">
                            <input wire:model="builderNewBlock.headline" class="h-10 rounded-xl border px-3 text-sm outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Headline') }}">
                            <input wire:model="builderNewBlock.cta" class="h-10 rounded-xl border px-3 text-sm outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('CTA') }}">
                            <label class="inline-flex items-center gap-2 text-sm" style="color: var(--theme-header-text-color);">
                                <input type="checkbox" wire:model="builderNewBlock.visible" class="rounded border">
                                {{ __('Visible') }}
                            </label>
                            <x-ui.button type="button" size="sm" wire:click="addBuilderBlock">{{ __('Add Block') }}</x-ui.button>
                        </div>
                    </section>

                    <section class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background: color-mix(in srgb, var(--theme-surface-overlay) 92%, transparent);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Prompt variables') }}</p>
                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Variables are stored as {variable_name}.') }}</p>
                            </div>
                            <i class="fa-light fa-brackets-curly" style="color: var(--theme-accent);"></i>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse (($form['variables'] ?? []) as $index => $variable)
                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                                    {{ '{'.$variable.'}' }}
                                    <button type="button" wire:click="removeBuilderVariable({{ $index }})" style="color: var(--theme-danger-color);"><i class="fa-light fa-xmark"></i></button>
                                </span>
                            @empty
                                <p class="rounded-[0.85rem] border p-3 text-xs" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);">{{ __('No variables yet.') }}</p>
                            @endforelse
                        </div>

                        <div class="mt-4 grid gap-2">
                            <input wire:model="builderNewVariable" class="h-10 rounded-xl border px-3 text-sm outline-none" style="border-color: var(--theme-border-color); background: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('business_name') }}">
                            <x-ui.button type="button" size="sm" wire:click="addBuilderVariable">{{ __('Add Variable') }}</x-ui.button>
                        </div>
                    </section>
                </div>

                @php
                    $builderContent = json_decode((string) ($form['content_json'] ?? '{}'), true);
                    $builderContent = is_array($builderContent) ? $builderContent : [];
                    $builderDesign = json_decode((string) ($form['design_json'] ?? '{}'), true);
                    $builderDesign = is_array($builderDesign) ? $builderDesign : [];
                    $previewAccent = $builderDesign['accent_color'] ?? '#2563eb';
                    $previewHeadline = $builderContent['headline'] ?? ($form['name'] ?: __('Template headline'));
                    $previewDescription = $builderContent['description'] ?? ($form['description'] ?: __('Template description will appear here.'));
                    $previewCta = $builderContent['cta'] ?? __('Continue');
                    $previewFields = $form['form_fields'] ?? [];
                    $previewBlocks = collect($form['landing_page_blocks'] ?? [])->filter(fn ($block) => ($block['visible'] ?? true) !== false)->values();
                @endphp

                <section class="mt-5 rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background: color-mix(in srgb, var(--theme-surface-overlay) 92%, transparent);">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Realtime preview') }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Desktop and mobile previews update from the builder fields and block order.') }}</p>
                        </div>
                        <i class="fa-light fa-display-code" style="color: var(--theme-accent);"></i>
                    </div>

                    <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1fr)_22rem]">
                        <div class="rounded-[1rem] border bg-white p-5 dark:bg-slate-950" style="border-color: var(--theme-border-color);">
                            <div class="rounded-[0.85rem] border p-5" style="border-color: {{ $previewAccent }}33; background: {{ $previewAccent }}0F;">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: {{ $previewAccent }};">{{ str($form['type'] ?? 'campaign')->replace('_', ' ')->headline() }}</p>
                                <h4 class="mt-3 text-3xl font-semibold leading-tight" style="color: var(--theme-header-text-color);">{{ $previewHeadline }}</h4>
                                <p class="mt-3 text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ $previewDescription }}</p>
                                <button type="button" class="mt-5 rounded-xl px-4 py-2 text-sm font-semibold text-white" style="background: {{ $previewAccent }};">{{ $previewCta }}</button>
                            </div>

                            @if ($previewBlocks->isNotEmpty())
                                <div class="mt-4 grid gap-3 md:grid-cols-2">
                                    @foreach ($previewBlocks as $block)
                                        <div class="rounded-[0.85rem] border p-3" style="border-color: var(--theme-border-color);">
                                            <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ str($block['type'] ?? 'block')->replace('_', ' ')->headline() }}</p>
                                            <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $block['settings']['headline'] ?? $block['title'] ?? __('Block') }}</p>
                                            @if (! empty($block['settings']['cta']))
                                                <p class="mt-1 text-xs" style="color: {{ $previewAccent }};">{{ $block['settings']['cta'] }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if (! empty($previewFields))
                                <div class="mt-4 grid gap-3 md:grid-cols-2">
                                    @foreach ($previewFields as $field)
                                        <label class="block">
                                            <span class="text-xs font-semibold" style="color: var(--theme-header-text-color);">{{ $field['label'] ?? $field['name'] ?? __('Field') }} @if(!empty($field['required']))* @endif</span>
                                            <div class="mt-1 h-10 rounded-xl border px-3 py-2 text-xs" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color); background: var(--theme-input-surface);">{{ $field['placeholder'] ?? str($field['type'] ?? 'text')->headline() }}</div>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="mx-auto w-full max-w-[21rem] rounded-[1.4rem] border bg-white p-3 shadow-sm dark:bg-slate-950" style="border-color: var(--theme-border-color);">
                            <div class="rounded-[1.1rem] border p-4" style="border-color: {{ $previewAccent }}33;">
                                <div class="mx-auto mb-4 h-1 w-16 rounded-full" style="background: rgba(var(--theme-border-color-rgb), .75);"></div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: {{ $previewAccent }};">{{ __('Mobile') }}</p>
                                <h4 class="mt-2 text-xl font-semibold leading-tight" style="color: var(--theme-header-text-color);">{{ $previewHeadline }}</h4>
                                <p class="mt-2 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $previewDescription }}</p>
                                <button type="button" class="mt-4 w-full rounded-xl px-3 py-2 text-xs font-semibold text-white" style="background: {{ $previewAccent }};">{{ $previewCta }}</button>
                                <div class="mt-4 space-y-2">
                                    @foreach (array_slice($previewFields, 0, 4) as $field)
                                        <div class="rounded-lg border px-3 py-2 text-xs" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);">{{ $field['label'] ?? $field['name'] ?? __('Field') }}</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <x-ui.textarea wire:model="form.settings_json" name="template_settings" :label="__('Settings JSON')" rows="12" :error="$errors->first('form.settings_json')">{{ $form['settings_json'] ?? '' }}</x-ui.textarea>
                    <x-ui.textarea wire:model="form.content_json" name="template_content" :label="__('Content JSON')" rows="12" :error="$errors->first('form.content_json')">{{ $form['content_json'] ?? '' }}</x-ui.textarea>
                    <x-ui.textarea wire:model="form.design_json" name="template_design" :label="__('Design JSON')" rows="10" :error="$errors->first('form.design_json')">{{ $form['design_json'] ?? '' }}</x-ui.textarea>
                    <x-ui.textarea wire:model="form.builder_schema_json" name="template_builder_schema" :label="__('Builder Schema JSON')" rows="10" :error="$errors->first('form.builder_schema_json')">{{ $form['builder_schema_json'] ?? '' }}</x-ui.textarea>
                </div>

                <div class="mt-5 flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline" wire:click="closeModal">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button type="submit">{{ __('Save template') }}</x-ui.button>
                </div>
            </form>
        </div>
    @endif

    @if ($previewTemplate)
        @php($previewContent = $previewTemplate->content ?: [])
        <div class="fixed inset-0 z-50 grid place-items-center bg-slate-950/55 p-4" wire:click.self="closeModal">
            <div class="max-h-[92vh] w-full max-w-5xl overflow-y-auto rounded-[1.25rem] border p-5 shadow-2xl" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: var(--theme-surface-base);">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Template Preview') }}</p>
                        <h3 class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ $previewTemplate->name }}</h3>
                    </div>
                    <button type="button" wire:click="closeModal" class="inline-flex h-9 w-9 items-center justify-center rounded-full border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);">
                        <i class="fa-light fa-xmark"></i>
                    </button>
                </div>

                <div class="mt-5 grid gap-5 lg:grid-cols-[1fr_22rem]">
                    <div class="rounded-[1.25rem] border p-5" style="border-color: var(--theme-border-color); background: color-mix(in srgb, var(--theme-surface-overlay) 88%, transparent);">
                        <div class="rounded-[1rem] border bg-white p-6 dark:bg-slate-950" style="border-color: var(--theme-border-color);">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ $previewTemplate->typeLabel() }}</p>
                            <h4 class="mt-4 text-3xl font-semibold" style="color: var(--theme-header-text-color);">{{ $previewContent['headline'] ?? $previewTemplate->name }}</h4>
                            <p class="mt-3 text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ $previewContent['description'] ?? $previewTemplate->description }}</p>
                            <div class="mt-5 grid gap-2">
                                @foreach (($previewContent['benefits'] ?? []) as $benefit)
                                    <div class="flex items-center gap-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">
                                        <i class="fa-light fa-check" style="color: var(--theme-success-color);"></i>
                                        {{ $benefit }}
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="mt-6 rounded-[0.9rem] px-5 py-3 text-sm font-semibold text-white" style="background: var(--theme-accent);">{{ $previewContent['cta'] ?? __('Continue') }}</button>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color);">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Form preview') }}</p>
                            <div class="mt-3 space-y-2">
                                @foreach (($previewContent['form_fields'] ?? []) as $field)
                                    <div class="rounded-[0.8rem] border px-3 py-2 text-sm" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);">
                                        {{ $field['label'] ?? $field['name'] ?? __('Field') }} · {{ $field['type'] ?? 'text' }} @if(!empty($field['required'])) · {{ __('required') }} @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color);">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Thank you') }}</p>
                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-header-text-color);">{{ $previewContent['thank_you_message'] ?? __('Thank you. Your submission has been received.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($useTemplate)
        @php($useSettings = $useTemplate->settings ?: [])
        @php($campaignType = $useSettings['campaign_type'] ?? $useTemplate->goal)
        <div class="fixed inset-0 z-50 grid place-items-center bg-slate-950/55 p-4" wire:click.self="closeModal">
            <div class="max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-[1.25rem] border p-5 shadow-2xl" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: var(--theme-surface-base);">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Use Template Wizard') }}</p>
                        <h3 class="mt-2 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ $useTemplate->name }}</h3>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Choose a business, customize the preset, then create real campaign assets.') }}</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="inline-flex h-9 w-9 items-center justify-center rounded-full border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);">
                        <i class="fa-light fa-xmark"></i>
                    </button>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    @foreach ([1 => __('Choose Business'), 2 => __('Review & Customize'), 3 => __('Create')] as $stepNumber => $stepLabel)
                        <button type="button" wire:click="setUseStep({{ $stepNumber }})" class="flex items-center gap-3 rounded-[0.95rem] border p-3 text-left transition hover:-translate-y-px" style="{{ $useStep === $stepNumber ? 'border-color: rgba(var(--theme-accent-rgb), .34); background: rgba(var(--theme-accent-rgb), .10);' : 'border-color: var(--theme-border-color); background: color-mix(in srgb, var(--theme-surface-overlay) 88%, transparent);' }}">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full text-xs font-semibold" style="{{ $useStep >= $stepNumber ? 'background: var(--theme-accent); color: #fff;' : 'background: rgba(var(--theme-border-color-rgb),.42); color: var(--theme-muted-text-color);' }}">{{ $stepNumber }}</span>
                            <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $stepLabel }}</span>
                        </button>
                    @endforeach
                </div>

                @if ($useStep === 1)
                    <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_18rem]">
                        <div class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, transparent);">
                            <x-ui.select wire:model="useForm.business_id" name="template_business_id" :label="__('Business')" :error="$errors->first('useForm.business_id')">
                                <option value="">{{ __('Choose business') }}</option>
                                @foreach ($businesses as $business)
                                    <option value="{{ $business->id }}">{{ $business->name }}</option>
                                @endforeach
                            </x-ui.select>
                            <p class="mt-3 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('The selected business will own the campaign, public page, QR code, leads, and report tracking created from this template.') }}</p>
                        </div>
                        <div class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color);">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Template output') }}</p>
                            <div class="mt-4 space-y-3">
                                @foreach ([__('Campaign'), __('Landing Page'), __('QR Code'), __('Tracking report')] as $item)
                                    <div class="flex items-center gap-2 text-sm font-semibold" style="color: var(--theme-header-text-color);"><i class="fa-light fa-circle-check" style="color: var(--theme-success-color);"></i>{{ $item }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @elseif ($useStep === 2)
                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <x-ui.input wire:model="useForm.campaign_name" name="template_campaign_name" :label="$useTemplate->type === 'landing_page' ? __('Page title') : __('Campaign name')" :error="$errors->first('useForm.campaign_name')" />
                        <x-ui.input wire:model="useForm.headline" name="template_headline" :label="__('Page headline')" :error="$errors->first('useForm.headline')" />
                        <x-ui.input wire:model="useForm.cta" name="template_cta" :label="__('CTA button')" :error="$errors->first('useForm.cta')" />
                        <x-ui.input wire:model="useForm.thank_you_message" name="template_thank_you" :label="__('Thank you message')" :error="$errors->first('useForm.thank_you_message')" />
                        <div class="lg:col-span-2">
                            <x-ui.textarea wire:model="useForm.description" name="template_description_custom" :label="__('Description')" rows="3" :error="$errors->first('useForm.description')">{{ $useForm['description'] ?? '' }}</x-ui.textarea>
                        </div>

                        @if (in_array($campaignType, ['review', 'reviews'], true))
                            <x-ui.input wire:model="useForm.google_review_url" name="template_google_review_url" :label="__('Google Review URL')" placeholder="https://g.page/r/..." :error="$errors->first('useForm.google_review_url')" />
                            <x-ui.input wire:model="useForm.facebook_review_url" name="template_facebook_review_url" :label="__('Facebook Review URL')" placeholder="https://facebook.com/.../reviews" :error="$errors->first('useForm.facebook_review_url')" />
                            <x-ui.input wire:model="useForm.positive_threshold" name="template_positive_threshold" type="number" min="3" max="5" :label="__('Positive rating threshold')" :error="$errors->first('useForm.positive_threshold')" />
                            <x-ui.input wire:model="useForm.negative_feedback_message" name="template_negative_message" :label="__('Low-score private message')" :error="$errors->first('useForm.negative_feedback_message')" />
                        @endif

                        @if (in_array($campaignType, ['coupon', 'coupons', 'retention'], true))
                            <x-ui.select wire:model="useForm.discount_type" name="template_discount_type" :label="__('Discount type')" :error="$errors->first('useForm.discount_type')">
                                <option value="percentage">{{ __('Percentage') }}</option>
                                <option value="fixed">{{ __('Fixed amount') }}</option>
                                <option value="free_item">{{ __('Free item') }}</option>
                                <option value="custom">{{ __('Custom') }}</option>
                            </x-ui.select>
                            <x-ui.input wire:model="useForm.discount_value" name="template_discount_value" :label="__('Discount value')" :error="$errors->first('useForm.discount_value')" />
                            <x-ui.input wire:model="useForm.coupon_code" name="template_coupon_code" :label="__('Coupon code prefix')" :error="$errors->first('useForm.coupon_code')" />
                            <x-ui.date-picker
                                wire:model="useForm.expiry_date"
                                name="template_expiry_date"
                                :label="__('Expiry date')"
                                :value="data_get($useForm, 'expiry_date')"
                                :error="$errors->first('useForm.expiry_date')"
                                :placeholder="__('Choose expiry date')"
                                placement="top"
                            />
                            <x-ui.input wire:model="useForm.usage_limit" name="template_usage_limit" type="number" min="1" :label="__('Usage limit')" :error="$errors->first('useForm.usage_limit')" />
                            <x-ui.input wire:model="useForm.terms" name="template_terms" :label="__('Terms')" :error="$errors->first('useForm.terms')" />
                        @endif

                        @if (in_array($campaignType, ['booking', 'bookings'], true))
                            <x-ui.input wire:model="useForm.service_name" name="template_service_name" :label="__('Service name')" :error="$errors->first('useForm.service_name')" />
                            <x-ui.input wire:model="useForm.duration_minutes" name="template_duration" type="number" min="5" :label="__('Duration minutes')" :error="$errors->first('useForm.duration_minutes')" />
                            <x-ui.input wire:model="useForm.price" name="template_price" :label="__('Price')" :error="$errors->first('useForm.price')" />
                        @endif
                    </div>
                @else
                    <div class="mt-5 rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background: color-mix(in srgb, var(--theme-surface-overlay) 88%, transparent);">
                        @if ($createdRedirectUrl || $createdPublicUrl)
                            <div class="flex items-start gap-3">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full" style="background: var(--theme-success-color); color: #fff;"><i class="fa-light fa-check"></i></span>
                                <div>
                                    <h4 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Campaign created successfully') }}</h4>
                                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('The real campaign assets are ready. Continue with the next action below.') }}</p>
                                </div>
                            </div>
                        @else
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('This template will create:') }}</p>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach ([__('Campaign or draft page'), __('Public Landing Page'), __('QR Code'), __('Tracking report')] as $item)
                                    <div class="flex items-center gap-2 rounded-[0.85rem] border px-3 py-2 text-sm font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);"><i class="fa-light fa-circle-check" style="color: var(--theme-success-color);"></i>{{ $item }}</div>
                                @endforeach
                            </div>
                            <p class="mt-4 text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Goal: :goal | Type: :type', ['goal' => $useSettings['tracking_goal'] ?? $useTemplate->goal, 'type' => $useTemplate->typeLabel()]) }}</p>
                        @endif
                    </div>
                @endif

                <div class="mt-5 flex justify-end gap-3">
                    @if ($createdRedirectUrl || $createdPublicUrl)
                        @if ($createdPublicUrl)
                            <x-ui.button type="button" variant="outline" x-on:click="navigator.clipboard?.writeText('{{ $createdPublicUrl }}')">{{ __('Copy Link') }}</x-ui.button>
                        @endif
                        @if ($createdQrUrl)
                            <x-ui.button :href="$createdQrUrl" variant="outline" target="_blank">{{ __('Download QR') }}</x-ui.button>
                        @endif
                        @if ($createdRedirectUrl)
                            <x-ui.button :href="$createdRedirectUrl" wire:navigate>{{ __('View Campaign') }}</x-ui.button>
                        @endif
                    @elseif ($useStep === 1)
                        <x-ui.button type="button" variant="outline" wire:click="closeModal">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="button" wire:click="nextUseStep" wire:loading.attr="disabled" wire:target="nextUseStep"><i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="nextUseStep"></i>{{ __('Continue') }}</x-ui.button>
                    @elseif ($useStep === 2)
                        <x-ui.button type="button" variant="outline" wire:click="previousUseStep">{{ __('Back') }}</x-ui.button>
                        <x-ui.button type="button" wire:click="nextUseStep" wire:loading.attr="disabled" wire:target="nextUseStep"><i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="nextUseStep"></i>{{ __('Review create plan') }}</x-ui.button>
                    @else
                        <x-ui.button type="button" variant="outline" wire:click="previousUseStep">{{ __('Back') }}</x-ui.button>
                        <x-ui.button type="button" wire:click="createFromTemplate" wire:loading.attr="disabled" wire:target="createFromTemplate">
                            <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="createFromTemplate"></i>
                            <span wire:loading.remove wire:target="createFromTemplate">{{ $useTemplate->type === 'content' ? __('Open Content Writer') : __('Create from Template') }}</span>
                            <span wire:loading wire:target="createFromTemplate">{{ __('Creating...') }}</span>
                        </x-ui.button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
