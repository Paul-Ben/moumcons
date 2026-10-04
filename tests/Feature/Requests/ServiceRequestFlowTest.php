<?php

namespace Tests\Feature\Requests;

use App\Models\BusinessDivision;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\AdminNewRequestAlert;
use App\Notifications\RequestSubmitted;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceRequestFlowTest extends TestCase
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

    public function test_service_request_form_renders(): void
    {
        $this->get('/request-service')
            ->assertOk()
            ->assertSee('Request a Service');
    }

    public function test_visitor_can_submit_service_request(): void
    {
        Notification::fake();
        Storage::fake('private');
        $this->seedRbac();

        $division = BusinessDivision::factory()->create();
        $admin = User::where('email', 'admin@moaum.test')->first();

        $response = $this->post('/request-service', [
            'name' => 'Jane Doe',
            'organization' => 'Acme Ltd',
            'email' => 'jane@example.com',
            'phone' => '+234 801 234 5678',
            'business_division_id' => $division->id,
            'location' => 'Makurdi',
            'requirements' => str_repeat('Print 500 brochures. ', 3),
            'budget_range' => '₦500k',
            'attachment' => UploadedFile::fake()->create('specs.pdf', 100, 'application/pdf'),
            'consent' => '1',
        ]);

        $request = ServiceRequest::where('email', 'jane@example.com')->first();
        $this->assertNotNull($request);

        $response->assertRedirect(route('requests.service.confirmation', $request->reference));
        $this->assertStringStartsWith('SRQ-', $request->reference);
        $this->assertSame('new', $request->status->value);
        $this->assertNotEmpty($request->attachment);
        Storage::disk('private')->assertExists($request->attachment);

        Notification::assertSentOnDemand(
            RequestSubmitted::class,
            fn (RequestSubmitted $notification, array $channels, $notifiable) => $notifiable->routeNotificationFor('mail') === 'jane@example.com'
        );
        Notification::assertSentTo($admin, AdminNewRequestAlert::class);
    }

    public function test_service_request_can_be_submitted_without_service_or_attachment(): void
    {
        Notification::fake();

        $division = BusinessDivision::factory()->create();

        $this->post('/request-service', [
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'phone' => '+234 801 234 5678',
            'business_division_id' => $division->id,
            'requirements' => str_repeat('General consultation. ', 2),
            'consent' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('service_requests', [
            'email' => 'john@example.com',
            'service_id' => null,
            'attachment' => null,
        ]);
    }

    public function test_honeypot_field_rejects_bots(): void
    {
        $division = BusinessDivision::factory()->create();

        $this->post('/request-service', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'phone' => '123',
            'business_division_id' => $division->id,
            'requirements' => str_repeat('Automated spam. ', 2),
            'consent' => '1',
            'website' => 'https://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_consent_is_required(): void
    {
        $division = BusinessDivision::factory()->create();

        $this->post('/request-service', [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'phone' => '123',
            'business_division_id' => $division->id,
            'requirements' => str_repeat('Need a service. ', 2),
        ])->assertSessionHasErrors('consent');
    }

    public function test_unknown_division_is_rejected(): void
    {
        $this->post('/request-service', [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'phone' => '123',
            'business_division_id' => 99999,
            'requirements' => str_repeat('Need a service. ', 2),
            'consent' => '1',
        ])->assertSessionHasErrors('business_division_id');

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_service_slug_preselects_service_in_form(): void
    {
        $service = Service::factory()->create();

        $this->get('/request-service?service='.$service->slug)
            ->assertOk()
            ->assertSee('pre-selected');
    }

    public function test_confirmation_page_shows_reference(): void
    {
        $request = ServiceRequest::factory()->create();

        $this->get('/request-service/'.$request->reference)
            ->assertOk()
            ->assertSee($request->reference)
            ->assertSee('Service Request Received');
    }

    public function test_confirmation_page_404s_for_unknown_reference(): void
    {
        $this->get('/request-service/NOT-EXIST')->assertNotFound();
    }
}
