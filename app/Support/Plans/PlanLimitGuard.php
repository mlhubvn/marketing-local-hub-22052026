<?php

namespace App\Support\Plans;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Modules\AdminPlans\Support\CatalogLocalization;
use Modules\AdminUser\Models\User;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppMarketingTemplates\Models\MarketingTemplate;
use Modules\AppQRCampaigns\Models\QrCampaign;

class PlanLimitGuard
{
    public function ensureLocalBoostEnabled(?User $user): void
    {
        if (! $user) {
            return;
        }

        if (! $user->canUsePlanFeature('localboost')) {
            throw ValidationException::withMessages([
                'plan' => __('Your current plan does not include LocalBoost AI.'),
            ]);
        }
    }

    public function ensureBusinessCanBeCreated(?User $user): void
    {
        $this->ensureLocalBoostEnabled($user);

        $this->ensureBelowLimit(
            $user,
            'max_businesses',
            LocalBusiness::query()->where('user_id', $user?->id)->count(),
            __('businesses')
        );
    }

    public function ensureCampaignCanBeCreated(?User $user): void
    {
        $this->ensureLocalBoostEnabled($user);

        $this->ensureBelowLimit(
            $user,
            'max_campaigns',
            $this->campaignCount($user),
            __('campaigns')
        );

        $this->ensureQrCodeCanBeCreated($user);
    }

    public function ensureLandingPageCanBeCreated(?User $user): void
    {
        $this->ensureLocalBoostEnabled($user);

        $this->ensureBelowLimit(
            $user,
            'max_landing_pages',
            LandingPage::query()->where('user_id', $user?->id)->count(),
            __('landing pages')
        );
    }

    public function ensureQrCodeCanBeCreated(?User $user): void
    {
        $this->ensureLocalBoostEnabled($user);

        $this->ensureBelowLimit(
            $user,
            'max_qr_codes',
            QrCampaign::query()->where('user_id', $user?->id)->count(),
            __('QR codes')
        );
    }

    public function ensureTemplateCanBeCreated(?User $user): void
    {
        $this->ensureLocalBoostEnabled($user);

        $this->ensureBelowLimit(
            $user,
            'max_templates',
            MarketingTemplate::query()
                ->where('user_id', $user?->id)
                ->where('is_system', false)
                ->count(),
            __('custom templates')
        );
    }

    public function usageSummary(?User $user): array
    {
        $creditSummary = $user?->creditSummary() ?? ['used' => 0, 'limit' => -1, 'remaining' => null, 'unlimited' => true, 'usage_percent' => 0];
        $rows = [
            'credits' => [
                'label' => 'AI credits',
                'key' => 'credits_usage_limit',
                'used' => (int) ($creditSummary['used'] ?? 0),
                'limit' => is_numeric($creditSummary['limit'] ?? null) ? (int) $creditSummary['limit'] : -1,
                'remaining' => $creditSummary['remaining'] ?? null,
                'unlimited' => (bool) ($creditSummary['unlimited'] ?? true),
                'percent' => ! (bool) ($creditSummary['unlimited'] ?? true) && is_numeric($creditSummary['limit'] ?? null) && (int) $creditSummary['limit'] > 0
                    ? min(100, (int) round(((int) ($creditSummary['used'] ?? 0) / (int) $creditSummary['limit']) * 100))
                    : 100,
                'is_full' => ! (bool) ($creditSummary['unlimited'] ?? true) && (int) ($creditSummary['remaining'] ?? 0) <= 0,
                'precomputed' => true,
            ],
            'businesses' => [
                'label' => 'Businesses',
                'key' => 'max_businesses',
                'used' => LocalBusiness::query()->where('user_id', $user?->id)->count(),
            ],
            'campaigns' => [
                'label' => 'Campaigns',
                'key' => 'max_campaigns',
                'used' => $this->campaignCount($user),
            ],
            'landing_pages' => [
                'label' => 'Landing pages',
                'key' => 'max_landing_pages',
                'used' => LandingPage::query()->where('user_id', $user?->id)->count(),
            ],
            'qr_codes' => [
                'label' => 'QR codes',
                'key' => 'max_qr_codes',
                'used' => QrCampaign::query()->where('user_id', $user?->id)->count(),
            ],
            'templates' => [
                'label' => 'Custom templates',
                'key' => 'max_templates',
                'used' => MarketingTemplate::query()
                    ->where('user_id', $user?->id)
                    ->where('is_system', false)
                    ->count(),
            ],
        ];

        $customDomainModel = 'Modules\\AppCustomDomain\\Models\\AppCustomDomain';

        if (class_exists($customDomainModel) && $user?->canUsePlanFeature('qr_custom_domains')) {
            $rows['custom_domains'] = [
                'label' => 'Custom domains',
                'key' => 'max_custom_domains',
                'used' => $customDomainModel::query()->where('owner_user_id', $user?->id)->count(),
            ];
        }

        $emailAutomationModel = 'Modules\\AppEmailAutomation\\Models\\EmailAutomation';
        $emailTemplateModel = 'Modules\\AppEmailAutomation\\Models\\EmailTemplate';
        $emailLogModel = 'Modules\\AppEmailAutomation\\Models\\EmailAutomationLog';

        if (
            class_exists($emailAutomationModel)
            && class_exists($emailTemplateModel)
            && class_exists($emailLogModel)
            && $user?->canUsePlanFeature('email_automation')
        ) {
            $rows['email_automations'] = [
                'label' => 'Email automations',
                'key' => 'max_email_automations',
                'used' => $this->countOwnedRows($emailAutomationModel, 'lb_email_automations', $user),
            ];

            $rows['email_templates'] = [
                'label' => 'Email templates',
                'key' => 'max_email_templates',
                'used' => $this->ownedQuery($emailTemplateModel, 'lb_email_templates', $user)
                    ->where('is_system', false)
                    ->count(),
            ];

            $rows['emails_this_month'] = [
                'label' => 'Emails this month',
                'key' => 'emails_per_month',
                'used' => $this->ownedQuery($emailLogModel, 'lb_email_automation_logs', $user)
                    ->whereIn('status', ['queued', 'sent', 'opened', 'clicked'])
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count(),
            ];
        }

        $googleConnectionModel = 'Modules\\AppGoogleBusiness\\Models\\GoogleBusinessConnection';
        $googleLocationModel = 'Modules\\AppGoogleBusiness\\Models\\GoogleBusinessLocation';

        $googleAccessClass = 'Modules\\AppGoogleBusiness\\Support\\GoogleBusinessAccess';

        if (
            class_exists($googleConnectionModel)
            && class_exists($googleLocationModel)
            && class_exists($googleAccessClass)
            && $user?->canUsePlanFeature('google_business')
        ) {
            $connectionUsed = $googleConnectionModel::query()->where('team_id', $user?->id)->count();
            $connectionLimit = $googleAccessClass::connectionLimit($user);
            $locationUsed = $googleLocationModel::query()->where('team_id', $user?->id)->count();
            $locationLimit = $googleAccessClass::locationLimit($user);

            $rows['google_business_connections'] = [
                'label' => 'Google connections',
                'key' => 'max_google_business_connections',
                'used' => $connectionUsed,
                'limit' => $connectionLimit,
                'remaining' => $connectionLimit < 0 ? null : max(0, $connectionLimit - $connectionUsed),
                'unlimited' => $connectionLimit < 0,
                'percent' => $connectionLimit > 0 ? min(100, (int) round(($connectionUsed / $connectionLimit) * 100)) : ($connectionLimit < 0 ? 100 : 0),
                'is_full' => $connectionLimit >= 0 && $connectionUsed >= $connectionLimit,
                'precomputed' => true,
            ];

            $rows['google_business_locations'] = [
                'label' => 'Google locations',
                'key' => 'max_google_business_locations',
                'used' => $locationUsed,
                'limit' => $locationLimit,
                'remaining' => $locationLimit < 0 ? null : max(0, $locationLimit - $locationUsed),
                'unlimited' => $locationLimit < 0,
                'percent' => $locationLimit > 0 ? min(100, (int) round(($locationUsed / $locationLimit) * 100)) : ($locationLimit < 0 ? 100 : 0),
                'is_full' => $locationLimit >= 0 && $locationUsed >= $locationLimit,
                'precomputed' => true,
            ];
        }

        return collect($rows)
            ->map(function (array $row) use ($user): array {
                $row['label'] = CatalogLocalization::resolve((string) ($row['label'] ?? ''));

                if (($row['precomputed'] ?? false) === true) {
                    return $row;
                }

                $limit = (int) ($user?->planLimit($row['key'], -1) ?? -1);
                $remaining = $limit < 0 ? null : max(0, $limit - (int) $row['used']);

                return [
                    ...$row,
                    'limit' => $limit,
                    'unlimited' => $limit < 0,
                    'remaining' => $remaining,
                    'percent' => $limit > 0 ? min(100, (int) round(((int) $row['used'] / $limit) * 100)) : ($limit < 0 ? 100 : 0),
                    'is_full' => $limit >= 0 && (int) $row['used'] >= $limit,
                ];
            })
            ->all();
    }

    public function campaignCount(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        return QrCampaign::query()
            ->where('user_id', $user->id)
            ->where(function ($query): void {
                $query
                    ->whereNull('settings->source')
                    ->orWhere('settings->source', '!=', 'manual');
            })
            ->count();
    }

    protected function ensureBelowLimit(?User $user, string $key, int $currentCount, string $label): void
    {
        if (! $user) {
            return;
        }

        $limit = (int) ($user->planLimit($key, -1) ?? -1);

        if ($limit < 0 || $currentCount < $limit) {
            return;
        }

        throw ValidationException::withMessages([
            'plan' => __('Your current plan allows up to :limit :label.', [
                'limit' => $limit,
                'label' => $label,
            ]),
        ]);
    }

    protected function countOwnedRows(string $modelClass, string $table, ?User $user): int
    {
        return $this->ownedQuery($modelClass, $table, $user)->count();
    }

    protected function ownedQuery(string $modelClass, string $table, ?User $user)
    {
        $column = Schema::hasColumn($table, 'user_id') ? 'user_id' : (Schema::hasColumn($table, 'team_id') ? 'team_id' : null);
        $query = $modelClass::query();

        return $column ? $query->where($column, $user?->id) : $query->whereRaw('1 = 0');
    }

    public static function planUsageCacheKey(?User $user): string
    {
        $userId = (int) ($user?->id ?? 0);
        $version = $user?->hasActivePlan() ? 'v1' : 'v0';
        $locale = strtolower((string) app()->getLocale());

        return "portal.plan_usage.{$version}.{$userId}.{$locale}";
    }

    public static function forgetPlanUsageCache(int $userId): void
    {
        foreach (['v0', 'v1', 'v2'] as $version) {
            foreach (['en', 'vi'] as $locale) {
                Cache::forget("portal.plan_usage.{$version}.{$userId}.{$locale}");
            }

            Cache::forget("portal.plan_usage.{$version}.{$userId}");
        }
    }
}
