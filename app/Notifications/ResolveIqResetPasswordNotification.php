<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResolveIqResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $token)
    {
        //
    }

    /**
     * Get the notification delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the password reset email.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $expiresIn = config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Reset your ResolveIQ password')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We received a request to reset the password for your ResolveIQ account.')
            ->line('Click the button below to create a new password and regain access to your helpdesk workspace.')
            ->action('Reset Password', $resetUrl)
            ->line('This password reset link will expire in ' . $expiresIn . ' minutes.')
            ->line('If you did not request a password reset, you can safely ignore this email. Your account will stay protected.')
            ->salutation('Regards, ResolveIQ Support');
    }
}
