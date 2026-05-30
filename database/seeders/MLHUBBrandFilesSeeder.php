<?php

namespace Database\Seeders;

use Database\Support\IdSequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Modules\AdminUser\Models\User;
use Modules\AppFiles\Models\AppFile;

class MLHUBBrandFilesSeeder extends Seeder
{
    public function run(): void
    {
        if (! class_exists(AppFile::class)) {
            return;
        }

        $ownerId = IdSequence::at(0);
        $user = User::query()->where('email', 'demo@mlhub.vn')->first();

        if (! $user || (int) $user->id !== $ownerId) {
            return;
        }

        $teamId = $user->ownedTeams()->value('id') ?? $ownerId;
        $disk = Storage::disk('public');

        foreach ((array) require database_path('seeders/data/mlhub_brand_files.php') as $file) {
            if (! $disk->exists($file['path'])) {
                continue;
            }

            AppFile::query()->updateOrCreate(
                ['id' => $file['id']],
                [
                    'id_secure' => $file['id_secure'],
                    'owner_user_id' => $ownerId,
                    'team_id' => $teamId,
                    'parent_id' => null,
                    'disk' => 'public',
                    'name' => $file['name'],
                    'path' => $file['path'],
                    'mime_type' => $file['mime_type'],
                    'extension' => $file['extension'],
                    'category' => $file['category'],
                    'size_bytes' => $file['size_bytes'],
                    'is_folder' => false,
                    'is_image' => true,
                    'width' => $file['width'],
                    'height' => $file['height'],
                ],
            );
        }
    }
}
