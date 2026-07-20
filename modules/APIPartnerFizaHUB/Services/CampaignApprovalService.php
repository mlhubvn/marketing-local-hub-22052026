<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Illuminate\Support\Facades\DB;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Support\PartnerApiException;
use Modules\AppQRCampaigns\Models\QrCampaign;

class CampaignApprovalService
{
    public function __construct(
        protected SupportTicketBridge $supportTickets,
    ) {}

    /** @return array<string, mixed> */
    public function decide(
        PartnerIntegration $integration,
        QrCampaign $campaign,
        string $decision,
        ?string $note = null
    ): array {
        return DB::transaction(function () use ($integration, $campaign, $decision, $note): array {
            $campaign = QrCampaign::query()->lockForUpdate()->findOrFail($campaign->id);

            if ($campaign->status !== 'pending_approval') {
                throw PartnerApiException::make(
                    'campaign_invalid_state',
                    __('Chiến dịch không còn ở trạng thái chờ duyệt.'),
                    409,
                    ['current_status' => $campaign->status]
                );
            }

            $settings = (array) ($campaign->settings ?? []);
            $settings['partner_approval'] = [
                'decision' => $decision,
                'note' => $note,
                'decided_at' => now()->utc()->toIso8601String(),
                'source' => 'fizahub',
            ];
            $updates = ['settings' => $settings];
            $ticket = null;

            if ($decision === 'approved') {
                $updates['status'] = 'active';
                $updates['published_at'] = $campaign->published_at ?: now();
            } else {
                $updates['status'] = 'pending_approval';
                $ticket = $this->supportTickets->campaignRequestTicket($integration, $campaign, $note);
            }

            $campaign->forceFill($updates)->save();

            return [
                'campaign_id' => $campaign->id,
                'decision' => $decision,
                'status' => (string) $campaign->status,
                'support_ticket_id' => $ticket?->id_secure,
                'decided_at' => $settings['partner_approval']['decided_at'],
            ];
        });
    }
}
