<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb),.13), transparent 34%), linear-gradient(35deg, rgba(var(--theme-warning-color-rgb),.10), transparent 38%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-7 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);"><i class="fa-light fa-list-check"></i>{{ __('Advanced CRM') }}</div>
                <h1 class="mt-4 text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('CRM Tasks') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">{{ __('Manage follow-up calls, emails, WhatsApp reminders, meetings, and recovery tasks across all customers.') }}</p>
            </div>
            <div class="rounded-[1.2rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3"><div><p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Task workload') }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Current filtered follow-ups') }}</p></div><div class="flex h-11 w-11 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),.12); color: var(--theme-accent);"><i class="fa-light fa-bolt"></i></div></div>
                <div class="mt-5 grid grid-cols-3 gap-3">
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);"><p class="text-2xl font-semibold tracking-[-0.045em]">{{ format_number_locale($tasks->total()) }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Tasks') }}</p></div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);"><p class="text-2xl font-semibold tracking-[-0.045em]">{{ format_number_locale($tasks->count()) }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Shown') }}</p></div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);"><p class="text-2xl font-semibold tracking-[-0.045em]">{{ format_number_locale($tasks->currentPage()) }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Page') }}</p></div>
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-visible rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div><p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Follow-up workspace') }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Filter tasks by status, priority, and page size.') }}</p></div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <x-ui.select wire:model.live="statusFilter"><option value="all">{{ __('All statuses') }}</option><option value="open">{{ __('Open') }}</option><option value="in_progress">{{ __('In progress') }}</option><option value="done">{{ __('Done') }}</option><option value="cancelled">{{ __('Cancelled') }}</option></x-ui.select>
                    <x-ui.select wire:model.live="priorityFilter"><option value="all">{{ __('All priorities') }}</option><option value="low">{{ __('Low') }}</option><option value="medium">{{ __('Medium') }}</option><option value="high">{{ __('High') }}</option><option value="urgent">{{ __('Urgent') }}</option></x-ui.select>
                    <x-ui.select wire:model.live="perPage"><option value="10">{{ __('10 / page') }}</option><option value="25">{{ __('25 / page') }}</option><option value="50">{{ __('50 / page') }}</option></x-ui.select>
                </div>
            </div>
        </div>

        @if ($tasks->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);"><tr><th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Task') }}</th><th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Customer') }}</th><th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Priority') }}</th><th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Due') }}</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th></tr></thead>
                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        @foreach ($tasks as $task)
                            <tr class="transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)]">
                                <td class="px-5 py-4"><div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-accent-rgb),.2); background-color: rgba(var(--theme-accent-rgb),.10); color: var(--theme-accent);"><i class="fa-light fa-list-check"></i></span><div><p class="font-semibold">{{ $task->title }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ str($task->type)->headline() }} · {{ str($task->status)->headline() }}</p></div></div></td>
                                <td class="px-5 py-4"><a href="{{ route('portal.crm.customers.show', $task->customer) }}" wire:navigate class="font-semibold" style="color: var(--theme-accent);">{{ $task->customer?->name }}</a><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $task->customer?->business?->name }}</p></td>
                                <td class="px-5 py-4"><x-ui.badge :variant="in_array($task->priority, ['high','urgent'], true) ? 'warning' : 'neutral'">{{ str($task->priority)->headline() }}</x-ui.badge></td>
                                <td class="px-5 py-4">{{ format_datetime_locale($task->due_at) ?: __('No due date') }}</td>
                                <td class="px-5 py-4 text-right">@if($task->status !== 'done')<button type="button" wire:click="completeTask({{ $task->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent);" title="{{ __('Mark done') }}"><i class="fa-light fa-check"></i></button>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">{{ $tasks->links() }}</div>
        @else
            <div class="p-4"><div class="rounded-[1.15rem] border p-8" style="border-color: rgba(var(--theme-border-color-rgb), .58); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb),.075), transparent 46%), color-mix(in srgb, var(--theme-surface-base) 94%, transparent);"><x-ui.empty icon="fa-light fa-list-check" :title="__('No tasks found')" :description="__('Tasks created from customer profiles and CRM automations will appear here.')" /></div></div>
        @endif
    </section>
</div>
