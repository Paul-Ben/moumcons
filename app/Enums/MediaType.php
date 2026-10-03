<?php

namespace App\Enums;

enum MediaType: string
{
    use TraitHasStatusLabels;

    case Image = 'image';
    case Video = 'video';
    case Document = 'document';
}
