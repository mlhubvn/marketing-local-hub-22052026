<?php

namespace Database\Seeders;

use Database\Support\IdSequence;
use Illuminate\Database\Seeder;
use Modules\AdminLanguages\Models\Language;
use Modules\AdminSettings\Support\OptionStore;

class MLHUBFoundationSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLanguages();
        $this->seedLocaleDefaults();
    }

    protected function seedLanguages(): void
    {
        $languages = [
            [
                'offset' => 0,
                'code' => 'vi',
                'name' => 'Tiếng Việt',
                'native_name' => 'Tiếng Việt',
                'icon' => 'vn',
                'is_default' => true,
                'sort_order' => 0,
            ],
            [
                'offset' => 1,
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'icon' => 'us',
                'is_default' => false,
                'sort_order' => 10,
            ],
        ];

        foreach ($languages as $language) {
            $record = Language::query()->firstOrNew(['code' => $language['code']]);

            if (! $record->exists && ($id = IdSequence::idForNewSeed($language['offset'])) !== null) {
                $record->id = $id;
            }

            $record->fill([
                'name' => $language['name'],
                'native_name' => $language['native_name'],
                'icon' => $language['icon'],
                'direction' => 'ltr',
                'is_default' => $language['is_default'],
                'is_active' => true,
                'auto_translate' => true,
                'sort_order' => $language['sort_order'],
            ])->save();
        }
    }

    protected function seedLocaleDefaults(): void
    {
        if (! class_exists(OptionStore::class)) {
            return;
        }

        $branding = $this->branding();
        /** @var OptionStore $options */
        $options = app(OptionStore::class);

        $options->set('default_locale', $branding['locale']);
        $options->set('app_timezone', $branding['timezone']);
    }

    protected function branding(): array
    {
        $path = database_path('config/MLHUB.php');

        if (! is_file($path)) {
            return [
                'locale' => 'vi',
                'timezone' => 'Asia/Ho_Chi_Minh',
            ];
        }

        return (array) (require $path)['branding'];
    }
}
