<?php

namespace App\Models;

use App\Casts\RichTextCast;
use App\Enums\NewsStatus;
use App\Models\Concerns\HasUniqueSlug;
use App\Support\RichText;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * PRD §16 — news article / announcement (table `news`, §27).
 *
 * Workflow: Draft → Review → Scheduled → Published → Archived. Scheduled
 * articles go live by date even before the scheduler flips their status
 * (news:publish-scheduled), so a missed cron run never delays publication.
 */
class NewsArticle extends Model
{
    use HasFactory, HasUniqueSlug;

    protected $table = 'news';

    protected $fillable = [
        'news_category_id', 'author_id', 'title', 'slug', 'excerpt', 'content',
        'featured_image', 'tags', 'status', 'featured',
        'seo_title', 'seo_description', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => NewsStatus::class,
            'content' => RichTextCast::class,
            'tags' => 'array',
            'featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'news_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** Visible on the public site right now. */
    public function scopePublished(Builder $q): Builder
    {
        return $q->whereIn('status', [NewsStatus::Published, NewsStatus::Scheduled])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeLatestFirst(Builder $q): Builder
    {
        return $q->orderByDesc('published_at')->orderByDesc('id');
    }

    public function isPublished(): bool
    {
        return in_array($this->status, [NewsStatus::Published, NewsStatus::Scheduled], true)
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    /** Excerpt for cards and meta descriptions, falling back to the body. */
    public function summary(int $limit = 180): string
    {
        return $this->excerpt ?: Str::limit(RichText::toPlainText($this->content), $limit);
    }

    /** Rough reading time for the article header. */
    public function readingMinutes(): int
    {
        return max(1, (int) ceil(str_word_count(RichText::toPlainText($this->content)) / 220));
    }
}
