<?php

namespace Modules\CustomFont\Support;

class VietnameseFont
{
    public const STACK = '"Be Vietnam Pro", Inter, "Segoe UI", ui-sans-serif, system-ui, sans-serif';

    public const BUNNY_STYLESHEET = 'https://fonts.bunny.net/css?family=be-vietnam-pro:400,500,600,700,800|inter:400,500,600,700,800';

    /**
     * @return array<int, string>
     */
    public static function landingRouteNames(): array
    {
        return [
            'landing-pages.public',
            'landing-pages.preview',
        ];
    }
}
