<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shared FizaHUB partner schema bootstrap for feature tests.
 */
function createFizaHubPartnerTables(): void
{
    Schema::dropIfExists('partner_support_attachments');
    Schema::dropIfExists('partner_webhook_outbox');
    Schema::dropIfExists('partner_support_ticket_contexts');
    Schema::dropIfExists('partner_support_presets');
    Schema::dropIfExists('partner_package_assignments');
    Schema::dropIfExists('partner_onboarding_status_histories');
    Schema::dropIfExists('partner_one_time_logins');
    Schema::dropIfExists('partner_api_logs');
    Schema::dropIfExists('partner_onboarding_requests');
    Schema::dropIfExists('partner_integrations');

    $migration = require base_path('modules/APIPartnerFizaHUB/Database/Migrations/2026_07_13_000000_create_fizahub_partner_api_tables.php');
    $migration->up();

    Schema::table('partner_onboarding_requests', function (Blueprint $table): void {
        $table->string('requested_package_code', 64)->nullable();
        $table->string('approved_package_code', 64)->nullable();
        $table->string('admin_status', 32)->nullable();
        $table->unsignedBigInteger('assigned_consultant_id')->nullable();
        $table->text('review_note')->nullable();
        $table->timestamp('partner_confirmed_at')->nullable();
        $table->timestamp('consultant_contacted_at')->nullable();
        $table->timestamp('ready_at')->nullable();
        $table->timestamp('completed_at')->nullable();
        $table->timestamp('cancelled_at')->nullable();
        $table->timestamp('last_synced_at')->nullable();
    });

    Schema::create('partner_onboarding_status_histories', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('onboarding_request_id');
        $table->string('from_status', 32)->nullable();
        $table->string('to_status', 32);
        $table->string('changed_by_type', 32);
        $table->unsignedBigInteger('changed_by_id')->nullable();
        $table->text('reason')->nullable();
        $table->json('metadata')->nullable();
        $table->timestamp('created_at')->useCurrent();
    });

    Schema::create('partner_package_assignments', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('partner_integration_id');
        $table->string('package_code', 64);
        $table->unsignedBigInteger('plan_id')->nullable();
        $table->string('status', 32)->default('active');
        $table->timestamp('effective_from')->nullable();
        $table->timestamp('effective_to')->nullable();
        $table->unsignedBigInteger('changed_by')->nullable();
        $table->string('reason', 255)->nullable();
        $table->json('metadata')->nullable();
        $table->timestamps();
    });

    Schema::create('partner_support_presets', function (Blueprint $table): void {
        $table->id();
        $table->string('code', 64);
        $table->string('partner_code', 32);
        $table->string('name', 255);
        $table->text('description')->nullable();
        $table->unsignedBigInteger('category_id')->nullable();
        $table->unsignedBigInteger('type_id')->nullable();
        $table->string('default_subject', 255)->nullable();
        $table->text('message_template')->nullable();
        $table->json('required_fields')->nullable();
        $table->json('allowed_package_codes')->nullable();
        $table->unsignedInteger('sla_hours')->nullable();
        $table->unsignedInteger('sort_order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->unique(['partner_code', 'code']);
    });

    Schema::create('partner_support_ticket_contexts', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('support_ticket_id')->unique();
        $table->unsignedBigInteger('partner_integration_id');
        $table->string('external_business_id', 128);
        $table->string('request_code', 64)->nullable();
        $table->string('package_code', 64)->nullable();
        $table->string('related_resource_type', 64)->nullable();
        $table->string('related_resource_id', 128)->nullable();
        $table->json('context')->nullable();
        $table->timestamps();
    });

    Schema::create('partner_webhook_outbox', function (Blueprint $table): void {
        $table->id();
        $table->string('partner_code', 32);
        $table->string('event_type', 64);
        $table->string('dedupe_key', 191);
        $table->string('endpoint_path', 255);
        $table->json('payload');
        $table->string('status', 32)->default('pending');
        $table->unsignedTinyInteger('attempts')->default(0);
        $table->timestamp('available_at')->nullable();
        $table->timestamp('delivered_at')->nullable();
        $table->text('last_error')->nullable();
        $table->string('signature_hash', 128)->nullable();
        $table->timestamps();
        $table->unique(['partner_code', 'dedupe_key']);
    });

    Schema::create('partner_support_attachments', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('support_ticket_id');
        $table->string('id_secure', 40)->unique();
        $table->string('original_name', 255);
        $table->string('mime_type', 120)->nullable();
        $table->unsignedBigInteger('size_bytes')->default(0);
        $table->string('disk', 40)->default('local');
        $table->string('path', 500);
        $table->unsignedBigInteger('uploaded_by_user_id')->nullable();
        $table->timestamps();
    });
}

function dropFizaHubPartnerTables(): void
{
    Schema::dropIfExists('partner_support_attachments');
    Schema::dropIfExists('partner_webhook_outbox');
    Schema::dropIfExists('partner_support_ticket_contexts');
    Schema::dropIfExists('partner_support_presets');
    Schema::dropIfExists('partner_package_assignments');
    Schema::dropIfExists('partner_onboarding_status_histories');
    Schema::dropIfExists('partner_one_time_logins');
    Schema::dropIfExists('partner_api_logs');
    Schema::dropIfExists('partner_onboarding_requests');
    Schema::dropIfExists('partner_integrations');
}
