<?php

namespace Modules\AppLeadForms\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Support\GrowthToolNotifier;
use Modules\AppCustomers\Support\CustomerUpserter;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppQRCampaigns\Models\QrCampaign;

class LeadSubmissionController extends Controller
{
    public function store(Request $request, QrCampaign $campaign): RedirectResponse
    {
        abort_unless($campaign->type === 'lead', 404);
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        app(CustomerUpserter::class)->fromCampaign($campaign, $payload, 'lead_form');
        LeadSubmission::query()->create(['user_id' => $campaign->user_id, 'campaign_id' => $campaign->id, ...$payload]);
        app(GrowthToolNotifier::class)->leadCreated($campaign, (string) $payload['name']);

        return back()->with('lead_saved', true);
    }
}
