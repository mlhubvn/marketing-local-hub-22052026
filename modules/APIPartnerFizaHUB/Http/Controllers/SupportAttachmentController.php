<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\APIPartnerFizaHUB\Services\SupportTicketBridge;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class SupportAttachmentController
{
    public function __construct(
        protected SupportTicketBridge $bridge
    ) {}

    public function index(string $external_business_id, string $ticket_id): JsonResponse
    {
        $integration = $this->bridge->findIntegrationOrFail($external_business_id);

        return PartnerApiResponse::success($this->bridge->listAttachments($integration, $ticket_id));
    }

    public function store(Request $request, string $external_business_id, string $ticket_id): JsonResponse
    {
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

        $integration = $this->bridge->findIntegrationOrFail($external_business_id);

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

    public function show(string $external_business_id, string $ticket_id, string $attachment_id): Response
    {
        $integration = $this->bridge->findIntegrationOrFail($external_business_id);
        $attachment = $this->bridge->findScopedAttachmentOrFail($integration, $ticket_id, $attachment_id);

        abort_unless(
            filled($attachment->path) && Storage::disk($attachment->disk)->exists($attachment->path),
            404
        );

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }
}
