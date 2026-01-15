<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Events;

use App\Ship\Enums\EventTypesEnum;

class CreatedEvent extends GalleryVideoEvent
{
    protected string $eventType = EventTypesEnum::CREATED->value;
}
