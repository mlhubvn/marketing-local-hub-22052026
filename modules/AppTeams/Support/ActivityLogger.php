<?php

namespace Modules\AppTeams\Support;

use Illuminate\Support\Facades\DB;

class ActivityLogger
{
    public function log(string $action, array $data = []): void
    {
        if (! DB::getSchemaBuilder()->hasTable('team_activity_logs')) {
            return;
        }

        DB::table('team_activity_logs')->insert([
            'team_id' => $data['team_id'] ?? null,
            'owner_user_id' => $data['owner_user_id'] ?? null,
            'actor_user_id' => $data['actor_user_id'] ?? auth()->id(),
            'subject_type' => $data['subject_type'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'action' => $action,
            'metadata' => json_encode((array) ($data['metadata'] ?? [])),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
