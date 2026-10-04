<?php

namespace Tests\Feature\Requests;

use App\Models\BusinessDivision;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Notifications\AdminNewRequestAlert;
use App\Notifications\RequestSubmitted;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class QuoteRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The triage alert is only delivered to seeded staff, so the tests that
     * assert on it need RBAC and the local admin account in the database.
     */
    private function seedRbac(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(AdminUserSeeder::class);
    }

    public function test_quote_request_form_renders(): void
    {
        $this->get('/request-quote')
            ->assertOk()
            ->assertSee('Request a Quote');
    }

    public function test_visitor_can_submit_quote_request(): void
    {
        Notification::fake();
        $this->seedRbac();

        $division = BusinessDivision::factory()->create();
        $admin = User::where('email', 'admin@moaum.test')->first();

        $response = $this->post('/request-quote', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+234 801 234 5678',
            'business_division_id' => $division->id,
            'project_title' => 'Quarterly offices deep clean',
            'location' => 'Makurdi',
            'requirements' => str_repeat('Clean two office floors weekly. ', 2),
            'estimated_quantity' => '2 floors',
            'desired_start_date' => '2026-11-01',
            'desired_completion_date' => '2026-12-01',
            'budget_range' => '₦1m',
            'consent' => '1',
        ]);

        $quote = QuoteRequest::where('email', 'jane@example.com')->first();
        $this->assertNotNull($quote);

        $response->assertRedirect(route('requests.quote.confirmation', $quote->reference));
        $this->assertStringStartsWith('QTE-', $quote->reference);
        $this->assertSame('requested', $quote->status->value);

        Notification::assertSentOnDemand(
            RequestSubmitted::class,
            fn (RequestSubmitted $notification, array $channels, $notifiable) => $notifiable->routeNotificationFor('mail') === 'jane@example.com'
        );
        Notification::assertSentTo($admin, AdminNewRequestAlert::class);
    }

    public function test_quote_request_without_service_is_accepted(): void
    {
        Notification::fake();

        $division = BusinessDivision::factory()->create();

        $this->post('/request-quote', [
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'phone' => '+234 801 234 5678',
            'business_division_id' => $division->id,
            'project_title' => 'Feasibility study',
            'requirements' => str_repeat('We need a feasibility study. ', 2),
            'consent' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('quote_requests', [
            'email' => 'john@example.com',
            'service_id' => null,
        ]);
    }

    public function test_completion_date_before_start_date_is_rejected(): void
    {
        $division = BusinessDivision::factory()->create();

        $this->post('/request-quote', [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'phone' => '123',
            'business_division_id' => $division->id,
            'project_title' => 'Late project',
            'requirements' => str_repeat('Scope description here. ', 2),
            'desired_start_date' => '2026-12-01',
            'desired_completion_date' => '2026-11-01',
            'consent' => '1',
        ])->assertSessionHasErrors('desired_completion_date');

        $this->assertDatabaseCount('quote_requests', 0);
    }

    public function test_honeypot_field_rejects_bots(): void
    {
        $division = BusinessDivision::factory()->create();

        $this->post('/request-quote', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'phone' => '123',
            'business_division_id' => $division->id,
            'project_title' => 'Spam',
            'requirements' => str_repeat('Automated spam. ', 2),
            'consent' => '1',
            'website' => 'https://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertDatabaseCount('quote_requests', 0);
    }

    public function test_confirmation_page_shows_reference(): void
    {
        $quote = QuoteRequest::factory()->create();

        $this->get('/request-quote/'.$quote->reference)
            ->assertOk()
            ->assertSee($quote->reference)
            ->assertSee('Quote Request Received');
    }

    public function test_confirmation_page_404s_for_unknown_reference(): void
    {
        $this->get('/request-quote/NOT-EXIST')->assertNotFound();
    }
}
