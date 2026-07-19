<?php

/**
 * Root-cause + permanent regression coverage for the production onboarding 500
 * (request_id 05dcc2bc-0401-45c1-b506-de2a85068bd7).
 *
 * Unlike FizaHubTestHelpers::createFizaHubPartnerTables(), this file boots the
 * schema by executing the ACTUAL module migrations
 * (2026_07_13_000000_create_fizahub_partner_api_tables +
 *  2026_07_17_120000_extend_fizahub_partner_onboarding_tables) so a migration-level
 * bug cannot hide behind a hand-rolled test schema. Keep this file running on real
 * migrations even if the onboarding schema changes again later.
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\AdminPlans\Models\AdminPlan;

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
}

function bootProductionLikeSchema(): void
{
    Schema::dropIfExists('affiliate_profiles');
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

/**
 * Exact production payload from tong-ket-test-postman.txt (request_id 05dcc2bc-...).
 */
function productionOnboardingPayload(): array
{
    return [
        'external_business_id' => 'fh-biz-demo-001',
        'external_user_id' => 'fh-user-demo-001',
        'package_code' => 'base',
        'owner' => [
            'name' => 'Nguyen Van A',
            'phone' => '0901 234 567',
            'email' => 'nguyenvana+demo001@example.com',
        ],
        'business' => [
            'name' => 'Fiza Demo Store - Com Tam Da Nang',
            'industry' => 'restaurant_food',
            'phone' => '0901 234 567',
            'address' => '123 Le Duan, Da Nang',
            'tax_code' => '0101 234 567',
            'business_license_number' => 'GPKD 123',
        ],
        'verification' => [
            'identity_verified' => true,
            'verified_at' => '2026-07-13T10:00:00+07:00',
            'verified_by' => 'fizahub',
        ],
    ];
}

function prodOnboardingHeaders(): array
{
    return [
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => '05dcc2bc-0401-45c1-b506-de2a85068bd7',
        'Idempotency-Key' => (string) str()->uuid(),
        'Accept' => 'application/json',
    ];
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);
    bootProductionLikeSchema();
});

afterEach(function (): void {
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
    Schema::dropIfExists('affiliate_profiles');
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
    Schema::dropIfExists('plans');
});

test('EVIDENCE: real migrations create the exact schema onboarding needs', function (): void {
    expect(Schema::hasTable('partner_onboarding_requests'))->toBeTrue()
        ->and(Schema::hasColumn('partner_onboarding_requests', 'requested_package_code'))->toBeTrue()
        ->and(Schema::hasColumn('partner_onboarding_requests', 'approved_package_code'))->toBeTrue()
        ->and(Schema::hasColumn('partner_onboarding_requests', 'admin_status'))->toBeTrue()
        ->and(Schema::hasTable('partner_package_assignments'))->toBeTrue()
        ->and(Schema::hasTable('partner_onboarding_status_histories'))->toBeTrue()
        ->and(Schema::hasTable('partner_support_ticket_contexts'))->toBeTrue()
        ->and(Schema::hasTable('partner_webhook_outbox'))->toBeTrue()
        ->and(Schema::hasTable('partner_support_attachments'))->toBeTrue();
});

test('EVIDENCE: onboarding succeeds with the production payload when the default plan exists', function (): void {
    AdminPlan::query()->create([
        'name' => 'MLHUB Free Da Nang',
        'slug' => 'mlhub-free-da-nang',
        'status' => true,
        'free_plan' => true,
        'default_signup_plan' => true,
        'currency' => 'VND',
        'price' => 0,
        'permissions' => [],
    ]);

    $response = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        productionOnboardingPayload(),
        prodOnboardingHeaders()
    );

    $response->assertCreated()->assertJsonPath('data.status', 'awaiting_consultant');
});

test('ROOT CAUSE, FIXED: onboarding returns a typed 503 default_plan_not_found (not a generic 500) when the default plan is missing, and the log is correlated to the partner request', function (): void {
    // Deliberately do NOT create the "mlhub-free-da-nang" plan row — this reproduces
    // a production DB where the plan seeder/mlhub:update was never (re-)run after the
    // FizaHUB package_map started depending on this slug. This was the exact root
    // cause of the production 500 on request_id 05dcc2bc-0401-45c1-b506-de2a85068bd7:
    // PartnerMappingService::resolvePlan() returned null and the service threw a bare
    // RuntimeException, which the middleware rendered as a generic partner_api_error.
    expect(AdminPlan::query()->where('slug', 'mlhub-free-da-nang')->exists())->toBeFalse();

    $loggedEntries = [];
    Log::listen(function ($event) use (&$loggedEntries): void {
        $loggedEntries[] = ['level' => $event->level, 'message' => $event->message, 'context' => $event->context];
    });

    $response = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        productionOnboardingPayload(),
        prodOnboardingHeaders()
    );

    // 1) FIX: this is now a typed, actionable 503 instead of an opaque 500 — deploy/config
    // readiness problems must never look like a business-logic bug to the partner.
    $response->assertStatus(503)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'default_plan_not_found')
        ->assertJsonPath('error.details.next_action', 'retry_later')
        ->assertHeader('X-Request-Id', '05dcc2bc-0401-45c1-b506-de2a85068bd7');

    // 2) The client-facing message must stay a safe, localized sentence — never the raw
    // exception message, SQL, or table/column names.
    $message = (string) $response->json('error.message');
    expect($message)->not->toContain('SQLSTATE')
        ->and($message)->not->toContain('RuntimeException')
        ->and($message)->not->toContain('mlhub-free-da-nang');

    // 3) No downstream records were created (matches the cascading 404s FizaHUB saw).
    expect(\Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest::query()->count())->toBe(0)
        ->and(\Modules\APIPartnerFizaHUB\Models\PartnerIntegration::query()->count())->toBe(0);

    // 4) OBSERVABILITY: PartnerApiException is an expected/handled error (not an "unexpected
    // exception"), so it is intentionally NOT sent through logUnexpected/report() — it must
    // not spam error-level logs for a routine, actionable, already-typed condition. What
    // matters for on-call debugging is that the response itself is fully correlated by
    // request_id (asserted above) and carries a stable, greppable error.code.
    $noisyErrorLog = collect($loggedEntries)->first(function (array $entry): bool {
        $haystack = $entry['message'].' '.json_encode($entry['context']);

        return $entry['level'] === 'error' && str_contains($haystack, '05dcc2bc-0401-45c1-b506-de2a85068bd7');
    });

    expect($noisyErrorLog)->toBeNull(
        'default_plan_not_found is a typed, actionable error and should not also be logged as an unexpected error-level exception.'
    );
});
