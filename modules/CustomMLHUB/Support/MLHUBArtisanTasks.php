<?php

namespace Modules\CustomMLHUB\Support;

use Illuminate\Console\Command;
use Modules\CustomMLHUB\Database\Seeders\MLHUBDemoBoardSeeder;
use Modules\CustomMLHUB\Database\Seeders\MLHUBDemoExtrasSeeder;

class MLHUBArtisanTasks
{
    public static function seed(Command $command, string $mode): void
    {
        config(['mlhub.seeding_mode' => $mode]);

        $command->info('Đang seed (gói, cấu hình VN, super admin, marketplace)...');
        $command->call('db:seed', ['--force' => true]);

        if ($mode === 'install' && class_exists(MLHUBDemoBoardSeeder::class)) {
            $command->info('Đang seed dữ liệu demo MLHUB (5 tài khoản)...');
            $command->call('db:seed', [
                '--class' => MLHUBDemoBoardSeeder::class,
                '--force' => true,
            ]);

            if (class_exists(MLHUBDemoExtrasSeeder::class)) {
                $command->info('Đang seed dữ liệu demo bổ sung (account/portal/CRM/admin)...');
                $command->call('db:seed', [
                    '--class' => MLHUBDemoExtrasSeeder::class,
                    '--force' => true,
                ]);
            }
        }
    }

    public static function optimize(Command $command): void
    {
        $command->call('optimize:clear', ['--except' => 'routes']);
        $command->call('optimize', ['--except' => 'routes']);
    }

    public static function firstUserCredentialsMissing(): bool
    {
        $email = trim((string) config('custommlhub.first_user.email', ''));
        $password = (string) config('custommlhub.first_user.password', '');

        return $email === '' || $password === '';
    }

    public static function destructiveResetBlocked(): bool
    {
        return app()->isProduction() && ! (bool) env('MLHUB_ALLOW_RESET_DEMO', false);
    }
}
