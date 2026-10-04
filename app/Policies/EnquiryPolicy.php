<?php

namespace App\Policies;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Models\User;

/**
 * Enquiry policy — gates the Module 4 admin triage screens (PRD §21/§23).
 *
 * Authorization is permission-based, never role-name checks. The four PRD
 * permissions map onto distinct triage powers so an organisation can, for
 * example, let a manager progress and annotate enquiries without letting them
 * formally close them.
 */
class EnquiryPolicy
{
    /** Statuses that constitute formally closing an enquiry. */
    private const CLOSING_STATUSES = [EnquiryStatus::Resolved->value, EnquiryStatus::Closed->value];

    public function viewAny(User $user): bool
    {
        return $user->can('view-enquiries');
    }

    public function view(User $user, Enquiry $enquiry): bool
    {
        return $user->can('view-enquiries');
    }

    /** Progression and internal notes. */
    public function update(User $user, Enquiry $enquiry): bool
    {
        return $user->can('update-enquiries');
    }

    /** Ownership of the enquiry. */
    public function assign(User $user, Enquiry $enquiry): bool
    {
        return $user->can('assign-enquiries');
    }

    /**
     * Resolution/closure is gated separately from ordinary progression: closing
     * an enquiry is the point at which it leaves the open queue and is treated
     * as a commitment to the customer.
     */
    public function close(User $user, Enquiry $enquiry): bool
    {
        return $user->can('close-enquiries');
    }

    /**
     * Attachments hold customer-supplied documents (potentially commercially
     * sensitive, PRD §33), so they inherit enquiry visibility.
     */
    public function downloadAttachment(User $user, Enquiry $enquiry): bool
    {
        return $user->can('view-enquiries');
    }

    /**
     * Whether moving an enquiry into (or out of) a closing status requires the
     * stronger 'close-enquiries' permission. Both directions are gated: closing
     * is a commitment to the customer, and reopening re-enters a settled record
     * back into the live queue.
     *
     * Shared by the form request and the view so the UI and the server agree.
     */
    public static function statusTransitionRequiresClosePermission(Enquiry $enquiry, ?string $newStatus): bool
    {
        $wasClosing = in_array($enquiry->status?->value, self::CLOSING_STATUSES, true);
        $willClose = in_array($newStatus, self::CLOSING_STATUSES, true);

        return $wasClosing !== $willClose;
    }
}
