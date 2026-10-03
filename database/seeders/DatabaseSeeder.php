<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Rbac;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

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
        ]);

        if (app()->environment('local')) {
            User::firstOrCreate(
                ['email' => 'test@example.com'],
                ['name' => 'Test User']
            )->assignRole(Rbac::CUSTOMER);
        }
    }
}
