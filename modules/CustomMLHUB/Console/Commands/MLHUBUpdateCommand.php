<?php

namespace Modules\CustomMLHUB\Console\Commands;

use Database\Support\IdSequence;
use Illuminate\Console\Command;
use Modules\CustomMLHUB\Support\MLHUBArtisanTasks;

class MLHUBUpdateCommand extends Command
{
    protected $signature = 'mlhub:update';

    protected $description = 'Cập nhật MLHUB: migrate + bổ sung seed, giữ nguyên dữ liệu hiện có (ID mới nối tiếp max hiện tại).';

    public function handle(): int
    {
        $this->info('Đang migrate (chỉ migration mới)...');
        $this->call('migrate', ['--force' => true]);

        MLHUBArtisanTasks::seed($this, 'update');
        MLHUBArtisanTasks::optimize($this);

        $this->newLine();
        $this->info('Hoàn tất cập nhật MLHUB — dữ liệu cũ được giữ nguyên.');
        $this->line('ID sequence: '.(string) IdSequence::startingId().' (AUTO_INCREMENT sau max hiện có).');

        return self::SUCCESS;
    }
}
