<?php

namespace Modules\CustomMLHUB\Console\Commands;

use Illuminate\Console\Command;
use Modules\AdminUser\Models\User;

class MLHUBInstallCommand extends Command
{
    protected $signature = 'mlhub:install
                            {--force : Cho phép seed khi DB đã có user (không wipe)}';

    protected $description = 'Cài đặt MLHUB lần đầu: migrate + seed (gói, AI templates, admin từ env).';

    public function handle(): int
    {
        $email = trim((string) config('custommlhub.first_user.email', ''));
        $password = (string) config('custommlhub.first_user.password', '');

        if ($email === '' || $password === '') {
            $this->error('Thiếu MLHUB_FIRST_USER_EMAIL hoặc MLHUB_FIRST_USER_PASSWORD trong Environment Variables.');

            return self::FAILURE;
        }

        if (User::query()->exists() && ! $this->option('force')) {
            $this->error('DB đã có user. Chỉ chạy lại nếu cần bổ sung seed (dùng --force) hoặc wipe DB thủ công trên staging.');

            return self::FAILURE;
        }

        $this->info('Đang migrate...');
        $this->call('migrate', ['--force' => true]);

        $this->info('Đang seed (gói, cấu hình VN, super admin, marketplace)...');
        $this->call('db:seed', ['--force' => true]);

        $this->call('optimize:clear');
        $this->call('optimize');

        $this->newLine();
        $this->info('Hoàn tất cài đặt MLHUB.');
        $this->line("Đăng nhập super admin: {$email}");
        $this->line('Giao diện: frontend = mlhubfrontend, backend = mlhubbackend.');
        $this->line('ID sequence: '.(string) config('custommlhub.starting_id', 147123468).' (AUTO_INCREMENT sau seed).');

        return self::SUCCESS;
    }
}
