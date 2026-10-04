<?php

namespace App\Policies;

/** PRD §11 — categories are managed with the services permissions. */
class ServiceCategoryPolicy extends ContentPolicy
{
    protected function area(): string
    {
        return 'services';
    }
}
