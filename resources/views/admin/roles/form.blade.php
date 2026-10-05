{{-- Admin → create / edit a role's permissions (PRD §23). --}}
@php
    $editing = $role->exists;
    $checked = old('permissions', $granted);
@endphp

<x-layouts.admin :title="$editing ? 'Role: '.$role->name : 'New role'">
    <form method="POST" action="{{ $editing ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.page-header :title="$editing ? $role->name : 'New role'" :back="route('admin.roles.index')" back-label="All roles"
            :description="$isSuper ? 'Super Administrators bypass every permission check, so this role cannot be restricted.' : ($isBuiltIn ? 'Built-in role — permissions can be adjusted, the role cannot be renamed or deleted.' : null)" />

        @unless ($editing)
            <div class="card max-w-xl">
                <x-admin.input name="name" label="Role name" required maxlength="100" placeholder="e.g. Division Coordinator" />
            </div>
        @endunless

        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($groups as $group => $permissions)
                <fieldset class="card space-y-2" @disabled($isSuper)>
                    <legend class="font-semibold text-moaum-charcoal capitalize mb-1">{{ str_replace('-', ' ', $group) }}</legend>
                    @foreach ($permissions as $permission)
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="permissions[]" value="{{ $permission }}" @checked($isSuper || in_array($permission, $checked, true))
                                   class="rounded border-slate-300 text-moaum-blue focus:ring-moaum-blue">
                            <span class="font-mono text-xs">{{ $permission }}</span>
                        </label>
                    @endforeach
                </fieldset>
            @endforeach
        </div>

        @unless ($isSuper)
            <div class="card">
                <x-admin.form-actions :cancel="route('admin.roles.index')" :submit="$editing ? 'Save permissions' : 'Create role'" />
            </div>
        @endunless
    </form>

    @if ($editing && ! $isBuiltIn)
        <div class="card mt-6 flex justify-end">
            <x-admin.delete-button :action="route('admin.roles.destroy', $role)" label="Delete role" confirm="Delete this role?" />
        </div>
    @endif
</x-layouts.admin>
