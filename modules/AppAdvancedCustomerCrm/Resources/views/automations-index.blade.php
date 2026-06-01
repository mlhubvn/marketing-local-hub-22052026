<div
    class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6"
    x-data="{ automationDialogOpen: false }"
    x-on:crm-automation-saved.window="automationDialogOpen = false"
>
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif

    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb),.13), transparent 34%), linear-gradient(35deg, rgba(var(--theme-warning-color-rgb),.10), transparent 38%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-7 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-wand-magic-sparkles"></i>{{ __('Advanced CRM') }}
                </div>
                <h1 class="mt-4 text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('CRM Automations') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">{{ __('Trigger tags, tasks, notes, score changes, and Email, WhatsApp, or Webhook automations from customer events.') }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button type="button" size="lg" x-on:click="automationDialogOpen = true">
                        <i class="fa-light fa-plus"></i>{{ __('Create automation') }}
                    </x-ui.button>
                    <x-ui.button href="{{ route('portal.crm.tasks') }}" variant="outline" size="lg" wire:navigate>
                        <i class="fa-light fa-list-check"></i>{{ __('View follow-ups') }}
                    </x-ui.button>
                </div>
            </div>
            <div class="rounded-[1.2rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div><p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Automation engine') }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Immediate and delayed CRM actions') }}</p></div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),.12); color: var(--theme-accent);"><i class="fa-light fa-bolt"></i></div>
                </div>
                <div class="mt-5 grid grid-cols-3 gap-3">
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);"><p class="text-2xl font-semibold tracking-[-0.045em]">{{ format_number_locale($automations->total()) }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Rules') }}</p></div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);"><p class="text-2xl font-semibold tracking-[-0.045em]">{{ format_number_locale($automations->where('status', 'active')->count()) }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Active') }}</p></div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);"><p class="text-2xl font-semibold tracking-[-0.045em]">{{ format_number_locale($automations->sum('logs_count')) }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Logs') }}</p></div>
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid divide-y sm:grid-cols-2 sm:divide-x sm:divide-y-0 xl:grid-cols-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
            @foreach ([
                ['label' => __('CRM rules'), 'value' => $automations->total(), 'description' => __('Active and draft automation rules'), 'icon' => 'fa-light fa-wand-magic-sparkles', 'tone' => 'accent'],
                ['label' => __('Delayed jobs'), 'value' => 0, 'description' => __('Queued follow-up actions'), 'icon' => 'fa-light fa-clock', 'tone' => 'warning'],
                ['label' => __('External actions'), 'value' => 3, 'description' => __('Email, WhatsApp, Webhook'), 'icon' => 'fa-light fa-plug', 'tone' => 'success'],
                ['label' => __('Automation logs'), 'value' => $automations->sum('logs_count'), 'description' => __('Completed or skipped runs'), 'icon' => 'fa-light fa-list-timeline', 'tone' => 'accent'],
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
                <article class="group flex min-h-[8.25rem] items-center gap-4 px-5 py-4 transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)]">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition group-hover:scale-[1.03]" style="background-color: rgba({{ $toneRgb }}, .11); color: {{ $toneColor }};"><i class="{{ $metric['icon'] }}"></i></div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-baseline justify-between gap-3"><p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p><p class="text-[1.75rem] font-semibold leading-none tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($metric['value']) }}</p></div>
                        <p class="mt-2 truncate text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $metric['description'] }}</p>
                        <div class="mt-3 h-1.5 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), .35);"><div class="h-full rounded-full" style="width: {{ (int) min(100, max(8, $metric['value'] > 0 ? 64 : 8)) }}%; background-color: {{ $toneColor }};"></div></div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Automation workspace') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Manage CRM triggers, conditions, actions, and delayed follow-up flows.') }}</p>
                </div>
                <x-ui.button type="button" size="sm" x-on:click="automationDialogOpen = true">
                    <i class="fa-light fa-plus"></i>{{ __('New automation') }}
                </x-ui.button>
            </div>
        </div>

        @if($automations->count())
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                        <tr><th class="w-[32%] px-6 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Automation') }}</th><th class="px-6 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Trigger') }}</th><th class="px-6 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Action') }}</th><th class="px-6 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th><th class="px-6 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Logs') }}</th><th class="w-[9rem] px-6 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th></tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        @foreach($automations as $automation)
                            @php $firstAction = (array) data_get($automation->action_json, 'actions.0', []); @endphp
                            <tr class="transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)]">
                                <td class="px-6 py-4"><div class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),.10); color: var(--theme-accent);"><i class="fa-light fa-wand-magic-sparkles"></i></span><div><p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $automation->name }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $automation->delay_type === 'after' ? __('Delayed') : __('Immediate') }}</p></div></div></td>
                                <td class="px-6 py-4">{{ $triggers[$automation->trigger_event] ?? str($automation->trigger_event)->headline() }}</td>
                                <td class="px-6 py-4"><x-ui.badge variant="neutral">{{ str((string) data_get($firstAction, 'type', 'action'))->replace('_', ' ')->headline() }}</x-ui.badge></td>
                                <td class="px-6 py-4"><x-ui.badge :variant="$automation->status === 'active' ? 'success' : 'neutral'">{{ str($automation->status)->headline() }}</x-ui.badge></td>
                                <td class="px-6 py-4">{{ format_number_locale($automation->logs_count) }}</td>
                                <td class="px-6 py-4 text-right"><div class="inline-flex items-center gap-2"><button type="button" wire:click="edit({{ $automation->id }})" x-on:click="automationDialogOpen = true" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent);" title="{{ __('Edit') }}"><i class="fa-light fa-pen"></i></button><button type="button" wire:click="delete({{ $automation->id }})" wire:confirm="{{ __('Delete this automation?') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color);" title="{{ __('Delete') }}"><i class="fa-light fa-trash"></i></button></div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">{{ $automations->links() }}</div>
        @else
            <div class="p-4">
                <div class="relative overflow-hidden rounded-[1.15rem] border p-6" style="border-color: rgba(var(--theme-border-color-rgb), .58); background: radial-gradient(circle at top right, rgba(var(--theme-warning-color-rgb),.13), transparent 34%), linear-gradient(135deg, rgba(var(--theme-accent-rgb),.075), transparent 46%), color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-center">
                        <div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb),.10); color: var(--theme-accent);"><i class="fa-light fa-wand-magic-sparkles text-xl"></i></div>
                            <h2 class="mt-5 text-xl font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">{{ __('No CRM automations yet') }}</h2>
                            <p class="mt-3 max-w-xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Create automations to add tags, create follow-up tasks, send email or WhatsApp messages, and call webhooks from customer events.') }}</p>
                            <div class="mt-5"><x-ui.button type="button" size="sm" x-on:click="automationDialogOpen = true"><i class="fa-light fa-plus"></i>{{ __('Create automation') }}</x-ui.button></div>
                        </div>
                        <div class="grid gap-2">
                            @foreach ([__('Low-score feedback -> follow-up task'), __('Coupon claimed -> email reminder'), __('Inactive customer -> comeback workflow')] as $label)
                                <div class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .52); color: var(--theme-header-text-color); background-color: color-mix(in srgb, var(--theme-surface-overlay) 82%, transparent);"><span class="flex h-7 w-7 items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-accent-rgb),.10); color: var(--theme-accent);"><i class="fa-light fa-check text-xs"></i></span>{{ $label }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>

    <template x-teleport="body">
        <div x-cloak x-show="automationDialogOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="automationDialogOpen = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="automationDialogOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <form wire:submit="save" x-show="automationDialogOpen" x-transition.opacity.scale.90 class="relative w-full max-w-4xl overflow-hidden rounded-[1.15rem] border shadow-[0_32px_80px_-34px_rgba(15,23,42,.32)]" style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent); background-color: var(--theme-surface-overlay);">
                    <div class="flex items-start justify-between gap-4 border-b px-5 py-4 sm:px-6" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent);">
                        <div><h3 class="text-[1.05rem] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ $editingId ? __('Edit automation') : __('Create automation') }}</h3><p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Choose a trigger, optional condition, action, and delay for CRM workflows.') }}</p></div>
                        <button type="button" style="color: var(--theme-muted-text-color);" x-on:click="automationDialogOpen = false"><i class="fa-light fa-xmark text-lg"></i></button>
                    </div>
                    <div class="max-h-[calc(100vh-12rem)] overflow-y-auto px-5 py-4 sm:px-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <x-ui.input wire:model="name" name="name" :label="__('Name')" :error="$errors->first('name')" />
                            <x-ui.select wire:model="business_id" :label="__('Business')"><option value="">{{ __('All businesses') }}</option>@foreach($businesses as $business)<option value="{{ $business->id }}">{{ $business->name }}</option>@endforeach</x-ui.select>
                            <x-ui.select wire:model="trigger_event" :label="__('Trigger')">@foreach($triggers as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</x-ui.select>
                            <x-ui.select wire:model="action_type" :label="__('Action')"><option value="create_task">{{ __('Create task') }}</option><option value="add_tag">{{ __('Add tag') }}</option><option value="change_status">{{ __('Change status') }}</option><option value="add_note">{{ __('Add note') }}</option><option value="increase_score">{{ __('Increase score') }}</option><option value="send_email">{{ __('Send Email Automation') }}</option><option value="send_whatsapp">{{ __('Send WhatsApp Automation') }}</option><option value="send_webhook">{{ __('Send Webhook Automation') }}</option></x-ui.select>
                        </div>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div class="rounded-xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Optional condition') }}</p>
                                <div class="mt-3 grid gap-3">
                                    <x-ui.select wire:model="condition_field"><option value="">{{ __('No condition') }}</option><option value="score">{{ __('Score') }}</option><option value="status">{{ __('Status') }}</option><option value="email">{{ __('Email') }}</option><option value="phone">{{ __('Phone') }}</option><option value="total_bookings">{{ __('Bookings') }}</option><option value="total_coupon_claims">{{ __('Coupon claims') }}</option><option value="total_reviews">{{ __('Reviews') }}</option><option value="total_referrals">{{ __('Referrals') }}</option><option value="review.rating">{{ __('Review rating') }}</option><option value="coupon.status">{{ __('Coupon status') }}</option><option value="booking.status">{{ __('Booking status') }}</option></x-ui.select>
                                    <x-ui.select wire:model="condition_operator"><option value="=">=</option><option value="!=">!=</option><option value=">">&gt;</option><option value="<">&lt;</option><option value=">=">&gt;=</option><option value="<=">&lt;=</option><option value="contains">{{ __('Contains') }}</option><option value="exists">{{ __('Exists') }}</option><option value="not_exists">{{ __('Not exists') }}</option></x-ui.select>
                                    <x-ui.input wire:model="condition_value" name="condition_value" :placeholder="__('Value')" />
                                </div>
                            </div>
                            <div class="rounded-xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Action details') }}</p>
                                <div class="mt-3 grid gap-3">
                                    @if(in_array($action_type, ['send_email', 'send_whatsapp', 'send_webhook'], true))
                                        <x-ui.select wire:model="action_value" :label="__('Automation trigger')">@foreach($externalTriggers as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</x-ui.select>
                                    @else
                                        <x-ui.input wire:model="action_value" name="action_value" :label="__('Action value')" :placeholder="__('Tag/status/note/points')" />
                                    @endif
                                    <x-ui.input wire:model="action_title" name="action_title" :label="__('Task title')" />
                                    <x-ui.select wire:model="action_priority" :label="__('Task priority')"><option value="low">{{ __('Low') }}</option><option value="medium">{{ __('Medium') }}</option><option value="high">{{ __('High') }}</option><option value="urgent">{{ __('Urgent') }}</option></x-ui.select>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 grid gap-4 md:grid-cols-3">
                            <x-ui.select wire:model="delay_type" :label="__('Delay')"><option value="immediate">{{ __('Immediately') }}</option><option value="after">{{ __('After delay') }}</option></x-ui.select>
                            @if($delay_type === 'after')
                                <x-ui.input wire:model="delay_value" name="delay_value" type="number" min="1" :label="__('Delay value')" />
                                <x-ui.select wire:model="delay_unit" :label="__('Delay unit')"><option value="minutes">{{ __('Minutes') }}</option><option value="hours">{{ __('Hours') }}</option><option value="days">{{ __('Days') }}</option></x-ui.select>
                            @else
                                <div></div><div></div>
                            @endif
                            <x-ui.select wire:model="status" :label="__('Status')"><option value="active">{{ __('Active') }}</option><option value="inactive">{{ __('Inactive') }}</option></x-ui.select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 border-t px-5 py-4 sm:px-6" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent); background-color: color-mix(in srgb, var(--theme-surface-soft) 88%, transparent);">
                        <x-ui.button type="button" variant="outline" x-on:click="automationDialogOpen = false">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="submit"><i class="fa-light fa-floppy-disk"></i>{{ $editingId ? __('Save automation') : __('Create automation') }}</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
