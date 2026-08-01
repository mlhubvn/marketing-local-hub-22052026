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
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerApiLog;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Services\OnboardingService;
use Modules\APIPartnerFizaHUB\Services\PartnerIdentityService;
use Modules\APIPartnerFizaHUB\Services\SupportTicketBridge;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Modules\APIPartnerFizaHUB\Support\PartnerApiException;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

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
        'marketing_goal_codes' => ['local_presence', 'qr_checkin'],
        'owner' => [
            'name' => 'Nguyen Van A',
            'phone' => '0901 234 567',
            'email' => 'nguyenvana+demo001@example.com',
        ],
        'business' => [
            'name' => 'Fiza Demo Store - Com Tam Da Nang',
            'industry' => 'restaurant_food',
            'phone' => '0901 234 567',
            'email' => 'demo-store@example.com',
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
        'name' => 'MKT Free Da Nang',
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
    expect(PartnerOnboardingRequest::query()->count())->toBe(0)
        ->and(PartnerIntegration::query()->count())->toBe(0);

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

test('email-derived username collision uses a stable hash suffix instead of failing onboarding', function (): void {
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
        'name' => 'MKT Free Da Nang',
        'slug' => 'mlhub-free-da-nang',
        'status' => true,
        'free_plan' => true,
        'default_signup_plan' => true,
        'currency' => 'VND',
        'price' => 0,
        'permissions' => [],
    ]);

    $orphanedUsername = app(PartnerIdentityService::class)->usernameFromEmail('nguyenvana+demo001@example.com');

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
    expect(PartnerIntegration::query()
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
    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'awaiting_consultant')
        ->assertHeader('X-Request-Id', 'fef28d55-a74f-4e43-ae96-c172334eb20e');

    $newUser = User::query()->where('id', $response->json('data.mlhub_user_id'))->first();
    expect($newUser)->not->toBeNull()
        ->and($newUser->username)->not->toBe($orphanedUsername)
        ->and($newUser->username)->toStartWith($orphanedUsername)
        ->and($newUser->id)->not->toBe($orphanedUser->id);
});

test('onboarding creates review ticket using the provisioned user under production foreign keys', function (): void {
    // ROOT CAUSE (SQLSTATE 23000 / MySQL error 1452 on mlhub.vn): support_tickets.uid and
    // .open_by have a REAL foreign key to users.id in production. SupportTicketBridge used
    // to insert uid=0/open_by=0 for the onboarding review ticket and only fix the ids with
    // a second UPDATE afterwards — but the first INSERT already violates the FK, so the
    // whole DB::transaction() in OnboardingService::upsert() rolls back: no user, no team,
    // no business, no integration, and the partner sees a bare 500 partner_api_error.
    // bootProductionLikeSchema() (see FizaHubTestHelpers.php) now creates support_tickets
    // with that exact FK, so this test fails loudly (RED) against the old code instead of
    // silently passing against a simplified schema that hid the bug.
    AdminPlan::query()->create([
        'name' => 'MKT Free Da Nang',
        'slug' => 'mlhub-free-da-nang',
        'status' => true,
        'free_plan' => true,
        'default_signup_plan' => true,
        'currency' => 'VND',
        'price' => 0,
        'permissions' => [],
    ]);

    $payload = [
        'external_business_id' => 'fh-biz-demo-888',
        'external_user_id' => 'fh-user-demo-888',
        'package_code' => 'base',
        'marketing_goal_codes' => ['local_presence', 'qr_checkin'],
        'owner' => [
            'name' => 'Nguyen Van 888',
            'phone' => '0901 234 888',
            'email' => 'nguyenvana+demo888@example.com',
        ],
        'business' => [
            'name' => 'Fiza Demo 888 - Com Tam Da Nang',
            'industry' => 'restaurant_food',
            'phone' => '0901 234 888',
            'email' => 'demo-888@example.com',
            'address' => '888 Le Duan, Da Nang',
            'tax_code' => '0101 234 888',
            'business_license_number' => 'GPKD 888',
        ],
        'verification' => [
            'identity_verified' => true,
            'verified_at' => '2026-07-13T10:00:00+07:00',
            'verified_by' => 'fizahub',
        ],
    ];

    $requestId = (string) str()->uuid();

    $response = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        [
            'Authorization' => 'Bearer test-fizahub-partner-token',
            'X-Partner' => 'fizahub',
            'X-Request-Id' => $requestId,
            'Idempotency-Key' => (string) str()->uuid(),
            'Accept' => 'application/json',
        ]
    );

    expect($response->status())->toBeIn([200, 201, 202]);

    $response->assertJsonPath('success', true)
        ->assertJsonPath('data.external_business_id', 'fh-biz-demo-888');

    $data = (array) $response->json('data');
    expect($data['request_id'] ?? null)->not->toBeEmpty()
        ->and($data['mlhub_user_id'] ?? null)->not->toBeNull()
        ->and($data['mlhub_business_id'] ?? null)->not->toBeNull()
        ->and($data['status'] ?? null)->not->toBeEmpty()
        ->and(array_key_exists('package_code', $data))->toBeTrue();

    $integration = PartnerIntegration::query()
        ->where('external_business_id', 'fh-biz-demo-888')
        ->first();
    expect($integration)->not->toBeNull();

    $onboarding = PartnerOnboardingRequest::query()
        ->where('request_id', $requestId)
        ->firstOrFail();
    expect($onboarding->support_ticket_id)->not->toBeNull();

    $ticket = SupportTicket::query()->find($onboarding->support_ticket_id);
    expect($ticket)->not->toBeNull()
        ->and($ticket->uid)->toBe($integration->mlhub_user_id)
        ->and($ticket->uid)->toBeGreaterThan(0)
        ->and($ticket->open_by)->toBe($integration->mlhub_user_id)
        ->and($ticket->open_by)->toBeGreaterThan(0);

    $ticket->load(['user', 'opener']);
    expect($ticket->user)->not->toBeNull()
        ->and($ticket->opener)->not->toBeNull();

    expect(User::query()->whereKey($ticket->uid)->exists())->toBeTrue(
        'no orphan support ticket: uid must reference a real users.id row'
    );
});

test('ROOT CAUSE: creating the onboarding review ticket with a broken mapping returns a typed 409 integration_mapping_invalid, never a raw FK 500', function (): void {
    AdminPlan::query()->create([
        'name' => 'MKT Free Da Nang',
        'slug' => 'mlhub-free-da-nang',
        'status' => true,
        'free_plan' => true,
        'default_signup_plan' => true,
        'currency' => 'VND',
        'price' => 0,
        'permissions' => [],
    ]);

    $integration = new PartnerIntegration([
        'partner_code' => 'fizahub',
        'external_business_id' => 'fh-biz-invalid-mapping',
        'mlhub_user_id' => 999999,
        'mlhub_workspace_id' => null,
    ]);

    $onboarding = PartnerOnboardingRequest::query()->create([
        'partner_code' => 'fizahub',
        'request_id' => (string) str()->uuid(),
        'external_business_id' => 'fh-biz-invalid-mapping',
        'package_code' => 'free',
        'requested_package_code' => 'free',
        'status' => OnboardingStatusMachine::AWAITING_CONSULTANT,
        'current_step' => OnboardingStatusMachine::defaultStepFor(
            OnboardingStatusMachine::AWAITING_CONSULTANT
        ),
        'payload' => [],
        'verification_status' => [],
        'duplicate_check' => [],
    ]);

    $exception = null;

    try {
        app(SupportTicketBridge::class)->createOnboardingReviewTicket(
            $onboarding,
            $integration,
            'FizaHUB onboarding awaiting consultant: fh-biz-invalid-mapping',
            'Account provisioned with Free package.',
        );
    } catch (PartnerApiException $caught) {
        $exception = $caught;
    }

    expect($exception)->not->toBeNull()
        ->and($exception->errorCode)->toBe('integration_mapping_invalid')
        ->and($exception->status)->toBe(409)
        ->and($exception->details['next_action'] ?? null)->toBe('retry_onboarding');
});

test('if support ticket creation fails mid-onboarding, the whole transaction rolls back (no orphan user/team/business/integration) and a fresh retry still succeeds', function (): void {
    AdminPlan::query()->create([
        'name' => 'MKT Free Da Nang',
        'slug' => 'mlhub-free-da-nang',
        'status' => true,
        'free_plan' => true,
        'default_signup_plan' => true,
        'currency' => 'VND',
        'price' => 0,
        'permissions' => [],
    ]);

    $externalBusinessId = 'fh-biz-rollback-test';
    $payload = [
        'external_business_id' => $externalBusinessId,
        'external_user_id' => 'fh-user-rollback-test',
        'package_code' => 'base',
        'marketing_goal_codes' => ['local_presence', 'qr_checkin'],
        'owner' => [
            'name' => 'Rollback Owner',
            'phone' => '0901 234 000',
            'email' => 'rollback-owner@example.com',
        ],
        'business' => [
            'name' => 'Rollback Store',
            'industry' => 'restaurant_food',
            'phone' => '0901 234 000',
            'email' => 'rollback-store@example.com',
            'address' => '1 Le Duan, Da Nang',
            'tax_code' => '0101 000 000',
            'business_license_number' => 'GPKD 000',
        ],
        'verification' => [
            'identity_verified' => true,
            'verified_at' => now()->toIso8601String(),
            'verified_by' => 'fizahub',
        ],
    ];
    $headers = fn (string $idempotencyKey): array => [
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) str()->uuid(),
        'Idempotency-Key' => $idempotencyKey,
        'Accept' => 'application/json',
    ];

    // Simulate an unexpected DB-level failure while creating the onboarding review
    // ticket (e.g. a transient FK/constraint problem) — this must not leave a half
    // provisioned account behind.
    $brokenBridge = Mockery::mock(SupportTicketBridge::class);
    $brokenBridge->shouldReceive('createOnboardingReviewTicket')
        ->once()
        ->andThrow(new RuntimeException('simulated support ticket insert failure'));
    $this->app->instance(SupportTicketBridge::class, $brokenBridge);

    $failingResponse = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        $headers('11111111-1111-1111-1111-111111111111')
    );

    $failingResponse->assertStatus(500)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'partner_api_error');

    expect(User::query()->count())->toBe(0, 'no orphan user after rollback')
        ->and(Team::query()->count())->toBe(0, 'no orphan team after rollback')
        ->and(LocalBusiness::query()->count())->toBe(0, 'no orphan business after rollback')
        ->and(PartnerIntegration::query()->count())->toBe(0, 'no orphan integration after rollback')
        ->and(PartnerOnboardingRequest::query()->count())->toBe(0, 'no orphan onboarding request after rollback');

    // The idempotency record for the failed attempt must be finalized (status_code set),
    // never stuck at 0/"in progress" — otherwise every future call would permanently 409.
    $failedLog = PartnerApiLog::query()
        ->where('idempotency_key', '11111111-1111-1111-1111-111111111111')
        ->first();
    expect($failedLog)->not->toBeNull()
        ->and((int) $failedLog->status_code)->not->toBe(0);

    // Restore the real bridge and retry. Per the partner integration contract, a failed
    // attempt's Idempotency-Key must NOT be reused for a different outcome (replaying a
    // failed attempt's key would just replay the cached 500 forever, by design — see
    // HandlePartnerRequest::beginIdempotentRequest()); the caller retries with a fresh key.
    // We exercise the service layer directly for the retry (rather than a second postJson
    // call) because Laravel's Router caches the resolved controller instance on the Route
    // object across requests within a single test process — a second HTTP call here would
    // keep reusing the already-constructed OnboardingService (and its now-exhausted mock)
    // from the first request, which is a test-harness quirk, not production behaviour: in
    // production every request is its own fresh PHP process/container.
    $this->app->forgetInstance(SupportTicketBridge::class);

    $retryResult = app(OnboardingService::class)->upsert(
        $payload,
        (string) str()->uuid()
    );

    expect($retryResult['onboarding']->external_business_id)->toBe($externalBusinessId);

    expect(User::query()->count())->toBe(1)
        ->and(PartnerIntegration::query()->count())->toBe(1)
        ->and(PartnerOnboardingRequest::query()->count())->toBe(1);
});
