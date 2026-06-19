<?php

namespace Modules\CustomMLHUB\Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\CustomMLHUB\Support\DemoData\DemoContentCatalog;
use Modules\CustomMLHUB\Support\DemoData\DemoTimeline;
use Modules\CustomMLHUB\Support\DemoData\SafeTableWriter;

/**
 * Lấp dữ liệu demo cho các bảng còn trống (account/portal/CRM/admin), bổ sung cho
 * MLHUBDemoBoardSeeder. Toàn bộ là dữ liệu mô phỏng, tenant-scoped, không gọi API thật.
 *
 * Chạy SAU MLHUBDemoBoardSeeder (chỉ ở chế độ install) để tái dùng businesses/customers/
 * campaigns đã tạo. Có guard hasTable + đếm theo owner để hạn chế nhân đôi khi chạy lại.
 */
class MLHUBDemoExtrasSeeder extends Seeder
{
    protected SafeTableWriter $writer;

    protected DemoTimeline $timeline;

    /** @var array<int, int|null> Cache team id thật theo user để tránh tạo trùng. */
    protected array $teamIdCache = [];

    public function run(): void
    {
        $this->writer = new SafeTableWriter;
        $this->timeline = new DemoTimeline;

        if (! $this->writer->hasTable('users') || ! $this->writer->hasTable('plans')) {
            return;
        }

        $this->seedAdminRoles();
        $this->seedSupportTaxonomy();
        $this->seedCoupons();
        $this->seedCreditPacks();
        $this->seedBlogRssSources();
        $broadcastIds = $this->seedBroadcastNotifications();

        foreach (DemoContentCatalog::demoUsers() as $username => $profile) {
            $user = DB::table('users')->where('email', $profile['email'])->first();

            if (! $user) {
                continue;
            }

            $userId = (int) $user->id;
            $planId = (int) ($user->plan_id ?? 0);
            $businesses = $this->ownedRows('lb_businesses', 'user_id', $userId);
            $customers = $this->ownedRows('lb_customers', 'user_id', $userId, 1500);
            $campaigns = $this->ownedRows('lb_campaigns', 'user_id', $userId);
            $isPartner = $username === 'mlhubpartner';
            $isPaid = $profile['plan'] !== 'mlhub-free-da-nang';

            $this->seedBroadcastStates($userId, $broadcastIds);
            $this->seedNotifications($userId);
            $this->seedFiles($userId, $businesses);
            $this->seedPayments($userId, $planId, $isPaid);
            $this->seedCreditTopups($userId, $isPaid);
            $this->seedAiStudio($userId, $businesses);
            $this->seedPublishingPrompts($userId);
            $this->seedMarketingTemplates($userId, $businesses);
            $this->seedGoogleAutoReply($userId, $businesses);
            $this->seedSupportTickets($userId);
            $this->seedTeamWorkspace($userId, $username, $isPaid);
            $this->seedCustomDomains($userId, $username, $isPaid);
            $this->seedAuditLogs($userId);
            $this->seedLoyaltyReferralExtras($userId, $username, $customers);
            $this->seedCrmExtras($userId, $customers);

            if ($isPartner) {
                $this->seedAffiliate($userId);
            }

            $this->seedPublishingMock($userId, $username, $businesses, $isPaid);
        }

        if ($this->command) {
            $this->command->info('Đã seed dữ liệu demo bổ sung (account/portal/CRM/admin).');
        }
    }

    /* ------------------------------------------------------------------ */
    /* Dữ liệu admin / hệ thống dùng chung                                 */
    /* ------------------------------------------------------------------ */

    protected function seedAdminRoles(): void
    {
        if (! $this->writer->hasTable('admin_roles')) {
            return;
        }

        foreach (DemoContentCatalog::adminRoles() as $i => $role) {
            if (DB::table('admin_roles')->where('slug', $role['slug'])->exists()) {
                continue;
            }

            $this->writer->insert('admin_roles', [
                'name' => $role['name'],
                'slug' => $role['slug'],
                'description' => $role['description'],
                'permissions' => $role['permissions'],
                'created_at' => CarbonImmutable::now()->subDays(120 - $i),
                'updated_at' => CarbonImmutable::now(),
            ]);
        }
    }

    protected function seedSupportTaxonomy(): void
    {
        $now = CarbonImmutable::now()->timestamp;

        if ($this->writer->hasTable('support_categories')) {
            foreach (DemoContentCatalog::supportCategories() as $i => $name) {
                if (DB::table('support_categories')->where('name', $name)->exists()) {
                    continue;
                }
                $this->writer->insert('support_categories', [
                    'id_secure' => $this->secure('support-cat-'.$name),
                    'name' => $name,
                    'icon' => 'fa-light fa-headset',
                    'color' => ['#2563eb', '#0f766e', '#dc2626', '#9333ea'][$i % 4],
                    'status' => true,
                    'changed' => $now,
                    'created' => CarbonImmutable::now()->subDays(110 - $i)->timestamp,
                ]);
            }
        }

        if ($this->writer->hasTable('support_types')) {
            foreach (DemoContentCatalog::supportTypes() as $i => $name) {
                if (DB::table('support_types')->where('name', $name)->exists()) {
                    continue;
                }
                $this->writer->insert('support_types', [
                    'id_secure' => $this->secure('support-type-'.$name),
                    'name' => $name,
                    'icon' => 'fa-light fa-tag',
                    'color' => ['#2563eb', '#0f766e', '#dc2626', '#9333ea', '#ca8a04'][$i % 5],
                    'status' => true,
                    'changed' => $now,
                    'created' => CarbonImmutable::now()->subDays(108 - $i)->timestamp,
                ]);
            }
        }

        if ($this->writer->hasTable('support_labels')) {
            foreach (DemoContentCatalog::supportLabels() as $i => $name) {
                if (DB::table('support_labels')->where('name', $name)->exists()) {
                    continue;
                }
                $this->writer->insert('support_labels', [
                    'id_secure' => $this->secure('support-label-'.$name),
                    'name' => $name,
                    'icon' => 'fa-light fa-bookmark',
                    'color' => ['#dc2626', '#ca8a04', '#2563eb', '#475569', '#0f766e'][$i % 5],
                    'status' => true,
                    'changed' => $now,
                    'created' => CarbonImmutable::now()->subDays(106 - $i)->timestamp,
                ]);
            }
        }
    }

    protected function seedCoupons(): void
    {
        if (! $this->writer->hasTable('coupons')) {
            return;
        }

        foreach (DemoContentCatalog::coupons() as $i => $coupon) {
            if (DB::table('coupons')->where('code', $coupon['code'])->exists()) {
                continue;
            }

            $this->writer->insert('coupons', [
                'id_secure' => $this->secure('coupon-'.$coupon['code'], 32),
                'name' => $coupon['name'],
                'code' => $coupon['code'],
                'type' => $coupon['type'],
                'discount' => $coupon['discount'],
                'start_date' => CarbonImmutable::now()->subDays(30)->timestamp,
                'end_date' => CarbonImmutable::now()->addDays(120 + $i * 30)->timestamp,
                'plans' => $coupon['plans'],
                'usage_limit' => 200,
                'usage_count' => 7 + ($i * 11),
                'status' => true,
                'changed' => CarbonImmutable::now()->timestamp,
                'created' => CarbonImmutable::now()->subDays(60 - $i)->timestamp,
            ]);
        }
    }

    protected function seedCreditPacks(): void
    {
        if (! $this->writer->hasTable('credit_packs')) {
            return;
        }

        foreach (DemoContentCatalog::creditPacks() as $i => $pack) {
            if (DB::table('credit_packs')->where('slug', $pack['slug'])->exists()) {
                continue;
            }

            $this->writer->insert('credit_packs', [
                'name' => $pack['name'],
                'slug' => $pack['slug'],
                'description' => 'Gói tín dụng dùng cho các tính năng AI của MLHUB.',
                'credits' => $pack['credits'],
                'price' => $pack['price'],
                'currency' => 'VND',
                'currency_symbol' => '₫',
                'status' => true,
                'featured' => $pack['featured'],
                'sort' => ($i + 1) * 10,
                'created_at' => CarbonImmutable::now()->subDays(90 - $i),
                'updated_at' => CarbonImmutable::now(),
            ]);
        }
    }

    protected function seedBlogRssSources(): void
    {
        if (! $this->writer->hasTable('blog_rss_sources')) {
            return;
        }

        $categoryId = $this->writer->hasTable('blog_categories')
            ? DB::table('blog_categories')->orderBy('id')->value('id')
            : null;

        $sources = [
            ['name' => 'Tin tăng trưởng địa phương', 'feed_url' => 'https://demo.mlhub.vn/rss/tang-truong-dia-phuong'],
            ['name' => 'Kiến thức marketing hộ kinh doanh', 'feed_url' => 'https://demo.mlhub.vn/rss/marketing-ho-kinh-doanh'],
        ];

        foreach ($sources as $i => $source) {
            if (DB::table('blog_rss_sources')->where('name', $source['name'])->exists()) {
                continue;
            }

            $sourceId = $this->writer->insert('blog_rss_sources', [
                'id_secure' => $this->secure('rss-source-'.$source['name']),
                'name' => $source['name'],
                'feed_url' => $source['feed_url'],
                'blog_category_id' => $categoryId ? (int) $categoryId : null,
                'tag_ids' => [],
                'status' => true,
                'auto_publish' => false,
                'ai_improve' => false,
                'ai_auto_translate' => false,
                'ai_prompt' => null,
                'sync_interval_minutes' => 120,
                'max_items_per_run' => 5,
                'last_checked_at' => CarbonImmutable::now()->subHours(6)->timestamp,
                'last_imported_at' => CarbonImmutable::now()->subDays(2)->timestamp,
                'changed' => CarbonImmutable::now()->timestamp,
                'created' => CarbonImmutable::now()->subDays(60 - $i)->timestamp,
            ]);

            if ($sourceId && $this->writer->hasTable('blog_rss_imports')) {
                $blogId = $this->writer->hasTable('blogs') ? DB::table('blogs')->orderBy('id')->value('id') : null;
                $rows = [];
                for ($j = 0; $j < 4; $j++) {
                    $importedAt = CarbonImmutable::now()->subDays(($i * 7) + $j + 1);
                    $rows[] = [
                        'blog_rss_source_id' => (int) $sourceId,
                        'blog_id' => $blogId ? (int) $blogId : null,
                        'external_guid' => 'demo-guid-'.$sourceId.'-'.$j,
                        'external_url' => $source['feed_url'].'/item-'.$j,
                        'content_hash' => $this->secure('rss-import-'.$sourceId.'-'.$j),
                        'title' => 'Bài nhập RSS demo #'.($j + 1),
                        'source_published_at' => $importedAt->timestamp,
                        'changed' => $importedAt->timestamp,
                        'created' => $importedAt->timestamp,
                    ];
                }
                $this->writer->insertRows('blog_rss_imports', $rows);
            }
        }
    }

    protected function seedPublishingPrompts(int $userId): void
    {
        if ($this->writer->hasTable('ai_publishing_prompts') && $this->countOwned('ai_publishing_prompts', 'owner_user_id', $userId) === 0) {
            $prompts = [
                'Viết bài đăng quảng bá ưu đãi cuối tuần cho cơ sở kinh doanh tại Đà Nẵng, giọng văn thân thiện.',
                'Soạn nội dung giới thiệu dịch vụ mới kèm lời kêu gọi đặt lịch.',
                'Tạo bài đăng cảm ơn khách hàng và mời để lại đánh giá thật.',
            ];
            foreach ($prompts as $i => $text) {
                $this->writer->insert('ai_publishing_prompts', [
                    'owner_user_id' => $userId,
                    'title' => 'Mẫu nội dung đăng bài #'.($i + 1),
                    'prompt_text' => $text,
                    'metadata' => ['demo' => true],
                    'is_active' => true,
                    'created_at' => CarbonImmutable::now()->subDays(30 - $i),
                    'updated_at' => CarbonImmutable::now(),
                ]);
            }
        }

        if ($this->writer->hasTable('ai_publishing_runs') && $this->countOwned('ai_publishing_runs', 'owner_user_id', $userId) === 0) {
            for ($i = 0; $i < 3; $i++) {
                $createdAt = $this->timeline->at($i + 18, 3);
                $this->writer->insert('ai_publishing_runs', [
                    'owner_user_id' => $userId,
                    'team_id' => null,
                    'workspace_owner_user_id' => $userId,
                    'name' => 'Đợt đăng nội dung tự động #'.($i + 1),
                    'campaign_id' => null,
                    'label_ids' => [],
                    'account_ids' => [],
                    'prompt_ids' => [],
                    'schedule_config' => ['demo' => true, 'weekdays' => [1, 3, 5]],
                    'generation_config' => ['demo' => true, 'tone' => 'thân thiện'],
                    'stats' => ['generated' => 9 + $i, 'published' => 6 + $i],
                    'status' => $i === 0 ? 'completed' : 'draft',
                    'last_processed_at' => $i === 0 ? $createdAt->addDays(1) : null,
                    'created_at' => $createdAt,
                    'updated_at' => CarbonImmutable::now(),
                ]);
            }
        }
    }

    /**
     * @return list<int>
     */
    protected function seedBroadcastNotifications(): array
    {
        if (! $this->writer->hasTable('notification_manual')) {
            return [];
        }

        $ids = [];

        foreach (DemoContentCatalog::broadcastNotifications() as $i => $item) {
            $existing = DB::table('notification_manual')->where('title', $item['title'])->first();

            if ($existing) {
                $ids[] = (int) $existing->id;

                continue;
            }

            $id = $this->writer->insert('notification_manual', [
                'id_secure' => $this->secure('broadcast-'.$item['title'], 32),
                'title' => $item['title'],
                'message' => $item['message'],
                'url' => null,
                'type' => $item['type'],
                'is_global' => true,
                'created_at' => CarbonImmutable::now()->subDays(45 - $i * 5),
                'updated_at' => CarbonImmutable::now(),
            ]);

            if ($id) {
                $ids[] = (int) $id;
            }
        }

        return $ids;
    }

    /**
     * @param  list<int>  $broadcastIds
     */
    protected function seedBroadcastStates(int $userId, array $broadcastIds): void
    {
        if ($broadcastIds === [] || ! $this->writer->hasTable('notification_manual_states')) {
            return;
        }

        foreach ($broadcastIds as $i => $manualId) {
            $exists = DB::table('notification_manual_states')
                ->where('notification_manual_id', $manualId)
                ->where('user_id', $userId)
                ->exists();

            if ($exists) {
                continue;
            }

            $read = $i % 2 === 0;
            $this->writer->insert('notification_manual_states', [
                'notification_manual_id' => $manualId,
                'user_id' => $userId,
                'read_at' => $read ? CarbonImmutable::now()->subDays($i + 1) : null,
                'archived_at' => null,
                'created_at' => CarbonImmutable::now()->subDays(10 + $i),
                'updated_at' => CarbonImmutable::now(),
            ]);
        }
    }

    /* ------------------------------------------------------------------ */
    /* Dữ liệu theo từng user                                              */
    /* ------------------------------------------------------------------ */

    protected function seedNotifications(int $userId): void
    {
        if (! $this->writer->hasTable('notifications') || $this->countOwned('notifications', 'user_id', $userId) > 0) {
            return;
        }

        $rows = [];
        $samples = DemoContentCatalog::notificationSamples();
        $count = 16;

        for ($i = 0; $i < $count; $i++) {
            $sample = $samples[$i % count($samples)];
            $createdAt = $this->timeline->at($i + 3, $count);
            $rows[] = [
                'id_secure' => $this->secure('notif-'.$userId.'-'.$i, 32),
                'user_id' => $userId,
                'source' => 'auto',
                'mid' => null,
                'type' => $sample['type'],
                'title' => $sample['title'],
                'message' => $sample['message'],
                'url' => null,
                'read_at' => $i % 3 === 0 ? $createdAt->addHours(5) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        $this->writer->insertRows('notifications', $rows);
    }

    /**
     * @param  list<object>  $businesses
     */
    protected function seedFiles(int $userId, array $businesses): void
    {
        if (! $this->writer->hasTable('files') || $this->countOwned('files', 'owner_user_id', $userId) > 0) {
            return;
        }

        $folderId = $this->writer->insert('files', [
            'id_secure' => $this->secure('file-folder-'.$userId),
            'owner_user_id' => $userId,
            'team_id' => null,
            'parent_id' => null,
            'disk' => 'public',
            'name' => 'Thư mục marketing',
            'path' => null,
            'category' => 'folder',
            'size_bytes' => 0,
            'is_folder' => true,
            'is_image' => false,
            'note' => 'Thư mục chứa hình ảnh và tài liệu marketing demo.',
            'created_at' => CarbonImmutable::now()->subDays(120),
            'updated_at' => CarbonImmutable::now(),
        ]);

        $rows = [];
        $names = ['logo-co-so', 'banner-uu-dai', 'menu-mua-he', 'anh-bia-google', 'qr-review', 'poster-khai-truong', 'hinh-san-pham', 'anh-khong-gian'];
        $count = max(8, min(count($businesses) * 3, 36));

        for ($i = 0; $i < $count; $i++) {
            $createdAt = $this->timeline->at($i + 6, $count);
            $name = $names[$i % count($names)].'-'.($i + 1).'.jpg';
            $rows[] = [
                'id_secure' => $this->secure('file-'.$userId.'-'.$i),
                'owner_user_id' => $userId,
                'team_id' => null,
                'parent_id' => $folderId ?: null,
                'disk' => 'public',
                'name' => $name,
                'path' => 'demo/'.$userId.'/'.$name,
                'mime_type' => 'image/jpeg',
                'extension' => 'jpg',
                'category' => 'image',
                'size_bytes' => 120000 + (($i * 53717) % 2400000),
                'is_folder' => false,
                'is_image' => true,
                'width' => 1280,
                'height' => 720,
                'note' => 'Tệp hình ảnh demo (mô phỏng, không có nội dung thật).',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        $this->writer->insertRows('files', $rows);
    }

    protected function seedPayments(int $userId, int $planId, bool $isPaid): void
    {
        if (! $isPaid || $planId <= 0) {
            return;
        }

        $plan = DB::table('plans')->where('id', $planId)->first();
        $price = (float) ($plan->price ?? 199000);
        $months = 8;

        if ($this->writer->hasTable('payment_history') && $this->countOwned('payment_history', 'uid', $userId) === 0) {
            $rows = [];
            for ($i = 0; $i < $months; $i++) {
                $paidAt = CarbonImmutable::now()->subMonths($months - $i)->timestamp;
                $rows[] = [
                    'id_secure' => $this->secure('ph-'.$userId.'-'.$i),
                    'uid' => $userId,
                    'plan_id' => $planId,
                    'from' => 'demo',
                    'transaction_id' => 'MLHUB-DEMO-'.$userId.'-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                    'currency' => 'VND',
                    'by' => 'manual',
                    'amount' => $price,
                    'status' => 1,
                    'meta' => ['demo' => true, 'plan' => $plan->name ?? 'plan', 'cycle' => 'monthly'],
                    'changed' => $paidAt,
                    'created' => $paidAt,
                ];
            }
            $this->writer->insertRows('payment_history', $rows);
        }

        if ($this->writer->hasTable('payment_subscriptions') && $this->countOwned('payment_subscriptions', 'uid', $userId) === 0) {
            $startedAt = CarbonImmutable::now()->subMonths($months)->timestamp;
            $this->writer->insert('payment_subscriptions', [
                'id_secure' => $this->secure('sub-'.$userId),
                'uid' => $userId,
                'plan_id' => $planId,
                'type' => 1,
                'service' => 'manual',
                'source' => 'demo',
                'subscription_id' => 'SUB-DEMO-'.$userId,
                'customer_id' => 'CUS-DEMO-'.$userId,
                'amount' => $price,
                'currency' => 'VND',
                'status' => 1,
                'changed' => CarbonImmutable::now()->timestamp,
                'created' => $startedAt,
            ]);
        }

        if ($this->writer->hasTable('payment_manual') && $this->countOwned('payment_manual', 'uid', $userId) === 0) {
            $this->writer->insert('payment_manual', [
                'id_secure' => $this->secure('pm-'.$userId),
                'uid' => $userId,
                'plan_id' => $planId,
                'payment_id' => 'MANUAL-DEMO-'.$userId,
                'payment_info' => 'Chuyển khoản ngân hàng (mô phỏng) cho gói '.($plan->name ?? 'MLHUB'),
                'amount' => $price,
                'currency' => 'VND',
                'notes' => 'Thanh toán demo, đã duyệt.',
                'status' => 1,
                'created' => CarbonImmutable::now()->subMonths(2)->timestamp,
                'changed' => CarbonImmutable::now()->timestamp,
            ]);
        }
    }

    protected function seedCreditTopups(int $userId, bool $isPaid): void
    {
        if (! $isPaid || ! $this->writer->hasTable('credit_topup_ledgers')) {
            return;
        }

        if ($this->countOwned('credit_topup_ledgers', 'user_id', $userId) > 0) {
            return;
        }

        $packId = DB::table('credit_packs')->orderBy('id')->value('id');
        $rows = [];

        for ($i = 0; $i < 3; $i++) {
            $createdAt = $this->timeline->at($i + 12, 3);
            $amount = [500, 2000, 5000][$i % 3];
            $rows[] = [
                'user_id' => $userId,
                'credit_pack_id' => $packId ? (int) $packId : null,
                'payment_history_id' => null,
                'type' => 'purchase',
                'amount' => $amount,
                'remaining' => (int) ($amount * 0.6),
                'expires_at' => CarbonImmutable::now()->addMonths(12),
                'metadata' => ['demo' => true, 'simulate_only' => true],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        $this->writer->insertRows('credit_topup_ledgers', $rows);
    }

    /**
     * @param  list<object>  $businesses
     */
    protected function seedAiStudio(int $userId, array $businesses): void
    {
        $now = CarbonImmutable::now();

        if ($this->writer->hasTable('ai_studio_user_settings') && ! DB::table('ai_studio_user_settings')->where('user_id', $userId)->exists()) {
            $this->writer->insert('ai_studio_user_settings', [
                'user_id' => $userId,
                'settings' => ['demo' => true, 'default_tone' => 'than_thien', 'default_language' => 'vi'],
                'created_at' => $now->subDays(60),
                'updated_at' => $now,
            ]);
        }

        if ($this->writer->hasTable('ai_studio_workspace_settings') && ! DB::table('ai_studio_workspace_settings')->where('owner_user_id', $userId)->whereNull('team_id')->exists()) {
            $this->writer->insert('ai_studio_workspace_settings', [
                'owner_user_id' => $userId,
                'team_id' => null,
                'settings' => ['demo' => true, 'brand_voice' => 'gần gũi với khách hàng địa phương Đà Nẵng'],
                'created_at' => $now->subDays(60),
                'updated_at' => $now,
            ]);
        }

        $modules = ['content_writer', 'review_reply', 'caption_studio', 'campaign_builder'];
        $tones = ['thân thiện', 'chuyên nghiệp', 'truyền cảm hứng'];

        if ($this->writer->hasTable('ai_prompt_histories') && $this->countOwned('ai_prompt_histories', 'owner_user_id', $userId) === 0) {
            $rows = [];
            for ($i = 0; $i < 18; $i++) {
                $createdAt = $this->timeline->at($i + 9, 18);
                $rows[] = [
                    'owner_user_id' => $userId,
                    'requested_by_user_id' => $userId,
                    'team_id' => null,
                    'module' => $modules[$i % count($modules)],
                    'title' => 'Yêu cầu nội dung #'.($i + 1),
                    'language' => 'vi',
                    'tone' => $tones[$i % count($tones)],
                    'prompt' => 'Viết nội dung marketing ngắn gọn, hấp dẫn cho cơ sở kinh doanh tại Đà Nẵng, nhấn mạnh ưu đãi và trải nghiệm khách hàng.',
                    'input_payload' => ['demo' => true, 'goal' => 'tang_tuong_tac'],
                    'output_payload' => ['demo' => true, 'text' => 'Nội dung gợi ý mô phỏng cho buổi trình bày demo.'],
                    'metadata' => ['simulate_only' => true],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }
            $this->writer->insertRows('ai_prompt_histories', $rows);
        }

        if ($this->writer->hasTable('ai_content_plans') && $this->countOwned('ai_content_plans', 'owner_user_id', $userId) === 0) {
            for ($i = 0; $i < 3; $i++) {
                $start = $now->subDays(30 * (3 - $i));
                $items = [];
                for ($d = 0; $d < 14; $d++) {
                    $items[] = [
                        'day' => $d + 1,
                        'date' => $start->addDays($d)->toDateString(),
                        'topic' => 'Bài đăng ngày '.($d + 1).': ưu đãi và câu chuyện khách hàng',
                        'channel' => ['facebook', 'google_post', 'zalo'][$d % 3],
                    ];
                }
                $this->writer->insert('ai_content_plans', [
                    'owner_user_id' => $userId,
                    'requested_by_user_id' => $userId,
                    'team_id' => null,
                    'title' => 'Kế hoạch nội dung 14 ngày #'.($i + 1),
                    'brief' => 'Lịch nội dung mô phỏng cho cơ sở kinh doanh địa phương, tập trung vào ưu đãi, đánh giá thật và chăm sóc khách quay lại.',
                    'start_date' => $start->toDateString(),
                    'days' => 14,
                    'source' => 'ai',
                    'overview' => 'Tổng quan: xen kẽ bài ưu đãi, bài câu chuyện khách hàng và bài xin đánh giá.',
                    'items' => $items,
                    'metadata' => ['demo' => true],
                    'created_at' => $start,
                    'updated_at' => $now,
                ]);
            }
        }

        if ($this->writer->hasTable('ai_image_jobs') && $this->countOwned('ai_image_jobs', 'owner_user_id', $userId) === 0) {
            $rows = [];
            for ($i = 0; $i < 9; $i++) {
                $createdAt = $this->timeline->at($i + 14, 9);
                $rows[] = [
                    'owner_user_id' => $userId,
                    'requested_by_user_id' => $userId,
                    'team_id' => null,
                    'file_id' => null,
                    'provider' => 'demo',
                    'model' => 'demo-image-v1',
                    'status' => 'generated',
                    'style' => ['hiện đại', 'tối giản', 'ấm cúng'][$i % 3],
                    'ratio' => ['1:1', '4:5', '16:9'][$i % 3],
                    'prompt' => 'Tạo ảnh quảng bá cho cơ sở kinh doanh tại Đà Nẵng (mô phỏng).',
                    'metadata' => ['demo' => true, 'simulate_only' => true],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }
            $this->writer->insertRows('ai_image_jobs', $rows);
        }

        if ($this->writer->hasTable('ai_usage_logs') && $this->countOwned('ai_usage_logs', 'user_id', $userId) === 0) {
            $rows = [];
            for ($i = 0; $i < 24; $i++) {
                $createdAt = $this->timeline->at($i + 20, 24);
                $rows[] = [
                    'user_id' => $userId,
                    'provider' => 'demo',
                    'capability' => ['text', 'image', 'text', 'text'][$i % 4],
                    'model' => 'demo-model',
                    'status' => $i % 11 === 0 ? 'error' : 'success',
                    'feature' => ['content_writer', 'review_reply', 'caption_studio'][$i % 3],
                    'route_name' => 'portal.ai-studio',
                    'prompt_tokens' => 120 + ($i * 7),
                    'completion_tokens' => 200 + ($i * 11),
                    'total_tokens' => 320 + ($i * 18),
                    'estimated_cost' => 0.0,
                    'latency_ms' => 600 + (($i * 37) % 2400),
                    'error_message' => $i % 11 === 0 ? 'Lỗi mô phỏng, không gọi nhà cung cấp thật.' : null,
                    'metadata' => ['demo' => true],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }
            $this->writer->insertRows('ai_usage_logs', $rows);
        }
    }

    /**
     * @param  list<object>  $businesses
     */
    protected function seedMarketingTemplates(int $userId, array $businesses): void
    {
        if (! $this->writer->hasTable('lb_marketing_templates') || $this->countOwned('lb_marketing_templates', 'user_id', $userId) > 0) {
            return;
        }

        $templateIds = [];

        foreach (DemoContentCatalog::marketingTemplates() as $i => $tpl) {
            $createdAt = $this->timeline->at($i + 7, 8);
            $id = $this->writer->insert('lb_marketing_templates', [
                'user_id' => $userId,
                'team_id' => null,
                'created_by' => $userId,
                'name' => $tpl['name'],
                'slug' => DemoContentCatalog::slug($tpl['name'], 'tpl-'.$userId),
                'type' => $tpl['type'],
                'category' => $tpl['category'],
                'goal' => $tpl['goal'],
                'description' => $tpl['description'],
                'icon' => 'fa-light fa-grid-2',
                'content' => ['demo' => true, 'sections' => ['hero', 'offer', 'form', 'footer']],
                'is_system' => false,
                'source' => 'custom',
                'visibility' => 'private',
                'marketplace_status' => 'none',
                'featured' => $i % 4 === 0,
                'rating_count' => 3 + ($i % 7),
                'rating_sum' => (3 + ($i % 7)) * 4,
                'status' => 'active',
                'version' => '1.0.0',
                'usage_count' => 5 + ($i * 3),
                'settings' => ['demo' => true],
                'design' => ['primary_color' => ['#0f766e', '#dc2626', '#2563eb', '#9333ea'][$i % 4]],
                'builder_schema' => ['demo' => true],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            if ($id) {
                $templateIds[] = (int) $id;
            }
        }

        if ($templateIds === []) {
            return;
        }

        if ($this->writer->hasTable('lb_template_usages')) {
            $rows = [];
            $business = $businesses[0] ?? null;
            foreach ($templateIds as $i => $templateId) {
                $createdAt = $this->timeline->at($i + 10, count($templateIds));
                $rows[] = [
                    'team_id' => null,
                    'template_id' => $templateId,
                    'business_id' => $business->id ?? null,
                    'created_object_type' => 'landing_page',
                    'created_object_id' => null,
                    'user_id' => $userId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }
            $this->writer->insertRows('lb_template_usages', $rows);
        }

        if ($this->writer->hasTable('lb_template_ratings')) {
            foreach ($templateIds as $i => $templateId) {
                if (DB::table('lb_template_ratings')->where('template_id', $templateId)->where('user_id', $userId)->exists()) {
                    continue;
                }
                $this->writer->insert('lb_template_ratings', [
                    'template_id' => $templateId,
                    'user_id' => $userId,
                    'rating' => [5, 4, 5, 4, 3][$i % 5],
                    'created_at' => CarbonImmutable::now()->subDays(20 - $i),
                    'updated_at' => CarbonImmutable::now(),
                ]);
            }
        }
    }

    /**
     * @param  list<object>  $businesses
     */
    protected function seedGoogleAutoReply(int $userId, array $businesses): void
    {
        if (! $this->writer->hasTable('lb_google_auto_reply_rules')) {
            return;
        }

        $locations = $this->writer->hasTable('lb_google_business_locations')
            ? DB::table('lb_google_business_locations')->where('team_id', $userId)->orderBy('id')->get()->all()
            : [];

        if ($locations === [] || $this->countOwned('lb_google_auto_reply_rules', 'team_id', $userId) > 0) {
            return;
        }

        $ruleConfigs = [
            ['name' => 'Tự động cảm ơn đánh giá 5 sao', 'rating' => 'positive', 'mode' => 'auto', 'reply' => 'Cảm ơn anh/chị đã dành thời gian đánh giá. Rất mong được phục vụ anh/chị lần sau!'],
            ['name' => 'Soạn nháp phản hồi đánh giá thấp', 'rating' => 'negative', 'mode' => 'draft', 'reply' => 'Cảm ơn anh/chị đã góp ý. Chúng tôi rất tiếc về trải nghiệm chưa trọn vẹn và sẽ cải thiện ngay.'],
        ];

        foreach ($ruleConfigs as $i => $config) {
            $location = $locations[$i % count($locations)];
            $ruleId = $this->writer->insert('lb_google_auto_reply_rules', [
                'team_id' => $userId,
                'business_id' => $location->business_id ?? null,
                'google_business_location_id' => $location->id,
                'name' => $config['name'],
                'rating_condition' => $config['rating'],
                'text_condition' => 'any',
                'keyword' => null,
                'reply_mode' => $config['mode'],
                'template_reply' => $config['reply'],
                'tone' => 'professional',
                'language' => 'same',
                'delay_minutes' => $i === 0 ? 0 : 30,
                'status' => 'active',
                'created_at' => CarbonImmutable::now()->subDays(40 - $i),
                'updated_at' => CarbonImmutable::now(),
            ]);

            if ($ruleId && $this->writer->hasTable('lb_google_auto_reply_logs') && $this->writer->hasTable('lb_google_reviews')) {
                $reviews = DB::table('lb_google_reviews')
                    ->where('team_id', $userId)
                    ->orderBy('id')
                    ->limit(9)
                    ->get()
                    ->all();

                $logRows = [];
                foreach ($reviews as $j => $review) {
                    $createdAt = $this->timeline->at($j + 30, max(1, count($reviews)));
                    $logRows[] = [
                        'team_id' => $userId,
                        'rule_id' => (int) $ruleId,
                        'review_id' => $review->id,
                        'action' => $config['mode'] === 'auto' ? 'replied' : 'drafted',
                        'generated_reply' => $config['reply'],
                        'publish_status' => $config['mode'] === 'auto' ? 'published' : 'draft',
                        'error_message' => null,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ];
                }
                $this->writer->insertRows('lb_google_auto_reply_logs', $logRows);
            }
        }
    }

    protected function seedSupportTickets(int $userId): void
    {
        if (! $this->writer->hasTable('support_tickets') || $this->countOwned('support_tickets', 'uid', $userId) > 0) {
            return;
        }

        $categoryIds = DB::table('support_categories')->orderBy('id')->pluck('id')->all();
        $typeIds = DB::table('support_types')->orderBy('id')->pluck('id')->all();
        $labelIds = DB::table('support_labels')->orderBy('id')->pluck('id')->all();
        $statusPool = [1, 1, 2, 0];

        foreach (DemoContentCatalog::supportTickets() as $i => $ticket) {
            $createdAt = $this->timeline->at($i + 25, 8);
            $status = $statusPool[$i % count($statusPool)];
            $ticketId = $this->writer->insert('support_tickets', [
                'id_secure' => $this->secure('ticket-'.$userId.'-'.$i),
                'uid' => $userId,
                'open_by' => $userId,
                'team_id' => null,
                'cate_id' => $categoryIds !== [] ? (int) $categoryIds[$i % count($categoryIds)] : null,
                'type_id' => $typeIds !== [] ? (int) $typeIds[$i % count($typeIds)] : null,
                'title' => $ticket['title'],
                'content' => $ticket['content'],
                'status' => $status,
                'pin' => $i === 0,
                'user_read' => true,
                'admin_read' => $status !== 1,
                'changed' => $createdAt->addDays(1)->timestamp,
                'created' => $createdAt->timestamp,
            ]);

            if (! $ticketId) {
                continue;
            }

            if ($status !== 1 && $this->writer->hasTable('support_comments')) {
                $this->writer->insert('support_comments', [
                    'id_secure' => $this->secure('ticket-comment-'.$userId.'-'.$i),
                    'ticket_id' => (int) $ticketId,
                    'user_id' => $userId,
                    'comment' => 'Cảm ơn đội ngũ MLHUB đã hỗ trợ, vấn đề của mình đã được xử lý.',
                    'changed' => $createdAt->addDays(2)->timestamp,
                    'created' => $createdAt->addDays(2)->timestamp,
                ]);
            }

            if ($labelIds !== [] && $this->writer->hasTable('support_map_labels')) {
                $this->writer->insert('support_map_labels', [
                    'ticket_id' => (int) $ticketId,
                    'label_id' => (int) $labelIds[$i % count($labelIds)],
                ]);
            }
        }
    }

    /**
     * Tạo (hoặc lấy) 1 team workspace thật cho demo user trả phí, kèm bản ghi
     * pivot owner. Trả về id của teams.id để các bảng team_* trỏ đúng khóa ngoại.
     */
    protected function resolveTeamId(int $userId, string $username): ?int
    {
        if (array_key_exists($userId, $this->teamIdCache)) {
            return $this->teamIdCache[$userId];
        }

        if (! $this->writer->hasTable('teams')) {
            return $this->teamIdCache[$userId] = null;
        }

        $existing = DB::table('teams')->where('owner_user_id', $userId)->value('id');

        if ($existing) {
            return $this->teamIdCache[$userId] = (int) $existing;
        }

        $user = DB::table('users')->where('id', $userId)->first();
        $displayName = (string) ($user->name ?? $username);
        $createdAt = CarbonImmutable::now()->subMonths(8);

        $teamId = $this->writer->insert('teams', [
            'name' => 'Đội ngũ '.$displayName,
            'slug' => 'team-'.$username.'-'.$userId,
            'description' => 'Workspace demo của '.$displayName.' trên MLHUB.',
            'enabled_modules' => null,
            'owner_user_id' => $userId,
            'created_at' => $createdAt,
            'updated_at' => CarbonImmutable::now(),
        ]);

        if (! $teamId) {
            return $this->teamIdCache[$userId] = null;
        }

        if ($this->writer->hasTable('team_user')
            && ! DB::table('team_user')->where('team_id', (int) $teamId)->where('user_id', $userId)->exists()) {
            $this->writer->insert('team_user', [
                'team_id' => (int) $teamId,
                'user_id' => $userId,
                'role' => 'owner',
                'permissions' => null,
                'managed_account_ids' => null,
                'created_at' => $createdAt,
                'updated_at' => CarbonImmutable::now(),
            ]);
        }

        return $this->teamIdCache[$userId] = (int) $teamId;
    }

    /** Lấy team id đã tồn tại của user (không tạo mới). */
    protected function existingTeamId(int $userId): ?int
    {
        if (array_key_exists($userId, $this->teamIdCache) && $this->teamIdCache[$userId]) {
            return $this->teamIdCache[$userId];
        }

        if (! $this->writer->hasTable('teams')) {
            return null;
        }

        $id = DB::table('teams')->where('owner_user_id', $userId)->value('id');

        return $id ? (int) $id : null;
    }

    protected function seedTeamWorkspace(int $userId, string $username, bool $isPaid): void
    {
        if (! $isPaid || ! $this->writer->hasTable('team_conversations')) {
            return;
        }

        $teamId = $this->resolveTeamId($userId, $username);

        if (! $teamId) {
            return;
        }

        if ($this->countOwned('team_conversations', 'team_id', $teamId) > 0) {
            return;
        }

        $rooms = ['Trao đổi chăm sóc khách hàng', 'Kế hoạch marketing tháng', 'Vận hành cơ sở'];
        $messages = [
            'Mọi người nhớ kiểm tra các đánh giá mới trên Google hôm nay nhé.',
            'Tuần này tập trung chạy ưu đãi cho khách quay lại.',
            'Đã cập nhật lịch chăm sóc khách VIP, mọi người xem qua giúp.',
            'Số lượt quét QR cuối tuần tăng tốt, giữ nhịp nội dung nhé.',
        ];

        foreach ($rooms as $i => $room) {
            $createdAt = CarbonImmutable::now()->subDays(50 - $i * 5);
            $conversationId = $this->writer->insert('team_conversations', [
                'team_id' => $teamId,
                'created_by_user_id' => $userId,
                'type' => 'room',
                'title' => $room,
                'description' => 'Kênh trao đổi nội bộ (dữ liệu demo).',
                'last_message_at' => CarbonImmutable::now()->subDays($i),
                'metadata' => ['demo' => true],
                'created_at' => $createdAt,
                'updated_at' => CarbonImmutable::now(),
            ]);

            if (! $conversationId) {
                continue;
            }

            if ($this->writer->hasTable('team_conversation_participants')) {
                $this->writer->insert('team_conversation_participants', [
                    'conversation_id' => (int) $conversationId,
                    'user_id' => $userId,
                    'role' => 'owner',
                    'created_at' => $createdAt,
                    'updated_at' => CarbonImmutable::now(),
                ]);
            }

            if ($this->writer->hasTable('team_messages')) {
                $rows = [];
                for ($m = 0; $m < 6; $m++) {
                    $sentAt = $createdAt->addDays($m)->addHours(2 + $m);
                    $rows[] = [
                        'conversation_id' => (int) $conversationId,
                        'user_id' => $userId,
                        'body' => $messages[($i + $m) % count($messages)],
                        'attachments' => null,
                        'metadata' => ['demo' => true],
                        'sent_at' => $sentAt,
                        'created_at' => $sentAt,
                        'updated_at' => $sentAt,
                    ];
                }
                $this->writer->insertRows('team_messages', $rows);
            }
        }

        if ($this->writer->hasTable('team_invitations') && $this->countOwned('team_invitations', 'team_id', $teamId) === 0) {
            $roles = ['member', 'editor', 'viewer'];
            for ($i = 0; $i < 3; $i++) {
                $this->writer->insert('team_invitations', [
                    'team_id' => $teamId,
                    'invited_by_user_id' => $userId,
                    'accepted_by_user_id' => null,
                    'email' => 'thanhvien'.($i + 1).'+'.$username.'@mlhub.vn',
                    'invite_code' => strtoupper(Str::random(4)).'-'.$userId.'-'.$i,
                    'role' => $roles[$i % count($roles)],
                    'permissions' => ['demo' => true],
                    'status' => $i === 0 ? 'accepted' : 'pending',
                    'message' => 'Mời bạn tham gia quản lý cơ sở cùng mình trên MLHUB.',
                    'expires_at' => CarbonImmutable::now()->addDays(14),
                    'accepted_at' => $i === 0 ? CarbonImmutable::now()->subDays(5) : null,
                    'metadata' => ['demo' => true],
                    'created_at' => CarbonImmutable::now()->subDays(20 - $i),
                    'updated_at' => CarbonImmutable::now(),
                ]);
            }
        }

        if ($this->writer->hasTable('team_activity_logs') && $this->countOwned('team_activity_logs', 'owner_user_id', $userId) === 0) {
            $rows = [];
            $actions = ['business.created', 'campaign.published', 'customer.tagged', 'automation.activated', 'review.replied'];
            for ($i = 0; $i < 14; $i++) {
                $createdAt = $this->timeline->at($i + 16, 14);
                $rows[] = [
                    'team_id' => $teamId,
                    'owner_user_id' => $userId,
                    'actor_user_id' => $userId,
                    'subject_type' => 'demo',
                    'subject_id' => null,
                    'action' => $actions[$i % count($actions)],
                    'metadata' => ['demo' => true],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }
            $this->writer->insertRows('team_activity_logs', $rows);
        }
    }

    /**
     * @param  list<object>  $businesses
     */
    protected function seedCustomDomains(int $userId, string $username, bool $isPaid): void
    {
        if (! $isPaid || ! $this->writer->hasTable('custom_domains')) {
            return;
        }

        if ($this->countOwned('custom_domains', 'owner_user_id', $userId) > 0) {
            return;
        }

        $domain = $username.'-demo.mlhub.vn';

        if (DB::table('custom_domains')->where('domain', $domain)->exists()) {
            return;
        }

        $this->writer->insert('custom_domains', [
            'owner_user_id' => $userId,
            'team_id' => null,
            'domain' => $domain,
            'status' => 'verified',
            'is_default' => true,
            'verification_token' => $this->secure('domain-'.$userId, 32),
            'verified_at' => CarbonImmutable::now()->subDays(30),
            'settings' => ['demo' => true],
            'created_at' => CarbonImmutable::now()->subDays(45),
            'updated_at' => CarbonImmutable::now(),
        ]);
    }

    protected function seedAuditLogs(int $userId): void
    {
        if (! $this->writer->hasTable('audit_logs') || $this->countOwned('audit_logs', 'causer_user_id', $userId) > 0) {
            return;
        }

        $events = ['login', 'business.updated', 'campaign.created', 'plan.changed', 'profile.updated', 'export.customers'];
        $rows = [];

        for ($i = 0; $i < 16; $i++) {
            $createdAt = $this->timeline->at($i + 4, 16);
            $event = $events[$i % count($events)];
            $rows[] = [
                'causer_user_id' => $userId,
                'event' => $event,
                'description' => 'Hoạt động '.$event.' (nhật ký demo).',
                'subject_type' => 'demo',
                'subject_id' => null,
                'route_name' => 'portal.dashboard',
                'area' => 'portal',
                'ip_address' => '113.161.'.(($i % 250) + 1).'.'.(($i * 7) % 250),
                'user_agent' => 'MLHUB Demo Browser/1.0',
                'metadata' => ['demo' => true],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        $this->writer->insertRows('audit_logs', $rows);
    }

    /**
     * @param  list<object>  $customers
     */
    protected function seedLoyaltyReferralExtras(int $userId, string $username, array $customers): void
    {
        if ($customers === []) {
            return;
        }

        // Phần thưởng loyalty: dựa trên thẻ tích điểm đã tạo ở board seeder.
        if ($this->writer->hasTable('lb_loyalty_rewards') && $this->writer->hasTable('lb_loyalty_cards')) {
            $cards = DB::table('lb_loyalty_cards')->where('user_id', $userId)->orderBy('id')->get()->all();

            foreach ($cards as $cardIndex => $card) {
                if (DB::table('lb_loyalty_rewards')->where('card_id', $card->id)->exists()) {
                    continue;
                }

                $cardCustomers = $this->writer->hasTable('lb_loyalty_customers')
                    ? DB::table('lb_loyalty_customers')->where('card_id', $card->id)->orderBy('id')->limit(40)->pluck('customer_id')->all()
                    : [];

                if ($cardCustomers === []) {
                    continue;
                }

                $rows = [];
                foreach ($cardCustomers as $i => $customerId) {
                    if ($i % 3 !== 0) {
                        continue;
                    }
                    $createdAt = $this->timeline->at($i + 5, count($cardCustomers));
                    $status = ['available', 'used', 'expired'][$i % 3];
                    $rows[] = [
                        'card_id' => $card->id,
                        'customer_id' => $customerId,
                        'code' => 'LOYAL-'.$userId.'-'.$cardIndex.'-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                        'status' => $status,
                        'expires_at' => CarbonImmutable::now()->addDays(60),
                        'used_at' => $status === 'used' ? $createdAt->addDays(3) : null,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ];
                }
                $this->writer->insertRows('lb_loyalty_rewards', $rows);
            }
        }

        // Lượt giới thiệu thành công + phần thưởng giới thiệu.
        if ($this->writer->hasTable('lb_referrals') && $this->writer->hasTable('lb_referral_campaigns') && $this->writer->hasTable('lb_referral_links')) {
            $campaigns = DB::table('lb_referral_campaigns')->where('user_id', $userId)->orderBy('id')->get()->all();

            foreach ($campaigns as $campaign) {
                if (DB::table('lb_referrals')->where('campaign_id', $campaign->id)->exists()) {
                    continue;
                }

                $links = DB::table('lb_referral_links')->where('campaign_id', $campaign->id)->orderBy('id')->limit(30)->get()->all();

                if ($links === []) {
                    continue;
                }

                $referralRows = [];
                $rewardRows = [];
                foreach ($links as $i => $link) {
                    if ($i % 2 !== 0) {
                        continue;
                    }
                    $referred = $customers[($i * 7) % count($customers)];
                    $createdAt = $this->timeline->at($i + 8, count($links));
                    $referralId = $this->writer->insert('lb_referrals', [
                        'campaign_id' => $campaign->id,
                        'referral_link_id' => $link->id,
                        'referrer_customer_id' => $link->customer_id,
                        'referred_customer_id' => $referred->id,
                        'target_action' => 'lead',
                        'target_id' => null,
                        'status' => 'converted',
                        'converted_at' => $createdAt->addDays(1),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt->addDays(1),
                    ]);

                    if ($referralId && $this->writer->hasTable('lb_referral_rewards')) {
                        $rewardRows[] = [
                            'campaign_id' => $campaign->id,
                            'customer_id' => $link->customer_id,
                            'referral_id' => (int) $referralId,
                            'code' => 'REF-'.$userId.'-'.$campaign->id.'-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                            'status' => $i % 2 === 0 ? 'available' : 'used',
                            'expires_at' => CarbonImmutable::now()->addDays(45),
                            'used_at' => $i % 2 === 0 ? null : $createdAt->addDays(4),
                            'created_at' => $createdAt->addDays(1),
                            'updated_at' => $createdAt->addDays(1),
                        ];
                    }
                }

                if ($rewardRows !== []) {
                    $this->writer->insertRows('lb_referral_rewards', $rewardRows);
                }
            }
        }
    }

    /**
     * @param  list<object>  $customers
     */
    protected function seedCrmExtras(int $userId, array $customers): void
    {
        // Hàng đợi automation CRM.
        if ($this->writer->hasTable('lb_crm_automation_jobs') && $this->writer->hasTable('lb_crm_automations')) {
            if ($this->countOwned('lb_crm_automation_jobs', 'team_id', $userId) === 0) {
                $automations = DB::table('lb_crm_automations')->where('team_id', $userId)->orderBy('id')->get()->all();

                if ($automations !== [] && $customers !== []) {
                    $rows = [];
                    $statuses = ['pending', 'processed', 'processed', 'failed'];
                    for ($i = 0; $i < 24; $i++) {
                        $automation = $automations[$i % count($automations)];
                        $customer = $customers[($i * 5) % count($customers)];
                        $createdAt = $this->timeline->at($i + 9, 24);
                        $status = $statuses[$i % count($statuses)];
                        $rows[] = [
                            'team_id' => $userId,
                            'automation_id' => $automation->id,
                            'customer_id' => $customer->id,
                            'event_name' => ['customer.created', 'coupon.used', 'booking.completed'][$i % 3],
                            'payload' => ['demo' => true, 'customer_id' => $customer->id],
                            'status' => $status,
                            'attempts' => $status === 'failed' ? 3 : 1,
                            'run_at' => $createdAt,
                            'processed_at' => $status === 'pending' ? null : $createdAt->addMinutes(5),
                            'message' => $status === 'failed' ? 'Lỗi mô phỏng, không thực thi thật.' : null,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt->addMinutes(5),
                        ];
                    }
                    $this->writer->insertRows('lb_crm_automation_jobs', $rows);
                }
            }
        }

        // Nhật ký gộp khách hàng trùng.
        if ($this->writer->hasTable('lb_customer_merge_logs') && count($customers) >= 10) {
            if ($this->countOwned('lb_customer_merge_logs', 'team_id', $userId) === 0) {
                $rows = [];
                for ($i = 0; $i < 5; $i++) {
                    $primary = $customers[$i * 2];
                    $merged = $customers[$i * 2 + 1];
                    $createdAt = $this->timeline->at($i + 13, 5);
                    $rows[] = [
                        'team_id' => $userId,
                        'primary_customer_id' => $primary->id,
                        'merged_customer_id' => $merged->id,
                        'merged_by' => $userId,
                        'metadata' => ['demo' => true, 'reason' => 'trùng số điện thoại'],
                        'created_at' => $createdAt,
                    ];
                }
                $this->writer->insertRows('lb_customer_merge_logs', $rows);
            }
        }
    }

    protected function seedAffiliate(int $userId): void
    {
        if ($this->writer->hasTable('affiliate_commissions') && $this->countOwned('affiliate_commissions', 'affiliate_user_id', $userId) === 0) {
            $rows = [];
            $statuses = [0, 1, 1, 2];
            for ($i = 0; $i < 18; $i++) {
                $createdAt = $this->timeline->at($i + 7, 18);
                $status = $statuses[$i % count($statuses)];
                $amount = 199000 + (($i * 137000) % 1800000);
                $rows[] = [
                    'id_secure' => $this->secure('aff-comm-'.$userId.'-'.$i),
                    'affiliate_user_id' => $userId,
                    'referred_user_id' => null,
                    'payment_history_id' => null,
                    'amount' => $amount,
                    'commission_rate' => 20.0,
                    'commission' => round($amount * 0.2, 2),
                    'status' => $status,
                    'meta' => ['demo' => true],
                    'approved_at' => $status === 1 ? $createdAt->addDays(2) : null,
                    'rejected_at' => $status === 2 ? $createdAt->addDays(2) : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt->addDays(2),
                ];
            }
            $this->writer->insertRows('affiliate_commissions', $rows);
        }

        if ($this->writer->hasTable('affiliate_withdrawals') && $this->countOwned('affiliate_withdrawals', 'affiliate_user_id', $userId) === 0) {
            $rows = [];
            $statuses = [0, 1, 1];
            for ($i = 0; $i < 3; $i++) {
                $createdAt = $this->timeline->at($i + 11, 3);
                $status = $statuses[$i % count($statuses)];
                $rows[] = [
                    'id_secure' => $this->secure('aff-wd-'.$userId.'-'.$i),
                    'affiliate_user_id' => $userId,
                    'amount' => 1500000 + ($i * 500000),
                    'payment_method' => 'bank_transfer',
                    'payment_details' => 'Ngân hàng demo - STK 0123456789 (mô phỏng).',
                    'notes' => 'Yêu cầu rút hoa hồng demo.',
                    'status' => $status,
                    'processed_at' => $status === 1 ? $createdAt->addDays(3) : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt->addDays(3),
                ];
            }
            $this->writer->insertRows('affiliate_withdrawals', $rows);
        }
    }

    /**
     * Best-effort cho module chưa cài (AppPublishing/AppChannels). Bảng tồn tại thì seed,
     * UI sẽ hiển thị sau khi cài module marketplace tương ứng.
     *
     * @param  list<object>  $businesses
     */
    protected function seedPublishingMock(int $userId, string $username, array $businesses, bool $isPaid): void
    {
        if (! $isPaid) {
            return;
        }

        $accountIds = [];

        if ($this->writer->hasTable('social_accounts') && $this->countOwned('social_accounts', 'created_by_user_id', $userId) === 0) {
            $providers = [
                ['provider_key' => 'facebook_page', 'name' => 'Trang Facebook cơ sở'],
                ['provider_key' => 'google_business', 'name' => 'Hồ sơ Google Business'],
                ['provider_key' => 'zalo_oa', 'name' => 'Zalo OA cơ sở'],
            ];
            foreach ($providers as $i => $provider) {
                $id = $this->writer->insert('social_accounts', [
                    'provider_key' => $provider['provider_key'],
                    'capability_key' => 'post',
                    'display_name' => $provider['name'],
                    'username' => $username.'_'.$provider['provider_key'],
                    'external_id' => 'mock-'.$userId.'-'.$i,
                    'category' => 'social',
                    'account_type' => 'manual',
                    'is_active' => true,
                    'metadata' => ['demo' => true, 'simulate_only' => true],
                    'created_by_user_id' => $userId,
                    'connected_at' => CarbonImmutable::now()->subDays(40 - $i),
                    'created_at' => CarbonImmutable::now()->subDays(40 - $i),
                    'updated_at' => CarbonImmutable::now(),
                ]);
                if ($id) {
                    $accountIds[] = (int) $id;
                }
            }
        }

        if ($this->writer->hasTable('posts') && $this->countOwned('posts', 'user_id', $userId) === 0) {
            $rows = [];
            $networks = ['facebook', 'google', 'zalo'];
            for ($i = 0; $i < 18; $i++) {
                $business = $businesses[$i % max(1, count($businesses))] ?? null;
                $createdAt = $this->timeline->at($i + 12, 18);
                $rows[] = [
                    'id_secure' => $this->secure('post-'.$userId.'-'.$i, 32),
                    'user_id' => $userId,
                    'team_id' => $userId,
                    'account_id' => $accountIds !== [] ? $accountIds[$i % count($accountIds)] : null,
                    'social_network' => $networks[$i % count($networks)],
                    'category' => 'marketing',
                    'type' => 'post',
                    'method' => 'basic',
                    'data' => json_encode([
                        'message' => 'Bài đăng demo: ưu đãi và câu chuyện khách hàng tại '.($business->name ?? 'cơ sở của bạn').'.',
                    ], JSON_UNESCAPED_UNICODE),
                    'time_post' => $createdAt->timestamp,
                    'status' => $i % 5 === 0 ? 0 : 1,
                    'changed' => $createdAt->timestamp,
                    'created' => $createdAt->timestamp,
                ];
            }
            $this->writer->insertRows('posts', $rows);
        }

        $postIds = $this->writer->hasTable('posts')
            ? DB::table('posts')->where('user_id', $userId)->orderBy('id')->limit(18)->pluck('id')->all()
            : [];

        $this->seedTeamPostReviews($userId, $postIds);

        if ($this->writer->hasTable('rss_schedules') && $this->countOwned('rss_schedules', 'user_id', $userId) === 0) {
            for ($i = 0; $i < 2; $i++) {
                $createdAt = CarbonImmutable::now()->subDays(30 - $i * 5);
                $scheduleId = $this->writer->insert('rss_schedules', [
                    'id_secure' => $this->secure('rss-'.$userId.'-'.$i, 32),
                    'user_id' => $userId,
                    'team_id' => null,
                    'name' => 'Lịch chia sẻ nội dung #'.($i + 1),
                    'feed_url' => 'https://demo.mlhub.vn/feed/'.$username.'/'.$i,
                    'description' => 'Lịch đăng nội dung tự động (mô phỏng).',
                    'account_ids' => $accountIds !== [] ? array_slice($accountIds, 0, 2) : [],
                    'settings' => ['demo' => true],
                    'time_posts' => ['08:30', '12:00', '19:30'],
                    'weekdays' => [1, 3, 5],
                    'start_at' => $createdAt->timestamp,
                    'status' => true,
                    'changed' => CarbonImmutable::now()->timestamp,
                    'created' => $createdAt->timestamp,
                ]);

                if ($scheduleId && $accountIds !== [] && $this->writer->hasTable('rss_schedule_histories')) {
                    $historyRows = [];
                    for ($j = 0; $j < 4; $j++) {
                        $queuedAt = $createdAt->addDays($j + 1);
                        $historyRows[] = [
                            'schedule_id' => (int) $scheduleId,
                            'account_id' => $accountIds[$j % count($accountIds)],
                            'publishing_post_id' => $postIds !== [] ? (int) $postIds[$j % count($postIds)] : null,
                            'external_guid' => 'rss-hist-'.$scheduleId.'-'.$j,
                            'external_url' => 'https://demo.mlhub.vn/feed/'.$username.'/'.$i.'/item-'.$j,
                            'content_hash' => $this->secure('rss-hist-'.$scheduleId.'-'.$j),
                            'title' => 'Bài chia sẻ tự động #'.($j + 1),
                            'queued_at' => $queuedAt->timestamp,
                            'published_at' => $queuedAt->addMinutes(10)->timestamp,
                            'changed' => $queuedAt->timestamp,
                            'created' => $queuedAt->timestamp,
                        ];
                    }
                    $this->writer->insertRows('rss_schedule_histories', $historyRows);
                }
            }
        }
    }

    /**
     * @param  list<int>  $postIds
     */
    protected function seedTeamPostReviews(int $userId, array $postIds): void
    {
        if ($postIds === []) {
            return;
        }

        $teamId = $this->existingTeamId($userId);

        if (! $teamId) {
            return;
        }

        if ($this->writer->hasTable('team_post_comments') && $this->countOwned('team_post_comments', 'team_id', $teamId) === 0) {
            $rows = [];
            $messages = [
                'Bài này ổn rồi, mình duyệt đăng nhé.',
                'Nên thêm lời kêu gọi đặt lịch ở cuối bài.',
                'Hình ảnh hợp với nội dung, giữ nguyên.',
            ];
            foreach (array_slice($postIds, 0, 9) as $i => $postId) {
                $createdAt = $this->timeline->at($i + 14, 9);
                $rows[] = [
                    'team_id' => $teamId,
                    'post_id' => (int) $postId,
                    'user_id' => $userId,
                    'message' => $messages[$i % count($messages)],
                    'metadata' => ['demo' => true],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }
            $this->writer->insertRows('team_post_comments', $rows);
        }

        if ($this->writer->hasTable('team_post_reviews') && $this->countOwned('team_post_reviews', 'team_id', $teamId) === 0) {
            $rows = [];
            $statuses = ['approved', 'pending', 'approved', 'changes_requested'];
            foreach (array_slice($postIds, 0, 8) as $i => $postId) {
                $createdAt = $this->timeline->at($i + 16, 8);
                $status = $statuses[$i % count($statuses)];
                $rows[] = [
                    'team_id' => $teamId,
                    'post_id' => (int) $postId,
                    'submitted_by_user_id' => $userId,
                    'decided_by_user_id' => $status === 'pending' ? null : $userId,
                    'status' => $status,
                    'decision_note' => $status === 'changes_requested' ? 'Cần chỉnh lại tiêu đề cho hấp dẫn hơn.' : null,
                    'submitted_at' => $createdAt,
                    'decided_at' => $status === 'pending' ? null : $createdAt->addHours(6),
                    'metadata' => ['demo' => true],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }
            $this->writer->insertRows('team_post_reviews', $rows);
        }
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * @return list<object>
     */
    protected function ownedRows(string $table, string $column, int $id, ?int $limit = null): array
    {
        if (! $this->writer->hasTable($table)) {
            return [];
        }

        $query = DB::table($table)->where($column, $id)->orderBy('id');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get()->all();
    }

    protected function countOwned(string $table, string $column, int $id): int
    {
        if (! $this->writer->hasTable($table) || ! $this->writer->hasColumn($table, $column)) {
            return 0;
        }

        return DB::table($table)->where($column, $id)->count();
    }

    protected function secure(string $seed, int $length = 40): string
    {
        return substr(hash('sha256', 'mlhub-demo-'.$seed), 0, $length);
    }
}
