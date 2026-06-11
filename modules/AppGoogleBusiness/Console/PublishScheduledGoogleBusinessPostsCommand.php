<?php

namespace Modules\AppGoogleBusiness\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Modules\AppGoogleBusiness\Models\GoogleBusinessPost;
use Modules\AppGoogleBusiness\Models\GoogleBusinessPostLog;
use Modules\AppGoogleBusiness\Support\GoogleBusinessClient;
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

        GoogleBusinessPost::query()
            ->with('location.connection')
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->whereHas('location', fn ($query) => $query->where('is_managed', true))
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get()
            ->each(function (GoogleBusinessPost $post) use ($client, &$published, &$failed): void {
                try {
                    $result = $client->publishPost($post);

                    GoogleBusinessPostLog::record([
                        'team_id' => $post->team_id,
                        'post_id' => $post->id,
                        'action' => 'scheduled_create',
                        'status' => 'success',
                        'request_payload' => $result['request'] ?? [],
                        'response_body' => $result['response'] ?? [],
                    ]);

                    $published++;
                    $this->info("Published scheduled post #{$post->id}.");
                } catch (Throwable $exception) {
                    $post->forceFill([
                        'status' => 'failed',
                        'error_message' => $exception->getMessage(),
                    ])->save();

                    GoogleBusinessPostLog::record([
                        'team_id' => $post->team_id,
                        'post_id' => $post->id,
                        'action' => 'scheduled_create',
                        'status' => 'failed',
                        'request_payload' => $client->postPayload($post),
                        'error_message' => $exception->getMessage(),
                    ]);

                    $failed++;
                    $this->error("Failed scheduled post #{$post->id}: {$exception->getMessage()}");
                }
            });

        $this->info("Scheduled Google posts processed. Published: {$published}. Failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
