<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Append-only audit log entry (PRD §27 audit_logs, §32 Audit logging).
 *
 * Records who did what, when, from where. Read access is gated behind the
 * 'view-audit-logs' permission; writes happen exclusively through the
 * AuditLogger service / model observers — never from request input.
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /* ------------------------------ Relations ---------------------------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->morphTo();
    }

    /* -------------------------------- Scopes ----------------------------- */

    /** Filter by action name or prefix, e.g. 'enquiry' matches 'enquiry.updated'. */
    public function scopeAction(Builder $query, ?string $action): Builder
    {
        if (blank($action)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($action) {
            $q->where('action', $action)
                ->orWhere('action', 'like', $action.'.%');
        });
    }

    /** Entries recorded against one audited record, newest first. */
    public function scopeTrailFor(Builder $query, Model $subject): Builder
    {
        return $query
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey())
            ->with('user:id,name')
            ->latest('id');
    }

    public function scopeForUser(Builder $query, ?string $userId): Builder
    {
        return blank($userId) ? $query : $query->where('user_id', $userId);
    }

    public function scopeBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        if (filled($from)) {
            $query->whereDate('created_at', '>=', $from);
        }

        if (filled($to)) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    /* ------------------------------- Helpers ----------------------------- */

    /** Short display label for the audited subject ("Enquiry #ENQ-..."). */
    public function subjectLabel(): string
    {
        $type = class_basename((string) $this->subject_type);

        return sprintf('%s #%s', $type, $this->subject_id);
    }

    /** Human-readable action, e.g. "enquiry.updated" => "Enquiry Updated". */
    public function actionLabel(): string
    {
        return ucwords(str_replace(['.', '-', '_'], ' ', $this->action));
    }

    /** Diff-style summary of changed attributes captured in properties. */
    public function changesSummary(): array
    {
        $changes = $this->properties['changes'] ?? [];

        $out = [];
        foreach ($changes as $key => $change) {
            $out[] = [
                'field' => Str::headline((string) $key),
                'from' => is_scalar($change['old'] ?? null) ? (string) $change['old'] : json_encode($change['old'] ?? null),
                'to' => is_scalar($change['new'] ?? null) ? (string) $change['new'] : json_encode($change['new'] ?? null),
            ];
        }

        return $out;
    }
}
