<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Events;

use App\Ship\Enums\EventTypesEnum;

class TagRemovedEvent extends PostEvent
{
    protected string $eventType = EventTypesEnum::TAG_REMOVED->value;
}
