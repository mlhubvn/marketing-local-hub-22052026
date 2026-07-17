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
        ->and($final->current_step)->toBe('completed')
        ->and($final->consultant_contacted_at)->not->toBeNull()
        ->and($final->ready_at)->not->toBeNull()
        ->and($final->completed_at)->not->toBeNull();

    $histories = PartnerOnboardingStatusHistory::query()
        ->where('onboarding_request_id', $seed['onboarding']->id)
        ->orderBy('id')
        ->pluck('to_status')
        ->all();

    expect($histories)->toBe([
        'consulting',
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
