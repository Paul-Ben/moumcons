<?php

namespace App\Enums;

enum Priority: string
{
    use TraitHasStatusLabels;

    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';
}
