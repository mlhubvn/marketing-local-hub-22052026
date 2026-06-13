<?php

namespace App\Support\Http;

use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Throwable;

class LivewireFailureReporter
{
    public static function report(Throwable $exception, Request $request): void
    {
        if (! self::isLivewireRequest($request) || self::statusCode($exception) !== 419) {
            return;
        }

        Log::warning('Livewire request rejected with 419.', [
            'path' => $request->path(),
            'exception' => $exception->getPrevious() instanceof Throwable
                ? $exception->getPrevious()::class
                : $exception::class,
            'session_driver' => config('session.driver'),
            'session_connection' => config('session.connection'),
        ]);
    }

    protected static function isLivewireRequest(Request $request): bool
    {
        return $request->hasHeader('X-Livewire')
            || is_array($request->input('components'))
            || $request->is('livewire/update')
            || (bool) preg_match('/^livewire-[a-f0-9]+\/update$/', $request->path());
    }

    protected static function statusCode(Throwable $exception): int
    {
        if ($exception instanceof TokenMismatchException) {
            return 419;
        }

        return method_exists($exception, 'getStatusCode')
            ? (int) $exception->getStatusCode()
            : 0;
    }
}
