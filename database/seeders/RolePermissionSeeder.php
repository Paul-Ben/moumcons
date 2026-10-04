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

        /*
         * Permission::findOrCreate() and Role::syncPermissions() resolve names
         * through the registrar's cache, never the database directly, and the
         * cache is only refreshed by the models' saved/deleted events. Flush it
         * explicitly so a run that is wrapped in withoutEvents() (or any other
         * suppressed dispatcher) still syncs the permissions below.
         */
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Rbac::roles() as $role) {
            $roleModel = Role::findOrCreate($role, 'web');
            $roleModel->syncPermissions(Rbac::rolePermissions()[$role] ?? []);
        }

        // Leave the cache consistent for later seeders (AdminUserSeeder looks
        // roles up by name).
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('RBAC seeded: '.count(Rbac::roles()).' roles, '.count(Rbac::allPermissions()).' permissions.');
    }
}
