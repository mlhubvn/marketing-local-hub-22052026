<?php

namespace Modules\AppGoogleBusiness\Console;

use Illuminate\Console\Command;
use Modules\AppGoogleBusiness\Models\GoogleBusinessLocation;
use Modules\AppGoogleBusiness\Models\GoogleReview;
use Modules\AppGoogleBusiness\Support\GoogleAutoReplyService;
use Modules\AppGoogleBusiness\Support\GoogleBusinessClient;
use Throwable;

class SyncGoogleBusinessReviewsCommand extends Command
{
    protected $signature = 'google-business:sync-reviews {--location_id=}';

    protected $description = 'Sync Google Business reviews and process auto reply rules.';

    public function handle(GoogleBusinessClient $client, GoogleAutoReplyService $autoReply): int
    {
        $query = GoogleBusinessLocation::query()
            ->with('connection')
            ->where('sync_reviews', true)
            ->where('is_managed', true)
            ->whereHas('connection', fn ($query) => $query->where('auto_sync', true)->where('status', 'connected'));

        if ($this->option('location_id') !== null) {
            $query->whereKey((int) $this->option('location_id'));
        }

        $query->chunkById(25, function ($locations) use ($client, $autoReply): void {
            foreach ($locations as $location) {
                try {
                    $before = GoogleReview::query()->where('google_business_location_id', $location->id)->pluck('id')->all();
                    $count = $client->syncReviews($location);
                    $location->forceFill(['last_reviews_synced_at' => now(), 'last_synced_at' => now()])->save();

                    GoogleReview::query()
                        ->where('google_business_location_id', $location->id)
                        ->whereNotIn('id', $before)
                        ->get()
                        ->each(fn (GoogleReview $review) => $autoReply->processReview($review));

                    $this->info("Synced {$count} reviews for {$location->name}.");
                } catch (Throwable $exception) {
                    $location->connection?->forceFill(['last_error' => $exception->getMessage()])->save();
                    $this->error("Failed {$location->name}: {$exception->getMessage()}");
                }
            }
        });

        return self::SUCCESS;
    }
}
