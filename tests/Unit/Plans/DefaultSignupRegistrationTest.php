<?php

use Database\Seeders\PlanSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery\MockInterface;
use Modules\AdminPlans\Support\DefaultSignupPlanResolver;
use Modules\AdminUser\Actions\Fortify\CreateNewUser;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\AdminUser\Support\PersonalTeamProvisioner;
use Modules\AppAffiliate\Models\AffiliateProfile;
use Modules\AppAffiliate\Support\AffiliateService;

function createRegistrationTables(): void
{
    Schema::create('plans', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->string('status')->default('active');
        $table->boolean('featured')->default(false);
        $table->string('currency')->default('USD');
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
        $table->string('username')->unique();
        $table->string('email')->unique();
        $table->string('timezone')->nullable();
        $table->unsignedBigInteger('plan_id')->nullable();
        $table->timestamp('plan_started_at')->nullable();
        $table->timestamp('plan_expires_at')->nullable();
        $table->string('referral_code')->nullable();
        $table->unsignedBigInteger('referred_by_user_id')->nullable();
        $table->string('password');
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
        $table->string('area')->default('admin');
        $table->string('ip_address')->nullable();
        $table->text('user_agent')->nullable();
        $table->json('metadata')->nullable();
        $table->timestamps();
    });
}

function registrationAction(): CreateNewUser
{
    $affiliate = mock(AffiliateService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('generateReferralCode')->andReturn('NEWUSER1234');
        $mock->shouldReceive('referredByUserIdFromSession')->andReturn(null);
        $mock->shouldReceive('ensureProfile')->andReturn(new AffiliateProfile);
    });

    $teams = mock(PersonalTeamProvisioner::class, function (MockInterface $mock): void {
        $mock->shouldReceive('ensureForUser')->andReturn(new Team(['name' => 'New User Team']));
    });

    return new CreateNewUser($affiliate, $teams, app(DefaultSignupPlanResolver::class));
}

function validRegistrationInput(string $username): array
{
    return [
        'name' => 'New User',
        'username' => $username,
        'email' => $username.'@example.com',
        'timezone' => 'Asia/Ho_Chi_Minh',
        'accept_terms' => true,
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];
}

test('new registration receives the official default signup plan', function (): void {
    createRegistrationTables();
    (new PlanSeeder)->run();

    $user = registrationAction()->create(validRegistrationInput('withplan'));

    expect($user->plan_id)->toBe(
        (int) app(DefaultSignupPlanResolver::class)->resolve()?->id,
    )->and($user->plan_started_at)->not->toBeNull();
});

test('new registration succeeds when no default signup plan exists', function (): void {
    createRegistrationTables();

    $user = registrationAction()->create(validRegistrationInput('withoutplan'));

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->plan_id)->toBeNull()
        ->and($user->plan_started_at)->toBeNull();
});
