<?php

namespace Modules\CustomMLHUB\Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\CustomMLHUB\Support\DemoData\DemoArticleLibrary;
use Modules\CustomMLHUB\Support\DemoData\DemoContentCatalog;
use Modules\CustomMLHUB\Support\DemoData\DemoTimeline;
use Modules\CustomMLHUB\Support\DemoData\SafeTableWriter;

class MLHUBDemoBoardSeeder extends Seeder
{
    protected SafeTableWriter $writer;

    protected DemoTimeline $timeline;

    /** @var array<string, array<string, int>> */
    protected array $summary = [];

    public function run(): void
    {
        $this->writer = new SafeTableWriter;
        $this->timeline = new DemoTimeline;

        if (! $this->writer->hasTable('users') || ! $this->writer->hasTable('plans')) {
            return;
        }

        foreach (DemoContentCatalog::demoUsers() as $username => $profile) {
            $plan = DB::table('plans')->where('slug', $profile['plan'])->where('status', true)->first();

            if (! $plan) {
                continue;
            }

            $permissions = $this->planPermissions($plan);
            $profile = $this->boundedProfile($profile, $permissions);
            $userId = $this->ensureDemoUser($username, $profile, (int) $plan->id);

            $businesses = $this->seedBusinesses($userId, $username, $profile);
            $this->seedLocations($userId, $businesses);
            $campaigns = $this->seedCampaigns($userId, $username, $businesses, $profile);
            $this->seedLandingPages($userId, $username, $businesses, $campaigns, $profile);
            $customers = $this->seedCustomers($userId, $username, $businesses, $profile);
            $this->seedQrScans($userId, $campaigns, $profile);
            $this->seedConversions($userId, $username, $businesses, $campaigns, $customers, $profile);
            $this->seedCrm($userId, $username, $businesses, $customers, $permissions);
            $this->seedLoyaltyAndReferral($userId, $username, $businesses, $customers, $permissions);
            $this->seedAutomation($userId, $username, $businesses, $customers, $permissions);
            $this->seedGoogleMock($userId, $username, $businesses, $campaigns, $permissions);
            $this->seedCreditLogs($userId, (int) $plan->id, $profile);
            $this->forgetDashboardCache($userId);

            $this->summary[$username] = $this->countsForUser($userId);
        }

        $contentCounts = $this->seedPublicContent();
        $this->writeSummary($contentCounts);
    }

    protected function ensureDemoUser(string $username, array $profile, int $planId): int
    {
        $now = CarbonImmutable::now();
        $row = [
            'name' => $profile['name'],
            'username' => $username,
            'email' => $profile['email'],
            'email_verified_at' => $now,
            'password' => Hash::make('12345678'),
            'locale' => 'vi',
            'timezone' => (string) config('mlhub.timezone', 'Asia/Ho_Chi_Minh'),
            'is_super_admin' => false,
            'plan_id' => $planId,
            'next_plan_id' => null,
            'plan_started_at' => $now->subDays(19),
            'plan_expires_at' => $now->addDays(41),
            'created_at' => $now->subDays(59),
            'updated_at' => $now,
        ];

        $query = DB::table('users')->where('email', $profile['email']);

        if ($this->writer->hasColumn('users', 'username')) {
            $query->orWhere('username', $username);
        }

        $existing = $query->first();

        if ($existing) {
            $this->writer->updateWhere('users', ['id' => $existing->id], $row);

            return (int) $existing->id;
        }

        return (int) $this->writer->insert('users', $row);
    }

    /**
     * @return list<object>
     */
    protected function seedBusinesses(int $userId, string $username, array $profile): array
    {
        if (! $this->writer->hasTable('lb_businesses')) {
            return [];
        }

        $count = (int) $profile['businesses'];
        $pool = DemoContentCatalog::businessPool();
        $addresses = DemoContentCatalog::addresses();
        $selectedGroups = array_flip((array) $profile['industries']);
        $candidates = array_values(array_filter(
            $pool,
            fn (array $business): bool => isset($selectedGroups[$business['group']])
        ));

        if ($candidates === []) {
            $candidates = $pool;
        }

        for ($i = 0; $i < $count; $i++) {
            $source = $candidates[$i % count($candidates)];
            $branchDistricts = DemoContentCatalog::branchDistricts();
            $suffix = $count > count($candidates) ? ' — '.$branchDistricts[$i % count($branchDistricts)] : '';
            $businessName = $source['name'].$suffix;
            $resolved = $this->resolveIndustry($source);
            $createdAt = $this->timeline->at($i, $count);

            $this->writer->insert('lb_businesses', [
                'user_id' => $userId,
                'name' => $businessName,
                'type' => $resolved['legacy_type'],
                'industry_group_code' => $resolved['group_code'],
                'industry_category_code' => $resolved['category_code'],
                'industry_taxonomy_version' => $resolved['taxonomy_version'],
                'industry_metadata' => $resolved['metadata_snapshot'],
                'phone' => '090'.str_pad((string) (($userId + $i * 37) % 10000000), 7, '0', STR_PAD_LEFT),
                'email' => DemoContentCatalog::businessContactEmail($username, $i, $businessName),
                'website' => 'https://'.Str::slug($businessName).'.vn',
                'address' => $addresses[$i % count($addresses)],
                'google_maps_url' => 'https://maps.google.com/?q='.rawurlencode($source['name'].' Da Nang'),
                'social_links' => [
                    'facebook' => 'https://facebook.com/'.Str::slug($businessName),
                    'instagram' => 'https://instagram.com/'.Str::slug($businessName),
                ],
                'opening_hours' => $this->openingHours($i),
                'created_at' => $createdAt,
                'updated_at' => $createdAt->addDays(3),
            ]);
        }

        return DB::table('lb_businesses')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get()
            ->all();
    }

    protected function seedLocations(int $userId, array $businesses): void
    {
        if (! $this->writer->hasTable('lb_locations')) {
            return;
        }

        $addresses = DemoContentCatalog::addresses();

        foreach ($businesses as $index => $business) {
            $createdAt = $this->timeline->at($index + 2, max(1, count($businesses)));

            $this->writer->insert('lb_locations', [
                'user_id' => $userId,
                'business_id' => $business->id,
                'name' => $business->name.' - Điểm bán '.(($index % 3) + 1),
                'phone' => $business->phone ?? null,
                'email' => $business->email ?? null,
                'address' => $addresses[($index + 5) % count($addresses)],
                'google_maps_url' => 'https://maps.google.com/?q='.rawurlencode($business->name.' Da Nang'),
                'opening_hours' => $this->openingHours($index + 1),
                'is_active' => true,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->addDays(1),
            ]);
        }
    }

    /**
     * @return list<object>
     */
    protected function seedCampaigns(int $userId, string $username, array $businesses, array $profile): array
    {
        if (! $this->writer->hasTable('lb_campaigns') || $businesses === []) {
            return [];
        }

        $types = ['review', 'coupon', 'lead', 'feedback', 'booking', 'url'];
        $goals = [
            'review' => 'Xin đánh giá thật',
            'coupon' => 'Ưu đãi quay lại',
            'lead' => 'Thu khách tiềm năng',
            'feedback' => 'Thu phản hồi riêng tư',
            'booking' => 'Đặt lịch tư vấn',
            'url' => 'QR giới thiệu cơ sở',
        ];
        $count = (int) $profile['campaigns'];

        for ($i = 0; $i < $count; $i++) {
            $business = $businesses[$i % count($businesses)];
            $type = $types[$i % count($types)];
            $createdAt = $this->timeline->at($i + 4, $count);
            $strong = $i % 9 !== 2;

            $this->writer->insert('lb_campaigns', [
                'user_id' => $userId,
                'business_id' => $business->id,
                'slug' => "{$username}-{$type}-".($i + 1),
                'name' => $goals[$type].' - '.$business->name,
                'type' => $type,
                'status' => $i % 19 === 0 ? 'paused' : 'active',
                'destination_url' => 'https://'.($profile['domain_slug'] ?? $username).'.mlhub.vn/q/'.$type.'/'.($i + 1),
                'settings' => [
                    'demo' => true,
                    'mock' => true,
                    'performance' => $strong ? 'strong' : 'weak',
                    'offer' => $type === 'coupon' ? 'Giảm '.(7 + ($i % 19)).'% cho lần ghé tiếp theo' : null,
                    'positive_threshold' => 4,
                    'simulate_only' => true,
                ],
                'published_at' => $createdAt->addHours(2),
                'created_at' => $createdAt,
                'updated_at' => $createdAt->addDays(2),
            ]);
        }

        return DB::table('lb_campaigns')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get()
            ->all();
    }

    protected function seedLandingPages(int $userId, string $username, array $businesses, array $campaigns, array $profile): void
    {
        if (! $this->writer->hasTable('lb_landing_pages') || $businesses === [] || $campaigns === []) {
            return;
        }

        $count = (int) $profile['landing_pages'];

        for ($i = 0; $i < $count; $i++) {
            $business = $businesses[$i % count($businesses)];
            $campaign = $campaigns[$i % count($campaigns)];
            $createdAt = $this->timeline->at($i + 8, $count);
            $visits = 49 + (($i * 37 + $count) % 719);
            $conversions = max(3, (int) floor($visits * (0.07 + (($i % 5) * 0.014))));

            $this->writer->insert('lb_landing_pages', [
                'user_id' => $userId,
                'business_id' => $business->id,
                'campaign_id' => $campaign->id,
                'slug' => "{$username}-landing-".($i + 1),
                'title' => 'Trang ưu đãi '.$business->name.' #'.($i + 1),
                'type' => $campaign->type ?? 'lead',
                'template' => 'local_campaign',
                'status' => $i % 23 === 0 ? 'draft' : 'published',
                'content' => [
                    'headline' => 'Ưu đãi và chăm sóc khách hàng tại '.$business->name,
                    'description' => 'Trang ưu đãi gồm mã QR, form thu thông tin khách hàng và báo cáo chuyển đổi theo thời gian thực.',
                    'cta' => 'Để lại thông tin',
                ],
                'settings' => [
                    'demo' => true,
                    'mock' => true,
                    'primary_color' => ['#0f766e', '#dc2626', '#2563eb', '#9333ea'][$i % 4],
                ],
                'visits_count' => $visits,
                'conversions_count' => min($visits - 1, $conversions),
                'published_at' => $createdAt->addDay(),
                'created_at' => $createdAt,
                'updated_at' => $createdAt->addDays(2),
            ]);
        }
    }

    /**
     * @return list<object>
     */
    protected function seedCustomers(int $userId, string $username, array $businesses, array $profile): array
    {
        if (! $this->writer->hasTable('lb_customers') || $businesses === []) {
            return [];
        }

        $count = (int) $profile['customers'];
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $business = $businesses[$i % count($businesses)];
            $firstSeen = $this->timeline->at($i + 5, $count);
            $lastActivity = $this->timeline->at($i + 17, $count);
            $score = DemoContentCatalog::customerScore($i, $userId);
            $name = DemoContentCatalog::customerDisplayName($i, $userId);

            $rows[] = [
                'user_id' => $userId,
                'team_id' => $userId,
                'business_id' => $business->id,
                'name' => $name,
                'phone' => '09'.str_pad((string) (($userId * 19 + $i * 31) % 100000000), 8, '0', STR_PAD_LEFT),
                'email' => DemoContentCatalog::customerEmail($i, $userId, $name),
                'tags' => $this->customerTags($i),
                'note' => $i % 13 === 0 ? 'Khách cần chăm sóc lại trong đợt này.' : null,
                'metadata' => [
                    'demo' => true,
                    'preferred_channel' => ['phone', 'email', 'qr', 'walk_in'][$i % 4],
                    'source' => ['qr_scan', 'coupon', 'booking', 'lead_form', 'review'][$i % 5],
                ],
                'first_seen_at' => $firstSeen,
                'last_activity_at' => $lastActivity,
                'last_contacted_at' => $lastActivity->subDays($i % 9),
                'score' => $score,
                'lifetime_value' => DemoContentCatalog::customerLifetimeValue($i, $userId, $score),
                'created_at' => $firstSeen,
                'updated_at' => $lastActivity,
            ];
        }

        $this->writer->insertRows('lb_customers', $rows, 500);

        return DB::table('lb_customers')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get()
            ->all();
    }

    protected function seedQrScans(int $userId, array $campaigns, array $profile): void
    {
        if (! $this->writer->hasTable('lb_qr_scans') || $campaigns === []) {
            return;
        }

        $count = (int) $profile['scans'];
        $devices = ['mobile', 'desktop', 'tablet'];
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $campaign = $campaigns[$i % count($campaigns)];

            $rows[] = [
                'user_id' => $userId,
                'campaign_id' => $campaign->id,
                'ip_address' => '10.'.(($i % 223) + 1).'.'.(($i * 3) % 251).'.'.(($i * 7) % 251),
                'user_agent' => DemoContentCatalog::realisticUserAgent($i),
                'device' => $devices[$i % count($devices)],
                'country' => 'Vietnam',
                'city' => ['Đà Nẵng', 'Hội An', 'Tam Kỳ', 'Huế'][$i % 4],
                'created_at' => $this->timeline->at($i, $count),
            ];
        }

        $this->writer->insertRows('lb_qr_scans', $rows, 1000);
    }

    protected function seedConversions(int $userId, string $username, array $businesses, array $campaigns, array $customers, array $profile): void
    {
        if ($campaigns === [] || $customers === []) {
            return;
        }

        $this->seedReviewFeedbacks($userId, $campaigns, $customers, (int) $profile['customers']);
        $this->seedFeedbackResponses($userId, $campaigns, $customers, (int) $profile['customers']);
        $this->seedLeadSubmissions($userId, $campaigns, $customers, (int) $profile['customers']);
        $services = $this->seedBookingServices($userId, $businesses);
        $this->seedBookings($userId, $campaigns, $customers, $services, (int) $profile['customers']);
        $this->seedCouponRedemptions($userId, $username, $campaigns, $customers, (int) $profile['customers']);
    }

    protected function seedReviewFeedbacks(int $userId, array $campaigns, array $customers, int $customerCount): void
    {
        if (! $this->writer->hasTable('lb_review_feedbacks')) {
            return;
        }

        $reviewCampaigns = $this->campaignsByType($campaigns, 'review');
        $count = $this->odd(min(max(9, intdiv($customerCount, 9)), 389));
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $customer = $customers[$i % count($customers)];
            $campaign = $reviewCampaigns[$i % count($reviewCampaigns)];
            $rating = DemoContentCatalog::reviewRating($i, $userId);
            $createdAt = $this->timeline->at($i + 31, $count);

            $rows[] = [
                'user_id' => $userId,
                'campaign_id' => $campaign->id,
                'rating' => $rating,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'message' => $rating >= 4 ? 'Trải nghiệm tốt, tôi sẽ quay lại.' : 'Có điểm cần cải thiện trong quy trình phục vụ.',
                'status' => $rating >= 4 ? 'resolved' : 'new',
                'reply' => $rating >= 4 ? 'Cảm ơn anh/chị đã đánh giá thật cho cơ sở.' : null,
                'replied_at' => $rating >= 4 ? $createdAt->addDays(1) : null,
                'resolved_at' => $rating >= 4 ? $createdAt->addDays(2) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->addDays(2),
            ];
        }

        $this->writer->insertRows('lb_review_feedbacks', $rows);
    }

    protected function seedFeedbackResponses(int $userId, array $campaigns, array $customers, int $customerCount): void
    {
        if (! $this->writer->hasTable('lb_feedback_responses')) {
            return;
        }

        $feedbackCampaigns = $this->campaignsByType($campaigns, 'feedback');
        $count = $this->odd(min(max(7, intdiv($customerCount, 13)), 277));
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $customer = $customers[($i * 3) % count($customers)];
            $createdAt = $this->timeline->at($i + 41, $count);
            $rating = DemoContentCatalog::reviewRating($i + 3, $userId);

            $rows[] = [
                'user_id' => $userId,
                'campaign_id' => $feedbackCampaigns[$i % count($feedbackCampaigns)]->id,
                'rating' => $rating,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'message' => 'Phản hồi về tốc độ phục vụ và mong muốn ưu đãi cho lần sau.',
                'payload' => ['demo' => true, 'channel' => 'qr_feedback', 'rating' => $rating],
                'status' => $rating >= 4 ? 'reviewed' : 'open',
                'resolved_at' => $rating >= 4 ? $createdAt->addDays(1) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->addDays(1),
            ];
        }

        $this->writer->insertRows('lb_feedback_responses', $rows);
    }

    protected function seedLeadSubmissions(int $userId, array $campaigns, array $customers, int $customerCount): void
    {
        if (! $this->writer->hasTable('lb_lead_submissions')) {
            return;
        }

        $leadCampaigns = $this->campaignsByType($campaigns, 'lead');
        $count = $this->odd(min(max(11, intdiv($customerCount, 8)), 503));
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $customer = $customers[($i * 5) % count($customers)];
            $createdAt = $this->timeline->at($i + 53, $count);

            $rows[] = [
                'user_id' => $userId,
                'campaign_id' => $leadCampaigns[$i % count($leadCampaigns)]->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'message' => 'Tôi muốn nhận tư vấn và ưu đãi phù hợp.',
                'payload' => ['demo' => true, 'intent' => ['tu_van', 'uu_dai', 'dat_lich'][$i % 3]],
                'status' => ['new', 'contacted', 'qualified'][$i % 3],
                'created_at' => $createdAt,
                'updated_at' => $createdAt->addHours(6),
            ];
        }

        $this->writer->insertRows('lb_lead_submissions', $rows);
    }

    /**
     * @return list<object>
     */
    protected function seedBookingServices(int $userId, array $businesses): array
    {
        if (! $this->writer->hasTable('lb_booking_services')) {
            return [];
        }

        foreach ($businesses as $index => $business) {
            foreach (['Tư vấn nhanh', 'Gói dịch vụ chính'] as $serviceIndex => $name) {
                $this->writer->insert('lb_booking_services', [
                    'user_id' => $userId,
                    'business_id' => $business->id,
                    'name' => $name.' - '.$business->name,
                    'duration_minutes' => $serviceIndex === 0 ? 30 : 75,
                    'price' => $serviceIndex === 0 ? 79000 + ($index * 11000) : 219000 + ($index * 17000),
                    'description' => 'Dịch vụ mẫu cho trang đặt lịch MLHUB.',
                    'available_days' => [1, 2, 3, 4, 5, 6],
                    'time_slots' => ['08:30', '10:00', '14:15', '16:30'],
                    'max_bookings_per_slot' => 1 + ($index % 2),
                    'is_active' => true,
                    'use_business_hours' => true,
                    'slot_interval' => 30,
                    'buffer_before' => 5,
                    'buffer_after' => 10,
                    'service_hours' => null,
                    'created_at' => $this->timeline->at($index + $serviceIndex, max(1, count($businesses))),
                    'updated_at' => CarbonImmutable::now(),
                ]);
            }
        }

        return DB::table('lb_booking_services')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get()
            ->all();
    }

    protected function seedBookings(int $userId, array $campaigns, array $customers, array $services, int $customerCount): void
    {
        if (! $this->writer->hasTable('lb_bookings') || $services === []) {
            return;
        }

        $bookingCampaigns = $this->campaignsByType($campaigns, 'booking');
        $count = $this->odd(min(max(9, intdiv($customerCount, 10)), 421));
        $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $customer = $customers[($i * 7) % count($customers)];
            $createdAt = $this->timeline->at($i + 61, $count);

            $rows[] = [
                'user_id' => $userId,
                'campaign_id' => $bookingCampaigns[$i % count($bookingCampaigns)]->id,
                'service_id' => $services[$i % count($services)]->id,
                'status' => $statuses[$i % count($statuses)],
                'booking_date' => $i % 5 === 0 ? $this->timeline->future($i) : $createdAt->addDays(($i % 17) + 1),
                'booking_time' => ['08:30', '09:45', '14:15', '16:30'][$i % 4],
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'note' => 'Lượt đặt lịch sinh từ mã QR / landing page.',
                'created_at' => $createdAt,
                'updated_at' => $createdAt->addHours(4),
            ];
        }

        $this->writer->insertRows('lb_bookings', $rows);
    }

    protected function seedCouponRedemptions(int $userId, string $username, array $campaigns, array $customers, int $customerCount): void
    {
        if (! $this->writer->hasTable('lb_coupon_redemptions')) {
            return;
        }

        $couponCampaigns = $this->campaignsByType($campaigns, 'coupon');
        $count = $this->odd(min(max(13, intdiv($customerCount, 7)), 607));
        $statuses = ['claimed', 'used', 'expired'];
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $customer = $customers[($i * 11) % count($customers)];
            $createdAt = $this->timeline->at($i + 71, $count);
            $status = $statuses[$i % count($statuses)];

            $rows[] = [
                'user_id' => $userId,
                'campaign_id' => $couponCampaigns[$i % count($couponCampaigns)]->id,
                'code' => strtoupper(substr($username, 5, 4)).'-'.str_pad((string) ($i + 37), 5, '0', STR_PAD_LEFT),
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'status' => $status,
                'used_at' => $status === 'used' ? $createdAt->addDays(($i % 9) + 1) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->addDays(2),
            ];
        }

        $this->writer->insertRows('lb_coupon_redemptions', $rows);
    }

    protected function seedCrm(int $userId, string $username, array $businesses, array $customers, array $permissions): void
    {
        if (! ($permissions['advanced_crm'] ?? false) || $customers === []) {
            return;
        }

        $tagIds = $this->seedCrmTags($userId, $permissions);
        $this->seedCrmTagMaps($userId, $customers, $tagIds);
        $this->seedCrmSegments($userId, $businesses, $permissions);
        $this->seedCrmTasks($userId, $businesses, $customers, $permissions);
        $this->seedCrmNotes($userId, $businesses, $customers);
        $this->seedCrmActivities($userId, $businesses, $customers);
        $this->seedCrmScoreLogs($userId, $customers);
        $this->seedCrmAutomations($userId, $username, $businesses, $permissions);
    }

    /**
     * @return list<int>
     */
    protected function seedCrmTags(int $userId, array $permissions): array
    {
        if (! $this->writer->hasTable('lb_customer_tags')) {
            return [];
        }

        $limit = (int) ($permissions['customer_tags'] ?? 0);
        $names = ['VIP', 'Mới đến', 'Đã dùng coupon', 'Cần gọi lại', 'Khách quay lại', 'Review tốt', 'Cần xử lý', 'Đã đặt lịch', 'Lead nóng', 'Khách ngủ đông'];
        $count = min(max(0, $limit), count($names));

        for ($i = 0; $i < $count; $i++) {
            $this->writer->insert('lb_customer_tags', [
                'owner_user_id' => $userId,
                'name' => $names[$i],
                'slug' => DemoContentCatalog::slug($names[$i], 'demo-'.$userId),
                'color' => ['#0f766e', '#2563eb', '#dc2626', '#9333ea', '#ca8a04'][$i % 5],
                'description' => 'Thẻ phân loại khách hàng trong CRM.',
                'is_system' => false,
                'created_at' => CarbonImmutable::now()->subDays(43 - $i),
                'updated_at' => CarbonImmutable::now(),
            ]);
        }

        return DB::table('lb_customer_tags')
            ->where('owner_user_id', $userId)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    protected function seedCrmTagMaps(int $userId, array $customers, array $tagIds): void
    {
        if (! $this->writer->hasTable('lb_customer_tag_maps') || $tagIds === []) {
            return;
        }

        $rows = [];
        $max = min(count($customers), 1200);

        for ($i = 0; $i < $max; $i++) {
            $rows[] = [
                'owner_user_id' => $userId,
                'customer_id' => $customers[$i]->id,
                'tag_id' => $tagIds[$i % count($tagIds)],
                'created_by' => $userId,
                'created_at' => $this->timeline->at($i + 3, $max),
            ];
        }

        $this->writer->insertRows('lb_customer_tag_maps', $rows, 500);
    }

    protected function seedCrmSegments(int $userId, array $businesses, array $permissions): void
    {
        if (! $this->writer->hasTable('lb_customer_segments')) {
            return;
        }

        $limit = (int) ($permissions['customer_segments'] ?? 0);
        $count = min($limit, 9);
        $names = ['Khách VIP', 'Khách mới 30 ngày', 'Cần winback', 'Đã dùng coupon', 'Đặt lịch gần đây', 'Review thấp cần gọi', 'Lead nóng', 'Khách trung thành', 'Giá trị cao'];

        for ($i = 0; $i < $count; $i++) {
            $business = $businesses[$i % count($businesses)];

            $this->writer->insert('lb_customer_segments', [
                'owner_user_id' => $userId,
                'business_id' => $business->id,
                'name' => $names[$i],
                'description' => 'Phân khúc khách hàng cho '.$business->name,
                'filters' => ['demo' => true, 'score_min' => 20 + ($i * 7), 'recent_days' => [7, 30, 90][$i % 3]],
                'is_dynamic' => $i % 4 !== 0,
                'color' => ['#0f766e', '#2563eb', '#dc2626'][$i % 3],
                'created_by' => $userId,
                'created_at' => CarbonImmutable::now()->subDays(31 - $i),
                'updated_at' => CarbonImmutable::now(),
            ]);
        }
    }

    protected function seedCrmTasks(int $userId, array $businesses, array $customers, array $permissions): void
    {
        if (! $this->writer->hasTable('lb_customer_tasks')) {
            return;
        }

        $limit = (int) ($permissions['customer_tasks'] ?? 0);
        $count = min($limit, $this->odd(max(9, intdiv(count($customers), 9))));
        $rows = [];
        $statuses = ['open', 'completed', 'overdue'];

        for ($i = 0; $i < $count; $i++) {
            $customer = $customers[($i * 13) % count($customers)];
            $status = $statuses[$i % count($statuses)];
            $dueAt = $status === 'overdue' ? CarbonImmutable::now()->subDays(($i % 17) + 1) : $this->timeline->future($i);
            $createdAt = $this->timeline->at($i + 15, $count);

            $rows[] = [
                'owner_user_id' => $userId,
                'business_id' => $customer->business_id ?: $businesses[$i % count($businesses)]->id,
                'customer_id' => $customer->id,
                'assigned_to' => $userId,
                'title' => ['Gọi lại khách', 'Gửi ưu đãi', 'Xác nhận lịch', 'Hỏi thăm trải nghiệm'][$i % 4],
                'description' => 'Việc cần làm gắn với hành trình khách hàng.',
                'type' => ['follow_up', 'booking', 'coupon', 'review'][$i % 4],
                'priority' => ['low', 'medium', 'high'][$i % 3],
                'status' => $status,
                'due_at' => $dueAt,
                'completed_at' => $status === 'completed' ? $createdAt->addDays(2) : null,
                'created_by' => $userId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->addDays(2),
            ];
        }

        $this->writer->insertRows('lb_customer_tasks', $rows);
    }

    protected function seedCrmNotes(int $userId, array $businesses, array $customers): void
    {
        if (! $this->writer->hasTable('lb_customer_notes')) {
            return;
        }

        $count = min(count($customers), 317);
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $customer = $customers[($i * 17) % count($customers)];
            $createdAt = $this->timeline->at($i + 22, $count);

            $rows[] = [
                'owner_user_id' => $userId,
                'business_id' => $customer->business_id ?: $businesses[$i % count($businesses)]->id,
                'customer_id' => $customer->id,
                'user_id' => $userId,
                'note' => 'Ghi chú: khách quan tâm ưu đãi và cần chăm sóc lại đúng thời điểm.',
                'visibility' => 'team',
                'pinned' => $i % 19 === 0,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        $this->writer->insertRows('lb_customer_notes', $rows);
    }

    protected function seedCrmActivities(int $userId, array $businesses, array $customers): void
    {
        if (! $this->writer->hasTable('lb_customer_activities')) {
            return;
        }

        $count = min(max(17, count($customers) * 2), 1801);
        $types = ['qr_scan', 'lead_created', 'coupon_claimed', 'booking_created', 'feedback_received', 'review_received'];
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $customer = $customers[$i % count($customers)];
            $type = $types[$i % count($types)];
            $occurredAt = $this->timeline->at($i + 11, $count);

            $rows[] = [
                'owner_user_id' => $userId,
                'business_id' => $customer->business_id ?: $businesses[$i % count($businesses)]->id,
                'customer_id' => $customer->id,
                'type' => $type,
                'title' => $this->activityTitle($type),
                'description' => 'Hoạt động được sinh từ luồng QR / CRM của cơ sở.',
                'related_type' => 'demo',
                'related_id' => null,
                'source_module' => 'CustomMLHUB',
                'icon' => 'fa-light fa-chart-line',
                'color' => '#0f766e',
                'metadata' => ['demo' => true, 'simulated' => true],
                'created_by' => $userId,
                'occurred_at' => $occurredAt,
                'created_at' => $occurredAt,
                'updated_at' => $occurredAt,
            ];
        }

        $this->writer->insertRows('lb_customer_activities', $rows, 500);
    }

    protected function seedCrmScoreLogs(int $userId, array $customers): void
    {
        if (! $this->writer->hasTable('lb_customer_score_logs')) {
            return;
        }

        $count = min(count($customers), 1207);
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $customer = $customers[$i % count($customers)];
            $newScore = (int) ($customer->score ?? DemoContentCatalog::customerScore($i, $userId));
            $delta = 2 + (($i * 7 + $userId) % 23);
            $createdAt = $this->timeline->at($i + 18, $count);

            $rows[] = [
                'owner_user_id' => $userId,
                'customer_id' => $customer->id,
                'old_score' => max(0, min(100, $newScore - $delta)),
                'new_score' => $newScore,
                'reason' => ['qr_scan', 'coupon_used', 'review_rating', 'booking_completed'][$i % 4],
                'related_type' => 'demo',
                'related_id' => null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        $this->writer->insertRows('lb_customer_score_logs', $rows, 500);
    }

    protected function seedCrmAutomations(int $userId, string $username, array $businesses, array $permissions): void
    {
        if (! $this->writer->hasTable('lb_crm_automations') || ! ($permissions['crm_automations'] ?? 0)) {
            return;
        }

        $count = min((int) $permissions['crm_automations'], 7);

        for ($i = 0; $i < $count; $i++) {
            $business = $businesses[$i % count($businesses)];
            $automationId = $this->writer->insert('lb_crm_automations', [
                'owner_user_id' => $userId,
                'business_id' => $business->id,
                'name' => 'Tự động hóa CRM #'.($i + 1),
                'trigger_event' => ['customer.created', 'coupon.used', 'booking.completed'][$i % 3],
                'status' => $i % 5 === 0 ? 'inactive' : 'active',
                'condition_json' => ['demo' => true, 'score_min' => 30 + ($i * 5)],
                'action_json' => ['create_task' => true, 'add_tag' => true],
                'created_by' => $userId,
                'created_at' => CarbonImmutable::now()->subDays(40 - $i),
                'updated_at' => CarbonImmutable::now(),
            ]);

            if ($automationId && $this->writer->hasTable('lb_crm_automation_logs')) {
                $this->writer->insertRows('lb_crm_automation_logs', $this->automationLogRows(
                    'crm',
                    $userId,
                    (int) $automationId,
                    $business->id,
                    9 + ($i % 4),
                ));
            }
        }
    }

    protected function seedLoyaltyAndReferral(int $userId, string $username, array $businesses, array $customers, array $permissions): void
    {
        if (! ($permissions['loyalty_stamp_cards'] ?? false) || $businesses === [] || $customers === []) {
            return;
        }

        $cardIds = [];
        $cardLimit = max(0, (int) ($permissions['max_loyalty_cards'] ?? 0));
        $cardCount = min($cardLimit, max(1, min(count($businesses), 5)));

        if ($this->writer->hasTable('lb_loyalty_cards')) {
            for ($i = 0; $i < $cardCount; $i++) {
                $business = $businesses[$i % count($businesses)];
                $cardId = $this->writer->insert('lb_loyalty_cards', [
                    'user_id' => $userId,
                    'team_id' => null,
                    'business_id' => $business->id,
                    'slug' => "{$username}-loyalty-".($i + 1),
                    'name' => 'Thẻ tích điểm '.$business->name,
                    'required_stamps' => 7 + ($i % 5),
                    'stamp_method' => 'qr_scan',
                    'customer_identifier' => 'phone',
                    'reward_title' => 'Quà tặng cho lần ghé tiếp theo',
                    'reward_type' => 'coupon',
                    'reward_value' => (string) (9 + ($i % 13)).'%',
                    'expiry_days' => 45 + ($i % 31),
                    'stamp_cooldown_minutes' => 720,
                    'max_stamps_per_day' => 2,
                    'settings' => ['demo' => true, 'simulate_only' => true],
                    'status' => 'active',
                    'created_at' => CarbonImmutable::now()->subDays(80 - $i),
                    'updated_at' => CarbonImmutable::now(),
                ]);

                if ($cardId) {
                    $cardIds[] = (int) $cardId;
                }
            }
        }

        $this->seedLoyaltyCustomersAndStamps($cardIds, $customers);
        $this->seedReferrals($userId, $username, $businesses, $customers, $permissions);
    }

    protected function seedLoyaltyCustomersAndStamps(array $cardIds, array $customers): void
    {
        if ($cardIds === [] || $customers === []) {
            return;
        }

        $customerCount = count($customers);

        foreach ($cardIds as $cardIndex => $cardId) {
            $loyaltyRows = [];
            $stampRows = [];
            $limit = min($customerCount, 191 + ($cardIndex * 17));
            $offset = ($cardIndex * 13) % $customerCount;

            for ($i = 0; $i < $limit; $i++) {
                $customer = $customers[($offset + $i) % $customerCount];
                $stamps = 1 + (($i + $cardIndex) % 8);
                $lastStampAt = $this->timeline->at($i + 9, $limit);

                $loyaltyRows[] = [
                    'card_id' => $cardId,
                    'customer_id' => $customer->id,
                    'stamps_count' => $stamps,
                    'completed_count' => $stamps >= 7 ? (($i % 3) + 1) : 0,
                    'last_stamp_at' => $lastStampAt,
                    'created_at' => $lastStampAt->subDays(31),
                    'updated_at' => $lastStampAt,
                ];

                for ($j = 0; $j < min($stamps, 5); $j++) {
                    $stampRows[] = [
                        'card_id' => $cardId,
                        'customer_id' => $customer->id,
                        'source' => ['qr_scan', 'staff', 'coupon'][$j % 3],
                        'staff_id' => null,
                        'created_at' => $lastStampAt->subDays($j * 3),
                        'updated_at' => $lastStampAt->subDays($j * 3),
                    ];
                }
            }

            $this->writer->insertRows('lb_loyalty_customers', $loyaltyRows, 500);
            $this->writer->insertRows('lb_loyalty_stamps', $stampRows, 500);
        }
    }

    protected function seedReferrals(int $userId, string $username, array $businesses, array $customers, array $permissions): void
    {
        $limit = max(0, (int) ($permissions['max_referral_campaigns'] ?? 0));

        if (! $this->writer->hasTable('lb_referral_campaigns') || $limit <= 0) {
            return;
        }

        $campaignIds = [];

        for ($i = 0; $i < min($limit, 5); $i++) {
            $business = $businesses[$i % count($businesses)];
            $id = $this->writer->insert('lb_referral_campaigns', [
                'user_id' => $userId,
                'team_id' => null,
                'business_id' => $business->id,
                'slug' => "{$username}-referral-".($i + 1),
                'name' => 'Giới thiệu bạn bè - '.$business->name,
                'reward_title' => 'Ưu đãi giới thiệu',
                'reward_type' => 'coupon',
                'reward_value' => (string) (11 + $i).'%',
                'required_referrals' => 1 + ($i % 2),
                'target_action' => 'lead',
                'expiry_days' => 60,
                'expires_at' => CarbonImmutable::now()->addDays(90 + $i),
                'settings' => ['demo' => true, 'simulate_only' => true],
                'status' => 'active',
                'created_at' => CarbonImmutable::now()->subDays(53 - $i),
                'updated_at' => CarbonImmutable::now(),
            ]);

            if ($id) {
                $campaignIds[] = (int) $id;
            }
        }

        if ($campaignIds === [] || ! $this->writer->hasTable('lb_referral_links')) {
            return;
        }

        foreach ($campaignIds as $campaignIndex => $campaignId) {
            $linkRows = [];
            $customerCount = count($customers);
            $limit = min($customerCount, 73 + ($campaignIndex * 11));
            $offset = ($campaignIndex * 11) % $customerCount;

            for ($i = 0; $i < $limit; $i++) {
                $customer = $customers[($offset + $i) % $customerCount];
                $linkRows[] = [
                    'campaign_id' => $campaignId,
                    'customer_id' => $customer->id,
                    'code' => strtoupper(substr($username, 5, 5)).'R'.$campaignIndex.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                    'clicks_count' => 3 + (($i * 7) % 61),
                    'conversions_count' => $i % 4 === 0 ? 1 + ($i % 3) : 0,
                    'status' => $i % 23 === 0 ? 'paused' : 'active',
                    'created_at' => $this->timeline->at($i + 27, $limit),
                    'updated_at' => CarbonImmutable::now(),
                ];
            }

            $this->writer->insertRows('lb_referral_links', $linkRows, 500);
        }
    }

    protected function seedAutomation(int $userId, string $username, array $businesses, array $customers, array $permissions): void
    {
        if ($businesses === []) {
            return;
        }

        if ($permissions['email_automation'] ?? false) {
            $this->seedEmailAutomation($userId, $username, $businesses, $customers, $permissions);
        }

        if ($permissions['webhook_automation'] ?? false) {
            $this->seedWebhookAutomation($userId, $username, $businesses, $customers, $permissions);
        }

        if ($permissions['whatsapp_notification'] ?? false) {
            $this->seedWhatsAppAutomation($userId, $username, $businesses, $customers, $permissions);
        }
    }

    protected function seedEmailAutomation(int $userId, string $username, array $businesses, array $customers, array $permissions): void
    {
        $templateIds = [];

        if ($this->writer->hasTable('lb_email_templates')) {
            for ($i = 0; $i < min(5, max(1, (int) ($permissions['max_email_templates'] ?? 0))); $i++) {
                $business = $businesses[$i % count($businesses)];
                $id = $this->writer->insert('lb_email_templates', [
                    'user_id' => $userId,
                    'business_id' => $business->id,
                    'name' => 'Mẫu email #'.($i + 1).' - '.$business->name,
                    'type' => ['welcome', 'coupon', 'booking', 'review'][$i % 4],
                    'subject' => 'Cảm ơn anh/chị đã ghé '.$business->name,
                    'preheader' => 'Cảm ơn anh/chị đã ghé thăm cơ sở.',
                    'body' => 'Email cảm ơn khách hàng sau khi để lại thông tin qua mã QR.',
                    'language' => 'vi',
                    'is_system' => false,
                    'status' => 'active',
                    'created_at' => CarbonImmutable::now()->subDays(31 - $i),
                    'updated_at' => CarbonImmutable::now(),
                ]);

                if ($id) {
                    $templateIds[] = (int) $id;
                }
            }
        }

        $this->seedChannelAutomations(
            table: 'lb_email_automations',
            logTable: 'lb_email_automation_logs',
            userId: $userId,
            username: $username,
            businesses: $businesses,
            customers: $customers,
            limit: (int) ($permissions['max_email_automations'] ?? 0),
            templateIds: $templateIds,
            templateColumn: 'email_template_id',
            channel: 'email'
        );
    }

    protected function seedWebhookAutomation(int $userId, string $username, array $businesses, array $customers, array $permissions): void
    {
        $this->seedChannelAutomations(
            table: 'lb_webhook_automations',
            logTable: 'lb_webhook_automation_logs',
            userId: $userId,
            username: $username,
            businesses: $businesses,
            customers: $customers,
            limit: (int) ($permissions['max_webhook_automations'] ?? 0),
            templateIds: [],
            templateColumn: null,
            channel: 'webhook'
        );
    }

    protected function seedWhatsAppAutomation(int $userId, string $username, array $businesses, array $customers, array $permissions): void
    {
        $templateIds = [];

        if ($this->writer->hasTable('lb_whatsapp_templates')) {
            for ($i = 0; $i < min(5, max(1, (int) ($permissions['max_whatsapp_templates'] ?? 0))); $i++) {
                $business = $businesses[$i % count($businesses)];
                $id = $this->writer->insert('lb_whatsapp_templates', [
                    'user_id' => $userId,
                    'business_id' => $business->id,
                    'name' => 'Mẫu WhatsApp #'.($i + 1).' - '.$business->name,
                    'type' => ['coupon', 'booking', 'review'][$i % 3],
                    'template_name' => 'mlhub_demo_'.$username.'_'.$i,
                    'language' => 'vi',
                    'body' => 'Xin chào, cảm ơn anh/chị đã ghé thăm. Mình có ưu đãi dành riêng cho lần quay lại.',
                    'is_system' => false,
                    'status' => 'active',
                    'created_at' => CarbonImmutable::now()->subDays(21 - $i),
                    'updated_at' => CarbonImmutable::now(),
                ]);

                if ($id) {
                    $templateIds[] = (int) $id;
                }
            }
        }

        $this->seedChannelAutomations(
            table: 'lb_whatsapp_notifications',
            logTable: 'lb_whatsapp_notification_logs',
            userId: $userId,
            username: $username,
            businesses: $businesses,
            customers: $customers,
            limit: (int) ($permissions['max_whatsapp_notifications'] ?? 0),
            templateIds: $templateIds,
            templateColumn: 'whatsapp_template_id',
            channel: 'whatsapp'
        );
    }

    protected function seedChannelAutomations(
        string $table,
        string $logTable,
        int $userId,
        string $username,
        array $businesses,
        array $customers,
        int $limit,
        array $templateIds,
        ?string $templateColumn,
        string $channel,
    ): void {
        if (! $this->writer->hasTable($table) || $limit <= 0) {
            return;
        }

        $count = min($limit, 9);

        for ($i = 0; $i < $count; $i++) {
            $business = $businesses[$i % count($businesses)];
            $row = [
                'user_id' => $userId,
                'business_id' => $business->id,
                'name' => $this->channelLabel($channel).' tự động #'.($i + 1),
                'trigger_event' => ['lead.created', 'coupon.claimed', 'booking.completed', 'review.low'][$i % 4],
                'status' => $i % 5 === 0 ? 'inactive' : 'active',
                'delay_type' => $i % 3 === 0 ? 'delay' : 'immediate',
                'delay_value' => $i % 3 === 0 ? 37 + $i : 0,
                'delay_unit' => 'minutes',
                'condition_json' => ['demo' => true, 'min_score' => 20 + ($i * 3)],
                'action_json' => ['simulate_only' => true, 'channel' => $channel],
                'send_to' => 'customer',
                'custom_email' => null,
                'custom_phone' => null,
                'webhook_url' => 'https://example.invalid/mlhub-demo-webhook',
                'method' => 'POST',
                'headers_json' => ['X-MLHUB-Demo' => 'true'],
                'secret_token' => null,
                'retry_on_failure' => true,
                'phone_number_id' => null,
                'access_token' => null,
                'template_name' => $channel.'_demo_template',
                'template_language' => 'vi',
                'created_by' => $userId,
                'created_at' => CarbonImmutable::now()->subDays(27 - $i),
                'updated_at' => CarbonImmutable::now(),
            ];

            if ($templateColumn && $templateIds !== []) {
                $row[$templateColumn] = $templateIds[$i % count($templateIds)];
            }

            $automationId = $this->writer->insert($table, $row);

            if ($automationId && $this->writer->hasTable($logTable)) {
                $this->writer->insertRows(
                    $logTable,
                    $this->channelLogRows($channel, $userId, (int) $automationId, $business->id, $customers, 17 + ($i % 7), $templateIds, $templateColumn),
                    500
                );
            }
        }
    }

    protected function seedGoogleMock(int $userId, string $username, array $businesses, array $campaigns, array $permissions): void
    {
        if (! ($permissions['google_business'] ?? false) || $businesses === []) {
            return;
        }

        if (! $this->writer->hasTable('lb_google_business_connections') || ! $this->writer->hasTable('lb_google_business_locations')) {
            return;
        }

        $connectionLimit = max(0, (int) ($permissions['max_google_business_connections'] ?? 0));
        $locationLimit = max(0, (int) ($permissions['max_google_business_locations'] ?? 0));

        if ($connectionLimit <= 0 || $locationLimit <= 0) {
            return;
        }

        $connectionId = $this->writer->insert('lb_google_business_connections', [
            'team_id' => $userId,
            'user_id' => $userId,
            'google_account_email' => "simulated+{$username}@mlhub.vn",
            'access_token' => null,
            'refresh_token' => null,
            'expires_at' => null,
            'status' => 'simulated',
            'provider_status' => 'simulated',
            'scopes' => ['mock' => true, 'reviews' => true, 'posts' => true],
            'auto_sync' => false,
            'last_synced_at' => CarbonImmutable::now()->subHours(7),
            'created_at' => CarbonImmutable::now()->subDays(63),
            'updated_at' => CarbonImmutable::now(),
        ]);

        if (! $connectionId) {
            return;
        }

        $locations = [];
        $count = min($locationLimit, count($businesses));

        for ($i = 0; $i < $count; $i++) {
            $business = $businesses[$i % count($businesses)];
            $locationId = $this->writer->insert('lb_google_business_locations', [
                'team_id' => $userId,
                'connection_id' => $connectionId,
                'business_id' => $business->id,
                'google_account_id' => 'accounts/mock-'.$username,
                'google_location_id' => 'locations/mock-'.$username.'-'.$i,
                'name' => $business->name.' (Google Mock)',
                'address' => $business->address ?? null,
                'phone' => $business->phone ?? null,
                'website' => $business->website ?? null,
                'category' => $business->type ?? 'Local business',
                'review_url' => 'https://example.invalid/google-review/'.$username.'/'.$i,
                'opening_hours' => $this->openingHours($i),
                'sync_business_info' => true,
                'sync_hours' => true,
                'sync_reviews' => true,
                'sync_insights' => true,
                'auto_reply_enabled' => $i % 3 !== 0,
                'is_managed' => true,
                'managed_at' => CarbonImmutable::now()->subDays(21 - ($i % 11)),
                'status' => 'simulated',
                'provider_status' => 'simulated',
                'last_synced_at' => CarbonImmutable::now()->subHours(11),
                'last_reviews_synced_at' => CarbonImmutable::now()->subHours(9),
                'last_info_synced_at' => CarbonImmutable::now()->subHours(17),
                'last_hours_synced_at' => CarbonImmutable::now()->subHours(19),
                'created_at' => CarbonImmutable::now()->subDays(45 - ($i % 13)),
                'updated_at' => CarbonImmutable::now(),
            ]);

            if ($locationId) {
                $locations[] = (object) ['id' => (int) $locationId, 'business_id' => $business->id];
            }
        }

        $this->seedGoogleReviews($userId, $locations);
        $this->seedGooglePosts($userId, $locations, $businesses, $campaigns, $permissions);
    }

    protected function seedGoogleReviews(int $userId, array $locations): void
    {
        if (! $this->writer->hasTable('lb_google_reviews') || $locations === []) {
            return;
        }

        $rows = [];
        $count = min(583, max(23, count($locations) * 17));

        for ($i = 0; $i < $count; $i++) {
            $location = $locations[$i % count($locations)];
            $rating = DemoContentCatalog::reviewRating($i + 11, $userId);
            $createdAt = $this->timeline->at($i + 77, $count);

            $rows[] = [
                'team_id' => $userId,
                'google_business_location_id' => $location->id,
                'business_id' => $location->business_id,
                'google_review_id' => 'mock-review-'.$userId.'-'.$i,
                'reviewer_name' => DemoContentCatalog::customerDisplayName($i + 41, $userId),
                'rating' => $rating,
                'comment' => $rating >= 4 ? 'Dịch vụ ổn, tôi sẽ quay lại ủng hộ.' : 'Cần cải thiện thêm về trải nghiệm phục vụ.',
                'reply' => $i % 3 === 0 ? 'Cảm ơn anh/chị đã góp ý cho cơ sở.' : null,
                'reply_status' => $i % 3 === 0 ? 'replied' : 'none',
                'review_created_at' => $createdAt,
                'review_updated_at' => $createdAt->addDays(1),
                'last_synced_at' => CarbonImmutable::now()->subHours($i % 23),
                'replied_at' => $i % 3 === 0 ? $createdAt->addDays(2) : null,
                'metadata' => ['mock' => true, 'provider_status' => 'simulated'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt->addDays(2),
            ];
        }

        $this->writer->insertRows('lb_google_reviews', $rows, 500);
    }

    protected function seedGooglePosts(int $userId, array $locations, array $businesses, array $campaigns, array $permissions): void
    {
        if (! ($permissions['google_business_posts'] ?? false) || ! $this->writer->hasTable('lb_google_business_posts') || $locations === []) {
            return;
        }

        $rows = [];
        $count = min(197, max(11, count($locations) * 5));

        for ($i = 0; $i < $count; $i++) {
            $location = $locations[$i % count($locations)];
            $business = $businesses[$i % count($businesses)];
            $campaign = $campaigns !== [] ? $campaigns[$i % count($campaigns)] : null;
            $createdAt = $this->timeline->at($i + 83, $count);
            $status = ['published', 'scheduled', 'failed'][$i % 3];
            // Bài "scheduled" phải đặt lịch trong tương lai để cron publish-scheduled-posts
            // bỏ qua (không gọi API Google thật trên dữ liệu demo).
            $scheduledAt = $status === 'scheduled'
                ? CarbonImmutable::now()->addDays(3 + ($i % 21))
                : $createdAt->addDays(1);

            $rows[] = [
                'team_id' => $userId,
                'google_business_location_id' => $location->id,
                'business_id' => $business->id,
                'campaign_id' => $campaign?->id,
                'landing_page_id' => null,
                'google_post_id' => 'mock-post-'.$userId.'-'.$i,
                'google_post_name' => 'posts/mock-'.$userId.'-'.$i,
                'type' => ['standard', 'offer', 'event'][$i % 3],
                'title' => 'Bài đăng Google - '.$business->name,
                'summary' => 'Ưu đãi cuối tuần dành cho khách hàng quen — ghé thăm cơ sở để nhận quà tặng nhỏ.',
                'cta_type' => ['CALL', 'LEARN_MORE', 'BOOK'][$i % 3],
                'cta_url' => 'https://example.invalid/mlhub-google-post/'.$userId.'/'.$i,
                'coupon_code' => $i % 3 === 1 ? 'MOCK'.(100 + $i) : null,
                'terms' => 'Áp dụng trong tháng này, không cộng dồn với ưu đãi khác.',
                'start_at' => $createdAt,
                'end_at' => $createdAt->addDays(14 + ($i % 9)),
                'status' => $status,
                'search_url' => 'https://example.invalid/search',
                'error_message' => $status === 'failed' ? 'Simulated failure log, no API call.' : null,
                'scheduled_at' => $scheduledAt,
                'published_at' => $status === 'published' ? $createdAt->addDays(1) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->addDays(1),
            ];
        }

        $this->writer->insertRows('lb_google_business_posts', $rows, 500);
    }

    protected function seedCreditLogs(int $userId, int $planId, array $profile): void
    {
        if (! $this->writer->hasTable('credit_usage_logs')) {
            return;
        }

        $count = min(37, max(5, intdiv((int) $profile['customers'], 137)));
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $amount = [1, 2, 1, 3, 5][$i % 5];
            $createdAt = $this->timeline->at($i + 101, $count);

            $rows[] = [
                'user_id' => $userId,
                'plan_id' => $planId,
                'action_key' => ['ai_studio_generate_captions', 'ai_studio_review_reply', 'ai_studio_plan_calendar'][$i % 3],
                'feature' => 'MLHUB AI demo',
                'amount' => $amount,
                'unit_cost' => $amount,
                'quantity' => 1,
                'credits_before' => 1000 - ($i * $amount),
                'credits_after' => 1000 - (($i + 1) * $amount),
                'is_unlimited' => false,
                'metadata' => ['demo' => true, 'simulate_only' => true],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        $this->writer->insertRows('credit_usage_logs', $rows);
    }

    /**
     * @return array{blogs: int, faqs: int}
     */
    protected function seedPublicContent(): array
    {
        return [
            'blogs' => $this->seedBlogs(),
            'faqs' => $this->seedFaqs(),
        ];
    }

    protected function seedBlogs(): int
    {
        if (
            ! class_exists('Modules\\AdminBlogs\\Models\\Blog')
            || ! $this->writer->hasTable('blogs')
        ) {
            return 0;
        }

        $categories = $this->seedBlogCategories();
        $tags = $this->seedBlogTags();
        $titles = DemoContentCatalog::blogTitles();
        $now = CarbonImmutable::now();
        $count = 0;

        foreach ($titles as $i => $title) {
            $slug = DemoContentCatalog::slug($title, 'mlhub-demo');
            $publishedAt = $this->timeline->at($i + 121, count($titles))->timestamp;
            $excerpt = DemoArticleLibrary::excerpt($title, $i);
            $content = DemoArticleLibrary::article($title, $i);
            $row = [
                'id_secure' => substr(hash('sha256', 'blog-'.$slug), 0, 64),
                'blog_category_id' => $categories !== [] ? $categories[$i % count($categories)] : null,
                'title' => $title,
                'title_translations' => ['vi' => $title, 'en' => $title],
                'excerpt' => $excerpt,
                'excerpt_translations' => ['vi' => $excerpt, 'en' => $excerpt],
                'content' => $content,
                'content_translations' => ['vi' => $content, 'en' => $content],
                'slug' => $slug,
                'meta_title' => $title,
                'meta_description' => Str::limit($excerpt, 155, ''),
                'canonical_url' => null,
                'og_image' => null,
                'thumbnail' => null,
                'status' => true,
                'published_at' => $publishedAt,
                'changed' => $now->timestamp,
                'created' => $publishedAt,
            ];

            $existing = DB::table('blogs')->where('slug', $slug)->first();

            if ($existing) {
                $this->writer->updateWhere('blogs', ['id' => $existing->id], $row);
                $blogId = (int) $existing->id;
            } else {
                $blogId = (int) $this->writer->insert('blogs', $row);
            }

            if ($blogId && $tags !== [] && $this->writer->hasTable('blog_tag_maps')) {
                DB::table('blog_tag_maps')->where('blog_id', $blogId)->delete();
                $this->writer->insertRows('blog_tag_maps', [
                    ['blog_id' => $blogId, 'blog_tag_id' => $tags[$i % count($tags)]],
                    ['blog_id' => $blogId, 'blog_tag_id' => $tags[($i + 3) % count($tags)]],
                ]);
            }

            $count++;
        }

        return $count;
    }

    /**
     * @return list<int>
     */
    protected function seedBlogCategories(): array
    {
        if (! class_exists('Modules\\AdminBlogCategories\\Models\\BlogCategory') || ! $this->writer->hasTable('blog_categories')) {
            return [];
        }

        $names = [
            'MLHUB',
            'Chuyển đổi số',
            'Google Maps',
            'QR & Review',
            'CRM & Automation',
            'Hộ kinh doanh Đà Nẵng',
            'Theo ngành nghề',
            'Dữ liệu & AI',
        ];
        $ids = [];

        foreach ($names as $i => $name) {
            $slug = DemoContentCatalog::slug($name, 'mlhub-demo-cat');
            $row = [
                'id_secure' => substr(hash('sha256', 'cat-'.$slug), 0, 64),
                'name' => $name,
                'name_translations' => ['vi' => $name, 'en' => $name],
                'description' => 'Danh mục blog của MLHUB.',
                'description_translations' => ['vi' => 'Danh mục blog của MLHUB.', 'en' => 'MLHUB blog category.'],
                'slug' => $slug,
                'icon' => 'fa-light fa-folder',
                'color' => ['#0f766e', '#2563eb', '#dc2626', '#9333ea'][$i % 4],
                'status' => true,
                'sort_order' => $i + 1,
                'changed' => CarbonImmutable::now()->timestamp,
                'created' => CarbonImmutable::now()->subDays(90 - $i)->timestamp,
            ];
            $existing = DB::table('blog_categories')->where('slug', $slug)->first();
            $ids[] = $existing
                ? (int) tap($existing->id, fn ($id) => $this->writer->updateWhere('blog_categories', ['id' => $id], $row))
                : (int) $this->writer->insert('blog_categories', $row);
        }

        return $ids;
    }

    /**
     * @return list<int>
     */
    protected function seedBlogTags(): array
    {
        if (! class_exists('Modules\\AdminBlogTags\\Models\\BlogTag') || ! $this->writer->hasTable('blog_tags')) {
            return [];
        }

        $names = ['mã QR', 'khách hàng', 'review thật', 'CRM', 'coupon', 'loyalty', 'Google Business', 'AI', 'Đà Nẵng'];
        $ids = [];

        foreach ($names as $i => $name) {
            $slug = DemoContentCatalog::slug($name, 'mlhub-demo-tag');
            $row = [
                'id_secure' => substr(hash('sha256', 'tag-'.$slug), 0, 64),
                'name' => $name,
                'name_translations' => ['vi' => $name, 'en' => $name],
                'description' => 'Thẻ blog của MLHUB.',
                'description_translations' => ['vi' => 'Thẻ blog của MLHUB.', 'en' => 'MLHUB blog tag.'],
                'slug' => $slug,
                'status' => true,
                'changed' => CarbonImmutable::now()->timestamp,
                'created' => CarbonImmutable::now()->subDays(80 - $i)->timestamp,
            ];
            $existing = DB::table('blog_tags')->where('slug', $slug)->first();
            $ids[] = $existing
                ? (int) tap($existing->id, fn ($id) => $this->writer->updateWhere('blog_tags', ['id' => $id], $row))
                : (int) $this->writer->insert('blog_tags', $row);
        }

        return $ids;
    }

    protected function seedFaqs(): int
    {
        if (! class_exists('Modules\\AdminFaqs\\Models\\Faq') || ! $this->writer->hasTable('faqs')) {
            return 0;
        }

        $questions = DemoArticleLibrary::faqs();
        $count = 0;

        foreach ($questions as $i => $faq) {
            $question = $faq['q'];
            $slug = DemoContentCatalog::slug($question, 'mlhub-demo-faq');
            $answer = $faq['a'];
            $createdAt = $this->timeline->at($i + 151, count($questions))->timestamp;
            $row = [
                'id_secure' => substr(hash('sha256', 'faq-'.$slug), 0, 32),
                'slug' => $slug,
                'title' => $question,
                'title_translations' => ['vi' => $question, 'en' => $question],
                'content' => $answer,
                'content_translations' => ['vi' => $answer, 'en' => $answer],
                'status' => true,
                'changed' => CarbonImmutable::now()->timestamp,
                'created' => $createdAt,
            ];
            $existing = DB::table('faqs')->where('slug', $slug)->first();

            if ($existing) {
                $this->writer->updateWhere('faqs', ['id' => $existing->id], $row);
            } else {
                $this->writer->insert('faqs', $row);
            }

            $count++;
        }

        return $count;
    }

    protected function boundedProfile(array $profile, array $permissions): array
    {
        $profile['businesses'] = $this->boundedCount((int) $profile['businesses'], $permissions, 'max_businesses');
        $profile['campaigns'] = $this->boundedCount((int) $profile['campaigns'], $permissions, 'max_campaigns');
        $profile['landing_pages'] = $this->boundedCount((int) $profile['landing_pages'], $permissions, 'max_landing_pages');
        $profile['campaigns'] = $this->boundedCount((int) $profile['campaigns'], $permissions, 'max_qr_codes');

        return $profile;
    }

    protected function boundedCount(int $desired, array $permissions, string $key): int
    {
        $limit = (int) ($permissions[$key] ?? -1);

        if ($limit < 0) {
            return $desired;
        }

        return max(0, min($desired, $limit));
    }

    protected function planPermissions(object $plan): array
    {
        $permissions = $plan->permissions ?? [];

        if (is_string($permissions)) {
            $decoded = json_decode($permissions, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($permissions) ? $permissions : [];
    }

    protected function resolveIndustry(array $source): array
    {
        $catalog = 'Modules\\AppBusinessProfiles\\Support\\BusinessTypeCatalog';

        if (class_exists($catalog)) {
            $resolved = $catalog::resolveSelection($source['group'], $source['category']);

            if (is_array($resolved)) {
                return $resolved;
            }
        }

        return [
            'group_code' => $source['group'],
            'category_code' => $source['category'],
            'legacy_type' => $source['type'],
            'taxonomy_version' => 'demo',
            'metadata_snapshot' => ['demo' => true, 'group_code' => $source['group'], 'category_code' => $source['category']],
        ];
    }

    protected function campaignsByType(array $campaigns, string $type): array
    {
        $filtered = array_values(array_filter($campaigns, fn (object $campaign): bool => ($campaign->type ?? null) === $type));

        return $filtered !== [] ? $filtered : $campaigns;
    }

    protected function openingHours(int $seed): array
    {
        return [
            'monday' => ['open' => '08:00', 'close' => '21:00'],
            'tuesday' => ['open' => '08:00', 'close' => '21:00'],
            'wednesday' => ['open' => '08:00', 'close' => '21:00'],
            'thursday' => ['open' => '08:00', 'close' => '21:00'],
            'friday' => ['open' => '08:00', 'close' => '21:30'],
            'saturday' => ['open' => '08:30', 'close' => '22:00'],
            'sunday' => ['open' => $seed % 3 === 0 ? '09:00' : '08:30', 'close' => '20:30'],
        ];
    }

    protected function customerTags(int $index): array
    {
        $tags = [['khach_moi', 'khach_quay_lai', 'vip', 'can_cham_soc'][$index % 4]];

        if ($index % 7 === 0) {
            $tags[] = 'coupon';
        }

        if ($index % 11 === 0) {
            $tags[] = 'review';
        }

        return $tags;
    }

    protected function odd(int $value): int
    {
        return $value % 2 === 0 ? $value + 1 : $value;
    }

    protected function channelLabel(string $channel): string
    {
        return match ($channel) {
            'email' => 'Email',
            'webhook' => 'Webhook',
            'whatsapp' => 'WhatsApp',
            default => ucfirst($channel),
        };
    }

    protected function activityTitle(string $type): string
    {
        return match ($type) {
            'qr_scan' => 'Khách quét mã QR',
            'lead_created' => 'Khách để lại thông tin',
            'coupon_claimed' => 'Khách nhận mã ưu đãi',
            'booking_created' => 'Khách đặt lịch dịch vụ',
            'feedback_received' => 'Khách gửi phản hồi',
            'review_received' => 'Khách để lại đánh giá',
            default => 'Hoạt động khách hàng',
        };
    }

    protected function channelLogRows(
        string $channel,
        int $userId,
        int $automationId,
        int $businessId,
        array $customers,
        int $count,
        array $templateIds,
        ?string $templateColumn,
    ): array {
        $rows = [];
        $statuses = ['sent', 'failed', 'skipped', 'pending'];

        for ($i = 0; $i < $count; $i++) {
            $customer = $customers !== [] ? $customers[$i % count($customers)] : null;
            $status = $statuses[$i % count($statuses)];
            $createdAt = $this->timeline->at($i + 91, $count);
            $row = [
                'user_id' => $userId,
                'automation_id' => $automationId,
                'business_id' => $businessId,
                'customer_id' => $customer?->id,
                'trigger_event' => ['lead.created', 'coupon.claimed', 'booking.completed'][$i % 3],
                'related_type' => 'demo',
                'related_id' => null,
                'recipient_email' => $customer?->email ?: 'demo@mlhub.vn',
                'recipient_phone' => $customer?->phone ?: '0900000000',
                'recipient_name' => $customer?->name,
                'subject' => 'Thông báo tự động từ MLHUB',
                'body' => 'Gửi email cảm ơn sau khi khách để lại thông tin.',
                'message_type' => 'text',
                'template_name' => $channel.'_demo_template',
                'template_language' => 'vi',
                'action_type' => $channel,
                'webhook_url' => 'https://example.invalid/mlhub-demo-webhook',
                'method' => 'POST',
                'request_headers' => ['X-MLHUB-Demo' => 'true'],
                'request_payload' => ['demo' => true, 'customer_id' => $customer?->id],
                'response_status' => $status === 'failed' ? 503 : 200,
                'response_body' => ['simulated' => true, 'status' => $status],
                'status' => $status,
                'error_message' => $status === 'failed' ? 'Simulated failure, no real provider call.' : null,
                'queued_at' => $createdAt,
                'sent_at' => $status === 'sent' ? $createdAt->addMinutes(3) : null,
                'opened_at' => $status === 'sent' && $i % 3 === 0 ? $createdAt->addHours(5) : null,
                'clicked_at' => $status === 'sent' && $i % 7 === 0 ? $createdAt->addHours(9) : null,
                'provider_message_id' => null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->addMinutes(4),
            ];

            if ($templateColumn && $templateIds !== []) {
                $row[$templateColumn] = $templateIds[$i % count($templateIds)];
            }

            $rows[] = $row;
        }

        return $rows;
    }

    protected function automationLogRows(string $channel, int $userId, int $automationId, int $businessId, int $count): array
    {
        $rows = [];
        $statuses = ['success', 'failed', 'skipped', 'pending'];

        for ($i = 0; $i < $count; $i++) {
            $createdAt = $this->timeline->at($i + 97, $count);

            $rows[] = [
                'owner_user_id' => $userId,
                'automation_id' => $automationId,
                'business_id' => $businessId,
                'customer_id' => null,
                'trigger_event' => 'demo.'.$channel,
                'status' => $statuses[$i % count($statuses)],
                'message' => 'Tạo việc gọi lại khách sau khi nhận đánh giá thấp.',
                'metadata' => ['demo' => true, 'simulate_only' => true],
                'created_at' => $createdAt,
                'updated_at' => $createdAt->addMinutes(5),
            ];
        }

        return $rows;
    }

    protected function forgetDashboardCache(int $userId): void
    {
        $metrics = 'App\\Support\\Portal\\PortalGrowthDashboardMetrics';

        if (class_exists($metrics) && method_exists($metrics, 'forget')) {
            $metrics::forget($userId);

            return;
        }

        $guard = 'App\\Support\\Plans\\PlanLimitGuard';

        if (class_exists($guard) && method_exists($guard, 'forgetPlanUsageCache')) {
            $guard::forgetPlanUsageCache($userId);
        }
    }

    protected function countsForUser(int $userId): array
    {
        $count = fn (string $table, ?string $column = 'user_id'): int => $this->writer->hasTable($table) && $column !== null
            ? DB::table($table)->where($column, $userId)->count()
            : 0;

        $crm = $count('lb_customer_tags', 'owner_user_id')
            + $count('lb_customer_tag_maps', 'owner_user_id')
            + $count('lb_customer_segments', 'owner_user_id')
            + $count('lb_customer_tasks', 'owner_user_id')
            + $count('lb_customer_notes', 'owner_user_id')
            + $count('lb_customer_activities', 'owner_user_id')
            + $count('lb_customer_score_logs', 'owner_user_id')
            + $count('lb_crm_automations', 'owner_user_id')
            + $count('lb_crm_automation_logs', 'owner_user_id');

        $automation = $count('lb_email_automations')
            + $count('lb_email_automation_logs')
            + $count('lb_webhook_automations')
            + $count('lb_webhook_automation_logs')
            + $count('lb_whatsapp_notifications')
            + $count('lb_whatsapp_notification_logs');

        $google = $count('lb_google_business_connections', 'team_id')
            + $count('lb_google_business_locations', 'team_id')
            + $count('lb_google_reviews', 'team_id')
            + $count('lb_google_business_posts', 'team_id');

        $conversions = $count('lb_review_feedbacks')
            + $count('lb_bookings')
            + $count('lb_coupon_redemptions')
            + $count('lb_feedback_responses')
            + $count('lb_lead_submissions');

        return [
            'businesses' => $count('lb_businesses'),
            'campaigns' => $count('lb_campaigns'),
            'scans' => $count('lb_qr_scans'),
            'customers' => $count('lb_customers'),
            'conversions' => $conversions,
            'crm' => $crm,
            'automation' => $automation,
            'google_mock' => $google,
        ];
    }

    protected function writeSummary(array $contentCounts): void
    {
        if (! $this->command) {
            return;
        }

        $this->command->info('Đã seed dữ liệu demo MLHUB cho 5 tài khoản.');

        foreach ($this->summary as $username => $counts) {
            $this->command->line(sprintf(
                '%s: businesses=%d campaigns=%d scans=%d customers=%d conversions=%d CRM=%d automation=%d google_mock=%d blog=%d FAQ=%d',
                $username,
                $counts['businesses'],
                $counts['campaigns'],
                $counts['scans'],
                $counts['customers'],
                $counts['conversions'],
                $counts['crm'],
                $counts['automation'],
                $counts['google_mock'],
                $contentCounts['blogs'],
                $contentCounts['faqs'],
            ));
        }

        $this->command->line('blogs='.$contentCounts['blogs'].' faqs='.$contentCounts['faqs']);
        $this->command->line('Mọi kênh gửi đi đều mô phỏng; không gọi email, webhook, WhatsApp hay Google API thật.');
    }
}
