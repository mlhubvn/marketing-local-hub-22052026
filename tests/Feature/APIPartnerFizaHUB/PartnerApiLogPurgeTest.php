<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerApiLog;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportAttachment;
use Modules\APIPartnerFizaHUB\Models\PartnerWebhookOutbox;
use Modules\APIPartnerFizaHUB\Services\OnboardingAdminService;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Modules\APIPartnerFizaHUB\Support\PartnerBusinessIdentifiers;
use Modules\APIPartnerFizaHUB\Support\PartnerBusinessLogMatcher;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

require_once __DIR__.'/FizaHubTestHelpers.php';

/**
 * Dedicated coverage for the `endpoint`/`request_payload`/`response_payload`
 * boundary + JSON-path matching bug in PartnerBusinessLogMatcher, and for the
 * `purgePartnerBusiness()` flow that consumes it. Payloads here intentionally use the
 * REAL nested shape produced by HandlePartnerRequest::finalizeLog() — body under
 * `request_payload->body`, data under `response_payload->data` — because the
 * production bug was that the purge/residue code searched for
 * `request_payload->external_business_id` directly, which never exists.
 */
function createPartnerLogPurgeTables(): void
{
    dropFizaHubPartnerTables();
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('username')->nullable()->unique();
        $table->string('email')->unique();
        $table->string('password');
        $table->boolean('is_super_admin')->default(false);
        $table->timestamps();
    });

    Schema::create('teams', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('slug');
        $table->unsignedBigInteger('owner_user_id')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_businesses', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->string('name');
        $table->string('type', 60)->default('other');
        $table->timestamps();
    });

    Schema::create('support_tickets', function (Blueprint $table): void {
        $table->id();
        $table->string('id_secure', 40)->unique();
        $table->unsignedBigInteger('uid');
        $table->unsignedBigInteger('open_by');
        $table->unsignedBigInteger('team_id')->nullable();
        $table->string('title', 255);
        $table->text('content');
        $table->unsignedTinyInteger('status')->default(1);
        $table->unsignedInteger('changed')->nullable();
        $table->unsignedInteger('created')->nullable();
    });

    createFizaHubPartnerTables();
}

function seedPartnerLogPurgeBusiness(string $externalBusinessId, User $user, Team $team): PartnerIntegration
{
    $business = LocalBusiness::query()->create([
        'user_id' => $user->id,
        'name' => 'Business '.$externalBusinessId,
        'type' => 'other',
    ]);

    return PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => $externalBusinessId,
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
        'package_code' => 'free',
        'status' => 'active',
    ]);
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.webhook_base_url', '');
    createPartnerLogPurgeTables();
});

afterEach(function (): void {
    dropFizaHubPartnerTables();
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
});

test('matcher matches a string external_business_id nested under request_payload.body', function (): void {
    $log = PartnerApiLog::query()->create([
        'partner_code' => 'fizahub',
        'method' => 'POST',
        'endpoint' => '/api/v1/partners/fizahub/onboarding-requests',
        'status_code' => 202,
        'request_payload' => ['_request_hash' => 'h', 'headers' => [], 'body' => ['external_business_id' => '155']],
        'response_payload' => ['data' => ['status' => 'accepted']],
    ]);

    $identifiers = new PartnerBusinessIdentifiers('fizahub', '155');
    $matched = PartnerBusinessLogMatcher::applyToApiLogs(DB::table('partner_api_logs'), $identifiers)->pluck('id');

    expect($matched->all())->toBe([$log->id]);
});

test('matcher matches a numeric external_business_id nested under request_payload.body', function (): void {
    // NOTE: JSON column is written with an unquoted numeric literal, exactly like a
    // partner client posting {"external_business_id": 155} (no quotes).
    DB::table('partner_api_logs')->insert([
        'partner_code' => 'fizahub',
        'method' => 'POST',
        'endpoint' => '/api/v1/partners/fizahub/onboarding-requests',
        'status_code' => 202,
        'request_payload' => '{"_request_hash":"h","headers":{},"body":{"external_business_id":155}}',
        'response_payload' => '{"data":{"status":"accepted"}}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $id = DB::table('partner_api_logs')->first()->id;

    $identifiers = new PartnerBusinessIdentifiers('fizahub', '155');
    $matched = PartnerBusinessLogMatcher::applyToApiLogs(DB::table('partner_api_logs'), $identifiers)->pluck('id');

    expect($matched->all())->toBe([$id]);
});

test('matcher matches a business id that only appears in the endpoint path segment', function (): void {
    $log = PartnerApiLog::query()->create([
        'partner_code' => 'fizahub',
        'method' => 'GET',
        'endpoint' => '/api/v1/partners/fizahub/businesses/155/dashboard',
        'status_code' => 200,
        'request_payload' => ['_request_hash' => 'h', 'headers' => []],
        'response_payload' => ['data' => ['visits' => 4]],
    ]);

    $identifiers = new PartnerBusinessIdentifiers('fizahub', '155');
    $matched = PartnerBusinessLogMatcher::applyToApiLogs(DB::table('partner_api_logs'), $identifiers)->pluck('id');

    expect($matched->all())->toBe([$log->id]);
});

test('matcher matches a business id in the endpoint query string', function (): void {
    $log = PartnerApiLog::query()->create([
        'partner_code' => 'fizahub',
        'method' => 'GET',
        'endpoint' => '/api/v1/partners/fizahub/businesses/155?include=package',
        'status_code' => 200,
        'request_payload' => ['_request_hash' => 'h', 'headers' => []],
        'response_payload' => ['data' => ['package' => 'free']],
    ]);

    $identifiers = new PartnerBusinessIdentifiers('fizahub', '155');
    $matched = PartnerBusinessLogMatcher::applyToApiLogs(DB::table('partner_api_logs'), $identifiers)->pluck('id');

    expect($matched->all())->toBe([$log->id]);
});

test('matcher never matches business 1155 or 1550 when purging business 155', function (): void {
    PartnerApiLog::query()->create([
        'partner_code' => 'fizahub',
        'method' => 'GET',
        'endpoint' => '/api/v1/partners/fizahub/businesses/1155/dashboard',
        'status_code' => 200,
        'request_payload' => ['_request_hash' => 'h1', 'headers' => []],
        'response_payload' => null,
    ]);
    PartnerApiLog::query()->create([
        'partner_code' => 'fizahub',
        'method' => 'GET',
        'endpoint' => '/api/v1/partners/fizahub/businesses/1550?include=package',
        'status_code' => 200,
        'request_payload' => ['_request_hash' => 'h2', 'headers' => []],
        'response_payload' => null,
    ]);
    PartnerApiLog::query()->create([
        'partner_code' => 'fizahub',
        'method' => 'POST',
        'endpoint' => '/api/v1/partners/fizahub/onboarding-requests',
        'status_code' => 202,
        'request_payload' => ['_request_hash' => 'h3', 'headers' => [], 'body' => ['external_business_id' => '1155']],
        'response_payload' => null,
    ]);

    $identifiers = new PartnerBusinessIdentifiers('fizahub', '155');
    $matched = PartnerBusinessLogMatcher::applyToApiLogs(DB::table('partner_api_logs'), $identifiers)->count();

    expect($matched)->toBe(0)
        ->and(PartnerApiLog::query()->count())->toBe(3);
});

test('matcher never crosses partner_code boundaries for the same business id', function (): void {
    PartnerApiLog::query()->create([
        'partner_code' => 'another-partner',
        'method' => 'GET',
        'endpoint' => '/api/v1/partners/another-partner/businesses/155/dashboard',
        'status_code' => 200,
        'request_payload' => ['_request_hash' => 'h', 'headers' => []],
        'response_payload' => null,
    ]);

    $identifiers = new PartnerBusinessIdentifiers('fizahub', '155');
    $matched = PartnerBusinessLogMatcher::applyToApiLogs(DB::table('partner_api_logs'), $identifiers)->count();

    expect($matched)->toBe(0);
});

test('matcher matches by request_id even when it never equals the onboarding request_id', function (): void {
    // partner_api_logs.request_id is the per-call X-Request-Id header, a different
    // UUID from the onboarding row's own request_id — the API log below is only
    // findable through business/endpoint matching, never through request_id equality.
    $log = PartnerApiLog::query()->create([
        'partner_code' => 'fizahub',
        'method' => 'GET',
        'endpoint' => '/api/v1/partners/fizahub/businesses/155/campaigns',
        'request_id' => (string) str()->uuid(),
        'status_code' => 200,
        'request_payload' => ['_request_hash' => 'h', 'headers' => []],
        'response_payload' => ['data' => ['business' => ['external_business_id' => '155']]],
    ]);

    $identifiers = new PartnerBusinessIdentifiers('fizahub', '155', [(string) str()->uuid()]);
    $matched = PartnerBusinessLogMatcher::applyToApiLogs(DB::table('partner_api_logs'), $identifiers)->pluck('id');

    expect($matched->all())->toBe([$log->id]);
});

test('matcher forces 1=0 and never deletes the table when every identifier is empty', function (): void {
    PartnerApiLog::query()->create([
        'partner_code' => 'fizahub',
        'method' => 'GET',
        'endpoint' => '/api/v1/partners/fizahub/businesses/155/dashboard',
        'status_code' => 200,
        'request_payload' => null,
        'response_payload' => null,
    ]);

    $identifiers = new PartnerBusinessIdentifiers('fizahub', '', []);
    PartnerBusinessLogMatcher::applyToApiLogs(DB::table('partner_api_logs'), $identifiers)->delete();

    expect(PartnerApiLog::query()->count())->toBe(1);
});

test('matcher forces 1=0 when partner_code is empty even with a business id supplied', function (): void {
    PartnerApiLog::query()->create([
        'partner_code' => 'fizahub',
        'method' => 'GET',
        'endpoint' => '/api/v1/partners/fizahub/businesses/155/dashboard',
        'status_code' => 200,
        'request_payload' => null,
        'response_payload' => null,
    ]);

    $identifiers = new PartnerBusinessIdentifiers('', '155', []);
    PartnerBusinessLogMatcher::applyToApiLogs(DB::table('partner_api_logs'), $identifiers)->delete();

    expect(PartnerApiLog::query()->count())->toBe(1);
});

test('purgePartnerBusiness deletes every log shape for the purged business and preserves 1155 and other partners', function (): void {
    Storage::fake('local');

    $user = User::query()->create([
        'name' => 'Owner 155',
        'username' => 'owner155',
        'email' => 'owner155@example.com',
        'password' => 'password-password-password-password-password-password-1234',
    ]);
    $team = Team::query()->create(['name' => 'Team 155', 'slug' => 'team-155', 'owner_user_id' => $user->id]);
    $integration = seedPartnerLogPurgeBusiness('155', $user, $team);

    $onboarding = PartnerOnboardingRequest::query()->create([
        'partner_code' => 'fizahub',
        'request_id' => (string) str()->uuid(),
        'external_business_id' => '155',
        'package_code' => 'free',
        'requested_package_code' => 'base',
        'status' => OnboardingStatusMachine::READY,
        'current_step' => OnboardingStatusMachine::READY,
        'admin_status' => OnboardingStatusMachine::READY,
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $integration->mlhub_business_id,
    ]);

    // The four required matched shapes for business 155.
    $endpointOnly = PartnerApiLog::query()->create([
        'partner_code' => 'fizahub', 'method' => 'GET',
        'endpoint' => '/api/v1/partners/fizahub/businesses/155/dashboard',
        'request_id' => (string) str()->uuid(), 'status_code' => 200,
        'request_payload' => ['_request_hash' => 'h1', 'headers' => []],
        'response_payload' => null,
    ]);
    $queryString = PartnerApiLog::query()->create([
        'partner_code' => 'fizahub', 'method' => 'GET',
        'endpoint' => '/api/v1/partners/fizahub/businesses/155?include=package',
        'request_id' => (string) str()->uuid(), 'status_code' => 200,
        'request_payload' => ['_request_hash' => 'h2', 'headers' => []],
        'response_payload' => null,
    ]);
    $nestedStringBody = PartnerApiLog::query()->create([
        'partner_code' => 'fizahub', 'method' => 'POST',
        'endpoint' => '/api/v1/partners/fizahub/onboarding-requests',
        'request_id' => (string) str()->uuid(), 'status_code' => 202,
        'request_payload' => ['_request_hash' => 'h3', 'headers' => [], 'body' => ['external_business_id' => '155']],
        'response_payload' => ['data' => ['status' => 'accepted']],
    ]);
    $nestedResponseBusiness = PartnerApiLog::query()->create([
        'partner_code' => 'fizahub', 'method' => 'GET',
        'endpoint' => '/api/v1/partners/fizahub/businesses/155/support-tickets',
        'request_id' => (string) str()->uuid(), 'status_code' => 200,
        'request_payload' => ['_request_hash' => 'h4', 'headers' => []],
        'response_payload' => ['data' => ['business' => ['external_business_id' => '155']]],
    ]);

    // Survivors: business 1155, a different partner_code, unrelated business.
    $businessOneOneFiveFive = PartnerApiLog::query()->create([
        'partner_code' => 'fizahub', 'method' => 'GET',
        'endpoint' => '/api/v1/partners/fizahub/businesses/1155/dashboard',
        'request_id' => (string) str()->uuid(), 'status_code' => 200,
        'request_payload' => ['_request_hash' => 'h5', 'headers' => []],
        'response_payload' => null,
    ]);
    $otherPartner = PartnerApiLog::query()->create([
        'partner_code' => 'another-partner', 'method' => 'GET',
        'endpoint' => '/api/v1/partners/another-partner/businesses/155/dashboard',
        'request_id' => (string) str()->uuid(), 'status_code' => 200,
        'request_payload' => ['_request_hash' => 'h6', 'headers' => []],
        'response_payload' => null,
    ]);

    $matchedWebhook = PartnerWebhookOutbox::query()->create([
        'partner_code' => 'fizahub',
        'event_type' => 'onboarding.status',
        'dedupe_key' => (string) str()->uuid().'|purge-155',
        'endpoint_path' => '/webhooks/onboarding',
        'payload' => ['external_business_id' => '155', 'request_id' => $onboarding->request_id],
        'status' => 'pending',
        'attempts' => 0,
        'available_at' => now(),
    ]);
    $survivorWebhook = PartnerWebhookOutbox::query()->create([
        'partner_code' => 'fizahub',
        'event_type' => 'onboarding.status',
        'dedupe_key' => (string) str()->uuid().'|purge-1155',
        'endpoint_path' => '/webhooks/onboarding',
        'payload' => ['external_business_id' => '1155'],
        'status' => 'pending',
        'attempts' => 0,
        'available_at' => now(),
    ]);

    $attachmentPath = 'partner-fizahub/support/155/proof.txt';
    Storage::disk('local')->put($attachmentPath, 'proof');
    $ticketForAttachment = DB::table('support_tickets')->insertGetId([
        'id_secure' => str()->random(32),
        'uid' => $user->id,
        'open_by' => $user->id,
        'title' => 'Ticket for 155',
        'content' => '{}',
        'status' => 1,
        'created' => time(),
        'changed' => time(),
    ]);
    $onboarding->forceFill(['support_ticket_id' => $ticketForAttachment])->save();
    PartnerSupportAttachment::query()->create([
        'support_ticket_id' => $ticketForAttachment,
        'id_secure' => str()->random(32),
        'original_name' => 'proof.txt',
        'mime_type' => 'text/plain',
        'size_bytes' => 5,
        'disk' => 'local',
        'path' => $attachmentPath,
        'uploaded_by_user_id' => $user->id,
    ]);

    app(OnboardingAdminService::class)->adminPurgeOnboarding($onboarding->fresh(), null);

    expect(PartnerApiLog::query()->find($endpointOnly->id))->toBeNull()
        ->and(PartnerApiLog::query()->find($queryString->id))->toBeNull()
        ->and(PartnerApiLog::query()->find($nestedStringBody->id))->toBeNull()
        ->and(PartnerApiLog::query()->find($nestedResponseBusiness->id))->toBeNull()
        ->and(PartnerApiLog::query()->find($businessOneOneFiveFive->id))->not->toBeNull()
        ->and(PartnerApiLog::query()->find($otherPartner->id))->not->toBeNull()
        ->and(PartnerWebhookOutbox::query()->find($matchedWebhook->id))->toBeNull()
        ->and(PartnerWebhookOutbox::query()->find($survivorWebhook->id))->not->toBeNull()
        ->and(User::query()->whereKey($user->id)->exists())->toBeTrue()
        ->and(User::query()->whereKey($user->id)->value('password'))->not->toBeNull();

    Storage::disk('local')->assertMissing($attachmentPath);

    // Independent data untouched.
    expect(PartnerIntegration::query()->where('external_business_id', '155')->exists())->toBeFalse();

    // Running the purge again for the same (now-deleted) business must not error
    // and must not touch the survivors either.
    app(OnboardingAdminService::class)->adminPurgeForUser($user->id, null);

    expect(PartnerApiLog::query()->find($businessOneOneFiveFive->id))->not->toBeNull()
        ->and(PartnerApiLog::query()->find($otherPartner->id))->not->toBeNull()
        ->and(User::query()->whereKey($user->id)->exists())->toBeTrue();
});
