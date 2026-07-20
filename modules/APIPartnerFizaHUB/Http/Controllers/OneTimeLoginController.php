<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Services\OneTimeLoginService;
use Modules\APIPartnerFizaHUB\Services\PartnerMappingService;
use Modules\APIPartnerFizaHUB\Services\SupportTicketBridge;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Modules\APIPartnerFizaHUB\Support\PartnerApiException;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;

class OneTimeLoginController
{
    public function __construct(
        protected SupportTicketBridge $integrations,
        protected OneTimeLoginService $logins,
        protected PartnerMappingService $mapping,
    ) {}

    public function store(Request $request, string $external_business_id): JsonResponse
    {
        $integration = $this->integrations->findIntegrationOrFail($external_business_id);

        if (! $integration->mlhub_business_id) {
            return PartnerApiResponse::error(
                'integration_not_found',
                __('Doanh nghiệp này chưa được liên kết với MLHUB.'),
                404,
                ['next_action' => 'create_onboarding_request']
            );
        }

        $this->assertOnboardingReady($integration->external_business_id);

        $requestId = (string) $request->attributes->get(
            'partner_request_id',
            $request->headers->get('X-Request-Id')
        );

        $payload = $this->logins->issue(
            $integration,
            trim((string) $request->header('Idempotency-Key')),
            $requestId
        );

        return PartnerApiResponse::success($payload, 201);
    }

    /**
     * One-time login is only allowed once the latest onboarding request is ready or completed.
     * A canonical onboarding request is mandatory for CRM access.
     */
    private function assertOnboardingReady(string $externalBusinessId): void
    {
        $latest = PartnerOnboardingRequest::query()
            ->where('partner_code', $this->mapping->partnerCode())
            ->where('external_business_id', $externalBusinessId)
            ->orderByDesc('id')
            ->first();

        if (! $latest) {
            throw PartnerApiException::make(
                'onboarding_not_ready',
                'Tài khoản đang chờ tư vấn viên MLHUB hoàn tất cấu hình.',
                409,
                ['status' => null]
            );
        }

        if (! OnboardingStatusMachine::allowsOneTimeLogin((string) $latest->status)) {
            throw PartnerApiException::make(
                'onboarding_not_ready',
                'Tài khoản đang chờ tư vấn viên MLHUB hoàn tất cấu hình.',
                409,
                [
                    'status' => $latest->status,
                    'status_label' => $latest->statusLabel(),
                ]
            );
        }
    }
}
