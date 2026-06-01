<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 36%),
        linear-gradient(35deg, rgba(var(--theme-success-color-rgb),0.09), transparent 38%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-6 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <a href="{{ route('portal.businesses.show', $business) }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold" style="color: var(--theme-muted-text-color);">
                    <i class="fa-light fa-arrow-left"></i>{{ $business->name }}
                </a>
                <h1 class="mt-4 text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('Reviews') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ __('Track customer ratings, low-score feedback, and public review clicks from your Review Booster campaigns.') }}
                </p>
            </div>

            <div class="rounded-[1.15rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Review health') }}</p>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($totalReviews) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Responses') }}</p>
                    </div>
                    <div class="rounded-2xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                        <p class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ $averageRating ?: '0.0' }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Avg rating') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('appbusinessprofiles::partials.business-tabs', ['business' => $business, 'active' => 'reviews'])

    @if ($statusMessage)
        <div class="rounded-xl border px-4 py-3 text-sm font-semibold" style="border-color: rgba(var(--theme-success-color-rgb),0.22); background-color: rgba(var(--theme-success-color-rgb),0.08); color: var(--theme-success-color);">
            {{ $statusMessage }}
        </div>
    @endif

    <section class="grid gap-3 md:grid-cols-3">
        @foreach ([
            ['label' => __('Ratings Captured'), 'value' => $totalReviews, 'hint' => __('Customer ratings collected'), 'icon' => 'fa-light fa-star', 'tone' => 'accent'],
            ['label' => __('Low-score feedback'), 'value' => $lowScoreCount, 'hint' => __('Needs attention'), 'icon' => 'fa-light fa-message-exclamation', 'tone' => 'warning'],
            ['label' => __('Google Review Clicks'), 'value' => max($totalReviews - $lowScoreCount, 0), 'hint' => __('Positive customers sent to Google'), 'icon' => 'fa-light fa-arrow-up-right', 'tone' => 'success'],
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
            <article class="group relative overflow-hidden rounded-[1.2rem] border p-5 transition duration-200 hover:shadow-[0_18px_50px_-42px_rgba(var(--theme-accent-rgb),0.85)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background:
                linear-gradient(145deg, rgba({{ $toneRgb }},0.09), transparent 38%),
                color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[2rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($metric['value']) }}</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $metric['hint'] }}</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background-color: rgba({{ $toneRgb }},0.12); color: {{ $toneColor }};">
                        <i class="{{ $metric['icon'] }}"></i>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-[1.3rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-0 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="min-w-0">
                <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68); background:
                    linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.055), transparent 42%),
                    color-mix(in srgb, var(--theme-surface-base) 86%, transparent);">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Review inbox') }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Ratings and internal feedback captured by Review Booster.') }}</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-[minmax(0,18rem)_8rem]">
                            <div class="relative">
                                <i class="fa-light fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm" style="color: var(--theme-muted-text-color);"></i>
                                <input type="search" wire:model.live.debounce.300ms="search" class="h-11 w-full rounded-xl border pl-10 pr-4 text-sm outline-none transition focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);" placeholder="{{ __('Search feedback...') }}">
                            </div>
                            <x-ui.select wire:model.live="perPage">
                                <option value="10">{{ __('10 / page') }}</option>
                                <option value="25">{{ __('25 / page') }}</option>
                                <option value="50">{{ __('50 / page') }}</option>
                            </x-ui.select>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-2 overflow-x-auto">
                        @foreach ([
                            'all' => __('All'),
                            'positive' => __('Positive'),
                            'low_score' => __('Low-score'),
                            'sent_to_google' => __('Sent to Google'),
                            'needs_reply' => __('Needs Reply'),
                            'resolved' => __('Resolved'),
                        ] as $value => $label)
                            <button type="button" wire:click="$set('filter', '{{ $value }}')" class="whitespace-nowrap rounded-full border px-3 py-1.5 text-xs font-semibold transition" style="{{ $filter === $value
                                ? 'border-color: rgba(var(--theme-accent-rgb),0.2); background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);'
                                : 'border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent); color: var(--theme-muted-text-color);'
                            }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                @if ($feedbacks->count() > 0)
                    <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead style="background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-muted-text-color);">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Customer') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Rating') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Campaign') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Feedback') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Created') }}</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            @foreach ($feedbacks as $feedback)
                                @php
                                    $feedbackStatus = $hasFeedbackStatusColumn ? (string) ($feedback->status ?: 'new') : 'new';
                                    $statusLabel = match ($feedbackStatus) {
                                        'replied' => __('Replied'),
                                        'resolved' => __('Resolved'),
                                        default => $feedback->rating <= 3 ? __('Needs reply') : __('Review intent'),
                                    };
                                    $statusVariant = match ($feedbackStatus) {
                                        'replied', 'resolved' => 'success',
                                        default => $feedback->rating <= 3 ? 'warning' : 'success',
                                    };
                                @endphp
                                <tr class="transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)]">
                                    <td class="px-5 py-4">
                                        <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $feedback->customer_name ?: __('Guest') }}</p>
                                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $feedback->customer_phone ?: $feedback->customer_email ?: __('No contact') }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ $feedback->rating }}/5</span>
                                    </td>
                                    <td class="max-w-[14rem] px-5 py-4">
                                        <p class="truncate" style="color: var(--theme-muted-text-color);">{{ $feedback->campaign?->name ?: __('Campaign removed') }}</p>
                                    </td>
                                    <td class="max-w-[22rem] px-5 py-4">
                                        <p class="line-clamp-2" style="color: var(--theme-muted-text-color);">{{ $feedback->message ?: ($feedback->rating >= 4 ? __('Positive rating routed to public review link.') : __('No message provided.')) }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <x-ui.badge :variant="$statusVariant">{{ $statusLabel }}</x-ui.badge>
                                    </td>
                                    <td class="px-5 py-4 text-xs" style="color: var(--theme-muted-text-color);">{{ format_date_locale($feedback->created_at) }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-ui.button
                                                href="{{ route('portal.ai-studio.review-reply', ['feedback_id' => $feedback->id]) }}"
                                                wire:navigate
                                                size="sm"
                                                variant="secondary"
                                            >
                                                <i class="fa-light fa-wand-magic-sparkles"></i>{{ __('AI Reply') }}
                                            </x-ui.button>

                                            @if ($hasFeedbackStatusColumn)
                                                <button
                                                    type="button"
                                                    wire:click="markReplied({{ $feedback->id }})"
                                                    class="inline-flex h-9 items-center justify-center rounded-xl border px-3 text-sm font-semibold transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.06)] disabled:opacity-50"
                                                    style="border-color: rgba(var(--theme-border-color-rgb),0.72); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);"
                                                    @disabled($feedbackStatus === 'replied' || $feedbackStatus === 'resolved')
                                                >
                                                    {{ __('Replied') }}
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click="markResolved({{ $feedback->id }})"
                                                    class="inline-flex h-9 items-center justify-center rounded-xl border px-3 text-sm font-semibold transition hover:bg-[color:rgba(var(--theme-success-color-rgb),0.08)] disabled:opacity-50"
                                                    style="border-color: rgba(var(--theme-success-color-rgb),0.24); color: var(--theme-success-color); background-color: rgba(var(--theme-success-color-rgb),0.06);"
                                                    @disabled($feedbackStatus === 'resolved')
                                                >
                                                    {{ __('Resolve') }}
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Showing') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($feedbacks->firstItem()) }}</span> - <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($feedbacks->lastItem()) }}</span> {{ __('of') }} <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($feedbacks->total()) }}</span></p>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="previousPage" @disabled($feedbacks->onFirstPage()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);"><i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}</button>
                        <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb), .08); color: var(--theme-accent);">{{ __('Page') }} {{ format_number_locale($feedbacks->currentPage()) }} / {{ format_number_locale($feedbacks->lastPage()) }}</span>
                        <button type="button" wire:click="nextPage" @disabled(! $feedbacks->hasMorePages()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">{{ __('Next') }}<i class="fa-light fa-arrow-right"></i></button>
                    </div>
                    </div>
                @else
                    <div class="p-5 sm:p-6">
                        <div class="grid min-h-[18rem] gap-6 rounded-[1.15rem] border p-6 lg:grid-cols-[minmax(0,1fr)_19rem] lg:items-center" style="border-color: rgba(var(--theme-border-color-rgb),0.54); background:
                            radial-gradient(circle at top right, rgba(var(--theme-accent-rgb),0.12), transparent 34%),
                            linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.065), transparent 48%),
                            color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                            <div>
                                <div class="flex h-16 w-16 items-center justify-center rounded-[1.15rem] border shadow-[0_16px_40px_-34px_rgba(var(--theme-accent-rgb),0.9)]" style="border-color: rgba(var(--theme-accent-rgb),0.18); background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                                    <i class="fa-light fa-star text-2xl"></i>
                                </div>
                                <h2 class="mt-5 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('No review data yet') }}</h2>
                                <p class="mt-3 max-w-xl text-sm leading-7" style="color: var(--theme-muted-text-color);">
                                    {{ __('Review Booster results will appear here after customers scan your review page, choose a rating, and either continue to Google Review or submit private feedback.') }}
                                </p>
                                <div class="mt-5">
                                    <x-ui.button href="{{ route('portal.review-booster', ['business_id' => $business->id]) }}" wire:navigate>
                                        <i class="fa-light fa-plus"></i>{{ __('Create Review Booster') }}
                                    </x-ui.button>
                                </div>
                            </div>
                            <div class="space-y-2">
                                @foreach ([__('Customer scans QR'), __('Chooses 1 to 5 stars'), __('Positive reviews go public'), __('Low scores stay internal')] as $step)
                                    <div class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background-color: color-mix(in srgb, var(--theme-surface-overlay) 84%, transparent); color: var(--theme-header-text-color);">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-success-color-rgb),0.12); color: var(--theme-success-color);">
                                            <i class="fa-light fa-check text-xs"></i>
                                        </span>
                                        {{ $step }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <aside class="border-t p-5 xl:border-l xl:border-t-0" style="border-color: rgba(var(--theme-border-color-rgb), .68); background:
                linear-gradient(180deg, rgba(var(--theme-accent-rgb),0.055), transparent 36%),
                color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Rating distribution') }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Score mix from Review Booster') }}</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                        <i class="fa-light fa-chart-simple"></i>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background-color: color-mix(in srgb, var(--theme-surface-overlay) 82%, transparent);">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-4xl font-semibold tracking-[-0.06em]" style="color: var(--theme-header-text-color);">{{ $averageRating ?: '0.0' }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Average score') }}</p>
                        </div>
                        <div class="text-right text-xs font-semibold" style="color: var(--theme-muted-text-color);">
                            {{ format_number_locale($totalReviews) }} {{ __('responses') }}
                        </div>
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    @for ($rating = 5; $rating >= 1; $rating--)
                        @php
                            $count = (int) ($ratingCounts[$rating] ?? 0);
                            $percent = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                        @endphp
                        <div>
                            <div class="mb-1.5 flex items-center justify-between text-xs font-semibold" style="color: var(--theme-muted-text-color);">
                                <span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-star text-[10px]" style="color: {{ $rating >= 4 ? 'var(--theme-success-color)' : ($rating === 3 ? 'var(--theme-warning-color)' : 'var(--theme-danger-color)') }};"></i>{{ $rating }}</span>
                                <span>{{ format_number_locale($count) }}</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb),0.34);">
                                <div class="h-full rounded-full" style="width: {{ $percent }}%; background-color: {{ $rating >= 4 ? 'var(--theme-success-color)' : ($rating === 3 ? 'var(--theme-warning-color)' : 'var(--theme-danger-color)') }};"></div>
                            </div>
                        </div>
                    @endfor
                </div>
            </aside>
        </div>
    </section>
</div>
