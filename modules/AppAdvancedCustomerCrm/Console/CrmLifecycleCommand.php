<?php

namespace Modules\AppAdvancedCustomerCrm\Console;

use Illuminate\Console\Command;
use Modules\AppAdvancedCustomerCrm\Support\CrmAutomationService;
use Modules\AppCustomers\Models\Customer;

class CrmLifecycleCommand extends Command
{
    protected $signature = 'crm:lifecycle {--inactive-days=30}';

    protected $description = 'Run Advanced CRM lifecycle checks such as inactive customers.';

    public function handle(CrmAutomationService $automations): int
    {
        $days = max(1, (int) $this->option('inactive-days'));
        $cutoff = now()->subDays($days);
        $count = 0;

        Customer::query()
            ->where(fn ($query) => $query->whereNull('last_activity_at')->orWhere('last_activity_at', '<', $cutoff))
            ->where('status', '!=', 'inactive')
            ->chunkById(100, function ($customers) use ($automations, $days, &$count): void {
                foreach ($customers as $customer) {
                    $customer->forceFill(['status' => 'inactive'])->save();
                    $automations->handle('customer_inactive', $customer->refresh(), ['inactive_days' => $days]);
                    $count++;
                }
            });

        $this->info(__('Marked :count customers inactive.', ['count' => $count]));

        return self::SUCCESS;
    }
}
