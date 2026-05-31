<?php

namespace Modules\AdminFaker\Support;

use App\Support\Storage\StorageDriverManager;
use Modules\AdminUser\Models\User;
use Modules\AppFiles\Models\AppFile;

class MlhubDemoImageResolver
{
    public function __construct(
        protected StorageDriverManager $storageDriverManager,
    ) {}

    /**
     * @param  array<int, AppFile>  $imageFiles
     */
    public function random(array $imageFiles): ?AppFile
    {
        if ($imageFiles === []) {
            return null;
        }

        return $imageFiles[array_rand($imageFiles)];
    }

    public function url(?AppFile $file): ?string
    {
        if (! $file || ! filled($file->path)) {
            return null;
        }

        $disk = (string) ($file->disk ?: 'public');
        $path = trim((string) $file->path);

        $publicUrl = $this->storageDriverManager->publicUrl($disk, $path);

        if ($publicUrl) {
            return (string) $publicUrl;
        }

        if ($disk === 'public') {
            return asset('storage/'.$path);
        }

        return null;
    }

    /**
     * @return array<int, AppFile>
     */
    public function usableImagesForUser(User $user): array
    {
        return AppFile::query()
            ->ownedBy($user)
            ->where('is_folder', false)
            ->where('is_image', true)
            ->whereNotNull('path')
            ->where('path', 'not like', 'files/demo-faker/%')
            ->get()
            ->filter(fn (AppFile $file): bool => $this->isUsable($file))
            ->shuffle()
            ->take(12)
            ->values()
            ->all();
    }

    protected function isUsable(AppFile $file): bool
    {
        if (! filled($file->path)) {
            return false;
        }

        $extension = strtolower((string) $file->extension);

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    }
}
