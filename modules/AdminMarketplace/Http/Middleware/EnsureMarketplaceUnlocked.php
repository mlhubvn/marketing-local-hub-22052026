<?php

namespace Modules\AdminMarketplace\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMarketplaceUnlocked
{
    public const SESSION_KEY = 'marketplace_unlocked';

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get(self::SESSION_KEY) === true) {
            return $next($request);
        }

        return redirect()->route('admin-marketplace.lock');
    }
}
