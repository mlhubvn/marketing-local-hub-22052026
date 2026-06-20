<?php

namespace App\Support\BusinessDirectory;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

        return $query
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (LocalBusiness $business): array => $this->present($business));
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
            'website_masked' => BusinessDirectoryMask::mask($business->website),
        ];
    }
}
