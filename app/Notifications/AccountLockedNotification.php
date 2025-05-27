<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountLockedNotification extends Notification
{
    use Queueable;

    protected $lockoutMinutes;
    protected $ipAddress;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $lockoutMinutes, string $ipAddress)
    {
        $this->lockoutMinutes = $lockoutMinutes;
        $this->ipAddress = $ipAddress;
    }

    /**
     * Get the notification's delivery channels.
     *
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
        return (new MailMessage)
            ->subject('Account Temporarily Locked - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your account has been temporarily locked due to multiple failed login attempts.')
            ->line('')
            ->line('**Account Details:**')
            ->line('• Email: ' . $notifiable->email)
            ->line('• IP Address: ' . $this->ipAddress)
            ->line('• Locked for: ' . $this->lockoutMinutes . ' minutes')
            ->line('• Unlock time: ' . now()->addMinutes($this->lockoutMinutes)->format('M j, Y \a\t g:i A'))
            ->line('')
            ->line('**Security Information:**')
            ->line('If this was you, please wait for the lockout period to expire before trying again.')
            ->line('If this was not you, someone may be trying to access your account. Please consider:')
            ->line('• Changing your password immediately after the lockout expires')
            ->line('• Enabling two-factor authentication for additional security')
            ->line('• Contacting our support team if you suspect unauthorized access')
            ->line('')
            ->line('Your account will automatically unlock after the lockout period expires.')
            ->salutation('Best regards, The ' . config('app.name') . ' Security Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'lockout_minutes' => $this->lockoutMinutes,
            'ip_address' => $this->ipAddress,
            'unlock_time' => now()->addMinutes($this->lockoutMinutes),
        ];
    }
}