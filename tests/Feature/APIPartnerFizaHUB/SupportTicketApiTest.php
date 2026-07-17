<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminSupport\Models\SupportComment;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

require_once __DIR__.'/FizaHubTestHelpers.php';

function createSupportApiTables(): void
{
    Schema::dropIfExists('partner_one_time_logins');
    Schema::dropIfExists('partner_api_logs');
    Schema::dropIfExists('partner_onboarding_requests');
    Schema::dropIfExists('partner_integrations');
    Schema::dropIfExists('support_comments');
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
    Schema::dropIfExists('plans');
    Schema::dropIfExists('audit_logs');

    Schema::create('plans', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->boolean('status')->default(true);
        $table->boolean('featured')->default(false);
        $table->string('currency')->default('VND');
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
        $table->string('username')->nullable()->unique();
        $table->string('email')->unique();
        $table->string('locale', 10)->nullable();
        $table->string('timezone', 100)->nullable();
        $table->unsignedBigInteger('plan_id')->nullable();
        $table->timestamp('plan_started_at')->nullable();
        $table->timestamp('plan_expires_at')->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('referral_code', 20)->nullable();
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
        $table->timestamps();
    });

    Schema::create('support_tickets', function (Blueprint $table): void {
        $table->id();
        $table->string('id_secure', 40)->unique();
        $table->unsignedBigInteger('uid');
        $table->unsignedBigInteger('open_by');
        $table->unsignedBigInteger('team_id')->nullable();
        $table->unsignedBigInteger('cate_id')->nullable();
        $table->unsignedBigInteger('type_id')->nullable();
        $table->string('title', 255);
        $table->text('content');
        $table->unsignedTinyInteger('status')->default(1);
        $table->boolean('pin')->default(false);
        $table->boolean('user_read')->default(false);
        $table->boolean('admin_read')->default(true);
        $table->unsignedInteger('changed')->nullable();
        $table->unsignedInteger('created')->nullable();
    });

    Schema::create('support_comments', function (Blueprint $table): void {
        $table->id();
        $table->string('id_secure', 40)->unique();
        $table->unsignedBigInteger('ticket_id');
        $table->unsignedBigInteger('user_id');
        $table->text('comment');
        $table->unsignedInteger('changed')->nullable();
        $table->unsignedInteger('created')->nullable();
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

    createFizaHubPartnerTables();
}

/**
 * @return array{user: User, team: Team, business: LocalBusiness, integration: PartnerIntegration}
 */
function seedMappedBusiness(string $externalBusinessId, string $email): array
{
    $plan = AdminPlan::query()->firstOrCreate(
        ['slug' => 'mlhub-free-da-nang'],
        [
            'name' => 'MLHUB Free Da Nang',
            'status' => true,
            'free_plan' => true,
            'currency' => 'VND',
            'price' => 0,
            'permissions' => [],
        ]
    );

    $user = User::query()->create([
        'name' => 'Owner '.$externalBusinessId,
        'username' => 'user_'.Str::lower(Str::random(8)),
        'email' => $email,
        'password' => 'password-password-password-password-password-password-1234',
        'plan_id' => $plan->id,
        'locale' => 'vi',
        'timezone' => 'Asia/Ho_Chi_Minh',
    ]);

    $team = Team::query()->create([
        'name' => $user->name.' Team',
        'slug' => 'team-'.$user->id,
        'owner_user_id' => $user->id,
    ]);

    $business = LocalBusiness::query()->create([
        'user_id' => $user->id,
        'name' => 'Shop '.$externalBusinessId,
        'type' => 'Restaurant',
    ]);

    $integration = PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => $externalBusinessId,
        'external_user_id' => 'ext-'.$externalBusinessId,
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
        'package_code' => 'base',
        'status' => 'active',
    ]);

    return compact('user', 'team', 'business', 'integration');
}

function supportHeaders(array $overrides = []): array
{
    return array_merge([
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) str()->uuid(),
        'Idempotency-Key' => (string) str()->uuid(),
        'Accept' => 'application/json',
    ], $overrides);
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);
    createSupportApiTables();
});

afterEach(function (): void {
    dropFizaHubPartnerTables();
    Schema::dropIfExists('support_comments');
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
    Schema::dropIfExists('plans');
    Schema::dropIfExists('audit_logs');
});

test('create support ticket requires idempotency and persists mapped ownership', function (): void {
    seedMappedBusiness('biz-a', 'a@example.com');

    $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-a/support-tickets',
        ['subject' => 'Need help', 'message' => 'Please check my campaign'],
        supportHeaders(['Idempotency-Key' => ''])
    )
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'validation_failed');

    $response = $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-a/support-tickets',
        ['subject' => 'Need help', 'message' => 'Please check my campaign'],
        supportHeaders()
    )->assertCreated();

    $ticketId = $response->json('data.ticket_id');
    expect($ticketId)->toBeString()->not->toBeEmpty();
    expect($response->json())->not->toHaveKey('data.id');

    $ticket = SupportTicket::query()->where('id_secure', $ticketId)->firstOrFail();
    $integration = PartnerIntegration::query()->where('external_business_id', 'biz-a')->firstOrFail();

    expect($ticket->uid)->toBe($integration->mlhub_user_id)
        ->and($ticket->open_by)->toBe($integration->mlhub_user_id)
        ->and($ticket->team_id)->toBe($integration->mlhub_workspace_id)
        ->and($ticket->status)->toBe(1)
        ->and($ticket->user_read)->toBeFalse()
        ->and($ticket->admin_read)->toBeTrue()
        ->and($ticket->created)->toBeInt()
        ->and($ticket->changed)->toBeInt()
        ->and($response->json('data.status'))->toBe('open')
        ->and($response->json('data.unread_by_business'))->toBeTrue();
});

test('list support tickets is scoped to mapped business only', function (): void {
    seedMappedBusiness('biz-a', 'a@example.com');
    seedMappedBusiness('biz-b', 'b@example.com');

    $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-a/support-tickets',
        ['subject' => 'Ticket A', 'message' => 'Body A'],
        supportHeaders()
    )->assertCreated();

    $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-b/support-tickets',
        ['subject' => 'Ticket B', 'message' => 'Body B'],
        supportHeaders()
    )->assertCreated();

    $listA = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-a/support-tickets',
        supportHeaders()
    )->assertOk();

    expect($listA->json('data.items'))->toHaveCount(1)
        ->and($listA->json('data.items.0.subject'))->toBe('Ticket A')
        ->and($listA->json('data.pagination.per_page'))->toBe(20);
});

test('detail returns initial message and supports since polling', function (): void {
    seedMappedBusiness('biz-a', 'a@example.com');

    $create = $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-a/support-tickets',
        ['subject' => 'Hello', 'message' => 'Initial body'],
        supportHeaders()
    )->assertCreated();

    $ticketId = $create->json('data.ticket_id');
    $integration = PartnerIntegration::query()->where('external_business_id', 'biz-a')->firstOrFail();

    $admin = User::query()->create([
        'name' => 'Admin',
        'username' => 'adminuser',
        'email' => 'admin@example.com',
        'password' => 'password-password-password-password-password-password-1234',
        'is_super_admin' => true,
    ]);

    $ticket = SupportTicket::query()->where('id_secure', $ticketId)->firstOrFail();

    SupportComment::query()->create([
        'id_secure' => Str::random(32),
        'ticket_id' => $ticket->id,
        'user_id' => $admin->id,
        'comment' => 'Admin reply here',
        'created' => time() + 10,
        'changed' => time() + 10,
    ]);

    $detail = $this->getJson(
        '/api/v1/partners/fizahub/support-tickets/'.$ticketId.'?external_business_id=biz-a',
        supportHeaders()
    )->assertOk();

    expect($detail->json('data.messages.0.message_id'))->toBe($ticketId.':initial')
        ->and($detail->json('data.messages.0.sender_type'))->toBe('business')
        ->and($detail->json('data.messages.0.body'))->toBe('Initial body')
        ->and($detail->json('data.messages.1.sender_type'))->toBe('admin')
        ->and($detail->json('data.next_poll_after_seconds'))->toBe(15);

    $since = urlencode((string) $detail->json('data.messages.0.created_at'));

    $polled = $this->getJson(
        '/api/v1/partners/fizahub/support-tickets/'.$ticketId.'?external_business_id=biz-a&since='.$since,
        supportHeaders()
    )->assertOk();

    expect($polled->json('data.messages'))->toHaveCount(1)
        ->and($polled->json('data.messages.0.sender_type'))->toBe('admin');

    unset($integration);
});

test('partner message strips html and rejects closed tickets', function (): void {
    seedMappedBusiness('biz-a', 'a@example.com');

    $ticketId = $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-a/support-tickets',
        ['subject' => 'HTML', 'message' => 'Start'],
        supportHeaders()
    )->json('data.ticket_id');

    $this->postJson(
        '/api/v1/partners/fizahub/support-tickets/'.$ticketId.'/messages?external_business_id=biz-a',
        ['message' => '<p>Hello <b>world</b></p>'],
        supportHeaders()
    )
        ->assertCreated()
        ->assertJsonPath('data.sender_type', 'business')
        ->assertJsonPath('data.body', 'Hello world');

    $comment = SupportComment::query()->latest('id')->firstOrFail();
    expect($comment->comment)->toBe('Hello world')
        ->and($comment->id_secure)->toBeString();

    $ticket = SupportTicket::query()->where('id_secure', $ticketId)->firstOrFail();
    expect($ticket->admin_read)->toBeTrue()
        ->and($ticket->user_read)->toBeFalse();

    $ticket->forceFill(['status' => 2])->save();

    $this->postJson(
        '/api/v1/partners/fizahub/support-tickets/'.$ticketId.'/messages?external_business_id=biz-a',
        ['message' => 'Should fail'],
        supportHeaders()
    )
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'ticket_not_open');
});

test('tenant isolation blocks cross business ticket access', function (): void {
    seedMappedBusiness('biz-a', 'a@example.com');
    seedMappedBusiness('biz-b', 'b@example.com');

    $ticketA = $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-a/support-tickets',
        ['subject' => 'A only', 'message' => 'secret'],
        supportHeaders()
    )->json('data.ticket_id');

    $this->getJson(
        '/api/v1/partners/fizahub/support-tickets/'.$ticketA.'?external_business_id=biz-b',
        supportHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'resource_not_found');

    $this->postJson(
        '/api/v1/partners/fizahub/support-tickets/'.$ticketA.'/messages?external_business_id=biz-b',
        ['message' => 'intrusion'],
        supportHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'resource_not_found');
});

test('onboarding review ticket remains visible to admin with unknown user', function (): void {
    $ticket = SupportTicket::query()->create([
        'id_secure' => Str::random(32),
        'uid' => 0,
        'open_by' => 0,
        'team_id' => null,
        'title' => 'FizaHUB onboarding pending verification: x',
        'content' => '{"summary":"pending"}',
        'status' => 1,
        'pin' => false,
        'user_read' => false,
        'admin_read' => true,
        'created' => time(),
        'changed' => time(),
    ]);

    $ticket->load('user');

    expect($ticket->user)->toBeNull()
        ->and($ticket->user?->name ?: 'Unknown user')->toBe('Unknown user')
        ->and(SupportTicket::query()->where('id_secure', $ticket->id_secure)->exists())->toBeTrue();
});

test('message endpoint requires idempotency key', function (): void {
    seedMappedBusiness('biz-a', 'a@example.com');

    $ticketId = $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-a/support-tickets',
        ['subject' => 'X', 'message' => 'Y'],
        supportHeaders()
    )->json('data.ticket_id');

    $this->postJson(
        '/api/v1/partners/fizahub/support-tickets/'.$ticketId.'/messages?external_business_id=biz-a',
        ['message' => 'No key'],
        supportHeaders(['Idempotency-Key' => ''])
    )
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'validation_failed');
});
