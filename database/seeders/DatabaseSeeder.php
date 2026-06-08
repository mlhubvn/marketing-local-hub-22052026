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
        foreach ((array) config('mlhub.default_seeders', []) as $seederClass) {
            $this->call($seederClass);
        }

        IdSequence::apply();

        if (class_exists(MLHUBEnvOptionsSync::class)) {
            app(MLHUBEnvOptionsSync::class)->apply();
        }
    }
}
