<?php

namespace App\Models;

use App\Enums\EnquiryStatus;
use App\Enums\Priority;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * PRD §21 — generic enquiry record (Contact form, Flow C). All enquiries
 * are persisted here and triaged in the admin inbox (Module 10).
 */
class Enquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'organization', 'email', 'phone', 'subject',
        'business_division_id', 'service_id', 'message', 'attachment', 'consent',
        'status', 'assigned_to', 'priority', 'internal_notes', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => EnquiryStatus::class,
            'priority' => Priority::class,
            'resolved_at' => 'datetime',
        ];
    }

    /* ------------------------------ Relations ---------------------------- */

    public function division(): BelongsTo
    {
        return $this->belongsTo(BusinessDivision::class, 'business_division_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /* ------------------------------- Helpers ----------------------------- */

    /** Public status-tracking lookup: reference + email must both match. */
    public static function findByPublicReference(string $reference, string $email): ?self
    {
        return static::query()
            ->whereRaw('UPPER(reference) = ?', [Str::upper(trim($reference))])
            ->whereRaw('LOWER(email) = ?', [Str::lower(trim($email))])
            ->first();
    }

    protected static function booted(): void
    {
        static::creating(function (self $enquiry) {
            if (empty($enquiry->reference)) {
                do {
                    $ref = 'ENQ-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
                } while (static::where('reference', $ref)->exists());
                $enquiry->reference = $ref;
            }
        });
    }

    /* -------------------------------- Scopes ----------------------------- */

    public function scopeOpen(Builder $q): Builder
    {
        return $q->whereIn('status', [EnquiryStatus::New, EnquiryStatus::Open, EnquiryStatus::InProgress]);
    }

    /** Free-text triage search across the fields staff actually scan. */
    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (blank($term)) {
            return $q;
        }

        // Escape LIKE wildcards so a literal % or _ cannot widen the search.
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], trim($term)).'%';

        return $q->where(function (Builder $inner) use ($like): void {
            $inner->where('name', 'like', $like)
                ->orWhere('organization', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('reference', 'like', $like)
                ->orWhere('subject', 'like', $like);
        });
    }

    public function scopeStatus(Builder $q, ?string $status): Builder
    {
        return $status ? $q->where('status', $status) : $q;
    }

    public function scopePriority(Builder $q, ?string $priority): Builder
    {
        return $priority ? $q->where('priority', $priority) : $q;
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
