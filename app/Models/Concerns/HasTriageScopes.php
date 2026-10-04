<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Query scopes shared by the admin triage queues (Enquiry, ServiceRequest,
 * QuoteRequest — PRD §12/§13/§21). Each model names the free-text columns its
 * queue searches via triageSearchColumns().
 */
trait HasTriageScopes
{
    /** @return list<string> columns matched by the queue's free-text search */
    abstract protected function triageSearchColumns(): array;

    /** Free-text triage search across the fields staff actually scan. */
    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (blank($term)) {
            return $q;
        }

        // Escape LIKE wildcards so a literal % or _ cannot widen the search.
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], trim($term)).'%';

        return $q->where(function (Builder $inner) use ($like): void {
            foreach ($this->triageSearchColumns() as $column) {
                $inner->orWhere($column, 'like', $like);
            }
        });
    }

    public function scopeStatus(Builder $q, ?string $status): Builder
    {
        return $status ? $q->where('status', $status) : $q;
    }

    public function scopeDivision(Builder $q, ?int $divisionId): Builder
    {
        return $divisionId ? $q->where('business_division_id', $divisionId) : $q;
    }

    public function scopeAssignedTo(Builder $q, ?int $userId): Builder
    {
        return $userId ? $q->where('assigned_to', $userId) : $q;
    }

    public function scopeUnassigned(Builder $q, bool $only = true): Builder
    {
        return $only ? $q->whereNull('assigned_to') : $q;
    }

    /** Received-date window; either bound may be omitted. */
    public function scopeReceivedBetween(Builder $q, ?string $from, ?string $to): Builder
    {
        return $q
            ->when($from, fn (Builder $b) => $b->whereDate('created_at', '>=', $from))
            ->when($to, fn (Builder $b) => $b->whereDate('created_at', '<=', $to));
    }
}
