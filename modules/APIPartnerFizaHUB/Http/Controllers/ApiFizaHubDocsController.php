<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ApiFizaHubDocsController
{
    public function show(): View
    {
        return view('apipartnerfizahub::api-fizahub', [
            'appUrl' => rtrim((string) config('app.url'), '/'),
            'baseUrl' => rtrim((string) config('app.url'), '/').'/api/v1/partners/fizahub',
            'postmanUrl' => route('partner.fizahub.docs.postman'),
            'helpTestUrl' => route('partner.fizahub.docs.help-test'),
            'healthUrl' => url('/api/v1/partners/fizahub/health'),
            // Testing-phase choice (see .env.example §8): always mirror the live webhook
            // secret/base URL here so FizaHUB can implement signature verification without
            // needing a separate out-of-band secret exchange.
            'webhookSecret' => (string) config('modules.apipartnerfizahub.webhook_secret', ''),
            'webhookBaseUrl' => (string) config('modules.apipartnerfizahub.webhook_base_url', ''),
            'reportingPortalUrl' => $this->reportingPortalUrl(),
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
            'reportingPortalUrl' => $this->reportingPortalUrl(),
        ]);
    }

    /**
     * Public URL of the separate FizaHUB Partner Reporting Portal (view-only dashboard for
     * FizaHUB leadership on its own domain), or null when the feature is disabled
     * (`FIZAHUB_DOMAIN` empty) — the docs pages hide the callout entirely in that case
     * instead of advertising a portal that has no routes registered.
     */
    private function reportingPortalUrl(): ?string
    {
        $domain = trim((string) config('modules.apipartnerfizahub.partner_reporting_domain', ''));

        return $domain === '' ? null : 'https://'.$domain;
    }

    public function postman(): Response
    {
        return $this->downloadCollection(
            'FizaHUB-Partner-API.postman_collection.json',
            'MKT-FizaHUB-Partner-API.postman_collection.json'
        );
    }

    /**
     * Backward-compatible alias: old Extended Beta download links now serve the unified collection.
     */
    public function postmanExtended(): Response
    {
        return $this->postman();
    }

    private function downloadCollection(string $sourceFilename, string $downloadFilename): Response
    {
        $path = base_path('modules/APIPartnerFizaHUB/docs/'.$sourceFilename);

        abort_unless(is_file($path), 404);

        $collection = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        // Testing-phase choice (see .env.example §8): inject the live base URL and partner token
        // so the downloaded collection runs immediately without any manual configuration.
        $runtimeVariables = [
            'base_url' => rtrim((string) config('app.url'), '/'),
            'partner_token' => (string) config('modules.apipartnerfizahub.token', ''),
        ];

        foreach ($collection['variable'] as &$variable) {
            $key = (string) ($variable['key'] ?? '');

            if (array_key_exists($key, $runtimeVariables)) {
                $variable['value'] = $runtimeVariables[$key];
            }
        }
        unset($variable);

        return response(
            json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
            200,
            [
                'Content-Type' => 'application/json; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$downloadFilename.'"',
                'Cache-Control' => 'no-store, private',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
}
