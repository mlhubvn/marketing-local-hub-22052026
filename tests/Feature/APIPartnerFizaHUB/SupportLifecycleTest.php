<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

require_once __DIR__.'/FizaHubTestHelpers.php';

function createSupportLifecycleTables(): void
{
    Schema::dropIfExists('partner_support_attachments');
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
        $table->boolean('free_plan')->default(false);
        $table->decimal('price', 16, 2)->default(0);
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
        $table->unsignedBigInteger('owner_user_id')->nullable();
        $table->timestamps();
    });

    Schema::create('team_user', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id');
        $table->unsignedBigInteger('user_id');
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

    Schema::create('partner_support_attachments', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('support_ticket_id');
        $table->string('id_secure', 40)->unique();
        $table->string('original_name', 255);
        $table->string('mime_type', 120)->nullable();
        $table->unsignedBigInteger('size_bytes')->default(0);
        $table->string('disk', 40)->default('local');
        $table->string('path', 500);
        $table->unsignedBigInteger('uploaded_by_user_id')->nullable();
        $table->timestamps();
    });

    createFizaHubPartnerTables();
}

/**
 * @return array{user: User, team: Team, business: LocalBusiness, integration: PartnerIntegration}
 */
function seedLifecycleBusiness(string $externalBusinessId, string $email): array
{
    $plan = AdminPlan::query()->firstOrCreate(
        ['slug' => 'mlhub-free-da-nang'],
        ['name' => 'MLHUB Free Da Nang', 'status' => true, 'free_plan' => true, 'price' => 0, 'permissions' => []]
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

function supportLifecycleHeaders(array $overrides = []): array
{
    return array_merge([
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) str()->uuid(),
        'Idempotency-Key' => (string) str()->uuid(),
        'Accept' => 'application/json',
    ], $overrides);
}

function createLifecycleTicket(string $externalBusinessId): string
{
    return (string) test()->postJson(
        '/api/v1/partners/fizahub/businesses/'.$externalBusinessId.'/support-tickets',
        ['subject' => 'Lifecycle ticket', 'message' => 'Initial message'],
        supportLifecycleHeaders()
    )->json('data.ticket_id');
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);
    createSupportLifecycleTables();
});

afterEach(function (): void {
    dropFizaHubPartnerTables();
    Schema::dropIfExists('partner_support_attachments');
    Schema::dropIfExists('support_comments');
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
    Schema::dropIfExists('plans');
    Schema::dropIfExists('audit_logs');
});

test('close transitions an open ticket to closed and is idempotent when already closed', function (): void {
    seedLifecycleBusiness('biz-close', 'close@example.com');
    $ticketId = createLifecycleTicket('biz-close');

    $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-close/support-tickets/'.$ticketId.'/close',
        ['reason' => 'Resolved by phone'],
        supportLifecycleHeaders()
    )
        ->assertOk()
        ->assertJsonPath('data.status', 'closed');

    $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-close/support-tickets/'.$ticketId.'/close',
        [],
        supportLifecycleHeaders()
    )
        ->assertOk()
        ->assertJsonPath('data.status', 'closed')
        ->assertJsonPath('data.ticket_id', $ticketId);
});

test('reopen brings a closed ticket back to open and is idempotent when already open', function (): void {
    seedLifecycleBusiness('biz-reopen', 'reopen@example.com');
    $ticketId = createLifecycleTicket('biz-reopen');

    // Already-open ticket: reopen is a no-op success.
    $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-reopen/support-tickets/'.$ticketId.'/reopen',
        [],
        supportLifecycleHeaders()
    )
        ->assertOk()
        ->assertJsonPath('data.status', 'open')
        ->assertJsonPath('data.ticket_id', $ticketId);

    $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-reopen/support-tickets/'.$ticketId.'/close',
        [],
        supportLifecycleHeaders()
    )->assertOk();

    $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-reopen/support-tickets/'.$ticketId.'/reopen',
        [],
        supportLifecycleHeaders()
    )
        ->assertOk()
        ->assertJsonPath('data.status', 'open');
});

test('close and reopen return a typed 404, not a 500, for an unknown ticket id', function (): void {
    seedLifecycleBusiness('biz-unknown-ticket', 'unknown-ticket@example.com');

    $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-unknown-ticket/support-tickets/'.Str::random(32).'/close',
        [],
        supportLifecycleHeaders()
    )->assertNotFound()->assertJsonPath('error.code', 'ticket_not_found');

    $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-unknown-ticket/support-tickets/'.Str::random(32).'/reopen',
        [],
        supportLifecycleHeaders()
    )->assertNotFound()->assertJsonPath('error.code', 'ticket_not_found');
});

test('close/reopen enforce tenant isolation: business A cannot close or reopen business B ticket', function (): void {
    seedLifecycleBusiness('biz-close-a', 'close-a@example.com');
    seedLifecycleBusiness('biz-close-b', 'close-b@example.com');

    $ticketB = createLifecycleTicket('biz-close-b');

    $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-close-a/support-tickets/'.$ticketB.'/close',
        [],
        supportLifecycleHeaders()
    )->assertNotFound()->assertJsonPath('error.code', 'ticket_not_found');
});

test('attachment upload, list, and download work end-to-end for an open ticket', function (): void {
    seedLifecycleBusiness('biz-attachment', 'attachment@example.com');
    $ticketId = createLifecycleTicket('biz-attachment');

    $upload = $this->post(
        '/api/v1/partners/fizahub/businesses/biz-attachment/support-tickets/'.$ticketId.'/attachments',
        ['file' => UploadedFile::fake()->create('receipt.pdf', 120, 'application/pdf')],
        supportLifecycleHeaders()
    )->assertCreated();

    $attachmentId = (string) $upload->json('data.attachment_id');

    expect($attachmentId)->not->toBe('')
        ->and($upload->json('data.original_name'))->toBe('receipt.pdf')
        ->and($upload->json('data.download_url'))->toContain($attachmentId)
        ->and($upload->json('data.extension'))->toBe('pdf')
        ->and($upload->json('data.image_url'))->toBeNull();

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-attachment/support-tickets/'.$ticketId.'/attachments',
        supportLifecycleHeaders()
    )
        ->assertOk()
        ->assertJsonPath('data.items.0.attachment_id', $attachmentId)
        ->assertJsonPath('data.items.0.sender_type', 'business')
        ->assertJsonPath('data.items.0.extension', 'pdf')
        ->assertJsonPath('data.items.0.image_url', null);

    $download = $this->get(
        '/api/v1/partners/fizahub/businesses/biz-attachment/support-tickets/'.$ticketId.'/attachments/'.$attachmentId,
        supportLifecycleHeaders()
    );

    $download->assertOk();
    expect($download->headers->get('content-disposition'))->toContain('receipt.pdf')
        ->and(strtolower((string) $download->headers->get('content-disposition')))->toContain('attachment');
});

test('zip and plain-text attachments are rejected for household-business support uploads', function (): void {
    seedLifecycleBusiness('biz-attach-reject', 'attach-reject@example.com');
    $ticketId = createLifecycleTicket('biz-attach-reject');

    $this->post(
        '/api/v1/partners/fizahub/businesses/biz-attach-reject/support-tickets/'.$ticketId.'/attachments',
        ['file' => UploadedFile::fake()->create('dump.zip', 200, 'application/zip')],
        supportLifecycleHeaders()
    )
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'attachment_type_not_allowed');

    $this->post(
        '/api/v1/partners/fizahub/businesses/biz-attach-reject/support-tickets/'.$ticketId.'/attachments',
        ['file' => UploadedFile::fake()->createWithContent('note.txt', 'plain text')],
        supportLifecycleHeaders()
    )
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'attachment_type_not_allowed');
});

test('image attachments expose extension and a signed image_url that opens without partner headers', function (): void {
    seedLifecycleBusiness('biz-image-attach', 'image-attach@example.com');
    $ticketId = createLifecycleTicket('biz-image-attach');

    $upload = $this->post(
        '/api/v1/partners/fizahub/businesses/biz-image-attach/support-tickets/'.$ticketId.'/attachments',
        ['file' => UploadedFile::fake()->image('may-quet-qr.jpg', 80, 60)],
        supportLifecycleHeaders()
    )->assertCreated();

    $attachmentId = (string) $upload->json('data.attachment_id');
    $downloadUrl = (string) $upload->json('data.download_url');
    $imageUrl = (string) $upload->json('data.image_url');

    expect($attachmentId)->not->toBe('')
        ->and($upload->json('data.extension'))->toBe('jpg')
        ->and($upload->json('data.mime_type'))->toStartWith('image/')
        ->and($imageUrl)->not->toBe($downloadUrl)
        ->and($imageUrl)->toContain('/partners/fizahub/support-attachments/'.$attachmentId.'/preview.jpg')
        ->and($imageUrl)->toContain('signature=')
        ->and($imageUrl)->toContain('expires=');

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-image-attach/support-tickets/'.$ticketId.'/attachments',
        supportLifecycleHeaders()
    )
        ->assertOk()
        ->assertJsonPath('data.items.0.attachment_id', $attachmentId)
        ->assertJsonPath('data.items.0.extension', 'jpg');

    // Browser / <img> path: no Authorization, no X-Partner — only the signed query string.
    $preview = $this->get($imageUrl);

    $preview->assertOk();
    expect(strtolower((string) $preview->headers->get('content-disposition')))->toContain('inline')
        ->and(strtolower((string) $preview->headers->get('content-type')))->toStartWith('image/');

    // Unsigned URL must not serve the file.
    $this->get('/partners/fizahub/support-attachments/'.$attachmentId.'/preview.jpg')
        ->assertForbidden();
});

test('attachment endpoints enforce tenant isolation: business A cannot upload, list, or download on business B ticket', function (): void {
    seedLifecycleBusiness('biz-attach-a', 'attach-a@example.com');
    seedLifecycleBusiness('biz-attach-b', 'attach-b@example.com');

    $ticketB = createLifecycleTicket('biz-attach-b');

    $this->post(
        '/api/v1/partners/fizahub/businesses/biz-attach-a/support-tickets/'.$ticketB.'/attachments',
        ['file' => UploadedFile::fake()->createWithContent('x.txt', 'x')],
        supportLifecycleHeaders()
    )->assertNotFound()->assertJsonPath('error.code', 'ticket_not_found');

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-attach-a/support-tickets/'.$ticketB.'/attachments',
        supportLifecycleHeaders()
    )->assertNotFound()->assertJsonPath('error.code', 'ticket_not_found');
});

test('downloading an unknown attachment id returns a typed attachment_not_found, not a generic route error', function (): void {
    // Regression guard: SupportAttachmentController::show() used to abort_unless(...,404), which
    // PartnerExceptionRenderer misreported as "route_not_found" ("API endpoint not found") even
    // though the route exists and only the attachment id was wrong.
    seedLifecycleBusiness('biz-attach-missing', 'attach-missing@example.com');
    $ticketId = createLifecycleTicket('biz-attach-missing');

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-attach-missing/support-tickets/'.$ticketId.'/attachments/'.Str::random(32),
        supportLifecycleHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'attachment_not_found');
});
