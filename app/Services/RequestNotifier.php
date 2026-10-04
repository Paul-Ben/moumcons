<?php

namespace App\Services;

use App\Models\QuoteRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\AdminNewRequestAlert;
use App\Notifications\RequestSubmitted;
use Illuminate\Support\Facades\Notification;

/**
 * PRD §12/§13/§25 — outbound notifications for the customer request flows:
 * customer receipt + triage alert. Uses the Notifications API; the Queueable
 * trait routes these through the queue in production and sync in tests.
 */
class RequestNotifier
{
    public function serviceSubmitted(ServiceRequest $request): void
    {
        Notification::route('mail', $request->email)->notify(
            new RequestSubmitted('service', $request->reference, $request->service?->name ?? $request->division?->name ?? 'MOAUM services')
        );

        $this->alertTriageUsers(
            new AdminNewRequestAlert('service', $request->reference, $request->name, $request->division?->name ?? '—')
        );
    }

    public function quoteSubmitted(QuoteRequest $quote): void
    {
        Notification::route('mail', $quote->email)->notify(
            new RequestSubmitted('quote', $quote->reference, $quote->project_title)
        );

        $this->alertTriageUsers(
            new AdminNewRequestAlert('quote', $quote->reference, $quote->name, $quote->division?->name ?? '—')
        );
    }

    /** Active users who can work service or quote requests. */
    private function alertTriageUsers(AdminNewRequestAlert $alert): void
    {
        /*
         * Resolved through the roles relation instead of spatie's
         * User::permission() scope: that scope resolves names against the
         * permission cache and throws PermissionDoesNotExist when RBAC has not
         * been seeded, which would turn a public form submission into a 500.
         * Triage notifications are best-effort, so an unseeded install simply
         * alerts nobody.
         */
        User::query()
            ->where('is_active', true)
            ->whereHas('roles.permissions', fn ($q) => $q->whereIn('name', [
                'update-service-requests',
                'update-quotes',
            ]))
            ->get()
            ->each(fn (User $user) => $user->notify($alert));
    }
}
