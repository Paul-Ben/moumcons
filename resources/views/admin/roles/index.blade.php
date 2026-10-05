{{-- Admin → Roles & permissions (PRD §23). --}}
<x-layouts.admin title="Roles">
    <div class="space-y-6">
        <x-admin.page-header title="Roles & Permissions" description="What each role can do. Users get permissions only through their roles."
            :back="route('admin.users.index')" back-label="Users">
            <a href="{{ route('admin.roles.create') }}" class="btn-primary text-sm"><x-icon name="plus" class="w-4 h-4" /> New role</a>
        </x-admin.page-header>

        <div class="card overflow-hidden p-0 divide-y divide-slate-100">
            @foreach ($roles as $role)
                <div class="px-4 py-3 flex flex-wrap items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-moaum-charcoal">{{ $role->name }}</p>
                        <p class="text-xs text-slate-400">
                            {{ $role->users_count }} {{ Str::plural('user', $role->users_count) }} ·
                            {{ $role->name === App\Support\Rbac::SUPER_ADMINISTRATOR ? 'every permission' : $role->permissions_count.' '.Str::plural('permission', $role->permissions_count) }}
                        </p>
                    </div>
                    @if (in_array($role->name, $builtIn, true))
                        <x-badge color="slate">Built-in</x-badge>
                    @endif
                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-moaum-blue hover:text-moaum-red font-medium text-sm">
                        {{ $role->name === App\Support\Rbac::SUPER_ADMINISTRATOR ? 'View' : 'Edit' }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
