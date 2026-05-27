<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('lb_crm_automation_jobs')) {
            Schema::create('lb_crm_automation_jobs', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('team_id')->nullable()->index();
                $table->foreignId('automation_id')->nullable()->constrained('lb_crm_automations')->cascadeOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained('lb_customers')->nullOnDelete();
                $table->string('event_name')->index();
                $table->json('payload')->nullable();
                $table->string('status', 40)->default('pending')->index();
                $table->unsignedInteger('attempts')->default(0);
                $table->timestamp('run_at')->nullable()->index();
                $table->timestamp('processed_at')->nullable();
                $table->text('message')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lb_crm_automation_jobs');
    }
};
