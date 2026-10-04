<?php

namespace App\Support;

/**
 * Company contact details for public display (PRD §20).
 *
 * config/moaum.php holds these values until the Settings table takes over in a
 * later module. Several are deliberately seeded as CLIENT_TO_PROVIDE while the
 * client supplies real data, and printing that marker on a public page would be
 * worse than showing nothing — so every read goes through here, which returns
 * null for placeholders and lets the view drop the row entirely.
 */
final class CompanyDetails
{
    /**
     * Fragments that mark a value as "not supplied yet". Matched as substrings,
     * so 'CLIENT_TO_PROVIDE (head office)' is filtered too.
     *
     * @var list<string>
     */
    private const PLACEHOLDERS = [
        'CLIENT_TO_PROVIDE',
        'TO_BE_PROVIDED',
        'TO BE PROVIDED',
        'TBC',
        'TBD',
        'COMING SOON',
        'N/A',
    ];

    /**
     * @return string|null null when the value is missing or still a placeholder
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $value = config('moaum.company.'.$key);

        if (! is_string($value)) {
            return $default;
        }

        $value = trim($value);

        if ($value === '' || self::isPlaceholder($value)) {
            return $default;
        }

        return $value;
    }

    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    /**
     * Social links, with placeholder and empty entries removed.
     *
     * @return array<string, string>
     */
    public static function socialLinks(): array
    {
        $social = config('moaum.company.social', []);

        return is_array($social)
            ? array_filter($social, fn ($url) => is_string($url) && ! self::isPlaceholder(trim($url)))
            : [];
    }

    private static function isPlaceholder(string $value): bool
    {
        foreach (self::PLACEHOLDERS as $marker) {
            if (str_contains(strtoupper($value), $marker)) {
                return true;
            }
        }

        return false;
    }
}
