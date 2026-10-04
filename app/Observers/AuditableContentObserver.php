<?php

namespace App\Observers;

use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Audit trail for CMS content (PRD §32): every create, edit and delete made
 * through the admin is recorded with a field-level diff. Registered per model
 * in AppServiceProvider. Triage records use AuditableTriageObserver instead.
 */
class AuditableContentObserver
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function created(Model $model): void
    {
        $this->audit->log(
            action: $this->prefix($model).'.created',
            description: sprintf('%s "%s" created', $this->label($model), $this->title($model)),
            subject: $model,
        );
    }

    public function updated(Model $model): void
    {
        $this->audit->logModelUpdate($this->prefix($model), $model, $model->getOriginal());
    }

    public function deleted(Model $model): void
    {
        $this->audit->log(
            action: $this->prefix($model).'.deleted',
            description: sprintf('%s "%s" deleted', $this->label($model), $this->title($model)),
            properties: ['id' => $model->getKey()],
        );
    }

    private function prefix(Model $model): string
    {
        return Str::snake(class_basename($model));
    }

    private function label(Model $model): string
    {
        return Str::headline(class_basename($model));
    }

    private function title(Model $model): string
    {
        return (string) ($model->getAttribute('title')
            ?? $model->getAttribute('name')
            ?? $model->getAttribute('question')
            ?? $model->getAttribute('original_name')
            ?? '#'.$model->getKey());
    }
}
