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
        $brandLogoDark = (string) config('mlhub.site.brand_logo_dark', 'img/logo-brand-dark.svg');
        $brandLogoLight = (string) config('mlhub.site.brand_logo_light', 'img/logo-brand-light.svg');

        $this->upgradeBrandLogoOption($options, 'website_logo_brand_dark', $brandLogoDark, [
            'img/logo-brand-dark.png',
            '',
        ]);
        $this->upgradeBrandLogoOption($options, 'website_logo_brand_light', $brandLogoLight, [
            'img/logo-brand-light.png',
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
    protected function upgradeBrandLogoOption(OptionStore $options, string $key, string $svgPath, array $legacyValues): void
    {
        $current = (string) $options->get($key, '');

        if (! in_array($current, $legacyValues, true)) {
            return;
        }

        $options->set($key, $svgPath);
    }
};
