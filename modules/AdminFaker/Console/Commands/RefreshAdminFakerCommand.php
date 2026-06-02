<?php

namespace Modules\AdminFaker\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\AdminFaker\Support\AdminFakerService;
use Modules\AdminFaker\Support\MLHUBDemoSeedProgress;

class RefreshAdminFakerCommand extends Command
{
    protected $signature = 'admin-faker:refresh {--no-clear : Skip clearing existing demo data before seeding}';

    protected $description = 'Refresh Admin Faker demo data for the first user account';

    public function handle(AdminFakerService $faker): int
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        DB::connection()->disableQueryLog();
        MLHUBDemoSeedProgress::bind($this->output);

        $this->info('Bắt đầu Admin Faker (~6M QR, ~60% volume — khoảng 12–25 phút, xem log bên dưới).');
        $this->newLine();

        $result = $faker->seedForFirstUser(! $this->option('no-clear'));

        $user = (array) ($result['user'] ?? []);
        $counts = (array) ($result['counts'] ?? []);

        $this->info(sprintf(
            'Admin Faker refreshed for user #%s (%s).',
            (string) ($user['id'] ?? '?'),
            (string) ($user['email'] ?? 'n/a')
        ));

        foreach ($counts as $key => $value) {
            $this->line(sprintf('- %s: %s', $key, (string) $value));
        }

        $this->newLine();
        $this->warn('Không chạy `optimize:clear` một mình trên production — sẽ mất routes cache và không login được.');
        $this->line('Nếu đã chạy nhầm: `php artisan optimize` để khôi phục.');

        return self::SUCCESS;
    }
}
