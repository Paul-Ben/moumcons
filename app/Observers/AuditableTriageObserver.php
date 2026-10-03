<?php

namespace App\Observers;

use App\Models\Enquiry;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic append/update/delete audit observer for triage models
 * (Enquiry, ServiceRequest, QuoteRequest). Delegates to AuditLogger so
 * controllers stay thin; status transitions get their own action keys.
 */
class AuditableTriageObserver
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function created(Model $model): void
    {
        $this->audit->log(
            action: $this->prefix($model) . '.created',
            description: sprintf(
                '%s %s created (%s)',
                class_basename($model),
                $model->reference ?? '#' . $model->getKey(),
                $model->status?->value ?? 'new'
            ),
            properties: ['reference' => $model->reference ?? null],
            subject: $model,
        );
    }

    public function updated(Model $model): void
    {
        // Status/priority/assignment changes are the meaningful triage events.
        if ($model->wasChanged('status')) {
            $this->audit->log(
                action: $this->prefix($model) . '.status_changed',
                description: sprintf(
                    '%s %s status: "%s" → "%s"',
                    class_basename($model),
                    $model->reference ?? '#' . $model->getKey(),
                    $model->getOriginal('status') instanceof \BackedEnum ? $model->getOriginal('status')->value : $model->getOriginal('status'),
                    $model->status?->value ?? '',
                ),
                properties: [
                    'changes' => [
                        'status' => [
                            'old' => $model->getOriginal('status') instanceof \BackedEnum
                                ? $model->getOriginal('status')->value
                                : $model->getOriginal('status'),
                            'new' => $model->status?->value,
                        ],
                    ],
                ],
                subject: $model,
            );
        }

        if ($model->wasChanged('assigned_to')) {
            $this->audit->log(
                action: $this->prefix($model) . '.assigned',
                description: sprintf('%s %s assignment changed', class_basename($model), $model->reference ?? '#' . $model->getKey()),
                properties: [
                    'changes' => [
                        'assigned_to' => ['old' => $model->getOriginal('assigned_to'), 'new' => $model->assigned_to],
                    ],
                ],
                subject: $model,
            );
        }

        $this->audit->logModelUpdate($this->prefix($model), $model, $model->getOriginal());
    }

    public function deleted(Model $model): void
    {
        $this->audit->log(
            action: $this->prefix($model) . '.deleted',
            description: sprintf('%s %s deleted', class_basename($model), $model->reference ?? '#' . $model->getKey()),
            properties: ['reference' => $model->reference ?? null],
        );
    }

    private function prefix(Model $model): string
    {
        return match (true) {
            $model instanceof Enquiry => 'enquiry',
            default => strtolower(class_basename($model)),
        };
    }
}
