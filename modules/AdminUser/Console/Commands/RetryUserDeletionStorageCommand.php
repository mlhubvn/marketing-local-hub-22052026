<?php

namespace Modules\AdminUser\Console\Commands;

use Illuminate\Console\Command;
use Modules\AdminUser\Support\UserDeletionStorageCleanup;

class RetryUserDeletionStorageCommand extends Command
{
    protected $signature = 'mlhub:user-deletion-storage-retry {--limit=100 : Maximum pending assets to retry}';

    protected $description = 'Retry storage assets that could not be removed after a committed user deletion';

    public function handle(UserDeletionStorageCleanup $cleanup): int
    {
        $result = $cleanup->retryPending((int) $this->option('limit'));

        $this->components->info(sprintf(
            'Processed %d asset(s): %d deleted, %d still pending.',
            $result['processed'],
            $result['deleted'],
            $result['failed'],
        ));

        return $result['failed'] === 0 ? self::SUCCESS : self::FAILURE;
    }
}
