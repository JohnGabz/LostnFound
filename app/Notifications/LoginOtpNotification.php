<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginOtpNotification extends Notification
{
    use Queueable;

    protected $otpCode;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $otpCode)
    {
        $this->otpCode = $otpCode;
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
            ->subject('Your Login Verification Code - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We received a login attempt for your account. To complete the login process, please use the verification code below:')
            ->line('')
            ->line('**Your verification code is: ' . $this->otpCode . '**')
            ->line('')
            ->line('This code will expire in 5 minutes for your security.')
            ->line('If you did not attempt to log in, please ignore this email and consider changing your password.')
            ->line('')
            ->line('For security reasons, never share this code with anyone.')
            ->salutation('Best regards, The ' . config('app.name') . ' Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'otp_code' => $this->otpCode,
        ];
    }
}