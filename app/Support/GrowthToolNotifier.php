<?php

namespace App\Support;

use App\Support\Portal\PortalGrowthDashboardMetrics;
use Modules\AdminNotifications\Services\NotificationService;
use Modules\AppQRCampaigns\Models\QrCampaign;

class GrowthToolNotifier
{
    public function leadCreated(QrCampaign $campaign, string $customerName): void
    {
        $this->notify(
            (int) $campaign->user_id,
            __('New lead captured'),
            __(':name submitted a lead through :campaign.', [
                'name' => $customerName !== '' ? $customerName : __('A customer'),
                'campaign' => $campaign->name ?: __('a lead form'),
            ]),
            route('portal.lead-forms'),
            'lead'
        );
    }

    public function bookingCreated(QrCampaign $campaign, string $customerName, string $date, string $time): void
    {
        $this->notify(
            (int) $campaign->user_id,
            __('New booking request'),
            __(':name requested a booking for :date at :time.', [
                'name' => $customerName !== '' ? $customerName : __('A customer'),
                'date' => $date,
                'time' => $time,
            ]),
            route('portal.booking-pages', ['tab' => 'bookings']),
            'booking'
        );
    }

    public function couponClaimed(QrCampaign $campaign, string $customerName, string $code): void
    {
        $this->notify(
            (int) $campaign->user_id,
            __('New coupon claim'),
            __(':name claimed coupon :code from :campaign.', [
                'name' => $customerName !== '' ? $customerName : __('A customer'),
                'code' => $code,
                'campaign' => $campaign->name ?: __('a coupon campaign'),
            ]),
            route('portal.coupon-campaigns', ['tab' => 'claims']),
            'coupon'
        );
    }

    public function feedbackCreated(QrCampaign $campaign, string $customerName, ?int $rating = null): void
    {
        $this->notify(
            (int) $campaign->user_id,
            __('New feedback received'),
            __(':name submitted feedback: :rating.', [
                'name' => $customerName !== '' ? $customerName : __('A customer'),
                'rating' => $rating ? $rating.' '.__('stars') : __('no rating'),
            ]),
            route('portal.feedback-forms', ['tab' => 'responses']),
            'feedback'
        );
    }

    public function googleReviewCreated(int $userId, string $locationName, string $reviewerName, int $rating): void
    {
        $this->notify(
            $userId,
            __('New Google review synced'),
            __(':reviewer left a :rating-star review for :location.', [
                'reviewer' => $reviewerName !== '' ? $reviewerName : __('A Google user'),
                'rating' => $rating,
                'location' => $locationName !== '' ? $locationName : __('a Google location'),
            ]),
            route('portal.google-business', ['tab' => 'reviews']),
            'review'
        );
    }

    protected function notify(int $userId, string $title, string $message, ?string $url, string $type): void
    {
        if ($userId <= 0) {
            return;
        }

        PortalGrowthDashboardMetrics::forget($userId);

        if (! class_exists(NotificationService::class)) {
            return;
        }

        app(NotificationService::class)->sendAuto($userId, $message, $url, $title, $type);
    }
}
