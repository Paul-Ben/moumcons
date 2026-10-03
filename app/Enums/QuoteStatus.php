<?php

namespace App\Enums;

/**
 * PRD §13 — quote workflow.
 */
enum QuoteStatus: string
{
    use TraitHasStatusLabels;

    case Requested = 'requested';
    case Review = 'review';
    case Clarification = 'clarification';
    case QuotePreparation = 'quote_preparation';
    case QuoteSent = 'quote_sent';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case OrderContract = 'order_contract';
}
