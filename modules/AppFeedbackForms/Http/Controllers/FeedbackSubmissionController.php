<?php

namespace Modules\AppFeedbackForms\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Support\GrowthToolNotifier;
use Modules\AppCustomers\Support\CustomerUpserter;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppQRCampaigns\Models\QrCampaign;

class FeedbackSubmissionController extends Controller
{
    public function store(Request $request, QrCampaign $campaign): RedirectResponse
    {
        abort_unless($campaign->type === 'feedback', 404);

        $ratingRule = data_get($campaign->settings, 'rating_required') ? 'required' : 'nullable';
        $contactRule = data_get($campaign->settings, 'contact_required') ? 'required_without:customer_email' : 'nullable';

        $payload = $request->validate([
            'rating' => [$ratingRule, 'integer', 'min:1', 'max:5'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => [$contactRule, 'string', 'max:80'],
            'customer_email' => [data_get($campaign->settings, 'contact_required') ? 'required_without:customer_phone' : 'nullable', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        app(CustomerUpserter::class)->fromCampaign($campaign, $payload, 'feedback_form');
        FeedbackResponse::query()->create([
            'user_id' => $campaign->user_id,
            'campaign_id' => $campaign->id,
            ...$payload,
        ]);
        app(GrowthToolNotifier::class)->feedbackCreated(
            $campaign,
            (string) ($payload['customer_name'] ?? ''),
            isset($payload['rating']) ? (int) $payload['rating'] : null
        );

        return back()->with('feedback_saved', true);
    }
}
