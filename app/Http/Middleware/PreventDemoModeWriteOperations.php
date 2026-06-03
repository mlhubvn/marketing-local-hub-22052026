<?php

namespace App\Http\Middleware;

use App\Exceptions\DemoModeRestrictedException;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventDemoModeWriteOperations
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.demo_mode')) {
            return $next($request);
        }

        if ($this->isBlockedLivewireTemporaryUploadRequest($request)) {
            return $this->blockedResponse($request);
        }

        if ($this->isLivewireRequest($request)) {
            return $next($request);
        }

        if ($this->isBlockedStandardRequest($request)) {
            return $this->blockedResponse($request);
        }

        return $next($request);
    }

    protected function isBlockedStandardRequest(Request $request): bool
    {
        if ($this->isLivewireRequest($request) || $request->isMethodSafe()) {
            return false;
        }

        return $this->isWorkspaceRequest($request);
    }

    protected function isWorkspaceRequest(Request $request): bool
    {
        $routeName = (string) ($request->route()?->getName() ?? '');

        if (
            $routeName === 'dashboard'
            || str_starts_with($routeName, 'dashboard.')
            || str_starts_with($routeName, 'portal.')
            || str_starts_with($routeName, 'admin.')
            || str_starts_with($routeName, 'admin-')
            || str_starts_with($routeName, 'settings.')
        ) {
            return true;
        }

        $path = trim($this->requestPath($request), '/');

        return $path === 'dashboard'
            || str_starts_with($path, 'dashboard/')
            || str_starts_with($path, 'portal/')
            || str_starts_with($path, 'admin/')
            || str_starts_with($path, 'admin/settings/');
    }

    protected function isBlockedLivewireTemporaryUploadRequest(Request $request): bool
    {
        if (! $request->route()?->named('*livewire.upload-file')) {
            return false;
        }

        return ! $request->isMethodSafe();
    }

    protected function isLivewireUpdate(Request $request): bool
    {
        $path = $request->path();

        return $path === 'livewire/update'
            || (bool) preg_match('/^livewire-[a-f0-9]+\/update$/', $path);
    }

    protected function isLivewireRequest(Request $request): bool
    {
        return $this->isLivewireUpdate($request)
            || $request->hasHeader('X-Livewire')
            || is_array($request->input('components'));
    }

    protected function requestPath(Request $request): string
    {
        if (! $this->isLivewireRequest($request)) {
            return $request->path();
        }

        $snapshot = json_decode((string) $request->input('components.0.snapshot', ''), true);
        $memoPath = data_get($snapshot, 'memo.path');

        if (is_string($memoPath) && $memoPath !== '') {
            return ltrim($memoPath, '/');
        }

        $referer = (string) $request->headers->get('referer', '');

        if ($referer === '') {
            return $request->path();
        }

        $path = parse_url($referer, PHP_URL_PATH);

        return is_string($path) && $path !== '' ? ltrim($path, '/') : $request->path();
    }

    protected function blockedResponse(Request $request): JsonResponse|RedirectResponse
    {
        $exception = DemoModeRestrictedException::withDefaultMessage();
        $message = $exception->getMessage();

        if ($request->expectsJson() || $this->isLivewireRequest($request)) {
            return response()->json([
                'message' => $message,
                'demo_mode' => true,
            ], 403);
        }

        return back()->with('warning', $message);
    }
}
