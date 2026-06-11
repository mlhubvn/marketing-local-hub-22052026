<?php

namespace Modules\AppPayments\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\AppPayments\Support\UserPlanTransitionService;
use Throwable;

class ActivateScheduledPlansCommand extends Command
{
    protected $signature = 'plans:activate-scheduled';

    protected $description = 'Activate scheduled plan downgrades once the current plan expires.';

    public function handle(UserPlanTransitionService $planTransitions): int
    {
        try {
            $count = $planTransitions->activateDuePlans();
        } catch (Throwable $exception) {
            Log::error('plans:activate-scheduled failed', [
                'message' => $exception->getMessage(),
            ]);

            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Activated {$count} scheduled plan transition(s).");

        return self::SUCCESS;
    }
}
