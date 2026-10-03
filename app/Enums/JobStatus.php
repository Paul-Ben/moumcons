<?php

namespace App\Enums;

enum JobStatus: string
{
    use TraitHasStatusLabels;

    case Open = 'open';
    case Closed = 'closed';
    case Filled = 'filled';
}
