<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('lb_crm_automations')) {
            Schema::create('lb_crm_automations', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('team_id')->nullable()->index();
                $table->foreignId('business_id')->nullable()->constrained('lb_businesses')->nullOnDelete();
                $table->string('name');
                $table->string('trigger_event')->index();
                $table->json('condition_json')->nullable();
                $table->json('action_json');
                $table->string('delay_type', 40)->default('immediate');
                $table->unsignedInteger('delay_value')->nullable();
                $table->string('delay_unit', 40)->nullable();
                $table->string('status', 40)->default('active')->index();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('lb_crm_automation_logs')) {
            Schema::create('lb_crm_automation_logs', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('team_id')->nullable()->index();
                $table->foreignId('automation_id')->constrained('lb_crm_automations')->cascadeOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained('lb_customers')->nullOnDelete();
                $table->string('related_type')->nullable();
                $table->unsignedBigInteger('related_id')->nullable();
                $table->string('status', 40)->default('success')->index();
                $table->text('message')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('lb_customer_merge_logs')) {
            Schema::create('lb_customer_merge_logs', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('team_id')->nullable()->index();
                $table->foreignId('primary_customer_id')->constrained('lb_customers')->cascadeOnDelete();
                $table->unsignedBigInteger('merged_customer_id');
                $table->foreignId('merged_by')->nullable()->constrained('users')->nullOnDelete();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lb_customer_merge_logs');
        Schema::dropIfExists('lb_crm_automation_logs');
        Schema::dropIfExists('lb_crm_automations');
    }
};
