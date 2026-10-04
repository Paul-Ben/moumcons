<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin → Audit logs (PRD §31/§32). Guards two regressions: the detail route
 * rendering a view that did not exist, and the index blowing up on an
 * unimported `Str` facade alias.
 */
class AuditLogViewerTest extends TestCase
{
    use RefreshDatabase;

    private function seedRbac(): void
    {
        $this->seed(RolePermissionSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Rbac::SUPER_ADMINISTRATOR);

        return $user->fresh();
    }

    private function log(array $attributes = []): AuditLog
    {
        return AuditLog::create(array_merge([
            'action' => 'enquiry.created',
            'description' => 'Enquiry created',
        ], $attributes));
    }

    public function test_index_requires_authentication(): void
    {
        $this->get('/admin/audit-logs')->assertRedirect(route('login'));
    }

    public function test_index_requires_the_view_audit_logs_permission(): void
    {
        $this->seedRbac();

        $user = User::factory()->create();
        $user->assignRole(Rbac::CONTENT_EDITOR);

        $this->actingAs($user)->get('/admin/audit-logs')->assertForbidden();
    }

    public function test_index_renders_entries(): void
    {
        $this->seedRbac();

        $this->log(['description' => 'First recorded action']);

        $this->actingAs($this->superAdmin())
            ->get('/admin/audit-logs')
            ->assertOk()
            ->assertSee('First recorded action');
    }

    public function test_index_renders_the_action_group_filter(): void
    {
        $this->seedRbac();

        // Str::headline() is used to label the category dropdown.
        $this->log(['action' => 'auth.login']);

        $this->actingAs($this->superAdmin())
            ->get('/admin/audit-logs')
            ->assertOk()
            ->assertSee('Auth');
    }

    public function test_index_can_filter_by_search_term(): void
    {
        $this->seedRbac();

        $this->log(['description' => 'Password reset requested']);
        $this->log(['description' => 'Division record updated']);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.audit-logs.index', ['q' => 'Password reset']))
            ->assertOk()
            ->assertSee('Password reset requested')
            ->assertDontSee('Division record updated');
    }

    public function test_detail_page_renders(): void
    {
        $this->seedRbac();

        $log = $this->log(['description' => 'Enquiry status changed']);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.audit-logs.show', $log))
            ->assertOk()
            ->assertSee('Enquiry status changed')
            ->assertSee('enquiry.created');
    }

    public function test_detail_page_renders_the_recorded_change_diff(): void
    {
        $this->seedRbac();

        $log = $this->log([
            'description' => 'Service request updated',
            'properties' => [
                'changes' => [
                    'status' => ['old' => 'new', 'new' => 'in_progress'],
                ],
            ],
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.audit-logs.show', $log))
            ->assertOk()
            ->assertSee('Status')
            ->assertSee('in_progress');
    }

    public function test_detail_page_requires_the_view_audit_logs_permission(): void
    {
        $this->seedRbac();

        $log = $this->log();

        $user = User::factory()->create();
        $user->assignRole(Rbac::CONTENT_EDITOR);

        $this->actingAs($user)
            ->get(route('admin.audit-logs.show', $log))
            ->assertForbidden();
    }
}
