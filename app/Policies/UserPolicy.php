<?php

namespace App\Policies;

use App\Models\User;

/**
 * User policy — gates the Module 10 "Users & Roles" admin screens.
 * Authorization is permission-based (PRD §23), never role-name checks.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-users');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('manage-users') || $user->is($model);
    }

    public function create(User $user): bool
    {
        return $user->can('manage-users');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('manage-users');
    }

    public function delete(User $user, User $model): bool
    {
        // Users may never delete themselves.
        return $user->can('manage-users') && ! $user->is($model);
    }

    /**
     * Assigning roles requires manage-roles; Super Admins bypass via Gate::before.
     */
    public function assignRoles(User $user): bool
    {
        return $user->can('manage-roles');
    }
}
