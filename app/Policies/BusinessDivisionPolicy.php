<?php

namespace App\Policies;

/** PRD §10. */
class BusinessDivisionPolicy extends ContentPolicy
{
    protected function area(): string
    {
        return 'divisions';
    }
}
