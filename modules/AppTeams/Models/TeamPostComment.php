<?php

namespace Modules\AppTeams\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;

class TeamPostComment extends Model
{
    protected $fillable = [
        'team_id',
        'post_id',
        'user_id',
        'message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
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

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
