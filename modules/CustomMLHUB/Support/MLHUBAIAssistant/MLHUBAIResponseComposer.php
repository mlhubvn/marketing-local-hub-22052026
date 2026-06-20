<?php

namespace Modules\CustomMLHUB\Support\MLHUBAIAssistant;

class MLHUBAIResponseComposer
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function compose(string $intent, array $context): string
    {
        return match ($intent) {
            'new_customers' => $this->composeNewCustomers($context),
            'campaigns' => $this->composeCampaigns($context),
            'reviews' => $this->composeReviews($context),
            'next_steps' => $this->composeNextSteps($context),
            'overview' => $this->composeOverview($context),
            'visits' => $this->composeVisits($context),
            default => $this->composeGeneral($context),
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeNewCustomers(array $context): string
    {
        $customers = (array) ($context['customers'] ?? []);
        $signals = (array) ($context['weekly_signals'] ?? []);
        $count = (int) ($customers['new_this_week'] ?? 0);
        $delta = (int) ($customers['delta'] ?? 0);

        if ($count === 0 && (int) ($signals['leads'] ?? 0) === 0 && (int) ($signals['bookings'] ?? 0) === 0) {
            return __('This week there are no new customers yet. Publish a campaign and share the QR code so MLHUB can start capturing leads and bookings.');
        }

        $deltaText = match (true) {
            $delta > 0 => __('+:count vs last week', ['count' => format_number_locale($delta)]),
            $delta < 0 => __(':count vs last week', ['count' => format_number_locale($delta)]),
            default => __('same as last week'),
        };

        $parts = array_values(array_filter([
            (int) ($signals['positive_reviews'] ?? 0) > 0
                ? __(':count from Review Booster', ['count' => format_number_locale((int) $signals['positive_reviews'])])
                : null,
            (int) ($signals['bookings'] ?? 0) > 0
                ? __(':count from booking pages', ['count' => format_number_locale((int) $signals['bookings'])])
                : null,
            (int) ($signals['leads'] ?? 0) > 0
                ? __(':count from lead forms', ['count' => format_number_locale((int) $signals['leads'])])
                : null,
        ]));

        $breakdown = $parts !== []
            ? implode(', ', $parts).'.'
            : __('Keep sharing your QR codes to grow the customer list.');

        return __('This week: :count new customers (:delta). :breakdown', [
            'count' => format_number_locale($count),
            'delta' => $deltaText,
            'breakdown' => $breakdown,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeCampaigns(array $context): string
    {
        $active = (array) ($context['active_campaigns'] ?? []);

        if ($active === []) {
            return __('You have no published campaigns yet. Create a review, booking, coupon, or lead campaign and publish it to start tracking results here.');
        }

        $lines = collect($active)
            ->take(4)
            ->map(function (array $campaign): string {
                $label = $this->campaignTypeLabel((string) ($campaign['type'] ?? ''));

                return __(':name (:type): :visits scans, :conversions conversions', [
                    'name' => (string) ($campaign['name'] ?? __('Campaign')),
                    'type' => $label,
                    'visits' => format_number_locale((int) ($campaign['visits'] ?? 0)),
                    'conversions' => format_number_locale((int) ($campaign['conversions'] ?? 0)),
                ]);
            })
            ->implode(' ');

        return __(':count active campaigns. :details', [
            'count' => format_number_locale(count($active)),
            'details' => $lines,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeReviews(array $context): string
    {
        $reviews = (array) ($context['reviews'] ?? []);
        $count = (int) ($reviews['count'] ?? 0);

        if ($count === 0) {
            return __('No new reviews this week yet. Turn on Review Booster and place the QR where guests can scan after their visit.');
        }

        $average = (float) ($reviews['average_rating'] ?? 0);
        $needsReply = (int) ($reviews['needs_reply'] ?? 0);
        $sentiment = $average >= 4.5
            ? __('Positive sentiment')
            : ($average >= 3.5 ? __('Mostly positive sentiment') : __('Mixed sentiment — review the feedback closely'));

        $replyNote = $needsReply > 0
            ? __(':count reviews still need a reply.', ['count' => format_number_locale($needsReply)])
            : __('All recent positive reviews have been replied to.');

        return __('Average :rating★ from :count new reviews. :sentiment — :reply', [
            'rating' => number_format($average, 1),
            'count' => format_number_locale($count),
            'sentiment' => $sentiment,
            'reply' => $replyNote,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeNextSteps(array $context): string
    {
        $hints = (array) ($context['onboarding'] ?? []);
        $metrics = (array) ($context['metrics'] ?? []);

        if ($hints === [] && (int) ($metrics['visits'] ?? 0) > 0) {
            return __('Your funnel is running. Next: reply to pending reviews, send a weekend coupon to repeat guests, and check Reports for the best-performing campaign.');
        }

        $suggestions = [];

        if (in_array('create_business', $hints, true)) {
            $suggestions[] = __('Add your first business profile so campaigns and QR codes have a home base.');
        }

        if (in_array('create_campaign', $hints, true)) {
            $suggestions[] = __('Create your first growth campaign — Review Booster is usually the fastest win for local shops.');
        }

        if (in_array('publish_campaign', $hints, true)) {
            $suggestions[] = __('Publish a draft campaign so the public page and QR code go live.');
        }

        if (in_array('share_qr', $hints, true)) {
            $suggestions[] = __('Print or share the campaign QR at the counter, tables, or receipt so visits start flowing in.');
        }

        if (in_array('boost_reviews', $hints, true)) {
            $suggestions[] = __('Launch Review Booster to collect Google reviews automatically after each visit.');
        }

        if ($suggestions === []) {
            $suggestions[] = __('Try a weekend coupon for repeat guests — MLHUB can draft the copy and landing page in AI Studio.');
        }

        return implode(' ', $suggestions);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeOverview(array $context): string
    {
        $metrics = (array) ($context['metrics'] ?? []);

        return __('Quick snapshot: :businesses businesses, :campaigns active campaigns, :visits visits, :leads leads, :bookings bookings, :coupons coupon claims, conversion rate :rate.', [
            'businesses' => format_number_locale((int) ($metrics['businesses'] ?? 0)),
            'campaigns' => format_number_locale((int) ($metrics['active_campaigns'] ?? 0)),
            'visits' => format_number_locale((int) ($metrics['visits'] ?? 0)),
            'leads' => format_number_locale((int) ($metrics['leads'] ?? 0)),
            'bookings' => format_number_locale((int) ($metrics['bookings'] ?? 0)),
            'coupons' => format_number_locale((int) ($metrics['coupon_claims'] ?? 0)),
            'rate' => format_percent_locale((float) ($metrics['conversion_rate'] ?? 0)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeVisits(array $context): string
    {
        $metrics = (array) ($context['metrics'] ?? []);
        $weekly = (array) ($context['weekly_signals'] ?? []);

        return __('Total visits: :total. This week: :week_scans QR scans, :week_leads leads, :week_bookings bookings.', [
            'total' => format_number_locale((int) ($metrics['visits'] ?? 0)),
            'week_scans' => format_number_locale((int) ($weekly['qr_scans'] ?? 0)),
            'week_leads' => format_number_locale((int) ($weekly['leads'] ?? 0)),
            'week_bookings' => format_number_locale((int) ($weekly['bookings'] ?? 0)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeGeneral(array $context): string
    {
        $metrics = (array) ($context['metrics'] ?? []);
        $customers = (array) ($context['customers'] ?? []);
        $activeCount = count((array) ($context['active_campaigns'] ?? []));

        if ((int) ($metrics['businesses'] ?? 0) === 0) {
            return __('I can report on customers, campaigns, reviews, and visits once you add a business profile. Try a suggested question or ask about a specific metric.');
        }

        return __('Right now you have :campaigns running campaigns, :visits total visits, and :customers new customers this week. Ask about reviews, campaigns, or what to do next for a focused report.', [
            'campaigns' => format_number_locale($activeCount),
            'visits' => format_number_locale((int) ($metrics['visits'] ?? 0)),
            'customers' => format_number_locale((int) ($customers['new_this_week'] ?? 0)),
        ]);
    }

    protected function campaignTypeLabel(string $type): string
    {
        return match ($type) {
            'review' => __('Review Booster'),
            'booking' => __('Booking'),
            'coupon' => __('Coupon'),
            'feedback' => __('Feedback'),
            'lead' => __('Lead form'),
            default => str($type)->headline()->toString(),
        };
    }
}
