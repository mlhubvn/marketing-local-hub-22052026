<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\APIPartnerFizaHUB\Http\Requests\DashboardRequest;
use Modules\APIPartnerFizaHUB\Services\DashboardService;
use Modules\APIPartnerFizaHUB\Services\SupportTicketBridge;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;

class DashboardController
{
    public function __construct(
        protected SupportTicketBridge $integrations,
        protected DashboardService $dashboard
    ) {}

    public function show(DashboardRequest $request, string $external_business_id): JsonResponse
    {
        $integration = $this->integrations->findIntegrationOrFail($external_business_id);

        if (! $integration->mlhub_business_id) {
            return PartnerApiResponse::error(
                'integration_not_found',
                'No MLHUB integration is mapped to this FizaHUB business.',
                404
            );
        }

        [$from, $to] = $request->resolvedRange();

        return PartnerApiResponse::success(
            $this->dashboard->summarize($integration, $from, $to)
        );
    }
}
