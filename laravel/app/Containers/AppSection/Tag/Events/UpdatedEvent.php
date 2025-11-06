<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Events;

use App\Ship\Enums\EventTypesEnum;

class UpdatedEvent extends TagEvent
{
    protected string $eventType = EventTypesEnum::UPDATED->value;
}
