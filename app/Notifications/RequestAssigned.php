<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PRD §12/§13/§25 — tells a member of staff they now own a service or quote
 * request. The enquiry equivalent is EnquiryAssigned.
 */
class RequestAssigned extends Notification
{
    use Queueable;

    /**
     * @param  string  $type  'service' | 'quote'
     */
    public function __construct(
        public readonly string $type,
        public readonly string $reference,
        public readonly string $requesterName,
        public readonly string $summary,
        public readonly string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->type === 'quote' ? 'quote request' : 'service request';

        return (new MailMessage)
            ->subject("You have been assigned {$label} {$this->reference}")
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("{$this->requesterName} submitted a {$label}: \"{$this->summary}\".")
            ->action('Review request', $this->url);
    }
}
