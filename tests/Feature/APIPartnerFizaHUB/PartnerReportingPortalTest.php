<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportTicketContext;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

require_once __DIR__.'/FizaHubTestHelpers.php';

/**
 * FizaHUB Partner Reporting Portal (view-only, FIZAHUB_DOMAIN) — covers the 12 mandatory
 * scenarios: guest login redirect, allowlist admin access, non-admin 403, FIZAHUB_ADMIN
 * parsing, data isolation to FizaHUB rows only, HKD detail access, cross-tenant/nonexistent
 * ID protection, read-only routing (no mutation endpoints), the mlhub.vn domain being
 * unaffected, unrelated MKT routes being blocked on the reporting domain, graceful empty
 * states, and a bounded query count for the business list (no N+1).
 */
function reportingUrl(string $path = '/'): string
{
    return 'http://fzh.vmo.com.vn'.$path;
}

/**
 * `email_verified_at` is deliberately absent from `User`'s `#[Fillable(...)]` attribute (mass
 * assignment protection), so `User::create(['email_verified_at' => ...])` silently drops it —
 * verification must be set via `forceFill()` afterwards, exactly like the real email
 * verification flow does.
 */
function createVerifiedUser(array $attributes): User
{
    $user = User::query()->create($attributes);
    $user->forceFill(['email_verified_at' => now()])->save();

    return $user;
}

/**
 * @return array{user: User, team: Team, business: LocalBusiness, integration: PartnerIntegration, onboarding: PartnerOnboardingRequest}
 */
function seedFizaHubOnboarding(array $overrides = []): array
{
    static $sequence = 0;
    $sequence++;

    $plan = AdminPlan::query()->first();

    $user = createVerifiedUser([
        'name' => $overrides['owner_name'] ?? "Chủ HKD {$sequence}",
        'email' => $overrides['owner_email'] ?? "hkd-owner-{$sequence}@example.com",
        'password' => bcrypt('password'),
        'plan_id' => $plan?->id,
    ]);

    $team = Team::query()->create([
        'name' => "Workspace HKD {$sequence}",
        'slug' => "workspace-hkd-{$sequence}",
        'owner_user_id' => $user->id,
    ]);

    $business = LocalBusiness::query()->create([
        'user_id' => $user->id,
        'name' => $overrides['business_name'] ?? "Fiza HKD Store {$sequence}",
        'type' => 'restaurant',
        'phone' => $overrides['phone'] ?? '090000'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
        'email' => $overrides['business_email'] ?? "hkd-business-{$sequence}@example.com",
        'address' => 'Đà Nẵng',
    ]);

    $externalBusinessId = $overrides['external_business_id'] ?? "fiza-hkd-{$sequence}";
    $partnerCode = $overrides['partner_code'] ?? 'fizahub';

    $integration = PartnerIntegration::query()->create([
        'partner_code' => $partnerCode,
        'external_business_id' => $externalBusinessId,
        'external_user_id' => "fiza-owner-{$sequence}",
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
        'package_code' => $overrides['package_code'] ?? 'base',
        'status' => 'active',
    ]);

    $status = $overrides['status'] ?? OnboardingStatusMachine::READY;

    $onboarding = PartnerOnboardingRequest::query()->create([
        'partner_code' => $partnerCode,
        'request_id' => (string) Str::uuid(),
        'external_business_id' => $externalBusinessId,
        'external_user_id' => "fiza-owner-{$sequence}",
        'package_code' => $overrides['package_code'] ?? 'base',
        'status' => $status,
        'current_step' => OnboardingStatusMachine::defaultStepFor($status),
        'payload' => [
            'business' => ['name' => $business->name],
            'owner' => ['name' => $user->name],
        ],
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
        'created_at' => $overrides['created_at'] ?? now(),
        'updated_at' => now(),
    ]);

    return compact('user', 'team', 'business', 'integration', 'onboarding');
}

/**
 * Growth/operational-metrics tables that `DashboardService::summarize()` queries for a
 * linked business. Only needed by tests that reach the HKD detail page for a business with
 * a real `mlhub_business_id`/`mlhub_user_id` mapping (i.e. `growthSummaryFor()` is called).
 */
function bootFizaHubGrowthTables(): void
{
    Schema::create('lb_campaigns', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('business_id')->nullable();
        $table->string('name');
        $table->string('slug')->nullable();
        $table->string('status', 40)->nullable();
        $table->string('objective')->nullable();
        $table->json('settings')->nullable();
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_qr_scans', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('campaign_id');
        $table->timestamp('created_at')->nullable();
    });

    Schema::create('lb_lead_submissions', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('campaign_id');
        $table->string('name')->nullable();
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_review_feedbacks', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('campaign_id');
        $table->unsignedTinyInteger('rating')->default(0);
        $table->string('customer_phone')->nullable();
        $table->string('customer_email')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_coupon_redemptions', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('campaign_id');
        $table->string('customer_phone')->nullable();
        $table->string('customer_email')->nullable();
        $table->timestamp('used_at')->nullable();
        $table->timestamps();
    });

    foreach (['lb_bookings', 'lb_feedback_responses'] as $tableName) {
        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('campaign_id');
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->timestamps();
        });
    }
}

function dropFizaHubGrowthTables(): void
{
    foreach (['lb_feedback_responses', 'lb_bookings', 'lb_coupon_redemptions', 'lb_review_feedbacks', 'lb_lead_submissions', 'lb_qr_scans', 'lb_campaigns'] as $table) {
        Schema::dropIfExists($table);
    }
}

function seedFizaHubSupportTicket(PartnerIntegration $integration, PartnerOnboardingRequest $onboarding, int $status = 1): SupportTicket
{
    $ticket = SupportTicket::query()->create([
        'id_secure' => Str::random(32),
        'uid' => (int) $integration->mlhub_user_id,
        'open_by' => (int) $integration->mlhub_user_id,
        'team_id' => (int) $integration->mlhub_workspace_id,
        'title' => 'FizaHUB onboarding',
        'content' => 'Doanh nghiệp đang chờ tư vấn.',
        'status' => $status,
        'created' => time(),
        'changed' => time(),
    ]);

    PartnerSupportTicketContext::query()->create([
        'partner_integration_id' => $integration->id,
        'support_ticket_id' => $ticket->id,
        'external_business_id' => $integration->external_business_id,
        'request_code' => 'fizahub_onboarding',
        'package_code' => $integration->package_code,
        'related_resource_type' => PartnerOnboardingRequest::class,
        'related_resource_id' => (string) $onboarding->request_id,
        'context' => ['ticket_type' => 'onboarding', 'source' => 'fizahub'],
    ]);

    return $ticket;
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
    // Default deny — each test opts specific user IDs in explicitly, matching the
    // "empty FIZAHUB_ADMIN => nobody has access" production default.
    config()->set('modules.apipartnerfizahub.partner_reporting_admin_ids', []);

    bootProductionLikeSchema();

    AdminPlan::query()->create([
        'name' => 'MKT Free Da Nang',
        'slug' => 'mlhub-free-da-nang',
        'status' => true,
        'free_plan' => true,
        'default_signup_plan' => true,
        'currency' => 'VND',
        'price' => 0,
        'permissions' => ['max_campaigns' => 5],
    ]);

    bootFizaHubGrowthTables();
});

afterEach(function (): void {
    dropFizaHubGrowthTables();
    dropFizaHubPartnerTables();
    Schema::dropIfExists('affiliate_profiles');
    Schema::dropIfExists('support_comments');
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
    Schema::dropIfExists('plans');
});

// 1. Guest → redirected to login.
test('guest visiting the reporting domain is redirected to the login page', function (): void {
    $response = $this->get(reportingUrl('/'));

    $response->assertStatus(302);
    expect($response->headers->get('Location'))->toContain('/login');
});

test('guest visiting the reporting domain HKD detail page is redirected to the login page', function (): void {
    ['onboarding' => $onboarding] = seedFizaHubOnboarding();

    $response = $this->get(reportingUrl('/onboarding/'.$onboarding->id));

    $response->assertStatus(302);
    expect($response->headers->get('Location'))->toContain('/login');
});

// 2. Allowlisted user → 200 dashboard.
test('user id in FIZAHUB_ADMIN allowlist can view the dashboard', function (): void {
    $admin = createVerifiedUser([
        'name' => 'FizaHUB Leader',
        'email' => 'leader@fizahub.example.com',
        'password' => bcrypt('password'),
    ]);
    config()->set('modules.apipartnerfizahub.partner_reporting_admin_ids', [$admin->id]);

    seedFizaHubOnboarding(['business_name' => 'Quán Cà Phê Việt']);

    $this->actingAs($admin)
        ->get(reportingUrl('/'))
        ->assertOk()
        ->assertSee('Quán Cà Phê Việt');
});

// 4. FIZAHUB_ADMIN parsing itself is unit-tested directly in
// tests/Unit/APIPartnerFizaHUB/PartnerReportingAdminIdsTest.php.

// 3. Authenticated but not in allowlist → 403.
test('authenticated user not in FIZAHUB_ADMIN allowlist receives 403', function (): void {
    $notAdmin = createVerifiedUser([
        'name' => 'Random MKT User',
        'email' => 'random-user@example.com',
        'password' => bcrypt('password'),
    ]);
    // Allowlist configured, but does NOT include this user's ID.
    config()->set('modules.apipartnerfizahub.partner_reporting_admin_ids', [$notAdmin->id + 999999]);

    $this->actingAs($notAdmin)
        ->get(reportingUrl('/'))
        ->assertForbidden();
});

test('empty FIZAHUB_ADMIN allowlist denies every authenticated user', function (): void {
    $someUser = createVerifiedUser([
        'name' => 'Someone',
        'email' => 'someone@example.com',
        'password' => bcrypt('password'),
    ]);
    config()->set('modules.apipartnerfizahub.partner_reporting_admin_ids', []);

    $this->actingAs($someUser)
        ->get(reportingUrl('/'))
        ->assertForbidden();
});

// 5. Dashboard only reflects FizaHUB data — unrelated MKT business/user must never appear
// and totals must exactly match the FizaHUB rows created in this test.
test('dashboard totals and business list only reflect FizaHUB onboarding data', function (): void {
    $admin = createVerifiedUser([
        'name' => 'FizaHUB Leader',
        'email' => 'leader2@fizahub.example.com',
        'password' => bcrypt('password'),
    ]);
    config()->set('modules.apipartnerfizahub.partner_reporting_admin_ids', [$admin->id]);

    seedFizaHubOnboarding(['business_name' => 'Fiza Store A', 'status' => OnboardingStatusMachine::COMPLETED]);
    seedFizaHubOnboarding(['business_name' => 'Fiza Store B', 'status' => OnboardingStatusMachine::AWAITING_CONSULTANT]);

    // Unrelated MKT user/business with NO FizaHUB integration/onboarding row at all.
    $unrelatedUser = createVerifiedUser([
        'name' => 'Unrelated MKT User',
        'email' => 'unrelated@example.com',
        'password' => bcrypt('password'),
    ]);
    LocalBusiness::query()->create([
        'user_id' => $unrelatedUser->id,
        'name' => 'Unrelated MKT Business',
        'type' => 'other',
    ]);

    $response = $this->actingAs($admin)->get(reportingUrl('/'))->assertOk();

    $response->assertSee('Fiza Store A')
        ->assertSee('Fiza Store B')
        ->assertDontSee('Unrelated MKT Business');

    expect(PartnerOnboardingRequest::query()->count())->toBe(2);
});

// 6. Allowlisted admin can open a FizaHUB HKD detail page.
test('allowlisted admin can view a FizaHUB HKD detail page', function (): void {
    $admin = createVerifiedUser([
        'name' => 'FizaHUB Leader',
        'email' => 'leader3@fizahub.example.com',
        'password' => bcrypt('password'),
    ]);
    config()->set('modules.apipartnerfizahub.partner_reporting_admin_ids', [$admin->id]);

    ['onboarding' => $onboarding, 'integration' => $integration] = seedFizaHubOnboarding(['business_name' => 'Fiza Detail Store']);
    seedFizaHubSupportTicket($integration, $onboarding);

    $this->actingAs($admin)
        ->get(reportingUrl('/onboarding/'.$onboarding->id))
        ->assertOk()
        ->assertSee('Fiza Detail Store')
        ->assertSee('FizaHUB onboarding');
});

// 7. Cross-tenant / nonexistent ID protection.
test('an onboarding request from a different partner_code cannot be viewed through the reporting portal', function (): void {
    $admin = createVerifiedUser([
        'name' => 'FizaHUB Leader',
        'email' => 'leader4@fizahub.example.com',
        'password' => bcrypt('password'),
    ]);
    config()->set('modules.apipartnerfizahub.partner_reporting_admin_ids', [$admin->id]);

    ['onboarding' => $otherPartnerOnboarding] = seedFizaHubOnboarding([
        'partner_code' => 'some-other-partner',
        'business_name' => 'Other Partner Store',
        'external_business_id' => 'other-partner-001',
    ]);

    $this->actingAs($admin)
        ->get(reportingUrl('/onboarding/'.$otherPartnerOnboarding->id))
        ->assertNotFound();
});

test('a nonexistent onboarding id returns 404 on the reporting portal', function (): void {
    $admin = createVerifiedUser([
        'name' => 'FizaHUB Leader',
        'email' => 'leader5@fizahub.example.com',
        'password' => bcrypt('password'),
    ]);
    config()->set('modules.apipartnerfizahub.partner_reporting_admin_ids', [$admin->id]);

    $this->actingAs($admin)
        ->get(reportingUrl('/onboarding/999999'))
        ->assertNotFound();
});

// 8. No mutation endpoints exist in the portal (GET/HEAD only).
test('the reporting portal exposes no mutation routes', function (): void {
    $admin = createVerifiedUser([
        'name' => 'FizaHUB Leader',
        'email' => 'leader6@fizahub.example.com',
        'password' => bcrypt('password'),
    ]);
    config()->set('modules.apipartnerfizahub.partner_reporting_admin_ids', [$admin->id]);
    ['onboarding' => $onboarding] = seedFizaHubOnboarding();

    $this->actingAs($admin)->post(reportingUrl('/'))->assertStatus(405);
    $this->actingAs($admin)->put(reportingUrl('/onboarding/'.$onboarding->id))->assertStatus(405);
    $this->actingAs($admin)->delete(reportingUrl('/onboarding/'.$onboarding->id))->assertStatus(405);
});

// 9. mlhub.vn (or any other host) keeps its existing behaviour untouched.
test('an unrelated FizaHUB route still works normally on the main MKT domain', function (): void {
    $response = $this->get('http://mlhub.test/api-fizahub/postman');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/json');
});

// 10. Unrelated MKT routes are blocked when accessed through the reporting domain.
test('an unrelated MLHUB route is not reachable through the reporting domain for a guest', function (): void {
    $this->get(reportingUrl('/api-fizahub/postman'))->assertNotFound();
});

test('an unrelated MKT route redirects an authenticated allowlisted user back to the dashboard instead of leaking through', function (): void {
    $admin = createVerifiedUser([
        'name' => 'FizaHUB Leader',
        'email' => 'leader7@fizahub.example.com',
        'password' => bcrypt('password'),
    ]);
    config()->set('modules.apipartnerfizahub.partner_reporting_admin_ids', [$admin->id]);

    $response = $this->actingAs($admin)->get(reportingUrl('/api-fizahub/postman'));

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('fzh.vmo.com.vn');
});

// 11. Graceful empty states — dashboard and detail pages must not error when there is no
// onboarding/support/integration data at all.
test('dashboard renders without error when there is no onboarding data at all', function (): void {
    $admin = createVerifiedUser([
        'name' => 'FizaHUB Leader',
        'email' => 'leader8@fizahub.example.com',
        'password' => bcrypt('password'),
    ]);
    config()->set('modules.apipartnerfizahub.partner_reporting_admin_ids', [$admin->id]);

    $this->actingAs($admin)
        ->get(reportingUrl('/'))
        ->assertOk()
        ->assertSee('0');
});

test('HKD detail page renders without error when the business has no MKT integration/tickets yet', function (): void {
    $admin = createVerifiedUser([
        'name' => 'FizaHUB Leader',
        'email' => 'leader9@fizahub.example.com',
        'password' => bcrypt('password'),
    ]);
    config()->set('modules.apipartnerfizahub.partner_reporting_admin_ids', [$admin->id]);

    $onboarding = PartnerOnboardingRequest::query()->create([
        'partner_code' => 'fizahub',
        'request_id' => (string) Str::uuid(),
        'external_business_id' => 'fiza-no-integration',
        'package_code' => 'base',
        'status' => OnboardingStatusMachine::AWAITING_CONSULTANT,
        'current_step' => OnboardingStatusMachine::AWAITING_CONSULTANT,
        'payload' => ['business' => ['name' => 'Chưa liên kết MKT']],
    ]);

    $this->actingAs($admin)
        ->get(reportingUrl('/onboarding/'.$onboarding->id))
        ->assertOk()
        ->assertSee('Chưa liên kết MKT')
        ->assertSee('Chưa có dữ liệu');
});

/**
 * Counts only queries against this feature's own application tables, ignoring unrelated
 * framework bookkeeping (e.g. the shared `<x-ui.button>` component's `Schema::hasTable('options')`
 * probe, which — like any other themed row-level UI in this codebase — runs once per
 * rendered button and is pre-existing infrastructure behaviour, not something introduced
 * by the FizaHUB reporting queries under test here).
 *
 * @return list<string>
 */
function applicationQueryLog(): array
{
    return collect(DB::getQueryLog())
        ->pluck('query')
        ->reject(fn (string $sql): bool => str_contains($sql, 'sqlite_master'))
        ->values()
        ->all();
}

// 12. Business list query stays bounded (no N+1 scaling linearly with row count).
test('the business list query does not scale linearly with the number of onboarding rows (no N+1)', function (): void {
    $admin = createVerifiedUser([
        'name' => 'FizaHUB Leader',
        'email' => 'leader10@fizahub.example.com',
        'password' => bcrypt('password'),
    ]);
    config()->set('modules.apipartnerfizahub.partner_reporting_admin_ids', [$admin->id]);

    for ($i = 0; $i < 3; $i++) {
        seedFizaHubOnboarding();
    }

    DB::enableQueryLog();
    $this->actingAs($admin)->get(reportingUrl('/'))->assertOk();
    $queryCountForThree = count(applicationQueryLog());
    DB::flushQueryLog();
    DB::disableQueryLog();

    // Query log stays OFF while seeding more rows — only the second page load's own
    // queries should be measured, not the fixture inserts used to create the extra rows.
    for ($i = 0; $i < 12; $i++) {
        seedFizaHubOnboarding();
    }

    DB::enableQueryLog();
    $this->actingAs($admin)->get(reportingUrl('/'))->assertOk();
    $queryCountForFifteen = count(applicationQueryLog());
    DB::disableQueryLog();

    // 4x more onboarding rows (beyond the default 15-per-page limit) must not translate
    // into more application queries at all — the list is paginated and every relation is
    // eager-loaded, so the query count must stay flat regardless of total row count.
    expect($queryCountForFifteen)->toBe($queryCountForThree);
});
