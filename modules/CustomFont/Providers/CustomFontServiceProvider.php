<?php

namespace Modules\CustomFont\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Modules\CustomFont\Http\Middleware\InjectVietnameseFont;

class CustomFontServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'customfont');

        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->pushMiddlewareToGroup('web', InjectVietnameseFont::class);
    }
}
