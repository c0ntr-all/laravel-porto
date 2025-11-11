<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Events;

use App\Ship\Enums\EventTypesEnum;

class CreatedEvent extends GalleryImageEvent
{
    protected string $eventType = EventTypesEnum::CREATED->value;
}
