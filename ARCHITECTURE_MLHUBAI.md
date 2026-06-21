# MLHUBAI - Báo cáo kiến trúc, từ khóa và ma trận nội dung

> Ngày quét: 2026-06-20  
> Cập nhật P0: 2026-06-21 - P0 đã được duyệt và đã triển khai.  
> **Đối chiếu code:** 2026-06-21 — rà soát lại trước khi giao Codex (xem §22–§24).
> Phạm vi: tính năng `MLHUB AI Assistant` tại `/portal/chatmlhubai`, Basic AI không dùng token OpenAI/Gemini, Advanced AI có thể dùng OpenAI/Gemini nếu bật và có API key.  
> Trạng thái: đã quét, lập ma trận nội dung, P0/P1 Knowledge Base + Phase B plan/credit snapshot đã triển khai trong code; test file có 31 case nhưng **chưa chạy được** trên môi trường local hiện tại.

> **Vai trò tài liệu:** file này là **nguồn sự thật kỹ thuật** cho Chat MLHUB AI (intent, context, template, route CTA). **Không** lặp catalog module → `ARCHITECTURE_MODULE.md`; **không** lặp backlog/độ sẵn sàng production → `ARCHITECTURE_FEATURE.md`; **không** lặp SOP ngành nghề đầy đủ → `ARCHITECTURE_SOP.md`; **không** lặp quy chuẩn copy UI → `ARCHITECTURE_I18N.md` §17.

---

## 1. Kết luận nhanh

Tính năng MLHUBAI hiện đã có khung khả dụng cho giai đoạn "trợ lý nội bộ không tốn token":

- Có route riêng: `/portal/chatmlhubai`, route name `portal.chatmlhubai`.
- Có widget compact trên dashboard thông qua `MLHUBAIDashboardPanel`.
- Có 2 chế độ:
  - `Basic AI`: nhận diện intent bằng từ khóa, đọc số liệu thật, ghép câu trả lời bằng template, không dùng credit/token.
  - `Advanced AI`: chỉ gọi OpenAI/Gemini khi user bật toggle, hệ thống có API key, và credit còn đủ.
- Có context builder đọc metrics thật: business, campaign, active campaign, visits, leads, bookings, coupon claims, feedback, conversion rate, khách mới, review, top campaigns, recent activity.
- Có CTA link theo intent: customers, QR campaigns, review booster, reports, businesses, AI Studio.

Tuy nhiên, để Basic AI trả lời "mượt" như trợ lý hàng ngày, vẫn còn khoảng trống runtime ngoài Phase C: CRM/Google/automation/loyalty chưa có snapshot sâu; `industry_recommendation` đã có template rule-based theo ngành/tình huống từ câu hỏi, nhưng chưa đọc sâu `lb_businesses.type`. Ma trận intent trong code hiện có **34 intent** trong `MLHUBAIKnowledgeBase::intentKeywords()` (xem §4.1, §12–§13).

Khuyến nghị: trước khi dùng token LLM, nên mở rộng Basic AI thành một "knowledge router" nội bộ: intent matrix + metric matrix + URL matrix + question bank + response segment bank. Các đề xuất code nằm ở cuối file và cần được duyệt trước khi làm.

---

## 2. Bản đồ code hiện tại

| Lớp | File | Vai trò hiện tại | Ghi chú đánh giá |
| --- | --- | --- | --- |
| Route | `modules/CustomMLHUB/Routes/web.php` | Đăng ký `/portal/chatmlhubai`, middleware `web`, `auth`, `verified`; route name `portal.chatmlhubai`. | Dùng route user portal, cần plan feature gate ở Livewire. |
| Config | `modules/CustomMLHUB/config/config.php` | Khai báo `route_prefix => portal/chatmlhubai`. | Ổn định, dễ đổi prefix nếu cần. |
| Provider | `modules/CustomMLHUB/Providers/CustomMLHUBServiceProvider.php` | Bind service singleton, load route/view, register credit action `mlhub_ai_chat`, thêm sidebar item. | Sidebar chỉ hiện khi user có feature `mlhub`. |
| Livewire full page | `modules/CustomMLHUB/Livewire/ChatMLHUBAI.php` | Trang chat full. | Mount assistant và render view `custommlhub::chat`. |
| Livewire compact | `modules/CustomMLHUB/Livewire/MLHUBAIDashboardPanel.php` | Panel chat compact trên dashboard. | Nếu không có feature `mlhub` thì ẩn panel. |
| Shared trait | `modules/CustomMLHUB/Livewire/Concerns/InteractsWithMLHUBAIAssistant.php` | Quản lý messages, question, suggested prompts, toggle Advanced AI, validate câu hỏi 2-500 ký tự. | Chưa lưu lịch sử chat dài hạn, mới tồn tại trong state Livewire. |
| Service | `modules/CustomMLHUB/Support/MLHUBAIAssistant/MLHUBAIAssistantService.php` | Orchestrator: build context, resolve intent, compose fallback, nếu advanced thì gọi OpenAI/Gemini và trừ credit. | Basic AI dùng trước, Advanced AI là lớp tăng cường. |
| Intent resolver | `modules/CustomMLHUB/Support/MLHUBAIAssistant/MLHUBAIIntentResolver.php` | So khớp keyword trên bản normalized; multi-intent, priority, dedupe, focus câu hỏi đời thường. | 34 intent trong knowledge base; vẫn `str_contains` — chưa fuzzy typo. |
| Context builder | `modules/CustomMLHUB/Support/MLHUBAIAssistant/MLHUBAIContextBuilder.php` | Gom snapshot số liệu user trong cache 60 giây. | Đã đọc growth core + top/recent activity + plan/credit snapshot; chưa CRM/Google sâu. |
| Response composer | `modules/CustomMLHUB/Support/MLHUBAIAssistant/MLHUBAIResponseComposer.php` | Ghép câu trả lời theo intent, greeting, CTA, footer metric/guidance, cap multi-intent. | 33 intent + `composeUnknown`; `credits`/`plan_limits` dùng số thật khi snapshot có dữ liệu. |
| UI | `modules/CustomMLHUB/Resources/views/partials/chat-shell.blade.php` | Chat shell, toggle Basic/Advanced, message bubbles, action buttons, suggested prompts. | UI đã nói rõ Basic AI không tốn credits; Advanced AI cần API key và credit. |

---

## 3. Luồng xử lý hiện tại

```mermaid
flowchart TD
    A["User hoi trong /portal/chatmlhubai"] --> B["Livewire validate question 2-500 ky tu"]
    B --> C["MLHUBAIAssistantService::ask"]
    C --> D["MLHUBAIContextBuilder build context cache 60s"]
    C --> E["MLHUBAIIntentResolver resolveAll"]
    D --> F["MLHUBAIResponseComposer composeMany"]
    E --> F
    F --> G["Tra loi Basic AI fallback + suggestions + CTA"]
    G --> H{"User bat Advanced AI?"}
    H -- "Khong" --> I["Hien thi Basic AI, khong tru credit"]
    H -- "Co" --> J{"AI status + API key + credit OK?"}
    J -- "Khong" --> I
    J -- "Co" --> K["Goi OpenAI/Gemini voi JSON context"]
    K --> L["Neu thanh cong: thay message, source ai, consume credit mlhub_ai_chat"]
```

Nguyên tắc tốt đã có:

- Basic AI luôn tạo câu trả lời trước, nên khi API lỗi vẫn có kết quả.
- Advanced AI được ràng buộc bởi `ai_chat_status`, provider credentials, và credit.
- System prompt Advanced AI yêu cầu chỉ dùng số liệu trong JSON context, không bịa metrics.
- Actions chỉ tạo nếu `Route::has($routeName)`.

Điểm cần cảnh giác:

- Model mặc định trong service đang là `gpt-5.4`. Cần đối chiếu với cấu hình thật trước khi bật Advanced AI production.
- Câu hỏi ngoài 34 intent hoặc keyword chưa phủ → `unknown` + gợi ý explore prompts; chưa có tri thức sản phẩm sâu cho automation/loyalty/public URL cụ thể.
- Fallback không log coverage/confidence ra storage, nên khó biết user đang hỏi nhóm nào bị thiếu (chỉ metadata trong response Livewire).

---

## 4. Hiện trạng Basic AI

### 4.1 Chỉ số hiện tại (đối chiếu code 2026-06-21)

| Chỉ số | Giá trị hiện tại | Nguồn |
| --- | ---: | --- |
| Intent trong `intentKeywords()` | 34 | `MLHUBAIKnowledgeBase::intentKeywords()` — gồm P0, P1, legacy (`greeting`, `new_customers`, …) và `industry_recommendation` |
| Intent có template `compose*()` | 33 + `composeUnknown` | `MLHUBAIResponseComposer` — `greeting` qua `composeGreeting()` |
| Cụm keyword (ước lượng) | ~280+ | Đếm trong block `intentKeywords()` |
| Starter prompts | 5 | `initialPrompts()` — tiếng Việt qua `__()` |
| Deepen prompts | 34 nhóm × 3 câu | `deepenPrompts()` |
| Explore prompts | 13 | `explorePrompts()` |
| CTA intent có `routeActions()` | 20+ nhóm | Runtime lọc bằng `Route::has()` trong `intentActions()` |
| Resolver | normalize + priority + dedupe | `MLHUBAIIntentResolver`: `normalize()`, `intentPriority()`, `focusEverydayQuestion()`, `removeDuplicateHelpCredit()`, `removeGenericNextSteps()`, `removeLooseDailyBriefing()`, `removeReviewDuplication()`; câu navigation ưu tiên 1 intent |
| Cache context | 60 giây | `MLHUBAIContextBuilder::CACHE_TTL_SECONDS` |
| Giới hạn input | 2–500 ký tự | Livewire validation |
| Unit tests | 41 cases | `tests/Unit/CustomMLHUB/MLHUBAIAssistantBasicAiTest.php` — **chưa chạy được** trên host (thiếu `php` PATH) |

> **Lưu ý:** §4.2 bên dưới là **ảnh chụp baseline trước P0/P1** (2026-06-20). Ma trận intent/keyword đầy đủ sau nâng cấp → §12.1, §13 và `MLHUBAIKnowledgeBase.php`.

### 4.2 Intent và keyword hiện tại (baseline trước P0/P1 — giữ để đối chiếu lịch sử)

| Intent | Keyword hiện tại | Câu hỏi gợi ý/follow-up hiện tại | Metrics/Context đang dùng | CTA hiện tại |
| --- | --- | --- | --- | --- |
| `greeting` | xin chào, xin chao, chào, chao, hello, helo, hallo, alo, hi bạn, hi ban | Không có follow-up riêng; dùng initial prompts. | `generated_at`, `user.short_name`. | Không có. |
| `new_customers` | khách mới, khach moi, customer mới, new customer, khách hàng mới, tuần này có khách, tuan nay co khach, có khách mới, co khach moi | "Where did the new customers come from?", "How does it compare to last week?", "How can I get more new customers?" | `customers.new_this_week`, `customers.new_last_week`, `customers.delta`, `weekly_signals.leads/bookings/positive_reviews`. | `portal.customers`. |
| `campaigns` | chiến dịch, chien dich, campaign, đang chạy, dang chay, tổng hợp chiến dịch, tong hop chien dich, running campaign | "Which campaign performs best?", "Which campaign needs improvement?", "How do I create a new campaign?" | `active_campaigns`, `campaign.type`, `visits`, `conversions`. | `portal.qr-campaigns`. |
| `reviews` | đánh giá, danh gia, review, sao, rating, phản hồi review, đánh giá tuần, danh gia tuan | "Which reviews need a reply?", "How can I get more 5-star reviews?", "What is my average rating?" | `reviews.count`, `reviews.average_rating`, `reviews.needs_reply`, `reviews.positive_count`. | `portal.review-booster`. |
| `next_steps` | làm gì, lam gi, gợi ý, goi y, next step, what should i do, chiến dịch mới, chien dich moi, đề xuất, de xuat, nên làm | "Suggest a weekend campaign", "What should I prioritize first?", "How can I grow revenue quickly?" | `onboarding`, `metrics.visits`, `metrics.review_clicks`. | Tùy onboarding: businesses, qr-campaigns, review-booster, ai-studio. |
| `overview` | tổng quan, tong quan, overview, báo cáo, bao cao, report, tình hình, tinh hinh, kết quả, ket qua | "Which metric is dropping?", "What stood out this week?", "What should I do next? / Suggest a new campaign." | `metrics.businesses`, `active_campaigns`, `visits`, `leads`, `bookings`, `coupon_claims`, `conversion_rate`. | `portal.reports`. |
| `visits` | lượt quét, luot quet, qr scan, lượt truy cập, luot truy cap, visits, traffic | "Where do the visits come from?", "What is my conversion rate?", "How can I get more QR scans?" | `metrics.visits`, `weekly_signals.qr_scans`, `weekly_signals.leads`, `weekly_signals.bookings`. | `portal.reports`. |
| `businesses` | cơ sở, co so, danh sách cơ sở, danh sach co so, doanh nghiệp, doanh nghiep, business, chi nhánh, chi nhanh, cửa hàng, cua hang, địa điểm, dia diem | "Which business performs best?", "How do I add a new business?", "Where do I update business info?" | `business_list.count`, `business_list.names`. | `portal.businesses`. |

### 4.3 Context metrics hiện có

| Context key | Nội dung | Nguồn | Độ sẵn sàng cho Basic AI |
| --- | --- | --- | --- |
| `metrics.businesses` | Số business của user | `LocalBusiness` | Sẵn sàng |
| `metrics.campaigns` | Tổng campaign | `QrCampaign` | Sẵn sàng |
| `metrics.active_campaigns` | Campaign đã publish | `QrCampaign.published_at` | Sẵn sàng |
| `metrics.visits` | Tổng QR scan | `QrScan` | Sẵn sàng, cần lọc bớt trong backlog riêng |
| `metrics.review_clicks` | Review feedback rating >= 4 | `ReviewFeedback` | Sẵn sàng |
| `metrics.leads` | Lead submissions | `LeadSubmission` | Sẵn sàng |
| `metrics.bookings` | Bookings | `Booking` | Sẵn sàng |
| `metrics.coupon_claims` | Coupon redemptions | `CouponRedemption` | Sẵn sàng |
| `metrics.feedback` | Feedback response + review rating <= 3 | `FeedbackResponse`, `ReviewFeedback` | Sẵn sàng |
| `metrics.conversion_rate` | Conversions / visits | Tổng hợp nội bộ | Sẵn sàng |
| `customers.*` | Khách mới tuần này, tuần trước, delta | `Customer` | Sẵn sàng |
| `weekly_signals.*` | leads, bookings, positive reviews, coupon claims, QR scans trong tuần | Nhiều model growth | Sẵn sàng |
| `reviews.*` | Count, average rating, needs reply, positive count trong tuần | `ReviewFeedback` | Sẵn sàng |
| `active_campaigns[]` | 6 campaign đã publish gần nhất, type, visits, conversions | `QrCampaign`, conversions tổng hợp | Sẵn sàng |
| `top_campaigns[]` | Top 5 campaign theo visits/conversions | `PortalGrowthDashboardMetrics` | Composer đã dùng trong `daily_briefing`, `top_campaigns` |
| `recent_activity[]` | 5 activity gần nhất | `PortalGrowthDashboardMetrics` | Composer đã dùng trong `daily_briefing` |
| `business_list` | Count và tối đa 12 tên business | `LocalBusiness` | Sẵn sàng |
| `onboarding` | create_business, create_campaign, publish_campaign, share_qr, boost_reviews | Derived từ metrics | Sẵn sàng |

---

## 5. Đánh giá "đã quét hết ngóc ngách chưa?"

### 5.1 Đã chạm đúng các ngóc ngách của riêng tính năng chat

| Khu vực | Kết quả |
| --- | --- |
| Route `/portal/chatmlhubai` | Đã có và dùng middleware auth/verified. |
| Sidebar portal | Đã đăng ký item `MLHUB AI Assistant`, chỉ hiện khi user có feature `mlhub`. |
| Dashboard panel | Đã có compact chat panel và link mở full chat. |
| Basic/Advanced toggle | Đã có UI và logic. Basic không dùng credit. Advanced cần API key và credit. |
| Context live data | Đã gom data thật từ dashboard/growth modules + plan/credit snapshot qua `MLHUBAIContextBuilder` | Chưa có CRM/Google/automation chi tiết |
| Intent matching | Đã có normalize, multi-intent, priority, dedupe, focus câu hỏi đời thường | Vẫn `str_contains` trên bản normalized — chưa fuzzy typo |
| Response templates | 33 intent + unknown; footer phân biệt metric vs guidance | `industry_recommendation` đã tách template theo ngành/tình huống Phase C |
| CTA action | 20+ intent; `Route::has()` — route module tắt thì không render | Tối đa 3 action/response; onboarding **không** gợi ý Studio mặc định |
| Credit | Advanced AI trừ `mlhub_ai_chat`; Basic không trừ | Cần API key + `ai_chat_status` — UI cảnh báo qua `advancedAiAvailable()` |
| Test riêng cho MLHUBAI | **Đã có** 31 Pest cases | `tests/Unit/CustomMLHUB/MLHUBAIAssistantBasicAiTest.php` — **chưa chạy được** trên host hiện tại |

### 5.2 Chưa bao phủ hết "ngóc ngách sản phẩm" (sau P0/P1 — cập nhật 2026-06-21)

P0/P1 đã có intent riêng cho: landing pages, booking, coupon, feedback, leads, CRM segments, Google Business/reviews, AI Studio handoff, billing, teams, support, credits/plan_limits, business locations, customers, marketing templates, industry keyword (`industry_recommendation`).

Nhóm **vẫn chưa** có intent/keyword/response riêng hoặc còn mỏng:

- Email / WhatsApp / Webhook automation (chỉ automation CRM ở mức hướng dẫn)
- Loyalty / referral campaigns
- File manager, media search, image editor
- Profile / security / language / 2FA
- Public URLs cụ thể (`/qr/{slug}`, `/lp/{slug}`, …) — cố ý không hard-code khi context thiếu slug
- Chi tiết theo từng business (`portal.businesses.show`)
- Snapshot sâu cho CRM/Google/automation/loyalty/files
- Gợi ý ngành theo `lb_businesses.type` / `BusinessTypeCatalog` sâu hơn (Phase C hiện nhận diện theo câu hỏi runtime)
- Coverage log cho `unknown` / fallback analytics
- Module AI dormant (`portal.ai-video`, …) — **đúng** là không gợi ý trong knowledge base

Nhận định: Basic AI đã vượt xa baseline 8 intent, nhưng chưa thay trợ lý vận hành đầy đủ cho automation/loyalty/public link/plan snapshot.

---

## 6. Ma trận URL tính năng liên quan

> **Cập nhật 2026-06-21:** Nhiều dòng bảng dưới là **ảnh chụp trước P0/P1**. Trạng thái intent/CTA thật sau nâng cấp → §12–§13, §22.6–§22.7, `MLHUBAIKnowledgeBase::routeActions()`.

| Nhóm | Route name | URL/prefix | Basic AI nên hiểu để trả lời | Trạng thái trong Basic AI hiện tại |
| --- | --- | --- | --- | --- |
| MLHUBAI | `portal.chatmlhubai` | `/portal/chatmlhubai` | Mở trợ lý, giải thích Basic/Advanced AI | **Đã có** intent `help_using_mlhubai` + CTA chat/credits/settings. |
| Dashboard | `portal.dashboard` | `/portal/dashboard` | Tổng quan ngày, onboarding, widgets | Metrics qua context; CTA `daily_briefing`, `onboarding`, `overview`. |
| Businesses | `portal.businesses` | `/portal/businesses` | Tạo/sửa business, danh sách cơ sở | **Đã có** intent `businesses`. |
| Business create | `portal.businesses.create` | `/portal/businesses/create` | Hướng dẫn tạo business đầu tiên | CTA qua `onboarding` / `next_steps`. |
| Business detail | `portal.businesses.show` | `/portal/businesses/{business}` | Hồ sơ, campaign, reviews, leads, reports của từng business | **Chưa có** intent theo business detail. |
| Locations | `portal.businesses.locations`, `portal.locations` | `/portal/businesses/{business}/locations`, `/portal/locations` | Chi nhánh/địa điểm/QR địa điểm | **Đã có** intent `business_locations` (hướng dẫn route). |
| Customers | `portal.customers` | `/portal/customers` | Danh sách khách, khách mới | **Đã có** `customers`, `new_customers`; chưa lifecycle/segment sâu. |
| CRM | `portal.crm.*` | `/portal/crm/...` | Segment, tag, task, automation, CRM report | **Đã có** intent `crm_segments` (hướng dẫn); chưa đọc CRM counts từ context. |
| QR Campaigns | `portal.qr-campaigns` | `/portal/qr-campaigns` | Tạo QR, xem analytics QR | **Đã có** `campaigns`, `qr_scans`, `top_campaigns`, `visits`. |
| QR Analytics | `portal.qr-campaigns.analytics` | `/portal/qr-campaigns/{slug}/analytics` | Campaign nào tốt/xấu | CTA qua `top_campaigns` / reports; chưa CTA analytics slug cụ thể. |
| Public QR | `qr-campaigns.public` | `/qr/{campaign}` | Link công khai khách quét | **Chưa có** intent public link (cố ý — thiếu slug trong context). |
| Landing Pages | `portal.landing-pages` | `/portal/landing-pages` | Tạo campaign page, form submit | **Đã có** intent `landing_pages`. |
| Public landing | `landing-pages.public` | `/lp/{slug}` | Link landing public | **Chưa có** intent public slug. |
| Review Booster | `portal.review-booster` | `/portal/review-booster` | Lấy review, review xấu, pending reply | **Đã có** `reviews`, `review_booster`. |
| Booking | `portal.booking-pages` | `/portal/booking-pages` | Lịch hẹn, dịch vụ, slot trống | **Đã có** intent `booking` (count tuần; chưa upcoming chi tiết). |
| Coupon | `portal.coupon-campaigns` | `/portal/coupon-campaigns` | Mã ưu đãi, claim, giới hạn | **Đã có** intent `coupon`. |
| Feedback | `portal.feedback-forms` | `/portal/feedback-forms` | Phản hồi riêng, điểm thấp, NPS | **Đã có** intent `feedback`. |
| Lead Forms | `portal.lead-forms` | `/portal/lead-forms` | Lead, form, follow-up | **Đã có** intent `leads`. |
| Reports | `portal.reports` | `/portal/reports` | Báo cáo, conversion, top campaign | **Đã có** `overview`, `conversion`, `daily_briefing` + `top_campaigns`/`recent_activity`. |
| Marketing Templates | `portal.marketing-templates` | `/portal/marketing-templates` | Mẫu nội dung, template | **Đã có** intent `marketing_templates`. |
| AI Studio | `portal.ai-studio` | `/portal/ai-studio` | Campaign Builder | **Đã có** intent `ai_studio` — handoff khi user hỏi tạo nội dung. |
| AI Review Reply | `portal.ai-studio.review-reply` | `/portal/ai-studio/review-reply` | Viết phản hồi review bằng AI | CTA qua `ai_studio`; thực thi ở Studio. |
| AI Content | `portal.ai-content` | `/portal/ai-studio/ai-content` | Viết caption/content | **Đã có** intent `ai_content_writer`. |
| AI Planner | `portal.ai-content-planner` | `/portal/ai-studio/planner` | Kế hoạch nội dung | Chưa intent riêng `ai_planner` — handoff qua `ai_studio` nếu hỏi chung. |
| AI Repurpose | `portal.ai-repurpose` | `/portal/ai-studio/repurpose` | Tái sử dụng nội dung | Chưa intent riêng — handoff Studio. |
| AI Image | `portal.ai-image` | `/portal/ai-studio/image` | Tạo ảnh AI | Chưa intent riêng — handoff Studio. |
| Prompt history | `portal.ai-studio.prompt-history` | `/portal/ai-studio/prompt-history` | Lịch sử prompt | CTA trong `ai_studio`. |
| AI settings | `portal.ai-studio.settings` | `/portal/ai-studio/settings` | Cấu hình AI workspace/user | CTA `help_using_mlhubai`, `credits`. |
| AI removed modules | `portal.ai-video`, `portal.ai-review`, `portal.ai-best-time`, `portal.ai-semantic-search` | Route files có trên đĩa nhưng providers không load route | Không nên gợi ý trong UI nếu provider đã remove surface | **Đúng** — knowledge base không gợi ý; `Route::has()` lọc CTA. |
| Google Business | `portal.google-business` | `/portal/integrations/google-business` | Google locations, reviews, posts, insights, auto reply | **Đã có** `google_business`, `google_reviews` (hướng dẫn; chưa connection snapshot). |
| Email Automation | `portal.email-*` | `/portal/email-automation/...` | Email automation/template/log | **Chưa có** intent riêng. |
| WhatsApp | `portal.whatsapp-*` | `/portal/whatsapp-notification/...` | WhatsApp automation/template/log | **Chưa có** intent riêng. |
| Webhook | `portal.webhook-*` | `/portal/webhook-automation/...` | Webhook automation/log | **Chưa có** intent riêng. |
| Loyalty | `portal.loyalty-cards` | `/portal/loyalty-cards` | Thẻ tích điểm, referral | **Chưa có** intent riêng. |
| Files | `portal.files.index` | `/portal/files` | Thư viện file, preview/download/edit image | **Chưa có** intent riêng. |
| Credits | `portal.credits` | `/portal/credits` | Credit còn lại, lịch sử dùng | **Đã có** intent `credits`; Phase B đọc số dư/cost nếu snapshot có dữ liệu. |
| Packages | `portal.packages` | `/portal/packages` | Nâng cấp gói | CTA qua `credits`, `billing`, `plan_limits`. |
| Billing | `portal.billing`, `portal.invoices` | `/portal/billing`, `/portal/invoices` | Hóa đơn, subscription | **Đã có** intent `billing`. |
| Teams | `portal.teams` | `/portal/teams` | Workspace, team members | **Đã có** intent `teams`. |
| Support | `portal.support.index` | `/portal/support` | Ticket hỗ trợ | **Đã có** intent `support`. |
| Profile | `portal.profile` | `/portal/profile` | Hồ sơ, mật khẩu | **Chưa có** intent riêng. |
| Affiliate | `portal.affiliate.index` | `/portal/affiliate` | Affiliate/referral revenue | **Chưa có** intent riêng. |
| Custom domains | `portal.brand.domains`, `portal.qr-codes.domains` | `/portal/brand/custom-domains`, `/portal/qr-codes/domains` | Tên miền riêng cho QR/campaign | **Chưa có** intent riêng. |

---

## 7. Ma trận intent để bổ sung

Bảng này là "content matrix" để Basic AI trả lời mượt hơn mà chưa cần OpenAI token. Nên tách ra file config/data riêng khi được duyệt.

| Intent đề xuất | Từ khóa nên thêm | Câu hỏi mẫu nên thêm | Metrics/dữ liệu cần đọc | CTA/URL liên quan | Ưu tiên |
| --- | --- | --- | --- | --- | --- |
| `help_using_mlhubai` | mlhub ai, trợ lý, assistant, basic ai, advanced ai, không tốn token, credit, hỏi gì được, cách dùng | "MLHUB AI hỏi được những gì?", "Basic AI có tốn credit không?", "Khi nào cần Advanced AI?" | `advancedAvailable`, credit summary nếu có | `portal.chatmlhubai`, `portal.credits`, `portal.ai-studio.settings` | P0 |
| `daily_briefing` | hôm nay, sáng nay, báo cáo ngày, daily, briefing, hôm qua, tuần này, cần chú ý | "Sáng nay tình hình thế nào?", "Hôm nay tôi nên xem chỉ số nào?", "Có gì bất thường không?" | metrics, weekly_signals, recent_activity, top_campaigns | `portal.dashboard`, `portal.reports` | P0 |
| `onboarding` | bắt đầu, setup, chưa có dữ liệu, tạo đầu tiên, cần làm gì trước, hướng dẫn | "Tôi mới tạo tài khoản thì làm gì trước?", "Tại sao dashboard chưa có số liệu?" | onboarding hints | businesses/create, qr-campaigns, review-booster | P0 |
| `business_locations` | chi nhánh, địa điểm, location, cơ sở con, mã QR địa điểm, cửa hàng nào | "Có bao nhiêu chi nhánh?", "Thêm địa điểm ở đâu?", "Mở QR của địa điểm thế nào?" | LocalBusiness + BusinessLocation count | `portal.businesses.locations`, `portal.locations` | P1 |
| `customers` | khách hàng, khách cũ, khách quay lại, danh bạ, data khách, customer list | "Có bao nhiêu khách?", "Khách mới đến từ đâu?", "Tôi nên chăm sóc nhóm khách nào?" | Customer count, new/returning, source, recent activity | `portal.customers`, `portal.crm.customers` | P0 |
| `customer_followup` | chăm sóc lại, follow up, nhắc khách, khách cũ, quay lại, gửi tin | "Nhắc khách quay lại như thế nào?", "Nên gửi ưu đãi cho nhóm nào?" | customers, CRM segments, email/whatsapp availability | CRM, email, whatsapp, coupon | P1 |
| `top_campaigns` | chiến dịch tốt nhất, top campaign, hiệu quả nhất, kém nhất, cần cải thiện | "Chiến dịch nào đang tốt nhất?", "Chiến dịch nào cần sửa?", "Tại sao conversion thấp?" | top_campaigns, active_campaigns, conversion_rate | `portal.qr-campaigns.analytics`, `portal.reports` | P0 |
| `qr_scans` | quét QR, scan, mã QR, lượt quét, in mã, đặt mã ở đâu | "Lượt quét tuần này thế nào?", "Làm sao tăng QR scan?", "Mã QR nằm ở đâu?" | visits, weekly qr_scans, campaign public URL | `portal.qr-campaigns`, public QR links | P0 |
| `landing_pages` | landing page, trang chiến dịch, trang công khai, public page, link campaign | "Tạo landing page ở đâu?", "Trang nào đang có form submit?", "Copy link public thế nào?" | landing page count/status/submissions if available | `portal.landing-pages`, `/lp/{slug}` | P1 |
| `review_booster` | review booster, xin đánh giá, đánh giá google, 5 sao, rating thấp, phản hồi riêng | "Làm sao tăng đánh giá 5 sao?", "Có review nào cần trả lời không?", "Review xấu xử lý thế nào?" | review stats, needs_reply, positive/private feedback | `portal.review-booster`, `portal.google-business?tab=reviews` | P0 |
| `google_reviews` | google review, đánh giá google, trả lời review, sync review, auto reply | "Đồng bộ Google review chưa?", "Review Google nào cần trả lời?", "Auto reply đang bật chưa?" | GoogleBusiness connection, reviews, rules | `portal.google-business?tab=reviews`, `?tab=auto_reply` | P1 |
| `booking` | đặt lịch, booking, lịch hẹn, slot, dịch vụ, khách đặt | "Hôm nay có lịch hẹn không?", "Tạo trang đặt lịch thế nào?", "Dịch vụ nào được đặt nhiều?" | bookings this week, service count, upcoming bookings | `portal.booking-pages` | P0 |
| `coupon` | coupon, mã giảm giá, voucher, ưu đãi, claim, khách nhận mã | "Có bao nhiêu khách nhận mã?", "Tạo voucher cuối tuần thế nào?", "Mã nào gần hết lượt?" | coupon_claims, campaign settings usage/expiry | `portal.coupon-campaigns` | P0 |
| `feedback` | feedback, phản hồi riêng, góp ý, khách không hài lòng, NPS, rating thấp | "Có phản hồi xấu nào không?", "Khách phàn nàn gì nhiều?", "Nên xử lý feedback thế nào?" | feedback count, low rating, recent feedback themes if available | `portal.feedback-forms`, reports | P0 |
| `leads` | lead, khách tiềm năng, form tư vấn, yêu cầu báo giá, số điện thoại mới | "Có lead mới không?", "Lead từ form nào?", "Nên follow up lead nào trước?" | leads this week, recent lead activity, campaign source | `portal.lead-forms`, `portal.businesses.leads` | P0 |
| `conversion` | chuyển đổi, conversion, tỉ lệ, hiệu suất, funnel, scan ra khách | "Tỉ lệ chuyển đổi bao nhiêu?", "Tại sao scan nhiều mà ít lead?", "Nên tối ưu bước nào?" | visits, conversions, conversion_rate, top_campaigns | `portal.reports` | P0 |
| `reports` | báo cáo, analytics, report, biểu đồ, chỉ số, dashboard | "Mở báo cáo ở đâu?", "Báo cáo tuần này có gì?", "Chỉ số nào giảm?" | metrics, top_campaigns, recent_activity | `portal.reports` | P0 |
| `ai_studio` | ai studio, campaign builder, tạo nội dung, viết caption, prompt, lịch sử prompt | "AI Studio làm được gì?", "Tạo nội dung chiến dịch ở đâu?", "Xem lại prompt cũ thế nào?" | credit summary, route availability, prompt history count if available | `portal.ai-studio`, `portal.ai-content`, `portal.ai-studio.prompt-history` | P1 |
| `ai_content_writer` | content writer, caption, bài viết, nội dung facebook, bài quảng cáo, CTA | "Viết caption cho ưu đãi cuối tuần", "Tạo CTA cho landing page", "Viết tin nhắn nhắc khách" | business profile, campaign type, brand voice if available | `portal.ai-content` | P1 |
| `ai_planner` | content planner, lịch nội dung, kế hoạch bài đăng, calendar | "Lập kế hoạch nội dung 7 ngày", "Tuần này đăng gì?" | campaign/business context, AI credit | `portal.ai-content-planner` | P2 |
| `ai_image` | ảnh AI, tạo ảnh, image, banner, poster, visual | "Tạo ảnh khuyến mãi ở đâu?", "AI image có tốn credit không?" | AI image jobs, credits | `portal.ai-image`, `portal.files.index` | P2 |
| `credits` | credit, token, số dư, hết credit, mua thêm, usage | "Tôi còn bao nhiêu credit?", "Basic AI có trừ credit không?", "Mua thêm credit ở đâu?" | credit_summary, plan permission cost | `portal.credits`, `portal.packages` | P0 |
| `billing` | thanh toán, hóa đơn, invoice, gói, nâng cấp, subscription | "Nâng cấp gói ở đâu?", "Xem hóa đơn ở đâu?", "Gói hiện tại giới hạn gì?" | plan usage, billing status, invoices | `portal.packages`, `portal.billing`, `portal.invoices` | P1 |
| `plan_limits` | giới hạn, limit, gói hiện tại, tối đa, quota, hết lượt | "Tôi còn tạo được bao nhiêu campaign?", "Vì sao không tạo được QR?", "Gói Free giới hạn gì?" | PlanLimitGuard usageSummary | dashboard planUsage, packages | P0 |
| `files` | file, media, ảnh, upload, thư viện, drive, dropbox, onedrive, edit image | "Upload ảnh ở đâu?", "Tìm ảnh online thế nào?", "Dung lượng còn bao nhiêu?" | storage usage, file count, plan limits | `portal.files.index`, `portal.files.search-online` | P2 |
| `crm_segments` | CRM, segment, phân nhóm, tag, task, ghi chú, automation CRM | "Phân nhóm khách thế nào?", "Tạo task chăm sóc khách ở đâu?", "CRM automation là gì?" | CRM counts, segments/tags/tasks/automation | `portal.crm.*` | P1 |
| `google_business` | Google Business, GBP, location google, bài đăng google, insight | "Kết nối Google Business ở đâu?", "Có sync review chưa?", "Đăng bài Google thế nào?" | connection count, location count, review sync, posts | `portal.google-business` | P1 |
| `email_automation` | email automation, email template, email log, gửi email tự động | "Tạo email chăm sóc lại ở đâu?", "Email có gửi lỗi không?" | automation count, templates, logs, SMTP status if available | `portal.email-automations`, `portal.email-logs` | P2 |
| `whatsapp_automation` | whatsapp, tin nhắn, cloud api, template whatsapp, log | "Gửi WhatsApp tự động thế nào?", "WhatsApp còn giới hạn gì?" | whatsapp limits/logs | `portal.whatsapp-notifications` | P2 |
| `webhook_automation` | webhook, zapier, automation ngoài, API, retry | "Kết nối Zapier/webhook ở đâu?", "Webhook lỗi xem ở đâu?" | webhook automations/logs | `portal.webhook-automations`, `portal.webhook-logs` | P2 |
| `loyalty_referral` | loyalty, tích điểm, thẻ thành viên, referral, giới thiệu bạn bè | "Tạo thẻ tích điểm ở đâu?", "Chiến dịch giới thiệu hoạt động thế nào?" | loyalty card/referral counts | `portal.loyalty-cards` | P2 |
| `custom_domains` | tên miền riêng, custom domain, domain QR, branded link | "Gắn tên miền riêng ở đâu?", "Domain đã verify chưa?" | custom domain status/count | `portal.brand.domains`, `portal.qr-codes.domains` | P2 |
| `teams` | team, workspace, thành viên, phân quyền, mời người | "Mời nhân viên vào workspace thế nào?", "Đổi workspace ở đâu?" | team members, workspace role | `portal.teams` | P1 |
| `support` | hỗ trợ, ticket, liên hệ, lỗi, cần giúp | "Gửi ticket hỗ trợ ở đâu?", "Tôi cần báo lỗi" | ticket count/status if available | `portal.support.index` | P1 |
| `profile_security` | hồ sơ, mật khẩu, đổi email, bảo mật, 2FA, ngôn ngữ | "Đổi mật khẩu ở đâu?", "Đổi ngôn ngữ thế nào?" | user profile, locale | `portal.profile`, language route | P2 |
| `unknown` | fallback | "Bạn có thể hỏi lại theo một trong các nhóm: khách, chiến dịch, review, booking..." | none | suggested prompts | P0 |

---

## 8. Bộ câu hỏi để MLHUBAI gợi ý

### 8.1 Starter prompts nên thay/bổ sung

**Hiện trạng code (2026-06-21):** `MLHUBAIKnowledgeBase::initialPrompts()` đã có **5 câu tiếng Việt** qua `__()`. Bảng dưới là gợi ý mở rộng thêm (chưa bắt buộc triển khai).

| Nhóm | Câu hỏi nên có |
| --- | --- |
| Tổng quan | "Sáng nay tình hình kinh doanh thế nào?" |
| Tổng quan | "Tóm tắt nhanh tuần này cho tôi." |
| Hành động | "Hôm nay tôi nên ưu tiên việc gì?" |
| Khách hàng | "Tuần này có khách mới không?" |
| Campaign | "Chiến dịch nào đang hiệu quả nhất?" |
| QR | "Lượt quét QR tuần này thế nào?" |
| Review | "Có đánh giá nào cần trả lời không?" |
| Booking | "Có lịch hẹn mới nào không?" |
| Lead | "Có lead mới nào cần chăm sóc không?" |
| Coupon | "Voucher/coupon nào đang được nhận nhiều?" |
| Feedback | "Có phản hồi xấu nào cần xử lý không?" |
| Plan/Credit | "Basic AI có tốn credit không?" |

### 8.2 Question bank theo ngữ cảnh

| Trạng thái dữ liệu | Câu hỏi nên gợi ý | Định hướng trả lời |
| --- | --- | --- |
| Chưa có business | "Tôi cần làm gì để bắt đầu?" | Thêm business trước, rồi tạo campaign/QR. |
| Có business, chưa có campaign | "Nên tạo chiến dịch đầu tiên loại nào?" | Review Booster nếu shop dịch vụ/F&B, lead form nếu cần tư vấn, coupon nếu cần quay lại. |
| Có campaign, chưa publish | "Làm sao đưa campaign ra public?" | Publish campaign/landing, copy link/QR. |
| Có publish, chưa có scan | "Tại sao chưa có lượt quét?" | Đặt QR ở quầy, bàn, hóa đơn, tin nhắn; kiểm tra public link. |
| Scan cao, conversion thấp | "Scan nhiều nhưng ít khách để lại thông tin, nên làm gì?" | Kiểm tra CTA, form ngắn hơn, ưu đãi rõ hơn, review/coupon/lead split. |
| Nhiều review tốt | "Làm sao tận dụng review tốt?" | Reply, dùng trong landing/social, Google Business post. |
| Nhiều feedback xấu | "Phản hồi xấu đang nói gì?" | Ưu tiên xử lý, dùng private feedback, tạo task CRM. |
| Nhiều lead | "Lead nào cần gọi trước?" | Ưu tiên lead gần nhất/từ campaign có ý định cao. |
| Hết credit | "Hết credit thì Basic AI còn dùng được không?" | Basic AI vẫn dùng được; Advanced AI/AI Studio cần credit. |
| Sắp vượt plan limit | "Tôi còn tạo được bao nhiêu campaign/QR?" | Đọc plan usage, gợi ý nâng cấp nếu gần hết. |

### 8.3 Câu hỏi bản ngày theo vai trò "trợ lý thông báo mỗi ngày"

| Thời điểm | Câu hỏi/chủ đề | Response segment nên ghép |
| --- | --- | --- |
| Sáng | "Chào buổi sáng, hôm nay tôi cần xem gì?" | Greeting + yesterday/this week snapshot + 1 rui ro + 1 next action. |
| Trước giờ mở cửa | "Có lịch hẹn hay lead nào cần chuẩn bị không?" | Booking upcoming + lead new + CTA booking/leads. |
| Giữa ngày | "Chiến dịch nào đang kéo khách tốt?" | Top campaign + conversion + gợi ý share QR. |
| Chiều | "Có feedback/review cần xử lý không?" | Needs reply + low rating/private feedback + CTA review/feedback. |
| Cuối ngày | "Tóm tắt ngày hôm nay." | Visits, leads, bookings, coupons, feedback, review count + next-day action. |
| Cuối tuần | "Cuối tuần nên chạy chiến dịch gì?" | Weekend coupon/review booster/booking reminder theo context. |

---

## 9. Bộ chỉ số nên thêm vào Basic AI

| Nhóm chỉ số | Field đề xuất | Cách dùng trong câu trả lời | Ưu tiên |
| --- | --- | --- | --- |
| Freshness | `context.generated_at`, `data_freshness_seconds` | Nói rõ "số liệu cập nhật lúc..." nếu cần. | P0 |
| Intent | `intent`, `matched_keywords`, `confidence`, `coverage_score` | Debug nội bộ, không cần hiện user. | P0 |
| Readiness | `context_readiness` theo module | Nếu thiếu bảng/module/permission, nói lý do. | P0 |
| Plan | `plan.name`, `plan.limits`, `plan.usage_percent` | Trả lời "còn bao nhiêu quota". | P0 |
| Credits | `credits.remaining`, `credits.used`, `credits.limit`, `credit_cost_mlhub_ai_chat` | Giải thích Basic vs Advanced. | P0 |
| Campaign trends | `top_campaigns`, `worst_campaigns`, `delta_visits`, `delta_conversions` | Nói "tăng/giảm so với tuần trước". | P1 |
| Conversion | `conversion_by_type`, `conversion_by_campaign` | Gợi ý tối ưu đúng điểm nghẽn. | P1 |
| Review | `needs_reply`, `low_rating_count`, `google_review_count`, `avg_rating_delta` | Ưu tiên xử lý review/feedback. | P1 |
| Booking | `upcoming_bookings`, `booking_by_service`, `no_show_or_cancelled` | Trợ lý vận hành ngày. | P1 |
| Leads | `new_leads`, `uncontacted_leads`, `lead_source_campaigns` | Gợi ý follow-up. | P1 |
| CRM | `open_tasks`, `segments_count`, `automations_active` | Trợ lý chăm sóc lại. | P2 |
| Google Business | `connected_locations`, `reviews_synced`, `posts_scheduled`, `auto_reply_enabled` | Giải thích local presence. | P2 |
| Automation | `email_logs_failed`, `whatsapp_logs_failed`, `webhook_logs_failed` | Cảnh báo vận hành. | P2 |

---

## 10. Công thức trả lời Basic AI để "mượt" mà không dùng token

Nên xem mỗi câu trả lời Basic AI là ghép 5 segment có điều kiện:

1. Greeting/time-aware segment  
   Ví dụ: "Chào buổi sáng anh/chị, em đã xem nhanh số liệu mới nhất."

2. Fact segment  
   Chỉ nói số liệu có trong context: "Tuần này có 12 lượt quét QR, 3 lead, 1 booking."

3. Interpretation segment  
   Dùng rule nội bộ: conversion rate thấp/cao, review rating tốt/xấu, campaign empty, onboarding missing.

4. Next action segment  
   Luôn đưa 1-3 việc có thể làm ngay: tạo campaign, share QR, reply review, gọi lead, tạo coupon.

5. CTA segment  
   Gắn action route: "Mở Reports", "Tạo chiến dịch", "Mở Review Booster".

### 10.1 Rule gợi ý nhanh

| Điều kiện | Câu trả lời nên ưu tiên |
| --- | --- |
| `businesses = 0` | Tạo business trước, vì campaign/QR cần home base. |
| `campaigns = 0` | Tạo Review Booster hoặc Lead Form đầu tiên. |
| `active_campaigns = 0 and campaigns > 0` | Publish campaign đang nháp. |
| `visits = 0 and active_campaigns > 0` | Chia sẻ/in QR, đặt tại quầy/hóa đơn/tin nhắn. |
| `visits > 0 and conversion_rate = 0` | CTA/form/ưu đãi chưa đủ mạnh; đề xuất coupon/lead form rõ hơn. |
| `reviews.needs_reply > 0` | Trả lời review trước để tăng uy tín. |
| `weekly_signals.leads > 0` | Gọi/chăm sóc lead trong ngày. |
| `weekly_signals.bookings > 0` | Kiểm tra lịch hẹn và nhắc khách. |
| `weekly_signals.coupon_claims > 0` | Theo dõi đổi mã và tạo follow-up quay lại. |
| `feedback > 0 or low_rating_count > 0` | Xử lý phản hồi riêng trước khi đẩy review công khai. |

### 10.2 Điểm tin cậy nội bộ nên tính

| Tên điểm | Công thức ý tưởng | Mục đích |
| --- | --- | --- |
| `keyword_score` | Tổng độ dài keyword match / độ dài câu hỏi, có trọng số theo keyword dài | Xếp hạng intent. |
| `route_confidence` | 1 nếu route tồn tại, 0 nếu không | Chỉ hiện CTA đúng. |
| `data_confidence` | 1 nếu context có metric cần thiết, 0.5 nếu thiếu một phần, 0 nếu không có | Chọn câu trả lời "dữ liệu thiếu" thay vì nói quá chắc. |
| `answer_confidence` | Trung bình có trọng số của keyword/data/route | Để fallback sang gợi ý hỏi lại. |
| `module_coverage` | Số module được context builder đọc / số module liên quan intent | Biết intent nào cần bổ sung context. |

---

## 11. Đề xuất nâng cấp code - chờ duyệt trước khi làm

Không thực hiện trong lần này. Đây là backlog để bạn chọn làm tiếp.

| Ưu tiên | Đề xuất | Lợi ích | Rủi ro/ghi chú |
| --- | --- | --- | --- |
| P0 | Tách `intentKeywords`, `questionBank`, `routeMatrix`, `responseSegments` ra class/config riêng | Dễ mở rộng không làm phình `MLHUBAIIntentResolver` và `ResponseComposer` | Cần test để tránh vỡ Basic AI. |
| P0 | Thêm unit test cho resolver/composer/service Basic AI | Bảo vệ keyword, multi-intent, fallback, CTA | Nên test trước khi sửa logic. |
| P0 | Normalize tiếng Việt không dấu: dùng bản normalized song song, xử lý "danh gia" và "đánh giá" như nhau | Tăng match khi user gõ không dấu | Cần tránh làm hỏng từ khóa có dấu hiện tại. |
| P0 | Thêm `matched_keywords`, `confidence` vào metadata; `fallback_reason` ở root response | Biết user hỏi gì mà hệ thống chưa trả lời được | **Chưa có** coverage log/persistence — chỉ trả về client Livewire state |
| P1 | Mở rộng `MLHUBAIContextBuilder` theo module: plan/credits, landing, booking, coupon, lead, feedback, CRM, Google | Basic AI trả lời đầy đủ sản phẩm | Cần đọc bảng có điều kiện `Schema::hasTable` và plan feature. |
| P1 | Thêm intent hierarchy: primary intent + secondary intents + topic entities | Câu hỏi "review và booking tuần này sao?" trả lời cả hai mượt hơn | Cần test multi-intent. |
| P1 | Dùng `top_campaigns` và `recent_activity` trong composer | Trả lời có ngữ cảnh hơn, giống báo cáo mỗi ngày | Context đã có, chỉ cần composer dùng. |
| P1 | Thêm Vietnamese-first prompts thay cho English starter nếu locale `vi` | UX hợp với MLHUB Việt Nam | Cần cập nhật translation. |
| P1 | Thêm "help cards" khi unknown: đưa 5 nhóm user có thể hỏi | Giảm cảm giác bị tắt | Đơn giản, ít rủi ro. |
| P2 | Admin/editor cho knowledge base Basic AI | Non-dev có thể thêm keyword/câu hỏi | Cần thiết kế UI và permission. |
| P2 | Lưu chat history hoặc prompt log Basic AI | Phân tích nhu cầu thật của user | Cần privacy/retention. |
| P2 | Scheduled daily briefing | Trợ lý tự động thông báo mỗi ngày | Cần automation/notification design. |

---

## 12. P0 đã triển khai (Xong - 2026-06-21)

| Hạng mục P0 | Trạng thái | File cập nhật | Ghi chú |
| --- | --- | --- | --- |
| Tách ma trận từ khóa/câu hỏi/route | Xong | `modules/CustomMLHUB/Support/MLHUBAIAssistant/MLHUBAIKnowledgeBase.php` | Thêm knowledge base riêng cho Basic AI: intent keywords, starter prompts, follow-up prompts, explore prompts, route actions. |
| Mở rộng intent P0 | Xong | `MLHUBAIKnowledgeBase.php`, `MLHUBAIIntentResolver.php` | Đã thêm `help_using_mlhubai`, `daily_briefing`, `onboarding`, `top_campaigns`, `qr_scans`, `review_booster`, `booking`, `coupon`, `feedback`, `leads`, `conversion`, `credits`, `plan_limits`. |
| Normalize tiếng Việt không dấu | Xong | `MLHUBAIIntentResolver.php` | Resolver chuyển câu hỏi và keyword về bản normalized, nên câu hỏi không dấu vẫn match được keyword có dấu/không dấu. |
| Trả về matched keywords/confidence | Xong | `MLHUBAIIntentResolver.php`, `MLHUBAIAssistantService.php` | Response có `metadata.confidence`, `metadata.matched_keywords`, `metadata.matches`, `metadata.intents`, `metadata.advanced_requested`. `fallback_reason` nằm ở **root response**, không nằm trong `metadata`. |
| Intent `industry_recommendation` + tinh chỉnh P1.3/Phase C | Xong (rule-based) | `MLHUBAIKnowledgeBase.php`, `MLHUBAIIntentResolver.php`, `MLHUBAIResponseComposer.php`, `MLHUBAIAssistantService.php` | Keyword ngành + focus/dedupe; Phase C truyền `request.question` scalar và trả template theo ngành/tình huống; **chưa** đọc sâu `lb_businesses.type`. |
| Starter prompts tiếng Việt | Xong | `MLHUBAIKnowledgeBase.php` | Đã thay bộ câu hỏi đầu bằng các câu hỏi Viet-first: báo cáo sáng nay, top campaign, Basic AI/credit, booking/coupon/lead, next action. |
| Composer dùng top campaigns/recent activity | Xong | `MLHUBAIResponseComposer.php` | `daily_briefing` và `top_campaigns` đã đọc `top_campaigns[]`; `daily_briefing` đã đọc `recent_activity[]`. |
| Thêm response segments cho P0 | Xong | `MLHUBAIResponseComposer.php` | Có đoạn trả lời riêng cho booking, coupon, feedback, leads, conversion, credits, plan limits, QR scan, Review Booster. |
| CTA route liên quan P0 | Xong | `MLHUBAIKnowledgeBase.php`, `MLHUBAIResponseComposer.php` | Đã mở rộng CTA tới dashboard, reports, chatmlhubai, ai settings, booking pages, coupon campaigns, feedback forms, lead forms, credits, packages. |
| Unit test bảo vệ Basic AI | Đã ghi nhận **65 cases** (49 functions, gồm dataset 18 nhóm ngành) — **chưa chạy được** | `tests/Unit/CustomMLHUB/MLHUBAIAssistantBasicAiTest.php` | Bao gồm P0, P1, P1.3, Phase B plan/credit, **Phase C.1 — 18 nhóm BusinessTypeCatalog**, footer, onboarding/CRM, CTA Việt. Host Windows thiếu `php` trên PATH (2026-06-21). |

### 12.1 Ma trận P0 sau nâng cấp

| Intent | Từ khóa chính đã có | Dữ liệu Basic AI đang dùng | URL/CTA liên quan |
| --- | --- | --- | --- |
| `help_using_mlhubai` | mlhub ai, tro ly, assistant, basic ai, advanced ai, token, credit, cach dung | Rule nội bộ Basic/Advanced AI | `portal.chatmlhubai`, `portal.ai-studio.settings` |
| `daily_briefing` | hom nay, sang nay, bao cao ngay, daily, briefing, can chu y | `metrics`, `top_campaigns.0`, `recent_activity.0` | `portal.dashboard`, `portal.reports` |
| `onboarding` | bat dau, setup, chua co du lieu, tao dau tien, can lam gi truoc | `onboarding`, `metrics` | businesses, qr-campaigns, review-booster, ai-studio tuy hint |
| `top_campaigns` | chien dich tot nhat, top campaign, hieu qua nhat, kem nhat, dang tot | `top_campaigns[]` | `portal.reports`, `portal.qr-campaigns` |
| `qr_scans` | quet qr, scan qr, ma qr, luot quet, in ma, dat ma | `metrics.visits`, `weekly_signals.qr_scans/leads/bookings` | `portal.reports`, `portal.qr-campaigns` |
| `review_booster` | review booster, xin danh gia, danh gia google, 5 sao, rating thap | `reviews.*` | `portal.review-booster` |
| `booking` | dat lich, booking, lich hen, slot, dich vu, khach dat | `metrics.bookings`, `weekly_signals.bookings` | `portal.booking-pages` |
| `coupon` | coupon, ma giam gia, voucher, uu dai, claim | `metrics.coupon_claims`, `weekly_signals.coupon_claims` | `portal.coupon-campaigns` |
| `feedback` | feedback, phan hoi, gop y, khach khong hai long, nps, rating thap | `metrics.feedback`, `reviews.needs_reply` | `portal.feedback-forms` |
| `leads` | lead, khach tiem nang, form tu van, yeu cau bao gia, so dien thoai moi | `metrics.leads`, `weekly_signals.leads` | `portal.lead-forms`, `portal.customers` |
| `conversion` | chuyen doi, conversion, ti le, hieu suat, funnel | `metrics.conversion_rate`, `visits`, `leads`, `bookings`, `coupon_claims`, `feedback` | `portal.reports` |
| `credits` | credit, token, so du, het credit, mua them, usage | Phase B dùng `credits.*` snapshot thật nếu có; thiếu snapshot thì trả lời thận trọng và nhắc Basic AI không trừ credit | `portal.credits` |
| `plan_limits` | gioi han, limit, goi hien tai, quota, het luot, nang goi | Phase B dùng `plan.*` snapshot thật nếu có; thiếu snapshot thì không bịa quota | `portal.packages` |

### 12.2 Trạng thái xác minh

| Kiểm tra | Kết quả | Ghi chú |
| --- | --- | --- |
| Pest `MLHUBAIAssistantBasicAiTest` | **Chưa chạy được** | Host báo `php` không có trên PATH (2026-06-21). File test tồn tại 41 cases — cần chạy trên PHP 8.3+ hoặc container app đầy đủ. |
| PHP lint (các file assistant) | Chưa chạy lại trong lần audit này | Lần trước ghi nhận pass qua Docker — không lặp lại vì môi trường local thiếu PHP. |

### 12.3 Việc còn lại sau P0

| Ưu tiên tiếp | Đề xuất | Lý do |
| --- | --- | --- |
| P1 | Bổ sung chi tiết gần nhất cho booking/coupon/lead/feedback | Plan/credit snapshot Phase B đã có; bước còn lại là trả lời "ai", "lúc nào", "campaign nào" sâu hơn. |
| P1 | Lưu coverage log cho unknown/fallback | Để biết user hỏi nhóm nào nhiều và bổ sung keyword đúng nhu cầu thật. |
| P1 | Chạy lại Pest trên môi trường PHP đầy đủ | Khóa chắt P0 bằng test tự động trước khi mở tiếp. |

## 13. P1 Knowledge Base đã triển khai (2026-06-21)

Phạm vi lần này: chỉ mở rộng Basic AI Knowledge Base và response templates trong `modules/CustomMLHUB/Support/MLHUBAIAssistant/*`, đối chiếu route portal/public với `ARCHITECTURE_MODULE.md` và route files thực tế. Không thêm admin-only route, không hard-code dynamic public URL khi context chưa có slug, không gọi OpenAI/Gemini, không tạo migration, không đổi schema/provider/bootstrap.

| Intent P1 | Trạng thái | Route action đã thêm | Ghi chú |
| --- | --- | --- | --- |
| `business_locations` | Xong | `portal.locations`, `portal.businesses` | Không dùng route có tham số như `portal.businesses.locations` vì cần business id. |
| `customers` | Xong | `portal.customers`, `portal.crm.customers` | `portal.crm.customers` sẽ tự bị bỏ qua nếu module CRM không load route. |
| `landing_pages` | Xong | `portal.landing-pages` | Không tự tạo public link landing khi chưa có slug trong context. |
| `marketing_templates` | Xong | `portal.marketing-templates` | Chỉ portal route. |
| `crm_segments` | Xong | `portal.crm.segments`, `portal.crm.customers` | Chỉ portal CRM route, không admin route. |
| `google_business` | Xong | `portal.google-business` | Chỉ hướng dẫn kiểm tra OAuth/location/insights trong portal. |
| `google_reviews` | Xong | `portal.google-business` | Không thêm query tab hard-code; response giải thích review/auto reply. |
| `ai_studio` | Xong | `portal.ai-studio`, `portal.ai-studio.prompt-history`, `portal.ai-studio.settings` | Basic AI vẫn không tốn credit; AI Studio task có thể tốn credit. |
| `ai_content_writer` | Xong | `portal.ai-content`, `portal.ai-studio` | `portal.ai-content` đã được grep trong route file. |
| `billing` | Xong | `portal.billing`, `portal.invoices`, `portal.packages` | Dùng portal billing/invoices/packages, không admin billing. |
| `teams` | Xong | `portal.teams` | Không dùng route join/stream/switch trong action. |
| `support` | Xong | `portal.support.index` | Không dùng support show vì cần ticket param. |

### 13.1 Test đã ghi nhận trong repo (41 cases — chưa chạy được runtime)

| Nhóm | Test case | Mục đích |
| --- | --- | --- |
| P0 resolver | `resolver recognizes P0 Vietnamese and unaccented daily operations intents` | Multi-intent booking/coupon/daily_briefing |
| P0 prompts | `resolver exposes P0 starter prompts in Vietnamese` | Starter tiếng Việt |
| P0 composer | `composer uses top campaigns and recent activity for daily briefing` | `top_campaigns[]`, `recent_activity[]` |
| P0 service | `basic assistant response includes intent metadata without using advanced AI` | `metadata.*`, intent `credits` |
| P1 resolver | `resolver recognizes P1 portal product intents` | 12 intent P1 |
| P1 routes | `P1 route actions stay portal scoped and avoid dynamic public URLs` | Không `admin`, không `{param}` |
| P1 composer | `composer answers P1 intents with rule based guidance` | landing/google/billing/support |
| Onboarding | `onboarding intent wins for new account first step questions` | Ưu tiên `onboarding` |
| CRM | `crm guidance is not contaminated by next steps or ai studio cta` | Không lẫn Studio |
| Footer | `composer adds knowledge footer for guidance-only responses` | Footer guidance |
| Footer | `composer adds account metrics footer for metric responses` | Footer metrics |
| Footer | `basic ai responses render exactly one composer footer` | Không trùng footer |
| UI | `chat shell does not render the old fixed basic ai footer` | Blade không footer cũ |
| i18n CTA | `P1 cta labels are Vietnamese` | Nhãn CTA Việt hóa |
| Copy | `onboarding billing and support copy avoid mixed English` | Không lẫn EN |
| Multi-intent | `composer summarizes long multi-intent questions without rendering every intent` | Tóm tắt >4 intent |
| P1.3 aliases | `resolver recognizes everyday P1.3 aliases without falling back` | 10 cặp câu/intent |
| P1.3 answers | `P1.3 everyday answers are focused and practical` | Nội dung thực dụng |
| Navigation | `navigation questions stay focused on one main intent` | 1 intent cho câu “ở đâu” |
| CRM/coupon | `returning customer question focuses CRM and offer guidance` | `crm_segments` + `coupon` |
| Contamination | `P1.3 focused answers avoid intent contamination` | QR/lead không lẫn review/billing |
| Google | `google review answer does not duplicate the review metrics sentence` | Không trùng câu review |
| Industry | `industry recommendation wins over generic onboarding for cafe questions` | Ưu tiên industry |
| Industry | `industry recommendation recognizes common local business types` | 8 loại hình |
| Phase C industry/scenario | 8 test mới cho spa, nhà hàng hải sản, bán lẻ mỹ phẩm, QR nhiều scan ít lead, review 2 sao, nhiều chi nhánh, không tốn credit, tạo nội dung Facebook | Bảo vệ template theo ngành/tình huống và ranh giới Chat → Studio |
| Credits | `credit questions merge help and credits into one focused answer` | Chỉ `credits` |
| QR | `qr guidance does not pull review or rating text` | 2 câu QR |
| Handoff | `onboarding actions avoid ai studio unless user asks ai explicitly` | Onboarding không CTA Studio |

### 13.2 Route đối chiếu

Tất cả route action P1 đã thêm đều được grep từ route files hiện tại. Runtime vẫn được bảo vệ bởi `Route::has()` trong `MLHUBAIResponseComposer::intentActions`, nên nếu module nào không load provider thì action sẽ không render thay vì lỗi.

| Route name | Nguồn grep |
| --- | --- |
| `portal.locations` | `modules/AppBusinessLocations/Routes/web.php` |
| `portal.customers` | `modules/AppCustomers/Routes/web.php` |
| `portal.crm.customers`, `portal.crm.segments` | `modules/AppAdvancedCustomerCrm/Routes/web.php` |
| `portal.landing-pages` | `modules/AppLandingPages/Routes/web.php` |
| `portal.marketing-templates` | `modules/AppMarketingTemplates/Routes/web.php` |
| `portal.google-business` | `modules/AppGoogleBusiness/Routes/web.php` |
| `portal.ai-studio`, `portal.ai-studio.prompt-history`, `portal.ai-studio.settings` | `modules/AppAIStudio/Routes/web.php` |
| `portal.ai-content` | `modules/AppAIContent/Routes/web.php` |
| `portal.billing`, `portal.invoices` | `modules/AppBilling/Routes/web.php` |
| `portal.packages` | `modules/AppPayments/Routes/web.php` |
| `portal.teams` | `modules/AppTeams/Routes/web.php` |
| `portal.support.index` | `modules/AppSupport/Routes/web.php` |

### 13.3 Chưa thay đổi

| File | Trạng thái |
| --- | --- |
| `ARCHITECTURE_MODULE.md` | Chỉ đọc đối chiếu route/module, không sửa. |
| `ARCHITECTURE_SOP.md` | Chỉ đọc đối chiếu SOP/CRM route, không sửa. |
| `MLHUBAIContextBuilder.php` | Trong P1 Knowledge Base thì chưa sửa ContextBuilder; sau Phase B, ContextBuilder đã được cập nhật plan/credit snapshot. |

## 14. Việc nên làm ngay sau khi duyệt

> **Cập nhật 2026-06-21:** Kế hoạch chi tiết theo phase → **§23**; prompt giao Codex → **§24**; đối chiếu code → **§22**.

1. P0/P1 Knowledge Base + Phase B plan/credit snapshot + Phase C industry/scenario refinement **đã triển khai** trong code; test file **41 cases** — **chưa chạy được** trên host hiện tại (thiếu `php` PATH).
2. **Phase A (doc sync):** hoàn tất trong lần audit này — không code.
3. **Phase B:** plan/credit snapshot trong context đã triển khai; cần chạy Pest trên môi trường PHP đầy đủ.
4. **Phase C:** đã triển khai industry/scenario theo câu hỏi runtime; vẫn cần chạy Pest + smoke manual trên môi trường PHP.
5. **Phase F (Advanced AI):** sau khi Basic AI ổn — không ưu tiên ngay.

---

## 15. Nguồn đã quét

| File/nhóm file | Lý do quét |
| --- | --- |
| `modules/CustomMLHUB/Routes/web.php` | Xác nhận route `/portal/chatmlhubai`. |
| `modules/CustomMLHUB/Providers/CustomMLHUBServiceProvider.php` | Xác nhận service binding, sidebar, credit action. |
| `modules/CustomMLHUB/Livewire/*` | Xác nhận UI full/compact và plan gate. |
| `modules/CustomMLHUB/Livewire/Concerns/InteractsWithMLHUBAIAssistant.php` | Xác nhận message flow, validation, suggested prompts. |
| `modules/CustomMLHUB/Support/MLHUBAIAssistant/*` | Xác nhận Basic/Advanced AI, intent, context, response composer. |
| `modules/CustomMLHUB/Resources/views/partials/chat-shell.blade.php` | Xác nhận UI Basic/Advanced, CTA, empty state. |
| `app/Support/Portal/PortalGrowthDashboardMetrics.php` | Xác nhận metrics dashboard đang dùng. |
| `database/seeders/PlanSeeder.php` | Xác nhận `credit_cost_mlhub_ai_chat` và permission `mlhub`. |
| `modules/*/Routes/web.php` của các module portal | Lập ma trận URL tính năng liên quan. |
| `modules/*/Providers/*ServiceProvider.php` liên quan | Xác nhận sidebar, plan key, route surface thật sự được load. |
| `ARCHITECTURE_FEATURE.md`, `ARCHITECTURE_MODULE.md` | Đối chiếu tài liệu hiện có; không thay thế quét code hiện tại. |

---

## 16. Định vị sản phẩm: Chat MLHUB AI vs Studio AI

| Khía cạnh | Chat MLHUB AI (`portal.chatmlhubai`) | Studio AI (`portal.ai-studio/*`) |
| --- | --- | --- |
| Vai trò | Trợ lý vận hành — đọc số liệu thật, trả lời nhanh, gợi ý việc làm, mở đúng màn hình portal | Công cụ sáng tạo — viết/soạn/tái sử dụng nội dung, lập lịch, tạo ảnh, trả lời review bằng LLM |
| Đầu vào | Câu hỏi tự nhiên 2–500 ký tự trong chat widget/full page | Form task cụ thể (caption, planner, repurpose, image, review reply…) |
| Đầu ra | Câu trả lời ngắn + CTA route + suggested prompts | Nội dung draft có thể copy/lưu; có prompt history |
| Dữ liệu nền | `MLHUBAIContextBuilder` — metrics dashboard/growth | Business/campaign context theo từng module AI Studio |
| Credit mặc định | **Basic AI:** không trừ credit. **Advanced AI:** trừ `mlhub_ai_chat` khi gọi OpenAI/Gemini thành công | Mỗi task trừ action key riêng (`ai_studio_*`) — xem `ARCHITECTURE_SOP.md` §19 |
| Permission | Feature `mlhub` (sidebar + panel) | Feature `ai_studio`, `ai_studio_caption_generator`, … |

**Câu chốt nội bộ:** Chat MLHUB AI trả lời *“hôm nay thế nào, nên làm gì, mở đâu”*; Studio AI thực hiện *“viết/soạn/tạo giúp tôi”*.

---

## 17. Ranh giới nhiệm vụ giữa Chat MLHUB AI và Studio AI

### 17.1 Chat MLHUB AI — nên làm

- Giải thích metrics: visits, leads, bookings, review, conversion, top campaign, recent activity.
- Gợi ý bước tiếp theo theo onboarding (`onboarding`, `next_steps`, `daily_briefing`).
- Trả lời “mở ở đâu / route nào” qua CTA (`MLHUBAIKnowledgeBase::routeActions()`).
- Gợi ý **loại** campaign/tool phù hợp ngành (intent `industry_recommendation`) — Phase C đã tách template rule-based theo cafe/nhà hàng/spa/bán lẻ/lưu trú/dịch vụ/giáo dục/sức khỏe/B2B; không viết full bài quảng cáo trong chat.

### 17.2 Chat MLHUB AI — không nên làm

- Viết caption dài, kế hoạch nội dung 7 ngày, poster AI, video script — chuyển sang Studio.
- Bịa số liệu ngoài JSON context (Advanced AI system prompt cũng cấm).
- Thay thế CRM/automation setup chi tiết — chỉ hướng dẫn route + rule ngắn.

### 17.3 Studio AI — nên làm

- Tạo/chỉnh nội dung marketing cụ thể (caption, repurpose, calendar, image, review reply).
- Task có input form, preview, lưu prompt history (`portal.ai-studio.prompt-history`).

### 17.4 Quy tắc phân luồng nhanh

| User hỏi | Xử lý |
| --- | --- |
| “Tuần này có bao nhiêu lead?” | Chat Basic AI — intent `leads` |
| “Viết caption khuyến mãi cuối tuần” | Chat CTA → `portal.ai-content`; thực thi ở Studio |
| “Trả lời review này giúp tôi” | Studio `portal.ai-studio.review-reply` |
| “MLHUB AI hỏi được gì?” | Chat — intent `help_using_mlhubai` |
| “Còn bao nhiêu credit?” | Chat — intent `credits`; Phase B đọc số dư/cost thật nếu snapshot có dữ liệu |

**Đã có trong code (2026-06-21):** `onboarding` / `next_steps` **không** gợi ý CTA `portal.ai-studio` mặc định — chỉ khi user hỏi rõ AI (`ai_studio`, `ai_content_writer`, `help_using_mlhubai`).

---

## 18. Basic AI / Advanced AI / Credit Rules

| Chế độ | Khi nào chạy | OpenAI/Gemini | Credit |
| --- | --- | --- | --- |
| **Basic AI** | Mặc định; toggle `useAdvancedAi = false` | Không gọi | Không trừ |
| **Advanced AI** | User bật toggle **và** `ai_chat_status = 1` **và** có API key provider **và** `credit_service()->ensureCanConsume(..., 'mlhub_ai_chat')` | Gọi qua `MLHUBAIAssistantService::requestAssistantReply()` | Trừ `mlhub_ai_chat` sau khi thành công; plan cost key `credit_cost_mlhub_ai_chat` (mặc định **1** trong `PlanSeeder`) |

**Luồng an toàn đã có trong code:**

1. Luôn compose Basic AI trước (`source = fallback`).
2. Nếu Advanced fail (API/key/credit/disabled) → vẫn hiển thị Basic AI + `fallback_reason` (root response).
3. UI: `advancedAiAvailable()` kiểm tra `ai_chat_status` + API key — nếu thiếu, chat-shell hiển thị cảnh báo và vẫn dùng Basic AI.
4. Studio AI **độc lập** — mỗi module gọi credit action riêng; không dùng chung toggle Advanced của chat.

**Không nhầm lẫn:**

- Basic AI chat ≠ miễn phí mọi AI trên portal.
- Advanced AI chat ≠ Studio AI — cùng có thể tốn credit nhưng khác feature key và UI entry.

---

## 19. Luồng điều hướng Chat → Studio

```mermaid
flowchart LR
    A["User hỏi trong Chat MLHUB AI"] --> B{"Intent nghiệp vụ?"}
    B -- "metrics / CTA portal" --> C["Basic AI trả lời + action route"]
    B -- "ai_studio / ai_content_writer" --> D["Chat giải thích + CTA Studio"]
    D --> E["User mở portal.ai-studio hoặc portal.ai-content"]
    E --> F["Studio task — consume ai_studio_* credit"]
    B -- "cần diễn giải tự do + bật Advanced" --> G["Advanced AI chat — mlhub_ai_chat"]
```

**CTA Studio hiện có trong knowledge base (P1):**

| Intent | Route action gợi ý |
| --- | --- |
| `ai_studio` | `portal.ai-studio`, `portal.ai-studio.prompt-history`, `portal.ai-studio.settings` |
| `ai_content_writer` | `portal.ai-content`, `portal.ai-studio` |
| `help_using_mlhubai` | `portal.chatmlhubai`, `portal.ai-studio.settings` |
| `credits` | `portal.credits`, `portal.ai-studio.settings` |

Copy nút CTA user-facing → `ARCHITECTURE_I18N.md` §17.3.

**Runtime:** mọi CTA qua `MLHUBAIResponseComposer::intentActions()` — chỉ render nếu `Route::has($routeName)`. Module/provider tắt → action biến mất, **không** gợi ý route AI dormant (`portal.ai-video`, …).

---

## 20. Roadmap hoàn thiện MLHUB AI

Roadmap **kỹ thuật assistant** (ngắn). Backlog production/QA/module → `ARCHITECTURE_FEATURE.md`.

| Giai đoạn | Hạng mục | Tham chiếu |
| --- | --- | --- |
| **Đã xong (P0/P1 + P1.3 tinh chỉnh + Phase B)** | 34 intent, resolver dedupe/priority, composer footer, CTA Việt hóa, industry/onboarding focus, plan/credit snapshot, 31 unit tests (file) | §12–§13, §22–§23, `MLHUBAIAssistantBasicAiTest.php` |
| **P1 còn lại (runtime data)** | Chi tiết booking/coupon/lead/feedback gần nhất; fallback coverage log nếu được duyệt riêng | §12.3, §13.3 |
| **P2** | Admin editor knowledge base; chat history; scheduled daily briefing notification | §11 |
| **Song song SOP** | Runtime gợi ý theo `lb_businesses.type` / nhóm ngành — ma trận scenario | `ARCHITECTURE_SOP.md` §E |
| **Song song i18n** | Nhãn Basic/Advanced, mô tả Chat vs Studio trên UI | `ARCHITECTURE_I18N.md` §17 |

**Không mở rộng thêm backlog dài trong file này** — cập nhật trạng thái production tại `ARCHITECTURE_FEATURE.md` khi QA xong từng hạng mục.

---

## 21. Tham chiếu ngành nghề và scenario

Chat MLHUB AI **không** lưu taxonomy ngành nghề đầy đủ. Nguồn sự thật:

| Nội dung | File | Mục |
| --- | --- | --- |
| Nhóm ngành SOP + tính năng ưu tiên theo vùng | `ARCHITECTURE_SOP.md` | §5, §2.6–§2.10 |
| KPI SOP → metric code | `ARCHITECTURE_SOP.md` | §7 |
| Ma trận scenario Chat vs Studio theo ngành | `ARCHITECTURE_SOP.md` | §E |
| Catalog type/onboarding UI | `ARCHITECTURE_MODULE.md` | `AppBusinessProfiles`, `BusinessTypeCatalog` |

**Trong code assistant hiện tại:**

- Intent `industry_recommendation` — **18 nhóm** `BusinessTypeCatalog` (alias + template + CTA tối đa 3); nhận diện từ câu hỏi runtime — **chưa** đọc `lb_businesses.type` động.
- CTA gợi ý: context-aware theo ngành/tình huống, vẫn lọc qua `Route::has()`.

Khi triển khai gợi ý theo type thật: đọc metadata từ `BusinessTypeCatalog` + map sang intent/CTA — **không** nhân bản ma trận ngành dài trong file này.

---

## 22. Trạng thái đối chiếu code hiện tại

**Ngày audit:** 2026-06-21
**Phạm vi:** Chat MLHUB AI (`modules/CustomMLHUB/Support/MLHUBAIAssistant/*`, Livewire trait, `chat-shell.blade.php`, unit test). **Không** sửa code trong lần audit này.

### 22.1 File đã kiểm tra

| File | Vai trò |
| --- | --- |
| `MLHUBAIAssistantService.php` | Orchestrator Basic/Advanced, credit `mlhub_ai_chat`, metadata |
| `MLHUBAIContextBuilder.php` | Snapshot metrics 60s |
| `MLHUBAIIntentResolver.php` | Normalize, multi-intent, priority, dedupe |
| `MLHUBAIKnowledgeBase.php` | Intent keywords, prompts, route actions, priority map |
| `MLHUBAIResponseComposer.php` | Template theo intent, CTA, footer, multi-intent cap |
| `InteractsWithMLHUBAIAssistant.php` | Livewire flow, plan gate `mlhub` |
| `chat-shell.blade.php` | Toggle Basic/Advanced, banner, empty state |
| `MLHUBAIAssistantBasicAiTest.php` | 31 Pest cases |
| `lang/en.json`, `lang/vi.json` | Chuỗi UI assistant (đối chiếu i18n doc) |

### 22.2 Đã đúng với code (trước đây tài liệu thiếu/cũ)

| Hạng mục | Trạng thái |
| --- | --- |
| P0 Knowledge Base tách `MLHUBAIKnowledgeBase.php` | **Đã có** |
| 13 intent P0 + normalize không dấu | **Đã có** |
| 12 intent P1 portal + route actions | **Đã có** |
| Intent `industry_recommendation` + focus P1.3/Phase C | **Đã có** (rule-based theo ngành/tình huống) |
| Metadata `confidence`, `matched_keywords`, `matches`, `intents` | **Đã có** |
| Composer dùng `top_campaigns` / `recent_activity` trong `daily_briefing` | **Đã có** |
| Footer phân biệt metric vs guidance | **Đã có** |
| CTA lọc `Route::has()` — không gợi ý route AI dormant | **Đã có** |
| Onboarding không CTA Studio mặc định | **Đã có** (test + `nextStepActions`) |
| Starter/deepen prompts tiếng Việt | **Đã có** |
| 31 unit tests trong repo | **Đã có** (file) |

### 22.3 Tài liệu ghi nhưng code chưa có / chưa đủ

| Hạng mục trong doc cũ | Thực tế code |
| --- | --- |
| §4.1 “8 intent, 76 keyword” | **Lỗi thời** — hiện 34 intent, ~280+ keyword |
| §5.1 “chưa thấy test” | **Sai** — đã có 41 cases |
| §5.2 thiếu intent landing/booking/CRM/… | **Lỗi thời** — P1 đã thêm |
| `fallback_reason` trong `metadata` (§11 P0) | **Sai vị trí** — ở root response |
| Coverage log / persistence fallback | **Chưa có** |
| Plan/credit snapshot trong context | **Đã có Phase B** — snapshot scalar, workspace owner scope, degrade `available=false` khi thiếu dữ liệu |
| `industry_recommendation` đọc `lb_businesses.type` | **Chưa có** — Phase C mới đọc câu hỏi runtime và không mở ContextBuilder |
| Intent email/whatsapp/webhook/loyalty/files/profile | **Chưa có** |
| §9 `module_coverage`, `data_confidence` scoring | **Chưa có** (chỉ confidence keyword) |

### 22.4 Code có nhưng tài liệu chưa phản ánh (đã cập nhật §4–§21)

| Hạng mục code | Ghi chú |
| --- | --- |
| `focusEverydayQuestion`, dedupe helpers | Resolver P1.3 |
| `intentPriority()` map 34 intent | Sắp xếp multi-intent |
| `composeMany` cap 4 intent + thông báo tóm tắt | Tránh wall of text |
| `appendNotice` footer 2 loại | UX minh bạch Basic AI |
| `advancedAiAvailable()` + banner Blade | Advanced chưa cấu hình |
| Deepen prompts 34 nhóm | Không còn “7 nhóm × 3” |
| Explore prompts 13 mục | Không còn 6 mục |

### 22.5 Rủi ro / mismatch cần xử lý (theo thứ tự)

| Mức | Vấn đề | Hướng xử lý đề xuất |
| --- | --- | --- |
| P1 | **41 tests chưa chạy được** trên môi trường dev hiện tại | Phase E: chạy Pest trên PHP 8.3 / container MLHUB |
| P1 | Plan/credit snapshot và Phase C đã thêm nhưng chưa chạy được runtime test trên host này | Chạy Pest trên PHP 8.3 / container MLHUB trước khi mở tiếp Phase D/E |
| P1 | `industry_recommendation` chưa đọc business type từ context | Phase sau nếu cần: thêm snapshot ngành từ `lb_businesses.type` / taxonomy |
| P2 | Không log `unknown` intent coverage | Phase riêng nếu owner duyệt — chưa làm trong Phase B |
| P2 | Advanced AI model mặc định `gpt-5.4` — cần đối chiếu admin | Phase F — sau khi Basic ổn |
| P2 | §4.2 baseline 8 intent vẫn trong file | Giữ làm lịch sử; không dùng làm spec mới |

### 22.6 ContextBuilder — dữ liệu đã đọc

| Context key | Nguồn | Ghi chú |
| --- | --- | --- |
| `metrics.*` | `PortalGrowthDashboardMetrics::rememberMetrics()` | businesses, campaigns, visits, leads, bookings, coupon_claims, feedback, conversion_rate, … |
| `top_campaigns[]` | `PortalGrowthDashboardMetrics::topCampaigns()` | Top 5 |
| `recent_activity[]` | `PortalGrowthDashboardMetrics::recentActivity()` | 5 bản ghi |
| `customers.*` | `Customer` weekly counts | new/delta |
| `weekly_signals.*` | Lead, Booking, Review, Coupon, QrScan | Tuần hiện tại |
| `reviews.*` | `ReviewFeedback` tuần hiện tại | average, needs_reply |
| `active_campaigns[]` | `QrCampaign` published | Tối đa 6 + conversions |
| `business_list` | `LocalBusiness` | count + 12 tên |
| `onboarding` | Derived từ metrics | hints create/publish/share/boost |
| `user` | `User.name` | short_name cho greeting |
| `workspace.*` | `TeamWorkspaceAccess::workspaceOwnerUserId()` | scalar owner/current user; cache key có `portal_team_id` |
| `plan.*` | `User` + `PlanLimitGuard::usageSummary()` | name/status/usage/limits/near_limit; `available=false` nếu thiếu snapshot |
| `credits.*` | `credit_summary()` / `CreditService::costFor()` | remaining/used/limit/topup/cost `mlhub_ai_chat`; `available=false` nếu lỗi |

**Chưa đọc:** CRM counts, Google connection, landing slug, loyalty, automation logs.

### 22.7 ResponseComposer — intent có template

`help_using_mlhubai`, `industry_recommendation`, `daily_briefing`, `onboarding`, `top_campaigns`, `qr_scans`, `review_booster`, `booking`, `coupon`, `feedback`, `leads`, `conversion`, `credits`, `plan_limits`, `business_locations`, `customers`, `landing_pages`, `marketing_templates`, `crm_segments`, `google_business`, `google_reviews`, `ai_studio`, `ai_content_writer`, `billing`, `teams`, `support`, `new_customers`, `campaigns`, `reviews`, `next_steps`, `overview`, `visits`, `businesses`, `greeting`, `unknown`.

---

## 23. Kế hoạch làm việc tiếp theo

### Phase A — Documentation sync / no code

| # | Việc | Trạng thái |
| --- | --- | --- |
| A1 | Cập nhật `ARCHITECTURE_MLHUBAI.md` theo code thật (§4, §5, §12–§13, §22) | **Xong trong lần audit này** |
| A2 | Giữ §4.2 baseline làm lịch sử; spec mới lấy §12–§13 + KnowledgeBase | **Đã ghi chú** |
| A3 | Đồng bộ chéo `ARCHITECTURE_SOP.md` §E, `ARCHITECTURE_I18N.md` §17 khi copy đổi | Chờ owner duyệt nếu đổi thêm |

**Không code** trong Phase A.

### Phase B — Runtime context completion

| # | Việc | File dự kiến |
| --- | --- | --- |
| B1 | Thêm plan/credit snapshot vào `MLHUBAIContextBuilder` | **Xong Phase B** |
| B2 | Cập nhật `composeCredits`, `composePlanLimits` dùng số thật khi context có snapshot | **Xong Phase B** |
| B3 | Coverage `unknown` / `fallback_reason` nội bộ | **Chưa làm** — ngoài scope Phase B |

**Phase B đã code:** không migration/schema/provider/bootstrap; chưa chạy được Pest trên host thiếu PHP.

### Phase C — Scenario & industry response refinement

| # | Việc | Trạng thái |
| --- | --- | --- |
| C1 | **Phase C.1 (2026-06-21):** mở rộng `industry_recommendation` phủ **18 nhóm** `BusinessTypeCatalog` — `industryGroupAliasMap()`, `industryGroupRouteActions()`, template `composeIndustryRecommendation()` | **Xong** |
| C1 note | Nhận diện ngành từ **câu hỏi runtime** (normalize không dấu); **chưa** đọc `lb_businesses.type` / taxonomy snapshot trong ContextBuilder | Rule-based Basic AI |
| C2 | Scenario overlay (review collection, phone collection, google→booking…) giữ trước template ngành | **Xong** (trước C.1) |
| C3 | Keyword alias bổ sung wholesale/OCOP/creator/hiệp hội…; content creation ưu tiên `ai_content_writer` trước industry | **Xong** trong C.1 |

### Phase D — Chat → Studio handoff refinement

| # | Việc |
| --- | --- |
| D1 | Rà lại intent `ai_studio` / `ai_content_writer` — CTA chỉ khi user hỏi tạo nội dung |
| D2 | Copy handoff trong composer khớp `ARCHITECTURE_I18N.md` §17 |
| D3 | Không gợi ý Studio khi intent vận hành thuần (metrics/onboarding) — giữ test regression |

### Phase E — Tests & verification

| # | Việc |
| --- | --- |
| E1 | Chạy `vendor/bin/pest tests/Unit/CustomMLHUB/MLHUBAIAssistantBasicAiTest.php` |
| E2 | Chạy và khóa test plan/credit snapshot trên môi trường PHP đầy đủ |
| E3 | Smoke manual: dashboard panel + full chat, toggle Advanced khi thiếu API key |

### Phase F — Advanced AI later (không làm ngay)

| # | Việc |
| --- | --- |
| F1 | Đối chiếu `ai_chat_model` admin vs production |
| F2 | QA Advanced AI + credit `mlhub_ai_chat` trên staging |
| F3 | Không mở rộng Basic AI bằng OpenAI/Gemini |

---

## 24. Prompt giao việc cho Codex theo phase tiếp theo

> **Mẫu prompt — chỉ plan/duyệt trước khi code.** Dán vào Codex sau khi owner gõ **“Duyệt Phase B”** (hoặc phase tương ứng).

```text
Bạn là Senior Laravel/Livewire Engineer cho MLHUB (Laravel 13 + Livewire 4, production mlhub.vn).

NHIỆM VỤ: [Phase B — Runtime context completion] cho Chat MLHUB AI ONLY.

ĐỌC BẮT BUỘC TRƯỚC KHI LẬP PLAN:
- .cursorrules
- ARCHITECTURE_CHECKLIST.md
- ARCHITECTURE_MLHUBAI.md (§22–§23, §12–§13)
- ARCHITECTURE_MODULE.md §10 CustomMLHUB
- modules/CustomMLHUB/Support/MLHUBAIAssistant/MLHUBAIContextBuilder.php
- modules/CustomMLHUB/Support/MLHUBAIAssistant/MLHUBAIResponseComposer.php
- tests/Unit/CustomMLHUB/MLHUBAIAssistantBasicAiTest.php

QUY TẮC CỨNG:
1. Lập PLAN chi tiết (file sẽ sửa, không migration/schema/provider/bootstrap trừ khi owner duyệt rõ).
2. CHƯA CODE cho đến khi owner gõ "Duyệt".
3. KHÔNG gọi OpenAI/Gemini trong Basic AI.
4. KHÔNG tạo file mới nếu gộp được vào class hiện có trong modules/CustomMLHUB/Support/MLHUBAIAssistant/.
5. KHÔNG sửa ngoài phạm vi Chat MLHUB AI (CustomMLHUB assistant + test liên quan).
6. Tenant scope: mọi query user_id = auth()->id() hoặc workspace owner đúng pattern hiện có.
7. Không bịa số — nếu thiếu snapshot, composer phải nói thiếu dữ liệu.
8. Cập nhật ARCHITECTURE_MLHUBAI.md §22–§23 sau khi code (Phase A follow-up).
9. Chạy pest filter MLHUBAIAssistantBasicAiTest — báo "chưa chạy được" nếu môi trường thiếu PHP.

OUTPUT PLAN (chưa code):
- Mục tiêu Phase B
- File thay đổi
- Field context mới
- Test sẽ thêm/sửa
- Rủi ro production
- Cách verify thủ công trên portal
```

**Phase C/D/E:** đổi tiêu đề phase và file đọc thêm (`BusinessTypeCatalog`, `ARCHITECTURE_SOP.md` §E, `ARCHITECTURE_I18N.md` §17) — giữ nguyên quy tắc cứng 1–9.

**Không tạo** `ARCHITECTURE_MLHUBAI_VISION.md` hoặc file kiến trúc mới.
