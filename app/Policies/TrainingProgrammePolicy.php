<?php

namespace App\Policies;

/** PRD §15 — training programmes. */
class TrainingProgrammePolicy extends ContentPolicy
{
    protected function area(): string
    {
        return 'training';
    }
}
