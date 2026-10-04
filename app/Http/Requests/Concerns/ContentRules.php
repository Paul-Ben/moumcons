<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;

/** Validation shared by the CMS form requests. */
trait ContentRules
{
    /** A slug unique within $table, ignoring the record being edited. */
    protected function slugRules(string $table, ?int $ignoreId = null): array
    {
        return ['nullable', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique($table, 'slug')->ignore($ignoreId)];
    }

    /**
     * Image fields hold a site-relative path from the media library
     * ("/storage/…", "/images/…") or an https URL — never javascript: or data:.
     */
    protected function imageRules(): array
    {
        return ['nullable', 'string', 'max:255', 'regex:#^(/(?!/)|https://)#'];
    }

    protected function seoRules(): array
    {
        return [
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:300'],
        ];
    }

    protected function contentMessages(): array
    {
        return [
            'slug.alpha_dash' => 'The slug may only contain letters, numbers, dashes and underscores.',
            '*.regex' => 'Choose an image from the media library.',
        ];
    }
}
