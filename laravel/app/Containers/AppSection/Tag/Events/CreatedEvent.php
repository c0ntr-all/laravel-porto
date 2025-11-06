<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Events;

use App\Ship\Enums\EventTypesEnum;

class CreatedEvent extends TagEvent
{
    protected string $eventType = EventTypesEnum::CREATED->value;
}
