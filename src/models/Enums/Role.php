<?php

declare(strict_types=1);

namespace App\Model\Enums;

enum Role: string
{
    case USER = 'USER';
    case ADMIN = 'ADMIN';
}
