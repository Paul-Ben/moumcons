<?php

namespace Tests\Feature\Admin;

use App\Enums\QuoteStatus;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Notifications\QuoteAvailable;
use App\Notifications\RequestStatusChanged;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * M1 — admin quote workflow (PRD §13/§25). The guarantees: preparing and
 * sending a quote, and settling one, are separate permissions; a quote cannot
 * be sent empty; the customer sees the price only once it has been sent.
 */
class QuoteRequestTriageTest extends TestCase
{
    use RefreshDatabase;

    /** @param  list<string>  $permissions */
    private function staffWith(array $permissions): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $role = Role::create(['name' => 'Test '.implode('-', $permissions), 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);
        $user->assignRole($role);

        return $user->fresh();
    }

    public function test_queue_requires_the_view_permission(): void
    {
        $staff = $this->staffWith(['view-service-requests']);

        $this->actingAs($staff)->get(route('admin.quote-requests.index'))->assertForbidden();
    }

    public function test_queue_and_detail_render(): void
    {
        $viewer = $this->staffWith(['view-quotes']);
        $quote = QuoteRequest::factory()->create(['project_title' => 'Perimeter fencing for farm']);

        $this->actingAs($viewer)->get(route('admin.quote-requests.index'))
            ->assertOk()->assertSee('Perimeter fencing for farm');

        $this->actingAs($viewer)->get(route('admin.quote-requests.show', $quote))
            ->assertOk()->assertSee('read-only access');
    }

    public function test_updater_without_prepare_permission_cannot_set_the_price(): void
    {
        $staff = $this->staffWith(['view-quotes', 'update-quotes']);
        $quote = QuoteRequest::factory()->create();

        $this->actingAs($staff)
            ->patch(route('admin.quote-requests.update', $quote), ['quoted_amount' => '250000'])
            ->assertForbidden();

        $this->assertNull($quote->fresh()->quoted_amount);
    }

    public function test_preparer_can_price_without_changing_status(): void
    {
        $staff = $this->staffWith(['view-quotes', 'create-quotes']);
        $quote = QuoteRequest::factory()->create();

        $this->actingAs($staff)
            ->patch(route('admin.quote-requests.update', $quote), [
                'quoted_amount' => '250000',
                'quote_message' => 'Includes materials and labour.',
            ])
            ->assertRedirect();

        $quote->refresh();
        $this->assertSame('250000.00', $quote->quoted_amount);
        $this->assertNull($quote->quote_sent_at);
        $this->assertFalse($quote->quoteIsVisibleToCustomer());
    }

    public function test_cannot_send_an_empty_quote(): void
    {
        $staff = $this->staffWith(['view-quotes', 'update-quotes', 'create-quotes']);
        $quote = QuoteRequest::factory()->create();

        $this->actingAs($staff)
            ->patch(route('admin.quote-requests.update', $quote), ['status' => 'quote_sent'])
            ->assertSessionHasErrors('status');

        $this->assertSame(QuoteStatus::Requested, $quote->fresh()->status);
    }

    public function test_sending_requires_prepare_permission(): void
    {
        $staff = $this->staffWith(['view-quotes', 'update-quotes']);
        $quote = QuoteRequest::factory()->create(['quoted_amount' => 1000]);

        $this->actingAs($staff)
            ->patch(route('admin.quote-requests.update', $quote), ['status' => 'quote_sent'])
            ->assertForbidden();
    }

    public function test_sending_stamps_the_quote_and_emails_the_customer(): void
    {
        Notification::fake();
        $staff = $this->staffWith(['view-quotes', 'update-quotes', 'create-quotes']);
        $quote = QuoteRequest::factory()->create(['email' => 'buyer@example.com']);

        $this->actingAs($staff)
            ->patch(route('admin.quote-requests.update', $quote), [
                'status' => 'quote_sent',
                'quoted_amount' => '1500000',
                'notify_customer' => '0',
            ])
            ->assertRedirect(route('admin.quote-requests.show', $quote));

        $quote->refresh();
        $this->assertSame(QuoteStatus::QuoteSent, $quote->status);
        $this->assertNotNull($quote->quote_sent_at);
        $this->assertTrue($quote->quoteIsVisibleToCustomer());

        Notification::assertSentTo(
            new AnonymousNotifiable,
            QuoteAvailable::class,
            fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === 'buyer@example.com'
        );
        Notification::assertNotSentTo(new AnonymousNotifiable, RequestStatusChanged::class);
    }

    public function test_settling_requires_close_permission(): void
    {
        $staff = $this->staffWith(['view-quotes', 'update-quotes', 'create-quotes']);
        $quote = QuoteRequest::factory()->create(['status' => QuoteStatus::QuoteSent, 'quoted_amount' => 10, 'quote_sent_at' => now()]);

        $this->actingAs($staff)
            ->patch(route('admin.quote-requests.update', $quote), ['status' => 'accepted'])
            ->assertForbidden();

        $closer = $this->staffWith(['view-quotes', 'update-quotes', 'close-quotes']);

        $this->actingAs($closer)
            ->patch(route('admin.quote-requests.update', $quote), ['status' => 'accepted'])
            ->assertRedirect();

        $this->assertSame(QuoteStatus::Accepted, $quote->fresh()->status);
    }

    public function test_tracking_page_shows_the_quote_only_after_it_is_sent(): void
    {
        $quote = QuoteRequest::factory()->create([
            'email' => 'buyer@example.com',
            'status' => QuoteStatus::QuotePreparation,
            'quoted_amount' => 987654,
        ]);

        $lookup = ['reference' => $quote->reference, 'email' => 'buyer@example.com'];

        $this->post(route('requests.track.lookup'), $lookup)->assertOk()->assertDontSee('987,654');

        $quote->update(['status' => QuoteStatus::QuoteSent, 'quote_sent_at' => now()]);

        $this->post(route('requests.track.lookup'), $lookup)->assertOk()->assertSee('987,654.00');
    }
}
