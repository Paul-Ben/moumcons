<?php

namespace App\Enums;

/** PRD §18 — file access levels for downloads. */
enum AccessLevel: string
{
    use TraitHasStatusLabels;

    case Public = 'public';
    case Registered = 'registered';
    case Internal = 'internal';
}
