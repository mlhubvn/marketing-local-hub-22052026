<?php

namespace Modules\AdminMarketplace\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AdminMarketplace\Http\Middleware\EnsureMarketplaceUnlocked;

#[Title('Marketplace locked')]
class MarketplaceLock extends Component
{
    public string $password = '';

    public function mount()
    {
        if (session()->get(EnsureMarketplaceUnlocked::SESSION_KEY) === true) {
            return redirect()->route('admin-marketplace.index');
        }
    }

    public function unlock()
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        $throttleKey = 'marketplace-unlock:'.auth()->id();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'password' => __('Too many attempts. Please wait :seconds seconds and try again.', [
                    'seconds' => RateLimiter::availableIn($throttleKey),
                ]),
            ]);
        }

        $configured = (string) config('custommlhub.first_user.password');

        if ($configured === '') {
            throw ValidationException::withMessages([
                'password' => __('The marketplace password is not configured. Set MLHUB_FIRST_USER_PASSWORD in the environment variables.'),
            ]);
        }

        if (! hash_equals($configured, $this->password)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'password' => __('The password is incorrect.'),
            ]);
        }

        RateLimiter::clear($throttleKey);
        session()->put(EnsureMarketplaceUnlocked::SESSION_KEY, true);

        return redirect()->route('admin-marketplace.index');
    }

    public function render(): View
    {
        return view('adminmarketplace::lock')->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Marketplace locked'),
        ]);
    }
}
