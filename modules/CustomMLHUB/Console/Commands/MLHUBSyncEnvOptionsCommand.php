<?php

namespace Modules\CustomMLHUB\Console\Commands;

use Illuminate\Console\Command;
use Modules\CustomMLHUB\Support\MLHUBEnvOptionsSync;

class MLHUBSyncEnvOptionsCommand extends Command
{
    protected $signature = 'mlhub:sync-env-options';

    protected $description = 'Đồng bộ Environment Variables (Coolify) → Admin Settings (options).';

    public function handle(MLHUBEnvOptionsSync $sync): int
    {
        $count = $sync->apply();

        $this->info("Đã đồng bộ {$count} giá trị từ env vào Admin Settings.");

        return self::SUCCESS;
    }
}
