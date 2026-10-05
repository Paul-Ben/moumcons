<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * PRD §17 — an application for a job opening (table `applications`, §27).
 * Personal data: visible only to staff with view-applications (§33).
 */
class JobApplication extends Model
{
    use HasFactory;

    protected $table = 'applications';

    protected $fillable = [
        'career_id', 'name', 'email', 'phone', 'qualifications', 'cover_letter',
        'cv_path', 'cv_original_name', 'consent', 'status', 'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'consent' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $application) {
            if (empty($application->reference)) {
                do {
                    $ref = 'APP-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
                } while (static::where('reference', $ref)->exists());
                $application->reference = $ref;
            }
        });

        static::deleted(fn (self $application) => Storage::disk(Document::DISK)->delete($application->cv_path));
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class, 'career_id');
    }

    /** Still being considered. */
    public function scopeActive(Builder $q): Builder
    {
        return $q->whereNotIn('status', [ApplicationStatus::Rejected, ApplicationStatus::Hired]);
    }
}
