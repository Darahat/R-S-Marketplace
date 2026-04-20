<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminLoginAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $ip,
        private string $device,
        private string $loggedInAt,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Admin Login Alert')
            ->greeting('Hello ' . ($notifiable->name ?? 'Admin') . ',')
            ->line('A successful admin login was detected for your account.')
            ->line('IP Address: ' . $this->ip)
            ->line('Device: ' . $this->device)
            ->line('Time: ' . $this->loggedInAt)
            ->line('If this was not you, reset your password immediately and review account activity.');
    }
}
