<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Events;

use App\Ship\Enums\EventTypesEnum;

class DetachedEvent extends TaggableEvent
{
    protected string $eventType = EventTypesEnum::DETACHED->value;
}
