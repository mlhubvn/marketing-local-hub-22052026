<div
    class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6"
    x-data="{ formDialogOpen: false, leadTab: 'forms' }"
    x-on:lead-form-saved.window="formDialogOpen = false"
>
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif
    @include('applandingpages::partials.growth-tool-created-actions')

    @php
        $leadFollowUpContentUrl = route('portal.ai-content', array_filter([
            'business_id' => $business_id ?: (string) optional($businesses->first())->id,
            'type' => 'lead_follow_up',
            'goal' => 'Follow up with new leads and invite the next step.',
            'offer' => 'Free consultation',
            'target_customer' => 'New leads',
            'details' => 'Write a helpful follow-up message after a lead form submission.',
            'source_type' => 'lead_form',
        ], fn ($value) => filled($value)));
        $salesMessageContentUrl = route('portal.ai-content', array_filter([
            'business_id' => $business_id ?: (string) optional($businesses->first())->id,
            'type' => 'whatsapp_sms',
            'goal' => 'Send a short sales message to a qualified local lead.',
            'offer' => 'Free consultation',
            'target_customer' => 'Qualified leads',
            'details' => 'Write a concise sales follow-up for WhatsApp, SMS, or email.',
            'source_type' => 'lead_form',
        ], fn ($value) => filled($value)));
    @endphp

    <section class="overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 32%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-6 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-md border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-clipboard-list-check"></i>{{ __('Growth Tools') }}
                </div>
                <h1 class="mt-4 max-w-3xl text-[2.15rem] font-semibold leading-[1.05] tracking-[-0.055em] sm:text-[2.85rem]" style="color: var(--theme-header-text-color);">{{ __('Lead Forms') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ __('Capture quote requests, callbacks, waitlists, and consultation leads from campaign pages, QR placements, and shared links.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button type="button" size="lg" x-on:click="formDialogOpen = true">
                        <i class="fa-light fa-plus"></i>{{ __('Create lead form') }}
                    </x-ui.button>
                    <x-ui.button href="{{ $leadFollowUpContentUrl }}" wire:navigate variant="outline" size="lg">
                        <i class="fa-light fa-user-plus"></i>{{ __('Generate follow-up') }}
                    </x-ui.button>
                    <x-ui.button href="{{ $salesMessageContentUrl }}" wire:navigate variant="outline" size="lg">
                        <i class="fa-light fa-message-dollar"></i>{{ __('Generate sales message') }}
                    </x-ui.button>
                    @if ($latestCampaign)
                        <x-ui.button href="{{ $latestCampaign->publicUrl() }}" target="_blank" variant="outline" size="lg">
                            <i class="fa-light fa-arrow-up-right"></i>{{ __('Open latest form') }}
                        </x-ui.button>
                    @endif
                </div>
            </div>

            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Lead capture health') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Published forms and conversion signal') }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-chart-simple"></i>
                    </span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($stats['published']) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Published') }}</p>
                    </div>
                    <div class="border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($stats['all_leads']) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Captured leads') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['label' => __('Lead Forms'), 'value' => $stats['forms'], 'description' => __('Capture pages'), 'icon' => 'fa-light fa-clipboard-list-check'],
            ['label' => __('Published'), 'value' => $stats['published'], 'description' => __('Live forms'), 'icon' => 'fa-light fa-circle-check'],
            ['label' => __('Visits'), 'value' => $stats['visits'], 'description' => __('QR scans & link visits'), 'icon' => 'fa-light fa-chart-line'],
            ['label' => __('Leads'), 'value' => $stats['all_leads'], 'description' => __('All captured contacts'), 'icon' => 'fa-light fa-address-book'],
            ['label' => __('Lead Rate'), 'value' => $stats['lead_rate'].'%', 'description' => __('Leads / visits'), 'icon' => 'fa-light fa-percent'],
        ] as $metric)
            <article class="relative overflow-hidden rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background: linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.07), transparent 44%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <span class="absolute inset-x-0 top-0 h-1" style="background-color: var(--theme-accent);"></span>
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[2rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ is_numeric($metric['value']) ? number_format($metric['value']) : $metric['value'] }}</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $metric['description'] }}</p>
                    </div>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="{{ $metric['icon'] }}"></i>
                    </span>
                </div>
            </article>
        @endforeach
    </section>

    <section class="overflow-visible rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="flex flex-col gap-4 border-b px-5 py-4 xl:flex-row xl:items-end xl:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <div>
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Lead form workspace') }}</p>
                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Search, publish, share, and inspect lead capture forms.') }}</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-[minmax(0,17rem)_minmax(0,15rem)]">
                <x-ui.input wire:model.live.debounce.300ms="search" name="search" :placeholder="__('Search form or business...')" icon="fa-light fa-magnifying-glass" />
                <x-ui.combobox
                    model="businessFilter"
                    name="business_filter"
                    :selected="$businessFilter"
                    :options="collect([['value' => 'all', 'label' => __('All businesses'), 'meta' => __('Every lead form'), 'icon' => 'fa-store']])->merge($businesses->map(fn ($business) => ['value' => (string) $business->id, 'label' => $business->name, 'meta' => __('Business'), 'icon' => 'fa-store']))->values()->all()"
                    :placeholder="__('All businesses')"
                    :search-placeholder="__('Search business')"
                    icon="fa-light fa-store"
                />
            </div>
        </div>

        <div class="flex gap-2 border-b px-5 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <button type="button" x-on:click="leadTab = 'forms'" class="rounded-[0.8rem] px-4 py-2 text-sm font-semibold transition" x-bind:style="leadTab === 'forms' ? 'background-color: rgba(var(--theme-accent-rgb), .14); color: var(--theme-accent); border: 1px solid rgba(var(--theme-accent-rgb), .28);' : 'color: var(--theme-muted-text-color); border: 1px solid transparent;'">
                {{ __('Forms') }}
            </button>
            <button type="button" x-on:click="leadTab = 'leads'" class="rounded-[0.8rem] px-4 py-2 text-sm font-semibold transition" x-bind:style="leadTab === 'leads' ? 'background-color: rgba(var(--theme-accent-rgb), .14); color: var(--theme-accent); border: 1px solid rgba(var(--theme-accent-rgb), .28);' : 'color: var(--theme-muted-text-color); border: 1px solid transparent;'">
                {{ __('Leads') }}
                <span class="ml-2 rounded-full px-2 py-0.5 text-xs" style="background-color: rgba(var(--theme-accent-rgb), .10);">{{ number_format($filteredLeadCount) }}</span>
            </button>
        </div>

        <div x-show="leadTab === 'forms'">
            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Lead forms') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Published capture pages and QR entry points.') }}</p>
                </div>
                <x-ui.select wire:model.live="formsPerPage" name="lead_forms_per_page" class="w-full sm:w-36">
                    <option value="10">{{ __('10 / page') }}</option>
                    <option value="25">{{ __('25 / page') }}</option>
                    <option value="50">{{ __('50 / page') }}</option>
                </x-ui.select>
            </div>

            @if ($campaigns->count() > 0)
                <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead style="color: var(--theme-muted-text-color);">
                                <tr>
                                    <th class="px-3 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Form') }}</th>
                                    <th class="px-3 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Business') }}</th>
                                    <th class="px-3 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Visits') }}</th>
                                    <th class="px-3 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                @foreach ($campaigns as $campaign)
                                    @php
                                        $campaignLeadContentUrl = route('portal.ai-content', array_filter([
                                            'business_id' => $campaign->business_id,
                                            'type' => 'lead_follow_up',
                                            'goal' => 'Follow up with new leads and invite the next step.',
                                            'offer' => 'Free consultation',
                                            'target_customer' => 'New leads',
                                            'details' => trim(implode("\n", array_filter([
                                                'Lead form: '.$campaign->name,
                                                'Headline: '.data_get($campaign->settings, 'headline'),
                                            ]))),
                                            'source_type' => 'lead_form',
                                            'source_id' => $campaign->id,
                                            'campaign_id' => $campaign->id,
                                        ], fn ($value) => filled($value)));
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-4">
                                            <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $campaign->name }}</p>
                                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ data_get($campaign->settings, 'headline', __('Request a callback')) }}</p>
                                        </td>
                                        <td class="px-3 py-4" style="color: var(--theme-muted-text-color);">{{ $campaign->business?->name ?: __('Business removed') }}</td>
                                        <td class="px-3 py-4 font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($campaign->scans_count) }}</td>
                                        <td class="px-3 py-4">
                                            <x-ui.badge :variant="$campaign->published_at ? 'success' : 'neutral'">{{ $campaign->published_at ? __('Live') : __('Paused') }}</x-ui.badge>
                                        </td>
                                        <td class="px-3 py-4 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ $campaign->publicUrl() }}" target="_blank" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('Open') }}">
                                                    <i class="fa-light fa-arrow-up-right"></i>
                                                </a>
                                                @if ($campaign->landingPage)
                                                    <a href="{{ route('portal.landing-pages', ['edit' => $campaign->landingPage->id, 'return' => request()->fullUrl()]) }}" wire:navigate class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-accent-rgb), .30); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .06);" title="{{ __('Edit design') }}">
                                                        <i class="fa-light fa-palette"></i>
                                                    </a>
                                                @endif
                                                <x-ui.dropdown-menu align="right" width="auto">
                                                    <x-slot:trigger>
                                                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('QR code') }}">
                                                            <i class="fa-light fa-qrcode"></i>
                                                        </button>
                                                    </x-slot:trigger>
                                                    <x-ui.dropdown-menu-item
                                                        icon="fa-light fa-copy"
                                                        onclick="navigator.clipboard && navigator.clipboard.writeText(this.dataset.copy || '')"
                                                        data-copy="{{ e($campaign->publicUrl()) }}"
                                                    >
                                                        {{ __('Copy QR link') }}
                                                    </x-ui.dropdown-menu-item>
                                                    <x-ui.dropdown-menu-item :href="route('qr-campaigns.png', ['campaign' => $campaign->slug])" icon="fa-light fa-file-image">
                                                        {{ __('Download PNG') }}
                                                    </x-ui.dropdown-menu-item>
                                                    <x-ui.dropdown-menu-item :href="route('qr-campaigns.svg', ['campaign' => $campaign->slug])" icon="fa-light fa-code">
                                                        {{ __('Download SVG') }}
                                                    </x-ui.dropdown-menu-item>
                                                    @if (\Illuminate\Support\Facades\Route::has('portal.qr-codes.analytics'))
                                                        <x-ui.dropdown-menu-item :href="route('portal.qr-codes.analytics', ['campaign' => $campaign->slug])" icon="fa-light fa-chart-line" wire:navigate>
                                                            {{ __('View Analytics') }}
                                                        </x-ui.dropdown-menu-item>
                                                    @endif
                                                </x-ui.dropdown-menu>
                                                <a href="{{ $campaignLeadContentUrl }}" wire:navigate class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-accent); background-color: var(--theme-surface-overlay);" title="{{ __('Generate content') }}">
                                                    <i class="fa-light fa-pen-nib"></i>
                                                </a>
                                                <button type="button" wire:click="togglePublish({{ $campaign->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ $campaign->published_at ? __('Pause') : __('Publish') }}">
                                                    <i class="fa-light {{ $campaign->published_at ? 'fa-pause' : 'fa-play' }}"></i>
                                                </button>
                                                <x-ui.dialog :title="__('Delete lead form')" :description="__('This removes the lead form, QR asset, scans, and submissions connected to it.')" width="sm" dismissible>
                                                    <x-slot:trigger>
                                                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color); background-color: rgba(var(--theme-danger-color-rgb), .05);" title="{{ __('Delete') }}">
                                                            <i class="fa-light fa-trash"></i>
                                                        </button>
                                                    </x-slot:trigger>
                                                    <x-slot:footer>
                                                        <div class="flex justify-end gap-3">
                                                            <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                                            <x-ui.button type="button" variant="danger" wire:click="delete({{ $campaign->id }})" x-on:click="open = false">{{ __('Delete') }}</x-ui.button>
                                                        </div>
                                                    </x-slot:footer>
                                                </x-ui.dialog>
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
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($campaigns->firstItem()) }}</span> -
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($campaigns->lastItem()) }}</span>
                        {{ __('of') }}
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($campaigns->total()) }}</span>
                        {{ __('lead forms') }}
                    </p>
                    <div class="flex items-center gap-2">
                        <x-ui.button type="button" variant="outline" wire:click="previousPage('formsPage')" :disabled="$campaigns->onFirstPage()">
                            <i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}
                        </x-ui.button>
                        <span class="rounded-[0.8rem] border px-4 py-2 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .10);">
                            {{ __('Page :page / :pages', ['page' => $campaigns->currentPage(), 'pages' => max(1, $campaigns->lastPage())]) }}
                        </span>
                        <x-ui.button type="button" variant="outline" wire:click="nextPage('formsPage')" :disabled="! $campaigns->hasMorePages()">
                            {{ __('Next') }}<i class="fa-light fa-arrow-right"></i>
                        </x-ui.button>
                    </div>
                </div>
            @else
                <div class="m-5 rounded-[1rem] border px-6 py-12 text-center" style="border-color: rgba(var(--theme-border-color-rgb), .58); background: linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.06), transparent 56%);">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: var(--theme-surface-overlay); color: var(--theme-accent);">
                            <i class="fa-light fa-clipboard-list-check"></i>
                        </span>
                        <h3 class="mt-4 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('No lead forms yet') }}</h3>
                        <p class="mx-auto mt-2 max-w-xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Create a lead form to capture callbacks, quotes, waitlists, or consultation requests from local campaigns.') }}</p>
                        <div class="mt-5">
                            <x-ui.button type="button" size="sm" x-on:click="formDialogOpen = true">
                                <i class="fa-light fa-plus"></i>{{ __('Create lead form') }}
                            </x-ui.button>
                        </div>
                </div>
            @endif
        </div>

        <div x-cloak x-show="leadTab === 'leads'">
            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Lead inbox') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Captured submissions from lead capture forms.') }}</p>
                </div>
                <x-ui.select wire:model.live="leadsPerPage" name="lead_submissions_per_page" class="w-full sm:w-36">
                    <option value="10">{{ __('10 / page') }}</option>
                    <option value="25">{{ __('25 / page') }}</option>
                    <option value="50">{{ __('50 / page') }}</option>
                </x-ui.select>
            </div>

            <div class="border-t px-5 py-5" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recent leads') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Name, contact, source form, and submit time.') }}</p>
                    </div>
                    <x-ui.badge variant="neutral">{{ number_format($filteredLeadCount) }}</x-ui.badge>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($leads as $lead)
                        <article class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $lead->name }}</p>
                                    <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $lead->campaign?->name ?: __('Lead form') }}</p>
                                </div>
                                <span class="text-xs" style="color: var(--theme-muted-text-color);">{{ $lead->created_at?->diffForHumans() }}</span>
                            </div>
                            <div class="mt-3 space-y-1 text-xs" style="color: var(--theme-muted-text-color);">
                                <p><i class="fa-light fa-phone mr-2"></i>{{ $lead->phone ?: __('No phone') }}</p>
                                <p><i class="fa-light fa-envelope mr-2"></i>{{ $lead->email ?: __('No email') }}</p>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-[1rem] border px-4 py-10 text-center" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                                <i class="fa-light fa-address-card"></i>
                            </span>
                            <p class="mt-3 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No leads yet') }}</p>
                            <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Submissions will appear here after visitors send a form.') }}</p>
                        </div>
                    @endforelse
                </div>

                @if ($leads->count() > 0)
                    <div class="mt-5 flex flex-col gap-3 border-t pt-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                        <p class="text-sm" style="color: var(--theme-muted-text-color);">
                            {{ __('Showing') }}
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($leads->firstItem()) }}</span> -
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($leads->lastItem()) }}</span>
                            {{ __('of') }}
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($leads->total()) }}</span>
                            {{ __('leads') }}
                        </p>
                        <div class="flex items-center gap-2">
                            <x-ui.button type="button" variant="outline" wire:click="previousPage('leadsPage')" :disabled="$leads->onFirstPage()">
                                <i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}
                            </x-ui.button>
                            <span class="rounded-[0.8rem] border px-4 py-2 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .10);">
                                {{ __('Page :page / :pages', ['page' => $leads->currentPage(), 'pages' => max(1, $leads->lastPage())]) }}
                            </span>
                            <x-ui.button type="button" variant="outline" wire:click="nextPage('leadsPage')" :disabled="! $leads->hasMorePages()">
                                {{ __('Next') }}<i class="fa-light fa-arrow-right"></i>
                            </x-ui.button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <template x-teleport="body">
        <div x-cloak x-show="formDialogOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="formDialogOpen = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="formDialogOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <form wire:submit="save" x-show="formDialogOpen" x-transition.opacity.scale.95 class="relative w-full max-w-xl overflow-hidden rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.72); background-color: var(--theme-surface-overlay);">
                    <div class="flex items-start justify-between gap-4 border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <div>
                            <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Create lead form') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Publish a simple public page that captures name, phone, email, and message.') }}</p>
                        </div>
                        <button type="button" x-on:click="formDialogOpen = false" class="flex h-10 w-10 items-center justify-center rounded-xl" style="color: var(--theme-muted-text-color);">
                            <i class="fa-light fa-xmark"></i>
                        </button>
                    </div>

                    <div class="grid gap-4 p-5">
                        <x-ui.combobox
                            :label="__('Business')"
                            model="business_id"
                            name="business_id"
                            :selected="$business_id"
                            :options="$businesses->map(fn ($business) => ['value' => (string) $business->id, 'label' => $business->name, 'meta' => __('Business profile'), 'icon' => 'fa-store'])->values()->all()"
                            :placeholder="__('Select business')"
                            :search-placeholder="__('Search business')"
                            :error="$errors->first('business_id')"
                            icon="fa-light fa-store"
                        />
                        <x-ui.input wire:model="name" name="name" :label="__('Form name')" :placeholder="__('Callback request, quote form, waitlist...')" :error="$errors->first('name')" />
                        <x-ui.input wire:model="headline" name="headline" :label="__('Public headline')" :placeholder="__('Request a callback')" :error="$errors->first('headline')" />
                        @include('applandingpages::partials.growth-tool-page-design', ['type' => 'lead', 'templates' => $leadTemplates])
                    </div>

                    <div class="border-t px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 70%, transparent);">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Captured fields') }}</p>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                            @foreach ([__('Name'), __('Phone'), __('Email'), __('Message')] as $field)
                                <div class="border px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb), .50); color: var(--theme-muted-text-color);">
                                    <i class="fa-light fa-check mr-2" style="color: var(--theme-accent);"></i>{{ $field }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <x-ui.button type="button" variant="outline" x-on:click="formDialogOpen = false">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="submit">
                            <span wire:loading.remove wire:target="save"><i class="fa-light fa-floppy-disk"></i>{{ __('Create lead form') }}</span>
                            <span wire:loading wire:target="save"><i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Creating...') }}</span>
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
