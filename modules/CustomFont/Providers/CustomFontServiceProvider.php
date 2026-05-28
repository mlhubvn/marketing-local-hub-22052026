<?php

namespace Modules\CustomFont\Providers;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\CustomFont\Console\CustomFontStatusCommand;
use Modules\CustomFont\Support\VietnameseFont;

class CustomFontServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'customfont');

        Event::listen(RequestHandled::class, function (RequestHandled $event): void {
            VietnameseFont::injectIntoResponse($event->request, $event->response);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                CustomFontStatusCommand::class,
            ]);
        }
    }
}
