<?php

namespace App\Notifications;

use App\Support\Mail\AuthMailMessageBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNewUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return AuthMailMessageBuilder::forNotifiable($notifiable, fn (): MailMessage => (new MailMessage)
            ->subject(__('Welcome to :app', ['app' => config('app.name', 'MKT')]))
            ->greeting(__('Welcome, :name!', ['name' => $notifiable->name ?: $notifiable->username ?: __('there')]))
            ->line(__('Your account is ready and you can now start using the platform.'))
            ->action(__('Open dashboard'), route('portal.dashboard'))
            ->line(__('Thank you for joining us.')));
    }
}
