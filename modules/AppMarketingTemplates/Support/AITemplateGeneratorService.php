<?php

namespace Modules\AppMarketingTemplates\Support;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\AdminSettings\Support\OptionStore;
use RuntimeException;
use Throwable;

class AITemplateGeneratorService
{
    public function __construct(
        protected OptionStore $options,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function generate(array $payload): array
    {
        $fallback = $this->generateFallback($payload);

        if ((string) $this->options->get('ai_chat_status', '1') !== '1') {
            return $this->markFallback($fallback, __('AI chat generation is disabled.'));
        }

        $provider = strtolower(trim((string) $this->options->get('ai_chat_provider', 'openai')));
        $model = trim((string) $this->options->get('ai_chat_model', 'gpt-5.4'));

        if (! $this->hasProviderCredentials($provider)) {
            return $this->markFallback($fallback, __('AI provider credentials are missing.'));
        }

        $startedAt = microtime(true);

        try {
            $response = match ($provider) {
                'openai' => $this->requestOpenAi($this->systemPrompt(), $this->userPrompt($payload), $model),
                'gemini' => $this->requestGemini($this->systemPrompt(), $this->userPrompt($payload), $model),
                default => throw new RuntimeException(__('The selected AI provider is not supported for template generation yet.')),
            };

            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => $provider,
                    'capability' => 'content',
                    'model' => $model,
                    'feature' => 'template-engine.generate',
                    'status' => 'success',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                ]);
            }

            return $this->normalizeAiTemplate($response, $fallback, $provider, $model);
        } catch (Throwable $exception) {
            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => $provider,
                    'capability' => 'content',
                    'model' => $model,
                    'feature' => 'template-engine.generate',
                    'status' => 'error',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                    'error_message' => $exception->getMessage(),
                ]);
            }

            return $this->markFallback($fallback, $exception->getMessage());
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function generateFallback(array $payload): array
    {
        $category = str((string) $payload['category'])->replace('_', ' ')->headline()->toString();
        $goal = (string) $payload['goal'];
        $tone = (string) ($payload['tone'] ?? 'friendly');
        $language = $this->languageLabel((string) ($payload['language'] ?? 'en'));
        $offer = trim((string) ($payload['offer'] ?? ''));
        $offerText = $offer !== '' ? $offer : match ($goal) {
            'review' => 'a simple review request',
            'booking' => 'a limited booking opportunity',
            'coupon', 'retention' => 'a limited-time local offer',
            'feedback' => 'a quick post-visit feedback request',
            default => 'a helpful local customer offer',
        };
        $goalLabel = str($goal)->replace('_', ' ')->headline()->toString();
        $name = $category.' '.$goalLabel.' Template';
        $headline = match ($goal) {
            'review' => 'How was your visit with {business_name}?',
            'booking' => 'Book your next visit with {business_name}',
            'coupon', 'retention' => 'Claim '.$offerText,
            'feedback' => 'Tell {business_name} how we did',
            default => 'Get started with {business_name}',
        };
        $cta = match ($goal) {
            'review' => 'Leave a Review',
            'booking' => 'Book Now',
            'coupon', 'retention' => 'Claim Offer',
            'feedback' => 'Send Feedback',
            default => 'Get Started',
        };

        return [
            'name' => $name,
            'description' => 'Generated '.$tone.' '.$language.' template for '.$category.' businesses focused on '.$goalLabel.'.',
            'icon' => match ($goal) {
                'review' => 'fa-light fa-star',
                'booking' => 'fa-light fa-calendar-check',
                'coupon', 'retention' => 'fa-light fa-ticket',
                'feedback' => 'fa-light fa-comment-dots',
                default => 'fa-light fa-wand-magic-sparkles',
            },
            'settings' => [
                'target_module' => $goal,
                'campaign_type' => in_array($goal, ['review', 'booking', 'coupon', 'feedback', 'lead'], true) ? $goal : 'lead',
                'creates_campaign' => $payload['campaign_type'] === 'campaign',
                'creates_landing_page' => in_array($payload['campaign_type'], ['campaign', 'landing_page'], true),
                'creates_qr_code' => in_array($payload['campaign_type'], ['campaign', 'landing_page', 'form'], true),
                'creates_tracking' => true,
                'tracking_goal' => $goal === 'retention' ? 'coupon_claim' : $goal,
                'default_status' => 'draft',
                'tone' => $tone,
                'language' => $payload['language'],
            ],
            'content' => [
                'campaign_name' => $name,
                'headline' => $headline,
                'description' => 'A '.$tone.' local campaign for '.$category.' customers. Offer: '.$offerText.'.',
                'cta' => $cta,
                'benefits' => ['Easy to use', 'Mobile-first experience', 'Track every result'],
                'thank_you_message' => 'Thank you. We will follow up soon.',
                'form_fields' => [
                    ['name' => 'name', 'type' => 'text', 'label' => 'Your name', 'required' => true],
                    ['name' => 'phone', 'type' => 'phone', 'label' => 'Phone number', 'required' => true],
                    ['name' => 'email', 'type' => 'email', 'label' => 'Email address', 'required' => false],
                    ['name' => 'message', 'type' => 'textarea', 'label' => 'Message', 'required' => false],
                ],
                'landing_page_blocks' => [
                    ['type' => 'hero', 'title' => 'Hero', 'visible' => true, 'settings' => ['headline' => $headline, 'body' => $offerText, 'cta' => $cta]],
                    ['type' => 'benefits', 'title' => 'Benefits', 'visible' => true, 'settings' => ['items' => ['Fast response', 'Simple form', 'Local business follow-up']]],
                    ['type' => 'form', 'title' => 'Lead Form', 'visible' => true, 'settings' => ['submit_button' => $cta]],
                    ['type' => 'thank_you', 'title' => 'Thank You', 'visible' => true, 'settings' => ['headline' => 'Thank you']],
                ],
                'prompt_template' => 'Create '.$tone.' '.$language.' copy for {business_name}. Goal: '.$goal.'. Offer: {offer}. CTA: {cta}.',
                'variables' => ['business_name', 'business_category', 'customer_name', 'offer', 'service_name', 'discount_value', 'expiry_date', 'cta'],
                'email_content' => [
                    'subject' => $headline,
                    'body' => $headline."\n\n".$offerText."\n\n".$cta,
                ],
                'whatsapp_content' => [
                    'message' => $headline.' '.$offerText.' '.$cta,
                ],
                'qr_text' => 'Scan to open this '.$category.' '.$goalLabel.' campaign.',
            ],
            'design' => [
                'theme' => 'clean',
                'accent_color' => match ($goal) {
                    'review' => '#d09100',
                    'booking' => '#0f766e',
                    'coupon', 'retention' => '#84a900',
                    'feedback' => '#0891b2',
                    default => '#2563eb',
                },
                'layout' => 'mobile_first',
            ],
            'builder_schema' => [
                'form_builder' => true,
                'landing_page_blocks' => in_array($payload['campaign_type'], ['campaign', 'landing_page'], true),
                'campaign_settings' => $payload['campaign_type'] === 'campaign',
                'prompt_variables' => in_array($payload['campaign_type'], ['content', 'email', 'whatsapp'], true),
            ],
        ];
    }

    protected function systemPrompt(): string
    {
        return implode("\n", [
            'You are the Template Engine generator for MLHUB AI, a platform for local-business marketing.',
            'Return one complete production-ready template as valid JSON only.',
            'Do not wrap the JSON in markdown.',
            'Use practical copy, realistic local-business defaults, and concise UI-safe text.',
            'The JSON must use this shape: name, description, icon, settings, content, design, builder_schema.',
            'content must include: campaign_name, headline, description, cta, benefits, thank_you_message, form_fields, landing_page_blocks, prompt_template, variables, email_content, whatsapp_content, qr_text.',
            'settings must include: target_module, campaign_type, creates_campaign, creates_landing_page, creates_qr_code, creates_tracking, tracking_goal, default_status, tone, language.',
            'form_fields items need name, type, label, required, placeholder, help_text, options where relevant.',
            'landing_page_blocks items need type, title, visible, settings.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function userPrompt(array $payload): string
    {
        return 'Create a MLHUB AI template from this brief: '.json_encode([
            'business_category' => (string) ($payload['category'] ?? 'local service'),
            'goal' => (string) ($payload['goal'] ?? 'lead'),
            'campaign_type' => (string) ($payload['campaign_type'] ?? 'campaign'),
            'offer' => (string) ($payload['offer'] ?? ''),
            'tone' => (string) ($payload['tone'] ?? 'friendly'),
            'language' => $this->languageLabel((string) ($payload['language'] ?? 'en')),
            'language_code' => (string) ($payload['language'] ?? 'en'),
            'required_template_types' => ['campaign', 'landing_page', 'form', 'content', 'email', 'whatsapp', 'automation'],
            'allowed_field_types' => ['text', 'email', 'phone', 'textarea', 'select', 'radio', 'checkbox', 'date', 'time', 'rating', 'hidden'],
            'allowed_block_types' => ['hero', 'benefits', 'form', 'offer', 'coupon_details', 'booking_services', 'business_info', 'social_links', 'faq', 'testimonials', 'map', 'opening_hours', 'thank_you', 'custom_html'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function languageLabel(string $language): string
    {
        $language = strtolower(trim($language));

        if ($language === '') {
            return 'English';
        }

        $matched = collect(world_languages())->first(
            fn ($item) => strtolower((string) data_get($item, 'code')) === $language
                || strtolower((string) data_get($item, 'name')) === $language
        );

        return (string) data_get($matched, 'name', strtoupper($language));
    }

    /**
     * @param  array<string, mixed>  $response
     * @param  array<string, mixed>  $fallback
     * @return array<string, mixed>
     */
    protected function normalizeAiTemplate(array $response, array $fallback, string $provider, string $model): array
    {
        $template = $fallback;

        foreach (['name', 'description', 'icon'] as $key) {
            $value = trim($this->stringValue($response[$key] ?? ($key === 'name' ? ($response['template_name'] ?? '') : '')));

            if ($value !== '') {
                $template[$key] = $value;
            }
        }

        foreach (['settings', 'content', 'design', 'builder_schema'] as $key) {
            if (isset($response[$key]) && is_array($response[$key])) {
                $template[$key] = array_replace_recursive((array) ($template[$key] ?? []), $response[$key]);
            }
        }

        foreach (['form_fields', 'landing_page_blocks', 'email_content', 'whatsapp_content'] as $key) {
            if (isset($response[$key]) && is_array($response[$key])) {
                $template['content'][$key] = $response[$key];
            }
        }

        if (isset($response['tracking_goal'])) {
            $template['settings']['tracking_goal'] = $this->stringValue($response['tracking_goal']);
        }

        $template['settings']['ai_generation'] = [
            'provider' => $provider,
            'model' => $model,
            'status' => 'success',
        ];

        return $template;
    }

    /**
     * @param  array<string, mixed>  $template
     * @return array<string, mixed>
     */
    protected function markFallback(array $template, string $reason): array
    {
        $template['settings']['ai_generation'] = [
            'provider' => 'rule_based_fallback',
            'model' => null,
            'status' => 'fallback',
            'reason' => Str::limit($reason, 180),
        ];

        return $template;
    }

    protected function hasProviderCredentials(string $provider): bool
    {
        return match ($provider) {
            'openai' => trim((string) $this->options->get('ai_openai_api_key', '')) !== '',
            'gemini' => trim((string) $this->options->get('ai_gemini_api_key', '')) !== '',
            default => false,
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function requestOpenAi(string $systemPrompt, string $userPrompt, string $model): array
    {
        $apiKey = trim((string) $this->options->get('ai_openai_api_key', ''));
        $baseUrl = rtrim((string) $this->options->get('ai_openai_url', 'https://api.openai.com/v1'), '/');

        if ($apiKey === '') {
            throw new RuntimeException(__('OpenAI API key is missing.'));
        }

        $response = Http::timeout(120)
            ->withToken($apiKey)
            ->acceptJson()
            ->post($baseUrl.'/chat/completions', [
                'model' => $model,
                'temperature' => 0.7,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($this->responseErrorMessage($response, __('AI request failed.')));
        }

        $decoded = json_decode((string) data_get($response->json(), 'choices.0.message.content', ''), true);

        if (! is_array($decoded)) {
            throw new RuntimeException(__('AI returned invalid JSON.'));
        }

        return $decoded;
    }

    /**
     * @return array<string, mixed>
     */
    protected function requestGemini(string $systemPrompt, string $userPrompt, string $model): array
    {
        $apiKey = trim((string) $this->options->get('ai_gemini_api_key', ''));

        if ($apiKey === '') {
            throw new RuntimeException(__('Gemini API key is missing.'));
        }

        $response = Http::timeout(120)
            ->acceptJson()
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [[
                    'parts' => [[
                        'text' => $systemPrompt."\n\n".$userPrompt,
                    ]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($this->responseErrorMessage($response, __('AI request failed.')));
        }

        $text = collect((array) data_get($response->json(), 'candidates.0.content.parts', []))
            ->map(fn ($part) => $this->stringValue(data_get($part, 'text', '')))
            ->filter(fn ($text) => trim($text) !== '')
            ->implode("\n");

        $decoded = json_decode($text, true);

        if (! is_array($decoded)) {
            throw new RuntimeException(__('AI returned invalid JSON.'));
        }

        return $decoded;
    }

    protected function responseErrorMessage(Response $response, string $fallback): string
    {
        $message = trim($this->stringValue(data_get($response->json(), 'error.message', '')));

        if ($message === '') {
            $message = trim($this->stringValue(data_get($response->json(), 'message', '')));
        }

        if ($message === '') {
            $message = trim((string) $response->body());
        }

        return $message === '' ? $fallback : Str::limit($message, 280);
    }

    protected function stringValue(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_null($value)) {
            return '';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            $text = data_get($value, 'text')
                ?? data_get($value, 'content')
                ?? data_get($value, 'caption')
                ?? data_get($value, 'value')
                ?? data_get($value, 'message');

            if ($text !== null && $text !== $value) {
                return $this->stringValue($text);
            }

            return trim(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return '';
    }
}
