<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif

    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb),.13), transparent 34%), linear-gradient(35deg, rgba(var(--theme-warning-color-rgb),.10), transparent 38%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-7 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);"><i class="fa-light fa-users-viewfinder"></i>{{ __('Advanced CRM') }}</div>
                <h1 class="mt-4 text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('CRM Customers') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">{{ __('Timeline, tags, status, score, notes, tasks, and customer lifecycle context from every LocalBoost touchpoint.') }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('portal.crm.export.customers') }}" class="inline-flex h-12 items-center justify-center gap-2 rounded-xl border px-5 text-sm font-semibold shadow-[0_12px_24px_-18px_rgba(20,125,120,.75)] transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-accent-rgb), .28); background-color: var(--theme-accent); color: var(--theme-accent-foreground, #fff);">
                        <i class="fa-light fa-file-csv"></i>{{ __('Export CSV') }}
                    </a>
                    <a href="{{ route('portal.crm.tasks') }}" wire:navigate class="inline-flex h-12 items-center justify-center gap-2 rounded-xl border px-5 text-sm font-semibold shadow-[0_12px_24px_-20px_rgba(20,125,120,.55)] transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-accent-rgb), .26); background-color: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">
                        <i class="fa-light fa-list-check"></i>{{ __('View tasks') }}
                    </a>
                </div>
            </div>
            <div class="rounded-[1.2rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3"><div><p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Customer health') }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Profiles ready for follow-up') }}</p></div><div class="flex h-11 w-11 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),.12); color: var(--theme-accent);"><i class="fa-light fa-sparkles"></i></div></div>
                <div class="mt-5 grid grid-cols-3 gap-3">
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);"><p class="text-2xl font-semibold tracking-[-0.045em]">{{ number_format($stats['total']) }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Customers') }}</p></div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);"><p class="text-2xl font-semibold tracking-[-0.045em]">{{ number_format($stats['vip']) }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('VIP') }}</p></div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);"><p class="text-2xl font-semibold tracking-[-0.045em]">{{ number_format($stats['needs_follow_up']) }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Tasks') }}</p></div>
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid divide-y sm:grid-cols-2 sm:divide-x sm:divide-y-0 xl:grid-cols-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
            @foreach ([['label' => __('Customers'), 'value' => $stats['total'], 'icon' => 'fa-light fa-address-book'], ['label' => __('VIP'), 'value' => $stats['vip'], 'icon' => 'fa-light fa-crown'], ['label' => __('Open Tasks'), 'value' => $stats['needs_follow_up'], 'icon' => 'fa-light fa-list-check'], ['label' => __('Inactive'), 'value' => $stats['inactive'], 'icon' => 'fa-light fa-user-clock']] as $metric)
                <article class="flex min-h-[7.25rem] items-center gap-4 px-5 py-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),.11); color: var(--theme-accent);"><i class="{{ $metric['icon'] }}"></i></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold" style="color: var(--theme-muted-text-color);">{{ $metric['label'] }}</p>
                        <p class="mt-2 text-3xl font-semibold leading-none tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ number_format($metric['value']) }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="overflow-visible rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,3fr)] lg:items-end">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Customer workspace') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Filter CRM customers by business, status, tag, and activity.') }}</p>
                </div>
                <div class="grid gap-3 md:grid-cols-6">
                    <x-ui.input wire:model.live.debounce.300ms="search" name="crm_search" :placeholder="__('Search customer...')" />
                    <x-ui.combobox :options="collect([['value' => 'all', 'label' => __('All businesses'), 'icon' => 'fa-layer-group']])->merge($businesses->map(fn ($business) => ['value' => (string) $business->id, 'label' => $business->name, 'icon' => 'fa-store']))->values()->all()" :selected="$businessFilter" model="businessFilter" :placeholder="__('All businesses')" icon="fa-light fa-store" />
                    <x-ui.combobox :options="[['value' => 'all', 'label' => __('All statuses')], ['value' => 'new', 'label' => __('New')], ['value' => 'active', 'label' => __('Active')], ['value' => 'inactive', 'label' => __('Inactive')], ['value' => 'vip', 'label' => __('VIP')], ['value' => 'blocked', 'label' => __('Blocked')]]" :selected="$statusFilter" model="statusFilter" :placeholder="__('All statuses')" icon="fa-light fa-toggle-on" />
                    <x-ui.combobox :options="collect([['value' => 'all', 'label' => __('All tags'), 'icon' => 'fa-tags']])->merge($tags->map(fn ($tag) => ['value' => (string) $tag->id, 'label' => $tag->name, 'icon' => 'fa-tag']))->values()->all()" :selected="$tagFilter" model="tagFilter" :placeholder="__('All tags')" icon="fa-light fa-tags" />
                    <x-ui.combobox :options="collect([['value' => 'all', 'label' => __('All segments'), 'icon' => 'fa-chart-pie-simple']])->merge($systemSegments)->merge($segments->map(fn ($segment) => ['value' => (string) $segment->id, 'label' => $segment->name, 'icon' => 'fa-chart-pie-simple']))->values()->all()" :selected="$segmentFilter" model="segmentFilter" :placeholder="__('All segments')" icon="fa-light fa-chart-pie-simple" />
                    <x-ui.select wire:model.live="perPage"><option value="10">{{ __('10 / page') }}</option><option value="25">{{ __('25 / page') }}</option><option value="50">{{ __('50 / page') }}</option></x-ui.select>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <x-ui.button href="{{ route('portal.crm.export.customers') }}" variant="outline" size="sm"><i class="fa-light fa-file-csv"></i>{{ __('Export CSV') }}</x-ui.button>
            </div>
        </div>

        @if ($customers->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Customer') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Business') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Tags') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Score') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Open tasks') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Last activity') }}</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        @foreach ($customers as $customer)
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $customer->name }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $customer->phone ?: $customer->email ?: __('No contact') }}</p>
                                </td>
                                <td class="px-5 py-4">{{ $customer->business?->name ?: __('No business') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse ($customer->crmTags as $tag)
                                            <span class="rounded-full px-2 py-1 text-[11px] font-semibold" style="background-color: {{ $tag->color }}1a; color: {{ $tag->color }};">{{ $tag->name }}</span>
                                        @empty
                                            <span style="color: var(--theme-muted-text-color);">{{ __('No tags') }}</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ (int) $customer->score }}<span class="text-xs font-semibold" style="color: var(--theme-muted-text-color);">/100</span></p>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ str($customer->status ?: 'active')->headline() }}</p>
                                </td>
                                <td class="px-5 py-4">{{ number_format($customer->open_tasks_count) }}</td>
                                <td class="px-5 py-4">
                                    <p>{{ $customer->last_activity_at?->format('M d, Y H:i') ?: $customer->updated_at?->format('M d, Y') }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ optional($customer->activities->first())->title ?: __('No recent activity') }}</p>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <x-ui.button href="{{ route('portal.crm.customers.show', $customer) }}" size="sm" variant="outline" wire:navigate><i class="fa-light fa-arrow-up-right"></i>{{ __('View') }}</x-ui.button>
                                        @if($customer->status === 'vip')
                                            <button type="button" class="inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-xl border opacity-60" style="border-color: rgba(var(--theme-warning-color-rgb), .28); color: var(--theme-warning-color);" title="{{ __('Already VIP') }}"><i class="fa-solid fa-crown"></i></button>
                                        @else
                                            <button type="button" wire:click="markVip({{ $customer->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent);" title="{{ __('Mark as VIP') }}"><i class="fa-light fa-crown"></i></button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Showing') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($customers->firstItem()) }}</span> - <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($customers->lastItem()) }}</span> {{ __('of') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($customers->total()) }}</span> {{ __('customers') }}</p>
                <div class="flex items-center gap-2">{{ $customers->links() }}</div>
            </div>
        @else
            <div class="p-8"><x-ui.empty icon="fa-light fa-users-viewfinder" :title="__('No CRM customers found')" :description="__('Customers appear here from LocalBoost forms, bookings, coupons, loyalty, referral, and manual records.')" /></div>
        @endif
    </section>
</div>
