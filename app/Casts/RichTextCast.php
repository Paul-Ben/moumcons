<?php

namespace App\Casts;

use App\Support\RichText;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Sanitises rich-text HTML on its way into the database (PRD §32). Reads are
 * untouched — the stored value is already clean.
 */
class RichTextCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return RichText::sanitize($value === null ? null : (string) $value);
    }
}
