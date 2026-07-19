<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;
use Modules\APIPartnerFizaHUB\Support\PartnerReadinessChecker;

class HealthController
{
    public function __construct(
        private readonly PartnerReadinessChecker $readiness
    ) {}

    /**
     * `GET /health` doubles as a readiness probe: database, FizaHUB schema, default plan,
     * and AdminSupport tables. Any critical dependency missing → 503 degraded instead of a
     * false-positive 200 ok, so FizaHUB can detect a not-yet-deployed environment before
     * hitting onboarding.
     */
    public function __invoke(): JsonResponse
    {
        $result = $this->readiness->check();

        $data = [
            'status' => $result['status'],
            'partner' => 'fizahub',
            'api_version' => 'v1',
            'server_time' => now()->toIso8601String(),
            'checks' => $result['checks'],
        ];

        if ($result['status'] !== 'ok') {
            return PartnerApiResponse::success($data, 503);
        }

        return PartnerApiResponse::success($data);
    }
}
