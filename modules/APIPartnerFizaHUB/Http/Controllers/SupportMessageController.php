<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\APIPartnerFizaHUB\Http\Requests\CreateSupportMessageRequest;
use Modules\APIPartnerFizaHUB\Services\SupportTicketBridge;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;
use RuntimeException;

class SupportMessageController
{
    public function __construct(
        protected SupportTicketBridge $bridge
    ) {}

    public function store(CreateSupportMessageRequest $request, string $ticket_id): JsonResponse
    {
        $externalBusinessId = trim((string) $request->query('external_business_id', $request->input('external_business_id', '')));

        if ($externalBusinessId === '') {
            return PartnerApiResponse::error(
                'validation_failed',
                'The given data was invalid.',
                422,
                ['external_business_id' => ['The external_business_id parameter is required for ticket scope.']]
            );
        }

        $integration = $this->bridge->findIntegrationOrFail($externalBusinessId);

        try {
            $message = $this->bridge->addMessage(
                $integration,
                $ticket_id,
                (string) $request->validated('message')
            );
        } catch (RuntimeException $exception) {
            if ($exception->getMessage() === 'ticket_not_open') {
                return PartnerApiResponse::error(
                    'ticket_not_open',
                    'This support ticket is closed or resolved.',
                    409
                );
            }

            throw $exception;
        }

        return PartnerApiResponse::success($message, 201);
    }
}
