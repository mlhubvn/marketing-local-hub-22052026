<?php

namespace Modules\CustomFont\Console;

use Illuminate\Console\Command;
use Modules\CustomFont\Support\VietnameseFont;

class CustomFontStatusCommand extends Command
{
    protected $signature = 'customfont:status';

    protected $description = 'Verify CustomFont module is loaded and can render Vietnamese font markup';

    public function handle(): int
    {
        $checks = [
            'provider_class' => class_exists(\Modules\CustomFont\Providers\CustomFontServiceProvider::class),
            'support_class' => class_exists(VietnameseFont::class),
            'view_exists' => view()->exists('customfont::partials.vietnamese-font-head'),
            'module_json' => is_file(base_path('modules/CustomFont/module.json')),
        ];

        foreach ($checks as $label => $ok) {
            $this->line(sprintf('[%s] %s', $ok ? 'OK' : 'FAIL', $label));
        }

        if ($checks['view_exists']) {
            $html = view('customfont::partials.vietnamese-font-head')->render();
            $hasMarker = str_contains($html, 'id="customfont-vietnamese"');
            $hasFont = str_contains($html, 'Be Vietnam Pro');
            $this->line(sprintf('[%s] view_marker', $hasMarker ? 'OK' : 'FAIL'));
            $this->line(sprintf('[%s] view_font_stack', $hasFont ? 'OK' : 'FAIL'));
        }

        return collect($checks)->contains(false) ? self::FAILURE : self::SUCCESS;
    }
}
