<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Modules\AdminSettings\Support\OptionStore;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('options') || ! class_exists(OptionStore::class)) {
            return;
        }

        $options = app(OptionStore::class);

        $this->upgradeSiteAssetOption($options, 'website_favicon', (string) config('mlhub.site.favicon', 'img/favicon.svg'), [
            'img/favicon.png',
            '',
        ]);
        $this->upgradeSiteAssetOption($options, 'website_logo_dark', (string) config('mlhub.site.logo_dark', 'img/logo-dark.svg'), [
            'img/logo-dark.png',
            '',
        ]);
        $this->upgradeSiteAssetOption($options, 'website_logo_light', (string) config('mlhub.site.logo_light', 'img/logo-light.svg'), [
            'img/logo-light.png',
            '',
        ]);
    }

    public function down(): void
    {
        //
    }

    /**
     * @param  list<string>  $legacyValues
     */
    protected function upgradeSiteAssetOption(OptionStore $options, string $key, string $svgPath, array $legacyValues): void
    {
        $current = (string) $options->get($key, '');

        if (! in_array($current, $legacyValues, true)) {
            return;
        }

        $options->set($key, $svgPath);
    }
};
