<?php

namespace App\Support\BusinessDirectory;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppBusinessProfiles\Support\BusinessTypeCatalog;

class BusinessDirectoryQuery
{
    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginate(string $search = '', ?string $industryGroup = null, int $perPage = 24): LengthAwarePaginator
    {
        if (! Schema::hasTable('lb_businesses')) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        $search = trim($search);
        $industryGroup = trim((string) $industryGroup);

        $query = LocalBusiness::query()
            ->select([
                'lb_businesses.id',
                'lb_businesses.name',
                'lb_businesses.address',
                'lb_businesses.phone',
                'lb_businesses.email',
                'lb_businesses.website',
                'lb_businesses.google_maps_url',
                'lb_businesses.type',
                'lb_businesses.industry_group_code',
                'lb_businesses.industry_category_code',
                'users.name as owner_name',
            ])
            ->join('users', 'users.id', '=', 'lb_businesses.user_id')
            ->when($industryGroup !== '' && BusinessTypeCatalog::isValidGroup($industryGroup), function ($builder) use ($industryGroup): void {
                $builder->where('lb_businesses.industry_group_code', $industryGroup);
            })
            ->when($search !== '', function ($builder) use ($search): void {
                $like = '%'.$search.'%';

                $builder->where(function ($nested) use ($like): void {
                    $nested
                        ->where('lb_businesses.name', 'like', $like)
                        ->orWhere('lb_businesses.address', 'like', $like)
                        ->orWhere('lb_businesses.type', 'like', $like)
                        ->orWhere('users.name', 'like', $like);
                });
            })
            ->orderBy('lb_businesses.name');

        $this->applyPublicActivityAggregates($query);

        return $query
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (LocalBusiness $business): array => $this->present($business));
    }

    /**
     * Public-safe aggregate counts per business (no PII — totals only).
     *
     * @param  Builder<LocalBusiness>  $query
     */
    protected function applyPublicActivityAggregates(Builder $query): void
    {
        if (Schema::hasTable('lb_campaigns')) {
            $query->selectSub(
                DB::table('lb_campaigns')
                    ->selectRaw('count(*)')
                    ->whereColumn('lb_campaigns.business_id', 'lb_businesses.id'),
                'campaigns_count'
            );
        }

        if (Schema::hasTable('lb_campaigns') && Schema::hasTable('lb_qr_scans')) {
            $query->selectSub(
                DB::table('lb_qr_scans')
                    ->join('lb_campaigns', 'lb_campaigns.id', '=', 'lb_qr_scans.campaign_id')
                    ->selectRaw('count(*)')
                    ->whereColumn('lb_campaigns.business_id', 'lb_businesses.id'),
                'qr_scans_count'
            );
        }

        if (Schema::hasTable('lb_bookings')) {
            $bookings = DB::table('lb_bookings');

            if (Schema::hasTable('lb_booking_services')) {
                $bookings
                    ->leftJoin('lb_campaigns', 'lb_campaigns.id', '=', 'lb_bookings.campaign_id')
                    ->leftJoin('lb_booking_services', 'lb_booking_services.id', '=', 'lb_bookings.service_id')
                    ->where(function ($nested): void {
                        $nested
                            ->whereColumn('lb_campaigns.business_id', 'lb_businesses.id')
                            ->orWhereColumn('lb_booking_services.business_id', 'lb_businesses.id');
                    });
            } elseif (Schema::hasTable('lb_campaigns')) {
                $bookings
                    ->join('lb_campaigns', 'lb_campaigns.id', '=', 'lb_bookings.campaign_id')
                    ->whereColumn('lb_campaigns.business_id', 'lb_businesses.id');
            } else {
                $bookings->whereRaw('0 = 1');
            }

            $query->selectSub(
                $bookings->selectRaw('count(distinct lb_bookings.id)'),
                'bookings_count'
            );
        }

        if (Schema::hasTable('lb_campaigns') && Schema::hasTable('lb_coupon_redemptions')) {
            $query->selectSub(
                DB::table('lb_coupon_redemptions')
                    ->join('lb_campaigns', 'lb_campaigns.id', '=', 'lb_coupon_redemptions.campaign_id')
                    ->selectRaw('count(*)')
                    ->whereColumn('lb_campaigns.business_id', 'lb_businesses.id'),
                'coupon_codes_count'
            );
        }
    }

    /**
     * @return list<array{code: string, label: string, icon: string, is_priority: bool}>
     */
    public function industryPickerGroups(): array
    {
        return collect(BusinessTypeCatalog::taxonomyTree())
            ->map(fn (array $group): array => [
                'code' => (string) $group['code'],
                'label' => (string) $group['label'],
                'icon' => (string) ($group['icon'] ?? 'fa-store'),
                'is_priority' => (bool) ($group['is_priority'] ?? false),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{code: string, label: string}>
     */
    public function industryFilterOptions(): array
    {
        return collect(BusinessTypeCatalog::taxonomyTree())
            ->map(fn (array $group): array => [
                'code' => (string) $group['code'],
                'label' => (string) $group['label'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function present(LocalBusiness $business): array
    {
        $groupCode = (string) ($business->industry_group_code ?: BusinessTypeCatalog::FALLBACK_GROUP);
        $categoryCode = (string) ($business->industry_category_code ?: '');

        $groupMeta = BusinessTypeCatalog::groupMeta($groupCode);
        $categoryMeta = $categoryCode !== ''
            ? BusinessTypeCatalog::categoryMeta($categoryCode)
            : null;

        return [
            'name' => (string) $business->name,
            'address' => trim((string) ($business->address ?? '')),
            'industry_group_label' => (string) ($groupMeta['group_label'] ?? __('Other / Needs Classification')),
            'industry_category_label' => $categoryMeta !== null
                ? (string) ($categoryMeta['category_label'] ?? '')
                : null,
            'owner_name' => trim((string) ($business->owner_name ?? '')),
            'phone_masked' => BusinessDirectoryMask::mask($business->phone),
            'email_masked' => BusinessDirectoryMask::mask($business->email),
            'website_url' => $this->publicWebsiteUrl($business->website),
            'website_label' => $this->publicWebsiteLabel($business->website),
            'google_maps_url' => $this->publicGoogleMapsUrl($business),
            'stats' => [
                'campaigns' => (int) ($business->campaigns_count ?? 0),
                'qr_scans' => (int) ($business->qr_scans_count ?? 0),
                'bookings' => (int) ($business->bookings_count ?? 0),
                'coupon_codes' => (int) ($business->coupon_codes_count ?? 0),
            ],
        ];
    }

    protected function publicWebsiteUrl(?string $website): ?string
    {
        $website = trim((string) $website);

        if ($website === '') {
            return null;
        }

        if (! preg_match('~^https?://~i', $website)) {
            $website = 'https://'.$website;
        }

        return filter_var($website, FILTER_VALIDATE_URL) ? $website : null;
    }

    protected function publicWebsiteLabel(?string $website): ?string
    {
        $url = $this->publicWebsiteUrl($website);

        if ($url === null) {
            return null;
        }

        $label = preg_replace('~^https?://~i', '', $url) ?? $url;
        $label = preg_replace('~^www\.~i', '', $label) ?? $label;

        return rtrim($label, '/');
    }

    protected function publicGoogleMapsUrl(LocalBusiness $business): ?string
    {
        $stored = trim((string) ($business->google_maps_url ?? ''));

        if ($stored !== '' && filter_var($stored, FILTER_VALIDATE_URL)) {
            return $stored;
        }

        $address = trim((string) ($business->address ?? ''));

        if ($address === '') {
            return null;
        }

        return 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($address);
    }
}
