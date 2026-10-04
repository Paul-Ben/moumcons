<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Content that goes live at `published_at` (PRD §14/§16 "Published date").
 * Blank means unpublished; a future date schedules it.
 */
trait Publishable
{
    public function scopePublished(Builder $q): Builder
    {
        return $q->whereNotNull($this->qualifyColumn('published_at'))
            ->where($this->qualifyColumn('published_at'), '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lte(now());
    }

    public function isScheduled(): bool
    {
        return $this->published_at !== null && $this->published_at->isFuture();
    }

    /** Admin label for the publication state. */
    public function publicationLabel(): string
    {
        return match (true) {
            $this->isPublished() => 'Published',
            $this->isScheduled() => 'Scheduled',
            default => 'Unpublished',
        };
    }

    public function publicationBadgeClasses(): string
    {
        return match (true) {
            $this->isPublished() => 'bg-emerald-50 text-emerald-700',
            $this->isScheduled() => 'bg-sky-50 text-sky-700',
            default => 'bg-slate-100 text-slate-600',
        };
    }
}
