<?php

namespace Modules\AdminUser\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Services\OnboardingAdminService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Throwable;

class DeleteUser
{
    public function __construct(
        protected OnboardingAdminService $onboarding
    ) {}

    public function execute(User $user, ?int $deletedByUserId = null): bool
    {
        return DB::transaction(function () use ($user, $deletedByUserId): bool {
            $lockedUser = User::query()->lockForUpdate()->find($user->getKey());

            if (! $lockedUser) {
                return false;
            }

            $this->deleteStoredFilesAfterCommit($this->storedFilesFor($lockedUser));
            $this->onboarding->adminPurgeForUser((int) $lockedUser->id, $deletedByUserId);

            // Delete businesses while the owning user still exists. Several child
            // tables use both user CASCADE and business SET NULL constraints; letting
            // MySQL process both paths from a user delete causes SQLSTATE 1452.
            if (Schema::hasTable('lb_businesses')) {
                LocalBusiness::query()
                    ->where('user_id', $lockedUser->id)
                    ->delete();
            }

            return (bool) $lockedUser->delete();
        });
    }

    /**
     * @return list<array{disk: string, path: string}>
     */
    private function storedFilesFor(User $user): array
    {
        $storedFiles = [];
        $avatarPath = trim((string) $user->avatar_path);
        $avatarDisk = trim((string) $user->avatar_disk);

        if ($avatarPath !== '' && ! Str::startsWith($avatarPath, ['http://', 'https://', '//'])) {
            if ($avatarDisk === '') {
                $avatarPath = ltrim($avatarPath, '/');
                $avatarPath = Str::replaceStart('public/storage/', '', $avatarPath);
                $avatarPath = Str::replaceStart('storage/', '', $avatarPath);
                $avatarPath = Str::replaceStart('public/', '', $avatarPath);
            }

            $storedFiles[] = [
                'disk' => $avatarDisk ?: 'public',
                'path' => $avatarPath,
            ];
        }

        if (Schema::hasTable('files')
            && Schema::hasColumn('files', 'owner_user_id')
            && Schema::hasColumn('files', 'disk')
            && Schema::hasColumn('files', 'path')) {
            foreach (DB::table('files')
                ->where('owner_user_id', $user->id)
                ->whereNotNull('path')
                ->get(['disk', 'path']) as $file) {
                $disk = trim((string) $file->disk);
                $path = trim((string) $file->path);

                if ($disk !== '' && $path !== '') {
                    $storedFiles[] = compact('disk', 'path');
                }
            }
        }

        return collect($storedFiles)
            ->unique(fn (array $file): string => $file['disk'].'|'.$file['path'])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{disk: string, path: string}>  $storedFiles
     */
    private function deleteStoredFilesAfterCommit(array $storedFiles): void
    {
        if ($storedFiles === []) {
            return;
        }

        DB::afterCommit(function () use ($storedFiles): void {
            foreach ($storedFiles as $file) {
                try {
                    Storage::disk($file['disk'])->delete($file['path']);
                } catch (Throwable $exception) {
                    report($exception);
                }
            }
        });
    }
}
