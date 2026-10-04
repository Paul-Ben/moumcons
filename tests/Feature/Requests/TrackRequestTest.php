<?php

namespace Tests\Feature\Requests;

use App\Models\QuoteRequest;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_track_form_renders(): void
    {
        $this->get('/track-request')
            ->assertOk()
            ->assertSee('Track Your Request');
    }

    public function test_reference_from_link_is_prefilled(): void
    {
        $this->get('/track-request/SRQ-261004-AB12')
            ->assertOk()
            ->assertSee('value="SRQ-261004-AB12"', escape: false);
    }

    public function test_visitor_can_track_service_request_with_reference_and_email(): void
    {
        $request = ServiceRequest::factory()->create();

        $this->post('/track-request', [
            'reference' => strtolower($request->reference),
            'email' => strtoupper($request->email),
        ])
            ->assertOk()
            ->assertSee($request->reference)
            ->assertSee('Service request');
    }

    public function test_visitor_can_track_quote_request(): void
    {
        $quote = QuoteRequest::factory()->create();

        $this->post('/track-request', [
            'reference' => $quote->reference,
            'email' => $quote->email,
        ])
            ->assertOk()
            ->assertSee($quote->reference)
            ->assertSee('Quote request');
    }

    public function test_wrong_email_shows_error_and_no_result(): void
    {
        $request = ServiceRequest::factory()->create();

        $this->post('/track-request', [
            'reference' => $request->reference,
            'email' => 'someone-else@example.com',
        ])
            ->assertStatus(422)
            ->assertSee('No request found');
    }

    public function test_unknown_reference_shows_error(): void
    {
        ServiceRequest::factory()->create();

        $this->post('/track-request', [
            'reference' => 'SRQ-000000-ZZZZ',
            'email' => 'ghost@example.com',
        ])->assertStatus(422);
    }

    public function test_tracking_requires_both_fields(): void
    {
        $this->post('/track-request', [
            'reference' => '',
            'email' => '',
        ])->assertSessionHasErrors(['reference', 'email']);
    }
}
