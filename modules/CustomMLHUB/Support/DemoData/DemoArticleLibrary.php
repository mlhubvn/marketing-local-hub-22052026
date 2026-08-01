<?php

namespace Modules\CustomMLHUB\Support\DemoData;

/**
 * Thư viện nội dung demo dài, có cấu trúc, tiếng Việt có dấu cho blog & FAQ.
 *
 * Tách riêng khỏi DemoContentCatalog vì khối lượng nội dung lớn. Nội dung được
 * sinh theo chủ đề (phát hiện từ khoá trong tiêu đề) để mỗi bài đủ dài, khác nhau
 * và phù hợp ngữ cảnh hộ kinh doanh tại Đà Nẵng - Quảng Nam.
 */
class DemoArticleLibrary
{
    /**
     * @return list<string>
     */
    public static function titles(): array
    {
        return [
            'MKT giúp hộ kinh doanh địa phương tăng trưởng như thế nào',
            'Vì sao hộ kinh doanh cần một bộ tăng trưởng số gọn nhẹ',
            'Từ mã QR đến CRM: hành trình dữ liệu của một cơ sở kinh doanh',
            'Marketing automation cho hộ kinh doanh nên bắt đầu từ đâu',
            'Chuyển đổi số hộ kinh doanh tại Đà Nẵng và Quảng Nam',
            'Cơ hội tăng trưởng cho spa, salon và cà phê tại Đà Nẵng',
            'Vì sao Google Maps quan trọng với cơ sở kinh doanh địa phương',
            'Bài toán dữ liệu khách hàng cho cửa hàng nhỏ tại Đà Nẵng',
            'Cách tối ưu hồ sơ Google Business cho quán cà phê',
            'Vì sao đánh giá thật quan trọng hơn review ảo',
            'Quy trình xin đánh giá khách hàng đúng cách',
            'Những lỗi phổ biến khi quản lý Google Maps',
            'Google Maps, review và niềm tin địa phương',
            'Dùng mã QR để thu phản hồi khách hàng tại quầy',
            'QR ưu đãi giúp quán ăn kéo khách quay lại ra sao',
            'Biến lượt quét QR thành dữ liệu khách hàng',
            'Vì sao feedback xấu vẫn là dữ liệu tốt',
            'Tạo form thu khách tiềm năng cho hộ kinh doanh',
            'Mã ưu đãi giúp khách quay lại như thế nào',
            'Loyalty card cho quán cà phê và spa nhỏ',
            'Giữ khách cũ rẻ hơn tìm khách mới',
            'Thiết kế ưu đãi không làm giảm giá trị thương hiệu',
            'Winback campaign cho khách cũ sau mua hàng',
            'CRM đơn giản cho hộ kinh doanh',
            'Phân nhóm khách hàng theo hành vi QR và coupon',
            'Tự động nhắc lịch cho spa và salon',
            'Chăm sóc khách sau khi dùng dịch vụ bằng automation',
            'Task CRM giúp chủ hộ không bỏ sót khách',
            'Bộ tăng trưởng số cho spa và gội đầu dưỡng sinh',
            'Bộ tăng trưởng số cho quán cà phê',
            'Bộ tăng trưởng số cho nhà hàng hải sản',
            'Bộ tăng trưởng số cho homestay',
            'Bộ tăng trưởng số cho cửa hàng đặc sản và OCOP',
            'Bộ tăng trưởng số cho garage và rửa xe',
            'Bộ tăng trưởng số cho nha khoa và phòng khám',
            'Bộ tăng trưởng số cho trung tâm giáo dục',
            'Bộ tăng trưởng số cho bất động sản cho thuê',
            'Hộ kinh doanh nên theo dõi những chỉ số nào',
            'QR scans, leads, bookings và coupon claims nói lên điều gì',
            'Báo cáo 7 ngày, 30 ngày, 90 ngày cho chủ hộ',
            'Cách đọc dashboard tăng trưởng địa phương',
            'Khi nào chiến dịch local marketing được xem là hiệu quả',
            'AI có thể hỗ trợ chủ hộ kinh doanh ra sao',
            'Dữ liệu khách hàng địa phương là tài sản tăng trưởng',
            'Từ feedback đến gợi ý chiến dịch bằng AI',
            'AI hỗ trợ trả lời review như thế nào',
            'Vì sao MKT dùng dữ liệu hành vi thay vì đoán mò',
            'Không nên mua review ảo',
            'Cách xin đánh giá khách hàng đúng chuẩn',
            'Bảo vệ dữ liệu khách hàng trong hộ kinh doanh',
            'Tôn trọng quyền riêng tư khi thu lead',
            'Dữ liệu demo và dữ liệu thật khác nhau như thế nào',
            'Quy trình tạo chiến dịch QR review trong 15 phút',
            'Tạo coupon theo mùa vụ cho cửa hàng nhỏ',
            'Khách quay lại và bài toán chăm sóc sau mua',
            'Đo hiệu quả tăng trưởng bằng dữ liệu gắn với hành vi',
            'Cách dùng landing page cho một ưu đãi địa phương',
            'Khi nào nên dùng booking page thay vì form liên hệ',
            'Lập danh sách khách hàng từ lead form như thế nào',
            'Phân biệt khách mới, khách cũ và khách nguy cơ rời bỏ',
            'Lịch chăm sóc khách 30 ngày cho spa nhỏ',
            'Chiến dịch review an toàn cho ngành nha khoa',
            'Cách dùng CRM task cho dịch vụ B2B địa phương',
            'Mô hình partner quản lý nhiều cơ sở bằng MKT',
            'Từ quán cà phê một điểm đến chuỗi nhỏ nhiều cơ sở',
            'Cách nhìn số liệu scan theo giờ cao điểm và thấp điểm',
            'Cách xử lý review 1 sao một cách bình tĩnh',
            'Tạo Google Business post mô phỏng cho lịch nội dung',
            'Email automation nên gửi lúc nào để không làm phiền khách',
            'Webhook demo và webhook thật khác nhau ở đâu',
            'WhatsApp và Zalo trong demo nên hiểu như kênh mô phỏng',
            'Phân khúc khách hàng theo giá trị vòng đời',
            'Chăm sóc khách VIP bằng tag và ghi chú CRM',
            'Giá trị của feedback riêng tư trước khi xin Google Review',
            'Những chỉ số nhà đầu tư nên nhìn trong demo MKT',
            'Câu chuyện dữ liệu của một homestay An Thượng',
            'Câu chuyện dữ liệu của một tiệm nail Hải Châu',
            'Câu chuyện dữ liệu của một quán hải sản Mỹ Khê',
            'Câu chuyện dữ liệu của một cửa hàng OCOP',
            'Tại sao số liệu lẻ tự nhiên đáng tin hơn số tròn',
            'Cách đọc conversion từ QR sang lead',
            'Cách đọc conversion từ coupon sang khách quay lại',
            'Mô phỏng 24 tháng dữ liệu cho buổi trình bày BOD',
            'Checklist trước khi trình bày dashboard MKT',
            'Lợi ích của dữ liệu tách biệt theo tenant trong SaaS địa phương',
            'Hành trình từ khách lạ đến khách trung thành tại cửa hàng nhỏ',
        ];
    }

    /**
     * Đoạn mô tả ngắn cho mỗi bài blog.
     */
    public static function excerpt(string $title, int $index): string
    {
        $angles = [
            'Phân tích thực tế dành cho hộ kinh doanh tại Đà Nẵng và Quảng Nam, kèm các bước triển khai cụ thể.',
            'Bài viết giải thích vì sao chủ đề này quan trọng và cách áp dụng ngay cho cơ sở của bạn.',
            'Hướng dẫn ngắn gọn, dễ áp dụng cho cửa hàng nhỏ muốn tăng trưởng bền vững bằng dữ liệu.',
            'Góc nhìn từ MKT về cách biến hoạt động hằng ngày thành dữ liệu và doanh thu.',
        ];

        return rtrim($title, '.').' — '.$angles[$index % count($angles)];
    }

    /**
     * Nội dung bài blog dài, có cấu trúc HTML, tiếng Việt có dấu.
     */
    public static function article(string $title, int $index): string
    {
        $theme = self::detectTheme($title);
        $blocks = [];

        $blocks[] = '<p>'.self::intro($title, $theme).'</p>';
        $blocks[] = '<p>Tại MKT, chúng tôi tin rằng một hộ kinh doanh tại Đà Nẵng dù nhỏ vẫn có thể vận hành bài bản như doanh nghiệp lớn, '
            .'miễn là biết biến từng lượt khách thành dữ liệu và từng dữ liệu thành quyết định. Bài viết này phân tích chủ đề '
            .'<strong>'.e($title).'</strong> theo hướng thực dụng, để bạn có thể áp dụng ngay cho cơ sở của mình.</p>';

        foreach (self::sections($theme, $index) as $heading => $body) {
            $blocks[] = '<h2>'.$heading.'</h2>';
            $blocks[] = '<p>'.$body.'</p>';
        }

        $blocks[] = '<h2>Một quy trình mẫu bạn có thể áp dụng</h2>';
        $blocks[] = '<ul>'
            .'<li>Bước 1: Khai báo cơ sở và ngành nghề để MKT gợi ý đúng loại chiến dịch.</li>'
            .'<li>Bước 2: Tạo mã QR đặt tại quầy, bàn hoặc hoá đơn để thu hành vi khách hàng.</li>'
            .'<li>Bước 3: Kết nối landing page và form thu thông tin khách tiềm năng.</li>'
            .'<li>Bước 4: Đưa khách vào CRM, gắn thẻ và lên lịch chăm sóc tự động.</li>'
            .'<li>Bước 5: Theo dõi báo cáo 7 / 30 / 90 ngày để điều chỉnh ưu đãi.</li>'
            .'</ul>';

        $blocks[] = '<h2>Những con số nói lên điều gì</h2>';
        $blocks[] = '<p>'.self::metrics($theme).' Quan trọng hơn con số tuyệt đối là <em>xu hướng</em>: số lượt quét tăng dần, '
            .'tỷ lệ chuyển đổi từ quét sang để lại thông tin được cải thiện, và tỷ lệ khách quay lại nhích lên theo từng tháng. '
            .'Đó mới là dấu hiệu của tăng trưởng bền vững.</p>';

        $blocks[] = '<h2>Kết luận</h2>';
        $blocks[] = '<p>'.self::conclusion($theme).' MKT đóng vai trò là bộ tăng trưởng số gọn nhẹ: gom dữ liệu khách hàng, '
            .'mã QR, đánh giá thật, ưu đãi, CRM và báo cáo về một nơi. Nhờ đó chủ hộ kinh doanh không cần nhiều công cụ rời rạc '
            .'mà vẫn nắm được bức tranh tăng trưởng của cơ sở.</p>';
        $blocks[] = '<p><strong>Bắt đầu ngay hôm nay:</strong> tạo chiến dịch QR đầu tiên, thu thập đánh giá thật và để dữ liệu '
            .'dẫn dắt quyết định kinh doanh của bạn.</p>';

        return implode("\n", $blocks);
    }

    /**
     * @return list<array{q: string, a: string}>
     */
    public static function faqs(): array
    {
        return [
            ['q' => 'MKT là gì?', 'a' => 'MKT là bộ tăng trưởng số dành cho hộ kinh doanh và cơ sở địa phương, giúp gom mã QR, đánh giá thật, ưu đãi, dữ liệu khách hàng và CRM về một nền tảng duy nhất.'],
            ['q' => 'MKT phù hợp với ai?', 'a' => 'MKT phù hợp với quán cà phê, nhà hàng, spa, salon, homestay, cửa hàng đặc sản, phòng khám và các hộ kinh doanh muốn tăng trưởng bằng dữ liệu thay vì cảm tính.'],
            ['q' => 'MKT có phải phần mềm bán hàng hay POS không?', 'a' => 'Không. MKT tập trung vào tăng trưởng và chăm sóc khách hàng: thu dữ liệu qua QR, xin đánh giá, phát ưu đãi và quản lý quan hệ khách hàng, chứ không thay thế phần mềm bán hàng.'],
            ['q' => 'MKT khác gì so với chạy quảng cáo?', 'a' => 'Quảng cáo giúp kéo khách mới đến một lần, còn MKT giúp giữ dữ liệu khách hàng và chăm sóc để họ quay lại nhiều lần, biến chi phí một lần thành tài sản lâu dài.'],
            ['q' => 'MKT có dùng được cho hộ kinh doanh nhỏ không?', 'a' => 'Có. MKT được thiết kế gọn nhẹ, có gói miễn phí để hộ kinh doanh nhỏ bắt đầu mà không cần kiến thức kỹ thuật.'],
            ['q' => 'Gói Free dùng được những gì?', 'a' => 'Gói Free cho phép quản lý một cơ sở, tạo một số chiến dịch QR và landing page cơ bản, đủ để trải nghiệm quy trình thu dữ liệu và xin đánh giá.'],
            ['q' => 'Khi nào nên nâng lên Starter?', 'a' => 'Khi bạn cần nhiều chiến dịch QR hơn, nhiều landing page hơn và bắt đầu muốn quản lý danh sách khách hàng một cách bài bản.'],
            ['q' => 'Gói Growth phù hợp với ai?', 'a' => 'Gói Growth phù hợp với cơ sở có nhiều điểm bán hoặc nhiều cơ sở, cần CRM nâng cao, phân khúc khách hàng và tự động hóa chăm sóc.'],
            ['q' => 'Gói Pro khác Growth ở điểm nào?', 'a' => 'Gói Pro mở rộng giới hạn cơ sở, chiến dịch, automation và tích hợp Google Business, phù hợp với chuỗi nhỏ đang tăng trưởng nhanh.'],
            ['q' => 'Gói Partner dành cho ai?', 'a' => 'Gói Partner dành cho đối tác triển khai quản lý nhiều hộ kinh doanh cùng lúc, với giới hạn rất cao về số cơ sở và chiến dịch.'],
            ['q' => 'Có thể quản lý nhiều cơ sở không?', 'a' => 'Có. Từ gói Growth trở lên, bạn có thể tạo và quản lý nhiều cơ sở, mỗi cơ sở có chiến dịch và dữ liệu khách hàng riêng.'],
            ['q' => 'Có thể đổi gói sau không?', 'a' => 'Có. Bạn có thể nâng hoặc hạ gói bất cứ lúc nào; dữ liệu khách hàng và chiến dịch của bạn vẫn được giữ nguyên.'],
            ['q' => 'Cần nhập thông tin gì khi tạo cơ sở?', 'a' => 'Bạn cần nhập tên cơ sở, ngành nghề, địa chỉ, số điện thoại và giờ mở cửa. Thông tin này giúp MKT gợi ý đúng loại chiến dịch.'],
            ['q' => 'Có thể tạo nhiều địa điểm không?', 'a' => 'Có. Mỗi cơ sở có thể có nhiều điểm bán, hữu ích cho chuỗi quán hoặc cửa hàng có nhiều chi nhánh.'],
            ['q' => 'Ngành nghề dùng để làm gì?', 'a' => 'Ngành nghề giúp MKT đưa ra gợi ý chiến dịch, mẫu landing page và nội dung phù hợp với đặc thù kinh doanh của bạn.'],
            ['q' => 'Nếu không thấy ngành của mình thì chọn gì?', 'a' => 'Bạn có thể chọn nhóm "Nhu cầu khác / phân loại sau" và vẫn dùng đầy đủ tính năng; có thể cập nhật ngành chính xác hơn về sau.'],
            ['q' => 'MKT có tự gợi ý chiến dịch theo ngành không?', 'a' => 'Có. Dựa trên ngành nghề bạn khai báo, MKT gợi ý các loại chiến dịch QR, ưu đãi và landing page phù hợp.'],
            ['q' => 'QR trong MKT dùng để làm gì?', 'a' => 'Mã QR dùng để xin đánh giá, phát ưu đãi, thu khách tiềm năng, nhận đặt lịch và thu phản hồi ngay tại điểm bán.'],
            ['q' => 'Có thể tạo QR xin review không?', 'a' => 'Có. Chiến dịch loại Review giúp hướng khách hài lòng để lại đánh giá thật, đồng thời lọc phản hồi tiêu cực để xử lý riêng.'],
            ['q' => 'Có thể tạo QR phát ưu đãi không?', 'a' => 'Có. Chiến dịch loại Coupon cho phép phát mã ưu đãi qua QR và theo dõi mã đã nhận, đã dùng hay hết hạn.'],
            ['q' => 'Có thể theo dõi lượt quét QR không?', 'a' => 'Có. MKT ghi nhận từng lượt quét kèm thời gian, thiết bị và khu vực để bạn biết chiến dịch nào hiệu quả.'],
            ['q' => 'Khách quét QR có cần cài app không?', 'a' => 'Không. Khách chỉ cần dùng camera điện thoại để quét và mở trang web, không cần cài đặt ứng dụng nào.'],
            ['q' => 'Review Booster là gì?', 'a' => 'Đây là quy trình xin đánh giá thông minh: khách hài lòng được hướng tới đánh giá công khai, khách chưa hài lòng được mời gửi phản hồi riêng để bạn khắc phục.'],
            ['q' => 'MKT có tạo review ảo không?', 'a' => 'Không. MKT chỉ giúp thu đánh giá thật từ khách hàng thật; chúng tôi không tạo và không khuyến khích review ảo.'],
            ['q' => 'Feedback xấu được xử lý thế nào?', 'a' => 'Phản hồi tiêu cực được ghi nhận riêng tư để bạn liên hệ và khắc phục, thay vì hiển thị công khai ngay, giúp bảo vệ uy tín cơ sở.'],
            ['q' => 'Có thể chuyển khách hài lòng sang Google Review không?', 'a' => 'Có. Sau khi khách thể hiện hài lòng, MKT có thể hướng họ tới trang Google Review của cơ sở.'],
            ['q' => 'Có thể xem báo cáo review không?', 'a' => 'Có. Bạn xem được số lượng đánh giá, điểm trung bình và xu hướng theo thời gian.'],
            ['q' => 'Booking Pages dùng cho ngành nào?', 'a' => 'Phù hợp với spa, salon, nha khoa, phòng khám, trung tâm dịch vụ và bất kỳ cơ sở nào nhận đặt lịch trước.'],
            ['q' => 'Lead Forms dùng để thu thông tin gì?', 'a' => 'Form thu tên, số điện thoại, email và nhu cầu của khách tiềm năng để bạn chủ động tư vấn và chăm sóc.'],
            ['q' => 'Coupon Campaigns hoạt động như thế nào?', 'a' => 'Bạn tạo mã ưu đãi, phát qua QR hoặc landing page, và theo dõi trạng thái nhận, dùng, hết hạn của từng mã.'],
            ['q' => 'Có phân biệt coupon đã nhận và đã dùng không?', 'a' => 'Có. Mỗi mã ưu đãi có trạng thái rõ ràng giúp bạn đo lường hiệu quả thật của chương trình.'],
            ['q' => 'Có thể tạo nhiều ưu đãi cho nhiều cơ sở không?', 'a' => 'Có, tùy theo giới hạn của gói. Mỗi cơ sở có thể có chương trình ưu đãi riêng.'],
            ['q' => 'CRM trong MKT có khó dùng không?', 'a' => 'Không. CRM được thiết kế đơn giản cho chủ hộ kinh doanh: xem khách, gắn thẻ, ghi chú và lên lịch chăm sóc một cách trực quan.'],
            ['q' => 'Khách hàng được tạo từ đâu?', 'a' => 'Khách hàng được tạo tự động từ lượt quét QR, form thu lead, đặt lịch và sử dụng coupon, không phải nhập tay.'],
            ['q' => 'Có thể gắn thẻ khách hàng không?', 'a' => 'Có. Bạn gắn thẻ như VIP, khách mới, khách quay lại để phân nhóm và chăm sóc phù hợp.'],
            ['q' => 'Có thể tạo task chăm sóc khách không?', 'a' => 'Có. Bạn tạo việc cần làm như gọi lại, gửi ưu đãi, xác nhận lịch và giao cho thành viên trong nhóm.'],
            ['q' => 'Customer score là gì?', 'a' => 'Là điểm thể hiện mức độ tương tác và giá trị của khách, giúp bạn ưu tiên chăm sóc đúng người.'],
            ['q' => 'Có xem lịch sử tương tác của khách không?', 'a' => 'Có. Mỗi khách có dòng thời gian ghi lại các lần quét QR, nhận coupon, đặt lịch và để lại đánh giá.'],
            ['q' => 'MKT có tự động gửi email không?', 'a' => 'Có. Bạn có thể thiết lập automation gửi email cảm ơn, nhắc lịch hoặc chăm sóc sau dịch vụ theo điều kiện.'],
            ['q' => 'Có thể tự động nhắc khách quay lại không?', 'a' => 'Có. Bạn cài automation để nhắc khách sau một khoảng thời gian hoặc sau khi sử dụng dịch vụ.'],
            ['q' => 'Webhook dùng để làm gì?', 'a' => 'Webhook giúp gửi dữ liệu sự kiện sang hệ thống khác của bạn, phục vụ tích hợp nâng cao.'],
            ['q' => 'WhatsApp và Zalo có được tích hợp không?', 'a' => 'MKT hỗ trợ kênh nhắn tin để chăm sóc khách; trong bản demo các kênh này là mô phỏng, không gửi tin thật.'],
            ['q' => 'Automation có gửi thật trong demo không?', 'a' => 'Không. Toàn bộ email, webhook, WhatsApp trong dữ liệu demo chỉ là mô phỏng, không gọi nhà cung cấp thật.'],
            ['q' => 'MKT có kết nối Google Business không?', 'a' => 'Có. Bạn có thể kết nối hồ sơ Google Business để quản lý đánh giá và bài đăng tập trung.'],
            ['q' => 'Google Business mock trong demo là gì?', 'a' => 'Là dữ liệu Google Business mô phỏng để minh họa giao diện và báo cáo, không lấy từ tài khoản Google thật.'],
            ['q' => 'MKT có giúp quản lý review Google không?', 'a' => 'Có. Bạn xem, phản hồi và theo dõi đánh giá Google ngay trong MKT.'],
            ['q' => 'Có tự động trả lời review không?', 'a' => 'Có. Bạn cài quy tắc tự động trả lời theo điểm đánh giá và từ khoá, với chế độ nháp hoặc gửi.'],
            ['q' => 'Có nên mua review Google không?', 'a' => 'Không nên. Review ảo vi phạm chính sách và làm mất niềm tin; MKT hướng tới đánh giá thật bền vững.'],
            ['q' => 'Dashboard hiển thị những chỉ số nào?', 'a' => 'Dashboard hiển thị lượt quét QR, khách tiềm năng, đặt lịch, coupon đã dùng, đánh giá và tỷ lệ chuyển đổi.'],
            ['q' => 'Báo cáo 7/30/90 ngày dùng để làm gì?', 'a' => 'Giúp bạn so sánh hiệu quả ngắn hạn và dài hạn, nhận ra xu hướng tăng trưởng hoặc dấu hiệu chững lại.'],
            ['q' => 'Conversion rate được hiểu như thế nào?', 'a' => 'Là tỷ lệ khách thực hiện hành động mong muốn, ví dụ từ quét QR sang để lại thông tin hoặc từ coupon sang quay lại.'],
            ['q' => 'Vì sao số liệu demo không tròn?', 'a' => 'Vì hành vi khách hàng thật không bao giờ tròn trịa; số liệu lẻ tự nhiên giúp buổi trình bày đáng tin hơn.'],
            ['q' => 'Dữ liệu khách hàng có được bảo vệ không?', 'a' => 'Có. Dữ liệu được tách biệt theo từng tài khoản và chỉ chủ sở hữu mới truy cập được dữ liệu của mình.'],
            ['q' => 'MKT có phù hợp với thị trường Đà Nẵng không?', 'a' => 'Rất phù hợp. MKT được xây dựng với ngữ cảnh hộ kinh doanh Đà Nẵng - Quảng Nam: du lịch, ẩm thực, dịch vụ địa phương.'],
            ['q' => 'Những ngành nào nên dùng MKT trước?', 'a' => 'Ẩm thực, làm đẹp, lưu trú và dịch vụ có khách quay lại thường thấy hiệu quả sớm nhất.'],
            ['q' => 'Spa, cà phê, homestay dùng MKT như thế nào?', 'a' => 'Họ dùng QR để xin đánh giá và phát ưu đãi, dùng CRM để chăm sóc khách quay lại và đo hiệu quả qua dashboard.'],
            ['q' => 'OCOP và đặc sản có dùng được MKT không?', 'a' => 'Có. Cửa hàng đặc sản, OCOP dùng landing page và form thu đơn, kết hợp ưu đãi mùa vụ để tăng doanh thu.'],
            ['q' => 'MKT có phù hợp cho đối tác triển khai nhiều hộ kinh doanh không?', 'a' => 'Có. Gói Partner cho phép quản lý số lượng lớn cơ sở và chiến dịch, phù hợp với đối tác triển khai.'],
            ['q' => 'Dữ liệu demo có gọi API thật không?', 'a' => 'Không. Dữ liệu demo hoàn toàn mô phỏng, không gọi API, không gửi email, webhook hay tin nhắn thật.'],
            ['q' => 'Blog demo trong MKT dùng để làm gì?', 'a' => 'Blog demo minh họa khả năng nội dung của hệ thống và cung cấp kiến thức tăng trưởng cho chủ hộ kinh doanh.'],
            ['q' => 'FAQ demo có thể sửa lại sau không?', 'a' => 'Có. Toàn bộ blog và FAQ demo có thể chỉnh sửa hoặc thay thế bằng nội dung thật của bạn.'],
            ['q' => 'Landing page khác QR campaign như thế nào?', 'a' => 'QR campaign là điểm chạm để khách quét, còn landing page là trang đích hiển thị ưu đãi và form; chúng thường kết hợp với nhau.'],
            ['q' => 'Loyalty card khác coupon ở điểm nào?', 'a' => 'Coupon là ưu đãi một lần, còn loyalty card tích lũy theo nhiều lần ghé để thưởng cho khách trung thành.'],
            ['q' => 'Referral campaign phù hợp ngành nào?', 'a' => 'Phù hợp với hầu hết dịch vụ có khách hài lòng sẵn sàng giới thiệu, như spa, cà phê, nhà hàng, homestay.'],
            ['q' => 'Khi nào nên dùng CRM segment?', 'a' => 'Khi bạn muốn gửi đúng ưu đãi cho đúng nhóm, ví dụ khách VIP, khách mới 30 ngày hoặc khách cần winback.'],
            ['q' => 'Task overdue trên CRM có ý nghĩa gì?', 'a' => 'Là việc chăm sóc khách đã quá hạn cần xử lý ngay, giúp bạn không bỏ sót cơ hội giữ khách.'],
            ['q' => 'Google review thấp có nên trả lời không?', 'a' => 'Nên. Trả lời lịch sự và cầu thị thể hiện sự chuyên nghiệp và thường giúp xoay chuyển ấn tượng của người đọc.'],
            ['q' => 'AI trong MKT có thay chủ kinh doanh quyết định không?', 'a' => 'Không. AI chỉ hỗ trợ gợi ý nội dung và phản hồi; quyết định cuối cùng luôn thuộc về chủ hộ kinh doanh.'],
        ];
    }

    private static function detectTheme(string $title): string
    {
        $t = self::ascii($title);

        return match (true) {
            str_contains($t, 'google') || str_contains($t, 'maps') => 'google',
            str_contains($t, 'review') || str_contains($t, 'danh gia') || str_contains($t, 'feedback') || str_contains($t, 'phan hoi') => 'review',
            str_contains($t, 'qr') || str_contains($t, 'quet') => 'qr',
            str_contains($t, 'crm') || str_contains($t, 'task') || str_contains($t, 'phan khuc') || str_contains($t, 'cham soc') => 'crm',
            str_contains($t, 'coupon') || str_contains($t, 'uu dai') || str_contains($t, 'loyalty') || str_contains($t, 'referral') || str_contains($t, 'gioi thieu') => 'offer',
            str_contains($t, 'ai') => 'ai',
            str_contains($t, 'dashboard') || str_contains($t, 'chi so') || str_contains($t, 'bao cao') || str_contains($t, 'conversion') || str_contains($t, 'so lieu') => 'metrics',
            str_contains($t, 'automation') || str_contains($t, 'email') || str_contains($t, 'webhook') || str_contains($t, 'whatsapp') || str_contains($t, 'zalo') => 'automation',
            str_contains($t, 'bo tang truong') || str_contains($t, 'spa') || str_contains($t, 'ca phe') || str_contains($t, 'homestay') || str_contains($t, 'ocop') || str_contains($t, 'nha khoa') => 'industry',
            default => 'general',
        };
    }

    private static function ascii(string $value): string
    {
        $value = mb_strtolower($value);
        $map = ['à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ','è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ','ì','í','ị','ỉ','ĩ','ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ','ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ','ỳ','ý','ỵ','ỷ','ỹ','đ'];
        $rep = ['a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','e','e','e','e','e','e','e','e','e','e','e','i','i','i','i','i','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','u','u','u','u','u','u','u','u','u','u','u','y','y','y','y','y','d'];

        return str_replace($map, $rep, $value);
    }

    private static function intro(string $title, string $theme): string
    {
        return match ($theme) {
            'google' => 'Với hộ kinh doanh địa phương, hồ sơ Google Business và bản đồ Google Maps thường là điểm chạm đầu tiên giữa khách và cửa hàng. Một hồ sơ đầy đủ, nhiều đánh giá thật sẽ tạo lợi thế cạnh tranh rõ rệt ngay trong khu phố của bạn.',
            'review' => 'Đánh giá thật là tài sản uy tín quý giá nhất của một cơ sở kinh doanh nhỏ. Một quy trình xin đánh giá đúng cách vừa tăng số sao trên bản đồ, vừa giúp bạn lắng nghe và cải thiện trải nghiệm khách hàng.',
            'qr' => 'Mã QR đặt đúng chỗ có thể biến mỗi lượt khách ghé qua thành một điểm dữ liệu. Thay vì để khách rời đi mà không để lại dấu vết, bạn thu được hành vi, phản hồi và thông tin liên hệ ngay tại điểm bán.',
            'crm' => 'Khi lượng khách đủ lớn, trí nhớ con người không còn đủ để chăm sóc từng người. Một hệ thống CRM gọn nhẹ giúp chủ hộ kinh doanh nhớ và chăm đúng khách, đúng thời điểm.',
            'offer' => 'Ưu đãi đúng cách không phải là giảm giá vô tội vạ, mà là công cụ kéo khách quay lại và tăng giá trị vòng đời. Vấn đề nằm ở cách thiết kế và đo lường hiệu quả của từng chương trình.',
            'ai' => 'AI không thay thế chủ hộ kinh doanh, nhưng có thể tiết kiệm rất nhiều thời gian: gợi ý nội dung, soạn phản hồi đánh giá và đề xuất chiến dịch dựa trên dữ liệu thực tế.',
            'metrics' => 'Tăng trưởng chỉ thực sự có ý nghĩa khi đo được. Hiểu đúng các chỉ số như lượt quét, khách tiềm năng, đặt lịch và tỷ lệ chuyển đổi sẽ giúp bạn ra quyết định dựa trên dữ liệu thay vì cảm tính.',
            'automation' => 'Tự động hóa giúp chủ hộ kinh doanh làm được nhiều hơn với ít thời gian hơn: email cảm ơn, nhắc lịch, chăm sóc sau dịch vụ đều có thể diễn ra mà không cần thao tác thủ công.',
            'industry' => 'Mỗi ngành nghề có đặc thù riêng về hành vi khách hàng và cách chăm sóc. Một bộ tăng trưởng số được điều chỉnh theo ngành sẽ phù hợp hơn nhiều so với giải pháp chung chung.',
            default => 'Chuyển đổi số cho hộ kinh doanh không cần phức tạp. Điều quan trọng là chọn đúng công cụ gọn nhẹ, tập trung vào việc thu dữ liệu khách hàng và biến nó thành doanh thu.',
        };
    }

    /**
     * @return array<string, string>
     */
    private static function sections(string $theme, int $index): array
    {
        $common = [
            'Bối cảnh thực tế tại Đà Nẵng' => 'Đà Nẵng và Quảng Nam là thị trường năng động với dòng khách du lịch lớn theo mùa và tệp khách địa phương ổn định. '
                .'Điều này tạo ra cơ hội nhưng cũng đặt ra thách thức: làm sao giữ chân khách giữa mùa cao điểm và duy trì doanh thu vào mùa thấp điểm. '
                .'Dữ liệu khách hàng chính là chìa khoá để cân bằng hai mục tiêu này.',
        ];

        $byTheme = match ($theme) {
            'google' => [
                'Tối ưu hồ sơ Google Business' => 'Một hồ sơ tốt cần tên, địa chỉ, số điện thoại nhất quán, giờ mở cửa chính xác, hình ảnh chất lượng và danh mục ngành đúng. '
                    .'Những yếu tố tưởng nhỏ này ảnh hưởng trực tiếp đến thứ hạng hiển thị khi khách tìm kiếm gần đó.',
                'Quản lý đánh giá tập trung' => 'Thay vì kiểm tra đánh giá rải rác, bạn nên gom về một nơi để phản hồi nhanh. '
                    .'Phản hồi kịp thời, lịch sự với cả đánh giá tốt lẫn chưa tốt là dấu hiệu của một cơ sở chuyên nghiệp.',
            ],
            'review' => [
                'Lọc phản hồi trước khi công khai' => 'Quy trình tốt sẽ hướng khách hài lòng tới đánh giá công khai và mời khách chưa hài lòng gửi phản hồi riêng. '
                    .'Cách này vừa tăng số sao, vừa cho bạn cơ hội khắc phục trước khi vấn đề lan rộng.',
                'Biến phản hồi xấu thành cải tiến' => 'Mỗi phản hồi tiêu cực là một gợi ý cải thiện miễn phí. Ghi nhận, phân loại và xử lý chúng một cách có hệ thống '
                    .'sẽ nâng chất lượng dịch vụ theo thời gian.',
            ],
            'qr' => [
                'Đặt mã QR ở đâu cho hiệu quả' => 'Vị trí lý tưởng là nơi khách có thời gian rảnh tay: trên bàn, tại quầy thanh toán, trên hoá đơn hoặc bao bì sản phẩm. '
                    .'Một lời mời ngắn gọn kèm lợi ích rõ ràng sẽ tăng tỷ lệ quét đáng kể.',
                'Mỗi lượt quét là một điểm dữ liệu' => 'MKT ghi lại thời gian, thiết bị và khu vực của mỗi lượt quét. '
                    .'Khi tổng hợp lại, bạn nhìn ra giờ cao điểm, ngày đông khách và chiến dịch nào đang thực sự hiệu quả.',
            ],
            'crm' => [
                'Phân nhóm khách hàng' => 'Gắn thẻ và phân khúc giúp bạn tách khách VIP, khách mới, khách quay lại và khách có nguy cơ rời bỏ. '
                    .'Mỗi nhóm cần một cách chăm sóc và ưu đãi khác nhau.',
                'Không bỏ sót khách nhờ task' => 'Việc cần làm như gọi lại, gửi ưu đãi hay xác nhận lịch được nhắc đúng hạn. '
                    .'Nhờ đó chủ hộ kinh doanh duy trì được mối quan hệ đều đặn mà không phải nhớ thủ công.',
            ],
            'offer' => [
                'Thiết kế ưu đãi thông minh' => 'Ưu đãi nên gắn với hành vi mong muốn: quay lại lần sau, giới thiệu bạn bè hoặc dùng thêm dịch vụ. '
                    .'Mức giảm vừa phải kèm điều kiện rõ ràng giúp giữ giá trị thương hiệu.',
                'Đo hiệu quả từng mã' => 'Theo dõi mã đã phát, đã nhận và đã dùng cho bạn biết chương trình nào thực sự tạo doanh thu, '
                    .'thay vì chỉ cảm giác là "có vẻ đông khách hơn".',
            ],
            'ai' => [
                'AI hỗ trợ nội dung' => 'AI có thể gợi ý tiêu đề bài đăng, soạn nội dung ưu đãi và đề xuất câu trả lời cho đánh giá, '
                    .'giúp chủ hộ tiết kiệm thời gian sáng tạo nội dung mỗi ngày.',
                'Con người vẫn ra quyết định' => 'AI đưa ra gợi ý dựa trên dữ liệu, nhưng người hiểu khách hàng và bối cảnh địa phương nhất vẫn là chủ cơ sở. '
                    .'Sự kết hợp giữa dữ liệu và kinh nghiệm mới tạo ra kết quả tốt.',
            ],
            'metrics' => [
                'Những chỉ số cần theo dõi' => 'Lượt quét QR, khách tiềm năng, lượt đặt lịch, coupon đã dùng và số đánh giá là những chỉ số nền tảng. '
                    .'Tỷ lệ chuyển đổi giữa các bước cho biết khâu nào đang nghẽn.',
                'Đọc báo cáo theo khung thời gian' => 'Báo cáo 7 ngày cho thấy biến động ngắn hạn, 30 ngày cho thấy xu hướng tháng, '
                    .'90 ngày cho thấy bức tranh tăng trưởng thật sự, ít bị nhiễu bởi sự kiện nhất thời.',
            ],
            'automation' => [
                'Gửi đúng lúc, đúng người' => 'Automation hiệu quả không phải là gửi thật nhiều, mà là gửi đúng thời điểm và đúng đối tượng. '
                    .'Một email nhắc lịch trước hẹn hữu ích hơn nhiều so với hàng loạt tin khuyến mãi.',
                'Mô phỏng an toàn trong demo' => 'Trong dữ liệu demo, mọi kênh email, webhook và nhắn tin đều là mô phỏng. '
                    .'Điều này cho phép trình bày đầy đủ luồng vận hành mà không gửi bất kỳ thông điệp thật nào.',
            ],
            'industry' => [
                'Điều chỉnh theo đặc thù ngành' => 'Spa và salon cần đặt lịch và nhắc hẹn; quán cà phê cần ưu đãi quay lại; homestay cần thu khách tiềm năng và đánh giá; '
                    .'cửa hàng đặc sản cần landing page và đơn hàng. MKT gợi ý đúng loại chiến dịch cho từng ngành.',
                'Quy trình triển khai gợi ý' => 'Bắt đầu từ một chiến dịch trọng tâm phù hợp ngành, đo kết quả trong 30 ngày, '
                    .'rồi mở rộng sang các chiến dịch bổ trợ khi đã thấy hiệu quả.',
            ],
            default => [
                'Bắt đầu từ việc nhỏ' => 'Không cần triển khai tất cả cùng lúc. Một chiến dịch QR xin đánh giá hoặc một landing page ưu đãi '
                    .'đã đủ để bắt đầu thu dữ liệu và thấy kết quả đầu tiên.',
                'Mở rộng dựa trên dữ liệu' => 'Khi đã quen, bạn mở rộng sang CRM, automation và tích hợp Google Business. '
                    .'Mỗi bước mở rộng đều nên dựa trên dữ liệu thực tế của chính cơ sở bạn.',
            ],
        };

        return $common + $byTheme;
    }

    private static function metrics(string $theme): string
    {
        return match ($theme) {
            'qr' => 'Một quán cà phê đặt mã QR tại bàn thường ghi nhận vài trăm lượt quét mỗi tháng, trong đó một phần đáng kể chuyển thành đánh giá hoặc thông tin liên hệ.',
            'review' => 'Khi quy trình xin đánh giá vận hành tốt, tỷ lệ khách hài lòng để lại đánh giá công khai có thể cải thiện rõ rệt qua từng tháng.',
            'offer' => 'Tỷ lệ coupon được sử dụng trên tổng số phát ra là thước đo trực tiếp cho sức hấp dẫn của ưu đãi và độ chính xác của nhóm khách mục tiêu.',
            'metrics' => 'Theo dõi đồng thời lượt quét, khách tiềm năng, đặt lịch và coupon đã dùng giúp bạn thấy được toàn bộ phễu chuyển đổi của cơ sở.',
            default => 'Các chỉ số như lượt quét, khách tiềm năng, lượt đặt lịch và đánh giá phản ánh sức khỏe tăng trưởng của cơ sở.',
        };
    }

    private static function conclusion(string $theme): string
    {
        return match ($theme) {
            'google' => 'Một hồ sơ Google chỉn chu cộng với đánh giá thật là nền tảng để khách tin tưởng và tìm đến bạn.',
            'review' => 'Đánh giá thật, được thu thập đúng cách, là tài sản uy tín bền vững hơn bất kỳ review ảo nào.',
            'qr' => 'Mã QR là cây cầu rẻ và nhanh nhất để biến khách ghé qua thành dữ liệu và mối quan hệ lâu dài.',
            'crm' => 'CRM gọn nhẹ giúp bạn chăm đúng khách, đúng lúc, và giữ chân họ một cách có hệ thống.',
            'offer' => 'Ưu đãi được thiết kế và đo lường tốt sẽ kéo khách quay lại mà không bào mòn lợi nhuận.',
            'ai' => 'AI là trợ lý đắc lực, nhưng giá trị thật đến từ việc bạn kết hợp nó với hiểu biết về khách hàng địa phương.',
            'metrics' => 'Khi đã đo được, bạn sẽ cải thiện được; dữ liệu chính là la bàn tăng trưởng của cơ sở.',
            'automation' => 'Tự động hóa đúng cách giúp bạn chăm sóc khách đều đặn mà vẫn dành thời gian cho việc kinh doanh cốt lõi.',
            'industry' => 'Một bộ công cụ được điều chỉnh theo ngành sẽ mang lại kết quả nhanh và rõ ràng hơn.',
            default => 'Chuyển đổi số cho hộ kinh doanh là một hành trình từng bước, bắt đầu từ những việc nhỏ nhưng đo được.',
        };
    }
}
