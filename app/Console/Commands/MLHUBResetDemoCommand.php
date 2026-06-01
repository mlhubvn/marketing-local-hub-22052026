<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MLHUBResetDemoCommand extends Command
{
    protected $signature = 'mlhub:reset-demo
                            {--force : Xác nhận xóa toàn bộ dữ liệu MySQL và seed lại}';

    protected $description = 'Xóa DB, migrate lại và seed MLHUB (admin demo + dữ liệu mẫu VN). Chỉ dùng pilot/staging.';

    public function handle(): int
    {
        if (! $this->option('force')) {
            $this->error('Lệnh nguy hiểm: thêm --force để xác nhận xóa toàn bộ dữ liệu.');

            return self::FAILURE;
        }

        if (app()->environment('production') && ! (bool) env('MLHUB_ALLOW_RESET_DEMO', false)) {
            $this->error('Production: đặt MLHUB_ALLOW_RESET_DEMO=true trong env (pilot) hoặc chạy từng bước thủ công.');

            return self::FAILURE;
        }

        $this->warn('Đang xóa toàn bộ bảng và seed lại...');

        $this->call('db:wipe', ['--force' => true, '--drop-views' => true]);
        $this->call('migrate', ['--force' => true]);
        $this->call('db:seed', ['--force' => true]);

        $this->warn('Đang seed Admin Faker enterprise (~10M QR, 11 cơ sở, 11k khách) — có thể 20–60 phút.');
        $this->line('Gợi ý pilot: max_execution_time ≥ 3600, memory_limit ≥ 1024M, MySQL innodb_buffer_pool lớn.');
        $fakerExit = $this->call('admin-faker:refresh', ['--no-clear' => true]);

        if ($fakerExit !== self::SUCCESS) {
            $this->error('admin-faker:refresh thất bại — kiểm tra module AdminFaker đã bật.');

            return self::FAILURE;
        }

        $this->call('db:seed', ['--class' => \Database\Seeders\MLHUBDemoExtrasSeeder::class, '--force' => true]);

        $this->call('optimize:clear');

        $this->newLine();
        $this->info('Hoàn tất. Đăng nhập: demo@mlhub.vn / 123456 (super admin + demo đầy đủ).');
        $this->line('Giao diện: frontend = mlhubfrontend, backend = mlhubbackend.');
        $this->line('Sau khi thoát container app, xóa cache session/queue trên Redis (mật khẩu từ Coolify REDIS_PASSWORD):');
        $this->line('  redis-cli -h <REDIS_HOST> -p <REDIS_PORT> -a "<REDIS_PASSWORD>" FLUSHALL');

        return self::SUCCESS;
    }
}
