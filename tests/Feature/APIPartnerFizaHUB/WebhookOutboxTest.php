<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Modules\APIPartnerFizaHUB\Jobs\DeliverPartnerWebhookJob;
use Modules\APIPartnerFizaHUB\Models\PartnerWebhookOutbox;
use Modules\APIPartnerFizaHUB\Services\WebhookOutboxService;

require_once __DIR__.'/FizaHubTestHelpers.php';

function createWebhookOutboxTables(): void
{
    dropFizaHubPartnerTables();
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
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

    createFizaHubPartnerTables();
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.partner_code', 'fizahub');
    config()->set('modules.apipartnerfizahub.webhook_base_url', 'https://hooks.fizahub.test');
    config()->set('modules.apipartnerfizahub.webhook_secret', 'test-webhook-secret');
    createWebhookOutboxTables();
});

afterEach(function (): void {
    dropFizaHubPartnerTables();
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
});

test('queueing an onboarding status webhook creates a pending row and dispatches once', function (): void {
    Queue::fake();
    $service = app(WebhookOutboxService::class);

    $row = $service->queueOnboardingStatus(
        ['request_id' => 'req-1', 'status' => 'ready'],
        'req-1|ready'
    );

    expect($row)->not->toBeNull()
        ->and($row->status)->toBe('pending')
        ->and($row->event_type)->toBe('onboarding-status')
        ->and($row->endpoint_path)->toBe('/mlhub/onboarding-status')
        ->and($row->dedupe_key)->toBe('onboarding-status|req-1|ready')
        ->and($row->partner_code)->toBe('fizahub');

    Queue::assertPushed(DeliverPartnerWebhookJob::class, 1);
});

test('queueing with the same dedupe key does not create a duplicate row', function (): void {
    Queue::fake();
    $service = app(WebhookOutboxService::class);

    $first = $service->queueOnboardingStatus(['request_id' => 'req-2', 'status' => 'ready'], 'req-2|ready');
    $second = $service->queueOnboardingStatus(['request_id' => 'req-2', 'status' => 'ready', 'extra' => 1], 'req-2|ready');

    expect($second->id)->toBe($first->id)
        ->and(PartnerWebhookOutbox::query()->count())->toBe(1);

    Queue::assertPushed(DeliverPartnerWebhookJob::class, 1);
});

test('delivering a webhook signs the payload and marks it delivered', function (): void {
    Queue::fake();
    Http::fake(['*' => Http::response('{"ok":true}', 200)]);

    $service = app(WebhookOutboxService::class);
    $row = $service->queueOnboardingStatus(['request_id' => 'req-3', 'status' => 'completed'], 'req-3|completed');

    $service->deliver($row->id);
    $row->refresh();

    expect($row->status)->toBe('delivered')
        ->and($row->delivered_at)->not->toBeNull()
        ->and($row->attempts)->toBe(1)
        ->and($row->signature_hash)->toBeString()->not->toBeEmpty();

    Http::assertSent(function ($request): bool {
        return str_contains($request->url(), '/mlhub/onboarding-status')
            && $request->header('X-Partner')[0] === 'fizahub'
            && $request->header('X-Event-Type')[0] === 'onboarding-status'
            && str_starts_with($request->header('X-MLHUB-Signature')[0], 'sha256=');
    });
});
