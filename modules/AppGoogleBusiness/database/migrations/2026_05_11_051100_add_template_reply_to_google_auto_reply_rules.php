<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lb_google_auto_reply_rules') && ! Schema::hasColumn('lb_google_auto_reply_rules', 'template_reply')) {
            Schema::table('lb_google_auto_reply_rules', function (Blueprint $table): void {
                $table->text('template_reply')->nullable()->after('reply_mode');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lb_google_auto_reply_rules') && Schema::hasColumn('lb_google_auto_reply_rules', 'template_reply')) {
            Schema::table('lb_google_auto_reply_rules', function (Blueprint $table): void {
                $table->dropColumn('template_reply');
            });
        }
    }
};
