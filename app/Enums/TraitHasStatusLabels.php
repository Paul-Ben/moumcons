<?php

namespace App\Enums;

/**
 * Provides consistent human labels and badge styling for string-backed
 * status/priority/type enums used across MOAUM resources.
 */
trait TraitHasStatusLabels
{
    /** @return array<string, string> value => label */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    /** Tailwind classes for a badge pill (Design System §25 badges). */
    public function badgeClasses(): string
    {
        return match ($this->value) {
            'active', 'published', 'completed', 'accepted', 'approved' => 'bg-emerald-50 text-emerald-700',
            'draft', 'planned', 'new', 'pending', 'review' => 'bg-amber-50 text-amber-700',
            'in_progress', 'ongoing', 'assigned', 'processing' => 'bg-sky-50 text-sky-700',
            'archived', 'suspended', 'closed', 'cancelled', 'declined' => 'bg-slate-100 text-slate-600',
            'urgent' => 'bg-red-50 text-red-700',
            default => 'bg-slate-100 text-slate-600',
        };
    }
}
