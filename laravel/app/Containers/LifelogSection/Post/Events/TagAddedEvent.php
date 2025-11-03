<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Events;

use App\Ship\Enums\EventTypesEnum;

class TagAddedEvent extends PostEvent
{
    protected string $eventType = EventTypesEnum::TAG_ADDED->value;
}
