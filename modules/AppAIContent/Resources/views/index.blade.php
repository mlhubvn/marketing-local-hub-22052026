<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 34%),
        linear-gradient(35deg, rgba(var(--theme-success-color-rgb),0.08), transparent 40%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-6 px-5 py-7 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb),0.48); background-color: color-mix(in srgb, var(--theme-surface-base) 82%, transparent); color: var(--theme-muted-text-color);">
                    <i class="fa-light fa-pen-nib"></i>{{ __('AI Tools') }}
                </span>
                <h1 class="mt-4 text-[2.35rem] font-semibold leading-[1.02] tracking-[-0.055em] sm:text-[3rem]" style="color: var(--theme-header-text-color);">{{ __('Content Writer') }}</h1>
                <p class="mt-4 max-w-3xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ __('Create focused local marketing copy for reviews, coupons, bookings, feedback, lead follow-up, social posts, and customer messages.') }}
                </p>
            </div>

            <div class="rounded-[1.15rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Writer workflow') }}</p>
                <div class="mt-4 space-y-2">
                    @foreach ([__('Choose business'), __('Pick content type'), __('Generate versions'), __('Copy or save content')] as $step)
                        <div class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.50); background-color: color-mix(in srgb, var(--theme-surface-overlay) 80%, transparent); color: var(--theme-muted-text-color);">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-success-color-rgb),0.12); color: var(--theme-success-color);">
                                <i class="fa-light fa-check text-xs"></i>
                            </span>
                            {{ $step }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="relative overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div wire:loading.flex wire:target="setTab,generate,makeShorter,makeLonger,makeFriendly,saveContent,loadHistory,deleteHistory" class="absolute inset-0 z-30 items-start justify-center pt-28" style="background-color: color-mix(in srgb, var(--theme-surface-overlay) 72%, transparent);">
            <div class="inline-flex items-center gap-3 rounded-2xl border px-4 py-3 shadow-[0_18px_50px_-38px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb), .64); background-color: var(--theme-surface-overlay); color: var(--theme-header-text-color);">
                <i class="fa-light fa-loader animate-spin" style="color: var(--theme-accent);"></i>
                <span class="text-sm font-semibold">{{ __('Loading...') }}</span>
            </div>
        </div>

        <div class="border-b px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <div class="flex flex-wrap items-center gap-2">
                @foreach ([['id' => 'writer', 'label' => __('Writer')], ['id' => 'saved', 'label' => __('Saved Content')]] as $item)
                    <button type="button" wire:click="setTab('{{ $item['id'] }}')" class="rounded-xl px-4 py-2 text-sm font-semibold transition" style="{{ $tab === $item['id']
                        ? 'background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);'
                        : 'color: var(--theme-muted-text-color);'
                    }}">
                        {{ $item['label'] }}
                    </button>
                @endforeach
            </div>
        </div>

        @if ($statusMessage)
            <div class="border-b px-5 py-3 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: rgba(var(--theme-success-color-rgb),0.07); color: var(--theme-success-color);">
                {{ $statusMessage }}
            </div>
        @endif

        @if ($tab === 'writer')
            <div class="grid lg:grid-cols-[25rem_minmax(0,1fr)]">
                <form wire:submit="generate" class="border-r p-5" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    <div class="space-y-4">
                        @if ($businesses->isEmpty())
                            <div class="rounded-xl border p-4" style="border-color: rgba(var(--theme-warning-color-rgb), .28); background-color: rgba(var(--theme-warning-color-rgb), .08);">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Create Business first') }}</p>
                                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Content Writer needs a business profile to write branded local copy.') }}</p>
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

                        <x-ui.select wire:model.live="content_type" name="content_type" :label="__('Content type')" :error="$errors->first('content_type')">
                            @foreach ($contentTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.input wire:model.defer="goal" name="goal" :label="__('Goal / topic')" :placeholder="__('Ask customers to leave a review after visiting')" :error="$errors->first('goal')" />
                        <x-ui.input wire:model.defer="offer" name="offer" :label="__('Offer / promotion')" :placeholder="__('20% off next visit this weekend')" :error="$errors->first('offer')" />
                        <x-ui.input wire:model.defer="target_customer" name="target_customer" :label="__('Target customer')" :placeholder="__('New local customers, returning guests...')" :error="$errors->first('target_customer')" />

                        <div class="grid gap-3 sm:grid-cols-2">
                            <x-ui.select wire:model.defer="tone" name="tone" :label="__('Tone')" :error="$errors->first('tone')">
                                @foreach ($toneOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </x-ui.select>

                            <x-ai.language-field wire:model.defer="language" name="language" :value="$language" :label="__('Language')" :preferred="['en', 'vi']" />
                        </div>

                        <x-ui.textarea wire:model.defer="extra_details" name="extra_details" :label="__('Prompt / extra details')" rows="5" :placeholder="__('Add timing, service details, terms, customer context, or message direction...')" :error="$errors->first('extra_details')">{{ $extra_details }}</x-ui.textarea>

                        <x-ui.button type="submit" block wire:loading.attr="disabled" wire:target="generate" :disabled="!($creditPreview['enough'] ?? true)">
                            <i class="fa-light fa-wand-magic-sparkles"></i>
                            <span wire:loading.remove wire:target="generate">{{ __('Generate Content') }}</span>
                            <span wire:loading wire:target="generate">{{ __('Generating...') }}</span>
                        </x-ui.button>

                        <div class="rounded-xl border px-3 py-2 text-xs" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background-color: color-mix(in srgb, var(--theme-surface-base) 88%, transparent); color: var(--theme-muted-text-color);">
                            <i class="fa-light fa-coins mr-1" style="color: var(--theme-accent);"></i>
                            {{ __(':credits credits per run', ['credits' => $creditPreview['amount'] ?? 0]) }}
                        </div>
                    </div>
                </form>

                <div class="min-h-[42rem] p-5">
                    @if ($result)
                        <div class="space-y-5">
                            <div class="flex flex-col gap-4 border-b pb-5 sm:flex-row sm:items-start sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-ui.badge variant="success">{{ $contentTypes[$content_type] ?? __('Content') }}</x-ui.badge>
                                        <x-ui.badge variant="neutral">{{ strtoupper($language) }}</x-ui.badge>
                                        @if (($result['source'] ?? '') === 'ai')
                                            <x-ui.badge variant="success">{{ __('AI Generated') }}</x-ui.badge>
                                        @endif
                                    </div>
                                    <h2 class="mt-3 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $result['title'] ?? __('Generated Content') }}</h2>
                                    <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Review the generated versions, copy the one you like, or save it for reuse later.') }}</p>
                                </div>
                                <x-ui.button type="button" wire:click="saveContent" wire:loading.attr="disabled" wire:target="saveContent">
                                    <i class="fa-light fa-floppy-disk"></i>{{ __('Save Content') }}
                                </x-ui.button>
                            </div>

                            @php
                                $contentCards = [
                                    ['key' => 'generated_content', 'title' => __('Generated Content'), 'icon' => 'fa-pen-nib'],
                                    ['key' => 'short_version', 'title' => __('Short Version'), 'icon' => 'fa-compress'],
                                    ['key' => 'professional_version', 'title' => __('Professional Version'), 'icon' => 'fa-briefcase'],
                                    ['key' => 'friendly_version', 'title' => __('Friendly Version'), 'icon' => 'fa-face-smile'],
                                ];
                            @endphp

                            <div class="grid gap-4 xl:grid-cols-2">
                                @foreach ($contentCards as $card)
                                    @php
                                        $text = (string) data_get($result, $card['key'], '');
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

                            <div class="grid gap-4 lg:grid-cols-2">
                                <article class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('CTA Suggestions') }}</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach ((array) data_get($result, 'cta_suggestions', []) as $cta)
                                            <span class="rounded-full border px-3 py-1.5 text-xs font-semibold" style="border-color: rgba(var(--theme-accent-rgb),0.22); background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">{{ $cta }}</span>
                                        @endforeach
                                    </div>
                                </article>
                                <article class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Hashtags') }}</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @forelse ((array) data_get($result, 'hashtags', []) as $tag)
                                            <span class="rounded-full border px-3 py-1.5 text-xs font-semibold" style="border-color: rgba(var(--theme-border-color-rgb),0.56); color: var(--theme-muted-text-color);">#{{ ltrim($tag, '#') }}</span>
                                        @empty
                                            <span class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No hashtags needed for this content type.') }}</span>
                                        @endforelse
                                    </div>
                                </article>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                <x-ui.button type="button" variant="outline" wire:click="generate" wire:loading.attr="disabled" wire:target="generate">
                                    <i class="fa-light fa-arrows-rotate"></i>{{ __('Regenerate') }}
                                </x-ui.button>
                                <x-ui.button type="button" variant="outline" wire:click="makeShorter" wire:loading.attr="disabled" wire:target="makeShorter">
                                    <i class="fa-light fa-compress"></i>{{ __('Make Shorter') }}
                                </x-ui.button>
                                <x-ui.button type="button" variant="outline" wire:click="makeLonger" wire:loading.attr="disabled" wire:target="makeLonger">
                                    <i class="fa-light fa-expand"></i>{{ __('Make Longer') }}
                                </x-ui.button>
                                <x-ui.button type="button" variant="outline" wire:click="makeFriendly" wire:loading.attr="disabled" wire:target="makeFriendly">
                                    <i class="fa-light fa-face-smile"></i>{{ __('Make Friendly') }}
                                </x-ui.button>
                            </div>
                        </div>
                    @endif

                    @if (! $result)
                        <div class="flex min-h-[34rem] items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-border-color-rgb), .58); background:
                            linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.07), transparent 38%),
                            color-mix(in srgb, var(--theme-surface-base) 82%, transparent);">
                            <div class="max-w-md px-6 text-center">
                                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-accent-rgb), .24); background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="fa-light fa-pen-nib"></i></span>
                                <h2 class="mt-4 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('No content generated yet') }}</h2>
                                <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Choose a business, content type, goal, tone, and language. AI will generate reusable local marketing copy.') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if ($tab === 'saved')
            <div class="p-5">
                <div class="grid gap-3 border-b pb-5 lg:grid-cols-[minmax(0,1fr)_14rem_10rem]" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    <x-ui.input wire:model.live.debounce.300ms="savedSearch" :label="__('Search saved content')" :placeholder="__('Search by title, prompt, or type...')" />
                    <x-ui.select wire:model.live="savedType" :label="__('Content type')">
                        <option value="all">{{ __('All content types') }}</option>
                        @foreach ($contentTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select wire:model.live="perPage" :label="__('Rows')">
                        <option value="10">{{ __('10 / page') }}</option>
                        <option value="25">{{ __('25 / page') }}</option>
                        <option value="50">{{ __('50 / page') }}</option>
                    </x-ui.select>
                </div>

                @if ($savedContent->isNotEmpty())
                    <div class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        @foreach ($savedContent as $history)
                            @php
                                $savedText = (string) data_get($history->output_payload, 'generated_content', '');
                                $savedTypeKey = (string) data_get($history->metadata, 'content_type', data_get($history->input_payload, 'content_type', ''));
                            @endphp
                            <div class="flex flex-col gap-4 py-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $history->title ?: __('Saved content') }}</h3>
                                        <x-ui.badge variant="success">{{ $contentTypes[$savedTypeKey] ?? __('Content') }}</x-ui.badge>
                                    </div>
                                    <p class="mt-1 line-clamp-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Updated :time', ['time' => $history->updated_at?->diffForHumans()]) }} · {{ \Illuminate\Support\Str::limit($history->prompt, 120) }}</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-ui.button type="button" size="sm" variant="outline" wire:click="loadHistory({{ $history->id }})">
                                        <i class="fa-light fa-folder-open"></i>{{ __('Open') }}
                                    </x-ui.button>
                                    <button type="button" class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border px-3 text-sm font-semibold transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.08)]" style="border-color: rgba(var(--theme-border-color-rgb), .58); color: var(--theme-header-text-color);" onclick="navigator.clipboard && navigator.clipboard.writeText(this.dataset.copy || '')" data-copy="{{ e($savedText) }}">
                                        <i class="fa-light fa-copy"></i>{{ __('Copy') }}
                                    </button>
                                    <button type="button" wire:click="deleteHistory({{ $history->id }})" wire:confirm="{{ __('Delete this saved content?') }}" class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border px-3 text-sm font-semibold transition hover:bg-[color:rgba(var(--theme-danger-color-rgb),0.08)]" style="border-color: rgba(var(--theme-danger-color-rgb), .35); color: var(--theme-danger-color);">
                                        <i class="fa-light fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex flex-col gap-3 border-t pt-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        <p class="text-sm" style="color: var(--theme-muted-text-color);">
                            {{ __('Showing') }}
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($savedContent->firstItem()) }}</span> -
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($savedContent->lastItem()) }}</span>
                            {{ __('of') }}
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($savedContent->total()) }}</span>
                            {{ __('saved content') }}
                        </p>
                        <div class="flex items-center gap-2">
                            <x-ui.button type="button" variant="outline" wire:click="previousPage" :disabled="$savedContent->onFirstPage()">
                                <i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}
                            </x-ui.button>
                            <span class="rounded-[0.8rem] border px-4 py-2 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .10);">
                                {{ __('Page :page / :pages', ['page' => $savedContent->currentPage(), 'pages' => max(1, $savedContent->lastPage())]) }}
                            </span>
                            <x-ui.button type="button" variant="outline" wire:click="nextPage" :disabled="! $savedContent->hasMorePages()">
                                {{ __('Next') }}<i class="fa-light fa-arrow-right"></i>
                            </x-ui.button>
                        </div>
                    </div>
                @endif

                @if ($savedContent->isEmpty())
                    <div class="mt-5 flex min-h-[18rem] items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-border-color-rgb), .58); background:
                        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.06), transparent 38%),
                        color-mix(in srgb, var(--theme-surface-base) 84%, transparent);">
                        <div class="max-w-md px-6 text-center">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border" style="border-color: rgba(var(--theme-accent-rgb), .22); background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="fa-light fa-bookmark"></i></span>
                            <h2 class="mt-4 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('No saved content yet') }}</h2>
                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Generate a marketing message and save it here before reusing it in campaigns, messages, and public pages.') }}</p>
                            <div class="mt-5">
                                <x-ui.button type="button" wire:click="setTab('writer')">
                                    <i class="fa-light fa-plus"></i>{{ __('Create content') }}
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </section>
</div>
