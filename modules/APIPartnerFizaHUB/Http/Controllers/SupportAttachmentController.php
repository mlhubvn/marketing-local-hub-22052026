<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportAttachment;
use Modules\APIPartnerFizaHUB\Services\SupportTicketBridge;
use Modules\APIPartnerFizaHUB\Support\PartnerApiException;
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

        // Deliberately a typed PartnerApiException (not abort_unless/404): a bare 404 here
        // would bubble past PartnerExceptionRenderer's ModelNotFoundException branch and get
        // misreported as the generic "route_not_found" ("API endpoint not found"), which is
        // wrong and confusing — the route exists, only the file content is missing.
        if (! filled($attachment->path) || ! Storage::disk($attachment->disk)->exists($attachment->path)) {
            throw PartnerApiException::make(
                'attachment_not_found',
                __('Không tìm thấy tệp đính kèm này.'),
                404,
                ['next_action' => 'list_attachments']
            );
        }

        $mime = (string) ($attachment->mime_type ?: 'application/octet-stream');
        $isImage = str_starts_with(strtolower($mime), 'image/');

        // Images: inline so authenticated download can also preview; other types stay as download.
        return Storage::disk($attachment->disk)->response(
            $attachment->path,
            $attachment->original_name,
            ['Content-Type' => $mime],
            $isImage ? 'inline' : 'attachment'
        );
    }

    /**
     * Public signed image preview for FizaHUB chat (<img src=image_url>).
     * No partner headers. Only image/*; invalid/expired signature → 403 from signed middleware.
     */
    public function preview(string $attachment_id, string $filename): Response
    {
        $attachment = PartnerSupportAttachment::query()
            ->where('id_secure', $attachment_id)
            ->first();

        $mime = strtolower((string) ($attachment?->mime_type ?? ''));

        if (
            ! $attachment
            || ! str_starts_with($mime, 'image/')
            || ! filled($attachment->path)
            || ! Storage::disk($attachment->disk)->exists($attachment->path)
        ) {
            abort(404);
        }

        return Storage::disk($attachment->disk)->response(
            $attachment->path,
            $filename,
            [
                'Content-Type' => (string) $attachment->mime_type,
                'Cache-Control' => 'private, max-age=3600',
            ],
            'inline'
        );
    }

    /**
     * Public signed download for download_url (pdf/video/docs/images).
     * No partner headers — click in browser works. Invalid/expired signature → 403.
     */
    public function signedDownload(string $attachment_id, string $filename): Response
    {
        $attachment = PartnerSupportAttachment::query()
            ->where('id_secure', $attachment_id)
            ->first();

        if (
            ! $attachment
            || ! filled($attachment->path)
            || ! Storage::disk($attachment->disk)->exists($attachment->path)
        ) {
            abort(404);
        }

        $mime = (string) ($attachment->mime_type ?: 'application/octet-stream');

        return Storage::disk($attachment->disk)->response(
            $attachment->path,
            filled($attachment->original_name) ? (string) $attachment->original_name : $filename,
            [
                'Content-Type' => $mime,
                'Cache-Control' => 'private, max-age=3600',
            ],
            'attachment'
        );
    }
}
