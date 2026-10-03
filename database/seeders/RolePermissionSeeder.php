<?php

namespace Database\Seeders;

use App\Support\Rbac;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed all MOAUM roles and permissions (PRD §23). Idempotent.
     */
    public function run(): void
    {
        // Reset the Spatie permission cache first.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Rbac::allPermissions() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (Rbac::roles() as $role) {
            $roleModel = Role::findOrCreate($role, 'web');
            $roleModel->syncPermissions(Rbac::rolePermissions()[$role] ?? []);
        }

        $this->command?->info('RBAC seeded: ' . count(Rbac::roles()) . ' roles, ' . count(Rbac::allPermissions()) . ' permissions.');
    }
}
