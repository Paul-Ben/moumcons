<?php

namespace App\Models;

use App\Casts\RichTextCast;
use App\Enums\PageStatus;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** PRD §9/§22 — CMS page. System pages have a fixed `key` and route. */
class Page extends Model
{
    use HasFactory, HasUniqueSlug;

    /**
     * System pages: key => [default title, route name]. Their routes are
     * declared in routes/web.php; Database\Seeders\PageSeeder creates them.
     */
    public const SYSTEM = [
        'about' => ['Company Profile', 'about.profile'],
        'mission-vision' => ['Mission, Vision & Values', 'about.mission'],
        'leadership' => ['Leadership', 'about.leadership'],
        'university-relationship' => ['University Relationship', 'about.university'],
        'privacy-policy' => ['Privacy Policy', 'legal.privacy'],
        'terms' => ['Terms of Use', 'legal.terms'],
    ];

    /** The About section's sub-navigation, in order. */
    public const ABOUT_KEYS = ['about', 'mission-vision', 'leadership', 'university-relationship'];

    protected $fillable = [
        'key', 'title', 'slug', 'summary', 'content', 'hero_image', 'status',
        'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
            'content' => RichTextCast::class,
        ];
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', PageStatus::Published);
    }

    public function isSystem(): bool
    {
        return $this->key !== null;
    }

    public function isPublished(): bool
    {
        return $this->status === PageStatus::Published;
    }

    public function url(): string
    {
        return $this->isSystem() && isset(self::SYSTEM[$this->key])
            ? route(self::SYSTEM[$this->key][1])
            : route('pages.show', $this);
    }

    public static function forKey(string $key): ?self
    {
        return static::query()->where('key', $key)->first();
    }
}
