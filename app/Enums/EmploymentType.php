<?php

namespace App\Enums;

enum EmploymentType: string
{
    use TraitHasStatusLabels;

    case FullTime = 'full_time';
    case PartTime = 'part_time';
    case Contract = 'contract';
    case Internship = 'internship';
    case Volunteer = 'volunteer';
}
