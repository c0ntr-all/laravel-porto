<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum NotificationChannelEnum: string
{
    use Arrayable;

    case EMAIL = 'email';
    // Future: TELEGRAM = 'telegram', PUSH = 'push', DATABASE = 'database';
}
