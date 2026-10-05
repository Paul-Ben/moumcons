<?php

namespace App\Notifications;

use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PRD §21/§25 — tells a member of staff they now own an enquiry.
 *
 * Sent when an enquiry is assigned, so ownership arriving in someone's inbox is
 * never a surprise discovered later in the queue.
 */
class EnquiryAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Enquiry $enquiry,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You have been assigned enquiry '.$this->enquiry->reference)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line($this->enquiry->name.' ('.$this->enquiry->email.') sent an enquiry titled "'.$this->enquiry->subject.'".')
            ->action('Review enquiry', route('admin.enquiries.show', $this->enquiry))
            ->line('Priority: '.$this->enquiry->priority->label().'.');
    }
}
