<?php

namespace Modules\CustomMLHUB\Support\DemoData;

use Illuminate\Support\Str;

class DemoContentCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function demoUsers(): array
    {
        return [
            'mlhubfree' => [
                'email' => 'mlhubfree@mlhub.vn',
                'name' => 'MLHUB Free Demo',
                'plan' => 'mlhub-free-da-nang',
                'businesses' => 1,
                'campaigns' => 5,
                'landing_pages' => 4,
                'customers' => 73,
                'scans' => 197,
                'industries' => ['food_beverage'],
            ],
            'mlhubstarter' => [
                'email' => 'mlhubstarter@mlhub.vn',
                'name' => 'MLHUB Starter Demo',
                'plan' => 'mlhub-starter-monthly',
                'businesses' => 1,
                'campaigns' => 17,
                'landing_pages' => 9,
                'customers' => 241,
                'scans' => 1027,
                'industries' => ['food_beverage', 'beauty_personal_care'],
            ],
            'mlhubgrowth' => [
                'email' => 'mlhubgrowth@mlhub.vn',
                'name' => 'MLHUB Growth Demo',
                'plan' => 'mlhub-growth-monthly',
                'businesses' => 3,
                'campaigns' => 64,
                'landing_pages' => 41,
                'customers' => 823,
                'scans' => 3489,
                'industries' => ['beauty_personal_care', 'food_beverage', 'tourism_hospitality_experience'],
            ],
            'mlhubpro' => [
                'email' => 'mlhubpro@mlhub.vn',
                'name' => 'MLHUB Pro Demo',
                'plan' => 'mlhub-pro-monthly',
                'businesses' => 9,
                'campaigns' => 257,
                'landing_pages' => 169,
                'customers' => 2197,
                'scans' => 8751,
                'industries' => [
                    'food_beverage',
                    'beauty_personal_care',
                    'tourism_hospitality_experience',
                    'retail_goods',
                    'technical_repair_maintenance',
                    'health_dental_fitness',
                    'education_training_coaching',
                    'professional_b2b_services',
                    'real_estate_rental_property',
                ],
            ],
            'mlhubpartner' => [
                'email' => 'mlhubpartner@mlhub.vn',
                'name' => 'MLHUB Partner Demo',
                'plan' => 'mlhub-partner-monthly',
                'businesses' => 73,
                'campaigns' => 617,
                'landing_pages' => 431,
                'customers' => 4783,
                'scans' => 11873,
                'industries' => [
                    'food_beverage',
                    'beauty_personal_care',
                    'tourism_hospitality_experience',
                    'retail_goods',
                    'technical_repair_maintenance',
                    'health_dental_fitness',
                    'education_training_coaching',
                    'professional_b2b_services',
                    'real_estate_rental_property',
                    'wholesale_distribution',
                    'home_construction_interior',
                    'transport_delivery_logistics',
                    'digital_creator_online_business',
                    'small_manufacturing_processing_ocop',
                    'agriculture_fisheries_local_supply',
                    'culture_entertainment_sports_community',
                    'organization_association_public_community',
                    'other_needs_classification',
                ],
            ],
        ];
    }

    /**
     * @return list<array{group: string, category: string, type: string, name: string}>
     */
    public static function businessPool(): array
    {
        return [
            ['group' => 'beauty_personal_care', 'category' => 'spa_massage_wellness', 'type' => 'Spa', 'name' => 'Moc Spa Da Nang'],
            ['group' => 'beauty_personal_care', 'category' => 'spa_massage_wellness', 'type' => 'Spa', 'name' => 'An Nhien Goi Dau'],
            ['group' => 'beauty_personal_care', 'category' => 'nail_lash_brow_studio', 'type' => 'Nail studio', 'name' => 'Nail House Hai Chau'],
            ['group' => 'beauty_personal_care', 'category' => 'hair_salon', 'type' => 'Salon', 'name' => 'Salon Toc Song Han'],
            ['group' => 'food_beverage', 'category' => 'cafe_milk_tea', 'type' => 'Coffee shop', 'name' => 'Ca Phe Song Han'],
            ['group' => 'food_beverage', 'category' => 'seafood_local_restaurant', 'type' => 'Restaurant', 'name' => 'Hai San My Khe'],
            ['group' => 'food_beverage', 'category' => 'restaurant_eatery', 'type' => 'Restaurant', 'name' => 'Bun Cha Ca Hai Chau'],
            ['group' => 'tourism_hospitality_experience', 'category' => 'homestay_guesthouse', 'type' => 'Hotel', 'name' => 'Homestay An Thuong'],
            ['group' => 'tourism_hospitality_experience', 'category' => 'villa_resort_stay', 'type' => 'Hotel', 'name' => 'Villa My Khe'],
            ['group' => 'tourism_hospitality_experience', 'category' => 'local_tour_experience', 'type' => 'Event venue', 'name' => 'Tour Local Hoi An'],
            ['group' => 'retail_goods', 'category' => 'local_specialty_ocop_retail', 'type' => 'Local store', 'name' => 'Dac San Quang Da'],
            ['group' => 'retail_goods', 'category' => 'local_specialty_ocop_retail', 'type' => 'Local store', 'name' => 'Qua Tang OCOP Da Nang'],
            ['group' => 'technical_repair_maintenance', 'category' => 'auto_motor_repair', 'type' => 'Auto repair', 'name' => 'Garage Son Tra'],
            ['group' => 'technical_repair_maintenance', 'category' => 'electronics_appliance_repair', 'type' => 'Professional service', 'name' => 'Dien Lanh Thanh Khe'],
            ['group' => 'technical_repair_maintenance', 'category' => 'laundry_dry_cleaning', 'type' => 'Professional service', 'name' => 'Giat Ui Thanh Khe'],
            ['group' => 'health_dental_fitness', 'category' => 'dental_clinic', 'type' => 'Dentist', 'name' => 'Nha Khoa Hai Chau'],
            ['group' => 'health_dental_fitness', 'category' => 'gym_fitness_center', 'type' => 'Gym', 'name' => 'Gym Son Tra'],
            ['group' => 'education_training_coaching', 'category' => 'language_center', 'type' => 'Education center', 'name' => 'Trung Tam Tieng Anh Da Nang'],
            ['group' => 'professional_b2b_services', 'category' => 'accounting_tax_service', 'type' => 'Professional service', 'name' => 'Ke Toan Quang Da'],
            ['group' => 'real_estate_rental_property', 'category' => 'rental_room_property', 'type' => 'Real estate office', 'name' => 'Nha Tro Lien Chieu'],
            ['group' => 'home_construction_interior', 'category' => 'furniture_interior_shop', 'type' => 'Local store', 'name' => 'Noi That Cam Le'],
            ['group' => 'transport_delivery_logistics', 'category' => 'local_transport_delivery', 'type' => 'Professional service', 'name' => 'Van Chuyen Da Nang'],
            ['group' => 'digital_creator_online_business', 'category' => 'creator_studio', 'type' => 'Agency client', 'name' => 'Creator Studio An Thuong'],
            ['group' => 'small_manufacturing_processing_ocop', 'category' => 'ocop_local_production', 'type' => 'Local store', 'name' => 'Hop Tac Xa Nong San Hoa Vang'],
            ['group' => 'wholesale_distribution', 'category' => 'wholesale_local_goods', 'type' => 'Local store', 'name' => 'Kho Si Quang Nam'],
            ['group' => 'agriculture_fisheries_local_supply', 'category' => 'local_farm_supply', 'type' => 'Other', 'name' => 'Vuon Rau Hoa Vang'],
            ['group' => 'culture_entertainment_sports_community', 'category' => 'event_entertainment_venue', 'type' => 'Event venue', 'name' => 'San Khau Cong Dong Son Tra'],
            ['group' => 'organization_association_public_community', 'category' => 'association_community_group', 'type' => 'Other', 'name' => 'Hoi Quan Khoi Nghiep Dia Phuong'],
            ['group' => 'other_needs_classification', 'category' => 'multi_industry_business', 'type' => 'Other', 'name' => 'Dich Vu Tong Hop Hai Chau'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function addresses(): array
    {
        return [
            '23 Bach Dang, Hai Chau',
            '41 Tran Phu, Hai Chau',
            '87 Nguyen Van Linh, Hai Chau',
            '58 Le Duan, Hai Chau',
            '112 Vo Nguyen Giap, Son Tra',
            '19 Ho Nghinh, Son Tra',
            '71 Pham Van Dong, Son Tra',
            '95 Ngo Quyen, Son Tra',
            '36 An Thuong, Ngu Hanh Son',
            '63 Chau Thi Vinh Te, Ngu Hanh Son',
            '144 Dien Bien Phu, Thanh Khe',
            '29 Ha Huy Tap, Thanh Khe',
            '202 Nguyen Tat Thanh, Thanh Khe',
            '78 Cach Mang Thang 8, Cam Le',
            '15 Ong Ich Duong, Cam Le',
            '256 Ton Duc Thang, Lien Chieu',
            '91 Nguyen Luong Bang, Lien Chieu',
        ];
    }

    /**
     * @return list<string>
     */
    public static function customerNames(): array
    {
        return [
            'Nguyen Minh Anh', 'Tran Quoc Bao', 'Le Thanh Hang', 'Pham Gia Huy', 'Vo Ngoc Linh',
            'Hoang Tuan Kiet', 'Dang Phuong Vy', 'Bui Minh Chau', 'Do Anh Thu', 'Huynh Bao Tran',
            'Phan Hoai Nam', 'Truong Thien An', 'Ngo Khanh Linh', 'Mai Duc Phat', 'Ly Ha My',
            'Dinh Quang Vinh', 'Cao Ngoc Han', 'Ta Minh Quan', 'Vu Gia Han', 'Lam Thanh Dat',
        ];
    }

    /**
     * @return list<string>
     */
    public static function blogTitles(): array
    {
        $topics = [
            'MLHUB giup ho kinh doanh dia phuong tang truong nhu the nao',
            'Vi sao ho kinh doanh can mot bo tang truong so gon nhe',
            'Tu ma QR den CRM: hanh trinh du lieu cua mot co so kinh doanh',
            'Marketing automation cho ho kinh doanh nen bat dau tu dau',
            'Chuyen doi so ho kinh doanh tai Da Nang va Quang Nam',
            'Co hoi tang truong cho spa, salon va cafe tai Da Nang',
            'Vi sao Google Maps quan trong voi co so kinh doanh dia phuong',
            'Bai toan du lieu khach hang cho cua hang nho tai Da Nang',
            'Cach toi uu ho so Google Business cho quan cafe',
            'Vi sao danh gia that quan trong hon review ao',
            'Quy trinh xin danh gia khach hang dung cach',
            'Nhung loi pho bien khi quan ly Google Maps',
            'Google Maps, review va niem tin dia phuong',
            'Dung ma QR de thu phan hoi khach hang tai quay',
            'QR uu dai giup quan an keo khach quay lai ra sao',
            'Bien luot quet QR thanh du lieu khach hang',
            'Vi sao feedback xau van la du lieu tot',
            'Tao form thu khach tiem nang cho ho kinh doanh',
            'Ma uu dai giup khach quay lai nhu the nao',
            'Loyalty card cho quan cafe va spa nho',
            'Giu khach cu re hon tim khach moi',
            'Thiet ke uu dai khong lam giam gia tri thuong hieu',
            'Winback campaign cho khach cu sau mua hang',
            'CRM don gian cho ho kinh doanh',
            'Phan nhom khach hang theo hanh vi QR va coupon',
            'Tu dong nhac lich cho spa va salon',
            'Cham soc khach sau khi dung dich vu bang automation',
            'Task CRM giup chu ho khong bo sot khach',
            'Bo tang truong so cho spa va goi dau duong sinh',
            'Bo tang truong so cho quan cafe',
            'Bo tang truong so cho nha hang hai san',
            'Bo tang truong so cho homestay',
            'Bo tang truong so cho cua hang dac san va OCOP',
            'Bo tang truong so cho garage va rua xe',
            'Bo tang truong so cho nha khoa va phong kham',
            'Bo tang truong so cho trung tam giao duc',
            'Bo tang truong so cho bat dong san cho thue',
            'Ho kinh doanh nen theo doi nhung chi so nao',
            'QR scans, leads, bookings va coupon claims noi len dieu gi',
            'Bao cao 7 ngay, 30 ngay, 90 ngay cho chu ho',
            'Cach doc dashboard tang truong dia phuong',
            'Khi nao chien dich local marketing duoc xem la hieu qua',
            'AI co the ho tro chu ho kinh doanh ra sao',
            'Du lieu khach hang dia phuong la tai san tang truong',
            'Tu feedback den goi y chien dich bang AI',
            'AI ho tro tra loi review nhu the nao',
            'Vi sao MLHUB dung du lieu hanh vi thay vi doan mo',
            'Khong nen mua review ao',
            'Cach xin danh gia khach hang dung chuan',
            'Bao ve du lieu khach hang trong ho kinh doanh',
            'Ton trong quyen rieng tu khi thu thap lead',
            'Du lieu demo va du lieu that khac nhau nhu the nao',
            'Quy trinh tao chien dich QR review trong 15 phut',
            'Tao coupon theo mua vu cho cua hang nho',
            'Khach quay lai va bai toan cham soc sau mua',
            'Do hieu qua tang truong bang du lieu gan voi hanh vi',
            'Cach dung landing page cho mot uu dai dia phuong',
            'Khi nao nen dung booking page thay vi form lien he',
            'Lap danh sach khach hang tu lead form nhu the nao',
            'Phan biet khach moi, khach cu va khach nguy co roi bo',
            'Lich cham soc khach 30 ngay cho spa nho',
            'Chien dich review an toan cho nganh nha khoa',
            'Cach dung CRM task cho dich vu B2B dia phuong',
            'Mo hinh partner quan ly nhieu co so bang MLHUB',
            'Tu quan cafe mot diem den chuoi nho nhieu co so',
            'Cach nhin so lieu scan theo gio cao diem va thap diem',
            'Cach xu ly review 1 sao mot cach binh tinh',
            'Tao Google Business post mo phong cho lich noi dung',
            'Email automation nen gui luc nao de khong lam phien khach',
            'Webhook demo va webhook that khac nhau o dau',
            'WhatsApp/Zalo trong demo nen hieu nhu kenh mo phong',
            'Phan khuc khach hang theo gia tri vong doi',
            'Cham soc khach VIP bang tag va note CRM',
            'Gia tri cua feedback rieng tu truoc khi xin Google Review',
            'Nhung chi so nha dau tu nen nhin trong demo MLHUB',
            'Cau chuyen du lieu cua mot homestay An Thuong',
            'Cau chuyen du lieu cua mot tiem nail Hai Chau',
            'Cau chuyen du lieu cua mot quan hai san My Khe',
            'Cau chuyen du lieu cua mot cua hang OCOP',
            'Tai sao so lieu le tu nhien dang tin hon so tron',
            'Cach doc conversion tu QR sang lead',
            'Cach doc conversion tu coupon sang khach quay lai',
            'Mo phong 24 thang du lieu cho demo BOD',
            'Checklist truoc khi trinh bay dashboard MLHUB',
            'Loi ich cua tenant scoped data trong SaaS dia phuong',
        ];

        return array_slice($topics, 0, 86);
    }

    /**
     * @return list<string>
     */
    public static function faqQuestions(): array
    {
        $questions = [
            'MLHUB la gi?',
            'MLHUB phu hop voi ai?',
            'MLHUB co phai phan mem ban hang hay POS khong?',
            'MLHUB khac gi so voi chay quang cao?',
            'MLHUB co dung duoc cho ho kinh doanh nho khong?',
            'Goi Free dung duoc nhung gi?',
            'Khi nao nen nang len Starter?',
            'Goi Growth phu hop voi ai?',
            'Goi Pro khac Growth o diem nao?',
            'Goi Partner danh cho ai?',
            'Co the quan ly nhieu co so khong?',
            'Co the doi goi sau khong?',
            'Can nhap thong tin gi khi tao co so?',
            'Co the tao nhieu dia diem khong?',
            'Nganh nghe dung de lam gi?',
            'Neu khong thay nganh cua minh thi chon gi?',
            'MLHUB co tu goi y campaign theo nganh khong?',
            'QR trong MLHUB dung de lam gi?',
            'Co the tao QR xin review khong?',
            'Co the tao QR phat uu dai khong?',
            'Co the theo doi luot quet QR khong?',
            'Khach quet QR co can cai app khong?',
            'Review Booster la gi?',
            'MLHUB co tao review ao khong?',
            'Feedback xau duoc xu ly the nao?',
            'Co the chuyen khach hai long sang Google Review khong?',
            'Co the xem bao cao review khong?',
            'Booking Pages dung cho nganh nao?',
            'Lead Forms dung de thu thong tin gi?',
            'Coupon Campaigns hoat dong nhu the nao?',
            'Co phan biet coupon da nhan va da dung khong?',
            'Co the tao nhieu uu dai cho nhieu co so khong?',
            'CRM trong MLHUB co kho dung khong?',
            'Khach hang duoc tao tu dau?',
            'Co the gan tag khach hang khong?',
            'Co the tao task cham soc khach khong?',
            'Customer score la gi?',
            'Co xem lich su tuong tac cua khach khong?',
            'MLHUB co tu dong gui email khong?',
            'Co the tu dong nhac khach quay lai khong?',
            'Webhook dung de lam gi?',
            'WhatsApp/Zalo co duoc tich hop khong?',
            'Automation co gui that trong demo khong?',
            'MLHUB co ket noi Google Business khong?',
            'Google Business mock trong demo la gi?',
            'MLHUB co giup quan ly review Google khong?',
            'Co tu dong tra loi review khong?',
            'Co nen mua review Google khong?',
            'Dashboard hien thi nhung chi so nao?',
            'Bao cao 7/30/90 ngay dung de lam gi?',
            'Conversion rate duoc hieu nhu the nao?',
            'Vi sao so lieu demo khong tron?',
            'Du lieu khach hang co duoc bao ve khong?',
            'MLHUB co phu hop voi thi truong Da Nang khong?',
            'Nhung nganh nao nen dung MLHUB truoc?',
            'Spa, cafe, homestay dung MLHUB nhu the nao?',
            'OCOP va dac san co dung duoc MLHUB khong?',
            'MLHUB co phu hop cho doi tac trien khai nhieu ho kinh doanh khong?',
            'Du lieu demo co goi API that khong?',
            'Blog demo trong MLHUB dung de lam gi?',
            'FAQ demo co the sua lai sau khong?',
            'Landing page khac QR campaign nhu the nao?',
            'Loyalty card khac coupon o diem nao?',
            'Referral campaign phu hop nganh nao?',
            'Khi nao nen dung CRM segment?',
            'Task overdue tren CRM co y nghia gi?',
            'Google review thap co nen tra loi khong?',
            'AI trong MLHUB co thay chu kinh doanh quyet dinh khong?',
        ];

        return array_slice($questions, 0, 68);
    }

    public static function slug(string $value, string $prefix = ''): string
    {
        $slug = Str::slug($value);

        if ($slug === '') {
            $slug = substr(md5($value), 0, 12);
        }

        return trim($prefix.'-'.$slug, '-');
    }
}

