<?php

namespace Modules\CustomMLHUB\Support\MLHUBAIAssistant;

use Carbon\CarbonImmutable;

class MLHUBAIResponseComposer
{
    /**
     * Combine several intents into one connected report.
     *
     * @param  list<string>  $intents
     * @param  array<string, mixed>  $context
     */
    public function composeMany(array $intents, array $context, bool $firstTouch = false): string
    {
        $intents = array_values(array_unique(array_filter($intents)));

        $hasGreeting = in_array('greeting', $intents, true);
        $intents = array_values(array_filter($intents, static fn (string $intent): bool => $intent !== 'greeting'));

        if ($intents === []) {
            $body = $hasGreeting ? '' : $this->composeUnknown($context);
        } else {
            $parts = [];

            foreach ($intents as $intent) {
                if ($intent === 'unknown') {
                    continue;
                }

                $segment = trim($this->compose($intent, $context));

                if ($segment !== '' && ! in_array($segment, $parts, true)) {
                    $parts[] = $segment;
                }
            }

            $body = $parts === [] ? $this->composeUnknown($context) : implode("\n\n", $parts);
        }

        if ($hasGreeting && $body === '') {
            return $this->composeGreeting($context);
        }

        if ($hasGreeting) {
            return trim($this->greetingLine($context)."\n\n".$body);
        }

        if ($firstTouch) {
            return trim($this->greetingLine($context)."\n\n".$body);
        }

        return $body;
    }

    /**
     * Short one-line greeting used to warm up the first reply.
     *
     * @param  array<string, mixed>  $context
     */
    protected function greetingLine(array $context): string
    {
        return __(':greeting! I am your MLHUB AI assistant.', [
            'greeting' => $this->timeGreeting($this->now($context)),
        ]);
    }

    /**
     * Full welcome with time, date and what the assistant can do.
     *
     * @param  array<string, mixed>  $context
     */
    protected function composeGreeting(array $context): string
    {
        $now = $this->now($context);

        $hello = __(':greeting! I am your MLHUB AI assistant. It is :time, :date.', [
            'greeting' => $this->timeGreeting($now),
            'time' => $now->format('H:i'),
            'date' => format_date_locale($now),
        ]);

        $help = __('Today I can report your customers, campaigns, reviews and visits, and suggest the next best move.');

        $ask = __('Try asking about: :examples.', [
            'examples' => __('new customers, running campaigns, reviews, visits, business list, or what to do next'),
        ]);

        return $hello.' '.$help."\n\n".$ask;
    }

    protected function timeGreeting(CarbonImmutable $now): string
    {
        $hour = (int) $now->format('H');

        return match (true) {
            $hour >= 5 && $hour <= 10 => __('Good morning'),
            $hour >= 11 && $hour <= 12 => __('Good noon'),
            $hour >= 13 && $hour <= 17 => __('Good afternoon'),
            $hour >= 18 && $hour <= 21 => __('Good evening'),
            default => __('Hello'),
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function now(array $context): CarbonImmutable
    {
        $generatedAt = $context['generated_at'] ?? null;

        if (is_string($generatedAt) && $generatedAt !== '') {
            try {
                return CarbonImmutable::parse($generatedAt);
            } catch (\Throwable) {
                // fall through to now()
            }
        }

        return CarbonImmutable::now();
    }

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
            'businesses' => $this->composeBusinesses($context),
            'greeting' => $this->composeGreeting($context),
            default => $this->composeUnknown($context),
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
    protected function composeBusinesses(array $context): string
    {
        $list = (array) ($context['business_list'] ?? []);
        $count = (int) ($list['count'] ?? 0);
        $names = array_values(array_filter((array) ($list['names'] ?? [])));

        if ($count === 0) {
            return __('You have no business profiles yet. Add your first business so campaigns and QR codes have a home base.');
        }

        $shown = array_slice($names, 0, 5);
        $namesText = implode(', ', $shown);

        if ($count > count($shown)) {
            $namesText = __(':names and :count more', [
                'names' => $namesText,
                'count' => format_number_locale($count - count($shown)),
            ]);
        }

        return __('You have :count businesses: :names.', [
            'count' => format_number_locale($count),
            'names' => $namesText,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeUnknown(array $context): string
    {
        return __('I did not quite catch that. Try asking about: new customers, running campaigns, reviews, visits, your business list, or what to do next.');
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
