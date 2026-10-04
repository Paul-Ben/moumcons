<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Base policy for CMS content (PRD §22/§23). Rbac gives each content area the
 * same five permissions — view-, create-, edit-, publish-, delete-{area} — so
 * a module's policy only names its area.
 *
 * "Publish" covers changing whether and how a record appears on the public
 * site (status, featured); "edit" covers its content.
 */
abstract class ContentPolicy
{
    /** Permission suffix, e.g. 'divisions' for view-divisions. */
    abstract protected function area(): string;

    public function viewAny(User $user): bool
    {
        return $user->can('view-'.$this->area());
    }

    public function view(User $user, Model $model): bool
    {
        return $user->can('view-'.$this->area());
    }

    public function create(User $user): bool
    {
        return $user->can('create-'.$this->area());
    }

    public function update(User $user, Model $model): bool
    {
        return $user->can('edit-'.$this->area());
    }

    /** Model-less so it can gate the create form too. */
    public function publish(User $user, ?Model $model = null): bool
    {
        return $user->can('publish-'.$this->area());
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->can('delete-'.$this->area());
    }
}
