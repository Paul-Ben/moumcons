<?php

namespace App\Support;

/**
 * Registry of editable site settings (PRD §20/§22 "Site settings").
 *
 * The admin settings screen is generated from this list, and the keys match
 * those App\Models\Setting::get() reads across the site. Values not yet saved
 * fall back to config('moaum') via Setting::get().
 */
final class SiteSettings
{
    /**
     * @return array<string, array{label: string, description: string, fields: array<string, array<string, mixed>>}>
     */
    public static function groups(): array
    {
        return [
            'contact' => [
                'label' => 'Contact details',
                'description' => 'Shown in the footer, on the contact page and as the fallback on division pages.',
                'fields' => [
                    'contact.email' => ['label' => 'Email', 'type' => 'email', 'rules' => ['nullable', 'email', 'max:190']],
                    'contact.phone' => ['label' => 'Phone', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:60']],
                    'contact.address' => ['label' => 'Address', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:500']],
                    'contact.hours' => ['label' => 'Business hours', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:190']],
                    'contact.map_embed_url' => [
                        'label' => 'Google Maps embed URL', 'type' => 'url',
                        'hint' => 'In Google Maps: Share → Embed a map → copy the src="…" address.',
                        'rules' => ['nullable', 'url:https', 'max:2000', 'starts_with:https://www.google.com/maps/embed'],
                    ],
                ],
            ],
            'social' => [
                'label' => 'Social media',
                'description' => 'Leave blank to hide a network. Full profile URLs.',
                'fields' => collect(self::SOCIAL_NETWORKS)->mapWithKeys(fn ($label, $network) => [
                    "social.{$network}" => ['label' => $label, 'type' => 'url', 'rules' => ['nullable', 'url:https,http', 'max:500']],
                ])->all(),
            ],
            'homepage' => [
                'label' => 'Home page',
                'description' => 'Copy for the home page hero, portfolio and call to action.',
                'fields' => [
                    'home.hero.badge' => ['label' => 'Hero badge', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:190']],
                    'home.hero.description' => ['label' => 'Hero introduction', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:600']],
                    'home.stats' => [
                        'label' => 'Trust banner statistics', 'type' => 'stats',
                        'hint' => 'Only publish figures MOAUM has confirmed.',
                        'rules' => ['nullable', 'array', 'max:4'],
                    ],
                    'home.portfolio.eyebrow' => ['label' => 'Portfolio eyebrow', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:120']],
                    'home.portfolio.title' => ['label' => 'Portfolio heading', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:190']],
                    'home.portfolio.description' => ['label' => 'Portfolio introduction', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:400']],
                    'home.cta.title' => ['label' => 'Call to action heading', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:190']],
                    'home.cta.description' => ['label' => 'Call to action text', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:400']],
                ],
            ],
            'seo' => [
                'label' => 'Search & sharing',
                'description' => 'Defaults used when a page has no SEO fields of its own.',
                'fields' => [
                    'seo.default_description' => ['label' => 'Default meta description', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:300']],
                    'seo.default_image' => ['label' => 'Default sharing image', 'type' => 'image', 'hint' => '1200×630 works best for social previews.', 'rules' => ['nullable', 'string', 'max:255', 'regex:#^(/(?!/)|https://)#']],
                ],
            ],
        ];
    }

    public const SOCIAL_NETWORKS = [
        'facebook' => 'Facebook',
        'twitter' => 'X (Twitter)',
        'linkedin' => 'LinkedIn',
        'instagram' => 'Instagram',
        'youtube' => 'YouTube',
    ];

    /** Colours offered for a trust-banner statistic. */
    public const STAT_COLOURS = ['red' => 'Red', 'blue' => 'Blue', 'green' => 'Green'];

    /** @return array<string, array<string, mixed>> key => field definition */
    public static function fields(): array
    {
        return collect(self::groups())->flatMap(fn ($group) => $group['fields'])->all();
    }
}
