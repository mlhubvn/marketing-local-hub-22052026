<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\APIPartnerFizaHUB\Http\Requests\DashboardRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
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
        $integration = $this->requireIntegration($external_business_id);

        if (! $integration instanceof PartnerIntegration) {
            return $integration;
        }

        [$from, $to] = $request->resolvedRange();
        $forceRefresh = $request->boolean('force_refresh');

        return PartnerApiResponse::success(
            $this->dashboard->summarizeCached($integration, $from, $to, $forceRefresh)
        );
    }

    public function insights(DashboardRequest $request, string $external_business_id): JsonResponse
    {
        $integration = $this->requireIntegration($external_business_id);

        if (! $integration instanceof PartnerIntegration) {
            return $integration;
        }

        [$from, $to] = $request->resolvedRange();

        return PartnerApiResponse::success(
            $this->dashboard->insightsPayload($integration, $from, $to)
        );
    }

    public function recommendations(DashboardRequest $request, string $external_business_id): JsonResponse
    {
        $integration = $this->requireIntegration($external_business_id);

        if (! $integration instanceof PartnerIntegration) {
            return $integration;
        }

        [$from, $to] = $request->resolvedRange();

        return PartnerApiResponse::success(
            $this->dashboard->recommendationsPayload($integration, $from, $to)
        );
    }

    public function campaigns(DashboardRequest $request, string $external_business_id): JsonResponse
    {
        $integration = $this->requireIntegration($external_business_id);

        if (! $integration instanceof PartnerIntegration) {
            return $integration;
        }

        [$from, $to] = $request->resolvedRange();

        return PartnerApiResponse::success(
            $this->dashboard->campaignList($integration, $from, $to)
        );
    }

    public function campaignShow(DashboardRequest $request, string $external_business_id, string $campaign_id): JsonResponse
    {
        $integration = $this->requireIntegration($external_business_id);

        if (! $integration instanceof PartnerIntegration) {
            return $integration;
        }

        [$from, $to] = $request->resolvedRange();

        return PartnerApiResponse::success(
            $this->dashboard->campaignDetail($integration, $campaign_id, $from, $to)
        );
    }

    private function requireIntegration(string $externalBusinessId): PartnerIntegration|JsonResponse
    {
        $integration = $this->integrations->findIntegrationOrFail($externalBusinessId);

        if (! $integration->mlhub_business_id) {
            return PartnerApiResponse::error(
                'integration_not_found',
                __('Doanh nghiệp này chưa được liên kết với MLHUB.'),
                404,
                ['next_action' => 'create_onboarding_request']
            );
        }

        return $integration;
    }
}
