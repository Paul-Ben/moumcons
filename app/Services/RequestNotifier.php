<?php

namespace App\Services;

use App\Models\Enquiry;
use App\Models\JobApplication;
use App\Models\QuoteRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\AdminNewRequestAlert;
use App\Notifications\EnquiryAssigned;
use App\Notifications\QuoteAvailable;
use App\Notifications\RequestAssigned;
use App\Notifications\RequestStatusChanged;
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
            new AdminNewRequestAlert('service', $request->reference, $request->name, $request->division?->name ?? '—', route('admin.service-requests.show', $request)),
            ['update-service-requests']
        );
    }

    public function quoteSubmitted(QuoteRequest $quote): void
    {
        Notification::route('mail', $quote->email)->notify(
            new RequestSubmitted('quote', $quote->reference, $quote->project_title)
        );

        $this->alertTriageUsers(
            new AdminNewRequestAlert('quote', $quote->reference, $quote->name, $quote->division?->name ?? '—', route('admin.quote-requests.show', $quote)),
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
            new AdminNewRequestAlert('enquiry', $enquiry->reference, $enquiry->name, $enquiry->division?->name ?? 'General enquiry', route('admin.enquiries.show', $enquiry)),
            ['view-enquiries', 'update-enquiries']
        );
    }

    /**
     * PRD §21/§25 — an enquiry has been handed to a member of staff. Null-safe
     * so an unassign (or reassignment) never sends anything.
     */
    public function enquiryAssigned(?User $assignee, Enquiry $enquiry): void
    {
        $assignee?->notify(new EnquiryAssigned($enquiry));
    }

    /** PRD §17/§25 "New application" — receipt for the applicant, alert for HR staff. */
    public function applicationSubmitted(JobApplication $application): void
    {
        Notification::route('mail', $application->email)->notify(
            new RequestSubmitted('application', $application->reference, $application->job->title)
        );

        $this->alertTriageUsers(
            new AdminNewRequestAlert('application', $application->reference, $application->name, $application->job->title, route('admin.applications.show', $application)),
            ['view-applications']
        );
    }

    /** PRD §12/§13/§25 — a service or quote request has been handed to staff. */
    public function requestAssigned(?User $assignee, ServiceRequest|QuoteRequest $request): void
    {
        if ($assignee === null) {
            return;
        }

        $isQuote = $request instanceof QuoteRequest;

        $assignee->notify(new RequestAssigned(
            type: $isQuote ? 'quote' : 'service',
            reference: $request->reference,
            requesterName: $request->name,
            summary: $isQuote ? $request->project_title : ($request->service?->name ?? $request->division?->name ?? 'Service request'),
            url: $isQuote ? route('admin.quote-requests.show', $request) : route('admin.service-requests.show', $request),
        ));
    }

    /** PRD §25 "Request status changed" — customer-facing status update. */
    public function statusChanged(ServiceRequest|QuoteRequest $request): void
    {
        Notification::route('mail', $request->email)->notify(new RequestStatusChanged(
            type: $request instanceof QuoteRequest ? 'quote' : 'service',
            reference: $request->reference,
            statusLabel: $request->status->label(),
        ));
    }

    /** PRD §25 "Quote available". */
    public function quoteSent(QuoteRequest $quote): void
    {
        Notification::route('mail', $quote->email)->notify(new QuoteAvailable($quote));
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
