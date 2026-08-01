@php
    $catalog = [
        [
            'title' => __('Portal overview'),
            'items' => [
                [
                    'label' => __('Dashboard'),
                    'path' => '/portal/dashboard',
                    'text' => __('Track workspace-wide growth metrics including visits, clicks, leads, bookings, coupons, and feedback. Monitor campaign performance and AI credit usage across your workspace.'),
                ],
            ],
        ],
        [
            'title' => __('Business organization'),
            'items' => [
                [
                    'label' => __('Businesses'),
                    'path' => '/portal/businesses',
                    'text' => __('Manage core store and business profiles—the data heart (name, address, contact) that powers QR campaigns, booking pages, and Google Maps sync.'),
                ],
                [
                    'label' => __('Customers'),
                    'path' => '/portal/customers',
                    'text' => __('Internal CDP that unifies contact data from every capture point (bookings, forms, coupons) so you can filter and manage customer health in one place.'),
                ],
            ],
        ],
        [
            'title' => __('Growth tools'),
            'intro' => __('Gathering layer — conversion funnels by customer intent.'),
            'groups' => [
                [
                    'subtitle' => __('Direct conversion funnel (high intent)'),
                    'items' => [
                        [
                            'label' => __('Booking pages'),
                            'path' => '/portal/booking-pages',
                            'text' => __('Capture guests who are ready to book—high-intent demand with clear scheduling intent.'),
                        ],
                        [
                            'label' => __('Lead forms'),
                            'path' => '/portal/lead-forms',
                            'text' => __('Collect name, phone, and email for consultation, quotes, and nurture campaigns.'),
                        ],
                    ],
                ],
                [
                    'subtitle' => __('Offer-driven funnel'),
                    'items' => [
                        [
                            'label' => __('Coupon campaigns'),
                            'path' => '/portal/coupon-campaigns',
                            'text' => __('Trade vouchers and discounts for contact data—ideal for offline QR at physical stores.'),
                        ],
                    ],
                ],
                [
                    'subtitle' => __('Nurturing & segmentation funnel'),
                    'items' => [
                        [
                            'label' => __('Feedback forms'),
                            'path' => '/portal/feedback-forms',
                            'text' => __('Measure experience temperature; negative feedback stays private for quiet recovery workflows.'),
                        ],
                        [
                            'label' => __('Review Booster'),
                            'path' => '/portal/review-booster',
                            'text' => __('Route happy guests to Google Maps and Facebook to strengthen local SEO and attract new traffic.'),
                        ],
                    ],
                ],
                [
                    'subtitle' => __('Retention & replication funnel'),
                    'items' => [
                        [
                            'label' => __('Loyalty & Referral'),
                            'path' => '/portal/loyalty-cards',
                            'text' => __('Turn one-time buyers into repeat customers, track habits, and power retargeting.'),
                        ],
                    ],
                ],
            ],
        ],
        [
            'title' => __('CRM (advanced module)'),
            'items' => [
                ['label' => __('CRM Customers'), 'path' => '/portal/crm/customers', 'text' => __('Detailed customer funnel management workspace.')],
                ['label' => __('CRM Segments'), 'path' => '/portal/crm/segments', 'text' => __('Slice and filter customers by behavior and attributes for personalized campaigns.')],
                ['label' => __('CRM Tags'), 'path' => '/portal/crm/tags', 'text' => __('Label customers (e.g. VIP, call back) for fast segmentation.')],
                ['label' => __('CRM Tasks'), 'path' => '/portal/crm/tasks', 'text' => __('Assign and track follow-up SOP work across staff and interns per customer.')],
                ['label' => __('CRM automations'), 'path' => '/portal/crm/automations', 'text' => __('Rule-based automations that run CRM actions for you.')],
                ['label' => __('CRM Reports'), 'path' => '/portal/crm/reports', 'text' => __('Measure conversion rates across sales stages.')],
            ],
        ],
        [
            'title' => __('AI tools'),
            'items' => [
                ['label' => __('AI Studio'), 'path' => '/portal/ai-studio', 'text' => __('Use AI to ideate and configure automated growth funnels.')],
                ['label' => __('AI Review'), 'path' => '/portal/ai-studio/review', 'text' => __('Understand and reply to Google and Facebook reviews with nuanced AI assistance.')],
                ['label' => __('AI Content'), 'path' => '/portal/ai-studio/ai-content', 'text' => __('Generate fanpage posts, email scripts, and sales copy at scale.')],
            ],
        ],
        [
            'title' => __('Assets'),
            'items' => [
                ['label' => __('Landing pages'), 'path' => '/portal/landing-pages', 'text' => __('Fast landing page builder for lead capture.')],
                ['label' => __('QR campaigns'), 'path' => '/portal/qr-campaigns', 'text' => __('Create and manage dynamic or static O2O QR touchpoints at points of sale.')],
                ['label' => __('Marketing templates'), 'path' => '/portal/marketing-templates', 'text' => __('Ready-made marketing templates for rapid reuse.')],
                ['label' => __('Custom domains'), 'path' => '/portal/brand/custom-domains', 'text' => __('Attach your own domain (e.g. booking.yourbrand.vn) for stronger trust.')],
                ['label' => __('Files'), 'path' => '/portal/files', 'text' => __('Central cloud storage for campaign images and media.')],
            ],
        ],
        [
            'title' => __('Reports'),
            'items' => [
                ['label' => __('Reports'), 'path' => '/portal/reports', 'text' => __('ROI hub with traffic charts and holistic campaign analytics.')],
            ],
        ],
        [
            'title' => __('Google Business'),
            'items' => [
                ['label' => __('Google Business overview'), 'path' => '/portal/integrations/google-business', 'text' => __('Bird’s-eye view of all mapped locations.')],
                ['label' => __('Locations'), 'path' => '/portal/integrations/google-business', 'text' => __('Manage hours, address, and local SEO contact details.')],
                ['label' => __('Google reviews'), 'path' => '/portal/integrations/google-business', 'text' => __('Sync 100% of Google Maps reviews into one inbox.')],
                ['label' => __('Google posts'), 'path' => '/portal/integrations/google-business', 'text' => __('Publish and schedule posts directly on Google Business Profile.')],
                ['label' => __('Google insights'), 'path' => '/portal/integrations/google-business', 'text' => __('Pull Google insights: directions, map views, and calls.')],
                ['label' => __('Auto review replies'), 'path' => '/portal/integrations/google-business', 'text' => __('AI reply scripts when new reviews arrive.')],
            ],
        ],
        [
            'title' => __('Automation'),
            'items' => [
                ['label' => __('Email automations'), 'path' => '/portal/email-automation/automations', 'text' => __('Email flows, templates, and delivery logs.')],
                ['label' => __('WhatsApp Notification'), 'path' => '/portal/whatsapp-notification/automations', 'text' => __('Instant messaging scripts, templates, and send history.')],
                ['label' => __('Webhook Automations'), 'path' => '/portal/webhook-automation/automations', 'text' => __('Push triggers to external systems (Vbout, HubSpot, Retune) with transmission logs.')],
            ],
        ],
        [
            'title' => __('Account'),
            'items' => [
                ['label' => __('Team members'), 'path' => '/portal/teams', 'text' => __('Roles and access for operators and interns.')],
                ['label' => __('Plans'), 'path' => '/portal/packages', 'text' => __('Track Starter, Growth, and Pro subscriptions.')],
                ['label' => __('Billing'), 'path' => '/portal/billing', 'text' => __('Payment history and invoices.')],
                ['label' => __('Settings'), 'path' => '/portal/profile', 'text' => __('Profile, password, and display preferences.')],
            ],
        ],
    ];
    $signupEnabled = auth_signup_enabled();
    $portalCtaUrl = $signupEnabled ? route('register') : route('login');
@endphp

<section id="solutions" class="lb-wrap lb-section lb-about scroll-mt-28">
    <div class="mb-10 text-center">
        <span class="lb-pill inline-flex items-center rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">{{ __('Solutions') }}</span>
        <h1 class="lb-serif lb-heading mx-auto mt-5 max-w-3xl">{{ __('MKT platform solutions') }}</h1>
        <p class="lb-body mx-auto mt-5 max-w-2xl" style="color: var(--lb-muted);">{{ __('Explore every workspace module—from overview and growth funnels to CRM, AI, assets, Google Business, automation, and account management.') }}</p>
    </div>

    <div class="grid gap-8">
        @foreach ($catalog as $section)
            <article class="lb-card lb-about-panel rounded-2xl p-6 sm:p-8">
                <h2 class="lb-card-title">{{ $section['title'] }}</h2>
                @if (! empty($section['intro']))
                    <p class="lb-body mt-3" style="color: var(--lb-muted);">{{ $section['intro'] }}</p>
                @endif

                @if (! empty($section['groups']))
                    <div class="mt-6 grid gap-6">
                        @foreach ($section['groups'] as $group)
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.14em]" style="color: var(--lb-muted);">{{ $group['subtitle'] }}</p>
                                <ul class="mt-4 grid gap-4">
                                    @foreach ($group['items'] as $item)
                                        <li class="rounded-xl border bg-white/80 p-4" style="border-color: var(--lb-line);">
                                            <div class="flex flex-wrap items-start justify-between gap-3">
                                                <p class="text-sm font-black">{{ $item['label'] }}</p>
                                                <code class="rounded-lg px-2 py-1 text-[11px] font-semibold text-neutral-500" style="background: color-mix(in srgb, var(--lb-lime) 18%, #fff);">{{ $item['path'] }}</code>
                                            </div>
                                            <p class="lb-caption mt-2" style="color: var(--lb-muted);">{{ $item['text'] }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @elseif (! empty($section['items']))
                    <ul class="mt-5 grid gap-4">
                        @foreach ($section['items'] as $item)
                            <li class="rounded-xl border bg-white/80 p-4" style="border-color: var(--lb-line);">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <p class="text-sm font-black">{{ $item['label'] }}</p>
                                    <code class="rounded-lg px-2 py-1 text-[11px] font-semibold text-neutral-500" style="background: color-mix(in srgb, var(--lb-lime) 18%, #fff);">{{ $item['path'] }}</code>
                                </div>
                                <p class="lb-caption mt-2" style="color: var(--lb-muted);">{{ $item['text'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </article>
        @endforeach
    </div>

    <div class="lb-window lb-final-cta mt-10 rounded-3xl p-6 text-center sm:p-8">
        <h2 class="lb-serif lb-heading mx-auto max-w-2xl">{{ __('Ready to open your MKT workspace?') }}</h2>
        <p class="lb-body mx-auto mt-4 max-w-xl" style="color: var(--lb-muted);">{{ __('Sign in after registration to use every path listed above inside your tenant.') }}</p>
        <a href="{{ $portalCtaUrl }}" class="lb-button mt-6 inline-flex items-center justify-center px-6 py-3.5 text-sm font-black">{{ __('Start your free trial') }}</a>
    </div>
</section>
