@props([
    'sections' => [],
    'mode' => 'desktop',
])

@php
    $icons = [
        'dashboard' => 'fa-light fa-house',
        'blogs' => 'fa-light fa-rss',
        'faq' => 'fa-light fa-messages-question',
        'support' => 'fa-light fa-life-ring',
        'mail' => 'fa-light fa-envelopes-bulk',
        'notification' => 'fa-light fa-bell',
        'proxy' => 'fa-light fa-hard-drive',
        'ai-report' => 'fa-light fa-chart-mixed',
        'ai-template' => 'fa-light fa-brain-circuit',
        'plans' => 'fa-light fa-box-open',
        'money' => 'fa-light fa-coins',
        'coupon' => 'fa-light fa-ticket-percent',
        'affiliate' => 'fa-light fa-handshake-angle',
        'users' => 'fa-light fa-users',
        'user-report' => 'fa-light fa-chart-user',
        'themes' => 'fa-light fa-swatchbook',
        'settings' => 'fa-light fa-sliders',
    ];

    $sectionIcons = [
        'overview' => 'fa-light fa-grid-2',
        'workspace' => 'fa-light fa-link',
        'local-businesses' => 'fa-light fa-building',
        'growth-tools' => 'fa-light fa-rocket',
        'ai-tools' => 'fa-light fa-sparkles',
        'analytics' => 'fa-light fa-chart-pie',
        'marketing-assets' => 'fa-light fa-folder-open',
        'team-billing' => 'fa-light fa-id-card',
        'automation' => 'fa-light fa-bolt',
        'crm' => 'fa-light fa-address-book',
        'google-business' => 'fa-brands fa-google',
        'content-tools' => 'fa-light fa-pen-nib',
        'library' => 'fa-light fa-books',
        'help-desk' => 'fa-light fa-life-ring',
        'portal' => 'fa-light fa-grid',
        'dashboard' => 'fa-light fa-gauge',
        'users' => 'fa-light fa-users',
        'billing' => 'fa-light fa-coins',
        'content' => 'fa-light fa-pen-to-square',
        'system' => 'fa-light fa-sliders',
        'main' => 'fa-light fa-gauge',
        'finance' => 'fa-light fa-coins',
        'localization' => 'fa-light fa-language',
        'frontend' => 'fa-light fa-palette',
        'ai-settings' => 'fa-light fa-brain-circuit',
        'user-portal' => 'fa-light fa-user-group',
        'default' => 'fa-light fa-layer-group',
    ];

    $resolveSectionIcon = function (array $section) use ($sectionIcons): string {
        $iconKey = (string) ($section['icon'] ?? $section['key'] ?? 'default');

        if (str_starts_with($iconKey, 'fa-')) {
            return $iconKey;
        }

        return $sectionIcons[$iconKey] ?? $sectionIcons['default'];
    };

    $sectionHasActiveItem = function (array $section): bool {
        return collect($section['items'] ?? [])->contains(function (array $item): bool {
            if ($item['active'] ?? false) {
                return true;
            }

            return collect($item['children'] ?? [])->contains(fn (array $child): bool => (bool) ($child['active'] ?? false));
        });
    };
@endphp

@if ($mode === 'mobile')
    <div class="mt-6 space-y-4">
        @foreach ($sections as $section)
            @php
                $sectionItems = collect($section['items'] ?? [])
                    ->flatMap(function ($item) {
                        if (! empty($item['children']) && is_array($item['children'])) {
                            return collect($item['children'])->map(function ($child) use ($item) {
                                $child['mobile_icon'] = $item['icon'] ?? 'dashboard';

                                return $child;
                            });
                        }

                        $item['mobile_icon'] = $item['icon'] ?? 'dashboard';

                        return [$item];
                    })
                    ->values();
            @endphp

            @php
                $sectionKey = (string) ($section['key'] ?? 'section-'.$loop->index);
                $sectionIcon = $resolveSectionIcon($section);
                $sectionActive = $sectionHasActiveItem($section);
            @endphp

            <section
                class="{{ $loop->first ? '' : 'border-t pt-4' }}"
                style="{{ $loop->first ? '' : 'border-color: rgba(var(--theme-border-color-rgb), 0.4);' }}"
                x-data="{
                    sectionKey: @js($sectionKey),
                    open: true,
                    init() {
                        const stored = JSON.parse(localStorage.getItem('app-sidebar-sections') || '{}');
                        const hasActive = @js($sectionActive);

                        if (hasActive) {
                            this.open = true;

                            return;
                        }

                        this.open = stored[this.sectionKey] ?? true;
                    },
                    toggleSection() {
                        this.open = ! this.open;
                        const stored = JSON.parse(localStorage.getItem('app-sidebar-sections') || '{}');
                        stored[this.sectionKey] = this.open;
                        localStorage.setItem('app-sidebar-sections', JSON.stringify(stored));
                    },
                }"
            >
                @if (! empty($section['label']))
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-left transition hover:bg-[color:rgba(var(--theme-border-color-rgb),0.12)]"
                        x-on:click="toggleSection()"
                    >
                        <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md" style="background-color: rgba(var(--theme-border-color-rgb), 0.08); color: var(--theme-muted-text-color);">
                            <i class="{{ $sectionIcon }} text-[12px]"></i>
                        </span>
                        <span class="min-w-0 flex-1 truncate text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">
                            {{ $section['label'] }}
                        </span>
                        <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center" style="color: var(--theme-muted-text-color);">
                            <i class="fa-solid text-[10px]" x-bind:class="open ? 'fa-minus' : 'fa-plus'"></i>
                        </span>
                    </button>
                @endif

                <div
                    class="mt-2 space-y-1"
                    x-show="open"
                    x-transition:enter="transition ease-out duration-160"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                >
                    @foreach ($sectionItems as $mobileLink)
                        @php
                            $iconKey = $mobileLink['mobile_icon'] ?? 'dashboard';
                            $icon = str_starts_with((string) $iconKey, 'fa-')
                                ? $iconKey
                                : ($icons[$iconKey] ?? $icons['dashboard']);
                            $isActive = (bool) ($mobileLink['active'] ?? false);
                            $isDisabled = (bool) ($mobileLink['disabled'] ?? false);
                            $href = $isDisabled ? '#' : ($mobileLink['route'] ?? '#');
                            $wireNavigate = ! $isDisabled && ! empty($mobileLink['route']);
                            $linkClass = 'group relative flex h-11 items-center rounded-xl pl-[52px] pr-3 text-[14px] font-medium tracking-[0.005em] transition-colors duration-150';
                            $linkClass .= $isDisabled ? ' cursor-default opacity-60' : '';
                            $linkStyle = $isActive
                                ? 'background-color: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);'
                                : 'color: var(--theme-sidebar-text-color);';
                            $iconWrapStyle = $isActive
                                ? 'border-color: rgba(var(--theme-accent-rgb), 0.16); background-color: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);'
                                : 'background-color: rgba(var(--theme-border-color-rgb), 0.08); color: var(--theme-sidebar-text-color);';
                            $badge = $mobileLink['badge'] ?? null;
                        @endphp

                        @if ($wireNavigate)
                            <a
                                href="{{ $href }}"
                                wire:navigate
                                class="{{ $linkClass }}"
                                style="{{ $linkStyle }}"
                            >
                                <span
                                    class="absolute left-[10px] top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg border border-transparent transition-colors duration-100"
                                    style="{{ $iconWrapStyle }}"
                                >
                                    <i class="{{ $icon }} fa-fw text-[15px] leading-none"></i>
                                </span>
                                <span class="min-w-0 flex-1 truncate">{{ $mobileLink['label'] ?? '' }}</span>
                                @if ($badge)
                                    <span title="{{ __('This is a separate addon module and is not included in the main script.') }}" class="ml-1 inline-flex shrink-0 items-center rounded border px-0.5 py-px text-[7px] font-bold uppercase leading-none tracking-normal" style="border-color: rgba(var(--theme-accent-rgb),0.16); background-color: rgba(var(--theme-accent-rgb),0.06); color: var(--theme-accent);">{{ $badge }}</span>
                                @endif
                            </a>
                        @else
                            <a
                                href="{{ $href }}"
                                class="{{ $linkClass }}"
                                style="{{ $linkStyle }}"
                            >
                                <span
                                    class="absolute left-[10px] top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg border border-transparent transition-colors duration-100"
                                    style="{{ $iconWrapStyle }}"
                                >
                                    <i class="{{ $icon }} fa-fw text-[15px] leading-none"></i>
                                </span>
                                <span class="min-w-0 flex-1 truncate">{{ $mobileLink['label'] ?? '' }}</span>
                                @if ($badge)
                                    <span title="{{ __('This is a separate addon module and is not included in the main script.') }}" class="ml-1 inline-flex shrink-0 items-center rounded border px-0.5 py-px text-[7px] font-bold uppercase leading-none tracking-normal" style="border-color: rgba(var(--theme-accent-rgb),0.16); background-color: rgba(var(--theme-accent-rgb),0.06); color: var(--theme-accent);">{{ $badge }}</span>
                                @endif
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
@else
    @foreach ($sections as $section)
        @php
            $sectionKey = (string) ($section['key'] ?? 'section-'.$loop->index);
            $sectionIcon = $resolveSectionIcon($section);
            $sectionActive = $sectionHasActiveItem($section);
        @endphp

        <section
            class="{{ $loop->first ? '' : 'mt-2.5 border-t border-slate-300/65 pt-2.5 dark:border-slate-800' }}"
            @if (! $loop->first) style="border-color: var(--theme-border-color);" @endif
            x-data="{
                sectionKey: @js($sectionKey),
                open: true,
                init() {
                    const stored = JSON.parse(localStorage.getItem('app-sidebar-sections') || '{}');
                    const hasActive = @js($sectionActive);

                    if (hasActive) {
                        this.open = true;

                        return;
                    }

                    this.open = stored[this.sectionKey] ?? true;
                },
                toggleSection() {
                    if (! sidebarContentVisible) {
                        return;
                    }

                    this.open = ! this.open;
                    const stored = JSON.parse(localStorage.getItem('app-sidebar-sections') || '{}');
                    stored[this.sectionKey] = this.open;
                    localStorage.setItem('app-sidebar-sections', JSON.stringify(stored));
                },
            }"
        >
            @if (! empty($section['label']))
                <button
                    type="button"
                    class="group flex w-full items-center gap-2 rounded-lg px-2 py-1 text-left transition hover:bg-[color:rgba(var(--theme-border-color-rgb),0.10)]"
                    x-on:click="toggleSection()"
                    x-bind:title="sidebarContentVisible ? @js($section['label']) : ''"
                >
                    <span
                        class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md text-slate-400 transition group-hover:text-[var(--theme-accent)]"
                        x-bind:class="sidebarContentVisible ? '' : 'mx-auto'"
                    >
                        <i class="{{ $sectionIcon }} text-[11px]"></i>
                    </span>

                    <span
                        class="min-w-0 flex-1 truncate text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500/90 group-hover:text-slate-600 dark:group-hover:text-slate-300"
                        x-cloak
                        x-show="sidebarContentVisible"
                        x-transition:enter="transition ease-out duration-140"
                        x-transition:enter-start="opacity-0 -translate-x-1"
                        x-transition:enter-end="opacity-100 translate-x-0"
                    >
                        {{ $section['label'] }}
                    </span>

                    <span
                        class="inline-flex h-5 w-5 shrink-0 items-center justify-center text-slate-400 transition group-hover:text-slate-600 dark:group-hover:text-slate-300"
                        x-cloak
                        x-show="sidebarContentVisible"
                        x-transition:enter="transition ease-out duration-120"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                    >
                        <i class="fa-solid text-[10px]" x-bind:class="open ? 'fa-minus' : 'fa-plus'"></i>
                    </span>
                </button>
            @endif

            <div
                class="mt-1 space-y-px"
                x-show="! sidebarContentVisible || open"
                x-transition:enter="transition ease-out duration-160"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
            >
                @foreach ($section['items'] as $item)
                    @php
                        $hasChildren = ! empty($item['children']);
                        $isActive = (bool) ($item['active'] ?? false);
                        $isCurrent = $isActive || collect($item['children'] ?? [])->contains(fn ($child) => $child['active'] ?? false);
                        $isDisabled = (bool) ($item['disabled'] ?? false);
                        $iconKey = $item['icon'] ?? 'dashboard';
                        $icon = str_starts_with($iconKey, 'fa-')
                            ? $iconKey
                            : ($icons[$iconKey] ?? $icons['dashboard']);
                        $badge = $item['badge'] ?? null;
                    @endphp

                    @if ($hasChildren)
                        <div x-data="{ open: {{ $isCurrent ? 'true' : 'false' }} }" class="rounded-xl">
                            <button
                                type="button"
                                class="group relative flex w-full items-center text-left transition-colors duration-150"
                                x-on:click="!sidebarContentVisible ? null : open = ! open"
                                x-bind:class="sidebarContentVisible
                                    ? '{{ $isCurrent ? 'h-10 rounded-xl pl-[45px] pr-3 text-slate-950 dark:text-white' : 'h-10 rounded-xl pl-[45px] pr-3 text-slate-600 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white' }}'
                                    : '{{ $isCurrent ? 'h-10 rounded-none bg-transparent pl-[7px] pr-0 text-slate-950 shadow-none ring-0 dark:text-white' : 'h-10 rounded-none bg-transparent pl-[7px] pr-0 text-slate-600 shadow-none ring-0 dark:text-slate-300' }}'"
                                title="{{ $item['label'] }}"
                            >
                                <span class="absolute left-[7px] top-1/2 inline-flex h-[1.875rem] w-[1.875rem] -translate-y-1/2 items-center justify-center rounded-lg border border-transparent transition-colors duration-100"
                                    x-bind:class="sidebarContentVisible
                                        ? '{{ $isCurrent ? 'shadow-[0_10px_18px_-16px_rgba(var(--theme-accent-rgb),0.32)]' : 'bg-transparent text-slate-500 group-hover:border-[color:rgba(var(--theme-accent-rgb),0.16)] group-hover:bg-[color:rgba(var(--theme-accent-rgb),0.12)] group-hover:text-[var(--theme-accent)] dark:text-slate-300 dark:group-hover:border-[color:rgba(var(--theme-accent-rgb),0.22)] dark:group-hover:bg-[color:rgba(var(--theme-accent-rgb),0.16)] dark:group-hover:text-[var(--theme-accent)]' }}'
                                        : '{{ $isCurrent ? 'bg-[var(--theme-accent)] text-white shadow-[0_10px_18px_-14px_rgba(var(--theme-accent-rgb),0.65)] dark:bg-[var(--theme-accent)] dark:text-white' : 'bg-transparent text-slate-600 group-hover:text-slate-900 dark:text-slate-200 dark:group-hover:text-white' }}'"
                                    @if ($isCurrent)
                                        x-bind:style="sidebarContentVisible ? 'border-color: rgba(var(--theme-accent-rgb), 0.16); background-color: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);' : ''"
                                    @endif>
                                    <i class="{{ $icon }} fa-fw text-[16px] leading-none"></i>
                                </span>
                                <span class="min-w-0 flex-1 truncate text-[13.5px] font-medium tracking-[0.005em] {{ $isCurrent ? 'text-slate-950 dark:text-white' : 'text-slate-700 dark:text-slate-300' }}"
                                    x-cloak
                                    x-show="sidebarContentVisible"
                                    x-transition:enter="transition ease-out duration-140"
                                    x-transition:enter-start="opacity-0 -translate-x-1.5"
                                    x-transition:enter-end="opacity-100 translate-x-0">{{ $item['label'] }}</span>
                                @if ($badge)
                                    <span title="{{ __('This is a separate addon module and is not included in the main script.') }}" class="ml-1 inline-flex shrink-0 items-center rounded border px-0.5 py-px text-[7px] font-bold uppercase leading-none tracking-normal"
                                        x-cloak
                                        x-show="sidebarContentVisible"
                                        x-transition:enter="transition ease-out duration-120"
                                        x-transition:enter-start="opacity-0"
                                        x-transition:enter-end="opacity-100"
                                        style="border-color: rgba(var(--theme-accent-rgb),0.16); background-color: rgba(var(--theme-accent-rgb),0.06); color: var(--theme-accent);">{{ $badge }}</span>
                                @endif
                                <span class="ml-auto inline-flex h-5 w-5 items-center justify-center text-slate-500"
                                    x-cloak
                                    x-show="sidebarContentVisible"
                                    x-transition:enter="transition ease-out duration-120"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100">
                                    <i class="fa-solid text-[10px]" :class="open ? 'fa-minus' : 'fa-plus'"></i>
                                </span>
                            </button>

                            <div class="relative ml-[1.75rem] mt-1 space-y-1 overflow-hidden pl-4 before:absolute before:bottom-1.5 before:left-0 before:top-1.5 before:w-px before:bg-slate-300/75 dark:before:bg-slate-700"
                                x-cloak
                                x-show="open && sidebarContentVisible"
                                x-transition:enter="transition ease-out duration-160"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0">
                                @foreach ($item['children'] as $child)
                                    @php
                                        $childDisabled = (bool) ($child['disabled'] ?? false);
                                        $childBadge = $child['badge'] ?? null;
                                    @endphp
                                    <a
                                        href="{{ $childDisabled ? '#' : ($child['route'] ?? '#') }}"
                                        @if (! $childDisabled && ! empty($child['route'])) wire:navigate @endif
                                        class="{{ ($child['active'] ?? false) ? 'text-slate-900 dark:text-white' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }} group relative flex items-center rounded-lg px-3 py-1.5 text-[12.5px] font-medium tracking-[0.005em] transition {{ $childDisabled ? 'cursor-default opacity-60' : '' }}"
                                    >
                                        <span class="absolute left-0 top-1/2 h-px w-3 -translate-x-[1rem] -translate-y-1/2 {{ ($child['active'] ?? false) ? 'bg-slate-400 dark:bg-slate-500' : 'bg-slate-300/90 dark:bg-slate-700' }}"></span>
                                        <span class="min-w-0 flex-1 truncate">{{ $child['label'] }}</span>
                                        @if ($childBadge)
                                            <span title="{{ __('This is a separate addon module and is not included in the main script.') }}" class="ml-1 inline-flex shrink-0 items-center rounded border px-0.5 py-px text-[7px] font-bold uppercase leading-none tracking-normal" style="border-color: rgba(var(--theme-accent-rgb),0.16); background-color: rgba(var(--theme-accent-rgb),0.06); color: var(--theme-accent);">{{ $childBadge }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a
                            href="{{ $isDisabled ? '#' : ($item['route'] ?? '#') }}"
                            @if (! $isDisabled && ! empty($item['route'])) wire:navigate @endif
                            class="group relative flex items-center transition-colors duration-150 {{ $isDisabled ? 'cursor-default opacity-60' : '' }}"
                            x-bind:class="sidebarContentVisible
                                ? '{{ $isCurrent ? 'h-10 rounded-xl pl-[45px] pr-3 text-slate-950 dark:text-white' : 'h-10 rounded-xl pl-[45px] pr-3 text-slate-600 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white' }}'
                                : '{{ $isCurrent ? 'h-10 rounded-none bg-transparent pl-[7px] pr-0 text-slate-950 shadow-none ring-0 dark:text-white' : 'h-10 rounded-none bg-transparent pl-[7px] pr-0 text-slate-600 shadow-none ring-0 dark:text-slate-300' }}'"
                            title="{{ $item['label'] }}"
                        >
                                <span class="absolute left-[7px] top-1/2 inline-flex h-[1.875rem] w-[1.875rem] -translate-y-1/2 items-center justify-center rounded-lg border border-transparent transition-colors duration-100"
                                    x-bind:class="sidebarContentVisible
                                    ? '{{ $isCurrent ? 'shadow-[0_10px_18px_-16px_rgba(var(--theme-accent-rgb),0.32)]' : 'bg-transparent text-slate-500 group-hover:border-[color:rgba(var(--theme-accent-rgb),0.16)] group-hover:bg-[color:rgba(var(--theme-accent-rgb),0.12)] group-hover:text-[var(--theme-accent)] dark:text-slate-300 dark:group-hover:border-[color:rgba(var(--theme-accent-rgb),0.22)] dark:group-hover:bg-[color:rgba(var(--theme-accent-rgb),0.16)] dark:group-hover:text-[var(--theme-accent)]' }}'
                                    : '{{ $isCurrent ? 'bg-[var(--theme-accent)] text-white shadow-[0_10px_18px_-14px_rgba(var(--theme-accent-rgb),0.65)] dark:bg-[var(--theme-accent)] dark:text-white' : 'bg-transparent text-slate-600 group-hover:text-slate-900 dark:text-slate-200 dark:group-hover:text-white' }}'"
                                @if ($isCurrent)
                                    x-bind:style="sidebarContentVisible ? 'border-color: rgba(var(--theme-accent-rgb), 0.16); background-color: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);' : ''"
                                @endif>
                                <i class="{{ $icon }} fa-fw text-[16px] leading-none"></i>
                            </span>
                            <span class="min-w-0 flex-1 truncate text-[13.5px] font-medium tracking-[0.005em] {{ $isCurrent ? 'text-slate-950 dark:text-white' : 'text-slate-700 dark:text-slate-300' }}"
                                x-cloak
                                x-show="sidebarContentVisible"
                                x-transition:enter="transition ease-out duration-140"
                                x-transition:enter-start="opacity-0 -translate-x-1.5"
                                x-transition:enter-end="opacity-100 translate-x-0">{{ $item['label'] }}</span>
                            @if ($badge)
                                <span title="{{ __('This is a separate addon module and is not included in the main script.') }}" class="ml-1 inline-flex shrink-0 items-center rounded border px-0.5 py-px text-[7px] font-bold uppercase leading-none tracking-normal"
                                    x-cloak
                                    x-show="sidebarContentVisible"
                                    x-transition:enter="transition ease-out duration-120"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    style="border-color: rgba(var(--theme-accent-rgb),0.16); background-color: rgba(var(--theme-accent-rgb),0.06); color: var(--theme-accent);">{{ $badge }}</span>
                            @endif
                            @if (! empty($item['suffix']))
                                <span class="ml-2 text-[12px] text-slate-400 group-hover:text-slate-500"
                                    x-cloak
                                    x-show="sidebarContentVisible"
                                    x-transition:enter="transition ease-out duration-120"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100">{{ $item['suffix'] }}</span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </div>
        </section>
    @endforeach
@endif
