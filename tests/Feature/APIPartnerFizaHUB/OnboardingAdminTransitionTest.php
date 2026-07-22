<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingStatusHistory;
use Modules\APIPartnerFizaHUB\Services\OnboardingAdminService;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

require_once __DIR__.'/FizaHubTestHelpers.php';

function createAdminTransitionTables(): void
{
    dropFizaHubPartnerTables();
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
        $table->boolean('free_plan')->default(false);
        $table->json('permissions')->nullable();
        $table->timestamps();
    });

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('username')->nullable()->unique();
        $table->string('email')->unique();
        $table->unsignedBigInteger('plan_id')->nullable();
        $table->timestamp('plan_started_at')->nullable();
        $table->timestamp('plan_expires_at')->nullable();
        $table->timestamp('email_verified_at')->nullable();
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
        $table->unsignedBigInteger('user_id');
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

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.webhook_base_url', '');
    createAdminTransitionTables();
});

afterEach(function (): void {
    dropFizaHubPartnerTables();
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
    $seed = seedAdminOnboarding();
    $userId = (int) $seed['integration']->mlhub_user_id;
    $onboardingId = (int) $seed['onboarding']->id;
    $integrationId = (int) $seed['integration']->id;

    $ticket = \Modules\AdminSupport\Models\SupportTicket::query()->create([
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

    \Modules\APIPartnerFizaHUB\Models\PartnerSupportTicketContext::query()->create([
        'support_ticket_id' => $ticket->id,
        'partner_integration_id' => $integrationId,
        'external_business_id' => 'biz-admin',
        'request_code' => 'fizahub_onboarding',
        'package_code' => 'free',
        'related_resource_type' => PartnerOnboardingRequest::class,
        'related_resource_id' => (string) $seed['onboarding']->request_id,
        'context' => ['ticket_type' => 'onboarding'],
    ]);

    \Modules\APIPartnerFizaHUB\Models\PartnerWebhookOutbox::query()->create([
        'partner_code' => 'fizahub',
        'event_type' => 'onboarding.status',
        'dedupe_key' => $seed['onboarding']->request_id.'|purge-test',
        'endpoint_path' => '/webhooks/onboarding',
        'payload' => ['external_business_id' => 'biz-admin', 'request_id' => $seed['onboarding']->request_id],
        'status' => 'pending',
        'attempts' => 0,
        'available_at' => now(),
    ]);

    $result = app(OnboardingAdminService::class)->adminPurgeOnboarding($seed['onboarding']->fresh(), 7);

    expect($result['external_business_id'])->toBe('biz-admin')
        ->and(PartnerOnboardingRequest::query()->find($onboardingId))->toBeNull()
        ->and(\Modules\APIPartnerFizaHUB\Models\PartnerIntegration::query()->find($integrationId))->toBeNull()
        ->and(\Modules\AdminSupport\Models\SupportTicket::query()->find($ticket->id))->toBeNull()
        ->and(\Modules\APIPartnerFizaHUB\Models\PartnerWebhookOutbox::query()->count())->toBe(0)
        ->and(\Modules\AdminUser\Models\User::query()->find($userId))->not->toBeNull();
});

test('adminAssignPackage upgrades effective package and marks approved', function (): void {
    $seed = seedAdminOnboarding();

    $plan = \Modules\AdminPlans\Models\AdminPlan::query()->create([
        'name' => 'MLHUB Free Da Nang',
        'slug' => 'mlhub-free-da-nang',
        'status' => true,
        'free_plan' => true,
    ]);

    $service = app(OnboardingAdminService::class);
    $updated = $service->adminAssignPackage($seed['onboarding'], 'base', 7);

    expect($updated->package_code)->toBe('base')
        ->and($updated->approved_package_code)->toBe('base')
        ->and($seed['integration']->fresh()->package_code)->toBe('base')
        ->and(\Modules\AdminUser\Models\User::query()->find($seed['integration']->mlhub_user_id)?->plan_id)->toBe($plan->id);
});
