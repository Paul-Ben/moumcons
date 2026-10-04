<?php

namespace App\Services;

use App\Models\Enquiry;
use App\Models\QuoteRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\AdminNewRequestAlert;
use App\Notifications\RequestSubmitted;
use Illuminate\Support\Facades\Notification;

/**
 * PRD §12/§13/§21/§25 — outbound notifications for the customer request flows
 * and the contact form: customer receipt + triage alert. Uses the Notifications
 * API; the Queueable trait routes these through the queue in production and
 * sync in tests.
 */
class RequestNotifier
{
    public function serviceSubmitted(ServiceRequest $request): void
    {
        Notification::route('mail', $request->email)->notify(
            new RequestSubmitted('service', $request->reference, $request->service?->name ?? $request->division?->name ?? 'MOAUM services')
        );

        $this->alertTriageUsers(
            new AdminNewRequestAlert('service', $request->reference, $request->name, $request->division?->name ?? '—'),
            ['update-service-requests']
        );
    }

    public function quoteSubmitted(QuoteRequest $quote): void
    {
        Notification::route('mail', $quote->email)->notify(
            new RequestSubmitted('quote', $quote->reference, $quote->project_title)
        );

        $this->alertTriageUsers(
            new AdminNewRequestAlert('quote', $quote->reference, $quote->name, $quote->division?->name ?? '—'),
            ['update-quotes']
        );
    }

    /**
     * PRD §21/§25 — contact-form enquiry. Reuses the same two notifications as
     * the request flows: a receipt for the visitor, an alert for triage staff.
     */
    public function enquirySubmitted(Enquiry $enquiry): void
    {
        Notification::route('mail', $enquiry->email)->notify(
            new RequestSubmitted('enquiry', $enquiry->reference, $enquiry->subject)
        );

        $this->alertTriageUsers(
            new AdminNewRequestAlert('enquiry', $enquiry->reference, $enquiry->name, $enquiry->division?->name ?? 'General enquiry'),
            ['view-enquiries', 'update-enquiries']
        );
    }

    /**
     * Active users who can work the given flow.
     *
     * @param  list<string>  $permissions  permissions that qualify a recipient
     */
    private function alertTriageUsers(AdminNewRequestAlert $alert, array $permissions): void
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
            ->whereHas('roles.permissions', fn ($q) => $q->whereIn('name', $permissions))
            ->get()
            ->each(fn (User $user) => $user->notify($alert));
    }
}
