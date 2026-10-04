<?php

namespace Tests\Feature\Admin;

use App\Enums\RequestStatus;
use App\Models\AuditLog;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\RequestAssigned;
use App\Notifications\RequestStatusChanged;
use App\Support\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** M1 — admin triage of service requests (PRD §12/§25). */
class ServiceRequestTriageTest extends TestCase
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

    public function test_queue_requires_authentication(): void
    {
        $this->get(route('admin.service-requests.index'))->assertRedirect(route('login'));
    }

    public function test_queue_requires_the_view_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $customer = User::factory()->create();
        $customer->assignRole(Rbac::CUSTOMER);

        $this->actingAs($customer)->get(route('admin.service-requests.index'))->assertForbidden();
    }

    public function test_queue_lists_and_filters_requests(): void
    {
        $viewer = $this->staffWith(['view-service-requests']);
        ServiceRequest::factory()->create(['name' => 'Ada Okonkwo', 'status' => RequestStatus::New]);
        ServiceRequest::factory()->create(['name' => 'Tunde Bello', 'status' => RequestStatus::Completed]);

        $this->actingAs($viewer)->get(route('admin.service-requests.index'))
            ->assertOk()
            ->assertSee('Ada Okonkwo')
            ->assertSee('Tunde Bello');

        $this->actingAs($viewer)->get(route('admin.service-requests.index', ['status' => 'completed']))
            ->assertOk()
            ->assertDontSee('Ada Okonkwo')
            ->assertSee('Tunde Bello');

        $this->actingAs($viewer)->get(route('admin.service-requests.index', ['q' => 'Ada']))
            ->assertSee('Ada Okonkwo')
            ->assertDontSee('Tunde Bello');
    }

    public function test_viewer_sees_detail_read_only(): void
    {
        $viewer = $this->staffWith(['view-service-requests']);
        $request = ServiceRequest::factory()->create(['requirements' => 'Fumigate the east wing']);

        $this->actingAs($viewer)->get(route('admin.service-requests.show', $request))
            ->assertOk()
            ->assertSee('Fumigate the east wing')
            ->assertSee('read-only access');
    }

    public function test_viewer_cannot_update(): void
    {
        $viewer = $this->staffWith(['view-service-requests']);
        $request = ServiceRequest::factory()->create();

        $this->actingAs($viewer)
            ->patch(route('admin.service-requests.update', $request), ['status' => 'in_progress'])
            ->assertForbidden();

        $this->assertSame(RequestStatus::New, $request->fresh()->status);
    }

    public function test_status_change_is_audited_and_emails_customer_when_asked(): void
    {
        Notification::fake();
        $staff = $this->staffWith(['view-service-requests', 'update-service-requests']);
        $request = ServiceRequest::factory()->create(['email' => 'client@example.com']);

        $this->actingAs($staff)
            ->patch(route('admin.service-requests.update', $request), ['status' => 'in_progress', 'notify_customer' => '1'])
            ->assertRedirect(route('admin.service-requests.show', $request));

        $this->assertSame(RequestStatus::InProgress, $request->fresh()->status);
        $this->assertTrue(AuditLog::query()->where('action', 'servicerequest.status_changed')->exists());

        Notification::assertSentTo(
            new AnonymousNotifiable,
            RequestStatusChanged::class,
            fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === 'client@example.com' && $n->statusLabel === 'In Progress'
        );
    }

    public function test_status_change_without_the_box_ticked_sends_nothing(): void
    {
        Notification::fake();
        $staff = $this->staffWith(['view-service-requests', 'update-service-requests']);
        $request = ServiceRequest::factory()->create();

        $this->actingAs($staff)
            ->patch(route('admin.service-requests.update', $request), ['status' => 'in_progress', 'notify_customer' => '0']);

        Notification::assertNothingSent();
    }

    public function test_assignment_notifies_the_new_owner(): void
    {
        Notification::fake();
        $staff = $this->staffWith(['view-service-requests', 'update-service-requests']);
        $owner = User::factory()->create(['is_active' => true]);
        $request = ServiceRequest::factory()->create();

        $this->actingAs($staff)
            ->patch(route('admin.service-requests.update', $request), ['assigned_to' => $owner->id])
            ->assertRedirect();

        $this->assertSame($owner->id, $request->fresh()->assigned_to);
        Notification::assertSentTo($owner, RequestAssigned::class);
    }

    public function test_inactive_staff_cannot_be_assigned(): void
    {
        $staff = $this->staffWith(['view-service-requests', 'update-service-requests']);
        $inactive = User::factory()->create(['is_active' => false]);
        $request = ServiceRequest::factory()->create();

        $this->actingAs($staff)
            ->patch(route('admin.service-requests.update', $request), ['assigned_to' => $inactive->id])
            ->assertSessionHasErrors('assigned_to');
    }

    public function test_attachment_download_is_audited(): void
    {
        Storage::fake('private');
        $viewer = $this->staffWith(['view-service-requests']);
        $path = UploadedFile::fake()->create('brief.pdf', 10)->storeAs('requests', 'SRQ-brief.pdf', 'private');
        $request = ServiceRequest::factory()->create(['attachment' => $path]);

        $this->actingAs($viewer)->get(route('admin.service-requests.attachment', $request))->assertOk();

        $this->assertTrue(AuditLog::query()->where('action', 'servicerequest.attachment_downloaded')->exists());
    }
}
