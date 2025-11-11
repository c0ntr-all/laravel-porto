<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Events;

use App\Ship\Enums\EventTypesEnum;

class DeletedEvent extends TaskEvent
{
    protected string $eventType = EventTypesEnum::DELETED->value;
}
