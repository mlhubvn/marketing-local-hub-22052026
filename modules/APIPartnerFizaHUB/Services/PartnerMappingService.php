<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Illuminate\Support\Carbon;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\AppBusinessProfiles\Support\BusinessTypeCatalog;

class PartnerMappingService
{
    public function partnerCode(): string
    {
        return (string) config('modules.apipartnerfizahub.partner_code', 'fizahub');
    }

    public function resolvePlanSlug(string $packageCode): ?string
    {
        $map = (array) config('modules.apipartnerfizahub.package_map', []);

        return isset($map[$packageCode]) ? (string) $map[$packageCode] : null;
    }

    public function resolvePlan(string $packageCode): ?AdminPlan
    {
        $slug = $this->resolvePlanSlug($packageCode);

        if ($slug === null || $slug === '') {
            return null;
        }

        return AdminPlan::query()
            ->where('slug', $slug)
            ->where('status', true)
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveIndustry(string $industryInput): array
    {
        $aliases = (array) config('modules.apipartnerfizahub.industry_aliases', []);
        $categoryCode = $aliases[$industryInput] ?? $industryInput;

        return BusinessTypeCatalog::resolveSelection(null, (string) $categoryCode);
    }

    public function normalizeExternalId(?string $value): string
    {
        return trim((string) $value);
    }

    public function normalizeEmail(?string $value): string
    {
        return strtolower(trim((string) $value));
    }

    public function normalizeIdentifier(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return preg_replace('/[\s\p{P}]+/u', '', $value) ?: null;
    }

    public function normalizeVerifiedAt(?string $value): ?Carbon
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return Carbon::parse($value)->utc();
    }

    /**
     * @param  array<string, mixed>  $owner
     * @param  array<string, mixed>  $business
     * @return list<array{type: string, id: int}>
     */
    public function detectDuplicates(
        string $externalBusinessId,
        array $owner,
        array $business
    ): array {
        $duplicates = [];
        $partnerCode = $this->partnerCode();
        $email = $this->normalizeEmail($owner['email'] ?? null);
        $taxCode = $this->normalizeIdentifier($business['tax_code'] ?? null);
        $license = $this->normalizeIdentifier($business['business_license_number'] ?? null);

        if ($email !== '') {
            $emailUser = User::query()->where('email', $email)->first();

            if ($emailUser) {
                $alreadyMappedHere = PartnerIntegration::query()
                    ->where('partner_code', $partnerCode)
                    ->where('external_business_id', $externalBusinessId)
                    ->where('mlhub_user_id', $emailUser->id)
                    ->exists();

                if (! $alreadyMappedHere) {
                    $duplicates[] = [
                        'type' => 'email',
                        'id' => (int) $emailUser->id,
                    ];
                }
            }
        }

        if ($taxCode !== null) {
            $taxMatch = PartnerIntegration::query()
                ->where('partner_code', $partnerCode)
                ->where('external_business_id', '!=', $externalBusinessId)
                ->where('metadata->tax_code', $taxCode)
                ->first();

            if ($taxMatch) {
                $duplicates[] = [
                    'type' => 'tax_code',
                    'id' => (int) $taxMatch->id,
                ];
            }
        }

        if ($license !== null) {
            $licenseMatch = PartnerIntegration::query()
                ->where('partner_code', $partnerCode)
                ->where('external_business_id', '!=', $externalBusinessId)
                ->where('metadata->business_license_number', $license)
                ->first();

            if ($licenseMatch) {
                $duplicates[] = [
                    'type' => 'business_license_number',
                    'id' => (int) $licenseMatch->id,
                ];
            }
        }

        return $duplicates;
    }

    public function deterministicUsername(string $externalBusinessId): string
    {
        return 'fizahub_'.substr(hash('sha256', 'fizahub|'.$externalBusinessId), 0, 12);
    }
}
