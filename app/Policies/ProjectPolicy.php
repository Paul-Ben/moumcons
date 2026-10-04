<?php

namespace App\Policies;

/** PRD §14 — projects / portfolio. */
class ProjectPolicy extends ContentPolicy
{
    protected function area(): string
    {
        return 'projects';
    }
}
