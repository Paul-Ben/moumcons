<?php

namespace App\Enums;

enum ProjectStatus: string
{
    use TraitHasStatusLabels;

    case Planned = 'planned';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';
}
