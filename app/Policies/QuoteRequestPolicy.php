<?php

namespace App\Policies;

use App\Enums\QuoteStatus;
use App\Models\QuoteRequest;
use App\Models\User;

/**
 * Quote request policy — gates the admin screens for Flow B (PRD §13/§23).
 *
 * Mirrors the four quote permissions in Rbac: seeing the queue, working it
 * (progression, ownership, notes), preparing and sending the quote itself,
 * and settling it (accepted / declined / converted to an order).
 */
class QuoteRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-quotes');
    }

    public function view(User $user, QuoteRequest $quote): bool
    {
        return $user->can('view-quotes');
    }

    /** Progression, ownership and internal notes. */
    public function update(User $user, QuoteRequest $quote): bool
    {
        return $user->can('update-quotes');
    }

    /** Pricing the work and sending the quote to the customer. */
    public function prepare(User $user, QuoteRequest $quote): bool
    {
        return $user->can('create-quotes');
    }

    /** Recording the customer's decision, which takes the quote off the queue. */
    public function close(User $user, QuoteRequest $quote): bool
    {
        return $user->can('close-quotes');
    }

    public function downloadAttachment(User $user, QuoteRequest $quote): bool
    {
        return $user->can('view-quotes');
    }

    /**
     * Moving into or out of a settled status needs 'close-quotes' — both
     * directions, as with enquiries: settling is a commitment to the customer
     * and reopening puts a decided quote back on the queue.
     */
    public static function statusTransitionRequiresClosePermission(QuoteRequest $quote, ?string $newStatus): bool
    {
        $closing = array_map(fn (QuoteStatus $s) => $s->value, QuoteRequest::CLOSING_STATUSES);

        return in_array($quote->status?->value, $closing, true) !== in_array($newStatus, $closing, true);
    }

    /** Sending the quote is part of preparing it, so it needs 'create-quotes'. */
    public static function statusTransitionRequiresPreparePermission(QuoteRequest $quote, ?string $newStatus): bool
    {
        return $newStatus === QuoteStatus::QuoteSent->value && $quote->status !== QuoteStatus::QuoteSent;
    }
}
