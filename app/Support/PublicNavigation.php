<?php

namespace App\Support;

use App\Models\BusinessDivision;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Divisions for the header mega menu and footer (PRD §35 server-side caching).
 * Rendered on every public page, so cached until a division changes —
 * BusinessDivision clears the key on save/delete.
 *
 * Plain arrays are cached, not models: Laravel's cache refuses to unserialise
 * arbitrary classes (config cache.serializable_classes), and a model would come
 * back as __PHP_Incomplete_Class.
 */
final class PublicNavigation
{
    public const CACHE_KEY = 'nav.public-divisions';

    /** @return Collection<int, BusinessDivision> */
    public static function divisions(): Collection
    {
        $rows = Cache::rememberForever(self::CACHE_KEY, fn () => BusinessDivision::query()
            ->publiclyVisible()
            ->ordered()
            ->get(['id', 'name', 'slug', 'category', 'status', 'sort_order'])
            ->map(fn (BusinessDivision $division) => $division->getAttributes())
            ->all());

        return BusinessDivision::hydrate($rows);
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
