<?php

namespace Modules\CustomMLHUB\Livewire\Concerns;

use Modules\CustomMLHUB\Support\MLHUBAIAssistant\MLHUBAIAssistantService;
use Modules\CustomMLHUB\Support\MLHUBAIAssistant\MLHUBAIIntentResolver;

trait InteractsWithMLHUBAIAssistant
{
    /** @var list<array{role: string, message: string, source?: string|null}> */
    public array $messages = [];

    public string $question = '';

    public bool $isThinking = false;

    /** @var list<string> */
    public array $suggestedPrompts = [];

    public function mountAssistant(): void
    {
        abort_unless(auth()->user()?->canUsePlanFeature('mlhub') ?? false, 403);

        $this->suggestedPrompts = app(MLHUBAIIntentResolver::class)->suggestedPrompts();
    }

    public function askAssistant(MLHUBAIAssistantService $assistant): void
    {
        abort_unless(auth()->user()?->canUsePlanFeature('mlhub') ?? false, 403);

        $validated = $this->validate([
            'question' => ['required', 'string', 'min:2', 'max:500'],
        ]);

        $prompt = trim($validated['question']);
        $this->messages[] = ['role' => 'user', 'message' => $prompt];
        $this->question = '';
        $this->isThinking = true;

        $response = $assistant->ask((int) auth()->id(), $prompt);

        $this->messages[] = [
            'role' => 'assistant',
            'message' => (string) $response['message'],
            'source' => (string) ($response['source'] ?? 'fallback'),
            'fallback_reason' => $response['fallback_reason'] ?? null,
        ];

        $this->suggestedPrompts = (array) ($response['suggestions'] ?? $this->suggestedPrompts);
        $this->isThinking = false;
    }

    public function askSuggested(string $prompt, MLHUBAIAssistantService $assistant): void
    {
        $this->question = $prompt;
        $this->askAssistant($assistant);
    }
}
