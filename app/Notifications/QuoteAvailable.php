<?php

namespace App\Notifications;

use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PRD §25 "Quote available" — sent once, when a quote request moves to
 * Quote Sent. Carries the amount and message staff prepared.
 */
class QuoteAvailable extends Notification
{
    use Queueable;

    public function __construct(
        public readonly QuoteRequest $quote,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Your quote is ready ({$this->quote->reference})")
            ->greeting('Hello '.$this->quote->name.'!')
            ->line("We have prepared a quote for \"{$this->quote->project_title}\".");

        if ($this->quote->quoted_amount !== null) {
            $mail->line('Quoted amount: ₦'.number_format((float) $this->quote->quoted_amount, 2));
        }

        if (filled($this->quote->quote_message)) {
            $mail->line($this->quote->quote_message);
        }

        if ($this->quote->quote_valid_until) {
            $mail->line('This quote is valid until '.$this->quote->quote_valid_until->format('j F Y').'.');
        }

        return $mail
            ->action('View your quote', route('requests.track', ['reference' => $this->quote->reference]))
            ->line('Reply to this email or contact us to accept the quote or ask questions.');
    }
}
