<?php

namespace App\Models;

use App\Enums\QuoteStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * PRD §13 — Quote Request (Flow B). Trackable publicly via reference + email.
 */
class QuoteRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'organization', 'email', 'phone',
        'business_division_id', 'service_id', 'project_title', 'location',
        'requirements', 'estimated_quantity', 'desired_start_date',
        'desired_completion_date', 'budget_range', 'attachment', 'consent',
        'status', 'assigned_to', 'internal_notes',
    ];

    /** DB default + safety net so 'new' (a ServiceRequest value) is never used. */
    protected function attributes(): array
    {
        return ['status' => QuoteStatus::Requested->value];
    }

    protected function casts(): array
    {
        return [
            'status' => QuoteStatus::class,
            'desired_start_date' => 'date',
            'desired_completion_date' => 'date',
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

    /** PRD §13 workflow rendered as a public timeline. */
    public function statusTimeline(): array
    {
        $order = [
            QuoteStatus::Requested, QuoteStatus::Review, QuoteStatus::Clarification,
            QuoteStatus::QuotePreparation, QuoteStatus::QuoteSent,
            QuoteStatus::Accepted, QuoteStatus::Declined, QuoteStatus::OrderContract,
        ];
        $current = array_search($this->status, $order, true);

        if ($current === false) {
            return [];
        }

        return collect($order)
            ->take($current + 1)
            ->map(fn (QuoteStatus $s) => ['label' => $s->label(), 'done' => true])
            ->concat(
                collect($order)->skip($current + 1)
                    ->map(fn (QuoteStatus $s) => ['label' => $s->label(), 'done' => false])
            )
            ->all();
    }

    protected static function booted(): void
    {
        static::creating(function (self $quote) {
            if (empty($quote->reference)) {
                do {
                    $ref = 'QTE-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
                } while (static::where('reference', $ref)->exists());
                $quote->reference = $ref;
            }
        });
    }
}
