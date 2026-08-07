<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lb_campaigns') && Schema::hasColumn('lb_campaigns', 'name')) {
            Schema::table('lb_campaigns', function (Blueprint $table): void {
                $table->text('name')->change();
            });
        }

        if (Schema::hasTable('lb_landing_pages') && Schema::hasColumn('lb_landing_pages', 'title')) {
            Schema::table('lb_landing_pages', function (Blueprint $table): void {
                $table->text('title')->change();
            });
        }
    }

    public function down(): void
    {
        // Long derived names may already exist, so narrowing these columns is intentionally unsafe.
    }
};
