<?php

namespace Modules\AppGoogleBusiness\Support;

use App\Support\GrowthToolNotifier;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\AdminSettings\Support\OptionStore;
use RuntimeException;
use Throwable;
use Modules\AppGoogleBusiness\Models\GoogleBusinessConnection;
use Modules\AppGoogleBusiness\Models\GoogleBusinessLocation;
use Modules\AppGoogleBusiness\Models\GoogleBusinessPost;
use Modules\AppGoogleBusiness\Models\GoogleReview;

class GoogleBusinessClient
{
    public const SCOPE = 'https://www.googleapis.com/auth/business.manage';

    public function authUrl(string $state): string
    {
        $this->ensureConfigured();

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => self::SCOPE.' openid email profile',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);
    }

    public function exchangeCode(string $code): array
    {
        $this->ensureConfigured();

        $response = Http::timeout(30)->asForm()->acceptJson()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
            'code' => $code,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException($response->json('error_description') ?: $response->body());
        }

        return $response->json();
    }

    public function userInfo(string $accessToken): array
    {
        $response = Http::timeout(30)->withToken($accessToken)->acceptJson()->get('https://openidconnect.googleapis.com/v1/userinfo');

        return $response->successful() ? $response->json() : [];
    }

    public function syncAccountsAndLocations(GoogleBusinessConnection $connection): int
    {
        $synced = 0;

        foreach ($this->fetchLocationCandidates($connection) as $candidate) {
            $this->importLocation($connection, (string) $candidate['account_id'], (array) $candidate['payload']);
            $synced++;
        }

        return $synced;
    }

    public function fetchLocationCandidates(GoogleBusinessConnection $connection): array
    {
        $accounts = $this->googleRequest($connection, 'GET', 'https://mybusinessaccountmanagement.googleapis.com/v1/accounts')
            ->json('accounts', []);

        $importedLocationIds = GoogleBusinessLocation::query()
            ->where('connection_id', $connection->id)
            ->pluck('google_location_id')
            ->flip();

        $candidates = [];
        $accountIndex = 0;

        foreach ($accounts as $account) {
            $accountId = Str::after((string) ($account['name'] ?? ''), 'accounts/');

            if ($accountId === '') {
                continue;
            }

            if ($accountIndex > 0) {
                usleep(300000);
            }

            $locations = $this->fetchAccountLocations($connection, $accountId);

            foreach ($locations as $location) {
                $locationName = (string) ($location['name'] ?? '');
                $locationId = Str::afterLast($locationName, '/');

                $candidates[] = [
                    'connection_id' => $connection->id,
                    'account_id' => $accountId,
                    'google_location_id' => $locationId,
                    'name' => (string) ($location['title'] ?? $locationId),
                    'address' => $this->addressFromPayload($location),
                    'category' => (string) data_get($location, 'categories.primaryCategory.displayName', ''),
                    'already_imported' => $importedLocationIds->has($locationId),
                    'payload' => $location,
                ];
            }

            $accountIndex++;
        }

        $connection->forceFill(['last_synced_at' => now(), 'status' => 'connected', 'last_error' => null])->save();

        return $candidates;
    }

    public function isQuotaExceeded(Throwable $exception): bool
    {
        if ($exception instanceof RequestException && $exception->response?->status() === 429) {
            return true;
        }

        $message = strtolower($exception->getMessage());

        return str_contains($message, '429')
            || str_contains($message, 'quota exceeded')
            || str_contains($message, 'rate limit');
    }

    public function importLocation(GoogleBusinessConnection $connection, string $accountId, array $payload): GoogleBusinessLocation
    {
        return $this->upsertLocation($connection, $accountId, $payload);
    }

    public function syncReviews(GoogleBusinessLocation $location): int
    {
        $reviews = $this->googleRequest(
            $location->connection,
            'GET',
            "https://mybusiness.googleapis.com/v4/accounts/{$location->google_account_id}/locations/{$location->google_location_id}/reviews"
        )->json('reviews', []);

        foreach ($reviews as $review) {
            $reviewName = (string) ($review['name'] ?? '');

            $googleReview = GoogleReview::query()->updateOrCreate(
                [
                    'google_business_location_id' => $location->id,
                    'google_review_id' => Str::afterLast($reviewName, '/'),
                ],
                [
                    'team_id' => $location->team_id,
                    'business_id' => $location->business_id,
                    'reviewer_name' => (string) data_get($review, 'reviewer.displayName', ''),
                    'rating' => $this->ratingValue((string) ($review['starRating'] ?? '')),
                    'comment' => (string) ($review['comment'] ?? ''),
                    'reply' => (string) data_get($review, 'reviewReply.comment', ''),
                    'reply_status' => filled(data_get($review, 'reviewReply.comment')) ? 'replied' : 'not_replied',
                    'review_created_at' => $this->parseTime($review['createTime'] ?? null),
                    'review_updated_at' => $this->parseTime($review['updateTime'] ?? null),
                    'last_synced_at' => now(),
                ]
            );

            if ($googleReview->wasRecentlyCreated) {
                app(GrowthToolNotifier::class)->googleReviewCreated(
                    (int) $location->team_id,
                    (string) $location->name,
                    (string) $googleReview->reviewer_name,
                    (int) $googleReview->rating
                );
            }
        }

        $location->forceFill([
            'last_synced_at' => now(),
            'last_reviews_synced_at' => now(),
        ])->save();

        return count($reviews);
    }

    public function replyToReview(GoogleReview $review, string $reply): void
    {
        $location = $review->googleLocation;

        $this->googleRequest(
            $location->connection,
            'PUT',
            "https://mybusiness.googleapis.com/v4/accounts/{$location->google_account_id}/locations/{$location->google_location_id}/reviews/{$review->google_review_id}/reply",
            ['comment' => $reply]
        );

        $review->forceFill([
            'reply' => $reply,
            'reply_status' => 'replied',
            'auto_reply_status' => $review->auto_reply_status === 'generated' ? 'published' : $review->auto_reply_status,
            'replied_at' => now(),
        ])->save();
    }

    public function publishPost(GoogleBusinessPost $post): array
    {
        $post->loadMissing('location.connection');
        $location = $post->location;
        $this->validatePostForPublish($post);
        $payload = $this->postPayload($post);

        try {
            $response = $this->googleRequest(
                $location->connection,
                'POST',
                "https://mybusiness.googleapis.com/v4/accounts/{$location->google_account_id}/locations/{$location->google_location_id}/localPosts",
                $payload
            );
        } catch (RuntimeException $exception) {
            throw new RuntimeException($this->googleErrorMessage([], $exception->getMessage(), $post));
        }

        $response = $response->json();

        $googleName = (string) ($response['name'] ?? '');

        $post->forceFill([
            'google_post_name' => $googleName,
            'google_post_id' => Str::afterLast($googleName, '/'),
            'search_url' => (string) ($response['searchUrl'] ?? ''),
            'status' => 'published',
            'error_message' => null,
            'published_at' => now(),
        ])->save();

        return ['request' => $payload, 'response' => $response];
    }

    public function postPayload(GoogleBusinessPost $post): array
    {
        $type = match ((string) $post->type) {
            'event' => 'EVENT',
            'offer' => 'OFFER',
            default => 'STANDARD',
        };

        $payload = [
            'languageCode' => 'en-US',
            'summary' => (string) $post->summary,
            'topicType' => $type,
        ];

        if ($post->media_url && $this->isPublicUrl((string) $post->media_url)) {
            $payload['media'] = [[
                'mediaFormat' => 'PHOTO',
                'sourceUrl' => (string) $post->media_url,
            ]];
        }

        if ($type !== 'OFFER' && $post->cta_type && ($post->cta_url || strtoupper((string) $post->cta_type) === 'CALL')) {
            $payload['callToAction'] = [
                'actionType' => strtoupper((string) $post->cta_type),
            ];

            if (strtoupper((string) $post->cta_type) !== 'CALL') {
                $payload['callToAction']['url'] = (string) $post->cta_url;
            }
        }

        if (in_array($type, ['EVENT', 'OFFER'], true)) {
            $payload['event'] = [
                'title' => (string) ($post->title ?: str($post->summary)->limit(58, '')),
                'schedule' => [
                    'startDate' => $this->datePayload($post->start_at ?: now()),
                    'startTime' => $this->timePayload($post->start_at ?: now()->startOfDay()),
                    'endDate' => $this->datePayload($post->end_at ?: now()->addDays(7)),
                    'endTime' => $this->timePayload($post->end_at ?: now()->addDays(7)->endOfDay()),
                ],
            ];
        }

        if ($type === 'OFFER') {
            $payload['offer'] = array_filter([
                'couponCode' => $post->coupon_code ?: null,
                'redeemOnlineUrl' => $post->cta_url ?: null,
                'termsConditions' => $post->terms ?: null,
            ], fn ($value) => filled($value));
        }

        return $payload;
    }

    protected function validatePostForPublish(GoogleBusinessPost $post): void
    {
        if (in_array((string) $post->type, ['offer', 'event'], true) && (! $post->start_at || ! $post->end_at)) {
            throw new RuntimeException(__('Offer and event posts require start date and end date before publishing to Google.'));
        }

        if ((string) $post->type === 'offer' && blank($post->coupon_code) && blank($post->cta_url)) {
            throw new RuntimeException(__('Offer posts require at least a coupon code or a redeem URL before publishing to Google.'));
        }

        if ((string) $post->type !== 'offer' && $post->cta_type && strtoupper((string) $post->cta_type) !== 'CALL' && blank($post->cta_url)) {
            throw new RuntimeException(__('This CTA requires a CTA URL before publishing to Google.'));
        }
    }

    protected function googleErrorMessage(array $payload, string $fallback, ?GoogleBusinessPost $post = null): string
    {
        $message = (string) data_get($payload, 'error.message', $fallback);
        $status = (string) data_get($payload, 'error.status', '');
        $reason = (string) data_get($payload, 'error.errors.0.reason', '');

        $parts = array_filter([$status, $reason, $message]);

        $error = __('Google rejected this post: :message', [
            'message' => implode(' - ', array_unique($parts)),
        ]);

        if ($status === 'INVALID_ARGUMENT') {
            $error .= ' '.__('Check that CTA/redeem URLs are public business or landing page URLs, offer dates are valid, and images are public.');

            if ($post && (string) $post->type === 'offer') {
                $error .= ' '.__('For offer posts, use a coupon or landing page as the redeem URL and write Terms as plain text instead of a video/social URL.');
            }
        }

        return $error;
    }

    protected function api(GoogleBusinessConnection $connection): PendingRequest
    {
        if ($connection->expires_at && $connection->expires_at->subMinutes(5)->isPast()) {
            $this->refresh($connection);
        }

        return Http::timeout(45)->acceptJson()->withToken((string) $connection->access_token);
    }

    protected function googleRequest(GoogleBusinessConnection $connection, string $method, string $url, array $payload = []): Response
    {
        $pending = $this->api($connection)->retry(4, function (int $attempt, Throwable $exception): int {
            if ($exception instanceof RequestException && $exception->response?->status() === 429) {
                return min(8000, 1000 * (2 ** max(0, $attempt - 1)));
            }

            throw $exception;
        });

        $response = match (strtoupper($method)) {
            'GET' => $pending->get($url, $payload),
            'POST' => $pending->post($url, $payload),
            'PUT' => $pending->put($url, $payload),
            default => throw new RuntimeException(__('Unsupported Google API request method.')),
        };

        if ($response->status() === 429 || $this->responseIndicatesQuota($response)) {
            throw new RuntimeException($this->quotaExceededMessage());
        }

        if (! $response->successful()) {
            throw new RuntimeException($this->responseErrorMessage($response));
        }

        return $response;
    }

    /** @return array<int, array<string, mixed>> */
    protected function fetchAccountLocations(GoogleBusinessConnection $connection, string $accountId): array
    {
        $locations = [];
        $pageToken = null;

        do {
            $query = [
                'readMask' => 'name,title,storefrontAddress,phoneNumbers,websiteUri,categories,regularHours,metadata',
                'pageSize' => 100,
            ];

            if ($pageToken) {
                $query['pageToken'] = $pageToken;
                usleep(250000);
            }

            $payload = $this->googleRequest(
                $connection,
                'GET',
                "https://mybusinessbusinessinformation.googleapis.com/v1/accounts/{$accountId}/locations",
                $query
            )->json();

            $locations = array_merge($locations, (array) ($payload['locations'] ?? []));
            $pageToken = (string) ($payload['nextPageToken'] ?? '');
        } while ($pageToken !== '');

        return $locations;
    }

    protected function quotaExceededMessage(): string
    {
        return __('Google API rate limit reached. Please wait about one minute and try again.');
    }

    protected function responseIndicatesQuota(Response $response): bool
    {
        $message = strtolower((string) data_get($response->json(), 'error.message', $response->body()));

        return str_contains($message, 'quota exceeded') || str_contains($message, 'rate limit');
    }

    protected function responseErrorMessage(Response $response): string
    {
        if ($response->status() === 429 || $this->responseIndicatesQuota($response)) {
            return $this->quotaExceededMessage();
        }

        return (string) (data_get($response->json(), 'error.message')
            ?: data_get($response->json(), 'error_description')
            ?: $response->body());
    }

    protected function refresh(GoogleBusinessConnection $connection): void
    {
        if (! filled($connection->refresh_token)) {
            $connection->forceFill(['status' => 'expired'])->save();
            throw new RuntimeException(__('Google connection expired. Please reconnect Google Business Profile.'));
        }

        $response = Http::timeout(30)->asForm()->acceptJson()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'grant_type' => 'refresh_token',
            'refresh_token' => $connection->refresh_token,
        ]);

        if (! $response->successful()) {
            $connection->forceFill(['status' => 'expired'])->save();
            throw new RuntimeException($response->json('error_description') ?: $response->body());
        }

        $payload = $response->json();

        $connection->forceFill([
            'access_token' => (string) $payload['access_token'],
            'expires_at' => now()->addSeconds((int) ($payload['expires_in'] ?? 3600)),
            'status' => 'connected',
        ])->save();
    }

    protected function upsertLocation(GoogleBusinessConnection $connection, string $accountId, array $payload): GoogleBusinessLocation
    {
        $locationName = (string) ($payload['name'] ?? '');
        $locationId = Str::afterLast($locationName, '/');
        $existing = GoogleBusinessLocation::query()
            ->where('connection_id', $connection->id)
            ->where('google_location_id', $locationId)
            ->first();

        if (! $existing && class_exists('Modules\\AdminUser\\Models\\User')) {
            $user = \Modules\AdminUser\Models\User::query()->find($connection->team_id);
            $limit = GoogleBusinessAccess::locationLimit($user);
            $used = GoogleBusinessLocation::query()->where('team_id', $connection->team_id)->count();

            if ($limit >= 0 && $used >= $limit) {
                return new GoogleBusinessLocation([
                    'team_id' => $connection->team_id,
                    'connection_id' => $connection->id,
                    'google_location_id' => $locationId,
                    'name' => (string) ($payload['title'] ?? $locationId),
                ]);
            }
        }

        $address = $this->addressFromPayload($payload);

        return GoogleBusinessLocation::query()->updateOrCreate(
            [
                'connection_id' => $connection->id,
                'google_location_id' => $locationId,
            ],
            [
                'team_id' => $connection->team_id,
                'google_account_id' => $accountId,
                'name' => (string) ($payload['title'] ?? $locationId),
                'address' => $address,
                'phone' => (string) (data_get($payload, 'phoneNumbers.primaryPhone') ?: data_get($payload, 'phoneNumbers.additionalPhones.0', '')),
                'website' => (string) ($payload['websiteUri'] ?? ''),
                'category' => (string) data_get($payload, 'categories.primaryCategory.displayName', ''),
                'review_url' => (string) data_get($payload, 'metadata.newReviewUri', ''),
                'opening_hours' => $payload['regularHours'] ?? null,
                'status' => 'active',
                'is_managed' => $existing?->is_managed ?? false,
                'managed_at' => $existing?->managed_at,
            ]
        );
    }

    protected function addressFromPayload(array $payload): string
    {
        return collect((array) data_get($payload, 'storefrontAddress.addressLines', []))
            ->merge([data_get($payload, 'storefrontAddress.locality'), data_get($payload, 'storefrontAddress.administrativeArea'), data_get($payload, 'storefrontAddress.postalCode')])
            ->filter()
            ->implode(', ');
    }

    protected function ratingValue(string $rating): int
    {
        return match ($rating) {
            'ONE' => 1,
            'TWO' => 2,
            'THREE' => 3,
            'FOUR' => 4,
            'FIVE' => 5,
            default => 0,
        };
    }

    protected function parseTime(mixed $value): ?Carbon
    {
        return filled($value) ? Carbon::parse((string) $value) : null;
    }

    protected function datePayload(mixed $value): array
    {
        $date = $value instanceof Carbon ? $value : Carbon::parse($value);

        return [
            'year' => (int) $date->year,
            'month' => (int) $date->month,
            'day' => (int) $date->day,
        ];
    }

    protected function timePayload(mixed $value): array
    {
        $time = $value instanceof Carbon ? $value : Carbon::parse($value);

        return [
            'hours' => (int) $time->hour,
            'minutes' => (int) $time->minute,
            'seconds' => (int) $time->second,
            'nanos' => 0,
        ];
    }

    protected function isPublicUrl(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($host === '' || in_array($host, ['localhost', '127.0.0.1', '::1'], true) || str_ends_with($host, '.test') || str_ends_with($host, '.local')) {
            return false;
        }

        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }

        return in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
    }

    protected function ensureConfigured(): void
    {
        if (! filled($this->clientId()) || ! filled($this->clientSecret())) {
            throw new RuntimeException(__('Google Business OAuth is not configured. Set GOOGLE_BUSINESS_CLIENT_ID and GOOGLE_BUSINESS_CLIENT_SECRET.'));
        }
    }

    protected function clientId(): string
    {
        return $this->setting('client_id', 'GOOGLE_BUSINESS_CLIENT_ID');
    }

    protected function clientSecret(): string
    {
        return $this->setting('client_secret', 'GOOGLE_BUSINESS_CLIENT_SECRET');
    }

    public function redirectUri(): string
    {
        return (string) config('services.google_business.redirect', route('portal.google-business.callback'));
    }

    protected function setting(string $key, string $envKey): string
    {
        if (class_exists(OptionStore::class)) {
            $value = (string) app(OptionStore::class)->get("integration_google_business_profile_{$key}", '');

            if (filled($value)) {
                return $value;
            }
        }

        return (string) config("services.google_business.{$key}", env($envKey, ''));
    }
}
