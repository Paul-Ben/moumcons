<?php

namespace App\Policies;

/** PRD §9 — the leadership team is managed with the pages permissions. */
class LeadershipMemberPolicy extends ContentPolicy
{
    protected function area(): string
    {
        return 'pages';
    }
}
