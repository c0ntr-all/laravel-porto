<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Events;

use App\Ship\Enums\EventTypesEnum;

class DeletedEvent extends PostEvent
{
    protected string $eventType = EventTypesEnum::DELETED->value;
}
