<?php

namespace Database\Seeders;

use Database\Support\IdSequence;
use Illuminate\Database\Seeder;
use Modules\CustomMLHUB\Support\MLHUBEnvOptionsSync;

class DatabaseSeeder extends Seeder
{
    /**
     * MLHUB seed stack — `php artisan db:seed` / `php artisan mlhub:install`.
     */
    public function run(): void
    {
        // Install: đặt AUTO_INCREMENT = STARTING_ID trên MỌI bảng (đang rỗng sau migrate:fresh)
        // TRƯỚC khi seed, để cả bản ghi seed tự sinh ID (options, teams, team_user,
        // affiliate_profiles, lb_email_templates, lb_template_packs…) đều bắt đầu từ STARTING_ID.
        // Update: bỏ qua để giữ nguyên dữ liệu cũ (ID mới nối tiếp max hiện có).
        if (! IdSequence::isUpdateMode()) {
            IdSequence::apply();
        }

        foreach ((array) config('mlhub.default_seeders', []) as $seederClass) {
            $this->call($seederClass);
        }

        IdSequence::apply();

        if (class_exists(MLHUBEnvOptionsSync::class)) {
            app(MLHUBEnvOptionsSync::class)->apply();
        }
    }
}
