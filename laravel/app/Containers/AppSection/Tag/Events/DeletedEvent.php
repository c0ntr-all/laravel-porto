<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Events;

use App\Ship\Enums\EventTypesEnum;

class DeletedEvent extends TagEvent
{
    protected string $eventType = EventTypesEnum::DELETED->value;
}
