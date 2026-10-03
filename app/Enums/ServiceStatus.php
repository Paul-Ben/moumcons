<?php

namespace App\Enums;

enum ServiceStatus: string
{
    use TraitHasStatusLabels;

    case Active = 'active';
    case Draft = 'draft';
    case Archived = 'archived';
}
