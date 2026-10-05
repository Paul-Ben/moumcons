<?php

namespace App\Models;

use App\Enums\GalleryType;
use App\Models\Concerns\HasUniqueSlug;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** PRD §19 — a photo album (corporate, business unit, project or event). */
class Gallery extends Model
{
    use HasFactory, HasUniqueSlug, Publishable;

    protected $fillable = [
        'business_division_id', 'project_id', 'title', 'slug', 'description', 'type',
        'cover_image', 'event_date', 'sort_order', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => GalleryType::class,
            'event_date' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class)->orderBy('sort_order');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(BusinessDivision::class, 'business_division_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderByDesc('event_date')->orderByDesc('id');
    }

    /** Cover image, falling back to the first photo. */
    public function coverUrl(): ?string
    {
        return $this->cover_image ?: $this->images->first()?->image;
    }
}
