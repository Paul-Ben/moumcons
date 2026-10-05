<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Rbac;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /*
     * Do NOT add Laravel's WithoutModelEvents trait here: spatie's Permission
     * and Role models only flush their permission cache from Eloquent model
     * events (RefreshesPermissionCache). Suppressing events leaves the cache
     * stale, and every name-based lookup made afterwards (syncPermissions,
     * hasRole, assignRole) fails with PermissionDoesNotExist.
     */

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            ContentSeeder::class,
            PageSeeder::class,
        ]);

        if (app()->environment('local')) {
            User::firstOrCreate(
                ['email' => 'test@example.com'],
                [
                    'name' => 'Test User',
                    // users.password is NOT NULL; local-only convenience login.
                    'password' => Hash::make('ChangeMe!2026'),
                ]
            )->assignRole(Rbac::CUSTOMER);
        }
    }
}
