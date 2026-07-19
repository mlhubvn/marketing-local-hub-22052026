<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

require_once __DIR__.'/FizaHubTestHelpers.php';

function createBusinessProfileApiTables(): void
{
    Schema::dropIfExists('partner_one_time_logins');
    Schema::dropIfExists('partner_api_logs');
    Schema::dropIfExists('partner_onboarding_requests');
    Schema::dropIfExists('partner_integrations');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('username')->nullable()->unique();
        $table->string('email')->unique();
        $table->string('locale', 10)->nullable();
        $table->string('timezone', 100)->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->boolean('is_super_admin')->default(false);
        $table->timestamps();
    });

    Schema::create('teams', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('slug');
        $table->text('description')->nullable();
        $table->unsignedBigInteger('owner_user_id')->nullable();
        $table->timestamps();
    });

    Schema::create('team_user', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id');
        $table->unsignedBigInteger('user_id');
        $table->string('role', 50)->default('member');
        $table->json('permissions')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_businesses', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('name');
        $table->string('type', 60)->default('other');
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->string('website')->nullable();
        $table->text('address')->nullable();
        $table->timestamps();
    });

    createFizaHubPartnerTables();
}

/**
 * @return array{user: User, integration: PartnerIntegration, business: LocalBusiness}
 */
function seedProfileBusiness(): array
{
    $user = User::query()->create([
        'name' => 'Original Owner',
        'username' => 'profile_'.Str::lower(Str::random(8)),
        'email' => 'profile-owner@example.com',
        'password' => 'password-password-password-password-password-password-1234',
        'locale' => 'vi',
        'timezone' => 'Asia/Ho_Chi_Minh',
    ]);

    $team = Team::query()->create([
        'name' => 'Profile Team',
        'slug' => 'team-profile',
        'owner_user_id' => $user->id,
    ]);

    $business = LocalBusiness::query()->create([
        'user_id' => $user->id,
        'name' => 'Original Store Name',
        'type' => 'Restaurant',
        'phone' => '0900 000 000',
        'address' => 'Old address',
    ]);

    $integration = PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'biz-profile',
        'external_user_id' => 'ext-profile',
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
        'package_code' => 'free',
        'status' => 'active',
    ]);

    return compact('user', 'integration', 'business');
}

function profileHeaders(array $overrides = []): array
{
    return array_merge([
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) str()->uuid(),
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ], $overrides);
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);
    createBusinessProfileApiTables();
});

afterEach(function (): void {
    dropFizaHubPartnerTables();
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
});

test('profile update accepts the canonical nested owner/business schema', function (): void {
    $seed = seedProfileBusiness();

    $response = $this->patchJson(
        '/api/v1/partners/fizahub/businesses/biz-profile/profile',
        [
            'owner' => ['name' => 'Nguyen Van A'],
            'business' => [
                'name' => 'Fiza Demo Store - Com Tam Da Nang',
                'phone' => '0901 234 567',
                'address' => '123 Le Duan, Da Nang',
            ],
        ],
        profileHeaders()
    )->assertOk();

    $response->assertJsonPath('data.external_business_id', 'biz-profile')
        ->assertJsonMissingPath('data.deprecation_notice');

    expect($seed['user']->refresh()->name)->toBe('Nguyen Van A')
        ->and($seed['business']->refresh()->name)->toBe('Fiza Demo Store - Com Tam Da Nang')
        ->and($seed['business']->refresh()->phone)->toBe('0901 234 567')
        ->and($seed['business']->refresh()->address)->toBe('123 Le Duan, Da Nang');
});

test('profile update normalizes a flat top-level name/phone/address body into business and adds a deprecation notice', function (): void {
    $seed = seedProfileBusiness();

    $response = $this->patchJson(
        '/api/v1/partners/fizahub/businesses/biz-profile/profile',
        [
            'name' => 'Fiza Demo Store - Com Tam Da Nang',
            'phone' => '0901 234 567',
            'address' => '123 Le Duan, Da Nang',
        ],
        profileHeaders()
    )->assertOk();

    $response->assertJsonPath('data.external_business_id', 'biz-profile');
    expect((string) $response->json('data.deprecation_notice'))->not->toBe('');

    expect($seed['business']->refresh()->name)->toBe('Fiza Demo Store - Com Tam Da Nang')
        ->and($seed['business']->refresh()->phone)->toBe('0901 234 567')
        ->and($seed['business']->refresh()->address)->toBe('123 Le Duan, Da Nang');
});

test('profile update returns 404 for an unmapped business', function (): void {
    $this->patchJson(
        '/api/v1/partners/fizahub/businesses/unknown-profile/profile',
        ['business' => ['name' => 'Anything']],
        profileHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'integration_not_found');
});
