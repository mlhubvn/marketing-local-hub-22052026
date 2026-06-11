<?php

namespace Modules\AppGoogleBusiness\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppGoogleBusiness\Models\GoogleAutoReplyLog;
use Modules\AppGoogleBusiness\Models\GoogleAutoReplyRule;
use Modules\AppGoogleBusiness\Models\GoogleBusinessConnection;
use Modules\AppGoogleBusiness\Models\GoogleBusinessLocation;
use Modules\AppGoogleBusiness\Models\GoogleBusinessPost;
use Modules\AppGoogleBusiness\Models\GoogleBusinessPostLog;
use Modules\AppGoogleBusiness\Models\GoogleReview;
use Modules\AppGoogleBusiness\Support\GoogleAutoReplyService;
use Modules\AppGoogleBusiness\Support\GoogleBusinessAccess;
use Modules\AppGoogleBusiness\Support\GoogleBusinessClient;
use Throwable;

#[Title('Google Business')]
class GoogleBusinessIndex extends Component
{
    use WithPagination;

    public string $statusMessage = '';

    public string $errorMessage = '';

    public ?int $mapBusinessId = null;

    public string $replyText = '';

    public array $replyDrafts = [];

    public array $locationCandidates = [];

    public bool $mappingOpen = false;

    public bool $duplicateImportOpen = false;

    public ?int $duplicateImportLocationId = null;

    public array $duplicateBusinessMatches = [];

    public bool $confirmOpen = false;

    public string $confirmAction = '';

    public ?int $confirmLocationId = null;

    public string $reviewSearch = '';

    public string $reviewRating = 'all';

    public string $replyStatus = 'all';

    public string $reviewDateRange = 'all';

    public int $reviewsPerPage = 10;

    public string $analyticsRange = 'all';

    public string $analyticsLocation = 'all';

    public string $analyticsRating = 'all';

    public string $analyticsReplyStatus = 'all';

    public string $postSearch = '';

    public string $postStatus = 'all';

    public string $postType = 'standard';

    public ?int $postLocationId = null;

    public string $postTitle = '';

    public string $postSummary = '';

    public string $postCtaType = 'LEARN_MORE';

    public string $postCtaUrl = '';

    public string $postMediaUrl = '';

    public string $postCouponCode = '';

    public string $postTerms = '';

    public string $postStartAt = '';

    public string $postEndAt = '';

    public string $postScheduledAt = '';

    public ?int $editingPostId = null;

    public bool $postFormOpen = false;

    #[Url(as: 'tab', except: 'overview')]
    public string $tab = 'overview';

    #[Url(as: 'location', except: null)]
    public ?int $selectedLocationId = null;

    public string $ruleName = '';

    public ?int $ruleLocationId = null;

    public ?int $ruleBusinessId = null;

    public string $ruleRatingCondition = 'positive';

    public string $ruleCustomRatingOperator = '>=';

    public int $ruleCustomRatingValue = 4;

    public string $ruleTextCondition = 'any';

    public string $ruleKeyword = '';

    public string $ruleReplyMode = 'draft';

    public string $ruleTemplateReply = '';

    public string $ruleTone = 'professional';

    public string $ruleLanguage = 'same';

    public int $ruleDelayMinutes = 0;

    public string $ruleStatus = 'active';

    public string $autoReplyRuleStatus = 'all';

    public bool $autoReplyRuleFormOpen = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canUsePlanFeature('google_business'), 403);

        $this->statusMessage = (string) session('google_business_status', '');
        $this->errorMessage = (string) session('google_business_error', '');
        $this->locationCandidates = array_values((array) data_get(session('google_business_location_candidates', []), 'locations', []));

        if (! in_array($this->tab, ['overview', 'locations', 'reviews', 'posts', 'auto_reply', 'analytics'], true)) {
            $this->tab = 'overview';
        }
    }

    public function syncConnection(int $connectionId): void
    {
        $connection = $this->connectionQuery()->findOrFail($connectionId);

        try {
            $this->locationCandidates = app(GoogleBusinessClient::class)->fetchLocationCandidates($connection);
            session()->put('google_business_location_candidates', [
                'connection_id' => $connection->id,
                'locations' => $this->locationCandidates,
            ]);
            $connection->forceFill(['last_error' => null])->save();
            $this->tab = 'locations';
            $this->statusMessage = __('Choose which Google locations you want to add and manage. :count locations are available.', ['count' => count($this->locationCandidates)]);
            $this->errorMessage = '';
        } catch (Throwable $exception) {
            $client = app(GoogleBusinessClient::class);
            $client->logApiFailure('google_business.sync_connection_failed', $exception, $connection);
            $message = $client->friendlyApiErrorMessage($exception);

            $connection->forceFill(['last_error' => $message])->save();
            $this->errorMessage = $message;
        }
    }

    public function disconnect(int $connectionId): void
    {
        $this->connectionQuery()->findOrFail($connectionId)->delete();
        $this->selectedLocationId = null;
        $this->statusMessage = __('Google Business connection removed.');
    }

    public function selectLocation(int $locationId): void
    {
        $location = $this->locationQuery()->findOrFail($locationId);
        $this->selectedLocationId = $location->id;
        $this->mapBusinessId = $location->business_id;
        $this->resetPage('googleReviewsPage');
    }

    public function updatedSelectedLocationId(mixed $value): void
    {
        if (! $value) {
            return;
        }

        $this->resetPage('googleReviewsPage');
        $this->selectLocation((int) $value);
    }

    public function updatedReviewSearch(): void
    {
        $this->resetPage('googleReviewsPage');
    }

    public function updatedReviewRating(): void
    {
        $this->resetPage('googleReviewsPage');
    }

    public function updatedReplyStatus(): void
    {
        $this->resetPage('googleReviewsPage');
    }

    public function updatedReviewDateRange(): void
    {
        $this->resetPage('googleReviewsPage');
    }

    public function updatedReviewsPerPage(): void
    {
        $this->resetPage('googleReviewsPage');
    }

    public function updatedPostSearch(): void
    {
        $this->resetPage('googlePostsPage');
    }

    public function updatedPostStatus(): void
    {
        $this->resetPage('googlePostsPage');
    }

    public function openMapping(int $locationId): void
    {
        $this->selectLocation($locationId);
        $this->mappingOpen = true;
    }

    public function mapLocation(): void
    {
        $validated = $this->validate([
            'selectedLocationId' => ['required', 'integer'],
            'mapBusinessId' => ['required', Rule::exists('lb_businesses', 'id')->where('user_id', auth()->id())],
        ]);

        $location = $this->locationQuery()->findOrFail($validated['selectedLocationId']);
        $business = LocalBusiness::query()->where('user_id', auth()->id())->findOrFail($validated['mapBusinessId']);

        $location->forceFill([
            'business_id' => $business->id,
            'is_managed' => true,
            'managed_at' => $location->managed_at ?: now(),
        ])->save();

        if ($location->sync_business_info) {
            $business->forceFill([
                'name' => $location->name ?: $business->name,
                'phone' => $location->phone ?: $business->phone,
                'website' => $location->website ?: $business->website,
                'address' => $location->address ?: $business->address,
                'google_maps_url' => $location->review_url ?: $business->google_maps_url,
                'opening_hours' => $location->sync_hours ? $location->opening_hours : $business->opening_hours,
            ])->save();
        }

        $this->statusMessage = __('Google location mapped to :business.', ['business' => $business->name]);
        $this->mappingOpen = false;
    }

    public function syncLocationInfo(int $locationId): void
    {
        $location = $this->locationQuery()->with('business')->findOrFail($locationId);

        if (! $location->business) {
            $this->errorMessage = __('Map this Google location to a LocalBoost business first.');
            return;
        }

        $location->business->forceFill([
            'name' => $location->name ?: $location->business->name,
            'phone' => $location->phone ?: $location->business->phone,
            'website' => $location->website ?: $location->business->website,
            'address' => $location->address ?: $location->business->address,
            'google_maps_url' => $location->review_url ?: $location->business->google_maps_url,
            'opening_hours' => $location->sync_hours ? $location->opening_hours : $location->business->opening_hours,
        ])->save();

        $this->statusMessage = __('Business info synced from Google location.');
        $this->errorMessage = '';
    }

    public function requestCreateBusinessFromLocation(int $locationId): void
    {
        $location = $this->locationQuery()->with('business')->findOrFail($locationId);
        $matches = $this->duplicateBusinessMatchesForLocation($location);

        if ($matches !== []) {
            $this->duplicateImportLocationId = $location->id;
            $this->duplicateBusinessMatches = $matches;
            $this->duplicateImportOpen = true;
            return;
        }

        $this->createBusinessFromLocation($location->id);
    }

    public function confirmCreateDuplicateBusiness(): void
    {
        if (! $this->duplicateImportLocationId) {
            $this->cancelDuplicateImport();
            return;
        }

        $this->createBusinessFromLocation($this->duplicateImportLocationId);
        $this->cancelDuplicateImport();
    }

    public function cancelDuplicateImport(): void
    {
        $this->duplicateImportOpen = false;
        $this->duplicateImportLocationId = null;
        $this->duplicateBusinessMatches = [];
    }

    public function createBusinessFromLocation(int $locationId): void
    {
        $location = $this->locationQuery()->findOrFail($locationId);

        $business = LocalBusiness::query()->create([
            'user_id' => auth()->id(),
            'name' => $location->name,
            'type' => 'other',
            'phone' => $location->phone,
            'website' => $location->website,
            'address' => $location->address,
            'google_maps_url' => $location->review_url,
            'opening_hours' => $location->opening_hours,
        ]);

        $location->forceFill([
            'business_id' => $business->id,
            'is_managed' => true,
            'managed_at' => $location->managed_at ?: now(),
        ])->save();
        $this->selectedLocationId = $location->id;
        $this->mapBusinessId = $business->id;
        $this->statusMessage = __('Business created from Google location.');
        $this->mappingOpen = false;
    }

    protected function duplicateBusinessMatchesForLocation(GoogleBusinessLocation $location): array
    {
        $matches = collect();

        if ($location->business) {
            $matches->push($location->business);
        }

        $canCompare = filled($location->phone)
            || filled($location->website)
            || (filled($location->name) && filled($location->address));

        if (! $canCompare) {
            return $matches
                ->unique('id')
                ->map(fn (LocalBusiness $business): array => [
                    'id' => $business->id,
                    'name' => $business->name,
                    'phone' => $business->phone,
                    'website' => $business->website,
                    'address' => $business->address,
                ])
                ->values()
                ->all();
        }

        $query = LocalBusiness::query()->where('user_id', auth()->id());

        $query->where(function ($builder) use ($location): void {
            if (filled($location->phone)) {
                $builder->orWhere('phone', $location->phone);
            }

            if (filled($location->website)) {
                $builder->orWhere('website', $location->website);
            }

            if (filled($location->name) && filled($location->address)) {
                $builder->orWhere(function ($nested) use ($location): void {
                    $nested->where('name', $location->name)->where('address', $location->address);
                });
            }
        });

        $query->limit(5)->get()->each(fn (LocalBusiness $business) => $matches->push($business));

        return $matches
            ->unique('id')
            ->map(fn (LocalBusiness $business): array => [
                'id' => $business->id,
                'name' => $business->name,
                'phone' => $business->phone,
                'website' => $business->website,
                'address' => $business->address,
            ])
            ->values()
            ->all();
    }

    public function toggleLocationSync(int $locationId, string $field): void
    {
        abort_unless(in_array($field, ['sync_business_info', 'sync_hours', 'sync_reviews', 'sync_insights', 'auto_reply_enabled'], true), 404);

        $location = $this->locationQuery()->findOrFail($locationId);
        $location->forceFill([$field => ! (bool) $location->{$field}])->save();
    }

    public function setTab(string $tab): void
    {
        abort_unless(in_array($tab, ['overview', 'locations', 'reviews', 'posts', 'auto_reply', 'analytics'], true), 404);

        $this->closeOpenModals();
        $this->tab = $tab;
    }

    public function openGooglePostForm(): void
    {
        $this->resetGooglePostForm($this->postLocationId);
        $this->postFormOpen = true;
        $this->tab = 'posts';
        $this->dispatch('google-post-modal-open');
    }

    public function editGooglePost(int $postId): void
    {
        $post = GoogleBusinessPost::query()
            ->where('team_id', auth()->id())
            ->findOrFail($postId);

        $this->editingPostId = $post->id;
        $this->postLocationId = (int) $post->google_business_location_id;
        $this->postType = in_array((string) $post->type, ['standard', 'offer', 'event'], true) ? (string) $post->type : 'standard';
        $this->postTitle = (string) ($post->title ?? '');
        $this->postSummary = (string) $post->summary;
        $this->postCtaType = (string) ($post->cta_type ?: 'LEARN_MORE');
        $this->postCtaUrl = (string) ($post->cta_url ?? '');
        $this->postMediaUrl = (string) ($post->media_url ?? '');
        $this->postCouponCode = (string) ($post->coupon_code ?? '');
        $this->postTerms = (string) ($post->terms ?? '');
        $this->postStartAt = $post->start_at ? $post->start_at->format('Y-m-d') : '';
        $this->postEndAt = $post->end_at ? $post->end_at->format('Y-m-d') : '';
        $this->postScheduledAt = $post->scheduled_at ? $post->scheduled_at->format('Y-m-d H:i:s') : '';
        $this->postFormOpen = true;
        $this->tab = 'posts';
        $this->dispatch('google-post-modal-open');
    }

    public function closeGooglePostForm(): void
    {
        $this->postFormOpen = false;
        $this->editingPostId = null;
        $this->dispatch('google-post-modal-close');
    }

    public function saveGooglePost(string $publish = 'draft'): void
    {
        abort_unless(auth()->user()?->canUsePlanFeature('google_business_posts'), 403);

        $payload = $this->validate([
            'postLocationId' => ['required', 'integer'],
            'postType' => ['required', 'string', Rule::in(['standard', 'offer', 'event'])],
            'postTitle' => ['nullable', 'string', 'max:255'],
            'postSummary' => ['required', 'string', 'max:1500'],
            'postCtaType' => ['nullable', 'string', Rule::in(['BOOK', 'ORDER', 'SHOP', 'LEARN_MORE', 'SIGN_UP', 'CALL'])],
            'postCtaUrl' => ['nullable', 'url', 'max:1000'],
            'postMediaUrl' => ['nullable', 'url', 'max:1000'],
            'postCouponCode' => ['nullable', 'string', 'max:120'],
            'postTerms' => ['nullable', 'string', 'max:1000'],
            'postStartAt' => ['nullable', 'date'],
            'postEndAt' => ['nullable', 'date', 'after_or_equal:postStartAt'],
            'postScheduledAt' => [$publish === 'schedule' ? 'required' : 'nullable', 'date'],
        ]);

        $location = $this->locationQuery()->where('is_managed', true)->findOrFail((int) $payload['postLocationId']);
        $scheduledAt = $payload['postScheduledAt'] ?: null;

        $post = $this->editingPostId
            ? GoogleBusinessPost::query()->where('team_id', auth()->id())->findOrFail($this->editingPostId)
            : new GoogleBusinessPost([
                'team_id' => auth()->id(),
            ]);

        $post->fill([
            'team_id' => auth()->id(),
            'google_business_location_id' => $location->id,
            'business_id' => $location->business_id,
            'type' => $payload['postType'],
            'title' => $payload['postTitle'] ?: null,
            'summary' => $payload['postSummary'],
            'cta_type' => $payload['postType'] === 'offer' ? null : ($payload['postCtaType'] ?: null),
            'cta_url' => $payload['postCtaUrl'] ?: null,
            'media_url' => $payload['postMediaUrl'] ?: null,
            'coupon_code' => $payload['postCouponCode'] ?: null,
            'terms' => $payload['postTerms'] ?: null,
            'start_at' => $payload['postStartAt'] ?: null,
            'end_at' => $payload['postEndAt'] ?: null,
            'scheduled_at' => $scheduledAt,
            'status' => $publish === 'schedule' ? 'scheduled' : ($this->editingPostId ? 'draft' : 'draft'),
            'google_post_id' => null,
            'google_post_name' => null,
            'search_url' => null,
            'published_at' => null,
            'error_message' => null,
        ])->save();

        $wasEditing = (bool) $this->editingPostId;

        $this->resetGooglePostForm($location->id);
        $this->postFormOpen = false;
        $this->dispatch('google-post-modal-close');

        if ($publish === 'publish') {
            $this->publishGooglePost($post->id);
            return;
        }

        if ($publish === 'schedule') {
            $this->statusMessage = $wasEditing ? __('Google Business post updated and scheduled.') : __('Google Business post scheduled.');
            $this->tab = 'posts';
            return;
        }

        $this->statusMessage = $wasEditing ? __('Google Business post updated.') : __('Google Business post saved as draft.');
        $this->tab = 'posts';
    }

    public function publishGooglePost(int $postId): void
    {
        abort_unless(auth()->user()?->canUsePlanFeature('google_business_posts'), 403);

        $post = GoogleBusinessPost::query()
            ->where('team_id', auth()->id())
            ->with('location.connection')
            ->findOrFail($postId);

        try {
            $result = app(GoogleBusinessClient::class)->publishPost($post);
            GoogleBusinessPostLog::record([
                'team_id' => auth()->id(),
                'post_id' => $post->id,
                'action' => 'create',
                'status' => 'success',
                'request_payload' => $result['request'] ?? [],
                'response_body' => $result['response'] ?? [],
            ]);
            $this->statusMessage = __('Google Business post published.');
            $this->errorMessage = '';
        } catch (Throwable $exception) {
            $requestPayload = [];

            try {
                $requestPayload = app(GoogleBusinessClient::class)->postPayload($post);
            } catch (Throwable) {
                $requestPayload = [];
            }

            $post->forceFill([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ])->save();
            GoogleBusinessPostLog::record([
                'team_id' => auth()->id(),
                'post_id' => $post->id,
                'action' => 'create',
                'status' => 'failed',
                'request_payload' => $requestPayload,
                'response_body' => ['error' => $exception->getMessage()],
                'error_message' => $exception->getMessage(),
            ]);
            $this->errorMessage = $exception->getMessage();
        }

        $this->tab = 'posts';
    }

    public function duplicateGooglePost(int $postId): void
    {
        $post = GoogleBusinessPost::query()->where('team_id', auth()->id())->findOrFail($postId);
        $copy = $post->replicate(['google_post_id', 'google_post_name', 'search_url', 'error_message', 'published_at']);
        $copy->status = 'draft';
        $copy->scheduled_at = null;
        $copy->save();

        $this->statusMessage = __('Google Business post duplicated as draft.');
    }

    public function deleteGooglePost(int $postId): void
    {
        GoogleBusinessPost::query()->where('team_id', auth()->id())->findOrFail($postId)->delete();
        $this->statusMessage = __('Google Business post deleted locally.');
    }

    protected function resetGooglePostForm(?int $locationId = null): void
    {
        $this->editingPostId = null;
        $this->postLocationId = $locationId;
        $this->postType = 'standard';
        $this->postTitle = '';
        $this->postSummary = '';
        $this->postCtaType = 'LEARN_MORE';
        $this->postCtaUrl = '';
        $this->postMediaUrl = '';
        $this->postCouponCode = '';
        $this->postTerms = '';
        $this->postStartAt = '';
        $this->postEndAt = '';
        $this->postScheduledAt = '';
    }

    public function manageLocation(int $locationId): void
    {
        $location = $this->locationQuery()->findOrFail($locationId);
        $location->forceFill([
            'is_managed' => true,
            'managed_at' => $location->managed_at ?: now(),
            'sync_reviews' => true,
        ])->save();

        $this->selectedLocationId = $location->id;
        $this->mapBusinessId = $location->business_id;
        $this->statusMessage = __(':location is ready to manage.', ['location' => $location->name]);
    }

    public function importCandidateLocation(int $candidateIndex): void
    {
        $candidate = $this->locationCandidates[$candidateIndex] ?? null;

        if (! is_array($candidate)) {
            $this->errorMessage = __('This Google location is no longer available. Refresh locations and try again.');
            return;
        }

        $connection = $this->connectionQuery()->findOrFail((int) ($candidate['connection_id'] ?? 0));
        $alreadyImported = GoogleBusinessLocation::query()
            ->where('connection_id', $connection->id)
            ->where('google_location_id', (string) ($candidate['google_location_id'] ?? ''))
            ->exists();

        if (! GoogleBusinessAccess::canImportGoogleLocation($alreadyImported)) {
            $this->errorMessage = __('Your current plan allows up to :limit Google locations.', [
                'limit' => GoogleBusinessAccess::locationLimit(),
            ]);

            return;
        }

        $location = app(GoogleBusinessClient::class)->importLocation(
            $connection,
            (string) ($candidate['account_id'] ?? ''),
            (array) ($candidate['payload'] ?? [])
        );

        $location->forceFill([
            'is_managed' => true,
            'managed_at' => $location->managed_at ?: now(),
            'sync_reviews' => true,
        ])->save();

        unset($this->locationCandidates[$candidateIndex]);
        $this->locationCandidates = array_values($this->locationCandidates);

        if ($this->locationCandidates === []) {
            session()->forget('google_business_location_candidates');
        } else {
            session()->put('google_business_location_candidates', [
                'connection_id' => $connection->id,
                'locations' => $this->locationCandidates,
            ]);
        }

        $this->selectedLocationId = $location->id;
        $this->mapBusinessId = $location->business_id;
        $this->statusMessage = __('Added :location to managed Google locations.', ['location' => $location->name]);
        $this->errorMessage = '';
    }

    public function clearLocationCandidates(): void
    {
        $this->locationCandidates = [];
        session()->forget('google_business_location_candidates');
    }

    public function stopManagingLocation(int $locationId): void
    {
        $location = $this->locationQuery()->findOrFail($locationId);
        $location->forceFill([
            'is_managed' => false,
            'auto_reply_enabled' => false,
        ])->save();

        if ($this->selectedLocationId === $location->id) {
            $this->selectedLocationId = null;
        }

        $this->statusMessage = __('Stopped managing :location.', ['location' => $location->name]);
    }

    public function deleteLocation(int $locationId): void
    {
        $location = $this->locationQuery()->findOrFail($locationId);
        $name = $location->name;

        if ($this->selectedLocationId === $location->id) {
            $this->selectedLocationId = null;
            $this->mapBusinessId = null;
        }

        $location->delete();

        $this->statusMessage = __('Deleted :location from Google Business locations.', ['location' => $name]);
        $this->errorMessage = '';
    }

    public function askStopManagingLocation(int $locationId): void
    {
        $this->locationQuery()->findOrFail($locationId);
        $this->confirmLocationId = $locationId;
        $this->confirmAction = 'stop';
        $this->confirmOpen = true;
    }

    public function askDeleteLocation(int $locationId): void
    {
        $this->locationQuery()->findOrFail($locationId);
        $this->confirmLocationId = $locationId;
        $this->confirmAction = 'delete';
        $this->confirmOpen = true;
    }

    public function cancelLocationConfirm(): void
    {
        $this->confirmOpen = false;
        $this->confirmAction = '';
        $this->confirmLocationId = null;
    }

    public function confirmLocationAction(): void
    {
        if (! $this->confirmLocationId) {
            $this->cancelLocationConfirm();
            return;
        }

        if ($this->confirmAction === 'stop') {
            $this->stopManagingLocation($this->confirmLocationId);
        }

        if ($this->confirmAction === 'delete') {
            $this->deleteLocation($this->confirmLocationId);
        }

        $this->cancelLocationConfirm();
    }

    public function toggleConnectionAutoSync(int $connectionId): void
    {
        $connection = $this->connectionQuery()->findOrFail($connectionId);
        $connection->forceFill(['auto_sync' => ! (bool) $connection->auto_sync])->save();
    }

    public function syncReviews(int $locationId): void
    {
        $location = $this->locationQuery()->findOrFail($locationId);
        abort_unless(auth()->user()?->canUsePlanFeature('google_review_sync'), 403);

        if (! $location->is_managed) {
            $this->errorMessage = __('Click Manage on this Google location before syncing reviews.');
            return;
        }

        try {
            $count = app(GoogleBusinessClient::class)->syncReviews($location);
            GoogleReview::query()
                ->where('google_business_location_id', $location->id)
                ->where(fn ($query) => $query->whereNull('reply')->orWhere('reply', ''))
                ->where(fn ($query) => $query->whereNull('local_reply')->orWhere('local_reply', ''))
                ->get()
                ->each(fn (GoogleReview $review) => app(GoogleAutoReplyService::class)->processReview($review));
            $this->statusMessage = __('Synced :count Google reviews.', ['count' => $count]);
            $this->errorMessage = '';
        } catch (Throwable $exception) {
            $this->errorMessage = $exception->getMessage();
        }
    }

    public function publishReply(int $reviewId): void
    {
        abort_unless(auth()->user()?->canUsePlanFeature('google_review_reply'), 403);

        $review = GoogleReview::query()
            ->where('team_id', auth()->id())
            ->with('googleLocation.connection')
            ->findOrFail($reviewId);

        $this->replyText = (string) ($this->replyDrafts[$reviewId] ?? $review->local_reply ?? $this->replyText);

        $validated = $this->validate([
            'replyText' => ['required', 'string', 'max:4096'],
        ]);

        try {
            app(GoogleBusinessClient::class)->replyToReview($review, $validated['replyText']);
            $this->replyText = '';
            unset($this->replyDrafts[$reviewId]);
            $this->statusMessage = __('Reply published to Google.');
            $this->errorMessage = '';
        } catch (Throwable $exception) {
            $this->errorMessage = $exception->getMessage();
        }
    }

    public function saveAutoReplyRule(): void
    {
        $this->ruleLocationId = $this->ruleLocationId ?: null;
        $this->ruleBusinessId = $this->ruleBusinessId ?: null;

        $validated = $this->validate([
            'ruleName' => ['required', 'string', 'max:120'],
            'ruleLocationId' => ['nullable', Rule::exists('lb_google_business_locations', 'id')->where('team_id', auth()->id())],
            'ruleBusinessId' => ['nullable', Rule::exists('lb_businesses', 'id')->where('user_id', auth()->id())],
            'ruleRatingCondition' => ['required', Rule::in(['any', 'five', 'positive', 'low', 'one_two', 'custom'])],
            'ruleCustomRatingOperator' => ['required', Rule::in(['>=', '<=', '='])],
            'ruleCustomRatingValue' => ['required', 'integer', 'min:1', 'max:5'],
            'ruleTextCondition' => ['required', Rule::in(['any', 'with_text', 'without_text', 'contains', 'not_contains'])],
            'ruleKeyword' => ['nullable', 'string', 'max:120'],
            'ruleReplyMode' => ['required', Rule::in(['manual', 'draft', 'auto_publish', 'template'])],
            'ruleTemplateReply' => ['nullable', 'string', 'max:1000'],
            'ruleTone' => ['required', Rule::in(array_keys($this->autoReplyToneOptions()))],
            'ruleLanguage' => ['required', 'string', 'max:40'],
            'ruleDelayMinutes' => ['required', 'integer', 'min:0', 'max:10080'],
            'ruleStatus' => ['required', Rule::in(['active', 'draft'])],
        ]);

        if (in_array($validated['ruleTextCondition'], ['contains', 'not_contains'], true) && blank($validated['ruleKeyword'])) {
            $this->addError('ruleKeyword', __('Keyword is required for this text condition.'));

            return;
        }

        if ($validated['ruleReplyMode'] === 'template' && blank($validated['ruleTemplateReply'])) {
            $this->addError('ruleTemplateReply', __('Template reply is required when using template mode.'));

            return;
        }

        $ratingCondition = $validated['ruleRatingCondition'] === 'custom'
            ? 'custom:'.$validated['ruleCustomRatingOperator'].':'.$validated['ruleCustomRatingValue']
            : $validated['ruleRatingCondition'];

        GoogleAutoReplyRule::query()->create([
            'team_id' => auth()->id(),
            'business_id' => $validated['ruleBusinessId'] ?: null,
            'google_business_location_id' => $validated['ruleLocationId'] ?: null,
            'name' => $validated['ruleName'],
            'rating_condition' => $ratingCondition,
            'text_condition' => $validated['ruleTextCondition'],
            'keyword' => $validated['ruleKeyword'] ?: null,
            'reply_mode' => $validated['ruleReplyMode'],
            'template_reply' => $validated['ruleTemplateReply'] ?: null,
            'tone' => $validated['ruleTone'],
            'language' => $validated['ruleLanguage'],
            'delay_minutes' => $validated['ruleDelayMinutes'],
            'status' => $validated['ruleStatus'],
        ]);

        $this->resetAutoReplyRuleForm();
        $this->autoReplyRuleFormOpen = false;
        $this->dispatch('google-auto-reply-rule-modal-close');
        $this->statusMessage = __('Auto reply rule saved.');
    }

    public function openAutoReplyRuleForm(): void
    {
        $this->resetAutoReplyRuleForm();
        $this->autoReplyRuleFormOpen = true;
        $this->tab = 'auto_reply';
        $this->dispatch('google-auto-reply-rule-modal-open');
    }

    public function closeAutoReplyRuleForm(): void
    {
        $this->autoReplyRuleFormOpen = false;
        $this->dispatch('google-auto-reply-rule-modal-close');
    }

    protected function closeOpenModals(): void
    {
        if ($this->postFormOpen || $this->editingPostId) {
            $this->postFormOpen = false;
            $this->editingPostId = null;
            $this->dispatch('google-post-modal-close');
        }

        if ($this->autoReplyRuleFormOpen) {
            $this->autoReplyRuleFormOpen = false;
            $this->dispatch('google-auto-reply-rule-modal-close');
        }
    }

    public function toggleAutoReplyRule(int $ruleId): void
    {
        $rule = GoogleAutoReplyRule::query()->where('team_id', auth()->id())->findOrFail($ruleId);
        $rule->forceFill(['status' => $rule->status === 'active' ? 'draft' : 'active'])->save();
    }

    public function deleteAutoReplyRule(int $ruleId): void
    {
        GoogleAutoReplyRule::query()->where('team_id', auth()->id())->findOrFail($ruleId)->delete();
        $this->statusMessage = __('Auto reply rule deleted.');
    }

    protected function resetAutoReplyRuleForm(): void
    {
        $this->ruleName = '';
        $this->ruleLocationId = null;
        $this->ruleBusinessId = null;
        $this->ruleRatingCondition = 'positive';
        $this->ruleCustomRatingOperator = '>=';
        $this->ruleCustomRatingValue = 4;
        $this->ruleTextCondition = 'any';
        $this->ruleKeyword = '';
        $this->ruleReplyMode = 'draft';
        $this->ruleTemplateReply = '';
        $this->ruleTone = 'professional';
        $this->ruleLanguage = 'same';
        $this->ruleDelayMinutes = 0;
        $this->ruleStatus = 'active';
    }

    public function updatedRuleRatingCondition(string $value): void
    {
        if (in_array($value, ['low', 'one_two'], true) && $this->ruleReplyMode === 'auto_publish') {
            $this->ruleReplyMode = 'draft';
        }
    }

    public function updatedPostType(string $value): void
    {
        if ($value !== 'offer') {
            $this->postCouponCode = '';
            $this->postTerms = '';
        }

        if ($value === 'offer') {
            $this->postCtaType = 'LEARN_MORE';
        }
    }

    public function updatedPostCtaType(string $value): void
    {
        if (strtoupper($value) === 'CALL') {
            $this->postCtaUrl = '';
        }
    }

    public function ruleRatingLabel(?string $condition): string
    {
        $condition = (string) $condition;

        if (str_starts_with($condition, 'custom:')) {
            $parts = explode(':', $condition);

            return __('Rating :operator :value', [
                'operator' => $parts[1] ?? '=',
                'value' => $parts[2] ?? '4',
            ]);
        }

        return match ($condition) {
            'five' => __('5 stars'),
            'positive' => __('4-5 stars'),
            'low' => __('3 stars or below'),
            'one_two' => __('1-2 stars'),
            default => __('Any rating'),
        };
    }

    public function ruleTextLabel(?string $condition): string
    {
        return match ((string) $condition) {
            'with_text' => __('Only reviews with text'),
            'without_text' => __('Only reviews without text'),
            'contains' => __('Contains keyword'),
            'not_contains' => __('Does not contain keyword'),
            default => __('Any review'),
        };
    }

    public function ruleReplyModeLabel(?string $mode): string
    {
        return match ((string) $mode) {
            'auto_publish' => __('Auto publish'),
            'template' => __('Template reply'),
            'manual' => __('Manual'),
            default => __('AI draft'),
        };
    }

    public function generateAiReply(int $reviewId): void
    {
        $review = GoogleReview::query()
            ->where('team_id', auth()->id())
            ->with('business', 'googleLocation.business')
            ->findOrFail($reviewId);

        $business = $review->business ?: $review->googleLocation?->business;
        $fallback = $this->fallbackReviewReply($review, $business?->name ?: __('our business'));

        if (! class_exists('Modules\\AppAIStudio\\Support\\AiContentStudioService') || ! $business) {
            $this->replyDrafts[$reviewId] = $fallback;
            $this->replyText = $fallback;
            $this->statusMessage = __('Fallback review reply generated.');
            return;
        }

        try {
            $aiReply = app('Modules\\AppAIStudio\\Support\\AiContentStudioService')->generateReviewReply([
                'business_name' => $business->name,
                'business_type' => $business->type ?: __('Local business'),
                'rating' => max(1, (int) $review->rating),
                'reply_type' => ((int) $review->rating) >= 4 ? 'Public review reply' : 'Private feedback recovery',
                'customer_name' => (string) $review->reviewer_name,
                'review_text' => (string) ($review->comment ?: __('Customer left a :rating star Google review.', ['rating' => $review->rating])),
                'tone' => 'professional',
                'language' => 'English',
            ]);

            $this->replyDrafts[$reviewId] = (string) data_get($aiReply, 'suggested_reply', $fallback);
            $this->replyText = $this->replyDrafts[$reviewId];
            $this->statusMessage = __('AI review reply generated.');
        } catch (Throwable $exception) {
            $this->replyDrafts[$reviewId] = $fallback;
            $this->replyText = $fallback;
            $this->statusMessage = __('AI unavailable. A fallback reply was generated.');
        }
    }

    public function render(): View
    {
        $connections = $this->connectionQuery()->withCount('locations')->latest()->get();
        $locations = $this->locationQuery()->with('business')->latest()->get();
        $managedLocations = $locations->where('is_managed', true)->values();
        $selectedLocation = $this->selectedLocationId
            ? $this->locationQuery()->with('business')->where('is_managed', true)->find($this->selectedLocationId)
            : $managedLocations->first();

        if (! $selectedLocation && $managedLocations->isNotEmpty()) {
            $selectedLocation = $managedLocations->first();
            $this->selectedLocationId = $selectedLocation->id;
            $this->mapBusinessId = $selectedLocation->business_id;
        }

        if ($selectedLocation && $this->selectedLocationId === null) {
            $this->selectedLocationId = $selectedLocation->id;
            $this->mapBusinessId = $selectedLocation->business_id;
        }

        if ($this->postLocationId === null && $managedLocations->isNotEmpty()) {
            $this->postLocationId = (int) $managedLocations->first()->id;
        }

        $reviewBaseQuery = $selectedLocation
            ? GoogleReview::query()->where('team_id', auth()->id())->where('google_business_location_id', $selectedLocation->id)
            : GoogleReview::query()->whereRaw('1 = 0');

        $reviewSummaryRows = (clone $reviewBaseQuery)->get(['rating', 'reply', 'local_reply', 'last_synced_at']);
        $reviewQuery = (clone $reviewBaseQuery)
            ->when($this->reviewSearch !== '', function ($query): void {
                $search = '%'.$this->reviewSearch.'%';
                $query->where(fn ($inner) => $inner
                    ->where('reviewer_name', 'like', $search)
                    ->orWhere('comment', 'like', $search)
                    ->orWhere('reply', 'like', $search));
            })
            ->when($this->reviewRating !== 'all', fn ($query) => $query->where('rating', (int) $this->reviewRating))
            ->when($this->replyStatus === 'replied', fn ($query) => $query->whereNotNull('reply')->where('reply', '!=', ''))
            ->when($this->replyStatus === 'draft', fn ($query) => $query->whereNotNull('local_reply')->where('local_reply', '!=', ''))
            ->when($this->replyStatus === 'not_replied', fn ($query) => $query->where(fn ($inner) => $inner->whereNull('reply')->orWhere('reply', ''))->where(fn ($inner) => $inner->whereNull('local_reply')->orWhere('local_reply', '')))
            ->when($this->reviewDateRange === '7', fn ($query) => $query->where('review_created_at', '>=', now()->subDays(7)))
            ->when($this->reviewDateRange === '30', fn ($query) => $query->where('review_created_at', '>=', now()->subDays(30)));

        $managedLocationIds = $managedLocations->pluck('id')->all();
        $allReviewsQuery = GoogleReview::query()
            ->where('team_id', auth()->id())
            ->when(
                $managedLocations->isNotEmpty(),
                fn ($query) => $query->whereIn('google_business_location_id', $managedLocationIds),
                fn ($query) => $query->whereRaw('1 = 0')
            );
        $allReviewRows = (clone $allReviewsQuery)->get(['rating', 'reply', 'local_reply', 'last_synced_at']);
        $hasAutoReplyTables = Schema::hasTable('lb_google_auto_reply_rules') && Schema::hasTable('lb_google_auto_reply_logs');
        $analyticsQuery = (clone $allReviewsQuery)
            ->with('googleLocation.business')
            ->when($this->analyticsLocation !== 'all', fn ($query) => $query->where('google_business_location_id', (int) $this->analyticsLocation))
            ->when($this->analyticsRating !== 'all', fn ($query) => $query->where('rating', (int) $this->analyticsRating))
            ->when($this->analyticsReplyStatus === 'replied', fn ($query) => $query->whereNotNull('reply')->where('reply', '!=', ''))
            ->when($this->analyticsReplyStatus === 'draft', fn ($query) => $query->whereNotNull('local_reply')->where('local_reply', '!=', ''))
            ->when($this->analyticsReplyStatus === 'not_replied', fn ($query) => $query->where(fn ($inner) => $inner->whereNull('reply')->orWhere('reply', ''))->where(fn ($inner) => $inner->whereNull('local_reply')->orWhere('local_reply', '')))
            ->when(in_array($this->analyticsRange, ['7', '30', '90'], true), fn ($query) => $query->where('review_created_at', '>=', now()->subDays((int) $this->analyticsRange)));

        $analyticsReviews = $analyticsQuery->latest('review_created_at')->get();
        $repliedCount = $analyticsReviews->filter(fn ($review) => filled($review->reply))->count();
        $draftCount = $analyticsReviews->filter(fn ($review) => filled($review->local_reply))->count();
        $notRepliedCount = $analyticsReviews->filter(fn ($review) => blank($review->reply) && blank($review->local_reply))->count();
        $autoReplyCount = $analyticsReviews->filter(fn ($review) => in_array((string) $review->auto_reply_status, ['generated', 'published'], true))->count();
        $failedReplyCount = $analyticsReviews->filter(fn ($review) => (string) $review->auto_reply_status === 'failed')->count();
        $replyRate = $analyticsReviews->count() > 0 ? (int) round((($repliedCount + $draftCount) / $analyticsReviews->count()) * 100) : 0;
        $autoReplySuccessRate = ($autoReplyCount + $failedReplyCount) > 0 ? (int) round(($autoReplyCount / ($autoReplyCount + $failedReplyCount)) * 100) : 0;
        $trendLabels = [];
        $newReviewSeries = [];
        $repliedSeries = [];
        $lowScoreSeries = [];

        if ($this->analyticsRange === 'all') {
            $months = $analyticsReviews
                ->filter(fn ($review) => filled($review->review_created_at))
                ->groupBy(fn ($review) => $review->review_created_at->format('Y-m'))
                ->sortKeys();

            foreach ($months as $key => $monthRows) {
                $date = \Carbon\Carbon::createFromFormat('Y-m', $key);

                $trendLabels[] = format_date_locale($date, 'm/Y');
                $newReviewSeries[] = $monthRows->count();
                $repliedSeries[] = $monthRows->filter(fn ($review) => filled($review->reply) || filled($review->local_reply))->count();
                $lowScoreSeries[] = $monthRows->where('rating', '<=', 3)->count();
            }
        } else {
            $trendDays = in_array($this->analyticsRange, ['7', '30', '90'], true) ? (int) $this->analyticsRange : 30;
            $trendStart = now()->subDays($trendDays - 1)->startOfDay();

            for ($day = 0; $day < $trendDays; $day++) {
                $date = $trendStart->copy()->addDays($day);
                $key = $date->format('Y-m-d');
                $dayRows = $analyticsReviews->filter(fn ($review) => $review->review_created_at && $review->review_created_at->format('Y-m-d') === $key);

                $trendLabels[] = format_date_locale($date, 'd/m');
                $newReviewSeries[] = $dayRows->count();
                $repliedSeries[] = $dayRows->filter(fn ($review) => filled($review->reply) || filled($review->local_reply))->count();
                $lowScoreSeries[] = $dayRows->where('rating', '<=', 3)->count();
            }
        }

        $ratingDistribution = collect([5, 4, 3, 2, 1])
            ->map(fn (int $rating): array => [
                'rating' => $rating,
                'count' => $analyticsReviews->where('rating', $rating)->count(),
                'percent' => $analyticsReviews->count() > 0 ? (int) round(($analyticsReviews->where('rating', $rating)->count() / $analyticsReviews->count()) * 100) : 0,
            ])
            ->values();

        $topLocations = $managedLocations
            ->map(function (GoogleBusinessLocation $location) use ($analyticsReviews): array {
                $rows = $analyticsReviews->where('google_business_location_id', $location->id);

                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'business' => $location->business?->name,
                    'reviews' => $rows->count(),
                    'average' => $rows->count() > 0 ? round((float) $rows->avg('rating'), 1) : 0,
                    'not_replied' => $rows->filter(fn ($review) => blank($review->reply) && blank($review->local_reply))->count(),
                    'low_score' => $rows->where('rating', '<=', 3)->count(),
                    'last_review' => $rows->max('review_created_at'),
                ];
            })
            ->filter(fn (array $location): bool => $location['reviews'] > 0)
            ->sortByDesc('reviews')
            ->values()
            ->take(8);

        $lowScoreReviews = $analyticsReviews
            ->filter(fn ($review) => (int) $review->rating <= 3 && blank($review->reply))
            ->take(5)
            ->values();

        $postQuery = GoogleBusinessPost::query()
            ->where('team_id', auth()->id())
            ->with('location.business', 'business')
            ->when($this->postStatus !== 'all', fn ($query) => $query->where('status', $this->postStatus))
            ->when($this->postSearch !== '', function ($query): void {
                $search = '%'.trim($this->postSearch).'%';
                $query->where(fn ($inner) => $inner
                    ->where('title', 'like', $search)
                    ->orWhere('summary', 'like', $search)
                    ->orWhereHas('location', fn ($locationQuery) => $locationQuery->where('name', 'like', $search)));
            });

        $postRows = GoogleBusinessPost::query()->where('team_id', auth()->id())->get(['status', 'type', 'published_at', 'scheduled_at']);

        return view('appgooglebusiness::index', [
            'connections' => $connections,
            'locations' => $locations,
            'locationCandidates' => $this->locationCandidates,
            'managedLocations' => $managedLocations,
            'selectedLocation' => $selectedLocation,
            'reviews' => $reviewQuery->latest('review_created_at')->paginate($this->reviewsPerPage, ['*'], 'googleReviewsPage'),
            'reviewSummary' => [
                'total' => $reviewSummaryRows->count(),
                'average' => $reviewSummaryRows->count() > 0 ? round((float) $reviewSummaryRows->avg('rating'), 1) : 0,
                'unreplied' => $reviewSummaryRows->filter(fn ($review) => blank($review->reply) && blank($review->local_reply))->count(),
                'replied' => $reviewSummaryRows->filter(fn ($review) => filled($review->reply))->count(),
                'drafts' => $reviewSummaryRows->filter(fn ($review) => filled($review->local_reply))->count(),
                'low_score' => $reviewSummaryRows->where('rating', '<=', 3)->count(),
                'last_synced_at' => $reviewSummaryRows->max('last_synced_at'),
            ],
            'analyticsSummary' => [
                'channels' => $connections->count(),
                'locations' => $locations->count(),
                'managed_locations' => $managedLocations->count(),
                'mapped_locations' => $managedLocations->whereNotNull('business_id')->count(),
                'total_reviews' => $allReviewRows->count(),
                'average_rating' => $allReviewRows->count() > 0 ? round((float) $allReviewRows->avg('rating'), 1) : 0,
                'not_replied' => $allReviewRows->filter(fn ($review) => blank($review->reply) && blank($review->local_reply))->count(),
                'replied' => $allReviewRows->filter(fn ($review) => filled($review->reply))->count(),
                'low_score' => $allReviewRows->where('rating', '<=', 3)->count(),
            ],
            'reviewAnalytics' => [
                'total' => $analyticsReviews->count(),
                'average_rating' => $analyticsReviews->count() > 0 ? round((float) $analyticsReviews->avg('rating'), 1) : 0,
                'replied' => $repliedCount,
                'drafts' => $draftCount,
                'not_replied' => $notRepliedCount,
                'low_score' => $analyticsReviews->where('rating', '<=', 3)->count(),
                'auto_replies' => $autoReplyCount,
                'failed_replies' => $failedReplyCount,
                'reply_rate' => $replyRate,
                'auto_reply_success_rate' => $autoReplySuccessRate,
                'trend_categories' => $trendLabels,
                'trend_series' => [
                    ['name' => __('New reviews'), 'data' => $newReviewSeries],
                    ['name' => __('Replied or drafted'), 'data' => $repliedSeries],
                    ['name' => __('Low-score'), 'data' => $lowScoreSeries],
                ],
                'rating_distribution' => $ratingDistribution,
                'top_locations' => $topLocations,
                'low_score_reviews' => $lowScoreReviews,
            ],
            'googlePosts' => $postQuery->latest()->paginate(10, ['*'], 'googlePostsPage'),
            'googlePostSummary' => [
                'total' => $postRows->count(),
                'draft' => $postRows->where('status', 'draft')->count(),
                'scheduled' => $postRows->where('status', 'scheduled')->count(),
                'published' => $postRows->where('status', 'published')->count(),
                'failed' => $postRows->where('status', 'failed')->count(),
                'offers' => $postRows->where('type', 'offer')->count(),
            ],
            'googlePostLogs' => Schema::hasTable('lb_google_business_post_logs')
                ? GoogleBusinessPostLog::query()->where('team_id', auth()->id())->with('post')->latest()->limit(GoogleBusinessPostLog::MAX_LOGS_PER_TEAM)->get()
                : collect(),
            'autoReplyRules' => $hasAutoReplyTables
                ? GoogleAutoReplyRule::query()
                    ->where('team_id', auth()->id())
                    ->with('location', 'business')
                    ->when($this->autoReplyRuleStatus !== 'all', fn ($query) => $query->where('status', $this->autoReplyRuleStatus))
                    ->latest()
                    ->get()
                : collect(),
            'autoReplyRuleSummary' => $hasAutoReplyTables
                ? [
                    'total' => GoogleAutoReplyRule::query()->where('team_id', auth()->id())->count(),
                    'active' => GoogleAutoReplyRule::query()->where('team_id', auth()->id())->where('status', 'active')->count(),
                    'draft' => GoogleAutoReplyRule::query()->where('team_id', auth()->id())->where('status', 'draft')->count(),
                ]
                : ['total' => 0, 'active' => 0, 'draft' => 0],
            'autoReplyLogs' => $hasAutoReplyTables
                ? GoogleAutoReplyLog::query()->where('team_id', auth()->id())->with('rule', 'review')->latest()->limit(25)->get()
                : collect(),
            'autoReplyToneOptions' => $this->autoReplyToneComboboxOptions(),
            'autoReplyLanguageOptions' => $this->autoReplyLanguageComboboxOptions(),
            'businesses' => LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get(),
            'configured' => $this->isGoogleConfigured(),
            'callbackUrl' => app(GoogleBusinessClient::class)->redirectUri(),
            'googleCloudSetupRequired' => $this->googleCloudSetupRequired($connections),
            'hasTables' => Schema::hasTable('lb_google_business_connections'),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Google Business'),
        ]);
    }

    protected function connectionQuery()
    {
        return GoogleBusinessConnection::query()->where('team_id', auth()->id());
    }

    protected function locationQuery()
    {
        return GoogleBusinessLocation::query()->where('team_id', auth()->id());
    }

    protected function isGoogleConfigured(): bool
    {
        $clientId = (string) config('services.google_business.client_id', env('GOOGLE_BUSINESS_CLIENT_ID', ''));
        $clientSecret = (string) config('services.google_business.client_secret', env('GOOGLE_BUSINESS_CLIENT_SECRET', ''));

        if (class_exists(OptionStore::class)) {
            $options = app(OptionStore::class);
            $clientId = (string) ($options->get('integration_google_business_profile_client_id', '') ?: $clientId);
            $clientSecret = (string) ($options->get('integration_google_business_profile_client_secret', '') ?: $clientSecret);
        }

        return filled($clientId) && filled($clientSecret);
    }

    protected function googleCloudSetupRequired($connections): bool
    {
        foreach ($connections as $connection) {
            $error = (string) $connection->last_error;

            if ($error === '') {
                continue;
            }

            if (str_contains($error, 'Application For Basic API Access')
                || str_contains($error, 'My Business Account Management API')
                || str_contains($error, 'Business Profile API access')
            ) {
                return true;
            }
        }

        return false;
    }

    protected function autoReplyToneOptions(): array
    {
        return [
            'professional' => __('Professional'),
            'friendly' => __('Friendly'),
            'sales' => __('Sales'),
            'educational' => __('Educational'),
            'bold' => __('Bold'),
            'casual' => __('Casual'),
            'apologetic' => __('Apologetic'),
            'grateful' => __('Grateful'),
        ];
    }

    protected function autoReplyToneComboboxOptions(): array
    {
        $icons = [
            'professional' => 'fa-briefcase',
            'friendly' => 'fa-face-smile',
            'sales' => 'fa-bullhorn',
            'educational' => 'fa-graduation-cap',
            'bold' => 'fa-bolt',
            'casual' => 'fa-mug-hot',
            'apologetic' => 'fa-hand-heart',
            'grateful' => 'fa-heart',
        ];

        return collect($this->autoReplyToneOptions())
            ->map(fn ($label, $value): array => [
                'value' => $value,
                'label' => $label,
                'icon' => $icons[$value] ?? 'fa-sliders',
            ])
            ->values()
            ->all();
    }

    protected function autoReplyLanguageComboboxOptions(): array
    {
        return collect([[
            'value' => 'same',
            'label' => __('Same as review'),
            'meta' => __('Detect from Google review'),
            'icon' => 'fa-language',
        ]])
            ->merge(collect(world_languages())->map(fn (array $language): array => [
                'value' => (string) data_get($language, 'code'),
                'label' => (string) data_get($language, 'name'),
                'meta' => strtoupper((string) data_get($language, 'code')),
                'icon' => 'fa-language',
            ]))
            ->values()
            ->all();
    }

    protected function fallbackReviewReply(GoogleReview $review, string $businessName): string
    {
        $name = trim((string) $review->reviewer_name);
        $prefix = $name !== '' ? __('Hi :name, ', ['name' => $name]) : '';

        if ((int) $review->rating >= 4) {
            return $prefix.__('thank you for taking the time to share your experience with :business. We appreciate your kind feedback and look forward to welcoming you again.', ['business' => $businessName]);
        }

        return $prefix.__('thank you for sharing this feedback. We are sorry your experience did not meet expectations, and our team will review this carefully so we can improve.');
    }
}
