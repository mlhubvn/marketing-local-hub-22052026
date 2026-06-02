<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lb_qr_scans')) {
            return;
        }

        Schema::table('lb_qr_scans', function (Blueprint $table): void {
            $table->index(['user_id', 'campaign_id'], 'lb_qr_scans_user_campaign_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('lb_qr_scans')) {
            return;
        }

        Schema::table('lb_qr_scans', function (Blueprint $table): void {
            $table->dropIndex('lb_qr_scans_user_campaign_index');
        });
    }
};
