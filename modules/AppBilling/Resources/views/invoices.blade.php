@component(theme_view('layouts.app', 'app'), ['title' => __('Invoices')])
    @php
        $paidOnPage = $invoices->getCollection()->where('status', 1);
        $paidCount = $paidOnPage->count();
        $visibleTotal = (float) $paidOnPage->sum('amount');
        $visibleCurrency = (string) ($paidOnPage->first()?->currency ?: auth()->user()?->plan?->currency ?: '');
        $visibleTotalFormatted = format_money($visibleTotal, $visibleCurrency !== '' ? $visibleCurrency : null);
    @endphp

    <div class="space-y-6">
        <section class="overflow-hidden rounded-[1.25rem] border px-5 py-6 sm:px-6 xl:px-7" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), .12), transparent 38%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_25rem] xl:items-center">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-md border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                        <i class="fa-light fa-file-invoice"></i>{{ __('Invoice center') }}
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <h1 class="text-[2.35rem] font-semibold leading-tight tracking-[-0.055em]" style="color: var(--theme-header-text-color);">{{ __('Invoices') }}</h1>
                        <x-ui.badge variant="neutral">{{ format_number_locale($invoices->total()) }} {{ __('records') }}</x-ui.badge>
                    </div>
                    <p class="mt-3 max-w-2xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Browse payment references, transaction IDs, status, and downloadable PDF invoices for this account.') }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Paid on this page') }}</p>
                        <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($paidCount) }}</p>
                    </div>
                    <div class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Paid total on this page') }}</p>
                        <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ $visibleTotalFormatted }}</p>
                    </div>
                    <x-ui.button href="{{ route('portal.billing') }}" variant="outline" class="col-span-2" wire:navigate><i class="fa-light fa-arrow-left"></i>{{ __('Back to billing') }}</x-ui.button>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
            <div class="flex flex-col gap-3 border-b px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Directory') }}</p>
                    <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Invoice history') }}</h2>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('All recorded billing rows belonging to the current account.') }}</p>
                </div>
                <x-ui.badge variant="neutral">{{ __('Page') }} {{ $invoices->currentPage() }} / {{ $invoices->lastPage() }}</x-ui.badge>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead style="color: var(--theme-muted-text-color);">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Invoice') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Transaction') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Plan') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Gateway') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Amount') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Created') }}</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold uppercase" style="color: var(--theme-header-text-color);">{{ $invoice->id_secure ?: __('N/A') }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">#{{ $invoice->id }}</p>
                                </td>
                                <td class="px-5 py-4"><span class="font-mono text-xs" style="color: var(--theme-muted-text-color);">{{ $invoice->transaction_id ?: __('N/A') }}</span></td>
                                <td class="px-5 py-4">{{ $invoice->plan?->name ?: __('N/A') }}</td>
                                <td class="px-5 py-4">{{ $invoice->from ?: __('N/A') }}</td>
                                <td class="px-5 py-4 font-semibold" style="color: var(--theme-header-text-color);">{{ ($invoice->currency ?: 'USD').' '.format_number_locale((float) $invoice->amount, 2) }}</td>
                                <td class="px-5 py-4"><x-ui.badge :variant="$invoice->statusVariant()">{{ $invoice->statusLabel() }}</x-ui.badge></td>
                                <td class="px-5 py-4">{{ $invoice->createdAtFormatted() ?: __('N/A') }}</td>
                                <td class="px-5 py-4 text-right">
                                    <x-ui.button href="{{ route('portal.invoices.download', $invoice) }}" variant="outline" size="sm"><i class="fa-light fa-download"></i>{{ __('PDF') }}</x-ui.button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-12 text-center" style="color: var(--theme-muted-text-color);">{{ __('No invoices recorded yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($invoices->hasPages())
                <div class="border-t px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">{{ $invoices->links() }}</div>
            @endif
        </section>
    </div>
@endcomponent
