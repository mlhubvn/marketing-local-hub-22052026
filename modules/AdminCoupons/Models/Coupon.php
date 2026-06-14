<?php

namespace Modules\AdminCoupons\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'coupons';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'type' => 'integer',
            'discount' => 'decimal:2',
            'start_date' => 'integer',
            'end_date' => 'integer',
            'plans' => 'array',
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
            'status' => 'boolean',
            'changed' => 'integer',
            'created' => 'integer',
        ];
    }

    public function typeLabel(): string
    {
        return (int) $this->type === 2 ? __('Price') : __('Percent');
    }

    public function discountLabel(): string
    {
        if ((int) $this->type === 2) {
            return format_money((float) $this->discount);
        }

        return format_percent_locale((float) $this->discount);
    }

    public function statusLabel(): string
    {
        return $this->status ? __('Enabled') : __('Disabled');
    }

    public function statusVariant(): string
    {
        return $this->status ? 'success' : 'neutral';
    }

    public function usageLimitLabel(): string
    {
        return (int) $this->usage_limit === -1 ? __('Unlimited') : format_number_locale((int) $this->usage_limit);
    }

    public function startDateFormatted(string $format = 'Y-m-d'): ?string
    {
        if (! $this->start_date) {
            return null;
        }

        return Carbon::createFromTimestamp((int) $this->start_date)->format($format);
    }

    public function endDateFormatted(string $format = 'Y-m-d'): ?string
    {
        if (! $this->end_date || (int) $this->end_date === -1) {
            return __('No expiry');
        }

        return Carbon::createFromTimestamp((int) $this->end_date)->format($format);
    }

    public function createdAtFormatted(string $format = 'Y-m-d H:i'): ?string
    {
        if (! $this->created) {
            return null;
        }

        return format_carbon_display(Carbon::createFromTimestamp((int) $this->created), $format);
    }
}
