<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a customer upload from the non-public disk and records the access
 * (PRD §32/§33). Callers authorise first; this only checks the file exists.
 */
trait StreamsPrivateAttachment
{
    protected function streamAttachment(Model $record, string $actionPrefix, AuditLogger $audit): StreamedResponse
    {
        $path = $record->attachment;

        if (blank($path) || ! Storage::disk('private')->exists($path)) {
            abort(404);
        }

        $audit->log(
            action: $actionPrefix.'.attachment_downloaded',
            description: sprintf('Attachment for %s %s downloaded by %s', class_basename($record), $record->reference, Auth::user()->name),
            properties: ['reference' => $record->reference, 'path' => $path],
            subject: $record,
        );

        // The stored name already carries the reference and the visitor's own
        // filename, so it can be handed to the browser unchanged.
        return Storage::disk('private')->download($path, basename($path));
    }
}
