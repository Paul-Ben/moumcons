<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

/**
 * Service request policy — gates the admin triage screens for Flow A (PRD §12/§23).
 *
 * Rbac defines two powers for this flow: seeing the queue, and working it
 * (progression, ownership and notes). Both are permission checks, never role
 * names.
 */
class ServiceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-service-requests');
    }

    public function view(User $user, ServiceRequest $request): bool
    {
        return $user->can('view-service-requests');
    }

    /** Progression, ownership and internal notes. */
    public function update(User $user, ServiceRequest $request): bool
    {
        return $user->can('update-service-requests');
    }

    /** Customer uploads inherit request visibility (PRD §33). */
    public function downloadAttachment(User $user, ServiceRequest $request): bool
    {
        return $user->can('view-service-requests');
    }
}
