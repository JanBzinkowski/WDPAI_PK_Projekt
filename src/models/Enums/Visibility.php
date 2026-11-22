<?php

declare(strict_types=1);

namespace App\Model\Enums;

enum Visibility: string
{
    case PRIVATE = 'PRIVATE';
    case PUBLIC = 'PUBLIC';
}
