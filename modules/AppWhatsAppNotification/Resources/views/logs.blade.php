<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif

    <section class="overflow-hidden rounded-[1.15rem] border px-5 py-6 sm:px-6 xl:px-7" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), .12), transparent 36%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div>
            <div class="inline-flex items-center gap-2 rounded-md border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);"><i class="fa-light fa-list-check"></i>{{ __('Automation') }}</div>
            <h1 class="mt-4 text-[2.2rem] font-semibold leading-tight tracking-[-0.055em]" style="color: var(--theme-header-text-color);">{{ __('WhatsApp Logs') }}</h1>
            <p class="mt-3 max-w-2xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Track queued, sent, and failed automation WhatsApps.') }}</p>
        </div>
    </section>

    <section class="overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-3 border-b px-5 py-4 xl:grid-cols-[minmax(0,1fr)_14rem_12rem_12rem_9rem]" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <x-ui.input wire:model.live.debounce.300ms="search" name="search" :placeholder="__('Search recipient, message, trigger...')" />
            <x-ui.select wire:model.live="automation" name="automation">
                <option value="all">{{ __('All automations') }}</option>
                @foreach ($automations as $automationItem)
                    <option value="{{ $automationItem->id }}">{{ $automationItem->name }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.select wire:model.live="status" name="status"><option value="all">{{ __('All status') }}</option><option value="queued">{{ __('Queued') }}</option><option value="sent">{{ __('Sent') }}</option><option value="failed">{{ __('Failed') }}</option><option value="skipped">{{ __('Skipped') }}</option></x-ui.select>
            <x-ui.select wire:model.live="dateRange" name="dateRange"><option value="all">{{ __('All time') }}</option><option value="7">{{ __('Last 7 days') }}</option><option value="30">{{ __('Last 30 days') }}</option><option value="90">{{ __('Last 90 days') }}</option></x-ui.select>
            <x-ui.select wire:model.live="perPage" name="perPage"><option value="10">10 / {{ __('page') }}</option><option value="15">15 / {{ __('page') }}</option><option value="25">25 / {{ __('page') }}</option><option value="50">50 / {{ __('page') }}</option></x-ui.select>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead style="color: var(--theme-muted-text-color);">
                    <tr>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Date') }}</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Recipient') }}</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Automation') }}</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Message') }}</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-5 py-4" style="color: var(--theme-muted-text-color);">{{ format_datetime_locale($log->created_at) }}</td>
                            <td class="px-5 py-4"><p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $log->recipient_name ?: __('No name') }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $log->recipient_phone }}</p></td>
                            <td class="px-5 py-4" style="color: var(--theme-muted-text-color);">{{ $log->automation?->name ?: $log->trigger_event }}</td>
                            <td class="px-5 py-4" style="color: var(--theme-muted-text-color);">{{ \Illuminate\Support\Str::limit((string) $log->body, 80) }}</td>
                            <td class="px-5 py-4"><x-ui.badge :variant="$log->status === 'sent' ? 'success' : ($log->status === 'failed' ? 'danger' : ($log->status === 'skipped' ? 'warning' : 'neutral'))">{{ ucfirst($log->status) }}</x-ui.badge>@if($log->error_message)<p class="mt-1 max-w-xs truncate text-xs" style="color: var(--theme-danger-color);">{{ $log->error_message }}</p>@endif</td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <x-ui.dialog :title="$log->template_name ?: __('WhatsApp message')" :description="$log->recipient_phone" width="lg" dismissible>
                                        <x-slot:trigger>
                                            <button type="button" class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);"><i class="fa-light fa-eye"></i>{{ __('View') }}</button>
                                        </x-slot:trigger>
                                        <div class="space-y-3 text-sm" style="color: var(--theme-muted-text-color);">
                                            @if ($log->error_message)<p class="rounded-xl border px-3 py-2" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color);">{{ $log->error_message }}</p>@endif
                                            <div class="whitespace-pre-line rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: var(--theme-surface-base);">{{ $log->body ?: __('No WhatsApp body was generated.') }}</div>
                                        </div>
                                    </x-ui.dialog>
                                    @if ($log->status !== 'skipped')
                                        <button type="button" wire:click="resend({{ $log->id }})" class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);"><i class="fa-light fa-rotate-right"></i>{{ __('Resend') }}</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-sm" style="color: var(--theme-muted-text-color);">{{ __('No WhatsApp Logs yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <p class="text-sm" style="color: var(--theme-muted-text-color);">
                {{ __('Showing') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($logs->firstItem() ?? 0) }}</span>
                -
                <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($logs->lastItem() ?? 0) }}</span>
                {{ __('of') }}
                <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($logs->total()) }}</span>
                {{ __('logs') }}
            </p>
            <div>{{ $logs->links() }}</div>
        </div>
    </section>
</div>
