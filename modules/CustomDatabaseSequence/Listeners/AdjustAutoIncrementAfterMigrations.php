<?php

namespace Modules\CustomDatabaseSequence\Listeners;

use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class AdjustAutoIncrementAfterMigrations
{
    public function handle(MigrationsEnded $event): void
    {
        if (config('modules.customdatabasesequence.auto_apply', true) !== true) {
            return;
        }

        try {
            Artisan::call('custom:set-id-sequence', ['--quiet-skip' => true]);
        } catch (Throwable $e) {
            // Never break a migration run because of the AUTO_INCREMENT pass.
            logger()->warning('CustomDatabaseSequence: post-migration adjustment failed', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
