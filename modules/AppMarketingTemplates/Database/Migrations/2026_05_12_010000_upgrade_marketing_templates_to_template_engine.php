<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lb_marketing_templates')) {
            return;
        }

        Schema::table('lb_marketing_templates', function (Blueprint $table): void {
            if (! Schema::hasColumn('lb_marketing_templates', 'preview_image')) {
                $table->string('preview_image')->nullable()->after('icon');
            }

            if (! Schema::hasColumn('lb_marketing_templates', 'source')) {
                $table->string('source', 30)->default('custom')->after('is_system')->index();
            }

            if (! Schema::hasColumn('lb_marketing_templates', 'visibility')) {
                $table->string('visibility', 30)->default('private')->after('source')->index();
            }

            if (! Schema::hasColumn('lb_marketing_templates', 'version')) {
                $table->string('version', 30)->default('1.0.0')->after('status');
            }

            if (! Schema::hasColumn('lb_marketing_templates', 'design')) {
                $table->json('design')->nullable()->after('content');
            }

            if (! Schema::hasColumn('lb_marketing_templates', 'builder_schema')) {
                $table->json('builder_schema')->nullable()->after('design');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('lb_marketing_templates')) {
            return;
        }

        Schema::table('lb_marketing_templates', function (Blueprint $table): void {
            foreach (['preview_image', 'source', 'visibility', 'version', 'design', 'builder_schema'] as $column) {
                if (Schema::hasColumn('lb_marketing_templates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
