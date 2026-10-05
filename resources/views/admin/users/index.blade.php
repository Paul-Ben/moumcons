{{-- Admin → Users (PRD §23). --}}
<x-layouts.admin title="Users">
    <div class="space-y-6">
        <x-admin.page-header title="Users" description="Staff accounts and their roles. Accounts are deactivated, not deleted, so the audit trail keeps its authors.">
            @can('manage-roles')
                <a href="{{ route('admin.roles.index') }}" class="btn-ghost text-sm border border-slate-200"><x-icon name="key" class="w-4 h-4" /> Roles &amp; permissions</a>
            @endcan
            @can('create', App\Models\User::class)
                <a href="{{ route('admin.users.create') }}" class="btn-primary text-sm"><x-icon name="plus" class="w-4 h-4" /> Add user</a>
            @endcan
        </x-admin.page-header>

        <form method="GET" class="flex flex-wrap items-center gap-2">
            <label for="q" class="sr-only">Search</label>
            <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name or email…" class="form-input text-sm max-w-xs">
            <label for="role" class="sr-only">Role</label>
            <select id="role" name="role" class="form-input text-sm max-w-[14rem]">
                <option value="">All roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected(($filters['role'] ?? '') === $role)>{{ $role }}</option>
                @endforeach
            </select>
            <label class="inline-flex items-center gap-2 text-sm text-slate-600 px-2">
                <input type="checkbox" name="inactive" value="1" @checked($filters['inactive'] ?? false) class="rounded border-slate-300 text-moaum-blue"> Deactivated only
            </label>
            <button type="submit" class="btn-ghost text-sm border border-slate-200">Filter</button>
        </form>

        <div class="card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Roles</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Joined</th>
                            <th class="px-4 py-3 text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <span class="block font-medium text-moaum-charcoal">{{ $user->name }} @if (auth()->user()->is($user))<span class="text-xs text-slate-400">(you)</span>@endif</span>
                                    <span class="block text-xs text-slate-400">{{ $user->email }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($user->roles as $role)
                                            <x-badge :color="$role->name === App\Support\Rbac::SUPER_ADMINISTRATOR ? 'red' : 'blue'">{{ $role->name }}</x-badge>
                                        @empty
                                            <span class="text-slate-400">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $user->is_active ? 'Active' : 'Deactivated' }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $user->created_at?->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-moaum-blue hover:text-moaum-red font-medium">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-slate-500">No users match.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">{{ $users->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.admin>
