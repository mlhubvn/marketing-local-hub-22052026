<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lb_loyalty_cards', function (Blueprint $table): void {
            if (! Schema::hasColumn('lb_loyalty_cards', 'settings')) {
                $table->json('settings')->nullable()->after('max_stamps_per_day');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lb_loyalty_cards', function (Blueprint $table): void {
            if (Schema::hasColumn('lb_loyalty_cards', 'settings')) {
                $table->dropColumn('settings');
            }
        });
    }
};
