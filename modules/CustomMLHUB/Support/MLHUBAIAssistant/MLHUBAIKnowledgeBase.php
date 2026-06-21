<?php

namespace Modules\CustomMLHUB\Support\MLHUBAIAssistant;

class MLHUBAIKnowledgeBase
{
    /**
     * Keyword matrix for Basic AI intent matching.
     *
     * @return array<string, list<string>>
     */
    public static function intentKeywords(): array
    {
        return [
            'help_using_mlhubai' => [
                'mlhub ai', 'trợ lý', 'tro ly', 'assistant', 'basic ai', 'advanced ai',
                'không tốn token', 'khong ton token', 'không tốn credit', 'khong ton credit',
                'tốn credit', 'ton credit', 'credit không', 'credit khong', 'hỏi gì được',
                'hoi gi duoc', 'cách dùng', 'cach dung', 'dùng thế nào', 'dung the nao',
            ],
            'daily_briefing' => [
                'hôm nay', 'hom nay', 'sáng nay', 'sang nay', 'báo cáo ngày', 'bao cao ngay',
                'daily', 'briefing', 'tình hình hôm nay', 'tinh hinh hom nay', 'hôm qua',
                'hom qua', 'tuần này', 'tuan nay', 'cần chú ý', 'can chu y',
            ],
            'onboarding' => [
                'bắt đầu', 'bat dau', 'setup', 'thiết lập', 'thiet lap', 'chưa có dữ liệu',
                'chua co du lieu', 'tạo đầu tiên', 'tao dau tien', 'cần làm gì trước',
                'can lam gi truoc', 'hướng dẫn', 'huong dan',
            ],
            'top_campaigns' => [
                'chiến dịch tốt nhất', 'chien dich tot nhat', 'top campaign', 'campaign tốt nhất',
                'campaign tot nhat', 'hiệu quả nhất', 'hieu qua nhat', 'kém nhất', 'kem nhat',
                'cần cải thiện', 'can cai thien', 'đang tốt', 'dang tot',
            ],
            'qr_scans' => [
                'quét qr', 'quet qr', 'scan qr', 'qr scan', 'mã qr', 'ma qr', 'lượt quét',
                'luot quet', 'in mã', 'in ma', 'đặt mã', 'dat ma',
            ],
            'review_booster' => [
                'review booster', 'xin đánh giá', 'xin danh gia', 'đánh giá google',
                'danh gia google', '5 sao', 'rating thấp', 'rating thap', 'phản hồi riêng',
                'phan hoi rieng', 'review',
            ],
            'booking' => [
                'đặt lịch', 'dat lich', 'booking', 'lịch hẹn', 'lich hen', 'slot',
                'dịch vụ', 'dich vu', 'khách đặt', 'khach dat',
            ],
            'coupon' => [
                'coupon', 'mã giảm giá', 'ma giam gia', 'voucher', 'ưu đãi', 'uu dai',
                'claim', 'khách nhận mã', 'khach nhan ma',
            ],
            'feedback' => [
                'feedback', 'phản hồi', 'phan hoi', 'góp ý', 'gop y', 'khách không hài lòng',
                'khach khong hai long', 'nps', 'rating thấp', 'rating thap',
            ],
            'leads' => [
                'lead', 'khách tiềm năng', 'khach tiem nang', 'form tư vấn', 'form tu van',
                'yêu cầu báo giá', 'yeu cau bao gia', 'số điện thoại mới', 'so dien thoai moi',
            ],
            'conversion' => [
                'chuyển đổi', 'chuyen doi', 'conversion', 'tỉ lệ', 'ti le', 'tỷ lệ', 'ty le',
                'hiệu suất', 'hieu suat', 'funnel', 'scan ra khách', 'scan ra khach',
            ],
            'credits' => [
                'credit', 'token', 'số dư', 'so du', 'hết credit', 'het credit',
                'mua thêm', 'mua them', 'usage', 'lịch sử dùng', 'lich su dung',
            ],
            'plan_limits' => [
                'giới hạn', 'gioi han', 'limit', 'gói hiện tại', 'goi hien tai',
                'tối đa', 'toi da', 'quota', 'hết lượt', 'het luot', 'nâng gói', 'nang goi',
            ],
            'business_locations' => [
                'chi nhánh', 'chi nhanh', 'địa điểm', 'dia diem', 'location', 'locations',
                'cơ sở con', 'co so con', 'mã qr địa điểm', 'ma qr dia diem',
                'qr địa điểm', 'qr dia diem', 'cửa hàng nào', 'cua hang nao',
            ],
            'customers' => [
                'khách hàng', 'khach hang', 'khách cũ', 'khach cu', 'khách quay lại',
                'khach quay lai', 'danh bạ', 'danh ba', 'data khách', 'data khach',
                'customer list', 'customers list', 'tệp khách', 'tep khach',
            ],
            'landing_pages' => [
                'landing page', 'landing pages', 'trang chiến dịch', 'trang chien dich',
                'trang công khai', 'trang cong khai', 'public page', 'link campaign',
                'copy link', 'form submit',
            ],
            'marketing_templates' => [
                'marketing template', 'marketing templates', 'mẫu marketing', 'mau marketing',
                'mẫu nội dung', 'mau noi dung', 'template', 'template pack',
                'mẫu chiến dịch', 'mau chien dich',
            ],
            'crm_segments' => [
                'crm', 'segment', 'segments', 'phân nhóm', 'phan nhom', 'tag', 'tags',
                'task', 'tasks', 'ghi chú khách', 'ghi chu khach', 'automation crm',
                'crm automation', 'chăm sóc khách', 'cham soc khach',
            ],
            'google_business' => [
                'google business', 'gbp', 'google profile', 'google location',
                'location google', 'địa điểm google', 'dia diem google', 'bài đăng google',
                'bai dang google', 'google insight', 'google insights',
            ],
            'google_reviews' => [
                'google review', 'google reviews', 'đánh giá google', 'danh gia google',
                'trả lời review', 'tra loi review', 'sync review', 'đồng bộ review',
                'dong bo review', 'auto reply', 'review google',
            ],
            'ai_studio' => [
                'ai studio', 'campaign builder', 'tạo nội dung', 'tao noi dung',
                'viết caption', 'viet caption', 'prompt history', 'lịch sử prompt',
                'lich su prompt', 'ai settings', 'cài đặt ai', 'cai dat ai',
            ],
            'ai_content_writer' => [
                'content writer', 'caption', 'bài viết', 'bai viet', 'nội dung facebook',
                'noi dung facebook', 'bài quảng cáo', 'bai quang cao', 'cta',
                'viết bài', 'viet bai', 'viết tin nhắn', 'viet tin nhan',
            ],
            'billing' => [
                'billing', 'thanh toán', 'thanh toan', 'hóa đơn', 'hoa don', 'invoice',
                'invoices', 'gói', 'goi', 'subscription', 'nâng cấp gói', 'nang cap goi',
            ],
            'teams' => [
                'team', 'teams', 'workspace', 'thành viên', 'thanh vien', 'phân quyền',
                'phan quyen', 'mời người', 'moi nguoi', 'đổi workspace', 'doi workspace',
            ],
            'support' => [
                'support', 'ticket', 'hỗ trợ', 'ho tro', 'liên hệ', 'lien he',
                'báo lỗi', 'bao loi', 'cần giúp', 'can giup', 'gửi ticket', 'gui ticket',
            ],
            'greeting' => [
                'xin chào', 'xin chao', 'chào', 'chao', 'hello', 'helo', 'hallo',
                'alo', 'hi bạn', 'hi ban',
            ],
            'new_customers' => [
                'khách mới', 'khach moi', 'customer mới', 'customer moi', 'new customer',
                'khách hàng mới', 'khach hang moi', 'tuần này có khách', 'tuan nay co khach',
                'có khách mới', 'co khach moi',
            ],
            'campaigns' => [
                'chiến dịch', 'chien dich', 'campaign', 'đang chạy', 'dang chay',
                'tổng hợp chiến dịch', 'tong hop chien dich', 'running campaign',
            ],
            'reviews' => [
                'đánh giá', 'danh gia', 'sao', 'rating', 'phản hồi review',
                'phan hoi review', 'đánh giá tuần', 'danh gia tuan',
            ],
            'next_steps' => [
                'làm gì', 'lam gi', 'gợi ý', 'goi y', 'next step', 'what should i do',
                'chiến dịch mới', 'chien dich moi', 'đề xuất', 'de xuat', 'nên làm',
                'nen lam',
            ],
            'overview' => [
                'tổng quan', 'tong quan', 'overview', 'báo cáo', 'bao cao', 'report',
                'tình hình', 'tinh hinh', 'kết quả', 'ket qua',
            ],
            'visits' => [
                'lượt truy cập', 'luot truy cap', 'visits', 'traffic',
            ],
            'businesses' => [
                'cơ sở', 'co so', 'danh sách cơ sở', 'danh sach co so', 'doanh nghiệp',
                'doanh nghiep', 'business', 'chi nhánh', 'chi nhanh', 'cửa hàng',
                'cua hang', 'địa điểm', 'dia diem',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function initialPrompts(): array
    {
        return [
            __('Sáng nay tình hình kinh doanh thế nào?'),
            __('Chiến dịch nào đang hiệu quả nhất?'),
            __('Basic AI có tốn credit không?'),
            __('Có booking, coupon hay lead mới tuần này không?'),
            __('Tôi nên ưu tiên làm gì tiếp theo?'),
        ];
    }

    /**
     * Drill-down prompt matrix by intent.
     *
     * @return array<string, list<string>>
     */
    public static function deepenPrompts(): array
    {
        return [
            'help_using_mlhubai' => [
                __('Basic AI khác Advanced AI thế nào?'),
                __('Tôi có thể hỏi MLHUB AI những gì?'),
                __('Khi nào câu hỏi mới dùng credit?'),
            ],
            'daily_briefing' => [
                __('Chỉ số nào cần chú ý nhất hôm nay?'),
                __('Hoạt động mới nhất đến từ chiến dịch nào?'),
                __('Tôi nên làm gì trong 30 phút tới?'),
            ],
            'onboarding' => [
                __('Tôi nên tạo gì đầu tiên?'),
                __('Cần bật tính năng nào để có dữ liệu?'),
                __('Làm sao để QR bắt đầu có lượt quét?'),
            ],
            'top_campaigns' => [
                __('Vì sao chiến dịch đó hiệu quả?'),
                __('Chiến dịch nào cần cải thiện?'),
                __('Tôi nên nhân bản chiến dịch nào?'),
            ],
            'qr_scans' => [
                __('Lượt quét tuần này thế nào?'),
                __('Đặt QR ở đâu để tăng scan?'),
                __('Scan đang chuyển đổi thành gì?'),
            ],
            'review_booster' => [
                __('Review nào cần phản hồi?'),
                __('Làm sao để tăng đánh giá 5 sao?'),
                __('Review Booster nên đặt ở đâu?'),
            ],
            'booking' => [
                __('Tuần này có bao nhiêu booking?'),
                __('Trang booking nào đang tốt?'),
                __('Làm sao để tăng lịch hẹn?'),
            ],
            'coupon' => [
                __('Coupon nào được nhận nhiều nhất?'),
                __('Có nên chạy ưu đãi cuối tuần không?'),
                __('Làm sao tăng lượt claim coupon?'),
            ],
            'feedback' => [
                __('Feedback nào cần xử lý trước?'),
                __('Khách đang góp ý điểm gì?'),
                __('Có phản hồi tiêu cực nào không?'),
            ],
            'leads' => [
                __('Lead mới đến từ nguồn nào?'),
                __('Lead nào nên gọi trước?'),
                __('Làm sao tăng form tư vấn?'),
            ],
            'conversion' => [
                __('Tỉ lệ chuyển đổi tuần này ra sao?'),
                __('Bước nào trong funnel đang yếu?'),
                __('Làm sao tăng conversion nhanh nhất?'),
            ],
            'credits' => [
                __('Basic AI có dùng token không?'),
                __('Xem lịch sử credit ở đâu?'),
                __('Khi nào cần mua thêm credit?'),
            ],
            'plan_limits' => [
                __('Gói hiện tại giới hạn gì?'),
                __('Tôi cần nâng gói khi nào?'),
                __('Tính năng nào bị quota giới hạn?'),
            ],
            'business_locations' => [
                __('Thêm địa điểm hoặc chi nhánh ở đâu?'),
                __('QR địa điểm khác QR campaign thế nào?'),
                __('Tôi nên quản lý các location ra sao?'),
            ],
            'customers' => [
                __('Xem danh sách khách hàng ở đâu?'),
                __('Nhóm khách nào nên chăm sóc lại?'),
                __('Khách mới và khách cũ khác nhau thế nào?'),
            ],
            'landing_pages' => [
                __('Tạo landing page chiến dịch ở đâu?'),
                __('Khi nào mới có public link để chia sẻ?'),
                __('Landing page nên dùng cho mục tiêu nào?'),
            ],
            'marketing_templates' => [
                __('Dùng mẫu marketing ở đâu?'),
                __('Mẫu nào phù hợp cho campaign cuối tuần?'),
                __('Template khác AI Studio thế nào?'),
            ],
            'crm_segments' => [
                __('Tạo segment khách hàng ở đâu?'),
                __('Tag và task CRM dùng thế nào?'),
                __('CRM automation giúp chăm sóc khách ra sao?'),
            ],
            'google_business' => [
                __('Kết nối Google Business ở đâu?'),
                __('Google locations dùng để làm gì?'),
                __('Xem insight Google Business ở đâu?'),
            ],
            'google_reviews' => [
                __('Đồng bộ Google review ở đâu?'),
                __('Review Google nào cần trả lời?'),
                __('Auto reply review hoạt động thế nào?'),
            ],
            'ai_studio' => [
                __('AI Studio làm được những gì?'),
                __('Xem lịch sử prompt ở đâu?'),
                __('Cài đặt AI workspace ở đâu?'),
            ],
            'ai_content_writer' => [
                __('Viết caption chiến dịch ở đâu?'),
                __('Tạo CTA cho landing page thế nào?'),
                __('Viết nội dung có tốn credit không?'),
            ],
            'billing' => [
                __('Xem hóa đơn ở đâu?'),
                __('Nâng cấp gói ở đâu?'),
                __('Billing khác Credit Usage thế nào?'),
            ],
            'teams' => [
                __('Mời thành viên vào workspace ở đâu?'),
                __('Đổi workspace thế nào?'),
                __('Phân quyền team cần chú ý gì?'),
            ],
            'support' => [
                __('Gửi ticket hỗ trợ ở đâu?'),
                __('Tôi nên báo lỗi như thế nào?'),
                __('Theo dõi ticket hỗ trợ ở đâu?'),
            ],
            'new_customers' => [
                __('Khách mới đến từ nguồn nào?'),
                __('So với tuần trước thế nào?'),
                __('Làm sao để có thêm khách mới?'),
            ],
            'campaigns' => [
                __('Chiến dịch nào hiệu quả nhất?'),
                __('Chiến dịch nào cần cải thiện?'),
                __('Tạo chiến dịch mới thế nào?'),
            ],
            'reviews' => [
                __('Review nào cần trả lời?'),
                __('Làm sao để có thêm review 5 sao?'),
                __('Điểm đánh giá trung bình là bao nhiêu?'),
            ],
            'visits' => [
                __('Lượt truy cập đến từ đâu?'),
                __('Tỉ lệ chuyển đổi là bao nhiêu?'),
                __('Làm sao để tăng lượt quét QR?'),
            ],
            'businesses' => [
                __('Cơ sở nào hiệu quả nhất?'),
                __('Thêm cơ sở mới thế nào?'),
                __('Cập nhật thông tin cơ sở ở đâu?'),
            ],
            'next_steps' => [
                __('Gợi ý chiến dịch cuối tuần'),
                __('Tôi nên ưu tiên việc gì trước?'),
                __('Làm sao tăng doanh thu nhanh?'),
            ],
            'overview' => [
                __('Chỉ số nào đang giảm?'),
                __('Tuần này có gì nổi bật?'),
                __('Tôi nên làm gì tiếp theo?'),
            ],
            'greeting' => [],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function explorePrompts(): array
    {
        return [
            'daily_briefing' => __('Sáng nay tình hình kinh doanh thế nào?'),
            'top_campaigns' => __('Chiến dịch nào đang hiệu quả nhất?'),
            'credits' => __('Basic AI có tốn credit không?'),
            'booking' => __('Có booking mới tuần này không?'),
            'coupon' => __('Coupon nào đang được nhận nhiều?'),
            'leads' => __('Có lead mới nào cần xử lý không?'),
            'conversion' => __('Tỉ lệ chuyển đổi hiện tại thế nào?'),
            'landing_pages' => __('Tạo landing page chiến dịch ở đâu?'),
            'google_business' => __('Kết nối Google Business ở đâu?'),
            'ai_studio' => __('AI Studio làm được những gì?'),
            'billing' => __('Xem hóa đơn và gói hiện tại ở đâu?'),
            'support' => __('Gửi ticket hỗ trợ ở đâu?'),
            'next_steps' => __('Tôi nên ưu tiên làm gì tiếp theo?'),
        ];
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    public static function routeActions(string $intent): array
    {
        return match ($intent) {
            'help_using_mlhubai' => [
                ['portal.chatmlhubai', __('Open MLHUB AI')],
                ['portal.ai-studio.settings', __('AI settings')],
            ],
            'daily_briefing' => [
                ['portal.dashboard', __('Open dashboard')],
                ['portal.reports', __('View reports')],
            ],
            'top_campaigns', 'qr_scans', 'campaigns', 'visits', 'overview' => [
                ['portal.reports', __('View reports')],
                ['portal.qr-campaigns', __('Manage campaigns')],
            ],
            'review_booster', 'reviews' => [['portal.review-booster', __('Open Review Booster')]],
            'booking' => [['portal.booking-pages', __('Open bookings')]],
            'coupon' => [['portal.coupon-campaigns', __('Open coupons')]],
            'feedback' => [['portal.feedback-forms', __('Open feedback forms')]],
            'leads' => [
                ['portal.lead-forms', __('Open lead forms')],
                ['portal.customers', __('Open Customers')],
            ],
            'conversion' => [['portal.reports', __('View reports')]],
            'credits' => [['portal.credits', __('Open credit usage')]],
            'plan_limits' => [['portal.packages', __('View packages')]],
            'business_locations' => [
                ['portal.locations', __('Open locations')],
                ['portal.businesses', __('Manage businesses')],
            ],
            'customers' => [
                ['portal.customers', __('Open Customers')],
                ['portal.crm.customers', __('Open CRM customers')],
            ],
            'landing_pages' => [['portal.landing-pages', __('Open landing pages')]],
            'marketing_templates' => [['portal.marketing-templates', __('Open marketing templates')]],
            'crm_segments' => [
                ['portal.crm.segments', __('Open CRM segments')],
                ['portal.crm.customers', __('Open CRM customers')],
            ],
            'google_business', 'google_reviews' => [['portal.google-business', __('Open Google Business')]],
            'ai_studio' => [
                ['portal.ai-studio', __('Open AI Studio')],
                ['portal.ai-studio.prompt-history', __('Open prompt history')],
                ['portal.ai-studio.settings', __('AI settings')],
            ],
            'ai_content_writer' => [
                ['portal.ai-content', __('Open AI Content')],
                ['portal.ai-studio', __('Open AI Studio')],
            ],
            'billing' => [
                ['portal.billing', __('Open billing')],
                ['portal.invoices', __('Open invoices')],
                ['portal.packages', __('View packages')],
            ],
            'teams' => [['portal.teams', __('Open teams')]],
            'support' => [['portal.support.index', __('Open support')]],
            'new_customers' => [['portal.customers', __('Open Customers')]],
            'businesses' => [['portal.businesses', __('Manage businesses')]],
            default => [],
        };
    }
}
