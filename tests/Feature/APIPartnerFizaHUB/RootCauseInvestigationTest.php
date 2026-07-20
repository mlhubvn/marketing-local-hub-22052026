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

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\APIPartnerFizaHUB\Services\PartnerMappingService;
use Modules\AdminUser\Models\User;

require_once __DIR__.'/FizaHubTestHelpers.php';

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

/**
 * Headers from the SECOND production incident (a different X-Request-Id than the
 * already-fixed missing-plan bug above), reported with the same public demo payload.
 */
function secondIncidentOnboardingHeaders(): array
{
    return [
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => 'fef28d55-a74f-4e43-ae96-c172334eb20e',
        'Idempotency-Key' => 'e3383f39-e53b-4690-a763-5c8d3808067d',
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

test('ROOT CAUSE: onboarding must not 500 when the deterministic partner username already belongs to an orphaned user (partner_integrations mapping missing/deleted)', function (): void {
    // EVIDENCE: PartnerMappingService::deterministicUsername() derives `users.username`
    // purely from external_business_id, and `users.username` has a UNIQUE index
    // (users_username_unique). detectDuplicates() only guards against a duplicate
    // *email* (falls back to a provisional email) — it never checks/avoids a username
    // collision. `fh-biz-demo-001` is the public demo external_business_id printed in
    // the Postman collection defaults, README and /api-fizahub/help-test, so it has
    // plausibly been submitted successfully before by another party. If the resulting
    // partner_integrations row was later removed (manual cleanup / demo reset) while the
    // provisioned `users` row (username `fizahub_<hash>`) was left behind, the next
    // onboarding attempt for the same external_business_id re-enters provision() (no
    // active mapping is found) and calls User::create() with the SAME deterministic
    // username -> unique constraint violation -> uncaught QueryException -> generic
    // HTTP 500 partner_api_error. This matches production request_id
    // fef28d55-a74f-4e43-ae96-c172334eb20e.
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

    $orphanedUsername = app(PartnerMappingService::class)->deterministicUsername('fh-biz-demo-001');

    $orphanedUser = User::query()->create([
        'name' => 'Orphaned FizaHUB Demo User',
        'username' => $orphanedUsername,
        'email' => 'orphaned-leftover@example.com',
        'timezone' => 'Asia/Ho_Chi_Minh',
        'locale' => 'vi',
        'password' => 'irrelevant-password',
    ]);

    // Sanity check: no partner_integrations row maps this external_business_id anymore
    // (e.g. it was deleted), which is exactly what forces OnboardingService::upsert()
    // back into the provision() branch instead of updateExistingMapping().
    expect(\Modules\APIPartnerFizaHUB\Models\PartnerIntegration::query()
        ->where('external_business_id', 'fh-biz-demo-001')
        ->exists())->toBeFalse();

    $response = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        productionOnboardingPayload(),
        secondIncidentOnboardingHeaders()
    );

    // FIX: must provision successfully (with a non-colliding username) and flag the
    // account for manual review instead of crashing. needs_review is intentionally
    // reported as 202 Accepted (OnboardingController::httpStatus) — accepted, but a
    // human must look at it before it's fully "created".
    $response->assertStatus(202)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'needs_review')
        ->assertHeader('X-Request-Id', 'fef28d55-a74f-4e43-ae96-c172334eb20e');

    $newUser = User::query()->where('id', $response->json('data.mlhub_user_id'))->first();
    expect($newUser)->not->toBeNull()
        ->and($newUser->username)->not->toBe($orphanedUsername)
        ->and($newUser->id)->not->toBe($orphanedUser->id);

    $duplicateCheck = (array) $response->json('data.duplicate_check');
    expect(collect($duplicateCheck)->contains(fn (array $row): bool => ($row['type'] ?? '') === 'username'))->toBeTrue();
});
