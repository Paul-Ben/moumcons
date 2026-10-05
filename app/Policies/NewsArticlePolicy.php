<?php

namespace App\Policies;

/** PRD §16 — news; categories share the news permissions. */
class NewsArticlePolicy extends ContentPolicy
{
    protected function area(): string
    {
        return 'news';
    }
}
