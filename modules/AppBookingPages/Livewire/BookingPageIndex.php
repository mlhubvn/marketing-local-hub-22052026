<?php

namespace Modules\AppBookingPages\Livewire;

use App\Support\Plans\PlanLimitGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBookingPages\Models\BookingService;
use Modules\AppBookingPages\Support\BookingAvailability;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppLandingPages\Support\Concerns\ManagesGrowthToolPageDesign;
use Modules\AppLandingPages\Support\LandingPageFactory;
use Modules\AppLandingPages\Support\PageTemplateCatalog;
use Modules\AppQRCampaigns\Models\QrCampaign;

#[Title('Booking Pages')]
class BookingPageIndex extends Component
{
    use WithPagination;
    use ManagesGrowthToolPageDesign;

    public string $business_id = '';
    public ?int $editing_service_id = null;
    public string $service_name = '';
    public string $page_name = '';
    public int $duration_minutes = 60;
    public string $price = '';
    public string $description = '';
    public bool $use_business_hours = true;
    public int $slot_interval = 30;
    public int $buffer_before = 0;
    public int $buffer_after = 0;
    public array $service_hours = [];
    public int $servicesPerPage = 10;
    public int $pagesPerPage = 10;
    public int $bookingsPerPage = 10;
    public ?string $statusMessage = null;

    public function mount(): void
    {
        $this->initializePageDesign('booking');
        $this->service_hours = app(BookingAvailability::class)->defaultWeeklyHours();
    }

    public function updatedServicesPerPage(): void
    {
        if (! in_array($this->servicesPerPage, [10, 25, 50], true)) {
            $this->servicesPerPage = 10;
        }

        $this->resetPage('servicesPage');
    }

    public function updatedPagesPerPage(): void
    {
        if (! in_array($this->pagesPerPage, [10, 25, 50], true)) {
            $this->pagesPerPage = 10;
        }

        $this->resetPage('bookingPagesPage');
    }

    public function updatedBookingsPerPage(): void
    {
        if (! in_array($this->bookingsPerPage, [10, 25, 50], true)) {
            $this->bookingsPerPage = 10;
        }

        $this->resetPage('bookingsPage');
    }

    public function createService(): void
    {
        $this->resetServiceForm();
        $this->dispatch('booking-service-editing');
    }

    public function editService(int $id): void
    {
        $service = BookingService::query()
            ->with('business')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $this->resetValidation();
        $this->editing_service_id = $service->id;
        $this->business_id = (string) $service->business_id;
        $this->service_name = (string) $service->name;
        $this->duration_minutes = (int) $service->duration_minutes;
        $this->price = $service->price !== null ? (string) $service->price : '';
        $this->description = (string) $service->description;
        $this->use_business_hours = (bool) $service->use_business_hours;
        $this->slot_interval = (int) ($service->slot_interval ?: 30);
        $this->buffer_before = (int) ($service->buffer_before ?: 0);
        $this->buffer_after = (int) ($service->buffer_after ?: 0);
        $this->service_hours = $this->use_business_hours
            ? app(BookingAvailability::class)->defaultWeeklyHours()
            : app(BookingAvailability::class)->weeklyHoursForService($service);

        $this->dispatch('booking-service-editing');
    }

    public function saveService(): void
    {
        $payload = $this->validate([
            'business_id' => ['required', 'integer'],
            'service_name' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'use_business_hours' => ['boolean'],
            'slot_interval' => ['required', 'integer', 'in:15,30,60'],
            'buffer_before' => ['required', 'integer', 'in:0,5,10,15,30'],
            'buffer_after' => ['required', 'integer', 'in:0,5,10,15,30'],
            'service_hours' => ['array'],
            'service_hours.*.is_closed' => ['boolean'],
            'service_hours.*.open_time' => ['nullable', 'date_format:H:i'],
            'service_hours.*.close_time' => ['nullable', 'date_format:H:i'],
        ]);

        LocalBusiness::query()->where('user_id', auth()->id())->findOrFail((int) $payload['business_id']);

        $attributes = [
            'business_id' => (int) $payload['business_id'],
            'name' => $payload['service_name'],
            'duration_minutes' => $payload['duration_minutes'],
            'price' => $payload['price'] !== '' ? $payload['price'] : null,
            'description' => $payload['description'],
            'available_days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'],
            'time_slots' => ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'],
            'use_business_hours' => (bool) $payload['use_business_hours'],
            'slot_interval' => (int) $payload['slot_interval'],
            'buffer_before' => (int) $payload['buffer_before'],
            'buffer_after' => (int) $payload['buffer_after'],
            'service_hours' => (bool) $payload['use_business_hours'] ? null : $this->normalizedServiceHours(),
        ];

        if ($this->editing_service_id) {
            BookingService::query()
                ->where('user_id', auth()->id())
                ->whereKey($this->editing_service_id)
                ->firstOrFail()
                ->update($attributes);

            $this->statusMessage = __('Booking service updated.');
        } else {
            BookingService::query()->create([
                'user_id' => auth()->id(),
                ...$attributes,
            ]);

            $this->statusMessage = __('Booking service created.');
        }

        $this->resetServiceForm();
        $this->dispatch('booking-service-saved');
    }

    public function saveBookingPage(): void
    {
        $payload = $this->validate([
            'business_id' => ['required', 'integer'],
            'page_name' => ['nullable', 'string', 'max:255'],
        ]);

        LocalBusiness::query()->where('user_id', auth()->id())->findOrFail((int) $payload['business_id']);

        $guard = app(PlanLimitGuard::class);
        $guard->ensureCampaignCanBeCreated(auth()->user());
        $guard->ensureLandingPageCanBeCreated(auth()->user());

        $pageSettings = $this->defaultPageDesignSettings('booking');
        $name = filled($payload['page_name']) ? $payload['page_name'] : __('Public Booking Page');
        $campaign = QrCampaign::query()->create([
            'user_id' => auth()->id(),
            'business_id' => (int) $payload['business_id'],
            'type' => 'booking',
            'slug' => $this->uniqueSlug($name),
            'name' => $name,
            'settings' => array_merge(['headline' => 'Book an appointment'], $pageSettings),
            'published_at' => now(),
        ]);

        $page = app(LandingPageFactory::class)->syncFromCampaign($campaign);

        $this->page_name = '';
        $this->resetPageDesign('booking');
        $this->statusMessage = __('Public booking page created.');
        $this->setCreatedGrowthToolActions($page, $campaign, __('Public booking page created and QR code generated.'));
        $this->dispatch('booking-page-saved');

        $this->redirect(route('portal.landing-pages', [
            'edit' => $page->id,
            'return' => route('portal.booking-pages'),
        ]), navigate: true);
    }

    public function toggleService(int $id): void
    {
        $service = BookingService::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $service->forceFill(['is_active' => ! $service->is_active])->save();

        $this->statusMessage = $service->is_active ? __('Service activated.') : __('Service paused.');
    }

    public function deleteService(int $id): void
    {
        BookingService::query()
            ->where('user_id', auth()->id())
            ->whereKey($id)
            ->delete();

        $this->statusMessage = __('Booking service deleted.');
    }

    public function toggleBookingPage(int $id): void
    {
        $campaign = QrCampaign::query()
            ->where('user_id', auth()->id())
            ->where('type', 'booking')
            ->findOrFail($id);

        $campaign->forceFill(['published_at' => $campaign->published_at ? null : now()])->save();

        $this->statusMessage = $campaign->published_at ? __('Booking page published.') : __('Booking page paused.');
    }

    public function deleteBookingPage(int $id): void
    {
        LandingPage::query()
            ->where('user_id', auth()->id())
            ->where('campaign_id', $id)
            ->delete();

        QrCampaign::query()
            ->where('user_id', auth()->id())
            ->where('type', 'booking')
            ->whereKey($id)
            ->delete();

        $this->statusMessage = __('Booking page deleted.');
    }

    public function setBookingStatus(int $id, string $status): void
    {
        abort_unless(in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'], true), 422);

        Booking::query()
            ->where('user_id', auth()->id())
            ->whereKey($id)
            ->update(['status' => $status]);

        $this->statusMessage = __('Booking status updated.');
    }

    public function deleteBooking(int $id): void
    {
        Booking::query()
            ->where('user_id', auth()->id())
            ->whereKey($id)
            ->delete();

        $this->statusMessage = __('Booking deleted.');
        $this->resetPage('bookingsPage');
    }

    public function render(): View
    {
        $businesses = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get();

        if ($this->business_id === '' && $businesses->isNotEmpty()) {
            $this->business_id = (string) $businesses->first()->id;
        }

        $allServices = BookingService::query()->where('user_id', auth()->id())->latest()->get();
        $services = BookingService::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate($this->servicesPerPage, ['*'], 'servicesPage');
        $allBookings = Booking::query()->where('user_id', auth()->id())->get();
        $bookings = Booking::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate($this->bookingsPerPage, ['*'], 'bookingsPage');
        $allBookingCampaigns = QrCampaign::query()
            ->with('business', 'landingPage')
            ->withCount('scans')
            ->where('user_id', auth()->id())
            ->where('type', 'booking')
            ->latest()
            ->get();
        $latestBookingCampaign = $allBookingCampaigns->first();
        $bookingCampaigns = QrCampaign::query()
            ->with('business', 'landingPage')
            ->withCount('scans')
            ->where('user_id', auth()->id())
            ->where('type', 'booking')
            ->latest()
            ->paginate($this->pagesPerPage, ['*'], 'bookingPagesPage');

        return view('appbookingpages::index', [
            'businesses' => $businesses,
            'businessNames' => $businesses->pluck('name', 'id'),
            'services' => $services,
            'bookings' => $bookings,
            'bookingCampaigns' => $bookingCampaigns,
            'latestBookingCampaign' => $latestBookingCampaign,
            'bookingTemplates' => PageTemplateCatalog::forType('booking'),
            'stats' => [
                'services' => $allServices->count(),
                'active_services' => $allServices->where('is_active', true)->count(),
                'booking_pages' => $allBookingCampaigns->count(),
                'visits' => $allBookingCampaigns->sum('scans_count'),
                'bookings' => $allBookings->count(),
                'pending' => $allBookings->where('status', 'pending')->count(),
                'confirmed' => $allBookings->where('status', 'confirmed')->count(),
                'cancelled' => $allBookings->where('status', 'cancelled')->count(),
                'completed' => $allBookings->where('status', 'completed')->count(),
            ],
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Booking Pages'),
        ]);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'booking';
        $slug = $base;
        $counter = 2;

        while (QrCampaign::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    protected function resetServiceForm(): void
    {
        $this->resetValidation();
        $this->editing_service_id = null;
        $this->service_name = '';
        $this->price = '';
        $this->description = '';
        $this->duration_minutes = 60;
        $this->use_business_hours = true;
        $this->slot_interval = 30;
        $this->buffer_before = 0;
        $this->buffer_after = 0;
        $this->service_hours = app(BookingAvailability::class)->defaultWeeklyHours();
    }

    protected function normalizedServiceHours(): array
    {
        $fallback = app(BookingAvailability::class)->defaultWeeklyHours();

        return collect(BookingAvailability::DAYS)
            ->mapWithKeys(function (string $day) use ($fallback) {
                $hours = (array) ($this->service_hours[$day] ?? []);

                return [$day => [
                    'is_closed' => (bool) ($hours['is_closed'] ?? false),
                    'open_time' => (string) ($hours['open_time'] ?? $fallback[$day]['open_time']),
                    'close_time' => (string) ($hours['close_time'] ?? $fallback[$day]['close_time']),
                ]];
            })
            ->all();
    }
}
