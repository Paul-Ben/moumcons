<?php

namespace App\Enums;

enum EnquiryStatus: string
{
    use TraitHasStatusLabels;

    case New = 'new';
    case Open = 'open';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';
}
