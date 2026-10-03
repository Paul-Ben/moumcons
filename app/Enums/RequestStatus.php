<?php

namespace App\Enums;

/**
 * PRD §12 — service request workflow statuses.
 */
enum RequestStatus: string
{
    use TraitHasStatusLabels;

    case New = 'new';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case AwaitingCustomer = 'awaiting_customer';
    case Quoted = 'quoted';
    case Completed = 'completed';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
}
