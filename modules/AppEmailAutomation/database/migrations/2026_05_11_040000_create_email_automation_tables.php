<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lb_email_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('business_id')->nullable()->constrained('lb_businesses')->nullOnDelete();
            $table->string('name');
            $table->string('type', 80)->default('general');
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->longText('body');
            $table->string('language', 12)->default('en');
            $table->boolean('is_system')->default(false);
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->index(['user_id', 'business_id', 'status']);
        });

        Schema::create('lb_email_automations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('business_id')->nullable()->constrained('lb_businesses')->nullOnDelete();
            $table->foreignId('email_template_id')->nullable()->constrained('lb_email_templates')->nullOnDelete();
            $table->string('name');
            $table->string('trigger_event', 80);
            $table->string('status', 20)->default('draft');
            $table->string('delay_type', 20)->default('immediate');
            $table->unsignedInteger('delay_value')->default(0);
            $table->string('delay_unit', 20)->default('minutes');
            $table->json('condition_json')->nullable();
            $table->json('action_json')->nullable();
            $table->string('send_to', 30)->default('customer');
            $table->string('custom_email')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'trigger_event', 'status']);
        });

        Schema::create('lb_email_automation_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('automation_id')->nullable()->constrained('lb_email_automations')->nullOnDelete();
            $table->foreignId('email_template_id')->nullable()->constrained('lb_email_templates')->nullOnDelete();
            $table->foreignId('business_id')->nullable()->constrained('lb_businesses')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('lb_customers')->nullOnDelete();
            $table->string('trigger_event', 80)->nullable();
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('subject');
            $table->longText('body')->nullable();
            $table->string('status', 20)->default('queued');
            $table->text('error_message')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status', 'created_at']);
            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lb_email_automation_logs');
        Schema::dropIfExists('lb_email_automations');
        Schema::dropIfExists('lb_email_templates');
    }
};
