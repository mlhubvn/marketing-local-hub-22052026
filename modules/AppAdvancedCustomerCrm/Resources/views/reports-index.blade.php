<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb),.13), transparent 34%), linear-gradient(35deg, rgba(var(--theme-warning-color-rgb),.10), transparent 38%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-7 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-chart-line"></i>{{ __('Advanced CRM') }}
                </div>
                <h1 class="mt-4 text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('CRM Reports') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">{{ __('Track customer growth, lifecycle health, activity volume, tag adoption, and follow-up workload.') }}</p>
            </div>
            <div class="rounded-[1.2rem] border p-4 shadow-[0_24px_70px_-48px_rgba(var(--theme-border-color-rgb),.9)]" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div><p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('CRM health') }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Customer database snapshot') }}</p></div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);"><i class="fa-light fa-sparkles"></i></div>
                </div>
                <div class="mt-5 grid grid-cols-3 gap-3">
                    @foreach(array_slice($metrics, 0, 3) as $metric)
                        <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                            <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($metric['value']) }}<span class="text-sm font-semibold tracking-normal" style="color: var(--theme-muted-text-color);">{{ $metric['suffix'] ?? '' }}</span></p>
                            <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $metric['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-visible rounded-[1.15rem] border px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <x-ui.select wire:model.live="businessFilter">
                <option value="all">{{ __('All businesses') }}</option>
                @foreach($businesses as $business)<option value="{{ $business->id }}">{{ $business->name }}</option>@endforeach
            </x-ui.select>
            <x-ui.select wire:model.live="dateRange">
                <option value="12_months">{{ __('Last 12 months') }}</option>
                <option value="90_days">{{ __('Last 90 days') }}</option>
                <option value="30_days">{{ __('Last 30 days') }}</option>
                <option value="year">{{ __('This year') }}</option>
            </x-ui.select>
            <x-ui.select wire:model.live="segmentFilter">
                <option value="all">{{ __('All segments') }}</option>
                @foreach($segments as $segment)<option value="{{ $segment->id }}">{{ $segment->name }}</option>@endforeach
            </x-ui.select>
            <x-ui.select wire:model.live="tagFilter">
                <option value="all">{{ __('All tags') }}</option>
                @foreach($allTags as $tag)<option value="{{ $tag->id }}">{{ $tag->name }}</option>@endforeach
            </x-ui.select>
        </div>
    </section>

    <section class="overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid divide-y sm:grid-cols-2 sm:divide-x sm:divide-y-0 xl:grid-cols-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
            @foreach($metrics as $metric)
                <article class="group flex min-h-[8.25rem] items-center gap-4 px-5 py-4 transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)]">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition group-hover:scale-[1.03]" style="background-color: rgba(var(--theme-accent-rgb),.11); color: var(--theme-accent);"><i class="{{ $metric['icon'] }}"></i></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-baseline justify-between gap-3"><p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p><p class="text-[1.75rem] font-semibold leading-none tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($metric['value']) }}<span class="text-sm font-semibold tracking-normal" style="color: var(--theme-muted-text-color);">{{ $metric['suffix'] ?? '' }}</span></p></div>
                        <div class="mt-3 h-1.5 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), .35);"><div class="h-full rounded-full" style="width: {{ min(100, max(8, (int) $metric['value'])) }}%; background-color: var(--theme-accent);"></div></div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <x-ui.chart :title="__('Customer growth trend')" :description="__('New customers captured over the last 12 months.')" type="line" :categories="$growthCategories" :series="$growthSeries" :height="320" />
        <x-ui.chart :title="__('Activity breakdown')" :description="__('Most common CRM timeline events.')" type="column" :categories="$activityCategories" :series="$activitySeries" :height="320" />
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,24rem)_minmax(0,1fr)]">
        <x-ui.chart :title="__('Score distribution')" :description="__('Customer lifecycle by CRM score.')" type="donut" :series="$scoreSeries" :height="300" legend />
        <div class="rounded-[1.15rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Top customer tags') }}</p>
            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Tags with the highest customer count.') }}</p>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @forelse($tags as $tag)
                    <a href="{{ route('portal.crm.customers', ['tag' => $tag->id]) }}" wire:navigate class="rounded-xl border p-4 transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        <div class="flex items-center gap-2"><span class="h-3 w-3 rounded-full" style="background-color: {{ $tag->color }}"></span><p class="font-semibold">{{ $tag->name }}</p></div>
                        <p class="mt-2 text-2xl font-semibold">{{ format_number_locale($tag->customers_count) }}</p>
                    </a>
                @empty
                    <x-ui.empty icon="fa-light fa-tags" :title="__('No tags yet')" />
                @endforelse
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <x-ui.chart :title="__('Follow-up workload')" :description="__('Open, overdue, due today, and completed CRM tasks.')" type="column" :categories="$taskCategories" :series="$taskSeries" :height="320" />
        <x-ui.chart :title="__('Top customer sources')" :description="__('Where CRM customer profiles are coming from.')" type="donut" :series="$sourceSeries" :height="320" legend />
    </section>
</div>
