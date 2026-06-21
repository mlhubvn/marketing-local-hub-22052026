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
        ->and($response['intent'])->toBe('help_using_mlhubai')
        ->and($response)->toHaveKey('metadata')
        ->and($response['metadata']['matched_keywords'])->toContain('basic ai')
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
