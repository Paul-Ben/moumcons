<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Central audit trail writer (PRD §32 — Audit logging).
 *
 * Append-only: entries are created here and by model observers only.
 * There is deliberately no update/delete API, so the log cannot be
 * tampered with through application code paths exposed to users.
 */
class AuditLogger
{
    /**
     * Record an administrative action.
     *
     * @param string $action      dotted action key, e.g. 'login', 'enquiry.updated'
     * @param string $description human-readable one-liner for the viewer
     * @param array  $properties  context payload ('changes' diff, extra data)
     * @param Model|null $subject the audited record (polymorphic)
     */
    public function log(string $action, string $description, array $properties = [], ?Model $subject = null): void
    {
        AuditLog::create([
            'user_id'      => Auth::id(),
            'action'       => $action,
            'description'  => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id'   => $subject?->getKey(),
            'properties'   => $properties ?: null,
            'ip_address'   => Request::ip(),
            'user_agent'   => substr((string) Request::userAgent(), 0, 500),
        ]);
    }

    /**
     * Log a model update with a before/after diff of tracked attributes.
     * Sensitive keys are never written into properties.
     */
    public function logModelUpdate(string $actionPrefix, Model $model, array $oldAttributes): void
    {
        $changes = [];

        foreach ($model->getChanges() as $key => $new) {
            if (in_array($key, ['password', 'remember_token', 'updated_at', 'created_at'], true)) {
                continue;
            }

            $old = $oldAttributes[$key] ?? null;

            if ($old !== $new) {
                $changes[$key] = ['old' => $old, 'new' => $new];
            }
        }

        if ($changes === []) {
            return;
        }

        $label = class_basename($model);
        $summary = collect($changes)
            ->map(fn ($c, $k) => sprintf('%s: "%s" → "%s"', $k, $c['old'] ?? '∅', $c['new'] ?? '∅'))
            ->implode('; ');

        $this->log(
            action: $actionPrefix . '.updated',
            description: sprintf('%s #%s updated — %s', $label, $model->getKey(), $summary),
            properties: ['changes' => $changes],
            subject: $model,
        );
    }
}
