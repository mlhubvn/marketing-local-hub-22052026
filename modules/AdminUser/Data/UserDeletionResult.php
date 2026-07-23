<?php

namespace Modules\AdminUser\Data;

class UserDeletionResult
{
    /**
     * @param  array<string, int>  $recordsDeleted
     * @param  list<string>  $warnings
     */
    public function __construct(
        public readonly int $userId,
        public bool $deleted = false,
        public int $businessesDeleted = 0,
        public int $campaignsDeleted = 0,
        public int $customersDeleted = 0,
        public int $personalTeamsDeleted = 0,
        public int $sharedTeamsDetached = 0,
        public int $onboardingBusinessesPurged = 0,
        public int $partnerTicketsDeleted = 0,
        public int $storageAssetsScheduled = 0,
        public int $storageAssetsDeleted = 0,
        public array $recordsDeleted = [],
        public array $warnings = [],
    ) {}

    public function addDeleted(string $table, int $count): void
    {
        if ($count <= 0) {
            return;
        }

        $this->recordsDeleted[$table] = ($this->recordsDeleted[$table] ?? 0) + $count;
    }

    /**
     * @return array<string, mixed>
     */
    public function auditMetadata(): array
    {
        return [
            'deleted_user_id' => $this->userId,
            'businesses_deleted' => $this->businessesDeleted,
            'campaigns_deleted' => $this->campaignsDeleted,
            'customers_deleted' => $this->customersDeleted,
            'personal_teams_deleted' => $this->personalTeamsDeleted,
            'shared_teams_detached' => $this->sharedTeamsDetached,
            'onboarding_businesses_purged' => $this->onboardingBusinessesPurged,
            'partner_tickets_deleted' => $this->partnerTicketsDeleted,
            'storage_assets_scheduled' => $this->storageAssetsScheduled,
            'storage_assets_deleted' => $this->storageAssetsDeleted,
            'records_deleted' => $this->recordsDeleted,
            'storage_warning_count' => count($this->warnings),
        ];
    }
}
