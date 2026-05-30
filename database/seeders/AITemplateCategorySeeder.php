<?php

namespace Database\Seeders;

use Database\Support\IdSequence;
use Illuminate\Database\Seeder;
use Modules\AdminAITemplateCategories\Models\AiTemplateCategory;

class AITemplateCategorySeeder extends Seeder
{
    public function run(): void
    {
        $changedAt = time();

        $categories = [
            ['id_secure' => '65a1059f85282', 'name' => 'Gợi ý', 'desc' => null, 'icon' => 'fa-light fa-stars', 'color' => 'dark', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f85951', 'name' => 'Facebook', 'desc' => null, 'icon' => 'fa-brands fa-square-facebook', 'color' => 'warning', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f85fda', 'name' => 'Instagram', 'desc' => null, 'icon' => 'fa-brands fa-instagram', 'color' => 'info', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f86883', 'name' => 'X (Twitter)', 'desc' => null, 'icon' => 'fa-brands fa-x-twitter', 'color' => 'success', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f87128', 'name' => 'LinkedIn', 'desc' => null, 'icon' => 'fa-brands fa-linkedin', 'color' => 'danger', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f876a9', 'name' => 'Pinterest', 'desc' => null, 'icon' => 'fa-brands fa-pinterest-p', 'color' => 'primary', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f87ad9', 'name' => 'Google Business Profile', 'desc' => null, 'icon' => 'fa-brands fa-google', 'color' => 'dark', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f87f18', 'name' => 'TikTok', 'desc' => null, 'icon' => 'fa-brands fa-tiktok', 'color' => 'success', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8833e', 'name' => 'Youtube', 'desc' => null, 'icon' => 'fa-brands fa-youtube', 'color' => 'primary', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f88814', 'name' => 'Viết lại', 'desc' => null, 'icon' => 'fa-light fa-file-signature', 'color' => 'danger', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f88c93', 'name' => 'Chỉnh sửa', 'desc' => null, 'icon' => 'fa-light fa-pencil-alt', 'color' => 'warning', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f890bc', 'name' => 'Giải thích & Mở rộng', 'desc' => null, 'icon' => 'fa-light fa-expand', 'color' => 'dark', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f894c3', 'name' => 'Tóm tắt', 'desc' => null, 'icon' => 'fa-light fa-filter', 'color' => 'success', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f898c7', 'name' => 'Khung tâm lý', 'desc' => null, 'icon' => 'fa-light fa-head-side-brain', 'color' => 'info', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f89cc3', 'name' => 'Khung sáng tạo nội dung', 'desc' => null, 'icon' => 'fa-light fa-lightbulb-on', 'color' => 'primary', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8a570', 'name' => 'Quảng cáo', 'desc' => null, 'icon' => 'fa-light fa-ad', 'color' => 'danger', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8aa4a', 'name' => 'Doanh nghiệp', 'desc' => null, 'icon' => 'fa-light fa-building', 'color' => 'warning', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8aeb0', 'name' => 'Kêu gọi hành động (CTA)', 'desc' => null, 'icon' => 'fa-light fa-mouse-pointer', 'color' => 'success', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8b318', 'name' => 'Giáo dục', 'desc' => null, 'icon' => 'fa-light fa-graduation-cap', 'color' => 'info', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8b77c', 'name' => 'Vui vẻ', 'desc' => null, 'icon' => 'fa-light fa-smile-beam', 'color' => 'primary', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8bc48', 'name' => 'Tương tác', 'desc' => null, 'icon' => 'fa-light fa-retweet', 'color' => 'success', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8c08b', 'name' => 'Truyền cảm hứng', 'desc' => null, 'icon' => 'fa-light fa-house-leave', 'color' => 'danger', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8c491', 'name' => 'Ngày lễ', 'desc' => null, 'icon' => 'fa-light fa-umbrella-beach', 'color' => 'success', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8c8b7', 'name' => 'Hộ kinh doanh nhỏ', 'desc' => null, 'icon' => 'fa-light fa-suitcase', 'color' => 'warning', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8ccb3', 'name' => 'Huấn luyện kinh doanh', 'desc' => null, 'icon' => 'fa-light fa-user-chart', 'color' => 'warning', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8d0cb', 'name' => 'Huấn luyện lối sống', 'desc' => null, 'icon' => 'fa-light fa-chalkboard-teacher', 'color' => 'primary', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8d49c', 'name' => 'Môi giới bất động sản', 'desc' => null, 'icon' => 'fa-light fa-house-day', 'color' => 'info', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8d874', 'name' => 'Tổ chức phi lợi nhuận', 'desc' => null, 'icon' => 'fa-light fa-home-heart', 'color' => 'warning', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8dc3f', 'name' => 'Doanh nhân', 'desc' => null, 'icon' => 'fa-light fa-user-tie', 'color' => 'primary', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8e044', 'name' => 'Agency marketing', 'desc' => null, 'icon' => 'fa-light fa-bullseye-arrow', 'color' => 'success', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8e81b', 'name' => 'Tác giả', 'desc' => null, 'icon' => 'fa-light fa-book-reader', 'color' => 'success', 'status' => true, 'created' => 0],
            ['id_secure' => 'legacyacctcat33seed01', 'name' => 'Công ty kế toán', 'desc' => null, 'icon' => 'fa-light fa-calculator', 'color' => 'info', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8f113', 'name' => 'Nhà hàng', 'desc' => null, 'icon' => 'fa-light fa-utensils', 'color' => 'info', 'status' => true, 'created' => 0],
            ['id_secure' => '65a1059f8f4fa', 'name' => 'Huấn luyện viên fitness', 'desc' => null, 'icon' => 'fa-light fa-dumbbell', 'color' => 'warning', 'status' => true, 'created' => 0],
        ];

        foreach ($categories as $index => $category) {
            $record = AiTemplateCategory::query()->firstOrNew(['id_secure' => $category['id_secure']]);

            if (! $record->exists) {
                $record->id = IdSequence::at($index);
            }

            $record->fill(array_merge($category, ['changed' => $changedAt]))->save();
        }
    }
}
