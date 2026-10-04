<?php

namespace App\Models;

use App\Casts\RichTextCast;
use App\Enums\PricingType;
use App\Enums\ServiceStatus;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory, HasUniqueSlug;

    protected $fillable = [
        'business_division_id', 'service_category_id', 'name', 'slug',
        'short_description', 'description', 'service_type', 'pricing_type',
        'starting_price', 'featured', 'status', 'image', 'sort_order',
        'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'pricing_type' => PricingType::class,
            'status' => ServiceStatus::class,
            'description' => RichTextCast::class,
            'featured' => 'boolean',
            'starting_price' => 'decimal:2',
        ];
    }

    /* ------------------------------- Scopes ------------------------------ */

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', ServiceStatus::Active);
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

    public function division(): BelongsTo
    {
        return $this->belongsTo(BusinessDivision::class, 'business_division_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function quoteRequests(): HasMany
    {
        return $this->hasMany(QuoteRequest::class);
    }

    /* ------------------------------- Helpers ----------------------------- */

    /** Prices are only displayed when the pricing type allows it (PRD §8). */
    public function displaysPrice(): bool
    {
        return $this->pricing_type === PricingType::Fixed
            && $this->starting_price !== null;
    }

    protected function slugSource(): string
    {
        return 'name';
    }
}
