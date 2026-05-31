const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const weeklyHours = {
  mon: { is_closed: false, open_time: '07:30', close_time: '21:30' },
  tue: { is_closed: false, open_time: '07:30', close_time: '21:30' },
  wed: { is_closed: false, open_time: '07:30', close_time: '21:30' },
  thu: { is_closed: false, open_time: '07:30', close_time: '21:30' },
  fri: { is_closed: false, open_time: '07:30', close_time: '22:00' },
  sat: { is_closed: false, open_time: '08:00', close_time: '22:30' },
  sun: { is_closed: false, open_time: '08:00', close_time: '21:00' },
};

const businesses = {
  mi_quang_1a: { slug: 'admin-faker-mi-quang-1a', name: 'Mì Quảng 1A Đà Nẵng', type: 'restaurant', phone: '0236 3881 888', email: 'datban@demo.mi-quang-1a.dn', website: 'https://mi-quang-1a.vn', address: '1 Hải Phòng, Hải Châu, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=1+Hai+Phong+Da+Nang', zalo: '0905123456' },
  banh_trang_tran: { slug: 'admin-faker-banh-trang-tran', name: 'Bánh Tráng Cuốn Thịt Heo Trần', type: 'restaurant', phone: '0236 3567 890', email: 'lienhe@demo.banhtrangtran.dn', website: 'https://banhtrangthitheo.vn', address: '4 Trần Phú, Hải Châu, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=4+Tran+Phu+Da+Nang', zalo: '0905234567' },
  be_man_seafood: { slug: 'admin-faker-be-man', name: 'Bé Mặn — Hải Sản Đà Nẵng', type: 'restaurant', phone: '0236 3939 393', email: 'datban@demo.beman.dn', website: 'https://beman.vn', address: '16 Võ Văn Kiệt, Sơn Trà, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=16+Vo+Van+Kiet+Da+Nang', zalo: '0905345678' },
  vanda_spa: { slug: 'admin-faker-vanda-spa', name: 'Vanda Spa Đà Nẵng', type: 'spa', phone: '0236 3568 999', email: 'booking@demo.vanda.dn', website: 'https://vandaspa.vn', address: '25 Nguyễn Văn Linh, Hải Châu, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=25+Nguyen+Van+Linh+Da+Nang', zalo: '0905456789' },
  sen_spa_dn: { slug: 'admin-faker-sen-spa-dn', name: 'Sen Spa — Đà Nẵng', type: 'spa', phone: '0236 3737 373', email: 'spa@demo.senspa.dn', website: 'https://senspa.vn', address: '108 Hoàng Diệu, Hải Châu, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=108+Hoang+Dieu+Da+Nang', zalo: '0905567890' },
  east_west_dental: { slug: 'admin-faker-east-west-dental', name: 'East Meets West Dental — Đà Nẵng', type: 'clinic', phone: '0236 3820 200', email: 'hello@demo.emwdental.dn', website: 'https://eastmeetswestdental.com', address: '96-98 Nguyễn Văn Linh, Hải Châu, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=96+Nguyen+Van+Linh+Da+Nang', zalo: '0905678901' },
  highlands_dn: { slug: 'admin-faker-highlands-dn', name: 'Highlands Coffee — Đà Nẵng', type: 'cafe', phone: '0236 3888 001', email: 'store@demo.highlands.dn', website: 'https://highlandscoffee.com.vn', address: '50 Bạch Đằng, Hải Châu, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=50+Bach+Dang+Da+Nang', zalo: '0905789012' },
  camellia_homestay: { slug: 'admin-faker-camellia', name: 'Camellia Homestay & Villa', type: 'homestay', phone: '0236 3999 118', email: 'stay@demo.camellia.dn', website: 'https://camelliahomestay.vn', address: '120 Võ Nguyên Giáp, Sơn Trà, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=120+Vo+Nguyen+Giap+Da+Nang', zalo: '0905890123' },
  chuong_duong_tour: { slug: 'admin-faker-chuong-duong', name: 'Chuồng Dê Tour & Trải nghiệm', type: 'tour', phone: '0236 3777 777', email: 'tour@demo.chuongduong.dn', website: 'https://chuongduongtour.vn', address: 'Khu du lịch Bà Nà, Hòa Vang, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=Ba+Na+Hills+Da+Nang', zalo: '0905901234' },
  california_gym: { slug: 'admin-faker-california-gym', name: 'California Fitness — Đà Nẵng', type: 'gym', phone: '0236 3666 888', email: 'club@demo.california.dn', website: 'https://californiafitness.com.vn', address: '255 Hùng Vương, Thanh Khê, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=255+Hung+Vuong+Da+Nang', zalo: '0905012345' },
};

const locations = [
  { business: 'mi_quang_1a', name: 'Mì Quảng 1A — Hải Phòng', phone: '0236 3881 888', email: 'haiphong@demo.mi-quang-1a.dn', address: '1 Hải Phòng, Hải Châu, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=1+Hai+Phong+Da+Nang', template: 'emerald_ring' },
  { business: 'mi_quang_1a', name: 'Mì Quảng 1A — Nguyễn Văn Thoại', phone: '0236 3881 889', email: 'nvthoai@demo.mi-quang-1a.dn', address: '45 Nguyễn Văn Thoại, Sơn Trà, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=45+Nguyen+Van+Thoai+Da+Nang', template: 'rounded_gradient' },
  { business: 'banh_trang_tran', name: 'Bánh Tráng Trần — Trần Phú', phone: '0236 3567 890', email: 'tranphu@demo.banhtrangtran.dn', address: '4 Trần Phú, Hải Châu, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=4+Tran+Phu+Da+Nang', template: 'clean_card' },
  { business: 'banh_trang_tran', name: 'Bánh Tráng Trần — Võ Văn Tần', phone: '0236 3567 891', email: 'vvtan@demo.banhtrangtran.dn', address: '12 Võ Văn Tần, Sơn Trà, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=12+Vo+Van+Tan+Da+Nang', template: 'emerald_ring' },
  { business: 'be_man_seafood', name: 'Bé Mặn — Võ Văn Kiệt', phone: '0236 3939 393', email: 'vovankiet@demo.beman.dn', address: '16 Võ Văn Kiệt, Sơn Trà, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=16+Vo+Van+Kiet+Da+Nang', template: 'rounded_gradient' },
  { business: 'be_man_seafood', name: 'Bé Mặn — Mỹ Khê', phone: '0236 3939 394', email: 'mykhe@demo.beman.dn', address: '118-120 Võ Nguyên Giáp, Sơn Trà, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=My+Khe+Beach+Da+Nang', template: 'clean_card' },
  { business: 'vanda_spa', name: 'Vanda Spa — Nguyễn Văn Linh', phone: '0236 3568 999', email: 'nvl@demo.vanda.dn', address: '25 Nguyễn Văn Linh, Hải Châu, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=25+Nguyen+Van+Linh+Da+Nang', template: 'rounded_gradient' },
  { business: 'vanda_spa', name: 'Vanda Spa — Hoàng Diệu', phone: '0236 3568 998', email: 'hoangdieu@demo.vanda.dn', address: '88 Hoàng Diệu, Hải Châu, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=88+Hoang+Dieu+Da+Nang', template: 'emerald_ring' },
  { business: 'sen_spa_dn', name: 'Sen Spa — Hoàng Diệu', phone: '0236 3737 373', email: 'hoangdieu@demo.senspa.dn', address: '108 Hoàng Diệu, Hải Châu, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=108+Hoang+Dieu+Da+Nang', template: 'clean_card' },
  { business: 'sen_spa_dn', name: 'Sen Spa — Mỹ An', phone: '0236 3737 374', email: 'myan@demo.senspa.dn', address: '15 Nguyễn Hoàng, Ngũ Hành Sơn, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=15+Nguyen+Hoang+Da+Nang', template: 'rounded_gradient' },
  { business: 'east_west_dental', name: 'EMW Dental — Nguyễn Văn Linh', phone: '0236 3820 200', email: 'nvl@demo.emwdental.dn', address: '96-98 Nguyễn Văn Linh, Hải Châu, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=96+Nguyen+Van+Linh+Da+Nang', template: 'clean_card' },
  { business: 'east_west_dental', name: 'EMW Dental — Lê Duẩn', phone: '0236 3820 201', email: 'leduan@demo.emwdental.dn', address: '50 Lê Duẩn, Hải Châu, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=50+Le+Duan+Da+Nang', template: 'emerald_ring' },
  { business: 'highlands_dn', name: 'Highlands — Bạch Đằng', phone: '0236 3888 001', email: 'bachdang@demo.highlands.dn', address: '50 Bạch Đằng, Hải Châu, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=50+Bach+Dang+Da+Nang', template: 'rounded_gradient' },
  { business: 'highlands_dn', name: 'Highlands — Nguyễn Văn Linh', phone: '0236 3888 002', email: 'nvl@demo.highlands.dn', address: '120 Nguyễn Văn Linh, Hải Châu, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=120+Nguyen+Van+Linh+Da+Nang', template: 'clean_card' },
  { business: 'highlands_dn', name: 'Highlands — Sơn Trà', phone: '0236 3888 003', email: 'sontra@demo.highlands.dn', address: '8 Võ Văn Kiệt, Sơn Trà, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=8+Vo+Van+Kiet+Da+Nang', template: 'emerald_ring' },
  { business: 'camellia_homestay', name: 'Camellia — Mỹ Khê', phone: '0236 3999 118', email: 'mykhe@demo.camellia.dn', address: '120 Võ Nguyên Giáp, Sơn Trà, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=120+Vo+Nguyen+Giap+Da+Nang', template: 'rounded_gradient' },
  { business: 'camellia_homestay', name: 'Camellia — An Thượng', phone: '0236 3999 119', email: 'anthuong@demo.camellia.dn', address: '12 An Thượng 29, Ngũ Hành Sơn, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=An+Thuong+Da+Nang', template: 'clean_card' },
  { business: 'chuong_duong_tour', name: 'Chuồng Dê — Bà Nà', phone: '0236 3777 777', email: 'bana@demo.chuongduong.dn', address: 'Khu du lịch Bà Nà, Hòa Vang, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=Ba+Na+Hills', template: 'emerald_ring' },
  { business: 'chuong_duong_tour', name: 'Chuồng Dê — Sơn Trà', phone: '0236 3777 778', email: 'sontra@demo.chuongduong.dn', address: '5 Hoàng Sa, Sơn Trà, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=5+Hoang+Sa+Da+Nang', template: 'rounded_gradient' },
  { business: 'california_gym', name: 'California — Hùng Vương', phone: '0236 3666 888', email: 'hungvuong@demo.california.dn', address: '255 Hùng Vương, Thanh Khê, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=255+Hung+Vuong+Da+Nang', template: 'clean_card' },
  { business: 'california_gym', name: 'California — Nguyễn Văn Linh', phone: '0236 3666 889', email: 'nvl@demo.california.dn', address: '302 Nguyễn Văn Linh, Thanh Khê, Đà Nẵng', google_maps_url: 'https://maps.google.com/?q=302+Nguyen+Van+Linh+Da+Nang', template: 'emerald_ring' },
];

function buildSettings(type, name, business) {
  const base = { landing_template: type + '_default' };
  if (type === 'review') {
    return { ...base, landing_template: 'review_google_focus', positive_threshold: 4, preferred_destination: 'google', thank_you_message: 'Cảm ơn bạn đã ghé chúng tôi.', negative_feedback_message: 'Hãy cho chúng tôi biết điều cần cải thiện.' };
  }
  if (type === 'lead') {
    return { ...base, landing_template: 'lead_quote_request', headline: name };
  }
  if (type === 'booking') {
    const tpl = business.includes('spa') ? 'booking_spa' : business.includes('dental') || business.includes('east') ? 'booking_clinic' : 'booking_restaurant';
    return { ...base, landing_template: tpl, headline: name };
  }
  if (type === 'coupon') {
    return { ...base, landing_template: 'coupon_weekend_deal', discount_type: 'percentage', discount_value: business.includes('gym') ? '30' : '20', coupon_code: 'DN' + business.slice(0, 4).toUpperCase(), usage_limit: 200, expiry_date: null, terms: 'Áp dụng tại chi nhánh Đà Nẵng. Mỗi khách một mã.' };
  }
  if (type === 'feedback') {
    return { ...base, landing_template: 'feedback_private', headline: name, thank_you_message: 'Cảm ơn góp ý — đội ngũ sẽ cải thiện dịch vụ.', rating_required: false, contact_required: false };
  }
  return base;
}

const campaignTemplates = [
  ['mi_quang_1a', 'review', 'admin-faker-mq1a-review-google', 'Đánh giá Google — Mì Quảng 1A', 420, 38, 14],
  ['mi_quang_1a', 'lead', 'admin-faker-mq1a-lead-dat-ban', 'Đặt bàn / gọi món trước', 280, 32, 45],
  ['mi_quang_1a', 'coupon', 'admin-faker-mq1a-coupon-sang', 'Ưu đãi bữa sáng 15%', 310, 41, 60],
  ['banh_trang_tran', 'review', 'admin-faker-btt-review', 'Đánh giá sau bữa cuốn', 380, 35, 21],
  ['banh_trang_tran', 'feedback', 'admin-faker-btt-feedback', 'Phản hồi chất lượng món', 260, 28, 90],
  ['banh_trang_tran', 'lead', 'admin-faker-btt-lead-nhom', 'Đặt bàn nhóm du lịch', 220, 25, 120],
  ['be_man_seafood', 'review', 'admin-faker-beman-review', 'Đánh giá hải sản Bé Mặn', 510, 44, 18],
  ['be_man_seafood', 'coupon', 'admin-faker-beman-coupon-toi', 'Giảm 20% bữa tối', 450, 39, 75],
  ['be_man_seafood', 'booking', 'admin-faker-beman-booking-ban', 'Đặt bàn hải sản', 340, 30, 30],
  ['vanda_spa', 'review', 'admin-faker-vanda-review', 'Đánh giá Google — Vanda Spa', 390, 36, 12],
  ['vanda_spa', 'booking', 'admin-faker-vanda-booking-massage', 'Đặt lịch massage', 360, 33, 8],
  ['vanda_spa', 'coupon', 'admin-faker-vanda-coupon-quaylai', 'Ưu đãi khách quay lại 15%', 290, 27, 100],
  ['sen_spa_dn', 'review', 'admin-faker-sen-review', 'Đánh giá Sen Spa', 350, 32, 25],
  ['sen_spa_dn', 'booking', 'admin-faker-sen-booking-combo', 'Đặt combo thư giãn', 320, 29, 40],
  ['sen_spa_dn', 'feedback', 'admin-faker-sen-feedback', 'Khảo sát trải nghiệm spa', 240, 22, 110],
  ['east_west_dental', 'review', 'admin-faker-emw-review', 'Đánh giá sau khám', 300, 28, 20],
  ['east_west_dental', 'lead', 'admin-faker-emw-lead-tu-van', 'Tư vấn nha khoa miễn phí', 280, 26, 55],
  ['east_west_dental', 'booking', 'admin-faker-emw-booking-kham', 'Đặt lịch khám', 260, 24, 35],
  ['east_west_dental', 'coupon', 'admin-faker-emw-coupon-tay-trang', 'Ưu đãi tẩy trắng khách mới', 220, 20, 150],
  ['highlands_dn', 'review', 'admin-faker-highlands-review', 'Đánh giá cửa hàng Highlands', 480, 42, 10],
  ['highlands_dn', 'coupon', 'admin-faker-highlands-coupon-sang', 'Mua 1 tặng 1 buổi sáng', 410, 36, 45],
  ['highlands_dn', 'feedback', 'admin-faker-highlands-feedback', 'Phản hồi dịch vụ', 270, 24, 80],
  ['camellia_homestay', 'lead', 'admin-faker-camellia-lead-dat-phong', 'Đặt phòng / villa', 330, 30, 70],
  ['camellia_homestay', 'review', 'admin-faker-camellia-review', 'Đánh giá lưu trú', 290, 26, 95],
  ['camellia_homestay', 'coupon', 'admin-faker-camellia-coupon-weekday', 'Giảm 10% ngày thường', 250, 23, 130],
  ['chuong_duong_tour', 'lead', 'admin-faker-cd-lead-tour', 'Đăng ký tour Bà Nà', 380, 34, 60],
  ['chuong_duong_tour', 'review', 'admin-faker-cd-review', 'Đánh giá trải nghiệm tour', 310, 28, 140],
  ['chuong_duong_tour', 'booking', 'admin-faker-cd-booking-slot', 'Giữ chỗ tour cuối tuần', 270, 25, 20],
  ['california_gym', 'lead', 'admin-faker-gym-lead-trial', 'Đăng ký tập thử', 300, 28, 15],
  ['california_gym', 'review', 'admin-faker-gym-review', 'Đánh giá câu lạc bộ', 260, 24, 50],
  ['california_gym', 'coupon', 'admin-faker-gym-coupon-thang', 'Giảm 30% gói tháng đầu', 340, 31, 90],
  ['mi_quang_1a', 'feedback', 'admin-faker-mq1a-feedback', 'Khảo sát mì quảng', 200, 18, 180],
];

const campaign_metrics = {};
const campaigns = campaignTemplates.map(([business, type, slug, name, visits, convPct, ageDays]) => {
  campaign_metrics[slug] = { visits, conversions: Math.round((visits * convPct) / 100) };
  return {
    key: slug.replace(/-/g, '_'),
    business,
    type,
    slug,
    name,
    age_days: ageDays,
    settings: buildSettings(type, name, business),
  };
});

const booking_services = [
  { business: 'vanda_spa', name: 'Massage thư giãn 60 phút', duration_minutes: 60, price: 550000, description: 'Liệu trình massage body cho khách mới và khách quen tại Vanda Spa.' },
  { business: 'vanda_spa', name: 'Liệu trình da mặt 45 phút', duration_minutes: 45, price: 480000, description: 'Chăm sóc da mặt cơ bản, phù hợp khách văn phòng.' },
  { business: 'sen_spa_dn', name: 'Combo Sen thư giãn 90 phút', duration_minutes: 90, price: 890000, description: 'Massage + xông hơi — combo bán chạy cuối tuần.' },
  { business: 'sen_spa_dn', name: 'Gội đầu dưỡng sinh 40 phút', duration_minutes: 40, price: 320000, description: 'Gội đầu thảo dược, thư giãn buổi chiều.' },
  { business: 'east_west_dental', name: 'Tư vấn nha khoa miễn phí', duration_minutes: 45, price: 0, description: 'Khám và tư vấn ban đầu cho khách mới.' },
  { business: 'east_west_dental', name: 'Tẩy trắng răng cơ bản', duration_minutes: 60, price: 2500000, description: 'Gói tẩy trắng — đặt lịch trước qua QR.' },
  { business: 'be_man_seafood', name: 'Đặt bàn hải sản tối', duration_minutes: 120, price: 0, description: 'Giữ bàn cho nhóm 4–12 khách, bữa tối.' },
  { business: 'be_man_seafood', name: 'Set hải sản 2 người', duration_minutes: 90, price: 1200000, description: 'Set combo — cần đặt trước 2 giờ.' },
  { business: 'mi_quang_1a', name: 'Đặt bàn trưa văn phòng', duration_minutes: 60, price: 0, description: 'Đặt bàn 11:30–13:30 các ngày trong tuần.' },
  { business: 'camellia_homestay', name: 'Giữ phòng villa 1 đêm', duration_minutes: 30, price: 2800000, description: 'Đặt cọc giữ phòng — xác nhận qua Zalo.' },
  { business: 'chuong_duong_tour', name: 'Tour Bà Nà nửa ngày', duration_minutes: 360, price: 890000, description: 'Vé + xe đưa đón — slot sáng/chiều.' },
  { business: 'california_gym', name: 'Buổi tập thử PT 60 phút', duration_minutes: 60, price: 0, description: 'Tập thử với huấn luyện viên — khách mới.' },
];

const vnNames = ['Nguyễn Thị Mai', 'Trần Văn Đức', 'Lê Thị Hương', 'Phạm Minh Khôi', 'Hoàng Lan Anh', 'Võ Thanh Bình', 'Đặng Thu Hà', 'Bùi Quốc Huy', 'Ngô Kim Ngân', 'Trịnh Văn Long', 'Phan Thị Yến', 'Đinh Hoàng Nam', 'Lý Minh Châu', 'Vũ Gia Hân', 'Cao Đức Anh', 'Huỳnh Quốc Bảo', 'Lương Thị Ngọc', 'Mai Văn Hùng', 'Đỗ Thị Linh', 'Trương Minh Tuấn', 'Võ Ngọc Hà', 'Phùng Văn Kiệt', 'Bùi Thị Thảo', 'Nguyễn Quang Huy', 'Lê Văn Phúc', 'Trần Thị Hồng', 'Phạm Đức Thắng', 'Hoàng Minh Đức', 'Vũ Thị Lan', 'Đặng Văn Sơn', 'Nguyễn Thị Hạnh', 'Lê Hoàng Long', 'Trần Kim Oanh', 'Phạm Thu Trang', 'Võ Đình Khang', 'Bùi Ngọc An', 'Lưu Thanh Tùng', 'Đinh Mỹ Linh', 'Hồ Văn Nam', 'Nguyễn Bảo Châu', 'Trịnh Thị Hoa', 'Cao Văn Đạt', 'Lý Thị Như', 'Mai Quốc Toàn', 'Huỳnh Văn Thịnh', 'Phan Minh Quân', 'Đỗ Thị Bích', 'Vũ Hoàng Anh', 'Trương Thị Mai', 'Ngô Văn Hải', 'Lê Thị Phương', 'Phạm Văn Tài', 'Hoàng Thị Diệu', 'Nguyễn Minh Khoa', 'Trần Văn Bình', 'Bùi Thị Hằng', 'Lương Văn Đạt', 'Đặng Thị Oanh', 'Võ Quốc Hưng', 'Phùng Thị Lan', 'Huỳnh Văn Tú', 'Mai Thị Hương', 'Đinh Quốc Việt', 'Cao Thị Yến', 'Lý Văn Phong', 'Trịnh Ngọc Hiếu', 'Nguyễn Thanh Tâm', 'Hoàng Văn Lộc', 'Phạm Thị Ngọc', 'Trần Đức Anh', 'Lê Văn Hải', 'Vũ Thị Thanh', 'Bùi Minh Đức', 'Đỗ Văn Quyết', 'Ngô Thị Hạnh', 'Phan Văn Sáu', 'Lưu Thị Hồng', 'Trương Văn Phú', 'Huỳnh Thị Nga', 'Mai Văn Dũng', 'Đặng Quốc Bình', 'Võ Thị Hoa', 'Nguyễn Văn Tèo', 'Trần Thị Bé', 'Phạm Hoàng Sơn', 'Lê Thị Cúc', 'Hoàng Văn Em', 'Vũ Minh Tâm', 'Bùi Thị Dung', 'Cao Văn Lâm', 'Lý Thị Sen', 'Trịnh Văn Cường', 'Nguyễn Thị Xuyến', 'Phùng Hoàng Nam', 'Đinh Thị Loan', 'Lương Văn Bé', 'Huỳnh Thị Sương', 'Mai Văn Giàu', 'Đỗ Thị Lành', 'Trương Quốc Huy', 'Ngô Văn Mười', 'Phan Thị Rót', 'Võ Minh Châu', 'Bùi Văn Năm', 'Lê Thị Sáu', 'Trần Hoàng Phúc', 'Nguyễn Thị Bảy', 'Phạm Văn Tám', 'Hoàng Thị Chín', 'Vũ Văn Mười', 'Đặng Thị Một', 'Lý Văn Hai', 'Cao Thị Ba', 'Trịnh Văn Bốn', 'Hồ Thị Năm', 'Nguyễn Văn Sáu', 'Lê Thị Bảy', 'Trần Văn Tám', 'Phạm Thị Chín', 'Võ Đức Mười', 'Bùi Thị Mười Một', 'Lưu Văn Mười Hai', 'Nguyễn Thị Lan', 'Trần Quốc Huy', 'Lê Minh Anh', 'Phạm Thị Hoa', 'Hoàng Văn Đạt', 'Vũ Thị Ngọc', 'Bùi Văn Long'];

const businessKeys = Object.keys(businesses);
const customers = vnNames.slice(0, 120).map((name, i) => ({
  name,
  phone: `09${String(10 + (i % 80)).padStart(2, '0')} ${String(100 + (i % 900)).padStart(3, '0')} ${String(200 + (i % 800)).padStart(3, '0')}`,
  email: `khach.dn.${i + 1}@demo.mlhub.vn`,
  business: businessKeys[i % businessKeys.length],
  created_days_ago: 5 + (i % 400),
}));

const standalone_landing_pages = [
  { business: 'mi_quang_1a', slug: 'admin-faker-lp-mq1a-review', title: 'Đánh giá Mì Quảng 1A', type: 'review', template: 'review_restaurant', headline: 'Bát mì quảng hôm nay thế nào?', subheadline: 'Góp ý giúp quán phục vụ khách Đà Nẵng tốt hơn.', cta: 'Gửi đánh giá', benefits: ['Nhanh', 'Riêng tư khi điểm thấp', 'Chuyển Google'], age_days: 120, visits: 220, conversions: 68 },
  { business: 'be_man_seafood', slug: 'admin-faker-lp-beman-coupon', title: 'Coupon hải sản tối', type: 'coupon', template: 'coupon_weekend_deal', headline: 'Giảm 20% bữa tối hải sản', subheadline: 'Claim mã tại bàn — áp dụng Sơn Trà.', cta: 'Nhận ưu đãi', benefits: ['Cuối tuần', 'Mã tức thì', 'Theo dõi redemption'], age_days: 90, visits: 310, conversions: 95 },
  { business: 'vanda_spa', slug: 'admin-faker-lp-vanda-booking', title: 'Đặt lịch Vanda Spa', type: 'booking', template: 'booking_spa', headline: 'Đặt massage tại Vanda', subheadline: 'Chọn khung giờ — lễ tân xác nhận qua Zalo.', cta: 'Đặt lịch', benefits: ['60–90 phút', 'Combo cuối tuần', 'Xác nhận nhanh'], age_days: 60, visits: 185, conversions: 52 },
  { business: 'east_west_dental', slug: 'admin-faker-lp-emw-lead', title: 'Tư vấn nha khoa EMW', type: 'lead', template: 'lead_quote_request', headline: 'Đặt lịch tư vấn miễn phí', subheadline: 'Phòng khám Nguyễn Văn Linh — Đà Nẵng.', cta: 'Gửi yêu cầu', benefits: ['Miễn phí', 'Bác sĩ nói tiếng Anh', 'Đặt lịch linh hoạt'], age_days: 150, visits: 140, conversions: 38 },
  { business: 'highlands_dn', slug: 'admin-faker-lp-highlands-feedback', title: 'Phản hồi Highlands', type: 'feedback', template: 'feedback_quality', headline: 'Ly cà phê hôm nay ra sao?', subheadline: 'Ý kiến gửi thẳng quản lý cửa hàng.', cta: 'Gửi phản hồi', benefits: ['Riêng tư', 'Cải thiện dịch vụ', 'Nhanh'], age_days: 45, visits: 98, conversions: 28 },
  { business: 'camellia_homestay', slug: 'admin-faker-lp-camellia-lead', title: 'Đặt phòng Camellia', type: 'lead', template: 'lead_new_customer', headline: 'Giữ phòng villa Mỹ Khê', subheadline: 'Nhận báo giá và ưu đãi ngày thường.', cta: 'Nhận báo giá', benefits: ['View biển', 'Giá minh bạch', 'Hỗ trợ Zalo'], age_days: 200, visits: 175, conversions: 41 },
  { business: 'chuong_duong_tour', slug: 'admin-faker-lp-cd-tour', title: 'Tour Bà Nà — Chuồng Dê', type: 'lead', template: 'lead_event_capture', headline: 'Đăng ký tour cuối tuần', subheadline: 'Slot sáng/chiều — xe đưa đón trung tâm Đà Nẵng.', cta: 'Đăng ký', benefits: ['Bà Nà', 'Hướng dẫn viên', 'Nhóm nhỏ'], age_days: 75, visits: 240, conversions: 58 },
  { business: 'california_gym', slug: 'admin-faker-lp-gym-trial', title: 'Tập thử California', type: 'lead', template: 'lead_new_customer', headline: 'Buổi tập thử miễn phí', subheadline: 'PT hướng dẫn — chi nhánh Thanh Khê.', cta: 'Đăng ký thử', benefits: ['60 phút', 'PT kèm', 'Không cam kết'], age_days: 30, visits: 165, conversions: 44 },
  { business: 'banh_trang_tran', slug: 'admin-faker-lp-btt-review', title: 'Đánh giá Bánh Tráng Trần', type: 'review', template: 'review_restaurant', headline: 'Món cuốn hôm nay thế nào?', subheadline: 'Khách du lịch và địa phương đều chào đón góp ý.', cta: 'Đánh giá', benefits: ['Google', 'Riêng tư', 'Nhanh'], age_days: 180, visits: 192, conversions: 55 },
  { business: 'sen_spa_dn', slug: 'admin-faker-lp-sen-coupon', title: 'Ưu đãi Sen Spa', type: 'coupon', template: 'coupon_birthday', headline: 'Tặng thêm 15 phút xông hơi', subheadline: 'Áp dụng combo cuối tuần tại Sen Spa.', cta: 'Nhận ưu đãi', benefits: ['Cuối tuần', 'Add-on miễn phí', 'Một mã/khách'], age_days: 55, visits: 128, conversions: 36 },
  { business: 'mi_quang_1a', slug: 'admin-faker-lp-mq1a-custom-event', title: 'Đêm mì quảng — sự kiện', type: 'custom', template: 'custom_local_event_page', headline: 'Đăng ký đêm mì quảng 100 suất', subheadline: 'Sự kiện cộng đồng Hải Châu — MLHUB demo.', cta: 'Giữ chỗ', benefits: ['Giới hạn suất', 'Chef host', 'Cộng đồng'], age_days: 14, visits: 88, conversions: 22 },
  { business: 'be_man_seafood', slug: 'admin-faker-lp-beman-feedback', title: 'Phản hồi Bé Mặn', type: 'feedback', template: 'feedback_private', headline: 'Bữa hải sản của bạn', subheadline: 'Góp ý riêng cho quản lý Sơn Trà.', cta: 'Gửi phản hồi', benefits: ['Riêng tư', 'Xử lý nhanh', 'Cải thiện menu'], age_days: 100, visits: 76, conversions: 19 },
  { business: 'vanda_spa', slug: 'admin-faker-lp-vanda-gift', title: 'Thẻ quà Vanda Spa', type: 'custom', template: 'custom_product_spotlight', headline: 'Tặng thẻ spa cho người thân', subheadline: 'Chọn mệnh giá — giao tại quầy hoặc Zalo.', cta: 'Hỏi thẻ quà', benefits: ['Quà Đà Nẵng', 'Linh hoạt', 'In hộp đẹp'], age_days: 220, visits: 62, conversions: 15 },
  { business: 'highlands_dn', slug: 'admin-faker-lp-highlands-review', title: 'Review Highlands Bạch Đằng', type: 'review', template: 'review_google_focus', headline: 'Trải nghiệm cà phê sáng nay', subheadline: 'Điểm cao → Google Maps.', cta: 'Gửi sao', benefits: ['Google', '30 giây', 'Không đăng nhập'], age_days: 40, visits: 205, conversions: 61 },
  { business: 'east_west_dental', slug: 'admin-faker-lp-emw-feedback', title: 'Chất lượng khám EMW', type: 'feedback', template: 'feedback_quality', headline: 'Buổi khám hôm nay', subheadline: 'Phản hồi giúp phòng khám cải thiện.', cta: 'Gửi ý kiến', benefits: ['Bảo mật', 'Đội chăm sóc', 'Nhanh'], age_days: 130, visits: 71, conversions: 20 },
  { business: 'camellia_homestay', slug: 'admin-faker-lp-camellia-review', title: 'Đánh giá lưu trú Camellia', type: 'review', template: 'review_clean_request', headline: 'Đêm nghỉ tại villa', subheadline: 'Chia sẻ cho khách sau.', cta: 'Đánh giá', benefits: ['Google', 'Booking sync', 'Riêng tư'], age_days: 160, visits: 134, conversions: 40 },
  { business: 'chuong_duong_tour', slug: 'admin-faker-lp-cd-booking', title: 'Giữ chỗ tour', type: 'booking', template: 'booking_clinic', headline: 'Chọn ngày tour Bà Nà', subheadline: 'Xác nhận qua hotline 0236 3777 777.', cta: 'Giữ chỗ', benefits: ['Sáng/chiều', 'Nhóm nhỏ', 'Xe đón'], age_days: 25, visits: 112, conversions: 31 },
  { business: 'california_gym', slug: 'admin-faker-lp-gym-coupon', title: 'Gói tháng đầu -30%', type: 'coupon', template: 'coupon_20_off', headline: 'Ưu đãi thành viên mới', subheadline: 'Chi nhánh Hùng Vương & Nguyễn Văn Linh.', cta: 'Nhận mã', benefits: ['30%', 'PT tư vấn', 'Không phí ẩn'], age_days: 20, visits: 198, conversions: 57 },
];

const marketing = {
  faqs: [
    { slug: 'demo-preview-chien-dich-qr-ho-kinh-doanh-da-nang', title: 'Chiến dịch QR giúp hộ kinh doanh Đà Nẵng làm gì?', content: '<p>Gom review Google, đặt lịch, coupon và form lead trên một landing — in QR tại quầy, menu, standee Mỹ Khê, Hải Châu.</p>' },
    { slug: 'demo-preview-doi-qr-sau-khi-in', title: 'In QR rồi có đổi trang đích được không?', content: '<p>Có. QR động giữ hình in; bạn đổi landing, ưu đãi và UTM sau khi dán tại quán.</p>' },
    { slug: 'demo-preview-landing-page-mlhub', title: 'Landing MLHUB khác website thế nào?', content: '<p>Một mục tiêu chuyển đổi, form và báo cáo sẵn — không cần site riêng từng chiến dịch.</p>' },
    { slug: 'demo-preview-bao-cao-chien-dich', title: 'Báo cáo chiến dịch gồm gì?', content: '<p>Quét, visit, lead, booking, coupon, review — theo chiến dịch và hồ sơ kinh doanh.</p>' },
    { slug: 'demo-preview-review-booster', title: 'Review Booster hoạt động ra sao?', content: '<p>Điểm cao → Google; điểm thấp → kênh riêng xử lý trước.</p>' },
    { slug: 'demo-preview-chi-nhanh-da-nang', title: 'Quản lý nhiều chi nhánh tại Đà Nẵng?', content: '<p>Mỗi chi nhánh có QR design riêng; chiến dịch gắn business/location nhất quán.</p>' },
    { slug: 'demo-preview-dat-lich-spa-quan', title: 'Đặt lịch spa / quán / nha khoa qua QR?', content: '<p>Khách chọn slot; bạn xác nhận trên dashboard — phù hợp SOHO và hộ cá thể.</p>' },
    { slug: 'demo-preview-coupon-qr', title: 'Coupon claim qua QR có giới hạn không?', content: '<p>Đặt mã, hạn dùng, số lượt; theo dõi claimed/used trong báo cáo.</p>' },
    { slug: 'demo-preview-khach-hang-crm', title: 'CRM khách hàng tích hợp thế nào?', content: '<p>Lead và booking tạo hồ sơ khách; tag VIP, khách quay lại, follow-up.</p>' },
    { slug: 'demo-preview-soho-kinh-te-ca-the', title: 'MLHUB phù hợp hộ kinh doanh cá thể?', content: '<p>Một workspace nhiều hồ sơ — spa, F&B, tour, homestay cùng tài khoản (như demo Đà Nẵng).</p>' },
    { slug: 'demo-preview-goi-mlhub', title: 'Gói MLHUB cho nhóm SOHO?', content: '<p>Gói agency/lifetime cho demo investor — module LocalBoost, không cần publishing.</p>' },
    { slug: 'demo-preview-zalo-qr', title: 'Kết hợp Zalo với QR?', content: '<p>Link Zalo trên hồ sơ kinh doanh; landing thu lead rồi chuyển chat thủ công.</p>' },
    { slug: 'demo-preview-bao-mat-du-lieu', title: 'Dữ liệu demo có xóa được không?', content: '<p>Admin Faker chỉ xóa record tag admin-faker / slug demo-preview — không đụng dữ liệu thật khác marker.</p>' },
    { slug: 'demo-preview-dong-bo-google', title: 'Có đồng bộ Google Business Profile không?', content: '<p>Module GBP nằm ngoài phạm vi faker hiện tại; review funnel vẫn dùng link Google tùy chỉnh.</p>' },
    { slug: 'demo-preview-ho-tro-da-nang', title: 'Hỗ trợ triển khai tại Đà Nẵng?', content: '<p>Ticket demo minh họa quy trình support — spa, F&B, nha khoa.</p>' },
  ],
  blogs: [
    { slug: 'demo-preview-mi-quang-qr-da-nang', title: 'Mì Quảng 1A và QR review tại Hải Châu', excerpt: 'In QR trên menu — thu đánh giá Google sau bữa trưa đông khách.' },
    { slug: 'demo-preview-hai-san-son-tra-coupon', title: 'Nhà hàng Sơn Trà: coupon tối qua QR', excerpt: 'Bé Mặn và các quán hải sản dùng coupon cuối tuần có giới hạn lượt.' },
    { slug: 'demo-preview-spa-vanda-booking', title: 'Vanda Spa — đặt lịch massage không gọi điện', excerpt: 'Khách quét QR tại lễ tân, chọn slot, nhận xác nhận Zalo.' },
    { slug: 'demo-preview-nha-khoa-emw-lead', title: 'Nha khoa Đà Nẵng: lead tư vấn miễn phí', excerpt: 'Form lead trên landing thay vì inbox Facebook rời rạc.' },
    { slug: 'demo-preview-highlands-feedback', title: 'Chuỗi café: feedback riêng trước Google', excerpt: 'Highlands demo — phản hồi chất lượng ly cà phê vào dashboard.' },
    { slug: 'demo-preview-homestay-my-khe', title: 'Homestay Mỹ Khê: giữ phòng qua landing', excerpt: 'Camellia demo — lead đặt phòng và review sau lưu trú.' },
    { slug: 'demo-preview-tour-ba-na-qr', title: 'Tour Bà Nà: QR tại quầy và trên xe', excerpt: 'Chuồng Dê demo — lead tour và giữ chỗ cuối tuần.' },
    { slug: 'demo-preview-gym-trial-qr', title: 'Phòng gym: tập thử qua QR', excerpt: 'California Fitness demo — lead PT và coupon tháng đầu.' },
    { slug: 'demo-preview-banhtrang-du-lich', title: 'Bánh tráng cuốn và khách du lịch', excerpt: 'Bánh Tráng Trần — review + feedback cho khách walk-in.' },
    { slug: 'demo-preview-bao-cao-soho', title: 'Một dashboard cho 10 hồ sơ SOHO', excerpt: 'Chủ nhóm kinh doanh Đà Nẵng xem analytics tập trung trên MLHUB.' },
    { slug: 'demo-preview-chi-nhanh-qr-design', title: 'Mỗi chi nhánh một thiết kế QR', excerpt: 'Hai mặt Mì Quảng, ba Highlands — QR design khác nhau, cùng workspace.' },
    { slug: 'demo-preview-investor-demo-mlhub', title: 'Chuẩn bị demo nhà đầu tư với Admin Faker', excerpt: 'Seed 12 tháng dữ liệu Đà Nẵng trong vài phút — tiếp tục chỉnh sau seed.' },
  ],
  support_tickets: [
    { title: '[DEMO] Thiết lập 10 hồ sơ Đà Nẵng cho pitch', content: 'Cần workspace demo với Mì Quảng, spa, nha khoa, tour — analytics đầy đủ.', status: 1, user_read: true, admin_read: false, offset: 86400, comments: [{ comment: '<p>Ưu tiên chi nhánh Hải Châu và Sơn Trà.</p>', offset: 72000 }] },
    { title: '[DEMO] QR in trên menu — đổi landing sau Tết', content: 'Đã in QR Tết, muốn đổi sang campaign coupon sau khi hết lễ.', status: 2, user_read: false, admin_read: true, offset: 172800, comments: [{ comment: '<p>QR động giữ nguyên — chỉ đổi trang đích.</p>', offset: 150000 }] },
    { title: '[DEMO] Báo cáo trống sau migrate', content: 'Sau migrate cần seed lại lượt quét 6–12 tháng.', status: 1, user_read: true, admin_read: true, offset: 259200, comments: [{ comment: '<p>Chạy admin-faker:refresh trên user demo.</p>', offset: 240000 }] },
    { title: '[DEMO] Đồng bộ tên khách CRM', content: 'Lead từ QR có tự tạo customer không?', status: 2, user_read: true, admin_read: false, offset: 345600, comments: [{ comment: '<p>Demo đã có 120 khách mẫu gắn business.</p>', offset: 320000 }] },
    { title: '[DEMO] Affiliate snapshot cho slide', content: 'Cần số commission mẫu trên admin.', status: 1, user_read: false, admin_read: true, offset: 432000, comments: [] },
    { title: '[DEMO] Blog tiếng Việt cho landing SEO', content: 'Thêm bài Đà Nẵng + QR cho guest site.', status: 2, user_read: true, admin_read: true, offset: 518400, comments: [{ comment: '<p>12 bài demo-preview đã publish.</p>', offset: 500000 }] },
  ],
  global_notifications: [
    { title: '[DEMO] 32 chiến dịch QR Đà Nẵng đã sẵn sàng', message: 'Review, booking, coupon, lead và feedback — xem analytics từng chiến dịch.', url_route: 'portal.qr-campaigns', url_fallback: 'portal/qr-campaigns', type: 'news' },
    { title: '[DEMO] 10 hồ sơ kinh doanh SOHO', message: 'F&B, spa, nha khoa, café, homestay, tour, gym — cùng một workspace.', url_route: 'portal.dashboard', url_fallback: 'portal/dashboard', type: 'info' },
    { title: '[DEMO] 20 chi nhánh + QR design', message: 'Mỗi chi nhánh có địa chỉ và template QR riêng.', url_route: 'portal.business-locations', url_fallback: 'portal/business-locations', type: 'tip' },
    { title: '[DEMO] 18 landing page độc lập', message: 'Trang landing không gắn campaign — dùng cho A/B và sự kiện.', url_route: 'portal.landing-pages', url_fallback: 'portal/landing-pages', type: 'info' },
    { title: '[DEMO] 120 khách hàng CRM mẫu', message: 'Tag VIP, khách quay lại — sẵn sàng cho demo CRM.', url_route: 'portal.customers', url_fallback: 'portal/customers', type: 'tip' },
    { title: '[DEMO] Dữ liệu 12–18 tháng', message: 'Timeline engagement trải theo mùa — phù hợp slide tăng trưởng.', url_route: 'portal.dashboard', url_fallback: 'portal/dashboard', type: 'news' },
    { title: '[DEMO] Chạy lại seed an toàn', message: 'Admin → Admin Faker hoặc php artisan admin-faker:refresh', url_route: 'admin-faker.index', url_fallback: 'admin/admin-faker', type: 'tip' },
  ],
};

const data = {
  meta: {
    region: 'Đà Nẵng',
    persona: 'SOHO / hộ kinh doanh / kinh tế cá thể',
    timeline_months: { min: 6, max: 18 },
    disclaimer: 'Tên thương hiệu chỉ minh họa sản phẩm MLHUB — không liên kết vận hành thực tế.',
  },
  weekly_hours: weeklyHours,
  scan_cities: ['Đà Nẵng', 'Hội An', 'Huế', 'Quảng Nam'],
  scan_country: 'VN',
  engagement_max_days_ago: 540,
  businesses,
  locations,
  campaigns,
  campaign_metrics,
  booking_services,
  customers,
  customer_name_pool: vnNames,
  standalone_landing_pages,
  messages: {
    review_positive: 'Dịch vụ tốt, nhân viên nhiệt tình — sẽ giới thiệu bạn bè.',
    review_negative: 'Thời gian chờ hơi lâu vào cuối tuần, mong cải thiện.',
    lead: 'Tôi muốn đặt lịch / tư vấn qua QR tại quán.',
    booking_note: 'Ưu tiên khung giờ chiều, liên hệ Zalo trước 30 phút.',
    feedback_positive: 'Không gian sạch, món/dịch vụ đúng kỳ vọng.',
    feedback_negative: 'Bàn/phòng chưa sẵn sàng đúng giờ đã hẹn.',
  },
  marketing,
};

function phpExport(value, indent = 0) {
  const pad = '    '.repeat(indent);
  const padInner = '    '.repeat(indent + 1);
  if (value === null) return 'null';
  if (typeof value === 'boolean') return value ? 'true' : 'false';
  if (typeof value === 'number') return String(value);
  if (typeof value === 'string') {
    return `'${value.replace(/\\/g, '\\\\').replace(/'/g, "\\'")}'`;
  }
  if (Array.isArray(value)) {
    if (value.length === 0) return '[]';
    const lines = value.map((item) => `${padInner}${phpExport(item, indent + 1)},`);
    return `[\n${lines.join('\n')}\n${pad}]`;
  }
  const entries = Object.entries(value);
  if (entries.length === 0) return '[]';
  const lines = entries.map(([key, val]) => {
    const k = `'${String(key).replace(/\\/g, '\\\\').replace(/'/g, "\\'")}'`;
    return `${padInner}${k} => ${phpExport(val, indent + 1)},`;
  });
  return `[\n${lines.join('\n')}\n${pad}]`;
}

const dir = __dirname;
const header = `<?php

/**
 * Admin Faker — demo investor Đà Nẵng (SOHO / hộ kinh doanh).
 * Chỉnh file này rồi: php artisan admin-faker:refresh
 *
 * DISCLAIMER: Tên thương hiệu minh họa UI — không đại diện thương hiệu thật.
 */
return `;
fs.writeFileSync(path.join(dir, 'mlhub_adminfaker_dn_soho.php'), `${header}${phpExport(data)};\n`, 'utf8');
console.log('OK', { campaigns: campaigns.length, customers: customers.length, locations: locations.length });
