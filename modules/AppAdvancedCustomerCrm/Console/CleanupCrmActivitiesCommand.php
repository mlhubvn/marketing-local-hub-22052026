<?php

namespace Modules\AppAdvancedCustomerCrm\Console;

use Illuminate\Console\Command;
use Modules\AppAdvancedCustomerCrm\Models\CustomerActivity;

class CleanupCrmActivitiesCommand extends Command
{
    protected $signature = 'crm:cleanup-activities {--days=365}';

    protected $description = 'Delete old Advanced CRM activity records.';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $deleted = CustomerActivity::query()
            ->where('occurred_at', '<', now()->subDays($days))
            ->delete();

        $this->info(__('Deleted :count old CRM activities.', ['count' => $deleted]));

        return self::SUCCESS;
    }
}
