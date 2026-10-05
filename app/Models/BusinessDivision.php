<?php

namespace App\Models;

use App\Casts\RichTextCast;
use App\Enums\DivisionStatus;
use App\Models\Concerns\HasUniqueSlug;
use App\Support\PublicNavigation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessDivision extends Model
{
    use HasFactory, HasUniqueSlug;

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
            'full_description' => RichTextCast::class,
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

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'business_division_id');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class, 'business_division_id');
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class, 'business_division_id');
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'business_division_id');
    }

    public function quoteRequests(): HasMany
    {
        return $this->hasMany(QuoteRequest::class, 'business_division_id');
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class, 'business_division_id');
    }

    public function capabilities(): HasMany
    {
        return $this->hasMany(DivisionCapability::class, 'business_division_id')
            ->orderBy('sort_order');
    }

    /* ------------------------------- Helpers ----------------------------- */

    protected static function booted(): void
    {
        // The header/footer division list is cached (App\Support\PublicNavigation).
        static::saved(fn () => PublicNavigation::forget());
        static::deleted(fn () => PublicNavigation::forget());
    }

    public function isAvailable(): bool
    {
        return $this->status === DivisionStatus::Active;
    }

    protected function slugSource(): string
    {
        return 'name';
    }
}
