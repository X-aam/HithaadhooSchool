<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailOtpCode extends Notification
{
    public function __construct(
        private readonly string $code,
        private readonly int $ttlMinutes,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your '.config('app.name').' login code')
            ->line('Use this verification code to finish signing in:')
            ->line('# '.$this->code)
            ->line("This code expires in {$this->ttlMinutes} minutes. If you did not try to sign in, you can safely ignore this email.");
    }
}
