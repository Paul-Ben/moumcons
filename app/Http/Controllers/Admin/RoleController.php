<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Support\Rbac;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Admin → Roles & permissions (PRD §6.7/§23), gated by 'manage-roles'.
 *
 * The built-in roles from App\Support\Rbac can have their permissions tuned
 * but not be deleted or renamed; custom roles can be added. Super
 * Administrator always has every ability (Gate::before), so its permissions
 * are shown read-only.
 */
class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::query()->withCount(['users', 'permissions'])->orderBy('name')->get(),
            'builtIn' => Rbac::roles(),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.form', $this->formData(new Role));
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(Rbac::allPermissions())],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        $audit->log('role.created', "Role \"{$role->name}\" created with ".count($data['permissions'] ?? []).' permissions', subject: $role);

        return redirect()->route('admin.roles.edit', $role)->with('success', "Role \"{$role->name}\" created.");
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.form', $this->formData($role->load('permissions')));
    }

    public function update(Request $request, Role $role, AuditLogger $audit): RedirectResponse
    {
        abort_if($role->name === Rbac::SUPER_ADMINISTRATOR, 403, 'Super Administrator always has every permission.');

        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(Rbac::allPermissions())],
        ]);

        $before = $role->permissions->pluck('name')->sort()->values();
        $role->syncPermissions($data['permissions'] ?? []);
        $after = $role->fresh()->permissions->pluck('name')->sort()->values();

        $added = $after->diff($before)->values()->all();
        $removed = $before->diff($after)->values()->all();

        if ($added || $removed) {
            $audit->log(
                action: 'role.permissions_changed',
                description: sprintf('Role "%s" permissions changed (+%d / -%d)', $role->name, count($added), count($removed)),
                properties: ['added' => $added, 'removed' => $removed],
                subject: $role,
            );
        }

        return redirect()->route('admin.roles.edit', $role)->with('success', "Permissions for \"{$role->name}\" saved.");
    }

    public function destroy(Role $role, AuditLogger $audit): RedirectResponse
    {
        if (in_array($role->name, Rbac::roles(), true)) {
            return back()->with('error', 'Built-in roles cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Remove this role from its users before deleting it.');
        }

        $name = $role->name;
        $role->delete();
        $audit->log('role.deleted', "Role \"{$name}\" deleted");

        return redirect()->route('admin.roles.index')->with('success', "Role \"{$name}\" deleted.");
    }

    /** @return array<string, mixed> */
    private function formData(Role $role): array
    {
        // Make sure every permission Rbac knows about exists, even on an
        // install whose seeder predates newer modules.
        foreach (Rbac::allPermissions() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        return [
            'role' => $role,
            'groups' => Rbac::permissionGroups(),
            'granted' => $role->exists ? $role->permissions->pluck('name')->all() : [],
            'isBuiltIn' => in_array($role->name, Rbac::roles(), true),
            'isSuper' => $role->name === Rbac::SUPER_ADMINISTRATOR,
        ];
    }
}
