<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6" x-data="{ mappingOpen: @entangle('mappingOpen'), duplicateImportOpen: @entangle('duplicateImportOpen') }">
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif

    @if ($errorMessage)
        <x-ui.alert variant="danger" :title="__('Google Business error')" :description="$errorMessage" />
    @endif

    <section class="overflow-hidden rounded-[1.15rem] border px-5 py-6 sm:px-6 xl:px-7" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), .12), transparent 36%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-md border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-brands fa-google"></i>{{ __('Google Business Channel') }}
                </div>
                <h1 class="mt-4 text-[2.2rem] font-semibold leading-tight tracking-[-0.055em]" style="color: var(--theme-header-text-color);">{{ __('Google Business') }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Connect Google once, map locations, auto-sync reviews, reply with AI, and track Google review performance from one addon.') }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <x-ui.button type="button" variant="outline" onclick="window.location.href='{{ route('portal.google-business.connect') }}'" :disabled="! $configured">
                    <i class="fa-brands fa-google"></i>{{ __('Connect Google') }}
                </x-ui.button>
            </div>
        </div>
    </section>

    @if (! $configured)
        <section class="rounded-[1.15rem] border p-5" style="border-color: rgba(var(--theme-warning-color-rgb), .32); background-color: color-mix(in srgb, var(--theme-warning-color) 9%, var(--theme-surface-overlay));">
            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Google OAuth is not configured') }}</p>
            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Configure Google Business Profile in Admin API Integration. Add this callback URL in Google Cloud OAuth credentials:') }}</p>
            <code class="mt-3 block rounded-xl border px-3 py-2 text-xs" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color); background-color: var(--theme-surface-base);">{{ $callbackUrl }}</code>
        </section>
    @endif

    <section class="relative overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div
            wire:loading.flex
            wire:target="setTab,selectedLocationId,analyticsLocation,analyticsRange,analyticsRating,analyticsReplyStatus,reviewSearch,reviewRating,replyStatus,reviewDateRange,reviewsPerPage,postSearch,postStatus,saveGooglePost,publishGooglePost,duplicateGooglePost,deleteGooglePost,syncConnection,importCandidateLocation,requestCreateBusinessFromLocation,confirmCreateDuplicateBusiness,openMapping,mapLocation,syncLocationInfo,askStopManagingLocation,askDeleteLocation,confirmLocationAction,syncReviews,generateAiReply,publishReply,saveAutoReplyRule,toggleAutoReplyRule,deleteAutoReplyRule"
            class="absolute inset-0 z-30 items-center justify-center"
            style="background-color: color-mix(in srgb, var(--theme-surface-overlay) 82%, transparent); backdrop-filter: blur(2px);"
        >
            <div class="inline-flex items-center gap-3 rounded-2xl border px-4 py-3 text-sm font-semibold shadow-lg" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color); background-color: var(--theme-surface-base);">
                <i class="fa-light fa-spinner-third fa-spin" style="color: var(--theme-accent);"></i>
                <span>{{ __('Loading Google workspace...') }}</span>
            </div>
        </div>
        <div class="flex gap-2 overflow-x-auto border-b px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            @foreach ([
                'overview' => ['label' => __('Overview'), 'icon' => 'fa-light fa-grid-2'],
                'locations' => ['label' => __('Locations'), 'icon' => 'fa-light fa-location-dot'],
                'reviews' => ['label' => __('Reviews'), 'icon' => 'fa-light fa-star'],
                'posts' => ['label' => __('Posts'), 'icon' => 'fa-light fa-bullhorn'],
                'auto_reply' => ['label' => __('Auto Reply'), 'icon' => 'fa-light fa-wand-magic-sparkles'],
                'analytics' => ['label' => __('Analytics'), 'icon' => 'fa-light fa-chart-line'],
            ] as $tabKey => $tabItem)
                <button type="button" wire:click="setTab('{{ $tabKey }}')" class="shrink-0 rounded-xl border px-4 py-2 text-sm font-semibold transition" style="{{ $tab === $tabKey ? 'border-color: rgba(var(--theme-accent-rgb), .35); color: var(--theme-accent); background-color: color-mix(in srgb, var(--theme-accent) 10%, white);' : 'border-color: transparent; color: var(--theme-muted-text-color); background-color: transparent;' }}">
                    <span class="inline-flex items-center gap-2">
                        <i class="{{ $tabItem['icon'] }}"></i>
                        {{ $tabItem['label'] }}
                    </span>
                </button>
            @endforeach
        </div>

        @if ($tab === 'overview')
            <div class="p-5">
                @php
                    $mappedPercent = $analyticsSummary['locations'] > 0 ? min(100, (int) round(($analyticsSummary['mapped_locations'] / $analyticsSummary['locations']) * 100)) : 0;
                    $replyPercent = $analyticsSummary['total_reviews'] > 0 ? max(0, min(100, (int) round((($analyticsSummary['total_reviews'] - $analyticsSummary['not_replied']) / $analyticsSummary['total_reviews']) * 100))) : 0;
                    $lowScorePercent = $analyticsSummary['total_reviews'] > 0 ? max(0, min(100, (int) round(($analyticsSummary['low_score'] / $analyticsSummary['total_reviews']) * 100))) : 0;
                @endphp

                <div class="grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_24rem]">
                    <div class="overflow-hidden rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .62); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), .10), transparent 42%), var(--theme-surface-base);">
                        <div class="grid gap-5 p-5 lg:grid-cols-[minmax(0,1fr)_17rem] lg:items-center">
                            <div>
                                <div class="flex flex-wrap gap-2">
                    <x-ui.badge variant="success">{{ __('Location workspace') }}</x-ui.badge>
                    <x-ui.badge variant="neutral">{{ __('Choose what to manage') }}</x-ui.badge>
                                </div>
                                <h2 class="mt-4 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Google reputation command center') }}</h2>
                                <p class="mt-2 max-w-2xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Map every Google Business location, keep reviews synced in the background, and move reply work into one focused queue.') }}</p>
                                <div class="mt-5 flex flex-wrap gap-2">
                                    <x-ui.button type="button" size="sm" wire:click="$set('tab', 'locations')"><i class="fa-light fa-location-dot"></i>{{ __('Map locations') }}</x-ui.button>
                                    <x-ui.button type="button" size="sm" variant="outline" wire:click="$set('tab', 'reviews')"><i class="fa-light fa-star"></i>{{ __('Open reviews') }}</x-ui.button>
                                    <x-ui.button type="button" size="sm" variant="outline" wire:click="$set('tab', 'auto_reply')"><i class="fa-light fa-wand-magic-sparkles"></i>{{ __('Auto reply rules') }}</x-ui.button>
                                </div>
                            </div>
                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-overlay) 90%, transparent);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Review health') }}</p>
                                <div class="mt-3 flex items-end justify-between gap-3">
                                    <p class="text-4xl font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ $analyticsSummary['average_rating'] }}</p>
                                    <p class="pb-1 text-sm font-semibold" style="color: var(--theme-muted-text-color);">{{ __('avg rating') }}</p>
                                </div>
                                <div class="mt-4 h-2 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), .4);">
                                    <div class="h-full rounded-full" style="width: {{ $replyPercent }}%; background: linear-gradient(90deg, rgba(var(--theme-accent-rgb), .72), var(--theme-accent));"></div>
                                </div>
                                <p class="mt-2 text-xs" style="color: var(--theme-muted-text-color);">{{ $replyPercent }}% {{ __('of reviews handled or drafted') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: var(--theme-surface-base);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Setup progress') }}</p>
                                <p class="mt-2 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ __('Location coverage') }}</p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl" style="background-color: color-mix(in srgb, var(--theme-accent) 10%, white); color: var(--theme-accent);">
                                <i class="fa-light fa-circle-nodes"></i>
                            </div>
                        </div>
                        <div class="mt-5 space-y-4">
                            @foreach ([
                                ['label' => __('Google connected'), 'done' => $analyticsSummary['channels'] > 0],
                                ['label' => __('Locations available'), 'done' => $analyticsSummary['locations'] > 0],
                                ['label' => __('Locations selected'), 'done' => $analyticsSummary['managed_locations'] > 0],
                                ['label' => __('Locations mapped'), 'done' => $analyticsSummary['mapped_locations'] > 0],
                                ['label' => __('Reviews synced'), 'done' => $analyticsSummary['total_reviews'] > 0],
                            ] as $step)
                                <div class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-xl border text-xs" style="{{ $step['done'] ? 'border-color: rgba(var(--theme-success-color-rgb), .28); background-color: color-mix(in srgb, var(--theme-success-color) 12%, white); color: var(--theme-success-color);' : 'border-color: rgba(var(--theme-border-color-rgb), .58); color: var(--theme-muted-text-color);' }}"><i class="fa-light {{ $step['done'] ? 'fa-check' : 'fa-minus' }}"></i></span>
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $step['label'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                    @foreach ([
                        ['label' => __('Locations'), 'value' => $analyticsSummary['locations'], 'icon' => 'fa-location-dot', 'progress' => $analyticsSummary['locations'] > 0 ? 100 : 8, 'tone' => '#0ea5e9'],
                        ['label' => __('Managed'), 'value' => $analyticsSummary['managed_locations'], 'icon' => 'fa-circle-check', 'progress' => $analyticsSummary['locations'] > 0 ? max(8, (int) round(($analyticsSummary['managed_locations'] / $analyticsSummary['locations']) * 100)) : 8, 'tone' => 'var(--theme-accent)'],
                        ['label' => __('Mapped'), 'value' => $analyticsSummary['mapped_locations'], 'icon' => 'fa-diagram-project', 'progress' => max(8, $mappedPercent), 'tone' => 'var(--theme-success-color)'],
                        ['label' => __('Reviews'), 'value' => $analyticsSummary['total_reviews'], 'icon' => 'fa-star', 'progress' => $analyticsSummary['total_reviews'] > 0 ? 100 : 8, 'tone' => '#d97706'],
                        ['label' => __('Not replied'), 'value' => $analyticsSummary['not_replied'], 'icon' => 'fa-reply-clock', 'progress' => max(8, 100 - $replyPercent), 'tone' => $analyticsSummary['not_replied'] > 0 ? 'var(--theme-warning-color)' : 'var(--theme-success-color)'],
                    ] as $card)
                        <div class="rounded-xl border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ $card['label'] }}</p>
                                    <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ is_numeric($card['value']) ? format_number_locale((float) $card['value']) : $card['value'] }}</p>
                                </div>
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: color-mix(in srgb, {{ $card['tone'] }} 10%, white); color: {{ $card['tone'] }};">
                                    <i class="fa-light {{ $card['icon'] }}"></i>
                                </div>
                            </div>
                            <div class="mt-4 h-1.5 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), .36);">
                                <div class="h-full rounded-full" style="width: {{ $card['progress'] }}%; background-color: {{ $card['tone'] }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 grid gap-4 xl:grid-cols-[minmax(0,1fr)_24rem]">
                    <div class="rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                        <div class="border-b px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recommended next actions') }}</p>
                        </div>
                        <div class="grid divide-y lg:grid-cols-3 lg:divide-x lg:divide-y-0" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            @foreach ([
                                ['title' => __('Choose locations'), 'text' => __('Select only the Google locations this workspace should manage.'), 'tab' => 'locations', 'icon' => 'fa-location-check'],
                                ['title' => __('Clear reply queue'), 'text' => __('Open not-replied reviews and generate AI drafts faster.'), 'tab' => 'reviews', 'icon' => 'fa-inbox'],
                                ['title' => __('Create safe rules'), 'text' => __('Draft low-score replies and auto-publish simple thank-you replies.'), 'tab' => 'auto_reply', 'icon' => 'fa-wand-magic-sparkles'],
                            ] as $action)
                                <button type="button" wire:click="$set('tab', '{{ $action['tab'] }}')" class="group p-4 text-left transition hover:bg-black/[0.015]">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: color-mix(in srgb, var(--theme-accent) 10%, white); color: var(--theme-accent);"><i class="fa-light {{ $action['icon'] }}"></i></span>
                                    <p class="mt-4 font-semibold" style="color: var(--theme-header-text-color);">{{ $action['title'] }}</p>
                                    <p class="mt-2 min-h-[3rem] text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $action['text'] }}</p>
                                    <span class="mt-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-accent);">{{ __('Open') }} <i class="fa-light fa-arrow-right transition group-hover:translate-x-1"></i></span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Risk watch') }}</p>
                            <x-ui.badge :variant="$analyticsSummary['low_score'] > 0 ? 'warning' : 'success'">{{ $analyticsSummary['low_score'] }} {{ __('low-score') }}</x-ui.badge>
                        </div>
                        <div class="mt-4 h-2 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), .36);">
                            <div class="h-full rounded-full" style="width: {{ max(4, $lowScorePercent) }}%; background-color: {{ $analyticsSummary['low_score'] > 0 ? 'var(--theme-warning-color)' : 'var(--theme-success-color)' }};"></div>
                        </div>
                        <p class="mt-3 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Low-score reviews should stay in draft/manual approval before publishing any reply to Google.') }}</p>
                        <button type="button" wire:click="$set('tab', 'reviews')" class="mt-4 inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl border text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);">
                            <i class="fa-light fa-filter"></i>{{ __('Review low-score queue') }}
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if (false)
            <div class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                @forelse ($connections as $connection)
                    <div class="flex flex-col gap-4 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $connection->google_account_email ?: __('Google account') }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">
                                {{ $connection->locations_count }} {{ __('locations') }} &middot; {{ ucfirst($connection->status) }}
                                @if ($connection->last_synced_at)
                                    &middot; {{ __('Last synced') }} {{ $connection->last_synced_at->diffForHumans() }}
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-ui.badge :variant="$connection->auto_sync ? 'success' : 'neutral'">{{ $connection->auto_sync ? __('Auto sync on') : __('Auto sync off') }}</x-ui.badge>
                            <button type="button" wire:click="toggleConnectionAutoSync({{ $connection->id }})" class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);"><i class="fa-light fa-clock-rotate-left"></i>{{ __('Toggle auto sync') }}</button>
                            <button type="button" wire:click="syncConnection({{ $connection->id }})" class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);"><i class="fa-light fa-rotate"></i>{{ __('Sync now') }}</button>
                            <button type="button" wire:click="disconnect({{ $connection->id }})" wire:confirm="{{ __('Disconnect this Google account?') }}" class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color); background-color: var(--theme-surface-overlay);"><i class="fa-light fa-trash"></i>{{ __('Disconnect') }}</button>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center text-sm" style="color: var(--theme-muted-text-color);">{{ __('No Google Business account connected yet.') }}</div>
                @endforelse
            </div>
        @endif

        @if ($tab === 'locations')
            <div class="flex flex-col gap-3 border-b px-5 py-4 lg:flex-row lg:items-center lg:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Google locations') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Add only the Google locations you want to manage, then map them to LocalBoost businesses.') }}</p>
                </div>
                <x-ui.button type="button" variant="outline" onclick="window.location.href='{{ route('portal.google-business.connect') }}'" :disabled="! $configured"><i class="fa-brands fa-google"></i>{{ __('Connect Google') }}</x-ui.button>
            </div>

            @if ($connections->isNotEmpty())
                <div class="divide-y border-b" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    @foreach ($connections as $connection)
                        <div class="flex flex-col gap-4 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $connection->google_account_email ?: __('Google account') }}</p>
                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">
                                    {{ $connection->locations_count }} {{ __('locations') }} &middot; {{ ucfirst($connection->status) }}
                                    @if ($connection->last_synced_at)
                                        &middot; {{ __('Last synced') }} {{ $connection->last_synced_at->diffForHumans() }}
                                    @endif
                                </p>
                                @if ($connection->last_error)
                                    <p class="mt-2 text-xs" style="color: var(--theme-danger-color);">{{ __('Last sync error') }}: {{ $connection->last_error }}</p>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <x-ui.button type="button" variant="primary" size="sm" wire:click="syncConnection({{ $connection->id }})" wire:loading.attr="disabled" wire:target="syncConnection({{ $connection->id }})">
                                    <span wire:loading.remove wire:target="syncConnection({{ $connection->id }})" class="inline-flex items-center gap-2"><i class="fa-light fa-rotate"></i>{{ __('Refresh locations') }}</span>
                                    <span wire:loading wire:target="syncConnection({{ $connection->id }})" class="inline-flex items-center gap-2"><i class="fa-light fa-spinner-third fa-spin"></i>{{ __('Loading...') }}</span>
                                </x-ui.button>
                                <x-ui.button type="button" variant="outline" size="sm" wire:click="toggleConnectionAutoSync({{ $connection->id }})"><i class="fa-light fa-clock-rotate-left"></i>{{ $connection->auto_sync ? __('Auto sync on') : __('Auto sync off') }}</x-ui.button>
                                <x-ui.button type="button" variant="danger" size="sm" wire:click="disconnect({{ $connection->id }})" wire:confirm="{{ __('Disconnect this Google account?') }}"><i class="fa-light fa-trash"></i>{{ __('Disconnect') }}</x-ui.button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (! empty($locationCandidates))
                <div class="border-b p-5" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    <div class="rounded-[1rem] border" style="border-color: rgba(var(--theme-accent-rgb), .24); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), .08), transparent 45%), var(--theme-surface-base);">
                        <div class="flex flex-col gap-3 border-b px-4 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            <div>
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.badge variant="success">{{ __('Choose locations') }}</x-ui.badge>
                                    <x-ui.badge variant="neutral">{{ count($locationCandidates) }} {{ __('available') }}</x-ui.badge>
                                </div>
                                <p class="mt-3 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Select Google locations to add into LocalBoost') }}</p>
                                <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Locations are not added automatically after Google connect. Add only the locations you want to manage for reviews, analytics, and auto reply.') }}</p>
                            </div>
                            <x-ui.button type="button" variant="outline" size="sm" wire:click="clearLocationCandidates"><i class="fa-light fa-xmark"></i>{{ __('Dismiss') }}</x-ui.button>
                        </div>
                        <div class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            @foreach ($locationCandidates as $candidateIndex => $candidate)
                                <div class="flex flex-col gap-3 px-4 py-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $candidate['name'] ?? __('Google location') }}</p>
                                            @if (! empty($candidate['already_imported']))
                                                <x-ui.badge variant="warning">{{ __('Already in LocalBoost') }}</x-ui.badge>
                                            @endif
                                        </div>
                                        <p class="mt-1 max-w-3xl text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $candidate['address'] ?? ($candidate['google_location_id'] ?? '') }}</p>
                                        @if (! empty($candidate['category']))
                                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $candidate['category'] }}</p>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <x-ui.button type="button" size="sm" wire:click="importCandidateLocation({{ $candidateIndex }})" wire:loading.attr="disabled" wire:target="importCandidateLocation({{ $candidateIndex }})">
                                            <span wire:loading.remove wire:target="importCandidateLocation({{ $candidateIndex }})" class="inline-flex items-center gap-2"><i class="fa-light fa-plus"></i>{{ __('Add to manage') }}</span>
                                            <span wire:loading wire:target="importCandidateLocation({{ $candidateIndex }})" class="inline-flex items-center gap-2"><i class="fa-light fa-spinner-third fa-spin"></i>{{ __('Adding...') }}</span>
                                        </x-ui.button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                @forelse ($locations as $location)
                    <div class="grid gap-4 px-5 py-4 lg:grid-cols-[minmax(0,1.4fr)_minmax(12rem,0.7fr)_minmax(14rem,0.8fr)_minmax(18rem,auto)] lg:items-center">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $location->name ?: __('Google location') }}</p>
                                @if ($location->is_managed)
                                    <x-ui.badge variant="success">{{ __('Managed') }}</x-ui.badge>
                                @endif
                            </div>
                            <p class="mt-1 max-w-xl text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $location->address ?: $location->google_location_id }}</p>
                        </div>
                        <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ $location->business?->name ?: __('Not mapped') }}</p>
                        <div class="flex flex-wrap gap-2">
                            <x-ui.badge :variant="$location->sync_business_info ? 'success' : 'neutral'">{{ __('Info') }}</x-ui.badge>
                            <x-ui.badge :variant="$location->sync_hours ? 'success' : 'neutral'">{{ __('Hours') }}</x-ui.badge>
                            <x-ui.badge :variant="$location->sync_reviews ? 'success' : 'neutral'">{{ __('Reviews') }}</x-ui.badge>
                            <x-ui.badge :variant="$location->auto_reply_enabled ? 'success' : 'neutral'">{{ __('Auto reply') }}</x-ui.badge>
                        </div>
                        <div class="flex flex-wrap justify-start gap-2 lg:justify-end">
                            @if ($location->is_managed)
                                <x-ui.button type="button" variant="outline" size="sm" wire:click="askStopManagingLocation({{ $location->id }})"><i class="fa-light fa-circle-minus"></i>{{ __('Stop') }}</x-ui.button>
                            @endif
                            <x-ui.button type="button" variant="outline" size="sm" wire:click="requestCreateBusinessFromLocation({{ $location->id }})"><i class="fa-light fa-store"></i>{{ __('Import') }}</x-ui.button>
                            <x-ui.button type="button" variant="outline" size="sm" wire:click="openMapping({{ $location->id }})"><i class="fa-light fa-link"></i>{{ __('Map') }}</x-ui.button>
                            <x-ui.button type="button" variant="outline" size="sm" wire:click="syncLocationInfo({{ $location->id }})"><i class="fa-light fa-rotate"></i>{{ __('Info') }}</x-ui.button>
                            <x-ui.button type="button" variant="outline" size="sm" wire:click="selectLocation({{ $location->id }}); $set('tab', 'reviews')"><i class="fa-light fa-star"></i>{{ __('Reviews') }}</x-ui.button>
                            <x-ui.button type="button" variant="danger" size="sm" wire:click="askDeleteLocation({{ $location->id }})"><i class="fa-light fa-trash"></i>{{ __('Delete') }}</x-ui.button>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center">
                        @if ($connections->isNotEmpty())
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Google account connected — load your Maps listings') }}</p>
                            <p class="mx-auto mt-2 max-w-md text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Click Refresh locations above to fetch your Google Business listings, then choose which location to add and manage.') }}</p>
                            @if ($connections->first())
                                <x-ui.button type="button" class="mt-5" wire:click="syncConnection({{ $connections->first()->id }})" wire:loading.attr="disabled" wire:target="syncConnection({{ $connections->first()->id }})">
                                    <span wire:loading.remove wire:target="syncConnection({{ $connections->first()->id }})" class="inline-flex items-center gap-2"><i class="fa-light fa-rotate"></i>{{ __('Refresh locations') }}</span>
                                    <span wire:loading wire:target="syncConnection({{ $connections->first()->id }})" class="inline-flex items-center gap-2"><i class="fa-light fa-spinner-third fa-spin"></i>{{ __('Loading...') }}</span>
                                </x-ui.button>
                            @endif
                        @else
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No Google locations added yet') }}</p>
                            <p class="mx-auto mt-2 max-w-md text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Connect Google, then choose the locations you want to manage. LocalBoost will not import every Google location automatically.') }}</p>
                        @endif
                    </div>
                @endforelse
            </div>
        @endif

        @if ($tab === 'reviews')
            @if ($selectedLocation)
                <div class="flex flex-col gap-4 border-b px-5 py-4 xl:flex-row xl:items-end xl:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Google Reviews Workspace') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Location') }}: {{ $selectedLocation->name }} &middot; {{ __('Mapped business') }}: {{ $selectedLocation->business?->name ?: __('Not mapped') }}</p>
                    </div>
                    <div class="grid w-full gap-3 md:grid-cols-[minmax(0,1fr)_auto] xl:w-auto xl:min-w-[34rem]">
                        <x-ui.combobox
                            model="selectedLocationId"
                            name="selectedLocationId"
                            :label="__('Google location')"
                            :selected="$selectedLocationId"
                            :options="$managedLocations->map(fn ($location) => ['value' => (string) $location->id, 'label' => $location->name, 'meta' => $location->business?->name ?: __('Not mapped'), 'icon' => 'fa-location-dot'])->values()->all()"
                            :placeholder="__('Choose Google location')"
                            :search-placeholder="__('Search Google location...')"
                            :empty-text="__('No managed locations found')"
                            icon="fa-light fa-location-dot"
                        />
                        <div class="flex items-end">
                            <x-ui.button type="button" variant="outline" wire:click="syncReviews({{ $selectedLocation->id }})" wire:loading.attr="disabled" wire:target="syncReviews({{ $selectedLocation->id }})">
                                <span wire:loading.remove wire:target="syncReviews({{ $selectedLocation->id }})" class="inline-flex items-center gap-2"><i class="fa-light fa-rotate"></i>{{ __('Sync reviews') }}</span>
                                <span wire:loading wire:target="syncReviews({{ $selectedLocation->id }})" class="inline-flex items-center gap-2"><i class="fa-light fa-spinner-third fa-spin"></i>{{ __('Syncing...') }}</span>
                            </x-ui.button>
                        </div>
                    </div>
                </div>
                <div class="grid gap-3 border-b px-5 py-4 sm:grid-cols-2 xl:grid-cols-6" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    @foreach ([
                        ['label' => __('Total'), 'value' => $reviewSummary['total'], 'icon' => 'fa-star', 'tone' => '#d97706'],
                        ['label' => __('Avg rating'), 'value' => $reviewSummary['average'], 'icon' => 'fa-ranking-star', 'tone' => 'var(--theme-success-color)'],
                        ['label' => __('Unreplied'), 'value' => $reviewSummary['unreplied'], 'icon' => 'fa-reply-clock', 'tone' => 'var(--theme-warning-color)'],
                        ['label' => __('Drafts'), 'value' => $reviewSummary['drafts'], 'icon' => 'fa-pen-field', 'tone' => 'var(--theme-accent)'],
                        ['label' => __('Replied'), 'value' => $reviewSummary['replied'], 'icon' => 'fa-circle-check', 'tone' => 'var(--theme-success-color)'],
                        ['label' => __('Low-score'), 'value' => $reviewSummary['low_score'], 'icon' => 'fa-triangle-exclamation', 'tone' => 'var(--theme-danger-color)'],
                    ] as $card)
                        <div class="rounded-xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $card['label'] }}</p>
                                    <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ is_numeric($card['value']) ? format_number_locale((float) $card['value']) : $card['value'] }}</p>
                                </div>
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl" style="background-color: color-mix(in srgb, {{ $card['tone'] }} 10%, white); color: {{ $card['tone'] }};"><i class="fa-light {{ $card['icon'] }}"></i></span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="grid gap-3 border-b px-5 py-4 md:grid-cols-[minmax(0,1fr)_10rem_12rem_12rem_9rem]" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-base) 84%, transparent);">
                    <x-ui.input wire:model.live.debounce.300ms="reviewSearch" name="reviewSearch" :placeholder="__('Search reviewer or review text...')" />
                    <x-ui.combobox
                        model="reviewRating"
                        name="reviewRating"
                        :selected="$reviewRating"
                        :options="collect([['value' => 'all', 'label' => __('All ratings'), 'icon' => 'fa-star']])->merge(collect(range(5, 1))->map(fn ($rating) => ['value' => (string) $rating, 'label' => $rating.' '.__('stars'), 'icon' => 'fa-star']))->values()->all()"
                        :placeholder="__('All ratings')"
                        :search-placeholder="__('Search rating...')"
                        icon="fa-light fa-star"
                    />
                    <x-ui.combobox
                        model="replyStatus"
                        name="replyStatus"
                        :selected="$replyStatus"
                        :options="[
                            ['value' => 'all', 'label' => __('All replies'), 'icon' => 'fa-reply'],
                            ['value' => 'not_replied', 'label' => __('Not replied'), 'icon' => 'fa-reply-clock'],
                            ['value' => 'draft', 'label' => __('Draft'), 'icon' => 'fa-pen-field'],
                            ['value' => 'replied', 'label' => __('Replied'), 'icon' => 'fa-circle-check'],
                        ]"
                        :placeholder="__('All replies')"
                        :search-placeholder="__('Search reply status...')"
                        icon="fa-light fa-reply"
                    />
                    <x-ui.combobox
                        model="reviewDateRange"
                        name="reviewDateRange"
                        :selected="$reviewDateRange"
                        :options="[
                            ['value' => 'all', 'label' => __('All time'), 'icon' => 'fa-calendar'],
                            ['value' => '7', 'label' => __('Last 7 days'), 'icon' => 'fa-calendar-week'],
                            ['value' => '30', 'label' => __('Last 30 days'), 'icon' => 'fa-calendar-days'],
                        ]"
                        :placeholder="__('All time')"
                        :search-placeholder="__('Search date range...')"
                        icon="fa-light fa-calendar"
                    />
                    <x-ui.combobox
                        model="reviewsPerPage"
                        name="reviewsPerPage"
                        :selected="$reviewsPerPage"
                        :options="[
                            ['value' => '10', 'label' => '10 / '.__('page'), 'icon' => 'fa-list'],
                            ['value' => '20', 'label' => '20 / '.__('page'), 'icon' => 'fa-list'],
                            ['value' => '50', 'label' => '50 / '.__('page'), 'icon' => 'fa-list'],
                        ]"
                        :placeholder="'10 / '.__('page')"
                        :search-placeholder="__('Search page size...')"
                        icon="fa-light fa-list"
                    />
                </div>
                <div class="space-y-4 p-5">
                    @forelse ($reviews as $review)
                        <article class="grid overflow-hidden rounded-[1rem] border shadow-sm lg:grid-cols-[minmax(0,1fr)_minmax(24rem,0.82fr)]" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                            <div class="p-4 lg:border-r" style="border-color: rgba(var(--theme-border-color-rgb), .52);">
                                <div class="flex flex-wrap items-start gap-3">
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">{{ str($review->reviewer_name ?: 'G')->substr(0, 1)->upper() }}</span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-semibold leading-6" style="color: var(--theme-header-text-color);">{{ $review->reviewer_name ?: __('Google user') }}</p>
                                            <x-ui.badge :variant="$review->reply ? 'success' : ($review->local_reply ? 'warning' : 'neutral')">{{ $review->reply ? __('Replied') : ($review->local_reply ? __('Draft') : __('Not replied')) }}</x-ui.badge>
                                            <span class="inline-flex items-center gap-1 rounded-full border px-2 py-1 text-[11px] font-semibold uppercase tracking-[0.12em]" style="border-color: rgba(var(--theme-border-color-rgb), .58); color: #d97706;"><i class="fa-solid fa-star text-[0.7rem]"></i>{{ $review->rating }} {{ __('stars') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-2 flex flex-wrap items-center gap-2 text-xs" style="color: var(--theme-muted-text-color);">
                                    @if ($review->review_created_at)
                                        <span>{{ format_datetime_locale($review->review_created_at) }}</span>
                                    @endif
                                    <span class="inline-flex items-center gap-1"><i class="fa-brands fa-google"></i>{{ __('Synced from Google') }}</span>
                                    @if ($review->last_synced_at)
                                        <span>{{ __('Last synced') }} {{ $review->last_synced_at->diffForHumans() }}</span>
                                    @endif
                                </p>
                                <div class="mt-4 rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .52); background-color: color-mix(in srgb, var(--theme-surface-overlay) 88%, transparent);">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Customer review') }}</p>
                                    <p class="mt-3 text-sm leading-7" style="color: var(--theme-header-text-color);">{{ $review->comment ?: __('No written comment.') }}</p>
                                </div>
                                @if ($review->reply || $review->local_reply)
                                    <div class="mt-3 rounded-2xl border p-4 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), .52); background-color: var(--theme-surface-base);">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $review->reply ? __('Published reply') : __('Draft reply') }}</p>
                                            <i class="fa-light {{ $review->reply ? 'fa-circle-check' : 'fa-pen-field' }}" style="color: {{ $review->reply ? 'var(--theme-success-color)' : 'var(--theme-warning-color)' }};"></i>
                                        </div>
                                        <p class="mt-2 leading-7" style="color: var(--theme-header-text-color);">{{ $review->reply ?: $review->local_reply }}</p>
                                    </div>
                                @endif
                            </div>
                            <form wire:submit="publishReply({{ $review->id }})" class="flex flex-col gap-3 p-4" style="background-color: color-mix(in srgb, var(--theme-surface-overlay) 82%, transparent);">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Reply on Google') }}</p>
                                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Generate a draft, edit it, then publish it back to Google.') }}</p>
                                    </div>
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb), .1); color: var(--theme-accent);"><i class="fa-light fa-reply"></i></span>
                                </div>
                                <x-ui.textarea wire:model="replyDrafts.{{ $review->id }}" name="replyText_{{ $review->id }}" rows="5" :placeholder="__('Write a reply to publish on Google...')">{{ $replyDrafts[$review->id] ?? $review->local_reply ?? '' }}</x-ui.textarea>
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.button type="button" variant="outline" size="sm" wire:click="generateAiReply({{ $review->id }})" wire:loading.attr="disabled" wire:target="generateAiReply({{ $review->id }})">
                                        <span wire:loading.remove wire:target="generateAiReply({{ $review->id }})" class="inline-flex items-center gap-2"><i class="fa-light fa-wand-magic-sparkles"></i>{{ __('Generate AI Reply') }}</span>
                                        <span wire:loading wire:target="generateAiReply({{ $review->id }})" class="inline-flex items-center gap-2"><i class="fa-light fa-spinner-third fa-spin"></i>{{ __('Generating...') }}</span>
                                    </x-ui.button>
                                    <x-ui.button type="submit" size="sm" wire:loading.attr="disabled" wire:target="publishReply({{ $review->id }})">
                                        <span wire:loading.remove wire:target="publishReply({{ $review->id }})" class="inline-flex items-center gap-2"><i class="fa-light fa-paper-plane"></i>{{ __('Publish to Google') }}</span>
                                        <span wire:loading wire:target="publishReply({{ $review->id }})" class="inline-flex items-center gap-2"><i class="fa-light fa-spinner-third fa-spin"></i>{{ __('Publishing...') }}</span>
                                    </x-ui.button>
                                </div>
                            </form>
                        </article>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No Google reviews synced yet') }}</p>
                            <p class="mx-auto mt-2 max-w-md text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $selectedLocation->business ? __('Sync reviews from the selected Google Business location to reply, analyze ratings, and use AI-generated responses.') : __('Map this Google location to a LocalBoost business before syncing reviews.') }}</p>
                        </div>
                    @endforelse
                    @if ($reviews->hasPages())
                        <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                            {{ $reviews->links() }}
                        </div>
                    @endif
                </div>
            @else
                <div class="px-5 py-12 text-center">
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Choose a Google location to manage first') }}</p>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Open Locations, then click Manage on the Google location you want to use for reviews, analytics, and auto reply.') }}</p>
                    <div class="mt-4"><x-ui.button type="button" wire:click="$set('tab', 'locations')"><i class="fa-light fa-location-dot"></i>{{ __('Choose locations') }}</x-ui.button></div>
                </div>
            @endif
        @endif

        @if ($tab === 'posts')
            <div class="space-y-5 p-5">
                <section class="overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                    <div class="flex flex-col gap-4 border-b px-5 py-4 xl:flex-row xl:items-end xl:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Google Business Posts') }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Manage drafts, scheduled posts, published posts, and failed Google Business publishing attempts.') }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-ui.button type="button" size="sm" variant="outline" x-on:click="$dispatch('google-post-logs-modal-open')" wire:loading.attr="disabled" wire:target="openGooglePostForm,publishGooglePost,editGooglePost,duplicateGooglePost,deleteGooglePost">
                                <i class="fa-light fa-clock-rotate-left" wire:loading.remove wire:target="openGooglePostForm,publishGooglePost,editGooglePost,duplicateGooglePost,deleteGooglePost"></i>
                                <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="openGooglePostForm,publishGooglePost,editGooglePost,duplicateGooglePost,deleteGooglePost"></i>
                                {{ __('Logs') }}
                            </x-ui.button>
                            <x-ui.button type="button" size="sm" wire:click="openGooglePostForm" wire:loading.attr="disabled" wire:target="openGooglePostForm">
                                <i class="fa-light fa-plus" wire:loading.remove wire:target="openGooglePostForm"></i>
                                <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="openGooglePostForm"></i>
                                <span wire:loading.remove wire:target="openGooglePostForm">{{ __('New post') }}</span>
                                <span wire:loading wire:target="openGooglePostForm">{{ __('Opening...') }}</span>
                            </x-ui.button>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 border-b px-5 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                        @foreach ([
                            ['key' => 'all', 'label' => __('All posts'), 'count' => $googlePostSummary['total']],
                            ['key' => 'draft', 'label' => __('Drafts'), 'count' => $googlePostSummary['draft']],
                            ['key' => 'scheduled', 'label' => __('Scheduled'), 'count' => $googlePostSummary['scheduled']],
                            ['key' => 'published', 'label' => __('Published'), 'count' => $googlePostSummary['published']],
                            ['key' => 'failed', 'label' => __('Failed'), 'count' => $googlePostSummary['failed']],
                        ] as $statusTab)
                            <button
                                type="button"
                                wire:click="$set('postStatus', '{{ $statusTab['key'] }}')"
                                class="rounded-[0.8rem] border px-4 py-2 text-sm font-semibold transition"
                                style="{{ $postStatus === $statusTab['key'] ? 'background-color: rgba(var(--theme-accent-rgb), .14); color: var(--theme-accent); border-color: rgba(var(--theme-accent-rgb), .28);' : 'color: var(--theme-muted-text-color); border-color: transparent;' }}"
                            >
                                {{ $statusTab['label'] }}
                                <span class="ml-2 rounded-full px-2 py-0.5 text-xs" style="background-color: rgba(var(--theme-accent-rgb), .10);">{{ format_number_locale($statusTab['count']) }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Post queue') }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Draft, scheduled, published, and failed posts are managed in one table.') }}</p>
                        </div>
                        <div class="w-full sm:w-80">
                            <x-ui.input wire:model.live.debounce.400ms="postSearch" placeholder="{{ __('Search posts') }}" />
                        </div>
                    </div>

                    @if ($googlePosts->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead style="color: var(--theme-muted-text-color);">
                                    <tr>
                                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Post') }}</th>
                                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Location') }}</th>
                                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Type') }}</th>
                                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Schedule') }}</th>
                                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                    @foreach ($googlePosts as $post)
                                        @php
                                            $statusVariant = match ($post->status) {
                                                'published' => 'success',
                                                'failed' => 'danger',
                                                'scheduled' => 'primary',
                                                default => 'neutral',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="px-5 py-4">
                                                <p class="max-w-sm truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $post->title ?: str($post->summary)->limit(80) }}</p>
                                                <p class="mt-1 max-w-sm truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $post->summary }}</p>
                                                @if ($post->error_message)
                                                    <p class="mt-2 max-w-sm rounded-lg border px-3 py-2 text-xs text-rose-600" style="border-color: rgba(var(--theme-danger-color-rgb), .24); background-color: rgba(var(--theme-danger-color-rgb), .06);">{{ $post->error_message }}</p>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4" style="color: var(--theme-muted-text-color);">
                                                <p class="max-w-[16rem] truncate">{{ $post->location?->name ?: __('Location removed') }}</p>
                                                <p class="mt-1 max-w-[16rem] truncate text-xs">{{ $post->business?->name ?: $post->location?->business?->name }}</p>
                                            </td>
                                            <td class="px-5 py-4"><x-ui.badge variant="neutral">{{ str($post->type)->headline() }}</x-ui.badge></td>
                                            <td class="px-5 py-4" style="color: var(--theme-muted-text-color);">
                                                @if ($post->scheduled_at && $post->status === 'scheduled')
                                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_datetime_locale($post->scheduled_at) }}</p>
                                                    <p class="mt-1 text-xs">{{ $post->scheduled_at->diffForHumans() }}</p>
                                                @elseif ($post->published_at)
                                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_datetime_locale($post->published_at) }}</p>
                                                    <p class="mt-1 text-xs">{{ __('Published') }}</p>
                                                @else
                                                    <span class="text-xs">{{ __('Not scheduled') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4"><x-ui.badge :variant="$statusVariant">{{ str($post->status)->headline() }}</x-ui.badge></td>
                                            <td class="px-5 py-4 text-right">
                                                <div class="inline-flex items-center gap-2">
                                                    @if ($post->status !== 'published')
                                                        <button type="button" wire:click="publishGooglePost({{ $post->id }})" wire:loading.attr="disabled" wire:target="publishGooglePost({{ $post->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5 disabled:pointer-events-none disabled:opacity-70" style="border-color: rgba(var(--theme-accent-rgb), .30); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .06);" title="{{ __('Publish') }}">
                                                            <i class="fa-light fa-paper-plane" wire:loading.remove wire:target="publishGooglePost({{ $post->id }})"></i>
                                                            <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="publishGooglePost({{ $post->id }})"></i>
                                                        </button>
                                                    @endif
                                                    @if ($post->search_url)
                                                        <a href="{{ $post->search_url }}" target="_blank" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-accent); background-color: var(--theme-surface-overlay);" title="{{ __('View on Google') }}"><i class="fa-light fa-arrow-up-right-from-square"></i></a>
                                                    @endif
                                                    <button type="button" wire:click="editGooglePost({{ $post->id }})" wire:loading.attr="disabled" wire:target="editGooglePost({{ $post->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5 disabled:pointer-events-none disabled:opacity-70" style="border-color: rgba(var(--theme-accent-rgb), .30); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .06);" title="{{ __('Edit') }}">
                                                        <i class="fa-light fa-pencil" wire:loading.remove wire:target="editGooglePost({{ $post->id }})"></i>
                                                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="editGooglePost({{ $post->id }})"></i>
                                                    </button>
                                                    <button type="button" wire:click="duplicateGooglePost({{ $post->id }})" wire:loading.attr="disabled" wire:target="duplicateGooglePost({{ $post->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5 disabled:pointer-events-none disabled:opacity-70" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('Duplicate') }}">
                                                        <i class="fa-light fa-copy" wire:loading.remove wire:target="duplicateGooglePost({{ $post->id }})"></i>
                                                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="duplicateGooglePost({{ $post->id }})"></i>
                                                    </button>
                                                    <x-ui.dialog :title="__('Delete Google post')" :description="__('This deletes the local post record and its saved publish state. It does not remove an already published post from Google.')" width="sm" dismissible>
                                                        <x-slot:trigger>
                                                            <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color); background-color: rgba(var(--theme-danger-color-rgb), .05);" title="{{ __('Delete') }}"><i class="fa-light fa-trash"></i></button>
                                                        </x-slot:trigger>
                                                        <x-slot:footer>
                                                            <div class="flex justify-end gap-3">
                                                                <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                                                <x-ui.button type="button" variant="danger" wire:click="deleteGooglePost({{ $post->id }})" wire:loading.attr="disabled" wire:target="deleteGooglePost({{ $post->id }})" x-on:click="open = false">
                                                                    <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="deleteGooglePost({{ $post->id }})"></i>
                                                                    <span wire:loading.remove wire:target="deleteGooglePost({{ $post->id }})">{{ __('Delete') }}</span>
                                                                    <span wire:loading wire:target="deleteGooglePost({{ $post->id }})">{{ __('Deleting...') }}</span>
                                                                </x-ui.button>
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
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Showing') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($googlePosts->firstItem()) }}</span> - <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($googlePosts->lastItem()) }}</span> {{ __('of') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($googlePosts->total()) }}</span> {{ __('posts') }}</p>
                            <div class="flex items-center gap-2">
                                <x-ui.button type="button" variant="outline" wire:click="previousPage('googlePostsPage')" wire:loading.attr="disabled" wire:target="previousPage,nextPage" :disabled="$googlePosts->onFirstPage()">
                                    <i class="fa-light fa-arrow-left" wire:loading.remove wire:target="previousPage('googlePostsPage')"></i>
                                    <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="previousPage('googlePostsPage')"></i>
                                    {{ __('Previous') }}
                                </x-ui.button>
                                <span class="rounded-[0.8rem] border px-4 py-2 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .10);">{{ __('Page :page / :pages', ['page' => $googlePosts->currentPage(), 'pages' => max(1, $googlePosts->lastPage())]) }}</span>
                                <x-ui.button type="button" variant="outline" wire:click="nextPage('googlePostsPage')" wire:loading.attr="disabled" wire:target="previousPage,nextPage" :disabled="! $googlePosts->hasMorePages()">
                                    {{ __('Next') }}
                                    <i class="fa-light fa-arrow-right" wire:loading.remove wire:target="nextPage('googlePostsPage')"></i>
                                    <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="nextPage('googlePostsPage')"></i>
                                </x-ui.button>
                            </div>
                        </div>
                    @else
                        <div class="p-8"><x-ui.empty icon="fa-light fa-newspaper" :title="__('No Google Business posts yet')" :description="__('Create a post, schedule it, or publish directly to a managed Google Business location.')" /></div>
                    @endif

                </section>

                <x-ui.modal
                    width="lg"
                    open-event="google-post-logs-modal-open"
                    close-event="google-post-logs-modal-close"
                    :title="__('Publish logs')"
                    :description="__('Recent Google Business post publish attempts. Only the latest 20 logs are kept.')"
                    body-class="p-0"
                >
                    <div class="max-h-[58vh] overflow-y-auto">
                        @forelse ($googlePostLogs as $log)
                            <div class="border-b px-5 py-4 last:border-b-0" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" style="background-color: {{ $log->status === 'success' ? 'rgba(16,185,129,.10)' : 'rgba(var(--theme-danger-color-rgb), .08)' }}; color: {{ $log->status === 'success' ? '#047857' : 'var(--theme-danger-color)' }};">
                                        <i class="fa-light {{ $log->status === 'success' ? 'fa-check' : 'fa-triangle-exclamation' }}"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <x-ui.badge :variant="$log->status === 'success' ? 'success' : 'danger'">{{ str($log->status)->headline() }}</x-ui.badge>
                                            <span class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ str($log->action)->headline() }}</span>
                                            <span class="text-xs" style="color: var(--theme-muted-text-color);">{{ $log->created_at?->diffForHumans() }}</span>
                                        </div>
                                        <p class="mt-2 truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $log->post?->title ?: str($log->post?->summary ?: __('Post removed'))->limit(90) }}</p>
                                        @if ($log->error_message)
                                            <p class="mt-1 line-clamp-2 text-xs leading-5 text-rose-600">{{ str($log->error_message)->limit(180) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8">
                                <x-ui.empty icon="fa-light fa-clock-rotate-left" :title="__('No publish logs yet')" :description="__('Publish or schedule a Google Business post to create the first log.')" />
                            </div>
                        @endforelse
                    </div>

                    <x-slot:footer>
                        <x-ui.button type="button" variant="outline" x-on:click="$dispatch('google-post-logs-modal-close')">{{ __('Close') }}</x-ui.button>
                    </x-slot:footer>
                </x-ui.modal>

                <x-ui.modal
                    width="xl"
                    open-event="google-post-modal-open"
                    close-event="google-post-modal-close"
                    :title="$editingPostId ? __('Edit Google post') : __('New Google post')"
                    :description="$editingPostId ? __('Update the local post, then publish again or keep it as a draft.') : __('Publish now, schedule for later, or save a local draft before pushing to Google.')"
                    body-class="space-y-6 overflow-visible max-h-none px-6 py-6"
                >
                    <form id="google-post-modal-form" wire:submit="saveGooglePost" class="space-y-5">
                        <x-ui.combobox
                            model="postLocationId"
                            name="postLocationId"
                            :label="__('Google location')"
                            :selected="$postLocationId"
                            :options="$managedLocations->map(fn ($location) => ['value' => (string) $location->id, 'label' => $location->name, 'meta' => $location->business?->name ?: __('Not mapped'), 'icon' => 'fa-location-dot'])->values()->all()"
                            :placeholder="__('Choose managed location')"
                            :search-placeholder="__('Search Google location...')"
                            :empty-text="__('No managed locations found')"
                            icon="fa-light fa-location-dot"
                            :error="$errors->first('postLocationId')"
                        />

                        <div class="grid gap-4 md:grid-cols-2">
                            <x-ui.combobox
                                model="postType"
                                name="postType"
                                :label="__('Post type')"
                                :selected="$postType"
                                :options="[
                                    ['value' => 'standard', 'label' => __('Standard update'), 'meta' => __('General Google Business update'), 'icon' => 'fa-newspaper'],
                                    ['value' => 'offer', 'label' => __('Offer post'), 'meta' => __('Uses Google OFFER topic type'), 'icon' => 'fa-badge-percent'],
                                    ['value' => 'event', 'label' => __('Event post'), 'meta' => __('Requires start and end dates'), 'icon' => 'fa-calendar-days'],
                                ]"
                                :placeholder="__('Choose post type')"
                                :search-placeholder="__('Search post type...')"
                                icon="fa-light fa-layer-group"
                                :error="$errors->first('postType')"
                            />
                            <x-ui.datetime-picker
                                wire:model="postScheduledAt"
                                name="postScheduledAt"
                                :label="__('Schedule publish time')"
                                :value="$postScheduledAt"
                                :placeholder="__('Optional schedule')"
                                picker-align="right"
                                picker-position="auto"
                                :help="__('Only used when clicking Schedule.')"
                                :error="$errors->first('postScheduledAt')"
                            />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <x-ui.combobox
                                model="postCtaType"
                                name="postCtaType"
                                :label="__('CTA')"
                                :selected="$postCtaType"
                                :options="[
                                    ['value' => 'BOOK', 'label' => __('Book'), 'meta' => __('Send visitors to a booking page'), 'icon' => 'fa-calendar-check'],
                                    ['value' => 'ORDER', 'label' => __('Order'), 'meta' => __('Send visitors to order online'), 'icon' => 'fa-bag-shopping'],
                                    ['value' => 'SHOP', 'label' => __('Shop'), 'meta' => __('Send visitors to a shop page'), 'icon' => 'fa-store'],
                                    ['value' => 'LEARN_MORE', 'label' => __('Learn more'), 'meta' => __('Send visitors to more details'), 'icon' => 'fa-arrow-up-right-from-square'],
                                    ['value' => 'SIGN_UP', 'label' => __('Sign up'), 'meta' => __('Send visitors to a lead form'), 'icon' => 'fa-user-plus'],
                                    ['value' => 'CALL', 'label' => __('Call'), 'meta' => __('Use Google call action'), 'icon' => 'fa-phone'],
                                ]"
                                :placeholder="__('Choose CTA')"
                                :search-placeholder="__('Search CTA...')"
                                icon="fa-light fa-arrow-pointer"
                                :live="false"
                                :error="$errors->first('postCtaType')"
                            />
                            @if ($postCtaType === 'CALL' && $postType !== 'offer')
                                <div class="rounded-xl border px-4 py-3 text-sm leading-6" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent); color: var(--theme-muted-text-color);">
                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Call CTA') }}</p>
                                    <p class="mt-1">{{ __('Google uses the business phone number for Call, so no CTA URL is required.') }}</p>
                                </div>
                            @else
                                <x-ui.input
                                    wire:model="postCtaUrl"
                                    :label="$postType === 'offer' ? __('Redeem URL') : __('CTA URL')"
                                    placeholder="https://example.com"
                                    :help="$postType === 'offer' ? __('Use a public coupon, booking, or landing page URL.') : null"
                                    :error="$errors->first('postCtaUrl')"
                                />
                            @endif
                        </div>

                        <x-ui.input wire:model="postTitle" :label="__('Title')" :error="$errors->first('postTitle')" />
                        <x-ui.textarea
                            wire:model="postSummary"
                            name="postSummary"
                            :label="__('Summary')"
                            rows="5"
                            :placeholder="__('Write the Google Business post body...')"
                            :error="$errors->first('postSummary')"
                        >{{ $postSummary }}</x-ui.textarea>

                        <x-ui.image-picker
                            wire:model="postMediaUrl"
                            name="postMediaUrl"
                            :label="__('Image')"
                            :value="$postMediaUrl"
                            :preview="$postMediaUrl"
                            context="portal"
                            value-field="url"
                            layout="compact"
                            :button-label="__('Browse image')"
                            :dialog-title="__('Choose Google post image')"
                            :dialog-description="__('Select or upload an image from Files for this Google Business post.')"
                            :help="__('Google can only publish images from public URLs. Localhost/private images are skipped when publishing.')"
                            :error="$errors->first('postMediaUrl')"
                        />

                        @if ($postType === 'offer')
                            <div class="grid gap-4 md:grid-cols-2">
                                <x-ui.input wire:model="postCouponCode" :label="__('Coupon code')" :error="$errors->first('postCouponCode')" />
                                <x-ui.input
                                    wire:model="postTerms"
                                    :label="__('Terms')"
                                    :placeholder="__('Valid for first-time customers. Cannot be combined with other offers.')"
                                    :help="__('Use plain text terms. Do not paste video or social URLs here.')"
                                    :error="$errors->first('postTerms')"
                                />
                            </div>
                        @endif

                        @if (in_array($postType, ['offer', 'event'], true))
                            <div class="grid gap-4 md:grid-cols-2">
                                <x-ui.date-picker
                                    wire:model="postStartAt"
                                    name="postStartAt"
                                    :label="__('Start date')"
                                    :value="$postStartAt"
                                    :placeholder="__('Choose start date')"
                                    :error="$errors->first('postStartAt')"
                                />
                                <x-ui.date-picker
                                    wire:model="postEndAt"
                                    name="postEndAt"
                                    :label="__('End date')"
                                    :value="$postEndAt"
                                    :placeholder="__('Choose end date')"
                                    :error="$errors->first('postEndAt')"
                                />
                            </div>
                        @endif
                    </form>

                    <x-slot:footer>
                        <x-ui.button type="button" variant="outline" wire:click="closeGooglePostForm">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="submit" form="google-post-modal-form" variant="outline" wire:loading.attr="disabled" wire:target="saveGooglePost">
                            <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="saveGooglePost"></i>
                            <span wire:loading.remove wire:target="saveGooglePost">{{ $editingPostId ? __('Save changes') : __('Save draft') }}</span>
                            <span wire:loading wire:target="saveGooglePost">{{ __('Saving...') }}</span>
                        </x-ui.button>
                        <x-ui.button type="button" variant="outline" wire:click="saveGooglePost('schedule')" wire:loading.attr="disabled" wire:target="saveGooglePost">
                            <i class="fa-light fa-clock" wire:loading.remove wire:target="saveGooglePost"></i>
                            <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="saveGooglePost"></i>
                            <span wire:loading.remove wire:target="saveGooglePost">{{ __('Schedule') }}</span>
                            <span wire:loading wire:target="saveGooglePost">{{ __('Scheduling...') }}</span>
                        </x-ui.button>
                        <x-ui.button type="button" wire:click="saveGooglePost('publish')" wire:loading.attr="disabled" wire:target="saveGooglePost">
                            <i class="fa-light fa-paper-plane" wire:loading.remove wire:target="saveGooglePost"></i>
                            <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="saveGooglePost"></i>
                            <span wire:loading.remove wire:target="saveGooglePost">{{ __('Publish now') }}</span>
                            <span wire:loading wire:target="saveGooglePost">{{ __('Publishing...') }}</span>
                        </x-ui.button>
                    </x-slot:footer>
                </x-ui.modal>
            </div>
        @endif

        @if ($tab === 'auto_reply')
            <div class="space-y-5 p-5">
                <section class="overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                    <div class="flex flex-col gap-4 border-b px-5 py-4 xl:flex-row xl:items-end xl:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Auto reply rules') }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Generate AI drafts or automatically publish Google review replies from scalable rules.') }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-ui.button type="button" size="sm" variant="outline" x-on:click="$dispatch('google-auto-reply-logs-modal-open')">
                                <i class="fa-light fa-clock-rotate-left"></i>{{ __('Logs') }}
                            </x-ui.button>
                            <x-ui.button type="button" size="sm" wire:click="openAutoReplyRuleForm" wire:loading.attr="disabled" wire:target="openAutoReplyRuleForm">
                                <i class="fa-light fa-plus" wire:loading.remove wire:target="openAutoReplyRuleForm"></i>
                                <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="openAutoReplyRuleForm"></i>
                                <span wire:loading.remove wire:target="openAutoReplyRuleForm">{{ __('New rule') }}</span>
                                <span wire:loading wire:target="openAutoReplyRuleForm">{{ __('Opening...') }}</span>
                            </x-ui.button>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 border-b px-5 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                        @foreach ([
                            ['key' => 'all', 'label' => __('All rules'), 'count' => $autoReplyRuleSummary['total']],
                            ['key' => 'active', 'label' => __('Active'), 'count' => $autoReplyRuleSummary['active']],
                            ['key' => 'draft', 'label' => __('Draft'), 'count' => $autoReplyRuleSummary['draft']],
                        ] as $stat)
                            <button
                                type="button"
                                wire:click="$set('autoReplyRuleStatus', '{{ $stat['key'] }}')"
                                class="rounded-[0.8rem] border px-4 py-2 text-sm font-semibold transition"
                                style="{{ $autoReplyRuleStatus === $stat['key'] ? 'background-color: rgba(var(--theme-accent-rgb), .14); color: var(--theme-accent); border-color: rgba(var(--theme-accent-rgb), .28);' : 'color: var(--theme-muted-text-color); border-color: transparent;' }}"
                            >
                                {{ $stat['label'] }}
                                <span class="ml-2 rounded-full px-2 py-0.5 text-xs" style="background-color: rgba(var(--theme-accent-rgb), .10);">{{ format_number_locale($stat['count']) }}</span>
                            </button>
                        @endforeach
                        <button
                            type="button"
                            x-on:click="$dispatch('google-auto-reply-logs-modal-open')"
                            class="rounded-[0.8rem] border px-4 py-2 text-sm font-semibold transition"
                            style="color: var(--theme-muted-text-color); border-color: transparent;"
                        >
                            {{ __('Logs') }}
                            <span class="ml-2 rounded-full px-2 py-0.5 text-xs" style="background-color: rgba(var(--theme-accent-rgb), .10);">{{ format_number_locale($autoReplyLogs->count()) }}</span>
                        </button>
                    </div>

                    <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Rule queue') }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Rules stay readable in a table even when your workspace has many locations and conditions.') }}</p>
                        </div>
                    </div>

                    @if ($autoReplyRules->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead style="color: var(--theme-muted-text-color);">
                                    <tr>
                                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Rule') }}</th>
                                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Scope') }}</th>
                                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Conditions') }}</th>
                                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Reply') }}</th>
                                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                    @foreach ($autoReplyRules as $rule)
                                        <tr>
                                            <td class="px-5 py-4">
                                                <p class="max-w-sm truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $rule->name }}</p>
                                                @if ($rule->keyword)
                                                    <p class="mt-1 max-w-sm truncate text-xs" style="color: var(--theme-muted-text-color);">{{ __('Keyword') }}: {{ $rule->keyword }}</p>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4" style="color: var(--theme-muted-text-color);">
                                                <p class="max-w-[16rem] truncate">{{ $rule->location?->name ?: __('All locations') }}</p>
                                                <p class="mt-1 max-w-[16rem] truncate text-xs">{{ $rule->business?->name ?: __('All businesses') }}</p>
                                            </td>
                                            <td class="px-5 py-4" style="color: var(--theme-muted-text-color);">
                                                <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $this->ruleRatingLabel($rule->rating_condition) }}</p>
                                                <p class="mt-1 text-xs">{{ $this->ruleTextLabel($rule->text_condition) }}</p>
                                            </td>
                                            <td class="px-5 py-4" style="color: var(--theme-muted-text-color);">
                                                <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $this->ruleReplyModeLabel($rule->reply_mode) }}</p>
                                                <p class="mt-1 text-xs">{{ ucfirst((string) $rule->tone) }} &middot; {{ $rule->delay_minutes }} {{ __('min') }}</p>
                                            </td>
                                            <td class="px-5 py-4"><x-ui.badge :variant="$rule->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($rule->status) }}</x-ui.badge></td>
                                            <td class="px-5 py-4 text-right">
                                                <div class="inline-flex items-center gap-2">
                                                    <button type="button" wire:click="toggleAutoReplyRule({{ $rule->id }})" wire:loading.attr="disabled" wire:target="toggleAutoReplyRule({{ $rule->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5 disabled:pointer-events-none disabled:opacity-70" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('Toggle') }}">
                                                        <i class="fa-light fa-power-off" wire:loading.remove wire:target="toggleAutoReplyRule({{ $rule->id }})"></i>
                                                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="toggleAutoReplyRule({{ $rule->id }})"></i>
                                                    </button>
                                                    <x-ui.dialog :title="__('Delete auto reply rule')" :description="__('This removes the rule. Existing review replies and logs are kept for reporting.')" width="sm" dismissible>
                                                        <x-slot:trigger>
                                                            <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color); background-color: rgba(var(--theme-danger-color-rgb), .05);" title="{{ __('Delete') }}"><i class="fa-light fa-trash"></i></button>
                                                        </x-slot:trigger>
                                                        <x-slot:footer>
                                                            <div class="flex justify-end gap-3">
                                                                <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                                                <x-ui.button type="button" variant="danger" wire:click="deleteAutoReplyRule({{ $rule->id }})" wire:loading.attr="disabled" wire:target="deleteAutoReplyRule({{ $rule->id }})" x-on:click="open = false">
                                                                    <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="deleteAutoReplyRule({{ $rule->id }})"></i>
                                                                    <span wire:loading.remove wire:target="deleteAutoReplyRule({{ $rule->id }})">{{ __('Delete') }}</span>
                                                                    <span wire:loading wire:target="deleteAutoReplyRule({{ $rule->id }})">{{ __('Deleting...') }}</span>
                                                                </x-ui.button>
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
                    @else
                        <div class="p-8"><x-ui.empty icon="fa-light fa-wand-magic-sparkles" :title="__('No auto reply rules yet')" :description="__('Create rules to generate AI drafts or automatically reply to Google reviews based on rating, text, location, and delay.')" /></div>
                    @endif
                </section>

                <x-ui.modal
                    width="lg"
                    open-event="google-auto-reply-logs-modal-open"
                    close-event="google-auto-reply-logs-modal-close"
                    :title="__('Auto reply logs')"
                    :description="__('Review detected, rule matched, AI generated, draft saved, published, failed, and skipped events.')"
                    body-class="p-0"
                >
                    <div class="max-h-[58vh] overflow-y-auto">
                        @forelse ($autoReplyLogs as $log)
                            <div class="border-b px-5 py-4 last:border-b-0" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" style="background-color: {{ $log->publish_status === 'failed' ? 'rgba(var(--theme-danger-color-rgb), .08)' : 'rgba(var(--theme-accent-rgb), .10)' }}; color: {{ $log->publish_status === 'failed' ? 'var(--theme-danger-color)' : 'var(--theme-accent)' }};"><i class="fa-light {{ $log->publish_status === 'failed' ? 'fa-triangle-exclamation' : 'fa-wand-magic-sparkles' }}"></i></span>
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <x-ui.badge :variant="$log->publish_status === 'failed' ? 'danger' : ($log->publish_status === 'published' ? 'success' : 'neutral')">{{ ucfirst(str_replace('_', ' ', (string) $log->publish_status)) }}</x-ui.badge>
                                            <span class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ ucfirst(str_replace('_', ' ', (string) $log->action)) }}</span>
                                            <span class="text-xs" style="color: var(--theme-muted-text-color);">{{ $log->created_at?->diffForHumans() }}</span>
                                        </div>
                                        <p class="mt-2 truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $log->rule?->name ?: __('Rule check') }}</p>
                                        <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $log->review?->reviewer_name ?: __('Google review') }}</p>
                                        @if ($log->error_message)
                                            <p class="mt-1 line-clamp-2 text-xs leading-5 text-rose-600">{{ str($log->error_message)->limit(180) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8"><x-ui.empty icon="fa-light fa-clock-rotate-left" :title="__('No auto reply logs yet')" :description="__('Logs appear after review sync runs and rules are evaluated.')" /></div>
                        @endforelse
                    </div>
                    <x-slot:footer>
                        <x-ui.button type="button" variant="outline" x-on:click="$dispatch('google-auto-reply-logs-modal-close')">{{ __('Close') }}</x-ui.button>
                    </x-slot:footer>
                </x-ui.modal>

                <x-ui.modal
                    width="xl"
                    open-event="google-auto-reply-rule-modal-open"
                    close-event="google-auto-reply-rule-modal-close"
                    :title="__('New auto reply rule')"
                    :description="__('Match new Google reviews by location, rating, text, and choose whether AI should draft or publish the reply.')"
                    body-class="space-y-6 overflow-visible max-h-none px-6 py-6"
                >
                    <form id="google-auto-reply-rule-form" wire:submit="saveAutoReplyRule" class="space-y-5">
                        <x-ui.input wire:model="ruleName" name="ruleName" :label="__('Rule name')" :placeholder="__('Thank positive reviews')" :error="$errors->first('ruleName')" />
                        <div class="grid gap-4 md:grid-cols-2">
                            <x-ui.combobox model="ruleLocationId" name="ruleLocationId" :label="__('Google location')" :selected="$ruleLocationId ?? ''" :options="collect([['value' => '', 'label' => __('All managed locations'), 'meta' => __('Every managed Google location'), 'icon' => 'fa-location-dot']])->merge($managedLocations->map(fn ($location) => ['value' => (string) $location->id, 'label' => $location->name, 'meta' => $location->business?->name ?: __('Not mapped'), 'icon' => 'fa-location-dot']))->values()->all()" :placeholder="__('All managed locations')" :search-placeholder="__('Search Google location...')" icon="fa-light fa-location-dot" />
                            <x-ui.combobox model="ruleBusinessId" name="ruleBusinessId" :label="__('Business')" :selected="$ruleBusinessId ?? ''" :options="collect([['value' => '', 'label' => __('All businesses'), 'meta' => __('Any mapped business'), 'icon' => 'fa-store']])->merge($businesses->map(fn ($business) => ['value' => (string) $business->id, 'label' => $business->name, 'meta' => __('Business'), 'icon' => 'fa-store']))->values()->all()" :placeholder="__('All businesses')" :search-placeholder="__('Search business...')" icon="fa-light fa-store" />
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <x-ui.combobox model="ruleRatingCondition" name="ruleRatingCondition" :label="__('Rating condition')" :selected="$ruleRatingCondition" :options="[['value' => 'any', 'label' => __('Any rating'), 'icon' => 'fa-star'], ['value' => 'five', 'label' => __('5 stars'), 'icon' => 'fa-star'], ['value' => 'positive', 'label' => __('4-5 stars'), 'icon' => 'fa-star-half-stroke'], ['value' => 'low', 'label' => __('3 stars or below'), 'icon' => 'fa-triangle-exclamation'], ['value' => 'one_two', 'label' => __('1-2 stars'), 'icon' => 'fa-triangle-exclamation'], ['value' => 'custom', 'label' => __('Custom condition'), 'icon' => 'fa-sliders']]" :placeholder="__('Rating condition')" :search-placeholder="__('Search rating rule...')" icon="fa-light fa-star" />
                            <x-ui.combobox model="ruleTextCondition" name="ruleTextCondition" :label="__('Text condition')" :selected="$ruleTextCondition" :options="[['value' => 'any', 'label' => __('Any review'), 'icon' => 'fa-message-lines'], ['value' => 'with_text', 'label' => __('Only reviews with text'), 'icon' => 'fa-message-text'], ['value' => 'without_text', 'label' => __('Only reviews without text'), 'icon' => 'fa-message-xmark'], ['value' => 'contains', 'label' => __('Review contains keyword'), 'icon' => 'fa-magnifying-glass'], ['value' => 'not_contains', 'label' => __('Review does not contain keyword'), 'icon' => 'fa-ban']]" :placeholder="__('Text condition')" :search-placeholder="__('Search text condition...')" icon="fa-light fa-message-lines" />
                        </div>
                        @if ($ruleRatingCondition === 'custom')
                            <div class="grid gap-4 md:grid-cols-2">
                                <x-ui.combobox model="ruleCustomRatingOperator" name="ruleCustomRatingOperator" :label="__('Custom rating rule')" :selected="$ruleCustomRatingOperator" :options="[['value' => '>=', 'label' => __('Rating >='), 'icon' => 'fa-greater-than-equal'], ['value' => '<=', 'label' => __('Rating <='), 'icon' => 'fa-less-than-equal'], ['value' => '=', 'label' => __('Rating ='), 'icon' => 'fa-equals']]" icon="fa-light fa-sliders" />
                                <x-ui.combobox model="ruleCustomRatingValue" name="ruleCustomRatingValue" :label="__('Value')" :selected="$ruleCustomRatingValue" :options="collect(range(5, 1))->map(fn ($rating) => ['value' => (string) $rating, 'label' => $rating.' '.__('stars'), 'icon' => 'fa-star'])->values()->all()" icon="fa-light fa-star" />
                            </div>
                        @endif
                        @if (in_array($ruleTextCondition, ['contains', 'not_contains'], true))
                            <x-ui.input wire:model="ruleKeyword" name="ruleKeyword" :label="__('Keyword')" :placeholder="__('discount, support, price...')" :error="$errors->first('ruleKeyword')" />
                        @endif
                        <div class="grid gap-4 md:grid-cols-2">
                            <x-ui.combobox model="ruleReplyMode" name="ruleReplyMode" :label="__('Reply mode')" :selected="$ruleReplyMode" :options="[['value' => 'draft', 'label' => __('Save AI Draft'), 'meta' => __('Generate and wait for approval'), 'icon' => 'fa-pen-field'], ['value' => 'auto_publish', 'label' => __('Auto Publish to Google'), 'meta' => __('Post directly to Google'), 'icon' => 'fa-paper-plane'], ['value' => 'template', 'label' => __('Use Template Reply'), 'meta' => __('Fixed text without AI'), 'icon' => 'fa-file-lines']]" :search-placeholder="__('Search reply mode...')" icon="fa-light fa-reply" />
                            <x-ui.combobox model="ruleTone" name="ruleTone" :label="__('Tone')" :selected="$ruleTone" :options="$autoReplyToneOptions" :search-placeholder="__('Search tone...')" icon="fa-light fa-sliders" />
                        </div>
                        @if ($ruleReplyMode === 'auto_publish')
                            <div class="rounded-xl border px-4 py-3 text-xs leading-5" style="border-color: rgba(var(--theme-warning-color-rgb), .32); background-color: rgba(var(--theme-warning-color-rgb), .08); color: var(--theme-header-text-color);"><span class="font-semibold">{{ __('Auto-publish warning:') }}</span> {{ __('Replies will be posted directly to Google. Use draft mode for low-score or sensitive reviews.') }}</div>
                        @endif
                        @if ($ruleReplyMode === 'template')
                            <x-ui.textarea wire:model="ruleTemplateReply" name="ruleTemplateReply" rows="4" :label="__('Template reply')" :placeholder="__('Thank you for your feedback. We appreciate your support and hope to see you again.')" :error="$errors->first('ruleTemplateReply')" />
                        @endif
                        <div class="grid gap-4 md:grid-cols-3">
                            <x-ui.combobox model="ruleLanguage" name="ruleLanguage" :label="__('Language')" :selected="$ruleLanguage" :options="$autoReplyLanguageOptions" :search-placeholder="__('Search language...')" icon="fa-light fa-language" />
                            <x-ui.input wire:model="ruleDelayMinutes" type="number" min="0" name="ruleDelayMinutes" :label="__('Delay minutes')" />
                            <x-ui.combobox model="ruleStatus" name="ruleStatus" :label="__('Status')" :selected="$ruleStatus" :options="[['value' => 'active', 'label' => __('Active'), 'icon' => 'fa-circle-check'], ['value' => 'draft', 'label' => __('Draft'), 'icon' => 'fa-file-lines']]" icon="fa-light fa-toggle-on" />
                        </div>
                    </form>
                    <x-slot:footer>
                        <x-ui.button type="button" variant="outline" wire:click="closeAutoReplyRuleForm">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="submit" form="google-auto-reply-rule-form" wire:loading.attr="disabled" wire:target="saveAutoReplyRule">
                            <i wire:loading.remove wire:target="saveAutoReplyRule" class="fa-light fa-floppy-disk"></i>
                            <i wire:loading wire:target="saveAutoReplyRule" class="fa-light fa-spinner-third fa-spin"></i>
                            <span wire:loading.remove wire:target="saveAutoReplyRule">{{ __('Save rule') }}</span>
                            <span wire:loading wire:target="saveAutoReplyRule">{{ __('Saving...') }}</span>
                        </x-ui.button>
                    </x-slot:footer>
                </x-ui.modal>
            </div>
        @endif

        @if ($tab === 'analytics')
            <div class="space-y-5 p-5">
                <div class="flex flex-col gap-4 rounded-[1rem] border p-4 lg:flex-row lg:items-end lg:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .58); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), .08), transparent 38%), var(--theme-surface-base);">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Google Review Analytics') }}</p>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Track review trends, reply performance, rating health, and location performance from managed Google locations.') }}</p>
                    </div>
                    <div class="grid w-full gap-3 sm:grid-cols-2 lg:w-auto lg:grid-cols-4">
                        <x-ui.combobox
                            model="analyticsLocation"
                            name="analyticsLocation"
                            :label="__('Location')"
                            :selected="$analyticsLocation"
                            :options="collect([['value' => 'all', 'label' => __('All managed'), 'meta' => __('Every managed Google location'), 'icon' => 'fa-location-dot']])->merge($managedLocations->map(fn ($location) => ['value' => (string) $location->id, 'label' => $location->name, 'meta' => $location->business?->name ?: __('Not mapped'), 'icon' => 'fa-location-dot']))->values()->all()"
                            :placeholder="__('All managed')"
                            :search-placeholder="__('Search location...')"
                            icon="fa-light fa-location-dot"
                        />
                        <x-ui.combobox
                            model="analyticsRange"
                            name="analyticsRange"
                            :label="__('Date range')"
                            :selected="$analyticsRange"
                            :options="[['value' => 'all', 'label' => __('All time'), 'icon' => 'fa-calendar-days'], ['value' => '7', 'label' => __('Last 7 days'), 'icon' => 'fa-calendar-week'], ['value' => '30', 'label' => __('Last 30 days'), 'icon' => 'fa-calendar'], ['value' => '90', 'label' => __('Last 90 days'), 'icon' => 'fa-calendar-range']]"
                            :placeholder="__('All time')"
                            :search-placeholder="__('Search date range...')"
                            icon="fa-light fa-calendar-days"
                        />
                        <x-ui.combobox
                            model="analyticsRating"
                            name="analyticsRating"
                            :label="__('Rating')"
                            :selected="$analyticsRating"
                            :options="collect([['value' => 'all', 'label' => __('All ratings'), 'icon' => 'fa-star']])->merge(collect(range(5, 1))->map(fn ($rating) => ['value' => (string) $rating, 'label' => $rating.' '.__('stars'), 'icon' => 'fa-star']))->values()->all()"
                            :placeholder="__('All ratings')"
                            :search-placeholder="__('Search rating...')"
                            icon="fa-light fa-star"
                        />
                        <x-ui.combobox
                            model="analyticsReplyStatus"
                            name="analyticsReplyStatus"
                            :label="__('Reply status')"
                            :selected="$analyticsReplyStatus"
                            :options="[['value' => 'all', 'label' => __('All'), 'icon' => 'fa-inbox'], ['value' => 'not_replied', 'label' => __('Not replied'), 'icon' => 'fa-reply-clock'], ['value' => 'draft', 'label' => __('Draft'), 'icon' => 'fa-pen-field'], ['value' => 'replied', 'label' => __('Replied'), 'icon' => 'fa-circle-check']]"
                            :placeholder="__('All')"
                            :search-placeholder="__('Search reply status...')"
                            icon="fa-light fa-reply"
                        />
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['label' => __('Managed locations'), 'value' => $analyticsSummary['managed_locations'], 'icon' => 'fa-location-check', 'tone' => 'var(--theme-accent)'],
                        ['label' => __('Total reviews'), 'value' => $reviewAnalytics['total'], 'icon' => 'fa-star', 'tone' => '#d97706'],
                        ['label' => __('Average rating'), 'value' => $reviewAnalytics['average_rating'], 'icon' => 'fa-ranking-star', 'tone' => 'var(--theme-success-color)'],
                        ['label' => __('Reply rate'), 'value' => $reviewAnalytics['reply_rate'].'%', 'icon' => 'fa-reply', 'tone' => '#0ea5e9'],
                        ['label' => __('Not replied'), 'value' => $reviewAnalytics['not_replied'], 'icon' => 'fa-reply-clock', 'tone' => 'var(--theme-warning-color)'],
                        ['label' => __('Low-score'), 'value' => $reviewAnalytics['low_score'], 'icon' => 'fa-triangle-exclamation', 'tone' => 'var(--theme-danger-color)'],
                        ['label' => __('Auto replies'), 'value' => $reviewAnalytics['auto_replies'], 'icon' => 'fa-wand-magic-sparkles', 'tone' => 'var(--theme-accent)'],
                        ['label' => __('Failed replies'), 'value' => $reviewAnalytics['failed_replies'], 'icon' => 'fa-circle-xmark', 'tone' => 'var(--theme-danger-color)'],
                    ] as $card)
                        <div class="rounded-xl border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ $card['label'] }}</p>
                                    <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ is_numeric($card['value']) ? format_number_locale((float) $card['value']) : $card['value'] }}</p>
                                </div>
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: color-mix(in srgb, {{ $card['tone'] }} 10%, white); color: {{ $card['tone'] }};"><i class="fa-light {{ $card['icon'] }}"></i></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($reviewAnalytics['total'] === 0)
                    <div class="rounded-[1rem] border px-5 py-12 text-center" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No Google review analytics yet') }}</p>
                        <p class="mx-auto mt-2 max-w-xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Sync reviews from your managed Google locations to see rating trends, reply performance, low-score reviews, and location performance.') }}</p>
                        <div class="mt-4"><x-ui.button type="button" wire:click="$set('tab', 'locations')"><i class="fa-light fa-location-dot"></i>{{ __('Open locations') }}</x-ui.button></div>
                    </div>
                @else
                    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(20rem,0.75fr)]">
                        <x-ui.chart
                            id="google-review-trend"
                            :title="__('Review Trend')"
                            :description="__('New reviews, replied/drafted reviews, and low-score reviews over time.')"
                            type="areaspline"
                            :categories="$reviewAnalytics['trend_categories']"
                            :series="$reviewAnalytics['trend_series']"
                            :legend="true"
                            height="340"
                        />

                        <x-ui.chart
                            id="google-rating-distribution"
                            :title="__('Rating Distribution')"
                            :description="__('Breakdown of synced Google reviews by star rating.')"
                            type="bar"
                            :categories="$reviewAnalytics['rating_distribution']->pluck('rating')->map(fn ($rating) => $rating.' '.__('stars'))->all()"
                            :series="[['name' => __('Reviews'), 'data' => $reviewAnalytics['rating_distribution']->pluck('count')->all()]]"
                            height="340"
                        />
                    </div>

                    <div class="grid gap-5 xl:grid-cols-[minmax(20rem,0.8fr)_minmax(0,1.2fr)]">
                        <div class="rounded-[1rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Reply Performance') }}</p>
                                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('How the review reply queue is moving.') }}</p>
                                </div>
                                <x-ui.badge :variant="$reviewAnalytics['reply_rate'] >= 70 ? 'success' : 'warning'">{{ $reviewAnalytics['reply_rate'] }}%</x-ui.badge>
                            </div>
                            <div class="mt-5 space-y-4">
                                @foreach ([
                                    ['label' => __('Replied'), 'value' => $reviewAnalytics['replied'], 'tone' => 'var(--theme-success-color)'],
                                    ['label' => __('Draft generated'), 'value' => $reviewAnalytics['drafts'], 'tone' => 'var(--theme-accent)'],
                                    ['label' => __('Not replied'), 'value' => $reviewAnalytics['not_replied'], 'tone' => 'var(--theme-warning-color)'],
                                    ['label' => __('Failed'), 'value' => $reviewAnalytics['failed_replies'], 'tone' => 'var(--theme-danger-color)'],
                                ] as $row)
                                    @php $percent = $reviewAnalytics['total'] > 0 ? max(4, (int) round(($row['value'] / $reviewAnalytics['total']) * 100)) : 4; @endphp
                                    <div>
                                        <div class="flex items-center justify-between gap-3 text-sm">
                                            <span style="color: var(--theme-muted-text-color);">{{ $row['label'] }}</span>
                                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ $row['value'] }}</span>
                                        </div>
                                        <div class="mt-2 h-2 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), .36);">
                                            <div class="h-full rounded-full" style="width: {{ $percent }}%; background-color: {{ $row['tone'] }};"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-5 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Auto reply success rate') }}: {{ $reviewAnalytics['auto_reply_success_rate'] }}%</p>
                        </div>

                        <div class="rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                            <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Top Locations by Reviews') }}</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead style="color: var(--theme-muted-text-color);">
                                        <tr>
                                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.14em]">{{ __('Location') }}</th>
                                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.14em]">{{ __('Reviews') }}</th>
                                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.14em]">{{ __('Avg') }}</th>
                                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.14em]">{{ __('Open') }}</th>
                                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.14em]">{{ __('Low') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                        @forelse ($reviewAnalytics['top_locations'] as $location)
                                            <tr>
                                                <td class="px-5 py-3">
                                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $location['name'] }}</p>
                                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $location['business'] ?: __('Not mapped') }}</p>
                                                </td>
                                                <td class="px-5 py-3 font-semibold" style="color: var(--theme-header-text-color);">{{ $location['reviews'] }}</td>
                                                <td class="px-5 py-3" style="color: var(--theme-muted-text-color);">{{ $location['average'] }}</td>
                                                <td class="px-5 py-3" style="color: var(--theme-muted-text-color);">{{ $location['not_replied'] }}</td>
                                                <td class="px-5 py-3" style="color: var(--theme-muted-text-color);">{{ $location['low_score'] }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="px-5 py-10 text-center text-sm" style="color: var(--theme-muted-text-color);">{{ __('No location review data yet.') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="grid items-start gap-5 xl:grid-cols-2">
                        <div class="rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                            <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Low-score Review Monitor') }}</p>
                            </div>
                            <div class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                @forelse ($reviewAnalytics['low_score_reviews'] as $review)
                                    <div class="px-5 py-4">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $review->reviewer_name ?: __('Google user') }}</p>
                                            <x-ui.badge variant="danger">{{ $review->rating }} {{ __('stars') }}</x-ui.badge>
                                        </div>
                                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $review->comment ?: __('No written comment.') }}</p>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <x-ui.button type="button" size="sm" variant="outline" wire:click="generateAiReply({{ $review->id }})"><i class="fa-light fa-wand-magic-sparkles"></i>{{ __('Generate AI Reply') }}</x-ui.button>
                                            <x-ui.button type="button" size="sm" wire:click="$set('tab', 'reviews')"><i class="fa-light fa-eye"></i>{{ __('View Review') }}</x-ui.button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-5 py-6">
                                        <div class="flex items-start gap-3 rounded-2xl border p-4" style="border-color: rgba(var(--theme-success-color-rgb), .22); background: linear-gradient(135deg, rgba(var(--theme-success-color-rgb), .08), transparent 45%), var(--theme-surface-base);">
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);"><i class="fa-light fa-shield-check"></i></span>
                                            <div>
                                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No low-score reviews need attention') }}</p>
                                                <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Reviews rated 3 stars or below will appear here when they need a response.') }}</p>
                                                <button type="button" wire:click="$set('tab', 'reviews')" class="mt-3 inline-flex h-9 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);">
                                                    <i class="fa-light fa-star"></i>{{ __('Open reviews') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                            <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recent Review Activity') }}</p>
                            </div>
                            <div class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                @foreach ($reviews->take(6) as $review)
                                    <div class="flex items-start gap-3 px-5 py-4">
                                        <span class="mt-0.5 flex h-9 w-9 items-center justify-center rounded-xl" style="background-color: color-mix(in srgb, var(--theme-accent) 10%, white); color: var(--theme-accent);"><i class="fa-light fa-star"></i></span>
                                        <div>
                                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Review synced from :location', ['location' => $review->googleLocation?->name ?: __('Google location')]) }}</p>
                                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $review->reviewer_name ?: __('Google user') }} &middot; {{ $review->rating }} {{ __('stars') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @if (false)
            <div class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                @forelse ($autoReplyLogs as $log)
                    <div class="grid gap-3 px-5 py-4 lg:grid-cols-[1fr_12rem_12rem]">
                        <div>
                            <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $log->rule?->name ?: __('Auto reply') }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $log->review?->reviewer_name ?: __('Google review') }} &middot; {{ $log->created_at?->diffForHumans() }}</p>
                            @if ($log->error_message)
                                <p class="mt-2 text-xs" style="color: var(--theme-danger-color);">{{ $log->error_message }}</p>
                            @endif
                        </div>
                        <x-ui.badge :variant="$log->publish_status === 'failed' ? 'danger' : 'success'">{{ $log->publish_status }}</x-ui.badge>
                        <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ $log->action }}</p>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center text-sm" style="color: var(--theme-muted-text-color);">{{ __('No Google Business automation logs yet.') }}</div>
                @endforelse
            </div>
        @endif

        @if (false)
            <div class="p-5">
                <div class="rounded-xl border p-4 lg:col-span-2" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Connected Google accounts') }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Accounts stay here only for reconnect, sync, or disconnect. Day-to-day work starts from Locations.') }}</p>
                        </div>
                        <x-ui.button type="button" variant="outline" size="sm" onclick="window.location.href='{{ route('portal.google-business.connect') }}'" :disabled="! $configured"><i class="fa-brands fa-google"></i>{{ __('Connect') }}</x-ui.button>
                    </div>
                    <div class="mt-4 divide-y rounded-xl border" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        @forelse ($connections as $connection)
                            <div class="flex flex-col gap-3 px-4 py-3 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $connection->google_account_email ?: __('Google account') }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $connection->locations_count }} {{ __('locations available') }} &middot; {{ ucfirst($connection->status) }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.badge :variant="$connection->auto_sync ? 'success' : 'neutral'">{{ $connection->auto_sync ? __('Auto sync on') : __('Auto sync off') }}</x-ui.badge>
                                    <button type="button" wire:click="toggleConnectionAutoSync({{ $connection->id }})" class="inline-flex h-9 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color);"><i class="fa-light fa-clock-rotate-left"></i>{{ __('Auto sync') }}</button>
                                    <button type="button" wire:click="syncConnection({{ $connection->id }})" class="inline-flex h-9 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color);"><i class="fa-light fa-rotate"></i>{{ __('Refresh locations') }}</button>
                                    <button type="button" wire:click="disconnect({{ $connection->id }})" wire:confirm="{{ __('Disconnect this Google account?') }}" class="inline-flex h-9 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color);"><i class="fa-light fa-trash"></i>{{ __('Disconnect') }}</button>
                                </div>
                            </div>
                        @empty
                            <div class="px-4 py-8 text-center text-sm" style="color: var(--theme-muted-text-color);">{{ __('No Google account connected yet.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </section>

    @if ($selectedLocation)
        <template x-teleport="body">
            <div x-cloak x-show="mappingOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="mappingOpen = false">
                <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="mappingOpen = false"></div>
                <div class="relative flex min-h-full items-start justify-center">
                    <section x-show="mappingOpen" x-transition.opacity.scale.95 class="relative w-full max-w-3xl overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .72); background-color: var(--theme-surface-overlay);">
                        <div class="flex items-start justify-between gap-4 border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                            <div>
                                <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Map Google location') }}</p>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $selectedLocation->name }}</p>
                            </div>
                            <button type="button" x-on:click="mappingOpen = false" class="flex h-10 w-10 items-center justify-center rounded-xl" style="color: var(--theme-muted-text-color);"><i class="fa-light fa-xmark"></i></button>
                        </div>

                        <div class="grid gap-5 p-5 lg:grid-cols-[1fr_1.1fr]">
                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: var(--theme-surface-base);">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Google location') }}</p>
                                <p class="mt-3 font-semibold" style="color: var(--theme-header-text-color);">{{ $selectedLocation->name }}</p>
                                <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $selectedLocation->address ?: $selectedLocation->google_location_id }}</p>
                            </div>

                            <div class="space-y-4">
                                <x-ui.select wire:model="mapBusinessId" name="mapBusinessId" :label="__('LocalBoost business')" :error="$errors->first('mapBusinessId')">
                                    <option value="">{{ __('Choose business') }}</option>
                                    @foreach ($businesses as $business)
                                        <option value="{{ $business->id }}">{{ $business->name }}</option>
                                    @endforeach
                                </x-ui.select>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    <x-ui.button type="button" wire:click="mapLocation"><i class="fa-light fa-link"></i>{{ __('Map location') }}</x-ui.button>
                                    <x-ui.button type="button" variant="outline" wire:click="requestCreateBusinessFromLocation({{ $selectedLocation->id }})"><i class="fa-light fa-store"></i>{{ __('Create business') }}</x-ui.button>
                                </div>
                                <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62);">
                                    <p class="mb-3 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Sync options') }}</p>
                                    <div class="grid gap-2 sm:grid-cols-2">
                                        <x-ui.checkbox wire:click="toggleLocationSync({{ $selectedLocation->id }}, 'sync_business_info')" :checked="$selectedLocation->sync_business_info" :label="__('Business info')" />
                                        <x-ui.checkbox wire:click="toggleLocationSync({{ $selectedLocation->id }}, 'sync_hours')" :checked="$selectedLocation->sync_hours" :label="__('Opening hours')" />
                                        <x-ui.checkbox wire:click="toggleLocationSync({{ $selectedLocation->id }}, 'sync_reviews')" :checked="$selectedLocation->sync_reviews" :label="__('Reviews')" />
                                        <x-ui.checkbox wire:click="toggleLocationSync({{ $selectedLocation->id }}, 'auto_reply_enabled')" :checked="$selectedLocation->auto_reply_enabled" :label="__('Auto reply')" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </template>
    @endif

    <template x-teleport="body">
        <div x-cloak x-show="duplicateImportOpen" class="fixed inset-0 z-[125] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="$wire.cancelDuplicateImport()">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="$wire.cancelDuplicateImport()"></div>
            <div class="relative flex min-h-full items-center justify-center">
                <section x-show="duplicateImportOpen" x-transition.opacity.scale.95 class="relative w-full max-w-xl overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .72); background-color: var(--theme-surface-overlay);">
                    <div class="flex items-start gap-4 border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" style="background-color: color-mix(in srgb, var(--theme-warning-color) 12%, white); color: var(--theme-warning-color);"><i class="fa-light fa-triangle-exclamation"></i></span>
                        <div>
                            <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Possible duplicate business') }}</p>
                            <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('A similar LocalBoost business already exists. Importing will create another business record. Use Map if you want to connect this Google location to an existing business.') }}</p>
                        </div>
                    </div>

                    <div class="space-y-3 p-5">
                        @foreach ($duplicateBusinessMatches as $match)
                            <div class="rounded-xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                                <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $match['name'] ?: __('Existing business') }}</p>
                                <div class="mt-2 grid gap-1 text-xs" style="color: var(--theme-muted-text-color);">
                                    <p>{{ $match['phone'] ?: __('No phone') }}</p>
                                    <p>{{ $match['website'] ?: __('No website') }}</p>
                                    <p>{{ $match['address'] ?: __('No address') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex flex-col-reverse gap-2 border-t px-5 py-4 sm:flex-row sm:justify-end" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                        <x-ui.button type="button" variant="outline" x-on:click="$wire.cancelDuplicateImport()">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="button" wire:click="openMapping({{ $duplicateImportLocationId ?: 0 }})" x-on:click="duplicateImportOpen = false" :disabled="! $duplicateImportLocationId"><i class="fa-light fa-link"></i>{{ __('Map instead') }}</x-ui.button>
                        <x-ui.button type="button" variant="danger" wire:click="confirmCreateDuplicateBusiness" x-on:click="duplicateImportOpen = false"><i class="fa-light fa-store"></i>{{ __('Create duplicate anyway') }}</x-ui.button>
                    </div>
                </section>
            </div>
        </div>
    </template>
</div>
