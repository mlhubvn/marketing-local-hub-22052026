<?php

namespace Modules\APIPartnerFizaHUB\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AdminSupport\Models\SupportTicket;

class PartnerSupportAttachment extends Model
{
    protected $table = 'partner_support_attachments';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'support_ticket_id' => 'integer',
            'size_bytes' => 'integer',
            'uploaded_by_user_id' => 'integer',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function humanSize(): string
    {
        $bytes = (int) $this->size_bytes;

        if ($bytes >= 1073741824) {
            return format_number_locale($bytes / 1073741824, 2).' GB';
        }

        if ($bytes >= 1048576) {
            return format_number_locale($bytes / 1048576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return format_number_locale($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }

    /**
     * `business` khi FizaHUB (chủ ticket) tải lên, `admin` khi nhân viên MLHUB đính kèm khi trả lời.
     * Nhận `$ticketOwnerUid` từ nơi gọi để tránh N+1 khi hiển thị danh sách đính kèm.
     */
    public function senderType(?int $ticketOwnerUid): string
    {
        return ((int) $this->uploaded_by_user_id === (int) $ticketOwnerUid) ? 'business' : 'admin';
    }

    public function mimeIcon(): string
    {
        $mime = (string) $this->mime_type;

        return match (true) {
            str_starts_with($mime, 'image/') => 'fa-image',
            str_starts_with($mime, 'video/') => 'fa-film',
            in_array($mime, ['application/zip', 'application/x-zip-compressed'], true) => 'fa-file-zipper',
            in_array($mime, ['application/pdf'], true) => 'fa-file-pdf',
            in_array($mime, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'], true) => 'fa-file-word',
            in_array($mime, ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'], true) => 'fa-file-excel',
            in_array($mime, ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'], true) => 'fa-file-powerpoint',
            default => 'fa-file',
        };
    }
}
