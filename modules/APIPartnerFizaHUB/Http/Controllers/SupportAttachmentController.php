<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\APIPartnerFizaHUB\Services\SupportTicketBridge;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;
use RuntimeException;

class SupportAttachmentController
{
    public function __construct(
        protected SupportTicketBridge $bridge
    ) {}

    public function store(Request $request, string $ticket_id): JsonResponse
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

        if (! $request->hasFile('file')) {
            return PartnerApiResponse::error(
                'validation_failed',
                'The given data was invalid.',
                422,
                ['file' => ['A file upload is required.']]
            );
        }

        $file = $request->file('file');

        if (is_array($file) || ! $file->isValid()) {
            return PartnerApiResponse::error(
                'validation_failed',
                'The uploaded file is invalid.',
                422,
                ['file' => ['The uploaded file is invalid.']]
            );
        }

        $integration = $this->bridge->findIntegrationOrFail($externalBusinessId);

        try {
            $payload = $this->bridge->storeAttachment($integration, $ticket_id, $file);
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

        return PartnerApiResponse::success($payload, 201);
    }
}
