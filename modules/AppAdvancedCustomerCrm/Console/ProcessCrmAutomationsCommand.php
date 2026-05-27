<?php

namespace Modules\AppAdvancedCustomerCrm\Console;

use Illuminate\Console\Command;
use Modules\AppAdvancedCustomerCrm\Models\CrmAutomationJob;
use Modules\AppAdvancedCustomerCrm\Support\CrmAutomationService;

class ProcessCrmAutomationsCommand extends Command
{
    protected $signature = 'crm:process-automations {--limit=50}';

    protected $description = 'Process due Advanced CRM automation jobs.';

    public function handle(CrmAutomationService $service): int
    {
        $jobs = CrmAutomationJob::query()
            ->with(['automation', 'customer'])
            ->where('status', 'pending')
            ->where(fn ($query) => $query->whereNull('run_at')->orWhere('run_at', '<=', now()))
            ->oldest('run_at')
            ->limit((int) $this->option('limit'))
            ->get();

        foreach ($jobs as $job) {
            $service->processJob($job);
        }

        $this->info(__('Processed :count CRM automation jobs.', ['count' => $jobs->count()]));

        return self::SUCCESS;
    }
}
