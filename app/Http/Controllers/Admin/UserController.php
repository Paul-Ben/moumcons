<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\Rbac;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * Admin → Users (PRD §6.6/§23, /admin/users in §31).
 *
 * Accounts are deactivated rather than deleted so audit history keeps its
 * author. Guard rails: nobody deactivates themselves, only a Super
 * Administrator grants or removes that role, and the last active Super
 * Administrator cannot be demoted or deactivated.
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'exists:roles,name'],
            'inactive' => ['nullable', 'boolean'],
        ]);

        $users = User::query()
            ->with('roles:id,name')
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($inner) => $inner
                ->where('name', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%')
                ->orWhere('email', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%')))
            ->when($filters['role'] ?? null, fn ($q, $role) => $q->role($role))
            ->when($filters['inactive'] ?? false, fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $filters,
            'roles' => Role::query()->orderBy('name')->pluck('name', 'name'),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('admin.users.form', $this->formData(new User(['is_active' => true])));
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $data = $this->validated($request, null);
        $sendInvite = blank($data['password'] ?? null);

        $user = DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                // An unusable random password until the invite link is used.
                'password' => Hash::make($data['password'] ?? Str::random(40)),
            ]);
            $this->syncRoles($request, $user, $data['roles'] ?? []);

            return $user;
        });

        if ($sendInvite) {
            Password::broker()->sendResetLink(['email' => $user->email]);
        }

        $audit->log('user.created', "User {$user->email} created with roles: ".($user->getRoleNames()->implode(', ') ?: 'none'), subject: $user);

        return redirect()->route('admin.users.edit', $user)->with('success', $sendInvite
            ? "{$user->name} added. A link to set their password has been emailed."
            : "{$user->name} added.");
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        return view('admin.users.form', $this->formData($user->load('roles')));
    }

    public function update(Request $request, User $user, AuditLogger $audit): RedirectResponse
    {
        Gate::authorize('update', $user);

        $data = $this->validated($request, $user);
        $wasActive = $user->is_active;
        $previousRoles = $user->getRoleNames()->sort()->values()->all();

        DB::transaction(function () use ($request, $user, $data, $audit) {
            $original = $user->getOriginal();
            $user->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);

            if (array_key_exists('is_active', $data)) {
                $user->is_active = (bool) $data['is_active'];

                // Invalidate "remember me" cookies as well as sessions.
                if ($user->isDirty('is_active') && ! $user->is_active) {
                    $user->setRememberToken(Str::random(60));
                }
            }

            if (filled($data['password'] ?? null)) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();
            $audit->logModelUpdate('user', $user, $original);

            if ($request->has('roles') || $request->boolean('roles_submitted')) {
                $this->syncRoles($request, $user, $data['roles'] ?? []);
            }
        });

        // A deactivated account is signed out everywhere immediately.
        if ($wasActive && ! $user->is_active) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        $roles = $user->getRoleNames()->sort()->values()->all();
        if ($roles !== $previousRoles) {
            $audit->log('user.roles_changed', sprintf('Roles for %s: [%s] → [%s]', $user->email, implode(', ', $previousRoles), implode(', ', $roles)),
                ['changes' => ['roles' => ['old' => implode(', ', $previousRoles), 'new' => implode(', ', $roles)]]], $user);
        }

        return redirect()->route('admin.users.edit', $user)->with('success', "{$user->name} saved.");
    }

    /** POST /admin/users/{user}/password-reset — email a reset link. */
    public function sendPasswordReset(User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        Password::broker()->sendResetLink(['email' => $user->email]);

        return back()->with('success', "A password reset link has been emailed to {$user->email}.");
    }

    /** @param  list<string>  $roles */
    private function syncRoles(Request $request, User $user, array $roles): void
    {
        $actor = $request->user();

        if (! $actor->can('assignRoles', User::class)) {
            return;
        }

        $grantingSuper = in_array(Rbac::SUPER_ADMINISTRATOR, $roles, true);
        $hasSuper = $user->hasRole(Rbac::SUPER_ADMINISTRATOR);

        if ($grantingSuper !== $hasSuper && ! $actor->hasRole(Rbac::SUPER_ADMINISTRATOR)) {
            throw ValidationException::withMessages(['roles' => 'Only a Super Administrator can grant or remove the Super Administrator role.']);
        }

        if ($hasSuper && ! $grantingSuper && $this->isLastActiveSuperAdmin($user)) {
            throw ValidationException::withMessages(['roles' => 'This is the last active Super Administrator; assign the role to someone else first.']);
        }

        $user->syncRoles($roles);
    }

    private function isLastActiveSuperAdmin(User $user): bool
    {
        return User::query()->role(Rbac::SUPER_ADMINISTRATOR)->where('is_active', true)->whereKeyNot($user->id)->doesntExist();
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?User $user): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'confirmed', PasswordRule::min(10)->mixedCase()->numbers()],
            'is_active' => ['sometimes', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        if ($user && $request->user()->is($user) && array_key_exists('is_active', $data) && ! $data['is_active']) {
            throw ValidationException::withMessages(['is_active' => 'You cannot deactivate your own account.']);
        }

        if ($user && array_key_exists('is_active', $data) && ! $data['is_active']
            && $user->hasRole(Rbac::SUPER_ADMINISTRATOR) && $this->isLastActiveSuperAdmin($user)) {
            throw ValidationException::withMessages(['is_active' => 'The last active Super Administrator cannot be deactivated.']);
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function formData(User $user): array
    {
        $actor = auth()->user();

        return [
            'user' => $user,
            'roles' => Role::query()->orderBy('name')->pluck('name')
                // Only Super Administrators may hand out the top role.
                ->reject(fn ($name) => $name === Rbac::SUPER_ADMINISTRATOR && ! $actor->hasRole(Rbac::SUPER_ADMINISTRATOR) && ! $user->hasRole(Rbac::SUPER_ADMINISTRATOR))
                ->values(),
            'canAssignRoles' => $actor->can('assignRoles', User::class),
            'isSelf' => $actor->is($user),
        ];
    }
}
