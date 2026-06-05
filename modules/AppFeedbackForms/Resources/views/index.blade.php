<div
    class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6"
    x-data="{ feedbackDialogOpen: false, feedbackTab: 'forms' }"
    x-on:feedback-form-saved.window="feedbackDialogOpen = false"
>
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif
    @include('applandingpages::partials.growth-tool-created-actions')

    @php
        $feedbackRequestContentUrl = route('portal.ai-content', array_filter([
            'business_id' => $business_id ?: (string) optional($businesses->first())->id,
            'type' => 'feedback_request',
            'goal' => 'Ask customers to share private feedback after service.',
            'target_customer' => 'Recent customers',
            'details' => 'Write a private feedback request message for a feedback form.',
            'source_type' => 'feedback_form',
        ], fn ($value) => filled($value)));
        $feedbackThankYouContentUrl = route('portal.ai-content', array_filter([
            'business_id' => $business_id ?: (string) optional($businesses->first())->id,
            'type' => 'thank_you',
            'goal' => 'Thank customers after they submit private feedback.',
            'target_customer' => 'Customers who submitted feedback',
            'details' => 'Write a concise thank you message after feedback submission.',
            'source_type' => 'feedback_form',
        ], fn ($value) => filled($value)));
    @endphp

    <section class="overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 32%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-6 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-md border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-message-lines"></i>{{ __('Growth Tools') }}
                </div>
                <h1 class="mt-4 max-w-3xl text-[2.15rem] font-semibold leading-[1.05] tracking-[-0.055em] sm:text-[2.85rem]" style="color: var(--theme-header-text-color);">{{ __('Feedback Forms') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ __('Collect private customer feedback, spot service issues early, and route follow-up before problems become public reviews.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-ui.button type="button" size="lg" x-on:click="feedbackDialogOpen = true">
                        <i class="fa-light fa-plus"></i>{{ __('Create feedback form') }}
                    </x-ui.button>
                    <x-ui.button href="{{ $feedbackRequestContentUrl }}" wire:navigate variant="outline" size="lg">
                        <i class="fa-light fa-pen-nib"></i>{{ __('Generate feedback request') }}
                    </x-ui.button>
                    <x-ui.button href="{{ $feedbackThankYouContentUrl }}" wire:navigate variant="outline" size="lg">
                        <i class="fa-light fa-message-heart"></i>{{ __('Generate thank you') }}
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
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Feedback health') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Private responses and ratings') }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-comments"></i>
                    </span>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-2">
                    @foreach ([
                        ['label' => __('Forms'), 'value' => $stats['forms']],
                        ['label' => __('Responses'), 'value' => $stats['responses']],
                        ['label' => __('Avg Rating'), 'value' => ($stats['avg_rating'] ? format_number_locale((float) $stats['avg_rating'], 1) : '0').'/5'],
                    ] as $item)
                        <div class="border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                            <p class="text-xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ is_numeric($item['value']) ? format_number_locale((float) $item['value']) : $item['value'] }}</p>
                            <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $item['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['label' => __('Feedback Forms'), 'value' => $stats['forms'], 'description' => __('Private response pages'), 'icon' => 'fa-light fa-message-lines'],
            ['label' => __('Published'), 'value' => $stats['published'], 'description' => __('Live forms'), 'icon' => 'fa-light fa-circle-check'],
            ['label' => __('Visits'), 'value' => $stats['visits'], 'description' => __('QR scans & link visits'), 'icon' => 'fa-light fa-chart-line'],
            ['label' => __('Responses'), 'value' => $stats['responses'], 'description' => __('Feedback submitted'), 'icon' => 'fa-light fa-inbox'],
            ['label' => __('Low-score'), 'value' => $stats['low_score'], 'description' => __('Needs attention'), 'icon' => 'fa-light fa-message-exclamation'],
        ] as $metric)
            <article class="relative overflow-hidden rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background: linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.07), transparent 44%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <span class="absolute inset-x-0 top-0 h-1" style="background-color: var(--theme-warning-color);"></span>
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[1.5rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($metric['value']) }}</p>
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
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Feedback workspace') }}</p>
                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Manage private feedback pages, public links, and response follow-up.') }}</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-[minmax(0,17rem)_minmax(0,15rem)]">
                <x-ui.input wire:model.live.debounce.300ms="search" name="search" :placeholder="__('Search form or business...')" icon="fa-light fa-magnifying-glass" />
                <x-ui.combobox
                    model="businessFilter"
                    name="business_filter"
                    :selected="$businessFilter"
                    :options="collect([['value' => 'all', 'label' => __('All businesses'), 'meta' => __('Every feedback form'), 'icon' => 'fa-store']])->merge($businesses->map(fn ($business) => ['value' => (string) $business->id, 'label' => $business->name, 'meta' => __('Business'), 'icon' => 'fa-store']))->values()->all()"
                    :placeholder="__('All businesses')"
                    :search-placeholder="__('Search business')"
                    icon="fa-light fa-store"
                />
            </div>
        </div>

        <div class="flex gap-2 border-b px-5 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <button type="button" x-on:click="feedbackTab = 'forms'" class="rounded-[0.8rem] px-4 py-2 text-sm font-semibold transition" x-bind:style="feedbackTab === 'forms' ? 'background-color: rgba(var(--theme-accent-rgb), .14); color: var(--theme-accent); border: 1px solid rgba(var(--theme-accent-rgb), .28);' : 'color: var(--theme-muted-text-color); border: 1px solid transparent;'">
                {{ __('Forms') }}
            </button>
            <button type="button" x-on:click="feedbackTab = 'responses'" class="rounded-[0.8rem] px-4 py-2 text-sm font-semibold transition" x-bind:style="feedbackTab === 'responses' ? 'background-color: rgba(var(--theme-accent-rgb), .14); color: var(--theme-accent); border: 1px solid rgba(var(--theme-accent-rgb), .28);' : 'color: var(--theme-muted-text-color); border: 1px solid transparent;'">
                {{ __('Responses') }}
                <span class="ml-2 rounded-full px-2 py-0.5 text-xs" style="background-color: rgba(var(--theme-accent-rgb), .10);">{{ format_number_locale($filteredResponseCount) }}</span>
            </button>
        </div>

        <div x-show="feedbackTab === 'forms'">
            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Feedback forms') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Private response pages and QR entry points.') }}</p>
                </div>
                <x-ui.select wire:model.live="formsPerPage" name="feedback_forms_per_page" class="w-full sm:w-36">
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
                                    <th class="px-3 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Rules') }}</th>
                                    <th class="px-3 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                @foreach ($campaigns as $campaign)
                                    @php
                                        $campaignFeedbackContentUrl = route('portal.ai-content', array_filter([
                                            'business_id' => $campaign->business_id,
                                            'type' => 'feedback_request',
                                            'goal' => 'Ask customers to share private feedback after service.',
                                            'target_customer' => 'Recent customers',
                                            'details' => trim(implode("\n", array_filter([
                                                'Feedback form: '.$campaign->name,
                                                'Headline: '.data_get($campaign->settings, 'headline'),
                                            ]))),
                                            'source_type' => 'feedback_form',
                                            'source_id' => $campaign->id,
                                            'campaign_id' => $campaign->id,
                                        ], fn ($value) => filled($value)));
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-4">
                                            <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $campaign->name }}</p>
                                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ data_get($campaign->settings, 'headline', __('Tell us about your experience')) }}</p>
                                        </td>
                                        <td class="px-3 py-4" style="color: var(--theme-muted-text-color);">{{ $campaign->business?->name ?: __('Business removed') }}</td>
                                        <td class="px-3 py-4 font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($campaign->scans_count) }}</td>
                                        <td class="px-3 py-4 text-xs" style="color: var(--theme-muted-text-color);">
                                            {{ data_get($campaign->settings, 'rating_required') ? __('Rating required') : __('Rating optional') }}
                                            <span class="mx-1">/</span>
                                            {{ data_get($campaign->settings, 'contact_required') ? __('Contact required') : __('Contact optional') }}
                                        </td>
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
                                                <a href="{{ $campaignFeedbackContentUrl }}" wire:navigate class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-accent); background-color: var(--theme-surface-overlay);" title="{{ __('Generate content') }}">
                                                    <i class="fa-light fa-pen-nib"></i>
                                                </a>
                                                <button type="button" wire:click="togglePublish({{ $campaign->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .7); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ $campaign->published_at ? __('Pause') : __('Publish') }}">
                                                    <i class="fa-light {{ $campaign->published_at ? 'fa-pause' : 'fa-play' }}"></i>
                                                </button>
                                                <x-ui.dialog :title="__('Delete feedback form')" :description="__('This removes the feedback form, QR asset, scans, and responses connected to it.')" width="sm" dismissible>
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
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($campaigns->firstItem()) }}</span> -
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($campaigns->lastItem()) }}</span>
                        {{ __('of') }}
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($campaigns->total()) }}</span>
                        {{ __('feedback forms') }}
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
                            <i class="fa-light fa-message-lines"></i>
                        </span>
                        <h3 class="mt-4 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('No feedback forms yet') }}</h3>
                        <p class="mx-auto mt-2 max-w-xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Create a private feedback page to capture service issues, product comments, and customer recovery notes.') }}</p>
                        <div class="mt-5">
                            <x-ui.button type="button" size="sm" x-on:click="feedbackDialogOpen = true">
                                <i class="fa-light fa-plus"></i>{{ __('Create feedback form') }}
                            </x-ui.button>
                        </div>
                    </div>
            @endif
        </div>

        <div x-cloak x-show="feedbackTab === 'responses'">
            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Response inbox') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Captured private feedback and follow-up status.') }}</p>
                </div>
                <x-ui.select wire:model.live="responsesPerPage" name="feedback_responses_per_page" class="w-full sm:w-36">
                    <option value="10">{{ __('10 / page') }}</option>
                    <option value="25">{{ __('25 / page') }}</option>
                    <option value="50">{{ __('50 / page') }}</option>
                </x-ui.select>
            </div>

            <div class="border-t px-5 py-5" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recent responses') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Rating, message, contact details, and resolution status.') }}</p>
                    </div>
                    <x-ui.badge variant="neutral">{{ format_number_locale($filteredResponseCount) }}</x-ui.badge>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($responses as $response)
                        <article class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $response->customer_name ?: __('Guest') }}</p>
                                    <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $response->campaign?->name ?: __('Feedback form') }}</p>
                                </div>
                                <div class="flex shrink-0 flex-col items-end gap-2">
                                    <x-ui.badge :variant="$response->rating && $response->rating <= 3 ? 'warning' : 'neutral'">{{ $response->rating ? $response->rating.'/5' : __('No rating') }}</x-ui.badge>
                                    <x-ui.badge :variant="$response->status === 'resolved' ? 'success' : 'neutral'">{{ str($response->status ?: 'new')->headline() }}</x-ui.badge>
                                </div>
                            </div>
                            <p class="mt-3 line-clamp-3 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $response->message }}</p>
                            <div class="mt-3 space-y-1 text-xs" style="color: var(--theme-muted-text-color);">
                                <p><i class="fa-light fa-phone mr-2"></i>{{ $response->customer_phone ?: __('No phone') }}</p>
                                <p><i class="fa-light fa-envelope mr-2"></i>{{ $response->customer_email ?: __('No email') }}</p>
                            </div>
                            <div class="mt-3">
                                @if ($response->status !== 'resolved')
                                    <x-ui.dialog :title="__('Mark response as resolved?')" :description="__('This will close the follow-up item. You can undo it later if the response still needs attention.')" width="sm" dismissible>
                                        <x-slot:trigger>
                                            <button type="button" class="inline-flex h-9 items-center gap-2 rounded-lg border px-3 text-xs font-semibold transition hover:bg-black/[0.03]" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color);">
                                                <i class="fa-light fa-check"></i>{{ __('Mark resolved') }}
                                            </button>
                                        </x-slot:trigger>

                                        <x-slot:footer>
                                            <div class="flex justify-end gap-3">
                                                <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                                <x-ui.button type="button" wire:click="markResolved({{ $response->id }})" x-on:click="open = false">
                                                    <i class="fa-light fa-check"></i>{{ __('Confirm') }}
                                                </x-ui.button>
                                            </div>
                                        </x-slot:footer>
                                    </x-ui.dialog>
                                @else
                                    <x-ui.dialog :title="__('Reopen this response?')" :description="__('This will move the response back to new so it appears as needing follow-up again.')" width="sm" dismissible>
                                        <x-slot:trigger>
                                            <button type="button" class="inline-flex h-9 items-center gap-2 rounded-lg border px-3 text-xs font-semibold transition hover:bg-black/[0.03]" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color);">
                                                <i class="fa-light fa-rotate-left"></i>{{ __('Undo') }}
                                            </button>
                                        </x-slot:trigger>

                                        <x-slot:footer>
                                            <div class="flex justify-end gap-3">
                                                <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                                <x-ui.button type="button" wire:click="undoResolved({{ $response->id }})" x-on:click="open = false">
                                                    <i class="fa-light fa-rotate-left"></i>{{ __('Confirm undo') }}
                                                </x-ui.button>
                                            </div>
                                        </x-slot:footer>
                                    </x-ui.dialog>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-[1rem] border px-4 py-10 text-center" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                                <i class="fa-light fa-inbox"></i>
                            </span>
                            <p class="mt-3 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No feedback yet') }}</p>
                            <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Responses will appear here after customers submit a form.') }}</p>
                        </div>
                    @endforelse
                </div>

                @if ($responses->count() > 0)
                    <div class="mt-5 flex flex-col gap-3 border-t pt-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                        <p class="text-sm" style="color: var(--theme-muted-text-color);">
                            {{ __('Showing') }}
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($responses->firstItem()) }}</span> -
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($responses->lastItem()) }}</span>
                            {{ __('of') }}
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($responses->total()) }}</span>
                            {{ __('responses') }}
                        </p>
                        <div class="flex items-center gap-2">
                            <x-ui.button type="button" variant="outline" wire:click="previousPage('responsesPage')" :disabled="$responses->onFirstPage()">
                                <i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}
                            </x-ui.button>
                            <span class="rounded-[0.8rem] border px-4 py-2 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .10);">
                                {{ __('Page :page / :pages', ['page' => $responses->currentPage(), 'pages' => max(1, $responses->lastPage())]) }}
                            </span>
                            <x-ui.button type="button" variant="outline" wire:click="nextPage('responsesPage')" :disabled="! $responses->hasMorePages()">
                                {{ __('Next') }}<i class="fa-light fa-arrow-right"></i>
                            </x-ui.button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <template x-teleport="body">
        <div x-cloak x-show="feedbackDialogOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="feedbackDialogOpen = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="feedbackDialogOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <form wire:submit="save" x-show="feedbackDialogOpen" x-transition.opacity.scale.95 class="relative w-full max-w-2xl overflow-hidden rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.72); background-color: var(--theme-surface-overlay);">
                    <div class="flex items-start justify-between gap-4 border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <div>
                            <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Create feedback form') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Build a private response page for post-visit feedback and issue recovery.') }}</p>
                        </div>
                        <button type="button" x-on:click="feedbackDialogOpen = false" class="flex h-10 w-10 items-center justify-center rounded-xl" style="color: var(--theme-muted-text-color);">
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
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input wire:model="name" name="name" :label="__('Form name')" :placeholder="__('Post-visit feedback')" :error="$errors->first('name')" />
                            <x-ui.input wire:model="headline" name="headline" :label="__('Public headline')" :placeholder="__('Tell us about your experience')" :error="$errors->first('headline')" />
                        </div>
                        <x-ui.textarea wire:model="thank_you_message" name="thank_you_message" :label="__('Thank you message')" rows="3" :error="$errors->first('thank_you_message')">{{ $thank_you_message }}</x-ui.textarea>
                        @include('applandingpages::partials.growth-tool-page-design', ['type' => 'feedback', 'templates' => $feedbackTemplates])
                    </div>

                    <div class="border-t px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 70%, transparent);">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Capture settings') }}</p>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <label class="flex items-start gap-3 border p-3 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), .58); color: var(--theme-header-text-color);">
                                <input type="checkbox" wire:model="rating_required" class="mt-1">
                                <span><span class="block font-semibold">{{ __('Require rating') }}</span><span class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Ask for 1-5 rating before submit') }}</span></span>
                            </label>
                            <label class="flex items-start gap-3 border p-3 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), .58); color: var(--theme-header-text-color);">
                                <input type="checkbox" wire:model="contact_required" class="mt-1">
                                <span><span class="block font-semibold">{{ __('Require contact') }}</span><span class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Phone or email required for follow-up') }}</span></span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <x-ui.button type="button" variant="outline" x-on:click="feedbackDialogOpen = false">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="submit">
                            <span wire:loading.remove wire:target="save"><i class="fa-light fa-floppy-disk"></i>{{ __('Create feedback form') }}</span>
                            <span wire:loading wire:target="save"><i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Creating...') }}</span>
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
