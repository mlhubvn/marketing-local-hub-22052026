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
     * @param  array{disk:string,path:string,directory:bool}  $asset
     * @return array{deleted:bool,fingerprint:string}
     */
    public function delete(int $deletedUserId, array $asset): array
    {
        $fingerprint = hash('sha256', $asset['disk'].'|'.$asset['path']);

        try {
            $disk = Storage::disk($asset['disk']);
            $deleted = ! $disk->exists($asset['path'])
                || ($asset['directory']
                    ? $disk->deleteDirectory($asset['path'])
                    : $disk->delete($asset['path']));

            if ($deleted) {
                $this->forgetFailure($fingerprint);

                return ['deleted' => true, 'fingerprint' => $fingerprint];
            }

            $this->recordFailure($deletedUserId, $asset, $fingerprint, 'delete_returned_false');
        } catch (Throwable $exception) {
            $this->recordFailure($deletedUserId, $asset, $fingerprint, $exception::class);
        }

        return ['deleted' => false, 'fingerprint' => $fingerprint];
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

            $summary[$result['deleted'] ? 'deleted' : 'failed']++;
        }

        return $summary;
    }

    /**
     * @param  array{disk:string,path:string,directory:bool}  $asset
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
