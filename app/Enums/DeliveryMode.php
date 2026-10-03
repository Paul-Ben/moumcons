<?php

namespace App\Enums;

enum DeliveryMode: string
{
    use TraitHasStatusLabels;

    case Physical = 'physical';
    case Online = 'online';
    case Hybrid = 'hybrid';
}
