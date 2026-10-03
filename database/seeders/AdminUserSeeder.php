<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Rbac;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed a Super Administrator account for local development.
     * Password is overridable via MOAUM_ADMIN_PASSWORD env var.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => env('MOAUM_ADMIN_EMAIL', 'admin@moaum.test')],
            [
                'name'      => 'MOAUM Super Admin',
                'password'  => Hash::make(env('MOAUM_ADMIN_PASSWORD', 'ChangeMe!2026')),
                'is_active' => true,
            ]
        );

        if (! $admin->hasRole(Rbac::SUPER_ADMINISTRATOR)) {
            $admin->assignRole(Rbac::SUPER_ADMINISTRATOR);
        }
    }
}
