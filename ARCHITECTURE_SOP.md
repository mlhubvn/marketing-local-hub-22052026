# BỘ KHUNG SOP + THAM CHIẾU KỸ THUẬT MLHUB

> **Mục đích:** Tài liệu này kết hợp (1) quy trình vận hành SOP cho onboarding hộ kinh doanh Đà Nẵng và (2) **bản đồ tham chiếu codebase thực tế** — module, bảng DB, tham số JSON, trigger automation, route, KPI, env — để team triển khai dữ liệu mẫu / template / preset không bỏ sót tính năng đã có trong code.
>
> **Nguồn sự thật kỹ thuật:** catalog module đầy đủ → `ARCHITECTURE_MODULE.md`; kiến trúc → `ARCHITECTURE_BACKEND.md`; độ sẵn sàng/backlog → `ARCHITECTURE_FEATURE.md`; quy trình → `ARCHITECTURE_CHECKLIST.md`. Quét trực tiếp `modules/`, `app/`, `.env.example` (cập nhật 2026-06). File SOP tập trung vào **onboarding + mapping SOP → code**, không lặp catalog module.
>
> **Thương hiệu:** Hiển thị user-facing = **MLHUB**; path/route/config = **mlhub** (không dùng LocalBoost trên UI production).

---

## Mục lục


| Phần    | Nội dung                                         |
| ------- | ------------------------------------------------ |
| **I**   | Nguyên tắc SOP & vòng đời hộ kinh doanh (§1–§10) |
| **II**  | Map SOP → Codebase (§11–§22)                     |
| **III** | Phụ lục nhanh (bảng tra cứu)                     |


---

# PHẦN I — SOP CHIẾN LƯỢC

# 1. Nguyên tắc thiết kế SOP

MLHUB không nên thiết kế SOP theo từng tính năng đơn lẻ như QR, voucher, form, chatbot, email, WhatsApp, dashboard.

SOP đúng nên thiết kế theo chuỗi:

**Ngành nghề → Mục tiêu kinh doanh → Điểm chạm khách hàng → Dữ liệu cần thu → Template cần dùng → Automation cần bật → Chỉ số cần đo → Hành động duy trì.**

Công thức tổng quát:

**MARKET → BUSINESS PROFILE → TRAFFIC → OFFER → PROCESS → GOAL → RETENTION → DATA ENRICHMENT**


| Bước SOP         | Map module / bảng trong code                                                                     |
| ---------------- | ------------------------------------------------------------------------------------------------ |
| Market           | Taxonomy §2 + `ManagesBusinessForm::typeOptions()` + `MarketingTemplateIndex::categoryOptions()` |
| Business Profile | `lb_businesses` (`AppBusinessProfiles`), `lb_locations`                                          |
| Traffic          | `lb_qr_scans`, `lb_landing_pages.visits_count`, UTM (metadata)                                   |
| Offer            | Campaign `type=coupon`, Loyalty `lb_loyalty_cards`, Referral `lb_referral_campaigns`             |
| Process          | CRM `lb_customer_tasks`, Email/Webhook/WhatsApp automation, CRM automations                      |
| Goal             | `PortalGrowthDashboardMetrics`, `AppLocalAnalytics` (`portal/reports`)                           |
| Retention        | Loyalty, coupon winback, email automation, CRM segments                                          |
| Data Enrichment  | `lb_customers` counters, `lb_customer_activities`, `lb_customer_score_logs`                      |


---

# 2. Kiến trúc dữ liệu ngành nghề

Không nên dùng một danh mục ngành duy nhất. Cần tách thành 3 lớp + **map sang code hiện có**.

## 2.1. Lớp 1: FizaHUB Tax/Finance Category

Dùng cho: Thuế, hóa đơn, giao dịch, kế toán, dòng tiền, FizaScore, đối soát giấy phép.

> **Trạng thái code:** Chưa có bảng/field riêng trong MLHUB. Lưu tạm trong `metadata` khách hàng hoặc option tùy chỉnh khi tích hợp FizaHUB sau.

Ví dụ nhóm: Bán hàng hóa, Dịch vụ ăn uống, Dịch vụ thuần, Sản xuất/gia công, Vận tải, Xây dựng, Cho thuê tài sản, Nội dung số, Kinh doanh khác.

## 2.2. Lớp 2: MLHUB Marketing Category (SOP)

8 nhóm SOP + map sang **category code trong `lb_marketing_templates.category`**:


| #   | Nhóm SOP                       | Code SOP (§8)              | `category` trong code (gần nhất)    |
| --- | ------------------------------ | -------------------------- | ----------------------------------- |
| 1   | Ăn uống & đồ uống              | `food_beverage`            | `restaurant`                        |
| 2   | Làm đẹp & chăm sóc cá nhân     | `beauty_personal_care`     | `spa`, `salon`                      |
| 3   | Bán lẻ & cửa hàng              | `retail_store`             | `retail`                            |
| 4   | Dịch vụ địa phương & sửa chữa  | `local_repair_service`     | `local_service`, `auto_service`     |
| 5   | Du lịch, lưu trú & trải nghiệm | `tourism_hospitality`      | `general` (+ tag `tourism`)         |
| 6   | Giáo dục & đào tạo             | `education_training`       | `general` (+ subcategory tùy chỉnh) |
| 7   | Sức khỏe, nha khoa & thể thao  | `health_dental_fitness`    | `clinic`, `dentist`, `gym`          |
| 8   | B2B, BĐS & dịch vụ chuyên môn  | `b2b_professional_service` | `agency`, `real_estate`             |
| —   | Khác / chưa rõ                 | `other`                    | `general`                           |


**Danh mục `category` đầy đủ trong code** (`MarketingTemplateIndex::categoryOptions()`):

`general`, `restaurant`, `spa`, `salon`, `clinic`, `dentist`, `gym`, `retail`, `agency`, `real_estate`, `auto_service`, `local_service`

## 2.3. Lớp 3: Business Tags

**SOP tags** (hành vi) vs **code**:


| SOP tag                                           | Nơi lưu trong code                                                   |
| ------------------------------------------------- | -------------------------------------------------------------------- |
| `booking`, `review`, `coupon`, `lead`, `feedback` | `QrCampaign.type` + `lb_campaigns.settings`                          |
| `loyalty`                                         | `lb_loyalty_cards`, `LoyaltyStamp`                                   |
| `referral`                                        | `lb_referral_campaigns`                                              |
| `reminder`                                        | Email/WhatsApp automation `delay_`*                                  |
| `walk-in`, `appointment`                          | `Booking.status`, `booking_date`                                     |
| `repeat_purchase`, `retention`                    | `MarketingTemplate.goal=retention`, CRM segment filters              |
| CRM behavior tags                                 | `lb_customer_tags` + `lb_customer_tag_maps`                          |
| Auto tags khi submit growth tool                  | `CustomerUpserter` gắn `['campaign', $type]` vào `lb_customers.tags` |


## 2.4. Loại hình kinh doanh (onboarding form)

**Field:** `lb_businesses.type` (string, default `other`) — dropdown từ `ManagesBusinessForm::typeOptions()`:

Restaurant, Coffee shop, Bakery, Bar / Pub, Food truck, Salon, Barbershop, Spa, Nail studio, Clinic, Dentist, Chiropractor, Optical store, Pharmacy, Gym, Yoga studio, Fitness coach, Local store, Boutique, Auto repair, Car wash, Real estate office, Hotel, Event venue, Education center, Pet grooming, Agency client, Professional service, Other

> Khi seed dữ liệu mẫu: dùng chuỗi đã bọc `__()` — key tiếng Anh trong `lang/en.json` + `lang/vi.json`.



## 2.5. Chiến lược grouped UI cho `lb_businesses.type` hiện tại

Hiện tại codebase chưa có field riêng cho `primary_category`, `subcategory`, `business_tags` hay bảng taxonomy ngành nghề. Field đang dùng trong onboarding cơ sở kinh doanh là:

```text
lb_businesses.type

```

Field này hiện lưu một chuỗi đơn như:

```text
Restaurant
Coffee shop
Bakery
Spa
Hotel
Education center
Professional service
Other

```

Vì vậy, ở giai đoạn hiện tại **không đổi database/schema**. Cách triển khai an toàn là:

> **Group ở UI, type ở DB, metadata ở service.**

Nghĩa là:

- UI hiển thị theo nhóm ngành dễ hiểu cho hộ kinh doanh.
- Khi user chọn ngành, hệ thống vẫn lưu giá trị `type` hiện tại.
- Sau đó hệ thống dùng mapping nội bộ để suy ra group, dashboard preset, template pack, tags và campaign gợi ý.
- Không phá dữ liệu cũ.
- Không cần migrate DB.
- Không ảnh hưởng các module đang dùng `business.type`.

Ví dụ:

User nhìn trên UI:

```text
Nhóm: Ăn uống & đồ uống
Ngành cụ thể: Cà phê / Trà sữa

```

DB vẫn lưu:

```text
type = Coffee shop

```

Sau đó hệ thống suy ra metadata:

```text
group = food_beverage
dashboard_preset = dashboard_food_beverage
template_pack = food_beverage_free_starter
tags = qr, coupon, loyalty, review, feedback

```

---

## 2.6. Nhóm ngành hiển thị trên UI onboarding

Không hiển thị danh sách phẳng 29 ngành cho chủ hộ. Nên hiển thị thành 8 nhóm ngành chính + Khác.


| #   | Nhóm hiển thị trên UI                  | Group code nội bộ          | Mục đích                                      |
| --- | -------------------------------------- | -------------------------- | --------------------------------------------- |
| 1   | Ăn uống & đồ uống                      | `food_beverage`            | QR, coupon, review, loyalty, feedback         |
| 2   | Làm đẹp & chăm sóc cá nhân             | `beauty_personal_care`     | Booking, reminder, feedback, referral         |
| 3   | Bán lẻ & cửa hàng                      | `retail_store`             | Coupon, loyalty, khách cũ, mua lại            |
| 4   | Dịch vụ địa phương & sửa chữa          | `local_repair_service`     | Lead, booking, báo giá, review                |
| 5   | Du lịch, lưu trú & sự kiện             | `tourism_hospitality`      | Booking, review, referral, mùa vụ             |
| 6   | Giáo dục & đào tạo                     | `education_training`       | Lead, tư vấn, học thử, follow-up              |
| 7   | Sức khỏe, nha khoa & thể thao          | `health_dental_fitness`    | Booking, reminder, hội viên, tái khám/tái tập |
| 8   | B2B, bất động sản & dịch vụ chuyên môn | `b2b_professional_service` | Lead pipeline, CRM task, tư vấn               |
| 9   | Khác / chưa rõ                         | `other`                    | Fallback để không làm rơi hồ sơ               |


Nguyên tắc:

- UI cho chủ hộ chọn nhóm dễ hiểu.
- Backend vẫn lưu `type` cũ.
- Không thêm group code vào DB ở giai đoạn 1.
- Group code được tính runtime từ `type`.

---

## 2.7. Grouped Type Options đề xuất

### 2.7.1. Ăn uống & đồ uống

Group code:

```text
food_beverage

```


| Type lưu DB | Label hiển thị        |
| ----------- | --------------------- |
| Restaurant  | Nhà hàng / Quán ăn    |
| Coffee shop | Cà phê / Trà sữa      |
| Bakery      | Tiệm bánh             |
| Bar / Pub   | Bar / Pub             |
| Food truck  | Xe đồ ăn / Quầy đồ ăn |


Metadata mặc định:

```text
dashboard_preset = dashboard_food_beverage
template_pack = food_beverage_free_starter
tags = qr, coupon, loyalty, review, feedback, retention
recommended_modules = qr_campaigns, coupon_campaigns, review_booster, feedback_forms, loyalty_cards
first_campaign_goals = coupon, review, feedback

```

---

### 2.7.2. Làm đẹp & chăm sóc cá nhân

Group code:

```text
beauty_personal_care

```


| Type lưu DB | Label hiển thị                     |
| ----------- | ---------------------------------- |
| Salon       | Salon tóc                          |
| Barbershop  | Barber                             |
| Spa         | Spa / Massage / Gội đầu dưỡng sinh |
| Nail studio | Tiệm nail / Mi / Mày               |


Metadata mặc định:

```text
dashboard_preset = dashboard_beauty_booking
template_pack = beauty_booking_starter
tags = booking, reminder, feedback, referral, retention
recommended_modules = booking_pages, feedback_forms, coupon_campaigns, email_automation, crm
first_campaign_goals = booking, feedback, retention

```

---

### 2.7.3. Bán lẻ & cửa hàng

Group code:

```text
retail_store

```


| Type lưu DB | Label hiển thị                          |
| ----------- | --------------------------------------- |
| Local store | Cửa hàng địa phương / Tạp hóa / Đặc sản |
| Boutique    | Thời trang / Boutique / Phụ kiện        |


Metadata mặc định:

```text
dashboard_preset = dashboard_retail_loyalty
template_pack = retail_coupon_loyalty_starter
tags = coupon, loyalty, lead, repeat_purchase, retention
recommended_modules = coupon_campaigns, lead_forms, loyalty_cards, crm
first_campaign_goals = coupon, loyalty, lead

```

Ghi chú:

Đặc sản, OCOP, quà tặng, lưu niệm, thủ công Quảng Nam - Đà Nẵng có thể map tạm vào `Local store`. Sau này nếu dữ liệu đủ lớn mới cân nhắc thêm type/subcategory riêng.

---

### 2.7.4. Dịch vụ địa phương & sửa chữa

Group code:

```text
local_repair_service

```


| Type lưu DB          | Label hiển thị                          |
| -------------------- | --------------------------------------- |
| Auto repair          | Sửa chữa ô tô / xe máy                  |
| Car wash             | Rửa xe / Chăm sóc xe                    |
| Pet grooming         | Chăm sóc thú cưng                       |
| Professional service | Dịch vụ chuyên môn / Dịch vụ địa phương |


Metadata mặc định:

```text
dashboard_preset = dashboard_local_service
template_pack = local_service_lead_booking_starter
tags = lead, booking, review, referral, task_follow_up
recommended_modules = lead_forms, booking_pages, review_booster, crm_tasks, email_automation
first_campaign_goals = lead, booking, review

```

---

### 2.7.5. Du lịch, lưu trú & sự kiện

Group code:

```text
tourism_hospitality

```


| Type lưu DB | Label hiển thị                         |
| ----------- | -------------------------------------- |
| Hotel       | Khách sạn / Lưu trú / Homestay         |
| Event venue | Địa điểm sự kiện / Cưới hỏi / Workshop |


Metadata mặc định:

```text
dashboard_preset = dashboard_tourism_booking
template_pack = tourism_booking_review_starter
tags = booking, review, referral, seasonal, tourism
recommended_modules = booking_pages, lead_forms, review_booster, referral_campaigns, crm
first_campaign_goals = booking, review, referral

```

Ghi chú:

Tour, trải nghiệm địa phương, thuê xe, villa, homestay có thể map tạm vào `Hotel` hoặc `Event venue` theo hành vi kinh doanh chính.

---

### 2.7.6. Giáo dục & đào tạo

Group code:

```text
education_training

```


| Type lưu DB      | Label hiển thị                         |
| ---------------- | -------------------------------------- |
| Education center | Trung tâm giáo dục / Lớp học / Đào tạo |


Metadata mặc định:

```text
dashboard_preset = dashboard_education_lead
template_pack = education_lead_trial_starter
tags = lead, booking, follow_up, consultation, trial
recommended_modules = lead_forms, booking_pages, crm_tasks, email_automation
first_campaign_goals = lead, booking

```

---

### 2.7.7. Sức khỏe, nha khoa & thể thao

Group code:

```text
health_dental_fitness

```


| Type lưu DB   | Label hiển thị          |
| ------------- | ----------------------- |
| Clinic        | Phòng khám              |
| Dentist       | Nha khoa                |
| Chiropractor  | Trị liệu / Phục hồi     |
| Optical store | Kính thuốc              |
| Pharmacy      | Nhà thuốc               |
| Gym           | Gym                     |
| Yoga studio   | Yoga / Pilates          |
| Fitness coach | Huấn luyện viên cá nhân |


Metadata mặc định:

```text
dashboard_preset = dashboard_health_reminder
template_pack = health_booking_reminder_starter
tags = booking, reminder, feedback, retention, membership
recommended_modules = booking_pages, feedback_forms, review_booster, crm, email_automation
first_campaign_goals = booking, feedback, retention

```

Lưu ý:

Nhóm sức khỏe, nha khoa, phòng khám cần kiểm soát nội dung template. Không viết nội dung cam kết điều trị, không quảng cáo y tế quá đà, không dùng claim nhạy cảm.

---

### 2.7.8. B2B, bất động sản & dịch vụ chuyên môn

Group code:

```text
b2b_professional_service

```


| Type lưu DB          | Label hiển thị                                  |
| -------------------- | ----------------------------------------------- |
| Real estate office   | Bất động sản / Cho thuê / Môi giới              |
| Agency client        | Agency / Dịch vụ marketing / Khách hàng agency  |
| Professional service | Kế toán / Pháp lý / Tư vấn / Dịch vụ chuyên môn |


Metadata mặc định:

```text
dashboard_preset = dashboard_b2b_pipeline
template_pack = b2b_lead_pipeline_starter
tags = lead, consultation, crm_task, proposal, pipeline
recommended_modules = lead_forms, booking_pages, crm, crm_tasks, email_automation
first_campaign_goals = lead, booking

```

---

### 2.7.9. Khác / chưa rõ

Group code:

```text
other

```


| Type lưu DB | Label hiển thị       |
| ----------- | -------------------- |
| Other       | Khác / Chưa rõ ngành |


Metadata mặc định:

```text
dashboard_preset = dashboard_general
template_pack = general_free_starter
tags = lead, feedback
recommended_modules = lead_forms, feedback_forms, qr_campaigns
first_campaign_goals = lead, feedback

```

---

## 2.8. Quick pick cho thị trường Đà Nẵng - Quảng Nam

Để onboarding nhanh cho hộ kinh doanh ít rành công nghệ, UI nên hiển thị 6 lựa chọn phổ biến ở đầu:


| Quick pick          | Type lưu DB                           | Group                  |
| ------------------- | ------------------------------------- | ---------------------- |
| Cà phê / Trà sữa    | Coffee shop                           | `food_beverage`        |
| Quán ăn / Nhà hàng  | Restaurant                            | `food_beverage`        |
| Spa / Làm đẹp       | Spa                                   | `beauty_personal_care` |
| Bán lẻ / Cửa hàng   | Local store                           | `retail_store`         |
| Khách sạn / Lưu trú | Hotel                                 | `tourism_hospitality`  |
| Dịch vụ sửa chữa    | Auto repair hoặc Professional service | `local_repair_service` |


Nếu user chọn “Dịch vụ sửa chữa”, nên mở group `local_repair_service` để user chọn rõ hơn:

- Sửa chữa ô tô / xe máy
- Rửa xe / Chăm sóc xe
- Chăm sóc thú cưng
- Dịch vụ chuyên môn / Dịch vụ địa phương

---

## 2.9. Search alias cho ngành nghề

Để chủ hộ dễ tìm, search nên hỗ trợ alias tiếng Việt, tiếng Anh, không dấu, có dấu.


| Từ khóa user gõ                                      | Type gợi ý            |
| ---------------------------------------------------- | --------------------- |
| cafe, cà phê, ca phe, trà sữa, tra sua               | Coffee shop           |
| quán ăn, quan an, nhà hàng, nha hang, hải sản        | Restaurant            |
| bánh, bakery, tiệm bánh, tiem banh                   | Bakery                |
| bar, pub, beer                                       | Bar / Pub             |
| xe đồ ăn, food truck, quầy đồ ăn                     | Food truck            |
| spa, massage, gội đầu, goi dau, chăm sóc da          | Spa                   |
| nail, mi, mày, làm móng                              | Nail studio           |
| tóc, toc, salon, barber, cắt tóc                     | Salon hoặc Barbershop |
| phòng khám, phong kham, clinic                       | Clinic                |
| nha khoa, răng, rang, dentist                        | Dentist               |
| kính, kinh, optical                                  | Optical store         |
| nhà thuốc, nha thuoc, pharmacy                       | Pharmacy              |
| gym, fitness                                         | Gym                   |
| yoga, pilates                                        | Yoga studio           |
| huấn luyện viên, hlv, coach                          | Fitness coach         |
| tạp hóa, tap hoa, cửa hàng, cua hang, đặc sản, ocop  | Local store           |
| thời trang, thoi trang, boutique, phụ kiện           | Boutique              |
| sửa xe, sua xe, auto repair                          | Auto repair           |
| rửa xe, rua xe, car wash                             | Car wash              |
| khách sạn, khach san, homestay, villa, lưu trú       | Hotel                 |
| sự kiện, su kien, cưới hỏi, wedding, event           | Event venue           |
| giáo dục, giao duc, lớp học, trung tâm, tiếng Anh    | Education center      |
| thú cưng, thu cung, pet                              | Pet grooming          |
| bất động sản, bat dong san, cho thuê, môi giới       | Real estate office    |
| marketing, agency, media                             | Agency client         |
| tư vấn, tu van, kế toán, pháp lý, dịch vụ chuyên môn | Professional service  |


---

## 2.10. Type Metadata mapping

Cần bổ sung một helper/service để từ `type` suy ra metadata. Metadata không cần lưu DB ở giai đoạn đầu, có thể tính runtime.

### Metadata chuẩn nên trả về

```text
group
group_label
dashboard_preset
template_pack
tags
recommended_modules
first_campaign_goals

```

### Type → Group mapping


| Type hiện tại        | Group                      |
| -------------------- | -------------------------- |
| Restaurant           | `food_beverage`            |
| Coffee shop          | `food_beverage`            |
| Bakery               | `food_beverage`            |
| Bar / Pub            | `food_beverage`            |
| Food truck           | `food_beverage`            |
| Salon                | `beauty_personal_care`     |
| Barbershop           | `beauty_personal_care`     |
| Spa                  | `beauty_personal_care`     |
| Nail studio          | `beauty_personal_care`     |
| Local store          | `retail_store`             |
| Boutique             | `retail_store`             |
| Auto repair          | `local_repair_service`     |
| Car wash             | `local_repair_service`     |
| Pet grooming         | `local_repair_service`     |
| Hotel                | `tourism_hospitality`      |
| Event venue          | `tourism_hospitality`      |
| Education center     | `education_training`       |
| Clinic               | `health_dental_fitness`    |
| Dentist              | `health_dental_fitness`    |
| Chiropractor         | `health_dental_fitness`    |
| Optical store        | `health_dental_fitness`    |
| Pharmacy             | `health_dental_fitness`    |
| Gym                  | `health_dental_fitness`    |
| Yoga studio          | `health_dental_fitness`    |
| Fitness coach        | `health_dental_fitness`    |
| Real estate office   | `b2b_professional_service` |
| Agency client        | `b2b_professional_service` |
| Professional service | `b2b_professional_service` |
| Other                | `other`                    |


---

## 2.11. Gợi ý method kỹ thuật

Có thể bổ sung trong `ManagesBusinessForm` hoặc tách sang support class riêng như:

```text
Modules\AppBusinessProfiles\Support\BusinessTypeCatalog

```

Khuyến nghị dài hạn:

- Nếu chỉ cần dùng trong form: thêm method vào `ManagesBusinessForm`.
- Nếu cần dùng ở dashboard, template, report, onboarding: tách thành `BusinessTypeCatalog`.

### Method đề xuất

```php
public static function groupedOptions(): array

```

Trả về nhóm ngành + options.

```php
public static function popularOptions(): array

```

Trả về 6 quick pick phổ biến.

```php
public static function metadataFor(string $type): array

```

Trả về group, dashboard preset, template pack, tags, recommended modules, first campaign goals.

```php
public static function searchAliases(): array

```

Trả về alias tiếng Việt/tiếng Anh để search ngành.

```php
public static function normalizeType(string $type): string

```

Đảm bảo type lưu về đúng key hiện tại.

> **Cập nhật taxonomy `2026.06` (đã triển khai):** `BusinessTypeCatalog` giờ là source of truth cho cây ngành Alternative Data — **18 nhóm** (9 nhóm ưu tiên Đà Nẵng–Quảng Nam) + full cây ngành con. API chính: `taxonomyTree()`, `priorityGroupCodes()`, `groupMeta()/categoryMeta()`, `resolveSelection($group,$category)` (chuẩn hóa + snapshot metadata + suy `legacy_type`), `inferFromLegacyType($type)` (suy group/category cho hộ cũ chỉ có `type`). Các method cũ `groupedOptions()/popularOptions()` đã thay bằng `taxonomyTree()`; `metadataFor()/searchAliases()/normalizeType()/typeOptions()` vẫn giữ để tương thích legacy. Onboarding lưu `industry_group_code` + `industry_category_code` + `taxonomy_version` (cột mới trên `lb_businesses`); `industry_metadata` chỉ là snapshot, **không** phải source of truth. Nhóm `health_dental_fitness` (+ `nutrition_wellness_coach`, `pharmacy_retail`, `pharma_medical_wholesale`) có `compliance_sensitive=true` → không gợi ý template claim điều trị.

---

## 2.12. Quy tắc triển khai an toàn

Bắt buộc giữ các nguyên tắc sau:

1. Không đổi tên field `type`.
2. Không đổi validation hiện tại nếu chưa cần.
3. Không xóa `typeOptions()` cũ.
4. Không migrate database ở giai đoạn này.
5. Không lưu group vào DB ở giai đoạn đầu.
6. Không hard-code label tiếng Việt trong Blade nếu có thể đưa vào `__()`.
7. Chọn xong grouped UI vẫn phải set `$type` bằng value cũ như `Restaurant`, `Coffee shop`, `Spa`.
8. Nếu type cũ không nằm trong mapping, fallback về `Other`.
9. Metadata chỉ dùng để gợi ý, không làm điều kiện bắt buộc gây lỗi onboarding.
10. Đảm bảo user cũ có type cũ vẫn mở/sửa business bình thường.

---

## 2.13. Giai đoạn triển khai

### Giai đoạn 1: Safe UI Grouping

Không đổi database.

Việc cần làm:

- Giữ `type`.
- Giữ `typeOptions()`.
- Thêm grouped options.
- Thêm popular options.
- Thêm metadata mapping.
- Update UI chọn ngành thành grouped select/modal.
- Thêm search alias nếu kịp.
- User chọn xong vẫn lưu `$type`.

Đầu ra:

- Chủ hộ dễ chọn ngành hơn.
- Không phá code cũ.
- Có nền group nội bộ để gợi ý dashboard/template/campaign.

---

### Giai đoạn 2: Runtime Recommendation

Dùng metadata để gợi ý:

- Template pack.
- Campaign đầu tiên.
- Dashboard preset.
- Automation preset.
- Menu ưu tiên.
- KPI ưu tiên.
- Onboarding readiness.

Chưa cần lưu metadata vào DB.

---

### Giai đoạn 3: Taxonomy Schema sau khi có dữ liệu thật

Chỉ làm sau khi:

- Có người dùng thật.
- Có nhu cầu API với FizaHUB/Vbout/đối tác.
- Có dữ liệu xác nhận ngành nào cần tách sâu.
- Đã test đủ Đà Nẵng - Quảng Nam.

Khi đó mới cân nhắc thêm:

```text
primary_group_code
primary_subcategory_code
business_tags
taxonomy_version
external_mappings

```

---

## 2.14. Bổ sung checklist vào SOP 01

Khi chuẩn bị dữ liệu thị trường, bổ sung checklist sau:

- Không đổi schema `lb_businesses.type` ở giai đoạn đầu
- Có grouped UI cho `type`
- Có quick pick 6 ngành phổ biến Đà Nẵng - Quảng Nam
- Có mapping `type` → `group`
- Có mapping `type` → `dashboard_preset`
- Có mapping `type` → `template_pack`
- Có mapping `type` → `tags`
- Có fallback `Other`
- Có alias search tiếng Việt không dấu/có dấu
- Kiểm thử user cũ có business type cũ vẫn hoạt động
- Kiểm thử business mới chọn ngành từ grouped UI vẫn lưu đúng `type`

---

# 3. Bộ khung dữ liệu mẫu trước onboarding

Mỗi nhóm ngành (8 nhóm §2.2) cần 7 bộ dữ liệu mẫu — **map bảng cụ thể**:


| Bộ SOP                  | Bảng / model chính                                                     | Ghi chú triển khai                            |
| ----------------------- | ---------------------------------------------------------------------- | --------------------------------------------- |
| Business Profile Sample | `lb_businesses`, `lb_locations`                                        | `user_id` = tenant demo; `opening_hours` JSON |
| Customer Sample         | `lb_customers`                                                         | CRM fields: `status`, counters, `score`       |
| Offer Sample            | `lb_campaigns` (`type=coupon`) + `settings`                            | Hoặc `lb_loyalty_cards`                       |
| Campaign Sample         | `lb_campaigns` (6 type)                                                | Mỗi type 1 slug mẫu                           |
| Automation Sample       | `lb_email_automations`, `lb_webhook_automations`, `lb_crm_automations` | Dùng trigger §14                              |
| Dashboard Sample        | Cache `portal.growth_metrics.v1.{userId}`                              | Seed data → metrics tự tính                   |
| Report Sample           | `AppLocalAnalytics` export CSV                                         | Tab `overview`, `leads`, `reviews`            |


---

# 4. Bộ SOP chuẩn theo vòng đời hộ kinh doanh

## SOP 01: Chuẩn bị dữ liệu thị trường

Output: Industry Mapping Sheet, Sample Business Dataset, Template Pack Matrix, Dashboard Preset Matrix, Automation Preset Matrix, KPI Matrix.

**Checklist kỹ thuật trước khi mở onboarding:**

- [ ] Seed `lb_template_packs` + `lb_template_pack_items` theo `category` + `goal`
- [ ] Seed `lb_marketing_templates` (`is_system=true`, `source=marketplace`)
- [ ] Mỗi ngành ≥ 1 `QrCampaign` mỗi `type` cần thiết
- [ ] Email template seeds: `EmailAutomationCatalog::templateSeeds()`
- [ ] WhatsApp template seeds: `WhatsAppNotificationCatalog::templateSeeds()`
- [ ] Plan slug mặc định signup: `mlhub-free-da-nang` (`PlanSeeder`)

## SOP 02: Onboarding cơ sở kinh doanh

Bước tối thiểu → field code:


| Bước UI           | Field / hành động                                                         |
| ----------------- | ------------------------------------------------------------------------- |
| Tên cơ sở         | `lb_businesses.name`                                                      |
| Nhóm ngành        | `lb_businesses.type` (dropdown §2.4)                                      |
| SĐT / email       | `phone`, `email`                                                          |
| Địa chỉ / Maps    | `address`, `google_maps_url`                                              |
| Mục tiêu đầu tiên | Map → `MarketingTemplate.goal` hoặc `CampaignBuilderIndex::goalOptions()` |
| Gợi ý gói         | `AdminPlan` slug; user mới → `mlhub-free-da-nang`                         |


**Mục tiêu onboarding (map `goal` template):**


| Lựa chọn SOP       | `goal` trong `lb_marketing_templates` | Growth tool                  |
| ------------------ | ------------------------------------- | ---------------------------- |
| Có thêm khách      | `lead`                                | `type=lead`                  |
| Lưu khách          | `lead`, `coupon`                      | Lead + Customer list         |
| Kéo khách quay lại | `retention`, `loyalty`                | Coupon + Loyalty card        |
| Nhận đặt lịch      | `booking`                             | `type=booking`               |
| Xin đánh giá       | `review`                              | `type=review`                |
| Phát voucher       | `coupon`                              | `type=coupon`                |
| Chăm sóc khách cũ  | `retention`                           | Email automation + CRM tasks |


## SOP 03: Kích hoạt nhanh 7 ngày đầu


| Ngày | Hành động                   | Metric / route kiểm tra                    |
| ---- | --------------------------- | ------------------------------------------ |
| 1    | Tạo business + campaign đầu | `portal.businesses`, `portal.qr-campaigns` |
| 2    | Dán QR                      | `/qr/{slug}`, `/qr/{slug}/qr.png`          |
| 3    | Kiểm tra scan               | `lb_qr_scans`, dashboard `visits`          |
| 4    | Bật automation              | `portal.email-automations`                 |
| 5    | Email/WhatsApp follow-up    | `lb_email_automation_logs`                 |
| 6    | Xin review/feedback         | `type=review` hoặc `feedback`              |
| 7    | Báo cáo 7 ngày              | `portal.reports` + `dateRange=7`           |


## SOP 04–07

Giữ nguyên logic chiến lược từ bản SOP gốc (chiến dịch theo ngành, automation, dashboard preset, báo cáo tuần/tháng). Chi tiết kỹ thuật xem **§14–§16**.

---

# 5. Bộ khung ngành nghề Đà Nẵng + tính năng ưu tiên


| Nhóm SOP           | Tính năng ưu tiên                     | Module portal (route prefix)                                               |
| ------------------ | ------------------------------------- | -------------------------------------------------------------------------- |
| F&B                | QR, Coupon, Loyalty, Review, Feedback | `portal/coupon-campaigns`, `portal/review-booster`, `portal/loyalty-cards` |
| Làm đẹp            | Booking, Reminder, Feedback, Referral | `portal/booking-pages`, `portal/email-automation`                          |
| Bán lẻ             | Coupon, Loyalty, Lead                 | `portal/coupon-campaigns`, `portal/lead-forms`                             |
| Dịch vụ địa phương | Lead, Booking, Task follow-up         | `portal/lead-forms`, `portal/crm/tasks`                                    |
| Du lịch            | Booking, Lead, Review, Referral       | `portal/booking-pages`, `portal/referral` (public `/referral/`*)           |
| Giáo dục           | Lead, Booking tư vấn                  | `portal/lead-forms`, `portal/booking-pages`                                |
| Sức khỏe           | Booking, Reminder, Feedback           | `portal/booking-pages`, automation triggers                                |
| B2B                | Lead pipeline, CRM tasks              | `portal/crm`, `portal/lead-forms`                                          |


---

# 6. Matrix chuẩn dữ liệu mẫu (không bỏ sót)


| Hạng mục        | Bắt buộc           | Entity code                             |
| --------------- | ------------------ | --------------------------------------- |
| Business Sample | ≥ 3 / ngành        | `LocalBusiness`                         |
| Offer Sample    | ≥ 3 / ngành        | `QrCampaign` coupon hoặc `LoyaltyCard`  |
| QR Campaign     | ≥ 1                | `QrCampaign` + `QrScan`                 |
| Landing/Form    | ≥ 1                | `LandingPage` hoặc public campaign page |
| Booking         | Nếu có lịch hẹn    | `BookingService` + `Booking`            |
| Coupon          | F&B/retail/dịch vụ | `CouponRedemption`                      |
| Loyalty         | Khách quay lại     | `LoyaltyCard` + `LoyaltyCustomer`       |
| Review          | Mọi ngành          | `QrCampaign type=review`                |
| Feedback        | Mọi ngành          | `QrCampaign type=feedback`              |
| Automation      | ≥ 3 flow           | §14                                     |
| Dashboard       | Preset theo ngành  | §15                                     |
| Report          | 7 ngày + 30 ngày   | `LocalAnalyticsIndex` `dateRange` 7/30  |
| KPI             | §7 + §15           | `PortalGrowthDashboardMetrics`          |


---

# 7. Bộ KPI chuẩn → metric trong code


| KPI SOP                | Field / nguồn dữ liệu                              | Module                             |
| ---------------------- | -------------------------------------------------- | ---------------------------------- |
| QR scan                | `lb_qr_scans` count; dashboard `visits`            | `AppQRCampaigns`                   |
| Landing mở             | `lb_landing_pages.visits_count`                    | `AppLandingPages`                  |
| Form submit            | `lb_lead_submissions`, `lb_feedback_responses`     | Lead/Feedback                      |
| Lead mới               | dashboard `leads`                                  | `PortalGrowthDashboardMetrics`     |
| Booking                | `lb_bookings`; dashboard `bookings`                | `AppBookingPages`                  |
| Coupon claimed         | `lb_coupon_redemptions` status=`claimed`           | `AppCouponCampaigns`               |
| Coupon used            | `used_at` not null / status=`used`                 | `AppCouponCampaigns`               |
| Review positive        | `lb_review_feedbacks` rating ≥ 4 → `review_clicks` | `AppReviewBooster`                 |
| Review low             | rating < 4; trigger `review.low_score`             | Automation                         |
| Feedback               | dashboard `feedback`                               | `AppFeedbackForms`                 |
| Khách quay lại         | CRM `last_activity_at`, loyalty `completed_count`  | CRM / Loyalty                      |
| Conversion rate        | dashboard `conversion_rate`                        | Tính từ visits vs conversions      |
| Task chưa xử lý        | `lb_customer_tasks` status=`open`                  | `AppAdvancedCustomerCrm`           |
| Automation đang bật    | count `status=active` trên 3 bảng automation       | Email/Webhook/CRM                  |
| Hộ hoạt động 7/30 ngày | `users.updated_at` / last campaign activity        | Custom report (chưa có widget sẵn) |


---

# 8. Bộ chuẩn đặt tên dữ liệu

## Category Code (SOP)

`food_beverage`, `beauty_personal_care`, `retail_store`, `local_repair_service`, `tourism_hospitality`, `education_training`, `health_dental_fitness`, `b2b_professional_service`, `other`

## Template Pack Code (SOP) → `lb_template_packs.slug`

Ví dụ: `food_beverage_free_starter`, `beauty_booking_starter`, `retail_coupon_loyalty_starter`, …

## Marketing template `goal` (code)

`review`, `booking`, `coupon`, `feedback`, `lead`, `retention`, `loyalty`, `referral`

## Marketing template `type` (code)

`campaign`, `landing_page`, `form`, `content`, `email`, `whatsapp`, `automation`

## Dashboard Preset Code (SOP)

`dashboard_food_beverage`, `dashboard_beauty_booking`, … — **chưa có bảng preset**; hiện implement bằng filter `AppLocalAnalytics` + ẩn sidebar item theo `canUsePlanFeature`.

## Automation Preset Code (SOP) → `trigger_event`

`auto_lead_follow_up` → `lead.submitted`; `auto_booking_reminder` → `booking.confirmed` + delay; `auto_coupon_reminder` → `coupon.claimed`; …

---

# 9. Roadmap triển khai SOP

Giữ 5 giai đoạn: (1) taxonomy + sample data, (2) template pack, (3) onboarding UI, (4) automation preset, (5) retention report.

**Phụ thuộc code:** Mỗi giai đoạn nên commit qua seeder trong `modules/CustomMLHUB/` hoặc `database/seeders/` — không sửa thủ công trên server Coolify.

---

# 10. Kết luận chiến lược

MLHUB có đủ nền kỹ thuật (72 module, growth engine `lb_campaigns`, CRM, automation, loyalty, AI studio) để onboarding hộ kinh doanh vào **bộ máy mẫu theo ngành** — không phải phần mềm trống.

Chủ hộ chọn ngành + mục tiêu → hệ thống gợi ý template pack, campaign đầu tiên, automation preset, dashboard filter, báo cáo duy trì.

---

# PHẦN II — THAM CHIẾU CODEBASE

# 11. Tổng quan kiến trúc


| Thành phần       | Vị trí                                                                                                           |
| ---------------- | ---------------------------------------------------------------------------------------------------------------- |
| Shell            | `app/` — auth, middleware, `PortalGrowthDashboardMetrics`, `GrowthToolNotifier`, `PlanLimitGuard`                |
| Nghiệp vụ        | `modules/` — 72 module (31 Admin*, 37 App*, 3 Payment*, 1 Custom*)                                               |
| Theme            | `resources/themes/guest/mlhubfrontend`, `resources/themes/app/mlhubbackend`                                      |
| Bootstrap module | `bootstrap/providers.php` (auto-scan), `bootstrap/providers.marketplace.php` (CustomMLHUB, AppLoyaltyStampCards) |
| Cài đặt VN       | `modules/CustomMLHUB/` — `mlhub:install`, `mlhub:update`, `mlhub:sync-env-options`                               |


**Quy ước bảng:** tiền tố `lb_` áp dụng cho engine growth/business/CRM (`lb_campaigns`, `lb_businesses`, `lb_customers`, `lb_loyalty_*`…). **Không phổ quát** — bảng hệ thống (`users`, `plans`, `options`, `payment_*`, `credit_*`, `files`, `affiliate_*`, `ai_*`, `custom_domains`) không dùng `lb_`. Tenant scope: `user_id` (hoặc `TeamWorkspaceAccess::workspaceOwnerUserId()` cho workspace/AI).

---

# 12. Catalog module

> **Danh sách module đầy đủ (72) + route prefix / model / bảng / plan key / public endpoint / trạng thái production → `ARCHITECTURE_MODULE.md`** (nguồn sự thật duy nhất, §3 catalog). File SOP không lặp catalog module để tránh lệch nhau. Các phần dưới đây (§13–§22) chỉ giữ phần **mapping SOP → code** (engine growth, automation, dashboard, plan, credit) cần cho onboarding/vận hành.

---

# 13. Growth Tools Engine (`lb_campaigns`)

## 13.1. Campaign types


| `type`     | Tool             | Portal route name         | POST public               |
| ---------- | ---------------- | ------------------------- | ------------------------- |
| `review`   | Review Booster   | `portal.review-booster`   | `review-booster.feedback` |
| `booking`  | Booking Pages    | `portal.booking-pages`    | `booking-pages.submit`    |
| `coupon`   | Coupon Campaigns | `portal.coupon-campaigns` | `coupon-campaigns.claim`  |
| `feedback` | Feedback Forms   | `portal.feedback-forms`   | `feedback-forms.submit`   |
| `lead`     | Lead Forms       | `portal.lead-forms`       | `lead-forms.submit`       |
| `url`      | Custom URL QR    | `portal.qr-campaigns`     | — (redirect)              |


**Luồng public:** `QrCampaignPublicController::show` → `recordScan()` → redirect `url` / `LandingPage` published / view theo `type`.

**Rate-limit:** 5 POST công khai của growth tool đã có `throttle:10,1` (10 req/phút/IP): review feedback, booking submit, coupon claim, feedback form submit, lead submit. Các POST còn lại (`landing-pages.submit`, loyalty public, referral public) **chưa** có throttle — xem backlog `ARCHITECTURE_FEATURE.md §14`.

## 13.2. Cột `lb_campaigns`

`id`, `user_id`, `business_id`, `slug` (unique), `name`, `type`, `status` (default `active`), `destination_url`, `settings` (JSON), `published_at`, `timestamps`

## 13.3. JSON `settings` theo type

### Chung (`ManagesGrowthToolPageDesign`)


| Key                  | Kiểu   | Mô tả                        |
| -------------------- | ------ | ---------------------------- |
| `create_public_page` | bool   | Tạo trang công khai          |
| `generate_qr_code`   | bool   | Sinh QR                      |
| `landing_template`   | string | Key từ `PageTemplateCatalog` |
| `design`             | object | Xem bảng design bên dưới     |


`**design` object:**

`template`, `layout_style`, `primary_color`, `background_color`, `accent_color`, `background_type`, `font_style`, `button_style`, `card_style`, `logo_url`, `logo_shape`, `cover_image`, `show_logo`, `show_business_info`, `show_social_links`, `show_benefits`, `show_terms`, `show_faq`

### `review`

`google_review_url`, `facebook_review_url`, `thank_you_message`, `negative_feedback_message`, `positive_threshold` (3–5), `preferred_destination` (`google`|`facebook`) + design chung

### `booking`

`headline` (default `"Book an appointment"`) + design chung. Dịch vụ: bảng `lb_booking_services`.

### `coupon`

`discount_type` (`percentage`|`fixed`|`free_item`|`custom`), `discount_value`, `coupon_code`, `usage_limit`, `expiry_date`, `terms`, `status` (`draft`|`active`) + design

### `feedback`

`headline`, `thank_you_message`, `rating_required`, `contact_required` + design

### `lead`

`headline` + design

### `url`

`destination_url` cột DB; settings có thể `source=manual`

## 13.4. `lb_booking_services`

`name`, `duration_minutes`, `price`, `description`, `available_days` (JSON), `time_slots` (JSON), `max_bookings_per_slot`, `is_active`, `use_business_hours`, `slot_interval`, `buffer_before`, `buffer_after`

## 13.5. Bảng kết quả growth tool


| Bảng                    | Cột chính                                                                                                                           |
| ----------------------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| `lb_review_feedbacks`   | `campaign_id`, `rating`, `customer_name/phone/email`, `message`                                                                     |
| `lb_bookings`           | `campaign_id`, `service_id`, `status` (pending/confirmed/cancelled/completed), `booking_date`, `booking_time`, `customer_*`, `note` |
| `lb_coupon_redemptions` | `campaign_id`, `code` (unique), `customer_*`, `status` (claimed/used/expired), `used_at`                                            |
| `lb_feedback_responses` | `campaign_id`, `rating`, `customer_*`, `message`, `payload` JSON                                                                    |
| `lb_lead_submissions`   | `campaign_id`, `name`, `phone`, `email`, `message`, `payload` JSON                                                                  |
| `lb_qr_scans`           | `campaign_id`, `ip_address`, `user_agent`, `device`, `country`, `city`, `created_at`                                                |


## 13.6. Customer upsert (mọi submit)

`Modules\AppCustomers\Support\CustomerUpserter::fromCampaign()`:

- Match theo `email` → `phone` → `name`
- Merge `metadata`: `last_source`, `last_campaign_id`, `last_campaign_type`
- Tags mới: `['campaign', $campaign->type]`

## 13.7. Thông báo nội bộ

`App\Support\GrowthToolNotifier`: `leadCreated`, `bookingCreated`, `couponClaimed`, `feedbackCreated`, `googleReviewCreated` → `NotificationService` + `PortalGrowthDashboardMetrics::forget($userId)`.

## 13.8. Route công khai đầy đủ


| Route name                  | Method | Path                         |
| --------------------------- | ------ | ---------------------------- |
| `qr-campaigns.public`       | GET    | `/qr/{campaign:slug}`        |
| `qr-campaigns.svg`          | GET    | `/qr/{campaign:slug}/qr.svg` |
| `qr-campaigns.png`          | GET    | `/qr/{campaign:slug}/qr.png` |
| `review-booster.feedback`   | POST   | `/qr/{slug}/feedback`        |
| `booking-pages.submit`      | POST   | `/qr/{slug}/booking`         |
| `coupon-campaigns.claim`    | POST   | `/qr/{slug}/coupon`          |
| `feedback-forms.submit`     | POST   | `/qr/{slug}/feedback-form`   |
| `lead-forms.submit`         | POST   | `/qr/{slug}/lead`            |
| `landing-pages.public`      | GET    | `/lp/{landingPage:slug}`     |
| `loyalty-cards.public`      | GET    | `/loyalty/{card:slug}`       |
| `loyalty-cards.stamp`       | POST   | `/loyalty/{card:slug}/stamp` |
| `referral-campaigns.public` | GET    | `/referral/{campaign:slug}`  |
| `referral-links.public`     | GET    | `/r/{link:code}`             |
| `referral-links.convert`    | POST   | `/r/{link:code}/convert`     |


> Loyalty/Referral public routes **chưa** có `throttle` — backlog `ARCHITECTURE_FEATURE.md`.

---

# 14. Automation — trigger, điều kiện, biến

## 14.1. Ma trận trigger: SOP ↔ Code


| Trigger SOP (§5 cũ) | Email `trigger_event` | Webhook                                   | WhatsApp              | CRM `trigger_event`            |
| ------------------- | --------------------- | ----------------------------------------- | --------------------- | ------------------------------ |
| customer.created    | `customer.created`    | `customer.created`                        | `customer.created`    | `customer_created`             |
| lead.submitted      | `lead.submitted`      | `lead.submitted`                          | `lead.submitted`      | — (qua lead model)             |
| booking.submitted   | `booking.submitted`   | `booking.submitted`                       | `booking.submitted`   | `booking_submitted`            |
| booking.confirmed   | `booking.confirmed`   | `booking.confirmed`                       | `booking.confirmed`   | `booking_confirmed`            |
| booking.completed   | `booking.completed`   | `booking.completed`                       | `booking.completed`   | `booking_completed`            |
| coupon.claimed      | `coupon.claimed`      | `coupon.claimed`                          | `coupon.claimed`      | `coupon_claimed`               |
| coupon.used         | `coupon.used`         | `coupon.used`                             | `coupon.used`         | `coupon_used`                  |
| feedback.submitted  | `feedback.submitted`  | `feedback.submitted`                      | `feedback.submitted`  | `feedback_submitted`           |
| review.positive     | `review.positive`     | `review.positive`                         | `review.positive`     | `review_rating_submitted`      |
| review.low_score    | `review.low_score`    | `review.low_score` / `feedback.low_score` | `review.low_score`    | `low_score_feedback_submitted` |
| loyalty.stamp_added | —                     | —                                         | `loyalty.stamp_added` | `loyalty_stamp_added`          |


**CRM-only triggers:** `customer_inactive`, `note_created`, `task_created`, `reward_unlocked`, `referral_converted`, `booking_cancelled`, `google_review_synced`

## 14.2. Cách fire event (Eloquent observers)

Đăng ký trong `*ServiceProvider::boot()`:

- `Booking::created` → `booking.submitted`
- `Booking::updated` (status change) → `booking.confirmed|cancelled|completed`
- `CouponRedemption::created` → `coupon.claimed`; updated `used_at` → `coupon.used`
- `FeedbackResponse::created` → `feedback.submitted`; rating thấp → `feedback.low_score`
- `ReviewFeedback::created` → rating ≥ 4 → `review.positive`, else `review.low_score`
- `Customer::created` → `customer.created`

## 14.3. Schema automation

### `lb_email_automations`

`user_id`, `business_id`, `email_template_id`, `name`, `trigger_event`, `status`, `delay_type`, `delay_value`, `delay_unit`, `condition_json`, `action_json`, `send_to`, `custom_email`, `created_by`

### `lb_webhook_automations`

- `webhook_url`, `method`, `headers_json`, `secret_token`, `retry_on_failure`

**Webhook condition fields:** `business_id`, `campaign_type`, `booking.status`, `coupon.status`, `lead.status`, `review.rating`, `customer.email`, `customer.phone`

### `lb_crm_automations`

`team_id`, `trigger_event` (snake_case), `action_json` — actions: `send_email`, `send_whatsapp`, `send_webhook` (map sang dot-notation external trigger)

## 14.4. Biến template (Email / WhatsApp)

`{business_name}`, `{business_phone}`, `{business_email}`, `{business_website}`, `{business_address}`, `{customer_name}`, `{customer_email}`, `{customer_phone}`, `{campaign_name}`, `{campaign_type}`, `{public_page_url}`, `{booking_service}`, `{booking_date}`, `{booking_time}`, `{booking_status}`, `{coupon_code}`, `{coupon_expiry}`, `{review_rating}`, `{feedback_message}`, `{lead_status}`

WhatsApp thêm: `{stamp_card_name}`, `{stamp_count}`, `{required_stamps}`, `{reward_title}`

## 14.5. Email template seeds có sẵn

`EmailAutomationCatalog::templateSeeds()` — 12 mẫu: Booking Request/Confirmed/Reminder/Cancelled, Coupon Claimed/Used, Lead Received/Follow-up, Feedback, Low-score Recovery, Review Thank You.

---

# 15. Dashboard, báo cáo, cache

## 15.1. `PortalGrowthDashboardMetrics`

File: `app/Support/Portal/PortalGrowthDashboardMetrics.php`


| Cache key                                    | TTL            | Nội dung       |
| -------------------------------------------- | -------------- | -------------- |
| `portal.growth_metrics.v1.{userId}`          | 15 phút        | Scalar metrics |
| `portal.top_campaigns.v2.{userId}`           | 15 phút        | Top 6 campaign |
| `portal.recent_activity.v2.{userId}.{limit}` | 10 phút        | limit 8–64     |
| `portal.plan_usage.{v}.{userId}.{locale}`    | PlanLimitGuard | Usage vs limit |


**Metric fields:** `businesses`, `campaigns`, `active_campaigns`, `visits`, `review_clicks`, `leads`, `bookings`, `coupon_claims`, `feedback`, `conversion_rate`

**Invalidate:** `PortalGrowthDashboardMetrics::forget($userId)` — sau scan, conversion, `GrowthToolNotifier`.

## 15.2. `AppLocalAnalytics` (`portal/reports`)


| Filter           | Giá trị                        |
| ---------------- | ------------------------------ |
| `tab`            | `overview`, `leads`, `reviews` |
| `dateRange`      | `7`, `30`, `90`, `all`         |
| `businessFilter` | `all` hoặc `business_id`       |
| `campaignType`   | `all` hoặc `review             |


Export CSV: cột Campaign, Business, Type, Visits, Leads, Bookings, Coupons, Review Clicks, Feedback, Conversion Rate.

## 15.3. Dashboard preset theo ngành (SOP §6)

Chưa có entity `dashboard_preset` — triển khai bằng:

1. Widget dashboard portal (`app/Livewire/Portal/Dashboard.php`) — lazy `loadDashboardSections`
2. Filter mặc định `LocalAnalytics` theo `category` business
3. Ẩn sidebar: `visible => fn () => auth()->user()?->canUsePlanFeature('...')`

---

# 16. CRM, Loyalty, Referral

## 16.1. `lb_customers` (đầy đủ sau CRM migration)

`user_id`, `business_id`, `team_id`, `name`, `phone`, `email`, `avatar`, `status` (default `active`), `source_type`, `source_id`, `tags` JSON, `note`, `metadata` JSON, `first_seen_at`, `last_activity_at`, `last_contacted_at`, counters (`total_bookings`, `total_coupon_claims`, `total_coupon_used`, `total_feedback`, `total_reviews`, `total_loyalty_stamps`, `total_referrals`), `score`, `lifetime_value`

## 16.2. CRM tables


| Bảng                                                                       | Mục đích                                                                 |
| -------------------------------------------------------------------------- | ------------------------------------------------------------------------ |
| `lb_customer_activities`                                                   | Timeline hoạt động (`type`, `title`, `related_type/id`, `source_module`) |
| `lb_customer_tags` / `lb_customer_tag_maps`                                | Tag CRM                                                                  |
| `lb_customer_notes`                                                        | Ghi chú (`visibility`, `pinned`)                                         |
| `lb_customer_tasks`                                                        | Task (`type`, `priority`, `status`, `due_at`, `assigned_to`)             |
| `lb_customer_segments`                                                     | Segment (`filters` JSON, `is_dynamic`)                                   |
| `lb_customer_score_logs`                                                   | Lịch sử điểm                                                             |
| `lb_crm_automations` / `lb_crm_automation_logs` / `lb_crm_automation_jobs` | Automation nội bộ CRM                                                    |


**CRM routes:** `portal.crm.customers`, `.segments`, `.tags`, `.tasks`, `.automations`, `.reports`, `.export.customers`

## 16.3. Loyalty (`lb_loyalty_cards`)

`slug`, `name`, `required_stamps`, `stamp_method` (default `qr_scan`), `customer_identifier` (default `phone`), `reward_title`, `reward_type`, `reward_value`, `expiry_days`, `stamp_cooldown_minutes`, `max_stamps_per_day`, `settings` JSON, `status`

## 16.4. Referral

`lb_referral_campaigns`, `lb_referral_links` (`code`), `lb_referrals`, `lb_referral_rewards`

---

# 17. Marketing Templates & Landing Pages

## 17.1. `lb_marketing_templates`

`name`, `slug`, `type`, `category`, `goal`, `description`, `icon`, `preview_image`, `content` JSON, `is_system`, `source` (`custom|imported|ai_generated|marketplace`), `visibility`, `marketplace_status`, `featured`, `rating_`*, `status`, `version`, `usage_count`

## 17.2. Template packs

`lb_template_packs`, `lb_template_pack_items`, `lb_template_usages`, `lb_template_imports`, `lb_template_ratings`

## 17.3. `lb_landing_pages`

`slug`, `title`, `type`, `template`, `status`, `content` JSON, `settings` JSON, `visits_count`, `conversions_count`, `campaign_id`, `business_id`

## 17.4. Page template catalog

`modules/AppLandingPages/Support/PageTemplateCatalog.php` — preset theo growth type (`review_*`, `booking_*`, `coupon_*`, `feedback_*`, `lead_*`). Method: `forType($type)`, `defaultForType($type)`, `designFor($template)`.

---

# 18. Gói cước & quyền (Plan)

## 18.1. Plan slugs (`PlanSeeder`)


| Slug                                      | Vai trò                                         |
| ----------------------------------------- | ----------------------------------------------- |
| `mlhub-free-da-nang`                      | **Default signup** (`default_signup_plan=true`) |
| `mlhub-starter-{monthly,yearly,lifetime}` | Starter                                         |
| `mlhub-growth-{monthly,yearly,lifetime}`  | Growth                                          |
| `mlhub-pro-{monthly,yearly,lifetime}`     | Pro                                             |
| `mlhub-partner-{monthly,yearly,lifetime}` | Partner (super admin: `MLHUB_ADMIN_PLAN_SLUG`)  |


## 18.2. Permission keys (`register_plan_permission`)


| Key                     | Limit fields chính                                                                                                |
| ----------------------- | ----------------------------------------------------------------------------------------------------------------- |
| `credits_usage`         | `credits_usage_limit`, `credit_cost_`*                                                                            |
| `localboost`            | `max_businesses`, `max_campaigns`, `max_landing_pages`, `max_qr_codes`, `max_templates`, `remove_branding`        |
| `files`                 | `max_storage_size_mb`, `max_file_size_mb`, `file_picker`, `image_editor`, …                                       |
| `ai_studio`             | `ai_studio_caption_generator`, `ai_studio_repurpose`, `ai_studio_content_planner`, `ai_studio_image`              |
| `teams`                 | `max_team_members`                                                                                                |
| `advanced_crm`          | `customer_tags`, `customer_segments`, `customer_tasks`, `crm_automations`, `crm_activity_retention_days`          |
| `google_business`       | `max_google_business_connections`, `max_google_business_locations`, `google_review_sync`, …                       |
| `email_automation`      | `max_email_automations`, `max_email_templates`, `emails_per_month`, `automation_delay`, `automation_conditions`   |
| `whatsapp_notification` | `max_whatsapp_notifications`, `max_whatsapp_templates`, `whatsapp_messages_per_month`, …                          |
| `webhook_automation`    | `max_webhook_automations`, `webhooks_per_month`, `webhook_custom_headers`, `webhook_retry`                        |
| `loyalty_stamp_cards`   | `max_loyalty_cards`, `max_loyalty_customers`, `max_referral_campaigns`, `loyalty_rewards`, `loyalty_staff_redeem` |
| `qr_custom_domains`     | `max_custom_domains`                                                                                              |
| `affiliate`             | toggle                                                                                                            |
| `support`               | toggle                                                                                                            |


**Enforcement:** `App\Support\Plans\PlanLimitGuard` — `ensureBusinessCanBeCreated`, `ensureCampaignCanBeCreated`, `ensureLandingPageCanBeCreated`, `ensureQrCodeCanBeCreated`, `ensureTemplateCanBeCreated`, …

**No-plan fallback:** `MLHUB_NO_PLAN_`* env → `config/mlhub.php` → `NoPlanAccess::permissionsFromEnv()`. Chi tiết ma trận: `ARCHITECTURE_FEATURE.md` §1.1.

---

# 19. Credit AI

## 19.1. Registered actions (`AppAIStudioServiceProvider`)


| Action key                    | Plan cost key                             | Default cost |
| ----------------------------- | ----------------------------------------- | ------------ |
| `ai_studio_generate_captions` | `credit_cost_ai_studio_generate_captions` | 1            |
| `ai_studio_repurpose_content` | `credit_cost_ai_studio_repurpose_content` | 1            |
| `ai_studio_plan_calendar`     | `credit_cost_ai_studio_plan_calendar`     | 1            |
| `ai_studio_review_reply`      | `credit_cost_ai_studio_review_reply`      | 1            |
| `ai_studio_generate_image`    | `credit_cost_ai_studio_generate_image`    | 3            |


**Dùng trong code chưa register (fallback cost):** `ai_studio_semantic_search`, `ai_studio_best_time`, `ai_studio_review_content`, `ai_studio_generate_video`

**Helpers:** `credit_service()`, `consume_credits()`, `credit_summary()` — `app/Support/helpers.php`

## 19.2. AI modules & routes


| Module              | Route                         | Credit action                  | Trạng thái        |
| ------------------- | ----------------------------- | ------------------------------ | ----------------- |
| AppAIStudio         | `portal/ai-studio`            | Campaign builder, review reply | ✅ Active          |
| AppAIContent        | `portal/ai-studio/ai-content` | captions                       | ✅ Active          |
| AppAIRepurpose      | `portal/ai-studio/repurpose`  | repurpose                      | 🟡 Active          |
| AppAIContentPlanner | `portal/ai-studio/planner`    | calendar                       | 🟡 Active          |
| AppAIImage          | `portal/ai-studio/image`      | image                          | 🟡 Active          |
| AppAIVideo          | *(config `…/video`)*          | video                          | 🔴 Dormant (no route) |
| AppAIReview         | *(config `…/review`)*         | review content                 | 🔴 Dormant (no route) |
| AppAIBestTime       | *(config `…/timing`)*         | best time                      | 🔴 Dormant (no route) |
| AppAISemanticSearch | *(config `…/search`)*         | search                         | 🔴 Dormant (no route) |

> **4 module dormant**: provider không `loadRoutesFrom` → route không truy cập được; credit action chưa `register_credit_action`. Chi tiết: `ARCHITECTURE_MODULE.md` §8, backlog `ARCHITECTURE_FEATURE.md` §7.

---

# 20. Sidebar portal (menu tham chiếu)


| Section key        | Nhãn            | Module tiêu biểu                                       |
| ------------------ | --------------- | ------------------------------------------------------ |
| `overview`         | Overview        | Dashboard                                              |
| `local-businesses` | Enterprise      | Businesses, Customers                                  |
| `growth-tools`     | Growth Tools    | Review, Booking, Coupon, Feedback, Lead, Loyalty       |
| `crm`              | CRM             | Customers, Segments, Tags, Tasks, Automations, Reports |
| `marketing-assets` | Assets          | QR, Landing, Templates, Files, Domains                 |
| `analytics`        | Reports         | Local Analytics                                        |
| `ai-tools`         | AI Tools        | Campaign Builder, Content Writer, …                    |
| `automation`       | Automation      | Email, Webhook, WhatsApp                               |
| `google-business`  | Google Business | GBP integration                                        |
| `team-billing`     | Account         | Teams, Billing, Profile, Credits, Support              |


Đăng ký qua: `register_user_sidebar_section()`, `register_user_sidebar_item()` — `app/Support/helpers.php`.

---

# 21. Helper & extension points


| Helper                                                          | File                                       | Mục đích           |
| --------------------------------------------------------------- | ------------------------------------------ | ------------------ |
| `register_sidebar_`*, `register_user_sidebar_*`                 | `app/Support/helpers.php`                  | Menu admin/portal  |
| `register_plan_permission`, `plan_permissions`                  | `app/Support/helpers.php`                  | Gói cước           |
| `register_credit_action`, `consume_credits`                     | `app/Support/helpers.php`                  | Credit AI          |
| `register_admin_dashboard_item`, `register_user_dashboard_item` | `app/Support/helpers.php`                  | Widget dashboard   |
| `format_money`, `format_number_locale`, `format_date_locale`, … | `app/Support/helpers.php`                  | Hiển thị locale VN |
| `platform_format_settings`, `platform_format_config`            | `app/Support/helpers.php`                  | JS format          |
| `theme_vite`, `theme_view`, `theme_asset`                       | `modules/AdminThemes/Support/helpers.php`  | Theme              |
| `captcha_render`, `captcha_verify`, `with_captcha_validation`   | `modules/AdminCaptcha/Support/helpers.php` | Captcha auth       |
| `option()`                                                      | `OptionStore`                              | Admin settings     |


**Điểm mở rộng an toàn:** registry helper → module `Custom`* → rebind container → `routes/custom.php` → `bootstrap/providers.marketplace.php`.

---

# 22. Biến môi trường quan trọng (nhóm)


| Nhóm                     | Keys                                                                             |
| ------------------------ | -------------------------------------------------------------------------------- |
| Laravel core             | `APP_*`, `DB_*`, `SESSION_*`, `REDIS_*`, `QUEUE_*`, `CACHE_*`                    |
| MLHUB install            | `MLHUB_FIRST_USER_*`, `MLHUB_STARTING_ID`, `MLHUB_ADMIN_PLAN_SLUG`               |
| MLHUB no-plan            | `MLHUB_NO_PLAN_*` (toàn bộ ma trận §18)                                          |
| MLHUB safety             | `MLHUB_ALLOW_RESET_DEMO`, `APP_DEMO`, `APP_INSTALLED`                            |
| Deploy / Livewire        | `LIVEWIRE_RELEASE_TOKEN`, `RUN_QUEUE_WORKER`, `TRUSTED_PROXIES`                  |
| Theme / site             | `THEME_FRONTEND`, `THEME_BACKEND`, `SITE_*`                                      |
| Sync Coolify → options   | `MLHUB_SYNC_ENV_OPTIONS`, `MLHUB_SYSTEM_CRON_SECURE_KEY`, `MLHUB_CONTACT_*`      |
| Auth OAuth               | `MLHUB_AUTH_*` (Google login **bộ 1**)                                           |
| Captcha                  | `MLHUB_CAPTCHA_TYPE`, `MLHUB_CLOUDFLARE_TURNSTILE_`*, `MLHUB_GOOGLE_RECAPTCHA_*` |
| Google Business **bộ 2** | `GOOGLE_BUSINESS_CLIENT_`*, `MLHUB_GOOGLE_BUSINESS_STATUS`                       |
| Stripe                   | `STRIPE_*`                                                                       |
| Mail                     | `MAIL_*`                                                                         |


Chi tiết từng biến: `.env.example`, `ARCHITECTURE_PROMPT.md` §4.

---

# PHẦN III — PHỤ LỤC TRA CỨU NHANH

## A. Toàn bộ bảng `lb_*`

`lb_businesses`, `lb_locations`, `lb_campaigns`, `lb_qr_scans`, `lb_review_feedbacks`, `lb_booking_services`, `lb_bookings`, `lb_coupon_redemptions`, `lb_customers`, `lb_feedback_responses`, `lb_lead_submissions`, `lb_landing_pages`, `lb_marketing_templates`, `lb_template_packs`, `lb_template_pack_items`, `lb_template_usages`, `lb_template_imports`, `lb_template_ratings`, `lb_customer_activities`, `lb_customer_tags`, `lb_customer_tag_maps`, `lb_customer_notes`, `lb_customer_tasks`, `lb_customer_segments`, `lb_customer_score_logs`, `lb_crm_automations`, `lb_crm_automation_logs`, `lb_crm_automation_jobs`, `lb_customer_merge_logs`, `lb_email_templates`, `lb_email_automations`, `lb_email_automation_logs`, `lb_whatsapp_templates`, `lb_whatsapp_notifications`, `lb_whatsapp_notification_logs`, `lb_webhook_automations`, `lb_webhook_automation_logs`, `lb_loyalty_cards`, `lb_loyalty_customers`, `lb_loyalty_stamps`, `lb_loyalty_rewards`, `lb_referral_campaigns`, `lb_referral_links`, `lb_referrals`, `lb_referral_rewards`, `lb_google_business_connections`, `lb_google_business_locations`, `lb_google_reviews`, `lb_google_auto_reply_rules`, `lb_google_auto_reply_logs`, `lb_google_business_posts`, `lb_google_business_post_logs`

## B. Checklist seed dữ liệu mẫu 1 ngành (F&B ví dụ)

1. `LocalBusiness` type=Restaurant + `opening_hours`
2. 5× `QrCampaign` (review, booking, coupon, feedback, lead) + `settings` + slug
3. `BookingService` × 2
4. `LoyaltyCard` required_stamps=10
5. `MarketingTemplate` × 5 (`category=restaurant`, goals khác nhau)
6. `TemplatePack` slug=`food_beverage_free_starter`
7. `EmailAutomation` × 3 (lead.submitted, coupon.claimed, review.positive)
8. `Customer` × 10 mẫu + tags
9. Chạy portal → verify `PortalGrowthDashboardMetrics`

## C. Tài liệu liên quan


| File                         | Nội dung                                       |
| ---------------------------- | ---------------------------------------------- |
| `ARCHITECTURE_BACKEND.md`    | Kiến trúc Laravel, middleware, registry        |
| `ARCHITECTURE_MODULE.md`     | **Bản đồ 72 module: route/bảng/model/plan**    |
| `ARCHITECTURE_FEATURE.md`    | Độ sẵn sàng production, backlog                |
| `ARCHITECTURE_FRONTEND.md`   | Blade, Livewire, theme, Tailwind               |
| `ARCHITECTURE_CHECKLIST.md`  | Quy trình task, an toàn deploy                 |
| `ARCHITECTURE_PROMPT.md`     | Prompt mẫu (dùng chay), bootstrap `mlhub:install` |
| `ARCHITECTURE_CODEX_PROMPT.md` | Prompt Codex audit/diff review               |
| `.cursorrules`               | Quy tắc AI agent                               |


## D. Gap / chưa có trong code (khi làm SOP cần custom)


| Hạng mục SOP                     | Trạng thái                                          |
| -------------------------------- | --------------------------------------------------- |
| FizaHUB Tax Category field       | Chưa có — lưu metadata hoặc tích hợp sau            |
| `dashboard_*` preset entity      | Chưa có — dùng filter + sidebar visibility          |
| Onboarding wizard 3 phút         | Chưa có flow riêng — dùng `BusinessCreate` hiện tại |
| Captcha 5 form growth public     | Chưa gắn — backlog §14 `ARCHITECTURE_FEATURE.md`    |
| Throttle loyalty/referral public | Chưa có                                             |
| KPI "hộ hoạt động 7/30 ngày"     | Cần query custom / report job                       |
| 8×5=40 template pack seed        | Cần tạo qua seeder CustomMLHUB                      |


## Bổ sung gap vào Phụ lục D

Thêm các dòng sau vào bảng `Gap / chưa có trong code`:


| Hạng mục SOP                                     | Trạng thái                                                     |
| ------------------------------------------------ | -------------------------------------------------------------- |
| Grouped UI cho `lb_businesses.type`              | Chưa có — cần bổ sung ở form tạo/sửa business                  |
| `BusinessTypeCatalog` hoặc service metadata type | Chưa có — nên thêm để map type → group/dashboard/template/tags |
| Quick pick ngành phổ biến Đà Nẵng - Quảng Nam    | Chưa có                                                        |
| Search alias ngành tiếng Việt                    | Chưa có                                                        |
| Runtime recommendation theo business type        | Chưa có — dùng metadata trước, chưa cần DB                     |
| Taxonomy schema chính thức                       | Chưa làm ở giai đoạn 1 — chỉ cân nhắc sau khi có dữ liệu thật  |


---

## Kết luận bổ sung vào Phụ lục D

Ở giai đoạn hiện tại, không cần xây taxonomy database phức tạp.

Cách đúng là:

```text
Group ở UI
Type ở DB
Metadata ở service
Mapping cho API sau

```

Câu chốt kỹ thuật:

> Không đổi schema trước khi thị trường xác nhận. Giữ `lb_businesses.type` để tương thích code cũ, nhưng trình bày theo nhóm ngành trên UI và dùng metadata mapping để cá nhân hóa dashboard, template, campaign, automation và báo cáo.

Câu chốt sản phẩm:

> Chủ hộ chỉ cần chọn ngành dễ hiểu. MLHUB tự biết nên gợi ý QR, voucher, booking, review, automation và dashboard nào phù hợp.



---

*Tài liệu này là khung SOP + tham chiếu kỹ thuật. Khi thêm module/field mới, cập nhật §12–§22 và Phụ lục A trong cùng commit.*