@php
    $widthClasses = [
        'compact' => 'lg:col-span-4',
        'half' => 'lg:col-span-6',
        'wide' => 'lg:col-span-8',
        'full' => 'lg:col-span-12',
    ];

    $adminUser = auth()->user();
    $adminName = $adminUser?->name ?: __('Administrator');
    $todayLabel = format_date_locale(now());
    $quickActionIcons = [
        'users' => 'fa-users',
        'teams' => 'fa-user-group',
        'plans' => 'fa-layer-group',
        'payments' => 'fa-credit-card',
        'ai usage' => 'fa-sparkles',
        'system reports' => 'fa-server',
        'support' => 'fa-headset',
    ];
@endphp

<div
    class="space-y-6"
    x-data="{
        draggingId: null,
        saveTimeout: null,
        saveUrl: @js(route('dashboard.layout.update')),
        csrf: @js(csrf_token()),
        saving: false,
        saved: false,
        startDrag(event) {
            const card = event.currentTarget;
            this.draggingId = card.dataset.dashboardId;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', this.draggingId);
            card.classList.add('opacity-60');
        },
        dragOver(event) {
            const target = event.currentTarget;
            const sourceId = this.draggingId;

            if (!sourceId || sourceId === target.dataset.dashboardId) {
                return;
            }

            const board = this.$refs.board;
            const source = board.querySelector(`[data-dashboard-id='${sourceId}']`);

            if (!source || !target || source === target) {
                return;
            }

            const rect = target.getBoundingClientRect();
            const before = event.clientY < rect.top + rect.height / 2;

            if (before) {
                board.insertBefore(source, target);
            } else {
                board.insertBefore(source, target.nextSibling);
            }
        },
        endDrag(event) {
            event.currentTarget.classList.remove('opacity-60');
            this.draggingId = null;
            this.persist();
        },
        persist() {
            clearTimeout(this.saveTimeout);

            this.saveTimeout = setTimeout(async () => {
                const itemIds = Array.from(this.$refs.board.querySelectorAll('[data-dashboard-id]'))
                    .map((element) => element.dataset.dashboardId);

                this.saving = true;
                this.saved = false;

                await fetch(this.saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ item_ids: itemIds }),
                });

                this.saving = false;
                this.saved = true;

                setTimeout(() => {
                    this.saved = false;
                }, 1600);
            }, 180);
        },
    }"
>
    <section class="overflow-hidden rounded-[1.15rem] border shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background: var(--theme-surface-base);">
        <div class="p-5 sm:p-6">
            <div class="rounded-[1rem] border p-5 sm:p-6" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background:
                radial-gradient(circle at top left, rgba(var(--theme-accent-rgb),0.16), transparent 34%),
                linear-gradient(135deg, color-mix(in srgb, var(--theme-surface-overlay) 96%, transparent), color-mix(in srgb, var(--theme-surface-base) 94%, rgba(var(--theme-accent-rgb),0.04)));">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb),0.18); background: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                                <i class="fa-light fa-command"></i>
                                {{ __('Admin control center') }}
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="background: rgba(var(--theme-success-color-rgb),0.10); color: var(--theme-success-color);">
                                <i class="fa-light fa-calendar-day"></i>
                                {{ $todayLabel }}
                            </span>
                        </div>

                        <h1 class="mt-3 text-[1.6rem] font-semibold leading-tight tracking-[-0.04em] sm:text-[2rem]" style="color: var(--theme-header-text-color);">
                            {{ __('SaaS operations dashboard') }}
                        </h1>
                        <p class="mt-3 max-w-4xl text-sm leading-7" style="color: var(--theme-muted-text-color);">
                            {{ __('Monitor users, teams, businesses, campaigns, payments, plans, AI usage, system reports, and support tickets from one control surface.') }}
                        </p>
                    </div>

                    <div class="w-full shrink-0 xl:w-[29rem]">
                        <div class="flex flex-wrap items-center gap-2 xl:justify-end">
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.16em]" style="background: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                                <i class="fa-light fa-bolt"></i>
                                {{ __('Quick actions') }}
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.16em]" style="background: color-mix(in srgb, var(--theme-surface-base) 82%, rgba(var(--theme-accent-rgb),0.06)); color: var(--theme-muted-text-color);">
                                {{ $adminName }}
                            </span>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-2">
                            @foreach ($adminQuickLinks as $index => $link)
                                @php($quickIcon = $quickActionIcons[strtolower((string) $link['label'])] ?? 'fa-arrow-up-right')
                                <a
                                    href="{{ $link['href'] }}"
                                    wire:navigate
                                    class="inline-flex h-10 items-center justify-center gap-2 rounded-[0.85rem] border px-3 text-sm font-semibold transition hover:-translate-y-px hover:shadow-sm"
                                    style="{{ $index === 0
                                        ? 'border-color: var(--theme-accent); background: var(--theme-accent); color: #fff;'
                                        : 'border-color: rgba(var(--theme-border-color-rgb),0.62); background: color-mix(in srgb, var(--theme-surface-base) 88%, transparent); color: var(--theme-header-text-color);' }}"
                                >
                                    <i class="fa-light {{ $quickIcon }} text-xs"></i>
                                    {{ $link['label'] }}
                                </a>
                            @endforeach
                        </div>

                        <span
                            x-cloak
                            x-show="saving || saved"
                            class="mt-4 inline-flex items-center rounded-full px-3 py-2 text-xs font-semibold"
                            style="background: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);"
                        >
                            <span x-show="saving">{{ __('Saving layout...') }}</span>
                            <span x-show="saved">{{ __('Layout saved') }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($adminSummary as $tile)
                    <a
                        href="{{ $tile['route'] ?: '#' }}"
                        class="rounded-[1rem] border p-4 transition hover:-translate-y-0.5 hover:shadow-sm {{ $tile['route'] ? '' : 'pointer-events-none' }}"
                        style="border-color: {{ $tile['color'] }}26; background: {{ $tile['color'] }}0d;"
                        @if ($tile['route']) wire:navigate @endif
                    >
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $tile['label'] }}</p>
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-[0.7rem]" style="background: {{ $tile['color'] }}12; color: {{ $tile['color'] }};">
                                <i class="fa-light {{ $tile['icon'] }}"></i>
                            </span>
                        </div>
                        <p class="mt-3 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ is_numeric($tile['value']) ? number_format($tile['value']) : $tile['value'] }}</p>
                        <div class="mt-2 flex items-center justify-between gap-2">
                            <p class="text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $tile['description'] }}</p>
                            @if (! empty($tile['meta']))
                                <span class="rounded-full px-2 py-1 text-[11px] font-semibold" style="background: color-mix(in srgb, var(--theme-surface-base) 76%, transparent); color: {{ $tile['color'] }};">{{ $tile['meta'] }}</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <div wire:init="loadWidgets">
    @if ($widgetsLoaded && (($welcomeItems ?? []) !== []))
        <div class="space-y-5">
            @foreach ($welcomeItems as $item)
                <section>
                    {!! $item['content'] !!}
                </section>
            @endforeach
        </div>
    @endif

    @if (! $widgetsLoaded)
        <div class="rounded-[1rem] border p-5 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.72); color: var(--theme-muted-text-color);">
            {{ __('Loading dashboard widgets...') }}
        </div>
    @elseif ($dashboardItems === [])
        <x-ui.empty
            :title="__('No dashboard items registered')"
            :description="__('Start by registering widgets from admin modules with register_admin_dashboard_item().')"
        />
    @else
        <div
            x-ref="board"
            class="grid gap-5 lg:grid-cols-12"
        >
            @foreach ($dashboardItems as $item)
                <section
                    draggable="true"
                    data-dashboard-id="{{ $item['id'] }}"
                    class="group relative {{ $widthClasses[$item['width'] ?? 'half'] ?? $widthClasses['half'] }}"
                    x-on:dragstart="startDrag($event)"
                    x-on:dragover.prevent="dragOver($event)"
                    x-on:dragend="endDrag($event)"
                >
                    <div class="pointer-events-none absolute right-3 top-3 z-10 inline-flex h-8 w-8 items-center justify-center rounded-md border bg-white/95 text-slate-400 opacity-0 shadow-sm transition group-hover:opacity-100" style="border-color: var(--theme-border-color);">
                        <i class="fa-light fa-grip-dots text-sm"></i>
                    </div>

                    {!! $item['content'] !!}
                </section>
            @endforeach
        </div>
    @endif
    </div>
</div>
