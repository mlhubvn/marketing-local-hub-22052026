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
            'demoPartnerToken' => 'fizahub',
        ]);
    }

    public function postman(): BinaryFileResponse|Response
    {
        $path = base_path('modules/APIPartnerFizaHUB/docs/FizaHUB-Partner-API.postman_collection.json');

        abort_unless(is_file($path), 404);

        return response()->download(
            $path,
            'MLHUB-FizaHUB-Partner-API.postman_collection.json',
            [
                'Content-Type' => 'application/json',
            ]
        );
    }
}
