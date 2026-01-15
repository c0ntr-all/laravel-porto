<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Events;

use App\Ship\Enums\EventTypesEnum;

class UpdatedEvent extends GalleryVideoEvent
{
    protected string $eventType = EventTypesEnum::UPDATED->value;
}
