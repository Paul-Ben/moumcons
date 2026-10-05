<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Company contact details for public display (PRD §20).
 *
 * Values come from the Settings table (edited under Admin → Settings), falling
 * back to config/moaum.php. Several defaults are deliberately CLIENT_TO_PROVIDE
 * while the client supplies real data, and printing that marker on a public
 * page would be worse than showing nothing — so every read goes through here,
 * which returns null for placeholders and lets the view drop the row entirely.
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
        // Setting::get() falls back to config('moaum.company.*') for contact.* keys.
        $value = Setting::get('contact.'.$key) ?? config('moaum.company.'.$key);

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
     * Social links, with placeholder, "#" and empty entries removed.
     *
     * @return array<string, string> network => URL
     */
    public static function socialLinks(): array
    {
        $links = [];

        foreach (array_keys(SiteSettings::SOCIAL_NETWORKS) as $network) {
            $url = Setting::get('social.'.$network) ?? config('moaum.company.social.'.$network);

            if (is_string($url) && preg_match('#^https?://#i', trim($url)) && ! self::isPlaceholder($url)) {
                $links[$network] = trim($url);
            }
        }

        return $links;
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
