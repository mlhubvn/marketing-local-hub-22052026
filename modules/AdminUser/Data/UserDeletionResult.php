<?php

namespace Modules\AdminUser\Data;

class UserDeletionResult
{
    /**
     * @param  array<string, int>  $recordsDeleted
     * @param  array<string, int>  $recordsAnonymized
     * @param  list<string>  $warnings
     */
    public function __construct(
        public readonly int $userId,
        public bool $deleted = false,
        public string $status = 'pending',
        public array $deletedBusinessIds = [],
        public array $deletedTeamIds = [],
        public array $detachedSharedTeamIds = [],
        public int $businessesDeleted = 0,
        public int $campaignsDeleted = 0,
        public int $customersDeleted = 0,
        public int $personalTeamsDeleted = 0,
        public int $sharedTeamsDetached = 0,
        public int $onboardingBusinessesPurged = 0,
        public int $partnerTicketsDeleted = 0,
        public int $storageAssetsScheduled = 0,
        public int $storageAssetsDeleted = 0,
        public int $storageAssetsMissing = 0,
        public int $storageFailureCount = 0,
        public int $databaseResidueCount = 0,
        public array $recordsDeleted = [],
        public array $recordsAnonymized = [],
        public array $storageFailures = [],
        public array $warnings = [],
    ) {}

    public function addDeleted(string $table, int $count): void
    {
        if ($count <= 0) {
            return;
        }

        $this->recordsDeleted[$table] = ($this->recordsDeleted[$table] ?? 0) + $count;
    }

    public function addAnonymized(string $table, int $count): void
    {
        if ($count <= 0) {
            return;
        }

        $this->recordsAnonymized[$table] = ($this->recordsAnonymized[$table] ?? 0) + $count;
    }

    public function completedCleanly(): bool
    {
        return $this->status === 'completed';
    }

    public function completedWithWarnings(): bool
    {
        return $this->status === 'completed_with_warnings';
    }

    public function failedVerification(): bool
    {
        return $this->status === 'failed_verification';
    }

    /**
     * @return array<string, mixed>
     */
    public function auditMetadata(): array
    {
        return [
            'deleted_user_id' => $this->userId,
            'status' => $this->status,
            'deleted_business_ids' => $this->deletedBusinessIds,
            'deleted_team_ids' => $this->deletedTeamIds,
            'detached_shared_team_ids' => $this->detachedSharedTeamIds,
            'businesses_deleted' => $this->businessesDeleted,
            'campaigns_deleted' => $this->campaignsDeleted,
            'customers_deleted' => $this->customersDeleted,
            'personal_teams_deleted' => $this->personalTeamsDeleted,
            'shared_teams_detached' => $this->sharedTeamsDetached,
            'onboarding_businesses_purged' => $this->onboardingBusinessesPurged,
            'partner_tickets_deleted' => $this->partnerTicketsDeleted,
            'storage_assets_scheduled' => $this->storageAssetsScheduled,
            'storage_assets_deleted' => $this->storageAssetsDeleted,
            'storage_assets_missing' => $this->storageAssetsMissing,
            'storage_failure_count' => $this->storageFailureCount,
            'database_residue_count' => $this->databaseResidueCount,
            'records_deleted' => $this->recordsDeleted,
            'records_anonymized' => $this->recordsAnonymized,
            'storage_failures' => $this->storageFailures,
            'storage_warning_count' => count($this->warnings),
        ];
    }
}
