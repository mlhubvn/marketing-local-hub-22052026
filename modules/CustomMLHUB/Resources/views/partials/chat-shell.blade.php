@php
    $compact = (bool) ($compact ?? false);
    $chatRoute = Route::has('portal.chatmlhubai') ? route('portal.chatmlhubai') : null;
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

    <div class="overflow-hidden rounded-[1.25rem] border bg-white shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.72);">
        <div class="flex items-center justify-between gap-3 border-b px-4 py-3 sm:px-5" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: rgba(var(--theme-surface-bg-rgb),0.55);">
            <div class="flex min-w-0 items-center gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-white" style="background: var(--theme-accent);">
                    <i class="fa-light fa-robot" aria-hidden="true"></i>
                </span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('MLHUB AI') }}</p>
                    <p class="truncate text-xs" style="color: var(--theme-muted-text-color);">{{ __('Growth assistant report') }}</p>
                </div>
            </div>
            @if ($compact && $chatRoute)
                <x-ui.button href="{{ $chatRoute }}" variant="outline" size="sm" wire:navigate>
                    {{ __('Open full chat') }}
                    <i class="fa-light fa-arrow-up-right-from-square"></i>
                </x-ui.button>
            @endif
        </div>

        <div class="grid gap-0 {{ $compact ? '' : 'lg:grid-cols-[minmax(0,1fr)_18rem]' }}">
            <div class="min-w-0 border-b lg:border-b-0 lg:border-r" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                <div class="max-h-[28rem] space-y-3 overflow-y-auto p-4 sm:p-5" wire:loading.remove wire:target="askAssistant,askSuggested">
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

                <form wire:submit="askAssistant" class="border-t p-4 sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                    <div
                        class="flex items-end gap-2 rounded-2xl border bg-white px-2 py-1.5 shadow-sm transition focus-within:border-[var(--theme-accent)] focus-within:ring-2 focus-within:ring-[var(--theme-accent)]"
                        style="border-color: rgba(var(--theme-border-color-rgb),0.72);"
                    >
                        <label for="mlhub-ai-question-{{ $compact ? 'compact' : 'full' }}" class="sr-only">{{ __('Type a question…') }}</label>
                        <textarea
                            id="mlhub-ai-question-{{ $compact ? 'compact' : 'full' }}"
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

            <aside class="p-4 sm:p-5 {{ $compact ? 'hidden lg:block' : '' }}">
                <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Suggested questions') }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($suggestedPrompts as $prompt)
                        <button
                            type="button"
                            wire:click="askSuggested(@js($prompt))"
                            wire:loading.attr="disabled"
                            wire:target="askAssistant,askSuggested"
                            class="rounded-full border px-3 py-2 text-left text-xs font-semibold leading-5 transition hover:border-[var(--theme-accent)]"
                            style="border-color: rgba(var(--theme-border-color-rgb),0.72); color: var(--theme-header-text-color); background: rgba(var(--theme-surface-bg-rgb),0.55);"
                        >
                            {{ $prompt }}
                        </button>
                    @endforeach
                </div>
                <p class="mt-4 text-xs leading-5" style="color: var(--theme-muted-text-color);">
                    {{ __('Without OpenAI, MLHUB AI still reports live numbers from your account. With OpenAI enabled, answers become more conversational and contextual.') }}
                </p>
            </aside>
        </div>
    </div>
</section>
