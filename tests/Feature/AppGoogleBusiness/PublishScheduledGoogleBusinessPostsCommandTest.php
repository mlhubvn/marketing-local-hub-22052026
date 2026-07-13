<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\AppGoogleBusiness\Models\GoogleBusinessConnection;
use Modules\AppGoogleBusiness\Models\GoogleBusinessLocation;
use Modules\AppGoogleBusiness\Models\GoogleBusinessPost;
use Modules\AppGoogleBusiness\Support\GoogleBusinessClient;

function createGoogleBusinessSchedulerTestTables(): void
{
    Schema::create('users', function (Blueprint $table): void {
        $table->id();
    });

    Schema::create('lb_google_business_connections', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id');
        $table->unsignedBigInteger('user_id');
        $table->text('access_token')->nullable();
        $table->text('refresh_token')->nullable();
        $table->timestamp('expires_at')->nullable();
        $table->string('status', 30)->default('connected');
        $table->timestamps();
    });

    Schema::create('lb_google_business_locations', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id');
        $table->unsignedBigInteger('connection_id');
        $table->unsignedBigInteger('business_id')->nullable();
        $table->string('google_account_id')->nullable();
        $table->string('google_location_id')->nullable();
        $table->boolean('is_managed')->default(true);
        $table->timestamps();
    });

    Schema::create('lb_google_business_posts', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id');
        $table->unsignedBigInteger('google_business_location_id');
        $table->unsignedBigInteger('business_id')->nullable();
        $table->string('google_post_id')->nullable();
        $table->string('google_post_name')->nullable();
        $table->string('type', 40)->default('standard');
        $table->text('summary');
        $table->string('status', 40)->default('scheduled');
        $table->text('search_url')->nullable();
        $table->text('error_message')->nullable();
        $table->timestamp('scheduled_at')->nullable();
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_google_business_post_logs', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id');
        $table->unsignedBigInteger('post_id')->nullable();
        $table->string('action', 40);
        $table->string('status', 40);
        $table->json('request_payload')->nullable();
        $table->json('response_body')->nullable();
        $table->text('error_message')->nullable();
        $table->timestamps();
    });
}

function createSchedulerConnection(string $status = 'connected', ?string $accessToken = 'access-token'): GoogleBusinessConnection
{
    return GoogleBusinessConnection::query()->create([
        'team_id' => 1,
        'user_id' => 1,
        'status' => $status,
        'access_token' => $accessToken,
        'expires_at' => now()->addHour(),
    ]);
}

function createSchedulerLocation(GoogleBusinessConnection $connection, array $attributes = []): GoogleBusinessLocation
{
    return GoogleBusinessLocation::query()->create(array_merge([
        'team_id' => 1,
        'connection_id' => $connection->id,
        'business_id' => 41,
        'google_account_id' => 'account-1',
        'google_location_id' => 'location-1',
        'is_managed' => true,
    ], $attributes));
}

function createDueSchedulerPost(GoogleBusinessLocation $location, string $summary): GoogleBusinessPost
{
    return GoogleBusinessPost::query()->create([
        'team_id' => 1,
        'google_business_location_id' => $location->id,
        'business_id' => $location->business_id,
        'type' => 'standard',
        'summary' => $summary,
        'status' => 'scheduled',
        'scheduled_at' => now()->subMinute(),
    ]);
}

beforeEach(function (): void {
    Carbon::setTestNow('2026-07-13 12:00:00');

    foreach (['lb_google_business_post_logs', 'lb_google_business_posts', 'lb_google_business_locations', 'lb_google_business_connections', 'users'] as $table) {
        Schema::dropIfExists($table);
    }

    createGoogleBusinessSchedulerTestTables();
    DB::table('users')->insert(['id' => 1]);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

test('empty scheduled post queue succeeds', function (): void {
    $client = Mockery::mock(GoogleBusinessClient::class);
    $client->shouldNotReceive('publishPost');
    $this->app->instance(GoogleBusinessClient::class, $client);

    $this->artisan('google-business:publish-scheduled-posts')
        ->expectsOutput('Scheduled Google posts processed. Published: 0. Failed: 0.')
        ->assertSuccessful();
});

test('scheduled post on a simulated connection is skipped unchanged', function (): void {
    $connection = createSchedulerConnection('simulated', null);
    $post = createDueSchedulerPost(createSchedulerLocation($connection), 'Demo scheduled post');

    $client = Mockery::mock(GoogleBusinessClient::class);
    $client->shouldNotReceive('publishPost');
    $client->shouldNotReceive('postPayload');
    $this->app->instance(GoogleBusinessClient::class, $client);

    $this->artisan('google-business:publish-scheduled-posts')->assertSuccessful();

    expect($post->refresh())
        ->status->toBe('scheduled')
        ->error_message->toBeNull();
});

test('simulated posts do not consume the connected publishing batch limit', function (): void {
    $simulatedPost = createDueSchedulerPost(
        createSchedulerLocation(createSchedulerConnection('simulated', null)),
        'Earlier demo post'
    );
    $simulatedPost->forceFill(['scheduled_at' => now()->subMinutes(2)])->save();

    $connectedPost = createDueSchedulerPost(
        createSchedulerLocation(createSchedulerConnection()),
        'Connected post'
    );

    $client = Mockery::mock(GoogleBusinessClient::class);
    $client->shouldReceive('publishPost')
        ->once()
        ->with(Mockery::on(fn (GoogleBusinessPost $post): bool => $post->is($connectedPost)))
        ->andReturnUsing(function (GoogleBusinessPost $post): array {
            $post->forceFill(['status' => 'published', 'published_at' => now()])->save();

            return ['request' => [], 'response' => ['name' => 'connected-post']];
        });
    $this->app->instance(GoogleBusinessClient::class, $client);

    $this->artisan('google-business:publish-scheduled-posts --limit=1')->assertSuccessful();

    expect($simulatedPost->refresh()->status)->toBe('scheduled')
        ->and($connectedPost->refresh()->status)->toBe('published');
});

test('connected post missing a required Google identifier is marked failed and warned without failing the command', function (?string $accessToken, array $locationAttributes, string $reason): void {
    $connection = createSchedulerConnection('connected', $accessToken);
    $location = createSchedulerLocation($connection, $locationAttributes);
    $post = createDueSchedulerPost($location, 'Invalid connected post');

    $client = Mockery::mock(GoogleBusinessClient::class);
    $client->shouldNotReceive('publishPost');
    $client->shouldReceive('postPayload')->once()->with(Mockery::type(GoogleBusinessPost::class))->andReturn([]);
    $this->app->instance(GoogleBusinessClient::class, $client);

    Log::shouldReceive('warning')
        ->once()
        ->with('Google Business scheduled post failed.', [
            'post_id' => $post->id,
            'user_id' => 1,
            'team_id' => 1,
            'business_id' => 41,
            'location_id' => $location->id,
            'google_location_id' => $location->google_location_id,
            'connection_id' => $connection->id,
            'reason' => $reason,
        ]);

    $this->artisan('google-business:publish-scheduled-posts')->assertSuccessful();

    expect($post->refresh())
        ->status->toBe('failed')
        ->error_message->toBe($reason);
})->with([
    'missing access token' => [null, [], 'Google Business connection access token is missing.'],
    'missing account ID' => ['access-token', ['google_account_id' => null], 'Google Business account ID is missing.'],
    'missing location ID' => ['access-token', ['google_location_id' => null], 'Google Business location ID is missing.'],
]);

test('one failed post does not block a later valid post or fail the command', function (): void {
    $connection = createSchedulerConnection();
    $location = createSchedulerLocation($connection);
    $failedPost = createDueSchedulerPost($location, 'Rejected post');
    $validPost = createDueSchedulerPost($location, 'Valid post');

    $client = Mockery::mock(GoogleBusinessClient::class);
    $client->shouldReceive('publishPost')
        ->twice()
        ->andReturnUsing(function (GoogleBusinessPost $post): array {
            if ($post->summary === 'Rejected post') {
                throw new RuntimeException('Google rejected the post.');
            }

            $post->forceFill([
                'status' => 'published',
                'published_at' => now(),
            ])->save();

            return ['request' => [], 'response' => ['name' => 'published-post']];
        });
    $client->shouldReceive('postPayload')->once()->andReturn([]);
    $this->app->instance(GoogleBusinessClient::class, $client);

    Log::shouldReceive('warning')
        ->once()
        ->with('Google Business scheduled post failed.', Mockery::on(
            fn (array $context): bool => $context['post_id'] === $failedPost->id
                && $context['reason'] === 'Google rejected the post.'
        ));

    $this->artisan('google-business:publish-scheduled-posts')->assertSuccessful();

    expect($failedPost->refresh()->status)->toBe('failed')
        ->and($validPost->refresh()->status)->toBe('published');
});

test('payload and audit logging failures do not escape the record failure boundary', function (): void {
    $connection = createSchedulerConnection();
    $post = createDueSchedulerPost(createSchedulerLocation($connection), 'Diagnostic failure post');

    Schema::drop('lb_google_business_post_logs');

    $client = Mockery::mock(GoogleBusinessClient::class);
    $client->shouldReceive('publishPost')->once()->andThrow(new RuntimeException('Google rejected the post.'));
    $client->shouldReceive('postPayload')->once()->andThrow(new RuntimeException('Payload generation failed.'));
    $this->app->instance(GoogleBusinessClient::class, $client);

    $this->artisan('google-business:publish-scheduled-posts')->assertSuccessful();

    expect($post->refresh())
        ->status->toBe('failed')
        ->error_message->toBe('Google rejected the post.');
});

test('structured warning failure does not escape the record failure boundary', function (): void {
    $post = createDueSchedulerPost(createSchedulerLocation(createSchedulerConnection()), 'Logger failure post');

    $client = Mockery::mock(GoogleBusinessClient::class);
    $client->shouldReceive('publishPost')->once()->andThrow(new RuntimeException('Google rejected the post.'));
    $client->shouldReceive('postPayload')->once()->andReturn([]);
    $this->app->instance(GoogleBusinessClient::class, $client);

    Log::shouldReceive('warning')->once()->andThrow(new RuntimeException('Logger unavailable.'));

    $this->artisan('google-business:publish-scheduled-posts')->assertSuccessful();

    expect($post->refresh()->status)->toBe('failed');
});

test('failed state persistence failure is a command failure', function (): void {
    $post = createDueSchedulerPost(createSchedulerLocation(createSchedulerConnection()), 'Persistence failure post');

    DB::unprepared(<<<'SQL'
        CREATE TRIGGER fail_google_post_update
        BEFORE UPDATE ON lb_google_business_posts
        BEGIN
            SELECT RAISE(FAIL, 'post update unavailable');
        END;
    SQL);

    $client = Mockery::mock(GoogleBusinessClient::class);
    $client->shouldReceive('publishPost')->once()->andThrow(new RuntimeException('Google rejected the post.'));
    $this->app->instance(GoogleBusinessClient::class, $client);

    $this->artisan('google-business:publish-scheduled-posts')->assertFailed();

    expect($post->refresh()->status)->toBe('scheduled');
});

test('relationship query failure remains a command failure', function (): void {
    Schema::drop('lb_google_business_locations');

    $this->artisan('google-business:publish-scheduled-posts')->assertFailed();
});
