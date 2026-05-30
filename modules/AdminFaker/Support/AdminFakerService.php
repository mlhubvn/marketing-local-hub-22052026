<?php

namespace Modules\AdminFaker\Support;

use App\Support\Storage\StorageDriverManager;
use Database\Support\MlhubDemoVolume;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\AdminAI\Models\AiUsageLog;
use Modules\AdminBlogCategories\Models\BlogCategory;
use Modules\AdminBlogs\Models\Blog;
use Modules\AdminBlogs\Models\BlogRssImport;
use Modules\AdminBlogs\Models\BlogRssSource;
use Modules\AdminBlogTags\Models\BlogTag;
use Modules\AdminFaqs\Models\Faq;
use Modules\AdminNotifications\Models\NotificationManual;
use Modules\AdminNotifications\Models\NotificationManualState;
use Modules\AdminPaymentHistory\Models\PaymentHistory;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminSupport\Models\SupportCategory;
use Modules\AdminSupport\Models\SupportComment;
use Modules\AdminSupport\Models\SupportLabel;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminSupport\Models\SupportType;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\AdminUser\Support\PersonalTeamProvisioner;
use Modules\AppAffiliate\Models\AffiliateCommission;
use Modules\AppAffiliate\Models\AffiliateWithdrawal;
use Modules\AppAffiliate\Support\AffiliateService;
use Modules\AppAiPublishing\Models\AiPublishingPrompt;
use Modules\AppAiPublishing\Models\AiPublishingRun;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBookingPages\Models\BookingService;
use Modules\AppBusinessLocations\Models\BusinessLocation;
use Modules\AppBusinessLocations\Support\LocationQrStyleCatalog;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppChannels\Models\SocialAccount;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppCustomers\Models\Customer;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppFiles\Models\AppFile;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppLandingPages\Support\LandingPageFactory;
use Modules\AppLandingPages\Support\PageTemplateCatalog;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppLinkBio\Models\LinkBioEvent;
use Modules\AppLinkBio\Models\LinkBioPage;
use Modules\AppQRCodes\Models\AppQrCode;
use Modules\AppQRCodes\Models\AppQrScanEvent;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppQRCampaigns\Models\QrScan;
use Modules\AppReviewBooster\Models\ReviewFeedback;
use Modules\AppShortLinkAnalytics\Models\AppShortLinkClick;
use Modules\AppShortLinks\Models\AppShortLink;
use Modules\AppPublishing\Models\PublishingPost;
use Modules\AppRssSchedules\Models\RssSchedule;

class AdminFakerService
{
    protected const DEMO_MARKER = 'admin-faker';

    public function __construct(
        protected PersonalTeamProvisioner $teamProvisioner,
        protected StorageDriverManager $storageDriverManager,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function seedForFirstUser(bool $clearBeforeSeed = true): array
    {
        $user = $this->resolveFirstUser();

        return $this->seedExistingUser($user, $clearBeforeSeed);
    }

    /**
     * @return array<string, mixed>
     */
    public function clearForFirstUser(): array
    {
        $user = $this->resolveFirstUser();

        return $this->clearByUser($user);
    }

    /**
     * @return array<string, mixed>
     */
    public function seed(string $email, string $name, bool $clearBeforeSeed = true): array
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $user = User::query()->create([
                'name' => $name,
                'username' => $this->uniqueUsername($email, $name),
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'locale' => 'vi',
                'timezone' => 'Asia/Saigon',
                'role_id' => null,
                'is_super_admin' => false,
            ]);
        }

        return $this->seedExistingUser($user, $clearBeforeSeed);
    }

    /**
     * @return array<string, mixed>
     */
    public function clear(string $email): array
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return [
                'user' => ['email' => $email, 'password_hint' => __('No account found')],
                'counts' => [],
            ];
        }

        return $this->clearByUser($user);
    }

    /**
     * @return array<string, mixed>
     */
    protected function seedExistingUser(User $user, bool $clearBeforeSeed = true): array
    {
        if ($clearBeforeSeed) {
            $this->clearByUser($user);
        }

        $user->forceFill([
            'email_verified_at' => $user->email_verified_at ?: now(),
            'locale' => $user->locale ?: 'vi',
            'timezone' => $user->timezone ?: 'Asia/Saigon',
        ])->save();

        $this->ensurePlan($user);

        $team = $this->teamProvisioner->ensureForUser($user);
        $team->update([
            'enabled_modules' => ['channels', 'publishing', 'ai_publishing', 'chat', 'link_bio', 'qr_codes', 'short_links'],
        ]);

        $now = now();
        $counts = [
            'channels' => 0,
            'media_images' => 0,
            'daily_schedules' => 0,
            'published_posts' => 0,
            'queued_posts' => 0,
            'draft_posts' => 0,
            'failed_posts' => 0,
            'processing_posts' => 0,
            'rss_schedules' => 0,
            'support_tickets' => 0,
            'support_comments' => 0,
            'affiliate_commissions' => 0,
            'affiliate_withdrawals' => 0,
            'blogs' => 0,
            'faqs' => 0,
            'ai_prompts' => 0,
            'ai_publishing_runs' => 0,
            'ai_logs' => 0,
            'global_notifications' => 0,
            'link_bio_pages' => 0,
            'link_bio_events' => 0,
            'app_qr_codes' => 0,
            'qr_scan_events' => 0,
            'short_links' => 0,
            'short_link_clicks' => 0,
            'local_businesses' => 0,
            'local_locations' => 0,
            'local_campaigns' => 0,
            'local_landing_pages' => 0,
            'local_qr_visits' => 0,
            'local_leads' => 0,
            'local_bookings' => 0,
            'local_coupon_claims' => 0,
            'local_review_ratings' => 0,
            'local_low_score_feedback' => 0,
            'local_recent_activity' => 0,
            'local_top_campaigns' => 0,
            'local_top_businesses' => 0,
        ];

        $imageFiles = $this->createDemoImages($user);
        $counts['media_images'] = count($imageFiles);

        $accounts = $this->createDemoChannels($user, $imageFiles);
        $counts['channels'] = $accounts->count();

        if (class_exists(LinkBioPage::class) && class_exists(LinkBioEvent::class)) {
            $this->createLinkBioPages($user, $team, $imageFiles, $counts);
        }
        if (class_exists(AppQrCode::class) && class_exists(AppQrScanEvent::class)) {
            $this->createQrDemoCodes($user, $team, $imageFiles, $counts);
        }
        if (class_exists(AppShortLink::class) && class_exists(AppShortLinkClick::class)) {
            $this->createShortLinks($user, $team, $imageFiles, $counts);
        }
        if (class_exists(PublishingPost::class)) {
            $this->createDailyPublishingPosts($user, $team, $accounts, $imageFiles, $now, $counts);
        }
        $this->createSupportInbox($user, $team, $counts);
        $this->createAffiliateSnapshot($user, $counts);
        $this->createFaqs($counts);
        [$category, $tags] = $this->createBlogTaxonomy();
        $blogs = $this->createBlogs($user, $category, $tags, $imageFiles, $counts);
        if (class_exists(RssSchedule::class)) {
            $this->createBlogRssSources($category, $tags, $blogs, $counts);
        }
        [$prompts, $runs] = [collect(), collect()];
        if (class_exists(AiPublishingPrompt::class) && class_exists(AiPublishingRun::class)) {
            [$prompts, $runs] = $this->createAiPublishing($user, $team, $accounts, $imageFiles, $now, $counts);
        }
        if (class_exists(AiUsageLog::class)) {
            $this->createAiLogs($user, $runs, $counts);
        }
        $this->createGlobalNotifications($user, $counts);
        $this->createLocalBoostDemoData($user, $counts);

        return [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'password_hint' => __('Preserved existing password'),
            ],
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
            ],
            'counts' => $counts,
            'prompt_ids' => $prompts->pluck('id')->all(),
            'run_ids' => $runs->pluck('id')->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function clearByUser(User $user): array
    {
        $deleted = [
            'channels' => 0,
            'media_images' => 0,
            'daily_schedules' => 0,
            'published_posts' => 0,
            'queued_posts' => 0,
            'draft_posts' => 0,
            'failed_posts' => 0,
            'processing_posts' => 0,
            'rss_schedules' => 0,
            'support_tickets' => 0,
            'support_comments' => 0,
            'affiliate_commissions' => 0,
            'affiliate_withdrawals' => 0,
            'blogs' => 0,
            'faqs' => 0,
            'ai_prompts' => 0,
            'ai_publishing_runs' => 0,
            'ai_logs' => 0,
            'global_notifications' => 0,
            'link_bio_pages' => 0,
            'link_bio_events' => 0,
            'app_qr_codes' => 0,
            'qr_scan_events' => 0,
            'short_links' => 0,
            'short_link_clicks' => 0,
            'local_businesses' => 0,
            'local_locations' => 0,
            'local_campaigns' => 0,
            'local_landing_pages' => 0,
            'local_qr_visits' => 0,
            'local_leads' => 0,
            'local_bookings' => 0,
            'local_coupon_claims' => 0,
            'local_review_ratings' => 0,
            'local_low_score_feedback' => 0,
            'local_recent_activity' => 0,
            'local_top_campaigns' => 0,
            'local_top_businesses' => 0,
        ];

        Storage::disk('public')->deleteDirectory('files/demo-faker/public/user-'.$user->id);

        $paths = AppFile::query()
            ->where('owner_user_id', $user->id)
            ->where('path', 'like', 'files/demo-faker/%')
            ->pluck('path')
            ->all();

        foreach ($paths as $path) {
            Storage::disk('public')->delete((string) $path);
        }

        $deleted['media_images'] = AppFile::query()
            ->where('owner_user_id', $user->id)
            ->where('path', 'like', 'files/demo-faker/%')
            ->delete();

        if (class_exists(LinkBioPage::class) && class_exists(LinkBioEvent::class)) {
            $this->clearLinkBioPages($user, $deleted);
        }
        if (class_exists(AppQrCode::class) && class_exists(AppQrScanEvent::class)) {
            $this->clearQrDemoCodes($user, $deleted);
        }
        if (class_exists(AppShortLink::class) && class_exists(AppShortLinkClick::class)) {
            $this->clearShortLinks($user, $deleted);
        }
        $this->clearLocalBoostDemoData($user, $deleted);

        if (class_exists(SocialAccount::class)) {
            $deleted['channels'] = SocialAccount::query()
                ->where('created_by_user_id', $user->id)
                ->get()
                ->filter(fn (SocialAccount $account): bool => data_get($account->metadata, 'source') === self::DEMO_MARKER)
                ->tap(function ($accounts): void {
                    $accounts->each->delete();
                })
                ->count();
        }

        $demoPosts = collect();

        if (class_exists(PublishingPost::class)) {
            $demoPosts = PublishingPost::query()
                ->where('user_id', $user->id)
                ->where('custom_data_2', self::DEMO_MARKER)
                ->get(['id', 'status']);
        }

        $demoPostIds = $demoPosts->pluck('id');

        if ($demoPostIds->isNotEmpty()) {
            DB::table('team_post_comments')->whereIn('post_id', $demoPostIds)->delete();
            DB::table('team_post_reviews')->whereIn('post_id', $demoPostIds)->delete();
        }

        foreach ($demoPosts as $post) {
            $this->incrementPublishingStatusCount($deleted, (int) $post->status);
        }

        if (class_exists(PublishingPost::class)) {
            $deleted['daily_schedules'] = PublishingPost::query()
                ->where('user_id', $user->id)
                ->where('custom_data_2', self::DEMO_MARKER)
                ->delete();
        }

        $rssSchedules = collect();

        if (class_exists(RssSchedule::class)) {
            $rssSchedules = RssSchedule::query()
                ->where('user_id', $user->id)
                ->get()
                ->filter(fn (RssSchedule $schedule) => data_get($schedule->settings, 'source') === self::DEMO_MARKER);
        }

        if (class_exists(RssSchedule::class) && $rssSchedules->isNotEmpty()) {
            DB::table('rss_schedule_histories')->whereIn('schedule_id', $rssSchedules->pluck('id'))->delete();
            RssSchedule::query()->whereIn('id', $rssSchedules->pluck('id'))->delete();
        }

        $deleted['rss_schedules'] = $rssSchedules->count();

        $supportTicketIds = SupportTicket::query()
            ->where('uid', $user->id)
            ->where('title', 'like', '[DEMO]%')
            ->pluck('id');

        if ($supportTicketIds->isNotEmpty()) {
            $deleted['support_comments'] = SupportComment::query()->whereIn('ticket_id', $supportTicketIds)->delete();
            DB::table('support_map_labels')->whereIn('ticket_id', $supportTicketIds)->delete();
            $deleted['support_tickets'] = SupportTicket::query()->whereIn('id', $supportTicketIds)->delete();
        }

        SupportCategory::query()->where('name', 'like', '[DEMO]%')->delete();
        SupportType::query()->where('name', 'like', '[DEMO]%')->delete();
        SupportLabel::query()->where('name', 'like', '[DEMO]%')->delete();

        $demoCommissions = AffiliateCommission::query()
            ->where('affiliate_user_id', $user->id)
            ->where('meta->source', self::DEMO_MARKER)
            ->get(['id', 'commission', 'status']);

        $demoPaymentHistoryIds = PaymentHistory::query()
            ->where('uid', $user->id)
            ->where('meta->source', self::DEMO_MARKER)
            ->pluck('id');

        if ($demoCommissions->isNotEmpty()) {
            AffiliateCommission::query()->whereIn('id', $demoCommissions->pluck('id'))->delete();
        }

        if ($demoPaymentHistoryIds->isNotEmpty()) {
            PaymentHistory::query()->whereIn('id', $demoPaymentHistoryIds)->delete();
        }

        $demoWithdrawals = AffiliateWithdrawal::query()
            ->where('affiliate_user_id', $user->id)
            ->where('notes', 'Generated by Admin Faker')
            ->get(['id', 'amount', 'status']);

        $approvedCommissionTotal = (float) $demoCommissions
            ->where('status', AffiliateCommission::STATUS_APPROVED)
            ->sum(fn (AffiliateCommission $commission) => (float) $commission->commission);
        $approvedWithdrawalTotal = (float) $demoWithdrawals
            ->where('status', AffiliateWithdrawal::STATUS_APPROVED)
            ->sum(fn (AffiliateWithdrawal $withdrawal) => (float) $withdrawal->amount);

        if ($demoWithdrawals->isNotEmpty()) {
            AffiliateWithdrawal::query()->whereIn('id', $demoWithdrawals->pluck('id'))->delete();
        }

        if ($demoCommissions->isNotEmpty() || $demoWithdrawals->isNotEmpty()) {
            $profile = app(AffiliateService::class)->ensureProfile($user);
            $profile->forceFill([
                'clicks' => max(0, (int) $profile->clicks - 184),
                'conversions' => max(0, (int) $profile->conversions - $demoCommissions->count()),
                'total_approved' => max(0, (float) $profile->total_approved - $approvedCommissionTotal),
                'total_withdrawal' => max(0, (float) $profile->total_withdrawal - $approvedWithdrawalTotal),
                'total_balance' => max(0, (float) $profile->total_balance - 32.00),
            ])->save();
        }

        $deleted['affiliate_commissions'] = $demoCommissions->count();
        $deleted['affiliate_withdrawals'] = $demoWithdrawals->count();

        $sources = BlogRssSource::query()
            ->where('name', 'like', '[DEMO]%')
            ->pluck('id');

        if ($sources->isNotEmpty()) {
            BlogRssImport::query()->whereIn('blog_rss_source_id', $sources)->delete();
            BlogRssSource::query()->whereIn('id', $sources)->delete();
        }

        $blogIds = Blog::query()->where('slug', 'like', 'demo-preview-%')->pluck('id');

        if ($blogIds->isNotEmpty()) {
            DB::table('blog_tag_maps')->whereIn('blog_id', $blogIds)->delete();
            $deleted['blogs'] = Blog::query()->whereIn('id', $blogIds)->delete();
        }

        $tagIds = BlogTag::query()->where('slug', 'like', 'demo-preview-%')->pluck('id');
        if ($tagIds->isNotEmpty()) {
            DB::table('blog_tag_maps')->whereIn('blog_tag_id', $tagIds)->delete();
            BlogTag::query()->whereIn('id', $tagIds)->delete();
        }

        BlogCategory::query()->where('slug', 'like', 'demo-preview-%')->delete();

        $deleted['faqs'] = Faq::query()->where('slug', 'like', 'demo-preview-%')->delete();

        $prompts = collect();

        if (class_exists(AiPublishingPrompt::class)) {
            $prompts = AiPublishingPrompt::query()
                ->ownedBy($user->id)
                ->get()
                ->filter(fn (AiPublishingPrompt $prompt) => data_get($prompt->metadata, 'source') === self::DEMO_MARKER);
        }

        if (class_exists(AiPublishingPrompt::class) && $prompts->isNotEmpty()) {
            AiPublishingPrompt::query()->whereIn('id', $prompts->pluck('id'))->delete();
        }
        $deleted['ai_prompts'] = $prompts->count();

        $runs = collect();

        if (class_exists(AiPublishingRun::class)) {
            $runs = AiPublishingRun::query()
                ->where('owner_user_id', $user->id)
                ->get()
                ->filter(fn (AiPublishingRun $run) => data_get($run->generation_config, 'source') === self::DEMO_MARKER);
        }

        if (class_exists(AiPublishingRun::class) && $runs->isNotEmpty()) {
            AiPublishingRun::query()->whereIn('id', $runs->pluck('id'))->delete();
        }
        $deleted['ai_publishing_runs'] = $runs->count();

        $aiLogs = AiUsageLog::query()
            ->where('user_id', $user->id)
            ->get()
            ->filter(fn (AiUsageLog $log) => data_get($log->metadata, 'source') === self::DEMO_MARKER);

        if ($aiLogs->isNotEmpty()) {
            AiUsageLog::query()->whereIn('id', $aiLogs->pluck('id'))->delete();
        }
        $deleted['ai_logs'] = $aiLogs->count();

        $globalNotifications = NotificationManual::query()
            ->where('is_global', true)
            ->where('title', 'like', '[DEMO]%')
            ->get(['id']);

        if ($globalNotifications->isNotEmpty()) {
            NotificationManualState::query()
                ->whereIn('notification_manual_id', $globalNotifications->pluck('id'))
                ->delete();

            NotificationManual::query()
                ->whereIn('id', $globalNotifications->pluck('id'))
                ->delete();
        }

        $deleted['global_notifications'] = $globalNotifications->count();

        return [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'password_hint' => __('Preserved existing password'),
            ],
            'counts' => $deleted,
        ];
    }

    protected function resolveFirstUser(): User
    {
        $user = User::query()->orderBy('id')->first();

        abort_if(! $user, 404, __('No user found for Admin Faker.'));

        return $user;
    }

    protected function ensurePlan(User $user): void
    {
        if ($user->plan_id) {
            return;
        }

        $plan = AdminPlan::query()
            ->where('status', true)
            ->orderByDesc('default_signup_plan')
            ->orderBy('position')
            ->first();

        if (! $plan) {
            return;
        }

        $user->forceFill([
            'plan_id' => $plan->id,
            'plan_started_at' => now(),
            'plan_expires_at' => now()->addDays(max(30, (int) ($plan->trial_day ?: 30))),
        ])->save();
    }

    /**
     * @return array<int, AppFile>
     */
    protected function createDemoImages(User $user): array
    {
        return AppFile::query()
            ->ownedBy($user)
            ->where('is_folder', false)
            ->where('is_image', true)
            ->whereNotNull('path')
            ->where('path', 'not like', 'files/demo-faker/%')
            ->get()
            ->filter(fn (AppFile $file): bool => $this->isUsableImageFile($file))
            ->shuffle()
            ->take(12)
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function mlhubDemoVn(): array
    {
        static $config;

        return $config ??= require database_path('seeders/data/mlhub_demo_vn.php');
    }

    /**
     * @return list<string>
     */
    protected function mlhubDemoBusinessNames(): array
    {
        return array_values(array_map(
            static fn (array $business): string => $business['name'],
            $this->mlhubDemoVn()['businesses'],
        ));
    }

    protected function createLocalBoostDemoData(User $user, array &$counts): void
    {
        if (! class_exists(LocalBusiness::class) || ! class_exists(QrCampaign::class)) {
            return;
        }

        $this->clearLocalBoostDemoData($user, $counts);

        $vn = $this->mlhubDemoVn();
        $weeklyHours = $vn['weekly_hours'];
        $messages = $vn['messages'];

        $businesses = collect($vn['businesses'])->mapWithKeys(fn (array $data, string $key): array => [
            $key => LocalBusiness::query()->create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'type' => $data['type'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'website' => $data['website'],
                'address' => $data['address'],
                'google_maps_url' => $data['google_maps_url'],
                'social_links' => [
                    'facebook' => 'https://facebook.com/'.Str::slug($data['name'], ''),
                    'zalo' => 'https://zalo.me/'.preg_replace('/\D+/', '', $data['phone']),
                ],
                'opening_hours' => $weeklyHours,
            ]),
        ]);

        $counts['local_businesses'] = $businesses->count();

        if (class_exists(BusinessLocation::class)) {
            collect([
                ['business' => 'spa', 'name' => 'Sen Vàng Spa — Thảo Điền', 'phone' => '028 7300 1101', 'email' => 'thaodien@senvangspa.vn', 'address' => '25 Nguyễn Văn Hưởng, Thảo Điền, TP. Hồ Chí Minh', 'google_maps_url' => 'https://maps.google.com/?q=Thao+Dien+District+2+HCMC', 'template' => 'rounded_gradient'],
                ['business' => 'bistro', 'name' => 'Cơm Nhà Bistro — Hoàn Kiếm', 'phone' => '024 7300 2102', 'email' => 'hoankiem@comnhabistro.vn', 'address' => '88 Hàng Bông, Hoàn Kiếm, Hà Nội', 'google_maps_url' => 'https://maps.google.com/?q=Hang+Bong+Hanoi', 'template' => 'emerald_ring'],
                ['business' => 'clinic', 'name' => 'Nha Khoa An Nhiên — Hải Châu', 'phone' => '0236 7300 3103', 'email' => 'haichau@nhakhoaannhien.vn', 'address' => '15 Lê Duẩn, Hải Châu, Đà Nẵng', 'google_maps_url' => 'https://maps.google.com/?q=Le+Duan+Da+Nang', 'template' => 'clean_card'],
            ])->each(function (array $location) use ($user, $businesses, $weeklyHours): void {
                $qrDesign = class_exists(LocationQrStyleCatalog::class)
                    ? LocationQrStyleCatalog::designFor((string) $location['template'])
                    : null;

                BusinessLocation::query()->create([
                    'user_id' => $user->id,
                    'business_id' => $businesses[$location['business']]->id,
                    'name' => $location['name'],
                    'phone' => $location['phone'],
                    'email' => $location['email'],
                    'address' => $location['address'],
                    'google_maps_url' => $location['google_maps_url'],
                    'opening_hours' => $weeklyHours,
                    'qr_design' => $qrDesign,
                    'is_active' => true,
                ]);
            });

            $counts['local_locations'] = 3;
        }

        $campaigns = collect($this->localBoostCampaignDefinitions())->mapWithKeys(function (array $data) use ($user, $businesses): array {
            $campaign = QrCampaign::query()->create([
                'user_id' => $user->id,
                'business_id' => $businesses[$data['business']]->id,
                'slug' => $data['slug'],
                'name' => $data['name'],
                'type' => $data['type'],
                'settings' => array_merge($data['settings'], ['demo_marker' => self::DEMO_MARKER, 'demo_scope' => 'localboost']),
                'published_at' => now()->subDays(14),
                'created_at' => now()->subDays(14),
                'updated_at' => now()->subDays(2),
            ]);

            app(LandingPageFactory::class)->syncFromCampaign($campaign);

            return [$data['key'] => $campaign];
        });

        $counts['local_campaigns'] = $campaigns->count();
        $counts['local_landing_pages'] += $campaigns->count();
        $counts['local_landing_pages'] += $this->createStandaloneLandingPages($user, $businesses);

        $bookingService = $vn['booking_service'];
        $services = collect([
            ['business' => $bookingService['business'], 'name' => $bookingService['name'], 'duration' => $bookingService['duration_minutes'], 'price' => $bookingService['price']],
            ['business' => 'clinic', 'name' => 'Tư vấn nha khoa miễn phí', 'duration' => 45, 'price' => 0],
        ])->map(fn (array $service) => BookingService::query()->create([
            'user_id' => $user->id,
            'business_id' => $businesses[$service['business']]->id,
            'name' => $service['name'],
            'duration_minutes' => $service['duration'],
            'price' => $service['price'],
            'description' => $bookingService['description'] ?? 'Dịch vụ demo MLHUB.',
            'available_days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'],
            'time_slots' => ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'],
            'use_business_hours' => true,
            'slot_interval' => 30,
            'buffer_before' => 0,
            'buffer_after' => 10,
            'service_hours' => null,
            'is_active' => true,
        ]));

        $customerNames = array_merge(
            array_column($vn['customers'], 'name'),
            ['Võ Thanh Bình', 'Đặng Thu Hà', 'Bùi Quốc Huy', 'Ngô Kim Ngân', 'Trịnh Văn Long', 'Phan Thị Yến', 'Đinh Hoàng Nam', 'Lý Minh Châu', 'Vũ Gia Hân', 'Cao Đức Anh'],
        );

        $customers = collect($customerNames)
            ->map(function (string $name, int $index) use ($user, $businesses, $vn): Customer {
                $business = $businesses->values()[$index % $businesses->count()];
                $seedCustomer = $vn['customers'][$index] ?? null;

                return Customer::query()->create([
                    'user_id' => $user->id,
                    'business_id' => $business->id,
                    'name' => $name,
                    'phone' => $seedCustomer['phone'] ?? sprintf('09%02d %03d %03d', 10 + ($index % 80), 100 + $index, 200 + $index),
                    'email' => $seedCustomer['email'] ?? 'khach.demo'.($index + 1).'@mlhub.vn',
                    'tags' => ['admin-faker', 'localboost'],
                    'metadata' => ['source' => self::DEMO_MARKER, 'scope' => 'localboost'],
                    'created_at' => now()->subDays(15 - $index),
                    'updated_at' => now()->subDays(15 - $index),
                ]);
            });

        $customerPool = $customers->map(fn (Customer $customer): object => (object) [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
        ]);

        $totalVisits = 0;
        $totalLeads = 0;
        $totalBookings = 0;
        $totalCoupons = 0;
        $totalReviews = 0;
        $totalFeedback = 0;

        foreach ($campaigns->values() as $campaignIndex => $campaign) {
            $metrics = MlhubDemoVolume::metricsForSlug($campaign->slug);
            $totalVisits += $metrics['visits'];

            MlhubDemoVolume::insertQrScans(
                $user->id,
                $campaign->id,
                $metrics['visits'],
                $vn['scan_cities'],
                $vn['scan_country'],
                $campaignIndex,
            );

            $couponCode = (string) data_get($campaign->settings, 'coupon_code', 'CUOITUAN20');

            match ($campaign->type) {
                'review' => (function () use ($user, $campaign, $metrics, $customerPool, $messages, &$totalReviews): void {
                    $totalReviews += $metrics['conversions'];
                    MlhubDemoVolume::insertReviewFeedback(
                        $user->id,
                        $campaign->id,
                        $metrics['conversions'],
                        $customerPool,
                        $messages['review_positive'],
                        $messages['review_negative'],
                    );
                })(),
                'lead' => (function () use ($user, $campaign, $metrics, $customerPool, $messages, &$totalLeads): void {
                    $totalLeads += $metrics['conversions'];
                    MlhubDemoVolume::insertLeads(
                        $user->id,
                        $campaign->id,
                        $metrics['conversions'],
                        $customerPool,
                        $messages['lead'],
                        self::DEMO_MARKER,
                    );
                })(),
                'booking' => (function () use ($user, $campaign, $metrics, $customerPool, $messages, $services, &$totalBookings): void {
                    $totalBookings += $metrics['conversions'];
                    MlhubDemoVolume::insertBookings(
                        $user->id,
                        $campaign->id,
                        $services->first()->id,
                        $metrics['conversions'],
                        $customerPool,
                        $messages['booking_note'],
                    );
                })(),
                'coupon' => (function () use ($user, $campaign, $metrics, $customerPool, $couponCode, &$totalCoupons): void {
                    $totalCoupons += $metrics['conversions'];
                    MlhubDemoVolume::insertCouponRedemptions(
                        $user->id,
                        $campaign->id,
                        $metrics['conversions'],
                        $customerPool,
                        $couponCode,
                    );
                })(),
                'feedback' => (function () use ($user, $campaign, $metrics, $customerPool, $messages, &$totalFeedback): void {
                    $totalFeedback += $metrics['conversions'];
                    MlhubDemoVolume::insertFeedbackResponses(
                        $user->id,
                        $campaign->id,
                        $metrics['conversions'],
                        $customerPool,
                        $messages['feedback_positive'],
                        $messages['feedback_negative'],
                        self::DEMO_MARKER,
                    );
                })(),
                default => null,
            };
        }

        $counts['local_qr_visits'] = $totalVisits;
        $counts['local_leads'] = $totalLeads;
        $counts['local_bookings'] = $totalBookings;
        $counts['local_coupon_claims'] = $totalCoupons;
        $counts['local_review_ratings'] = $totalReviews;
        $counts['local_low_score_feedback'] = (int) round($totalFeedback * 0.25);
        $counts['local_recent_activity'] = $totalLeads + $totalBookings + $totalCoupons + $totalReviews + $totalFeedback;
        $counts['local_top_campaigns'] = min(6, $campaigns->count());
        $counts['local_top_businesses'] = $businesses->count();
    }

    protected function clearLocalBoostDemoData(User $user, array &$deleted): void
    {
        if (! class_exists(QrCampaign::class)) {
            return;
        }

        $deleted['local_landing_pages'] += LandingPage::query()
            ->where('user_id', $user->id)
            ->whereIn('slug', $this->localBoostStandaloneLandingPageSlugs())
            ->delete();

        $campaignIds = QrCampaign::query()
            ->where('user_id', $user->id)
            ->whereIn('slug', $this->localBoostDemoCampaignSlugs())
            ->pluck('id');

        if ($campaignIds->isNotEmpty()) {
            $deleted['local_leads'] += LeadSubmission::query()->whereIn('campaign_id', $campaignIds)->delete();
            $deleted['local_bookings'] += Booking::query()->whereIn('campaign_id', $campaignIds)->delete();
            $deleted['local_coupon_claims'] += CouponRedemption::query()->whereIn('campaign_id', $campaignIds)->delete();
            $deleted['local_review_ratings'] += ReviewFeedback::query()->whereIn('campaign_id', $campaignIds)->delete();
            $deleted['local_low_score_feedback'] += FeedbackResponse::query()->whereIn('campaign_id', $campaignIds)->where('rating', '<=', 3)->delete();
            FeedbackResponse::query()->whereIn('campaign_id', $campaignIds)->delete();
            $deleted['local_qr_visits'] += QrScan::query()->whereIn('campaign_id', $campaignIds)->delete();
            $deleted['local_landing_pages'] += LandingPage::query()->where('user_id', $user->id)->whereIn('campaign_id', $campaignIds)->delete();
            $deleted['local_campaigns'] += QrCampaign::query()->whereIn('id', $campaignIds)->delete();
        }

        $businessIds = LocalBusiness::query()
            ->where('user_id', $user->id)
            ->whereIn('name', $this->mlhubDemoBusinessNames())
            ->pluck('id');

        if ($businessIds->isNotEmpty()) {
            if (class_exists(BusinessLocation::class)) {
                $deleted['local_locations'] += BusinessLocation::query()->where('user_id', $user->id)->whereIn('business_id', $businessIds)->delete();
            }
            BookingService::query()->where('user_id', $user->id)->whereIn('business_id', $businessIds)->delete();
            Customer::query()->where('user_id', $user->id)->whereIn('business_id', $businessIds)->delete();
            $deleted['local_businesses'] += LocalBusiness::query()->whereIn('id', $businessIds)->delete();
        }
    }

    protected function localBoostDemoCampaignSlugs(): array
    {
        return collect($this->localBoostCampaignDefinitions())->pluck('slug')->all();
    }

    protected function localBoostStandaloneLandingPageSlugs(): array
    {
        return collect($this->localBoostStandaloneLandingPageDefinitions())->pluck('slug')->all();
    }

    protected function createStandaloneLandingPages(User $user, $businesses): int
    {
        if (! class_exists(LandingPage::class) || ! class_exists(PageTemplateCatalog::class)) {
            return 0;
        }

        return collect($this->localBoostStandaloneLandingPageDefinitions())
            ->map(function (array $data) use ($user, $businesses): LandingPage {
                $type = (string) $data['type'];
                $template = (string) $data['template'];
                $business = $businesses[$data['business']] ?? $businesses->first();
                $design = array_merge(
                    PageTemplateCatalog::designFor($template),
                    (array) ($data['design'] ?? [])
                );
                $content = [
                    'headline' => $data['headline'],
                    'subheadline' => $data['subheadline'],
                    'description' => $data['description'] ?? '',
                    'cta' => $data['cta'],
                    'benefits' => $data['benefits'],
                    'thank_you_message' => $data['thank_you_message'] ?? 'Thank you. We have received your request.',
                    'landing_page_blocks' => [],
                ];
                $settings = array_merge([
                    'design' => $design,
                    'blocks' => [],
                    'demo_marker' => self::DEMO_MARKER,
                    'demo_scope' => 'localboost-landing-pages',
                ], (array) ($data['settings'] ?? []));

                return LandingPage::query()->create([
                    'user_id' => $user->id,
                    'business_id' => $business?->id,
                    'campaign_id' => null,
                    'slug' => $data['slug'],
                    'title' => $data['title'],
                    'type' => $type,
                    'template' => $template,
                    'status' => 'published',
                    'content' => $content,
                    'settings' => $settings,
                    'visits_count' => $data['visits'] ?? 0,
                    'conversions_count' => $data['conversions'] ?? 0,
                    'published_at' => now()->subDays($data['age_days'] ?? 7),
                    'created_at' => now()->subDays($data['age_days'] ?? 7),
                    'updated_at' => now()->subDays(1),
                ]);
            })
            ->count();
    }

    protected function localBoostStandaloneLandingPageDefinitions(): array
    {
        return [
            [
                'business' => 'spa',
                'slug' => 'admin-faker-lp-review-clean-spa',
                'title' => 'Luồng đánh giá Sen Vàng Spa',
                'type' => 'review',
                'template' => 'review_clean_request',
                'headline' => 'Buổi spa của bạn thế nào?',
                'subheadline' => 'Đánh giá giúp đội ngũ cải thiện và giúp khách khác chọn dịch vụ phù hợp.',
                'cta' => 'Gửi đánh giá',
                'benefits' => ['Phản hồi riêng khi điểm thấp', 'Chuyển sang Google', 'Theo dõi nhanh tại chỗ'],
                'settings' => ['review_url' => 'https://g.page/r/bloom-spa-demo/review'],
                'visits' => 184,
                'conversions' => 62,
            ],
            [
                'business' => 'bistro',
                'slug' => 'admin-faker-lp-review-restaurant-bistro',
                'title' => 'Đánh giá bữa tối Cơm Nhà',
                'type' => 'review',
                'template' => 'review_restaurant',
                'headline' => 'Đánh giá bữa tối hôm nay',
                'subheadline' => 'Khách hài lòng có thể đánh giá Google. Phản hồi riêng gửi thẳng quản lý.',
                'cta' => 'Gửi điểm',
                'benefits' => ['Chấm điểm nhanh', 'Hàng đợi quản lý', 'Chuyển Google'],
                'settings' => ['review_url' => 'https://g.page/r/corner-table-demo/review'],
                'visits' => 139,
                'conversions' => 48,
            ],
            [
                'business' => 'clinic',
                'slug' => 'admin-faker-lp-lead-clinic-quote',
                'title' => 'Dental Consultation Lead Page',
                'type' => 'lead',
                'template' => 'lead_quote_request',
                'headline' => 'Request a smile consultation',
                'subheadline' => 'Tell us what you need and our front desk will help with the next step.',
                'cta' => 'Request consultation',
                'benefits' => ['Fast response', 'Clear treatment options', 'Friendly local team'],
                'visits' => 96,
                'conversions' => 21,
            ],
            [
                'business' => 'spa',
                'slug' => 'admin-faker-lp-lead-new-client-spa',
                'title' => 'Spa New Client Inquiry',
                'type' => 'lead',
                'template' => 'lead_new_customer',
                'headline' => 'Plan your first spa visit',
                'subheadline' => 'Share your preferred service and we will recommend the right appointment.',
                'cta' => 'Send inquiry',
                'benefits' => ['Personalized recommendation', 'No-pressure follow-up', 'New client friendly'],
                'visits' => 124,
                'conversions' => 34,
            ],
            [
                'business' => 'spa',
                'slug' => 'admin-faker-lp-booking-spa',
                'title' => 'Massage Booking Landing Page',
                'type' => 'booking',
                'template' => 'booking_spa',
                'headline' => 'Reserve your massage appointment',
                'subheadline' => 'Choose a preferred time and the team will confirm your booking.',
                'cta' => 'Request booking',
                'benefits' => ['Choose a service', 'Pick an available slot', 'Receive confirmation'],
                'settings' => ['service' => 'Relaxation massage', 'duration' => '60 minutes', 'price' => '$89', 'available_slots' => ['09:00', '10:00', '14:00', '15:00']],
                'visits' => 151,
                'conversions' => 37,
            ],
            [
                'business' => 'clinic',
                'slug' => 'admin-faker-lp-booking-clinic',
                'title' => 'Clinic Consultation Booking',
                'type' => 'booking',
                'template' => 'booking_clinic',
                'headline' => 'Book a consultation time',
                'subheadline' => 'Pick a slot for a quick dental consultation with our local team.',
                'cta' => 'Book consultation',
                'benefits' => ['Simple scheduling', 'Front desk confirmation', 'Clear next step'],
                'settings' => ['service' => 'Dental consultation', 'duration' => '45 minutes', 'price' => 'Free', 'available_slots' => ['09:30', '11:00', '13:30', '16:00']],
                'visits' => 88,
                'conversions' => 19,
            ],
            [
                'business' => 'bistro',
                'slug' => 'admin-faker-lp-coupon-weekend-bistro',
                'title' => 'Weekend Dinner Coupon',
                'type' => 'coupon',
                'template' => 'coupon_weekend_deal',
                'headline' => 'Save 20% on dinner this weekend',
                'subheadline' => 'Claim the offer and show your code when you visit.',
                'cta' => 'Claim dinner coupon',
                'benefits' => ['Limited weekend offer', 'Instant claim code', 'Redeem with staff'],
                'settings' => ['coupon_title' => 'WEEKEND20', 'discount' => '20% off', 'expiry' => now()->addDays(14)->toDateString(), 'terms' => 'Valid for dine-in dinner. One coupon per table.'],
                'visits' => 231,
                'conversions' => 77,
            ],
            [
                'business' => 'spa',
                'slug' => 'admin-faker-lp-coupon-birthday-spa',
                'title' => 'Birthday Spa Offer',
                'type' => 'coupon',
                'template' => 'coupon_birthday',
                'headline' => 'Enjoy a birthday spa upgrade',
                'subheadline' => 'Claim a complimentary aromatherapy add-on during your birthday month.',
                'cta' => 'Claim birthday offer',
                'benefits' => ['Birthday month only', 'Free service upgrade', 'Easy in-store redemption'],
                'settings' => ['coupon_title' => 'BDAYSPA', 'discount' => 'Free aromatherapy upgrade', 'expiry' => now()->addDays(30)->toDateString(), 'terms' => 'Valid during birthday month with appointment.'],
                'visits' => 112,
                'conversions' => 29,
            ],
            [
                'business' => 'bistro',
                'slug' => 'admin-faker-lp-feedback-private-bistro',
                'title' => 'Private Dining Feedback',
                'type' => 'feedback',
                'template' => 'feedback_private',
                'headline' => 'Tell us about your dining experience',
                'subheadline' => 'Private feedback goes directly to the local team.',
                'cta' => 'Send private feedback',
                'benefits' => ['Private response', 'Manager follow-up', 'Helps improve service'],
                'visits' => 73,
                'conversions' => 18,
            ],
            [
                'business' => 'clinic',
                'slug' => 'admin-faker-lp-feedback-quality-clinic',
                'title' => 'Clinic Service Quality Check',
                'type' => 'feedback',
                'template' => 'feedback_quality',
                'headline' => 'How did your visit go?',
                'subheadline' => 'Your feedback helps us improve patient experience.',
                'cta' => 'Send feedback',
                'benefits' => ['Quality check', 'Private message', 'Care team review'],
                'visits' => 91,
                'conversions' => 24,
            ],
            [
                'business' => 'bistro',
                'slug' => 'admin-faker-lp-custom-local-event',
                'title' => 'Local Tasting Night Campaign',
                'type' => 'custom',
                'template' => 'custom_local_event_page',
                'headline' => 'Join our local tasting night',
                'subheadline' => 'Reserve interest for a limited community tasting event.',
                'cta' => 'Join the list',
                'benefits' => ['Limited seats', 'Chef-led tasting', 'Local community event'],
                'visits' => 68,
                'conversions' => 16,
            ],
            [
                'business' => 'spa',
                'slug' => 'admin-faker-lp-custom-product-spotlight',
                'title' => 'Spa Gift Card Spotlight',
                'type' => 'custom',
                'template' => 'custom_product_spotlight',
                'headline' => 'Gift a relaxing local experience',
                'subheadline' => 'Share your details and our team will help you choose the right gift card.',
                'cta' => 'Ask about gift cards',
                'benefits' => ['Great local gift', 'Flexible value', 'Easy pickup'],
                'visits' => 57,
                'conversions' => 13,
            ],
        ];
    }

    protected function localBoostCampaignDefinitions(): array
    {
        return [
            ['key' => 'review_spa', 'business' => 'spa', 'slug' => 'admin-faker-review-spa', 'name' => 'Thu thập đánh giá Google — Spa', 'type' => 'review', 'settings' => ['landing_template' => 'review_google_focus', 'positive_threshold' => 4, 'preferred_destination' => 'google', 'thank_you_message' => 'Cảm ơn bạn đã ghé Sen Vàng Spa.', 'negative_feedback_message' => 'Hãy cho chúng tôi biết điều cần cải thiện trước lần ghé tiếp theo.']],
            ['key' => 'review_bistro', 'business' => 'bistro', 'slug' => 'admin-faker-review-bistro', 'name' => 'Đánh giá bữa tối — Nhà hàng', 'type' => 'review', 'settings' => ['landing_template' => 'review_restaurant', 'positive_threshold' => 4, 'preferred_destination' => 'google', 'thank_you_message' => 'Cảm ơn bạn đã dùng bữa tại Cơm Nhà Bistro.', 'negative_feedback_message' => 'Hãy cho chúng tôi biết điều chưa hài lòng.']],
            ['key' => 'review_clinic', 'business' => 'clinic', 'slug' => 'admin-faker-review-clinic', 'name' => 'Đánh giá sau khám — Nha khoa', 'type' => 'review', 'settings' => ['landing_template' => 'review_clinic', 'positive_threshold' => 4, 'preferred_destination' => 'google', 'thank_you_message' => 'Cảm ơn bạn đã tin tưởng Nha Khoa An Nhiên.', 'negative_feedback_message' => 'Hãy góp ý để đội ngũ chăm sóc phục vụ tốt hơn.']],
            ['key' => 'lead_clinic', 'business' => 'clinic', 'slug' => 'admin-faker-lead-clinic', 'name' => 'Form tư vấn nha khoa miễn phí', 'type' => 'lead', 'settings' => ['landing_template' => 'lead_quote_request', 'headline' => 'Đặt lịch tư vấn nha khoa miễn phí']],
            ['key' => 'lead_spa', 'business' => 'spa', 'slug' => 'admin-faker-lead-spa', 'name' => 'Khách mới — Spa', 'type' => 'lead', 'settings' => ['landing_template' => 'lead_new_customer', 'headline' => 'Lên lịch lần ghé spa đầu tiên']],
            ['key' => 'lead_bistro', 'business' => 'bistro', 'slug' => 'admin-faker-lead-bistro', 'name' => 'Đặt tiệc / sự kiện', 'type' => 'lead', 'settings' => ['landing_template' => 'lead_event_capture', 'headline' => 'Đặt tiệc riêng hoặc sự kiện nhỏ']],
            ['key' => 'booking_spa', 'business' => 'spa', 'slug' => 'admin-faker-booking-spa', 'name' => 'Đặt lịch massage', 'type' => 'booking', 'settings' => ['landing_template' => 'booking_spa', 'headline' => 'Đặt lịch massage thư giãn']],
            ['key' => 'booking_clinic', 'business' => 'clinic', 'slug' => 'admin-faker-booking-clinic', 'name' => 'Đặt lịch khám nha khoa', 'type' => 'booking', 'settings' => ['landing_template' => 'booking_clinic', 'headline' => 'Chọn khung giờ tư vấn']],
            ['key' => 'booking_bistro', 'business' => 'bistro', 'slug' => 'admin-faker-booking-bistro', 'name' => 'Đặt bàn nhà hàng', 'type' => 'booking', 'settings' => ['landing_template' => 'booking_restaurant', 'headline' => 'Đặt bàn trong tuần này']],
            ['key' => 'coupon_bistro', 'business' => 'bistro', 'slug' => 'admin-faker-coupon-bistro', 'name' => 'Giảm 20% bữa tối cuối tuần', 'type' => 'coupon', 'settings' => ['landing_template' => 'coupon_weekend_deal', 'discount_type' => 'percentage', 'discount_value' => '20', 'coupon_code' => 'CUOITUAN20', 'usage_limit' => 200, 'expiry_date' => now()->addDays(21)->toDateString(), 'terms' => 'Áp dụng khi ăn tại chỗ, bữa tối thứ Sáu–Chủ nhật. Không áp dụng ngày lễ.']],
            ['key' => 'coupon_spa', 'business' => 'spa', 'slug' => 'admin-faker-coupon-spa', 'name' => 'Ưu đãi quay lại spa', 'type' => 'coupon', 'settings' => ['landing_template' => 'coupon_comeback', 'discount_type' => 'percentage', 'discount_value' => '15', 'coupon_code' => 'QUAYLAI15', 'usage_limit' => 100, 'expiry_date' => now()->addDays(30)->toDateString(), 'terms' => 'Mỗi khách một mã. Không cộng dồn ưu đãi khác.']],
            ['key' => 'coupon_clinic', 'business' => 'clinic', 'slug' => 'admin-faker-coupon-clinic', 'name' => 'Ưu đãi khách mới — Tẩy trắng', 'type' => 'coupon', 'settings' => ['landing_template' => 'coupon_20_off', 'discount_type' => 'fixed', 'discount_value' => '500000', 'coupon_code' => 'NHO500', 'usage_limit' => 75, 'expiry_date' => now()->addDays(28)->toDateString(), 'terms' => 'Áp dụng gói tư vấn tẩy trắng cho khách mới.']],
            ['key' => 'feedback_bistro', 'business' => 'bistro', 'slug' => 'admin-faker-feedback-bistro', 'name' => 'Phản hồi trải nghiệm ẩm thực', 'type' => 'feedback', 'settings' => ['landing_template' => 'feedback_private', 'headline' => 'Chia sẻ trải nghiệm bữa ăn của bạn', 'thank_you_message' => 'Cảm ơn bạn. Ý kiến giúp đội ngũ Cơm Nhà phục vụ tốt hơn.', 'rating_required' => false, 'contact_required' => false]],
            ['key' => 'feedback_clinic', 'business' => 'clinic', 'slug' => 'admin-faker-feedback-clinic', 'name' => 'Phản hồi sau khám', 'type' => 'feedback', 'settings' => ['landing_template' => 'feedback_quality', 'headline' => 'Buổi khám của bạn thế nào?', 'thank_you_message' => 'Cảm ơn bạn đã giúp chúng tôi cải thiện dịch vụ.', 'rating_required' => false, 'contact_required' => false]],
            ['key' => 'feedback_spa', 'business' => 'spa', 'slug' => 'admin-faker-feedback-spa', 'name' => 'Khảo sát trải nghiệm spa', 'type' => 'feedback', 'settings' => ['landing_template' => 'feedback_satisfaction', 'headline' => 'Buổi spa của bạn thư giãn đến mức nào?', 'thank_you_message' => 'Cảm ơn bạn. Góp ý giúp đội ngũ spa phục vụ tốt hơn.', 'rating_required' => false, 'contact_required' => false]],
        ];
    }

    protected function createDemoChannels(User $user, array $imageFiles)
    {
        if (! class_exists(SocialAccount::class)) {
            return collect();
        }

        $personas = [
            ['name' => 'Stackposts', 'handle' => 'stackposts'],
            ['name' => 'John Smith', 'handle' => 'johnsmith'],
            ['name' => 'Anna Studio', 'handle' => 'annastudio'],
            ['name' => "Sunny & Gannie's", 'handle' => 'sunnygannies'],
            ['name' => 'Blue Harbor', 'handle' => 'blueharbor'],
            ['name' => 'Mia Carter', 'handle' => 'miacarter'],
            ['name' => 'North Peak', 'handle' => 'northpeak'],
            ['name' => 'Olivia Reed', 'handle' => 'oliviareed'],
            ['name' => 'Pixel Garden', 'handle' => 'pixelgarden'],
            ['name' => 'Ethan Walker', 'handle' => 'ethanwalker'],
            ['name' => 'Emma Stone', 'handle' => 'emmastone'],
            ['name' => 'Liam Brooks', 'handle' => 'liambrooks'],
            ['name' => 'Ava Wilson', 'handle' => 'avawilson'],
            ['name' => 'Noah Bennett', 'handle' => 'noahbennett'],
            ['name' => 'Sophia Lane', 'handle' => 'sophialane'],
            ['name' => 'Mason Reed', 'handle' => 'masonreed'],
            ['name' => 'Isabella Hart', 'handle' => 'isabellahart'],
            ['name' => 'Lucas Grant', 'handle' => 'lucasgrant'],
            ['name' => 'Charlotte Hayes', 'handle' => 'charlottehayes'],
            ['name' => 'James Cooper', 'handle' => 'jamescooper'],
            ['name' => 'Amelia Scott', 'handle' => 'ameliascott'],
            ['name' => 'Benjamin Cole', 'handle' => 'benjamincole'],
            ['name' => 'Harper Quinn', 'handle' => 'harperquinn'],
            ['name' => 'Elijah Ross', 'handle' => 'elijahross'],
            ['name' => 'Evelyn Price', 'handle' => 'evelynprice'],
            ['name' => 'Henry Adams', 'handle' => 'henryadams'],
            ['name' => 'Abigail Moore', 'handle' => 'abigailmoore'],
            ['name' => 'Alexander Bell', 'handle' => 'alexanderbell'],
            ['name' => 'Emily Foster', 'handle' => 'emilyfoster'],
            ['name' => 'Daniel Ward', 'handle' => 'danielward'],
            ['name' => 'Luna Creative', 'handle' => 'lunacreative'],
            ['name' => 'Urban Bloom', 'handle' => 'urbanbloom'],
            ['name' => 'Golden Finch', 'handle' => 'goldenfinch'],
            ['name' => 'Rose Atelier', 'handle' => 'roseatelier'],
            ['name' => 'Cedar Studio', 'handle' => 'cedarstudio'],
            ['name' => 'Maple & Co', 'handle' => 'mapleco'],
            ['name' => 'Bright Horizon', 'handle' => 'brighthorizon'],
            ['name' => 'Nora Blake', 'handle' => 'norablake'],
            ['name' => 'Samuel Green', 'handle' => 'samuelgreen'],
            ['name' => 'Chloe Brooks', 'handle' => 'chloebrooks'],
            ['name' => 'Jack Turner', 'handle' => 'jackturner'],
            ['name' => 'Grace Mitchell', 'handle' => 'gracemitchell'],
            ['name' => 'Owen Parker', 'handle' => 'owenparker'],
            ['name' => 'Lily Morgan', 'handle' => 'lilymorgan'],
            ['name' => 'Logan Hill', 'handle' => 'loganhill'],
            ['name' => 'Zoe Carter', 'handle' => 'zoecarter'],
            ['name' => 'Wyatt Hughes', 'handle' => 'wyatthughes'],
            ['name' => 'Aurora House', 'handle' => 'aurorahouse'],
            ['name' => 'Velvet Olive', 'handle' => 'velvetolive'],
            ['name' => 'Nova Harbor', 'handle' => 'novaharbor'],
            ['name' => 'Willow Lane', 'handle' => 'willowlane'],
            ['name' => 'Jasper North', 'handle' => 'jaspernorth'],
            ['name' => 'Hazel & Pine', 'handle' => 'hazelpine'],
            ['name' => 'Mila Harper', 'handle' => 'milaharper'],
            ['name' => 'Leo West', 'handle' => 'leowest'],
            ['name' => 'Ivy Clarke', 'handle' => 'ivyclarke'],
            ['name' => 'Theo James', 'handle' => 'theojames'],
            ['name' => 'Ruby Lane', 'handle' => 'rubylane'],
            ['name' => 'Finn Harper', 'handle' => 'finnharper'],
            ['name' => 'Echo Studio', 'handle' => 'echostudio'],
        ];

        $definitions = collect(publishable_channel_capability_keys($user))
            ->values()
            ->map(function (string $capabilityKey, int $index) use ($personas): ?array {
                $capability = channel_capability($capabilityKey);
                $providerKey = (string) ($capability['provider_key'] ?? '');

                if ($providerKey === '') {
                    return null;
                }

                $persona = $personas[$index % count($personas)];
                $categoryLabel = (string) ($capability['label'] ?? $capability['name'] ?? Str::headline(str_replace('_', ' ', $capabilityKey)));
                $categorySlug = Str::slug($categoryLabel);
                $providerSlug = $this->demoProviderSlug($providerKey);
                $handleBase = $persona['handle'];
                $handleSuffix = $categorySlug !== '' ? '.'.$categorySlug : '';

                return [
                    'provider' => $providerKey,
                    'capability_key' => $capabilityKey,
                    'account_type' => 'oauth',
                    'category' => $categoryLabel,
                    'name' => $persona['name'],
                    'username' => match ($providerKey) {
                        'x', 'reddit', 'telegram', 'vk' => $handleBase.'_'.$providerSlug.($categorySlug !== '' ? '_'.$categorySlug : ''),
                        'linkedin_profile', 'linkedin_page', 'google_business_profile' => $handleBase.'-'.$providerSlug.($categorySlug !== '' ? '-'.$categorySlug : ''),
                        default => $handleBase.$handleSuffix,
                    },
                    'url' => $this->demoProfileUrl($providerKey, $persona['handle'], $categorySlug),
                ];
            })
            ->filter()
            ->values();

        return $definitions->map(function (array $definition, int $index) use ($user, $imageFiles) {
            $image = $this->randomDemoImage($imageFiles);
            $storedAvatar = $image
                ? $this->storeChannelAvatarFromFile($image, $user, $definition['provider'], Str::upper($definition['provider']).'-DEMO-'.$user->id.'-'.($index + 1))
                : null;

            return SocialAccount::query()->create([
                'provider_key' => $definition['provider'],
                'capability_key' => $definition['capability_key'],
                'display_name' => $definition['name'],
                'username' => $definition['username'],
                'external_id' => Str::upper($definition['provider']).'-DEMO-'.$user->id.'-'.($index + 1),
                'category' => $definition['category'],
                'account_type' => $definition['account_type'],
                'profile_url' => $definition['url'],
                'avatar_url' => $storedAvatar['url'] ?? null,
                'avatar_disk' => $storedAvatar['disk'] ?? null,
                'avatar_path' => $storedAvatar['path'] ?? null,
                'metadata' => [
                    'source' => self::DEMO_MARKER,
                    'image_file_id' => $image?->id,
                    'avatar_source' => $storedAvatar ? 'app_file_copy' : 'fallback',
                ],
                'notes' => 'Generated by Admin Faker',
                'is_active' => true,
                'created_by_user_id' => $user->id,
                'connected_at' => now(),
                'last_synced_at' => now(),
            ]);
        });
    }

    protected function createLinkBioPages(User $user, Team $team, array $imageFiles, array &$counts): void
    {
        $imageUrls = collect($imageFiles)
            ->map(fn (AppFile $file): ?string => $this->demoPublicImageUrl($file))
            ->filter()
            ->values()
            ->all();

        $image = fn (int $index): string => (string) ($imageUrls[$index % max(1, count($imageUrls))] ?? '');

        $definitions = [
            [
                'title' => 'Stackposts Bio Page',
                'slug' => 'demo-smartbio-stackposts',
                'headline' => 'Smart links for product updates, demos, and support.',
                'description' => 'A clean SmartBio page that routes visitors to the most important Stackposts resources.',
                'template_key' => 'studio-pro',
                'accent_color' => '#2563eb',
                'published' => true,
                'avatar_url' => $image(0),
                'cover_url' => $image(1),
                'background_image' => $image(2),
                'blocks' => [
                    $this->linkBioBlock('header', 'Start here', 'New visitors', 'Explore Stackposts features, pricing, and help resources from one focused page.', [], 'View live demo', 'https://try.stackposts.com/stackposts_addons/'),
                    $this->linkBioBlock('links', 'Quick Links', 'Useful destinations', '', [
                        $this->linkBioItem('Live demo', 'https://try.stackposts.com/stackposts_addons/', 'Try the demo workspace', 'fa-solid fa-arrow-up-right-from-square'),
                        $this->linkBioItem('Documentation', 'https://doc.stackposts.com/', 'Read installation and usage guides', 'fa-solid fa-book-open'),
                        $this->linkBioItem('Contact support', 'https://doc.stackposts.com/docs/stackposts/how-to/contact-stackposts-team/', 'Get help from the Stackposts team', 'fa-solid fa-life-ring'),
                    ]),
                    $this->linkBioBlock('video', 'Product overview', 'Watch before installing', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', [], 'Watch video', 'https://www.youtube.com/'),
                    $this->linkBioBlock('faq', 'Buyer FAQ', 'Common questions', '', [
                        $this->linkBioItem('Does it require Stackposts?', '', '', 'fa-solid fa-circle-question', '', '', 'Yes. SmartBio is an addon built for Stackposts.'),
                        $this->linkBioItem('Can I customize templates?', '', '', 'fa-solid fa-circle-question', '', '', 'Yes. You can edit templates, colors, blocks, media, and public page content.'),
                    ]),
                ],
                'views' => 184,
            ],
            [
                'title' => 'Creator Launch Kit',
                'slug' => 'demo-smartbio-creator',
                'headline' => 'Creator resources, socials, and latest offers in one link.',
                'description' => 'Built for creators who need a fast profile page with clear calls to action.',
                'template_key' => 'aurora',
                'accent_color' => '#e83f6f',
                'published' => true,
                'avatar_url' => $image(3),
                'cover_url' => $image(4),
                'background_image' => $image(4),
                'blocks' => [
                    $this->linkBioBlock('social', 'Social Channels', 'Stay connected', '', [
                        $this->linkBioItem('Instagram', 'https://instagram.com/demo.creator', 'Behind the scenes and reels', 'fa-brands fa-instagram'),
                        $this->linkBioItem('YouTube', 'https://youtube.com/@demo.creator', 'Tutorials and long-form videos', 'fa-brands fa-youtube'),
                        $this->linkBioItem('TikTok', 'https://www.tiktok.com/@demo.creator', 'Short daily clips', 'fa-brands fa-tiktok'),
                    ]),
                    $this->linkBioBlock('links', 'Featured Links', 'Best next steps', '', [
                        $this->linkBioItem('Book a collaboration', 'https://example.com/collab', 'For brands and partners', 'fa-solid fa-handshake'),
                        $this->linkBioItem('Download media kit', 'https://example.com/media-kit', 'Audience stats and packages', 'fa-solid fa-file-arrow-down'),
                    ]),
                    $this->linkBioBlock('gallery', 'Latest Work', 'Recent highlights', '', [
                        $this->linkBioItem('Studio shoot', 'https://example.com/gallery/studio', 'Portrait campaign', 'fa-solid fa-images', $image(5)),
                        $this->linkBioItem('Travel vlog', 'https://example.com/gallery/travel', 'Weekend story set', 'fa-solid fa-images', $image(6)),
                    ]),
                ],
                'views' => 261,
            ],
            [
                'title' => 'Mini Shop Bio',
                'slug' => 'demo-smartbio-shop',
                'headline' => 'Shop featured products and promotions.',
                'description' => 'A compact storefront for sellers who want products, offers, and contact options in one bio link.',
                'template_key' => 'promo-orange',
                'accent_color' => '#ef4444',
                'published' => true,
                'avatar_url' => $image(7),
                'cover_url' => $image(8),
                'background_image' => $image(8),
                'blocks' => [
                    $this->linkBioBlock('product', 'Featured Products', 'Best sellers this week', '', [
                        $this->linkBioItem('Starter bundle', 'https://example.com/products/starter', 'A simple package for new customers', 'fa-solid fa-bag-shopping', $image(9), '$29'),
                        $this->linkBioItem('Pro bundle', 'https://example.com/products/pro', 'More templates and premium support', 'fa-solid fa-bag-shopping', $image(10), '$79'),
                    ]),
                    $this->linkBioBlock('contact', 'Need help choosing?', 'Talk to sales', '', [
                        $this->linkBioItem('Email sales', 'mailto:sales@example.com', 'Usually replies within 24 hours', 'fa-solid fa-envelope', '', '', '', 'sales@example.com'),
                        $this->linkBioItem('WhatsApp', 'https://wa.me/15550100', 'Fast support for pre-sale questions', 'fa-brands fa-whatsapp', '', '', '', '+1 555 0100'),
                    ]),
                ],
                'views' => 142,
            ],
            [
                'title' => 'Coach Booking Page',
                'slug' => 'demo-smartbio-coach',
                'headline' => 'Book coaching, read testimonials, and start with a quick guide.',
                'description' => 'A draft page for a coach or consultant who wants leads and appointment bookings.',
                'template_key' => 'sage-calm',
                'accent_color' => '#15803d',
                'published' => true,
                'avatar_url' => $image(11),
                'cover_url' => $image(0),
                'background_image' => '',
                'blocks' => [
                    $this->linkBioBlock('links', 'Work with me', 'Choose your next step', '', [
                        $this->linkBioItem('Book discovery call', 'https://example.com/book', 'Free 20-minute consultation', 'fa-solid fa-calendar-check'),
                        $this->linkBioItem('Download guide', 'https://example.com/guide', 'Start with a practical checklist', 'fa-solid fa-file-lines'),
                    ]),
                    $this->linkBioBlock('faq', 'Questions', 'Before booking', '', [
                        $this->linkBioItem('Who is this for?', '', '', 'fa-solid fa-circle-question', '', '', 'Founders, creators, and small teams who need a practical growth plan.'),
                    ]),
                ],
                'views' => 96,
            ],
        ];

        foreach ($definitions as $definition) {
            $blocks = array_values($definition['blocks']);
            $page = LinkBioPage::query()->create([
                'owner_user_id' => $user->id,
                'team_id' => $team->id,
                'title' => $definition['title'],
                'slug' => $this->uniqueLinkBioSlug((string) $definition['slug']),
                'headline' => $definition['headline'],
                'description' => $definition['description'],
                'accent_color' => $definition['accent_color'],
                'avatar_url' => $definition['avatar_url'],
                'cover_url' => $definition['cover_url'],
                'template_key' => $definition['template_key'],
                'status' => $definition['published'] ? 'published' : 'draft',
                'is_published' => (bool) $definition['published'],
                'blocks' => $blocks,
                'settings' => [
                    'source' => self::DEMO_MARKER,
                    'branding_text' => 'Powered by SmartBio',
                    'avatar_style' => 'circle',
                    'button_style' => 'rounded',
                    'content_align' => 'left',
                    'background_image' => $definition['background_image'],
                    'background_overlay' => $definition['background_image'] !== '' ? 28 : 0,
                    'background_position' => 'center',
                    'background_fit' => 'cover',
                ],
            ]);

            $counts['link_bio_pages']++;
            $this->createLinkBioDemoEvents($page, (int) $definition['views'], $blocks, $counts);
        }
    }

    protected function clearLinkBioPages(User $user, array &$deleted): void
    {
        $pageIds = LinkBioPage::query()
            ->where('owner_user_id', $user->id)
            ->get(['id', 'slug', 'settings'])
            ->filter(fn (LinkBioPage $page): bool => data_get($page->settings, 'source') === self::DEMO_MARKER || str_starts_with((string) $page->slug, 'demo-smartbio-'))
            ->pluck('id');

        if ($pageIds->isEmpty()) {
            return;
        }

        $deleted['link_bio_events'] = LinkBioEvent::query()
            ->whereIn('link_bio_page_id', $pageIds)
            ->delete();

        $deleted['link_bio_pages'] = LinkBioPage::query()
            ->whereIn('id', $pageIds)
            ->delete();
    }

    protected function createLinkBioDemoEvents(LinkBioPage $page, int $views, array $blocks, array &$counts): void
    {
        if ($views <= 0) {
            return;
        }

        $now = now();

        for ($i = 0; $i < $views; $i++) {
            DB::table('link_bio_events')->insert([
                'link_bio_page_id' => $page->id,
                'type' => 'view',
                'block_index' => null,
                'item_index' => null,
                'url' => null,
                'ip_hash' => hash('sha256', 'demo-view-'.$page->id.'-'.$i),
                'user_agent' => 'AdminFaker Demo Browser',
                'created_at' => $now->copy()->subMinutes(random_int(5, 7200)),
                'updated_at' => $now->copy()->subMinutes(random_int(5, 7200)),
            ]);
            $counts['link_bio_events']++;
        }

        foreach ($this->linkBioClickTargets($blocks) as $targetIndex => $target) {
            $clicks = max(3, (int) floor($views / (6 + $targetIndex)));

            for ($i = 0; $i < $clicks; $i++) {
                DB::table('link_bio_events')->insert([
                    'link_bio_page_id' => $page->id,
                    'type' => 'click',
                    'block_index' => $target['block_index'],
                    'item_index' => $target['item_index'],
                    'url' => $target['url'],
                    'ip_hash' => hash('sha256', 'demo-click-'.$page->id.'-'.$targetIndex.'-'.$i),
                    'user_agent' => 'AdminFaker Demo Browser',
                    'created_at' => $now->copy()->subMinutes(random_int(5, 7200)),
                    'updated_at' => $now->copy()->subMinutes(random_int(5, 7200)),
                ]);
                $counts['link_bio_events']++;
            }
        }
    }

    protected function createQrDemoCodes(User $user, Team $team, array $imageFiles, array &$counts): void
    {
        $imageUrls = collect($imageFiles)
            ->map(fn (AppFile $file): ?string => $this->demoPublicImageUrl($file))
            ->filter()
            ->values()
            ->all();

        $image = fn (int $index): string => (string) ($imageUrls[$index % max(1, count($imageUrls))] ?? '');
        $bioPage = LinkBioPage::query()
            ->where('owner_user_id', $user->id)
            ->get()
            ->first(fn (LinkBioPage $page): bool => data_get($page->settings, 'source') === self::DEMO_MARKER);
        $bioUrl = $bioPage ? route('link-bio.public.show', ['slug' => $bioPage->slug]) : 'https://example.com/bio';

        $qrIndex = 0;
        $publicTemplates = [
            'split_showcase',
            'mobile_stack',
            'catalog_grid',
            'editorial',
            'compact_banner',
            'sidebar_profile',
            'menu_board',
            'lead_capture',
            'event_pass',
            'file_card',
            'review_spotlight',
            'resume_sheet',
            'booking_card',
            'app_download',
            'payment_card',
            'link_hub',
        ];
        $publicLandingTypes = [
            'business_profile',
            'website_builder',
            'vcard_plus',
            'vcard',
            'lead_form',
            'product_catalogue',
            'restaurant_menu',
            'app_download',
            'resume_qr_code',
            'file_upload',
            'event',
            'booking',
            'donation',
        ];

        foreach (AppQrCode::typeCatalog() as $typeMeta) {
            $type = (string) $typeMeta['key'];
            $label = (string) $typeMeta['label'];
            $content = $this->qrDemoContent($type, $label, $bioUrl, $image);
            $destination = $this->qrDemoDestination($type, $content);
            $shortCode = AppQrCode::uniqueShortCode('demo-'.$type);

            $qr = AppQrCode::query()->create([
                'owner_user_id' => $user->id,
                'team_id' => $team->id,
                'type' => $type,
                'name' => '[DEMO] QR - '.$label,
                'status' => 'active',
                'destination_url' => $destination,
                'short_code' => $shortCode,
                'short_domain' => null,
                'foreground_color' => '#0f172a',
                'background_color' => '#ffffff',
                'pattern' => in_array($type, ['restaurant_menu', 'product_catalogue', 'business_profile'], true) ? 'mosaic' : 'gradient_rounded',
                'logo_url' => $image(0) ?: null,
                'scans_count' => 0,
                'last_scanned_at' => now()->subHours(random_int(1, 96)),
                'settings' => [
                    'source' => self::DEMO_MARKER,
                    'type_label' => $label,
                    'type_content' => $content,
                    'analytics' => [
                        'clicks' => random_int(8, 96),
                        'cta_clicks' => random_int(4, 48),
                    ],
                    'style_template' => 'clean_card',
                    'public_template' => in_array($type, $publicLandingTypes, true)
                        ? ($publicTemplates[$qrIndex % count($publicTemplates)] ?? 'split_showcase')
                        : null,
                    'link_bio_page_id' => $type === 'bio_links' ? $bioPage?->id : null,
                ],
            ]);

            $qrIndex++;
            $counts['app_qr_codes']++;
            $this->createQrDemoScanEvents($qr, random_int(12, 180), $counts);
        }
    }

    protected function clearQrDemoCodes(User $user, array &$deleted): void
    {
        $qrIds = AppQrCode::query()
            ->where('owner_user_id', $user->id)
            ->get(['id', 'name', 'settings'])
            ->filter(fn (AppQrCode $qrCode): bool => data_get($qrCode->settings, 'source') === self::DEMO_MARKER || str_starts_with((string) $qrCode->name, '[DEMO] QR -') || str_starts_with((string) $qrCode->name, 'DEMO QR -'))
            ->pluck('id');

        if ($qrIds->isEmpty()) {
            return;
        }

        $deleted['qr_scan_events'] = AppQrScanEvent::query()
            ->whereIn('app_qr_code_id', $qrIds)
            ->delete();

        $deleted['app_qr_codes'] = AppQrCode::query()
            ->whereIn('id', $qrIds)
            ->delete();
    }

    protected function createShortLinks(User $user, Team $team, array $imageFiles, array &$counts): void
    {
        if (! Schema::hasTable('app_short_links')) {
            return;
        }

        $imageUrls = collect($imageFiles)
            ->map(fn (AppFile $file): ?string => $this->demoPublicImageUrl($file))
            ->filter()
            ->values()
            ->all();

        $image = fn (int $index): ?string => $imageUrls[$index % max(1, count($imageUrls))] ?? null;

        $bioPages = LinkBioPage::query()
            ->where('owner_user_id', $user->id)
            ->get()
            ->filter(fn (LinkBioPage $page): bool => data_get($page->settings, 'source') === self::DEMO_MARKER)
            ->values();

        $qrCodes = AppQrCode::query()
            ->where('owner_user_id', $user->id)
            ->get()
            ->filter(fn (AppQrCode $qrCode): bool => data_get($qrCode->settings, 'source') === self::DEMO_MARKER)
            ->values();

        $bioUrl = fn (int $index): string => $this->linkBioPublicUrl($bioPages->get($index));
        $qrUrl = fn (string $type, string $fallback): string => optional($qrCodes->firstWhere('type', $type))->shortUrl() ?: $fallback;
        $now = now();

        $definitions = [
            [
                'name' => 'Instagram bio launch hub',
                'code' => 'demo-bio-launch',
                'folder' => 'LinkBio demos',
                'campaign' => 'Creator launch',
                'destination_url' => $bioUrl(0),
                'clicks' => 96,
                'tags' => ['linkbio', 'instagram', 'creator'],
                'og_title' => 'LinkBio campaign hub for creators',
                'og_description' => 'One branded profile page for launch links, social proof, QR campaigns, and lead capture.',
                'og_image' => $image(1),
            ],
            [
                'name' => 'QR packaging product scan',
                'code' => 'demo-qr-packaging',
                'folder' => 'QR campaigns',
                'campaign' => 'Retail packaging',
                'destination_url' => $qrUrl('product_catalogue', 'https://example.com/products/skincare-launch'),
                'clicks' => 74,
                'tags' => ['qr-code', 'packaging', 'product'],
                'og_title' => 'Product QR campaign with editable destination',
                'og_description' => 'Use dynamic QR short links for packaging, product cards, and post-purchase instructions.',
                'og_image' => $image(2),
            ],
            [
                'name' => 'Restaurant menu QR redirect',
                'code' => 'demo-menu-qr',
                'folder' => 'QR campaigns',
                'campaign' => 'Restaurant table tents',
                'destination_url' => $qrUrl('restaurant_menu', 'https://example.com/menu'),
                'clicks' => 138,
                'tags' => ['qr-code', 'menu', 'local-business'],
                'og_title' => 'Restaurant menu QR with analytics',
                'og_description' => 'Track menu scans by day, country, and device while keeping the printed QR destination editable.',
                'og_image' => $image(3),
            ],
            [
                'name' => 'Campaign short link A/B test',
                'code' => 'demo-ab-offer',
                'folder' => 'Paid campaigns',
                'campaign' => 'Spring offer',
                'destination_url' => 'https://example.com/spring-offer',
                'clicks' => 112,
                'tags' => ['shortlinks', 'ab-test', 'utm'],
                'og_title' => 'Short link with routing and A/B variants',
                'og_description' => 'Route clicks between offer pages, devices, or countries without changing ads and social posts.',
                'og_image' => $image(4),
                'redirect_rules' => [
                    ['url' => 'https://example.com/spring-offer-a', 'weight' => 65],
                    ['url' => 'https://example.com/spring-offer-b', 'weight' => 35, 'devices' => ['mobile']],
                ],
            ],
            [
                'name' => 'Sales deck QR follow-up',
                'code' => 'demo-sales-deck',
                'folder' => 'Sales assets',
                'campaign' => 'Offline sales kit',
                'destination_url' => 'https://example.com/resources/sales-deck',
                'clicks' => 43,
                'tags' => ['shortlinks', 'sales', 'offline'],
                'og_title' => 'Reusable short link for sales decks and printed cards',
                'og_description' => 'Keep one clean short link for decks, flyers, QR cards, and sales follow-up campaigns.',
                'og_image' => $image(5),
            ],
            [
                'name' => 'Bio page to QR handoff',
                'code' => 'demo-bio-to-qr',
                'folder' => 'Cross-channel',
                'campaign' => 'Bio + QR flow',
                'destination_url' => $bioUrl(1),
                'clicks' => 61,
                'tags' => ['linkbio', 'qr-code', 'shortlinks'],
                'og_title' => 'Connect LinkBio, short links, and QR tracking',
                'og_description' => 'Show how the same brand campaign can move from social bio to QR scans and tracked links.',
                'og_image' => $image(6),
            ],
        ];

        foreach ($definitions as $index => $definition) {
            $clicks = (int) $definition['clicks'];
            $lastClickedAt = $now->copy()->subMinutes(random_int(15, 1440));

            $link = AppShortLink::query()->create([
                'owner_user_id' => $user->id,
                'team_id' => $team->id,
                'name' => $definition['name'],
                'folder' => $definition['folder'],
                'campaign' => $definition['campaign'],
                'tags' => $definition['tags'],
                'destination_url' => $definition['destination_url'],
                'short_code' => $this->uniqueShortLinkCode((string) $definition['code']),
                'status' => 'active',
                'expires_at' => null,
                'click_limit' => null,
                'password_hash' => null,
                'clicks_count' => $clicks,
                'last_clicked_at' => $lastClickedAt,
                'og_title' => $definition['og_title'],
                'og_description' => $definition['og_description'],
                'og_image' => $definition['og_image'],
                'settings' => [
                    'source' => self::DEMO_MARKER,
                    'demo_kind' => 'short_link',
                    'recommended_use' => ['social_bio', 'qr_code', 'offline_print'][$index % 3],
                ],
                'redirect_rules' => $definition['redirect_rules'] ?? null,
                'moderation_status' => 'approved',
                'moderation_note' => null,
            ]);

            $counts['short_links']++;
            $this->createShortLinkDemoClicks($link, $clicks, $counts);
        }
    }

    protected function clearShortLinks(User $user, array &$deleted): void
    {
        if (! Schema::hasTable('app_short_links')) {
            return;
        }

        $links = AppShortLink::query()
            ->where('owner_user_id', $user->id)
            ->get(['id', 'short_code', 'settings'])
            ->filter(fn (AppShortLink $link): bool => data_get($link->settings, 'source') === self::DEMO_MARKER || str_starts_with((string) $link->short_code, 'demo-'));

        if ($links->isEmpty()) {
            return;
        }

        $deleted['short_link_clicks'] = AppShortLinkClick::query()
            ->whereIn('app_short_link_id', $links->pluck('id'))
            ->delete();

        $deleted['short_links'] = AppShortLink::query()
            ->whereIn('id', $links->pluck('id'))
            ->delete();
    }

    protected function createShortLinkDemoClicks(AppShortLink $link, int $clicks, array &$counts): void
    {
        if (! Schema::hasTable('app_short_link_clicks')) {
            return;
        }

        $countries = ['VN', 'US', 'SG', 'TH', 'JP', 'KR'];
        $referers = ['https://instagram.com', 'https://facebook.com', 'https://tiktok.com', 'https://google.com', 'https://example.com/newsletter'];
        $devices = ['Mobile', 'Mobile', 'Desktop', 'Tablet'];
        $now = now();

        for ($i = 0; $i < $clicks; $i++) {
            AppShortLinkClick::query()->create([
                'app_short_link_id' => $link->id,
                'owner_user_id' => $link->owner_user_id,
                'ip_address' => '203.0.113.'.(($i % 190) + 10),
                'user_agent' => 'AdminFaker Short Link Demo Browser',
                'referer' => $referers[$i % count($referers)],
                'country' => $countries[$i % count($countries)],
                'metadata' => [
                    'source' => self::DEMO_MARKER,
                    'device' => $devices[$i % count($devices)],
                    'browser' => ['Chrome', 'Safari', 'Edge', 'Firefox'][$i % 4],
                    'campaign' => $link->campaign,
                    'variant' => $link->redirect_rules ? ['key' => $i % 3 === 0 ? 'b' : 'a'] : null,
                ],
                'created_at' => $now->copy()->subMinutes(random_int(5, 43200)),
            ]);

            $counts['short_link_clicks']++;
        }
    }

    protected function linkBioPublicUrl(?LinkBioPage $page): string
    {
        if (! $page) {
            return 'https://example.com/bio';
        }

        if (Route::has('link-bio.public.show')) {
            return route('link-bio.public.show', ['slug' => $page->slug]);
        }

        return url('/b/'.$page->slug);
    }

    protected function uniqueShortLinkCode(string $base): string
    {
        $base = Str::limit(Str::slug($base, '-'), 42, '');
        $code = $base !== '' ? $base : Str::lower(Str::random(8));
        $counter = 2;

        while (AppShortLink::query()->where('short_code', $code)->exists()) {
            $code = Str::limit($base, 40, '').'-'.$counter;
            $counter++;
        }

        return $code;
    }

    protected function createQrDemoScanEvents(AppQrCode $qrCode, int $events, array &$counts): void
    {
        $countries = ['VN', 'US', 'SG', 'TH', 'JP', 'KR'];
        $sources = ['poster', 'packaging', 'menu_table', 'social_profile', 'event_badge'];
        $now = now();

        for ($i = 0; $i < $events; $i++) {
            AppQrScanEvent::query()->create([
                'app_qr_code_id' => $qrCode->id,
                'owner_user_id' => $qrCode->owner_user_id,
                'source' => $sources[$i % count($sources)],
                'ip_address' => hash('sha256', 'admin-faker-qr-'.$qrCode->id.'-'.$i),
                'user_agent' => 'AdminFaker QR Demo Browser',
                'country' => $countries[$i % count($countries)],
                'referer' => $i % 4 === 0 ? 'https://example.com/demo-referrer' : '',
                'metadata' => [
                    'source' => self::DEMO_MARKER,
                    'browser' => ['Chrome', 'Safari', 'Edge', 'Firefox'][$i % 4],
                    'os' => ['iOS', 'Android', 'Windows', 'macOS'][$i % 4],
                    'device' => $i % 3 === 0 ? 'Desktop' : 'Mobile',
                    'language' => ['vi-VN', 'en-US', 'th-TH', 'ja-JP'][$i % 4],
                    'dynamic_destination' => $qrCode->destination_url,
                ],
                'created_at' => $now->copy()->subMinutes(random_int(10, 43200)),
            ]);

            $counts['qr_scan_events']++;
        }

        $qrCode->forceFill([
            'scans_count' => $events,
            'last_scanned_at' => $now->copy()->subMinutes(random_int(5, 1440)),
        ])->save();
    }

    protected function qrDemoContent(string $type, string $label, string $bioUrl, callable $image): array
    {
        return match ($type) {
            'dynamic_url' => [
                'title' => 'Spring Campaign Landing',
                'url' => 'https://example.com/spring-offer',
                'utm_source' => 'demo_qr',
                'utm_medium' => 'print',
                'utm_campaign' => 'spring_launch',
            ],
            'bio_links' => [
                'title' => 'Creator Link Hub',
                'url' => $bioUrl,
            ],
            'business_profile' => [
                'title' => 'Stack Coffee Studio',
                'category' => 'Specialty coffee shop',
                'description' => 'Fresh espresso, quiet workspace, and weekend tasting sessions in District 1.',
                'phone' => '+84 901 234 567',
                'email' => 'hello@stackcoffee.test',
                'website' => 'https://example.com/stack-coffee',
                'address' => '24 Nguyen Hue, District 1, Ho Chi Minh City',
                'opening_hours' => "Mon-Fri 08:00-21:00\nSat-Sun 09:00-22:00",
                'logo_url' => $image(0),
            ],
            'business_review', 'google_review' => [
                'title' => 'Stack Dental Clinic',
                'review_url' => 'https://example.com/review/stack-dental',
                'headline' => 'How was your visit?',
                'rating_threshold' => '4+',
                'message' => 'Your review helps new patients choose a clinic with confidence.',
                'thank_you_message' => 'Thank you for helping us improve every appointment.',
            ],
            'website_builder' => [
                'title' => 'Summer Skin Launch',
                'headline' => 'Clean skincare for humid city days',
                'description' => 'Explore the launch bundle, ingredient notes, and booking slots for a free skin consultation.',
                'hero_image_url' => $image(1),
                'cta_label' => 'Shop launch kit',
                'cta_url' => 'https://example.com/skincare-launch',
                'links' => [
                    ['label' => 'Ingredients', 'url' => 'https://example.com/ingredients'],
                    ['label' => 'Book consultation', 'url' => 'https://example.com/book'],
                    ['label' => 'Customer results', 'url' => 'https://example.com/results'],
                ],
            ],
            'vcard_plus', 'vcard' => [
                'title' => 'Mai Nguyen',
                'company' => 'Northstar Design',
                'job_title' => 'Brand Strategist',
                'phone' => '+84 912 345 678',
                'email' => 'mai@northstar.test',
                'website' => 'https://example.com/mai',
                'address' => 'Hanoi, Vietnam',
                'links' => [
                    ['label' => 'Portfolio', 'url' => 'https://example.com/portfolio'],
                    ['label' => 'LinkedIn', 'url' => 'https://example.com/linkedin'],
                ],
            ],
            'lead_form' => [
                'title' => 'Book a Strategy Call',
                'submit_label' => 'Request consultation',
                'description' => 'Tell us what you are launching and our team will respond with a project fit check.',
                'notify_email' => 'sales@example.com',
                'webhook_url' => 'https://example.com/webhooks/leads',
                'thank_you_message' => 'Thanks. We will get back to you within one business day.',
                'fields' => [
                    ['label' => 'Full name', 'type' => 'text', 'required' => true],
                    ['label' => 'Email address', 'type' => 'email', 'required' => true],
                    ['label' => 'Project brief', 'type' => 'textarea', 'required' => false],
                ],
            ],
            'restaurant_menu' => [
                'title' => 'Saigon Bowl House',
                'subtitle' => 'Vietnamese bowls and iced tea',
                'description' => 'Scan to browse today\'s menu, allergen notes, and quick WhatsApp ordering.',
                'background_image_url' => $image(2),
                'logo_url' => $image(3),
                'show_prices' => 'yes',
                'language' => 'English',
                'whatsapp_enabled' => 'enabled',
                'whatsapp_number' => '+84901234567',
                'products' => [
                    ['name' => 'Lemongrass chicken bowl', 'price' => '89,000 VND', 'description' => 'Rice, herbs, pickles, grilled chicken, house sauce.', 'url' => 'https://example.com/menu/chicken', 'image_url' => $image(4)],
                    ['name' => 'Tofu summer roll set', 'price' => '69,000 VND', 'description' => 'Fresh rolls, peanut sauce, seasonal greens.', 'url' => 'https://example.com/menu/tofu', 'image_url' => $image(5)],
                    ['name' => 'Peach jasmine tea', 'price' => '39,000 VND', 'description' => 'Cold brewed tea with peach and basil seed.', 'url' => 'https://example.com/menu/tea', 'image_url' => $image(6)],
                ],
                'categories' => [['label' => 'Bowls', 'url' => '#bowls'], ['label' => 'Drinks', 'url' => '#drinks']],
                'allergens' => [['label' => 'Peanuts available on request', 'url' => '#']],
                'reviews' => [['label' => 'Review us on Google', 'url' => 'https://example.com/review']],
            ],
            'product_catalogue' => [
                'title' => 'Minimal Desk Gear',
                'description' => 'Compact work accessories for clean setups and repeatable daily workflows.',
                'show_prices' => 'yes',
                'cta_label' => 'Ask for quote',
                'cta_url' => 'https://example.com/contact-sales',
                'whatsapp_number' => '+84901234567',
                'products' => [
                    ['name' => 'Aluminum laptop stand', 'price' => '$49', 'description' => 'Foldable, matte finish, travel friendly.', 'url' => 'https://example.com/products/stand', 'image_url' => $image(7)],
                    ['name' => 'Cable dock kit', 'price' => '$29', 'description' => 'Magnetic clips and desk routing tray.', 'url' => 'https://example.com/products/dock', 'image_url' => $image(8)],
                    ['name' => 'Focus desk mat', 'price' => '$35', 'description' => 'Large surface, stitched edge, soft glide.', 'url' => 'https://example.com/products/mat', 'image_url' => $image(9)],
                ],
            ],
            'app_download' => [
                'title' => 'Flow Notes App',
                'description' => 'Capture meeting notes, tasks, and voice memos from one lightweight mobile app.',
                'ios_url' => 'https://apps.apple.com/app/example',
                'android_url' => 'https://play.google.com/store/apps/details?id=example',
                'fallback_url' => 'https://example.com/app',
            ],
            'resume_qr_code' => [
                'title' => 'Alex Tran',
                'job_title' => 'Senior Product Designer',
                'summary' => 'Portfolio, case studies, and downloadable CV for product design roles.',
                'portfolio_url' => 'https://example.com/alex-portfolio',
                'resume_file_url' => 'https://example.com/alex-tran-cv.pdf',
            ],
            'file_upload' => [
                'title' => 'Event Media Kit',
                'file_url' => 'https://example.com/files/event-media-kit.pdf',
                'description' => 'Download brand photos, speaker bios, venue map, and usage guidelines.',
                'button_label' => 'Download media kit',
                'access_note' => 'Updated for 2026 sponsor package.',
            ],
            'event' => [
                'title' => 'Creator Growth Night',
                'starts_at' => '2026-06-18 18:30',
                'ends_at' => '2026-06-18 21:00',
                'location' => 'The Workshop, District 3, Ho Chi Minh City',
                'rsvp_url' => 'https://example.com/events/creator-growth-night',
                'description' => 'A compact evening of growth case studies, content systems, and creator networking.',
            ],
            'booking' => [
                'title' => 'Studio Appointment',
                'description' => 'Reserve a 45-minute consultation for brand photos, product sets, or founder portraits.',
                'booking_url' => 'https://example.com/book/studio',
                'location' => 'Studio 4B, Hanoi',
            ],
            'email_dynamic', 'email' => ['title' => 'Email Sales Team', 'email' => 'sales@example.com', 'subject' => 'Product inquiry', 'body' => 'Hi, I would like to ask about your current package.'],
            'sms_dynamic', 'sms' => ['title' => 'SMS Support', 'phone' => '+84901234567', 'message' => 'Hi, I need help with my booking.'],
            'call' => ['title' => 'Call Hotline', 'phone' => '+84901234567'],
            'wifi' => ['title' => 'Guest WiFi', 'ssid' => 'Stack Guest', 'password' => 'demo2026', 'encryption' => 'WPA'],
            'whatsapp' => ['title' => 'WhatsApp Sales', 'phone' => '+84901234567', 'message' => 'Hi, I want to order.'],
            'facetime' => ['title' => 'FaceTime Consultation', 'contact' => '+84901234567', 'mode' => 'video'],
            'location' => ['title' => 'Store Location', 'address' => '24 Nguyen Hue, District 1, Ho Chi Minh City', 'map_url' => 'https://maps.google.com/?q=24+Nguyen+Hue+Ho+Chi+Minh'],
            'crypto' => ['title' => 'Bitcoin Wallet', 'currency' => 'BTC', 'wallet' => 'bc1qdemo7samplewalletaddress0000000000'],
            'donation' => ['title' => 'Community Library Fund', 'description' => 'Support weekend reading rooms for local students.', 'cta_label' => 'Donate now', 'cta_url' => 'https://example.com/donate'],
            'paypal' => ['title' => 'PayPal Payment', 'email' => 'billing@example.com', 'amount' => '25.00', 'currency' => 'USD', 'url' => 'https://paypal.me/example/25'],
            'upi_static', 'upi_dynamic' => ['title' => 'UPI Payment', 'upi_id' => 'merchant@upi', 'payee_name' => 'Stack Demo Store', 'amount' => '500', 'note' => 'Demo order payment'],
            'zoom' => ['title' => 'Zoom Meeting', 'url' => 'https://example.zoom.us/j/123456789'],
            'telegram' => ['title' => 'Telegram Channel', 'url' => 'https://t.me/examplechannel'],
            'brazilian_pix' => ['title' => 'Brazilian PIX Payment', 'pix_key' => 'demo@example.com', 'amount' => '75.00'],
            'messenger' => ['title' => 'Messenger Support', 'url' => 'https://m.me/examplepage'],
            'viber' => ['title' => 'Viber Chat', 'phone' => '+84901234567', 'message' => 'Hello, I need assistance.'],
            'static_url' => ['title' => 'Static Product URL', 'url' => 'https://example.com/static-product'],
            default => ['title' => 'Demo '.$label, 'description' => 'Demo content for '.$label, 'url' => 'https://example.com/'.Str::slug($label)],
        };
    }

    protected function qrDemoDestination(string $type, array $content): string
    {
        return match ($type) {
            'email', 'email_dynamic' => 'mailto:'.($content['email'] ?? 'hello@example.com').'?subject='.rawurlencode($content['subject'] ?? 'Hello').'&body='.rawurlencode($content['body'] ?? ''),
            'sms', 'sms_dynamic' => 'sms:'.($content['phone'] ?? '+84901234567').'?body='.rawurlencode($content['message'] ?? ''),
            'call' => 'tel:'.($content['phone'] ?? '+84901234567'),
            'wifi' => 'WIFI:T:'.($content['encryption'] ?? 'WPA').';S:'.($content['ssid'] ?? 'Guest').';P:'.($content['password'] ?? '').';;',
            'whatsapp' => 'https://wa.me/'.preg_replace('/\D+/', '', (string) ($content['phone'] ?? '+84901234567')).'?text='.rawurlencode($content['message'] ?? ''),
            'facetime' => ($content['mode'] ?? 'video') === 'audio'
                ? 'facetime-audio:'.($content['contact'] ?? '+84901234567')
                : 'facetime:'.($content['contact'] ?? '+84901234567'),
            'location' => $content['map_url'] ?? 'https://maps.google.com',
            'crypto' => 'bitcoin:'.($content['wallet'] ?? 'bc1qdemo'),
            'paypal' => $content['url'] ?? 'https://paypal.me/example',
            'upi_static', 'upi_dynamic' => 'upi://pay?'.http_build_query(array_filter([
                'pa' => $content['upi_id'] ?? 'merchant@upi',
                'pn' => $content['payee_name'] ?? 'Stack Demo Store',
                'am' => $content['amount'] ?? '100',
                'tn' => $content['note'] ?? '',
            ])),
            'viber' => 'viber://chat?number='.rawurlencode(preg_replace('/[^\d+]+/', '', (string) ($content['phone'] ?? '+84901234567')) ?: '+84901234567'),
            'zoom', 'telegram', 'messenger', 'static_url' => $content['url'] ?? 'https://example.com',
            'business_review', 'google_review' => $content['review_url'] ?? 'https://example.com/review',
            'file_upload' => $content['file_url'] ?? 'https://example.com/file.pdf',
            'event' => $content['rsvp_url'] ?? 'https://example.com/event',
            'booking' => $content['booking_url'] ?? 'https://example.com/book',
            'app_download' => $content['fallback_url'] ?? 'https://example.com/app',
            'resume_qr_code' => $content['portfolio_url'] ?? 'https://example.com/resume',
            default => $content['cta_url'] ?? $content['website'] ?? $content['url'] ?? 'https://example.com/'.Str::slug((string) ($content['title'] ?? 'demo')),
        };
    }

    protected function linkBioBlock(
        string $type,
        string $title,
        string $subtitle = '',
        string $content = '',
        array $items = [],
        string $buttonLabel = '',
        string $buttonUrl = '',
    ): array {
        return [
            'type' => $type,
            'title' => $title,
            'subtitle' => $subtitle,
            'content' => $content,
            'url' => '',
            'button_label' => $buttonLabel,
            'button_url' => $buttonUrl,
            'enabled' => true,
            'items' => $items,
        ];
    }

    protected function linkBioItem(
        string $label,
        string $url = '',
        string $note = '',
        string $icon = 'fa-solid fa-link',
        string $image = '',
        string $price = '',
        string $answer = '',
        string $value = '',
    ): array {
        return [
            'label' => $label,
            'url' => $url,
            'note' => $note,
            'icon' => $icon,
            'image' => $image,
            'value' => $value,
            'price' => $price,
            'placeholder' => '',
            'answer' => $answer,
            'field_type' => 'text',
        ];
    }

    protected function linkBioClickTargets(array $blocks): array
    {
        $targets = [];

        foreach ($blocks as $blockIndex => $block) {
            foreach ((array) data_get($block, 'items', []) as $itemIndex => $item) {
                $url = trim((string) data_get($item, 'url', ''));

                if ($url === '') {
                    continue;
                }

                $targets[] = [
                    'block_index' => $blockIndex,
                    'item_index' => $itemIndex,
                    'url' => $url,
                ];
            }
        }

        return $targets;
    }

    protected function uniqueLinkBioSlug(string $base): string
    {
        $base = Str::slug($base) ?: 'demo-smartbio-page';
        $slug = $base;
        $counter = 2;

        while (LinkBioPage::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    protected function demoPublicImageUrl(?AppFile $file): string
    {
        if (! $file || ! filled($file->path)) {
            return '';
        }

        $disk = (string) ($file->disk ?: 'public');
        $path = trim((string) $file->path);

        return (string) ($this->storageDriverManager->publicUrl($disk, $path) ?: $this->demoImageUrl($file) ?: '');
    }

    protected function createSupportInbox(User $user, Team $team, array &$counts): void
    {
        $now = time();

        $category = SupportCategory::query()->firstOrCreate(
            ['name' => '[DEMO] Workspace Help'],
            [
                'id_secure' => Str::random(32),
                'icon' => 'fa-light fa-life-ring',
                'color' => '#2563eb',
                'status' => true,
                'changed' => $now,
                'created' => $now,
            ],
        );

        $type = SupportType::query()->firstOrCreate(
            ['name' => '[DEMO] Product Guidance'],
            [
                'id_secure' => Str::random(32),
                'icon' => 'fa-light fa-compass-drafting',
                'color' => '#7c3aed',
                'status' => true,
                'changed' => $now,
                'created' => $now,
            ],
        );

        $label = SupportLabel::query()->firstOrCreate(
            ['name' => '[DEMO] Priority'],
            [
                'id_secure' => Str::random(32),
                'icon' => 'fa-light fa-bolt',
                'color' => '#f59e0b',
                'status' => true,
                'changed' => $now,
                'created' => $now,
            ],
        );

        $tickets = [
            [
                'title' => '[DEMO] Need help aligning the publishing queue',
                'content' => 'I want the demo workspace to show a realistic queue with published and scheduled content. Which modules should I prepare first?',
                'status' => 1,
                'user_read' => true,
                'admin_read' => false,
                'offset' => 7200,
                'comments' => [
                    ['user_id' => $user->id, 'comment' => '<p>I mainly need the buyer-facing pages to look active and coherent.</p>', 'offset' => 5400],
                ],
            ],
            [
                'title' => '[DEMO] RSS automation preview looks empty',
                'content' => 'The RSS page is visible but it does not show enough activity for a sales demo. I need a better baseline state.',
                'status' => 2,
                'user_read' => false,
                'admin_read' => true,
                'offset' => 14400,
                'comments' => [
                    ['user_id' => $user->id, 'comment' => '<p>I would like a couple of queued items and one published outcome.</p>', 'offset' => 12600],
                ],
            ],
        ];

        foreach ($tickets as $index => $item) {
            $ticket = SupportTicket::query()->create([
                'id_secure' => Str::random(32),
                'uid' => $user->id,
                'open_by' => $user->id,
                'team_id' => $team->id,
                'cate_id' => $category->id,
                'type_id' => $type->id,
                'title' => $item['title'],
                'content' => $item['content'],
                'status' => $item['status'],
                'pin' => $index === 0,
                'user_read' => $item['user_read'],
                'admin_read' => $item['admin_read'],
                'changed' => $now - 1800,
                'created' => $now - $item['offset'],
            ]);

            DB::table('support_map_labels')->insert([
                'ticket_id' => $ticket->id,
                'label_id' => $label->id,
            ]);

            foreach ($item['comments'] as $comment) {
                SupportComment::query()->create([
                    'id_secure' => Str::random(32),
                    'ticket_id' => $ticket->id,
                    'user_id' => $comment['user_id'],
                    'comment' => $comment['comment'],
                    'changed' => $now - 1200,
                    'created' => $now - $comment['offset'],
                ]);

                $counts['support_comments']++;
            }

            $counts['support_tickets']++;
        }
    }

    protected function createAffiliateSnapshot(User $user, array &$counts): void
    {
        $affiliate = app(AffiliateService::class);
        $affiliate->ensureReferralCode($user);

        $profile = $affiliate->ensureProfile($user);

        $payments = [
            ['transaction_id' => 'DEMO-AFF-001-'.$user->id, 'amount' => 320.00, 'commission_rate' => 15.00, 'commission' => 48.00, 'status' => AffiliateCommission::STATUS_APPROVED, 'created_offset' => 172800],
            ['transaction_id' => 'DEMO-AFF-002-'.$user->id, 'amount' => 220.00, 'commission_rate' => 15.00, 'commission' => 33.00, 'status' => AffiliateCommission::STATUS_PENDING, 'created_offset' => 86400],
            ['transaction_id' => 'DEMO-AFF-003-'.$user->id, 'amount' => 180.00, 'commission_rate' => 15.00, 'commission' => 27.00, 'status' => AffiliateCommission::STATUS_APPROVED, 'created_offset' => 43200],
        ];

        foreach ($payments as $index => $item) {
            // Keyed by transaction_id so re-running with --no-clear stays idempotent
            // instead of violating the unique constraint on payment_history.transaction_id.
            $payment = PaymentHistory::query()->updateOrCreate(
                ['transaction_id' => $item['transaction_id']],
                [
                    'id_secure' => Str::random(32),
                    'uid' => $user->id,
                    'plan_id' => $user->plan_id,
                    'from' => 'manual',
                    'currency' => 'USD',
                    'by' => 'admin-faker',
                    'amount' => $item['amount'],
                    'status' => 1,
                    'changed' => time() - $item['created_offset'] + 1200,
                    'created' => time() - $item['created_offset'],
                    'meta' => [
                        'source' => self::DEMO_MARKER,
                    ],
                ],
            );

            AffiliateCommission::query()->updateOrCreate(
                ['payment_history_id' => $payment->id],
                [
                    'id_secure' => Str::random(32),
                    'affiliate_user_id' => $user->id,
                    'referred_user_id' => $user->id,
                    'amount' => $item['amount'],
                    'commission_rate' => $item['commission_rate'],
                    'commission' => $item['commission'],
                    'status' => $item['status'],
                    'meta' => [
                        'source' => self::DEMO_MARKER,
                        'sample' => $index + 1,
                    ],
                    'approved_at' => $item['status'] === AffiliateCommission::STATUS_APPROVED ? now()->subHours($index + 5) : null,
                    'created_at' => now()->subSeconds($item['created_offset']),
                    'updated_at' => now()->subSeconds(max(0, $item['created_offset'] - 1800)),
                ],
            );

            $counts['affiliate_commissions']++;
        }

        $withdrawals = [
            ['amount' => 450000, 'payment_method' => 'Chuyển khoản ngân hàng', 'payment_details' => 'Vietcombank – 0123456789 – NGUYEN VAN A', 'status' => AffiliateWithdrawal::STATUS_APPROVED, 'offset' => 28800],
            ['amount' => 625000, 'payment_method' => 'Chuyển khoản ngân hàng', 'payment_details' => 'Techcombank – đang chờ duyệt chi', 'status' => AffiliateWithdrawal::STATUS_PENDING, 'offset' => 10800],
        ];

        foreach ($withdrawals as $withdrawal) {
            AffiliateWithdrawal::query()->create([
                'id_secure' => Str::random(32),
                'affiliate_user_id' => $user->id,
                'amount' => $withdrawal['amount'],
                'payment_method' => $withdrawal['payment_method'],
                'payment_details' => $withdrawal['payment_details'],
                'notes' => 'Generated by Admin Faker',
                'status' => $withdrawal['status'],
                'processed_at' => $withdrawal['status'] === AffiliateWithdrawal::STATUS_APPROVED ? now()->subHours(4) : null,
                'created_at' => now()->subSeconds($withdrawal['offset']),
                'updated_at' => now()->subSeconds(max(0, $withdrawal['offset'] - 1200)),
            ]);

            $counts['affiliate_withdrawals']++;
        }

        $profile->forceFill([
            'clicks' => (int) $profile->clicks + 184,
            'conversions' => (int) $profile->conversions + count($payments),
            'total_approved' => (float) $profile->total_approved + 75.00,
            'total_withdrawal' => (float) $profile->total_withdrawal + 18.00,
            'total_balance' => (float) $profile->total_balance + 32.00,
        ])->save();
    }

    protected function createDailyPublishingPosts(User $user, Team $team, $accounts, array $imageFiles, CarbonInterface $now, array &$counts): void
    {
        $candidateAccounts = $this->preferredDemoPostingAccounts($accounts);
        $timeSlots = [[8, 30], [9, 0], [10, 0], [11, 30], [13, 15], [14, 15], [15, 15], [16, 15]];
        $titleSeeds = [
            'published' => ['published overview', 'published update', 'published local highlight'],
            'queued' => ['queued campaign', 'queued promo', 'queued product teaser'],
            'draft' => ['draft text idea', 'draft local note', 'draft quick update'],
            'processing' => ['processing creative', 'processing caption polish'],
        ];

        foreach (range(-5, 5) as $dayOffset) {
            $postsForDay = random_int(1, 3);

            for ($slotIndex = 0; $slotIndex < $postsForDay; $slotIndex++) {
                $account = $candidateAccounts->random();
                $networkLabel = $this->demoNetworkLabel((string) $account->provider_key);
                [$statusKey, $status] = $this->demoStatusForDayOffset($dayOffset);
                [$hour, $minute] = $timeSlots[array_rand($timeSlots)];
                $timePost = $now->copy()->addDays($dayOffset)->setTime($hour, $minute + ($slotIndex * 5));
                $isTextOnly = random_int(1, 100) <= 25;
                $image = $isTextOnly ? null : $this->randomDemoImage($imageFiles);
                $titleSuffix = $titleSeeds[$statusKey][array_rand($titleSeeds[$statusKey])];
                $title = $networkLabel.' '.$titleSuffix;
                $caption = $this->demoCaptionForStatus($networkLabel, $statusKey, $isTextOnly);
                $result = null;
                $changedAt = $status === PublishingPost::STATUS_PROCESSING
                    ? $timePost->copy()->subMinutes(3)
                    : $timePost->copy()->addMinutes(8);

                if ($status === PublishingPost::STATUS_PUBLISHED) {
                    $result = [
                        'state' => 'published',
                        'url' => $this->demoPublishedPostUrl((string) $account->provider_key, Str::slug($title).'-'.$account->id),
                        'remote_post_id' => 'demo-'.Str::slug((string) $account->provider_key).'-'.$account->id.'-'.$timePost->timestamp,
                    ];
                }

                $this->createDemoPublishingPost(
                    user: $user,
                    team: $team,
                    account: $account,
                    image: $image,
                    title: $title,
                    caption: $caption,
                    status: $status,
                    timePost: $timePost,
                    changedAt: $changedAt,
                    result: $result,
                    module: 'publishing',
                    customData1: 'daily-preview',
                    customData3: 'manual-preview',
                );

                $counts['daily_schedules']++;
                $this->incrementPublishingStatusCount($counts, $status);
            }
        }

        RssSchedule::query()->create([
            'id_secure' => Str::random(32),
            'user_id' => $user->id,
            'team_id' => $team->id,
            'name' => 'Daily RSS Preview Schedule',
            'feed_url' => 'https://example.com/feed.xml',
            'description' => 'Sample RSS automation with a ready queue.',
            'account_ids' => $accounts->pluck('id')->take(2)->values()->all(),
            'settings' => [
                'source' => self::DEMO_MARKER,
                'post_type' => 'caption_image',
                'import_images' => true,
            ],
            'time_posts' => ['09:00', '15:00'],
            'weekdays' => ['mon', 'tue', 'wed', 'thu', 'fri'],
            'start_at' => $now->copy()->startOfDay()->timestamp,
            'last_checked_at' => $now->copy()->subHour()->timestamp,
            'last_queued_at' => $now->copy()->subMinutes(30)->timestamp,
            'next_run_at' => $now->copy()->addHour()->timestamp,
            'status' => true,
            'changed' => $now->timestamp,
            'created' => $now->timestamp,
        ]);

        RssSchedule::query()->create([
            'id_secure' => Str::random(32),
            'user_id' => $user->id,
            'team_id' => $team->id,
            'name' => 'Empty Starter Schedule',
            'feed_url' => 'https://example.com/starter-feed.xml',
            'description' => 'Blank schedule state for product preview.',
            'account_ids' => [$accounts->first()->id],
            'settings' => [
                'source' => self::DEMO_MARKER,
                'post_type' => 'caption_only',
                'empty_state' => true,
            ],
            'time_posts' => ['10:30'],
            'weekdays' => ['sat'],
            'start_at' => $now->copy()->startOfDay()->timestamp,
            'last_checked_at' => null,
            'last_queued_at' => null,
            'next_run_at' => $now->copy()->addDay()->timestamp,
            'status' => true,
            'changed' => $now->timestamp,
            'created' => $now->timestamp,
        ]);

        $counts['rss_schedules'] += 2;
    }

    protected function createFaqs(array &$counts): void
    {
        foreach ([
            [
                'slug' => 'demo-preview-trang-linkbio-ho-kinh-doanh',
                'title' => 'Trang LinkBio giúp hộ kinh doanh Đà Nẵng làm gì?',
                'content' => '<p>Trang LinkBio gom đặt lịch, phiếu ưu đãi, form khách hàng, đánh giá Google và liên hệ vào một đường dẫn duy nhất — phù hợp bio Facebook, Zalo hoặc tem QR trên quầy.</p>',
            ],
            [
                'slug' => 'demo-preview-sua-qr-sau-khi-in',
                'title' => 'In mã QR rồi có đổi trang đích được không?',
                'content' => '<p>Có. Mã QR động giữ hình in không đổi; bạn vẫn cập nhật landing page, UTM và nội dung chiến dịch sau khi dán trên menu, bảng hiệu hoặc standee tại quán.</p>',
            ],
            [
                'slug' => 'demo-preview-lien-ket-rut-gon-mlhub',
                'title' => 'Vì sao nên dùng liên kết rút gọn trên MLHUB?',
                'content' => '<p>Liên kết rút gọn trên tên miền của bạn giúp đo click, gắn thương hiệu mlhub.vn, quản lý chuyển hướng và nối báo cáo với mã QR cùng trang LinkBio.</p>',
            ],
            [
                'slug' => 'demo-preview-chia-luot-click-theo-thiet-bi',
                'title' => 'Có chia lượt click theo thiết bị hoặc khu vực không?',
                'content' => '<p>Có. Bạn có thể A/B test landing, xoay vòng theo tỷ trọng, lọc theo quốc gia, thiết bị hoặc khung giờ — hữu ích khi chạy quảng cáo Facebook/Google cho spa, quán ăn tại Đà Nẵng.</p>',
            ],
            [
                'slug' => 'demo-preview-linkbio-qr-lien-ket-cung-he-thong',
                'title' => 'LinkBio, mã QR và liên kết rút gọn dùng chung thế nào?',
                'content' => '<p>Dùng LinkBio làm trung tâm online, QR cho khách offline (bàn, tờ rơi, biển hiệu), liên kết rút gọn cho quảng cáo và tin nhắn — tất cả về một báo cáo trên MLHUB.</p>',
            ],
        ] as $faq) {
            Faq::query()->create([
                'id_secure' => Str::random(32),
                'title' => $faq['title'],
                'title_translations' => ['en' => $faq['title'], 'vi' => $faq['title']],
                'slug' => $faq['slug'],
                'content' => $faq['content'],
                'content_translations' => ['en' => $faq['content'], 'vi' => $faq['content']],
                'status' => 1,
                'changed' => time(),
                'created' => time(),
            ]);

            $counts['faqs']++;
        }
    }

    protected function createBlogTaxonomy(): array
    {
        $category = BlogCategory::query()->create([
            'id_secure' => Str::random(32),
            'name' => 'Hướng dẫn tăng trưởng cho hộ kinh doanh Đà Nẵng',
            'name_translations' => ['en' => 'Growth guides for Da Nang small businesses', 'vi' => 'Hướng dẫn tăng trưởng cho hộ kinh doanh Đà Nẵng'],
            'description' => 'Kiến thức thực tế về trang LinkBio, mã QR và liên kết rút gọn cho spa, quán ăn, nha khoa tại Đà Nẵng.',
            'description_translations' => ['en' => 'Practical guides for LinkBio, QR and short links for local businesses in Da Nang.', 'vi' => 'Kiến thức thực tế về trang LinkBio, mã QR và liên kết rút gọn cho spa, quán ăn, nha khoa tại Đà Nẵng.'],
            'slug' => 'demo-preview-huong-dan-ho-kinh-doanh-da-nang',
            'icon' => 'fa-light fa-link-simple',
            'color' => '#2563eb',
            'status' => 1,
            'sort_order' => 10,
            'changed' => time(),
            'created' => time(),
        ]);

        $tags = collect([
            ['slug' => 'demo-preview-trang-lien-ket', 'name' => 'Trang liên kết', 'name_en' => 'LinkBio'],
            ['slug' => 'demo-preview-ma-qr', 'name' => 'Mã QR', 'name_en' => 'QR Codes'],
            ['slug' => 'demo-preview-lien-ket-rut-gon', 'name' => 'Liên kết rút gọn', 'name_en' => 'Short Links'],
        ])->map(function (array $tag) {
            return BlogTag::query()->create([
                'id_secure' => Str::random(32),
                'name' => $tag['name'],
                'name_translations' => ['en' => $tag['name_en'], 'vi' => $tag['name']],
                'description' => 'Thẻ demo MLHUB – hộ kinh doanh Đà Nẵng.',
                'description_translations' => ['en' => 'MLHUB demo tag for Da Nang local businesses.', 'vi' => 'Thẻ demo MLHUB – hộ kinh doanh Đà Nẵng.'],
                'slug' => $tag['slug'],
                'color' => '#2563eb',
                'status' => 1,
                'changed' => time(),
                'created' => time(),
            ]);
        });

        return [$category, $tags];
    }

    protected function createBlogs(User $user, BlogCategory $category, $tags, array $imageFiles, array &$counts)
    {
        $now = time();

        $blogs = collect([
            [
                'slug' => 'demo-preview-trang-linkbio-ho-kinh-doanh-da-nang',
                'title' => 'Tạo trang LinkBio chuyển đổi cao cho hộ kinh doanh Đà Nẵng',
                'excerpt' => 'Gom đặt lịch, phiếu giảm giá, form khách hàng và đánh giá Google vào một đường dẫn bio — phù hợp spa, quán ăn, nha khoa.',
            ],
            [
                'slug' => 'demo-preview-ma-qr-menu-standee',
                'title' => 'Mã QR trên menu và standee — không khóa trang đích',
                'excerpt' => 'In QR trên menu, tờ rơi, biển hiệu; sau đó vẫn đổi landing, UTM và ưu đãi mà không in lại.',
            ],
            [
                'slug' => 'demo-preview-lien-ket-rut-gon-thuong-hieu',
                'title' => 'Liên kết rút gọn thương hiệu khách hàng tin hơn',
                'excerpt' => 'Thay link dài bằng mlhub.vn/... — dễ nhớ, đo click và gắn với chiến dịch QR, LinkBio.',
            ],
            [
                'slug' => 'demo-preview-bao-cao-linkbio-qr-lien-ket',
                'title' => 'Một báo cáo cho LinkBio, QR và liên kết rút gọn',
                'excerpt' => 'Xem click mạng xã hội, lượt quét offline và chuyển hướng trong cùng luồng chiến dịch MLHUB.',
            ],
            [
                'slug' => 'demo-preview-qr-dong-sau-khi-in',
                'title' => 'Mã QR động sau khi đã in — spa & quán ăn Đà Nẵng',
                'excerpt' => 'Cập nhật trang đích và nội dung ưu đãi sau khi QR đã dán tại quầy thu ngân.',
            ],
            [
                'slug' => 'demo-preview-ab-test-lien-ket-quang-cao',
                'title' => 'A/B test liên kết cho quảng cáo Facebook/Google',
                'excerpt' => 'Chia traffic landing theo tỷ trọng, thiết bị, quốc gia hoặc khung giờ mà không đổi link công khai.',
            ],
            [
                'slug' => 'demo-preview-linkbio-spa-quan-an',
                'title' => 'LinkBio cho spa, quán ăn và nha khoa tại Đà Nẵng',
                'excerpt' => 'Hiển thị dịch vụ, đặt lịch, đánh giá, tải file và form lead trên một trang mobile-first.',
            ],
            [
                'slug' => 'demo-preview-utm-qr-va-lien-ket',
                'title' => 'Preset UTM cho QR và liên kết rút gọn',
                'excerpt' => 'Chuẩn hóa tracking giữa quét QR, bio mạng xã hội, email và quảng cáo trả phí.',
            ],
            [
                'slug' => 'demo-preview-pixel-tai-danh-lien-ket',
                'title' => 'Gắn pixel retargeting trên liên kết rút gọn',
                'excerpt' => 'Xây audience remarketing từ click có ý định cao trên link chiến dịch.',
            ],
            [
                'slug' => 'demo-preview-ban-giai-phap-mlhub-cho-dai-ly',
                'title' => 'Bán gói MLHUB cho đại lý và hộ kinh doanh',
                'excerpt' => 'Định vị LinkBio, QR và liên kết rút gọn thành một hệ thống đo lường cho khách Đà Nẵng.',
            ],
        ])->values()->map(function (array $blog, int $index) use ($category, $tags, $imageFiles, $now, &$counts) {
            $image = $this->randomDemoImage($imageFiles);
            $content = '<p>'.$blog['excerpt'].'</p><p>Bài demo MLHUB minh họa cách hộ kinh doanh tại Đà Nẵng kết hợp trang LinkBio, mã QR in tại quầy và liên kết rút gọn cho quảng cáo — tất cả trong một báo cáo, không cần ghép nhiều công cụ.</p><p>Bạn có thể ra mắt trang công khai, in QR động, chia sẻ link thương hiệu và so sánh lượt quét hoặc click ngay trên bảng điều khiển.</p>';

            $entry = Blog::query()->create([
                'id_secure' => Str::random(32),
                'blog_category_id' => $category->id,
                'title' => $blog['title'],
                'title_translations' => ['en' => $blog['title'], 'vi' => $blog['title']],
                'excerpt' => $blog['excerpt'],
                'excerpt_translations' => ['en' => $blog['excerpt'], 'vi' => $blog['excerpt']],
                'content' => $content,
                'content_translations' => ['en' => $content, 'vi' => $content],
                'slug' => $blog['slug'],
                'meta_title' => $blog['title'],
                'meta_description' => $blog['excerpt'],
                'canonical_url' => null,
                'og_image' => $this->demoImageUrl($image),
                'thumbnail' => $this->demoImageUrl($image),
                'status' => 1,
                'published_at' => $now - ($index * 86400),
                'changed' => $now,
                'created' => $now,
            ]);

            $entry->tags()->syncWithoutDetaching($tags->pluck('id')->take(2)->all());
            $counts['blogs']++;

            return $entry;
        });

        return $blogs;
    }

    protected function createBlogRssSources(BlogCategory $category, $tags, $blogs, array &$counts): void
    {
        $source = BlogRssSource::query()->create([
            'id_secure' => Str::random(32),
            'name' => '[DEMO] Marketing Feed',
            'feed_url' => 'https://example.com/blog-feed.xml',
            'blog_category_id' => $category->id,
            'tag_ids' => $tags->pluck('id')->values()->all(),
            'status' => 1,
            'auto_publish' => 1,
            'ai_improve' => 1,
            'ai_auto_translate' => 0,
            'ai_prompt' => 'Keep the tone practical and product-led.',
            'sync_interval_minutes' => 60,
            'max_items_per_run' => 5,
            'last_checked_at' => time() - 3600,
            'last_imported_at' => time() - 1800,
            'changed' => time(),
            'created' => time(),
        ]);

        foreach ($blogs->take(2) as $index => $blog) {
            BlogRssImport::query()->create([
                'blog_rss_source_id' => $source->id,
                'blog_id' => $blog->id,
                'external_guid' => 'demo-guid-'.($index + 1),
                'external_url' => 'https://example.com/articles/demo-'.($index + 1),
                'content_hash' => sha1('demo-import-'.$index),
                'title' => $blog->title,
                'source_published_at' => time() - (($index + 1) * 7200),
                'changed' => time(),
                'created' => time(),
            ]);
        }
    }

    protected function createAiPublishing(User $user, Team $team, $accounts, array $imageFiles, CarbonInterface $now, array &$counts): array
    {
        $prompts = collect([
            ['title' => 'Launch teaser', 'text' => 'Write a short launch teaser with one strong CTA and one hashtag block.'],
            ['title' => 'Trust post', 'text' => 'Create a trust-building post that highlights scheduling, RSS, and AI publishing in one narrative.'],
        ])->map(function (array $prompt) use ($user, &$counts) {
            $model = AiPublishingPrompt::query()->create([
                'owner_user_id' => $user->id,
                'title' => $prompt['title'],
                'prompt_text' => $prompt['text'],
                'metadata' => [
                    'source' => self::DEMO_MARKER,
                ],
                'is_active' => true,
            ]);

            $counts['ai_prompts']++;

            return $model;
        });

        $runs = collect([
            [
                'name' => 'Demo AI Daily Publisher',
                'status' => 'completed',
                'stats' => ['generated_items' => 4, 'created_posts' => 3],
            ],
            [
                'name' => 'Demo AI Queue Builder',
                'status' => 'queued',
                'stats' => ['generated_items' => 0, 'created_posts' => 0],
            ],
        ])->values()->map(function (array $definition, int $index) use ($user, $team, $accounts, $prompts, $imageFiles, $now, &$counts) {
            $run = AiPublishingRun::query()->create([
                'owner_user_id' => $user->id,
                'team_id' => $team->id,
                'workspace_owner_user_id' => $user->id,
                'name' => $definition['name'],
                'campaign_id' => null,
                'label_ids' => [],
                'account_ids' => $accounts->pluck('id')->take(2)->values()->all(),
                'prompt_ids' => $prompts->pluck('id')->values()->all(),
                'schedule_config' => [
                    'timezone' => 'Asia/Saigon',
                    'days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
                    'times' => ['08:30', '14:00'],
                ],
                'generation_config' => [
                    'source' => self::DEMO_MARKER,
                    'image_mode' => count($imageFiles) > 0 ? 'uploaded_only' : 'disabled',
                    'voice' => 'brand',
                ],
                'stats' => $definition['stats'],
                'status' => $definition['status'],
                'last_processed_at' => $definition['status'] === 'completed' ? $now->copy()->subMinutes(20) : null,
            ]);

            if ($index === 0) {
                $candidateAccounts = $this->preferredDemoPostingAccounts($accounts);
                $aiDefinitions = [
                    [
                        'status' => PublishingPost::STATUS_PUBLISHED,
                        'time' => $now->copy()->subDays(2)->setTime(13, 15),
                    ],
                    [
                        'status' => PublishingPost::STATUS_SCHEDULED,
                        'time' => $now->copy()->addDays(2)->setTime(11, 30),
                    ],
                ];

                foreach ($aiDefinitions as $definition) {
                    $account = $candidateAccounts->random();
                    $networkLabel = $this->demoNetworkLabel((string) $account->provider_key);
                    $image = random_int(1, 100) <= 35 ? null : $this->randomDemoImage($imageFiles);
                    $result = null;

                    if ($definition['status'] === PublishingPost::STATUS_PUBLISHED) {
                        $result = [
                            'state' => 'published',
                            'url' => $this->demoPublishedPostUrl((string) $account->provider_key, 'ai-'.Str::slug($networkLabel).'-recap'),
                            'remote_post_id' => 'ai-demo-'.$account->provider_key.'-'.$account->id,
                        ];
                    }

                    $this->createDemoPublishingPost(
                        user: $user,
                        team: $team,
                        account: $account,
                        image: $image,
                        title: 'AI '.$networkLabel.' '.($definition['status'] === PublishingPost::STATUS_PUBLISHED ? 'published recap' : 'queued idea'),
                        caption: $definition['status'] === PublishingPost::STATUS_PUBLISHED
                            ? 'AI-generated '.$networkLabel.' post already published to show a completed automation result.'
                            : 'AI-generated '.$networkLabel.' preview copy queued for automatic publishing.',
                        status: $definition['status'],
                        timePost: $definition['time'],
                        changedAt: $definition['status'] === PublishingPost::STATUS_PUBLISHED ? $definition['time']->copy()->addMinutes(3) : $now,
                        result: $result,
                        module: 'ai_publishing',
                        customData1: (string) $run->id,
                        customData3: 'ai-publishing',
                    );

                    $counts['daily_schedules']++;
                    $this->incrementPublishingStatusCount($counts, (int) $definition['status']);
                }
            }

            $counts['ai_publishing_runs']++;

            return $run;
        });

        return [$prompts, $runs];
    }

    protected function createAiLogs(User $user, $runs, array &$counts): void
    {
        $rows = [
            ['provider' => 'openai', 'capability' => 'chat', 'model' => 'gpt-5.4', 'feature' => 'ai-publishing', 'tokens' => [950, 420, 1370], 'cost' => 0.024100, 'latency' => 1420],
            ['provider' => 'openai', 'capability' => 'image', 'model' => 'gpt-image-1', 'feature' => 'ai-publishing-image', 'tokens' => [0, 0, 0], 'cost' => 0.018000, 'latency' => 2140],
            ['provider' => 'openai', 'capability' => 'chat', 'model' => 'gpt-5.4-mini', 'feature' => 'faq-draft', 'tokens' => [320, 210, 530], 'cost' => 0.004200, 'latency' => 680],
            ['provider' => 'openai', 'capability' => 'chat', 'model' => 'gpt-5.4', 'feature' => 'rss-rewrite', 'tokens' => [1200, 560, 1760], 'cost' => 0.031500, 'latency' => 1640],
        ];

        foreach ($rows as $index => $row) {
            AiUsageLog::query()->create([
                'user_id' => $user->id,
                'provider' => $row['provider'],
                'capability' => $row['capability'],
                'model' => $row['model'],
                'status' => 'success',
                'feature' => $row['feature'],
                'route_name' => 'admin-faker.index',
                'prompt_tokens' => $row['tokens'][0],
                'completion_tokens' => $row['tokens'][1],
                'total_tokens' => $row['tokens'][2],
                'estimated_cost' => $row['cost'],
                'latency_ms' => $row['latency'],
                'metadata' => [
                    'source' => self::DEMO_MARKER,
                    'demo_run_id' => $runs->first()?->id,
                    'sample_index' => $index + 1,
                ],
                'created_at' => now()->subMinutes(($index + 1) * 12),
                'updated_at' => now()->subMinutes(($index + 1) * 12),
            ]);

            $counts['ai_logs']++;
        }
    }

    protected function createGlobalNotifications(User $user, array &$counts): void
    {
        $rows = [
            [
                'title' => '[DEMO] New Calendar Activity',
                'message' => 'Fresh sample posts were added so the calendar shows realistic activity across the next few days.',
                'url' => $this->demoRouteUrl('portal.publishing.calendar', 'portal'),
                'type' => 'news',
            ],
            [
                'title' => '[DEMO] Channels Ready For Review',
                'message' => 'Demo social accounts are available with avatars and realistic names so buyers can inspect the workspace faster.',
                'url' => $this->demoRouteUrl('portal.channels', 'portal'),
                'type' => 'news',
            ],
            [
                'title' => '[DEMO] AI Publishing Samples Added',
                'message' => 'A small set of AI-generated posts was added to show how automation feeds into the publishing queue.',
                'url' => $this->demoRouteUrl('portal.ai-publishing', 'portal'),
                'type' => 'info',
            ],
            [
                'title' => '[DEMO] Local SEO Preview Updated',
                'message' => 'Google Business and other demo channels were refreshed to make the trial workspace feel more complete.',
                'url' => $this->demoRouteUrl('portal.channels', 'portal'),
                'type' => 'tip',
            ],
            [
                'title' => '[DEMO] Workspace Review Tip',
                'message' => 'Open the queue, drafts, and calendar together to show buyers how content moves from draft to published state.',
                'url' => $this->demoRouteUrl('portal.publishing.calendar', 'portal'),
                'type' => 'tip',
            ],
        ];

        foreach ($rows as $index => $row) {
            NotificationManual::query()->create([
                'id_secure' => Str::random(32),
                'title' => $row['title'],
                'message' => $row['message'],
                'url' => $row['url'],
                'type' => $row['type'],
                'created_by' => $user->id,
                'is_global' => true,
                'created_at' => now()->subMinutes(($index + 1) * 9),
                'updated_at' => now()->subMinutes(($index + 1) * 9),
            ]);

            $counts['global_notifications']++;
        }
    }

    protected function demoRouteUrl(string $routeName, string $fallbackPath = ''): string
    {
        if (Route::has($routeName)) {
            return route($routeName);
        }

        return url($fallbackPath !== '' ? $fallbackPath : '/');
    }

    protected function mediaItem(AppFile $file, User $user): array
    {
        $previewUrl = $this->demoImageUrl($file);

        return [
            'id' => $file->id,
            'idSecure' => $file->id_secure,
            'name' => $file->name,
            'url' => $previewUrl,
            'previewUrl' => $previewUrl,
            'embedUrl' => $previewUrl,
            'thumbnail' => $previewUrl,
            'size' => $file->humanSize(),
            'category' => $file->category,
            'mimeType' => $file->mime_type,
            'extension' => $file->extension,
            'isImage' => (bool) $file->is_image,
        ];
    }

    protected function randomDemoImage(array $imageFiles): ?AppFile
    {
        if ($imageFiles === []) {
            return null;
        }

        return $imageFiles[array_rand($imageFiles)];
    }

    protected function preferredDemoPostingAccounts($accounts)
    {
        $preferredProviders = ['facebook', 'instagram', 'google_business_profile', 'x', 'reddit'];
        $preferred = $accounts->filter(fn (SocialAccount $account): bool => in_array((string) $account->provider_key, $preferredProviders, true))->values();

        return $preferred->isNotEmpty() ? $preferred : $accounts->values();
    }

    protected function demoStatusForDayOffset(int $dayOffset): array
    {
        if ($dayOffset <= -2) {
            return ['published', PublishingPost::STATUS_PUBLISHED];
        }

        if ($dayOffset === -1) {
            return random_int(1, 100) <= 80
                ? ['published', PublishingPost::STATUS_PUBLISHED]
                : ['processing', PublishingPost::STATUS_PROCESSING];
        }

        if ($dayOffset === 0) {
            $roll = random_int(1, 100);

            if ($roll <= 45) {
                return ['published', PublishingPost::STATUS_PUBLISHED];
            }

            if ($roll <= 80) {
                return ['queued', PublishingPost::STATUS_SCHEDULED];
            }

            return ['processing', PublishingPost::STATUS_PROCESSING];
        }

        $roll = random_int(1, 100);

        if ($roll <= 75) {
            return ['queued', PublishingPost::STATUS_SCHEDULED];
        }

        return ['draft', PublishingPost::STATUS_DRAFT];
    }

    protected function demoCaptionForStatus(string $networkLabel, string $statusKey, bool $isTextOnly): string
    {
        $format = $isTextOnly ? 'text-only' : 'image';

        return match ($statusKey) {
            'published' => 'A published '.$networkLabel.' '.$format.' sample post so the calendar feels active instead of empty.',
            'queued' => 'Queued '.$networkLabel.' '.$format.' content prepared for an upcoming slot in the next few days.',
            'draft' => 'Draft '.$networkLabel.' '.$format.' content waiting for a final edit before it moves into the queue.',
            'processing' => 'Processing '.$networkLabel.' '.$format.' content so the preview also shows work that is still in progress.',
            default => 'Demo '.$networkLabel.' '.$format.' post for preview.',
        };
    }

    protected function storeChannelAvatarFromFile(AppFile $file, User $user, string $providerKey, string $externalId): ?array
    {
        if (! filled($file->path)) {
            return null;
        }

        $url = $this->demoImageUrl($file);

        if (! $url) {
            return null;
        }

        return [
            'disk' => null,
            'path' => null,
            'url' => $url,
        ];
    }

    protected function demoImageUrl(?AppFile $file): ?string
    {
        if (! $file || ! filled($file->path)) {
            return null;
        }

        $sourceDisk = (string) ($file->disk ?: 'public');
        $sourcePath = trim((string) $file->path);

        if ($sourcePath === '' || ! Storage::disk($sourceDisk)->exists($sourcePath)) {
            return null;
        }

        return route('portal.files.preview', $file);
    }

    protected function isUsableImageFile(AppFile $file): bool
    {
        $disk = (string) ($file->disk ?: 'public');
        $path = trim((string) $file->path);
        $extension = strtolower((string) ($file->extension ?: pathinfo($path, PATHINFO_EXTENSION)));
        $mimeType = strtolower(trim((string) ($file->mime_type ?: '')));

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            return false;
        }

        if ($mimeType !== '' && ! in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'], true)) {
            return false;
        }

        if ($path === '' || ! Storage::disk($disk)->exists($path)) {
            return false;
        }

        try {
            $size = (int) Storage::disk($disk)->size($path);

            if ($size < 1024 || $size > (20 * 1024 * 1024)) {
                return false;
            }

            $content = Storage::disk($disk)->get($path);

            if ($content === '') {
                return false;
            }

            $dimensions = @getimagesizefromstring($content);

            if ($dimensions === false) {
                return false;
            }

            return (int) ($dimensions[0] ?? 0) >= 200 && (int) ($dimensions[1] ?? 0) >= 200;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function createDemoPublishingPost(
        User $user,
        Team $team,
        SocialAccount $account,
        ?AppFile $image,
        string $title,
        string $caption,
        int $status,
        CarbonInterface $timePost,
        CarbonInterface $changedAt,
        ?array $result,
        string $module,
        string $customData1,
        string $customData3,
    ): void {
        $type = $image ? 'media' : 'text';
        $data = [
            'title' => $title,
            'caption' => $caption,
            'media_type' => $image ? 'image' : 'text',
            'medias' => $image ? [$this->mediaItem($image, $user)] : [],
            'options' => [
                'schedule_mode' => 'specific_days_times',
                'repeat_rule' => 'none',
            ],
            'ai' => [
                'tags' => ['demo', 'preview', $module],
            ],
        ];

        PublishingPost::query()->create([
            'id_secure' => Str::random(32),
            'user_id' => $user->id,
            'team_id' => $team->id,
            'account_id' => $account->id,
            'social_network' => $account->provider_key,
            'category' => 'social_post',
            'module' => $module,
            'function' => 'post',
            'api_type' => 1,
            'type' => $type,
            'method' => 'basic',
            'data' => $data,
            'time_post' => $timePost->timestamp,
            'delay' => 0,
            'repost_frequency' => 0,
            'result' => $result,
            'custom_data_1' => $customData1,
            'custom_data_2' => self::DEMO_MARKER,
            'custom_data_3' => $customData3,
            'status' => $status,
            'changed' => $changedAt->timestamp,
            'created' => $timePost->copy()->subMinutes(20)->timestamp,
        ]);
    }

    protected function demoNetworkLabel(string $providerKey): string
    {
        return match ($providerKey) {
            'facebook' => 'Facebook',
            'tiktok' => 'TikTok',
            'x' => 'X',
            'linkedin_profile' => 'LinkedIn',
            'linkedin_page' => 'LinkedIn',
            'instagram' => 'Instagram',
            'instagram_unofficial' => 'Instagram',
            'pinterest' => 'Pinterest',
            'reddit' => 'Reddit',
            'threads' => 'Threads',
            'ok' => 'OK',
            'youtube' => 'YouTube',
            'google_business_profile' => 'Google Business',
            'telegram' => 'Telegram',
            'vk' => 'VK',
            'mastodon' => 'Mastodon',
            'discord' => 'Discord',
            default => Str::headline(str_replace('_', ' ', $providerKey)),
        };
    }

    protected function demoPublishedPostUrl(string $providerKey, string $slug): string
    {
        return match ($providerKey) {
            'facebook' => 'https://facebook.com/demo.brand.fb/posts/'.$slug,
            'tiktok' => 'https://www.tiktok.com/@demo.brand.tt/video/'.$slug,
            'x' => 'https://x.com/demo_brand_x/status/'.$slug,
            'linkedin_profile' => 'https://www.linkedin.com/feed/update/'.$slug,
            'linkedin_page' => 'https://www.linkedin.com/feed/update/'.$slug,
            'instagram' => 'https://instagram.com/p/'.$slug,
            'instagram_unofficial' => 'https://instagram.com/p/'.$slug,
            'pinterest' => 'https://www.pinterest.com/pin/'.$slug,
            'reddit' => 'https://www.reddit.com/r/demo/comments/'.$slug,
            'threads' => 'https://www.threads.com/@demo/post/'.$slug,
            'ok' => 'https://ok.ru/group/'.$slug,
            'youtube' => 'https://www.youtube.com/watch?v='.$slug,
            'google_business_profile' => 'https://www.google.com/maps/place/'.$slug,
            'telegram' => 'https://t.me/'.$slug,
            'vk' => 'https://vk.com/'.$slug,
            'mastodon' => 'https://mastodon.social/@demo/'.$slug,
            'discord' => 'https://discord.com/channels/'.$slug,
            default => 'https://example.com/demo/'.$slug,
        };
    }

    protected function demoProviderSlug(string $providerKey): string
    {
        return str_replace('-', '.', Str::slug($providerKey));
    }

    protected function demoProfileUrl(string $providerKey, string $handleBase = 'demo-brand', string $categorySlug = ''): string
    {
        $dotSuffix = $categorySlug !== '' ? '.'.$categorySlug : '';
        $dashSuffix = $categorySlug !== '' ? '-'.$categorySlug : '';
        $underscoreSuffix = $categorySlug !== '' ? '_'.$categorySlug : '';

        return match ($providerKey) {
            'facebook' => 'https://facebook.com/'.$handleBase.$dotSuffix,
            'tiktok' => 'https://www.tiktok.com/@'.$handleBase.$dotSuffix,
            'x' => 'https://x.com/'.$handleBase.$underscoreSuffix,
            'linkedin_profile' => 'https://www.linkedin.com/in/'.$handleBase.$dashSuffix,
            'linkedin_page' => 'https://www.linkedin.com/company/'.$handleBase.$dashSuffix,
            'instagram', 'instagram_unofficial' => 'https://instagram.com/'.$handleBase.$dotSuffix,
            'pinterest' => 'https://www.pinterest.com/'.$handleBase.$dotSuffix,
            'reddit' => 'https://www.reddit.com/user/'.$handleBase.$underscoreSuffix,
            'threads' => 'https://www.threads.net/@'.$handleBase.$dotSuffix,
            'ok' => 'https://ok.ru/group/'.$handleBase.$dashSuffix,
            'youtube' => 'https://www.youtube.com/@'.$handleBase.$dashSuffix,
            'google_business_profile' => 'https://www.google.com/maps/place/'.$handleBase.$dashSuffix,
            'telegram' => 'https://t.me/'.$handleBase.$underscoreSuffix,
            'vk' => 'https://vk.com/'.$handleBase.$underscoreSuffix,
            'mastodon' => 'https://mastodon.social/@'.$handleBase.$dashSuffix,
            'discord' => 'https://discord.gg/'.$handleBase.$dashSuffix,
            default => 'https://example.com/demo/'.$handleBase.$dotSuffix,
        };
    }

    protected function incrementPublishingStatusCount(array &$counts, int $status): void
    {
        match ($status) {
            PublishingPost::STATUS_PUBLISHED => $counts['published_posts']++,
            PublishingPost::STATUS_SCHEDULED => $counts['queued_posts']++,
            PublishingPost::STATUS_DRAFT => $counts['draft_posts']++,
            PublishingPost::STATUS_FAILED => $counts['failed_posts']++,
            PublishingPost::STATUS_PROCESSING => $counts['processing_posts']++,
            default => null,
        };
    }

    protected function uniqueUsername(string $email, string $name): string
    {
        $base = Str::slug(Str::before($email, '@'), '_');
        $base = $base !== '' ? str_replace('-', '_', $base) : str_replace('-', '_', Str::slug($name, '_'));
        $base = $base !== '' ? $base : 'preview_user';

        $username = $base;
        $counter = 2;

        while (User::query()->where('username', $username)->exists()) {
            $username = $base.'_'.$counter++;
        }

        return $username;
    }
}
