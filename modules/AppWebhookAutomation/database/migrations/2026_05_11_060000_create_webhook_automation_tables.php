<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lb_webhook_automations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('business_id')->nullable()->constrained('lb_businesses')->nullOnDelete();
            $table->string('name');
            $table->string('trigger_event', 80);
            $table->string('status', 20)->default('draft');
            $table->string('delay_type', 20)->default('immediate');
            $table->unsignedInteger('delay_value')->default(0);
            $table->string('delay_unit', 20)->default('minutes');
            $table->json('condition_json')->nullable();
            $table->string('webhook_url', 2048);
            $table->string('method', 12)->default('POST');
            $table->json('headers_json')->nullable();
            $table->string('secret_token')->nullable();
            $table->boolean('retry_on_failure')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'trigger_event', 'status']);
        });

        Schema::create('lb_webhook_automation_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('automation_id')->nullable()->constrained('lb_webhook_automations')->nullOnDelete();
            $table->foreignId('business_id')->nullable()->constrained('lb_businesses')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('lb_customers')->nullOnDelete();
            $table->string('trigger_event', 80)->nullable();
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('action_type', 40)->default('webhook');
            $table->string('webhook_url', 2048)->nullable();
            $table->string('method', 12)->default('POST');
            $table->json('request_headers')->nullable();
            $table->json('request_payload')->nullable();
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->longText('response_body')->nullable();
            $table->string('status', 20)->default('queued');
            $table->text('error_message')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status', 'created_at']);
            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lb_webhook_automation_logs');
        Schema::dropIfExists('lb_webhook_automations');
    }
};
