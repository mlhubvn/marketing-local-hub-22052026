<?php

namespace Modules\AppBookingPages\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBookingPages\Models\BookingService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

class BookingAvailability
{
    public const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    public function weeklyHoursForBusiness(?LocalBusiness $business): array
    {
        return $this->normalizeWeeklyHours($business?->opening_hours ?: [], [
            'mon' => ['is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'],
            'tue' => ['is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'],
            'wed' => ['is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'],
            'thu' => ['is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'],
            'fri' => ['is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'],
            'sat' => ['is_closed' => false, 'open_time' => '10:00', 'close_time' => '15:00'],
            'sun' => ['is_closed' => true, 'open_time' => '09:00', 'close_time' => '18:00'],
        ]);
    }

    public function weeklyHoursForService(BookingService $service): array
    {
        if (! (bool) $service->use_business_hours && is_array($service->service_hours) && $service->service_hours !== []) {
            return $this->normalizeWeeklyHours($service->service_hours, $this->weeklyHoursForBusiness($service->business));
        }

        return $this->weeklyHoursForBusiness($service->business);
    }

    public function slotsForDate(BookingService $service, string $date): array
    {
        $day = $this->dayKey($date);
        $hours = $this->weeklyHoursForService($service)[$day] ?? null;

        if (! $hours || (bool) ($hours['is_closed'] ?? true)) {
            return [];
        }

        $open = $this->timeOnDate($date, (string) $hours['open_time']);
        $close = $this->timeOnDate($date, (string) $hours['close_time']);

        if ($close->lessThanOrEqualTo($open)) {
            return [];
        }

        $duration = max(5, (int) $service->duration_minutes);
        $bufferBefore = max(0, (int) $service->buffer_before);
        $bufferAfter = max(0, (int) $service->buffer_after);
        $interval = max(5, (int) ($service->slot_interval ?: 30));
        $cursor = $open->copy()->addMinutes($bufferBefore);
        $slots = [];

        while ($cursor->copy()->addMinutes($duration + $bufferAfter)->lessThanOrEqualTo($close)) {
            $slots[] = $cursor->format('H:i');
            $cursor->addMinutes($interval);
        }

        return $this->removeBookedSlots($service, $date, $slots);
    }

    public function availabilityMap(Collection $services, int $days = 90): array
    {
        $today = now()->startOfDay();

        return $services->mapWithKeys(function (BookingService $service) use ($today, $days) {
            $dates = [];

            for ($offset = 0; $offset <= $days; $offset++) {
                $date = $today->copy()->addDays($offset)->toDateString();
                $slots = $this->slotsForDate($service, $date);

                if ($slots !== []) {
                    $dates[$date] = $slots;
                }
            }

            return [$service->id => $dates];
        })->all();
    }

    public function isSlotAvailable(BookingService $service, string $date, string $time): bool
    {
        return in_array($time, $this->slotsForDate($service, $date), true);
    }

    public function defaultWeeklyHours(): array
    {
        return $this->weeklyHoursForBusiness(null);
    }

    protected function removeBookedSlots(BookingService $service, string $date, array $slots): array
    {
        $limit = max(1, (int) $service->max_bookings_per_slot);
        $booked = Booking::query()
            ->where('service_id', $service->id)
            ->whereDate('booking_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw('booking_time, count(*) as total')
            ->groupBy('booking_time')
            ->pluck('total', 'booking_time');

        return array_values(array_filter($slots, fn (string $slot) => (int) ($booked[$slot] ?? 0) < $limit));
    }

    protected function normalizeWeeklyHours(mixed $hours, array $fallback): array
    {
        $normalized = $fallback;
        $hours = $this->expandLegacyHours($hours);

        foreach ($hours as $key => $value) {
            $days = $this->expandDayKey((string) $key);

            foreach ($days as $day) {
                $normalized[$day] = $this->normalizeDayHours($value, $normalized[$day] ?? $fallback[$day]);
            }
        }

        return $normalized;
    }

    protected function expandLegacyHours(mixed $hours): array
    {
        if (is_array($hours)) {
            return $hours;
        }

        if (! is_string($hours)) {
            return [];
        }

        $expanded = [];
        foreach (preg_split('/\R+/', $hours) ?: [] as $line) {
            if (preg_match('/([A-Za-z]{3,9}(?:\s*-\s*[A-Za-z]{3,9})?)\s+(\d{1,2}:\d{2}\s*-\s*\d{1,2}:\d{2})/', trim($line), $matches)) {
                $expanded[strtolower(str_replace(' ', '', $matches[1]))] = $matches[2];
            }
        }

        return $expanded;
    }

    protected function normalizeDayHours(mixed $value, array $fallback): array
    {
        if (is_string($value) && preg_match('/(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/', $value, $matches)) {
            return ['is_closed' => false, 'open_time' => $matches[1], 'close_time' => $matches[2]];
        }

        if (is_array($value)) {
            return [
                'is_closed' => (bool) ($value['is_closed'] ?? false),
                'open_time' => $this->validTime((string) ($value['open_time'] ?? '')) ?: $fallback['open_time'],
                'close_time' => $this->validTime((string) ($value['close_time'] ?? '')) ?: $fallback['close_time'],
            ];
        }

        return $fallback;
    }

    protected function expandDayKey(string $key): array
    {
        $key = strtolower(str_replace(['day', ' '], '', $key));
        $aliases = ['monday' => 'mon', 'tuesday' => 'tue', 'wednesday' => 'wed', 'thursday' => 'thu', 'friday' => 'fri', 'saturday' => 'sat', 'sunday' => 'sun'];
        $key = $aliases[$key] ?? $key;

        if ($key === 'mon-fri') {
            return ['mon', 'tue', 'wed', 'thu', 'fri'];
        }

        if ($key === 'sat-sun') {
            return ['sat', 'sun'];
        }

        return in_array($key, self::DAYS, true) ? [$key] : [];
    }

    protected function dayKey(string $date): string
    {
        return strtolower(Carbon::parse($date)->format('D'));
    }

    protected function timeOnDate(string $date, string $time): Carbon
    {
        return Carbon::parse($date.' '.$time);
    }

    protected function validTime(string $time): ?string
    {
        return preg_match('/^\d{1,2}:\d{2}$/', $time) ? $time : null;
    }
}
