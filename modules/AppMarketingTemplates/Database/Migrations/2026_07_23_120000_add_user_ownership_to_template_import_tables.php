<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lb_template_imports')
            && ! Schema::hasColumn('lb_template_imports', 'user_id')) {
            Schema::table('lb_template_imports', function (Blueprint $table): void {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('team_id')
                    ->constrained('users')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('lb_template_packs')
            && ! Schema::hasColumn('lb_template_packs', 'created_by_user_id')) {
            Schema::table('lb_template_packs', function (Blueprint $table): void {
                $table->foreignId('created_by_user_id')
                    ->nullable()
                    ->after('team_id')
                    ->constrained('users')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lb_template_imports')
            && Schema::hasColumn('lb_template_imports', 'user_id')) {
            Schema::table('lb_template_imports', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('user_id');
            });
        }

        if (Schema::hasTable('lb_template_packs')
            && Schema::hasColumn('lb_template_packs', 'created_by_user_id')) {
            Schema::table('lb_template_packs', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('created_by_user_id');
            });
        }
    }
};
