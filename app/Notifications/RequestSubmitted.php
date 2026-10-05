<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PRD §12/§13/§21 — customer receipt after a request or enquiry is submitted.
 * Sent to the sender's email address with their reference number.
 *
 * Only the service and quote flows are publicly trackable (the tracking lookup
 * searches those two tables), so enquiries deliberately get no tracking link —
 * promising one would send the customer to a dead end.
 */
class RequestSubmitted extends Notification
{
    use Queueable;

    /**
     * @param  string  $type  'service' | 'quote' | 'enquiry' | 'application'
     */
    public function __construct(
        public readonly string $type,
        public readonly string $reference,
        public readonly string $projectName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = match ($this->type) {
            'quote' => 'Quote Request',
            'enquiry' => 'Enquiry',
            'application' => 'Job Application',
            default => 'Service Request',
        };

        $mail = (new MailMessage)
            ->subject("We received your {$label} ({$this->reference})")
            ->greeting('Thank you for contacting '.config('moaum.company.short_name').'!')
            ->line("Your {$label} regarding \"{$this->projectName}\" has been logged as reference {$this->reference}.")
            ->line('Our team will review it and get back to you shortly.');

        if (in_array($this->type, ['enquiry', 'application'], true)) {
            return $mail
                ->line('Please quote this reference in any reply so we can find your enquiry quickly.');
        }

        return $mail
            ->action('Track your request', route('requests.track', ['reference' => $this->reference]))
            ->line('Keep this reference and the email address you used to check progress anytime.');
    }
}
