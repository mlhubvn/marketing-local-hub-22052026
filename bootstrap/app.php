<?php

use App\Exceptions\DemoModeRestrictedException;
use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\PreventDemoModeWriteOperations;
use App\Http\Middleware\ResolveUserPlanState;
use App\Support\Http\LivewireFailureReporter;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Modules\AdminLanguages\Http\Middleware\SetLocale;
use Modules\AdminThemes\Http\Middleware\SetThemeContext;
use Modules\AppAffiliate\Http\Middleware\CaptureAffiliateReferral;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Behind Traefik/Coolify the app sees HTTP; signed URLs are generated as HTTPS.
        $trustedProxies = env('TRUSTED_PROXIES');

        if (is_string($trustedProxies) && $trustedProxies !== '') {
            $middleware->trustProxies(at: $trustedProxies === '*' ? '*' : array_map('trim', explode(',', $trustedProxies)));
        } elseif (env('APP_ENV') === 'production') {
            $middleware->trustProxies(at: '*');
        }

        $middleware->validateCsrfTokens(except: [
            'livewire/upload-file',
            'livewire-*/upload-file',
        ]);

        $middleware->web(append: [
            SetLocale::class,
            SetThemeContext::class,
            CaptureAffiliateReferral::class,
            ResolveUserPlanState::class,
            EnsureAdminAccess::class,
            PreventDemoModeWriteOperations::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, Request $request): void {
            LivewireFailureReporter::report($exception, $request);
        });

        $exceptions->render(function (DemoModeRestrictedException $exception, Request $request) {
            $livewirePath = $request->is('livewire/update')
                || (bool) preg_match('/^livewire-[a-f0-9]+\/update$/', $request->path());

            if ($request->expectsJson() || $livewirePath) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'demo_mode' => true,
                ], 403);
            }

            return back()->with('warning', $exception->getMessage());
        });
    })->create();
