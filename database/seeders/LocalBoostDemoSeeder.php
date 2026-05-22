<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\AdminUser\Models\User;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBookingPages\Models\BookingService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppCustomers\Models\Customer;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppLandingPages\Support\LandingPageFactory;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppQRCampaigns\Models\QrScan;
use Modules\AppReviewBooster\Models\ReviewFeedback;

class LocalBoostDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'demo@localboost.test'],
            [
                'name' => 'LocalBoost Demo',
                'username' => 'localboostdemo',
                'password' => Hash::make('123456'),
                'locale' => 'en',
                'email_verified_at' => now(),
            ],
        );

        $businesses = collect([
            ['slug' => 'bloom-spa', 'name' => 'Bloom Spa', 'type' => 'spa', 'phone' => '+1 555 0101', 'email' => 'hello@bloomspa.test', 'website' => 'https://bloomspa.test', 'address' => '120 Market Street'],
            ['slug' => 'corner-table', 'name' => 'Corner Table Bistro', 'type' => 'restaurant', 'phone' => '+1 555 0102', 'email' => 'team@cornertable.test', 'website' => 'https://cornertable.test', 'address' => '42 Main Avenue'],
            ['slug' => 'clear-smile-clinic', 'name' => 'Clear Smile Clinic', 'type' => 'clinic', 'phone' => '+1 555 0103', 'email' => 'care@clearsmile.test', 'website' => 'https://clearsmile.test', 'address' => '8 Wellness Plaza'],
        ])->mapWithKeys(fn (array $data) => [
            $data['slug'] => LocalBusiness::query()->updateOrCreate(
                ['user_id' => $user->id, 'name' => $data['name']],
                collect($data)->except('slug')->all(),
            ),
        ]);

        $campaigns = collect([
            [
                'slug' => 'bloom-google-review-request',
                'business' => 'bloom-spa',
                'name' => 'Google Review Request',
                'type' => 'review',
                'settings' => [
                    'google_review_url' => 'https://www.google.com/search?q=Bloom+Spa+reviews',
                    'facebook_review_url' => 'https://www.facebook.com/',
                    'positive_threshold' => 4,
                    'preferred_destination' => 'google',
                    'thank_you_message' => 'Thanks for visiting Bloom Spa.',
                    'negative_feedback_message' => 'Tell us what we can improve before your next visit.',
                ],
            ],
            [
                'slug' => 'clear-smile-free-consultation',
                'business' => 'clear-smile-clinic',
                'name' => 'Free Consultation Lead Capture',
                'type' => 'lead',
                'settings' => ['headline' => 'Book a free smile consultation'],
            ],
            [
                'slug' => 'corner-table-weekend-coupon',
                'business' => 'corner-table',
                'name' => '20% Off Weekend Dinner',
                'type' => 'coupon',
                'settings' => ['discount_type' => 'percentage', 'discount_value' => '20', 'coupon_code' => 'WEEKEND20', 'usage_limit' => 200, 'expiry_date' => now()->addDays(21)->toDateString(), 'terms' => 'Valid for dine-in dinner this weekend.'],
            ],
            [
                'slug' => 'bloom-massage-booking',
                'business' => 'bloom-spa',
                'name' => 'Massage Appointment Booking',
                'type' => 'booking',
                'settings' => ['headline' => 'Reserve your massage appointment'],
            ],
            [
                'slug' => 'corner-table-feedback',
                'business' => 'corner-table',
                'name' => 'Private Dining Feedback',
                'type' => 'feedback',
                'settings' => ['headline' => 'Tell us about your dining experience', 'thank_you_message' => 'Thanks. Your feedback helps our team improve.', 'rating_required' => false, 'contact_required' => false],
            ],
        ])->mapWithKeys(function (array $data) use ($user, $businesses): array {
            $campaign = QrCampaign::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'user_id' => $user->id,
                    'business_id' => $businesses[$data['business']]->id,
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'settings' => $data['settings'],
                    'published_at' => now()->subDays(14),
                ],
            );

            app(LandingPageFactory::class)->syncFromCampaign($campaign);

            return [$data['type'] => $campaign];
        });

        $service = BookingService::query()->updateOrCreate(
            ['user_id' => $user->id, 'business_id' => $businesses['bloom-spa']->id, 'name' => '60-minute Relaxation Massage'],
            ['duration_minutes' => 60, 'price' => 89, 'description' => 'Relaxation massage for new and returning guests.', 'available_days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'], 'time_slots' => ['09:00', '10:00', '14:00', '15:00'], 'is_active' => true],
        );

        QrScan::query()->where('user_id', $user->id)->whereIn('campaign_id', $campaigns->pluck('id'))->delete();
        foreach ($campaigns as $campaign) {
            foreach (range(1, random_int(18, 42)) as $index) {
                QrScan::query()->create([
                    'user_id' => $user->id,
                    'campaign_id' => $campaign->id,
                    'ip_address' => '127.0.0.'.($index % 240 + 1),
                    'user_agent' => $index % 3 === 0 ? 'Mobile Safari Demo' : 'Chrome Desktop Demo',
                    'device' => $index % 3 === 0 ? 'mobile' : 'desktop',
                    'city' => 'Demo City',
                    'country' => 'US',
                    'created_at' => now()->subDays(random_int(0, 14)),
                ]);
            }
        }

        $customers = collect([
            ['name' => 'Mia Johnson', 'phone' => '+1 555 1001', 'email' => 'mia@example.test', 'business' => 'bloom-spa'],
            ['name' => 'Daniel Lee', 'phone' => '+1 555 1002', 'email' => 'daniel@example.test', 'business' => 'corner-table'],
            ['name' => 'Priya Patel', 'phone' => '+1 555 1003', 'email' => 'priya@example.test', 'business' => 'clear-smile-clinic'],
            ['name' => 'Noah Smith', 'phone' => '+1 555 1004', 'email' => 'noah@example.test', 'business' => 'bloom-spa'],
            ['name' => 'Olivia Chen', 'phone' => '+1 555 1005', 'email' => 'olivia@example.test', 'business' => 'corner-table'],
        ])->map(fn (array $data) => Customer::query()->updateOrCreate(
            ['email' => $data['email']],
            [
                'user_id' => $user->id,
                'business_id' => $businesses[$data['business']]->id,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'tags' => ['demo', 'local-customer'],
                'metadata' => ['last_source' => 'demo_seed'],
            ],
        ));

        ReviewFeedback::query()->whereIn('campaign_id', [$campaigns['review']->id])->delete();
        foreach ([5, 5, 4, 4, 3, 2] as $index => $rating) {
            ReviewFeedback::query()->create([
                'user_id' => $user->id,
                'campaign_id' => $campaigns['review']->id,
                'rating' => $rating,
                'customer_name' => $customers[$index % $customers->count()]->name,
                'customer_phone' => $customers[$index % $customers->count()]->phone,
                'customer_email' => $customers[$index % $customers->count()]->email,
                'message' => $rating >= 4 ? 'Great visit and friendly staff.' : 'Wait time was longer than expected.',
                'status' => $rating <= 3 ? 'new' : 'replied',
                'created_at' => now()->subDays($index),
                'updated_at' => now()->subDays($index),
            ]);
        }

        LeadSubmission::query()->where('campaign_id', $campaigns['lead']->id)->delete();
        foreach ($customers->take(3) as $customer) {
            LeadSubmission::query()->create(['user_id' => $user->id, 'campaign_id' => $campaigns['lead']->id, 'name' => $customer->name, 'phone' => $customer->phone, 'email' => $customer->email, 'message' => 'Interested in a consultation.', 'created_at' => now()->subDays(random_int(1, 7))]);
        }

        CouponRedemption::query()->where('campaign_id', $campaigns['coupon']->id)->delete();
        foreach ($customers->skip(1)->take(3) as $customer) {
            CouponRedemption::query()->create(['user_id' => $user->id, 'campaign_id' => $campaigns['coupon']->id, 'code' => 'WEEKEND20-'.Str::upper(Str::random(6)), 'customer_name' => $customer->name, 'customer_phone' => $customer->phone, 'customer_email' => $customer->email, 'status' => random_int(0, 1) ? 'used' : 'claimed', 'used_at' => random_int(0, 1) ? now()->subDays(1) : null]);
        }

        Booking::query()->where('campaign_id', $campaigns['booking']->id)->delete();
        foreach (['pending', 'confirmed', 'completed'] as $index => $status) {
            $customer = $customers[$index];
            Booking::query()->create(['user_id' => $user->id, 'campaign_id' => $campaigns['booking']->id, 'service_id' => $service->id, 'status' => $status, 'booking_date' => now()->addDays($index + 1)->toDateString(), 'booking_time' => ['09:00', '10:00', '14:00'][$index], 'customer_name' => $customer->name, 'customer_phone' => $customer->phone, 'customer_email' => $customer->email, 'note' => 'Demo booking request.']);
        }

        FeedbackResponse::query()->where('campaign_id', $campaigns['feedback']->id)->delete();
        foreach ([5, 4, 3, 2] as $index => $rating) {
            $customer = $customers[$index];
            FeedbackResponse::query()->create(['user_id' => $user->id, 'campaign_id' => $campaigns['feedback']->id, 'rating' => $rating, 'customer_name' => $customer->name, 'customer_phone' => $customer->phone, 'customer_email' => $customer->email, 'message' => $rating >= 4 ? 'Dinner was excellent.' : 'The table was not ready on time.', 'status' => $rating <= 3 ? 'new' : 'resolved', 'resolved_at' => $rating >= 4 ? now()->subDays(1) : null]);
        }
    }
}
