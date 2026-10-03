<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Rbac;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /*
         * Super Administrators bypass all Gate checks (PRD §23: role-based
         * access with an unconditional top tier). Spatie's "super admin"
         * concept is handled here instead of granting every permission.
         */
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole(Rbac::SUPER_ADMINISTRATOR)) {
                return true;
            }

            return null;
        });

        /*
         * Policies for Module 9 CMS resources are registered there as the
         * models come into existence; RBAC permissions drive authorization
         * through them (never role-name checks in controllers).
         */
    }
}
