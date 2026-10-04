<?php

namespace App\Policies;

/** PRD §11. */
class ServicePolicy extends ContentPolicy
{
    protected function area(): string
    {
        return 'services';
    }
}
