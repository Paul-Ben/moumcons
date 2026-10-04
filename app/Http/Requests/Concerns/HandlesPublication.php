<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * "Visible on site" checkbox plus an optional go-live date, mapped onto a
 * published_at column (PRD §14/§16). Ticked with no date publishes now (or
 * keeps the existing date); unticked unpublishes.
 */
trait HandlesPublication
{
    protected function publicationRules(): array
    {
        return [
            'publish' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    /** True when this submission touches publication at all. */
    protected function touchesPublication(): bool
    {
        return $this->hasAny(['publish', 'published_at']);
    }

    /**
     * @param  array<string, mixed>  $data  validated data; publish keys are consumed
     * @return array<string, mixed>
     */
    protected function applyPublication(array $data, ?Model $existing): array
    {
        $touched = array_key_exists('publish', $data);
        $publish = (bool) ($data['publish'] ?? false);
        $date = $data['published_at'] ?? null;
        unset($data['publish'], $data['published_at']);

        if (! $touched) {
            return $data;
        }

        $data['published_at'] = $publish
            ? ($date ? Carbon::parse($date) : ($existing?->published_at ?? now()))
            : null;

        return $data;
    }
}
