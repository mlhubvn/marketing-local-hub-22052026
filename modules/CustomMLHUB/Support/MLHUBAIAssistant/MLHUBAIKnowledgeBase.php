<?php

namespace Modules\CustomMLHUB\Support\MLHUBAIAssistant;

use Illuminate\Support\Str;

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
                'đại lý', 'dai ly', 'phân phối', 'phan phoi', 'bán sỉ', 'ban si',
                'nội thất', 'noi that', 'xây dựng', 'xay dung', 'sửa nhà', 'sua nha',
                'vận tải', 'van tai', 'giao hàng', 'giao hang', 'logistics',
                'creator', 'livestream', 'ecommerce', 'freelancer', 'online course',
                'ocop', 'xưởng', 'xuong', 'sản xuất', 'san xuat', 'gia công', 'gia cong',
                'nông sản', 'nong san', 'thủy sản', 'thuy san', 'nhà vườn', 'nha vuon',
                'karaoke', 'sân thể thao', 'san the thao', 'câu lạc bộ', 'cau lac bo',
                'hiệp hội', 'hiep hoi', 'cộng đồng', 'cong dong', 'chương trình chính quyền',
                'chuong trinh chinh quyen', 'chưa rõ ngành', 'chua ro nganh', 'nhiều ngành',
                'nhieu nganh',
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
                __('Tôi có thể hỏi MKT AI những gì?'),
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
                ['portal.chatmlhubai', __('Mở MKT AI')],
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

    /**
     * Studio handoff types (Chat → Studio), most specific first.
     *
     * @return list<string>
     */
    public static function studioHandoffDetectionOrder(): array
    {
        return [
            'review_reply_writing',
            'image_generation',
            'content_planner',
            'content_writing',
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function studioHandoffAliasMap(): array
    {
        return [
            'review_reply_writing' => [
                'tra loi review', 'tra loi review nay', 'tra loi review giup', 'giup toi tra loi review',
                'viet phan hoi review', 'viet tra loi review', 'tao phan hoi review',
                'review reply giup', 'phan hoi review bang ai', 'ai viet phan hoi review',
                'tra loi danh gia giup', 'viet phan hoi danh gia',
            ],
            'image_generation' => [
                'tao anh', 'tao hinh', 'banner ai', 'poster ai', 'anh khuyen mai',
                'tao banner', 'ai image', 'visual ai', 'tao poster', 'hinh khuyen mai',
            ],
            'content_planner' => [
                'lap lich noi dung', 'ke hoach noi dung', 'ke hoach bai dang',
                'content planner', 'lich dang bai', 'lap lich dang bai', 'calendar noi dung',
                'lap ke hoach bai dang', 'lich noi dung 7 ngay',
            ],
            'content_writing' => [
                'tao noi dung', 'viet caption', 'caption', 'noi dung facebook',
                'bai quang cao', 'viet bai', 'tao caption', 'viet noi dung',
                'viet bai quang cao', 'viet script', 'viet bai facebook',
                'bai facebook', 'viet bai cho', 'facebook cho', 'noi dung cho',
            ],
        ];
    }

    public static function detectStudioHandoff(string $normalizedQuestion): ?string
    {
        $types = self::detectStudioHandoffTypes($normalizedQuestion);

        if (count($types) >= 2) {
            return 'multi_studio';
        }

        return $types[0] ?? null;
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    public static function studioHandoffRouteActions(string $type): array
    {
        return match ($type) {
            'content_writing' => [
                ['portal.ai-content', __('Mở công cụ viết nội dung AI')],
                ['portal.ai-studio', __('Mở AI Studio')],
                ['portal.marketing-templates', __('Mở mẫu marketing')],
            ],
            'content_planner' => [
                ['portal.ai-content-planner', __('Mở lập lịch nội dung')],
                ['portal.ai-studio', __('Mở AI Studio')],
                ['portal.ai-studio.prompt-history', __('Mở lịch sử câu lệnh')],
            ],
            'review_reply_writing' => [
                ['portal.ai-studio.review-reply', __('Mở trả lời đánh giá AI')],
                ['portal.ai-studio', __('Mở AI Studio')],
                ['portal.ai-studio.prompt-history', __('Mở lịch sử câu lệnh')],
            ],
            'image_generation' => [
                ['portal.ai-image', __('Mở tạo ảnh AI')],
                ['portal.ai-studio', __('Mở AI Studio')],
                ['portal.marketing-templates', __('Mở mẫu marketing')],
            ],
            default => [],
        };
    }

    public static function studioHandoffMessage(string $type): string
    {
        return match ($type) {
            'content_writing' => __('Chat MKT AI không viết caption hay bài quảng cáo dài tại đây. Mở AI Content hoặc AI Studio để sinh nội dung, hoặc Marketing Templates nếu muốn mẫu sẵn. AI Cơ bản (Basic AI) không trừ tín dụng AI; tác vụ sinh nội dung trong Studio có thể dùng tín dụng AI theo gói.'),
            'content_planner' => __('Lập lịch nội dung nên làm trong AI Studio — mở Lập lịch nội dung hoặc AI Studio. Chat chỉ hướng dẫn mở đúng màn hình; AI Cơ bản không trừ tín dụng AI, tác vụ planner trong Studio có thể dùng tín dụng AI theo gói.'),
            'review_reply_writing' => __('Viết/trả lời review bằng AI nên mở Trả lời đánh giá AI trong AI Studio — Chat không soạn phản hồi dài tại đây. AI Cơ bản không trừ tín dụng AI; tác vụ trong Studio có thể dùng tín dụng AI theo gói.'),
            'image_generation' => __('Tạo ảnh/banner AI nên mở Tạo ảnh AI hoặc AI Studio — Chat không render hình tại đây. AI Cơ bản không trừ tín dụng AI; tác vụ tạo ảnh trong Studio có thể dùng tín dụng AI theo gói.'),
            default => '',
        };
    }

    public static function studioHandoffFocusIntent(string $type): string
    {
        return $type === 'content_writing' ? 'ai_content_writer' : 'ai_studio';
    }

    /**
     * @param  list<string>  $needles
     */
    protected static function containsNormalizedNeedle(string $normalized, array $needles): bool
    {
        foreach ($needles as $needle) {
            $normalizedNeedle = Str::ascii(mb_strtolower($needle));

            if ($normalizedNeedle !== '' && str_contains($normalized, $normalizedNeedle)) {
                return true;
            }
        }

        return false;
    }

    protected static function matchesCreatorContentWriting(string $normalized): bool
    {
        return self::containsNormalizedNeedle($normalized, ['creator', 'livestream', 'content creator', 'streamer'])
            && self::containsNormalizedNeedle($normalized, ['viet', 'tao', 'caption', 'noi dung', 'script', 'bai dang', 'hinh', 'anh']);
    }

    protected static function studioHandoffExcluded(string $normalized): bool
    {
        return self::containsNormalizedNeedle($normalized, [
            'review nao can tra loi',
            'danh gia google nao can',
            'dong bo review',
            'sync review',
            'auto reply',
            'review nao can phan hoi',
        ]);
    }

    public static function normalizeQuestion(string $question): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', Str::ascii(mb_strtolower($question))));
    }

    /**
     * @return list<string>
     */
    public static function detectMatchedIndustryGroups(string $question): array
    {
        $normalized = self::normalizeQuestion($question);

        if ($normalized === '') {
            return [];
        }

        $matched = [];

        foreach (self::industryGroupAliasMap() as $group => $aliases) {
            if (self::containsNormalizedNeedle($normalized, $aliases)) {
                $matched[] = $group;
            }
        }

        return array_values(array_unique($matched));
    }

    public static function isMultiIndustryBatchQuestion(string $question): bool
    {
        return count(self::detectMatchedIndustryGroups($question)) >= 3;
    }

    /**
     * @return array<string, array{groups: list<string>, summary: string}>
     */
    public static function industryBatchClusterDefinitions(): array
    {
        return [
            'service_at_point' => [
                'groups' => [
                    'food_beverage', 'retail_goods', 'beauty_personal_care', 'tourism_hospitality_experience',
                    'health_dental_fitness', 'technical_repair_maintenance', 'culture_entertainment_sports_community',
                ],
                'summary' => __('Dịch vụ tại điểm bán/F&B/Beauty: ưu tiên QR, Review Booster, mã ưu đãi và trang đặt lịch khi cần giữ khách quay lại.'),
            ],
            'b2b_pipeline' => [
                'groups' => [
                    'wholesale_distribution', 'professional_b2b_services', 'real_estate_rental_property',
                    'home_construction_interior', 'transport_delivery_logistics', 'education_training_coaching',
                ],
                'summary' => __('B2B/Wholesale/Professional/BĐS: ưu tiên trang đích, form khách tiềm năng và CRM để theo pipeline.'),
            ],
            'production_supply' => [
                'groups' => ['small_manufacturing_processing_ocop', 'agriculture_fisheries_local_supply'],
                'summary' => __('Sản xuất/OCOP/Nông sản: landing sản phẩm, form đại lý/báo giá sỉ và CRM phân phối.'),
            ],
            'community_program' => [
                'groups' => [
                    'organization_association_public_community', 'digital_creator_online_business',
                    'other_needs_classification',
                ],
                'summary' => __('Cộng đồng/Sự kiện/Tổ chức: landing chương trình, form đăng ký và báo cáo theo dõi.'),
            ],
        ];
    }

    /**
     * @param  list<string>  $matchedGroups
     * @return list<string>
     */
    public static function resolveIndustryBatchClusters(array $matchedGroups): array
    {
        $clusters = [];

        foreach (self::industryBatchClusterDefinitions() as $clusterKey => $definition) {
            foreach ($matchedGroups as $group) {
                if (in_array($group, $definition['groups'], true)) {
                    $clusters[] = $clusterKey;
                    break;
                }
            }
        }

        return array_slice(array_values(array_unique($clusters)), 0, 4);
    }

    /**
     * @param  list<string>  $clusterKeys
     */
    public static function industryBatchSummaryMessage(array $clusterKeys): string
    {
        $definitions = self::industryBatchClusterDefinitions();
        $lines = [__('Mình thấy bạn đang hỏi nhiều nhóm ngành, nên chia thành các cụm ưu tiên:')];

        foreach ($clusterKeys as $index => $clusterKey) {
            $summary = $definitions[$clusterKey]['summary'] ?? '';
            if ($summary !== '') {
                $lines[] = ($index + 1).'. '.$summary;
            }
        }

        $lines[] = __('Hãy chọn một cụm để triển khai trước, rồi hỏi lại chi tiết từng ngành nếu cần — Chat không gộp 18 nhóm thành một quy trình dài.');

        return implode(' ', $lines);
    }

    /**
     * @param  list<string>  $clusterKeys
     * @return list<array{0: string, 1: string}>
     */
    public static function industryBatchRouteActions(array $clusterKeys): array
    {
        $actions = in_array('service_at_point', $clusterKeys, true)
            ? [
                ['portal.qr-campaigns', __('Quản lý chiến dịch')],
                ['portal.lead-forms', __('Mở form khách tiềm năng')],
                ['portal.crm.customers', __('Mở khách hàng trong CRM')],
            ]
            : [
                ['portal.landing-pages', __('Mở trang đích')],
                ['portal.lead-forms', __('Mở form khách tiềm năng')],
                ['portal.reports', __('Xem báo cáo')],
            ];

        return array_slice($actions, 0, 3);
    }

    public static function isStudioToolAdvisoryQuestion(string $question): bool
    {
        $normalized = self::normalizeQuestion($question);

        if ($normalized === '' || self::hasStudioCreationIntent($normalized)) {
            return false;
        }

        if (! self::containsNormalizedNeedle($normalized, ['ai content', 'ai studio', 'mau marketing', 'marketing template'])) {
            return false;
        }

        return self::containsNormalizedNeedle($normalized, [
            'uu tien', 'nen uu tien', 'nen dung', 'nen chon', 'the nao', ' hay ', 'landing page',
            'trang dich', 'bao cao', 'crm', 'qr', 'cong cu nao', 'tinh nang nao', 'tool nao',
            'marketing templates', 'mau marketing', 'trong nganh', 'nganh nay',
        ]);
    }

    public static function hasStudioCreationIntent(string $normalized): bool
    {
        return self::containsNormalizedNeedle($normalized, [
            'viet ', 'tao ', 'sinh ', 'soan ', 'lap lich', 'tra loi review', 'giup toi tra loi',
            'viet caption', 'tao caption', 'tao bai', 'viet bai', 'tao anh', 'tao hinh', 'tao banner',
            'viet phan hoi', 'tao phan hoi',
        ]);
    }

    /**
     * @return list<string>
     */
    public static function detectStudioHandoffTypes(string $question): array
    {
        $normalized = self::normalizeQuestion($question);

        if ($normalized === ''
            || self::studioHandoffExcluded($normalized)
            || self::isStudioToolAdvisoryQuestion($question)) {
            return [];
        }

        $types = [];

        foreach (self::studioHandoffDetectionOrder() as $type) {
            if (! self::containsNormalizedNeedle($normalized, self::studioHandoffAliasMap()[$type] ?? [])) {
                continue;
            }

            if ($type === 'content_writing' && ! self::hasStudioCreationIntent($normalized)) {
                continue;
            }

            $types[] = $type;
        }

        if (self::matchesCreatorContentWriting($normalized)) {
            $types[] = 'content_writing';
        }

        return array_values(array_unique($types));
    }

    public static function isMultiScenarioOrderingQuestion(string $question): bool
    {
        $normalized = self::normalizeQuestion($question);

        if ($normalized === '') {
            return false;
        }

        $asksOrder = self::containsNormalizedNeedle($normalized, [
            'theo thu tu', 'thu tu nao', 'uu tien', 'lam gi truoc', 'nen lam gi truoc', 'xuly theo',
        ]);

        if (! $asksOrder) {
            return false;
        }

        $signals = 0;

        if (self::containsNormalizedNeedle($normalized, [
            'danh gia 1 sao', 'danh gia 2 sao', 'danh gia 3 sao', '1 sao', '2 sao', '3 sao',
            'khach 1 sao', 'khach 2 sao', 'khach 3 sao', 'danh gia thap', 'rating thap',
            'khach khong hai long', 'review xau',
        ])) {
            $signals++;
        }

        if (self::containsNormalizedNeedle($normalized, [
            'khong de lai danh gia', 'xin danh gia', 'lay danh gia', 'khong de lai review',
        ])) {
            $signals++;
        }

        if (self::containsNormalizedNeedle($normalized, [
            'google nhieu nguoi xem', 'tim tren google', 'it dat ban', 'it dat lich', 'it booking',
        ])) {
            $signals++;
        }

        return $signals >= 2;
    }

    public static function multiScenarioOrderingMessage(): string
    {
        return __('Với nhiều tình huống cùng lúc, nên ưu tiên theo thứ tự: (1) Xử lý khách không hài lòng bằng form góp ý riêng/CRM trước. (2) Sau đó mới xin đánh giá công khai bằng Review Booster. (3) Tối ưu Google Business. (4) Gắn trang đặt lịch hoặc trang đích để chuyển lượt xem thành đặt bàn/lead. Chat chỉ sắp xếp thứ tự — không soạn nội dung dài.');
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    public static function multiScenarioOrderingRouteActions(): array
    {
        return [
            ['portal.feedback-forms', __('Mở form góp ý')],
            ['portal.review-booster', __('Mở công cụ xin đánh giá')],
            ['portal.google-business', __('Mở Google Business')],
        ];
    }

    /**
     * @param  list<string>  $types
     */
    public static function studioMultiHandoffMessage(array $types): string
    {
        $parts = [__('Các tác vụ sinh nội dung này nên mở AI Studio và chia theo loại:')];
        $hints = [];

        if (in_array('content_writing', $types, true)) {
            $hints[] = __('caption/bài quảng cáo → AI Content');
        }

        if (in_array('content_planner', $types, true)) {
            $hints[] = __('lịch nội dung → Lập lịch nội dung/AI Studio');
        }

        if (in_array('review_reply_writing', $types, true)) {
            $hints[] = __('trả lời review → Trả lời đánh giá AI');
        }

        if (in_array('image_generation', $types, true)) {
            $hints[] = __('ảnh/banner → Tạo ảnh AI/AI Studio');
        }

        if ($hints !== []) {
            $parts[] = implode('; ', $hints).'.';
        }

        $parts[] = __('Chat không soạn dài tại đây; AI Cơ bản (Basic AI) không trừ tín dụng AI, tác vụ trong Studio có thể dùng tín dụng AI theo gói.');

        return implode(' ', $parts);
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    public static function studioMultiHandoffRouteActions(): array
    {
        return [
            ['portal.ai-studio', __('Mở AI Studio')],
            ['portal.ai-content', __('Mở công cụ viết nội dung AI')],
            ['portal.ai-studio.prompt-history', __('Mở lịch sử câu lệnh')],
        ];
    }

    /**
     * Detection order for industry group (more specific groups first).
     *
     * @return list<string>
     */
    public static function industryGroupDetectionOrder(): array
    {
        return [
            'other_needs_classification',
            'organization_association_public_community',
            'digital_creator_online_business',
            'small_manufacturing_processing_ocop',
            'agriculture_fisheries_local_supply',
            'wholesale_distribution',
            'real_estate_rental_property',
            'transport_delivery_logistics',
            'home_construction_interior',
            'culture_entertainment_sports_community',
            'professional_b2b_services',
            'education_training_coaching',
            'health_dental_fitness',
            'technical_repair_maintenance',
            'tourism_hospitality_experience',
            'beauty_personal_care',
            'retail_goods',
            'food_beverage',
        ];
    }

    /**
     * Alias keywords per BusinessTypeCatalog group (ASCII-friendly matching).
     *
     * @return array<string, list<string>>
     */
    public static function industryGroupAliasMap(): array
    {
        return [
            'food_beverage' => [
                'quan an', 'nha hang', 'hai san', 'quan ca phe', 'quan cafe', 'ca phe', 'cafe',
                'tra sua', 'quan tra sua', 'quan nuoc', 'nuoc ep', 'sinh to', 'tiem banh', 'bakery',
                'bar', 'pub', 'do an', 'food beverage', 'nau an', 'catering',
            ],
            'retail_goods' => [
                'ban le', 'cua hang', 'tap hoa', 'thoi trang', 'sieu thi', 'tien loi',
                'ban hang le', 'hang tieu dung', 'my pham ban le', 'shop thoi trang',
            ],
            'beauty_personal_care' => [
                'spa', 'salon', 'nail', 'goi dau', 'duong sinh', 'lam dep', 'tiem toc',
                'barber', 'cat toc', 'mi', 'makeup', 'skincare clinic',
            ],
            'tourism_hospitality_experience' => [
                'khach san', 'homestay', 'villa', 'resort', 'du lich', 'tour', 'luu tru',
                'travel', 'hostel', 'airbnb', 'experience tour',
            ],
            'health_dental_fitness' => [
                'phong kham', 'nha khoa', 'gym', 'yoga', 'fitness', 'pilates', 'tram rang',
                'phong tap', 'personal trainer', 'cham soc suc khoe',
            ],
            'technical_repair_maintenance' => [
                'sua chua', 'dien lanh', 'garage', 'rua xe', 'giat ui', 'bao tri',
                'tho dien', 'tho nuoc', 'sua xe', 'may lanh', 'dich vu sua chua',
            ],
            'education_training_coaching' => [
                'trung tam', 'trung tam tieng anh', 'trung tam tieng', 'lop hoc', 'dao tao',
                'giao duc', 'khoa hoc', 'hoc vien', 'gia su', 'coaching', 'day kem',
                'trung tam anh ngu', 'ielts', 'tieng anh',
            ],
            'wholesale_distribution' => [
                'dai ly', 'phan phoi', 'ban si', 'nha phan phoi', 'ctv ban si',
                'dai ly my pham', 'phan phoi my pham', 'phan phoi thuc pham', 'ho so si',
            ],
            'professional_b2b_services' => [
                'agency', 'tu van', 'ke toan', 'luat', 'phap ly', 'cong ty dich vu',
                'dich vu chuyen mon', 'b2b', 'marketing agency', 'consulting',
            ],
            'home_construction_interior' => [
                'noi that', 'xay dung', 'sua nha', 'kien truc', 'thiet ke noi that',
                'vat lieu xay dung', 'thi cong', 'rem cua', 'son nha',
            ],
            'transport_delivery_logistics' => [
                'van tai', 'giao hang', 'logistics', 'ship hang', 'cho thue xe',
                'taxi', 'tai xe', 'kho van', 'giao nhanh',
            ],
            'real_estate_rental_property' => [
                'bat dong san', 'bds', 'cho thue nha', 'cho thue mat bang', 'moi gioi nha dat',
                'van phong cho thue', 'can ho cho thue', 'moi gioi bat dong san',
            ],
            'digital_creator_online_business' => [
                'creator', 'content creator', 'livestream', 'streamer', 'ecommerce',
                'ban hang online', 'freelancer', 'khoa hoc online', 'shop online',
                'tiktok shop', 'ban online', 'kinh doanh online',
            ],
            'small_manufacturing_processing_ocop' => [
                'ocop', 'xuong', 'san xuat', 'gia cong', 'che bien', 'nha may nho',
                'lang nghe', 'go ocop', 'xuong san xuat',
            ],
            'agriculture_fisheries_local_supply' => [
                'nong san', 'thuy san', 'nha vuon', 'trang trai', 'nong trai',
                'vuon rau', 'ho nuoi', 'cung cap nong san', 'hang tuoi',
            ],
            'culture_entertainment_sports_community' => [
                'karaoke', 'san the thao', 'cau lac bo', 'su kien', 'rap phim', 'bowling',
                'phong tap cong cong', 'team building', 'am nhac', 'phong choi',
            ],
            'organization_association_public_community' => [
                'hiep hoi', 'cong dong', 'chuong trinh chinh quyen', 'xa phuong',
                'doan the', 'clb', 'association', 'to chuc cong dong', 'hoi nhom',
            ],
            'other_needs_classification' => [
                'chua ro nganh', 'nhieu nganh', 'lam nhieu nganh', 'chua phan loai',
                'chua biet nganh', 'moi dang ky chua ro', 'chua xac dinh nganh',
                'nhieu linh vuc', 'hon hop nganh',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function industryGroupAliases(string $group): array
    {
        return self::industryGroupAliasMap()[$group] ?? [];
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    public static function industryGroupRouteActions(string $group): array
    {
        return match ($group) {
            'food_beverage' => [
                ['portal.qr-campaigns', __('Quản lý chiến dịch')],
                ['portal.review-booster', __('Mở công cụ xin đánh giá')],
                ['portal.coupon-campaigns', __('Mở mã ưu đãi')],
            ],
            'retail_goods' => [
                ['portal.lead-forms', __('Mở form khách tiềm năng')],
                ['portal.coupon-campaigns', __('Mở mã ưu đãi')],
                ['portal.crm.customers', __('Mở khách hàng trong CRM')],
            ],
            'beauty_personal_care' => [
                ['portal.booking-pages', __('Mở trang đặt lịch')],
                ['portal.review-booster', __('Mở công cụ xin đánh giá')],
                ['portal.coupon-campaigns', __('Mở mã ưu đãi')],
            ],
            'tourism_hospitality_experience' => [
                ['portal.google-business', __('Mở Google Business')],
                ['portal.landing-pages', __('Mở trang đích')],
                ['portal.booking-pages', __('Mở trang đặt lịch')],
            ],
            'health_dental_fitness' => [
                ['portal.booking-pages', __('Mở trang đặt lịch')],
                ['portal.lead-forms', __('Mở form khách tiềm năng')],
                ['portal.feedback-forms', __('Mở form góp ý')],
            ],
            'technical_repair_maintenance' => [
                ['portal.lead-forms', __('Mở form khách tiềm năng')],
                ['portal.booking-pages', __('Mở trang đặt lịch')],
                ['portal.review-booster', __('Mở công cụ xin đánh giá')],
            ],
            'education_training_coaching' => [
                ['portal.landing-pages', __('Mở trang đích')],
                ['portal.lead-forms', __('Mở form khách tiềm năng')],
                ['portal.crm.customers', __('Mở khách hàng trong CRM')],
            ],
            'wholesale_distribution' => [
                ['portal.lead-forms', __('Mở form khách tiềm năng')],
                ['portal.crm.segments', __('Mở nhóm khách hàng CRM')],
                ['portal.landing-pages', __('Mở trang đích')],
            ],
            'professional_b2b_services' => [
                ['portal.landing-pages', __('Mở trang đích')],
                ['portal.lead-forms', __('Mở form khách tiềm năng')],
                ['portal.crm.customers', __('Mở khách hàng trong CRM')],
            ],
            'home_construction_interior' => [
                ['portal.lead-forms', __('Mở form khách tiềm năng')],
                ['portal.landing-pages', __('Mở trang đích')],
                ['portal.crm.customers', __('Mở khách hàng trong CRM')],
            ],
            'transport_delivery_logistics' => [
                ['portal.lead-forms', __('Mở form khách tiềm năng')],
                ['portal.landing-pages', __('Mở trang đích')],
                ['portal.reports', __('Xem báo cáo')],
            ],
            'real_estate_rental_property' => [
                ['portal.landing-pages', __('Mở trang đích')],
                ['portal.lead-forms', __('Mở form khách tiềm năng')],
                ['portal.crm.customers', __('Mở khách hàng trong CRM')],
            ],
            'digital_creator_online_business' => [
                ['portal.landing-pages', __('Mở trang đích')],
                ['portal.ai-content', __('Mở công cụ viết nội dung AI')],
                ['portal.marketing-templates', __('Mở mẫu marketing')],
            ],
            'small_manufacturing_processing_ocop' => [
                ['portal.landing-pages', __('Mở trang đích')],
                ['portal.lead-forms', __('Mở form khách tiềm năng')],
                ['portal.google-business', __('Mở Google Business')],
            ],
            'agriculture_fisheries_local_supply' => [
                ['portal.landing-pages', __('Mở trang đích')],
                ['portal.lead-forms', __('Mở form khách tiềm năng')],
                ['portal.crm.customers', __('Mở khách hàng trong CRM')],
            ],
            'culture_entertainment_sports_community' => [
                ['portal.booking-pages', __('Mở trang đặt lịch')],
                ['portal.landing-pages', __('Mở trang đích')],
                ['portal.coupon-campaigns', __('Mở mã ưu đãi')],
            ],
            'organization_association_public_community' => [
                ['portal.landing-pages', __('Mở trang đích')],
                ['portal.lead-forms', __('Mở form khách tiềm năng')],
                ['portal.reports', __('Xem báo cáo')],
            ],
            default => [
                ['portal.businesses', __('Quản lý cơ sở kinh doanh')],
                ['portal.qr-campaigns', __('Quản lý chiến dịch')],
                ['portal.lead-forms', __('Mở form khách tiềm năng')],
            ],
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
