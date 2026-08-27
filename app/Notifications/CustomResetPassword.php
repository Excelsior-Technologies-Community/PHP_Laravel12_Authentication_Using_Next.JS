<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomResetPassword extends Notification
{
    public function __construct(
        protected string $url
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset Your Password')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You requested a password reset.')
            ->action(
                'Reset Password',
                $this->url
            )
            ->line(
                'This password reset link will expire in 60 minutes.'
            )
            ->line(
                'If you did not request this password reset, no further action is required.'
            );
    }
}