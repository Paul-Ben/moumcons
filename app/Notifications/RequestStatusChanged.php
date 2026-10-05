<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PRD §25 "Request status changed" — customer update when staff move a
 * service or quote request along. Only the public status label is shared;
 * internal notes never leave the admin.
 */
class RequestStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $type  'service' | 'quote'
     */
    public function __construct(
        public readonly string $type,
        public readonly string $reference,
        public readonly string $statusLabel,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->type === 'quote' ? 'Quote Request' : 'Service Request';

        return (new MailMessage)
            ->subject("Update on your {$label} ({$this->reference})")
            ->greeting('Hello!')
            ->line("The status of your {$label} {$this->reference} is now: {$this->statusLabel}.")
            ->action('Track your request', route('requests.track', ['reference' => $this->reference]))
            ->line('You will need the email address you used when submitting the request.');
    }
}
