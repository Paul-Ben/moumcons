<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\User;
use App\Support\CompanyDetails;
use App\Support\Rbac;
use Database\Seeders\ContentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** M10 — users, roles, profile and site settings (PRD §20/§23). */
class UsersAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function withRole(string $role, array $attributes = []): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user->fresh();
    }

    /* --------------------------------- Users -------------------------------- */

    public function test_user_screens_require_manage_users(): void
    {
        $editor = $this->withRole(Rbac::CONTENT_EDITOR);

        $this->actingAs($editor)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.roles.index'))->assertForbidden();
    }

    public function test_admin_creates_a_user_and_an_invite_is_emailed(): void
    {
        Notification::fake();
        $admin = $this->withRole(Rbac::ADMINISTRATOR);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Mercy Ochigbo',
            'email' => 'mercy@moaum.test',
            'roles' => [Rbac::CONTENT_EDITOR],
            'is_active' => '1',
        ])->assertRedirect();

        $user = User::where('email', 'mercy@moaum.test')->sole();
        $this->assertTrue($user->hasRole(Rbac::CONTENT_EDITOR));
        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertTrue(AuditLog::query()->where('action', 'user.created')->exists());
    }

    public function test_weak_passwords_are_rejected(): void
    {
        $admin = $this->withRole(Rbac::ADMINISTRATOR);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'X', 'email' => 'x@moaum.test',
            'password' => 'password', 'password_confirmation' => 'password',
        ])->assertSessionHasErrors('password');
    }

    public function test_only_super_admins_grant_the_super_admin_role(): void
    {
        $admin = $this->withRole(Rbac::ADMINISTRATOR);
        $target = User::factory()->create();

        $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => $target->name, 'email' => $target->email,
            'roles' => [Rbac::SUPER_ADMINISTRATOR], 'roles_submitted' => '1',
        ])->assertSessionHasErrors('roles');

        $this->assertFalse($target->fresh()->hasRole(Rbac::SUPER_ADMINISTRATOR));
    }

    public function test_last_super_admin_cannot_be_demoted_or_deactivated(): void
    {
        $super = $this->withRole(Rbac::SUPER_ADMINISTRATOR);

        $this->actingAs($super)->put(route('admin.users.update', $super), [
            'name' => $super->name, 'email' => $super->email,
            'roles' => [Rbac::ADMINISTRATOR], 'roles_submitted' => '1',
        ])->assertSessionHasErrors('roles');

        $other = $this->withRole(Rbac::SUPER_ADMINISTRATOR);
        $other->update(['is_active' => false]);

        $this->actingAs($other->fresh()->forceFill(['is_active' => true]))->put(route('admin.users.update', $super), [
            'name' => $super->name, 'email' => $super->email, 'is_active' => '0',
        ])->assertSessionHasErrors('is_active');
    }

    public function test_users_cannot_deactivate_themselves(): void
    {
        $admin = $this->withRole(Rbac::ADMINISTRATOR);

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email, 'is_active' => '0',
        ])->assertSessionHasErrors('is_active');
    }

    public function test_deactivating_a_user_ends_their_sessions_and_role_changes_are_audited(): void
    {
        $admin = $this->withRole(Rbac::ADMINISTRATOR);
        $target = $this->withRole(Rbac::CONTENT_EDITOR);
        DB::table('sessions')->insert(['id' => 'abc', 'user_id' => $target->id, 'payload' => '', 'last_activity' => time()]);

        $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => $target->name, 'email' => $target->email, 'is_active' => '0',
            'roles' => [Rbac::BUSINESS_MANAGER], 'roles_submitted' => '1',
        ])->assertRedirect();

        $this->assertFalse($target->fresh()->is_active);
        $this->assertSame(0, DB::table('sessions')->where('user_id', $target->id)->count());
        $this->assertTrue($target->fresh()->hasRole(Rbac::BUSINESS_MANAGER));
        $this->assertTrue(AuditLog::query()->where('action', 'user.roles_changed')->exists());
        $this->assertTrue(AuditLog::query()->where('action', 'user.updated')->exists());
    }

    public function test_user_screens_render(): void
    {
        $admin = $this->withRole(Rbac::ADMINISTRATOR);

        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk()->assertSee($admin->email);
        $this->actingAs($admin)->get(route('admin.users.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.edit', $admin))->assertOk();
        $this->actingAs($admin)->get(route('admin.profile.edit'))->assertOk();
    }

    /* --------------------------------- Roles -------------------------------- */

    public function test_role_permissions_can_be_changed_and_are_audited(): void
    {
        $admin = $this->withRole(Rbac::ADMINISTRATOR);
        $role = Role::findByName(Rbac::BUSINESS_MANAGER);

        $this->actingAs($admin)->get(route('admin.roles.edit', $role))->assertOk();
        $this->actingAs($admin)->put(route('admin.roles.update', $role), [
            'permissions' => ['view-admin-dashboard', 'view-projects'],
        ])->assertRedirect();

        $this->assertEqualsCanonicalizing(['view-admin-dashboard', 'view-projects'], $role->fresh()->permissions->pluck('name')->all());
        $this->assertTrue(AuditLog::query()->where('action', 'role.permissions_changed')->exists());
    }

    public function test_custom_roles_can_be_created_and_deleted_but_built_ins_cannot(): void
    {
        $admin = $this->withRole(Rbac::ADMINISTRATOR);

        $this->actingAs($admin)->post(route('admin.roles.store'), [
            'name' => 'Division Coordinator', 'permissions' => ['view-divisions'],
        ])->assertRedirect();
        $custom = Role::findByName('Division Coordinator');

        $this->actingAs($admin)->delete(route('admin.roles.destroy', $custom))->assertRedirect(route('admin.roles.index'));
        $this->assertNull(Role::where('name', 'Division Coordinator')->first());

        $this->actingAs($admin)->delete(route('admin.roles.destroy', Role::findByName(Rbac::CONTENT_EDITOR)))->assertSessionHas('error');
    }

    /* -------------------------------- Profile ------------------------------- */

    public function test_staff_change_their_own_password(): void
    {
        $editor = $this->withRole(Rbac::CONTENT_EDITOR, ['password' => Hash::make('OldPassword1')]);

        $this->actingAs($editor)->put(route('admin.profile.password'), [
            'current_password' => 'wrong', 'password' => 'NewPassword12', 'password_confirmation' => 'NewPassword12',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($editor)->put(route('admin.profile.password'), [
            'current_password' => 'OldPassword1', 'password' => 'NewPassword12', 'password_confirmation' => 'NewPassword12',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('NewPassword12', $editor->fresh()->password));
    }

    /* -------------------------------- Settings ------------------------------ */

    public function test_settings_update_contact_details_social_links_and_stats(): void
    {
        $admin = $this->withRole(Rbac::ADMINISTRATOR);
        $this->seed(ContentSeeder::class);

        $this->actingAs($admin)->get(route('admin.settings.edit'))->assertOk();

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'contact__phone' => '+234 703 000 0000',
            'contact__address' => 'Plot 1, University Road, Makurdi',
            'social__linkedin' => 'https://www.linkedin.com/company/moaum',
            'home__stats' => [['value' => '16', 'label' => 'Divisions', 'color' => 'blue']],
            'home__hero__badge' => 'Owned by the University',
        ])->assertRedirect(route('admin.settings.edit'));

        $this->assertSame('+234 703 000 0000', CompanyDetails::get('phone'));
        $this->assertSame(['linkedin' => 'https://www.linkedin.com/company/moaum'], CompanyDetails::socialLinks());
        $this->assertSame([['value' => '16', 'label' => 'Divisions', 'color' => 'blue']], Setting::get('home.stats'));
        $this->assertTrue(AuditLog::query()->where('action', 'settings.updated')->exists());

        $this->get(route('home'))
            ->assertSee('Owned by the University')
            ->assertSee('Plot 1, University Road, Makurdi')
            ->assertSee('https://www.linkedin.com/company/moaum');
    }

    public function test_map_url_must_be_a_google_embed(): void
    {
        $admin = $this->withRole(Rbac::ADMINISTRATOR);

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'contact__map_embed_url' => 'https://evil.example/frame',
        ])->assertSessionHasErrors('contact__map_embed_url');
    }

    public function test_reseeding_does_not_overwrite_edited_settings(): void
    {
        $this->seed(ContentSeeder::class);
        Setting::where('key', 'home.cta.title')->update(['value' => 'Edited heading']);

        $this->seed(ContentSeeder::class);

        $this->assertSame('Edited heading', Setting::where('key', 'home.cta.title')->value('value'));
    }
}
