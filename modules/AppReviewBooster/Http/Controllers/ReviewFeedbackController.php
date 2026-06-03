<?php

namespace Modules\AppReviewBooster\Http\Controllers;

use App\Support\GrowthToolNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AppCustomers\Support\CustomerUpserter;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppReviewBooster\Models\ReviewFeedback;

class ReviewFeedbackController extends Controller
{
    public function store(Request $request, QrCampaign $campaign): RedirectResponse
    {
        abort_unless($campaign->type === 'review', 404);

        $payload = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:80'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:3000'],
        ]);

        $rating = (int) $payload['rating'];
        $threshold = (int) data_get($campaign->settings, 'positive_threshold', 4);
        $preferredDestination = (string) data_get($campaign->settings, 'preferred_destination', 'google');
        $googleUrl = (string) data_get($campaign->settings, 'google_review_url', '');
        $facebookUrl = (string) data_get($campaign->settings, 'facebook_review_url', '');

        app(CustomerUpserter::class)->fromCampaign($campaign, $payload, 'review_booster');
        ReviewFeedback::query()->create([
            'user_id' => $campaign->user_id,
            'campaign_id' => $campaign->id,
            ...$payload,
        ]);

        app(GrowthToolNotifier::class)->feedbackCreated(
            $campaign,
            (string) ($payload['customer_name'] ?? ''),
            $rating,
        );

        if ($rating >= $threshold) {
            $destinationUrl = $preferredDestination === 'facebook' ? ($facebookUrl ?: $googleUrl) : ($googleUrl ?: $facebookUrl);

            if (filled($destinationUrl)) {
                return redirect()->away($destinationUrl);
            }
        }

        return back()->with('review_feedback_saved', true);
    }
}
