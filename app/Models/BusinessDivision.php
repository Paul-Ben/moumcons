<?php

namespace App\Models;

use App\Enums\DivisionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BusinessDivision extends Model
{
    use HasFactory;

    protected $table = 'business_divisions';

    protected $fillable = [
        'name', 'slug', 'short_description', 'full_description', 'category',
        'status', 'featured', 'icon', 'hero_image', 'cover_image',
        'contact_email', 'contact_phone', 'location', 'operating_hours',
        'sort_order', 'seo_title', 'seo_description', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => DivisionStatus::class,
            'featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /* ------------------------------- Scopes ------------------------------ */

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', DivisionStatus::Active);
    }

    /** Visible on the public site: active or coming-soon (prototype shows both). */
    public function scopePubliclyVisible(Builder $q): Builder
    {
        return $q->whereIn('status', [DivisionStatus::Active, DivisionStatus::ComingSoon]);
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('featured', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('name');
    }

    /* ------------------------------ Relations ---------------------------- */

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function capabilities(): HasMany
    {
        return $this->hasMany(DivisionCapability::class, 'business_division_id')
            ->orderBy('sort_order');
    }

    /* ------------------------------- Helpers ----------------------------- */

    public function isAvailable(): bool
    {
        return $this->status === DivisionStatus::Active;
    }

    /** Slug generated from name on creation (unique-suffix guarded). */
    protected static function booted(): void
    {
        static::creating(function (self $division) {
            if (empty($division->slug)) {
                $base = Str::slug($division->name);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . ++$i;
                }
                $division->slug = $slug;
            }
        });
    }
}
