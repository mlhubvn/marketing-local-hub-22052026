<?php

namespace Modules\CustomMLHUB\Support\MLHUBAIAssistant;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

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
            'industry_recommendation' => $this->industryActions($context),
            'conversion' => $this->scenario($context) === 'qr_high_scan_low_lead'
                ? [
                    ['portal.lead-forms', __('Mở form khách tiềm năng')],
                    ['portal.coupon-campaigns', __('Mở mã ưu đãi')],
                    ['portal.reports', __('Xem báo cáo')],
                ]
                : MLHUBAIKnowledgeBase::routeActions($intent),
            'feedback' => $this->scenario($context) === 'low_rating_recovery'
                ? [
                    ['portal.feedback-forms', __('Mở form góp ý')],
                    ['portal.crm.customers', __('Mở khách hàng trong CRM')],
                ]
                : MLHUBAIKnowledgeBase::routeActions($intent),
            'credits' => $this->scenario($context) === 'no_credit_campaign'
                ? [
                    ['portal.coupon-campaigns', __('Mở mã ưu đãi')],
                    ['portal.crm.customers', __('Mở khách hàng trong CRM')],
                    ['portal.marketing-templates', __('Mở mẫu marketing')],
                ]
                : MLHUBAIKnowledgeBase::routeActions($intent),
            'business_locations' => $this->scenario($context) === 'branch_performance'
                ? [
                    ['portal.locations', __('Mở địa điểm')],
                    ['portal.reports', __('Xem báo cáo')],
                    ['portal.qr-campaigns', __('Quản lý chiến dịch')],
                ]
                : MLHUBAIKnowledgeBase::routeActions($intent),
            'ai_content_writer' => ($handoff = MLHUBAIKnowledgeBase::detectStudioHandoff($this->normalizedQuestion($context))) === 'content_writing'
                ? MLHUBAIKnowledgeBase::studioHandoffRouteActions('content_writing')
                : MLHUBAIKnowledgeBase::routeActions('ai_content_writer'),
            'ai_studio' => ($handoff = MLHUBAIKnowledgeBase::detectStudioHandoff($this->normalizedQuestion($context))) !== null
                && $handoff !== 'content_writing'
                ? MLHUBAIKnowledgeBase::studioHandoffRouteActions($handoff)
                : MLHUBAIKnowledgeBase::routeActions('ai_studio'),
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
    protected function industryActions(array $context): array
    {
        if ($this->scenario($context) === 'google_to_booking') {
            return [
                ['portal.google-business', __('Mở Google Business')],
                ['portal.booking-pages', __('Mở trang đặt lịch')],
                ['portal.landing-pages', __('Mở trang đích')],
            ];
        }

        if ($this->scenario($context) === 'phone_collection') {
            return [
                ['portal.lead-forms', __('Mở form khách tiềm năng')],
                ['portal.crm.customers', __('Mở khách hàng trong CRM')],
                ['portal.coupon-campaigns', __('Mở mã ưu đãi')],
            ];
        }

        if ($this->scenario($context) === 'review_collection') {
            return [
                ['portal.booking-pages', __('Mở trang đặt lịch')],
                ['portal.review-booster', __('Mở công cụ xin đánh giá')],
                ['portal.coupon-campaigns', __('Mở mã ưu đãi')],
            ];
        }

        return MLHUBAIKnowledgeBase::industryGroupRouteActions($this->industryGroup($context));
    }

    /**
     * @param  array<string, mixed>  $context
     * @return list<array{0: string, 1: string}>
     */
    protected function nextStepActions(array $context): array
    {
        $hints = (array) ($context['onboarding'] ?? []);

        if (in_array('create_business', $hints, true)) {
            return [
                ['portal.businesses', __('Quản lý cơ sở kinh doanh')],
                ['portal.qr-campaigns', __('Quản lý chiến dịch')],
            ];
        }

        if (in_array('create_campaign', $hints, true) || in_array('publish_campaign', $hints, true)) {
            return [
                ['portal.businesses', __('Quản lý cơ sở kinh doanh')],
                ['portal.qr-campaigns', __('Quản lý chiến dịch')],
            ];
        }

        if (in_array('boost_reviews', $hints, true)) {
            return [['portal.review-booster', __('Mở công cụ xin đánh giá')]];
        }

        return [
            ['portal.businesses', __('Quản lý cơ sở kinh doanh')],
            ['portal.qr-campaigns', __('Quản lý chiến dịch')],
        ];
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

        $wasLimited = count($intents) > 4;

        return [array_slice($intents, 0, 4), $wasLimited];
    }

    /**
     * @param  list<string>  $intents
     */
    protected function appendNotice(string $body, array $intents, array $context): string
    {
        $notice = $this->usesAccountMetrics($intents, $context)
            ? __('Câu trả lời có sử dụng số liệu thực tế từ tài khoản của bạn.')
            : __('Câu trả lời dựa trên tri thức nội bộ và cấu trúc tính năng của MLHUB.');

        return trim($body)."\n\n".$notice;
    }

    /**
     * @param  list<string>  $intents
     */
    protected function usesAccountMetrics(array $intents, array $context): bool
    {
        if (in_array($this->scenario($context), [
            'branch_performance',
            'studio_handoff',
            'google_to_booking',
            'low_rating_recovery',
            'no_credit_campaign',
            'phone_collection',
            'qr_high_scan_low_lead',
            'review_collection',
        ], true)) {
            return false;
        }

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

        if (in_array('credits', $intents, true) && (bool) data_get($context, 'credits.available', false)) {
            return true;
        }

        if (in_array('plan_limits', $intents, true) && (bool) data_get($context, 'plan.available', false)) {
            return true;
        }

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
            $body = $this->appendNotice($body, $intents, $context);
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
    protected function question(array $context): string
    {
        return (string) data_get($context, 'request.question', '');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function normalizedQuestion(array $context): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', Str::ascii(mb_strtolower($this->question($context)))));
    }

    /**
     * @param  list<string>  $needles
     */
    protected function containsAnyQuestion(array $context, array $needles): bool
    {
        $question = $this->normalizedQuestion($context);

        foreach ($needles as $needle) {
            if (str_contains($question, Str::ascii(mb_strtolower($needle)))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function industryGroup(array $context): string
    {
        foreach (MLHUBAIKnowledgeBase::industryGroupDetectionOrder() as $group) {
            if ($this->containsAnyQuestion($context, MLHUBAIKnowledgeBase::industryGroupAliases($group))) {
                return $group;
            }
        }

        return 'other_needs_classification';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function scenario(array $context): string
    {
        if (MLHUBAIKnowledgeBase::detectStudioHandoff($this->normalizedQuestion($context)) !== null) {
            return 'studio_handoff';
        }

        return match (true) {
            $this->containsAnyQuestion($context, ['khong muon ton', 'khong ton diem', 'khong ton tin dung', 'khong ton credit']) => 'no_credit_campaign',
            $this->containsAnyQuestion($context, ['danh gia 1 sao', 'danh gia 2 sao', 'danh gia 3 sao', 'review xau', 'danh gia thap', 'rating thap', 'khach khong hai long']) => 'low_rating_recovery',
            $this->containsAnyQuestion($context, ['google nhieu nguoi xem', 'tim tren google', 'it dat ban', 'it dat lich']) => 'google_to_booking',
            $this->containsAnyQuestion($context, ['qr co nhieu luot quet', 'nhieu luot quet nhung it', 'it khach de lai thong tin', 'it lead']) => 'qr_high_scan_low_lead',
            $this->containsAnyQuestion($context, ['chi nhanh nao keo khach', 'chi nhanh nao tot', '3 chi nhanh', 'nhieu chi nhanh']) => 'branch_performance',
            $this->containsAnyQuestion($context, ['de lai so dien thoai', 'lay so dien thoai', 'thu so dien thoai', 'de lai thong tin']) => 'phone_collection',
            $this->containsAnyQuestion($context, ['khong de lai danh gia', 'xin danh gia', 'lay danh gia']) => 'review_collection',
            $this->containsAnyQuestion($context, ['khach quay lai', 'khach cu quay lai', 'cuoi tuan', 'uu dai quay lai']) => 'returning_customers',
            default => 'general',
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function compose(string $intent, array $context): string
    {
        return match ($intent) {
            'help_using_mlhubai' => $this->composeHelpUsingMLHUBAI($context),
            'industry_recommendation' => $this->composeIndustryRecommendation($context),
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
    protected function composeIndustryRecommendation(array $context): string
    {
        if (trim($this->question($context)) === '') {
            return __('Với quán cà phê hoặc trà sữa, nên bắt đầu bằng 3 việc: tạo cơ sở kinh doanh, đặt QR xin đánh giá tại quầy, và tạo mã ưu đãi để kéo khách quay lại. Nếu muốn lấy số điện thoại khách, dùng thêm form khách tiềm năng hoặc trang đích có form tư vấn.');
        }

        $scenario = $this->scenario($context);

        if ($scenario === 'review_collection') {
            return __('Với :industry, nên cài quy trình sau trải nghiệm: nhắc khách đặt lịch lần sau, gửi Review Booster ngay khi dịch vụ hoàn tất, lưu khách vào CRM để chăm sóc lại và tặng mã ưu đãi quay lại cho nhóm chưa phản hồi. Đừng chỉ xin đánh giá công khai; hãy có form góp ý riêng để xử lý khách chưa hài lòng trước.', [
                'industry' => $this->industryLabel($context),
            ]);
        }

        if ($scenario === 'google_to_booking') {
            return __('Với :industry, hãy tối ưu Google Business trước để khách thấy đúng giờ mở cửa, ảnh và liên kết hành động. Sau đó gắn nút đặt bàn/đặt lịch về trang đặt lịch hoặc trang đích, dùng Review Booster và form góp ý riêng để tăng niềm tin trước khi khách quyết định. Theo dõi báo cáo để biết Google đang kéo lượt xem nhưng rơi ở bước nào.', [
                'industry' => $this->industryLabel($context),
            ]);
        }

        if ($scenario === 'phone_collection') {
            return __('Với :industry, nên dùng form khách tiềm năng làm điểm thu số điện thoại, kèm một ưu đãi hoặc tư vấn rõ ràng để khách có lý do để lại thông tin. Sau đó đưa khách vào CRM, gắn nhãn nguồn chiến dịch và tạo việc chăm sóc lại sau 7 ngày; nếu bán tại quầy, đặt thêm QR dẫn về form này.', [
                'industry' => $this->industryLabel($context),
            ]);
        }

        return match ($this->industryGroup($context)) {
            'food_beverage' => __('Với Food & Beverage (quán cà phê, trà sữa, nhà hàng, quán ăn), nên ưu tiên chiến dịch QR tại quầy/bàn, Review Booster, mã ưu đãi quay lại và Google Business nếu khách tìm trên bản đồ. Bước đầu: tạo cơ sở kinh doanh, đặt QR review, tạo mã ưu đãi; nhà hàng thêm trang đặt bàn hoặc trang đích. Chat gợi ý quy trình — caption/menu dài hãy sang mẫu marketing hoặc AI Content.'),
            'retail_goods' => __('Với bán lẻ (tạp hóa, thời trang, mỹ phẩm lẻ), nên dùng form khách tiềm năng lấy số điện thoại, mã ưu đãi kéo mua lại, CRM phân nhóm khách và QR tại quầy. Bắt đầu bằng một ưu đãi đơn giản, theo dõi báo cáo để biết khách nào quay lại; mẫu tin nhắn dùng Marketing Templates thay vì viết dài trong chat.'),
            'beauty_personal_care' => __('Với spa, salon, nail hoặc gội đầu dưỡng sinh, nên bắt đầu bằng trang đặt lịch, Review Booster sau dịch vụ, CRM nhắc lịch chăm sóc lại và mã ưu đãi quay lại. Mỗi khách sau khi hoàn tất dịch vụ nên được lưu vào CRM, hẹn lần tiếp theo và chỉ xin đánh giá khi trải nghiệm ổn.'),
            'tourism_hospitality_experience' => __('Với khách sạn, homestay hoặc trải nghiệm du lịch, nên ưu tiên Google Business, trang đích giới thiệu dịch vụ, trang đặt lịch hoặc form khách tiềm năng và Review Booster sau trải nghiệm. Đừng tự tạo link công khai nếu chưa có slug; hãy mở module tương ứng rồi copy link thật từ MLHUB.'),
            'health_dental_fitness' => __('Với phòng khám, nha khoa, gym hoặc yoga, nên dùng đặt lịch, form tư vấn, góp ý riêng và CRM nhắc lịch. Chat chỉ gợi ý quy trình vận hành — không thay tư vấn y khoa, không hứa chữa khỏi hay kết quả điều trị; ưu tiên phản hồi trung tính và đo hiệu quả bằng báo cáo.'),
            'technical_repair_maintenance' => __('Với sửa chữa, điện lạnh, rửa xe hoặc giặt ủi, nên dùng form khách tiềm năng nhận báo giá, trang đặt lịch chốt khung giờ, Google Business tăng tin cậy và Review Booster sau khi hoàn tất. CRM giúp nhắc gọi lại và chăm sóc khách cũ theo từng yêu cầu dịch vụ.'),
            'education_training_coaching' => __('Với trung tâm, lớp học hoặc đào tạo, nên dùng trang đích giới thiệu khóa học, form khách tiềm năng nhận tư vấn, CRM chăm sóc phụ huynh/học viên và mẫu marketing cho tin nhắn tuyển sinh. Chat gợi ý quy trình; bài viết tuyển sinh dài nên sang AI Content hoặc mẫu marketing.'),
            'wholesale_distribution' => __('Với đại lý/phân phối/bán sỉ, nên dùng form khách tiềm năng B2B, CRM phân nhóm đại lý theo vùng và trang đích giới thiệu chính sách giá sỉ. MLHUB giúp thu yêu cầu báo giá và theo dõi pipeline — không thay ERP tồn kho. Bước đầu: landing chính sách, form đăng ký đại lý, gắn nhãn CRM.'),
            'professional_b2b_services' => __('Với agency, tư vấn, kế toán, luật hoặc dịch vụ B2B, nên dùng trang đích trình bày dịch vụ, form khách tiềm năng nhận brief, CRM pipeline theo từng khách và Google Business/review nếu có điểm giao dịch rõ. Ưu tiên đo nguồn lead và tạo việc chăm sóc tiếp theo.'),
            'home_construction_interior' => __('Với nội thất, xây dựng hoặc sửa nhà, nên dùng form báo giá, trang đích dự án/dịch vụ, CRM chăm sóc từng hồ sơ và Review Booster sau bàn giao. Bắt đầu bằng landing mô tả quy trình làm việc và form thu nhu cầu — chat không thay hợp đồng hay bản vẽ kỹ thuật.'),
            'transport_delivery_logistics' => __('Với vận tải, giao hàng hoặc logistics, nên dùng form báo giá/route, trang đích giới thiệu dịch vụ, CRM khách doanh nghiệp và báo cáo theo nguồn lead. Bước đầu: landing dịch vụ, form yêu cầu báo giá, phân nhóm CRM theo loại hàng hoặc khu vực.'),
            'real_estate_rental_property' => __('Với bất động sản, cho thuê hoặc môi giới, nên dùng trang đích từng sản phẩm, form khách tiềm năng tư vấn, CRM pipeline theo nhu cầu và Google Business nếu có văn phòng/điểm giao dịch. Bắt đầu bằng landing rõ tiêu chí, form thu số liệu khách quan tâm — không hứa lợi nhuận hay pháp lý trong chat.'),
            'digital_creator_online_business' => __('Với creator, livestream, ecommerce hoặc khóa học online, nên dùng trang đích, form thu lead/community, CRM theo dõi khách và mẫu marketing hoặc AI Content khi cần viết bài. Chat gợi ý công cụ — không soạn caption/script dài tại đây; mở AI Content hoặc AI Studio để tạo nội dung.'),
            'small_manufacturing_processing_ocop' => __('Với xưởng sản xuất, gia công hoặc OCOP, nên dùng trang đích sản phẩm, form khách tiềm năng tìm đại lý, Google Business và CRM phân phối. Bước đầu: landing giới thiệu sản phẩm/chứng nhận, form đăng ký đại lý hoặc báo giá sỉ, theo dõi nguồn lead trong báo cáo.'),
            'agriculture_fisheries_local_supply' => __('Với nông sản, thủy sản hoặc nhà vườn cung cấp hàng, nên dùng trang đích nguồn hàng, form báo giá/đặt sỉ, CRM khách sỉ và Google Business nếu có điểm bán cố định. Bắt đầu bằng landing mô tả sản phẩm theo mùa và form thu đơn hàng sỉ — chat không thay hợp đồng nông sản.'),
            'culture_entertainment_sports_community' => __('Với karaoke, sân thể thao, câu lạc bộ hoặc sự kiện, nên dùng trang đặt lịch/đăng ký, trang đích sự kiện, mã ưu đãi/voucher và Review Booster sau trải nghiệm. Bước đầu: landing sự kiện hoặc bảng giá dịch vụ, form thu thông tin đăng ký, QR tại quầy/check-in.'),
            'organization_association_public_community' => __('Với hiệp hội, cộng đồng hoặc chương trình chính quyền, nên dùng trang đích chương trình, form đăng ký tham gia, CRM danh sách thành viên và báo cáo theo dõi nguồn đăng ký. Bắt đầu bằng landing mô tả mục tiêu chương trình và form thu thông tin — chat không thay quy trình hành chính chính thức.'),
            default => __('Nếu chưa rõ ngành hoặc kinh doanh nhiều lĩnh vực, hãy cho biết thêm ngành cụ thể hoặc cập nhật nhóm ngành trong hồ sơ cơ sở kinh doanh. Tạm thời bắt đầu từ cơ sở kinh doanh, một chiến dịch QR, form khách tiềm năng và báo cáo để có số liệu trước khi mở rộng module chuyên sâu.'),
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function industryLabel(array $context): string
    {
        return match ($this->industryGroup($context)) {
            'food_beverage' => __('F&B'),
            'retail_goods' => __('bán lẻ'),
            'beauty_personal_care' => __('spa/salon'),
            'tourism_hospitality_experience' => __('du lịch/lưu trú'),
            'health_dental_fitness' => __('sức khỏe/thể thao'),
            'technical_repair_maintenance' => __('sửa chữa/bảo trì'),
            'education_training_coaching' => __('giáo dục/đào tạo'),
            'wholesale_distribution' => __('phân phối/bán sỉ'),
            'professional_b2b_services' => __('dịch vụ B2B'),
            'home_construction_interior' => __('xây dựng/nội thất'),
            'transport_delivery_logistics' => __('vận tải/giao hàng'),
            'real_estate_rental_property' => __('bất động sản'),
            'digital_creator_online_business' => __('kinh doanh online/creator'),
            'small_manufacturing_processing_ocop' => __('sản xuất/OCOP'),
            'agriculture_fisheries_local_supply' => __('nông/thủy sản'),
            'culture_entertainment_sports_community' => __('giải trí/thể thao'),
            'organization_association_public_community' => __('hiệp hội/cộng đồng'),
            default => __('cơ sở kinh doanh của bạn'),
        };
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

        return __('QR đang có :total lượt truy cập tổng, tuần này thêm :week_scans lượt quét. Hãy đặt mã QR ở quầy, hóa đơn, bàn hoặc tin nhắn chăm sóc khách, rồi mở báo cáo để xem lượt quét, khách tiềm năng và lượt đặt lịch từ QR.', [
            'total' => format_number_locale((int) ($metrics['visits'] ?? 0)),
            'week_scans' => format_number_locale((int) ($weekly['qr_scans'] ?? 0)),
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
        if ($this->scenario($context) === 'low_rating_recovery') {
            return __('Với khách đánh giá 1-3 sao, hãy xử lý theo luồng phục hồi riêng: mở form góp ý hoặc phản hồi riêng, ghi rõ vấn đề khách không hài lòng, tạo việc cần làm trong CRM để nhân viên gọi lại và chỉ xin đánh giá công khai sau khi đã hỗ trợ ổn. Không nên đẩy khách chưa hài lòng thẳng sang review công khai.');
        }

        $metrics = (array) ($context['metrics'] ?? []);
        $reviews = (array) ($context['reviews'] ?? []);

        return __('Form góp ý hiện có :total phản hồi, trong đó :needs_reply đánh giá tích cực đang chờ trả lời. Nếu khách đánh giá thấp, hãy xử lý riêng: xin lỗi, hỏi rõ vấn đề, tạo việc chăm sóc lại trong CRM và chỉ xin đánh giá công khai khi khách đã được hỗ trợ ổn.', [
            'total' => format_number_locale((int) ($metrics['feedback'] ?? 0)),
            'needs_reply' => format_number_locale((int) ($reviews['needs_reply'] ?? 0)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeLeads(array $context): string
    {
        if ($this->scenario($context) === 'phone_collection') {
            return __('Muốn lấy số điện thoại khách, hãy dùng form khách tiềm năng hoặc trang đích có form tư vấn, kèm một offer rõ như mã ưu đãi, tư vấn miễn phí hoặc nhắc lịch. Sau khi khách gửi form, đưa vào CRM, gắn nhãn nguồn chiến dịch và tạo việc chăm sóc lại để nhân viên gọi đúng thời điểm.');
        }

        $metrics = (array) ($context['metrics'] ?? []);
        $weekly = (array) ($context['weekly_signals'] ?? []);

        return __('Khách tiềm năng hiện có :total, tuần này thêm :week_count khách tiềm năng mới. Nếu muốn khách để lại số điện thoại, hãy dùng form khách tiềm năng hoặc trang đích có form tư vấn. Hãy gọi lại nhóm khách mới trước, rồi xem nguồn chiến dịch để biết QR/form nào đang kéo khách tốt nhất.', [
            'total' => format_number_locale((int) ($metrics['leads'] ?? 0)),
            'week_count' => format_number_locale((int) ($weekly['leads'] ?? 0)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeConversion(array $context): string
    {
        if ($this->scenario($context) === 'qr_high_scan_low_lead') {
            return __('QR có nhiều lượt quét nhưng ít khách để lại thông tin thì nên sửa phần chuyển đổi trước: làm form ngắn hơn, đặt CTA rõ hơn, thêm ưu đãi đủ hấp dẫn và kiểm tra báo cáo theo từng chiến dịch. Mục tiêu không phải chỉ tăng lượt quét QR, mà là biến lượt quét thành form, khách tiềm năng hoặc đặt lịch.');
        }

        $metrics = (array) ($context['metrics'] ?? []);

        return __('Muốn biết kênh nào mang khách tốt nhất, hãy mở báo cáo và xem nguồn/chiến dịch có lượt quét, khách tiềm năng và chuyển đổi tốt. Hiện tỉ lệ chuyển đổi là :rate từ :visits lượt truy cập, gồm :leads khách tiềm năng, :bookings lượt đặt lịch, :coupons lượt nhận mã ưu đãi và :feedback phản hồi góp ý.', [
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
        if ($this->scenario($context) === 'no_credit_campaign') {
            return __('Bạn vẫn có thể tạo chiến dịch kéo khách cũ quay lại mà không tốn tín dụng AI: dùng AI Cơ bản để hỏi quy trình, chọn mẫu marketing có sẵn, tạo mã ưu đãi quay lại, lọc khách cũ trong CRM rồi gửi thủ công hoặc theo quy trình hiện có. AI Cơ bản không trừ tín dụng AI; AI Studio/AI Nâng cao chỉ nên dùng khi bạn muốn sinh nội dung mới bằng provider.');
        }

        $credits = (array) ($context['credits'] ?? []);

        if (! (bool) ($credits['available'] ?? false)) {
            return __('Snapshot hiện tại chưa có dữ liệu tín dụng, nên mình không đoán số dư. AI Cơ bản vẫn không trừ tín dụng AI vì chỉ dùng tri thức nội bộ; tín dụng AI chủ yếu dùng cho AI Nâng cao (Advanced AI), AI Studio hoặc tác vụ có gọi provider như OpenAI/Gemini.');
        }

        $unlimited = (bool) ($credits['unlimited'] ?? false);
        $remaining = $unlimited
            ? __('không giới hạn')
            : $this->formatNullableNumber($credits['remaining'] ?? null);
        $limit = $unlimited
            ? __('không giới hạn')
            : $this->formatNullableNumber($credits['limit'] ?? null);
        $used = format_number_locale((int) ($credits['used'] ?? 0));
        $topup = (int) ($credits['topup_remaining'] ?? 0);
        $cost = $credits['costs']['mlhub_ai_chat'] ?? null;

        $parts = [
            __('Tín dụng AI hiện còn :remaining, đã dùng :used trên hạn mức :limit.', [
                'remaining' => $remaining,
                'used' => $used,
                'limit' => $limit,
            ]),
        ];

        if ($topup > 0) {
            $parts[] = __('Số dư nạp thêm còn :topup.', [
                'topup' => format_number_locale($topup),
            ]);
        }

        if (is_numeric($cost)) {
            $parts[] = __('Nếu bật AI Nâng cao (Advanced AI), mỗi câu trả lời MLHUB AI Chat dự kiến dùng :cost tín dụng AI theo gói hiện tại.', [
                'cost' => format_number_locale((int) $cost),
            ]);
        }

        $parts[] = __('AI Cơ bản vẫn không trừ tín dụng AI.');

        return implode(' ', $parts);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composePlanLimits(array $context): string
    {
        $plan = (array) ($context['plan'] ?? []);

        if (! (bool) ($plan['available'] ?? false)) {
            return __('Snapshot hiện tại chưa có dữ liệu hạn mức, nên mình không đoán quota cho từng tính năng. Hãy mở gói dịch vụ để kiểm tra hạn mức chính thức trước khi tạo thêm chiến dịch, QR, trang đích hoặc mẫu marketing.');
        }

        $name = trim((string) ($plan['name'] ?? ''));
        $status = trim((string) ($plan['status'] ?? ''));
        $usageLines = $this->planUsageLines((array) ($plan['usage'] ?? []));

        $intro = __('Gói hiện tại: :name:type.', [
            'name' => $name !== '' ? $name : __('chưa rõ tên gói'),
            'type' => $status !== '' ? ' ('.$status.')' : '',
        ]);

        if ($usageLines === []) {
            return $intro.' '.__('Snapshot đã nhận diện được gói, nhưng chưa có chi tiết mức đã dùng/hạn mức để khẳng định quota từng tính năng.');
        }

        return $intro.' '.__('Một vài hạn mức nổi bật: :items', [
            'items' => implode('; ', $usageLines),
        ]);
    }

    protected function formatNullableNumber(mixed $value): string
    {
        return is_numeric($value)
            ? format_number_locale((int) $value)
            : __('chưa rõ');
    }

    /**
     * @param  array<string, array<string, mixed>>  $usage
     * @return list<string>
     */
    protected function planUsageLines(array $usage): array
    {
        return collect($usage)
            ->filter(fn (array $row): bool => trim((string) ($row['label'] ?? '')) !== '')
            ->sortByDesc(fn (array $row): int => (int) ($row['percent'] ?? 0))
            ->take(3)
            ->map(function (array $row): string {
                $label = (string) ($row['label'] ?? '');
                $used = format_number_locale((int) ($row['used'] ?? 0));
                $unlimited = (bool) ($row['unlimited'] ?? false);
                $limit = $unlimited ? __('không giới hạn') : $this->formatNullableNumber($row['limit'] ?? null);
                $remaining = $unlimited ? __('không giới hạn') : $this->formatNullableNumber($row['remaining'] ?? null);

                return __(':label: :used/:limit đã dùng, còn :remaining', [
                    'label' => $label,
                    'used' => $used,
                    'limit' => $limit,
                    'remaining' => $remaining,
                ]);
            })
            ->values()
            ->all();
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
        if ($this->scenario($context) === 'branch_performance') {
            return __('Với nhiều chi nhánh, hãy quản lý từng chi nhánh trong Địa điểm, gắn chiến dịch/QR theo đúng điểm bán rồi mở báo cáo để so sánh lượt quét, khách tiềm năng, đặt lịch và chuyển đổi theo từng chi nhánh. Ưu tiên nhân bản chiến dịch đang kéo khách tốt nhất sang chi nhánh yếu hơn.');
        }

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
        $handoff = MLHUBAIKnowledgeBase::detectStudioHandoff($this->normalizedQuestion($context));

        if ($handoff !== null && $handoff !== 'content_writing') {
            return MLHUBAIKnowledgeBase::studioHandoffMessage($handoff);
        }

        return __('AI Studio là khu vực tạo chiến dịch, xem lịch sử câu lệnh, viết trả lời đánh giá và cài đặt AI. AI Cơ bản (Basic AI) trong /portal/chatmlhubai không tốn tín dụng AI; các tác vụ sinh nội dung trong AI Studio hoặc AI Nâng cao (Advanced AI) có thể dùng tín dụng AI tùy cấu hình gói.');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function composeAiContentWriter(array $context): string
    {
        $handoff = MLHUBAIKnowledgeBase::detectStudioHandoff($this->normalizedQuestion($context));

        if ($handoff === 'content_writing') {
            return MLHUBAIKnowledgeBase::studioHandoffMessage('content_writing');
        }

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
