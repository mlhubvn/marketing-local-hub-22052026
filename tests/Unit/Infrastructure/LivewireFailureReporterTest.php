<?php

use App\Support\Http\LivewireFailureReporter;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('livewire csrf token mismatches are reported as 419 failures', function (): void {
    $request = Request::create('/livewire/update', 'POST');

    Log::shouldReceive('warning')
        ->once()
        ->with('Livewire request rejected with 419.', [
            'path' => 'livewire/update',
            'exception' => TokenMismatchException::class,
            'session_driver' => config('session.driver'),
            'session_connection' => config('session.connection'),
        ]);

    LivewireFailureReporter::report(new TokenMismatchException, $request);
});

test('prepared livewire 419 responses retain the original exception class', function (): void {
    $request = Request::create('/livewire/update', 'POST');
    $tokenMismatch = new TokenMismatchException;

    Log::shouldReceive('warning')
        ->once()
        ->with('Livewire request rejected with 419.', [
            'path' => 'livewire/update',
            'exception' => TokenMismatchException::class,
            'session_driver' => config('session.driver'),
            'session_connection' => config('session.connection'),
        ]);

    LivewireFailureReporter::report(new HttpException(419, '', $tokenMismatch), $request);
});
