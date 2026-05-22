<?php

namespace Modules\AppBookingPages\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Support\GrowthToolNotifier;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBookingPages\Models\BookingService;
use Modules\AppBookingPages\Support\BookingAvailability;
use Modules\AppCustomers\Support\CustomerUpserter;
use Modules\AppQRCampaigns\Models\QrCampaign;

class BookingSubmissionController extends Controller
{
    public function store(Request $request, QrCampaign $campaign): RedirectResponse
    {
        abort_unless($campaign->type === 'booking', 404);

        $payload = $request->validate([
            'service_id' => ['required', 'integer'],
            'booking_date' => ['required', 'date'],
            'booking_time' => ['required', 'string', 'max:10'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'min:6', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $service = BookingService::query()
            ->where('business_id', $campaign->business_id)
            ->where('is_active', true)
            ->findOrFail((int) $payload['service_id']);

        if (! app(BookingAvailability::class)->isSlotAvailable($service, (string) $payload['booking_date'], (string) $payload['booking_time'])) {
            return back()
                ->withErrors(['booking_time' => __('Selected time is outside business hours or no longer available.')])
                ->withInput();
        }

        app(CustomerUpserter::class)->fromCampaign($campaign, $payload, 'booking_page');
        Booking::query()->create([
            'user_id' => $campaign->user_id,
            'campaign_id' => $campaign->id,
            ...$payload,
        ]);
        app(GrowthToolNotifier::class)->bookingCreated(
            $campaign,
            (string) $payload['customer_name'],
            (string) $payload['booking_date'],
            (string) $payload['booking_time']
        );

        return back()->with('booking_saved', true);
    }
}
