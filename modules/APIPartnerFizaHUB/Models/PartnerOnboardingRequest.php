<?php

namespace Modules\APIPartnerFizaHUB\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

class PartnerOnboardingRequest extends Model
{
    protected $table = 'partner_onboarding_requests';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'mlhub_user_id' => 'integer',
            'mlhub_workspace_id' => 'integer',
            'mlhub_business_id' => 'integer',
            'support_ticket_id' => 'integer',
            'assigned_consultant_id' => 'integer',
            'payload' => 'array',
            'verification_status' => 'array',
            'duplicate_check' => 'array',
            'partner_confirmed_at' => 'datetime',
            'consultant_contacted_at' => 'datetime',
            'ready_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mlhub_user_id');
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'mlhub_workspace_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(LocalBusiness::class, 'mlhub_business_id');
    }

    public function supportTicket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_consultant_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(PartnerOnboardingStatusHistory::class, 'onboarding_request_id');
    }

    public function statusLabel(): string
    {
        return OnboardingStatusMachine::label($this->publicStatus());
    }

    public function publicStatus(): string
    {
        return OnboardingStatusMachine::publicStatus((string) $this->status);
    }
}
