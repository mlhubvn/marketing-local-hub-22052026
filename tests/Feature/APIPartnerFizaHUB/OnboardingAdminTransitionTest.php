<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Actions\DeleteUser;
use Modules\AdminUser\Livewire\UserIndex;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Livewire\FizaHubOnboardingIndex;
use Modules\APIPartnerFizaHUB\Models\PartnerApiLog;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingStatusHistory;
use Modules\APIPartnerFizaHUB\Models\PartnerPackageAssignment;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportAttachment;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportTicketContext;
use Modules\APIPartnerFizaHUB\Models\PartnerWebhookOutbox;
use Modules\APIPartnerFizaHUB\Services\OnboardingAdminService;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Modules\AppAdvancedCustomerCrm\Models\CustomerActivity;
use Modules\AppAffiliate\Models\AffiliateProfile;
use Modules\AppBookingPages\Models\BookingService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCustomers\Models\Customer;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppLoyaltyStampCards\Models\LoyaltyCard;
use Modules\AppQRCampaigns\Models\QrCampaign;

require_once __DIR__.'/FizaHubTestHelpers.php';

function createAdminTransitionTables(): void
{
    dropFizaHubDefaultDataTables();
    dropFizaHubPartnerTables();
    Schema::dropIfExists('audit_logs');
    Schema::dropIfExists('files');
    bootProductionLikeSchema();

    Schema::table('users', function (Blueprint $table): void {
        $table->string('avatar_path')->nullable();
        $table->string('avatar_disk')->nullable();
    });

    createFizaHubDefaultDataTables();

    Schema::create('files', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
        $table->string('disk')->default('local');
        $table->string('path')->nullable();
        $table->boolean('is_folder')->default(false);
        $table->timestamps();
    });

    Schema::create('audit_logs', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('causer_user_id')->nullable();
        $table->string('event');
        $table->string('description')->nullable();
        $table->string('subject_type')->nullable();
        $table->unsignedBigInteger('subject_id')->nullable();
        $table->string('route_name')->nullable();
        $table->string('area', 20)->default('admin');
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->json('metadata')->nullable();
        $table->timestamps();
    });

}

/**
 * @return array{integration: PartnerIntegration, onboarding: PartnerOnboardingRequest}
 */
function seedAdminOnboarding(): array
{
    $user = User::query()->create([
        'name' => 'Owner',
        'username' => 'owner_admin',
        'email' => 'owner-admin@example.com',
        'password' => 'password-password-password-password-password-password-1234',
    ]);

    $team = Team::query()->create([
        'name' => 'Owner Team',
        'slug' => 'owner-team',
        'owner_user_id' => $user->id,
    ]);

    $business = LocalBusiness::query()->create([
        'user_id' => $user->id,
        'name' => 'Owner Shop',
        'type' => 'other',
    ]);

    $integration = PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'biz-admin',
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
        'package_code' => 'free',
        'status' => 'active',
    ]);

    $onboarding = PartnerOnboardingRequest::query()->create([
        'partner_code' => 'fizahub',
        'request_id' => (string) str()->uuid(),
        'external_business_id' => 'biz-admin',
        'package_code' => 'free',
        'requested_package_code' => 'base',
        'status' => OnboardingStatusMachine::AWAITING_CONSULTANT,
        'current_step' => OnboardingStatusMachine::defaultStepFor(OnboardingStatusMachine::AWAITING_CONSULTANT),
        'admin_status' => OnboardingStatusMachine::AWAITING_CONSULTANT,
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
    ]);

    return compact('integration', 'onboarding');
}

function adminOnboardingHeaders(array $overrides = []): array
{
    return array_merge([
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) str()->uuid(),
        'Idempotency-Key' => (string) str()->uuid(),
        'Accept' => 'application/json',
    ], $overrides);
}

function adminOnboardingPayload(): array
{
    return [
        'external_business_id' => 'biz-admin',
        'external_user_id' => 'user-admin',
        'package_code' => 'base',
        'marketing_goal_codes' => ['local_presence', 'qr_checkin'],
        'owner' => [
            'name' => 'Admin Lifecycle Owner',
            'phone' => '0901234567',
            'email' => 'admin-lifecycle-owner@example.com',
        ],
        'business' => [
            'name' => 'Admin Lifecycle Shop',
            'industry' => 'restaurant_food',
            'phone' => '0901234567',
            'email' => 'admin-lifecycle-shop@example.com',
            'website' => 'https://admin-lifecycle.example.com',
            'address' => 'Da Nang',
            'tax_code' => '0101234567',
            'business_license_number' => 'GPKD123',
        ],
        'verification' => [
            'identity_verified' => true,
            'verified_at' => '2026-07-13T10:00:00+07:00',
            'verified_by' => 'fizahub',
        ],
    ];
}

function adminUnrelatedOnboardingPayload(): array
{
    $payload = adminOnboardingPayload();
    $payload['external_business_id'] = 'biz-admin-unrelated';
    $payload['external_user_id'] = 'user-admin-unrelated';
    $payload['owner']['name'] = 'Unrelated Lifecycle Owner';
    $payload['owner']['email'] = 'unrelated-lifecycle-owner@example.com';
    $payload['business']['name'] = 'Unrelated Lifecycle Shop';
    $payload['business']['email'] = 'unrelated-lifecycle-shop@example.com';
    $payload['business']['tax_code'] = '0207654321';
    $payload['business']['business_license_number'] = 'GPKD987';

    return $payload;
}

function adminSnapshotAttributes(object $model, array $attributes): array
{
    return collect($model->only($attributes))
        ->map(fn (mixed $value): mixed => $value instanceof DateTimeInterface
            ? $value->format('Y-m-d\TH:i:s.uP')
            : $value)
        ->all();
}

function adminDefaultDataSnapshot(PartnerIntegration $integration): array
{
    $userId = (int) $integration->mlhub_user_id;
    $businessId = (int) $integration->mlhub_business_id;
    $campaigns = QrCampaign::query()
        ->where('user_id', $userId)
        ->where('business_id', $businessId)
        ->orderBy('type')
        ->get();
    $customer = Customer::query()
        ->where('user_id', $userId)
        ->where('business_id', $businessId)
        ->firstOrFail();
    $bookingService = BookingService::query()
        ->where('user_id', $userId)
        ->where('business_id', $businessId)
        ->firstOrFail();
    $loyaltyCard = LoyaltyCard::query()
        ->where('user_id', $userId)
        ->where('business_id', $businessId)
        ->firstOrFail();
    $campaignTypes = $campaigns->mapWithKeys(fn (QrCampaign $campaign): array => [$campaign->id => $campaign->type]);

    return [
        'integration_metadata' => $integration->metadata,
        'customer' => adminSnapshotAttributes($customer, ['id', 'user_id', 'business_id', 'name', 'phone', 'email', 'tags', 'note', 'metadata', 'first_seen_at', 'last_activity_at']),
        'booking_service' => adminSnapshotAttributes($bookingService, ['id', 'user_id', 'business_id', 'name', 'duration_minutes', 'price', 'description', 'available_days', 'time_slots', 'max_bookings_per_slot', 'use_business_hours', 'slot_interval', 'buffer_before', 'buffer_after', 'service_hours', 'is_active']),
        'loyalty_card' => adminSnapshotAttributes($loyaltyCard, ['id', 'user_id', 'team_id', 'business_id', 'slug', 'name', 'required_stamps', 'stamp_method', 'customer_identifier', 'reward_title', 'reward_type', 'reward_value', 'expiry_days', 'stamp_cooldown_minutes', 'max_stamps_per_day', 'settings', 'status']),
        'campaigns' => $campaigns->map(fn (QrCampaign $campaign): array => adminSnapshotAttributes($campaign, [
            'id', 'user_id', 'business_id', 'slug', 'name', 'type', 'status', 'destination_url', 'settings', 'published_at',
        ]))->all(),
        'landing_pages' => LandingPage::query()
            ->where('user_id', $userId)
            ->where('business_id', $businessId)
            ->orderBy('campaign_id')
            ->get()
            ->map(fn (LandingPage $page): array => array_merge(
                ['campaign_type' => $campaignTypes->get($page->campaign_id)],
                adminSnapshotAttributes($page, ['id', 'user_id', 'business_id', 'campaign_id', 'slug', 'title', 'type', 'template', 'status', 'content', 'settings', 'visits_count', 'conversions_count', 'published_at'])
            ))
            ->all(),
    ];
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.webhook_base_url', '');
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);
    createAdminTransitionTables();
});

afterEach(function (): void {
    dropFizaHubDefaultDataTables();
    dropFizaHubPartnerTables();
    Schema::dropIfExists('audit_logs');
    Schema::dropIfExists('files');
    Schema::dropIfExists('affiliate_profiles');
    Schema::dropIfExists('support_comments');
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
    Schema::dropIfExists('plans');
});

test('valid admin transitions advance status and record history with timestamps', function (): void {
    $seed = seedAdminOnboarding();
    $service = app(OnboardingAdminService::class);

    $service->transition($seed['onboarding'], OnboardingStatusMachine::CONSULTING, 'admin', 7, 'Called owner.');
    $service->transition($seed['onboarding']->fresh(), OnboardingStatusMachine::CONFIGURING, 'admin', 7);
    $service->transition($seed['onboarding']->fresh(), OnboardingStatusMachine::READY, 'admin', 7);
    $final = $service->transition($seed['onboarding']->fresh(), OnboardingStatusMachine::COMPLETED, 'admin', 7);

    expect($final->status)->toBe('completed')
        ->and($final->current_step)->toBe('ready')
        ->and($final->consultant_contacted_at)->not->toBeNull()
        ->and($final->ready_at)->not->toBeNull()
        ->and($final->completed_at)->not->toBeNull();

    $histories = PartnerOnboardingStatusHistory::query()
        ->where('onboarding_request_id', $seed['onboarding']->id)
        ->orderBy('id')
        ->pluck('to_status')
        ->all();

    expect($histories)->toBe([
        'in_consultation',
        'configuring',
        'ready',
        'completed',
    ]);
});

test('invalid admin transition is rejected', function (): void {
    $seed = seedAdminOnboarding();
    $service = app(OnboardingAdminService::class);

    expect(fn () => $service->transition($seed['onboarding'], OnboardingStatusMachine::COMPLETED))
        ->toThrow(InvalidArgumentException::class);

    expect($seed['onboarding']->fresh()->status)->toBe('awaiting_consultant')
        ->and(PartnerOnboardingStatusHistory::query()->count())->toBe(0);
});

test('Admin status changes are data-neutral and existing user deletion removes every FizaHUB default', function (): void {
    Queue::fake();
    config()->set('modules.apipartnerfizahub.webhook_base_url', 'https://fizahub.test');

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

    $targetRequestId = (string) str()->uuid();
    $response = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        adminOnboardingPayload(),
        adminOnboardingHeaders(['X-Request-Id' => $targetRequestId])
    )->assertCreated();

    $seed = [
        'integration' => PartnerIntegration::query()
            ->where('external_business_id', 'biz-admin')
            ->firstOrFail(),
        'onboarding' => PartnerOnboardingRequest::query()
            ->where('request_id', $response->json('data.request_id'))
            ->firstOrFail(),
    ];
    $userId = (int) $seed['integration']->mlhub_user_id;
    $businessId = (int) $seed['integration']->mlhub_business_id;

    expect(Customer::query()->where('user_id', $userId)->where('business_id', $businessId)->count())->toBe(1)
        ->and(BookingService::query()->where('user_id', $userId)->where('business_id', $businessId)->count())->toBe(1)
        ->and(LoyaltyCard::query()->where('user_id', $userId)->where('business_id', $businessId)->count())->toBe(1);

    $snapshot = adminDefaultDataSnapshot($seed['integration']);

    expect($snapshot['campaigns'])->toHaveCount(5)
        ->and($snapshot['landing_pages'])->toHaveCount(5)
        ->and(array_column($snapshot['campaigns'], 'type'))->toBe(['booking', 'coupon', 'feedback', 'lead', 'review']);

    $service = app(OnboardingAdminService::class);
    $service->transition($seed['onboarding'], OnboardingStatusMachine::CONSULTING, 'admin', 7);
    $service->transition($seed['onboarding']->fresh(), OnboardingStatusMachine::CONFIGURING, 'admin', 7);
    $service->transition($seed['onboarding']->fresh(), OnboardingStatusMachine::READY, 'admin', 7);
    $service->transition($seed['onboarding']->fresh(), OnboardingStatusMachine::COMPLETED, 'admin', 7);

    expect(adminDefaultDataSnapshot($seed['integration']->fresh()))->toBe($snapshot);

    $unrelatedRequestId = (string) str()->uuid();
    $unrelatedResponse = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        adminUnrelatedOnboardingPayload(),
        adminOnboardingHeaders(['X-Request-Id' => $unrelatedRequestId])
    )->assertCreated();
    $unrelatedIntegration = PartnerIntegration::query()
        ->where('external_business_id', 'biz-admin-unrelated')
        ->firstOrFail();
    $unrelatedOnboarding = PartnerOnboardingRequest::query()
        ->where('request_id', $unrelatedResponse->json('data.request_id'))
        ->firstOrFail();
    $unrelatedSnapshot = adminDefaultDataSnapshot($unrelatedIntegration);

    $targetOnboarding = $seed['onboarding']->fresh();
    $targetGraph = [
        'user_id' => $userId,
        'team_id' => (int) $seed['integration']->mlhub_workspace_id,
        'business_id' => $businessId,
        'affiliate_profile_ids' => AffiliateProfile::query()->where('user_id', $userId)->pluck('id')->all(),
        'package_assignment_ids' => PartnerPackageAssignment::query()->where('partner_integration_id', $seed['integration']->id)->pluck('id')->all(),
        'onboarding_ids' => PartnerOnboardingRequest::query()->where('mlhub_user_id', $userId)->pluck('id')->all(),
        'history_ids' => PartnerOnboardingStatusHistory::query()->where('onboarding_request_id', $targetOnboarding->id)->pluck('id')->all(),
        'support_ticket_ids' => SupportTicket::query()->where('uid', $userId)->pluck('id')->all(),
        'support_context_ids' => PartnerSupportTicketContext::query()->where('partner_integration_id', $seed['integration']->id)->pluck('id')->all(),
        'customer_activity_ids' => CustomerActivity::query()->where('customer_id', $snapshot['customer']['id'])->pluck('id')->all(),
        'webhook_ids' => PartnerWebhookOutbox::query()->where('dedupe_key', 'like', '%'.$targetRequestId.'%')->pluck('id')->all(),
        'api_log_ids' => PartnerApiLog::query()->where('request_id', $targetRequestId)->pluck('id')->all(),
    ];
    $unrelatedGraph = [
        'user_id' => (int) $unrelatedIntegration->mlhub_user_id,
        'team_id' => (int) $unrelatedIntegration->mlhub_workspace_id,
        'business_id' => (int) $unrelatedIntegration->mlhub_business_id,
        'affiliate_profile_ids' => AffiliateProfile::query()->where('user_id', $unrelatedIntegration->mlhub_user_id)->pluck('id')->all(),
        'package_assignment_ids' => PartnerPackageAssignment::query()->where('partner_integration_id', $unrelatedIntegration->id)->pluck('id')->all(),
        'history_ids' => PartnerOnboardingStatusHistory::query()->where('onboarding_request_id', $unrelatedOnboarding->id)->pluck('id')->all(),
        'support_ticket_ids' => SupportTicket::query()->where('uid', $unrelatedIntegration->mlhub_user_id)->pluck('id')->all(),
        'support_context_ids' => PartnerSupportTicketContext::query()->where('partner_integration_id', $unrelatedIntegration->id)->pluck('id')->all(),
        'customer_activity_ids' => CustomerActivity::query()->where('customer_id', $unrelatedSnapshot['customer']['id'])->pluck('id')->all(),
        'webhook_ids' => PartnerWebhookOutbox::query()->where('dedupe_key', 'like', '%'.$unrelatedRequestId.'%')->pluck('id')->all(),
        'api_log_ids' => PartnerApiLog::query()->where('request_id', $unrelatedRequestId)->pluck('id')->all(),
    ];

    foreach (['affiliate_profile_ids', 'package_assignment_ids', 'onboarding_ids', 'history_ids', 'support_ticket_ids', 'support_context_ids', 'customer_activity_ids', 'webhook_ids', 'api_log_ids'] as $key) {
        expect($targetGraph[$key])->not->toBeEmpty();
    }

    foreach (['affiliate_profile_ids', 'package_assignment_ids', 'history_ids', 'support_ticket_ids', 'support_context_ids', 'customer_activity_ids', 'webhook_ids', 'api_log_ids'] as $key) {
        expect($unrelatedGraph[$key])->not->toBeEmpty();
    }

    $target = User::query()->findOrFail($seed['integration']->mlhub_user_id);
    $admin = User::query()->create([
        'name' => 'Admin',
        'username' => 'default_data_delete_admin',
        'email' => 'default-data-delete-admin@example.com',
        'password' => 'password-password-password-password-password-password-1234',
        'is_super_admin' => true,
    ]);
    app(DeleteUser::class)->execute($target, $admin->id);

    expect(User::query()->find($target->id))->toBeNull()
        ->and(Team::query()->whereKey($targetGraph['team_id'])->exists())->toBeFalse()
        ->and(DB::table('team_user')->where('user_id', $targetGraph['user_id'])->exists())->toBeFalse()
        ->and(LocalBusiness::query()->whereKey($targetGraph['business_id'])->exists())->toBeFalse()
        ->and(AffiliateProfile::query()->whereIn('id', $targetGraph['affiliate_profile_ids'])->exists())->toBeFalse()
        ->and(PartnerPackageAssignment::query()->whereIn('id', $targetGraph['package_assignment_ids'])->exists())->toBeFalse()
        ->and(PartnerOnboardingStatusHistory::query()->whereIn('id', $targetGraph['history_ids'])->exists())->toBeFalse()
        ->and(SupportTicket::query()->whereIn('id', $targetGraph['support_ticket_ids'])->exists())->toBeFalse()
        ->and(PartnerSupportTicketContext::query()->whereIn('id', $targetGraph['support_context_ids'])->exists())->toBeFalse()
        ->and(CustomerActivity::query()->whereIn('id', $targetGraph['customer_activity_ids'])->exists())->toBeFalse()
        ->and(PartnerWebhookOutbox::query()->whereIn('id', $targetGraph['webhook_ids'])->exists())->toBeFalse()
        ->and(PartnerApiLog::query()->whereIn('id', $targetGraph['api_log_ids'])->exists())->toBeFalse()
        ->and(Customer::query()->whereKey($snapshot['customer']['id'])->exists())->toBeFalse()
        ->and(BookingService::query()->whereKey($snapshot['booking_service']['id'])->exists())->toBeFalse()
        ->and(LoyaltyCard::query()->whereKey($snapshot['loyalty_card']['id'])->exists())->toBeFalse()
        ->and(QrCampaign::query()->whereIn('id', array_column($snapshot['campaigns'], 'id'))->exists())->toBeFalse()
        ->and(LandingPage::query()->whereIn('id', array_column($snapshot['landing_pages'], 'id'))->exists())->toBeFalse()
        ->and(PartnerOnboardingRequest::query()->where('external_business_id', 'biz-admin')->exists())->toBeFalse()
        ->and(PartnerIntegration::query()->where('external_business_id', 'biz-admin')->exists())->toBeFalse()
        ->and(User::query()->whereKey($unrelatedGraph['user_id'])->exists())->toBeTrue()
        ->and(Team::query()->whereKey($unrelatedGraph['team_id'])->exists())->toBeTrue()
        ->and(DB::table('team_user')->where('user_id', $unrelatedGraph['user_id'])->where('team_id', $unrelatedGraph['team_id'])->exists())->toBeTrue()
        ->and(LocalBusiness::query()->whereKey($unrelatedGraph['business_id'])->exists())->toBeTrue()
        ->and(AffiliateProfile::query()->whereIn('id', $unrelatedGraph['affiliate_profile_ids'])->count())->toBe(count($unrelatedGraph['affiliate_profile_ids']))
        ->and(PartnerPackageAssignment::query()->whereIn('id', $unrelatedGraph['package_assignment_ids'])->count())->toBe(count($unrelatedGraph['package_assignment_ids']))
        ->and(PartnerOnboardingStatusHistory::query()->whereIn('id', $unrelatedGraph['history_ids'])->count())->toBe(count($unrelatedGraph['history_ids']))
        ->and(SupportTicket::query()->whereIn('id', $unrelatedGraph['support_ticket_ids'])->count())->toBe(count($unrelatedGraph['support_ticket_ids']))
        ->and(PartnerSupportTicketContext::query()->whereIn('id', $unrelatedGraph['support_context_ids'])->count())->toBe(count($unrelatedGraph['support_context_ids']))
        ->and(CustomerActivity::query()->whereIn('id', $unrelatedGraph['customer_activity_ids'])->count())->toBe(count($unrelatedGraph['customer_activity_ids']))
        ->and(PartnerWebhookOutbox::query()->whereIn('id', $unrelatedGraph['webhook_ids'])->count())->toBe(count($unrelatedGraph['webhook_ids']))
        ->and(PartnerApiLog::query()->whereIn('id', $unrelatedGraph['api_log_ids'])->count())->toBe(count($unrelatedGraph['api_log_ids']))
        ->and(adminDefaultDataSnapshot($unrelatedIntegration->fresh()))->toBe($unrelatedSnapshot);
});

test('adminSetStatus can reactivate a cancelled onboarding request', function (): void {
    $seed = seedAdminOnboarding();
    $service = app(OnboardingAdminService::class);

    $service->transition($seed['onboarding'], OnboardingStatusMachine::CANCELLED, 'admin', 7, 'Cancelled.');
    $reactivated = $service->adminSetStatus(
        $seed['onboarding']->fresh(),
        OnboardingStatusMachine::IN_CONSULTATION,
        7,
        'Reactivated by admin.'
    );

    expect($reactivated->status)->toBe(OnboardingStatusMachine::IN_CONSULTATION)
        ->and($reactivated->current_step)->toBe(OnboardingStatusMachine::IN_CONSULTATION)
        ->and($reactivated->consultant_contacted_at)->not->toBeNull();

    $last = PartnerOnboardingStatusHistory::query()
        ->where('onboarding_request_id', $seed['onboarding']->id)
        ->orderByDesc('id')
        ->first();

    expect($last)->not->toBeNull()
        ->and($last->from_status)->toBe(OnboardingStatusMachine::CANCELLED)
        ->and($last->to_status)->toBe(OnboardingStatusMachine::IN_CONSULTATION)
        ->and(data_get($last->metadata, 'admin_override'))->toBeTrue();
});

test('adminPurgeOnboarding removes partner mapping and keeps the mlhub user', function (): void {
    Storage::fake('local');

    $seed = seedAdminOnboarding();
    $userId = (int) $seed['integration']->mlhub_user_id;
    $onboardingId = (int) $seed['onboarding']->id;
    $integrationId = (int) $seed['integration']->id;

    $ticket = SupportTicket::query()->create([
        'id_secure' => str()->random(32),
        'uid' => $userId,
        'open_by' => $userId,
        'team_id' => $seed['integration']->mlhub_workspace_id,
        'title' => 'FizaHUB onboarding awaiting consultant: biz-admin',
        'content' => '{"summary":"Account provisioned with Free package."}',
        'status' => 1,
        'created' => time(),
        'changed' => time(),
    ]);

    $seed['onboarding']->forceFill(['support_ticket_id' => $ticket->id])->save();

    PartnerSupportTicketContext::query()->create([
        'support_ticket_id' => $ticket->id,
        'partner_integration_id' => $integrationId,
        'external_business_id' => 'biz-admin',
        'request_code' => 'fizahub_onboarding',
        'package_code' => 'free',
        'related_resource_type' => PartnerOnboardingRequest::class,
        'related_resource_id' => (string) $seed['onboarding']->request_id,
        'context' => ['ticket_type' => 'onboarding'],
    ]);

    $attachmentPath = 'partner-fizahub/support/'.$ticket->id.'/proof.txt';
    Storage::disk('local')->put($attachmentPath, 'proof');
    PartnerSupportAttachment::query()->create([
        'support_ticket_id' => $ticket->id,
        'id_secure' => str()->random(32),
        'original_name' => 'proof.txt',
        'mime_type' => 'text/plain',
        'size_bytes' => 5,
        'disk' => 'local',
        'path' => $attachmentPath,
        'uploaded_by_user_id' => $userId,
    ]);

    PartnerWebhookOutbox::query()->create([
        'partner_code' => 'fizahub',
        'event_type' => 'onboarding.status',
        'dedupe_key' => $seed['onboarding']->request_id.'|purge-test',
        'endpoint_path' => '/webhooks/onboarding',
        'payload' => ['external_business_id' => 'biz-admin', 'request_id' => $seed['onboarding']->request_id],
        'status' => 'pending',
        'attempts' => 0,
        'available_at' => now(),
    ]);

    $unrelatedWebhook = PartnerWebhookOutbox::query()->create([
        'partner_code' => 'fizahub',
        'event_type' => 'onboarding.status',
        'dedupe_key' => 'unrelated|purge-test',
        'endpoint_path' => '/webhooks/onboarding',
        'payload' => [
            'external_business_id' => 'biz-unrelated',
            'note' => 'Do not match the biz-admin substring.',
        ],
        'status' => 'pending',
        'attempts' => 0,
        'available_at' => now(),
    ]);

    PartnerApiLog::query()->create([
        'partner_code' => 'fizahub',
        'method' => 'POST',
        'endpoint' => '/api/v1/partners/fizahub/onboarding-requests',
        'request_id' => (string) str()->uuid(),
        'status_code' => 202,
        'request_payload' => ['external_business_id' => 'biz-admin'],
        'response_payload' => ['data' => ['status' => 'accepted']],
    ]);

    $unrelatedApiLog = PartnerApiLog::query()->create([
        'partner_code' => 'fizahub',
        'method' => 'POST',
        'endpoint' => '/api/v1/partners/fizahub/onboarding-requests',
        'request_id' => (string) str()->uuid(),
        'status_code' => 202,
        'request_payload' => ['external_business_id' => 'biz-unrelated'],
        'response_payload' => ['data' => ['external_business_id' => 'biz-unrelated']],
    ]);

    $result = app(OnboardingAdminService::class)->adminPurgeOnboarding($seed['onboarding']->fresh(), 7);

    expect($result['external_business_id'])->toBe('biz-admin')
        ->and(PartnerOnboardingRequest::query()->find($onboardingId))->toBeNull()
        ->and(PartnerIntegration::query()->find($integrationId))->toBeNull()
        ->and(SupportTicket::query()->find($ticket->id))->toBeNull()
        ->and(PartnerWebhookOutbox::query()->find($unrelatedWebhook->id))->not->toBeNull()
        ->and(PartnerApiLog::query()->find($unrelatedApiLog->id))->not->toBeNull()
        ->and(User::query()->find($userId))->not->toBeNull();

    Storage::disk('local')->assertMissing($attachmentPath);
});

test('admin user deletion purges FizaHUB onboarding before deleting owned business data', function (): void {
    Storage::fake('local');

    $seed = seedAdminOnboarding();
    $targetUserId = (int) $seed['integration']->mlhub_user_id;
    $businessId = (int) $seed['integration']->mlhub_business_id;
    $avatarPath = 'avatars/target-user.png';
    $filePath = 'users/'.$targetUserId.'/campaign-export.csv';

    Storage::disk('local')->put($avatarPath, 'avatar');
    Storage::disk('local')->put($filePath, 'campaign');

    User::query()->whereKey($targetUserId)->update([
        'avatar_path' => $avatarPath,
        'avatar_disk' => 'local',
    ]);

    DB::table('files')->insert([
        'owner_user_id' => $targetUserId,
        'disk' => 'local',
        'path' => $filePath,
        'is_folder' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('lb_customers')->insert([
        'user_id' => $targetUserId,
        'business_id' => $businessId,
        'name' => 'Campaign customer',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::query()->create([
        'name' => 'Admin',
        'username' => 'admin_delete',
        'email' => 'admin-delete@example.com',
        'password' => 'password-password-password-password-password-password-1234',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin);

    $index = new UserIndex;
    $index->deleteConfirmation[$targetUserId] = 'XOA USER '.$targetUserId;
    $index->deleteUser($targetUserId);

    expect(User::query()->find($targetUserId))->toBeNull()
        ->and(DB::table('lb_businesses')->where('user_id', $targetUserId)->count())->toBe(0)
        ->and(DB::table('lb_customers')->where('user_id', $targetUserId)->count())->toBe(0)
        ->and(PartnerOnboardingRequest::query()->where('external_business_id', 'biz-admin')->count())->toBe(0)
        ->and(PartnerIntegration::query()->where('external_business_id', 'biz-admin')->count())->toBe(0);

    Storage::disk('local')->assertMissing($avatarPath);
    Storage::disk('local')->assertMissing($filePath);
});

test('admin purge for user returns the exact FizaHUB verification context', function (): void {
    $seed = seedAdminOnboarding();
    $targetUserId = (int) $seed['integration']->mlhub_user_id;
    $requestId = (string) $seed['onboarding']->request_id;

    $result = app(OnboardingAdminService::class)->adminPurgeForUser($targetUserId, 7);

    expect($result)->toMatchArray([
        'businesses_purged' => 1,
        'external_business_ids' => ['biz-admin'],
        'request_ids' => [$requestId],
    ])
        ->and(PartnerOnboardingRequest::query()->where('external_business_id', 'biz-admin')->exists())->toBeFalse()
        ->and(PartnerIntegration::query()->where('external_business_id', 'biz-admin')->exists())->toBeFalse()
        ->and(User::query()->whereKey($targetUserId)->exists())->toBeTrue();
});

test('admin user deletion purges every FizaHUB business for the target and preserves unrelated mappings', function (): void {
    $seed = seedAdminOnboarding();
    $target = User::query()->findOrFail($seed['integration']->mlhub_user_id);
    $secondBusiness = LocalBusiness::query()->create([
        'user_id' => $target->id,
        'name' => 'Owner Second Shop',
        'type' => 'other',
    ]);
    PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'biz-admin-second',
        'mlhub_user_id' => $target->id,
        'mlhub_workspace_id' => $seed['integration']->mlhub_workspace_id,
        'mlhub_business_id' => $secondBusiness->id,
        'package_code' => 'base',
        'status' => 'active',
    ]);
    PartnerOnboardingRequest::query()->create([
        'partner_code' => 'fizahub',
        'request_id' => (string) str()->uuid(),
        'external_business_id' => 'biz-admin-second',
        'package_code' => 'base',
        'requested_package_code' => 'base',
        'status' => OnboardingStatusMachine::READY,
        'current_step' => OnboardingStatusMachine::READY,
        'admin_status' => OnboardingStatusMachine::READY,
        'mlhub_user_id' => $target->id,
        'mlhub_workspace_id' => $seed['integration']->mlhub_workspace_id,
        'mlhub_business_id' => $secondBusiness->id,
    ]);

    $unrelated = User::query()->create([
        'name' => 'Unrelated Owner',
        'username' => 'unrelated_owner',
        'email' => 'unrelated-owner@example.com',
        'password' => 'password-password-password-password-password-password-1234',
    ]);
    $unrelatedBusiness = LocalBusiness::query()->create([
        'user_id' => $unrelated->id,
        'name' => 'Unrelated Shop',
        'type' => 'other',
    ]);
    $unrelatedIntegration = PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'biz-unrelated-owner',
        'mlhub_user_id' => $unrelated->id,
        'mlhub_business_id' => $unrelatedBusiness->id,
        'package_code' => 'free',
        'status' => 'active',
    ]);
    $admin = User::query()->create([
        'name' => 'Admin',
        'username' => 'multi_business_admin',
        'email' => 'multi-business-admin@example.com',
        'password' => 'password-password-password-password-password-password-1234',
        'is_super_admin' => true,
    ]);
    $dashboardCacheKey = 'fizahub:dashboard|fizahub|biz-admin-second|2026-07-01|2026-07-23';
    $dashboardIndexKey = 'fizahub:dashboard:index:'.hash('sha256', 'fizahub|biz-admin-second');
    Cache::put($dashboardCacheKey, ['private' => true], 600);
    Cache::put($dashboardCacheKey.':generated_at', now()->toIso8601String(), 600);
    Cache::put($dashboardCacheKey.':cooldown', true, 600);
    Cache::put($dashboardIndexKey, [$dashboardCacheKey], 600);

    $result = app(DeleteUser::class)->execute($target, $admin->id);

    expect($result->status)->toBe('completed')
        ->and($result->onboardingBusinessesPurged)->toBe(2)
        ->and(PartnerOnboardingRequest::query()->where('mlhub_user_id', $target->id)->count())->toBe(0)
        ->and(PartnerIntegration::query()->where('mlhub_user_id', $target->id)->count())->toBe(0)
        ->and(LocalBusiness::query()->where('user_id', $target->id)->count())->toBe(0)
        ->and(User::query()->find($unrelated->id))->not->toBeNull()
        ->and(PartnerIntegration::query()->find($unrelatedIntegration->id))->not->toBeNull()
        ->and(Cache::has($dashboardCacheKey))->toBeFalse()
        ->and(Cache::has($dashboardCacheKey.':generated_at'))->toBeFalse()
        ->and(Cache::has($dashboardCacheKey.':cooldown'))->toBeFalse()
        ->and(Cache::has($dashboardIndexKey))->toBeFalse();
});

test('FizaHub UserDeletion action removes the resolved MKT user and all onboarding rows', function (): void {
    $seed = seedAdminOnboarding();
    $targetUserId = (int) $seed['onboarding']->mlhub_user_id;
    $admin = User::query()->create([
        'name' => 'Admin',
        'username' => 'fizahub_delete_admin',
        'email' => 'fizahub-delete-admin@example.com',
        'password' => 'password-password-password-password-password-password-1234',
        'is_super_admin' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(FizaHubOnboardingIndex::class)
        ->assertSee('Xóa dữ liệu onboarding')
        ->assertSee('Xóa User và toàn bộ dữ liệu')
        ->assertSee('o***@example.com')
        ->set('userDeleteConfirmation.'.$seed['onboarding']->id, 'XOA USER '.$targetUserId)
        ->call('deleteUserAndData', $seed['onboarding']->id)
        ->assertSet('errorMessage', null);

    expect(User::query()->find($targetUserId))->toBeNull()
        ->and(PartnerOnboardingRequest::query()->where('external_business_id', 'biz-admin')->count())->toBe(0)
        ->and(PartnerIntegration::query()->where('external_business_id', 'biz-admin')->count())->toBe(0);
});

test('FizaHub UserDeletion action refuses ambiguous user mappings', function (): void {
    $seed = seedAdminOnboarding();
    $firstUserId = (int) $seed['onboarding']->mlhub_user_id;
    $other = User::query()->create([
        'name' => 'Conflicting Owner',
        'username' => 'conflicting_owner',
        'email' => 'conflicting-owner@example.com',
        'password' => 'password-password-password-password-password-password-1234',
    ]);
    $admin = User::query()->create([
        'name' => 'Admin',
        'username' => 'ambiguous_admin',
        'email' => 'ambiguous-admin@example.com',
        'password' => 'password-password-password-password-password-password-1234',
        'is_super_admin' => true,
    ]);

    $seed['integration']->forceFill(['mlhub_user_id' => $other->id])->save();

    Livewire::actingAs($admin)
        ->test(FizaHubOnboardingIndex::class)
        ->call('deleteUserAndData', $seed['onboarding']->id)
        ->assertSet('statusMessage', null)
        ->assertSet('errorMessage', __('The onboarding mapping points to multiple MKT users. No account was deleted.'));

    expect(User::query()->find($firstUserId))->not->toBeNull()
        ->and(User::query()->find($other->id))->not->toBeNull()
        ->and(PartnerOnboardingRequest::query()->find($seed['onboarding']->id))->not->toBeNull();
});

test('deleteOnboarding refuses an empty or wrong confirmation and never purges data', function (): void {
    $seed = seedAdminOnboarding();
    $admin = User::query()->create([
        'name' => 'Admin',
        'username' => 'onboarding_confirm_admin',
        'email' => 'onboarding-confirm-admin@example.com',
        'password' => 'password-password-password-password-password-password-1234',
        'is_super_admin' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(FizaHubOnboardingIndex::class)
        ->call('deleteOnboarding', $seed['onboarding']->id)
        ->assertSet('statusMessage', null)
        ->assertSet('errorMessage', __('Type ":phrase" exactly to confirm this deletion.', ['phrase' => 'XOA ONBOARDING']));

    expect(PartnerOnboardingRequest::query()->find($seed['onboarding']->id))->not->toBeNull()
        ->and(PartnerIntegration::query()->find($seed['integration']->id))->not->toBeNull();

    Livewire::actingAs($admin)
        ->test(FizaHubOnboardingIndex::class)
        ->set('onboardingDeleteConfirmation.'.$seed['onboarding']->id, 'xoa onboarding')
        ->call('deleteOnboarding', $seed['onboarding']->id)
        ->assertSet('errorMessage', __('Type ":phrase" exactly to confirm this deletion.', ['phrase' => 'XOA ONBOARDING']));

    expect(PartnerOnboardingRequest::query()->find($seed['onboarding']->id))->not->toBeNull()
        ->and(PartnerIntegration::query()->find($seed['integration']->id))->not->toBeNull();
});

test('deleteOnboarding purges data once the exact confirmation phrase is typed', function (): void {
    $seed = seedAdminOnboarding();
    $onboardingId = (int) $seed['onboarding']->id;
    $admin = User::query()->create([
        'name' => 'Admin',
        'username' => 'onboarding_confirm_exact_admin',
        'email' => 'onboarding-confirm-exact-admin@example.com',
        'password' => 'password-password-password-password-password-password-1234',
        'is_super_admin' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(FizaHubOnboardingIndex::class)
        ->set('onboardingDeleteConfirmation.'.$onboardingId, 'XOA ONBOARDING')
        ->call('deleteOnboarding', $onboardingId)
        ->assertSet('errorMessage', null)
        ->assertSet('statusMessage', 'Đã xóa dữ liệu onboarding FizaHUB. Tài khoản MKT vẫn được giữ lại.');

    expect(PartnerOnboardingRequest::query()->find($onboardingId))->toBeNull()
        ->and(User::query()->find($seed['integration']->mlhub_user_id))->not->toBeNull();
});

test('deleteUserAndData refuses an empty or wrong confirmation and never deletes the MKT user', function (): void {
    $seed = seedAdminOnboarding();
    $targetUserId = (int) $seed['integration']->mlhub_user_id;
    $admin = User::query()->create([
        'name' => 'Admin',
        'username' => 'user_delete_confirm_admin',
        'email' => 'user-delete-confirm-admin@example.com',
        'password' => 'password-password-password-password-password-password-1234',
        'is_super_admin' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(FizaHubOnboardingIndex::class)
        ->call('deleteUserAndData', $seed['onboarding']->id)
        ->assertSet('errorMessage', __('Type ":phrase" exactly to confirm this deletion.', ['phrase' => 'XOA USER '.$targetUserId]));

    expect(User::query()->find($targetUserId))->not->toBeNull();

    Livewire::actingAs($admin)
        ->test(FizaHubOnboardingIndex::class)
        ->set('userDeleteConfirmation.'.$seed['onboarding']->id, 'XOA USER 999999')
        ->call('deleteUserAndData', $seed['onboarding']->id)
        ->assertSet('errorMessage', __('Type ":phrase" exactly to confirm this deletion.', ['phrase' => 'XOA USER '.$targetUserId]));

    expect(User::query()->find($targetUserId))->not->toBeNull()
        ->and(PartnerOnboardingRequest::query()->find($seed['onboarding']->id))->not->toBeNull();
});

test('FizaHub resend webhook action queues delivery without a callback return type error', function (): void {
    Queue::fake();
    config()->set('modules.apipartnerfizahub.webhook_base_url', 'https://fizahub.test');

    $seed = seedAdminOnboarding();
    $admin = User::query()->create([
        'name' => 'Admin',
        'username' => 'fizahub_webhook_admin',
        'email' => 'fizahub-webhook-admin@example.com',
        'password' => 'password-password-password-password-password-password-1234',
        'is_super_admin' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(FizaHubOnboardingIndex::class)
        ->call('resendWebhook', $seed['onboarding']->id)
        ->assertSet('errorMessage', null)
        ->assertSet('statusMessage', __('Webhook re-queued for delivery.'));

    expect(PartnerWebhookOutbox::query()->count())->toBe(1);
});

test('adminAssignPackage upgrades effective package and marks approved', function (): void {
    $seed = seedAdminOnboarding();

    $plan = AdminPlan::query()->create([
        'name' => 'MKT Free Da Nang',
        'slug' => 'mlhub-free-da-nang',
        'status' => true,
        'free_plan' => true,
    ]);

    $service = app(OnboardingAdminService::class);
    $updated = $service->adminAssignPackage($seed['onboarding'], 'base', 7);

    expect($updated->package_code)->toBe('base')
        ->and($updated->approved_package_code)->toBe('base')
        ->and($seed['integration']->fresh()->package_code)->toBe('base')
        ->and(User::query()->find($seed['integration']->mlhub_user_id)?->plan_id)->toBe($plan->id);
});
