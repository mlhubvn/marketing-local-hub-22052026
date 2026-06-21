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
                'ai cơ bản', 'ai co ban', 'ai nâng cao', 'ai nang cao',
                'không tốn token', 'khong ton token', 'không tốn credit', 'khong ton credit',
                'tốn credit', 'ton credit', 'credit không', 'credit khong', 'hỏi gì được',
                'hoi gi duoc', 'cách dùng', 'cach dung', 'dùng thế nào', 'dung the nao',
                'khác nhau thế nào', 'khac nhau the nao', 'khác gì', 'khac gi',
            ],
            'daily_briefing' => [
                'hôm nay', 'hom nay', 'sáng nay', 'sang nay', 'báo cáo ngày', 'bao cao ngay',
                'daily', 'briefing', 'tình hình hôm nay', 'tinh hinh hom nay', 'hôm qua',
                'hom qua', 'tuần này', 'tuan nay', 'cần chú ý', 'can chu y',
            ],
            'industry_recommendation' => [
                'quán cà phê', 'quan ca phe', 'quán cafe', 'quan cafe', 'quán trà sữa',
                'quan tra sua', 'trà sữa', 'tra sua', 'quán nước', 'quan nuoc',
                'quán ăn', 'quan an', 'nhà hàng', 'nha hang', 'hải sản', 'hai san',
                'spa', 'gội đầu dưỡng sinh', 'goi dau duong sinh', 'salon', 'nail',
                'làm đẹp', 'lam dep', 'bán lẻ', 'ban le', 'mỹ phẩm', 'my pham',
                'thời trang', 'thoi trang', 'tạp hóa', 'tap hoa', 'khách sạn',
                'khach san', 'homestay', 'du lịch', 'du lich', 'sửa chữa', 'sua chua',
                'điện lạnh', 'dien lanh', 'garage', 'rửa xe', 'rua xe', 'giặt ủi',
                'giat ui', 'trung tâm', 'trung tam', 'lớp học', 'lop hoc',
                'đào tạo', 'dao tao', 'giáo dục', 'giao duc', 'khóa học', 'khoa hoc',
                'phòng khám', 'phong kham', 'nha khoa', 'gym', 'yoga', 'fitness',
                'agency', 'tư vấn', 'tu van', 'kế toán', 'ke toan', 'pháp lý',
                'phap ly', 'bất động sản', 'bat dong san', 'môi giới', 'moi gioi',
                'nên dùng gì', 'nen dung gi',
                'dùng tính năng nào', 'dung tinh nang nao',
                'nên dùng tính năng nào đầu tiên', 'nen dung tinh nang nao dau tien',
                'bắt đầu từ đâu', 'bat dau tu dau',
            ],
            'onboarding' => [
                'bắt đầu', 'bat dau', 'setup', 'thiết lập', 'thiet lap', 'chưa có dữ liệu',
                'chua co du lieu', 'tạo đầu tiên', 'tao dau tien', 'cần làm gì trước',
                'can lam gi truoc', 'làm gì trước', 'lam gi truoc', 'mới tạo tài khoản',
                'moi tao tai khoan', 'tạo tài khoản', 'tao tai khoan', 'hướng dẫn', 'huong dan',
                'tạo cơ sở trước hay tạo chiến dịch trước', 'tao co so truoc hay tao chien dich truoc',
                'tạo cơ sở kinh doanh trước hay tạo chiến dịch trước',
                'tao co so kinh doanh truoc hay tao chien dich truoc',
            ],
            'top_campaigns' => [
                'chiến dịch tốt nhất', 'chien dich tot nhat', 'top campaign', 'campaign tốt nhất',
                'campaign tot nhat', 'hiệu quả nhất', 'hieu qua nhat', 'kém nhất', 'kem nhat',
                'cần cải thiện', 'can cai thien', 'đang tốt', 'dang tot',
            ],
            'qr_scans' => [
                'quét qr', 'quet qr', 'scan qr', 'qr scan', 'mã qr', 'ma qr', 'lượt quét',
                'luot quet', 'in mã', 'in ma', 'đặt mã', 'dat ma',
                'qr có nhiều lượt quét', 'qr co nhieu luot quet',
            ],
            'review_booster' => [
                'review booster', 'xin đánh giá', 'xin danh gia', 'đánh giá google',
                'danh gia google', '5 sao', 'rating thấp', 'rating thap', 'phản hồi riêng',
                'phan hoi rieng', 'không để lại đánh giá', 'khong de lai danh gia',
                'khách không để lại đánh giá', 'khach khong de lai danh gia', 'review',
            ],
            'booking' => [
                'đặt lịch', 'dat lich', 'booking', 'lịch hẹn', 'lich hen', 'slot',
                'dịch vụ', 'dich vu', 'khách đặt', 'khach dat',
            ],
            'coupon' => [
                'coupon', 'mã giảm giá', 'ma giam gia', 'voucher', 'ưu đãi', 'uu dai',
                'claim', 'khách nhận mã', 'khach nhan ma',
                'ưu đãi quay lại', 'uu dai quay lai', 'mã ưu đãi quay lại',
                'ma uu dai quay lai', 'khách quay lại', 'khach quay lai',
            ],
            'feedback' => [
                'feedback', 'phản hồi', 'phan hoi', 'góp ý', 'gop y', 'khách không hài lòng',
                'khach khong hai long', 'nps', 'rating thấp', 'rating thap',
                'đánh giá thấp', 'danh gia thap', 'khách đánh giá thấp', 'khach danh gia thap',
                'đánh giá 1 sao', 'danh gia 1 sao', 'đánh giá 2 sao', 'danh gia 2 sao',
                'đánh giá 3 sao', 'danh gia 3 sao', 'review xấu', 'review xau',
            ],
            'leads' => [
                'lead', 'khách tiềm năng', 'khach tiem nang', 'form tư vấn', 'form tu van',
                'yêu cầu báo giá', 'yeu cau bao gia', 'số điện thoại mới', 'so dien thoai moi',
                'để lại số điện thoại', 'de lai so dien thoai', 'lấy số điện thoại',
                'lay so dien thoai', 'thu số điện thoại', 'thu so dien thoai',
                'để lại thông tin', 'de lai thong tin', 'gọi lại', 'goi lai',
            ],
            'conversion' => [
                'chuyển đổi', 'chuyen doi', 'conversion', 'tỉ lệ', 'ti le', 'tỷ lệ', 'ty le',
                'hiệu suất', 'hieu suat', 'funnel', 'scan ra khách', 'scan ra khach',
                'kênh nào mang khách', 'kenh nao mang khach', 'nguồn nào mang khách',
                'nguon nao mang khach', 'kênh hiệu quả', 'kenh hieu qua',
                'nguồn hiệu quả', 'nguon hieu qua',
                'ít khách để lại thông tin', 'it khach de lai thong tin',
                'ít lead', 'it lead', 'nhiều lượt quét nhưng ít', 'nhieu luot quet nhung it',
            ],
            'credits' => [
                'credit', 'token', 'số dư', 'so du', 'hết credit', 'het credit',
                'mua thêm', 'mua them', 'usage', 'lịch sử dùng', 'lich su dung',
                'điểm tín dụng', 'diem tin dung', 'tốn điểm', 'ton diem',
                'còn bao nhiêu điểm', 'con bao nhieu diem', 'mua thêm điểm', 'mua them diem',
                'tín dụng AI', 'tin dung ai', 'tín dụng', 'tin dung',
                'số dư tín dụng', 'so du tin dung',
                'còn bao nhiêu tín dụng', 'con bao nhieu tin dung',
                'hết tín dụng', 'het tin dung',
                'không muốn tốn điểm tín dụng ai', 'khong muon ton diem tin dung ai',
                'không tốn điểm tín dụng ai', 'khong ton diem tin dung ai',
            ],
            'plan_limits' => [
                'giới hạn', 'gioi han', 'limit', 'gói hiện tại', 'goi hien tai',
                'tối đa', 'toi da', 'quota', 'hết lượt', 'het luot', 'nâng gói', 'nang goi',
            ],
            'business_locations' => [
                'chi nhánh', 'chi nhanh', 'địa điểm', 'dia diem', 'location', 'locations',
                'cơ sở con', 'co so con', 'mã qr địa điểm', 'ma qr dia diem',
                'qr địa điểm', 'qr dia diem', 'cửa hàng nào', 'cua hang nao',
                'chi nhánh nào kéo khách', 'chi nhanh nao keo khach',
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
                'khách nào cần chăm sóc lại', 'khach nao can cham soc lai',
                'khách cũ quay lại', 'khach cu quay lai', 'chăm sóc khách cũ tự động',
                'cham soc khach cu tu dong', 'nhắc nhân viên chăm sóc', 'nhac nhan vien cham soc',
                'ít khách quay lại', 'it khach quay lai', 'khách ít quay lại', 'khach it quay lai',
            ],
            'google_business' => [
                'google business', 'gbp', 'google profile', 'google location',
                'location google', 'địa điểm google', 'dia diem google', 'bài đăng google',
                'bai dang google', 'google insight', 'google insights',
                'tìm trên google', 'tim tren google', 'google nhiều người xem',
                'google nhieu nguoi xem', 'ít đặt bàn', 'it dat ban', 'ít đặt lịch', 'it dat lich',
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
                'mời nhân viên', 'moi nhan vien', 'thêm nhân viên', 'them nhan vien',
                'phân quyền nhân viên', 'phan quyen nhan vien', 'nhân sự', 'nhan su',
                'đội ngũ', 'doi ngu',
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
                'cập nhật thông tin quán', 'cap nhat thong tin quan',
                'sửa thông tin quán', 'sua thong tin quan', 'thông tin cửa hàng',
                'thong tin cua hang', 'hồ sơ quán', 'ho so quan',
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
            __('AI Cơ bản (Basic AI) có tốn tín dụng AI không?'),
            __('Có đặt lịch, mã ưu đãi hay khách tiềm năng mới tuần này không?'),
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
                __('AI Cơ bản (Basic AI) khác AI Nâng cao (Advanced AI) thế nào?'),
                __('Tôi có thể hỏi MLHUB AI những gì?'),
                __('Khi nào câu hỏi mới dùng tín dụng AI?'),
            ],
            'daily_briefing' => [
                __('Chỉ số nào cần chú ý nhất hôm nay?'),
                __('Hoạt động mới nhất đến từ chiến dịch nào?'),
                __('Tôi nên làm gì trong 30 phút tới?'),
            ],
            'industry_recommendation' => [
                __('Quán cà phê nên đặt QR ở đâu?'),
                __('Mã ưu đãi quay lại nên chạy thế nào?'),
                __('Làm sao lấy số điện thoại khách tại quầy?'),
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
                __('Đặt QR ở đâu để tăng lượt quét?'),
                __('Lượt quét đang chuyển đổi thành gì?'),
            ],
            'review_booster' => [
                __('Đánh giá nào cần phản hồi?'),
                __('Làm sao để tăng đánh giá 5 sao?'),
                __('Công cụ xin đánh giá nên đặt ở đâu?'),
            ],
            'booking' => [
                __('Tuần này có bao nhiêu lượt đặt lịch?'),
                __('Trang đặt lịch nào đang tốt?'),
                __('Làm sao để tăng lịch hẹn?'),
            ],
            'coupon' => [
                __('Mã ưu đãi nào được nhận nhiều nhất?'),
                __('Có nên chạy ưu đãi cuối tuần không?'),
                __('Làm sao tăng lượt nhận mã ưu đãi?'),
            ],
            'feedback' => [
                __('Form góp ý nào cần xử lý trước?'),
                __('Khách đang góp ý điểm gì?'),
                __('Có phản hồi tiêu cực nào không?'),
            ],
            'leads' => [
                __('Khách tiềm năng mới đến từ nguồn nào?'),
                __('Khách tiềm năng nào nên gọi trước?'),
                __('Làm sao tăng form tư vấn?'),
            ],
            'conversion' => [
                __('Tỉ lệ chuyển đổi tuần này ra sao?'),
                __('Bước nào trong phễu đang yếu?'),
                __('Làm sao tăng chuyển đổi nhanh nhất?'),
            ],
            'credits' => [
                __('AI Cơ bản (Basic AI) có dùng token không?'),
                __('Xem lịch sử tín dụng AI ở đâu?'),
                __('Khi nào cần mua thêm tín dụng AI?'),
            ],
            'plan_limits' => [
                __('Gói hiện tại giới hạn gì?'),
                __('Tôi cần nâng gói khi nào?'),
                __('Tính năng nào bị giới hạn theo gói?'),
            ],
            'business_locations' => [
                __('Thêm địa điểm hoặc chi nhánh ở đâu?'),
                __('QR địa điểm khác QR chiến dịch thế nào?'),
                __('Tôi nên quản lý các địa điểm ra sao?'),
            ],
            'customers' => [
                __('Xem danh sách khách hàng ở đâu?'),
                __('Nhóm khách nào nên chăm sóc lại?'),
                __('Khách mới và khách cũ khác nhau thế nào?'),
            ],
            'landing_pages' => [
                __('Tạo trang đích chiến dịch ở đâu?'),
                __('Khi nào mới có liên kết công khai để chia sẻ?'),
                __('Trang đích nên dùng cho mục tiêu nào?'),
            ],
            'marketing_templates' => [
                __('Dùng mẫu marketing ở đâu?'),
                __('Mẫu nào phù hợp cho chiến dịch cuối tuần?'),
                __('Mẫu có sẵn khác AI Studio thế nào?'),
            ],
            'crm_segments' => [
                __('Tạo nhóm khách hàng ở đâu?'),
                __('Nhãn và việc cần làm trong CRM dùng thế nào?'),
                __('Tự động hóa CRM giúp chăm sóc khách ra sao?'),
            ],
            'google_business' => [
                __('Kết nối Google Business ở đâu?'),
                __('Địa điểm Google dùng để làm gì?'),
                __('Xem chỉ số Google Business ở đâu?'),
            ],
            'google_reviews' => [
                __('Đồng bộ đánh giá Google ở đâu?'),
                __('Đánh giá Google nào cần trả lời?'),
                __('Tự động trả lời đánh giá hoạt động thế nào?'),
            ],
            'ai_studio' => [
                __('AI Studio làm được những gì?'),
                __('Xem lịch sử câu lệnh ở đâu?'),
                __('Cài đặt AI cho không gian làm việc ở đâu?'),
            ],
            'ai_content_writer' => [
                __('Viết chú thích bài đăng cho chiến dịch ở đâu?'),
                __('Tạo lời kêu gọi hành động cho trang đích thế nào?'),
                __('Viết nội dung có tốn tín dụng AI không?'),
            ],
            'billing' => [
                __('Xem hóa đơn ở đâu?'),
                __('Nâng cấp gói ở đâu?'),
                __('Thanh toán khác lịch sử tín dụng AI thế nào?'),
            ],
            'teams' => [
                __('Mời thành viên vào không gian làm việc ở đâu?'),
                __('Đổi không gian làm việc thế nào?'),
                __('Phân quyền đội ngũ cần chú ý gì?'),
            ],
            'support' => [
                __('Gửi phiếu hỗ trợ ở đâu?'),
                __('Tôi nên báo lỗi như thế nào?'),
                __('Theo dõi phiếu hỗ trợ ở đâu?'),
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
                __('Đánh giá nào cần trả lời?'),
                __('Làm sao để có thêm đánh giá 5 sao?'),
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
            'credits' => __('AI Cơ bản (Basic AI) có tốn tín dụng AI không?'),
            'booking' => __('Có lượt đặt lịch mới tuần này không?'),
            'coupon' => __('Mã ưu đãi nào đang được nhận nhiều?'),
            'leads' => __('Có khách tiềm năng mới nào cần xử lý không?'),
            'conversion' => __('Tỉ lệ chuyển đổi hiện tại thế nào?'),
            'landing_pages' => __('Tạo trang đích chiến dịch ở đâu?'),
            'google_business' => __('Kết nối Google Business ở đâu?'),
            'ai_studio' => __('AI Studio làm được những gì?'),
            'billing' => __('Xem hóa đơn và gói hiện tại ở đâu?'),
            'support' => __('Gửi phiếu hỗ trợ ở đâu?'),
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
                ['portal.chatmlhubai', __('Mở MLHUB AI')],
                ['portal.ai-studio.settings', __('Cài đặt AI')],
            ],
            'industry_recommendation' => [
                ['portal.businesses', __('Quản lý cơ sở kinh doanh')],
                ['portal.qr-campaigns', __('Quản lý chiến dịch')],
                ['portal.coupon-campaigns', __('Mở mã ưu đãi')],
            ],
            'daily_briefing' => [
                ['portal.dashboard', __('Mở bảng điều khiển')],
                ['portal.reports', __('Xem báo cáo')],
            ],
            'top_campaigns', 'qr_scans', 'campaigns', 'visits', 'overview' => [
                ['portal.reports', __('Xem báo cáo')],
                ['portal.qr-campaigns', __('Quản lý chiến dịch')],
            ],
            'review_booster', 'reviews' => [['portal.review-booster', __('Mở công cụ xin đánh giá')]],
            'booking' => [['portal.booking-pages', __('Mở trang đặt lịch')]],
            'coupon' => [['portal.coupon-campaigns', __('Mở mã ưu đãi')]],
            'feedback' => [['portal.feedback-forms', __('Mở form góp ý')]],
            'leads' => [
                ['portal.lead-forms', __('Mở form khách tiềm năng')],
                ['portal.customers', __('Mở khách hàng')],
            ],
            'conversion' => [['portal.reports', __('Xem báo cáo')]],
            'credits' => [
                ['portal.credits', __('Xem lịch sử tín dụng AI')],
                ['portal.ai-studio.settings', __('Cài đặt AI')],
            ],
            'plan_limits' => [['portal.packages', __('Xem gói dịch vụ')]],
            'business_locations' => [
                ['portal.locations', __('Mở địa điểm')],
                ['portal.businesses', __('Quản lý cơ sở kinh doanh')],
            ],
            'customers' => [
                ['portal.customers', __('Mở khách hàng')],
                ['portal.crm.customers', __('Mở khách hàng trong CRM')],
            ],
            'landing_pages' => [['portal.landing-pages', __('Mở trang đích')]],
            'marketing_templates' => [['portal.marketing-templates', __('Mở mẫu marketing')]],
            'crm_segments' => [
                ['portal.crm.segments', __('Mở nhóm khách hàng CRM')],
                ['portal.crm.customers', __('Mở khách hàng trong CRM')],
            ],
            'google_business', 'google_reviews' => [['portal.google-business', __('Mở Google Business')]],
            'ai_studio' => [
                ['portal.ai-studio', __('Mở AI Studio')],
                ['portal.ai-studio.prompt-history', __('Mở lịch sử câu lệnh')],
                ['portal.ai-studio.settings', __('Cài đặt AI')],
            ],
            'ai_content_writer' => [
                ['portal.ai-content', __('Mở công cụ viết nội dung AI')],
                ['portal.ai-studio', __('Mở AI Studio')],
            ],
            'billing' => [
                ['portal.billing', __('Mở thanh toán')],
                ['portal.invoices', __('Mở hóa đơn')],
                ['portal.packages', __('Xem gói dịch vụ')],
            ],
            'teams' => [['portal.teams', __('Mở đội ngũ')]],
            'support' => [['portal.support.index', __('Mở hỗ trợ')]],
            'new_customers' => [['portal.customers', __('Mở khách hàng')]],
            'businesses' => [['portal.businesses', __('Quản lý cơ sở kinh doanh')]],
            default => [],
        };
    }

    public static function intentPriority(string $intent): int
    {
        return [
            'help_using_mlhubai' => 10,
            'industry_recommendation' => 15,
            'onboarding' => 20,
            'daily_briefing' => 30,
            'overview' => 40,
            'customers' => 50,
            'new_customers' => 55,
            'reviews' => 60,
            'review_booster' => 65,
            'google_reviews' => 70,
            'google_business' => 75,
            'billing' => 80,
            'credits' => 85,
            'plan_limits' => 90,
            'teams' => 100,
            'support' => 110,
            'crm_segments' => 120,
            'business_locations' => 130,
            'businesses' => 135,
            'landing_pages' => 140,
            'marketing_templates' => 150,
            'ai_studio' => 160,
            'ai_content_writer' => 170,
            'top_campaigns' => 180,
            'campaigns' => 185,
            'qr_scans' => 190,
            'visits' => 195,
            'booking' => 200,
            'coupon' => 210,
            'feedback' => 220,
            'leads' => 230,
            'conversion' => 240,
            'next_steps' => 900,
            'greeting' => 950,
        ][$intent] ?? 500;
    }
}
