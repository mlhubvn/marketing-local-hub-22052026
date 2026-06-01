<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif

    <section class="overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 32%),
        linear-gradient(35deg, rgba(var(--theme-success-color-rgb),0.08), transparent 38%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-6 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_23rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-md border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-star-sharp-half-stroke"></i>{{ __('AI Tools') }}
                </div>
                <h1 class="mt-4 max-w-3xl text-[2.1rem] font-semibold leading-[1.05] tracking-[-0.055em] sm:text-[2.75rem]" style="color: var(--theme-header-text-color);">{{ __('AI Review Reply') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ __('Write professional replies for public reviews and private low-score feedback, using the default AI settings from your workspace.') }}
                </p>
            </div>

            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Reply workflow') }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('For review boosters, feedback inboxes, and public review responses.') }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="fa-light fa-comments"></i></span>
                </div>
                <div class="mt-4 grid gap-2">
                    @foreach ([__('4-5 stars: thank and invite back'), __('1-3 stars: apologize and recover privately'), __('Save replies for later reuse')] as $step)
                        <div class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), .46); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-overlay) 88%, transparent);">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="fa-light fa-check text-xs"></i></span>
                            {{ $step }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="relative overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div wire:loading.flex wire:target="setTab,generate,makeShorter,makeWarmer,saveReply,loadHistory,deleteHistory" class="absolute inset-0 z-30 items-start justify-center pt-28" style="background-color: color-mix(in srgb, var(--theme-surface-overlay) 72%, transparent);">
            <div class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: var(--theme-surface-base); color: var(--theme-header-text-color);">
                <i class="fa-light fa-spinner-third animate-spin" style="color: var(--theme-accent);"></i>
                {{ __('Working on reply...') }}
            </div>
        </div>

        <div class="flex gap-2 border-b px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            @foreach ([['id' => 'builder', 'label' => __('Builder')], ['id' => 'saved', 'label' => __('Saved Replies')]] as $item)
                <button type="button" wire:click="setTab('{{ $item['id'] }}')" wire:loading.attr="disabled" wire:target="setTab" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.08)] disabled:cursor-wait disabled:opacity-70" style="{{ $tab === $item['id'] ? 'background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);' : 'color: var(--theme-muted-text-color);' }}">
                    <span>{{ $item['label'] }}</span>
                </button>
            @endforeach
        </div>

        @if ($tab === 'builder')
        <div class="grid gap-0 xl:grid-cols-[27rem_minmax(0,1fr)]">
            <form wire:submit="generate" class="border-r p-5" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                <div class="space-y-4">
                    @if ($businesses->isEmpty())
                        <div class="rounded-xl border p-4" style="border-color: rgba(var(--theme-warning-color-rgb), .28); background-color: rgba(var(--theme-warning-color-rgb), .08);">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Create Business first') }}</p>
                            <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('AI Review Reply needs a business profile before it can write branded replies.') }}</p>
                        </div>
                    @endif

                    <x-ui.combobox
                        :label="__('Business')"
                        model="business_id"
                        name="business_id"
                        :selected="$business_id"
                        :options="$businesses->map(fn ($business) => ['value' => (string) $business->id, 'label' => $business->name, 'meta' => $business->type ?: __('Local business'), 'icon' => 'fa-store'])->values()->all()"
                        :placeholder="__('Select business')"
                        :search-placeholder="__('Search business')"
                        :error="$errors->first('business_id')"
                        icon="fa-light fa-store"
                    />

                    <x-ui.select wire:model="rating" name="rating" :label="__('Review rating')" :error="$errors->first('rating')">
                        @for ($star = 5; $star >= 1; $star--)
                            <option value="{{ $star }}">{{ trans_choice(':count star|:count stars', $star, ['count' => $star]) }}</option>
                        @endfor
                    </x-ui.select>

                    <x-ui.select wire:model="reply_type" name="reply_type" :label="__('Reply type')" :error="$errors->first('reply_type')">
                        <option value="auto">{{ __('Auto by rating') }}</option>
                        <option value="public">{{ __('Public review reply') }}</option>
                        <option value="private">{{ __('Private feedback recovery') }}</option>
                    </x-ui.select>

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <x-ui.label for="customer_name">{{ __('Customer name') }}</x-ui.label>
                            <button type="button" wire:click="generateCustomerName" wire:loading.attr="disabled" wire:target="generateCustomerName" class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-xs font-semibold transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.08)]" style="border-color: rgba(var(--theme-accent-rgb), .24); color: var(--theme-accent);">
                                <span wire:loading.remove wire:target="generateCustomerName" class="inline-flex items-center gap-1.5"><i class="fa-light fa-wand-magic-sparkles"></i>{{ __('Auto name') }}</span>
                                <span wire:loading wire:target="generateCustomerName" class="inline-flex items-center gap-1.5"><i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Generating') }}</span>
                            </button>
                        </div>
                        <x-ui.input wire:model="customer_name" name="customer_name" :placeholder="__('Optional, e.g. Sarah')" :error="$errors->first('customer_name')" />
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Leave blank if you want a generic reply without a customer name.') }}</p>
                    </div>

                    @php
                        $reviewTextPlaceholder = (int) $rating >= 4
                            ? __('Example: Amazing service, friendly staff, and fast support.')
                            : __('Example: I waited too long and the service was not what I expected.');
                    @endphp

                    <x-ui.textarea
                        wire:model="review_text"
                        name="review_text"
                        :label="__('Customer review / feedback')"
                        rows="7"
                        :placeholder="$reviewTextPlaceholder"
                        :error="$errors->first('review_text')"
                    >{{ $review_text }}</x-ui.textarea>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.select wire:model="tone" name="tone" :label="__('Tone')" :error="$errors->first('tone')">
                            @foreach ($toneOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.language-select wire:model="language" name="language" :label="__('Language')" :value="$language" :error="$errors->first('language')" />
                    </div>

                    <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled" wire:target="generate" :disabled="!($creditPreview['enough'] ?? true)">
                        <span wire:loading.remove wire:target="generate" class="inline-flex items-center gap-2"><i class="fa-light fa-wand-magic-sparkles"></i>{{ __('Generate AI Reply') }}</span>
                        <span wire:loading wire:target="generate" class="inline-flex items-center gap-2"><i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Generating...') }}</span>
                    </x-ui.button>

                    <div class="inline-flex w-fit items-center gap-2 rounded-full border px-3 py-2 text-sm leading-none" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
                        <span class="inline-flex h-4 w-4 shrink-0 items-center justify-center">
                            <i class="fa-light fa-coins text-xs" style="color: var(--theme-accent);"></i>
                        </span>
                        <span>{{ __(':credits credits', ['credits' => $creditPreview['amount'] ?? 0]) }}</span>
                        <span>&bull;</span>
                        <span>{{ ($creditPreview['unlimited'] ?? false) ? __('Unlimited plan') : __(':credits left', ['credits' => $creditPreview['remaining'] ?? 0]) }}</span>
                    </div>

                    @if (!($creditPreview['enough'] ?? true))
                        <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ __('Not enough credits remaining for this action.') }}</p>
                        @include(theme_view('partials.credit-topup-cta', 'app'))
                    @endif
                </div>
            </form>

            <div class="p-5">
                @if ($reply)
                    <div class="space-y-5">
                        <div class="flex flex-col gap-3 border-b pb-4 sm:flex-row sm:items-start sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    @php
                                        $reviewReplyVariant = (int) $rating >= 4 ? 'success' : 'warning';
                                        $reviewReplyLabel = (int) $rating >= 4 ? __('Positive review') : __('Recovery reply');
                                        $reviewSourceVariant = $aiSource === 'ai' ? 'success' : 'warning';
                                    @endphp
                                    <x-ui.badge :variant="$reviewReplyVariant">
                                        {{ $reviewReplyLabel }}
                                    </x-ui.badge>
                                    <x-ui.badge :variant="$reviewSourceVariant">
                                        {{ $aiSource === 'ai' ? __('AI generated') : __('Fallback reply') }}
                                    </x-ui.badge>
                                </div>
                                <h2 class="mt-3 text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ __('Suggested Reply') }}</h2>
                                @if (data_get($reply, 'fallback_reason'))
                                    <p class="mt-2 text-xs" style="color: var(--theme-warning-color);">{{ __('AI fallback: :reason', ['reason' => data_get($reply, 'fallback_reason')]) }}</p>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <x-ui.button type="button" variant="outline" wire:click="generate" wire:loading.attr="disabled" wire:target="generate" size="sm">
                                    <i class="fa-light fa-arrows-rotate"></i>{{ __('Regenerate') }}
                                </x-ui.button>
                                <x-ui.button type="button" variant="outline" wire:click="makeShorter" wire:loading.attr="disabled" wire:target="makeShorter" size="sm">
                                    <i class="fa-light fa-compress"></i>{{ __('Make shorter') }}
                                </x-ui.button>
                                <x-ui.button type="button" variant="outline" wire:click="makeWarmer" wire:loading.attr="disabled" wire:target="makeWarmer" size="sm">
                                    <i class="fa-light fa-hand-heart"></i>{{ __('Make warmer') }}
                                </x-ui.button>
                            </div>
                        </div>

                        @php
                            $replyCards = [
                                ['key' => 'suggested_reply', 'title' => __('Suggested reply'), 'icon' => 'fa-message-check'],
                                ['key' => 'short_reply', 'title' => __('Short reply'), 'icon' => 'fa-align-left'],
                                ['key' => 'professional_reply', 'title' => __('Professional reply'), 'icon' => 'fa-briefcase'],
                                ['key' => 'friendly_reply', 'title' => __('Friendly reply'), 'icon' => 'fa-face-smile'],
                            ];
                        @endphp

                        <div class="grid gap-4 xl:grid-cols-2">
                            @foreach ($replyCards as $card)
                                @php
                                    $text = (string) data_get($reply, $card['key'], '');
                                @endphp
                                <article class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 74%, transparent);">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="fa-light {{ $card['icon'] }}"></i></span>
                                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $card['title'] }}</p>
                                        </div>
                                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.08)]" style="border-color: rgba(var(--theme-border-color-rgb), .58); color: var(--theme-muted-text-color);" onclick="navigator.clipboard && navigator.clipboard.writeText(this.dataset.copy || '')" data-copy="{{ e($text) }}" title="{{ __('Copy') }}">
                                            <i class="fa-light fa-copy"></i>
                                        </button>
                                    </div>
                                    <p class="mt-4 whitespace-pre-line text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ $text }}</p>
                                </article>
                            @endforeach
                        </div>

                        <div class="flex flex-col gap-3 rounded-2xl border p-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Actions') }}</p>
                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Copy a reply, save it to history, or continue refining the tone.') }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($review_feedback_id)
                                    <x-ui.button type="button" variant="outline" wire:click="markLinkedFeedbackResolved" wire:loading.attr="disabled" wire:target="markLinkedFeedbackResolved">
                                        <i class="fa-light fa-circle-check"></i>{{ __('Mark resolved') }}
                                    </x-ui.button>
                                @endif
                                <x-ui.button type="button" wire:click="saveReply" wire:loading.attr="disabled" wire:target="saveReply">
                                    <i class="fa-light fa-floppy-disk"></i>{{ __('Save reply') }}
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                @endif

                @if (! $reply)
                    <div class="flex min-h-[31rem] items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-border-color-rgb), .58); background:
                        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.07), transparent 38%),
                        color-mix(in srgb, var(--theme-surface-base) 82%, transparent);">
                        <div class="max-w-md px-6 text-center">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-accent-rgb), .24); background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="fa-light fa-star-sharp-half-stroke"></i></span>
                            <h2 class="mt-4 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('No review reply yet') }}</h2>
                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">
                                {{ __('Choose a business, add the customer rating and review text, then generate polished reply options for public reviews or private recovery messages.') }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif

        @if ($tab === 'saved')
            <div class="p-5">
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Saved Replies') }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Reuse, copy, or remove AI replies you saved from previous review and feedback responses.') }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <x-ui.select wire:model.live="savedRepliesPerPage" name="saved_replies_per_page">
                            <option value="10">{{ __('10 / page') }}</option>
                            <option value="25">{{ __('25 / page') }}</option>
                            <option value="50">{{ __('50 / page') }}</option>
                        </x-ui.select>
                        <x-ui.button type="button" variant="outline" wire:click="setTab('builder')">
                            <i class="fa-light fa-plus"></i>{{ __('Create reply') }}
                        </x-ui.button>
                    </div>
                </div>

                @if ($savedReplies->isNotEmpty())
                    <div class="overflow-hidden rounded-2xl border" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        @foreach ($savedReplies as $history)
                            @php
                                $savedText = (string) data_get($history->output_payload, 'suggested_reply', '');
                                $savedRating = (int) data_get($history->metadata, 'rating', data_get($history->input_payload, 'rating', 0));
                            @endphp
                            <div class="flex flex-col gap-4 border-b p-4 last:border-b-0 lg:flex-row lg:items-center lg:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .50);">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $history->title }}</p>
                                        <span class="rounded-full border px-2 py-0.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent);">{{ $savedRating }} {{ __('stars') }}</span>
                                    </div>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Updated :time', ['time' => $history->updated_at?->diffForHumans()]) }}</p>
                                    <p class="mt-2 line-clamp-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $savedText }}</p>
                                </div>
                                <div class="flex shrink-0 flex-wrap items-center gap-2">
                                    <x-ui.button type="button" size="sm" variant="outline" wire:click="loadHistory({{ $history->id }})">
                                        <i class="fa-light fa-folder-open"></i>{{ __('Open') }}
                                    </x-ui.button>
                                    <button type="button" onclick="navigator.clipboard && navigator.clipboard.writeText(this.dataset.copy || '')" data-copy="{{ e($savedText) }}" class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border px-3 text-sm font-semibold transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.08)]" style="border-color: rgba(var(--theme-border-color-rgb), .58); color: var(--theme-header-text-color);">
                                        <i class="fa-light fa-copy"></i>{{ __('Copy') }}
                                    </button>
                                    <button type="button" wire:click="deleteHistory({{ $history->id }})" wire:confirm="{{ __('Delete this saved reply?') }}" class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border px-3 text-sm font-semibold transition hover:bg-[color:rgba(var(--theme-danger-color-rgb),0.08)]" style="border-color: rgba(var(--theme-danger-color-rgb), .35); color: var(--theme-danger-color);">
                                        <i class="fa-light fa-trash"></i>{{ __('Delete') }}
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm" style="color: var(--theme-muted-text-color);">
                            {{ __('Showing') }}
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($savedReplies->firstItem()) }}</span>
                            -
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($savedReplies->lastItem()) }}</span>
                            {{ __('of') }}
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($savedReplies->total()) }}</span>
                            {{ __('saved replies') }}
                        </p>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="previousPage" @disabled($savedReplies->onFirstPage()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">
                                <i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}
                            </button>
                            <span class="inline-flex h-10 items-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .25); background-color: rgba(var(--theme-accent-rgb), .10); color: var(--theme-accent);">
                                {{ __('Page :page / :pages', ['page' => $savedReplies->currentPage(), 'pages' => max(1, $savedReplies->lastPage())]) }}
                            </span>
                            <button type="button" wire:click="nextPage" @disabled(! $savedReplies->hasMorePages()) class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .7); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">
                                {{ __('Next') }}<i class="fa-light fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                @endif

                @if ($savedReplies->isEmpty())
                    <div class="flex min-h-[22rem] items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-border-color-rgb), .58); background:
                        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.07), transparent 38%),
                        color-mix(in srgb, var(--theme-surface-base) 82%, transparent);">
                        <div class="max-w-md px-6 text-center">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-accent-rgb), .24); background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="fa-light fa-folder-open"></i></span>
                            <h2 class="mt-4 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('No saved replies yet') }}</h2>
                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Generate and save an AI reply, then it will appear here for reuse.') }}</p>
                            <div class="mt-5">
                                <x-ui.button type="button" wire:click="setTab('builder')">
                                    <i class="fa-light fa-wand-magic-sparkles"></i>{{ __('Create first reply') }}
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </section>
</div>
