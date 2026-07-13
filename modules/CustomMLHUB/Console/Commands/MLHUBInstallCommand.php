<?php

namespace Modules\CustomMLHUB\Console\Commands;

use Database\Support\IdSequence;
use Illuminate\Console\Command;
use Modules\CustomMLHUB\Actions\SeedStaticPagesAction;
use Modules\CustomMLHUB\Support\MLHUBArtisanTasks;
use Throwable;

class MLHUBInstallCommand extends Command
{
    protected $signature = 'mlhub:install
                            {--force : Bỏ qua xác nhận xóa toàn bộ database}';

    protected $description = 'Cài đặt MLHUB từ đầu: xóa sạch database, migrate + seed (gói, AI templates, admin từ env).';

    public function handle(SeedStaticPagesAction $seedStaticPages): int
    {
        if (MLHUBArtisanTasks::firstUserCredentialsMissing()) {
            $this->error('Thiếu MLHUB_FIRST_USER_EMAIL hoặc MLHUB_FIRST_USER_PASSWORD trong Environment Variables.');

            return self::FAILURE;
        }

        if (MLHUBArtisanTasks::destructiveResetBlocked()) {
            $this->error('Production đang chặn xóa DB. Đặt tạm MLHUB_ALLOW_RESET_DEMO=true trên Coolify, redeploy, rồi chạy lại lệnh này.');
            $this->line('Sau khi cài xong, đặt lại MLHUB_ALLOW_RESET_DEMO=false.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('XÓA TOÀN BỘ database và cài lại từ đầu?', false)) {
            $this->warn('Đã hủy. Dùng mlhub:update nếu chỉ cần cập nhật mà giữ dữ liệu.');

            return self::FAILURE;
        }

        $this->warn('Đang xóa sạch database và migrate lại...');
        $this->call('migrate:fresh', ['--force' => true]);

        MLHUBArtisanTasks::seed($this, 'install');

        try {
            // Always force: migrate:fresh already wiped options; command --force is only a confirm bypass.
            $result = $seedStaticPages->handle(force: true);
            $this->info('Đã cài trang pháp lý MLHUB ('.count($result['updated']).' option).');
        } catch (Throwable $e) {
            $this->error('Không thể cài trang pháp lý MLHUB: '.$e->getMessage());

            return self::FAILURE;
        }

        MLHUBArtisanTasks::optimize($this);

        $email = trim((string) config('custommlhub.first_user.email', ''));

        $this->newLine();
        $this->info('Hoàn tất cài đặt MLHUB (database mới).');
        $this->line("Đăng nhập super admin: {$email}");
        $this->line('Giao diện: frontend = mlhubfrontend, backend = mlhubbackend.');
        $this->line('ID sequence: '.(string) IdSequence::startingId().' (bắt đầu từ ID này).');

        return self::SUCCESS;
    }
}
