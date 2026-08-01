<?php

namespace Modules\AppLandingPages\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppBusinessProfiles\Support\BusinessQrRenderer;
use Illuminate\View\View;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBookingPages\Models\BookingService;
use Modules\AppBookingPages\Support\BookingAvailability;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppCustomers\Models\Customer;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppLandingPages\Support\PageTemplateCatalog;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppReviewBooster\Models\ReviewFeedback;

class LandingPagePublicController extends Controller
{
    public function preview(Request $request): View
    {
        $type = (string) $request->query('type', 'lead');
        $type = in_array($type, ['review', 'booking', 'coupon', 'promotion', 'feedback', 'lead', 'loyalty', 'custom'], true) ? $type : 'lead';
        $template = (string) $request->query('template', PageTemplateCatalog::defaultForType($type));

        if (! array_key_exists($template, PageTemplateCatalog::forType($type))) {
            $template = PageTemplateCatalog::defaultForType($type);
        }

        $business = null;
        if ($request->user() && filled($request->query('business_id'))) {
            $business = LocalBusiness::query()
                ->where('user_id', $request->user()->id)
                ->find((int) $request->query('business_id'));
        }

        $content = array_merge($this->previewContent($type), $this->previewContentOverrides($request));
        $blocks = $this->previewBlocks($request, $type);
        $design = array_merge(PageTemplateCatalog::designFor($template), [
            'template' => $template,
            'primary_color' => $this->validHex((string) $request->query('primary_color')) ?: data_get(PageTemplateCatalog::designFor($template), 'primary_color'),
            'background_color' => $this->validHex((string) $request->query('background_color')) ?: data_get(PageTemplateCatalog::designFor($template), 'background_color'),
            'background_type' => $this->allowed((string) $request->query('background_type'), ['gradient', 'solid', 'soft'], 'gradient'),
            'font_style' => $this->allowed((string) $request->query('font_style'), ['modern', 'classic', 'elegant', 'friendly'], 'modern'),
            'button_style' => $this->allowed((string) $request->query('button_style'), ['pill', 'rounded', 'square'], 'pill'),
            'card_style' => $this->allowed((string) $request->query('card_style'), ['soft', 'bordered', 'flat'], 'soft'),
            'logo_url' => filter_var($request->query('logo_url'), FILTER_VALIDATE_URL) ? (string) $request->query('logo_url') : '',
            'logo_shape' => $this->allowed((string) $request->query('logo_shape'), ['circle', 'square'], 'circle'),
            'cover_image' => filter_var($request->query('cover_image'), FILTER_VALIDATE_URL) ? (string) $request->query('cover_image') : '',
            'show_logo' => $request->boolean('show_logo', true),
            'show_benefits' => $request->boolean('show_benefits', true),
            'show_terms' => $request->boolean('show_terms', true),
            'show_business_info' => $request->boolean('show_business_info', true),
            'show_social_links' => $request->boolean('show_social_links', true),
            'show_faq' => $request->boolean('show_faq', true),
        ]);

        $landingPage = new LandingPage([
            'user_id' => $request->user()?->id,
            'business_id' => $business?->id,
            'slug' => 'preview',
            'type' => $type,
            'template' => $template,
            'title' => (string) $content['headline'],
            'status' => 'published',
            'content' => array_merge($content, ['landing_page_blocks' => $blocks]),
            'settings' => array_merge($this->previewSettings($type), [
                'design' => $design,
                'blocks' => $blocks,
            ], $this->previewSettingOverrides($request, $type)),
        ]);
        $landingPage->setRelation('business', $business);

        $bookingServices = $this->bookingServicesFor($landingPage);

        return view($this->publicViewFor($template, $landingPage->type), [
            'landingPage' => $landingPage,
            'bookingServices' => $bookingServices,
            'bookingAvailability' => app(BookingAvailability::class)->availabilityMap($bookingServices, 120),
        ]);
    }

    public function show(Request $request, LandingPage $landingPage): View
    {
        abort_unless($landingPage->status === 'published', 404);

        if (! $request->session()->has('landing_page_visited_'.$landingPage->id)) {
            $landingPage->increment('visits_count');
            $this->recordCampaignVisit($request, $landingPage);
            $request->session()->put('landing_page_visited_'.$landingPage->id, true);
        }

        $landingPage->load('business', 'campaign');

        $bookingServices = $this->bookingServicesFor($landingPage);

        return view($this->publicViewFor((string) $landingPage->template, (string) $landingPage->type), [
            'landingPage' => $landingPage,
            'bookingServices' => $bookingServices,
            'bookingAvailability' => app(BookingAvailability::class)->availabilityMap($bookingServices, 120),
        ]);
    }

    public function submit(Request $request, LandingPage $landingPage): RedirectResponse
    {
        abort_unless($landingPage->status === 'published', 404);

        $bookingServices = $this->bookingServicesFor($landingPage);
        $rules = match ($landingPage->type) {
            'review' => [
                'rating' => ['required', 'integer', 'min:1', 'max:5'],
                'feedback' => ['nullable', 'string', 'max:2000'],
                'name' => ['nullable', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:80'],
            ],
            'booking' => [
                'service_id' => [$bookingServices->isNotEmpty() ? 'required' : 'nullable', 'integer'],
                'service' => [$bookingServices->isNotEmpty() ? 'nullable' : 'required', 'string', 'max:255'],
                'date' => ['required', 'date'],
                'time' => ['required', 'string', 'max:20'],
                'name' => ['required', 'string', 'max:100'],
                'phone' => ['required', 'string', 'min:6', 'max:30'],
                'email' => ['nullable', 'email', 'max:150'],
            ],
            'feedback' => [
                'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
                'topic' => ['nullable', 'string', 'max:120'],
                'feedback' => ['required', 'string', 'max:2000'],
                'name' => ['nullable', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
            ],
            default => [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:80'],
                'message' => ['nullable', 'string', 'max:2000'],
                'interested_service' => ['nullable', 'string', 'max:255'],
            ],
        };

        $payload = $request->validate($rules);
        if ($landingPage->type === 'booking' && $bookingServices->isNotEmpty()) {
            $service = $bookingServices->firstWhere('id', (int) data_get($payload, 'service_id'));
            abort_unless($service, 422);

            if (! app(BookingAvailability::class)->isSlotAvailable($service, (string) $payload['date'], (string) $payload['time'])) {
                return back()
                    ->withErrors(['time' => __('Selected time is outside business hours or no longer available.')])
                    ->withInput();
            }

            $payload['service'] = $service->name;
            $payload['service_id'] = $service->id;
        }
        $campaign = $this->campaignFor($landingPage);

        $landingPage->increment('conversions_count');
        $this->storeCustomer($landingPage, $payload);
        $this->storeConversion($landingPage, $campaign, $payload);

        if ($landingPage->type === 'review' && (int) data_get($payload, 'rating') >= 4) {
            $destination = (string) data_get($landingPage->settings, 'review_url', '');

            if (filled($destination)) {
                return redirect()->away($destination);
            }
        }

        return back()->with('landing_page_converted', true);
    }

    private function publicViewFor(string $template, string $type): string
    {
        $view = PageTemplateCatalog::publicViewFor($template, $type);

        return view()->exists($view) ? $view : 'applandingpages::public.show';
    }

    protected function storeConversion(LandingPage $landingPage, QrCampaign $campaign, array $payload): void
    {
        match ($landingPage->type) {
            'review' => ReviewFeedback::query()->create([
                'user_id' => $landingPage->user_id,
                'campaign_id' => $campaign->id,
                'rating' => (int) data_get($payload, 'rating'),
                'customer_name' => data_get($payload, 'name'),
                'customer_phone' => data_get($payload, 'phone'),
                'customer_email' => data_get($payload, 'email'),
                'message' => data_get($payload, 'feedback'),
            ]),
            'booking' => Booking::query()->create([
                'user_id' => $landingPage->user_id,
                'campaign_id' => $campaign->id,
                'service_id' => data_get($payload, 'service_id'),
                'booking_date' => data_get($payload, 'date'),
                'booking_time' => data_get($payload, 'time'),
                'customer_name' => data_get($payload, 'name'),
                'customer_phone' => data_get($payload, 'phone'),
                'customer_email' => data_get($payload, 'email'),
                'note' => data_get($payload, 'service'),
            ]),
            'coupon', 'promotion' => CouponRedemption::query()->create([
                'user_id' => $landingPage->user_id,
                'campaign_id' => $campaign->id,
                'code' => $this->uniqueCouponCode((string) data_get($landingPage->settings, 'coupon_title', '')),
                'customer_name' => data_get($payload, 'name'),
                'customer_phone' => data_get($payload, 'phone'),
                'customer_email' => data_get($payload, 'email'),
            ]),
            'feedback' => FeedbackResponse::query()->create([
                'user_id' => $landingPage->user_id,
                'campaign_id' => $campaign->id,
                'rating' => data_get($payload, 'rating'),
                'customer_name' => data_get($payload, 'name'),
                'customer_email' => data_get($payload, 'email'),
                'message' => data_get($payload, 'feedback'),
                'payload' => ['topic' => data_get($payload, 'topic'), 'landing_page_id' => $landingPage->id],
            ]),
            default => LeadSubmission::query()->create([
                'user_id' => $landingPage->user_id,
                'campaign_id' => $campaign->id,
                'name' => data_get($payload, 'name'),
                'phone' => data_get($payload, 'phone'),
                'email' => data_get($payload, 'email'),
                'message' => data_get($payload, 'message'),
                'payload' => ['interested_service' => data_get($payload, 'interested_service'), 'landing_page_id' => $landingPage->id],
            ]),
        };
    }

    protected function storeCustomer(LandingPage $landingPage, array $payload): void
    {
        $name = (string) (data_get($payload, 'name') ?: data_get($payload, 'customer_name') ?: 'Guest');
        $email = data_get($payload, 'email');
        $phone = data_get($payload, 'phone');

        if (! filled($email) && ! filled($phone) && $name === 'Guest') {
            return;
        }

        $query = Customer::query()
            ->where('user_id', $landingPage->user_id)
            ->where('business_id', $landingPage->business_id);

        if (filled($email)) {
            $query->where('email', $email);
        } elseif (filled($phone)) {
            $query->where('phone', $phone);
        } else {
            $query->where('name', $name);
        }

        $customer = $query->first();
        $metadata = array_merge((array) ($customer?->metadata ?: []), [
            'last_source' => 'landing_page',
            'last_landing_page_id' => $landingPage->id,
            'last_landing_page_type' => $landingPage->type,
        ]);

        if ($customer) {
            $customer->forceFill([
                'name' => $name !== 'Guest' ? $name : $customer->name,
                'phone' => $phone ?: $customer->phone,
                'email' => $email ?: $customer->email,
                'metadata' => $metadata,
            ])->save();

            return;
        }

        Customer::query()->create([
            'user_id' => $landingPage->user_id,
            'business_id' => $landingPage->business_id,
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'tags' => ['landing-page', $landingPage->type],
            'metadata' => $metadata,
        ]);
    }

    protected function bookingServicesFor(LandingPage $landingPage)
    {
        if ($landingPage->type !== 'booking' || ! $landingPage->business_id) {
            return collect();
        }

        return BookingService::query()
            ->with('business')
            ->where('business_id', $landingPage->business_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    protected function campaignFor(LandingPage $landingPage): QrCampaign
    {
        if ($landingPage->campaign) {
            return $landingPage->campaign;
        }

        $campaign = QrCampaign::query()->create([
            'user_id' => $landingPage->user_id,
            'business_id' => $landingPage->business_id,
            'slug' => $this->uniqueCampaignSlug($landingPage->title),
            'name' => $landingPage->title,
            'type' => match ($landingPage->type) {
                'promotion' => 'coupon',
                'review', 'booking', 'coupon', 'feedback', 'lead' => $landingPage->type,
                default => 'lead',
            },
            'settings' => ['source' => 'landing_page', 'landing_page_id' => $landingPage->id],
            'published_at' => now(),
        ]);

        $landingPage->forceFill(['campaign_id' => $campaign->id])->save();
        $landingPage->setRelation('campaign', $campaign);

        return $campaign;
    }

    protected function recordCampaignVisit(Request $request, LandingPage $landingPage): void
    {
        $campaign = $landingPage->campaign;

        if (! $campaign) {
            return;
        }

        $agent = (string) $request->userAgent();

        $campaign->scans()->create([
            'user_id' => $campaign->user_id,
            'ip_address' => $request->ip(),
            'user_agent' => $agent,
            'device' => str_contains(strtolower($agent), 'mobile') ? 'mobile' : 'desktop',
            'created_at' => now(),
        ]);
    }

    protected function uniqueCouponCode(string $prefix = ''): string
    {
        $prefix = Str::of($prefix)->upper()->replaceMatches('/[^A-Z0-9]/', '')->substr(0, 10)->toString();

        do {
            $code = ($prefix !== '' ? $prefix.'-' : '').Str::upper(Str::random(6));
        } while (CouponRedemption::query()->where('code', $code)->exists());

        return $code;
    }

    protected function uniqueCampaignSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'landing-page';
        $slug = $base;
        $counter = 2;

        while (QrCampaign::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    protected function previewContent(string $type): array
    {
        return match ($type) {
            'review' => [
                'headline' => 'How was your visit?',
                'subheadline' => 'Choose a rating. Happy customers continue to a public review, while private feedback goes to the team.',
                'description' => '',
                'cta' => 'Continue',
                'benefits' => ['Fast rating flow', 'Private feedback for low scores', 'Public review click tracking'],
                'thank_you_message' => 'Thank you for your feedback.',
            ],
            'booking' => [
                'headline' => 'Book an appointment',
                'subheadline' => 'Choose a service, pick a time, and send your booking request.',
                'description' => '',
                'cta' => 'Request booking',
                'benefits' => ['Choose a service', 'Pick an available slot', 'Get confirmation from the team'],
                'thank_you_message' => 'Thanks. We received your booking request.',
            ],
            'coupon' => [
                'headline' => 'Claim your local offer',
                'subheadline' => 'Claim this limited-time offer and show your code in-store.',
                'description' => 'Valid for one customer. Cannot be combined with other offers.',
                'cta' => 'Claim coupon',
                'benefits' => ['Limited-time offer', 'Instant claim code', 'Redeem with staff'],
                'thank_you_message' => 'Your coupon has been sent.',
            ],
            'feedback' => [
                'headline' => 'Tell us about your experience',
                'subheadline' => 'Send private feedback so the team can improve the next visit.',
                'description' => '',
                'cta' => 'Send feedback',
                'benefits' => ['Private feedback', 'Optional rating', 'Team follow-up'],
                'thank_you_message' => 'Thanks. Your feedback helps us improve.',
            ],
            'loyalty' => [
                'headline' => 'Collect stamps and unlock a reward',
                'subheadline' => 'Scan the in-store QR code after each visit and track your progress toward a local reward.',
                'description' => 'Rewards are issued automatically when the stamp card is complete.',
                'cta' => 'Collect stamp',
                'benefits' => ['Digital stamp card', 'Automatic reward unlock', 'Easy in-store redemption'],
                'thank_you_message' => 'Thanks. Your stamp has been added.',
            ],
            default => [
                'headline' => 'Request a free consultation',
                'subheadline' => 'Tell us what you need and our local team will follow up.',
                'description' => '',
                'cta' => 'Send request',
                'benefits' => ['Fast response', 'Friendly local team', 'Simple next step'],
                'thank_you_message' => 'Thank you. We have received your request.',
            ],
        };
    }

    protected function previewSettings(string $type): array
    {
        return match ($type) {
            'booking' => ['service' => 'Appointment', 'duration' => '60 minutes', 'price' => 'Ask us', 'available_slots' => ['09:00', '10:00', '14:00', '15:00']],
            'coupon' => ['coupon_title' => 'LOCAL20', 'discount' => '20% off', 'expiry' => now()->addDays(14)->toDateString(), 'terms' => 'Valid for one customer. Cannot be combined with other offers.'],
            'review' => ['review_url' => ''],
            default => [],
        };
    }

    protected function previewContentOverrides(Request $request): array
    {
        $headline = trim((string) $request->query('headline', ''));
        $subheadline = trim((string) $request->query('subheadline', ''));
        $description = trim((string) $request->query('description', ''));
        $cta = trim((string) $request->query('cta', ''));
        $thankYou = trim((string) $request->query('thank_you_message', ''));
        $benefits = collect(preg_split('/\r\n|\r|\n/', (string) $request->query('benefits', '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        return array_filter([
            'headline' => $headline !== '' ? $headline : null,
            'subheadline' => $subheadline !== '' ? $subheadline : null,
            'description' => $description !== '' ? $description : null,
            'cta' => $cta !== '' ? $cta : null,
            'thank_you_message' => $thankYou !== '' ? $thankYou : null,
            'benefits' => $benefits !== [] ? $benefits : null,
        ], fn ($value) => $value !== null);
    }

    protected function previewSettingOverrides(Request $request, string $type): array
    {
        $overrides = [];

        if (in_array($type, ['coupon', 'promotion'], true)) {
            $overrides['coupon_title'] = (string) $request->query('coupon_title', data_get($this->previewSettings($type), 'coupon_title', ''));
            $overrides['discount'] = (string) $request->query('discount', data_get($this->previewSettings($type), 'discount', ''));
            $overrides['expiry'] = (string) $request->query('expiry', data_get($this->previewSettings($type), 'expiry', ''));
            $overrides['terms'] = (string) $request->query('terms', data_get($this->previewSettings($type), 'terms', ''));
        }

        if ($type === 'booking') {
            $overrides['service'] = (string) $request->query('service', data_get($this->previewSettings($type), 'service', ''));
            $overrides['duration'] = (string) $request->query('duration', data_get($this->previewSettings($type), 'duration', ''));
            $overrides['price'] = (string) $request->query('price', data_get($this->previewSettings($type), 'price', ''));
            $overrides['available_slots'] = collect(preg_split('/\r\n|\r|\n/', (string) $request->query('available_slots', '')))
                ->map(fn ($line) => trim($line))
                ->filter()
                ->values()
                ->all() ?: data_get($this->previewSettings($type), 'available_slots', []);
        }

        if ($type === 'review') {
            $overrides['review_url'] = (string) $request->query('review_url', '');
        }

        return $overrides;
    }

    protected function previewBlocks(Request $request, string $type): array
    {
        $payload = (string) $request->query('landing_blocks', '');

        if ($payload === '') {
            return PageTemplateCatalog::blocksFor($type);
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : PageTemplateCatalog::blocksFor($type);
    }

    protected function validHex(string $value): ?string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? $value : null;
    }

    protected function allowed(string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }

    public function qr(LandingPage $landingPage, BusinessQrRenderer $renderer)
    {
        $business = $this->qrBusinessFor($landingPage);

        return response($renderer->render($business, null, $landingPage->publicUrl()), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="'.$landingPage->slug.'.svg"',
        ]);
    }

    public function png(LandingPage $landingPage, BusinessQrRenderer $renderer)
    {
        $business = $this->qrBusinessFor($landingPage);

        return response($renderer->renderPng($business, null, $landingPage->publicUrl()), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.$landingPage->slug.'.png"',
        ]);
    }

    protected function qrBusinessFor(LandingPage $landingPage): LocalBusiness
    {
        $landingPage->loadMissing(['business', 'campaign.business']);

        $business = $landingPage->business ?: $landingPage->campaign?->business;

        if ($business instanceof LocalBusiness) {
            return $business;
        }

        return new LocalBusiness([
            'name' => (string) ($landingPage->title ?: config('app.name', 'MKT AI')),
            'address' => '',
            'qr_design' => [],
        ]);
    }
}
