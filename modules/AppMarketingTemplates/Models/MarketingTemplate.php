<?php

namespace Modules\AppMarketingTemplates\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;

class MarketingTemplate extends Model
{
    protected $table = 'lb_marketing_templates';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'settings' => 'array',
            'design' => 'array',
            'builder_schema' => 'array',
            'is_system' => 'boolean',
            'featured' => 'boolean',
            'usage_count' => 'integer',
            'rating_count' => 'integer',
            'rating_sum' => 'integer',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'campaign' => __('Campaign'),
            'landing_page' => __('Landing Page'),
            'form' => __('Form'),
            'content' => __('Content'),
            'email' => __('Email'),
            'whatsapp' => __('WhatsApp'),
            'automation' => __('Automation'),
            default => str((string) $this->type)->headline()->toString(),
        };
    }

    public function categoryLabel(): string
    {
        return str((string) $this->category ?: 'general')->replace('_', ' ')->headline()->toString();
    }

    public function goalLabel(): string
    {
        return str((string) $this->goal ?: 'lead')->replace('_', ' ')->headline()->toString();
    }

    public function statusLabel(): string
    {
        return str((string) $this->status ?: 'active')->headline()->toString();
    }

    public function originLabel(): string
    {
        return match ((string) $this->source) {
            'system' => __('System'),
            'marketplace' => __('Marketplace'),
            'imported' => __('Imported'),
            'ai_generated' => __('AI generated'),
            default => $this->is_system ? __('System') : __('Custom'),
        };
    }

    public function visibilityLabel(): string
    {
        return str((string) $this->visibility ?: 'private')->replace('_', ' ')->headline()->toString();
    }

    public function marketplaceStatusLabel(): string
    {
        return match ((string) ($this->marketplace_status ?: 'none')) {
            'pending' => __('Pending approval'),
            'approved' => __('Approved'),
            'rejected' => __('Rejected'),
            default => __('Not submitted'),
        };
    }

    public function ratingAverage(): float
    {
        return (int) $this->rating_count > 0
            ? round(((int) $this->rating_sum) / ((int) $this->rating_count), 1)
            : 0.0;
    }

    public function canBeManagedBy(?User $user): bool
    {
        return ! $this->is_system && (int) $this->user_id === (int) ($user?->id ?? 0);
    }
}
