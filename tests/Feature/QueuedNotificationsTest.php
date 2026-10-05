<?php

namespace Tests\Feature;

use App\Enums\QuoteStatus;
use App\Models\Enquiry;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Notifications\AdminNewRequestAlert;
use App\Notifications\EnquiryAssigned;
use App\Notifications\QuoteAvailable;
use App\Notifications\RequestAssigned;
use App\Notifications\RequestStatusChanged;
use App\Notifications\RequestSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * M12 — PRD §25/§35 queued email. Every notification must be queueable, and
 * must survive serialisation (the sync queue in tests serialises for real),
 * including those carrying models in readonly properties.
 */
class QueuedNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_notification_is_queued(): void
    {
        foreach ([AdminNewRequestAlert::class, EnquiryAssigned::class, QuoteAvailable::class, RequestAssigned::class, RequestStatusChanged::class, RequestSubmitted::class] as $class) {
            $this->assertContains(ShouldQueue::class, class_implements($class), "{$class} should be queued.");
        }
    }

    public function test_model_carrying_notifications_survive_the_queue(): void
    {
        $quote = QuoteRequest::factory()->create(['status' => QuoteStatus::QuoteSent, 'quoted_amount' => 125000, 'quote_sent_at' => now()]);
        $enquiry = Enquiry::factory()->create();
        $staff = User::factory()->create();

        Notification::route('mail', 'buyer@example.com')->notify(new QuoteAvailable($quote));
        $staff->notify(new EnquiryAssigned($enquiry));

        $messages = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(2, $messages);
        $this->assertStringContainsString('125,000.00', $messages[0]->getOriginalMessage()->getHtmlBody());
    }
}
