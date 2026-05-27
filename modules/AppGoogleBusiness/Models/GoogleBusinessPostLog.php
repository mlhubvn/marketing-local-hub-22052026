<?php

namespace Modules\AppGoogleBusiness\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleBusinessPostLog extends Model
{
    public const MAX_LOGS_PER_TEAM = 20;

    protected $table = 'lb_google_business_post_logs';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_body' => 'array',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(GoogleBusinessPost::class, 'post_id');
    }

    public static function record(array $attributes): self
    {
        $log = static::query()->create($attributes);
        static::trimForTeam((int) ($attributes['team_id'] ?? 0));

        return $log;
    }

    public static function trimForTeam(int $teamId): void
    {
        if ($teamId <= 0) {
            return;
        }

        $keepIds = static::query()
            ->where('team_id', $teamId)
            ->latest('id')
            ->limit(self::MAX_LOGS_PER_TEAM)
            ->pluck('id');

        static::query()
            ->where('team_id', $teamId)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }
}
