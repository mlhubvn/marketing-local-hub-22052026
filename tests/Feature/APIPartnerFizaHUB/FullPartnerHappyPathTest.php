<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;

require_once __DIR__.'/FizaHubTestHelpers.php';

/**
 * End-to-end happy path across the full FizaHUB partner journey, using the same real
 * migrations as RootCauseInvestigationTest (production schema), covering every step
 * called out in the mission's happy path: Health -> Onboarding -> Status -> Integration
 * -> Package -> Dashboard -> Profile -> Ticket -> Message -> Attachment -> Close ->
 * Reopen -> One-time Login. No step in this chain may ever return a 500.
 */
function happyPathHeaders(array $overrides = []): array
{
    return array_merge([
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) str()->uuid(),
        'Accept' => 'application/json',
    ], $overrides);
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 600);
    config()->set('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
    config()->set('modules.apipartnerfizahub.support_max_attachment_size_mb', 10);
    Storage::fake('local');

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

    Schema::create('lb_campaigns', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('business_id')->nullable();
        $table->string('name');
        $table->string('slug')->nullable();
        $table->string('status', 40)->nullable();
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

    Schema::create('lb_bookings', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('campaign_id');
        $table->string('customer_phone')->nullable();
        $table->string('customer_email')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_feedback_responses', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('campaign_id');
        $table->string('customer_phone')->nullable();
        $table->string('customer_email')->nullable();
        $table->timestamps();
    });
});

afterEach(function (): void {
    Schema::dropIfExists('lb_feedback_responses');
    Schema::dropIfExists('lb_bookings');
    Schema::dropIfExists('lb_coupon_redemptions');
    Schema::dropIfExists('lb_review_feedbacks');
    Schema::dropIfExists('lb_lead_submissions');
    Schema::dropIfExists('lb_qr_scans');
    Schema::dropIfExists('lb_campaigns');
});

test('full partner happy path never 500s end to end: Health -> Onboarding -> Status -> Integration -> Package -> Dashboard -> Profile -> Ticket -> Message -> Attachment -> Close -> Reopen -> One-time Login', function (): void {
    $externalBusinessId = 'fh-happy-path-001';

    // 1. Health.
    $this->getJson('/api/v1/partners/fizahub/health', happyPathHeaders())
        ->assertOk()
        ->assertJsonPath('data.status', 'ok');

    // 2. Onboarding.
    $requestId = (string) str()->uuid();
    $onboardingResponse = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        [
            'external_business_id' => $externalBusinessId,
            'external_user_id' => 'fh-happy-user-001',
            'package_code' => 'free',
            'owner' => [
                'name' => 'Nguyen Van Happy',
                'email' => 'happy-path@example.com',
                'phone' => '0901234567',
            ],
            'business' => [
                'name' => 'Happy Path Store',
                'industry' => 'food_beverage',
                'address' => '123 Happy Street, Da Nang',
                'tax_code' => '0101999999',
            ],
            'verification' => [
                'identity_verified' => true,
                'verified_at' => now()->toIso8601String(),
                'verified_by' => 'fizahub',
            ],
        ],
        happyPathHeaders(['X-Request-Id' => $requestId, 'Idempotency-Key' => (string) str()->uuid()])
    )->assertCreated();

    $onboardingResponse->assertJsonPath('data.status', 'awaiting_consultant');
    $mlhubUserId = $onboardingResponse->json('data.mlhub_user_id');
    $mlhubBusinessId = $onboardingResponse->json('data.mlhub_business_id');

    // 3. Onboarding show by request_id.
    $this->getJson(
        '/api/v1/partners/fizahub/onboarding-requests/'.$requestId,
        happyPathHeaders()
    )->assertOk()->assertJsonPath('data.status', 'awaiting_consultant');

    // 4. Integration status.
    $this->getJson(
        '/api/v1/partners/fizahub/businesses/'.$externalBusinessId.'/integration-status',
        happyPathHeaders()
    )->assertOk()->assertJsonPath('data.external_business_id', $externalBusinessId);

    // 5. Package.
    $this->getJson(
        '/api/v1/partners/fizahub/businesses/'.$externalBusinessId.'/package',
        happyPathHeaders()
    )->assertOk()->assertJsonPath('data.package_code', 'free');

    // 6. Package catalog.
    $this->getJson('/api/v1/partners/fizahub/packages', happyPathHeaders())->assertOk();

    // 7. Dashboard (zero-data must still be 200).
    $this->getJson(
        '/api/v1/partners/fizahub/businesses/'.$externalBusinessId.'/dashboard',
        happyPathHeaders()
    )->assertOk()->assertJsonPath('data.metrics.qr_scans', 0);

    // 8. Insights / Recommendations / Campaigns (zero-data must still be 200).
    $this->getJson('/api/v1/partners/fizahub/businesses/'.$externalBusinessId.'/insights', happyPathHeaders())->assertOk();
    $this->getJson('/api/v1/partners/fizahub/businesses/'.$externalBusinessId.'/recommendations', happyPathHeaders())->assertOk();
    $this->getJson('/api/v1/partners/fizahub/businesses/'.$externalBusinessId.'/campaigns', happyPathHeaders())
        ->assertOk()->assertJsonPath('data.items', []);

    // 9. Profile update.
    $this->patchJson(
        '/api/v1/partners/fizahub/businesses/'.$externalBusinessId.'/profile',
        ['business' => ['name' => 'Happy Path Store Updated', 'phone' => '0909876543']],
        happyPathHeaders()
    )->assertOk()->assertJsonPath('data.external_business_id', $externalBusinessId);

    // 10. Support summary.
    $this->getJson(
        '/api/v1/partners/fizahub/businesses/'.$externalBusinessId.'/support-summary',
        happyPathHeaders()
    )->assertOk();

    // 11. Create support ticket.
    $ticketId = $this->postJson(
        '/api/v1/partners/fizahub/businesses/'.$externalBusinessId.'/support-tickets',
        ['subject' => 'Happy path support', 'message' => 'Need help with onboarding.'],
        happyPathHeaders(['Idempotency-Key' => (string) str()->uuid()])
    )->assertCreated()->json('data.ticket_id');

    // 12. List support tickets.
    $this->getJson(
        '/api/v1/partners/fizahub/businesses/'.$externalBusinessId.'/support-tickets',
        happyPathHeaders()
    )->assertOk();

    // 13. Ticket detail.
    $this->getJson(
        '/api/v1/partners/fizahub/support-tickets/'.$ticketId.'?external_business_id='.$externalBusinessId,
        happyPathHeaders()
    )->assertOk();

    // 14. Add message.
    $this->postJson(
        '/api/v1/partners/fizahub/support-tickets/'.$ticketId.'/messages?external_business_id='.$externalBusinessId,
        ['message' => 'Following up on my request.'],
        happyPathHeaders(['Idempotency-Key' => (string) str()->uuid()])
    )->assertCreated();

    // 15. Attach a file.
    $file = UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf');
    $this->post(
        '/api/v1/partners/fizahub/support-tickets/'.$ticketId.'/attachments?external_business_id='.$externalBusinessId,
        ['file' => $file],
        happyPathHeaders()
    )->assertCreated();

    // 16. Close the ticket.
    $this->patchJson(
        '/api/v1/partners/fizahub/support-tickets/'.$ticketId.'/close?external_business_id='.$externalBusinessId,
        ['reason' => 'Resolved during happy path test'],
        happyPathHeaders()
    )->assertOk()->assertJsonPath('data.status', 'closed');

    // 17. Reopen the ticket.
    $this->postJson(
        '/api/v1/partners/fizahub/support-tickets/'.$ticketId.'/reopen?external_business_id='.$externalBusinessId,
        [],
        happyPathHeaders()
    )->assertOk()->assertJsonPath('data.status', 'open');

    // 18. Confirm onboarding (partner acknowledges receipt).
    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests/'.$requestId.'/confirm',
        ['note' => 'Partner confirms the request was received.'],
        happyPathHeaders()
    )->assertOk();

    // 19. MLHUB staff completes consulting/configuration and marks the onboarding ready
    // (an internal admin action, not part of the 24 partner endpoints).
    PartnerOnboardingRequest::query()->where('request_id', $requestId)->update([
        'status' => OnboardingStatusMachine::READY,
        'current_step' => OnboardingStatusMachine::defaultStepFor(OnboardingStatusMachine::READY),
        'ready_at' => now(),
    ]);

    // 20. One-time login is now allowed.
    $this->postJson(
        '/api/v1/partners/fizahub/businesses/'.$externalBusinessId.'/one-time-login',
        [],
        happyPathHeaders(['Idempotency-Key' => (string) str()->uuid()])
    )->assertCreated()->assertJsonPath('success', true);

    // 21. SSO verify.
    $this->postJson('/api/v1/partners/fizahub/partner/sso/verify', [], happyPathHeaders())
        ->assertOk()->assertJsonPath('data.authenticated', true);

    expect($mlhubUserId)->toBeInt()->and($mlhubBusinessId)->toBeInt();
});
