<?php

namespace Modules\APIPartnerFizaHUB\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AdminSupport\Models\SupportTicket;

class PartnerSupportTicketContext extends Model
{
    protected $table = 'partner_support_ticket_contexts';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'support_ticket_id' => 'integer',
            'partner_integration_id' => 'integer',
            'context' => 'array',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(PartnerIntegration::class, 'partner_integration_id');
    }
}
