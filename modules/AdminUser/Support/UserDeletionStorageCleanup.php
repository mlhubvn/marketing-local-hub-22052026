<?php

namespace Modules\AdminUser\Support;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UserDeletionStorageCleanup
{
    /**
     * @param  array{disk:string,path:string,directory:bool,verified_user_owned?:bool}  $asset
     * @return array{deleted:bool,status:'deleted'|'missing'|'failed',fingerprint:string,failure_reason:?string,retry_status:'not_needed'|'pending'}
     */
    public function delete(int $deletedUserId, array $asset): array
    {
        $fingerprint = hash('sha256', $asset['disk'].'|'.$asset['path']);
        $safetyFailure = $this->safetyFailure($deletedUserId, $asset);

        if ($safetyFailure !== null) {
            $this->recordFailure($deletedUserId, $asset, $fingerprint, $safetyFailure);

            return [
                'deleted' => false,
                'status' => 'failed',
                'fingerprint' => $fingerprint,
                'failure_reason' => $safetyFailure,
                'retry_status' => 'pending',
            ];
        }

        try {
            $disk = Storage::disk($asset['disk']);

            if (! $disk->exists($asset['path'])) {
                $this->forgetFailure($fingerprint);

                return [
                    'deleted' => true,
                    'status' => 'missing',
                    'fingerprint' => $fingerprint,
                    'failure_reason' => null,
                    'retry_status' => 'not_needed',
                ];
            }

            $deleted = $asset['directory']
                ? $disk->deleteDirectory($asset['path'])
                : $disk->delete($asset['path']);

            if ($deleted) {
                $this->forgetFailure($fingerprint);

                return [
                    'deleted' => true,
                    'status' => 'deleted',
                    'fingerprint' => $fingerprint,
                    'failure_reason' => null,
                    'retry_status' => 'not_needed',
                ];
            }

            $failureReason = 'delete_returned_false';
            $this->recordFailure($deletedUserId, $asset, $fingerprint, $failureReason);
        } catch (Throwable $exception) {
            $failureReason = $exception::class;
            $this->recordFailure($deletedUserId, $asset, $fingerprint, $failureReason);
        }

        return [
            'deleted' => false,
            'status' => 'failed',
            'fingerprint' => $fingerprint,
            'failure_reason' => $failureReason,
            'retry_status' => 'pending',
        ];
    }

    /**
     * @param  array{disk:string,path:string,directory:bool,verified_user_owned?:bool}  $asset
     */
    private function safetyFailure(int $deletedUserId, array $asset): ?string
    {
        $path = trim(str_replace('\\', '/', (string) $asset['path']));

        if ($path === ''
            || str_starts_with($path, '/')
            || preg_match('/^[a-z]:\//i', $path) === 1
            || in_array($path, ['.', '..'], true)
            || str_contains('/'.$path.'/', '/../')
            || str_contains('/'.$path.'/', '/./')) {
            return 'unsafe_asset_path';
        }

        $segments = array_values(array_filter(explode('/', trim($path, '/')), fn (string $part): bool => $part !== ''));

        if ((bool) $asset['directory'] && count($segments) < 2) {
            return 'unsafe_asset_path';
        }

        if ((bool) $asset['directory'] && ! (bool) ($asset['verified_user_owned'] ?? false)) {
            return 'unverified_directory_ownership';
        }

        if (! Schema::hasTable('files')
            || ! Schema::hasColumns('files', ['owner_user_id', 'path'])) {
            return null;
        }

        $query = DB::table('files')
            ->where('owner_user_id', '!=', $deletedUserId)
            ->where(function ($builder) use ($path, $asset): void {
                $builder->where('path', $path);

                if ((bool) $asset['directory']) {
                    $builder->orWhere('path', 'like', rtrim($path, '/').'/%');
                }
            });

        if (Schema::hasColumn('files', 'disk')) {
            $query->where('disk', (string) $asset['disk']);
        }

        return $query->exists() ? 'shared_storage_path' : null;
    }

    /**
     * @return array{processed:int,deleted:int,failed:int}
     */
    public function retryPending(int $limit = 100): array
    {
        $summary = ['processed' => 0, 'deleted' => 0, 'failed' => 0];

        if (! Schema::hasTable('user_deletion_storage_failures')) {
            return $summary;
        }

        $rows = DB::table('user_deletion_storage_failures')
            ->orderBy('last_attempted_at')
            ->orderBy('id')
            ->limit(max(1, min($limit, 1000)))
            ->get();

        foreach ($rows as $row) {
            $summary['processed']++;

            try {
                $path = Crypt::decryptString((string) $row->encrypted_path);
            } catch (Throwable $exception) {
                $this->markRetryFailure((int) $row->id, $exception::class);
                $summary['failed']++;

                continue;
            }

            $result = $this->delete((int) $row->deleted_user_id, [
                'disk' => (string) $row->disk,
                'path' => $path,
                'directory' => (bool) $row->is_directory,
            ]);

            $summary[$result['status'] === 'failed' ? 'failed' : 'deleted']++;
        }

        return $summary;
    }

    /**
     * @param  array{disk:string,path:string,directory:bool,verified_user_owned?:bool}  $asset
     */
    private function recordFailure(
        int $deletedUserId,
        array $asset,
        string $fingerprint,
        string $errorClass
    ): void {
        if (! Schema::hasTable('user_deletion_storage_failures')) {
            $this->logFailure($deletedUserId, $asset['disk'], $fingerprint, $errorClass, false);

            return;
        }

        try {
            $existing = DB::table('user_deletion_storage_failures')
                ->where('asset_fingerprint', $fingerprint)
                ->first();

            if ($existing) {
                DB::table('user_deletion_storage_failures')
                    ->where('id', $existing->id)
                    ->update([
                        'attempts' => ((int) $existing->attempts) + 1,
                        'last_error_class' => $errorClass,
                        'last_attempted_at' => now(),
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('user_deletion_storage_failures')->insert([
                    'deleted_user_id' => $deletedUserId,
                    'asset_fingerprint' => $fingerprint,
                    'disk' => $asset['disk'],
                    'encrypted_path' => Crypt::encryptString($asset['path']),
                    'is_directory' => $asset['directory'],
                    'attempts' => 1,
                    'last_error_class' => $errorClass,
                    'last_attempted_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->logFailure($deletedUserId, $asset['disk'], $fingerprint, $errorClass, true);
        } catch (Throwable $exception) {
            $this->logFailure($deletedUserId, $asset['disk'], $fingerprint, $exception::class, false);
        }
    }

    private function forgetFailure(string $fingerprint): void
    {
        if (Schema::hasTable('user_deletion_storage_failures')) {
            DB::table('user_deletion_storage_failures')
                ->where('asset_fingerprint', $fingerprint)
                ->delete();
        }
    }

    private function markRetryFailure(int $id, string $errorClass): void
    {
        DB::table('user_deletion_storage_failures')
            ->where('id', $id)
            ->increment('attempts', 1, [
                'last_error_class' => $errorClass,
                'last_attempted_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function logFailure(
        int $deletedUserId,
        string $disk,
        string $fingerprint,
        string $errorClass,
        bool $retryPersisted
    ): void {
        Log::warning('user_deletion.storage_cleanup_failed', [
            'deleted_user_id' => $deletedUserId,
            'asset_fingerprint' => $fingerprint,
            'disk' => $disk,
            'exception' => $errorClass,
            'retry_persisted' => $retryPersisted,
        ]);
    }
}
