<?php

namespace Modules\CustomMLHUB\Support\MLHUBAIAssistant;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Route;

class MLHUBAIResponseComposer
{
    /**
     * Deep-link calls to action for one or more intents.
     *
     * @param  list<string>  $intents
     * @param  array<string, mixed>  $context
     * @return list<array{label: string, url: string}>
     */
    public function actionsFor(array $intents, array $context): array
    {
        $actions = [];

        foreach ($intents as $intent) {
            foreach ($this->intentActions($intent, $context) as $action) {
                $key = $action['url'];

                if (! isset($actions[$key])) {
                    $actions[$key] = $action;
                }
            }
        }

        return array_slice(array_values($actions), 0, 3);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return list<array{label: string, url: string}>
     */
    protected function intentActions(string $intent, array $context): array
    {
        $map = match ($intent) {
            'next_steps', 'onboarding' => $this->nextStepActions($context),
            default => MLHUBAIKnowledgeBase::routeActions($intent),
        };

        $actions = [];

        foreach ($map as [$routeName, $label]) {
            if (Route::has($routeName)) {
                $actions[] = ['label' => $label, 'url' => route($routeName)];
            }
        }

        return $actions;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return list<array{0: string, 1: string}>
     */
    protected function nextStepActions(array $context): array
    {
        $hints = (array) ($context['onboarding'] ?? []);

        if (in_array('create_business', $hints, true)) {
            return [['portal.businesses', __('Add a business')]];
        }

        if (in_array('create_campaign', $hints, true) || in_array('publish_campaign', $hints, true)) {
            return [['portal.qr-campaigns', __('Create a campaign')]];
        }

        if (in_array('boost_reviews', $hints, true)) {
            return [['portal.review-booster', __('Open Review Booster')]];
        }

        return [['portal.ai-studio', __('Open AI Studio')]];
    }

    /**
     * Combine several intents into one connected report.
     *
     * @param  list<string>  $intents
     * @param  array<string, mixed>  $context
     */
    public function composeMany(array $intents, array $context, bool $firstTouch = false): string
    {
        $intents = array_values(array_unique(array_filter($intents)));

        $hasGreeting = in_array('greeting', $intents, true);
        $intents = array_values(array_filter($intents, static fn (string $intent): bool => $intent !== 'greeting'));

        if ($intents === []) {
            $body = $hasGreeting ? '' : $this->composeUnknown($context);
        } else {
            $parts = [];

            foreach ($intents as $intent) {
                if ($intent === 'unknown') {
                    continue;
                }

                $segment = trim($this->compose($intent, $context));

                if ($segment !== '' && ! in_array($segment, $parts, true)) {
                    $parts[] = $segment;
                }
            }

            $body = $parts === [] ? $this->composeUnknown($context) : implode("\n\n", $parts);
        }

        if ($hasGreeting && $body === '') {
            return $this->composeGreeting($context);
        }

        if ($hasGreeting) {
            return trim($this->greetingLine($context)."\n\n".$body);
        }

        if ($firstTouch) {
            return trim($this->greetingLine($context)."\n\n".$body);
        }

        return $body;
    }

    /**
     * Short one-line greeting used to warm up the first reply.
     *
     * @param  array<string, mixed>  $context
     */
    protected function greetingLine(array $context): string
    {
        $name = trim((string) data_get($context, 'user.short_name', ''));
        $greeting = $this->timeGreeting($this->now($context));

        if ($name !== '') {
            return __(':greeting :name! I am your MLHUB AI assistant.', [
                'greeting' => $greeting,
                'name' => $name,
            ]);
        }

        return __(':greeting! I am your MLHUB AI assistant.', [
            'greeting' => $greeting,
        ]);
    }

    /**
     * Full welcome with time, date and what the assistant can do.
     *
     * @param  array<string, mixed>  $context
     */
    protected function composeGreeting(array $context): string
    {
        $now = $this->now($context);
        $name = trim((string) data_get($context, 'user.short_name', ''));
        $greeting = $this->timeGreeting($now);

        $hello = $name !== ''
            ? __(':greeting :name! I am your MLHUB AI assistant. It is :time, :date.', [
                'greeting' => $greeting,
                'name' => $name,
                'time' => $now->format('H:i'),
                'date' => format_date_locale($now),
            ])
            : __(':greeting! I am your MLHUB AI assistant. It is :time, :date.', [
                'greeting' => $greeting,
                'time' => $now->format('H:i'),
                'date' => format_date_locale($now),
            ]);

        $help = __('Hôm nay tôi có thể báo cáo tình hình kinh doanh, top campaign, QR scan, booking, coupon, lead, review, credit và gợi ý việc nên làm tiếp theo.');

        $ask = __('Try asking about: :examples.', [
            'examples' => __('báo cáo sáng nay, chiến dịch hiệu quả nhất, booking mới, coupon đang tốt, hoặc Basic AI có tốn credit không'),
        ]);

        return $hello.' '.$help."\n\n".$ask;
    }

    protected function timeGreeting(CarbonImmutable $now): string
    {
        $hour = (int) $now->format('H');

        return match (true) {
            $hour >= 5 && $hour <= 10 => __('Good morning'),
            $hour >= 11 && $hour <= 12 => __('Good noon'),
            $hour >= 13 && $hour <= 17 => __('Good afternoon'),
            $hour >= 18 && $hour <= 21 => __('Good evening'),
            default => __('Hello'),
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function now(array $context): CarbonImmutable
    {
        $generatedAt = $context['generated_at'] ?? null;

        if (is_string($generatedAt) && $generatedAt !== '') {
            try {
                return CarbonImmutable::parse($generatedAt);
            } catch (\Throwable) {
                // fall through to now()
            }
        }

        return CarbonImmutable::now();
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function compose(string $intent, array $context): string
    {
        return match ($intent) {
            'help_using_mlhubai' => $this->composeHelpUsingMLHUBAI($context),
            'daily_briefing' => $this->composeDailyBriefing($context),
            'onboarding' => $this->composeNextSteps($context),
            'top_campaigns' => $this->composeTopCampaigns($context),
            'qr_scans' => $this->composeQrScans($context),
            'review_booster' => $this->composeReviewBooster($context),
            'booking' => $this->composeBookings($context),
            'coupon' => $this->composeCoupons($context),
            'feedback' => $this->composeFeedback($context),
            'leads' => $this->composeLeads($context),
            'conversion' => $this->composeConversion($context),
            'credits' => $this->composeCredits($context),
            'plan_limits' => $this->composePlanLimits($context),
            'business_locations' => $this->composeBusinessLocations($context),
            'customers' => $this->composeCustomers($context),
            'landing_pages' => $this->composeLandingPages($context),
            'marketing_templates' => $this->composeMarketingTemplates($context),
            'crm_segments' => $this->composeCrmSegments($context),
            'google_business' => $this->composeGoogleBusiness($context),
            'google_reviews' => $this->composeGoogleReviews($context),
            'ai_studio' => $this->composeAiStudio($context),
            'ai_content_writer' => $this->composeAiContentWriter($context),
            'billing' => $this->composeBilling($context),
            'teams' => $this->composeTeams($context),
            'support' => $this->composeSupport($context),
            'new_customers' => $this->composeNewCustomers($context),
            'campaigns' => $this->composeCampaigns($context),
            'reviews' => $this->composeReviews($context),
            'next_steps' => $this->composeNextSteps($context),
            'overview' => $this->composeOverview($context),
            'visits' => $this->composeVisits($context),
            'businesses' => $this->composeBusinesses($context),
            'greeting' => $this->composeGreeting($context),
            default => $this->composeUnknown($context),
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeHelpUsingMLHUBAI(array $context): string
    {
        return __('Basic AI đang trả lời bằng dữ liệu nội bộ và ma trận từ khóa MLHUB, nên không gọi OpenAI và không tốn token/credit. Advanced AI chỉ dùng provider khi bạn bật, có API key và còn credit. Bạn có thể hỏi về báo cáo hôm nay, chiến dịch, QR, booking, coupon, lead, review, credit, giới hạn gói và việc nên làm tiếp theo.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeDailyBriefing(array $context): string
    {
        $metrics = (array) ($context['metrics'] ?? []);
        $parts = [
            __('Sáng nay: :businesses cơ sở, :campaigns chiến dịch đang chạy, :visits lượt truy cập, :leads lead, :bookings booking, :coupons coupon, conversion :rate.', [
                'businesses' => format_number_locale((int) ($metrics['businesses'] ?? 0)),
                'campaigns' => format_number_locale((int) ($metrics['active_campaigns'] ?? 0)),
                'visits' => format_number_locale((int) ($metrics['visits'] ?? 0)),
                'leads' => format_number_locale((int) ($metrics['leads'] ?? 0)),
                'bookings' => format_number_locale((int) ($metrics['bookings'] ?? 0)),
                'coupons' => format_number_locale((int) ($metrics['coupon_claims'] ?? 0)),
                'rate' => format_percent_locale((float) ($metrics['conversion_rate'] ?? 0)),
            ]),
        ];

        $topLine = $this->topCampaignLine($context);
        $activityLine = $this->recentActivityLine($context);

        if ($topLine !== null) {
            $parts[] = $topLine;
        }

        if ($activityLine !== null) {
            $parts[] = $activityLine;
        }

        return implode(' ', $parts);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeTopCampaigns(array $context): string
    {
        $campaigns = array_slice((array) ($context['top_campaigns'] ?? []), 0, 3);

        if ($campaigns === []) {
            return __('Chưa có bảng xếp hạng chiến dịch. Hãy publish một campaign và chia sẻ QR để MLHUB bắt đầu đo lượt quét, chuyển đổi và campaign tốt nhất.');
        }

        $details = collect($campaigns)
            ->map(function (array $campaign): string {
                return $this->campaignPerformanceLine($campaign);
            })
            ->implode(' ');

        return __('Top chiến dịch hiện tại: :details', ['details' => $details]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeQrScans(array $context): string
    {
        $metrics = (array) ($context['metrics'] ?? []);
        $weekly = (array) ($context['weekly_signals'] ?? []);

        return __('QR đang có :total lượt truy cập tổng, tuần này thêm :week_scans lượt quét. Từ các lượt đó ghi nhận :week_leads lead và :week_bookings booking.', [
            'total' => format_number_locale((int) ($metrics['visits'] ?? 0)),
            'week_scans' => format_number_locale((int) ($weekly['qr_scans'] ?? 0)),
            'week_leads' => format_number_locale((int) ($weekly['leads'] ?? 0)),
            'week_bookings' => format_number_locale((int) ($weekly['bookings'] ?? 0)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeReviewBooster(array $context): string
    {
        return $this->composeReviews($context).' '.__('Nếu cần hành động nhanh, ưu tiên trả lời review đang chờ và đặt QR Review Booster ở điểm khách vừa hoàn tất dịch vụ.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeBookings(array $context): string
    {
        $metrics = (array) ($context['metrics'] ?? []);
        $weekly = (array) ($context['weekly_signals'] ?? []);

        return __('Booking hiện có :total lượt, riêng tuần này ghi nhận :week_count lịch hẹn. Nếu muốn tăng thêm, hãy đặt link booking ở QR chính và nhắc khách chọn slot ngay sau khi xem ưu đãi.', [
            'total' => format_number_locale((int) ($metrics['bookings'] ?? 0)),
            'week_count' => format_number_locale((int) ($weekly['bookings'] ?? 0)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeCoupons(array $context): string
    {
        $metrics = (array) ($context['metrics'] ?? []);
        $weekly = (array) ($context['weekly_signals'] ?? []);

        return __('Coupon có :total lượt nhận mã, tuần này thêm :week_count lượt claim. Ưu tiên kiểm tra campaign coupon đang kéo scan tốt nhất rồi nhân bản cho khung giờ cao điểm.', [
            'total' => format_number_locale((int) ($metrics['coupon_claims'] ?? 0)),
            'week_count' => format_number_locale((int) ($weekly['coupon_claims'] ?? 0)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeFeedback(array $context): string
    {
        $metrics = (array) ($context['metrics'] ?? []);
        $reviews = (array) ($context['reviews'] ?? []);

        return __('Feedback hiện có :total phản hồi, trong đó :needs_reply review tích cực đang chờ trả lời. Nên xử lý phản hồi mới nhất trước, đặc biệt các góp ý có rating thấp hoặc mô tả vấn đề cụ thể.', [
            'total' => format_number_locale((int) ($metrics['feedback'] ?? 0)),
            'needs_reply' => format_number_locale((int) ($reviews['needs_reply'] ?? 0)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeLeads(array $context): string
    {
        $metrics = (array) ($context['metrics'] ?? []);
        $weekly = (array) ($context['weekly_signals'] ?? []);

        return __('Lead hiện có :total, tuần này thêm :week_count lead mới. Hãy gọi hoặc nhắn nhóm lead mới trước, sau đó xem campaign nguồn để biết QR/form nào đang kéo khách tốt nhất.', [
            'total' => format_number_locale((int) ($metrics['leads'] ?? 0)),
            'week_count' => format_number_locale((int) ($weekly['leads'] ?? 0)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeConversion(array $context): string
    {
        $metrics = (array) ($context['metrics'] ?? []);

        return __('Tỉ lệ chuyển đổi hiện tại là :rate từ :visits lượt truy cập, gồm :leads lead, :bookings booking, :coupons coupon và :feedback feedback. Nếu muốn tăng nhanh, ưu tiên campaign có scan cao nhưng conversion thấp.', [
            'rate' => format_percent_locale((float) ($metrics['conversion_rate'] ?? 0)),
            'visits' => format_number_locale((int) ($metrics['visits'] ?? 0)),
            'leads' => format_number_locale((int) ($metrics['leads'] ?? 0)),
            'bookings' => format_number_locale((int) ($metrics['bookings'] ?? 0)),
            'coupons' => format_number_locale((int) ($metrics['coupon_claims'] ?? 0)),
            'feedback' => format_number_locale((int) ($metrics['feedback'] ?? 0)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeCredits(array $context): string
    {
        return __('Basic AI của /portal/chatmlhubai không dùng token OpenAI và không trừ credit vì chỉ dùng context nội bộ, từ khóa và câu trả lời dựng sẵn. Credit chủ yếu liên quan Advanced AI, AI Studio, provider API hoặc các tác vụ sinh nội dung có tính phí.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composePlanLimits(array $context): string
    {
        return __('Basic AI hiện chưa có số liệu quota chi tiết trong context, nên chưa tự khẳng định giới hạn gói. Bạn nên kiểm tra Packages hoặc Credit Usage; P1 có thể bổ sung plan snapshot để trợ lý trả lời chính xác giới hạn từng tính năng.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeBusinessLocations(array $context): string
    {
        $businesses = (int) data_get($context, 'business_list.count', 0);

        return __('Locations dùng để quản lý chi nhánh/địa điểm vật lý theo business. Hiện context Basic AI đang thấy :count business; để quản lý location hoặc QR địa điểm, mở Locations hoặc vào từng business. Basic AI không tự dựng link QR/location public nếu context chưa có slug cụ thể.', [
            'count' => format_number_locale($businesses),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeCustomers(array $context): string
    {
        $customers = (array) ($context['customers'] ?? []);

        return __('Customers là nơi xem danh bạ và dữ liệu khách. Tuần này có :new khách mới, :delta so với tuần trước. Nếu cần phân nhóm/tag/task chăm sóc lại, dùng CRM customers hoặc CRM segments.', [
            'new' => format_number_locale((int) ($customers['new_this_week'] ?? 0)),
            'delta' => format_number_locale((int) ($customers['delta'] ?? 0)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeLandingPages(array $context): string
    {
        return __('Landing Pages dùng để tạo trang chiến dịch/public page có form và CTA. Basic AI chỉ hướng dẫn nơi mở và cách dùng; public link chỉ nên copy khi landing page đã có slug trong dữ liệu, nên trợ lý không tự tạo link động khi context chưa có slug.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeMarketingTemplates(array $context): string
    {
        return __('Marketing Templates là thư viện mẫu để tạo nhanh nội dung/campaign theo mục tiêu. Dùng templates khi bạn muốn xuất phát từ mẫu có sẵn; dùng AI Studio khi muốn sinh hoặc chỉnh nội dung bằng AI và có thể cần credit.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeCrmSegments(array $context): string
    {
        return __('CRM giúp quản lý khách sâu hơn: customers, segments, tags, tasks, automations và reports. Nếu muốn chăm sóc lại khách cũ, hãy tạo segment trước, gắn tag phù hợp, rồi dùng task/automation để theo dõi follow-up.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeGoogleBusiness(array $context): string
    {
        return __('Google Business dùng để kết nối hồ sơ doanh nghiệp, locations, posts, reviews và insights. Basic AI chưa tự khẳng định trạng thái kết nối nếu context không có Google snapshot; hãy mở Google Business để kiểm tra OAuth, location và dữ liệu đồng bộ.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeGoogleReviews(array $context): string
    {
        $reviews = (array) ($context['reviews'] ?? []);

        return __('Google Reviews liên quan đồng bộ và trả lời đánh giá trên Google Business. Trong context nội bộ tuần này đang có :count review và :needs_reply review cần trả lời; để xử lý review Google hoặc auto reply, mở Google Business.', [
            'count' => format_number_locale((int) ($reviews['count'] ?? 0)),
            'needs_reply' => format_number_locale((int) ($reviews['needs_reply'] ?? 0)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeAiStudio(array $context): string
    {
        return __('AI Studio là khu vực tạo chiến dịch, prompt history, review reply và AI settings. Basic AI trong /portal/chatmlhubai không tốn credit; các tác vụ sinh nội dung trong AI Studio hoặc Advanced AI có thể dùng credit tùy cấu hình gói.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeAiContentWriter(array $context): string
    {
        return __('AI Content Writer hỗ trợ viết caption, bài quảng cáo, CTA, nội dung Facebook hoặc tin nhắn nhắc khách. Nếu chỉ hỏi hướng dẫn tại đây thì Basic AI không tốn credit; khi chạy tác vụ sinh nội dung trong AI Content, hệ thống có thể tính credit theo gói.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeBilling(array $context): string
    {
        return __('Billing là nơi xem subscription, gói, hóa đơn và lịch sử thanh toán. Muốn nâng cấp thì mở Packages; muốn tải hóa đơn thì mở Invoices; muốn xem credit dùng cho AI thì mở Credit Usage.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeTeams(array $context): string
    {
        return __('Teams quản lý workspace, thành viên và chuyển workspace. Khi mời thêm người, hãy kiểm tra vai trò/quyền trước để tránh cho nhân sự truy cập nhầm dữ liệu hoặc công cụ thanh toán.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeSupport(array $context): string
    {
        return __('Support là nơi gửi và theo dõi ticket hỗ trợ. Khi báo lỗi, nên ghi rõ màn hình đang dùng, thao tác vừa làm, thời điểm xảy ra và ảnh chụp nếu có để đội hỗ trợ xử lý nhanh hơn.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeNewCustomers(array $context): string
    {
        $customers = (array) ($context['customers'] ?? []);
        $signals = (array) ($context['weekly_signals'] ?? []);
        $count = (int) ($customers['new_this_week'] ?? 0);
        $delta = (int) ($customers['delta'] ?? 0);

        if ($count === 0 && (int) ($signals['leads'] ?? 0) === 0 && (int) ($signals['bookings'] ?? 0) === 0) {
            return __('This week there are no new customers yet. Publish a campaign and share the QR code so MLHUB can start capturing leads and bookings.');
        }

        $deltaText = match (true) {
            $delta > 0 => __('+:count vs last week', ['count' => format_number_locale($delta)]),
            $delta < 0 => __(':count vs last week', ['count' => format_number_locale($delta)]),
            default => __('same as last week'),
        };

        $parts = array_values(array_filter([
            (int) ($signals['positive_reviews'] ?? 0) > 0
                ? __(':count from Review Booster', ['count' => format_number_locale((int) $signals['positive_reviews'])])
                : null,
            (int) ($signals['bookings'] ?? 0) > 0
                ? __(':count from booking pages', ['count' => format_number_locale((int) $signals['bookings'])])
                : null,
            (int) ($signals['leads'] ?? 0) > 0
                ? __(':count from lead forms', ['count' => format_number_locale((int) $signals['leads'])])
                : null,
        ]));

        $breakdown = $parts !== []
            ? implode(', ', $parts).'.'
            : __('Keep sharing your QR codes to grow the customer list.');

        return __('This week: :count new customers (:delta). :breakdown', [
            'count' => format_number_locale($count),
            'delta' => $deltaText,
            'breakdown' => $breakdown,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeCampaigns(array $context): string
    {
        $active = (array) ($context['active_campaigns'] ?? []);

        if ($active === []) {
            return __('You have no published campaigns yet. Create a review, booking, coupon, or lead campaign and publish it to start tracking results here.');
        }

        $lines = collect($active)
            ->take(4)
            ->map(function (array $campaign): string {
                $label = $this->campaignTypeLabel((string) ($campaign['type'] ?? ''));

                return __(':name (:type): :visits scans, :conversions conversions', [
                    'name' => (string) ($campaign['name'] ?? __('Campaign')),
                    'type' => $label,
                    'visits' => format_number_locale((int) ($campaign['visits'] ?? 0)),
                    'conversions' => format_number_locale((int) ($campaign['conversions'] ?? 0)),
                ]);
            })
            ->implode(' ');

        return __(':count active campaigns. :details', [
            'count' => format_number_locale(count($active)),
            'details' => $lines,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeReviews(array $context): string
    {
        $reviews = (array) ($context['reviews'] ?? []);
        $count = (int) ($reviews['count'] ?? 0);

        if ($count === 0) {
            return __('No new reviews this week yet. Turn on Review Booster and place the QR where guests can scan after their visit.');
        }

        $average = (float) ($reviews['average_rating'] ?? 0);
        $needsReply = (int) ($reviews['needs_reply'] ?? 0);
        $sentiment = $average >= 4.5
            ? __('Positive sentiment')
            : ($average >= 3.5 ? __('Mostly positive sentiment') : __('Mixed sentiment — review the feedback closely'));

        $replyNote = $needsReply > 0
            ? __(':count reviews still need a reply.', ['count' => format_number_locale($needsReply)])
            : __('All recent positive reviews have been replied to.');

        return __('Average :rating★ from :count new reviews. :sentiment — :reply', [
            'rating' => number_format($average, 1),
            'count' => format_number_locale($count),
            'sentiment' => $sentiment,
            'reply' => $replyNote,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeNextSteps(array $context): string
    {
        $hints = (array) ($context['onboarding'] ?? []);
        $metrics = (array) ($context['metrics'] ?? []);

        if ($hints === [] && (int) ($metrics['visits'] ?? 0) > 0) {
            return __('Your funnel is running. Next: reply to pending reviews, send a weekend coupon to repeat guests, and check Reports for the best-performing campaign.');
        }

        $suggestions = [];

        if (in_array('create_business', $hints, true)) {
            $suggestions[] = __('Add your first business profile so campaigns and QR codes have a home base.');
        }

        if (in_array('create_campaign', $hints, true)) {
            $suggestions[] = __('Create your first growth campaign — Review Booster is usually the fastest win for local shops.');
        }

        if (in_array('publish_campaign', $hints, true)) {
            $suggestions[] = __('Publish a draft campaign so the public page and QR code go live.');
        }

        if (in_array('share_qr', $hints, true)) {
            $suggestions[] = __('Print or share the campaign QR at the counter, tables, or receipt so visits start flowing in.');
        }

        if (in_array('boost_reviews', $hints, true)) {
            $suggestions[] = __('Launch Review Booster to collect Google reviews automatically after each visit.');
        }

        if ($suggestions === []) {
            $suggestions[] = __('Try a weekend coupon for repeat guests — MLHUB can draft the copy and landing page in AI Studio.');
        }

        return implode(' ', $suggestions);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeOverview(array $context): string
    {
        $metrics = (array) ($context['metrics'] ?? []);

        return __('Quick snapshot: :businesses businesses, :campaigns active campaigns, :visits visits, :leads leads, :bookings bookings, :coupons coupon claims, conversion rate :rate.', [
            'businesses' => format_number_locale((int) ($metrics['businesses'] ?? 0)),
            'campaigns' => format_number_locale((int) ($metrics['active_campaigns'] ?? 0)),
            'visits' => format_number_locale((int) ($metrics['visits'] ?? 0)),
            'leads' => format_number_locale((int) ($metrics['leads'] ?? 0)),
            'bookings' => format_number_locale((int) ($metrics['bookings'] ?? 0)),
            'coupons' => format_number_locale((int) ($metrics['coupon_claims'] ?? 0)),
            'rate' => format_percent_locale((float) ($metrics['conversion_rate'] ?? 0)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeVisits(array $context): string
    {
        $metrics = (array) ($context['metrics'] ?? []);
        $weekly = (array) ($context['weekly_signals'] ?? []);

        return __('Total visits: :total. This week: :week_scans QR scans, :week_leads leads, :week_bookings bookings.', [
            'total' => format_number_locale((int) ($metrics['visits'] ?? 0)),
            'week_scans' => format_number_locale((int) ($weekly['qr_scans'] ?? 0)),
            'week_leads' => format_number_locale((int) ($weekly['leads'] ?? 0)),
            'week_bookings' => format_number_locale((int) ($weekly['bookings'] ?? 0)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeBusinesses(array $context): string
    {
        $list = (array) ($context['business_list'] ?? []);
        $count = (int) ($list['count'] ?? 0);
        $names = array_values(array_filter((array) ($list['names'] ?? [])));

        if ($count === 0) {
            return __('You have no business profiles yet. Add your first business so campaigns and QR codes have a home base.');
        }

        $shown = array_slice($names, 0, 5);
        $namesText = implode(', ', $shown);

        if ($count > count($shown)) {
            $namesText = __(':names and :count more', [
                'names' => $namesText,
                'count' => format_number_locale($count - count($shown)),
            ]);
        }

        return __('You have :count businesses: :names.', [
            'count' => format_number_locale($count),
            'names' => $namesText,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function topCampaignLine(array $context): ?string
    {
        $campaign = (array) data_get($context, 'top_campaigns.0', []);

        if ($campaign === []) {
            return null;
        }

        return __('Nổi bật nhất là :details', [
            'details' => $this->campaignPerformanceLine($campaign),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function recentActivityLine(array $context): ?string
    {
        $activity = (array) data_get($context, 'recent_activity.0', []);

        if ($activity === []) {
            return null;
        }

        $customer = trim((string) ($activity['customer'] ?? ''));
        $action = trim((string) ($activity['action'] ?? $activity['label'] ?? ''));
        $campaign = trim((string) ($activity['campaign'] ?? ''));
        $business = trim((string) ($activity['business'] ?? ''));

        $subject = trim(implode(' ', array_filter([$customer, $action])));

        if ($subject === '') {
            $subject = __('có hoạt động mới');
        }

        $source = trim(implode(' / ', array_filter([$campaign, $business])));

        if ($source === '') {
            return __('Hoạt động mới nhất: :subject.', ['subject' => $subject]);
        }

        return __('Hoạt động mới nhất: :subject từ :source.', [
            'subject' => $subject,
            'source' => $source,
        ]);
    }

    /**
     * @param  array<string, mixed>  $campaign
     */
    protected function campaignPerformanceLine(array $campaign): string
    {
        $name = (string) ($campaign['campaign_name'] ?? $campaign['name'] ?? __('Campaign'));
        $type = $this->campaignTypeLabel((string) ($campaign['campaign_type'] ?? $campaign['type'] ?? ''));
        $visits = (int) ($campaign['visits'] ?? 0);
        $conversions = (int) ($campaign['conversions'] ?? 0);
        $rate = (float) ($campaign['conversion_rate'] ?? ($visits > 0 ? ($conversions / $visits) * 100 : 0));

        return __(':name (:type): :visits lượt truy cập, :conversions chuyển đổi, conversion :rate.', [
            'name' => $name,
            'type' => $type,
            'visits' => format_number_locale($visits),
            'conversions' => format_number_locale($conversions),
            'rate' => format_percent_locale($rate),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeUnknown(array $context): string
    {
        return __('Mình chưa bắt đúng ý câu hỏi. Bạn có thể hỏi về báo cáo hôm nay, top campaign, QR scan, booking, coupon, lead, review, credit, giới hạn gói hoặc việc nên làm tiếp theo.');
    }

    protected function campaignTypeLabel(string $type): string
    {
        return match ($type) {
            'review' => __('Review Booster'),
            'booking' => __('Booking'),
            'coupon' => __('Coupon'),
            'feedback' => __('Feedback'),
            'lead' => __('Lead form'),
            default => str($type)->headline()->toString(),
        };
    }
}
