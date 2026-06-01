<?php

namespace Modules\AdminFaker\Support;

use Illuminate\Support\Str;

/**
 * Sinh FAQ/blog demo MLHUB (SOHO, hộ kinh doanh, Đà Nẵng) — tránh nhồi 500+ dòng vào file data.
 */
final class MLHUBMarketingContentGenerator
{
    /**
     * @return list<array{slug: string, title: string, content: string}>
     */
    public static function faqs(int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $items = [];
        $features = self::features();
        $districts = self::districts();
        $personas = self::personas();
        $angles = self::faqAngles();

        for ($i = 0; $i < $count; $i++) {
            $feature = $features[$i % count($features)];
            $district = $districts[($i * 3) % count($districts)];
            $persona = $personas[($i * 5) % count($personas)];
            $angle = $angles[($i * 7) % count($angles)];

            $title = match ($angle) {
                'how' => 'Làm sao '.$persona['label'].' tại '.$district.' dùng '.$feature['name'].' trên MLHUB?',
                'why' => 'Vì sao hộ kinh doanh '.$district.' nên dùng '.$feature['name'].' thay vì '.$feature['alt'].'?',
                'what' => $feature['name'].' trên MLHUB giúp '.$persona['short'].' làm gì?',
                'when' => 'Khi nào nên bật '.$feature['name'].' cho '.$persona['short'].' ở '.$district.'?',
                default => $persona['label'].' '.$district.': '.$feature['name'].' có phù hợp SOHO không?',
            };

            $items[] = [
                'slug' => 'demo-preview-faq-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT).'-'.Str::slug(Str::limit($title, 48, '')),
                'title' => $title,
                'content' => self::faqBody($feature, $district, $persona, $angle, $i),
            ];
        }

        return $items;
    }

    /**
     * @return list<array{slug: string, title: string, excerpt: string, content: string, category_index: int, tag_indexes: list<int>}>
     */
    public static function blogs(int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $items = [];
        $features = self::features();
        $districts = self::districts();
        $personas = self::personas();
        $hooks = self::blogHooks();

        for ($i = 0; $i < $count; $i++) {
            $feature = $features[($i * 2) % count($features)];
            $district = $districts[$i % count($districts)];
            $persona = $personas[($i + 1) % count($personas)];
            $hook = $hooks[$i % count($hooks)];

            $title = $hook.' — '.$persona['label'].' '.$district.' & '.$feature['short'];

            $excerpt = 'Hướng dẫn thực tế cho nhóm SOHO tại Đà Nẵng: triển khai '
                .$feature['short'].' trên MLHUB, đo lường lead và giữ trải nghiệm khách tại chỗ.';

            $items[] = [
                'slug' => 'demo-preview-blog-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT).'-'.Str::slug(Str::limit($title, 40, '')),
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => self::blogBody($feature, $district, $persona, $i),
                'category_index' => $i % 6,
                'tag_indexes' => [$i % 8, ($i + 3) % 8],
            ];
        }

        return $items;
    }

    /**
     * @return list<array{slug: string, name: string, name_en: string, description: string}>
     */
    public static function blogCategories(): array
    {
        return [
            [
                'slug' => 'demo-preview-huong-dan-ho-kinh-doanh-da-nang',
                'name' => 'Hướng dẫn hộ kinh doanh Đà Nẵng',
                'name_en' => 'Da Nang small business guides',
                'description' => 'Triển khai MLHUB cho spa, F&B, nha khoa và SOHO tại Đà Nẵng.',
            ],
            [
                'slug' => 'demo-preview-chien-dich-qr-landing',
                'name' => 'Chiến dịch QR & Landing',
                'name_en' => 'QR campaigns and landing pages',
                'description' => 'In QR, đổi trang đích, theo dõi quét và chuyển đổi.',
            ],
            [
                'slug' => 'demo-preview-crm-soho',
                'name' => 'CRM & khách hàng SOHO',
                'name_en' => 'CRM for SOHO teams',
                'description' => 'Tag khách, follow-up, automation và loyalty.',
            ],
            [
                'slug' => 'demo-preview-danh-gia-va-phan-hoi',
                'name' => 'Đánh giá & phản hồi',
                'name_en' => 'Reviews and feedback',
                'description' => 'Review Booster, feedback riêng và báo cáo chất lượng.',
            ],
            [
                'slug' => 'demo-preview-dat-lich-coupon',
                'name' => 'Đặt lịch & Coupon',
                'name_en' => 'Booking and coupons',
                'description' => 'Slot, xác nhận Zalo, mã ưu đãi và redemption.',
            ],
            [
                'slug' => 'demo-preview-van-hanh-nhieu-co-so',
                'name' => 'Vận hành nhiều cơ sở',
                'name_en' => 'Multi-location operations',
                'description' => 'Quản lý 11+ chi nhánh, QR design và analytics tập trung.',
            ],
        ];
    }

    /**
     * @return list<array{slug: string, name: string, name_en: string}>
     */
    public static function blogTags(): array
    {
        return [
            ['slug' => 'demo-preview-chien-dich-qr', 'name' => 'Chiến dịch QR', 'name_en' => 'QR Campaigns'],
            ['slug' => 'demo-preview-landing-page', 'name' => 'Landing page', 'name_en' => 'Landing Pages'],
            ['slug' => 'demo-preview-danh-gia-google', 'name' => 'Đánh giá Google', 'name_en' => 'Google Reviews'],
            ['slug' => 'demo-preview-ho-kinh-doanh', 'name' => 'Hộ kinh doanh', 'name_en' => 'Household business'],
            ['slug' => 'demo-preview-soho-da-nang', 'name' => 'SOHO Đà Nẵng', 'name_en' => 'Da Nang SOHO'],
            ['slug' => 'demo-preview-crm-mlhub', 'name' => 'CRM MLHUB', 'name_en' => 'MLHUB CRM'],
            ['slug' => 'demo-preview-email-tu-dong', 'name' => 'Email tự động', 'name_en' => 'Email automation'],
            ['slug' => 'demo-preview-loyalty', 'name' => 'Loyalty & referral', 'name_en' => 'Loyalty and referral'],
        ];
    }

    /**
     * @param  array{name: string, short: string, detail: string, alt: string, tip: string}  $feature
     * @param  array{label: string, short: string, context: string}  $persona
     */
    protected static function faqBody(array $feature, string $district, array $persona, string $angle, int $index): string
    {
        $p1 = '<p><strong>MLHUB</strong> là nền tảng SaaS marketing cho '
            .$persona['short'].' tại Đà Nẵng — gom '
            .$feature['short'].' vào một workspace thay vì dùng rời '
            .$feature['alt'].'. '.$persona['context'].' tại '.$district.' thường cần vận hành nhanh, ít nhân sự marketing.</p>';

        $p2 = '<p>'.$feature['detail'].' Trên dashboard bạn thấy lượt quét, form gửi, đặt lịch và đánh giá theo từng chiến dịch — phù hợp nhóm SOHO quản lý nhiều cơ sở (demo MLHUB có tới 11 hồ sơ kinh doanh).</p>';

        $p3 = match ($angle) {
            'how' => '<p><strong>Cách làm:</strong> tạo hồ sơ kinh doanh → chiến dịch QR → in mã tại quầy/menu → theo dõi 7–30 ngày đầu. '
                .$feature['tip'].' Nếu cần, gắn thêm tag CRM (VIP, khách quay lại) sau mỗi lead.</p>',
            'why' => '<p><strong>Lợi ích:</strong> giảm phụ thuộc inbox Facebook/Zalo rời, dữ liệu nằm trong CRM MLHUB. '
                .$feature['tip'].' Đặc biệt hữu ích mùa du lịch '.$district.' khi lượng khách walk-in tăng.</p>',
            'when' => '<p><strong>Thời điểm:</strong> sau khi có menu/standee QR ổn định, hoặc trước mùa cao điểm. '
                .$feature['tip'].' Có thể chạy song song landing độc lập và campaign QR cùng loại growth tool.</p>',
            default => '<p><strong>Gợi ý:</strong> '.$feature['tip'].' MLHUB hỗ trợ tiếng Việt, timezone Asia/Ho_Chi_Minh và báo cáo theo hộ kinh doanh — không bắt buộc team IT.</p>',
        };

        if ($index % 4 === 0) {
            $p3 .= '<p><em>Lưu ý:</em> Dữ liệu demo (slug <code>demo-preview-*</code>) chỉ minh họa; xóa bằng Admin Faker Clear mà không ảnh hưởng bản ghi production khác marker.</p>';
        }

        return $p1.$p2.$p3;
    }

    /**
     * @param  array{name: string, short: string, detail: string, alt: string, tip: string}  $feature
     * @param  array{label: string, short: string, context: string}  $persona
     */
    protected static function blogBody(array $feature, string $district, array $persona, int $index): string
    {
        $season = ['mùa hè biển', 'cuối tuần', 'dịp lễ', 'mùa du lịch', 'sáng sớm'][$index % 5];

        return '<p>'.$persona['label'].' khu vực <strong>'.$district.'</strong> đang dùng MLHUB để chuẩn hóa '
            .$feature['short'].' — một phần trong bộ growth tool (review, lead, coupon, feedback, booking) thay vì ghép nhiều app.</p>'
            .'<p>'.$feature['detail'].' Với '.$persona['context'].', điểm then chốt là <strong>đo được</strong>: bao nhiêu quét QR, bao nhiêu lead/booking, khách nào quay lại. MLHUB gom báo cáo theo chiến dịch và theo hồ sơ kinh doanh, tiện cho chủ hộ kinh doanh hoặc nhóm SOHO vận hành 5–11 cơ sở.</p>'
            .'<p>Trong '.$season.' tại Đà Nẵng, nhiều cửa hàng in QR trên menu, bàn hoặc standee Mỹ Khê/Hải Châu. '
            .$feature['tip'].' Landing page đồng bộ từ campaign giúp đổi ưu đãi sau Tết mà không in lại mã (QR động).</p>'
            .'<p>Nếu bạn mới bắt đầu: tạo một chiến dịch thử (vd review Google hoặc coupon) → gắn đúng chi nhánh → theo dõi 14 ngày → mở CRM gắn tag <em>khách quay lại</em>. '
            .'Các module nâng cao (email automation, loyalty tem, referral) có thể bật sau khi lượng khách ổn định.</p>'
            .'<p><strong>Kết luận:</strong> MLHUB không thay thế chất lượng dịch vụ tại chỗ, nhưng giúp '.$persona['short'].' tại '.$district.' biết funnel nào mang khách thật — phù hợp mô hình SaaS self-service cho thị trường Việt Nam.</p>';
    }

    /**
     * @return list<string>
     */
    protected static function faqAngles(): array
    {
        return ['how', 'why', 'what', 'when', 'fit'];
    }

    /**
     * @return list<string>
     */
    protected static function districts(): array
    {
        return [
            'quận Hải Châu',
            'quận Sơn Trà',
            'quận Ngũ Hành Sơn',
            'quận Thanh Khê',
            'quận Liên Chiểu',
            'quận Cẩm Lệ',
            'khu Mỹ Khê',
            'khu An Thượng',
            'Hòa Vang (Bà Nà)',
        ];
    }

    /**
     * @return list<array{label: string, short: string, context: string}>
     */
    protected static function personas(): array
    {
        return [
            ['label' => 'Quán ăn hộ kinh doanh', 'short' => 'quán ăn', 'context' => 'Menu QR, đặt bàn và review sau bữa'],
            ['label' => 'Spa & làm đẹp SOHO', 'short' => 'spa', 'context' => 'Đặt lịch massage, combo cuối tuần'],
            ['label' => 'Nha khoa / phòng khám', 'short' => 'nha khoa', 'context' => 'Lead tư vấn và lịch khám'],
            ['label' => 'Café & đồ uống', 'short' => 'café', 'context' => 'Feedback ly cà phê, coupon sáng'],
            ['label' => 'Homestay / lưu trú', 'short' => 'homestay', 'context' => 'Giữ phòng và review sau lưu trú'],
            ['label' => 'Tour & trải nghiệm', 'short' => 'tour', 'context' => 'Lead tour, slot Bà Nà'],
            ['label' => 'Phòng gym / fitness', 'short' => 'gym', 'context' => 'Tập thử, gói tháng'],
            ['label' => 'Tiệm tạp hóa / bán lẻ SOHO', 'short' => 'bán lẻ', 'context' => 'Khách quen, ưu đãi QR tại quầy'],
            ['label' => 'Salon / nail hộ cá thể', 'short' => 'salon', 'context' => 'Đặt lịch stylist, khách nữ'],
            ['label' => 'Hải sản / F&B biển', 'short' => 'nhà hàng', 'context' => 'Coupon tối, đặt bàn nhóm'],
        ];
    }

    /**
     * @return list<string>
     */
    protected static function blogHooks(): array
    {
        return [
            'Playbook 7 ngày',
            'Checklist triển khai',
            'Sai lầm thường gặp',
            'Case study SOHO',
            'Tối ưu chuyển đổi',
            'So sánh trước/sau MLHUB',
            'Hướng dẫn từng bước',
            'Câu chuyện chủ quán',
            'Metrics cần theo dõi',
            'Chuẩn bị mùa cao điểm',
        ];
    }

    /**
     * @return list<array{name: string, short: string, detail: string, alt: string, tip: string}>
     */
    protected static function features(): array
    {
        return [
            [
                'name' => 'Chiến dịch QR',
                'short' => 'chiến dịch QR',
                'detail' => 'Mỗi chiến dịch có slug, landing và loại growth tool (review, lead, coupon, feedback, booking, url).',
                'alt' => 'link Bitly/Google Form rời',
                'tip' => 'In cùng một mã QR tại quầy thu ngân và trên menu.',
            ],
            [
                'name' => 'Review Booster',
                'short' => 'Review Booster',
                'detail' => 'Điểm cao hướng khách sang Google Maps; điểm thấp vào kênh riêng để xử lý trước khi public.',
                'alt' => 'nhờ khách tự tìm link Google',
                'tip' => 'Đặt ngưỡng 4 sao phù hợp F&B và spa.',
            ],
            [
                'name' => 'Landing page',
                'short' => 'landing page',
                'detail' => 'Trang đích một mục tiêu chuyển đổi, template theo ngành, đồng bộ từ campaign hoặc tạo độc lập.',
                'alt' => 'website WordPress nặng',
                'tip' => 'Dùng template review/coupon/booking có sẵn trong MLHUB.',
            ],
            [
                'name' => 'Form lead',
                'short' => 'form lead',
                'detail' => 'Thu tên, SĐT, email; tự tạo hoặc cập nhật customer trong CRM.',
                'alt' => 'Google Form không gắn CRM',
                'tip' => 'Thêm field ghi chú nhu cầu (đặt bàn, tư vấn, báo giá).',
            ],
            [
                'name' => 'Đặt lịch (Booking)',
                'short' => 'đặt lịch online',
                'detail' => 'Khách chọn ngày/giờ; chủ xác nhận pending/confirmed trên portal.',
                'alt' => 'nhắn Zalo thủ công',
                'tip' => 'Gắn dịch vụ (massage 60p, khám tư vấn) cho từng business.',
            ],
            [
                'name' => 'Coupon QR',
                'short' => 'coupon QR',
                'detail' => 'Mã giảm giá, giới hạn lượt, trạng thái claimed/used trong báo cáo.',
                'alt' => 'phiếu giấy',
                'tip' => 'Đặt hạn Tết/cuối tuần và theo dõi redemption theo chi nhánh.',
            ],
            [
                'name' => 'Form feedback',
                'short' => 'feedback riêng',
                'detail' => 'Thu phản hồi chất lượng trước khi khách lên Google — giảm review tiêu cực bất ngờ.',
                'alt' => 'comment Facebook công khai',
                'tip' => 'Kết hợp với Review Booster trong cùng workspace.',
            ],
            [
                'name' => 'CRM khách hàng',
                'short' => 'CRM khách hàng',
                'detail' => 'Hồ sơ khách theo business, tag, điểm, lịch sử booking/coupon/feedback.',
                'alt' => 'Excel chia sẻ',
                'tip' => 'Dùng segment động cho khách quay lại 30 ngày.',
            ],
            [
                'name' => 'CRM nâng cao',
                'short' => 'CRM nâng cao (task, automation)',
                'detail' => 'Task follow-up, ghi chú team, automation khi lead mới hoặc điểm thấp.',
                'alt' => 'nhắc việc trên giấy',
                'tip' => 'Gán task “gọi lại” sau lead cuối tuần.',
            ],
            [
                'name' => 'Email automation',
                'short' => 'email automation',
                'detail' => 'Gửi mail theo trigger (lead mới, booking) với template hệ thống.',
                'alt' => 'gửi mail thủ công',
                'tip' => 'Bật sau khi đã có template và SMTP (Emailit trên MLHUB).',
            ],
            [
                'name' => 'Loyalty tem',
                'short' => 'thẻ loyalty tem',
                'detail' => 'Tích tem qua QR; đổi quà khi đủ số tem — phù hợp quán uống, café.',
                'alt' => 'thẻ giấy đục lỗ',
                'tip' => 'Đặt 8–10 tem và phần thưởng rõ ràng trên quầy.',
            ],
            [
                'name' => 'Referral',
                'short' => 'giới thiệu bạn bè (referral)',
                'detail' => 'Link giới thiệu, theo dõi conversion cho từng chiến dịch referral.',
                'alt' => 'chiết khấu miệng',
                'tip' => 'Chạy song song coupon cho khách mới.',
            ],
            [
                'name' => 'Hồ sơ & chi nhánh',
                'short' => 'hồ sơ kinh doanh & chi nhánh',
                'detail' => 'Nhiều location, QR design khác nhau, giờ mở cửa và link Zalo/Google Maps.',
                'alt' => 'một fanpage chung',
                'tip' => 'Mỗi chi nhánh một location trong MLHUB.',
            ],
            [
                'name' => 'Local Analytics',
                'short' => 'báo cáo Local Analytics',
                'detail' => 'Visit, conversion, nguồn quét theo thời gian — xem trên dashboard portal.',
                'alt' => 'đoán cảm tính',
                'tip' => 'So sánh campaign review vs coupon mỗi tháng.',
            ],
            [
                'name' => 'AI Content',
                'short' => 'AI Content',
                'detail' => 'Sinh nội dung caption/bài viết hỗ trợ marketing (tùy gói credit).',
                'alt' => 'copy thủ công',
                'tip' => 'Dùng AI soạn draft, chỉnh lại giọng địa phương Đà Nẵng.',
            ],
            [
                'name' => 'Team workspace',
                'short' => 'team workspace',
                'detail' => 'Nhiều user cùng quản lý CRM và chiến dịch theo team.',
                'alt' => 'share password một tài khoản',
                'tip' => 'Phân quyền cho lễ tân vs chủ hộ kinh doanh.',
            ],
        ];
    }
}
