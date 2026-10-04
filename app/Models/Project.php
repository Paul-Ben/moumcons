<?php

namespace App\Models;

use App\Casts\RichTextCast;
use App\Enums\ProjectStatus;
use App\Models\Concerns\HasUniqueSlug;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** PRD §14 — project / portfolio entry ("proof of capability"). */
class Project extends Model
{
    use HasFactory, HasUniqueSlug, Publishable;

    protected $fillable = [
        'business_division_id', 'title', 'slug', 'client', 'location', 'summary',
        'description', 'scope', 'start_date', 'completion_date', 'status',
        'featured_image', 'featured', 'tags', 'sort_order',
        'seo_title', 'seo_description', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'description' => RichTextCast::class,
            'scope' => RichTextCast::class,
            'start_date' => 'date',
            'completion_date' => 'date',
            'featured' => 'boolean',
            'tags' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(BusinessDivision::class, 'business_division_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order');
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('featured', true);
    }

    /** Featured first, then by display order, newest first. */
    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderByDesc('completion_date')->orderByDesc('id');
    }

    /** Year span for cards, e.g. "2024 – 2025" or "2025 – present". */
    public function period(): ?string
    {
        if (! $this->start_date && ! $this->completion_date) {
            return null;
        }

        $start = $this->start_date?->format('Y');
        $end = $this->completion_date?->format('Y') ?? ($this->status === ProjectStatus::Ongoing ? 'present' : null);

        return match (true) {
            $start && $end && $start !== $end => "{$start} – {$end}",
            default => $start ?? $end,
        };
    }
}
