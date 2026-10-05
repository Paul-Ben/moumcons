<?php

namespace App\Models;

use App\Casts\RichTextCast;
use App\Enums\DeliveryMode;
use App\Enums\TrainingStatus;
use App\Models\Concerns\HasUniqueSlug;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** PRD §15 — training / capacity-building programme. */
class TrainingProgramme extends Model
{
    use HasFactory, HasUniqueSlug, Publishable;

    protected $fillable = [
        'business_division_id', 'title', 'slug', 'summary', 'description',
        'course_category', 'trainer', 'duration', 'start_date', 'end_date',
        'registration_deadline', 'delivery_mode', 'venue', 'fee', 'capacity',
        'curriculum', 'requirements', 'certificate_info', 'status',
        'featured_image', 'featured', 'seo_title', 'seo_description', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TrainingStatus::class,
            'delivery_mode' => DeliveryMode::class,
            'description' => RichTextCast::class,
            'curriculum' => RichTextCast::class,
            'requirements' => RichTextCast::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'registration_deadline' => 'date',
            'fee' => 'decimal:2',
            'capacity' => 'integer',
            'featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(BusinessDivision::class, 'business_division_id');
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('featured', true);
    }

    /** Programmes that have not finished or been cancelled. */
    public function scopeCurrent(Builder $q): Builder
    {
        return $q->whereNotIn('status', [TrainingStatus::Completed, TrainingStatus::Cancelled]);
    }

    /** Soonest first; undated programmes last. */
    public function scopeChronological(Builder $q): Builder
    {
        return $q->orderByRaw('start_date IS NULL')->orderBy('start_date')->orderBy('title');
    }

    /** Interest can be registered while the programme is upcoming/open and the deadline has not passed. */
    public function acceptsInterest(): bool
    {
        if (! in_array($this->status, [TrainingStatus::Upcoming, TrainingStatus::OpenForRegistration], true)) {
            return false;
        }

        return $this->registration_deadline === null || $this->registration_deadline->endOfDay()->isFuture();
    }

    public function feeLabel(): string
    {
        return $this->fee === null ? 'Contact us' : ((float) $this->fee === 0.0 ? 'Free' : '₦'.number_format((float) $this->fee));
    }

    /** "12 – 14 May 2026", "12 May – 3 Jun 2026" or a single date. */
    public function dateRange(): ?string
    {
        $start = $this->start_date;
        $end = $this->end_date;

        if (! $start) {
            return null;
        }

        if (! $end || $start->isSameDay($end)) {
            return $start->format('j M Y');
        }

        return match (true) {
            $start->isSameMonth($end) => $start->format('j').' – '.$end->format('j M Y'),
            $start->isSameYear($end) => $start->format('j M').' – '.$end->format('j M Y'),
            default => $start->format('j M Y').' – '.$end->format('j M Y'),
        };
    }
}
