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
            'postmanExtendedUrl' => route('partner.fizahub.docs.postman.extended'),
            'helpTestUrl' => route('partner.fizahub.docs.help-test'),
            'healthUrl' => url('/api/v1/partners/fizahub/health'),
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
            'postmanExtendedUrl' => route('partner.fizahub.docs.postman.extended'),
            'appUrl' => rtrim((string) config('app.url'), '/'),
            'dashboardFrom' => $dashboardFrom,
            'dashboardTo' => $dashboardTo,
            'demoPartnerToken' => 'fizahub',
        ]);
    }

    public function postman(): BinaryFileResponse|Response
    {
        return $this->downloadCollection(
            'FizaHUB-Partner-API-MVP-v1.postman_collection.json',
            'MLHUB-FizaHUB-Partner-API.postman_collection.json'
        );
    }

    public function postmanExtended(): BinaryFileResponse|Response
    {
        return $this->downloadCollection(
            'FizaHUB-Partner-API-Extended-Beta.postman_collection.json',
            'MLHUB-FizaHUB-Partner-API-Extended-Beta.postman_collection.json'
        );
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
