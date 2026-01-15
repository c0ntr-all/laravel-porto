<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Events;

use App\Ship\Enums\EventTypesEnum;

class DeletedEvent extends GalleryVideoEvent
{
    protected string $eventType = EventTypesEnum::DELETED->value;
}
