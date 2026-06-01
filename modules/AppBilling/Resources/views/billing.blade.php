@component(theme_view('layouts.app', 'app'), ['title' => __('Billing')])
    @php
        $billingCycle = $plan ? match ((int) $plan->type) {
            2 => __('Yearly'),
            3 => __('Lifetime'),
            default => __('Monthly'),
        } : __('No billing cycle');
        $nextBillingCycle = $user?->nextPlan ? match ((int) $user->nextPlan->type) {
            2 => __('Yearly'),
            3 => __('Lifetime'),
            default => __('Monthly'),
        } : null;
        $planStatus = $user?->isInPlanTrial() ? __('Trial active') : ($user?->hasActivePlan() ? __('Active') : __('Inactive'));
        $creditUsageLabel = $creditSummary['unlimited']
            ? __('Unlimited')
            : format_number_locale((int) ($creditSummary['remaining'] ?? 0)).' '.__('left');
        $lifetimeValue = format_money($summary['lifetimeValue'], $plan?->currency);
    @endphp

    <div class="space-y-6">
        <section class="overflow-hidden rounded-[1.25rem] border px-5 py-6 sm:px-6 xl:px-7" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), .12), transparent 38%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_26rem] xl:items-center">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-md border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                        <i class="fa-light fa-credit-card"></i>{{ __('Billing workspace') }}
                    </div>
                    <h1 class="mt-4 text-[2.35rem] font-semibold leading-tight tracking-[-0.055em]" style="color: var(--theme-header-text-color);">{{ __('Billing') }}</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Review your active plan, credit coverage, subscription status, and recent payment activity from one workspace.') }}</p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        @if (credit_topup_service()->canUserBuyTopup($user))
                            <x-ui.button href="{{ route('portal.credits') }}" wire:navigate><i class="fa-light fa-coins"></i>{{ __('Buy more credits') }}</x-ui.button>
                        @endif
                        <x-ui.button href="{{ route('portal.invoices') }}" variant="outline" wire:navigate><i class="fa-light fa-file-invoice"></i>{{ __('Open invoices') }}</x-ui.button>
                    </div>
                </div>

                <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-overlay) 92%, transparent);">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Current plan') }}</p>
                    <div class="mt-3 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $plan?->name ?? __('No plan assigned') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $billingCycle }} · {{ $planStatus }}</p>
                        </div>
                        <x-ui.badge :variant="$user?->hasActivePlan() ? 'success' : 'neutral'">{{ $planStatus }}</x-ui.badge>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-xl border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .52); background-color: var(--theme-surface-base);">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Invoices paid') }}</p>
                            <p class="mt-2 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($summary['paidInvoices']) }}</p>
                        </div>
                        <div class="rounded-xl border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .52); background-color: var(--theme-surface-base);">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Lifetime value') }}</p>
                            <p class="mt-2 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ $lifetimeValue }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @foreach ([
            $activeRecurringSubscription ? ['icon' => 'fa-arrows-rotate', 'title' => __('Active recurring subscription'), 'text' => __('Your account is currently on an auto-renewing subscription for :plan. To change plan, choose a different plan from pricing.', ['plan' => $activeRecurringSubscription->plan?->name ?: __('your current plan')]), 'tone' => 'accent'] : null,
            $user?->isInPlanTrial() ? ['icon' => 'fa-hourglass-clock', 'title' => __('Trial active'), 'text' => __('Your :plan trial is active until :date.', ['plan' => $plan?->name ?: __('current plan'), 'date' => $user?->trialEndsAt() ? format_date_vn($user->trialEndsAt(), 'd/m/Y H:i') : __('the trial end date')]), 'tone' => 'warning'] : null,
            $user?->nextPlan ? ['icon' => 'fa-calendar-clock', 'title' => __('Scheduled next plan'), 'text' => __('Your account is scheduled to switch to :plan (:cycle) when the current billing period ends on :date.', ['plan' => $user->nextPlan->name, 'cycle' => $nextBillingCycle, 'date' => $user->plan_expires_at ? format_date_vn($user->plan_expires_at) : __('the next billing date')]), 'tone' => 'accent'] : null,
        ] as $notice)
            @if ($notice)
                <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: var(--theme-surface-overlay);">
                    <div class="flex items-start gap-3">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background-color: color-mix(in srgb, {{ $notice['tone'] === 'warning' ? 'var(--theme-warning-color)' : 'var(--theme-accent)' }} 10%, white); color: {{ $notice['tone'] === 'warning' ? 'var(--theme-warning-color)' : 'var(--theme-accent)' }};"><i class="fa-light {{ $notice['icon'] }}"></i></span>
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $notice['title'] }}</p>
                            <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $notice['text'] }}</p>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => __('Plan price'), 'value' => $plan ? format_money((float) $plan->price, $plan->currency) : __('N/A'), 'text' => $billingCycle, 'icon' => 'fa-tag', 'tone' => 'var(--theme-accent)'],
                ['label' => __('Subscription'), 'value' => $latestSubscription?->statusLabel() ?? __('Inactive'), 'text' => $latestSubscription?->source ?: __('No payment source'), 'icon' => 'fa-arrows-rotate', 'tone' => 'var(--theme-success-color)'],
                ['label' => __('Credits'), 'value' => $creditUsageLabel, 'text' => $creditSummary['unlimited'] ? __('No credit cap on this plan') : __('Plan and top-up balances combined'), 'icon' => 'fa-coins', 'tone' => '#d97706'],
                ['label' => __('Next date'), 'value' => (($d = ($user?->isInPlanTrial() ? $user?->trialEndsAt() : $user?->plan_expires_at)) ? format_date_vn($d) : __('No expiry')), 'text' => $user?->isInPlanTrial() ? __('Trial ends') : __('Plan expiry'), 'icon' => 'fa-calendar-days', 'tone' => '#0ea5e9'],
            ] as $card)
                <div class="rounded-[1rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ $card['label'] }}</p>
                            <p class="mt-2 text-xl font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ $card['value'] }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $card['text'] }}</p>
                        </div>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: color-mix(in srgb, {{ $card['tone'] }} 10%, white); color: {{ $card['tone'] }};"><i class="fa-light {{ $card['icon'] }}"></i></span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_24rem]">
            <section class="overflow-hidden rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Plan overview') }}</p>
                    <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $plan?->name ?? __('No plan assigned') }}</h2>
                </div>
                <div class="grid gap-3 p-5 md:grid-cols-3">
                    @foreach ([
                        __('Status') => $planStatus,
                        __('Billing cycle') => $billingCycle,
                        __('Credits usage') => $creditUsageLabel,
                    ] as $label => $value)
                        <div class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .56); background-color: var(--theme-surface-overlay);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ $label }}</p>
                            <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $value }}</p>
                        </div>
                    @endforeach
                    <div class="rounded-xl border p-4 md:col-span-3" style="border-color: rgba(var(--theme-border-color-rgb), .56); background-color: var(--theme-surface-overlay);">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Credit coverage') }}</p>
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">
                                {{ $creditSummary['unlimited'] ? __('This plan does not enforce a credit cap.') : __('Plan left: :plan / Top-up left: :topup', ['plan' => format_number_locale((int) ($creditSummary['plan_remaining'] ?? 0)), 'topup' => format_number_locale((int) ($creditSummary['topup_remaining'] ?? 0))]) }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Latest subscription') }}</p>
                    <h2 class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $latestSubscription?->subscription_id ?: __('No active subscription') }}</h2>
                </div>
                <div class="space-y-3 p-5">
                    @foreach ([
                        __('Gateway') => $latestSubscription?->source ?: __('N/A'),
                        __('Service') => $latestSubscription?->service ?: __('N/A'),
                        __('Started') => $latestSubscription?->createdAtFormatted('Y-m-d') ?? __('N/A'),
                    ] as $label => $value)
                        <div class="flex items-center justify-between gap-4 rounded-xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .56); background-color: var(--theme-surface-overlay);">
                            <span class="text-sm" style="color: var(--theme-muted-text-color);">{{ $label }}</span>
                            <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $value }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between gap-4 rounded-xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .56); background-color: var(--theme-surface-overlay);">
                        <span class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Status') }}</span>
                        <x-ui.badge :variant="$latestSubscription?->statusVariant() ?? 'neutral'">{{ $latestSubscription?->statusLabel() ?? __('Inactive') }}</x-ui.badge>
                    </div>
                    @if ($latestSubscription?->canBeCancelledByUser())
                        <div class="pt-2">@livewire(\Modules\AppBilling\Livewire\CancelRecurringButton::class, ['subscriptionId' => $latestSubscription->id], key('cancel-recurring-'.$latestSubscription->id))</div>
                    @endif
                </div>
            </section>
        </div>

        <section class="overflow-hidden rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
            <div class="flex flex-col gap-3 border-b px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Directory') }}</p>
                    <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Recent payments') }}</h2>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('The latest charges tied to your account.') }}</p>
                </div>
                <x-ui.button href="{{ route('portal.invoices') }}" variant="outline" size="sm" wire:navigate><i class="fa-light fa-arrow-right"></i>{{ __('View all') }}</x-ui.button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead style="color: var(--theme-muted-text-color);">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Invoice') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Plan') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Gateway') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Amount') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Created') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        @forelse ($recentPayments as $payment)
                            <tr>
                                <td class="px-5 py-4"><p class="font-semibold uppercase" style="color: var(--theme-header-text-color);">{{ $payment->id_secure ?: __('N/A') }}</p><p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $payment->transaction_id }}</p></td>
                                <td class="px-5 py-4">{{ $payment->plan?->name ?: __('N/A') }}</td>
                                <td class="px-5 py-4">{{ $payment->from ?: __('N/A') }}</td>
                                <td class="px-5 py-4 font-semibold" style="color: var(--theme-header-text-color);">{{ ($payment->currency ?: 'USD').' '.format_number_locale((float) $payment->amount, 2) }}</td>
                                <td class="px-5 py-4"><x-ui.badge :variant="$payment->statusVariant()">{{ $payment->statusLabel() }}</x-ui.badge></td>
                                <td class="px-5 py-4">{{ $payment->createdAtFormatted('Y-m-d H:i') ?: __('N/A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center" style="color: var(--theme-muted-text-color);">{{ __('No payment records found yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endcomponent
