<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lb_customers', function (Blueprint $table): void {
            if (! Schema::hasColumn('lb_customers', 'total_loyalty_stamps')) {
                $table->unsignedInteger('total_loyalty_stamps')->default(0);
            }

            if (! Schema::hasColumn('lb_customers', 'total_referrals')) {
                $table->unsignedInteger('total_referrals')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('lb_customers', function (Blueprint $table): void {
            if (Schema::hasColumn('lb_customers', 'total_referrals')) {
                $table->dropColumn('total_referrals');
            }

            if (Schema::hasColumn('lb_customers', 'total_loyalty_stamps')) {
                $table->dropColumn('total_loyalty_stamps');
            }
        });
    }
};
