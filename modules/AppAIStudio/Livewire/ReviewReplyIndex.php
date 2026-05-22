<?php

namespace Modules\AppAIStudio\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AppAIStudio\Models\AIPromptHistory;
use Modules\AppAIStudio\Models\AIStudioUserSetting;
use Modules\AppAIStudio\Models\AIStudioWorkspaceSetting;
use Modules\AppAIStudio\Support\AIStudioAccess;
use Modules\AppAIStudio\Support\AiContentStudioService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppReviewBooster\Models\ReviewFeedback;
use Modules\AppTeams\Support\TeamWorkspaceAccess;
use Throwable;

#[Title('AI Review Reply')]
class ReviewReplyIndex extends Component
{
    use AIStudioAccess;
    use WithPagination;

    public string $tab = 'builder';
    public string $business_id = '';
    public int $rating = 5;
    public string $reply_type = 'auto';
    public string $customer_name = '';
    public string $review_text = '';
    public string $tone = '';
    public string $language = '';
    public ?array $reply = null;
    public ?string $statusMessage = null;
    public ?string $aiSource = null;
    public ?int $review_feedback_id = null;
    public ?int $source_campaign_id = null;
    public int $savedRepliesPerPage = 10;

    public function mount(): void
    {
        $defaults = $this->resolveAiDefaults();
        $this->tone = $defaults['tone'];
        $this->language = $defaults['language'];

        $business = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->first();

        if ($business) {
            $this->business_id = (string) request('business_id', $business->id);
        }

        $feedbackId = (int) request('feedback_id', request('review_feedback_id', 0));

        if ($feedbackId > 0 && $this->fillFromReviewFeedback($feedbackId)) {
            return;
        }

        $this->rating = max(1, min(5, (int) request('rating', 5)));
        $this->reply_type = (string) request('reply_type', 'auto');
        $this->customer_name = trim((string) request('customer_name', ''));
        $this->review_text = trim((string) request('feedback', request('review_text', '')));
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['builder', 'saved'], true)) {
            $this->tab = $tab;
        }
    }

    public function updatedSavedRepliesPerPage(): void
    {
        $this->savedRepliesPerPage = in_array((int) $this->savedRepliesPerPage, [10, 25, 50], true)
            ? (int) $this->savedRepliesPerPage
            : 10;

        $this->resetPage();
    }

    public function generate(): void
    {
        $payload = $this->validatePayload();
        $business = $this->businessForPayload($payload);
        $fallbackReply = $this->buildFallbackReply($business, $payload);

        $this->reply = $fallbackReply;
        $this->aiSource = 'fallback';

        try {
            $planOwner = $this->aiStudioPlanOwner();

            if (function_exists('credit_service')) {
                credit_service()->ensureCanConsume($planOwner, 'ai_studio_review_reply');
            }

            $aiReply = app(AiContentStudioService::class)->generateReviewReply([
                'business_name' => $business->name,
                'business_type' => $business->type ?: __('Local business'),
                'rating' => $payload['rating'],
                'reply_type' => $this->replyTypeLabel($payload['reply_type'], (int) $payload['rating']),
                'customer_name' => $payload['customer_name'],
                'review_text' => $payload['review_text'],
                'tone' => $payload['tone'],
                'language' => $this->languageLabel($payload['language']),
            ]);

            $this->reply = array_replace($fallbackReply, $aiReply, ['source' => 'ai']);
            $this->aiSource = 'ai';
            $this->statusMessage = __('AI review reply generated.');

            if (function_exists('consume_credits')) {
                consume_credits($planOwner, 'ai_studio_review_reply', [
                    'feature' => 'localboost.ai-review-reply',
                    'metadata' => [
                        'rating' => $payload['rating'],
                        'reply_type' => $payload['reply_type'],
                        'language' => $payload['language'],
                        'tone' => $payload['tone'],
                    ],
                ]);
            }
        } catch (Throwable $exception) {
            $this->reply = array_replace($fallbackReply, [
                'source' => 'fallback',
                'fallback_reason' => $exception->getMessage(),
            ]);
            $this->statusMessage = __('AI unavailable. A fallback reply was generated.');
        }
    }

    public function makeShorter(): void
    {
        $payload = $this->validatePayload();
        $this->tone = 'professional';
        $this->generate();

        if ($this->reply) {
            $this->reply['suggested_reply'] = (string) data_get($this->reply, 'short_reply', data_get($this->reply, 'suggested_reply'));
            $this->statusMessage = __('Shorter reply generated.');
        }
    }

    public function makeWarmer(): void
    {
        $this->validatePayload();
        $this->tone = 'friendly';
        $this->generate();

        if ($this->reply) {
            $this->reply['suggested_reply'] = (string) data_get($this->reply, 'friendly_reply', data_get($this->reply, 'suggested_reply'));
            $this->statusMessage = __('Warmer reply generated.');
        }
    }

    public function saveReply(): void
    {
        if (! $this->reply) {
            $this->generate();
        }

        $payload = $this->validatePayload();
        $business = $this->businessForPayload($payload);
        $user = auth()->user();
        $team = TeamWorkspaceAccess::activeTeam($user);

        $history = AIPromptHistory::query()->create([
            'owner_user_id' => TeamWorkspaceAccess::workspaceOwnerUserId($user),
            'requested_by_user_id' => $user->id,
            'team_id' => $team?->id,
            'module' => 'ai_review_reply',
            'title' => __('Review reply for :business', ['business' => $business->name]),
            'language' => $payload['language'],
            'tone' => $payload['tone'],
            'prompt' => $payload['review_text'],
            'input_payload' => [
                'business_id' => $business->id,
                'review_feedback_id' => $this->review_feedback_id,
                'campaign_id' => $this->source_campaign_id,
                ...$payload,
            ],
            'output_payload' => $this->reply,
            'metadata' => [
                'rating' => $payload['rating'],
                'source' => $this->aiSource ?: 'fallback',
                'review_feedback_id' => $this->review_feedback_id,
                'campaign_id' => $this->source_campaign_id,
            ],
        ]);

        $this->markLinkedFeedbackAsReplied($history->id);

        $this->statusMessage = __('Reply saved to AI history.');
        $this->resetPage();
    }

    public function markLinkedFeedbackResolved(): void
    {
        if (! $this->review_feedback_id || ! Schema::hasColumn('lb_review_feedbacks', 'status')) {
            $this->statusMessage = __('Open this tool from a review feedback item to update its status.');

            return;
        }

        $feedback = ReviewFeedback::query()
            ->where('user_id', auth()->id())
            ->whereKey($this->review_feedback_id)
            ->first();

        if (! $feedback) {
            $this->statusMessage = __('The linked feedback could not be found.');

            return;
        }

        $updates = ['status' => 'resolved'];

        if (Schema::hasColumn('lb_review_feedbacks', 'resolved_at')) {
            $updates['resolved_at'] = now();
        }

        $feedback->forceFill($updates)->save();
        $this->statusMessage = __('Linked feedback marked as resolved.');
    }

    public function loadHistory(int $id): void
    {
        $history = $this->historyQuery()->findOrFail($id);

        $this->business_id = (string) data_get($history->input_payload, 'business_id', $this->business_id);
        $this->rating = (int) data_get($history->input_payload, 'rating', $this->rating);
        $this->reply_type = (string) data_get($history->input_payload, 'reply_type', $this->reply_type);
        $this->customer_name = (string) data_get($history->input_payload, 'customer_name', $this->customer_name);
        $this->review_text = (string) $history->prompt;
        $this->tone = (string) data_get($history->input_payload, 'tone', $this->tone);
        $this->language = $this->normalizeLanguageCode((string) data_get($history->input_payload, 'language', $this->language));
        $this->review_feedback_id = (int) data_get($history->input_payload, 'review_feedback_id', data_get($history->metadata, 'review_feedback_id')) ?: null;
        $this->source_campaign_id = (int) data_get($history->input_payload, 'campaign_id', data_get($history->metadata, 'campaign_id')) ?: null;
        $this->reply = $history->output_payload ?: null;
        $this->aiSource = (string) data_get($history->metadata, 'source', 'saved');
        $this->tab = 'builder';
        $this->statusMessage = __('Saved reply loaded.');
    }

    public function deleteHistory(int $id): void
    {
        $this->historyQuery()->whereKey($id)->delete();
        $this->resetPage();
        $this->statusMessage = __('Saved reply deleted.');
    }

    public function generateCustomerName(): void
    {
        $names = match ($this->normalizeLanguageCode($this->language)) {
            'vi' => ['Anh Minh', 'Chi Linh', 'Anh Nam', 'Chi Mai', 'Anh Khoa', 'Chi Trang'],
            'es' => ['Carlos', 'Sofia', 'Miguel', 'Lucia', 'Diego', 'Valeria'],
            'fr' => ['Camille', 'Lucas', 'Emma', 'Hugo', 'Lea', 'Thomas'],
            default => ['Sarah', 'Michael', 'Emily', 'David', 'Jessica', 'Daniel'],
        };

        $this->customer_name = $names[array_rand($names)];
        $this->statusMessage = __('Customer name generated.');
    }

    public function render(): View
    {
        $businesses = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get();

        return view('appaistudio::review-reply', [
            'businesses' => $businesses,
            'toneOptions' => $this->toneOptions(),
            'creditPreview' => $this->aiStudioCreditPreview('ai_studio_review_reply'),
            'savedReplies' => $this->historyQuery()->latest()->paginate($this->savedRepliesPerPage),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('AI Review Reply'),
        ]);
    }

    protected function validatePayload(): array
    {
        return $this->validate([
            'business_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'reply_type' => ['required', 'string', 'in:auto,public,private'],
            'customer_name' => ['nullable', 'string', 'max:80'],
            'review_text' => ['required', 'string', 'max:3000'],
            'tone' => ['required', 'string', 'max:40'],
            'language' => ['required', 'string', 'max:80'],
        ]);
    }

    protected function businessForPayload(array $payload): LocalBusiness
    {
        return LocalBusiness::query()
            ->where('user_id', auth()->id())
            ->findOrFail((int) $payload['business_id']);
    }

    protected function fillFromReviewFeedback(int $feedbackId): bool
    {
        $feedback = ReviewFeedback::query()
            ->with('campaign')
            ->where('user_id', auth()->id())
            ->find($feedbackId);

        if (! $feedback || ! $feedback->campaign) {
            return false;
        }

        $business = LocalBusiness::query()
            ->where('user_id', auth()->id())
            ->find($feedback->campaign->business_id);

        if (! $business) {
            return false;
        }

        $this->review_feedback_id = $feedback->id;
        $this->source_campaign_id = $feedback->campaign_id;
        $this->business_id = (string) $business->id;
        $this->rating = max(1, min(5, (int) $feedback->rating));
        $this->reply_type = $this->rating >= 4 ? 'public' : 'private';
        $this->customer_name = trim((string) $feedback->customer_name);
        $this->review_text = trim((string) ($feedback->message ?: ($this->rating >= 4
            ? __('Customer selected :rating stars and continued to the public review link.', ['rating' => $this->rating])
            : __('Customer selected :rating stars but did not leave a written message.', ['rating' => $this->rating])
        )));
        $this->statusMessage = __('Review feedback loaded for AI reply.');

        return true;
    }

    protected function markLinkedFeedbackAsReplied(int $historyId): void
    {
        if (! $this->review_feedback_id || ! Schema::hasColumn('lb_review_feedbacks', 'status')) {
            return;
        }

        $feedback = ReviewFeedback::query()
            ->where('user_id', auth()->id())
            ->whereKey($this->review_feedback_id)
            ->first();

        if (! $feedback) {
            return;
        }

        $updates = [
            'status' => 'replied',
        ];

        if (Schema::hasColumn('lb_review_feedbacks', 'ai_reply_history_id')) {
            $updates['ai_reply_history_id'] = $historyId;
        }

        if (Schema::hasColumn('lb_review_feedbacks', 'replied_at')) {
            $updates['replied_at'] = now();
        }

        $feedback->forceFill($updates)->save();
    }

    protected function buildFallbackReply(LocalBusiness $business, array $payload): array
    {
        $positive = (int) $payload['rating'] >= 4;
        $businessName = $business->name;
        $customerPrefix = trim((string) ($payload['customer_name'] ?? '')) !== ''
            ? __('Hi :name, ', ['name' => trim((string) $payload['customer_name'])])
            : '';

        if ($positive) {
            $suggested = $customerPrefix.__('Thank you so much for your kind words! We are happy to hear you enjoyed your experience with :business. We appreciate your support and look forward to welcoming you again soon.', ['business' => $businessName]);
            $short = $customerPrefix.__('Thank you for the wonderful feedback! We are glad you enjoyed your experience and hope to see you again soon.');
            $professional = $customerPrefix.__('Thank you for taking the time to share your experience. We are pleased to hear that our team met your expectations, and we appreciate your support of :business.', ['business' => $businessName]);
            $friendly = $customerPrefix.__('Thanks so much! We are really glad you had a great experience with us. We hope to welcome you back again soon.');
        } else {
            $suggested = $customerPrefix.__('We are sorry to hear about your experience. Thank you for sharing this feedback with us. We will review it with our team and would appreciate the opportunity to follow up privately so we can make things right.');
            $short = $customerPrefix.__('We are sorry your experience was not what it should have been. Thank you for telling us; we will review this and follow up privately.');
            $professional = $customerPrefix.__('Thank you for bringing this to our attention. We regret that your experience did not meet expectations and will review your feedback carefully with our team.');
            $friendly = $customerPrefix.__('We are really sorry this happened. Thanks for letting us know; we will look into it and would like to follow up with you directly.');
        }

        return [
            'suggested_reply' => $suggested,
            'short_reply' => $short,
            'professional_reply' => $professional,
            'friendly_reply' => $friendly,
            'source' => 'fallback',
        ];
    }

    protected function historyQuery()
    {
        return AIPromptHistory::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->where('module', 'ai_review_reply');
    }

    protected function resolveAiDefaults(): array
    {
        $user = auth()->user();
        $team = TeamWorkspaceAccess::activeTeam($user);
        $ownerUserId = TeamWorkspaceAccess::workspaceOwnerUserId($user);
        $options = app(OptionStore::class);

        $adminLanguage = (string) $options->get('ai_default_language', $user?->locale ?: app()->getLocale() ?: 'en');
        $adminTone = (string) $options->get('ai_default_tone_of_voice', 'professional');

        $workspaceSettings = (array) (AIStudioWorkspaceSetting::query()
            ->ownedBy($ownerUserId)
            ->forTeam($team?->id)
            ->value('settings') ?? []);

        $userSettings = (array) (AIStudioUserSetting::query()
            ->forUser((int) $user->id)
            ->value('settings') ?? []);

        $language = (string) ($userSettings['default_language']
            ?? $workspaceSettings['default_language']
            ?? $adminLanguage
            ?? 'en');
        $tone = (string) ($userSettings['default_tone']
            ?? $workspaceSettings['default_tone']
            ?? $adminTone
            ?? 'professional');

        return [
            'language' => $this->normalizeLanguageCode($language),
            'tone' => $this->normalizeTone($tone),
        ];
    }

    protected function toneOptions(): array
    {
        return [
            'professional' => __('Professional'),
            'friendly' => __('Friendly'),
            'apologetic' => __('Apologetic'),
            'grateful' => __('Grateful'),
            'warm' => __('Warm'),
        ];
    }

    protected function replyTypeLabel(string $replyType, int $rating): string
    {
        return match ($replyType) {
            'public' => 'Public review reply',
            'private' => 'Private feedback recovery',
            default => $rating >= 4 ? 'Public review reply' : 'Private feedback recovery',
        };
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

    protected function normalizeTone(string $tone): string
    {
        $tone = strtolower(Str::slug(trim($tone), '_'));

        return array_key_exists($tone, $this->toneOptions()) ? $tone : 'professional';
    }
}
