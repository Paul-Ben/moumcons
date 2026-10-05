<?php

namespace App\Policies;

/** PRD §18 — document library. */
class DocumentPolicy extends ContentPolicy
{
    protected function area(): string
    {
        return 'downloads';
    }
}
