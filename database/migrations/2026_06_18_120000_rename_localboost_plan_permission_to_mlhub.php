<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plans')) {
            return;
        }

        DB::table('plans')
            ->select(['id', 'permissions'])
            ->orderBy('id')
            ->chunkById(100, function ($plans): void {
                foreach ($plans as $plan) {
                    $permissions = json_decode((string) $plan->permissions, true);

                    if (! is_array($permissions)) {
                        continue;
                    }

                    if (! array_key_exists('localboost', $permissions) || array_key_exists('mlhub', $permissions)) {
                        continue;
                    }

                    $permissions['mlhub'] = $permissions['localboost'];
                    unset($permissions['localboost']);

                    DB::table('plans')
                        ->where('id', $plan->id)
                        ->update(['permissions' => json_encode($permissions)]);
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('plans')) {
            return;
        }

        DB::table('plans')
            ->select(['id', 'permissions'])
            ->orderBy('id')
            ->chunkById(100, function ($plans): void {
                foreach ($plans as $plan) {
                    $permissions = json_decode((string) $plan->permissions, true);

                    if (! is_array($permissions)) {
                        continue;
                    }

                    if (! array_key_exists('mlhub', $permissions) || array_key_exists('localboost', $permissions)) {
                        continue;
                    }

                    $permissions['localboost'] = $permissions['mlhub'];
                    unset($permissions['mlhub']);

                    DB::table('plans')
                        ->where('id', $plan->id)
                        ->update(['permissions' => json_encode($permissions)]);
                }
            });
    }
};
