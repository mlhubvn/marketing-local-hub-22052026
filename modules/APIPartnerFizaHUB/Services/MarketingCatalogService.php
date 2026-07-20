<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Modules\AppBusinessProfiles\Support\BusinessTypeCatalog;

class MarketingCatalogService
{
    public function __construct(
        protected PackageAssignmentService $packages,
    ) {}

    /** @return array<string, mixed> */
    public function catalog(?string $industryCode = null): array
    {
        $packageCatalog = $this->packages->catalog();
        $definitions = (array) config('modules.apipartnerfizahub.package_definitions', []);
        $resolvedIndustry = $this->resolveIndustryCode($industryCode);

        $packages = collect($packageCatalog['packages'])
            ->map(function (array $package) use ($definitions, $resolvedIndustry): array {
                $code = (string) $package['package_code'];
                $definition = (array) ($definitions[$code] ?? []);
                $industryCodes = array_values((array) ($definition['industry_codes'] ?? []));

                if ($resolvedIndustry !== null && $industryCodes === []) {
                    $industryCodes = [$resolvedIndustry];
                }

                return $package + [
                    'description' => (string) ($definition['description'] ?? ''),
                    'features' => array_values((array) ($definition['features'] ?? [])),
                    'recommended_goal_codes' => array_values((array) ($definition['recommended_goal_codes'] ?? [])),
                    'industry_codes' => $industryCodes,
                ];
            })
            ->values()
            ->all();

        return [
            'max_goal_selection' => 3,
            'default_package_code' => $packageCatalog['default_package_code'],
            'marketing_goals' => collect((array) config('modules.apipartnerfizahub.marketing_goals', []))
                ->map(fn (array $goal, string $code): array => ['code' => $code] + $goal)
                ->values()
                ->all(),
            'industries' => BusinessTypeCatalog::taxonomyTree(),
            'packages' => $packages,
        ];
    }

    private function resolveIndustryCode(?string $industryCode): ?string
    {
        $industryCode = trim((string) $industryCode);

        if ($industryCode === '') {
            return null;
        }

        return (string) config('modules.apipartnerfizahub.industry_aliases.'.$industryCode, $industryCode);
    }
}
