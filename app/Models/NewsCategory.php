<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** PRD §16/§27 — news category (announcements, events, publications…). */
class NewsCategory extends Model
{
    use HasFactory, HasUniqueSlug;

    protected $fillable = ['name', 'slug', 'description', 'sort_order'];

    public function articles(): HasMany
    {
        return $this->hasMany(NewsArticle::class, 'news_category_id');
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('name');
    }

    protected function slugSource(): string
    {
        return 'name';
    }
}
