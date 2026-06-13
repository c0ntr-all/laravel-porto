<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Period\Events;

use App\Ship\Enums\EventTypesEnum;

class DeletedEvent extends PeriodEvent
{
    protected string $eventType = EventTypesEnum::DELETED->value;
}
