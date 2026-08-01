<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

    $crmLoginMigration = require base_path('modules/APIPartnerFizaHUB/Database/Migrations/2026_07_20_000000_add_crm_login_idempotency_to_partner_one_time_logins.php');
    $crmLoginMigration->up();
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

/**
 * Minimal schema so that `GET /health` (now a readiness probe — database, partner_schema,
 * default_plan, support_tables) reports 200 ok instead of 503 degraded. Used by tests that
 * only care about header/auth/rate-limit/logging behaviour, not readiness itself.
 */
function bootFizaHubReadinessSchema(): void
{
    dropFizaHubReadinessSchema();

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->timestamps();
    });

    Schema::create('teams', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->unsignedBigInteger('owner_user_id')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_businesses', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->timestamps();
    });

    Schema::create('support_tickets', function (Blueprint $table): void {
        $table->id();
        $table->string('subject')->nullable();
        $table->timestamps();
    });

    Schema::create('support_comments', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('support_ticket_id')->nullable();
        $table->text('comment')->nullable();
        $table->timestamps();
    });

    Schema::create('plans', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('slug')->unique();
        $table->boolean('status')->default(true);
        $table->timestamps();
    });

    DB::table('plans')->insert([
        'name' => 'MKT Free Da Nang',
        'slug' => 'mlhub-free-da-nang',
        'status' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    createFizaHubPartnerTables();
}

function dropFizaHubReadinessSchema(): void
{
    dropFizaHubPartnerTables();
    Schema::dropIfExists('plans');
    Schema::dropIfExists('support_comments');
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
}

/**
 * Boots the FizaHUB partner schema by executing the ACTUAL module migrations
 * (2026_07_13_000000_create_fizahub_partner_api_tables +
 *  2026_07_17_120000_extend_fizahub_partner_onboarding_tables) so a migration-level
 * bug cannot hide behind a hand-rolled test schema. Used by tests that need to prove
 * behaviour against the real, deployed schema rather than a simplified stand-in.
 */
function runRealFizaHubMigrations(): void
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

    $create = require base_path('modules/APIPartnerFizaHUB/Database/Migrations/2026_07_13_000000_create_fizahub_partner_api_tables.php');
    $create->up();

    $extend = require base_path('modules/APIPartnerFizaHUB/Database/Migrations/2026_07_17_120000_extend_fizahub_partner_onboarding_tables.php');
    $extend->up();

    $crmLogin = require base_path('modules/APIPartnerFizaHUB/Database/Migrations/2026_07_20_000000_add_crm_login_idempotency_to_partner_one_time_logins.php');
    $crmLogin->up();
}

/**
 * Production-shaped host schema (users/teams/lb_businesses/support_tickets/plans/
 * affiliate_profiles) plus the real FizaHUB migrations on top, for tests that need to
 * exercise the module against the exact schema deployed to mlhub.vn.
 */
function bootProductionLikeSchema(): void
{
    Schema::dropIfExists('affiliate_profiles');
    Schema::dropIfExists('support_comments');
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
    Schema::dropIfExists('plans');

    Schema::create('plans', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->boolean('status')->default(true);
        $table->boolean('featured')->default(false);
        $table->string('currency')->default('VND');
        $table->decimal('price', 16, 2)->default(0);
        $table->unsignedTinyInteger('type')->default(1);
        $table->boolean('free_plan')->default(false);
        $table->boolean('default_signup_plan')->default(false);
        $table->unsignedInteger('trial_day')->default(0);
        $table->integer('position')->default(0);
        $table->text('desc')->nullable();
        $table->json('permissions')->nullable();
        $table->timestamps();
    });

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('username')->nullable()->unique();
        $table->string('email')->unique();
        $table->string('locale', 10)->nullable();
        $table->string('timezone', 100)->nullable();
        $table->unsignedBigInteger('plan_id')->nullable();
        $table->unsignedBigInteger('next_plan_id')->nullable();
        $table->timestamp('plan_started_at')->nullable();
        $table->timestamp('plan_expires_at')->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('referral_code', 20)->nullable();
        $table->unsignedBigInteger('referred_by_user_id')->nullable();
        $table->boolean('is_super_admin')->default(false);
        $table->timestamps();
    });

    Schema::create('teams', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('slug');
        $table->text('description')->nullable();
        $table->unsignedBigInteger('owner_user_id')->nullable();
        $table->json('enabled_modules')->nullable();
        $table->timestamps();
    });

    Schema::create('team_user', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id');
        $table->unsignedBigInteger('user_id');
        $table->string('role', 50)->default('member');
        $table->json('permissions')->nullable();
        $table->json('managed_account_ids')->nullable();
        $table->timestamps();
        $table->unique(['team_id', 'user_id']);
    });

    Schema::create('lb_businesses', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('name');
        $table->string('type', 60)->default('other');
        $table->string('industry_group_code', 64)->nullable();
        $table->string('industry_category_code', 80)->nullable();
        $table->json('industry_metadata')->nullable();
        $table->string('taxonomy_version', 20)->nullable();
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->string('website')->nullable();
        $table->text('address')->nullable();
        $table->timestamps();
    });

    Schema::create('support_tickets', function (Blueprint $table): void {
        $table->id();
        $table->string('id_secure', 40)->unique();
        $table->unsignedBigInteger('uid');
        $table->unsignedBigInteger('open_by');
        $table->unsignedBigInteger('team_id')->nullable();
        $table->unsignedBigInteger('cate_id')->nullable();
        $table->unsignedBigInteger('type_id')->nullable();
        $table->string('title', 255);
        $table->text('content');
        $table->unsignedTinyInteger('status')->default(1);
        $table->boolean('pin')->default(false);
        $table->boolean('user_read')->default(false);
        $table->boolean('admin_read')->default(true);
        $table->unsignedInteger('changed')->nullable();
        $table->unsignedInteger('created')->nullable();

        // Matches the REAL production foreign keys (SQLSTATE 23000 / MySQL error 1452
        // reported on mlhub.vn): uid and open_by must reference an existing users.id row.
        // The previous test schema omitted these FKs entirely, which hid the production
        // bug (SupportTicketBridge inserting uid=0/open_by=0) behind a green test suite.
        $table->foreign('uid')->references('id')->on('users');
        $table->foreign('open_by')->references('id')->on('users');
    });

    Schema::create('support_comments', function (Blueprint $table): void {
        $table->id();
        $table->string('id_secure', 40)->unique();
        $table->unsignedBigInteger('ticket_id');
        $table->unsignedBigInteger('user_id');
        $table->text('comment');
        $table->unsignedInteger('changed')->nullable();
        $table->unsignedInteger('created')->nullable();
    });

    Schema::create('affiliate_profiles', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id')->unique();
        $table->unsignedInteger('clicks')->default(0);
        $table->unsignedInteger('conversions')->default(0);
        $table->decimal('total_approved', 12, 2)->default(0);
        $table->decimal('total_withdrawal', 12, 2)->default(0);
        $table->decimal('total_balance', 12, 2)->default(0);
        $table->timestamps();
    });

    runRealFizaHubMigrations();
}
