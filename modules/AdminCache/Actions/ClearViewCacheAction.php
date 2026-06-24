<?php

namespace Modules\AdminCache\Actions;

use Illuminate\Support\Facades\Artisan;
use Modules\AdminCache\Actions\Contracts\CacheAction;

class ClearViewCacheAction implements CacheAction
{
    public function key(): string
    {
        return 'view';
    }

    public function title(): string
    {
        return 'View cache';
    }

    public function description(): string
    {
        return 'Removes compiled Blade view files from storage so templates are rendered fresh.';
    }

    public function whenToUse(): string
    {
        return 'Use after editing Blade views, changing themes, or updating UI text when pages still show the old layout.';
    }

    public function afterRunning(): string
    {
        return 'Views compile again on the next page render. The first page load may be slower; users stay logged in.';
    }

    public function icon(): string
    {
        return 'fa-eye';
    }

    public function buttonLabel(): string
    {
        return 'Clear views';
    }

    public function buttonVariant(): string
    {
        return 'outline';
    }

    public function confirmMessage(): string
    {
        return 'This removes compiled Blade view files. Laravel regenerates them automatically when pages are rendered again.';
    }

    public function confirmTitle(): string
    {
        return 'Are you absolutely sure?';
    }

    public function handle(): string
    {
        Artisan::call('view:clear');

        return __('View cache cleared. Blade templates will be recompiled on the next page render.');
    }
}
