<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_integrations', function (Blueprint $table): void {
            $table->id();
            $table->string('partner_code', 32);
            $table->string('external_business_id', 128);
            $table->string('external_user_id', 128)->nullable();
            $table->foreignId('mlhub_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('mlhub_workspace_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->unsignedBigInteger('mlhub_business_id')->nullable();
            $table->string('package_code', 64)->nullable();
            $table->string('status', 32)->default('active');
            $table->json('verification_status')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['partner_code', 'external_business_id'], 'partner_integrations_partner_external_business_unique');
            $table->index(['partner_code', 'external_user_id'], 'partner_integrations_partner_external_user_index');
            $table->index(['partner_code', 'status'], 'partner_integrations_partner_status_index');

            $table->foreign('mlhub_business_id')
                ->references('id')
                ->on('lb_businesses')
                ->nullOnDelete();
        });

        Schema::create('partner_onboarding_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('partner_code', 32);
            $table->uuid('request_id')->unique();
            $table->string('external_business_id', 128);
            $table->string('external_user_id', 128)->nullable();
            $table->string('package_code', 64);
            $table->string('status', 32);
            $table->string('current_step', 32);
            $table->json('payload')->nullable();
            $table->json('verification_status')->nullable();
            $table->json('duplicate_check')->nullable();
            $table->foreignId('support_ticket_id')->nullable()->constrained('support_tickets')->nullOnDelete();
            $table->foreignId('mlhub_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('mlhub_workspace_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->unsignedBigInteger('mlhub_business_id')->nullable();
            $table->timestamps();

            $table->index(['partner_code', 'status'], 'partner_onboarding_partner_status_index');
            $table->index(['partner_code', 'request_id'], 'partner_onboarding_partner_request_index');

            $table->foreign('mlhub_business_id')
                ->references('id')
                ->on('lb_businesses')
                ->nullOnDelete();
        });

        Schema::create('partner_api_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('partner_code', 32);
            $table->string('method', 16);
            $table->string('endpoint', 255);
            $table->uuid('request_id')->nullable();
            $table->string('idempotency_key', 128)->nullable();
            $table->string('request_hash', 64)->nullable();
            $table->unsignedInteger('status_code')->default(0);
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamps();

            $table->unique(
                ['partner_code', 'method', 'endpoint', 'idempotency_key'],
                'partner_api_logs_idempotency_unique'
            );
            $table->index(['partner_code', 'request_id'], 'partner_api_logs_partner_request_index');
            $table->index(['partner_code', 'created_at'], 'partner_api_logs_partner_created_index');
        });

        Schema::create('partner_one_time_logins', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_integration_id')
                ->constrained('partner_integrations')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('request_id');
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_one_time_logins');
        Schema::dropIfExists('partner_api_logs');
        Schema::dropIfExists('partner_onboarding_requests');
        Schema::dropIfExists('partner_integrations');
    }
};
