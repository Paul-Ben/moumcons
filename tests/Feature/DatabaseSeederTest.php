<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Guards the full `php artisan db:seed` path (DatabaseSeeder), not just the
 * individual seeders: spatie's Permission/Role models only flush their
 * permission cache from Eloquent model events, so anything that suppresses
 * model events (WithoutModelEvents) makes name-based lookups fail.
 */
class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_roles_and_permissions(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(count(Rbac::roles()), Role::count());

        foreach (Rbac::allPermissions() as $permission) {
            $this->assertNotNull(
                Permission::findByName($permission, 'web'),
                "Permission [{$permission}] was not seeded."
            );
        }

        $this->assertSame(
            count(Rbac::allPermissions()),
            Role::findByName(Rbac::SUPER_ADMINISTRATOR, 'web')->permissions()->count()
        );
    }

    public function test_database_seeder_assigns_the_super_administrator_role(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@moaum.test')->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole(Rbac::SUPER_ADMINISTRATOR));
        $this->assertTrue(Hash::check('ChangeMe!2026', $admin->password));
    }

    public function test_database_seeder_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(count(Rbac::roles()), Role::count());
        $this->assertSame(count(Rbac::allPermissions()), Permission::count());
        $this->assertSame(1, User::where('email', 'admin@moaum.test')->count());
    }
}
