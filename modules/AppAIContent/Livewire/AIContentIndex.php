<?php

namespace Modules\AppAIContent\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AppAIStudio\Models\AIPromptHistory;
use Modules\AppAIStudio\Support\AiContentStudioService;
use Modules\AppAIStudio\Support\AIStudioAccess;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Throwable;

#[Title('Content Writer')]
class AIContentIndex extends Component
{
    use AIStudioAccess;
    use WithPagination;

    public string $tab = 'writer';
    public string $business_id = '';
    public string $content_type = 'review_request';
    public string $goal = '';
    public string $offer = '';
    public string $target_customer = '';
    public string $tone = 'professional';
    public string $language = 'en';
    public string $extra_details = '';
    public string $source_type = '';
    public string $source_id = '';
    public string $campaign_id = '';
    public ?array $result = null;
    public ?string $statusMessage = null;
    public ?string $aiSource = null;

    public string $savedSearch = '';
    public string $savedType = 'all';
    public int $perPage = 10;

    public function mount(OptionStore $options): void
    {
        $this->tone = $this->normalizeTone((string) $this->aiStudioSetting('default_tone', $options->get('ai_default_tone_of_voice', 'professional')));
        $this->language = $this->normalizeLanguageCode((string) $this->aiStudioSetting('default_language', $options->get('ai_default_language', auth()->user()?->locale ?: app()->getLocale() ?: 'en')));

        $business = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->first();

        if ($business) {
            $this->business_id = (string) request('business_id', $business->id);
        }

        $this->content_type = (string) request('type', $this->content_type);
        $this->goal = trim((string) request('goal', 'Ask customers to take the next local marketing action.'));
        $this->offer = trim((string) request('offer', ''));
        $this->target_customer = trim((string) request('target_customer', 'Local customers'));
        $this->extra_details = trim((string) request('details', ''));
        $this->source_type = trim((string) request('source_type', ''));
        $this->source_id = trim((string) request('source_id', ''));
        $this->campaign_id = trim((string) request('campaign_id', ''));
        $this->syncDefaultsForType();
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['writer', 'saved'], true)) {
            $this->tab = $tab;
        }
    }

    public function updatedContentType(): void
    {
        $this->syncDefaultsForType();
    }

    public function updatedSavedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSavedType(): void
    {
        if (! array_key_exists($this->savedType, ['all' => ''] + $this->contentTypeLabels())) {
            $this->savedType = 'all';
        }

        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->perPage = in_array((int) $this->perPage, [10, 25, 50], true) ? (int) $this->perPage : 10;
        $this->resetPage();
    }

    public function generate(AiContentStudioService $studio): void
    {
        abort_unless($this->aiStudioFeatureEnabled('ai_studio_caption_generator'), 404);

        $payload = $this->validatedPayload();
        $business = $this->businessForPayload($payload);
        $fallback = $this->fallbackContent($business, $payload);

        $this->result = $fallback;
        $this->aiSource = 'fallback';

        try {
            $planOwner = $this->aiStudioPlanOwner();

            if (function_exists('credit_service')) {
                credit_service()->ensureCanConsume($planOwner, 'ai_studio_generate_captions');
            }

            $aiResult = $studio->generateLocalMarketingContent([
                'business_name' => $business->name,
                'business_type' => $business->type ?: __('Local business'),
                'content_type' => $this->contentTypeLabels()[$payload['content_type']] ?? $payload['content_type'],
                'goal' => $payload['goal'],
                'offer' => $payload['offer'],
                'target_customer' => $payload['target_customer'],
                'tone' => $payload['tone'],
                'language' => $this->languageLabel($payload['language']),
                'extra_details' => $payload['extra_details'],
                ...$this->aiStudioWorkspacePromptConfig(),
            ]);

            $this->result = array_replace($fallback, $aiResult, ['source' => 'ai']);
            $this->aiSource = 'ai';
            $this->statusMessage = __('Content generated.');

            if (function_exists('consume_credits')) {
                consume_credits($planOwner, 'ai_studio_generate_captions', [
                    'feature' => 'localboost.content-writer',
                    'metadata' => [
                        'content_type' => $payload['content_type'],
                        'language' => $payload['language'],
                        'tone' => $payload['tone'],
                    ],
                ]);
            }
        } catch (Throwable $exception) {
            $this->result = array_replace($fallback, [
                'source' => 'fallback',
                'fallback_reason' => $exception->getMessage(),
            ]);
            $this->statusMessage = __('AI unavailable. A fallback content set was generated.');
        }
    }

    public function makeShorter(AiContentStudioService $studio): void
    {
        $this->extra_details = trim($this->extra_details."\n\nMake the content shorter and easier to scan.");
        $this->generate($studio);
    }

    public function makeLonger(AiContentStudioService $studio): void
    {
        $this->extra_details = trim($this->extra_details."\n\nExpand the content with more useful detail while keeping it practical.");
        $this->generate($studio);
    }

    public function makeFriendly(AiContentStudioService $studio): void
    {
        $this->tone = 'friendly';
        $this->generate($studio);
    }

    public function saveContent(): void
    {
        if (! $this->result) {
            $this->generate(app(AiContentStudioService::class));
        }

        $payload = $this->validatedPayload();
        $business = $this->businessForPayload($payload);
        $title = trim((string) data_get($this->result, 'title'));
        $sourceMetadata = $this->sourceMetadata();

        if ($title === '') {
            $title = ($this->contentTypeLabels()[$payload['content_type']] ?? __('Content')).' - '.$business->name;
        }

        app(AiContentStudioService::class)->recordPromptHistory(
            auth()->user(),
            'content_writer',
            $this->buildPromptSummary($business, $payload),
            [
                'business_id' => $business->id,
                ...$payload,
                ...$sourceMetadata,
            ],
            $this->result ?: [],
            [
                'title' => $title,
                'language' => $payload['language'],
                'tone' => $payload['tone'],
                'metadata' => [
                    'content_type' => $payload['content_type'],
                    'business_id' => $business->id,
                    'source' => $this->aiSource ?: 'fallback',
                    ...$sourceMetadata,
                ],
            ],
        );

        $this->statusMessage = __('Content saved.');
    }

    public function loadHistory(int $id): void
    {
        $history = $this->historyQuery()->findOrFail($id);

        $this->business_id = (string) data_get($history->input_payload, 'business_id', $this->business_id);
        $this->content_type = (string) data_get($history->input_payload, 'content_type', $this->content_type);
        $this->goal = (string) data_get($history->input_payload, 'goal', $this->goal);
        $this->offer = (string) data_get($history->input_payload, 'offer', $this->offer);
        $this->target_customer = (string) data_get($history->input_payload, 'target_customer', $this->target_customer);
        $this->tone = $this->normalizeTone((string) data_get($history->input_payload, 'tone', $this->tone));
        $this->language = $this->normalizeLanguageCode((string) data_get($history->input_payload, 'language', $this->language));
        $this->extra_details = (string) data_get($history->input_payload, 'extra_details', $this->extra_details);
        $this->source_type = (string) data_get($history->input_payload, 'source_type', data_get($history->metadata, 'source_type', ''));
        $this->source_id = (string) data_get($history->input_payload, 'source_id', data_get($history->metadata, 'source_id', ''));
        $this->campaign_id = (string) data_get($history->input_payload, 'campaign_id', data_get($history->metadata, 'campaign_id', ''));
        $this->result = $history->output_payload ?: null;
        $this->aiSource = (string) data_get($history->metadata, 'source', 'saved');
        $this->tab = 'writer';
        $this->statusMessage = __('Saved content loaded.');
    }

    public function deleteHistory(int $id): void
    {
        $this->historyQuery()->whereKey($id)->delete();
        $this->statusMessage = __('Saved content deleted.');
    }

    public function render(): View
    {
        abort_unless($this->aiStudioFeatureEnabled('ai_studio_caption_generator'), 404);

        return view('appaicontent::index', [
            'businesses' => LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get(),
            'contentTypes' => $this->contentTypeLabels(),
            'toneOptions' => $this->toneOptions(),
            'savedContent' => $this->historyQuery()
                ->latest()
                ->paginate($this->perPage),
            'creditPreview' => $this->aiStudioCreditPreview('ai_studio_generate_captions'),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Content Writer'),
        ]);
    }

    protected function validatedPayload(): array
    {
        return $this->validate([
            'business_id' => ['required', 'integer'],
            'content_type' => ['required', 'string', 'max:80'],
            'goal' => ['required', 'string', 'max:500'],
            'offer' => ['nullable', 'string', 'max:500'],
            'target_customer' => ['nullable', 'string', 'max:180'],
            'tone' => ['required', 'string', 'max:40'],
            'language' => ['required', 'string', 'max:80'],
            'extra_details' => ['nullable', 'string', 'max:3000'],
            'source_type' => ['nullable', 'string', 'max:80'],
            'source_id' => ['nullable', 'string', 'max:80'],
            'campaign_id' => ['nullable', 'string', 'max:80'],
        ]);
    }

    protected function sourceMetadata(): array
    {
        return array_filter([
            'source_type' => $this->source_type !== '' ? $this->source_type : null,
            'source_id' => $this->source_id !== '' ? $this->source_id : null,
            'campaign_id' => $this->campaign_id !== '' ? $this->campaign_id : null,
        ], static fn ($value) => $value !== null && $value !== '');
    }

    protected function businessForPayload(array $payload): LocalBusiness
    {
        return LocalBusiness::query()
            ->where('user_id', auth()->id())
            ->findOrFail((int) $payload['business_id']);
    }

    protected function historyQuery()
    {
        return AIPromptHistory::query()
            ->ownedBy($this->workspaceOwnerUserId())
            ->where('module', 'content_writer')
            ->when($this->savedType !== 'all', fn ($query) => $query->where('metadata->content_type', $this->savedType))
            ->when(trim($this->savedSearch) !== '', function ($query): void {
                $search = trim($this->savedSearch);
                $query->where(function ($builder) use ($search): void {
                    $builder->where('title', 'like', '%'.$search.'%')
                        ->orWhere('prompt', 'like', '%'.$search.'%');
                });
            });
    }

    protected function syncDefaultsForType(): void
    {
        $defaults = [
            'review_request' => ['goal' => 'Ask customers to leave a Google review after visiting.', 'offer' => '', 'target' => 'Happy recent customers'],
            'coupon_message' => ['goal' => 'Promote a limited-time coupon offer.', 'offer' => '20% off next visit this weekend', 'target' => 'New and returning local customers'],
            'booking_reminder' => ['goal' => 'Remind customers about an upcoming appointment.', 'offer' => '', 'target' => 'Booked customers'],
            'feedback_request' => ['goal' => 'Ask customers to share private feedback after service.', 'offer' => '', 'target' => 'Recent customers'],
            'lead_follow_up' => ['goal' => 'Follow up with a new lead and invite the next step.', 'offer' => 'Free consultation', 'target' => 'New leads'],
            'social_post' => ['goal' => 'Promote a local business update or offer on social media.', 'offer' => '', 'target' => 'Local audience'],
            'whatsapp_sms' => ['goal' => 'Send a short direct message to local customers.', 'offer' => '', 'target' => 'Opted-in customers'],
            'thank_you' => ['goal' => 'Thank customers after a visit or purchase.', 'offer' => '', 'target' => 'Recent customers'],
            'business_description' => ['goal' => 'Write a clear description for the business profile.', 'offer' => '', 'target' => 'People discovering the business'],
            'landing_page_copy' => ['goal' => 'Write concise copy for a public landing page.', 'offer' => '', 'target' => 'Campaign visitors'],
        ];

        if (! array_key_exists($this->content_type, $this->contentTypeLabels())) {
            $this->content_type = 'review_request';
        }

        $preset = $defaults[$this->content_type] ?? $defaults['review_request'];

        if (trim($this->goal) === '' || $this->goal === 'Ask customers to take the next local marketing action.') {
            $this->goal = $preset['goal'];
        }

        if (trim($this->offer) === '') {
            $this->offer = $preset['offer'];
        }

        if (trim($this->target_customer) === '' || $this->target_customer === 'Local customers') {
            $this->target_customer = $preset['target'];
        }
    }

    protected function fallbackContent(LocalBusiness $business, array $payload): array
    {
        $typeLabel = $this->contentTypeLabels()[$payload['content_type']] ?? __('Marketing Content');
        $businessName = $business->name;
        $offer = trim((string) $payload['offer']);
        $goal = trim((string) $payload['goal']);
        $cta = $this->ctaForType($payload['content_type']);
        $base = trim(match ($payload['content_type']) {
            'review_request' => __('Thank you for visiting :business. If you enjoyed your experience, we would really appreciate your review. Your feedback helps our local business grow and serve you better.', ['business' => $businessName]),
            'coupon_message' => __('This offer is available for a limited time: :offer. Claim it today and come back for another great experience at :business.', ['offer' => $offer ?: __('a special local discount'), 'business' => $businessName]),
            'booking_reminder' => __('Hi! This is a friendly reminder about your upcoming appointment with :business. We look forward to seeing you soon.', ['business' => $businessName]),
            'feedback_request' => __('We would love to hear about your experience with :business. Your private feedback helps us improve and continue serving you better.', ['business' => $businessName]),
            'lead_follow_up' => __('Thanks for your interest in :business. We would be happy to help with your request and guide you through the next step.', ['business' => $businessName]),
            'social_post' => __('Local update from :business: :goal. :offer', ['business' => $businessName, 'goal' => $goal, 'offer' => $offer]),
            'whatsapp_sms' => __('Hi from :business! :goal :offer Reply here if you would like help with the next step.', ['business' => $businessName, 'goal' => $goal, 'offer' => $offer]),
            'thank_you' => __('Thank you for choosing :business. We appreciate your visit and look forward to welcoming you again soon.', ['business' => $businessName]),
            'business_description' => __(':business helps local customers with friendly, reliable service and a simple experience from first contact to follow-up.', ['business' => $businessName]),
            'landing_page_copy' => __('Discover :business and take the next step today. :goal :offer', ['business' => $businessName, 'goal' => $goal, 'offer' => $offer]),
            default => __(':business: :goal :offer', ['business' => $businessName, 'goal' => $goal, 'offer' => $offer]),
        });

        return [
            'title' => $typeLabel.' - '.$businessName,
            'generated_content' => $base,
            'short_version' => Str::limit($base, 140, ''),
            'professional_version' => $base.' '.__('Please contact us if you have any questions.'),
            'friendly_version' => $base.' '.__('We would love to hear from you.'),
            'cta_suggestions' => [$cta, __('Contact us today'), __('Learn more')],
            'hashtags' => in_array($payload['content_type'], ['social_post', 'coupon_message'], true)
                ? ['LocalBusiness', Str::studly($businessName), 'ShopLocal']
                : [],
            'source' => 'fallback',
        ];
    }

    protected function buildPromptSummary(LocalBusiness $business, array $payload): string
    {
        return trim(implode("\n", array_filter([
            'Business: '.$business->name,
            'Content type: '.($this->contentTypeLabels()[$payload['content_type']] ?? $payload['content_type']),
            'Goal: '.$payload['goal'],
            $payload['offer'] !== '' ? 'Offer: '.$payload['offer'] : null,
            $payload['target_customer'] !== '' ? 'Target customer: '.$payload['target_customer'] : null,
            $payload['extra_details'] !== '' ? 'Extra details: '.$payload['extra_details'] : null,
            $this->source_type !== '' ? 'Source: '.$this->source_type : null,
        ])));
    }

    protected function contentTypeLabels(): array
    {
        return [
            'review_request' => __('Review Request'),
            'coupon_message' => __('Coupon Message'),
            'booking_reminder' => __('Booking Reminder'),
            'feedback_request' => __('Feedback Request'),
            'lead_follow_up' => __('Lead Follow-up'),
            'social_post' => __('Social Post'),
            'whatsapp_sms' => __('WhatsApp / SMS Message'),
            'thank_you' => __('Thank You Message'),
            'business_description' => __('Business Description'),
            'landing_page_copy' => __('Landing Page Copy'),
        ];
    }

    protected function ctaForType(string $type): string
    {
        return match ($type) {
            'review_request' => __('Leave a review'),
            'coupon_message' => __('Claim your offer'),
            'booking_reminder' => __('Confirm your appointment'),
            'feedback_request' => __('Share feedback'),
            'lead_follow_up' => __('Book a consultation'),
            'social_post' => __('Message us today'),
            'whatsapp_sms' => __('Reply to this message'),
            'thank_you' => __('Visit us again'),
            'business_description' => __('Contact us'),
            'landing_page_copy' => __('Get started'),
            default => __('Learn more'),
        };
    }

    protected function toneOptions(): array
    {
        return [
            'professional' => __('Professional'),
            'friendly' => __('Friendly'),
            'sales' => __('Sales'),
            'educational' => __('Educational'),
            'bold' => __('Bold'),
            'casual' => __('Casual'),
            'luxury' => __('Luxury'),
            'warm' => __('Warm'),
        ];
    }

    protected function normalizeTone(string $tone): string
    {
        $tone = strtolower(Str::slug(trim($tone), '_'));

        return array_key_exists($tone, $this->toneOptions()) ? $tone : 'professional';
    }

    protected function normalizeLanguageCode(string $language): string
    {
        $language = strtolower(trim($language));

        if ($language === '') {
            return 'en';
        }

        if (collect(world_languages())->contains(fn ($item) => strtolower((string) data_get($item, 'code')) === $language)) {
            return $language;
        }

        $matched = collect(world_languages())->first(fn ($item) => strtolower((string) data_get($item, 'name')) === $language);

        return (string) data_get($matched, 'code', 'en');
    }

    protected function languageLabel(string $language): string
    {
        $code = $this->normalizeLanguageCode($language);

        return (string) (collect(world_languages())->firstWhere('code', $code)['name'] ?? strtoupper($code));
    }
}
