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
        [$intents] = $this->prepareIntents($intents);
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
            return [['portal.businesses', __('Thêm cơ sở kinh doanh')]];
        }

        if (in_array('create_campaign', $hints, true) || in_array('publish_campaign', $hints, true)) {
            return [['portal.qr-campaigns', __('Tạo chiến dịch')]];
        }

        if (in_array('boost_reviews', $hints, true)) {
            return [['portal.review-booster', __('Mở công cụ xin đánh giá')]];
        }

        return [['portal.ai-studio', __('Mở AI Studio')]];
    }

    /**
     * @param  list<string>  $intents
     * @return array{0: list<string>, 1: bool}
     */
    protected function prepareIntents(array $intents): array
    {
        $intents = array_values(array_unique(array_filter($intents, static fn (string $intent): bool => $intent !== 'unknown')));

        usort(
            $intents,
            static fn (string $left, string $right): int => MLHUBAIKnowledgeBase::intentPriority($left) <=> MLHUBAIKnowledgeBase::intentPriority($right),
        );

        $wasLimited = count($intents) > 5;

        return [array_slice($intents, 0, 5), $wasLimited];
    }

    /**
     * @param  list<string>  $intents
     */
    protected function appendNotice(string $body, array $intents): string
    {
        $notice = $this->usesAccountMetrics($intents)
            ? __('Câu trả lời có sử dụng số liệu thực tế từ tài khoản của bạn.')
            : __('Câu trả lời dựa trên tri thức nội bộ và cấu trúc tính năng của MLHUB.');

        return trim($body)."\n\n".$notice;
    }

    /**
     * @param  list<string>  $intents
     */
    protected function usesAccountMetrics(array $intents): bool
    {
        $metricIntents = [
            'daily_briefing',
            'overview',
            'top_campaigns',
            'qr_scans',
            'review_booster',
            'booking',
            'coupon',
            'feedback',
            'leads',
            'conversion',
            'new_customers',
            'customers',
            'campaigns',
            'reviews',
            'visits',
            'businesses',
            'business_locations',
            'google_reviews',
        ];

        return array_intersect($intents, $metricIntents) !== [];
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
        [$intents, $wasLimited] = $this->prepareIntents($intents);

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

        if ($wasLimited && $body !== '') {
            $body = __('Bạn đang hỏi nhiều nhóm, mình tóm tắt nhanh các nhóm chính trước.')."\n\n".$body;
        }

        if ($body !== '') {
            $body = $this->appendNotice($body, $intents);
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
            return __(':greeting :name! Mình là trợ lý MLHUB AI của bạn.', [
                'greeting' => $greeting,
                'name' => $name,
            ]);
        }

        return __(':greeting! Mình là trợ lý MLHUB AI của bạn.', [
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
            ? __(':greeting :name! Mình là trợ lý MLHUB AI của bạn. Bây giờ là :time, :date.', [
                'greeting' => $greeting,
                'name' => $name,
                'time' => $now->format('H:i'),
                'date' => format_date_locale($now),
            ])
            : __(':greeting! Mình là trợ lý MLHUB AI của bạn. Bây giờ là :time, :date.', [
                'greeting' => $greeting,
                'time' => $now->format('H:i'),
                'date' => format_date_locale($now),
            ]);

        $help = __('Hôm nay mình có thể báo cáo tình hình kinh doanh, chiến dịch nổi bật, lượt quét QR, đặt lịch, mã ưu đãi, khách tiềm năng, đánh giá, tín dụng AI và gợi ý việc nên làm tiếp theo.');

        $ask = __('Bạn có thể hỏi: :examples.', [
            'examples' => __('báo cáo sáng nay, chiến dịch hiệu quả nhất, đặt lịch mới, mã ưu đãi đang tốt, hoặc AI Cơ bản (Basic AI) có tốn tín dụng AI không'),
        ]);

        return $hello.' '.$help."\n\n".$ask;
    }

    protected function timeGreeting(CarbonImmutable $now): string
    {
        $hour = (int) $now->format('H');

        return match (true) {
            $hour >= 5 && $hour <= 10 => __('Chào buổi sáng'),
            $hour >= 11 && $hour <= 12 => __('Chào buổi trưa'),
            $hour >= 13 && $hour <= 17 => __('Chào buổi chiều'),
            $hour >= 18 && $hour <= 21 => __('Chào buổi tối'),
            default => __('Xin chào'),
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
            'onboarding' => $this->composeOnboarding($context),
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
        return __('AI Cơ bản (Basic AI) đang trả lời bằng dữ liệu nội bộ và ma trận từ khóa MLHUB, nên không gọi OpenAI và không tốn token/tín dụng AI. AI Nâng cao (Advanced AI) chỉ dùng provider khi bạn bật, có API key và còn tín dụng AI. Bạn có thể hỏi về báo cáo hôm nay, chiến dịch, QR, đặt lịch, mã ưu đãi, khách tiềm năng, đánh giá, tín dụng AI, giới hạn gói và việc nên làm tiếp theo.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeDailyBriefing(array $context): string
    {
        $metrics = (array) ($context['metrics'] ?? []);
        $parts = [
            __('Sáng nay: :businesses cơ sở kinh doanh, :campaigns chiến dịch đang chạy, :visits lượt truy cập, :leads khách tiềm năng, :bookings lượt đặt lịch, :coupons lượt nhận mã ưu đãi, tỉ lệ chuyển đổi :rate.', [
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
            return __('Chưa có bảng xếp hạng chiến dịch. Hãy xuất bản một chiến dịch và chia sẻ QR để MLHUB bắt đầu đo lượt quét, chuyển đổi và chiến dịch tốt nhất.');
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

        return __('QR đang có :total lượt truy cập tổng, tuần này thêm :week_scans lượt quét. Từ các lượt đó ghi nhận :week_leads khách tiềm năng và :week_bookings lượt đặt lịch.', [
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
        return $this->composeReviews($context).' '.__('Nếu cần hành động nhanh, ưu tiên trả lời đánh giá đang chờ và đặt QR công cụ xin đánh giá ở điểm khách vừa hoàn tất dịch vụ.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeBookings(array $context): string
    {
        $metrics = (array) ($context['metrics'] ?? []);
        $weekly = (array) ($context['weekly_signals'] ?? []);

        return __('Đặt lịch hiện có :total lượt, riêng tuần này ghi nhận :week_count lịch hẹn. Nếu muốn tăng thêm, hãy đặt liên kết đặt lịch ở QR chính và nhắc khách chọn khung giờ ngay sau khi xem ưu đãi.', [
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

        return __('Mã ưu đãi có :total lượt nhận mã, tuần này thêm :week_count lượt nhận mới. Ưu tiên kiểm tra chiến dịch ưu đãi đang kéo lượt quét tốt nhất rồi nhân bản cho khung giờ cao điểm.', [
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

        return __('Form góp ý hiện có :total phản hồi, trong đó :needs_reply đánh giá tích cực đang chờ trả lời. Nên xử lý phản hồi mới nhất trước, đặc biệt các góp ý có điểm đánh giá thấp hoặc mô tả vấn đề cụ thể.', [
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

        return __('Khách tiềm năng hiện có :total, tuần này thêm :week_count khách tiềm năng mới. Hãy gọi hoặc nhắn nhóm khách mới trước, sau đó xem nguồn chiến dịch để biết QR/form nào đang kéo khách tốt nhất.', [
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

        return __('Tỉ lệ chuyển đổi hiện tại là :rate từ :visits lượt truy cập, gồm :leads khách tiềm năng, :bookings lượt đặt lịch, :coupons lượt nhận mã ưu đãi và :feedback phản hồi góp ý. Nếu muốn tăng nhanh, ưu tiên chiến dịch có lượt quét cao nhưng chuyển đổi thấp.', [
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
        return __('AI Cơ bản (Basic AI) của /portal/chatmlhubai không dùng token OpenAI và không trừ tín dụng AI vì chỉ dùng ngữ cảnh nội bộ, từ khóa và câu trả lời dựng sẵn. Tín dụng AI chủ yếu liên quan AI Nâng cao (Advanced AI), AI Studio, provider API hoặc các tác vụ sinh nội dung có tính phí.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composePlanLimits(array $context): string
    {
        return __('AI Cơ bản (Basic AI) hiện chưa có số liệu giới hạn gói chi tiết trong ngữ cảnh, nên chưa tự khẳng định giới hạn từng tính năng. Bạn nên kiểm tra gói dịch vụ hoặc lịch sử tín dụng AI; P1 có thể bổ sung ảnh chụp gói đang dùng để trợ lý trả lời chính xác hơn.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeOnboarding(array $context): string
    {
        $hints = (array) ($context['onboarding'] ?? []);

        $steps = [
            __('Bước 1: Tạo cơ sở kinh doanh đầu tiên để MLHUB có hồ sơ chính cho QR, trang đích và dữ liệu khách.'),
            __('Bước 2: Tạo một chiến dịch đầu tiên, thường nên bắt đầu với công cụ xin đánh giá, form khách tiềm năng hoặc mã ưu đãi tùy mục tiêu.'),
            __('Bước 3: Xuất bản chiến dịch rồi chia sẻ/in QR ở quầy, hóa đơn, tin nhắn hoặc kênh xã hội để bắt đầu có dữ liệu.'),
        ];

        if (in_array('boost_reviews', $hints, true)) {
            $steps[] = __('Bước 4: Bật công cụ xin đánh giá để xin đánh giá sau khi khách hoàn tất dịch vụ.');
        }

        return implode(' ', $steps);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeBusinessLocations(array $context): string
    {
        $businesses = (int) data_get($context, 'business_list.count', 0);

        return __('Địa điểm dùng để quản lý chi nhánh hoặc điểm bán vật lý theo cơ sở kinh doanh. Hiện AI Cơ bản (Basic AI) đang thấy :count cơ sở kinh doanh; để quản lý địa điểm hoặc QR địa điểm, hãy mở màn hình Địa điểm hoặc vào từng cơ sở kinh doanh. Trợ lý không tự dựng liên kết QR/trang công khai nếu ngữ cảnh chưa có mã định danh cụ thể.', [
            'count' => format_number_locale($businesses),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeCustomers(array $context): string
    {
        $customers = (array) ($context['customers'] ?? []);

        return __('Khách hàng là nơi xem danh bạ và dữ liệu khách. Tuần này có :new khách mới, :delta so với tuần trước. Nếu cần phân nhóm, gắn nhãn hoặc tạo việc chăm sóc lại, hãy dùng khách hàng trong CRM hoặc nhóm khách hàng CRM.', [
            'new' => format_number_locale((int) ($customers['new_this_week'] ?? 0)),
            'delta' => format_number_locale((int) ($customers['delta'] ?? 0)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeLandingPages(array $context): string
    {
        return __('Trang đích dùng để tạo trang chiến dịch hoặc trang công khai có form và lời kêu gọi hành động. AI Cơ bản (Basic AI) chỉ hướng dẫn nơi mở và cách dùng; liên kết công khai chỉ nên copy khi trang đích đã có mã định danh trong dữ liệu, nên trợ lý không tự tạo liên kết động khi ngữ cảnh chưa có mã định danh.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeMarketingTemplates(array $context): string
    {
        return __('Mẫu marketing là thư viện mẫu để tạo nhanh nội dung hoặc chiến dịch theo mục tiêu. Dùng mẫu có sẵn khi bạn muốn khởi đầu nhanh; dùng AI Studio khi muốn sinh hoặc chỉnh nội dung bằng AI và có thể cần tín dụng AI.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeCrmSegments(array $context): string
    {
        return __('CRM giúp quản lý khách sâu hơn: khách hàng, nhóm khách hàng, nhãn, việc cần làm, tự động hóa và báo cáo. Nếu muốn chăm sóc lại khách cũ, hãy tạo nhóm khách hàng trước, gắn nhãn phù hợp, rồi dùng việc cần làm hoặc tự động hóa để theo dõi chăm sóc tiếp.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeGoogleBusiness(array $context): string
    {
        return __('Google Business dùng để kết nối hồ sơ doanh nghiệp, địa điểm, bài đăng, đánh giá và chỉ số. AI Cơ bản (Basic AI) chưa tự khẳng định trạng thái kết nối nếu ngữ cảnh không có ảnh chụp Google; hãy mở Google Business để kiểm tra OAuth, địa điểm và dữ liệu đồng bộ.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeGoogleReviews(array $context): string
    {
        $reviews = (array) ($context['reviews'] ?? []);

        return __('Đánh giá Google liên quan đồng bộ và trả lời đánh giá trên Google Business. Trong ngữ cảnh nội bộ tuần này đang có :count đánh giá và :needs_reply đánh giá cần trả lời; để xử lý đánh giá Google hoặc trả lời tự động, hãy mở Google Business.', [
            'count' => format_number_locale((int) ($reviews['count'] ?? 0)),
            'needs_reply' => format_number_locale((int) ($reviews['needs_reply'] ?? 0)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeAiStudio(array $context): string
    {
        return __('AI Studio là khu vực tạo chiến dịch, xem lịch sử câu lệnh, viết trả lời đánh giá và cài đặt AI. AI Cơ bản (Basic AI) trong /portal/chatmlhubai không tốn tín dụng AI; các tác vụ sinh nội dung trong AI Studio hoặc AI Nâng cao (Advanced AI) có thể dùng tín dụng AI tùy cấu hình gói.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeAiContentWriter(array $context): string
    {
        return __('AI Content Writer hỗ trợ viết chú thích bài đăng, bài quảng cáo, lời kêu gọi hành động, nội dung Facebook hoặc tin nhắn nhắc khách. Nếu chỉ hỏi hướng dẫn tại đây thì AI Cơ bản (Basic AI) không tốn tín dụng AI; khi chạy tác vụ sinh nội dung trong AI Content, hệ thống có thể tính tín dụng AI theo gói.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeBilling(array $context): string
    {
        return __('Thanh toán là nơi xem gói đang dùng, hóa đơn và lịch sử thanh toán. Muốn nâng cấp thì mở gói dịch vụ; muốn tải hóa đơn thì mở hóa đơn; muốn xem tín dụng AI đã dùng thì mở lịch sử tín dụng AI.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeTeams(array $context): string
    {
        return __('Đội ngũ giúp quản lý không gian làm việc, thành viên và phân quyền. Khi mời thêm người, hãy kiểm tra vai trò/quyền trước để tránh cho nhân sự truy cập nhầm dữ liệu hoặc công cụ thanh toán.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeSupport(array $context): string
    {
        return __('Hỗ trợ là nơi gửi và theo dõi phiếu hỗ trợ. Khi báo lỗi, nên ghi rõ màn hình đang dùng, thao tác vừa làm, thời điểm xảy ra và ảnh chụp nếu có để đội hỗ trợ xử lý nhanh hơn.');
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
            return __('Tuần này chưa có khách hàng mới. Hãy xuất bản chiến dịch và chia sẻ mã QR để MLHUB bắt đầu ghi nhận khách tiềm năng và lượt đặt lịch.');
        }

        $deltaText = match (true) {
            $delta > 0 => __('tăng :count so với tuần trước', ['count' => format_number_locale($delta)]),
            $delta < 0 => __('giảm :count so với tuần trước', ['count' => format_number_locale(abs($delta))]),
            default => __('bằng tuần trước'),
        };

        $parts = array_values(array_filter([
            (int) ($signals['positive_reviews'] ?? 0) > 0
                ? __(':count từ công cụ xin đánh giá', ['count' => format_number_locale((int) $signals['positive_reviews'])])
                : null,
            (int) ($signals['bookings'] ?? 0) > 0
                ? __(':count từ trang đặt lịch', ['count' => format_number_locale((int) $signals['bookings'])])
                : null,
            (int) ($signals['leads'] ?? 0) > 0
                ? __(':count từ form khách tiềm năng', ['count' => format_number_locale((int) $signals['leads'])])
                : null,
        ]));

        $breakdown = $parts !== []
            ? implode(', ', $parts).'.'
            : __('Tiếp tục chia sẻ mã QR để tăng danh sách khách hàng.');

        return __('Tuần này: :count khách hàng mới (:delta). :breakdown', [
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
            return __('Bạn chưa có chiến dịch nào đã xuất bản. Hãy tạo chiến dịch xin đánh giá, đặt lịch, mã ưu đãi hoặc khách tiềm năng rồi xuất bản để bắt đầu theo dõi kết quả tại đây.');
        }

        $lines = collect($active)
            ->take(4)
            ->map(function (array $campaign): string {
                $label = $this->campaignTypeLabel((string) ($campaign['type'] ?? ''));

                return __(':name (:type): :visits lượt quét, :conversions chuyển đổi', [
                    'name' => (string) ($campaign['name'] ?? __('Chiến dịch')),
                    'type' => $label,
                    'visits' => format_number_locale((int) ($campaign['visits'] ?? 0)),
                    'conversions' => format_number_locale((int) ($campaign['conversions'] ?? 0)),
                ]);
            })
            ->implode(' ');

        return __(':count chiến dịch đang chạy. :details', [
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
            return __('Tuần này chưa có đánh giá mới. Hãy bật công cụ xin đánh giá và đặt QR ở nơi khách có thể quét sau khi trải nghiệm dịch vụ.');
        }

        $average = (float) ($reviews['average_rating'] ?? 0);
        $needsReply = (int) ($reviews['needs_reply'] ?? 0);
        $sentiment = $average >= 4.5
            ? __('Cảm nhận tích cực')
            : ($average >= 3.5 ? __('Cảm nhận nhìn chung tích cực') : __('Cảm nhận còn lẫn lộn, nên xem kỹ góp ý'));

        $replyNote = $needsReply > 0
            ? __(':count đánh giá vẫn cần trả lời.', ['count' => format_number_locale($needsReply)])
            : __('Tất cả đánh giá tích cực gần đây đã được trả lời.');

        return __('Trung bình :rating★ từ :count đánh giá mới. :sentiment. :reply', [
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
            return __('Phễu của bạn đang chạy. Việc nên làm tiếp theo: trả lời đánh giá đang chờ, gửi mã ưu đãi cuối tuần cho khách quay lại, và xem báo cáo để chọn chiến dịch hiệu quả nhất.');
        }

        $suggestions = [];

        if (in_array('create_business', $hints, true)) {
            $suggestions[] = __('Thêm hồ sơ cơ sở kinh doanh đầu tiên để chiến dịch và mã QR có nơi gắn dữ liệu.');
        }

        if (in_array('create_campaign', $hints, true)) {
            $suggestions[] = __('Tạo chiến dịch tăng trưởng đầu tiên, công cụ xin đánh giá thường là điểm bắt đầu nhanh nhất cho cửa hàng địa phương.');
        }

        if (in_array('publish_campaign', $hints, true)) {
            $suggestions[] = __('Xuất bản chiến dịch nháp để trang công khai và mã QR bắt đầu hoạt động.');
        }

        if (in_array('share_qr', $hints, true)) {
            $suggestions[] = __('In hoặc chia sẻ QR chiến dịch tại quầy, bàn hoặc hóa đơn để lượt truy cập bắt đầu đổ về.');
        }

        if (in_array('boost_reviews', $hints, true)) {
            $suggestions[] = __('Bật công cụ xin đánh giá để thu thập đánh giá Google tự động sau mỗi lượt khách.');
        }

        if ($suggestions === []) {
            $suggestions[] = __('Thử mã ưu đãi cuối tuần cho khách quay lại, MLHUB có thể nháp nội dung và trang đích trong AI Studio.');
        }

        return implode(' ', $suggestions);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeOverview(array $context): string
    {
        $metrics = (array) ($context['metrics'] ?? []);

        return __('Tóm tắt nhanh: :businesses cơ sở kinh doanh, :campaigns chiến dịch đang chạy, :visits lượt truy cập, :leads khách tiềm năng, :bookings lượt đặt lịch, :coupons lượt nhận mã ưu đãi, tỉ lệ chuyển đổi :rate.', [
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

        return __('Tổng lượt truy cập: :total. Tuần này: :week_scans lượt quét QR, :week_leads khách tiềm năng, :week_bookings lượt đặt lịch.', [
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
            return __('Bạn chưa có hồ sơ cơ sở kinh doanh nào. Hãy thêm cơ sở kinh doanh đầu tiên để chiến dịch và mã QR có nơi gắn dữ liệu.');
        }

        $shown = array_slice($names, 0, 5);
        $namesText = implode(', ', $shown);

        if ($count > count($shown)) {
            $namesText = __(':names and :count more', [
                'names' => $namesText,
                'count' => format_number_locale($count - count($shown)),
            ]);
        }

        return __('Bạn có :count cơ sở kinh doanh: :names.', [
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
        $name = (string) ($campaign['campaign_name'] ?? $campaign['name'] ?? __('Chiến dịch'));
        $type = $this->campaignTypeLabel((string) ($campaign['campaign_type'] ?? $campaign['type'] ?? ''));
        $visits = (int) ($campaign['visits'] ?? 0);
        $conversions = (int) ($campaign['conversions'] ?? 0);
        $rate = (float) ($campaign['conversion_rate'] ?? ($visits > 0 ? ($conversions / $visits) * 100 : 0));

        return __(':name (:type): :visits lượt truy cập, :conversions chuyển đổi, tỉ lệ chuyển đổi :rate.', [
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
        return __('Mình chưa bắt đúng ý câu hỏi. Bạn có thể hỏi về báo cáo hôm nay, chiến dịch nổi bật, lượt quét QR, đặt lịch, mã ưu đãi, khách tiềm năng, đánh giá, tín dụng AI, giới hạn gói hoặc việc nên làm tiếp theo.');
    }

    protected function campaignTypeLabel(string $type): string
    {
        return match ($type) {
            'review' => __('Công cụ xin đánh giá'),
            'booking' => __('Đặt lịch'),
            'coupon' => __('Mã ưu đãi'),
            'feedback' => __('Form góp ý'),
            'lead' => __('Form khách tiềm năng'),
            default => str($type)->headline()->toString(),
        };
    }
}
