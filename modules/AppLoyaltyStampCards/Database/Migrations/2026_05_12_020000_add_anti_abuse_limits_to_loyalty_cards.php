<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lb_loyalty_cards', function (Blueprint $table): void {
            if (! Schema::hasColumn('lb_loyalty_cards', 'stamp_cooldown_minutes')) {
                $table->unsignedInteger('stamp_cooldown_minutes')->default(1440)->after('expiry_days');
            }

            if (! Schema::hasColumn('lb_loyalty_cards', 'max_stamps_per_day')) {
                $table->unsignedInteger('max_stamps_per_day')->default(1)->after('stamp_cooldown_minutes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lb_loyalty_cards', function (Blueprint $table): void {
            if (Schema::hasColumn('lb_loyalty_cards', 'max_stamps_per_day')) {
                $table->dropColumn('max_stamps_per_day');
            }

            if (Schema::hasColumn('lb_loyalty_cards', 'stamp_cooldown_minutes')) {
                $table->dropColumn('stamp_cooldown_minutes');
            }
        });
    }
};
