<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Events;

use App\Ship\Enums\EventTypesEnum;

class CreatedEvent extends PresetEvent
{
    protected string $eventType = EventTypesEnum::CREATED->value;
}
