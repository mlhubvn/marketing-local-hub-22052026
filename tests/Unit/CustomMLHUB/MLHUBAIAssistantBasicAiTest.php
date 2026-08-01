<?php

use Modules\AdminSettings\Support\OptionStore;
use Modules\CustomMLHUB\Support\MLHUBAIAssistant\MLHUBAIAssistantService;
use Modules\CustomMLHUB\Support\MLHUBAIAssistant\MLHUBAIContextBuilder;
use Modules\CustomMLHUB\Support\MLHUBAIAssistant\MLHUBAIIntentResolver;
use Modules\CustomMLHUB\Support\MLHUBAIAssistant\MLHUBAIKnowledgeBase;
use Modules\CustomMLHUB\Support\MLHUBAIAssistant\MLHUBAIResponseComposer;

function mlhubAssistantContext(array $overrides = []): array
{
    return array_replace_recursive([
        'generated_at' => '2026-06-20T08:15:00+07:00',
        'locale' => 'vi',
        'user' => ['name' => 'Chu Quan', 'short_name' => 'Quan'],
        'metrics' => [
            'businesses' => 1,
            'campaigns' => 3,
            'active_campaigns' => 2,
            'visits' => 120,
            'review_clicks' => 12,
            'leads' => 9,
            'bookings' => 4,
            'coupon_claims' => 7,
            'feedback' => 2,
            'conversion_rate' => 28,
        ],
        'top_campaigns' => [
            [
                'campaign_name' => 'Weekend Review Push',
                'campaign_type' => 'review',
                'business_name' => 'Quan Cafe',
                'visits' => 70,
                'conversions' => 19,
                'conversion_rate' => 27,
            ],
        ],
        'recent_activity' => [
            [
                'customer' => 'Mai',
                'action' => 'submitted a lead',
                'campaign' => 'Weekend Review Push',
                'business' => 'Quan Cafe',
                'icon' => 'fa-user-plus',
                'time' => '2026-06-20T07:45:00+07:00',
            ],
        ],
        'customers' => [
            'new_this_week' => 5,
            'new_last_week' => 3,
            'delta' => 2,
        ],
        'weekly_signals' => [
            'leads' => 3,
            'bookings' => 2,
            'positive_reviews' => 4,
            'coupon_claims' => 5,
            'qr_scans' => 35,
        ],
        'reviews' => [
            'count' => 6,
            'average_rating' => 4.6,
            'needs_reply' => 2,
            'positive_count' => 5,
        ],
        'active_campaigns' => [
            ['name' => 'Weekend Review Push', 'type' => 'review', 'visits' => 70, 'conversions' => 19],
            ['name' => 'Voucher Quay Lai', 'type' => 'coupon', 'visits' => 50, 'conversions' => 14],
        ],
        'business_list' => [
            'count' => 1,
            'names' => ['Quan Cafe'],
        ],
        'onboarding' => [],
    ], $overrides);
}

function mlhubFakeContextBuilder(array $context): MLHUBAIContextBuilder
{
    return new class($context) extends MLHUBAIContextBuilder
    {
        public function __construct(private array $context) {}

        public function build(int $userId): array
        {
            return $this->context;
        }
    };
}

test('resolver recognizes P0 Vietnamese and unaccented daily operations intents', function (): void {
    $resolver = new MLHUBAIIntentResolver;

    $matches = $resolver->resolveAll('Sang nay co lich hen booking nao va coupon nao dang tot khong?');

    expect(array_column($matches, 'intent'))
        ->toContain('daily_briefing')
        ->toContain('booking')
        ->toContain('coupon')
        ->and($matches[0])->toHaveKey('matched_keywords')
        ->and($matches[0]['confidence'])->toBeGreaterThan(0);
});

test('resolver exposes P0 starter prompts in Vietnamese', function (): void {
    $prompts = (new MLHUBAIIntentResolver)->initialPrompts();

    expect($prompts)
        ->toContain('Sáng nay tình hình kinh doanh thế nào?')
        ->toContain('AI Cơ bản (Basic AI) có tốn tín dụng AI không?');
});

test('composer uses top campaigns and recent activity for daily briefing', function (): void {
    $message = (new MLHUBAIResponseComposer)->composeMany(
        ['daily_briefing', 'top_campaigns'],
        mlhubAssistantContext(),
    );

    expect($message)
        ->toContain('Weekend Review Push')
        ->toContain('Mai')
        ->toContain('120');
});

test('basic assistant response includes intent metadata without using advanced AI', function (): void {
    $service = new MLHUBAIAssistantService(
        new OptionStore,
        mlhubFakeContextBuilder(mlhubAssistantContext()),
        new MLHUBAIIntentResolver,
        new MLHUBAIResponseComposer,
    );

    $response = $service->ask(1, 'Basic AI có tốn credit không?', false, false);

    expect($response['source'])->toBe('fallback')
        ->and($response['intent'])->toBe('credits')
        ->and($response)->toHaveKey('metadata')
        ->and($response['metadata']['matched_keywords'])->toContain('credit')
        ->and($response['metadata']['confidence'])->toBeGreaterThan(0);
});

test('resolver recognizes P1 portal product intents', function (): void {
    $resolver = new MLHUBAIIntentResolver;

    $matches = $resolver->resolveAll(
        'Toi muon quan ly chi nhanh location, customer list, landing page, marketing template, CRM segment, Google Business, google review, AI Studio, content writer, billing invoice, team workspace va ticket support',
    );

    expect(array_column($matches, 'intent'))
        ->toContain('business_locations')
        ->toContain('customers')
        ->toContain('landing_pages')
        ->toContain('marketing_templates')
        ->toContain('crm_segments')
        ->toContain('google_business')
        ->toContain('google_reviews')
        ->toContain('ai_studio')
        ->toContain('ai_content_writer')
        ->toContain('billing')
        ->toContain('teams')
        ->toContain('support');
});

test('P1 route actions stay portal scoped and avoid dynamic public URLs', function (): void {
    $intents = [
        'business_locations',
        'customers',
        'landing_pages',
        'marketing_templates',
        'crm_segments',
        'google_business',
        'google_reviews',
        'ai_studio',
        'ai_content_writer',
        'billing',
        'teams',
        'support',
    ];

    $routeNames = [];

    foreach ($intents as $intent) {
        foreach (MLHUBAIKnowledgeBase::routeActions($intent) as [$routeName]) {
            $routeNames[] = $routeName;
        }
    }

    expect($routeNames)
        ->toContain('portal.locations')
        ->toContain('portal.customers')
        ->toContain('portal.landing-pages')
        ->toContain('portal.marketing-templates')
        ->toContain('portal.crm.segments')
        ->toContain('portal.google-business')
        ->toContain('portal.ai-studio')
        ->toContain('portal.ai-content')
        ->toContain('portal.billing')
        ->toContain('portal.invoices')
        ->toContain('portal.teams')
        ->toContain('portal.support.index');

    foreach ($routeNames as $routeName) {
        expect(str_starts_with($routeName, 'portal.'))->toBeTrue()
            ->and($routeName)->not->toContain('{')
            ->not->toContain('admin');
    }
});

test('composer answers P1 intents with rule based guidance', function (): void {
    $message = (new MLHUBAIResponseComposer)->composeMany(
        ['landing_pages', 'google_reviews', 'billing', 'support'],
        mlhubAssistantContext(),
    );

    expect($message)
        ->toContain('Trang đích')
        ->toContain('Google')
        ->toContain('Thanh toán')
        ->toContain('Hỗ trợ')
        ->not->toContain('{');
});

test('onboarding intent wins for new account first step questions', function (): void {
    $resolver = new MLHUBAIIntentResolver;
    $matches = $resolver->resolveAll('Tôi mới tạo tài khoản thì làm gì trước?');

    expect($matches[0]['intent'])->toBe('onboarding');

    $message = (new MLHUBAIResponseComposer)->composeMany(
        array_column($matches, 'intent'),
        mlhubAssistantContext(['onboarding' => ['create_business', 'create_campaign', 'share_qr']]),
    );

    expect($message)
        ->toContain('Bước 1')
        ->toContain('cơ sở kinh doanh')
        ->toContain('chiến dịch')
        ->toContain('Xuất bản chiến dịch')
        ->not->toContain('business')
        ->not->toContain('campaign')
        ->not->toContain('Publish')
        ->not->toContain('Phễu của bạn đang chạy');
});

test('crm guidance is not contaminated by next steps or ai studio cta', function (): void {
    $resolver = new MLHUBAIIntentResolver;
    $matches = $resolver->resolveAll('CRM dùng để làm gì?');
    $intents = array_column($matches, 'intent');

    expect($intents)
        ->toContain('crm_segments')
        ->not->toContain('next_steps');

    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext());
    $routeNames = array_map(
        static fn (array $action): string => $action[0],
        MLHUBAIKnowledgeBase::routeActions('crm_segments'),
    );

    expect($message)
        ->toContain('CRM')
        ->not->toContain('Phễu của bạn đang chạy')
        ->not->toContain('AI Studio')
        ->and($routeNames)->not->toContain('portal.ai-studio');
});

test('composer adds knowledge footer for guidance-only responses', function (): void {
    $message = (new MLHUBAIResponseComposer)->composeMany(
        ['landing_pages', 'marketing_templates', 'support'],
        mlhubAssistantContext(),
    );

    expect($message)
        ->toContain('Câu trả lời dựa trên tri thức nội bộ và cấu trúc tính năng của MKT.')
        ->not->toContain('Câu trả lời có sử dụng số liệu thực tế từ tài khoản của bạn.');
});

test('composer adds account metrics footer for metric responses', function (): void {
    $message = (new MLHUBAIResponseComposer)->composeMany(
        ['customers', 'reviews'],
        mlhubAssistantContext(),
    );

    expect($message)
        ->toContain('Câu trả lời có sử dụng số liệu thực tế từ tài khoản của bạn.')
        ->not->toContain('Câu trả lời dựa trên tri thức nội bộ và cấu trúc tính năng của MKT.');
});

test('P1 cta labels are Vietnamese', function (): void {
    $labels = [];

    foreach ([
        'daily_briefing',
        'review_booster',
        'booking',
        'coupon',
        'feedback',
        'leads',
        'credits',
        'business_locations',
        'help_using_mlhubai',
        'crm_segments',
        'landing_pages',
        'marketing_templates',
        'google_business',
        'ai_studio',
        'teams',
        'support',
    ] as $intent) {
        foreach (MLHUBAIKnowledgeBase::routeActions($intent) as [, $label]) {
            $labels[] = $label;
        }
    }

    expect($labels)
        ->toContain('Mở MKT AI')
        ->toContain('Cài đặt AI')
        ->toContain('Mở bảng điều khiển')
        ->toContain('Xem báo cáo')
        ->toContain('Mở công cụ xin đánh giá')
        ->toContain('Mở trang đặt lịch')
        ->toContain('Mở mã ưu đãi')
        ->toContain('Mở form góp ý')
        ->toContain('Mở form khách tiềm năng')
        ->toContain('Xem lịch sử tín dụng AI')
        ->toContain('Quản lý cơ sở kinh doanh')
        ->toContain('Mở khách hàng trong CRM')
        ->toContain('Mở nhóm khách hàng CRM')
        ->toContain('Mở trang đích')
        ->toContain('Mở mẫu marketing')
        ->toContain('Mở Google Business')
        ->toContain('Mở lịch sử câu lệnh')
        ->toContain('Mở hỗ trợ')
        ->toContain('Mở đội ngũ')
        ->not->toContain('Open MKT AI')
        ->not->toContain('Open support')
        ->not->toContain('Thêm business')
        ->not->toContain('Mở dashboard')
        ->not->toContain('Mở booking')
        ->not->toContain('Mở coupon')
        ->not->toContain('Mở feedback forms')
        ->not->toContain('Mở lead forms');
});

test('onboarding billing and support copy avoid mixed English', function (): void {
    $composer = new MLHUBAIResponseComposer;

    $onboarding = $composer->composeMany(['onboarding'], mlhubAssistantContext());
    $billing = $composer->composeMany(['billing'], mlhubAssistantContext());
    $support = $composer->composeMany(['support'], mlhubAssistantContext());

    expect($onboarding)
        ->toContain('cơ sở kinh doanh')
        ->toContain('chiến dịch')
        ->not->toContain('business')
        ->not->toContain('campaign')
        ->not->toContain('publish')
        ->and($billing)
        ->toContain('Thanh toán')
        ->toContain('gói đang dùng')
        ->toContain('hóa đơn')
        ->toContain('lịch sử tín dụng AI')
        ->not->toContain('subscription')
        ->not->toContain('Packages')
        ->not->toContain('Invoices')
        ->not->toContain('Credit Usage')
        ->and($support)
        ->toContain('Hỗ trợ')
        ->toContain('phiếu hỗ trợ')
        ->not->toContain('ticket');
});

test('composer summarizes long multi-intent questions without rendering every intent', function (): void {
    $message = (new MLHUBAIResponseComposer)->composeMany(
        ['help_using_mlhubai', 'onboarding', 'overview', 'customers', 'reviews', 'google_business', 'billing', 'teams', 'support'],
        mlhubAssistantContext(),
    );

    expect($message)
        ->toContain('Bạn đang hỏi nhiều nhóm, mình tóm tắt nhanh các nhóm chính trước.')
        ->toContain('MKT AI')
        ->toContain('Bước 1')
        ->toContain('Tóm tắt nhanh')
        ->toContain('Khách hàng')
        ->toContain('đánh giá')
        ->not->toContain('Google Business')
        ->not->toContain('Thanh toán')
        ->not->toContain('Hỗ trợ');
});

test('basic ai responses render exactly one composer footer', function (): void {
    $messages = [
        (new MLHUBAIResponseComposer)->composeMany(['landing_pages'], mlhubAssistantContext()),
        (new MLHUBAIResponseComposer)->composeMany(['customers'], mlhubAssistantContext()),
    ];

    foreach ($messages as $message) {
        $footerCount = substr_count($message, 'Câu trả lời có sử dụng số liệu thực tế từ tài khoản của bạn.')
            + substr_count($message, 'Câu trả lời dựa trên tri thức nội bộ và cấu trúc tính năng của MKT.');

        expect($footerCount)->toBe(1)
            ->and($message)->not->toContain('Câu trả lời dựa trên số liệu thực tế của tài khoản được AI nội bộ xử lý.');
    }
});

test('chat shell does not render the old fixed basic ai footer', function (): void {
    $view = file_get_contents(base_path('modules/CustomMLHUB/Resources/views/partials/chat-shell.blade.php'));

    expect($view)
        ->not->toContain('Answer based on your account\\\'s real data, processed by the built-in AI.')
        ->not->toContain('Câu trả lời dựa trên số liệu thực tế của tài khoản được AI nội bộ xử lý.');
});

test('resolver recognizes everyday P1.3 aliases without falling back', function (string $question, string $intent): void {
    $match = (new MLHUBAIIntentResolver)->resolve($question);

    expect($match['intent'])->toBe($intent)
        ->and($match['intent'])->not->toBe('unknown')
        ->and($match['confidence'])->toBeGreaterThan(0);
})->with([
    ['AI Cơ bản có tốn điểm tín dụng không?', 'credits'],
    ['AI Nâng cao khác AI Cơ bản thế nào?', 'help_using_mlhubai'],
    ['Tôi còn bao nhiêu điểm tín dụng?', 'credits'],
    ['Mời nhân viên vào đội ngũ ở đâu?', 'teams'],
    ['Cập nhật thông tin quán ở đâu?', 'businesses'],
    ['Tôi có một quán cà phê, nên dùng tính năng nào đầu tiên?', 'industry_recommendation'],
    ['Tôi cần tạo cơ sở kinh doanh trước hay tạo chiến dịch trước?', 'onboarding'],
    ['Tôi muốn khách để lại số điện thoại thì dùng tính năng nào?', 'leads'],
    ['Tôi muốn biết kênh nào mang khách tốt nhất thì xem ở đâu?', 'conversion'],
    ['Nếu khách đánh giá thấp thì nên làm gì?', 'feedback'],
]);

test('P1.3 everyday answers are focused and practical', function (): void {
    $composer = new MLHUBAIResponseComposer;

    $coffee = $composer->composeMany(['industry_recommendation'], mlhubAssistantContext());
    $lead = $composer->composeMany(['leads'], mlhubAssistantContext());
    $channel = $composer->composeMany(['conversion'], mlhubAssistantContext());
    $lowReview = $composer->composeMany(['feedback'], mlhubAssistantContext());
    $returning = $composer->composeMany(['crm_segments', 'coupon'], mlhubAssistantContext());

    expect($coffee)
        ->toContain('cơ sở kinh doanh')
        ->toContain('QR xin đánh giá')
        ->toContain('mã ưu đãi')
        ->and($lead)
        ->toContain('form khách tiềm năng')
        ->not->toContain('đánh giá đang chờ')
        ->and($channel)
        ->toContain('báo cáo')
        ->toContain('nguồn')
        ->and($lowReview)
        ->toContain('xin lỗi')
        ->toContain('chăm sóc lại')
        ->and($returning)
        ->toContain('khách cũ')
        ->toContain('mã ưu đãi');
});

test('navigation questions stay focused on one main intent', function (string $question, string $intent): void {
    $matches = (new MLHUBAIIntentResolver)->resolveAll($question);

    expect(array_column($matches, 'intent'))
        ->toBe([$intent]);
})->with([
    ['Mời nhân viên vào đội ngũ ở đâu?', 'teams'],
    ['Cập nhật thông tin quán ở đâu?', 'businesses'],
    ['Tôi muốn biết kênh nào mang khách tốt nhất thì xem ở đâu?', 'conversion'],
    ['Tôi muốn khách để lại số điện thoại thì dùng tính năng nào?', 'leads'],
]);

test('returning customer question focuses CRM and offer guidance', function (): void {
    $intents = array_column((new MLHUBAIIntentResolver)->resolveAll('Quán tôi ít khách quay lại, nên làm gì?'), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext());

    expect($intents)
        ->toContain('crm_segments')
        ->toContain('coupon')
        ->not->toContain('customers')
        ->and($message)
        ->toContain('khách cũ')
        ->toContain('mã ưu đãi');
});

test('P1.3 focused answers avoid intent contamination', function (): void {
    $resolver = new MLHUBAIIntentResolver;
    $composer = new MLHUBAIResponseComposer;

    $qrIntents = array_column($resolver->resolveAll('Lượt quét QR tuần này thế nào?'), 'intent');
    $leadIntents = array_column($resolver->resolveAll('Tôi muốn khách để lại số điện thoại thì dùng tính năng nào?'), 'intent');
    $callbackIntents = array_column($resolver->resolveAll('Có khách tiềm năng mới nào cần gọi lại không?'), 'intent');

    $qrMessage = $composer->composeMany($qrIntents, mlhubAssistantContext());
    $leadMessage = $composer->composeMany($leadIntents, mlhubAssistantContext());
    $callbackMessage = $composer->composeMany($callbackIntents, mlhubAssistantContext());

    expect($qrIntents)->toBe(['qr_scans'])
        ->and($qrMessage)
        ->not->toContain('đánh giá đang chờ')
        ->not->toContain('Công cụ xin đánh giá')
        ->and($leadMessage)->not->toContain('đánh giá đang chờ')
        ->and($callbackMessage)
        ->not->toContain('Thanh toán')
        ->not->toContain('billing');
});

test('google review answer does not duplicate the review metrics sentence', function (): void {
    $resolver = new MLHUBAIIntentResolver;
    $intents = array_column($resolver->resolveAll('Đánh giá Google nào cần trả lời?'), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext());

    expect(substr_count($message, 'Trung bình'))->toBeLessThanOrEqual(1)
        ->and(substr_count($message, 'đánh giá cần trả lời'))->toBeLessThanOrEqual(1);
});

test('industry recommendation wins over generic onboarding for cafe questions', function (): void {
    $resolver = new MLHUBAIIntentResolver;
    $composer = new MLHUBAIResponseComposer;

    $matches = $resolver->resolveAll('Tôi có một quán cà phê, nên dùng tính năng nào đầu tiên?');
    $intents = array_column($matches, 'intent');
    $message = $composer->composeMany($intents, mlhubAssistantContext());
    $routeLabels = array_map(
        static fn (array $action): string => $action[1],
        MLHUBAIKnowledgeBase::routeActions('industry_recommendation'),
    );

    expect($intents[0])->toBe('industry_recommendation')
        ->and($intents)->not->toContain('onboarding')
        ->and($message)
        ->toContain('quán cà phê hoặc trà sữa')
        ->toContain('tạo cơ sở kinh doanh')
        ->toContain('mã ưu đãi')
        ->not->toContain('Bước 1')
        ->and($routeLabels)
        ->toContain('Quản lý cơ sở kinh doanh')
        ->toContain('Quản lý chiến dịch')
        ->toContain('Mở mã ưu đãi');
});

test('industry recommendation recognizes common local business types', function (string $question): void {
    $match = (new MLHUBAIIntentResolver)->resolve($question);

    expect($match['intent'])->toBe('industry_recommendation');
})->with([
    'Tôi có quán cafe thì nên dùng gì?',
    'Quán trà sữa nên bắt đầu từ đâu?',
    'Tôi có quán ăn thì dùng tính năng nào?',
    'Tôi có nhà hàng thì dùng tính năng nào?',
    'Tôi có spa thì dùng tính năng nào?',
    'Tôi có salon thì dùng tính năng nào?',
    'Tôi có bán lẻ thì dùng tính năng nào?',
    'Tôi có khách sạn thì dùng tính năng nào?',
]);

test('credit questions merge help and credits into one focused answer', function (): void {
    $resolver = new MLHUBAIIntentResolver;
    $composer = new MLHUBAIResponseComposer;

    $intents = array_column($resolver->resolveAll('AI Cơ bản có tốn điểm tín dụng không?'), 'intent');
    $message = $composer->composeMany($intents, mlhubAssistantContext());
    $actions = MLHUBAIKnowledgeBase::routeActions($intents[0]);
    $labels = array_map(static fn (array $action): string => $action[1], $actions);

    expect($intents)->toBe(['credits'])
        ->and(substr_count($message, 'AI Cơ bản'))->toBe(1)
        ->and(substr_count($message, 'tín dụng AI'))->toBeLessThanOrEqual(3)
        ->and($message)->not->toContain('Bạn có thể hỏi về báo cáo hôm nay')
        ->and($labels)
        ->toContain('Xem lịch sử tín dụng AI')
        ->toContain('Cài đặt AI')
        ->not->toContain('Mở MKT AI');
});

test('resolver recognizes Vietnamese credit balance wording', function (): void {
    $resolver = new MLHUBAIIntentResolver;
    $matches = $resolver->resolveAll('Tôi còn bao nhiêu tín dụng AI?');
    $intents = array_column($matches, 'intent');

    expect($matches)->not->toBeEmpty()
        ->and($matches[0]['intent'])->toBe('credits')
        ->and($intents)->toContain('credits')
        ->and($matches[0]['confidence'])->toBeGreaterThan(0);
});

test('plan limit question wins over billing for current plan limits', function (): void {
    $resolver = new MLHUBAIIntentResolver;
    $matches = $resolver->resolveAll('Gói hiện tại của tôi giới hạn gì?');
    $message = (new MLHUBAIResponseComposer)->composeMany(
        array_column($matches, 'intent'),
        mlhubAssistantContext([
            'plan' => [
                'available' => true,
                'name' => 'MKT Growth',
                'status' => 'Active',
                'usage' => [
                    'campaigns' => [
                        'label' => 'Chiến dịch',
                        'used' => 2,
                        'limit' => 10,
                        'remaining' => 8,
                        'unlimited' => false,
                        'percent' => 20,
                    ],
                ],
            ],
        ]),
    );

    expect($matches)->not->toBeEmpty()
        ->and($matches[0]['intent'])->toBe('plan_limits')
        ->and($message)->toContain('Gói hiện tại')
        ->and($message)->toContain('Chiến dịch')
        ->and($message)->toContain('2/10')
        ->and(mb_substr($message, 0, 80))->not->toContain('Thanh toán')
        ->and(mb_substr($message, 0, 80))->not->toContain('hóa đơn');
});

test('credits response uses real snapshot when available', function (): void {
    $message = (new MLHUBAIResponseComposer)->composeMany(
        ['credits'],
        mlhubAssistantContext([
            'credits' => [
                'available' => true,
                'remaining' => 42,
                'used' => 8,
                'limit' => 50,
                'topup_remaining' => 12,
                'unlimited' => false,
                'low_balance' => false,
                'costs' => [
                    'mlhub_ai_chat' => 1,
                ],
            ],
        ]),
    );

    expect($message)
        ->toContain('42')
        ->toContain('8')
        ->toContain('50')
        ->toContain('12')
        ->toContain('1')
        ->toContain('Câu trả lời có sử dụng số liệu thực tế từ tài khoản của bạn.')
        ->not->toContain('chưa có dữ liệu tín dụng');
});

test('credits response degrades safely when snapshot is missing', function (): void {
    $message = (new MLHUBAIResponseComposer)->composeMany(
        ['credits'],
        mlhubAssistantContext([
            'credits' => [
                'available' => false,
                'reason' => 'missing_module',
            ],
        ]),
    );

    expect($message)
        ->toContain('chưa có dữ liệu tín dụng')
        ->toContain('AI Cơ bản')
        ->toContain('không trừ')
        ->toContain('Câu trả lời dựa trên tri thức nội bộ và cấu trúc tính năng của MKT.')
        ->not->toContain('Câu trả lời có sử dụng số liệu thực tế từ tài khoản của bạn.');
});

test('plan limits response uses real snapshot when available', function (): void {
    $message = (new MLHUBAIResponseComposer)->composeMany(
        ['plan_limits'],
        mlhubAssistantContext([
            'plan' => [
                'available' => true,
                'name' => 'MKT Growth',
                'status' => 'Active',
                'usage' => [
                    'businesses' => [
                        'label' => 'Cơ sở kinh doanh',
                        'used' => 1,
                        'limit' => 3,
                        'remaining' => 2,
                        'unlimited' => false,
                        'percent' => 33,
                    ],
                    'campaigns' => [
                        'label' => 'Chiến dịch',
                        'used' => 2,
                        'limit' => 10,
                        'remaining' => 8,
                        'unlimited' => false,
                        'percent' => 20,
                    ],
                ],
            ],
        ]),
    );

    expect($message)
        ->toContain('MKT Growth')
        ->toContain('Active')
        ->toContain('Cơ sở kinh doanh')
        ->toContain('1/3')
        ->toContain('Chiến dịch')
        ->toContain('2/10')
        ->toContain('Câu trả lời có sử dụng số liệu thực tế từ tài khoản của bạn.')
        ->not->toContain('chưa có dữ liệu hạn mức');
});

test('plan limits response degrades safely when snapshot is missing', function (): void {
    $message = (new MLHUBAIResponseComposer)->composeMany(
        ['plan_limits'],
        mlhubAssistantContext([
            'plan' => [
                'available' => false,
                'reason' => 'query_failed',
            ],
        ]),
    );

    expect($message)
        ->toContain('chưa có dữ liệu hạn mức')
        ->toContain('không đoán quota')
        ->toContain('Câu trả lời dựa trên tri thức nội bộ và cấu trúc tính năng của MKT.')
        ->not->toContain('Câu trả lời có sử dụng số liệu thực tế từ tài khoản của bạn.');
});

test('industry recommendation for spa uses booking review crm and coupon', function (): void {
    $question = 'Tôi là spa gội đầu dưỡng sinh, khách đến xong không để lại đánh giá thì nên cài quy trình nào?';
    $resolver = new MLHUBAIIntentResolver;
    $intents = array_column($resolver->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('industry_recommendation')
        ->and($message)
        ->toContain('đặt lịch')
        ->toContain('đánh giá')
        ->toContain('CRM')
        ->toContain('ưu đãi')
        ->not->toContain('quán cà phê')
        ->not->toContain('trà sữa')
        ->not->toContain('quán nước');
});

test('industry recommendation for seafood restaurant uses google booking landing review', function (): void {
    $question = 'Tôi có nhà hàng hải sản, khách du lịch tìm trên Google nhiều nhưng ít đặt bàn, MKT giúp gì?';
    $resolver = new MLHUBAIIntentResolver;
    $intents = array_column($resolver->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('industry_recommendation')
        ->and($message)
        ->toContain('Google Business')
        ->toContain('đặt bàn')
        ->toContain('trang đích')
        ->toContain('đánh giá')
        ->not->toContain('spa')
        ->not->toContain('quán cà phê');
});

test('retail cosmetic phone collection uses lead form and crm', function (): void {
    $question = 'Tôi bán mỹ phẩm, muốn lấy số điện thoại khách và chăm sóc lại sau 7 ngày thì dùng tính năng nào?';
    $resolver = new MLHUBAIIntentResolver;
    $intents = array_column($resolver->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('industry_recommendation')
        ->and($message)
        ->toContain('form khách tiềm năng')
        ->toContain('CRM')
        ->toContain('ưu đãi')
        ->not->toContain('quán cà phê');
});

test('qr high scan low lead focuses conversion not reviews', function (): void {
    $question = 'QR có nhiều lượt quét nhưng ít khách để lại thông tin, tôi nên sửa ở đâu trước?';
    $resolver = new MLHUBAIIntentResolver;
    $intents = array_column($resolver->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('conversion')
        ->and($message)
        ->toContain('form')
        ->toContain('ưu đãi')
        ->toContain('báo cáo')
        ->toContain('chuyển đổi')
        ->toContain('Câu trả lời dựa trên tri thức nội bộ')
        ->not->toContain('Câu trả lời có sử dụng số liệu thực tế')
        ->not->toContain('Trung bình')
        ->not->toContain('review average')
        ->not->toContain('rating');
});

test('low rating scenario uses private feedback recovery', function (): void {
    $question = 'Có khách đánh giá 2 sao, tôi nên xử lý trong MKT như thế nào?';
    $resolver = new MLHUBAIIntentResolver;
    $intents = array_column($resolver->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('feedback')
        ->and($message)
        ->toContain('phản hồi riêng')
        ->toContain('khách không hài lòng')
        ->toContain('CRM')
        ->toContain('Câu trả lời dựa trên tri thức nội bộ')
        ->not->toContain('Câu trả lời có sử dụng số liệu thực tế')
        ->not->toContain('Cảm nhận tích cực')
        ->not->toContain('Tất cả đánh giá tích cực');
});

test('branch performance question uses locations and reports', function (): void {
    $question = 'Tôi có 3 chi nhánh, làm sao biết chi nhánh nào kéo khách tốt nhất?';
    $resolver = new MLHUBAIIntentResolver;
    $intents = array_column($resolver->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('business_locations')
        ->and($message)
        ->toContain('chi nhánh')
        ->toContain('báo cáo')
        ->toContain('so sánh')
        ->toContain('chiến dịch');
});

test('no credit campaign stays manual basic and template based', function (): void {
    $question = 'Tôi muốn tạo chiến dịch kéo khách cũ quay lại nhưng không muốn tốn điểm tín dụng AI thì làm sao?';
    $resolver = new MLHUBAIIntentResolver;
    $intents = array_column($resolver->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('credits')
        ->and($message)
        ->toContain('AI Cơ bản')
        ->toContain('không trừ tín dụng AI')
        ->toContain('mã ưu đãi')
        ->toContain('CRM')
        ->toContain('mẫu')
        ->not->toContain('bắt buộc dùng AI Nâng cao');
});

test('content writing request routes to studio not long chat copy', function (): void {
    $question = 'Tôi muốn tạo nội dung Facebook cho mã ưu đãi';
    $resolver = new MLHUBAIIntentResolver;
    $intents = array_column($resolver->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('ai_content_writer')
        ->and($message)
        ->toContain('AI Content')
        ->toContain('AI Studio')
        ->toContain('Marketing Templates')
        ->toContain('không viết caption')
        ->not->toContain('#')
        ->and(mb_strlen($message))->toBeLessThan(700);
});

test('qr guidance does not pull review or rating text', function (string $question): void {
    $resolver = new MLHUBAIIntentResolver;
    $intents = array_column($resolver->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext());

    expect($intents)->toBe(['qr_scans'])
        ->and($message)
        ->toContain('QR')
        ->toContain('báo cáo')
        ->not->toContain('Trung bình')
        ->not->toContain('đánh giá')
        ->not->toContain('review')
        ->not->toContain('rating')
        ->not->toContain('4.0★');
})->with([
    'Tôi muốn khách quét mã QR thì phải làm sao?',
    'Làm sao để tăng lượt quét QR?',
]);

test('onboarding actions avoid ai studio unless user asks ai explicitly', function (): void {
    $service = new MLHUBAIAssistantService(
        new OptionStore,
        mlhubFakeContextBuilder(mlhubAssistantContext([
            'onboarding' => ['create_business', 'create_campaign'],
        ])),
        new MLHUBAIIntentResolver,
        new MLHUBAIResponseComposer,
    );

    $response = $service->ask(1, 'Tôi mới tạo tài khoản thì làm gì trước?', false, false);
    $labels = array_column($response['actions'], 'label');

    expect($response['intent'])->toBe('onboarding')
        ->and($labels)
        ->toContain('Quản lý cơ sở kinh doanh')
        ->toContain('Quản lý chiến dịch')
        ->not->toContain('Mở AI Studio');
});

test('industry recommendation covers all 18 business groups', function (string $question, string $snippet): void {
    $resolver = new MLHUBAIIntentResolver;
    $intents = array_column($resolver->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('industry_recommendation')
        ->and($message)->toContain($snippet);
})->with([
    'food_beverage' => ['Tôi có quán ăn thì nên dùng MKT thế nào?', 'QR'],
    'retail_goods' => ['Tôi kinh doanh bán lẻ thời trang thì dùng gì?', 'form khách tiềm năng'],
    'beauty_personal_care' => ['Tôi có spa salon thì nên bắt đầu từ đâu?', 'đặt lịch'],
    'tourism_hospitality_experience' => ['Tôi có homestay du lịch thì dùng gì?', 'Google Business'],
    'health_dental_fitness' => ['Tôi có phòng khám nha khoa thì nên dùng gì?', 'góp ý riêng'],
    'technical_repair_maintenance' => ['Tôi làm sửa chữa điện lạnh thì dùng gì?', 'báo giá'],
    'education_training_coaching' => ['Tôi làm trung tâm tiếng Anh thì dùng gì?', 'trang đích'],
    'wholesale_distribution' => ['Tôi làm đại lý phân phối mỹ phẩm thì dùng gì?', 'B2B'],
    'professional_b2b_services' => ['Tôi là agency tư vấn B2B thì dùng gì?', 'pipeline'],
    'home_construction_interior' => ['Tôi làm xây dựng nội thất thì dùng gì?', 'landing'],
    'transport_delivery_logistics' => ['Tôi làm giao hàng logistics thì dùng gì?', 'báo cáo'],
    'real_estate_rental_property' => ['Tôi làm bất động sản cho thuê thì dùng gì?', 'trang đích'],
    'digital_creator_online_business' => ['Tôi kinh doanh online ecommerce thì dùng gì?', 'trang đích'],
    'small_manufacturing_processing_ocop' => ['Tôi có xưởng OCOP thì bắt đầu từ đâu?', 'đại lý'],
    'agriculture_fisheries_local_supply' => ['Tôi bán nông sản nhà vườn thì dùng gì?', 'sỉ'],
    'culture_entertainment_sports_community' => ['Tôi có karaoke sân thể thao thì dùng gì?', 'mã ưu đãi'],
    'organization_association_public_community' => ['Tôi là hiệp hội cộng đồng thì dùng gì?', 'báo cáo'],
    'other_needs_classification' => ['Tôi chưa rõ ngành, mới đăng ký thì làm gì?', 'chưa rõ ngành'],
]);

test('industry alias map aligns with BusinessTypeCatalog group count', function (): void {
    expect(MLHUBAIKnowledgeBase::industryGroupDetectionOrder())->toHaveCount(18)
        ->and(array_keys(MLHUBAIKnowledgeBase::industryGroupAliasMap()))->toHaveCount(18);
});

test('unknown or mixed industry asks for clarification without falling back badly', function (): void {
    $question = 'Tôi làm nhiều ngành khác nhau, chưa phân loại được thì MKT gợi ý gì?';
    $intents = array_column((new MLHUBAIIntentResolver)->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('industry_recommendation')
        ->and($message)
        ->toContain('ngành')
        ->toContain('cơ sở kinh doanh')
        ->not->toContain('quán cà phê hoặc trà sữa');
});

test('health industry avoids medical overclaim', function (): void {
    $question = 'Tôi có phòng khám, muốn dùng MKT thì nên làm gì?';
    $message = (new MLHUBAIResponseComposer)->composeMany(
        ['industry_recommendation'],
        mlhubAssistantContext(['request' => ['question' => $question]]),
    );

    expect($message)
        ->toContain('không thay tư vấn y khoa')
        ->toContain('không hứa chữa khỏi')
        ->not->toContain('điều trị thành công')
        ->not->toContain('bảo đảm chữa');
});

test('creator content request routes to studio instead of writing long copy', function (): void {
    $question = 'Tôi là creator, viết caption khuyến mãi livestream giúp tôi';
    $intents = array_column((new MLHUBAIIntentResolver)->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('ai_content_writer')
        ->and($message)
        ->toContain('AI Content')
        ->toContain('AI Studio')
        ->and(mb_strlen($message))->toBeLessThan(700);
});

test('wholesale and distribution recommends b2b lead and crm', function (): void {
    $question = 'Tôi làm đại lý phân phối mỹ phẩm thì nên dùng MKT thế nào?';
    $composer = new MLHUBAIResponseComposer;
    $context = mlhubAssistantContext(['request' => ['question' => $question]]);
    $message = $composer->composeMany(['industry_recommendation'], $context);
    $labels = array_column($composer->actionsFor(['industry_recommendation'], $context), 'label');

    expect($message)->toContain('CRM')->toContain('form khách tiềm năng')
        ->and($labels)->toContain('Mở form khách tiềm năng');
});

test('real estate recommends landing lead and crm', function (): void {
    $question = 'Tôi làm bất động sản cho thuê, muốn lấy lead khách quan tâm thì dùng gì?';
    $composer = new MLHUBAIResponseComposer;
    $context = mlhubAssistantContext(['request' => ['question' => $question]]);
    $message = $composer->composeMany(['industry_recommendation'], $context);
    $labels = array_column($composer->actionsFor(['industry_recommendation'], $context), 'label');

    expect($message)->toContain('trang đích')->toContain('CRM')
        ->and($labels)->toContain('Mở trang đích')->toContain('Mở form khách tiềm năng');
});

test('organizations recommends registration lead reports', function (): void {
    $question = 'Tôi là hiệp hội, chạy chương trình chính quyền thì nên dùng gì?';
    $composer = new MLHUBAIResponseComposer;
    $context = mlhubAssistantContext(['request' => ['question' => $question]]);
    $message = $composer->composeMany(['industry_recommendation'], $context);
    $labels = array_column($composer->actionsFor(['industry_recommendation'], $context), 'label');

    expect($message)->toContain('đăng ký')->toContain('báo cáo')
        ->and($labels)->toContain('Mở trang đích')->toContain('Xem báo cáo');
});

test('content caption request routes to studio before industry', function (): void {
    $question = 'Tôi là creator livestream muốn viết caption bán mỹ phẩm';
    $intents = array_column((new MLHUBAIIntentResolver)->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('ai_content_writer')
        ->and($intents)->not->toContain('industry_recommendation')
        ->and($message)->toContain('AI Content')
        ->not->toContain('phân phối/bán sỉ');
});

test('restaurant facebook content request routes to studio not restaurant guidance', function (): void {
    $question = 'Tôi làm nhà hàng muốn viết bài Facebook cho ưu đãi cuối tuần.';
    $intents = array_column((new MLHUBAIIntentResolver)->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('ai_content_writer')
        ->and($intents)->not->toContain('industry_recommendation')
        ->and($message)
        ->toContain('AI Content')
        ->toContain('không viết caption')
        ->not->toContain('Google Business');
});

test('spa operational question stays industry guidance not studio', function (): void {
    $question = 'Tôi làm spa, nên dùng MKT tính năng nào trước?';
    $intents = array_column((new MLHUBAIIntentResolver)->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('industry_recommendation')
        ->and($intents)->not->toContain('ai_content_writer')
        ->and($intents)->not->toContain('ai_studio')
        ->and($message)->toContain('đặt lịch')
        ->not->toContain('không viết caption');
});

test('review reply writing request routes to studio', function (): void {
    $question = 'Trả lời review này giúp tôi.';
    $intents = array_column((new MLHUBAIIntentResolver)->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('ai_studio')
        ->and($message)
        ->toContain('AI Studio')
        ->toContain('không soạn phản hồi dài')
        ->not->toContain('Review Booster');
});

test('image generation request routes to studio or ai image', function (): void {
    $question = 'Tạo ảnh banner khuyến mãi bằng AI ở đâu?';
    $composer = new MLHUBAIResponseComposer;
    $context = mlhubAssistantContext(['request' => ['question' => $question]]);
    $intents = array_column((new MLHUBAIIntentResolver)->resolveAll($question), 'intent');
    $message = $composer->composeMany($intents, $context);
    $labels = array_column($composer->actionsFor($intents, $context), 'label');

    expect($intents[0])->toBe('ai_studio')
        ->and($message)->toContain('Tạo ảnh AI')
        ->and($labels)->toContain('Mở tạo ảnh AI');
});

test('basic chat does not produce long marketing copy', function (): void {
    $questions = [
        'Viết caption khuyến mãi cuối tuần cho quán cafe',
        'Tôi muốn viết bài quảng cáo dài cho landing page',
    ];

    foreach ($questions as $question) {
        $intents = array_column((new MLHUBAIIntentResolver)->resolveAll($question), 'intent');
        $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
            'request' => ['question' => $question],
        ]));

        expect($intents[0])->toBe('ai_content_writer')
            ->and(mb_strlen($message))->toBeLessThan(500)
            ->and($message)->toMatch('/không viết|không soạn/i');
    }
});

test('phase B credit and plan guidance regressions remain stable', function (): void {
    $composer = new MLHUBAIResponseComposer;

    $creditsMessage = $composer->composeMany(
        ['credits'],
        mlhubAssistantContext([
            'credits' => [
                'available' => true,
                'remaining' => 42,
                'used' => 8,
                'limit' => 50,
                'topup_remaining' => 12,
                'unlimited' => false,
                'low_balance' => false,
                'costs' => ['mlhub_ai_chat' => 1],
            ],
        ]),
    );

    $planMessage = $composer->composeMany(
        ['plan_limits'],
        mlhubAssistantContext([
            'plan' => [
                'available' => true,
                'name' => 'MKT Growth',
                'status' => 'Active',
                'usage' => [
                    'campaigns' => [
                        'label' => 'Chiến dịch',
                        'used' => 2,
                        'limit' => 10,
                        'remaining' => 8,
                        'unlimited' => false,
                        'percent' => 20,
                    ],
                ],
            ],
        ]),
    );

    expect($creditsMessage)->toContain('42')->toContain('Câu trả lời có sử dụng số liệu thực tế')
        ->and($planMessage)->toContain('2/10')->toContain('Gói hiện tại');
});

test('phase C1 eighteen industry groups regression remains stable', function (): void {
    expect(MLHUBAIKnowledgeBase::industryGroupDetectionOrder())->toHaveCount(18)
        ->and((new MLHUBAIIntentResolver)->resolve('Tôi có quán ăn thì nên dùng MLHUB thế nào?')['intent'])
        ->toBe('industry_recommendation');
});

test('batch multi industry question summarizes groups instead of picking one industry', function (): void {
    $question = 'Tôi vừa mở spa, đại lý phân phối mỹ phẩm, xưởng OCOP và hiệp hội — nên dùng MKT thế nào?';
    $resolver = new MLHUBAIIntentResolver;
    $composer = new MLHUBAIResponseComposer;
    $context = mlhubAssistantContext(['request' => ['question' => $question]]);
    $intents = array_column($resolver->resolveAll($question), 'intent');
    $message = $composer->composeMany($intents, $context);
    $labels = array_column($composer->actionsFor($intents, $context), 'label');

    expect($intents[0])->toBe('industry_recommendation')
        ->and($message)->toContain('nhiều nhóm ngành')
        ->and($message)->toContain('B2B')
        ->and($message)->toContain('OCOP')
        ->and($message)->not->toContain('Với spa, salon')
        ->and(count($labels))->toBeLessThanOrEqual(3);
});

test('mentioning ai content as a tool does not trigger studio handoff', function (): void {
    $question = 'MKT nên ưu tiên landing page, QR, CRM, báo cáo, mẫu marketing hay AI Content như thế nào?';
    $intents = array_column((new MLHUBAIIntentResolver)->resolveAll($question), 'intent');

    expect($intents)->not->toContain('ai_content_writer')
        ->and($intents)->not->toContain('ai_studio')
        ->and(MLHUBAIKnowledgeBase::detectStudioHandoff($question))->toBeNull();
});

test('multi scenario ordering returns feedback review google booking sequence', function (): void {
    $question = 'Khách không để lại đánh giá, có khách 2 sao, Google nhiều người xem nhưng ít đặt bàn — tôi nên xử lý theo thứ tự nào?';
    $resolver = new MLHUBAIIntentResolver;
    $composer = new MLHUBAIResponseComposer;
    $context = mlhubAssistantContext(['request' => ['question' => $question]]);
    $intents = array_column($resolver->resolveAll($question), 'intent');
    $message = $composer->composeMany($intents, $context);
    $labels = array_column($composer->actionsFor($intents, $context), 'label');

    expect($intents[0])->toBe('feedback')
        ->and($message)->toContain('form góp ý')
        ->and($message)->toContain('Review Booster')
        ->and($message)->toContain('Google Business')
        ->and($message)->toContain('đặt bàn')
        ->and($labels)->toContain('Mở form góp ý')
        ->and(count($labels))->toBeLessThanOrEqual(3);
});

test('multi studio task question summarizes handoff types', function (): void {
    $question = 'Tôi cần viết caption, lập lịch nội dung, trả lời review và tạo banner AI — mở đâu?';
    $resolver = new MLHUBAIIntentResolver;
    $composer = new MLHUBAIResponseComposer;
    $context = mlhubAssistantContext(['request' => ['question' => $question]]);
    $intents = array_column($resolver->resolveAll($question), 'intent');
    $message = $composer->composeMany($intents, $context);
    $labels = array_column($composer->actionsFor($intents, $context), 'label');

    expect($intents[0])->toBe('ai_studio')
        ->and($message)->toContain('chia theo loại')
        ->and($message)->toContain('AI Content')
        ->and($message)->toContain('Lập lịch nội dung')
        ->and($message)->toContain('Trả lời đánh giá AI')
        ->and($message)->toContain('Tạo ảnh AI')
        ->and(count($labels))->toBeLessThanOrEqual(3);
});

test('phase E1 regression single spa industry still returns spa guidance', function (): void {
    $question = 'Tôi có spa thì dùng tính năng nào?';
    $intents = array_column((new MLHUBAIIntentResolver)->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('industry_recommendation')
        ->and($message)->toContain('spa')
        ->and($message)->toContain('đặt lịch')
        ->and($message)->not->toContain('nhiều nhóm ngành');
});

test('phase E1 regression direct caption request still handoffs ai content', function (): void {
    $question = 'Viết caption khuyến mãi cuối tuần cho quán cafe';
    $intents = array_column((new MLHUBAIIntentResolver)->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('ai_content_writer')
        ->and($message)->toContain('AI Content');
});

test('phase E1 regression credit question still returns credits', function (): void {
    $question = 'Tôi còn bao nhiêu tín dụng AI?';
    $intents = array_column((new MLHUBAIIntentResolver)->resolveAll($question), 'intent');

    expect($intents)->toContain('credits');
});

test('phase E2 new account start question stays onboarding not industry', function (): void {
    $question = 'Tôi mới tạo tài khoản thì bắt đầu từ đâu?';
    $intents = array_column((new MLHUBAIIntentResolver)->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
        'onboarding' => ['create_business', 'create_campaign'],
    ]));

    expect($intents[0])->toBe('onboarding')
        ->and($message)->toContain('cơ sở kinh doanh')
        ->and($message)->not->toContain('nhiều nhóm ngành');
});

test('phase E2 zero credit balance still states basic ai does not consume credits', function (): void {
    $message = (new MLHUBAIResponseComposer)->composeMany(['credits'], mlhubAssistantContext([
        'credits' => [
            'available' => true,
            'remaining' => 0,
            'used' => 50,
            'limit' => 50,
            'topup_remaining' => 0,
            'unlimited' => false,
            'low_balance' => true,
            'costs' => ['mlhub_ai_chat' => 1],
        ],
    ]));

    expect($message)->toContain('0')
        ->toContain('không trừ tín dụng AI')
        ->toContain('Câu trả lời có sử dụng số liệu thực tế');
});

test('phase E2 six industry question uses batch summary not single template', function (): void {
    $question = 'Tôi có quán cà phê, nhà hàng hải sản, spa, bán lẻ mỹ phẩm, khách sạn homestay và phòng khám nha khoa. MKT nên ưu tiên gì?';
    $resolver = new MLHUBAIIntentResolver;
    $composer = new MLHUBAIResponseComposer;
    $context = mlhubAssistantContext(['request' => ['question' => $question]]);
    $intents = array_column($resolver->resolveAll($question), 'intent');
    $message = $composer->composeMany($intents, $context);

    expect(count(MLHUBAIKnowledgeBase::detectMatchedIndustryGroups($question)))->toBeGreaterThanOrEqual(3)
        ->and($intents[0])->toBe('industry_recommendation')
        ->and($message)->toContain('nhiều nhóm ngành')
        ->and($message)->toContain('tri thức nội bộ')
        ->and($message)->not->toContain('Với spa, salon');
});

test('phase E2 creator operational question stays industry guidance not studio', function (): void {
    $question = 'Tôi làm creator livestream ecommerce thì nên dùng MKT thế nào?';
    $intents = array_column((new MLHUBAIIntentResolver)->resolveAll($question), 'intent');
    $message = (new MLHUBAIResponseComposer)->composeMany($intents, mlhubAssistantContext([
        'request' => ['question' => $question],
    ]));

    expect($intents[0])->toBe('industry_recommendation')
        ->and($intents)->not->toContain('ai_content_writer')
        ->and($message)->toContain('trang đích')
        ->and($message)->not->toContain('không viết caption');
});

test('phase E2 ai content tool advisory within industry does not studio handoff', function (): void {
    $question = 'AI Content nên dùng như công cụ nào trong ngành spa?';
    $intents = array_column((new MLHUBAIIntentResolver)->resolveAll($question), 'intent');

    expect($intents)->not->toContain('ai_content_writer')
        ->and($intents)->not->toContain('ai_studio')
        ->and(MLHUBAIKnowledgeBase::detectStudioHandoff($question))->toBeNull();
});

test('phase E2 assistant attaches request question after context build not inside cache payload', function (): void {
    $cachedContext = mlhubAssistantContext();
    $builder = new class($cachedContext) extends MLHUBAIContextBuilder
    {
        public function __construct(private array $context) {}

        public function build(int $userId): array
        {
            return $this->context;
        }
    };

    $service = new MLHUBAIAssistantService(
        new OptionStore,
        $builder,
        new MLHUBAIIntentResolver,
        new MLHUBAIResponseComposer,
    );

    $question = 'Tôi làm spa, nên dùng MLHUB tính năng nào trước?';
    $response = $service->ask(1, $question, false, false);

    expect($cachedContext)->not->toHaveKey('request')
        ->and($response['message'])->toContain('đặt lịch')
        ->and($response['intent'])->toBe('industry_recommendation');
});
