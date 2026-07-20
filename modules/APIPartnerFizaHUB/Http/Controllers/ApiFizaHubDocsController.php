<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApiFizaHubDocsController
{
    public function show(): View
    {
        return view('apipartnerfizahub::api-fizahub', [
            'baseUrl' => rtrim((string) config('app.url'), '/').'/api/v1/partners/fizahub',
            'postmanUrl' => route('partner.fizahub.docs.postman'),
            'helpTestUrl' => route('partner.fizahub.docs.help-test'),
            'healthUrl' => url('/api/v1/partners/fizahub/health'),
            // Testing-phase choice (see .env.example §8): always mirror the live webhook
            // secret/base URL here so FizaHUB can implement signature verification without
            // needing a separate out-of-band secret exchange.
            'webhookSecret' => (string) config('modules.apipartnerfizahub.webhook_secret', ''),
            'webhookBaseUrl' => (string) config('modules.apipartnerfizahub.webhook_base_url', ''),
        ]);
    }

    public function helpTest(): View
    {
        $timezone = (string) config('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
        $dashboardTo = now($timezone)->toDateString();
        $dashboardFrom = now($timezone)->subDays(29)->toDateString();

        return view('apipartnerfizahub::api-fizahub-help-test', [
            'docsUrl' => route('partner.fizahub.docs'),
            'postmanUrl' => route('partner.fizahub.docs.postman'),
            'appUrl' => rtrim((string) config('app.url'), '/'),
            'dashboardFrom' => $dashboardFrom,
            'dashboardTo' => $dashboardTo,
            // Always mirrors the live FIZAHUB_PARTNER_TOKEN so this page can never drift from
            // the real token FizaHUB must use — testing-phase choice to show it in the clear
            // (see .env.example §8), not a leftover hardcoded demo value.
            'demoPartnerToken' => (string) config('modules.apipartnerfizahub.token', 'fizahub'),
        ]);
    }

    public function postman(): BinaryFileResponse|Response
    {
        return $this->downloadCollection(
            'FizaHUB-Partner-API.postman_collection.json',
            'MLHUB-FizaHUB-Partner-API.postman_collection.json'
        );
    }

    /**
     * Backward-compatible alias: old Extended Beta download links now serve the unified collection.
     */
    public function postmanExtended(): BinaryFileResponse|Response
    {
        return $this->postman();
    }

    private function downloadCollection(string $sourceFilename, string $downloadFilename): BinaryFileResponse|Response
    {
        $path = base_path('modules/APIPartnerFizaHUB/docs/'.$sourceFilename);

        abort_unless(is_file($path), 404);

        return response()->download(
            $path,
            $downloadFilename,
            [
                'Content-Type' => 'application/json',
            ]
        );
    }
}
