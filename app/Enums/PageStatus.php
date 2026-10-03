<?php

namespace App\Enums;

enum PageStatus: string
{
    use TraitHasStatusLabels;

    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
