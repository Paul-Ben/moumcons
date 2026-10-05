<?php

namespace App\Policies;

/** PRD §19 — galleries. */
class GalleryPolicy extends ContentPolicy
{
    protected function area(): string
    {
        return 'gallery';
    }
}
