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

    public function store(
        CreateSupportMessageRequest $request,
        string $external_business_id,
        string $ticket_id
    ): JsonResponse {
        $integration = $this->bridge->findIntegrationOrFail($external_business_id);

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
