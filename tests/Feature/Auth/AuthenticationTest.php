<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'password' => Hash::make('Password123'),
        ]);
    }

    /**
     * The RBAC roles live in the database (seeded by RolePermissionSeeder),
     * so tests that reference them must seed first. RefreshDatabase keeps
     * one transaction per test, making this cheap after the first call.
     */
    private function seedRbac(): void
    {
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_login_screen_renders_for_guests(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_login_screen_redirects_authenticated_users(): void
    {
        $this->actingAs($this->admin())->get('/login')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_users_can_login_with_valid_credentials(): void
    {
        $user = $this->admin();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_users_cannot_login_with_invalid_credentials(): void
    {
        $user = $this->admin();

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deactivated_users_cannot_login(): void
    {
        $user = $this->admin();
        $user->forceFill(['is_active' => false])->save();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_rejected_deactivated_login_is_not_audited_as_a_success(): void
    {
        $user = $this->admin();
        $user->forceFill(['is_active' => false])->save();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password123',
        ])->assertSessionHasErrors('email');

        // The credentials matched, but the account is deactivated: neither the
        // Login nor the Logout event may reach the audit trail.
        $this->assertDatabaseMissing('audit_logs', ['action' => 'auth.login']);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'auth.logout']);
    }

    public function test_successful_login_is_audited(): void
    {
        $user = $this->admin();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password123',
        ]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.login']);
    }

    public function test_login_is_throttled_after_five_failed_attempts(): void
    {
        $user = $this->admin();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'bad']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $this->seedRbac();
        $user = $this->admin();
        $user->assignRole(Rbac::ADMINISTRATOR);

        $this->actingAs($user)->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_admin_dashboard_requires_authentication(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_without_permission_cannot_view_dashboard(): void
    {
        Permission::findOrCreate('view-admin-dashboard', 'web');
        Role::findOrCreate(Rbac::CUSTOMER, 'web')->syncPermissions([]);

        $user = $this->admin();
        $user->assignRole(Rbac::CUSTOMER);

        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_super_administrator_bypasses_all_checks(): void
    {
        $this->seedRbac();
        $user = $this->admin();
        $user->assignRole(Rbac::SUPER_ADMINISTRATOR);

        $this->actingAs($user)->get('/admin/dashboard')->assertOk();
    }
}
