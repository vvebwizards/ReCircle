<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FailedFacialVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private User $user,
        private int $failedAttempts,
        private string $ipAddress,
        private string $userAgent
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Write to database first so the admin UI always has a record,
        // even if the mail channel fails in the same synchronous dispatch.
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'ReCircle');

        return (new MailMessage)
            ->subject('⚠️ Security Alert: Failed Facial Verification Attempt')
            ->greeting('Security Alert')
            ->line("A failed facial verification attempt was detected for user account: {$this->user->name} ({$this->user->email})")
            ->line('Details:')
            ->line("• User ID: {$this->user->id}")
            ->line("• Failed Attempts: {$this->failedAttempts}")
            ->line("• IP Address: {$this->ipAddress}")
            ->line('• User Agent: '.substr($this->userAgent, 0, 100).(strlen($this->userAgent) > 100 ? '...' : ''))
            ->line('• Timestamp: '.now()->format('Y-m-d H:i:s T'))
            ->line('')
            ->line('The user account has been temporarily locked for security purposes.')
            ->action('Review User Management', url('/admin/users/'.$this->user->id))
            ->line('If this was a legitimate user, they can wait for the lockout period to expire or contact support.')
            ->line('If you suspect malicious activity, consider extending the lockout or blocking the account.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'failed_facial_verification',
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'failed_attempts' => $this->failedAttempts,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'locked_until' => $this->user->locked_until?->toIso8601String(),
            'message' => "Failed facial verification attempt for user {$this->user->name} ({$this->user->email}). Account temporarily locked.",
        ];
    }
}
