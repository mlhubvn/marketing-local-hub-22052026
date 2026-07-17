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
}
