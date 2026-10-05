<?php

namespace App\Policies;

use App\Models\JobApplication;
use App\Models\User;

/** PRD §17/§33 — applications hold personal data and CVs; access is its own permission. */
class JobApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-applications');
    }

    public function view(User $user, JobApplication $application): bool
    {
        return $user->can('view-applications');
    }

    public function update(User $user, JobApplication $application): bool
    {
        return $user->can('update-applications');
    }
}
