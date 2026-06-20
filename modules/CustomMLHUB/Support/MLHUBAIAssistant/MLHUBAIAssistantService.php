<?php

namespace Modules\CustomMLHUB\Support\MLHUBAIAssistant;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AppTeams\Support\TeamWorkspaceAccess;
use Modules\AdminUser\Models\User;
use RuntimeException;
use Throwable;

class MLHUBAIAssistantService
{
    public function __construct(
        protected OptionStore $options,
        protected MLHUBAIContextBuilder $contextBuilder,
        protected MLHUBAIIntentResolver $intentResolver,
        protected MLHUBAIResponseComposer $responseComposer,
    ) {}

    /**
     * @return array{
     *     message: string,
     *     source: string,
     *     intent: string,
     *     fallback_reason: string|null,
     *     suggestions: list<string>
     * }
     */
    public function ask(int $userId, string $question): array
    {
        $question = trim($question);

        if ($question === '') {
            return [
                'message' => __('Type a question about your customers, campaigns, reviews, or visits.'),
                'source' => 'fallback',
                'intent' => 'empty',
                'fallback_reason' => null,
                'suggestions' => $this->intentResolver->suggestedPrompts(),
            ];
        }

        $context = $this->contextBuilder->build($userId);
        $matches = $this->intentResolver->resolveAll($question);
        $intents = array_map(static fn (array $match): string => $match['intent'], $matches);
        $primaryIntent = $intents[0] ?? 'unknown';
        $fallbackMessage = $this->responseComposer->composeMany($intents, $context);

        $result = [
            'message' => $fallbackMessage,
            'source' => 'fallback',
            'intent' => $primaryIntent,
            'fallback_reason' => null,
            'suggestions' => $this->intentResolver->suggestedPrompts(),
        ];

        if ((string) $this->options->get('ai_chat_status', '1') !== '1') {
            $result['fallback_reason'] = __('AI chat generation is disabled.');

            return $result;
        }

        $provider = strtolower(trim((string) $this->options->get('ai_chat_provider', 'openai')));

        if (! $this->hasProviderCredentials($provider)) {
            $result['fallback_reason'] = __('OpenAI API key is missing.');

            return $result;
        }

        try {
            $user = auth()->user();
            $team = TeamWorkspaceAccess::activeTeam($user);
            $planOwner = $team?->owner ?: $user;

            if (function_exists('credit_service') && $planOwner instanceof User) {
                credit_service()->ensureCanConsume($planOwner, 'mlhub_ai_chat');
            }

            $aiMessage = $this->requestAssistantReply($provider, $question, $context, $fallbackMessage);

            if ($aiMessage !== '') {
                $result['message'] = $aiMessage;
                $result['source'] = 'ai';
                $result['fallback_reason'] = null;

                if (function_exists('consume_credits') && $planOwner instanceof User) {
                    consume_credits($planOwner, 'mlhub_ai_chat', [
                        'feature' => 'mlhub.ai-assistant',
                        'metadata' => [
                            'intent' => $primaryIntent,
                            'intents' => $intents,
                            'provider' => $provider,
                        ],
                    ]);
                }
            }
        } catch (Throwable $exception) {
            $result['fallback_reason'] = $exception->getMessage();
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function requestAssistantReply(string $provider, string $question, array $context, string $fallbackMessage): string
    {
        $model = trim((string) $this->options->get('ai_chat_model', 'gpt-5.4'));
        $systemPrompt = implode("\n", [
            'You are MLHUB AI, a concise Vietnamese-first local business growth assistant.',
            'Answer like a trusted staff member reporting to the shop owner — warm, clear, no jargon.',
            'Use ONLY numbers and facts from the provided JSON context. Never invent metrics.',
            'If the context is sparse, say what is missing and suggest the next practical step.',
            'Keep answers under 120 words unless the user asks for detail.',
            'Prefer complete sentences over bullet lists.',
            'App locale: '.app()->getLocale().'.',
            'Baseline local answer for reference (do not copy blindly if context differs): '.$fallbackMessage,
        ]);

        $userPrompt = trim(implode("\n\n", [
            'Business context JSON:',
            json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'User question:',
            $question,
        ]));

        $startedAt = microtime(true);

        try {
            $message = match ($provider) {
                'openai' => $this->requestOpenAi($systemPrompt, $userPrompt, $model),
                'gemini' => $this->requestGemini($systemPrompt, $userPrompt, $model),
                default => throw new RuntimeException(__('The selected AI provider is not supported.')),
            };

            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => $provider,
                    'capability' => 'chat',
                    'model' => $model,
                    'feature' => 'mlhub.ai-assistant',
                    'status' => 'success',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                ]);
            }

            return trim($message);
        } catch (Throwable $exception) {
            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => $provider,
                    'capability' => 'chat',
                    'model' => $model,
                    'feature' => 'mlhub.ai-assistant',
                    'status' => 'error',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                    'error_message' => $exception->getMessage(),
                ]);
            }

            throw $exception;
        }
    }

    protected function requestOpenAi(string $systemPrompt, string $userPrompt, string $model): string
    {
        $apiKey = trim((string) $this->options->get('ai_openai_api_key', ''));
        $baseUrl = rtrim((string) $this->options->get('ai_openai_url', 'https://api.openai.com/v1'), '/');

        if ($apiKey === '') {
            throw new RuntimeException(__('OpenAI API key is missing.'));
        }

        $response = Http::timeout(90)
            ->withToken($apiKey)
            ->acceptJson()
            ->post($baseUrl.'/chat/completions', [
                'model' => $model,
                'temperature' => 0.4,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($this->responseErrorMessage($response, __('AI request failed.')));
        }

        return trim((string) data_get($response->json(), 'choices.0.message.content', ''));
    }

    protected function requestGemini(string $systemPrompt, string $userPrompt, string $model): string
    {
        $apiKey = trim((string) $this->options->get('ai_gemini_api_key', ''));

        if ($apiKey === '') {
            throw new RuntimeException(__('Gemini API key is missing.'));
        }

        $response = Http::timeout(90)
            ->acceptJson()
            ->post('https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent?key='.$apiKey, [
                'systemInstruction' => ['parts' => [['text' => $systemPrompt]]],
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $userPrompt]]],
                ],
                'generationConfig' => [
                    'temperature' => 0.4,
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($this->responseErrorMessage($response, __('AI request failed.')));
        }

        return trim((string) data_get($response->json(), 'candidates.0.content.parts.0.text', ''));
    }

    protected function hasProviderCredentials(string $provider): bool
    {
        return match ($provider) {
            'openai' => trim((string) $this->options->get('ai_openai_api_key', '')) !== '',
            'gemini' => trim((string) $this->options->get('ai_gemini_api_key', '')) !== '',
            default => false,
        };
    }

    protected function responseErrorMessage(Response $response, string $fallback): string
    {
        $message = data_get($response->json(), 'error.message')
            ?? data_get($response->json(), 'message')
            ?? $response->body();

        $message = trim(strip_tags((string) $message));

        return $message !== '' ? $message : $fallback;
    }
}
