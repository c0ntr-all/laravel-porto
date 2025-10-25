<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Enums;

enum ActivityLogActionTypeCategory: string
{
    case USER = 'user';
    case SYSTEM = 'system';
}
