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
            'healthUrl' => url('/api/v1/partners/fizahub/health'),
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
