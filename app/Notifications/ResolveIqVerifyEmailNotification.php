<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class ResolveIqVerifyEmailNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $relativeUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
            false
        );

        $appUrl = (string) config('app.url', 'http://127.0.0.1:8000');

        $verificationUrl = rtrim($appUrl, '/') . $relativeUrl;

        return (new MailMessage)
            ->subject('Verify your ResolveIQ email address')
            ->greeting('Welcome to ResolveIQ, ' . $notifiable->name . '!')
            ->line('Thanks for creating your ResolveIQ account.')
            ->line('Please verify your email address so you can access the full helpdesk workflow, create tickets, and receive important ticket notifications.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('If you did not create this account, you can safely ignore this email.')
            ->salutation('Regards, ResolveIQ Support');
    }
}
