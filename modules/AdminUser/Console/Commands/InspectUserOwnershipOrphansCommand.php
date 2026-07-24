<?php

namespace Modules\AdminUser\Console\Commands;

use Illuminate\Console\Command;
use Modules\AdminUser\Support\UserOwnershipOrphanInspector;

class InspectUserOwnershipOrphansCommand extends Command
{
    protected $signature = 'users:inspect-orphans';

    protected $description = 'Inspect unresolved user-owned records without changing data.';

    public function handle(UserOwnershipOrphanInspector $inspector): int
    {
        $findings = $inspector->inspect();

        if ($findings === []) {
            $this->info('No ownership orphans detected.');

            return self::SUCCESS;
        }

        $this->table(
            ['table', 'record_id', 'reason', 'ownership_evidence', 'safe_to_delete'],
            array_map(fn (array $finding): array => [
                $finding['table'],
                $finding['record_id'],
                $finding['reason'],
                $finding['ownership_evidence'],
                $finding['safe_to_delete'] ? 'yes' : 'no',
            ], $findings)
        );

        $this->warn(count($findings).' ownership issue(s) require attention.');

        return self::SUCCESS;
    }
}
