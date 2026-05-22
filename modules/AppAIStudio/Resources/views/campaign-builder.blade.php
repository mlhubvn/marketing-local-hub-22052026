<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif

    <section class="overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.13), transparent 32%),
        linear-gradient(35deg, rgba(var(--theme-success-color-rgb),0.08), transparent 38%),
        color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="grid gap-6 px-5 py-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-center sm:px-6 xl:px-7">
            <div>
                <div class="inline-flex items-center gap-2 rounded-md border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-wand-magic-sparkles"></i>{{ __('AI Tools') }}
                </div>
                <h1 class="mt-4 max-w-3xl text-[2.15rem] font-semibold leading-[1.05] tracking-[-0.055em] sm:text-[2.85rem]" style="color: var(--theme-header-text-color);">{{ __('AI Campaign Builder') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-[1rem]" style="color: var(--theme-muted-text-color);">
                    {{ __('Turn a local growth goal into a ready-to-publish campaign with page copy, offer text, social posts, follow-up messages, and tracking.') }}
                </p>
            </div>

            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: color-mix(in srgb, var(--theme-surface-base) 90%, transparent);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Builder flow') }}</p>
                <div class="mt-4 space-y-3">
                    @foreach ([__('Choose business'), __('Choose growth goal'), __('Generate plan'), __('Create campaign')] as $step)
                        <div class="flex items-center gap-3 text-sm" style="color: var(--theme-muted-text-color);">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="fa-light fa-check text-xs"></i></span>
                            {{ $step }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="relative overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div wire:loading.flex wire:target="setTab" class="absolute inset-0 z-30 items-start justify-center pt-24" style="background-color: color-mix(in srgb, var(--theme-surface-overlay) 76%, transparent);">
            <div class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: var(--theme-surface-base); color: var(--theme-header-text-color);">
                <i class="fa-light fa-spinner-third animate-spin" style="color: var(--theme-accent);"></i>
                {{ __('Loading workspace...') }}
            </div>
        </div>
        <div class="flex gap-2 border-b px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            @foreach ([['id' => 'builder', 'label' => __('Builder')], ['id' => 'ideas', 'label' => __('Offer Ideas')], ['id' => 'drafts', 'label' => __('Saved Drafts')]] as $item)
                <button type="button" wire:click="setTab('{{ $item['id'] }}')" wire:loading.attr="disabled" wire:target="setTab" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.08)] disabled:cursor-wait disabled:opacity-70" style="{{ $tab === $item['id'] ? 'background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);' : 'color: var(--theme-muted-text-color);' }}">
                    <span wire:loading.remove wire:target="setTab">{{ $item['label'] }}</span>
                    <span wire:loading wire:target="setTab" class="inline-flex items-center gap-2">
                        @if ($tab === $item['id'])
                            <i class="fa-light fa-spinner-third animate-spin"></i>
                        @endif
                        {{ $item['label'] }}
                    </span>
                </button>
            @endforeach
        </div>

        @if ($tab === 'builder')
            <div class="grid gap-0 xl:grid-cols-[26rem_minmax(0,1fr)]">
                <form wire:submit="generate" class="border-r p-5" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                    <div class="space-y-4">
                        @if ($businesses->isEmpty())
                            <div class="border p-4" style="border-color: rgba(var(--theme-warning-color-rgb), .28); background-color: rgba(var(--theme-warning-color-rgb), .08);">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Create Business first') }}</p>
                                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('AI Campaign Builder needs a business profile before it can create a campaign.') }}</p>
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

                        <div>
                            <x-ui.label>{{ __('What do you want to grow?') }}</x-ui.label>
                            <div class="mt-2 grid gap-2">
                                @foreach ($goalOptions as $key => $option)
                                    <button type="button" wire:click="$set('goal', '{{ $key }}')" class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-left transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.06)]" style="border-color: {{ $goal === $key ? 'rgba(var(--theme-accent-rgb), .42)' : 'rgba(var(--theme-border-color-rgb), .58)' }}; background-color: {{ $goal === $key ? 'rgba(var(--theme-accent-rgb), .08)' : 'transparent' }};">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="fa-light {{ $option['icon'] }}"></i></span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $option['label'] }}</span>
                                            <span class="block text-xs" style="color: var(--theme-muted-text-color);">{{ $option['type'] }}</span>
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <x-ui.input wire:model="offer" name="offer" :label="__('Campaign idea / offer')" :placeholder="__('20% off massage this weekend')" :error="$errors->first('offer')" />
                        <x-ui.input wire:model="target_customer" name="target_customer" :label="__('Target customer')" :placeholder="__('New local customers, returning guests...')" :error="$errors->first('target_customer')" />
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.select wire:model="tone" name="tone" :label="__('Tone')" :error="$errors->first('tone')">
                                @foreach ($toneOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.language-select wire:model="language" name="language" :label="__('Language')" :value="$language" :error="$errors->first('language')" />
                        </div>
                        <x-ui.textarea wire:model="prompt" name="prompt" :label="__('Describe what you want to create')" rows="4" :placeholder="__('I have a spa and want to increase weekend bookings...')" :error="$errors->first('prompt')">{{ $prompt }}</x-ui.textarea>

                        <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled" wire:target="generate">
                            <span wire:loading.remove wire:target="generate"><i class="fa-light fa-wand-magic-sparkles"></i>{{ __('Generate AI Campaign Plan') }}</span>
                            <span wire:loading wire:target="generate"><i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Generating...') }}</span>
                        </x-ui.button>
                    </div>
                </form>

                <div class="p-5">
                    @if ($plan)
                        <div class="space-y-5">
                            <div class="flex flex-col gap-3 border-b pb-4 sm:flex-row sm:items-start sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-ui.badge variant="success">{{ data_get($plan, 'campaign_type') }}</x-ui.badge>
                                        <x-ui.badge :variant="data_get($plan, 'source') === 'ai' ? 'success' : 'warning'">
                                            {{ data_get($plan, 'source') === 'ai' ? __('AI generated') : __('Fallback plan') }}
                                        </x-ui.badge>
                                    </div>
                                    <h2 class="mt-3 text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ data_get($plan, 'campaign_name') }}</h2>
                                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ data_get($plan, 'description') }}</p>
                                    @if (data_get($plan, 'source') !== 'ai' && data_get($plan, 'fallback_reason'))
                                        <p class="mt-2 text-xs" style="color: var(--theme-warning-color);">{{ __('AI fallback: :reason', ['reason' => data_get($plan, 'fallback_reason')]) }}</p>
                                    @endif
                                </div>
                            </div>

                            <section class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 72%, transparent);">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Campaign Plan') }}</p>
                                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Review the strategic basics before publishing.') }}</p>
                                    </div>
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="fa-light fa-bullseye-arrow"></i></span>
                                </div>
                                <dl class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                                    @foreach ([
                                        __('Campaign type') => data_get($plan, 'campaign_type'),
                                        __('Campaign name') => data_get($plan, 'campaign_name'),
                                        __('Goal') => data_get($plan, 'goal'),
                                        __('Offer') => $offer ?: data_get($plan, 'description'),
                                        __('CTA') => data_get($plan, 'cta'),
                                    ] as $label => $value)
                                        <div class="min-w-0 rounded-xl border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .48); background-color: color-mix(in srgb, var(--theme-surface-overlay) 92%, transparent);">
                                            <dt class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $label }}</dt>
                                            <dd class="mt-2 truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $value }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </section>

                            <div class="grid gap-4 xl:grid-cols-[minmax(0,1.05fr)_minmax(0,.95fr)]">
                                <section class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Page Copy') }}</p>
                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Headline') }}</p>
                                            <h3 class="mt-2 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ data_get($plan, 'headline') }}</h3>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Subheadline') }}</p>
                                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ data_get($plan, 'subheadline') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Description') }}</p>
                                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ data_get($plan, 'description') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Benefits') }}</p>
                                            <ul class="mt-2 space-y-2 text-sm" style="color: var(--theme-muted-text-color);">
                                                @foreach (data_get($plan, 'benefits', []) as $benefit)
                                                    <li class="flex gap-2"><i class="fa-light fa-check mt-1" style="color: var(--theme-accent);"></i><span>{{ $benefit }}</span></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="inline-flex px-3 py-2 text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                                            {{ data_get($plan, 'cta') }}
                                        </div>
                                    </div>
                                </section>

                                <div class="space-y-4">
                                    <section class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Marketing Messages') }}</p>
                                        <div class="mt-4 space-y-4">
                                            <div>
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Social post') }}</p>
                                                <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ data_get($plan, 'social_caption') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('SMS / WhatsApp') }}</p>
                                                <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ data_get($plan, 'sms_message') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Email') }}</p>
                                                <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ data_get($plan, 'email_subject') }}</p>
                                                <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ data_get($plan, 'email_body') }}</p>
                                            </div>
                                        </div>
                                    </section>

                                    <section class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('QR / Poster Text') }}</p>
                                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                            <div class="rounded-xl border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .48);">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Short CTA') }}</p>
                                                <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ data_get($plan, 'cta') }}</p>
                                            </div>
                                            <div class="rounded-xl border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .48);">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Poster headline') }}</p>
                                                <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ data_get($plan, 'qr_poster_text') }}</p>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </div>

                            @php
                                $contentWriterFromPlanUrl = route('portal.ai-content', array_filter([
                                    'business_id' => $business_id,
                                    'type' => 'social_post',
                                    'goal' => data_get($plan, 'goal') ?: 'Promote this AI-generated campaign.',
                                    'offer' => $offer,
                                    'target_customer' => $target_customer,
                                    'details' => trim(implode("\n\n", array_filter([
                                        'Campaign: '.data_get($plan, 'campaign_name'),
                                        'Social post: '.data_get($plan, 'social_caption'),
                                        'SMS / WhatsApp: '.data_get($plan, 'sms_message'),
                                        'Email subject: '.data_get($plan, 'email_subject'),
                                        'Email body: '.data_get($plan, 'email_body'),
                                    ]))),
                                    'source_type' => 'ai_campaign_builder',
                                ], fn ($value) => filled($value)));
                            @endphp

                            <section class="flex flex-col gap-3 rounded-2xl border p-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 78%, transparent);">
                                <div>
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Actions') }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Regenerate, save this draft, or turn the plan into a campaign.') }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.button href="{{ $contentWriterFromPlanUrl }}" wire:navigate variant="outline">
                                        <i class="fa-light fa-pen-nib"></i>{{ __('Open in Content Writer') }}
                                    </x-ui.button>
                                    <x-ui.button type="button" variant="outline" wire:click="generate" wire:loading.attr="disabled" wire:target="generate">
                                        <span wire:loading.remove wire:target="generate"><i class="fa-light fa-rotate"></i>{{ __('Regenerate') }}</span>
                                        <span wire:loading wire:target="generate"><i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Generating...') }}</span>
                                    </x-ui.button>
                                    <x-ui.button type="button" variant="outline" wire:click="saveCurrentDraft" wire:loading.attr="disabled" wire:target="saveCurrentDraft">
                                        <span wire:loading.remove wire:target="saveCurrentDraft"><i class="fa-light fa-floppy-disk"></i>{{ __('Save Draft') }}</span>
                                        <span wire:loading wire:target="saveCurrentDraft"><i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Saving...') }}</span>
                                    </x-ui.button>
                                    <x-ui.button type="button" wire:click="createCampaign" wire:loading.attr="disabled" wire:target="createCampaign">
                                        <span wire:loading.remove wire:target="createCampaign"><i class="fa-light fa-plus"></i>{{ __('Create Campaign') }}</span>
                                        <span wire:loading wire:target="createCampaign"><i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Creating...') }}</span>
                                    </x-ui.button>
                                </div>
                            </section>
                        </div>
                        </div>
                    @else
                        <div class="flex min-h-[34rem] items-center justify-center border" style="border-color: rgba(var(--theme-border-color-rgb), .58); background: linear-gradient(145deg, rgba(var(--theme-accent-rgb),0.06), transparent 56%);">
                            <div class="max-w-md px-6 text-center">
                                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);"><i class="fa-light fa-wand-magic-sparkles"></i></span>
                                <h3 class="mt-4 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('No campaign plan yet') }}</h3>
                                <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Choose a business, goal, offer, and tone. AI will create the campaign plan and let you turn it into a real campaign.') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @elseif ($tab === 'ideas')
            <div class="p-5">
                <div class="flex flex-col gap-4 border-b pb-4 lg:flex-row lg:items-end lg:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Offer Ideas') }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Pick a proven local growth idea and AI will prepare the Builder inputs for it.') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($ideaFilters as $key => $label)
                            <button type="button" wire:click="setIdeaFilter('{{ $key }}')" wire:loading.attr="disabled" wire:target="setIdeaFilter" class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.06)] disabled:cursor-wait disabled:opacity-70" style="border-color: {{ $ideaFilter === $key ? 'rgba(var(--theme-accent-rgb), .42)' : 'rgba(var(--theme-border-color-rgb), .58)' }}; background-color: {{ $ideaFilter === $key ? 'rgba(var(--theme-accent-rgb), .10)' : 'transparent' }}; color: {{ $ideaFilter === $key ? 'var(--theme-accent)' : 'var(--theme-muted-text-color)' }};">
                                <span wire:loading.remove wire:target="setIdeaFilter">{{ $label }}</span>
                                <span wire:loading wire:target="setIdeaFilter" class="inline-flex items-center gap-2">
                                    @if ($ideaFilter === $key)
                                        <i class="fa-light fa-spinner-third animate-spin"></i>
                                    @endif
                                    {{ $label }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="relative mt-5">
                    <div wire:loading.flex wire:target="setIdeaFilter" class="absolute inset-0 z-10 items-start justify-center rounded-2xl pt-10" style="background-color: color-mix(in srgb, var(--theme-surface-overlay) 72%, transparent);">
                        <div class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: var(--theme-surface-base); color: var(--theme-header-text-color);">
                            <i class="fa-light fa-spinner-third animate-spin" style="color: var(--theme-accent);"></i>
                            {{ __('Loading ideas...') }}
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" wire:loading.class="opacity-40" wire:target="setIdeaFilter">
                    @foreach ($offerIdeas as $ideaIndex => $idea)
                        <article class="flex min-h-[13rem] flex-col rounded-2xl border p-4 transition hover:-translate-y-0.5 hover:bg-[color:rgba(var(--theme-accent-rgb),0.04)]" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-overlay) 94%, transparent);">
                            <div class="flex items-start justify-between gap-3">
                                <x-ui.badge variant="success">{{ $idea['type'] }}</x-ui.badge>
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                                    <i class="fa-light {{ data_get($goalOptions, $idea['goal'].'.icon', 'fa-wand-magic-sparkles') }}"></i>
                                </span>
                            </div>

                            <div class="mt-4 min-w-0 flex-1">
                                <h3 class="text-base font-semibold leading-6" style="color: var(--theme-header-text-color);">{{ $idea['label'] }}</h3>
                                <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $idea['description'] }}</p>
                            </div>

                            <div class="mt-4 border-t pt-4" style="border-color: rgba(var(--theme-border-color-rgb), .5);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Best for') }}</p>
                                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $idea['best_for'] }}</p>
                                <button type="button" wire:click="useIdeaTemplate({{ $ideaIndex }})" wire:loading.attr="disabled" wire:target="useIdeaTemplate" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border px-3 py-2 text-sm font-semibold transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.06)] disabled:cursor-wait disabled:opacity-70" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                                    <span wire:loading.remove wire:target="useIdeaTemplate" class="inline-flex items-center gap-2">
                                        <i class="fa-light fa-arrow-right"></i>{{ __('Use this idea') }}
                                    </span>
                                    <span wire:loading wire:target="useIdeaTemplate" class="inline-flex items-center gap-2">
                                        <i class="fa-light fa-spinner-third animate-spin"></i>{{ __('Applying...') }}
                                    </span>
                                </button>
                            </div>
                        </article>
                    @endforeach
                    </div>
                </div>

                @if (empty($offerIdeas))
                    <div class="py-10">
                        <x-ui.empty icon="fa-light fa-lightbulb" :title="__('No ideas found')" :description="__('Choose another filter to see more campaign ideas.')" />
                    </div>
                @endif
            </div>
        @else
            <div>
                <div class="border-b p-5" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Draft workspace') }}</p>
                            <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Search, filter, and continue AI campaign plans before publishing.') }}</p>
                        </div>
                        <div class="text-xs font-semibold" style="color: var(--theme-muted-text-color);">
                            {{ trans_choice(':count draft|:count drafts', $drafts->total(), ['count' => $drafts->total()]) }}
                        </div>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_14rem_12rem_9rem]">
                        <x-ui.input
                            type="search"
                            wire:model.live.debounce.300ms="draftSearch"
                            name="draftSearch"
                            :label="__('Search Drafts')"
                            :placeholder="__('Search by title, prompt, or type...')"
                        />

                        <x-ui.select wire:model.live="draftType" name="draftType" :label="__('Campaign Type')">
                            @foreach ($draftTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select wire:model.live="draftStatus" name="draftStatus" :label="__('Status')">
                            @foreach ($draftStatuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select wire:model.live="draftPerPage" name="draftPerPage" :label="__('Rows')">
                            <option value="5">{{ __('5 / page') }}</option>
                            <option value="10">{{ __('10 / page') }}</option>
                            <option value="25">{{ __('25 / page') }}</option>
                            <option value="50">{{ __('50 / page') }}</option>
                        </x-ui.select>
                    </div>
                </div>

                <div class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                @forelse ($drafts as $draft)
                    @php
                        $status = (string) data_get($draft->metadata, 'status', 'draft');
                        $createdCampaignId = (int) data_get($draft->metadata, 'created_campaign_id');
                        $createdCampaign = $createdCampaignId ? $createdCampaigns->get($createdCampaignId) : null;
                        $campaignType = (string) data_get($draft->output_payload, 'campaign_type', __('Campaign'));
                        $businessId = (int) data_get($draft->input_payload, 'business_id');
                    @endphp
                    <div wire:key="ai-draft-{{ $draft->id }}" class="flex flex-col gap-4 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            @if ($renamingDraftId === $draft->id)
                                <div class="flex max-w-2xl flex-col gap-2 sm:flex-row">
                                    <input type="text" wire:model="draftTitle" class="min-w-0 flex-1 rounded-xl border px-3 py-2 text-sm font-semibold outline-none" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: color-mix(in srgb, var(--theme-surface-base) 86%, transparent); color: var(--theme-header-text-color);">
                                    <div class="flex gap-2">
                                        <button type="button" wire:click="saveDraftTitle" class="inline-flex h-9 items-center justify-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .45); background-color: rgba(var(--theme-accent-rgb), .10); color: var(--theme-accent);">{{ __('Save') }}</button>
                                        <button type="button" wire:click="cancelRenameDraft" class="inline-flex h-9 items-center justify-center rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color);">{{ __('Cancel') }}</button>
                                    </div>
                                </div>
                            @else
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="truncate text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $draft->title }}</p>
                                    <x-ui.badge variant="success">{{ $campaignType }}</x-ui.badge>
                                    <x-ui.badge :variant="$status === 'created' ? 'success' : 'warning'">{{ $status === 'created' ? __('Created') : __('Draft') }}</x-ui.badge>
                                </div>
                            @endif
                            <p class="mt-2 text-xs leading-5" style="color: var(--theme-muted-text-color);">
                                {{ __('Updated :time', ['time' => $draft->updated_at?->diffForHumans()]) }}
                                @if ($draft->prompt)
                                    <span class="mx-1">-</span>{{ Str::limit($draft->prompt, 96) }}
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                            <x-ui.button type="button" size="sm" variant="outline" wire:click="loadDraft({{ $draft->id }})" wire:loading.attr="disabled" wire:target="loadDraft">
                                <i class="fa-light fa-folder-open"></i>{{ __('Continue') }}
                            </x-ui.button>
                            @if ($status === 'created' && $createdCampaign)
                                <x-ui.button href="{{ route('portal.businesses.campaigns.show', [$createdCampaign->business_id, $createdCampaign]) }}" target="_blank" size="sm" variant="outline">
                                    <i class="fa-light fa-arrow-up-right"></i>{{ __('View Campaign') }}
                                </x-ui.button>
                            @elseif ($businessId)
                                <x-ui.button type="button" size="sm" wire:click="createCampaignFromDraft({{ $draft->id }})" wire:loading.attr="disabled" wire:target="createCampaignFromDraft">
                                    <i class="fa-light fa-plus"></i>{{ __('Create Campaign') }}
                                </x-ui.button>
                            @endif
                            <button type="button" wire:click="duplicateDraft({{ $draft->id }})" wire:loading.attr="disabled" wire:target="duplicateDraft" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.06)]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color);" title="{{ __('Duplicate') }}">
                                <i class="fa-light fa-copy"></i>
                            </button>
                            <button type="button" wire:click="startRenameDraft({{ $draft->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.06)]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color);" title="{{ __('Rename') }}">
                                <i class="fa-light fa-pen"></i>
                            </button>
                            <button type="button" wire:click="deleteDraft({{ $draft->id }})" onclick="return confirm('{{ __('Delete this draft?') }}')" wire:loading.attr="disabled" wire:target="deleteDraft" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border transition hover:bg-[color:rgba(var(--theme-danger-color-rgb),0.08)]" style="border-color: rgba(var(--theme-danger-color-rgb), .38); color: var(--theme-danger-color);" title="{{ __('Delete') }}">
                                <i class="fa-light fa-trash"></i>
                            </button>
                        </div>
                        <div class="hidden">
                            <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $draft->title }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ data_get($draft->output_payload, 'campaign_type') }} · {{ $draft->created_at?->diffForHumans() }}</p>
                        </div>
                        <span class="hidden"></span>
                    </div>
                @empty
                    <div class="p-8">
                        <x-ui.empty icon="fa-light fa-folder-open" :title="__('No saved drafts yet')" :description="__('Generated campaign plans will appear here so you can return and create campaigns later.')" />
                    </div>
                @endforelse
            </div>

                @if ($drafts->count() > 0)
                    <div class="flex flex-col gap-3 border-t px-5 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        <p class="text-sm" style="color: var(--theme-muted-text-color);">
                            {{ __('Showing') }}
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($drafts->firstItem()) }}</span> -
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($drafts->lastItem()) }}</span>
                            {{ __('of') }}
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($drafts->total()) }}</span>
                            {{ __('drafts') }}
                        </p>
                        <div class="flex items-center gap-2">
                            <x-ui.button type="button" variant="outline" wire:click="previousPage('draftsPage')" :disabled="$drafts->onFirstPage()">
                                <i class="fa-light fa-arrow-left"></i>{{ __('Previous') }}
                            </x-ui.button>
                            <span class="rounded-[0.8rem] border px-4 py-2 text-sm font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .10);">
                                {{ __('Page :page / :pages', ['page' => $drafts->currentPage(), 'pages' => max(1, $drafts->lastPage())]) }}
                            </span>
                            <x-ui.button type="button" variant="outline" wire:click="nextPage('draftsPage')" :disabled="! $drafts->hasMorePages()">
                                {{ __('Next') }}<i class="fa-light fa-arrow-right"></i>
                            </x-ui.button>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </section>
</div>
