<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\APIPartnerFizaHUB\Http\Requests\CampaignApprovalRequest;
use Modules\APIPartnerFizaHUB\Http\Requests\DashboardRequest;
use Modules\APIPartnerFizaHUB\Http\Requests\ListCampaignsRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Services\DashboardService;
use Modules\APIPartnerFizaHUB\Services\CampaignApprovalService;
use Modules\APIPartnerFizaHUB\Services\SupportTicketBridge;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;
use Modules\AppQRCampaigns\Models\QrCampaign;

class DashboardController
{
    public function __construct(
        protected SupportTicketBridge $integrations,
        protected DashboardService $dashboard,
        protected CampaignApprovalService $approvals,
    ) {}

    public function show(DashboardRequest $request, string $external_business_id): JsonResponse
    {
        $integration = $this->requireIntegration($external_business_id);

        if (! $integration instanceof PartnerIntegration) {
            return $integration;
        }

        [$from, $to, $range] = $request->resolvedRange();
        $forceRefresh = $request->boolean('force_refresh');
        $data = $this->dashboard->summarizeCached($integration, $from, $to, $forceRefresh);
        $data['period']['range'] = $range;

        return PartnerApiResponse::success(
            $data
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

    public function growthInsights(DashboardRequest $request, string $external_business_id): JsonResponse
    {
        $integration = $this->requireIntegration($external_business_id);

        if (! $integration instanceof PartnerIntegration) {
            return $integration;
        }

        [$from, $to, $range] = $request->resolvedRange();
        $data = $this->dashboard->growthInsights($integration, $from, $to);
        $data['data_period']['range'] = $range;

        return PartnerApiResponse::success($data);
    }

    public function campaigns(ListCampaignsRequest $request, string $external_business_id): JsonResponse
    {
        $integration = $this->requireIntegration($external_business_id);

        if (! $integration instanceof PartnerIntegration) {
            return $integration;
        }

        [$from, $to, $range] = $request->resolvedRange();
        $filters = $request->filters() + ['from' => $from, 'to' => $to, 'range' => $range];

        return PartnerApiResponse::success(
            $this->dashboard->campaignList($integration, $filters)
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

    public function approveCampaign(
        CampaignApprovalRequest $request,
        string $external_business_id,
        string $campaign_id
    ): JsonResponse {
        $integration = $this->requireIntegration($external_business_id);

        if (! $integration instanceof PartnerIntegration) {
            return $integration;
        }

        $campaign = QrCampaign::query()
            ->where('user_id', $integration->mlhub_user_id)
            ->where('business_id', $integration->mlhub_business_id)
            ->whereKey($campaign_id)
            ->first();

        if (! $campaign) {
            throw (new ModelNotFoundException)->setModel(QrCampaign::class, [$campaign_id]);
        }

        return PartnerApiResponse::success(
            $this->approvals->decide(
                $integration,
                $campaign,
                (string) $request->validated('decision'),
                $request->validated('note')
            )
        );
    }

    private function requireIntegration(string $externalBusinessId): PartnerIntegration|JsonResponse
    {
        $integration = $this->integrations->findIntegrationOrFail($externalBusinessId);

        if (! $integration->mlhub_business_id) {
            return PartnerApiResponse::error(
                'integration_not_found',
                __('Doanh nghiệp này chưa được liên kết với MKT.'),
                404,
                ['next_action' => 'create_onboarding_request']
            );
        }

        return $integration;
    }
}
