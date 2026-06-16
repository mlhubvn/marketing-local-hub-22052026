<?php

namespace Modules\AppBusinessProfiles\Livewire\Concerns;

use Modules\AppBookingPages\Support\BookingAvailability;
use Modules\AppBusinessProfiles\Support\BusinessTypeCatalog;

trait ManagesBusinessForm
{
    public string $name = '';

    public string $type = 'Restaurant';

    public string $phone = '';

    public string $email = '';

    public string $website = '';

    public string $address = '';

    public string $google_maps_url = '';

    public array $opening_hours = [
        'mon' => ['is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'],
        'tue' => ['is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'],
        'wed' => ['is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'],
        'thu' => ['is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'],
        'fri' => ['is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'],
        'sat' => ['is_closed' => false, 'open_time' => '10:00', 'close_time' => '15:00'],
        'sun' => ['is_closed' => true, 'open_time' => '09:00', 'close_time' => '18:00'],
    ];

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:60'],
            'phone' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:500'],
            'address' => ['nullable', 'string', 'max:1000'],
            'google_maps_url' => ['nullable', 'url', 'max:1000'],
            'opening_hours' => ['array'],
            'opening_hours.*.is_closed' => ['boolean'],
            'opening_hours.*.open_time' => ['nullable', 'date_format:H:i'],
            'opening_hours.*.close_time' => ['nullable', 'date_format:H:i'],
        ];
    }

    protected function businessPayload(): array
    {
        $payload = $this->validate($this->rules());
        $payload['type'] = BusinessTypeCatalog::normalizeType($payload['type'] ?? 'Other');
        $payload['opening_hours'] = $this->normalizedOpeningHours();

        return $payload;
    }

    protected function setOpeningHours(mixed $hours): void
    {
        $this->opening_hours = app(BookingAvailability::class)->weeklyHoursForBusiness(
            new \Modules\AppBusinessProfiles\Models\LocalBusiness(['opening_hours' => $hours])
        );
    }

    protected function normalizedOpeningHours(): array
    {
        $fallback = app(BookingAvailability::class)->defaultWeeklyHours();

        return collect(BookingAvailability::DAYS)
            ->mapWithKeys(function (string $day) use ($fallback) {
                $hours = (array) ($this->opening_hours[$day] ?? []);

                return [$day => [
                    'is_closed' => (bool) ($hours['is_closed'] ?? false),
                    'open_time' => (string) ($hours['open_time'] ?? $fallback[$day]['open_time']),
                    'close_time' => (string) ($hours['close_time'] ?? $fallback[$day]['close_time']),
                ]];
            })
            ->all();
    }

    protected function typeOptions(): array
    {
        return BusinessTypeCatalog::typeOptions();
    }

    protected function groupedTypeOptions(): array
    {
        return BusinessTypeCatalog::groupedOptions();
    }

    protected function popularTypeOptions(): array
    {
        return BusinessTypeCatalog::popularOptions();
    }

    protected function typeMetadata(?string $type = null): array
    {
        return BusinessTypeCatalog::metadataFor($type ?? $this->type);
    }
}
