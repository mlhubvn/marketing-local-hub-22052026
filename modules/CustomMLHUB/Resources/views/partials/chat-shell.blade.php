@php
    $compact = (bool) ($compact ?? false);
    $chatRoute = Route::has('portal.chatmlhubai') ? route('portal.chatmlhubai') : null;
    $inputId = 'mlhub-ai-question-'.($compact ? 'compact' : 'full');
    $advancedReady = method_exists($this, 'advancedAiAvailable') ? $this->advancedAiAvailable() : false;
@endphp

<section class="{{ $compact ? '' : 'space-y-5' }}">
    @unless ($compact)
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Introducing MLHUB AI') }}</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Ask MLHUB AI in natural language') }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6" style="color: var(--theme-muted-text-color);">
                    {{ __('Talk to your growth data on Portal Dashboard. Ask in plain language — get answers about campaigns, reviews, and bookings.') }}
                </p>
            </div>
        </div>
    @endunless

    <div
        class="flex w-full flex-col overflow-hidden rounded-[1.25rem] border bg-white shadow-sm {{ $compact ? 'h-[32rem]' : 'h-[calc(100vh-12rem)] min-h-[34rem]' }}"
        style="border-color: rgba(var(--theme-border-color-rgb),0.72);"
    >
        <div class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-b px-4 py-3 sm:px-5" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: rgba(var(--theme-surface-bg-rgb),0.55);">
            <div class="flex min-w-0 items-center gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-white" style="background: var(--theme-accent);">
                    <i class="fa-light fa-robot" aria-hidden="true"></i>
                </span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('MLHUB AI') }}</p>
                    <p class="truncate text-xs" style="color: var(--theme-muted-text-color);">{{ __('Growth assistant report') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div class="inline-flex items-center rounded-full border p-0.5" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background: var(--theme-surface-bg);">
                    <button
                        type="button"
                        wire:click="$set('useAdvancedAi', false)"
                        wire:loading.attr="disabled"
                        wire:target="askAssistant,askSuggested"
                        @class([
                            'rounded-full px-3 py-1.5 text-xs font-semibold transition',
                        ])
                        @style([
                            'background: var(--theme-accent); color: #fff;' => ! $useAdvancedAi,
                            'color: var(--theme-muted-text-color);' => $useAdvancedAi,
                        ])
                    >
                        <i class="fa-light fa-bolt-lightning"></i>
                        {{ __('Basic AI') }}
                    </button>
                    <button
                        type="button"
                        wire:click="$set('useAdvancedAi', true)"
                        wire:loading.attr="disabled"
                        wire:target="askAssistant,askSuggested"
                        @class([
                            'rounded-full px-3 py-1.5 text-xs font-semibold transition',
                        ])
                        @style([
                            'background: var(--theme-accent); color: #fff;' => $useAdvancedAi,
                            'color: var(--theme-muted-text-color);' => ! $useAdvancedAi,
                        ])
                    >
                        <i class="fa-light fa-wand-magic-sparkles"></i>
                        {{ __('Advanced AI') }}
                    </button>
                </div>

                @if ($compact && $chatRoute)
                    <x-ui.button href="{{ $chatRoute }}" variant="outline" size="sm" wire:navigate>
                        {{ __('Open full chat') }}
                        <i class="fa-light fa-arrow-up-right-from-square"></i>
                    </x-ui.button>
                @endif
            </div>
        </div>

        <div class="shrink-0 border-b px-4 py-2 text-xs leading-5 sm:px-5" style="border-color: rgba(var(--theme-border-color-rgb),0.5);">
            @if ($useAdvancedAi)
                @if ($advancedReady)
                    <p style="color: var(--theme-warning-color);">
                        <i class="fa-light fa-wand-magic-sparkles"></i>
                        {{ __('Advanced AI: smoother answers across more contexts, the assistant works harder for you. Each answer deducts credits.') }}
                    </p>
                @else
                    <p style="color: var(--theme-warning-color);">
                        <i class="fa-light fa-triangle-exclamation"></i>
                        {{ __('Advanced AI is not configured yet (missing API key). Using Basic AI for now.') }}
                    </p>
                @endif
            @else
                <p style="color: var(--theme-muted-text-color);">
                    <i class="fa-light fa-bolt-lightning"></i>
                    {{ __('Basic AI: quick info lookup and seamless usage guidance, no credits used.') }}
                </p>
            @endif
        </div>

        <div
            class="flex-1 space-y-3 overflow-y-auto p-4 sm:p-5"
            x-data
            x-init="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
            x-on:scroll-chat.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
        >
            @forelse ($messages as $index => $entry)
                @if (($entry['role'] ?? '') === 'user')
                    <div wire:key="mlhub-ai-user-{{ $index }}" class="ml-auto max-w-[88%] rounded-2xl rounded-br-md px-4 py-3 text-sm font-semibold leading-snug text-white" style="background: var(--theme-accent);">
                        {{ $entry['message'] }}
                    </div>
                @else
                    <div wire:key="mlhub-ai-assistant-{{ $index }}" class="flex max-w-[92%] gap-3 rounded-2xl rounded-bl-md border px-3 py-3 sm:px-4" style="border-color: rgba(var(--theme-border-color-rgb),0.55); background: rgba(var(--theme-surface-bg-rgb),0.65);">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm text-white" style="background: var(--theme-accent);">
                            <i class="fa-light fa-robot" aria-hidden="true"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="whitespace-pre-line text-sm leading-6" style="color: var(--theme-header-text-color);">{{ $entry['message'] }}</p>

                            @if (! empty($entry['actions']))
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($entry['actions'] as $action)
                                        <a
                                            href="{{ $action['url'] }}"
                                            wire:navigate
                                            class="inline-flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-xs font-semibold transition hover:border-[var(--theme-accent)]"
                                            style="border-color: rgba(var(--theme-border-color-rgb),0.72); color: var(--theme-accent); background: rgba(var(--theme-surface-bg-rgb),0.55);"
                                        >
                                            <i class="fa-light fa-arrow-up-right-from-square"></i>
                                            {{ $action['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            @if (($entry['source'] ?? '') === 'ai')
                                <p class="mt-2 text-[11px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-accent);">{{ __('AI generated') }}</p>
                            @else
                                <p class="mt-2 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Answer based on your account\'s real data, processed by the built-in AI.') }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            @empty
                <div class="rounded-2xl border border-dashed px-4 py-8 text-center" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Ask about customers, campaigns, reviews, or what to do next.') }}</p>
                    <p class="mt-2 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('MLHUB AI reads your live dashboard metrics and replies in natural language — even without OpenAI.') }}</p>
                </div>
            @endforelse

            @if ($isThinking)
                <div class="flex max-w-[92%] gap-3 rounded-2xl rounded-bl-md border px-3 py-3 sm:px-4" style="border-color: rgba(var(--theme-border-color-rgb),0.55);">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm text-white" style="background: var(--theme-accent);">
                        <i class="fa-light fa-robot" aria-hidden="true"></i>
                    </span>
                    <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Preparing your report...') }}</p>
                </div>
            @endif
        </div>

        <div class="shrink-0 border-t p-3 sm:p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: rgba(var(--theme-surface-bg-rgb),0.4);">
            @if (! empty($suggestedPrompts))
                <div class="mb-3 flex gap-2 overflow-x-auto pb-1">
                    @foreach ($suggestedPrompts as $prompt)
                        <button
                            type="button"
                            wire:click="askSuggested(@js($prompt))"
                            wire:loading.attr="disabled"
                            wire:target="askAssistant,askSuggested"
                            class="shrink-0 whitespace-nowrap rounded-full border px-3 py-1.5 text-xs font-semibold leading-5 transition hover:border-[var(--theme-accent)]"
                            style="border-color: rgba(var(--theme-border-color-rgb),0.72); color: var(--theme-header-text-color); background: var(--theme-surface-bg);"
                        >
                            {{ $prompt }}
                        </button>
                    @endforeach
                </div>
            @endif

            <form wire:submit="askAssistant">
                <div
                    class="flex items-end gap-2 rounded-2xl border bg-white px-2 py-1.5 shadow-sm transition focus-within:border-[var(--theme-accent)] focus-within:ring-2 focus-within:ring-[var(--theme-accent)]"
                    style="border-color: rgba(var(--theme-border-color-rgb),0.72);"
                >
                    <label for="{{ $inputId }}" class="sr-only">{{ __('Type a question…') }}</label>
                    <textarea
                        id="{{ $inputId }}"
                        wire:model="question"
                        x-data
                        x-on:keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); $wire.askAssistant(); }"
                        rows="{{ $compact ? 1 : 2 }}"
                        class="max-h-40 min-h-[2.5rem] w-full resize-none border-0 bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-0"
                        style="color: var(--theme-header-text-color);"
                        placeholder="{{ __('Type a question…') }}"
                        @disabled($isThinking)
                    ></textarea>
                    <x-ui.button type="submit" size="sm" class="shrink-0 rounded-xl" wire:loading.attr="disabled" wire:target="askAssistant,askSuggested">
                        <i class="fa-light fa-paper-plane-top" wire:loading.remove wire:target="askAssistant,askSuggested"></i>
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="askAssistant,askSuggested"></i>
                    </x-ui.button>
                </div>
                <p class="mt-2 text-[11px]" style="color: var(--theme-muted-text-color);">{{ __('Press Enter to send, Shift + Enter for a new line.') }}</p>
            </form>
        </div>
    </div>
</section>
