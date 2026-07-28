<?php

namespace App\Livewire\Auth;

use App\Notifications\WelcomeNewUserNotification;
use App\Concerns\PasswordValidationRules;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AdminUser\Actions\Fortify\CreateNewUser;
use Modules\AdminUser\Concerns\ProfileValidationRules;
use Modules\AdminUser\Models\User;

#[Title('Register')]
class RegisterPage extends Component
{
    use PasswordValidationRules;
    use ProfileValidationRules;

    protected const DEFAULT_TIMEZONE = 'Asia/Ho_Chi_Minh';

    public string $name = '';

    public string $email = '';

    public string $username = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $timezone = '';

    public string $referral_code = '';

    public bool $accept_terms = false;

    public string $captchaToken = '';

    public function mount(): void
    {
        abort_unless(auth_signup_enabled(), 404);

        $this->timezone = self::DEFAULT_TIMEZONE;
        $this->referral_code = (string) session('affiliate_referral_code', '');
    }

    public function register(CreateNewUser $creator): mixed
    {
        $validated = $this->validate($this->rules(), [], [
            'accept_terms' => __('terms and conditions'),
            'referral_code' => __('referral code'),
        ]);

        $referralCode = strtoupper(trim($validated['referral_code']));
        $referrer = User::query()->whereRaw('UPPER(referral_code) = ?', [$referralCode])->first();

        if (! $referrer) {
            $this->addError('referral_code', __('This referral code is invalid.'));

            return null;
        }

        if (function_exists('captcha_enabled') && captcha_enabled()) {
            if (! captcha_verify_token(
                token: $this->captchaToken,
                host: request()->getHost(),
                ip: request()->ip(),
            )) {
                $this->addError('captchaToken', captcha_error_message());
                $this->captchaToken = '';
                $this->dispatch('captcha-reset');

                return null;
            }
        }

        session([
            'affiliate_referral_code' => $referrer->referral_code,
            'affiliate_referrer_user_id' => $referrer->id,
        ]);

        $user = $creator->create([
            ...$validated,
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['email']),
            'timezone' => self::DEFAULT_TIMEZONE,
            'accept_terms' => $validated['accept_terms'] ? '1' : '0',
            'password_confirmation' => $this->password_confirmation,
        ]);

        if (! auth_activation_email_enabled()) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        event(new Registered($user));

        if (auth_welcome_email_enabled()) {
            $user->notify(new WelcomeNewUserNotification);
        }

        Auth::guard(config('fortify.guard'))->login($user);
        request()->session()->regenerate();

        return $this->redirect($this->registerRedirectUrl($user), navigate: false);
    }

    protected function registerRedirectUrl(User $user): string
    {
        if (auth_activation_email_enabled() && ! $user->hasVerifiedEmail() && Route::has('verification.notice')) {
            return route('verification.notice');
        }

        return Fortify::redirects('register') ?? config('fortify.home');
    }

    protected function rules(): array
    {
        return [
            ...$this->profileRules(),
            'timezone' => ['required', 'string', 'timezone:all', Rule::in(timezone_options())],
            'referral_code' => ['required', 'string', 'max:20'],
            'accept_terms' => ['accepted'],
            'password' => $this->passwordRules(),
        ];
    }

    public function render(): View
    {
        return view(theme_view('livewire.auth.register-page', 'guest'));
    }
}
