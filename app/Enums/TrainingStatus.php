<?php

namespace App\Enums;

enum TrainingStatus: string
{
    use TraitHasStatusLabels;

    case Upcoming = 'upcoming';
    case OpenForRegistration = 'open_for_registration';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
