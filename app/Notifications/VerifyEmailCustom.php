<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyEmailCustom extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $expires = 60;
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes($expires),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Use a custom Blade view to match app UI
        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->view('emails.verify', [
                'user' => $notifiable,
                'verifyUrl' => $verifyUrl,
                'expiresIn' => $expires,
            ])
            ->text('emails.verify_plain', [
                'user' => $notifiable,
                'verifyUrl' => $verifyUrl,
                'expiresIn' => $expires,
            ]);
    }
}
