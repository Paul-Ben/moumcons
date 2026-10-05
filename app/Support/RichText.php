<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * PRD §32 XSS protection for CMS rich text (Trix output).
 *
 * Content is sanitised on write (App\Casts\RichTextCast), so what public
 * pages render with {!! !!} is already safe even if an editor account is
 * compromised. Only the formatting Trix can produce survives; scripts, event
 * handlers, styles and javascript: URLs are removed.
 */
final class RichText
{
    private static ?HtmlSanitizer $sanitizer = null;

    public static function sanitize(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        return self::sanitizer()->sanitize($html);
    }

    /**
     * HTML for public pages. Content saved before the editor existed is plain
     * text with blank-line paragraphs, so that is escaped and wrapped; HTML is
     * sanitised again on the way out in case it reached the database by a
     * path that bypassed the cast.
     */
    public static function render(?string $value): string
    {
        if (blank($value)) {
            return '';
        }

        if ($value === strip_tags($value)) {
            return collect(preg_split('/\R{2,}/', trim($value)))
                // Decode first: the write-side sanitiser has already encoded "&".
                ->map(fn (string $p) => '<p>'.nl2br(e(html_entity_decode(trim($p), ENT_QUOTES | ENT_HTML5))).'</p>')
                ->implode('');
        }

        return (string) self::sanitize($value);
    }

    /**
     * Drop blocks still holding a CLIENT_TO_PROVIDE marker — and the heading
     * directly above one — so unconfirmed copy never reaches visitors. The
     * admin flags these pages instead.
     */
    public static function withoutPlaceholders(?string $html): ?string
    {
        if ($html === null || ! str_contains($html, 'CLIENT_TO_PROVIDE')) {
            return $html;
        }

        return preg_replace(
            '#(<h[1-3][^>]*>[^<]*</h[1-3]>\s*)?<(div|p|li)[^>]*>[^<]*CLIENT_TO_PROVIDE.*?</\2>#s',
            '',
            $html
        );
    }

    /** Plain-text version, e.g. for meta descriptions and excerpts. */
    public static function toPlainText(?string $html): string
    {
        $text = html_entity_decode(strip_tags(str_replace(['<br>', '</div>', '</p>', '</li>'], ' ', (string) $html)), ENT_QUOTES | ENT_HTML5);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    private static function sanitizer(): HtmlSanitizer
    {
        return self::$sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowElement('div')
                ->allowElement('p')
                ->allowElement('br')
                ->allowElement('strong')
                ->allowElement('b')
                ->allowElement('em')
                ->allowElement('i')
                ->allowElement('del')
                ->allowElement('h1')
                ->allowElement('h2')
                ->allowElement('h3')
                ->allowElement('blockquote')
                ->allowElement('pre')
                ->allowElement('ul')
                ->allowElement('ol')
                ->allowElement('li')
                ->allowElement('figure')
                ->allowElement('figcaption')
                ->allowElement('a', ['href', 'title'])
                ->allowElement('img', ['src', 'alt', 'width', 'height'])
                ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
                ->allowMediaSchemes(['https', 'http'])
                ->allowRelativeLinks()
                ->allowRelativeMedias()
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
        );
    }
}
