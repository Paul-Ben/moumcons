{{-- Admin → add / edit a user (PRD §23). --}}
@php
    $editing = $user->exists;
    $current = old('roles', $user->exists ? $user->roles->pluck('name')->all() : []);
@endphp

<x-layouts.admin :title="$editing ? 'Edit '.$user->name : 'Add user'">
    <form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" class="space-y-6 max-w-4xl">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.page-header :title="$editing ? $user->name : 'Add a user'" :back="route('admin.users.index')" back-label="All users"
            :description="$editing ? 'Member since '.$user->created_at?->format('j F Y') : null" />

        <div class="card space-y-4">
            <div class="grid sm:grid-cols-2 gap-4">
                <x-admin.input name="name" label="Full name" :value="$user->name" required maxlength="255" />
                <x-admin.input name="email" type="email" label="Email" :value="$user->email" required maxlength="255" />
                <x-admin.input name="phone" label="Phone" :value="$user->phone" maxlength="30" />
            </div>
            @unless ($isSelf)
                <x-admin.checkbox name="is_active" label="Account active" :checked="$user->is_active"
                                  hint="Deactivating signs the user out everywhere and blocks sign-in." />
            @endunless
        </div>

        <div class="card space-y-4">
            <div>
                <h2 class="font-semibold text-moaum-charcoal">Password</h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ $editing ? 'Leave blank to keep the current password.' : 'Leave blank to email the user a link to set their own password.' }}
                    At least 10 characters with upper and lower case letters and a number.
                </p>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <x-admin.input name="password" type="password" label="New password" autocomplete="new-password" />
                <x-admin.input name="password_confirmation" type="password" label="Confirm password" autocomplete="new-password" />
            </div>
        </div>

        @if ($canAssignRoles)
            <div class="card space-y-3">
                <div>
                    <h2 class="font-semibold text-moaum-charcoal">Roles</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Permissions come from roles. <a href="{{ route('admin.roles.index') }}" class="text-moaum-blue hover:underline">Review what each role can do</a>.</p>
                </div>
                <input type="hidden" name="roles_submitted" value="1">
                <div class="grid sm:grid-cols-2 gap-2">
                    @foreach ($roles as $role)
                        <label class="flex items-center gap-2 text-sm text-slate-700 p-2 rounded-lg hover:bg-slate-50">
                            <input type="checkbox" name="roles[]" value="{{ $role }}" @checked(in_array($role, $current, true))
                                   class="rounded border-slate-300 text-moaum-blue focus:ring-moaum-blue">
                            {{ $role }}
                        </label>
                    @endforeach
                </div>
                @error('roles') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        @endif

        <div class="card">
            <x-admin.form-actions :cancel="route('admin.users.index')" :submit="$editing ? 'Save user' : 'Add user'" />
        </div>
    </form>

    @if ($editing && ! $isSelf)
        <form method="POST" action="{{ route('admin.users.password-reset', $user) }}" class="card mt-6 max-w-4xl flex flex-wrap items-center justify-between gap-3">
            @csrf
            <p class="text-sm text-slate-600">Email {{ $user->name }} a link to choose a new password.</p>
            <button type="submit" class="btn-ghost text-sm border border-slate-200"><x-icon name="mail" class="w-4 h-4" /> Send reset link</button>
        </form>
    @endif
</x-layouts.admin>
