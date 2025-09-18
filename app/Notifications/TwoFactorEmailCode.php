<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorEmailCode extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $code) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expires = 10; // minutes

        return (new MailMessage)
            ->subject('Your sign-in code')
            ->view('emails.twofa_code', [
                'user' => $notifiable,
                'code' => $this->code,
                'expiresIn' => $expires,
            ])
            ->text('emails.twofa_code_plain', [
                'user' => $notifiable,
                'code' => $this->code,
                'expiresIn' => $expires,
            ]);
    }
}
