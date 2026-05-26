<?php

namespace Database\Seeders;

use Database\Support\IdSequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * MLHUB seed stack — used by `php artisan db:seed` and mirrored in installer config.
     */
    public function run(): void
    {
        $this->call([
            MLHUBFoundationSeeder::class,
            PlanSeeder::class,
            AITemplateCategorySeeder::class,
            AITemplateSeeder::class,
        ]);

        IdSequence::apply();
    }
}
