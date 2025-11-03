<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Events;

use App\Ship\Enums\EventTypesEnum;

class CreatedEvent extends PostEvent
{
    protected string $eventType = EventTypesEnum::CREATED->value;
}
