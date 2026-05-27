<?php

namespace Modules\AppGoogleBusiness\Support;

use Modules\AppGoogleBusiness\Models\GoogleAutoReplyLog;
use Modules\AppGoogleBusiness\Models\GoogleAutoReplyRule;
use Modules\AppGoogleBusiness\Models\GoogleReview;
use Throwable;

class GoogleAutoReplyService
{
    public function processReview(GoogleReview $review): void
    {
        $review->loadMissing('googleLocation.business', 'business');
        $location = $review->googleLocation;

        if (! $location || ! $location->auto_reply_enabled || filled($review->reply) || filled($review->local_reply)) {
            return;
        }

        $rule = GoogleAutoReplyRule::query()
            ->where('team_id', $review->team_id)
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('google_business_location_id')->orWhere('google_business_location_id', $location->id))
            ->where(fn ($query) => $query->whereNull('business_id')->orWhere('business_id', $review->business_id))
            ->get()
            ->first(fn (GoogleAutoReplyRule $rule): bool => $this->matches($rule, $review));

        if (! $rule) {
            GoogleAutoReplyLog::query()->create([
                'team_id' => $review->team_id,
                'review_id' => $review->id,
                'action' => 'rule_checked',
                'publish_status' => 'skipped',
                'error_message' => __('No matching auto reply rule was found.'),
            ]);

            return;
        }

        try {
            if ($rule->reply_mode === 'manual') {
                GoogleAutoReplyLog::query()->create([
                    'team_id' => $review->team_id,
                    'rule_id' => $rule->id,
                    'review_id' => $review->id,
                    'action' => 'manual',
                    'publish_status' => 'skipped',
                ]);

                return;
            }

            $reply = $rule->reply_mode === 'template'
                ? trim((string) $rule->template_reply)
                : $this->generateReply($review, $rule);

            if (blank($reply)) {
                GoogleAutoReplyLog::query()->create([
                    'team_id' => $review->team_id,
                    'rule_id' => $rule->id,
                    'review_id' => $review->id,
                    'action' => $rule->reply_mode,
                    'publish_status' => 'skipped',
                    'error_message' => __('No reply text was generated.'),
                ]);

                return;
            }

            $status = 'draft';

            if (in_array($rule->reply_mode, ['auto_publish', 'template'], true)) {
                app(GoogleBusinessClient::class)->replyToReview($review, $reply);
                $status = 'published';
            } else {
                $review->forceFill([
                    'local_reply' => $reply,
                    'reply_status' => 'draft',
                    'auto_reply_status' => 'generated',
                ])->save();
            }

            GoogleAutoReplyLog::query()->create([
                'team_id' => $review->team_id,
                'rule_id' => $rule->id,
                'review_id' => $review->id,
                'action' => $rule->reply_mode,
                'generated_reply' => $reply,
                'publish_status' => $status,
            ]);
        } catch (Throwable $exception) {
            $review->forceFill(['auto_reply_status' => 'failed'])->save();
            GoogleAutoReplyLog::query()->create([
                'team_id' => $review->team_id,
                'rule_id' => $rule->id,
                'review_id' => $review->id,
                'action' => $rule->reply_mode,
                'publish_status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
        }
    }

    protected function matches(GoogleAutoReplyRule $rule, GoogleReview $review): bool
    {
        $rating = (int) $review->rating;
        $hasText = filled($review->comment);

        $ratingPass = match ((string) $rule->rating_condition) {
            'five' => $rating === 5,
            'positive' => $rating >= 4,
            'low' => $rating <= 3,
            'one_two' => $rating <= 2,
            default => true,
        };

        if (str_starts_with((string) $rule->rating_condition, 'custom:')) {
            $ratingPass = $this->matchesCustomRating((string) $rule->rating_condition, $rating);
        }

        $textPass = match ((string) $rule->text_condition) {
            'with_text' => $hasText,
            'without_text' => ! $hasText,
            'contains' => filled($rule->keyword) && str_contains(strtolower((string) $review->comment), strtolower((string) $rule->keyword)),
            'not_contains' => filled($rule->keyword) && ! str_contains(strtolower((string) $review->comment), strtolower((string) $rule->keyword)),
            default => true,
        };

        return $ratingPass && $textPass;
    }

    protected function matchesCustomRating(string $condition, int $rating): bool
    {
        [, $operator, $value] = array_pad(explode(':', $condition, 3), 3, null);
        $target = max(1, min(5, (int) $value));

        return match ($operator) {
            '>=' => $rating >= $target,
            '<=' => $rating <= $target,
            '=' => $rating === $target,
            default => true,
        };
    }

    protected function generateReply(GoogleReview $review, GoogleAutoReplyRule $rule): string
    {
        $business = $review->business ?: $review->googleLocation?->business;
        $businessName = $business?->name ?: __('our business');

        if (class_exists('Modules\\AppAIStudio\\Support\\AiContentStudioService') && $business) {
            $reply = app('Modules\\AppAIStudio\\Support\\AiContentStudioService')->generateReviewReply([
                'business_name' => $businessName,
                'business_type' => $business->type ?: __('Local business'),
                'rating' => max(1, (int) $review->rating),
                'reply_type' => ((int) $review->rating) >= 4 ? 'Public review reply' : 'Private feedback recovery',
                'customer_name' => (string) $review->reviewer_name,
                'review_text' => (string) ($review->comment ?: __('Customer left a :rating star Google review.', ['rating' => $review->rating])),
                'tone' => (string) $rule->tone,
                'language' => $rule->language === 'same' ? 'English' : $this->languageLabel((string) $rule->language),
            ]);

            return (string) data_get($reply, 'suggested_reply', '');
        }

        $prefix = filled($review->reviewer_name) ? __('Hi :name, ', ['name' => $review->reviewer_name]) : '';

        return ((int) $review->rating >= 4)
            ? $prefix.__('thank you for sharing your experience with :business. We appreciate your feedback and look forward to welcoming you again.', ['business' => $businessName])
            : $prefix.__('thank you for sharing this feedback. We are sorry your experience did not meet expectations, and our team will review this carefully.');
    }

    protected function languageLabel(string $language): string
    {
        $matched = collect(world_languages())->firstWhere('code', $language);

        return (string) ($matched['name'] ?? $language);
    }
}
