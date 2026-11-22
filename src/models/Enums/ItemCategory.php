<?php

declare(strict_types=1);

namespace App\Model\Enums;

enum ItemCategory: string
{
    case AVATAR = 'AVATAR';
    case BADGE = 'BADGE';
    case THEME = 'THEME';
    case BOOSTER = 'BOOSTER';
}
