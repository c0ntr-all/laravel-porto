<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Events;

use App\Ship\Enums\EventTypesEnum;

class CreatedEvent extends TaskEvent
{
    protected string $eventType = EventTypesEnum::CREATED->value;
}
