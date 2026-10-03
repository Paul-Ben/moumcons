<?php

namespace App\Enums;

enum DivisionStatus: string
{
    use TraitHasStatusLabels;

    case Active = 'active';
    case Planned = 'planned';
    case ComingSoon = 'coming_soon';
    case Suspended = 'suspended';
    case Archived = 'archived';
}
