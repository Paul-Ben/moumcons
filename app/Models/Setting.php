<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Site settings (Module 2 table). Module 10 adds the admin editor;
 * Module 4+ reads values here with a graceful fallback to config('moaum').
 */
class Setting extends Model
{
    protected $fillable = ['key', 'group', 'type', 'value'];

    protected static function booted(): void
    {
        // Invalidate the memo cache whenever settings change.
        $invalidate = fn () => Cache::forget('moaum.settings.all');
        static::saved($invalidate);
        static::deleted($invalidate);
    }

    /** All settings as key => value, cached. */
    public static function all_cached(): array
    {
        return Cache::rememberForever('moaum.settings.all', fn () => static::query()
            ->get()
            ->mapWithKeys(fn (self $s) => [$s->key => $s->value])
            ->all());
    }

    /**
     * Resolve a setting by key. Falls back to the matching config('moaum.*')
     * entry so the site works even before settings are seeded.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::all_cached()[$key] ?? null;

        if ($value !== null && $value !== '') {
            return match ($type = static::typeFor($key)) {
                'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                'json' => json_decode($value, true) ?? $default,
                default => $value,
            };
        }

        // Fallback map: contact.phone => config('moaum.company.phone'), etc.
        if (str_starts_with($key, 'contact.')) {
            return config('moaum.company.' . substr($key, strlen('contact.')), $default);
        }

        return $default;
    }

    /** Cached lookup of a setting's declared type (text|textarea|image|bool|json). */
    protected static function typeFor(string $key): string
    {
        $types = Cache::rememberForever('moaum.settings.types', fn () => static::query()
            ->pluck('type', 'key')->all());

        return $types[$key] ?? 'text';
    }
}
