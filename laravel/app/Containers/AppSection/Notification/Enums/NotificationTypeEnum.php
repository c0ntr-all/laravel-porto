<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Enums;

enum NotificationTypeEnum: string
{
    case SYSTEM = 'system';
    case REMINDER_DUE = 'reminder.due';
}
