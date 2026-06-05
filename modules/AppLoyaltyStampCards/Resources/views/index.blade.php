<div
    class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6"
    x-data="{ loyaltyDialogOpen: false, referralDialogOpen: false, loyaltyTab: 'cards' }"
    x-on:loyalty-card-saved.window="loyaltyDialogOpen = false"
    x-on:referral-campaign-saved.window="referralDialogOpen = false"
>
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif

    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 34%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-6 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-md border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-stamp"></i>{{ __('Growth Tools') }}
                </div>
                <h1 class="mt-4 max-w-3xl text-[2.25rem] font-semibold leading-[1.04] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('Loyalty Cards') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">{{ __('Create QR stamp cards, collect customer visits, unlock rewards, and track staff redemptions.') }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button type="button" size="lg" x-on:click="loyaltyDialogOpen = true; $wire.create()"><i class="fa-light fa-plus"></i>{{ __('Create stamp card') }}</x-ui.button>
                    @if ($allCards->isNotEmpty())
                        <x-ui.button href="{{ $allCards->first()->publicUrl() }}" target="_blank" variant="outline" size="lg"><i class="fa-light fa-arrow-up-right"></i>{{ __('Open latest card') }}</x-ui.button>
                    @endif
                </div>
            </div>

            <div class="rounded-[1.15rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Reward pipeline') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Available rewards waiting for redemption') }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);"><i class="fa-light fa-gift"></i></span>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-2">
                    @foreach ([['label' => __('Active'), 'value' => $stats['active']], ['label' => __('Available'), 'value' => $stats['available_rewards']], ['label' => __('Used'), 'value' => $stats['used_rewards']]] as $item)
                        <div class="rounded-lg border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                            <p class="text-xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($item['value']) }}</p>
                            <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $item['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid divide-y sm:grid-cols-2 sm:divide-x sm:divide-y-0 xl:grid-cols-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.58);">
        @foreach ([['label' => __('Cards'), 'value' => $stats['cards'], 'description' => __('Stamp programs'), 'icon' => 'fa-light fa-id-card'], ['label' => __('Customers'), 'value' => $stats['customers'], 'description' => __('Tracked progress'), 'icon' => 'fa-light fa-users'], ['label' => __('Stamps'), 'value' => $stats['stamps'], 'description' => __('Total collected'), 'icon' => 'fa-light fa-stamp'], ['label' => __('Referrals'), 'value' => $stats['referrals'], 'description' => __('Friend conversions'), 'icon' => 'fa-light fa-share-nodes'], ['label' => __('Rewards'), 'value' => $stats['available_rewards'] + $stats['referral_rewards'], 'description' => __('Ready to redeem'), 'icon' => 'fa-light fa-gift']] as $metric)
            <article class="group flex min-h-[8.25rem] items-center gap-4 px-5 py-4 transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)]">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition group-hover:scale-[1.03]" style="background-color: rgba(var(--theme-accent-rgb),0.11); color: var(--theme-accent);">
                    <i class="{{ $metric['icon'] }}"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-baseline justify-between gap-3">
                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p>
                        <p class="text-[1.75rem] font-semibold leading-none tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($metric['value']) }}</p>
                    </div>
                    <p class="mt-2 truncate text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $metric['description'] }}</p>
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), 0.35);">
                        <div class="h-full rounded-full" style="width: {{ (int) min(100, max(8, $metric['value'] > 0 ? 68 : 8)) }}%; background-color: var(--theme-accent);"></div>
                    </div>
                </div>
            </article>
        @endforeach
        </div>
    </section>

    <section class="overflow-visible rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Loyalty workspace') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Manage stamp cards, customer progress, and reward redemptions.') }}</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-[minmax(18rem,1fr)_12rem]">
                    <x-ui.combobox
                        :options="collect([['value' => 'all', 'label' => __('All cards'), 'meta' => __('Every loyalty stamp card'), 'icon' => 'fa-layer-group']])
                            ->merge($allCards->map(fn ($card) => [
                                'value' => (string) $card->id,
                                'label' => $card->name,
                                'meta' => $card->business?->name ?: __('Business removed'),
                                'icon' => 'fa-id-card',
                            ]))
                            ->values()
                            ->all()"
                        :selected="$cardFilter"
                        model="cardFilter"
                        name="loyalty_card_filter"
                        :placeholder="__('All cards')"
                        :search-placeholder="__('Search loyalty card...')"
                        :empty-text="__('No loyalty cards found')"
                        icon="fa-light fa-id-card"
                    />
                    <div x-show="loyaltyTab === 'cards'">
                        <x-ui.select wire:model.live="cardsPerPage" name="loyalty_cards_per_page">
                            <option value="10">{{ __('10 / page') }}</option>
                            <option value="25">{{ __('25 / page') }}</option>
                            <option value="50">{{ __('50 / page') }}</option>
                        </x-ui.select>
                    </div>
                    <div x-cloak x-show="loyaltyTab === 'customers'">
                        <x-ui.select wire:model.live="customersPerPage" name="loyalty_customers_per_page">
                            <option value="10">{{ __('10 / page') }}</option>
                            <option value="25">{{ __('25 / page') }}</option>
                            <option value="50">{{ __('50 / page') }}</option>
                        </x-ui.select>
                    </div>
                    <div x-cloak x-show="loyaltyTab === 'stamps'">
                        <x-ui.select wire:model.live="stampsPerPage" name="loyalty_stamps_per_page">
                            <option value="10">{{ __('10 / page') }}</option>
                            <option value="25">{{ __('25 / page') }}</option>
                            <option value="50">{{ __('50 / page') }}</option>
                        </x-ui.select>
                    </div>
                    <div x-cloak x-show="loyaltyTab === 'rewards'">
                        <x-ui.select wire:model.live="rewardsPerPage" name="loyalty_rewards_per_page">
                            <option value="10">{{ __('10 / page') }}</option>
                            <option value="25">{{ __('25 / page') }}</option>
                            <option value="50">{{ __('50 / page') }}</option>
                        </x-ui.select>
                    </div>
                    <div x-cloak x-show="loyaltyTab === 'referralCampaigns'">
                        <x-ui.select wire:model.live="referralCampaignsPerPage" name="referral_campaigns_per_page">
                            <option value="10">{{ __('10 / page') }}</option>
                            <option value="25">{{ __('25 / page') }}</option>
                            <option value="50">{{ __('50 / page') }}</option>
                        </x-ui.select>
                    </div>
                    <div x-cloak x-show="loyaltyTab === 'referrals'">
                        <x-ui.select wire:model.live="referralsPerPage" name="referrals_per_page">
                            <option value="10">{{ __('10 / page') }}</option>
                            <option value="25">{{ __('25 / page') }}</option>
                            <option value="50">{{ __('50 / page') }}</option>
                        </x-ui.select>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 overflow-x-auto border-b p-2" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            @foreach (['cards' => __('Stamp Cards'), 'customers' => __('Customers'), 'stamps' => __('Stamps'), 'referralCampaigns' => __('Referral Campaigns'), 'referrals' => __('Referrals'), 'rewards' => __('Rewards')] as $tabKey => $tabLabel)
                <button type="button" x-on:click="loyaltyTab = @js($tabKey)" class="whitespace-nowrap rounded-xl border px-4 py-2 text-sm font-semibold transition" :style="loyaltyTab === @js($tabKey) ? 'border-color: rgba(var(--theme-accent-rgb),0.16); background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);' : 'border-color: transparent; color: var(--theme-muted-text-color);'">{{ $tabLabel }}</button>
            @endforeach
        </div>

        <div x-show="loyaltyTab === 'cards'">
            <div class="flex justify-end border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                <x-ui.button type="button" size="sm" x-on:click="loyaltyDialogOpen = true; $wire.create()"><i class="fa-light fa-plus"></i>{{ __('New card') }}</x-ui.button>
            </div>
            @if ($cards->count() > 0)
                <div class="hidden overflow-x-auto lg:block">
                    <table class="min-w-full text-left text-sm">
                        <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Card') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Business') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Rule') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Reward') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Activity') }}</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            @foreach ($cards as $card)
                                <tr>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $card->name }}</p>
                                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $card->slug }}</p>
                                    </td>
                                    <td class="px-5 py-4">{{ $card->business?->name ?: __('Business removed') }}</td>
                                    <td class="px-5 py-4">{{ __(':count stamps', ['count' => $card->required_stamps]) }}</td>
                                    <td class="px-5 py-4">{{ $card->reward_title }}</td>
                                    <td class="px-5 py-4">
                                        <p>{{ __(':count customers', ['count' => $card->customers_count]) }}</p>
                                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __(':count stamps', ['count' => $card->stamps_count]) }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ $card->publicUrl() }}" target="_blank" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('Open') }}">
                                                <i class="fa-light fa-arrow-up-right"></i>
                                            </a>
                                            <x-ui.dropdown-menu align="right" width="auto">
                                                <x-slot:trigger>
                                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('QR code') }}">
                                                        <i class="fa-light fa-qrcode"></i>
                                                    </button>
                                                </x-slot:trigger>
                                                <x-ui.dropdown-menu-item icon="fa-light fa-copy" onclick="navigator.clipboard && navigator.clipboard.writeText(this.dataset.copy || '')" data-copy="{{ e($card->publicUrl()) }}">{{ __('Copy QR link') }}</x-ui.dropdown-menu-item>
                                                <x-ui.dropdown-menu-item :href="route('loyalty-cards.png', ['card' => $card->slug])" icon="fa-light fa-file-image">{{ __('Download PNG') }}</x-ui.dropdown-menu-item>
                                                <x-ui.dropdown-menu-item :href="route('loyalty-cards.svg', ['card' => $card->slug])" icon="fa-light fa-code">{{ __('Download SVG') }}</x-ui.dropdown-menu-item>
                                            </x-ui.dropdown-menu>
                                            <button type="button" x-on:click="loyaltyDialogOpen = true; $wire.edit({{ $card->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-accent-rgb), .30); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .06);" title="{{ __('Edit') }}">
                                                <i class="fa-light fa-pen"></i>
                                            </button>
                                            <button type="button" wire:click="toggleStatus({{ $card->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ $card->status === 'active' ? __('Pause') : __('Activate') }}">
                                                <i class="fa-light {{ $card->status === 'active' ? 'fa-pause' : 'fa-play' }}"></i>
                                            </button>
                                            <button type="button" wire:click="delete({{ $card->id }})" wire:confirm="{{ __('Delete this loyalty card?') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color); background-color: rgba(var(--theme-danger-color-rgb), .04);" title="{{ __('Delete') }}">
                                                <i class="fa-light fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    <p class="text-sm" style="color: var(--theme-muted-text-color);">
                        {{ __('Showing') }}
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($cards->firstItem()) }}</span>
                        -
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($cards->lastItem()) }}</span>
                        {{ __('of') }}
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($cards->total()) }}</span>
                        {{ __('loyalty cards') }}
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="previousPage('cardsPage')" @disabled($cards->onFirstPage()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"><i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}</button>
                        <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">{{ __('Page') }} {{ format_number_locale($cards->currentPage()) }} / {{ format_number_locale($cards->lastPage()) }}</span>
                        <button type="button" wire:click="nextPage('cardsPage')" @disabled(! $cards->hasMorePages()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">{{ __('Next') }}<i class="fa-light fa-arrow-right"></i></button>
                    </div>
                </div>
            @else
                <div class="p-8"><x-ui.empty icon="fa-light fa-stamp" :title="__('No loyalty cards yet')" :description="__('Create a QR stamp card for repeat visits and automatic rewards.')" /></div>
            @endif
        </div>

        <div x-cloak x-show="loyaltyTab === 'customers'">
            @if ($loyaltyCustomers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Customer') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Card') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Progress') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Completed') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Last stamp') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            @foreach ($loyaltyCustomers as $progress)
                                <tr>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $progress->customer?->name }}</p>
                                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $progress->customer?->phone ?: $progress->customer?->email }}</p>
                                    </td>
                                    <td class="px-5 py-4">{{ $progress->card?->name }}</td>
                                    <td class="px-5 py-4">{{ $progress->stamps_count }} / {{ $progress->card?->required_stamps }}</td>
                                    <td class="px-5 py-4">{{ format_number_locale($progress->completed_count) }}</td>
                                    <td class="px-5 py-4">{{ $progress->last_stamp_at?->format('M d, Y') ?: __('Never') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    <p class="text-sm" style="color: var(--theme-muted-text-color);">
                        {{ __('Showing') }}
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($loyaltyCustomers->firstItem()) }}</span>
                        -
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($loyaltyCustomers->lastItem()) }}</span>
                        {{ __('of') }}
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($loyaltyCustomers->total()) }}</span>
                        {{ __('loyalty customers') }}
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="previousPage('customersPage')" @disabled($loyaltyCustomers->onFirstPage()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"><i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}</button>
                        <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">{{ __('Page') }} {{ format_number_locale($loyaltyCustomers->currentPage()) }} / {{ format_number_locale($loyaltyCustomers->lastPage()) }}</span>
                        <button type="button" wire:click="nextPage('customersPage')" @disabled(! $loyaltyCustomers->hasMorePages()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">{{ __('Next') }}<i class="fa-light fa-arrow-right"></i></button>
                    </div>
                </div>
            @else
                <div class="p-8"><x-ui.empty icon="fa-light fa-users" :title="__('No loyalty customers yet')" :description="__('Customers appear here after they scan a loyalty card and collect stamps.')" /></div>
            @endif
        </div>

        <div x-cloak x-show="loyaltyTab === 'stamps'">
            @if ($stamps->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Customer') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Card') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Business') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Source') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Stamped at') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            @foreach ($stamps as $stamp)
                                <tr>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $stamp->customer?->name }}</p>
                                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $stamp->customer?->phone ?: $stamp->customer?->email }}</p>
                                    </td>
                                    <td class="px-5 py-4">{{ $stamp->card?->name }}</td>
                                    <td class="px-5 py-4">{{ $stamp->card?->business?->name ?: __('Business removed') }}</td>
                                    <td class="px-5 py-4"><x-ui.badge variant="neutral">{{ str($stamp->source)->headline() }}</x-ui.badge></td>
                                    <td class="px-5 py-4">{{ $stamp->created_at?->format('M d, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    <p class="text-sm" style="color: var(--theme-muted-text-color);">
                        {{ __('Showing') }}
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($stamps->firstItem()) }}</span>
                        -
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($stamps->lastItem()) }}</span>
                        {{ __('of') }}
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($stamps->total()) }}</span>
                        {{ __('stamps') }}
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="previousPage('stampsPage')" @disabled($stamps->onFirstPage()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"><i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}</button>
                        <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">{{ __('Page') }} {{ format_number_locale($stamps->currentPage()) }} / {{ format_number_locale($stamps->lastPage()) }}</span>
                        <button type="button" wire:click="nextPage('stampsPage')" @disabled(! $stamps->hasMorePages()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">{{ __('Next') }}<i class="fa-light fa-arrow-right"></i></button>
                    </div>
                </div>
            @else
                <div class="p-8"><x-ui.empty icon="fa-light fa-stamp" :title="__('No stamps yet')" :description="__('Stamp history appears here after customers scan loyalty cards.')" /></div>
            @endif
        </div>

        <div x-cloak x-show="loyaltyTab === 'referralCampaigns'">
            <div class="flex justify-end border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                <x-ui.button type="button" size="sm" x-on:click="referralDialogOpen = true; $wire.createReferralCampaign()"><i class="fa-light fa-plus"></i>{{ __('New referral campaign') }}</x-ui.button>
            </div>
            @if ($referralCampaigns->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Campaign') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Business') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Rule') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Reward') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Activity') }}</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            @foreach ($referralCampaigns as $campaign)
                                <tr>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $campaign->name }}</p>
                                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $campaign->slug }}</p>
                                    </td>
                                    <td class="px-5 py-4">{{ $campaign->business?->name ?: __('Business removed') }}</td>
                                    <td class="px-5 py-4">{{ __(':count referrals', ['count' => $campaign->required_referrals]) }} · {{ str($campaign->target_action)->headline() }}</td>
                                    <td class="px-5 py-4">{{ $campaign->reward_title }}</td>
                                    <td class="px-5 py-4">
                                        <p>{{ __(':count links', ['count' => $campaign->links_count]) }}</p>
                                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __(':count conversions', ['count' => $campaign->referrals_count]) }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ $campaign->publicUrl() }}" target="_blank" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('Open') }}"><i class="fa-light fa-arrow-up-right"></i></a>
                                            <x-ui.dropdown-menu align="right" width="auto">
                                                <x-slot:trigger>
                                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('QR code') }}"><i class="fa-light fa-qrcode"></i></button>
                                                </x-slot:trigger>
                                                <x-ui.dropdown-menu-item icon="fa-light fa-copy" onclick="navigator.clipboard && navigator.clipboard.writeText(this.dataset.copy || '')" data-copy="{{ e($campaign->publicUrl()) }}">{{ __('Copy link') }}</x-ui.dropdown-menu-item>
                                                <x-ui.dropdown-menu-item :href="route('referral-campaigns.png', ['campaign' => $campaign->slug])" icon="fa-light fa-file-image">{{ __('Download PNG') }}</x-ui.dropdown-menu-item>
                                                <x-ui.dropdown-menu-item :href="route('referral-campaigns.svg', ['campaign' => $campaign->slug])" icon="fa-light fa-code">{{ __('Download SVG') }}</x-ui.dropdown-menu-item>
                                            </x-ui.dropdown-menu>
                                            <button type="button" x-on:click="referralDialogOpen = true; $wire.editReferralCampaign({{ $campaign->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-accent-rgb), .30); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .06);" title="{{ __('Edit') }}"><i class="fa-light fa-pen"></i></button>
                                            <button type="button" wire:click="toggleReferralCampaignStatus({{ $campaign->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ $campaign->status === 'active' ? __('Pause') : __('Activate') }}"><i class="fa-light {{ $campaign->status === 'active' ? 'fa-pause' : 'fa-play' }}"></i></button>
                                            <button type="button" wire:click="deleteReferralCampaign({{ $campaign->id }})" wire:confirm="{{ __('Delete this referral campaign?') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color); background-color: rgba(var(--theme-danger-color-rgb), .04);" title="{{ __('Delete') }}"><i class="fa-light fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Showing') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($referralCampaigns->firstItem()) }}</span> - <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($referralCampaigns->lastItem()) }}</span> {{ __('of') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($referralCampaigns->total()) }}</span> {{ __('referral campaigns') }}</p>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="previousPage('referralCampaignsPage')" @disabled($referralCampaigns->onFirstPage()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"><i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}</button>
                        <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">{{ __('Page') }} {{ format_number_locale($referralCampaigns->currentPage()) }} / {{ format_number_locale($referralCampaigns->lastPage()) }}</span>
                        <button type="button" wire:click="nextPage('referralCampaignsPage')" @disabled(! $referralCampaigns->hasMorePages()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">{{ __('Next') }}<i class="fa-light fa-arrow-right"></i></button>
                    </div>
                </div>
            @else
                <div class="p-8"><x-ui.empty icon="fa-light fa-share-nodes" :title="__('No referral campaigns yet')" :description="__('Create an invite friend campaign so customers can share links and earn rewards.')" /></div>
            @endif
        </div>

        <div x-cloak x-show="loyaltyTab === 'referrals'">
            @if ($referrals->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Referrer') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Friend') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Campaign') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Target') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Converted') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            @foreach ($referrals as $referral)
                                <tr>
                                    <td class="px-5 py-4">{{ $referral->referrer?->name }}</td>
                                    <td class="px-5 py-4">{{ $referral->referred?->name }}</td>
                                    <td class="px-5 py-4">{{ $referral->campaign?->name }}</td>
                                    <td class="px-5 py-4"><x-ui.badge variant="neutral">{{ str($referral->target_action)->headline() }}</x-ui.badge></td>
                                    <td class="px-5 py-4">{{ $referral->converted_at?->format('M d, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Showing') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($referrals->firstItem()) }}</span> - <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($referrals->lastItem()) }}</span> {{ __('of') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($referrals->total()) }}</span> {{ __('referrals') }}</p>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="previousPage('referralsPage')" @disabled($referrals->onFirstPage()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"><i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}</button>
                        <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">{{ __('Page') }} {{ format_number_locale($referrals->currentPage()) }} / {{ format_number_locale($referrals->lastPage()) }}</span>
                        <button type="button" wire:click="nextPage('referralsPage')" @disabled(! $referrals->hasMorePages()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">{{ __('Next') }}<i class="fa-light fa-arrow-right"></i></button>
                    </div>
                </div>
            @else
                <div class="p-8"><x-ui.empty icon="fa-light fa-share-nodes" :title="__('No referrals yet')" :description="__('Referral conversions appear here after friends submit invite links.')" /></div>
            @endif
        </div>

        <div x-cloak x-show="loyaltyTab === 'rewards'">
            @if ($rewards->count() > 0 || $referralRewards->count() > 0)
                @if ($rewards->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Customer') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Reward') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Code') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Expires') }}</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            @foreach ($rewards as $reward)
                                <tr>
                                    <td class="px-5 py-4">{{ $reward->customer?->name }}</td>
                                    <td class="px-5 py-4">{{ $reward->card?->reward_title }}</td>
                                    <td class="px-5 py-4"><span class="rounded-lg border px-2.5 py-1.5 font-semibold tracking-[0.08em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color);">{{ $reward->code }}</span></td>
                                    <td class="px-5 py-4"><x-ui.badge :variant="$reward->status === 'used' ? 'success' : 'warning'">{{ str($reward->status)->headline() }}</x-ui.badge></td>
                                    <td class="px-5 py-4">{{ $reward->expires_at?->format('M d, Y') ?: __('No expiry') }}</td>
                                    <td class="px-5 py-4 text-right">
                                        @if ($reward->status === 'used')
                                            <button type="button" wire:click="restoreReward({{ $reward->id }})" class="rounded-lg border px-3 py-2 text-xs font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color);">{{ __('Undo') }}</button>
                                        @else
                                            <button type="button" wire:click="markRewardUsed({{ $reward->id }})" class="rounded-lg border px-3 py-2 text-xs font-semibold" style="border-color: rgba(var(--theme-success-color-rgb), .28); color: var(--theme-success-color);">{{ __('Mark used') }}</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    <p class="text-sm" style="color: var(--theme-muted-text-color);">
                        {{ __('Showing') }}
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($rewards->firstItem()) }}</span>
                        -
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($rewards->lastItem()) }}</span>
                        {{ __('of') }}
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($rewards->total()) }}</span>
                        {{ __('rewards') }}
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="previousPage('rewardsPage')" @disabled($rewards->onFirstPage()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"><i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}</button>
                        <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">{{ __('Page') }} {{ format_number_locale($rewards->currentPage()) }} / {{ format_number_locale($rewards->lastPage()) }}</span>
                        <button type="button" wire:click="nextPage('rewardsPage')" @disabled(! $rewards->hasMorePages()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">{{ __('Next') }}<i class="fa-light fa-arrow-right"></i></button>
                    </div>
                </div>
                @endif
                @if ($referralRewards->count() > 0)
                    <div class="border-t px-5 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Referral rewards') }}</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                                <tr>
                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Customer') }}</th>
                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Campaign') }}</th>
                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Reward') }}</th>
                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Code') }}</th>
                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Expires') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                @foreach ($referralRewards as $reward)
                                    <tr>
                                        <td class="px-5 py-4">{{ $reward->customer?->name }}</td>
                                        <td class="px-5 py-4">{{ $reward->campaign?->name }}</td>
                                        <td class="px-5 py-4">{{ $reward->campaign?->reward_title }}</td>
                                        <td class="px-5 py-4"><span class="rounded-lg border px-2.5 py-1.5 font-semibold tracking-[0.08em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color);">{{ $reward->code }}</span></td>
                                        <td class="px-5 py-4"><x-ui.badge :variant="$reward->status === 'used' ? 'success' : 'warning'">{{ str($reward->status)->headline() }}</x-ui.badge></td>
                                        <td class="px-5 py-4">{{ $reward->expires_at?->format('M d, Y') ?: __('No expiry') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @else
                <div class="p-8"><x-ui.empty icon="fa-light fa-gift" :title="__('No rewards yet')" :description="__('Rewards appear automatically when customers complete a stamp card or referral campaign.')" /></div>
            @endif
        </div>
    </section>

    <template x-teleport="body">
        <div x-cloak x-show="loyaltyDialogOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="loyaltyDialogOpen = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="loyaltyDialogOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <form wire:submit="save" x-show="loyaltyDialogOpen" x-transition.opacity.scale.95 class="relative w-full max-w-5xl overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.72); background-color: var(--theme-surface-overlay);">
                    <div class="flex items-start justify-between gap-4 border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <div>
                            <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $editingId ? __('Edit loyalty stamp card') : __('Create loyalty stamp card') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Customers scan a QR code, collect stamps, and unlock a reward.') }}</p>
                        </div>
                        <button type="button" x-on:click="loyaltyDialogOpen = false" class="flex h-10 w-10 items-center justify-center rounded-xl" style="color: var(--theme-muted-text-color);"><i class="fa-light fa-xmark"></i></button>
                    </div>
                    <div class="grid gap-4 p-5">
                        <x-ui.select wire:model="business_id" name="business_id" :label="__('Business')" :error="$errors->first('business_id')">
                            <option value="">{{ __('Select business') }}</option>
                            @foreach ($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.input wire:model="name" name="name" :label="__('Card name')" :placeholder="__('Coffee Stamp Card')" :error="$errors->first('name')" />
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input wire:model="required_stamps" name="required_stamps" type="number" :label="__('Required stamps')" :error="$errors->first('required_stamps')" />
                            <x-ui.select wire:model="stamp_method" name="stamp_method" :label="__('Stamp method')" :error="$errors->first('stamp_method')">
                                <option value="qr_scan">{{ __('QR scan') }}</option>
                                <option value="staff_approval">{{ __('Staff approval') }}</option>
                            </x-ui.select>
                        </div>
                        <x-ui.select wire:model="customer_identifier" name="customer_identifier" :label="__('Customer identifier')" :error="$errors->first('customer_identifier')">
                            <option value="phone">{{ __('Phone') }}</option>
                            <option value="email">{{ __('Email') }}</option>
                            <option value="phone_or_email">{{ __('Phone or email') }}</option>
                        </x-ui.select>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input wire:model="reward_title" name="reward_title" :label="__('Reward title')" :placeholder="__('Free drink reward')" :error="$errors->first('reward_title')" />
                            <x-ui.select wire:model="reward_type" name="reward_type" :label="__('Reward type')" :error="$errors->first('reward_type')">
                                <option value="free_item">{{ __('Free item') }}</option>
                                <option value="coupon">{{ __('Coupon') }}</option>
                                <option value="discount">{{ __('Discount') }}</option>
                                <option value="custom">{{ __('Custom') }}</option>
                            </x-ui.select>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input wire:model="reward_value" name="reward_value" :label="__('Reward value')" :placeholder="__('Free drink')" :error="$errors->first('reward_value')" />
                            <x-ui.input wire:model="expiry_days" name="expiry_days" type="number" :label="__('Reward expiry days')" :placeholder="__('30')" :error="$errors->first('expiry_days')" />
                        </div>
                        <div class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Anti-abuse rules') }}</p>
                            <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Prevent the same customer from repeatedly collecting stamps from the public QR page.') }}</p>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <x-ui.input wire:model="max_stamps_per_day" name="max_stamps_per_day" type="number" :label="__('Max stamps per customer / day')" :error="$errors->first('max_stamps_per_day')" />
                                <x-ui.input wire:model="stamp_cooldown_minutes" name="stamp_cooldown_minutes" type="number" :label="__('Cooldown minutes')" :help="__('Use 1440 for one stamp per day. Use 0 to disable cooldown.')" :error="$errors->first('stamp_cooldown_minutes')" />
                            </div>
                        </div>
                        @include('applandingpages::partials.growth-tool-page-design', [
                            'type' => 'loyalty',
                            'templates' => $loyaltyTemplates,
                        ])
                        <x-ui.select wire:model="status" name="status" :label="__('Status')" :error="$errors->first('status')">
                            <option value="active">{{ __('Active') }}</option>
                            <option value="draft">{{ __('Draft') }}</option>
                        </x-ui.select>
                    </div>
                    <div class="flex justify-end gap-3 border-t px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <x-ui.button type="button" variant="outline" x-on:click="loyaltyDialogOpen = false">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="submit"><i class="fa-light fa-floppy-disk"></i>{{ $editingId ? __('Save changes') : __('Create card') }}</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-cloak x-show="referralDialogOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="referralDialogOpen = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="referralDialogOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <form wire:submit="saveReferralCampaign" x-show="referralDialogOpen" x-transition.opacity.scale.95 class="relative w-full max-w-2xl overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.72); background-color: var(--theme-surface-overlay);">
                    <div class="flex items-start justify-between gap-4 border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <div>
                            <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $editingReferralCampaignId ? __('Edit referral campaign') : __('Create referral campaign') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Customers get invite links, friends submit details, and rewards unlock automatically.') }}</p>
                        </div>
                        <button type="button" x-on:click="referralDialogOpen = false" class="flex h-10 w-10 items-center justify-center rounded-xl" style="color: var(--theme-muted-text-color);"><i class="fa-light fa-xmark"></i></button>
                    </div>
                    <div class="grid gap-4 p-5">
                        <x-ui.select wire:model="referral_business_id" name="referral_business_id" :label="__('Business')" :error="$errors->first('referral_business_id')">
                            <option value="">{{ __('Select business') }}</option>
                            @foreach ($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.input wire:model="referral_name" name="referral_name" :label="__('Campaign name')" :placeholder="__('Invite a Friend')" :error="$errors->first('referral_name')" />
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input wire:model="referral_reward_title" name="referral_reward_title" :label="__('Reward title')" :placeholder="__('20% off next visit')" :error="$errors->first('referral_reward_title')" />
                            <x-ui.select wire:model="referral_reward_type" name="referral_reward_type" :label="__('Reward type')" :error="$errors->first('referral_reward_type')">
                                <option value="coupon">{{ __('Coupon') }}</option>
                                <option value="discount">{{ __('Discount') }}</option>
                                <option value="free_item">{{ __('Free item') }}</option>
                                <option value="custom">{{ __('Custom') }}</option>
                            </x-ui.select>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input wire:model="referral_reward_value" name="referral_reward_value" :label="__('Reward value')" :placeholder="__('20% off')" :error="$errors->first('referral_reward_value')" />
                            <x-ui.input wire:model="referral_required_referrals" name="referral_required_referrals" type="number" :label="__('Required referrals')" :error="$errors->first('referral_required_referrals')" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.select wire:model="referral_target_action" name="referral_target_action" :label="__('Target action')" :error="$errors->first('referral_target_action')">
                                <option value="lead">{{ __('Lead submitted') }}</option>
                                <option value="booking">{{ __('Booking submitted') }}</option>
                                <option value="coupon_claim">{{ __('Coupon claimed') }}</option>
                            </x-ui.select>
                            <x-ui.input wire:model="referral_expiry_days" name="referral_expiry_days" type="number" :label="__('Reward expiry days')" :placeholder="__('30')" :error="$errors->first('referral_expiry_days')" />
                        </div>
                        <x-ui.select wire:model="referral_status" name="referral_status" :label="__('Status')" :error="$errors->first('referral_status')">
                            <option value="active">{{ __('Active') }}</option>
                            <option value="draft">{{ __('Draft') }}</option>
                        </x-ui.select>
                    </div>
                    <div class="flex justify-end gap-3 border-t px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <x-ui.button type="button" variant="outline" x-on:click="referralDialogOpen = false">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="submit"><i class="fa-light fa-floppy-disk"></i>{{ $editingReferralCampaignId ? __('Save changes') : __('Create campaign') }}</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
