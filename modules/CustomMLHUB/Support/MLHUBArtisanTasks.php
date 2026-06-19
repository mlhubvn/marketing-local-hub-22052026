<?php

namespace Modules\CustomMLHUB\Support;

use Illuminate\Console\Command;
use Modules\CustomMLHUB\Database\Seeders\MLHUBDemoBoardSeeder;

class MLHUBArtisanTasks
{
    public static function seed(Command $command, string $mode): void
    {
        config(['mlhub.seeding_mode' => $mode]);

        $command->info('Đang seed (gói, cấu hình VN, super admin, marketplace)...');
        $command->call('db:seed', ['--force' => true]);

        if ($mode === 'install' && class_exists(MLHUBDemoBoardSeeder::class)) {
            $command->info('Dang seed demo BOD MLHUB...');
            $command->call('db:seed', [
                '--class' => MLHUBDemoBoardSeeder::class,
                '--force' => true,
            ]);
        }
    }

    public static function optimize(Command $command): void
    {
        $command->call('optimize:clear');
        $command->call('optimize');
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
