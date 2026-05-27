<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lb_google_business_posts')) {
            return;
        }

        Schema::table('lb_google_business_posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('lb_google_business_posts', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('end_at')->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('lb_google_business_posts')) {
            return;
        }

        Schema::table('lb_google_business_posts', function (Blueprint $table): void {
            if (Schema::hasColumn('lb_google_business_posts', 'scheduled_at')) {
                $table->dropColumn('scheduled_at');
            }
        });
    }
};
