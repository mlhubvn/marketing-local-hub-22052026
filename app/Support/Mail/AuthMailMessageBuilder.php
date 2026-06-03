<?php

namespace App\Support\Mail;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;
use Modules\AdminUser\Models\User;

class AuthMailMessageBuilder
{
    public static function register(): void
    {
        ResetPassword::toMailUsing(
            fn (object $notifiable, string $token): MailMessage => self::resetPassword($notifiable, $token),
        );

        VerifyEmail::toMailUsing(
            fn (object $notifiable, string $url): MailMessage => self::verifyEmail($notifiable, $url),
        );
    }

    public static function resetPassword(object $notifiable, string $token): MailMessage
    {
        return self::forNotifiable($notifiable, function () use ($notifiable, $token): MailMessage {
            $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

            return (new MailMessage)
                ->subject(__('Reset your password'))
                ->line(__('You are receiving this email because we received a password reset request for your account.'))
                ->action(__('Reset Password'), self::resetPasswordUrl($notifiable, $token))
                ->line(__('This password reset link will expire in :count minutes.', ['count' => $expire]))
                ->line(__('If you did not request a password reset, no further action is required.'));
        });
    }

    public static function verifyEmail(object $notifiable, string $url): MailMessage
    {
        return self::forNotifiable($notifiable, fn (): MailMessage => (new MailMessage)
            ->subject(__('Verify your email address'))
            ->line(__('Please click the button below to verify your email address.'))
            ->action(__('Verify Email Address'), $url)
            ->line(__('If you did not create an account, no further action is required.')));
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function forNotifiable(object $notifiable, callable $callback): mixed
    {
        $locale = self::resolveLocale($notifiable);
        $original = App::getLocale();

        if ($locale !== null && $locale !== $original) {
            App::setLocale($locale);
        }

        try {
            return $callback();
        } finally {
            if ($locale !== null && $locale !== $original) {
                App::setLocale($original);
            }
        }
    }

    protected static function resetPasswordUrl(object $notifiable, string $token): string
    {
        if (ResetPassword::$createUrlCallback !== null) {
            return (string) call_user_func(ResetPassword::$createUrlCallback, $notifiable, $token);
        }

        return url(route('password.reset', [
            'token' => $token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }

    protected static function resolveLocale(object $notifiable): ?string
    {
        if (! $notifiable instanceof User) {
            return null;
        }

        $locale = strtolower(trim((string) $notifiable->preferredLocale()));

        return $locale !== '' ? $locale : null;
    }
}
