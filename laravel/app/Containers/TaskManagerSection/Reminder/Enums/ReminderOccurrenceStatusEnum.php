<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum ReminderOccurrenceStatusEnum: string
{
    use Arrayable;

    case NOTIFIED = 'notified';
    case COMPLETED = 'completed';
}
