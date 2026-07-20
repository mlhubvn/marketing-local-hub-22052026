<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\APIPartnerFizaHUB\Http\Requests\CreateSupportTicketRequest;
use Modules\APIPartnerFizaHUB\Services\SupportTicketBridge;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;
use Throwable;

class SupportTicketController
{
    public function __construct(
        protected SupportTicketBridge $bridge
    ) {}

    public function store(CreateSupportTicketRequest $request, string $external_business_id): JsonResponse
    {
        $integration = $this->bridge->findIntegrationOrFail($external_business_id);

        $ticket = $this->bridge->createForBusiness($integration, $request->validated());

        return PartnerApiResponse::success(
            $this->bridge->serializeTicket($ticket),
            201
        );
    }

    public function summary(string $external_business_id): JsonResponse
    {
        $integration = $this->bridge->findIntegrationOrFail($external_business_id);

        return PartnerApiResponse::success(
            $this->bridge->supportSummary($integration)
        );
    }

    public function close(Request $request, string $external_business_id, string $ticket_id): JsonResponse
    {
        $integration = $this->bridge->findIntegrationOrFail($external_business_id);

        $reason = $request->input('reason');

        return PartnerApiResponse::success(
            $this->bridge->close($integration, $ticket_id, is_string($reason) ? $reason : null)
        );
    }

    public function reopen(Request $request, string $external_business_id, string $ticket_id): JsonResponse
    {
        $integration = $this->bridge->findIntegrationOrFail($external_business_id);

        return PartnerApiResponse::success(
            $this->bridge->reopen($integration, $ticket_id)
        );
    }

    public function index(Request $request, string $external_business_id): JsonResponse
    {
        $integration = $this->bridge->findIntegrationOrFail($external_business_id);
        $perPage = (int) $request->query('per_page', 20);
        $page = (int) $request->query('page', 1);

        $paginator = $this->bridge->list($integration, $page, $perPage);

        return PartnerApiResponse::success([
            'items' => collect($paginator->items())
                ->map(fn ($ticket) => $this->bridge->serializeTicket($ticket))
                ->values()
                ->all(),
            'pagination' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, string $external_business_id, string $ticket_id): JsonResponse
    {
        $integration = $this->bridge->findIntegrationOrFail($external_business_id);
        $since = $this->parseSince($request->query('since'));
        $detail = $this->bridge->detail($integration, $ticket_id, $since);

        return PartnerApiResponse::success($detail);
    }

    private function parseSince(mixed $since): ?CarbonImmutable
    {
        if (! is_string($since) || trim($since) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($since)->utc();
        } catch (Throwable) {
            return null;
        }
    }
}
