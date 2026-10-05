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
        public readonly string $type,       // 'service' | 'quote' | 'enquiry'
        public readonly string $reference,
        public readonly string $requesterName,
        public readonly string $divisionName,
        public readonly ?string $url = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = match ($this->type) {
            'quote' => 'quote request',
            'enquiry' => 'enquiry',
            'application' => 'job application',
            default => 'service request',
        };

        return (new MailMessage)
            ->subject('New '.$label.': '.$this->reference)
            ->line("{$this->requesterName} submitted a new {$label} ({$this->divisionName}).")
            ->action('Open in admin', $this->url ?? route('admin.dashboard'));
    }
}
