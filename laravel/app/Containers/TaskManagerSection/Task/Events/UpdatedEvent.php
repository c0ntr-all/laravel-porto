<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Events;

use App\Ship\Enums\EventTypesEnum;

class UpdatedEvent extends TaskEvent
{
    protected string $eventType = EventTypesEnum::UPDATED->value;
}
