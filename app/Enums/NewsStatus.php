<?php

namespace App\Enums;

enum NewsStatus: string
{
    use TraitHasStatusLabels;

    case Draft = 'draft';
    case Review = 'review';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';
}
