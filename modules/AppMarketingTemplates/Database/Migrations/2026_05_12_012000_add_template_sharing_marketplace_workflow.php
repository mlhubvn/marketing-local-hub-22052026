<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lb_marketing_templates')) {
            Schema::table('lb_marketing_templates', function (Blueprint $table): void {
                if (! Schema::hasColumn('lb_marketing_templates', 'marketplace_status')) {
                    $table->string('marketplace_status', 30)->default('none')->after('visibility')->index();
                }

                if (! Schema::hasColumn('lb_marketing_templates', 'submitted_at')) {
                    $table->timestamp('submitted_at')->nullable()->after('marketplace_status');
                }

                if (! Schema::hasColumn('lb_marketing_templates', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('submitted_at');
                }

                if (! Schema::hasColumn('lb_marketing_templates', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
                }

                if (! Schema::hasColumn('lb_marketing_templates', 'featured')) {
                    $table->boolean('featured')->default(false)->after('approved_by')->index();
                }

                if (! Schema::hasColumn('lb_marketing_templates', 'rating_count')) {
                    $table->unsignedInteger('rating_count')->default(0)->after('featured');
                }

                if (! Schema::hasColumn('lb_marketing_templates', 'rating_sum')) {
                    $table->unsignedInteger('rating_sum')->default(0)->after('rating_count');
                }
            });
        }

        if (! Schema::hasTable('lb_template_ratings')) {
            Schema::create('lb_template_ratings', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('template_id')->constrained('lb_marketing_templates')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedTinyInteger('rating');
                $table->timestamps();
                $table->unique(['template_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lb_template_ratings');

        if (! Schema::hasTable('lb_marketing_templates')) {
            return;
        }

        Schema::table('lb_marketing_templates', function (Blueprint $table): void {
            if (Schema::hasColumn('lb_marketing_templates', 'approved_by')) {
                $table->dropForeign(['approved_by']);
            }

            foreach (['marketplace_status', 'submitted_at', 'approved_at', 'approved_by', 'featured', 'rating_count', 'rating_sum'] as $column) {
                if (Schema::hasColumn('lb_marketing_templates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
