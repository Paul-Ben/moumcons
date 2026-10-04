<?php

namespace Tests\Feature\Admin;

use App\Enums\DivisionStatus;
use App\Enums\EnquiryStatus;
use App\Enums\QuoteStatus;
use App\Enums\RequestStatus;
use App\Enums\ServiceStatus;
use App\Models\AuditLog;
use App\Models\BusinessDivision;
use App\Models\Enquiry;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Module 2 — Administration Dashboard (PRD §24).
 *
 * Guards the access rules (auth + permission), that the widget figures reflect
 * the database rather than placeholders, and that the shared sidebar is
 * permission-filtered.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole(Rbac::SUPER_ADMINISTRATOR);

        return $user->fresh();
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_requires_the_view_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);

        // Customers get no admin permissions at all.
        $customer = User::factory()->create();
        $customer->assignRole(Rbac::CUSTOMER);

        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_dashboard_renders_for_an_authorised_admin(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Total enquiries')
            ->assertSee('Open service requests')
            ->assertSee('Active divisions')
            ->assertSee('Enquiries by month');
    }

    public function test_widgets_reflect_the_database(): void
    {
        $admin = $this->superAdmin();

        $division = BusinessDivision::factory()->create(['status' => DivisionStatus::Active]);
        BusinessDivision::factory()->create(['status' => DivisionStatus::Planned]);

        // Pin related records: the factories would otherwise create their own
        // divisions and services, which would skew the catalogue counters.
        $service = Service::factory()->create([
            'status' => ServiceStatus::Active,
            'business_division_id' => $division->id,
        ]);
        Service::factory()->create([
            'status' => ServiceStatus::Draft,
            'business_division_id' => $division->id,
        ]);

        Enquiry::factory()->count(2)->create(['status' => EnquiryStatus::New]);
        Enquiry::factory()->create(['status' => EnquiryStatus::Closed]);

        ServiceRequest::factory()->create([
            'business_division_id' => $division->id,
            'service_id' => $service->id,
            'status' => RequestStatus::New,
        ]);
        ServiceRequest::factory()->create([
            'business_division_id' => $division->id,
            'service_id' => $service->id,
            'status' => RequestStatus::Completed,
        ]);

        QuoteRequest::factory()->create([
            'status' => QuoteStatus::Requested,
            'business_division_id' => $division->id,
            'service_id' => $service->id,
        ]);
        QuoteRequest::factory()->create([
            'status' => QuoteStatus::Accepted,
            'business_division_id' => $division->id,
            'service_id' => $service->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        $stats = collect($response->viewData('stats'))->keyBy('label');

        // Closed/completed/accepted records must not inflate the open counters.
        $this->assertSame(3, $stats['Total enquiries']['value']);
        $this->assertSame(2, $stats['New enquiries']['value']);
        $this->assertSame(1, $stats['Open service requests']['value']);
        $this->assertSame(2, $stats['Quote requests']['value']);
        $this->assertSame(1, $stats['Active divisions']['value']);
        $this->assertSame(1, $stats['Active services']['value']);

        // Breakdown charts are built from open requests only.
        $byDivision = collect($response->viewData('requestsByDivision'));
        $this->assertCount(1, $byDivision);
        $this->assertSame($division->name, $byDivision->first()['label']);
        $this->assertSame(1, $byDivision->first()['value']);
    }

    public function test_monthly_trend_covers_twelve_months_and_ignores_older_enquiries(): void
    {
        $admin = $this->superAdmin();

        $this->enquiryAt(now());
        $this->enquiryAt(now()->subMonths(3));
        $this->enquiryAt(now()->subYear());

        $series = collect($this->actingAs($admin)->get(route('admin.dashboard'))->viewData('enquiriesByMonth'));

        $this->assertCount(12, $series);
        $this->assertSame(now()->format('M'), $series->last()['label']);
        $this->assertSame(1, $series->last()['value']);
        // The year-old enquiry falls outside the 12-month window.
        $this->assertSame(2, $series->sum('value'));
    }

    /** created_at is not mass-assignable, so backdate after creation. */
    private function enquiryAt(Carbon $when): Enquiry
    {
        $enquiry = Enquiry::factory()->create(['status' => EnquiryStatus::New]);
        $enquiry->created_at = $when;
        $enquiry->save();

        return $enquiry;
    }

    public function test_sidebar_shows_only_permitted_screens(): void
    {
        $this->seed(RolePermissionSeeder::class);

        // Business managers get the dashboard but never the audit trail.
        $manager = User::factory()->create();
        $manager->assignRole(Rbac::BUSINESS_MANAGER);

        $this->actingAs($manager)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.dashboard'), escape: false)
            ->assertDontSee(route('admin.audit-logs.index'), escape: false);

        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.audit-logs.index'), escape: false);
    }

    public function test_unbuilt_modules_are_listed_instead_of_shown_as_zero(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Coming with the next modules')
            ->assertSee('Projects')
            ->assertSee('News articles')
            ->assertSee('Training programmes');
    }

    public function test_recent_activity_surfaces_the_audit_tail(): void
    {
        $admin = $this->superAdmin();

        AuditLog::create([
            'action' => 'enquiry.created',
            'description' => 'Enquiry ENQ-TEST created',
        ]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Enquiry ENQ-TEST created');
    }

    public function test_admin_root_redirects_to_the_dashboard(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get('/admin')->assertRedirect(route('admin.dashboard'));
    }
}
