<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * PRD §12 — Service Request (Flow A). Submitted without an account;
 * trackable publicly via reference + email.
 */
class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'organization', 'email', 'phone',
        'business_division_id', 'service_id', 'location', 'preferred_date',
        'requirements', 'budget_range', 'attachment', 'consent',
        'status', 'assigned_to', 'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => RequestStatus::class,
            'preferred_date' => 'date',
            'consent' => 'boolean',
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

    /* -------------------------------- Scopes ----------------------------- */

    /**
     * Requests still moving through triage — i.e. everything that has not been
     * completed, closed or cancelled (PRD §12).
     */
    public function scopeOpen(Builder $q): Builder
    {
        return $q->whereIn('status', [
            RequestStatus::New,
            RequestStatus::Assigned,
            RequestStatus::InProgress,
            RequestStatus::AwaitingCustomer,
            RequestStatus::Quoted,
        ]);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /* ------------------------------- Helpers ----------------------------- */

    public static function findByPublicReference(string $reference, string $email): ?self
    {
        return static::query()
            ->whereRaw('UPPER(reference) = ?', [Str::upper(trim($reference))])
            ->whereRaw('LOWER(email) = ?', [Str::lower(trim($email))])
            ->first();
    }

    /** Last public-facing status line shown on the tracking page. */
    public function statusTimeline(): array
    {
        $order = [
            RequestStatus::New, RequestStatus::Assigned, RequestStatus::InProgress,
            RequestStatus::AwaitingCustomer, RequestStatus::Quoted, RequestStatus::Completed,
        ];
        $current = array_search($this->status, $order, true);
        // Closed/Cancelled render as a terminal state badge instead of a timeline.
        if ($current === false || $this->status->value === 'cancelled') {
            return [];
        }

        return collect($order)
            ->take($current === null ? 1 : $current + 1)
            ->map(fn (RequestStatus $s) => ['label' => $s->label(), 'done' => true])
            ->concat(
                collect($order)->skip(($current ?? 0) + 1)
                    ->map(fn (RequestStatus $s) => ['label' => $s->label(), 'done' => false])
            )
            ->all();
    }

    protected static function booted(): void
    {
        static::creating(function (self $request) {
            if (empty($request->reference)) {
                do {
                    $ref = 'SRQ-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
                } while (static::where('reference', $ref)->exists());
                $request->reference = $ref;
            }
        });
    }
}
