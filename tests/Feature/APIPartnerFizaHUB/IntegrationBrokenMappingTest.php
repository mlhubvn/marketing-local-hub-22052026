<?php

/**
 * partner_integrations.mlhub_user_id / mlhub_workspace_id / mlhub_business_id are all
 * nullOnDelete. So an admin manually deleting a demo/test users/lb_businesses row
 * (without also deleting the partner_integrations mapping) leaves a "broken" row with
 * the SAME (partner_code, external_business_id) still occupying the unique index and
 * all FKs nulled.
 *
 * Before this fix, re-onboarding the same external_business_id fell into
 * OnboardingService::provision() (since the null FKs skip the updateExistingMapping()
 * branch) and called PartnerIntegration::create() — colliding with the leftover broken
 * row's unique index and surfacing as an uncaught 500 partner_api_error. provision()
 * now uses updateOrCreate() so it self-heals the broken row instead of crashing.
 *
 * Separately, if a partner_integrations row ever points at a genuinely non-existent
 * mlhub_user_id (e.g. manual data-fix gone wrong, not a normal delete — nullOnDelete
 * makes the normal FK case impossible), PackageService::forBusiness() must not surface
 * an uncaught ModelNotFoundException either.
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

require_once __DIR__.'/FizaHubTestHelpers.php';

function brokenMappingHeaders(): array
{
    return [
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) str()->uuid(),
        'Idempotency-Key' => (string) str()->uuid(),
        'Accept' => 'application/json',
    ];
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);
    bootProductionLikeSchema();

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

function makeDanglingIntegration(string $externalBusinessId): PartnerIntegration
{
    $user = User::query()->create([
        'name' => 'Soon Deleted Owner',
        'username' => 'dangling_'.Str::lower(Str::random(8)),
        'email' => 'dangling-'.Str::lower(Str::random(6)).'@example.com',
        'password' => 'password-password-password-password-password-1234',
    ]);

    $team = Team::query()->create([
        'name' => 'Dangling Team',
        'slug' => 'dangling-team-'.Str::lower(Str::random(6)),
        'owner_user_id' => $user->id,
    ]);

    $business = LocalBusiness::query()->create([
        'user_id' => $user->id,
        'name' => 'Dangling Business',
        'type' => 'other',
    ]);

    $integration = PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => $externalBusinessId,
        'external_user_id' => 'ext-'.$externalBusinessId,
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
        'package_code' => 'free',
        'status' => 'active',
    ]);

    // Simulate an admin deleting the demo account/business without cleaning up the
    // partner_integrations mapping. mlhub_user_id/mlhub_workspace_id/mlhub_business_id
    // are all nullOnDelete, so the row survives with its FKs nulled — it is NOT
    // cascade-deleted.
    $business->delete();
    $user->delete();

    return $integration->fresh();
}

test('re-onboarding a broken mapping returns a typed conflict without provisioning replacements', function (): void {
    $broken = makeDanglingIntegration('fh-biz-dangling-001');

    expect($broken->mlhub_user_id)->toBeNull()
        ->and($broken->mlhub_business_id)->toBeNull();

    $response = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        [
            'external_business_id' => 'fh-biz-dangling-001',
            'external_user_id' => 'fh-user-dangling-001',
            'package_code' => 'base',
            'marketing_goal_codes' => ['local_presence', 'qr_checkin'],
            'owner' => [
                'name' => 'Nguyen Van A',
                'phone' => '0901 234 567',
                'email' => 'nguyenvana+dangling@example.com',
            ],
            'business' => [
                'name' => 'Dangling Retry Store',
                'industry' => 'restaurant_food',
                'phone' => '0901 234 567',
                'email' => 'dangling-store@example.com',
                'address' => '1 Le Duan, Da Nang',
                'tax_code' => '0101 999',
                'business_license_number' => 'GPKD 999',
            ],
            'verification' => [
                'identity_verified' => true,
                'verified_at' => now()->toIso8601String(),
                'verified_by' => 'fizahub',
            ],
        ],
        brokenMappingHeaders()
    );

    $response->assertConflict()
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'integration_broken')
        ->assertJsonPath('error.details.next_action', 'contact_support');

    $unchanged = PartnerIntegration::query()->where('external_business_id', 'fh-biz-dangling-001')->first();
    expect($unchanged)->not->toBeNull()
        ->and($unchanged->id)->toBe($broken->id)
        ->and($unchanged->mlhub_user_id)->toBeNull()
        ->and($unchanged->mlhub_business_id)->toBeNull()
        ->and(User::query()->where('email', 'nguyenvana+dangling@example.com')->exists())->toBeFalse()
        ->and(LocalBusiness::query()->where('name', 'Dangling Retry Store')->exists())->toBeFalse();

    expect(PartnerIntegration::query()->where('external_business_id', 'fh-biz-dangling-001')->count())->toBe(1);
});

test('ROOT CAUSE: package lookup for a mapping pointing at a non-existent user returns a typed 409, not a 500', function (): void {
    $integration = makeDanglingIntegration('fh-biz-dangling-002');

    // Simulate a mapping left pointing at a truly non-existent user id (e.g. a bad manual
    // data fix) — nullOnDelete makes this impossible via a normal delete, but the id may
    // still be stale/wrong, so PackageService must not trust it blindly. FK checks are
    // toggled off only to force this otherwise-impossible-via-normal-delete state.
    DB::statement('PRAGMA foreign_keys=OFF');
    $integration->forceFill(['mlhub_user_id' => 999999, 'mlhub_workspace_id' => 999999])->save();
    DB::statement('PRAGMA foreign_keys=ON');

    $response = $this->getJson(
        '/api/v1/partners/fizahub/businesses/fh-biz-dangling-002/package',
        brokenMappingHeaders()
    );

    $response->assertStatus(409)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'integration_broken')
        ->assertJsonPath('error.details.next_action', 'contact_support');

    $message = (string) $response->json('error.message');
    expect($message)->not->toContain('SQLSTATE')
        ->and($message)->not->toContain('ModelNotFoundException');
});
