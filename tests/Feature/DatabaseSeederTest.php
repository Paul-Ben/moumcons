<?php

namespace Tests\Feature;

use App\Models\BusinessDivision;
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

    public function test_seeded_divisions_reference_placeholder_images_that_exist(): void
    {
        $this->seed(DatabaseSeeder::class);

        $withImage = BusinessDivision::where('slug', 'printing-and-publishing-services')->firstOrFail();

        $this->assertSame('/images/division-printing-publishing.jpg', $withImage->cover_image);
        $this->assertSame($withImage->cover_image, $withImage->hero_image);
        $this->assertFileExists(public_path(ltrim($withImage->cover_image, '/')));

        foreach (BusinessDivision::whereNotNull('cover_image')->get() as $division) {
            $this->assertFileExists(public_path(ltrim($division->cover_image, '/')));
        }
    }

    public function test_seeded_divisions_without_placeholder_art_keep_a_null_image(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Null lets the card fall back to its icon block instead of a broken <img>.
        $division = BusinessDivision::where('slug', 'construction-services')->firstOrFail();

        $this->assertNull($division->cover_image);
        $this->assertNull($division->hero_image);
    }
}
