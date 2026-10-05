<?php

namespace App\Policies;

/** PRD §22 — FAQs. */
class FaqPolicy extends ContentPolicy
{
    protected function area(): string
    {
        return 'faqs';
    }
}
