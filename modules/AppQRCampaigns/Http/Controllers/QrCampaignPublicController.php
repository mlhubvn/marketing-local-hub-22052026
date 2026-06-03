<?php

namespace Modules\AppQRCampaigns\Http\Controllers;

use App\Support\Portal\PortalGrowthDashboardMetrics;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\AppBusinessProfiles\Support\BusinessQrRenderer;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppQRCampaigns\Models\QrCampaign;

class QrCampaignPublicController extends Controller
{
    public function show(Request $request, QrCampaign $campaign): View|RedirectResponse
    {
        $this->recordScan($request, $campaign);

        if (($campaign->type === 'url' || data_get($campaign->settings, 'source') === 'manual') && filled($campaign->destination_url)) {
            return redirect()->away((string) $campaign->destination_url);
        }

        $landingPage = LandingPage::query()
            ->where('campaign_id', $campaign->id)
            ->where('status', 'published')
            ->first();

        if ($landingPage) {
            if (! $request->session()->has('landing_page_visited_'.$landingPage->id)) {
                $landingPage->increment('visits_count');
                $request->session()->put('landing_page_visited_'.$landingPage->id, true);
            }

            return view('applandingpages::public.show', [
                'landingPage' => $landingPage->load('business', 'campaign'),
            ]);
        }

        $specializedView = match ($campaign->type) {
            'review' => 'appreviewbooster::public.show',
            'booking' => 'appbookingpages::public.show',
            'coupon' => 'appcouponcampaigns::public.show',
            'feedback' => 'appfeedbackforms::public.show',
            'lead' => 'appleadforms::public.show',
            default => null,
        };

        if ($specializedView && view()->exists($specializedView)) {
            return view($specializedView, [
                'campaign' => $campaign->load('business'),
            ]);
        }

        return view('appqrcampaigns::public.show', [
            'campaign' => $campaign->load('business'),
        ]);
    }

    public function qr(QrCampaign $campaign, BusinessQrRenderer $renderer)
    {
        $campaign->loadMissing('business');

        return response($renderer->render($campaign->business, null, $campaign->publicUrl()), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="'.$campaign->slug.'.svg"',
        ]);
    }

    public function png(QrCampaign $campaign, BusinessQrRenderer $renderer)
    {
        $campaign->loadMissing('business');

        return response($renderer->renderPng($campaign->business, null, $campaign->publicUrl()), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.$campaign->slug.'.png"',
        ]);
    }

    protected function recordScan(Request $request, QrCampaign $campaign): void
    {
        $agent = (string) $request->userAgent();

        $campaign->scans()->create([
            'user_id' => $campaign->user_id,
            'ip_address' => $request->ip(),
            'user_agent' => $agent,
            'device' => str_contains(strtolower($agent), 'mobile') ? 'mobile' : 'desktop',
            'created_at' => now(),
        ]);

        PortalGrowthDashboardMetrics::forget((int) $campaign->user_id);
    }
}
