<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\APIPartnerFizaHUB\Services\OneTimeLoginService;
use Modules\APIPartnerFizaHUB\Services\SupportTicketBridge;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;

class OneTimeLoginController
{
    public function __construct(
        protected SupportTicketBridge $integrations,
        protected OneTimeLoginService $logins
    ) {}

    public function store(Request $request, string $external_business_id): JsonResponse
    {
        $integration = $this->integrations->findIntegrationOrFail($external_business_id);

        if (! $integration->mlhub_business_id) {
            return PartnerApiResponse::error(
                'integration_not_found',
                'No MLHUB integration is mapped to this FizaHUB business.',
                404
            );
        }

        $requestId = (string) $request->attributes->get(
            'partner_request_id',
            $request->headers->get('X-Request-Id')
        );

        $payload = $this->logins->issue($integration, $requestId);

        return PartnerApiResponse::success($payload, 201);
    }
}
