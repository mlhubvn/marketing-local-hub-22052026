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
        ->toContain('Câu trả lời dựa trên tri thức nội bộ và cấu trúc tính năng của MLHUB.')
        ->not->toContain('Câu trả lời có sử dụng số liệu thực tế từ tài khoản của bạn.');
});

test('composer adds account metrics footer for metric responses', function (): void {
    $message = (new MLHUBAIResponseComposer)->composeMany(
        ['customers', 'reviews'],
        mlhubAssistantContext(),
    );

    expect($message)
        ->toContain('Câu trả lời có sử dụng số liệu thực tế từ tài khoản của bạn.')
        ->not->toContain('Câu trả lời dựa trên tri thức nội bộ và cấu trúc tính năng của MLHUB.');
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
        ->toContain('Mở MLHUB AI')
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
        ->not->toContain('Open MLHUB AI')
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
        ->toContain('MLHUB AI')
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
            + substr_count($message, 'Câu trả lời dựa trên tri thức nội bộ và cấu trúc tính năng của MLHUB.');

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
        ->not->toContain('Mở MLHUB AI');
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
        ->toContain('Câu trả lời dựa trên tri thức nội bộ và cấu trúc tính năng của MLHUB.')
        ->not->toContain('Câu trả lời có sử dụng số liệu thực tế từ tài khoản của bạn.');
});

test('plan limits response uses real snapshot when available', function (): void {
    $message = (new MLHUBAIResponseComposer)->composeMany(
        ['plan_limits'],
        mlhubAssistantContext([
            'plan' => [
                'available' => true,
                'name' => 'MLHUB Growth',
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
        ->toContain('MLHUB Growth')
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
        ->toContain('Câu trả lời dựa trên tri thức nội bộ và cấu trúc tính năng của MLHUB.')
        ->not->toContain('Câu trả lời có sử dụng số liệu thực tế từ tài khoản của bạn.');
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
