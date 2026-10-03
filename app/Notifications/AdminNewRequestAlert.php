<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** PRD §12/§13/§30 — alert triage users when a new request arrives. */
class AdminNewRequestAlert extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $type,       // 'service' | 'quote'
        public readonly string $reference,
        public readonly string $requesterName,
        public readonly string $divisionName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New {$this->type} request: {$this->reference}")
            ->line("{$this->requesterName} submitted a " . ucfirst($this->type) . " request for {$this->divisionName}.")
            ->action('Open triage inbox', route('admin.dashboard'));
    }
}
