<div
    class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6"
    x-data="{ segmentDialogOpen: false }"
    x-on:crm-segment-saved.window="segmentDialogOpen = false"
>
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif

    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb),.13), transparent 34%), linear-gradient(35deg, rgba(var(--theme-warning-color-rgb),.10), transparent 38%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-7 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-chart-pie-simple"></i>{{ __('Advanced CRM') }}
                </div>
                <h1 class="mt-4 text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('CRM Segments') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">{{ __('Build dynamic customer groups using multiple rules for lifecycle, follow-up, retention, and export workflows.') }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button type="button" size="lg" wire:click="create" x-on:click="segmentDialogOpen = true">
                        <i class="fa-light fa-plus"></i>{{ __('Create segment') }}
                    </x-ui.button>
                </div>
            </div>
            <div class="rounded-[1.2rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Segment builder') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Saved and system audiences') }}</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);"><i class="fa-light fa-layer-group"></i></div>
                </div>
                <div class="mt-5 grid grid-cols-3 gap-3">
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);"><p class="text-2xl font-semibold tracking-[-0.045em]">{{ format_number_locale($savedSegments->count()) }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Saved') }}</p></div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);"><p class="text-2xl font-semibold tracking-[-0.045em]">{{ format_number_locale(count($segments)) }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('System') }}</p></div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);"><p class="text-2xl font-semibold tracking-[-0.045em]">{{ strtoupper($match) }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Match') }}</p></div>
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Segments workspace') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Manage saved dynamic segments and review default CRM audiences.') }}</p>
                </div>
                <x-ui.button type="button" size="sm" wire:click="create" x-on:click="segmentDialogOpen = true">
                    <i class="fa-light fa-plus"></i>{{ __('New segment') }}
                </x-ui.button>
            </div>
        </div>

        @if($savedSegments->count())
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                        <tr>
                            <th class="w-[42%] px-6 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Segment') }}</th>
                            <th class="px-6 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Customers') }}</th>
                            <th class="px-6 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Type') }}</th>
                            <th class="w-[12rem] px-6 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        @foreach($savedSegments as $segment)
                            <tr class="transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)]">
                                <td class="px-6 py-4">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" style="background-color: {{ $segment['color'] }}1a; color: {{ $segment['color'] }};"><i class="fa-light fa-chart-pie-simple"></i></span>
                                        <div class="min-w-0"><p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $segment['name'] }}</p><p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $segment['description'] ?: __('Dynamic CRM segment') }}</p></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ format_number_locale($segment['count']) }}</td>
                                <td class="px-6 py-4"><x-ui.badge variant="info">{{ __('Dynamic') }}</x-ui.badge></td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('portal.crm.customers', ['segment' => $segment['id']]) }}" wire:navigate class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color);" title="{{ __('View customers') }}"><i class="fa-light fa-users"></i></a>
                                        <button type="button" wire:click="edit({{ $segment['id'] }})" x-on:click="segmentDialogOpen = true" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent);" title="{{ __('Edit segment') }}"><i class="fa-light fa-pen"></i></button>
                                        <button type="button" wire:click="duplicate({{ $segment['id'] }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-muted-text-color);" title="{{ __('Duplicate segment') }}"><i class="fa-light fa-copy"></i></button>
                                        <a href="{{ route('portal.crm.export.customers', ['segment' => $segment['id']]) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent);" title="{{ __('Export customers') }}"><i class="fa-light fa-file-csv"></i></a>
                                        <button type="button" wire:click="delete({{ $segment['id'] }})" wire:confirm="{{ __('Delete this segment?') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color);" title="{{ __('Delete') }}"><i class="fa-light fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-4">
                <div class="relative overflow-hidden rounded-[1.15rem] border p-6" style="border-color: rgba(var(--theme-border-color-rgb), .58); background: radial-gradient(circle at top right, rgba(var(--theme-warning-color-rgb),.13), transparent 34%), linear-gradient(135deg, rgba(var(--theme-accent-rgb),.075), transparent 46%), color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-center">
                        <div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb),.10); color: var(--theme-accent);"><i class="fa-light fa-chart-pie-simple text-xl"></i></div>
                            <h2 class="mt-5 text-xl font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">{{ __('No saved segments yet') }}</h2>
                            <p class="mt-3 max-w-xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Create a dynamic segment to group customers by score, activity, status, bookings, coupons, loyalty stamps, or referrals.') }}</p>
                            <div class="mt-5"><x-ui.button type="button" size="sm" wire:click="create" x-on:click="segmentDialogOpen = true"><i class="fa-light fa-plus"></i>{{ __('Create segment') }}</x-ui.button></div>
                        </div>
                        <div class="grid gap-2">
                            @foreach ([__('Multiple rules'), __('Match all or any'), __('CSV export ready')] as $label)
                                <div class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .52); color: var(--theme-header-text-color); background-color: color-mix(in srgb, var(--theme-surface-overlay) 82%, transparent);"><span class="flex h-7 w-7 items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-accent-rgb),.10); color: var(--theme-accent);"><i class="fa-light fa-check text-xs"></i></span>{{ $label }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($segments as $segment)
            <a href="{{ route('portal.crm.customers', ['segment' => $segment['key']]) }}" wire:navigate class="group rounded-[1.15rem] border p-5 transition hover:-translate-y-0.5 hover:shadow-[0_18px_46px_-32px_rgba(15,23,42,.42)]" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <div class="flex items-start justify-between gap-4">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl" style="background-color: {{ $segment['color'] }}1a; color: {{ $segment['color'] }};"><i class="fa-light fa-chart-pie-simple"></i></span>
                    <span class="text-3xl font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($segment['count']) }}</span>
                </div>
                <div class="mt-4 flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $segment['name'] }}</h2>
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border opacity-0 transition group-hover:opacity-100" style="border-color: rgba(var(--theme-accent-rgb), .24); color: var(--theme-accent);"><i class="fa-light fa-arrow-up-right"></i></span>
                </div>
                <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $segment['description'] }}</p>
            </a>
        @endforeach
    </section>

    <template x-teleport="body">
        <div x-cloak x-show="segmentDialogOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="segmentDialogOpen = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="segmentDialogOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <form wire:submit="save" x-show="segmentDialogOpen" x-transition.opacity.scale.90 class="relative w-full max-w-3xl overflow-hidden rounded-[1.15rem] border shadow-[0_32px_80px_-34px_rgba(15,23,42,.32)]" style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent); background-color: var(--theme-surface-overlay);">
                    <div class="flex items-start justify-between gap-4 border-b px-5 py-4 sm:px-6" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent);">
                        <div><h3 class="text-[1.05rem] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ $editingId ? __('Edit dynamic segment') : __('Create dynamic segment') }}</h3><p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Define one or more rules to save a reusable customer audience.') }}</p></div>
                        <button type="button" style="color: var(--theme-muted-text-color);" x-on:click="segmentDialogOpen = false"><i class="fa-light fa-xmark text-lg"></i></button>
                    </div>
                    <div class="max-h-[calc(100vh-12rem)] overflow-y-auto px-5 py-4 sm:px-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <x-ui.input wire:model="name" name="name" :label="__('Name')" :error="$errors->first('name')" />
                            <x-ui.select wire:model.live="business_id" :label="__('Business')"><option value="">{{ __('All businesses') }}</option>@foreach($businesses as $business)<option value="{{ $business->id }}">{{ $business->name }}</option>@endforeach</x-ui.select>
                            <div class="md:col-span-2"><x-ui.textarea wire:model="description" name="description" :label="__('Description')" rows="3">{{ $description }}</x-ui.textarea></div>
                            <x-ui.select wire:model.live="match" :label="__('Match')"><option value="all">{{ __('All rules') }}</option><option value="any">{{ __('Any rule') }}</option></x-ui.select>
                            <x-ui.color-picker wire:model="color" name="color" :label="__('Color')" :value="$color" :error="$errors->first('color')" />
                        </div>
                        <div class="mt-4 space-y-3 rounded-xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            @foreach($rules as $index => $rule)
                                <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_10rem_minmax(0,1fr)_auto] md:items-end">
                                    <x-ui.select wire:model.live="rules.{{ $index }}.field" :label="$index === 0 ? __('Field') : null"><option value="score">{{ __('Score') }}</option><option value="status">{{ __('Status') }}</option><option value="tag">{{ __('Tag') }}</option><option value="business_id">{{ __('Business') }}</option><option value="source_type">{{ __('Source') }}</option><option value="open_tasks">{{ __('Open tasks') }}</option><option value="name">{{ __('Name') }}</option><option value="email">{{ __('Email') }}</option><option value="phone">{{ __('Phone') }}</option><option value="total_bookings">{{ __('Total bookings') }}</option><option value="total_coupon_claims">{{ __('Coupon claims') }}</option><option value="total_coupon_used">{{ __('Coupon used') }}</option><option value="total_feedback">{{ __('Feedback') }}</option><option value="total_reviews">{{ __('Reviews') }}</option><option value="total_loyalty_stamps">{{ __('Loyalty stamps') }}</option><option value="total_referrals">{{ __('Referrals') }}</option><option value="last_activity_at">{{ __('Last activity') }}</option><option value="created_at">{{ __('Created date') }}</option></x-ui.select>
                                    <x-ui.select wire:model.live="rules.{{ $index }}.operator" :label="$index === 0 ? __('Operator') : null"><option value="=">=</option><option value="!=">!=</option><option value=">">&gt;</option><option value="<">&lt;</option><option value=">=">&gt;=</option><option value="<=">&lt;=</option><option value="contains">{{ __('Contains') }}</option><option value="not_contains">{{ __('Not contains') }}</option><option value="within_days">{{ __('Within last days') }}</option><option value="older_than_days">{{ __('Older than days') }}</option><option value="before">{{ __('Before') }}</option><option value="after">{{ __('After') }}</option><option value="exists">{{ __('Exists') }}</option><option value="not_exists">{{ __('Not exists') }}</option></x-ui.select>
                                    <x-ui.input wire:model.live.debounce.300ms="rules.{{ $index }}.value" name="rules_{{ $index }}_value" :label="$index === 0 ? __('Value') : null" :placeholder="__('Value')" :error="$errors->first('rules.'.$index.'.value')" />
                                    <button type="button" wire:click="removeRule({{ $index }})" @disabled(count($rules) <= 1) class="inline-flex h-11 w-11 items-center justify-center rounded-xl border disabled:cursor-not-allowed disabled:opacity-40" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-muted-text-color);" title="{{ __('Remove rule') }}"><i class="fa-light fa-trash"></i></button>
                                </div>
                            @endforeach
                            <button type="button" wire:click="addRule" class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .07);"><i class="fa-light fa-plus"></i>{{ __('Add rule') }}</button>
                        </div>
                        <div class="mt-4 flex items-center justify-between gap-3 rounded-xl border px-4 py-3" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb), .07);">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Preview matched customers') }}</p>
                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Live estimate based on current business, match mode, and rules.') }}</p>
                            </div>
                            <span class="shrink-0 rounded-2xl px-4 py-2 text-2xl font-semibold tracking-[-0.04em]" style="background-color: color-mix(in srgb, var(--theme-surface-overlay) 84%, transparent); color: var(--theme-accent);">{{ format_number_locale($previewCount) }}</span>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 border-t px-5 py-4 sm:px-6" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent); background-color: color-mix(in srgb, var(--theme-surface-soft) 88%, transparent);">
                        <x-ui.button type="button" variant="outline" x-on:click="segmentDialogOpen = false">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="submit"><i class="fa-light fa-floppy-disk"></i>{{ $editingId ? __('Save segment') : __('Create segment') }}</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
