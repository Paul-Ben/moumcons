<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PRD §12/§13 — customer receipt after a service/quote request is submitted.
 * Sent to the requester's email with their reference number + tracking link.
 */
class RequestSubmitted extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $type,          // 'service' | 'quote'
        public readonly string $reference,
        public readonly string $projectName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->type === 'quote' ? 'Quote Request' : 'Service Request';

        return (new MailMessage)
            ->subject("We received your {$label} ({$this->reference})")
            ->greeting('Thank you for contacting ' . config('moaum.company.short_name') . '!')
            ->line("Your {$label} for \"{$this->projectName}\" has been logged as reference {$this->reference}.")
            ->line('Our team will review it and get back to you shortly.')
            ->action('Track your request', route('requests.track', ['reference' => $this->reference]))
            ->line('Keep this reference and the email address you used to check progress anytime.');
    }
}
