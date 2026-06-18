<?php

namespace Modules\AppBusinessProfiles\Livewire\Concerns;

use Illuminate\Validation\Rule;
use Modules\AppBookingPages\Support\BookingAvailability;
use Modules\AppBusinessProfiles\Support\BusinessTypeCatalog;

trait ManagesBusinessForm
{
    public string $name = '';

    /**
     * Legacy compatibility field — still written to lb_businesses.type, but it
     * is now derived from the selected industry category, not edited directly.
     */
    public string $type = '';

    public string $industry_group_code = '';

    public string $industry_category_code = '';

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
            'industry_category_code' => ['required', 'string', Rule::in(BusinessTypeCatalog::categoryCodes())],
            'industry_group_code' => ['nullable', 'string', 'max:64'],
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

    protected function validationAttributes(): array
    {
        return [
            'industry_category_code' => __('Specific industry'),
            'industry_group_code' => __('Main industry group'),
        ];
    }

    protected function businessPayload(): array
    {
        $payload = $this->validate($this->rules());

        $resolved = BusinessTypeCatalog::resolveSelection(
            $payload['industry_group_code'] ?? null,
            $payload['industry_category_code'] ?? null
        );

        // Keep local state in sync (e.g. fallback was applied server-side).
        $this->industry_group_code = $resolved['group_code'];
        $this->industry_category_code = $resolved['category_code'];
        $this->type = $resolved['legacy_type'];

        $payload['industry_group_code'] = $resolved['group_code'];
        $payload['industry_category_code'] = $resolved['category_code'];
        $payload['taxonomy_version'] = $resolved['taxonomy_version'];
        $payload['industry_metadata'] = $resolved['metadata_snapshot'];
        $payload['type'] = $resolved['legacy_type'];
        $payload['opening_hours'] = $this->normalizedOpeningHours();

        return $payload;
    }

    /**
     * Hydrate the industry selection, inferring group/category for legacy
     * businesses that only have lb_businesses.type.
     */
    protected function hydrateIndustrySelection(?string $type, ?string $groupCode, ?string $categoryCode): void
    {
        $this->type = (string) $type;
        $groupCode = (string) $groupCode;
        $categoryCode = (string) $categoryCode;

        if ($categoryCode === '' || ! BusinessTypeCatalog::isValidCategory($categoryCode)) {
            $inferred = BusinessTypeCatalog::inferFromLegacyType($this->type);
            $groupCode = $inferred['group_code'];
            $categoryCode = $inferred['category_code'];
        }

        $resolved = BusinessTypeCatalog::resolveSelection($groupCode, $categoryCode);

        $this->industry_group_code = $resolved['group_code'];
        $this->industry_category_code = $resolved['category_code'];
        $this->type = $resolved['legacy_type'];
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

    protected function industryTaxonomy(): array
    {
        return BusinessTypeCatalog::taxonomyTree();
    }

    protected function priorityGroupCodes(): array
    {
        return BusinessTypeCatalog::priorityGroupCodes();
    }
}
