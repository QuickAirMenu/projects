<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()], false));

        return (new MailMessage)
            ->subject(__('mail.reset_password_subject'))
            ->greeting(__('mail.reset_password_greeting', ['name' => $notifiable->name]))
            ->line(__('mail.reset_password_line1'))
            ->action(__('mail.reset_password_action'), $url)
            ->line(__('mail.reset_password_line2', ['minutes' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60)]));
    }
}
