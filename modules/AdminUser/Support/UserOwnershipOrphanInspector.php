<?php

namespace Modules\AdminUser\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserOwnershipOrphanInspector
{
    /**
     * @return list<array{
     *   table:string,
     *   record_id:int,
     *   reason:string,
     *   ownership_evidence:string,
     *   safe_to_delete:bool
     * }>
     */
    public function inspect(): array
    {
        return array_merge(
            $this->crmTagFindings(),
            $this->templatePackFindings(),
            $this->templateImportFindings(),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function crmTagFindings(): array
    {
        if (! $this->hasColumns('lb_customer_tags', ['id', 'owner_user_id'])) {
            return [];
        }

        $findings = [];
        $query = DB::table('lb_customer_tags as tags')
            ->leftJoin('users', 'users.id', '=', 'tags.team_id')
            ->leftJoin('teams', 'teams.id', '=', 'tags.team_id')
            ->whereNull('tags.owner_user_id')
            ->select([
                'tags.id',
                'tags.team_id',
                'users.id as matching_user_id',
                'teams.id as matching_team_id',
            ]);

        if ($this->hasColumns('lb_customer_tag_maps', ['tag_id', 'customer_id'])
            && Schema::hasTable('lb_customers')) {
            $query->leftJoin(
                'lb_customer_tag_maps as tag_maps',
                'tag_maps.tag_id',
                '=',
                'tags.id'
            )->leftJoin(
                'lb_customers as mapped_customers',
                'mapped_customers.id',
                '=',
                'tag_maps.customer_id'
            )->selectRaw('COUNT(DISTINCT mapped_customers.id) as live_customer_maps')
                ->groupBy([
                    'tags.id',
                    'tags.team_id',
                    'users.id',
                    'teams.id',
                ]);
        } else {
            $query->selectRaw('0 as live_customer_maps');
        }

        foreach ($query->orderBy('tags.id')->cursor() as $tag) {
            $legacyId = isset($tag->team_id) ? (int) $tag->team_id : 0;
            $matchingUser = $tag->matching_user_id !== null;
            $matchingTeam = $tag->matching_team_id !== null;
            $liveCustomerMaps = (int) $tag->live_customer_maps;

            $reason = match (true) {
                $matchingUser => 'legacy_owner_backfill_pending',
                $matchingTeam => 'ambiguous_legacy_team_or_user_value',
                $legacyId < 1 => 'owner_missing_without_legacy_reference',
                default => 'legacy_owner_missing',
            };
            $safe = ! $matchingUser
                && ! $matchingTeam
                && $legacyId > 0
                && $liveCustomerMaps === 0;

            $findings[] = [
                'table' => 'lb_customer_tags',
                'record_id' => (int) $tag->id,
                'reason' => $reason,
                'ownership_evidence' => sprintf(
                    'legacy_team_id=%d;matching_user=%s;matching_team=%s;live_customer_maps=%d',
                    $legacyId,
                    $matchingUser ? 'yes' : 'no',
                    $matchingTeam ? 'yes' : 'no',
                    $liveCustomerMaps
                ),
                'safe_to_delete' => $safe,
            ];
        }

        return $findings;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function templatePackFindings(): array
    {
        if (! $this->hasColumns(
            'lb_template_packs',
            ['id', 'team_id', 'created_by_user_id']
        )) {
            return [];
        }

        $query = DB::table('lb_template_packs')
            ->whereNull('team_id')
            ->whereNull('created_by_user_id');

        if (Schema::hasColumn('lb_template_packs', 'source')) {
            $query->where('source', 'custom');
        }

        if (Schema::hasColumn('lb_template_packs', 'visibility')) {
            $query->where('visibility', 'private');
        }

        return $query->orderBy('id')->pluck('id')->map(fn ($id): array => [
            'table' => 'lb_template_packs',
            'record_id' => (int) $id,
            'reason' => 'private_custom_pack_without_owner',
            'ownership_evidence' => 'team_id=NULL;created_by_user_id=NULL',
            'safe_to_delete' => false,
        ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function templateImportFindings(): array
    {
        if (! $this->hasColumns('lb_template_imports', ['id', 'team_id', 'user_id'])) {
            return [];
        }

        return DB::table('lb_template_imports')
            ->whereNull('team_id')
            ->whereNull('user_id')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id): array => [
                'table' => 'lb_template_imports',
                'record_id' => (int) $id,
                'reason' => 'template_import_without_owner',
                'ownership_evidence' => 'team_id=NULL;user_id=NULL',
                'safe_to_delete' => false,
            ])
            ->all();
    }

    /**
     * @param  list<string>  $columns
     */
    private function hasColumns(string $table, array $columns): bool
    {
        return Schema::hasTable($table)
            && collect($columns)->every(
                fn (string $column): bool => Schema::hasColumn($table, $column)
            );
    }
}
