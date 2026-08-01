<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminSupport\Models\SupportComment;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportTicketContext;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

require_once __DIR__.'/FizaHubTestHelpers.php';

/**
 * @return array{user: User, team: Team, business: LocalBusiness, integration: PartnerIntegration}
 */
function seedSupportUiBusiness(string $externalBusinessId, string $email): array
{
    $plan = AdminPlan::query()->firstOrCreate(
        ['slug' => 'mlhub-free-da-nang'],
        [
            'name' => 'MKT Free Da Nang',
            'status' => true,
            'free_plan' => true,
            'currency' => 'VND',
            'price' => 0,
            'permissions' => [],
        ]
    );

    $user = User::query()->create([
        'name' => 'Owner '.$externalBusinessId,
        'username' => 'support'.Str::lower(Str::random(10)),
        'email' => $email,
        'password' => 'password-password-password-password-password-password-1234',
        'plan_id' => $plan->id,
        'locale' => 'vi',
        'timezone' => 'Asia/Ho_Chi_Minh',
    ]);
    $team = Team::query()->create([
        'name' => 'Team '.$externalBusinessId,
        'slug' => 'team-'.$externalBusinessId,
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
        'package_code' => 'free',
        'status' => 'active',
    ]);

    return compact('user', 'team', 'business', 'integration');
}

function supportUiHeaders(?string $idempotencyKey = null): array
{
    return [
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) Str::uuid(),
        'Idempotency-Key' => $idempotencyKey ?? (string) Str::uuid(),
        'Accept' => 'application/json',
    ];
}

function createSupportUiOnboardingTicket(array $mapping): SupportTicket
{
    $ticket = SupportTicket::query()->create([
        'id_secure' => Str::random(32),
        'uid' => $mapping['user']->id,
        'open_by' => $mapping['user']->id,
        'team_id' => $mapping['team']->id,
        'title' => 'FizaHUB onboarding',
        'content' => 'Chờ tư vấn viên liên hệ.',
        'status' => 1,
        'pin' => false,
        'user_read' => false,
        'admin_read' => true,
        'created' => time(),
        'changed' => time(),
    ]);

    PartnerSupportTicketContext::query()->create([
        'support_ticket_id' => $ticket->id,
        'partner_integration_id' => $mapping['integration']->id,
        'external_business_id' => $mapping['integration']->external_business_id,
        'request_code' => 'fizahub_onboarding',
        'package_code' => 'free',
        'context' => [
            'ticket_type' => 'onboarding',
            'source' => 'fizahub',
            'preset_code' => 'fizahub_onboarding',
        ],
    ]);

    return $ticket;
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 200);
    bootProductionLikeSchema();
});

afterEach(function (): void {
    dropFizaHubPartnerTables();
    Schema::dropIfExists('affiliate_profiles');
    Schema::dropIfExists('support_comments');
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
    Schema::dropIfExists('plans');
});

test('support presets expose SOP metadata without attachment promises', function (): void {
    seedSupportUiBusiness('biz-presets', 'presets@example.com');

    $response = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-presets/support-presets',
        supportUiHeaders()
    )->assertOk()->assertJsonStructure(['data' => ['items' => [[
        'preset_code',
        'subject',
        'subject_locked',
        'description',
        'ticket_type',
        'required_context',
        'sla_hours',
        'response_channels',
        'requires_campaign',
    ]]]]);

    expect(collect($response->json('data.items'))->pluck('preset_code')->all())
        ->toContain('qr_scan_not_recorded', 'growth_recommendation', 'campaign_request', 'package_upgrade')
        ->not->toContain('fizahub_onboarding')
        ->and(strtolower((string) json_encode($response->json())))->not->toContain('attachment');
});

test('ticket list includes onboarding and user tickets with summary filters and cursor pagination', function (): void {
    $mapping = seedSupportUiBusiness('biz-list', 'list@example.com');
    createSupportUiOnboardingTicket($mapping);

    $created = $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-list/support-tickets',
        [
            'subject' => 'QR chưa đồng bộ',
            'message' => 'Dashboard chưa nhận lượt quét.',
            'response_channel' => 'in_app',
            'metadata' => ['screen' => 'growth_marketing'],
        ],
        supportUiHeaders()
    )->assertCreated();

    $response = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-list/support-tickets?q=QR&status=open&per_page=1',
        supportUiHeaders()
    )->assertOk()->assertJsonStructure(['data' => [
        'summary' => ['open', 'resolved', 'closed', 'unread_by_business'],
        'items',
        'pagination' => ['next_cursor', 'has_more', 'per_page'],
    ]]);

    expect($response->json('data.items'))->toHaveCount(1)
        ->and($response->json('data.items.0.ticket_id'))->toBe($created->json('data.ticket_id'))
        ->and($response->json('data.pagination.per_page'))->toBe(1);

    $all = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-list/support-tickets',
        supportUiHeaders()
    )->assertOk();

    expect(collect($all->json('data.items'))->pluck('ticket_type')->all())
        ->toContain('onboarding', 'support');
});

test('canonical preset ticket runs the complete text lifecycle and message replay is idempotent', function (): void {
    seedSupportUiBusiness('biz-life', 'life@example.com');

    $create = $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-life/support-tickets',
        [
            'preset_code' => 'qr_scan_not_recorded',
            'campaign_id' => 'campaign-123',
            'subject' => 'Client subject must not win',
            'message' => 'QR đặt tại quầy nhưng dashboard chưa cập nhật.',
            'response_channel' => 'phone',
            'metadata' => ['screen' => 'growth_marketing', 'source' => 'fizahub'],
        ],
        supportUiHeaders()
    )->assertCreated()
        ->assertJsonPath('data.preset_code', 'qr_scan_not_recorded')
        ->assertJsonPath('data.campaign_id', 'campaign-123')
        ->assertJsonPath('data.ticket_type', 'support')
        ->assertJsonPath('data.source', 'fizahub')
        ->assertJsonPath('data.next_poll_after_seconds', 15);

    expect($create->json('data.subject'))->not->toBe('Client subject must not win');
    $ticketId = (string) $create->json('data.ticket_id');

    $detail = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-life/support-tickets/'.$ticketId,
        supportUiHeaders()
    )->assertOk()->assertJsonStructure(['data' => [
        'ticket_id', 'ticket_type', 'source', 'preset_code', 'campaign_id', 'subject',
        'status', 'status_label', 'messages', 'created_at', 'updated_at', 'next_poll_after_seconds',
    ]]);
    expect(strtolower((string) json_encode($detail->json())))->not->toContain('attachment');

    $messageKey = 'message-replay-key';
    $messageUrl = '/api/v1/partners/fizahub/businesses/biz-life/support-tickets/'.$ticketId.'/messages';
    $firstMessage = $this->postJson($messageUrl, ['message' => '<b>Xin hỗ trợ</b>'], supportUiHeaders($messageKey))
        ->assertCreated()
        ->assertJsonPath('data.body', 'Xin hỗ trợ');
    $this->postJson($messageUrl, ['message' => '<b>Xin hỗ trợ</b>'], supportUiHeaders($messageKey))
        ->assertCreated()
        ->assertJsonPath('data.message_id', $firstMessage->json('data.message_id'));
    expect(SupportComment::query()->count())->toBe(1);

    $closeUrl = '/api/v1/partners/fizahub/businesses/biz-life/support-tickets/'.$ticketId.'/close';
    $this->postJson($closeUrl, [], supportUiHeaders())->assertOk()->assertJsonPath('data.status', 'closed');
    $this->postJson($closeUrl, [], supportUiHeaders())
        ->assertOk()
        ->assertJsonPath('data.status', 'closed');

    $reopenUrl = '/api/v1/partners/fizahub/businesses/biz-life/support-tickets/'.$ticketId.'/reopen';
    $this->postJson($reopenUrl, [], supportUiHeaders())->assertOk()->assertJsonPath('data.status', 'open');
    $this->postJson($reopenUrl, [], supportUiHeaders())
        ->assertOk()
        ->assertJsonPath('data.status', 'open');
});

test('all ticket operations including attachments are tenant isolated', function (): void {
    seedSupportUiBusiness('biz-tenant-a', 'tenant-a@example.com');
    seedSupportUiBusiness('biz-tenant-b', 'tenant-b@example.com');

    $ticketId = $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-tenant-b/support-tickets',
        ['subject' => 'B private', 'message' => 'B only'],
        supportUiHeaders()
    )->assertCreated()->json('data.ticket_id');
    $base = '/api/v1/partners/fizahub/businesses/biz-tenant-a/support-tickets/'.$ticketId;

    $this->getJson($base, supportUiHeaders())->assertNotFound()->assertJsonPath('error.code', 'ticket_not_found');
    $this->postJson($base.'/messages', ['message' => 'intrusion'], supportUiHeaders())
        ->assertNotFound()->assertJsonPath('error.code', 'ticket_not_found');
    $this->postJson($base.'/close', [], supportUiHeaders())
        ->assertNotFound()->assertJsonPath('error.code', 'ticket_not_found');
    $this->postJson($base.'/reopen', [], supportUiHeaders())
        ->assertNotFound()->assertJsonPath('error.code', 'ticket_not_found');

    $headers = supportUiHeaders('attachment-tenant-guard');
    $this->post(
        $base.'/attachments',
        ['file' => UploadedFile::fake()->createWithContent('intrusion.txt', 'x')],
        $headers
    )
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('meta.request_id', $headers['X-Request-Id'])
        ->assertJsonPath('error.code', 'ticket_not_found');

    $this->getJson($base.'/attachments', supportUiHeaders())
        ->assertNotFound()->assertJsonPath('error.code', 'ticket_not_found');
});
