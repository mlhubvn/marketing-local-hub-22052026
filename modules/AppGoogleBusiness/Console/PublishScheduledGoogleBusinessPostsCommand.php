<?php

namespace Modules\AppGoogleBusiness\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\AppGoogleBusiness\Models\GoogleBusinessPost;
use Modules\AppGoogleBusiness\Models\GoogleBusinessPostLog;
use Modules\AppGoogleBusiness\Support\GoogleBusinessClient;
use RuntimeException;
use Throwable;

class PublishScheduledGoogleBusinessPostsCommand extends Command
{
    protected $signature = 'google-business:publish-scheduled-posts {--limit=50}';

    protected $description = 'Publish scheduled Google Business posts that are due.';

    public function handle(GoogleBusinessClient $client): int
    {
        if (! Schema::hasTable((new GoogleBusinessPost)->getTable())) {
            return self::SUCCESS;
        }

        $limit = max(1, min(200, (int) $this->option('limit')));
        $published = 0;
        $failed = 0;

        try {
            GoogleBusinessPost::query()
                ->with('location.connection')
                ->where('status', 'scheduled')
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '<=', now())
                ->whereHas('location', fn ($query) => $query
                    ->where('is_managed', true)
                    ->whereHas('connection', fn ($connectionQuery) => $connectionQuery->where('status', 'connected')))
                ->orderBy('scheduled_at')
                ->limit($limit)
                ->get()
                ->each(function (GoogleBusinessPost $post) use ($client, &$published, &$failed): void {
                    $connection = $post->location?->connection;

                    if ((string) $connection?->status !== 'connected') {
                        $status = (string) ($connection?->status ?: 'missing');
                        $this->info("Skipped scheduled post #{$post->id}: connection status [{$status}].");

                        return;
                    }

                    try {
                        if ($reason = $this->invalidPublishReason($post)) {
                            throw new RuntimeException($reason);
                        }

                        $result = $client->publishPost($post);

                        $this->recordPostLogSafely([
                            'team_id' => $post->team_id,
                            'post_id' => $post->id,
                            'action' => 'scheduled_create',
                            'status' => 'success',
                            'request_payload' => $result['request'] ?? [],
                            'response_body' => $result['response'] ?? [],
                        ], $post);

                        $published++;
                        $this->info("Published scheduled post #{$post->id}.");
                    } catch (Throwable $exception) {
                        $post->forceFill([
                            'status' => 'failed',
                            'error_message' => $exception->getMessage(),
                        ])->save();

                        $this->recordPostLogSafely([
                            'team_id' => $post->team_id,
                            'post_id' => $post->id,
                            'action' => 'scheduled_create',
                            'status' => 'failed',
                            'request_payload' => $this->postPayloadSafely($client, $post),
                            'error_message' => $exception->getMessage(),
                        ], $post);

                        $this->warnSafely('Google Business scheduled post failed.', $this->warningContext($post, $exception->getMessage()));

                        $failed++;
                        $this->error("Failed scheduled post #{$post->id}: {$exception->getMessage()}");
                    }
                });
        } catch (Throwable $exception) {
            $this->error("Google Business scheduled post system failure: {$exception->getMessage()}");

            return self::FAILURE;
        }

        $this->info("Scheduled Google posts processed. Published: {$published}. Failed: {$failed}.");

        return self::SUCCESS;
    }

    protected function invalidPublishReason(GoogleBusinessPost $post): ?string
    {
        $location = $post->location;
        $connection = $location?->connection;

        if (! $location) {
            return 'Google Business location is missing.';
        }

        if (! $connection) {
            return 'Google Business connection is missing.';
        }

        if (blank($connection->access_token)) {
            return 'Google Business connection access token is missing.';
        }

        if (blank($location->google_account_id)) {
            return 'Google Business account ID is missing.';
        }

        if (blank($location->google_location_id)) {
            return 'Google Business location ID is missing.';
        }

        return null;
    }

    protected function postPayloadSafely(GoogleBusinessClient $client, GoogleBusinessPost $post): array
    {
        try {
            return $client->postPayload($post);
        } catch (Throwable $exception) {
            $this->warnSafely('Google Business scheduled post payload logging failed.', $this->warningContext($post, $exception->getMessage()));

            return [];
        }
    }

    protected function recordPostLogSafely(array $attributes, GoogleBusinessPost $post): void
    {
        try {
            GoogleBusinessPostLog::record($attributes);
        } catch (Throwable $exception) {
            $this->warnSafely('Google Business scheduled post audit logging failed.', $this->warningContext($post, $exception->getMessage()));
        }
    }

    protected function warningContext(GoogleBusinessPost $post, string $reason): array
    {
        $location = $post->location;
        $connection = $location?->connection;

        return [
            'post_id' => $post->id,
            'user_id' => $connection?->user_id,
            'team_id' => $post->team_id,
            'business_id' => $post->business_id,
            'location_id' => $location?->id,
            'google_location_id' => $location?->google_location_id,
            'connection_id' => $connection?->id,
            'reason' => $reason,
        ];
    }

    protected function warnSafely(string $message, array $context): void
    {
        try {
            Log::warning($message, $context);
        } catch (Throwable) {
            // Diagnostic logging must not turn a handled record failure into a command failure.
        }
    }
}
