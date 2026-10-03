<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    use TraitHasStatusLabels;

    case Received = 'received';
    case UnderReview = 'under_review';
    case Shortlisted = 'shortlisted';
    case Interview = 'interview';
    case Rejected = 'rejected';
    case Hired = 'hired';
}
