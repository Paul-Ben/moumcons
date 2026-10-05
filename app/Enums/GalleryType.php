<?php

namespace App\Enums;

/** PRD §19 — the four gallery kinds. */
enum GalleryType: string
{
    use TraitHasStatusLabels;

    case Corporate = 'corporate';
    case Division = 'division';
    case Project = 'project';
    case Event = 'event';

    public function label(): string
    {
        return match ($this) {
            self::Corporate => 'Corporate',
            self::Division => 'Business unit',
            self::Project => 'Project',
            self::Event => 'Training & events',
        };
    }
}
