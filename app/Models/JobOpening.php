<?php

namespace App\Models;

use App\Casts\RichTextCast;
use App\Enums\EmploymentType;
use App\Enums\JobStatus;
use App\Models\Concerns\HasUniqueSlug;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** PRD §17 — a job opening (table `careers`, §27). */
class JobOpening extends Model
{
    use HasFactory, HasUniqueSlug, Publishable;

    protected $table = 'careers';

    protected $fillable = [
        'business_division_id', 'title', 'slug', 'location', 'employment_type', 'summary',
        'description', 'responsibilities', 'qualifications', 'requirements',
        'application_deadline', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'employment_type' => EmploymentType::class,
            'status' => JobStatus::class,
            'description' => RichTextCast::class,
            'responsibilities' => RichTextCast::class,
            'qualifications' => RichTextCast::class,
            'requirements' => RichTextCast::class,
            'application_deadline' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(BusinessDivision::class, 'business_division_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'career_id');
    }

    /** Open, and the deadline (if any) has not passed. */
    public function acceptsApplications(): bool
    {
        return $this->status === JobStatus::Open
            && ($this->application_deadline === null || $this->application_deadline->endOfDay()->isFuture());
    }
}
