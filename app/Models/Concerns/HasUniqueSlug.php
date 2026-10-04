<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * PRD §34 "Every public content type should have a clean slug".
 *
 * Fills `slug` from the model's title/name on create when it is left blank,
 * suffixing -2, -3… to stay unique. An explicit slug typed by an editor is
 * kept as-is (slugified) — uniqueness of those is enforced by validation.
 */
trait HasUniqueSlug
{
    /** Attribute the slug is derived from. */
    protected function slugSource(): string
    {
        return 'title';
    }

    protected static function bootHasUniqueSlug(): void
    {
        static::saving(function (self $model): void {
            if (filled($model->slug)) {
                $model->slug = Str::slug($model->slug);

                return;
            }

            $model->slug = $model->uniqueSlugFrom((string) $model->getAttribute($model->slugSource()));
        });
    }

    public function uniqueSlugFrom(string $value): string
    {
        $base = Str::slug($value) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 1;

        while (static::query()->where('slug', $slug)->when($this->exists, fn ($q) => $q->whereKeyNot($this->getKey()))->exists()) {
            $slug = $base.'-'.++$i;
        }

        return $slug;
    }
}
