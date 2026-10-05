<?php

namespace App\Models;

use App\Casts\RichTextCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** PRD §10/§22 — a frequently asked question, general or for one division. */
class Faq extends Model
{
    use HasFactory;

    protected $fillable = ['business_division_id', 'question', 'answer', 'category', 'sort_order', 'is_published'];

    protected function casts(): array
    {
        return [
            'answer' => RichTextCast::class,
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(BusinessDivision::class, 'business_division_id');
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('id');
    }
}
