<?php

namespace Modules\AppTeams\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;

class TeamPostReview extends Model
{
    protected $fillable = [
        'team_id',
        'post_id',
        'submitted_by_user_id',
        'decided_by_user_id',
        'status',
        'decision_note',
        'submitted_at',
        'decided_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'decided_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function post(): BelongsTo
    {
        $class = 'Modules\\AppPublishing\\Models\\PublishingPost';

        if (is_file(base_path('modules/AppPublishing/Models/PublishingPost.php')) && class_exists($class)) {
            return $this->belongsTo($class, 'post_id');
        }

        return $this->belongsTo(User::class, 'post_id')->whereRaw('1 = 0');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }
}
