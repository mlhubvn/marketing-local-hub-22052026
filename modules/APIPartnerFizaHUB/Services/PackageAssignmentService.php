<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerPackageAssignment;
use Modules\APIPartnerFizaHUB\Support\PartnerApiException;

class PackageAssignmentService
{
    public function __construct(
        protected PartnerMappingService $mapping,
        protected WebhookOutboxService $webhooks,
    ) {}

    public function defaultPackageCode(): string
    {
        return (string) config('modules.apipartnerfizahub.default_package', 'free');
    }

    public function assignEffectivePackage(
        PartnerIntegration $integration,
        string $packageCode,
        ?int $changedBy = null,
        ?string $reason = null,
        ?PartnerOnboardingRequest $onboarding = null,
        bool $markApproved = false
    ): PartnerPackageAssignment {
        $plan = $this->mapping->resolvePlan($packageCode);

        if (! $plan instanceof AdminPlan) {
            throw PartnerApiException::make(
                'default_plan_not_found',
                __('Hệ thống chưa sẵn sàng để tạo tài khoản. Vui lòng thử lại sau ít phút.'),
                503,
                ['next_action' => 'retry_later']
            );
        }

        return DB::transaction(function () use ($integration, $packageCode, $plan, $changedBy, $reason, $onboarding, $markApproved) {
            PartnerPackageAssignment::query()
                ->where('partner_integration_id', $integration->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'ended',
                    'effective_to' => now(),
                ]);

            $assignment = PartnerPackageAssignment::query()->create([
                'partner_integration_id' => $integration->id,
                'package_code' => $packageCode,
                'plan_id' => $plan->id,
                'status' => 'active',
                'effective_from' => now(),
                'effective_to' => null,
                'changed_by' => $changedBy,
                'reason' => $reason,
                'metadata' => [],
            ]);

            $integration->forceFill([
                'package_code' => $packageCode,
            ])->save();

            if ($integration->mlhub_user_id) {
                User::query()->whereKey($integration->mlhub_user_id)->update([
                    'plan_id' => $plan->id,
                    'plan_started_at' => now(),
                ]);
            }

            if ($onboarding) {
                $updates = ['package_code' => $packageCode];

                if ($markApproved) {
                    $updates['approved_package_code'] = $packageCode;
                }

                $onboarding->forceFill($updates)->save();
            }

            $this->safeAudit('partner.fizahub.package.assign', 'Assigned FizaHUB package.', [
                'subject_type' => PartnerPackageAssignment::class,
                'subject_id' => $assignment->id,
                'area' => 'admin',
                'causer_user_id' => $changedBy,
                'metadata' => [
                    'package_code' => $packageCode,
                    'external_business_id' => $integration->external_business_id,
                    'reason' => $reason,
                ],
            ]);

            if ($onboarding && ($markApproved || $changedBy !== null)) {
                $this->webhooks->queueOnboardingStatus(
                    $this->onboardingWebhookPayload($onboarding->fresh(), $integration->fresh()),
                    $onboarding->request_id.'|package|'.$packageCode.'|'.$assignment->id
                );
            }

            return $assignment;
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function catalog(): array
    {
        $map = (array) config('modules.apipartnerfizahub.package_map', []);
        $items = [];

        foreach ($map as $code => $slug) {
            $plan = AdminPlan::query()->where('slug', $slug)->where('status', true)->first();

            $items[] = [
                'package_code' => (string) $code,
                'plan_slug' => (string) $slug,
                'package_name' => $plan?->name,
                'is_default' => (string) $code === $this->defaultPackageCode(),
                'is_free' => (bool) ($plan?->free_plan ?? false),
            ];
        }

        return [
            'default_package_code' => $this->defaultPackageCode(),
            'packages' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function onboardingWebhookPayload(PartnerOnboardingRequest $onboarding, PartnerIntegration $integration): array
    {
        return [
            'request_id' => $onboarding->request_id,
            'external_business_id' => $onboarding->external_business_id,
            'status' => $onboarding->status,
            'current_step' => $onboarding->current_step,
            'status_label' => $onboarding->statusLabel(),
            'requested_package_code' => $onboarding->requested_package_code,
            'package_code' => $integration->package_code,
            'approved_package_code' => $onboarding->approved_package_code,
            'mlhub_user_id' => $onboarding->mlhub_user_id,
            'mlhub_business_id' => $onboarding->mlhub_business_id,
            'occurred_at' => now()->utc()->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function safeAudit(string $event, string $description, array $attributes): void
    {
        if (! Schema::hasTable('audit_logs') || ! function_exists('log_activity')) {
            return;
        }

        try {
            log_activity($event, $description, $attributes);
        } catch (\Throwable) {
            // Audit must not block package assignment.
        }
    }
}
