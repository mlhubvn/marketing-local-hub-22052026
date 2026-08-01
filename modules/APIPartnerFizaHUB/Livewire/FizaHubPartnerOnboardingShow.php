<?php

namespace Modules\APIPartnerFizaHUB\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Services\PartnerReportingService;
use Throwable;

/**
 * FizaHUB Partner Reporting Portal — single HKD detail page (view-only).
 *
 * The route parameter is the numeric `partner_onboarding_requests.id` (never trusted as an
 * Eloquent model until re-fetched through {@see PartnerReportingService::findOnboardingOrFail()},
 * which 404s exactly like implicit route-model binding would for an unknown/foreign ID —
 * there is no other partner's data in this module's tables to leak in the first place, and
 * ticket lookups additionally re-validate ownership through SupportTicketBridge.
 */
#[Title('FizaHUB Partner Reporting - Chi tiết HKD')]
class FizaHubPartnerOnboardingShow extends Component
{
    public PartnerOnboardingRequest $onboardingRequest;

    public ?string $activeTicketId = null;

    /** @var array<string, mixed>|null */
    public ?array $activeTicketDetail = null;

    public ?string $ticketError = null;

    /**
     * The route parameter is named `onboardingRequestId` (NOT `onboardingRequest`) on
     * purpose: Livewire resolves implicit route-model binding for full-page components by
     * matching the route segment name against a typed PUBLIC PROPERTY of the same name,
     * independently of this method's own `int` type hint. If the segment were named
     * `onboardingRequest` it would collide with the `PartnerOnboardingRequest
     * $onboardingRequest` property above and Livewire would try to inject an
     * auto-resolved Eloquent model here instead of the raw ID, causing a TypeError.
     */
    public function mount(int $onboardingRequestId): void
    {
        $this->onboardingRequest = app(PartnerReportingService::class)->findOnboardingOrFail($onboardingRequestId);
    }

    public function viewTicket(string $ticketId): void
    {
        $this->ticketError = null;

        if ($this->activeTicketId === $ticketId) {
            $this->activeTicketId = null;
            $this->activeTicketDetail = null;

            return;
        }

        $service = app(PartnerReportingService::class);
        $integration = $service->findIntegrationFor($this->onboardingRequest);

        if (! $integration) {
            $this->ticketError = __('Không tìm thấy dữ liệu liên kết MKT cho HKD này.');

            return;
        }

        try {
            $this->activeTicketDetail = $service->ticketDetail($integration, $ticketId);
            $this->activeTicketId = $ticketId;
        } catch (Throwable) {
            $this->ticketError = __('Không tìm thấy phiếu hỗ trợ này.');
            $this->activeTicketId = null;
            $this->activeTicketDetail = null;
        }
    }

    public function render(): View
    {
        $service = app(PartnerReportingService::class);
        $integration = $service->findIntegrationFor($this->onboardingRequest);

        return view('apipartnerfizahub::livewire.partner-onboarding-show', [
            'onboarding' => $this->onboardingRequest,
            'integration' => $integration,
            'growth' => $integration ? $service->growthSummaryFor($integration) : null,
            'tickets' => $integration ? $service->supportTicketsFor($integration) : null,
            'packageHistory' => $integration ? $service->packageHistoryFor($integration) : collect(),
            'planWindowStatus' => $service->planWindowStatus($this->onboardingRequest->user?->plan_expires_at),
        ])->layout('apipartnerfizahub::layouts.partner-reporting', [
            'title' => __('FizaHUB Partner Reporting - Chi tiết HKD'),
        ]);
    }
}
