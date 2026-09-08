<?php declare(strict_types=1);

namespace App\Containers\AppSection\Document\Events;

use App\Ship\Enums\EventTypesEnum;

class DeletedEvent extends DocumentEvent
{
    protected string $eventType = EventTypesEnum::DELETED->value;
}
