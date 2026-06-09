<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAccountInvitation extends Notification
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Your Million Memory Project Reporting account')
            ->greeting('Welcome to Million Memory Project Reporting')
            ->line('An administrator has created a reporting account for you.')
            ->line('To get started, set your own password using the button below.')
            ->action('Set your password', $url)
            ->line('This link will expire in 60 minutes. If it expires, use "Forgot your password?" on the sign-in page to request a new one.');
    }
}
