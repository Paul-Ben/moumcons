<?php

namespace App\Policies;

/** PRD §17 — job adverts. Applications have their own policy. */
class JobOpeningPolicy extends ContentPolicy
{
    protected function area(): string
    {
        return 'careers';
    }
}
