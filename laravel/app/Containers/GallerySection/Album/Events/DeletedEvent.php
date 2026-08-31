<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Events;

use App\Ship\Enums\EventTypesEnum;

class DeletedEvent extends GalleryAlbumEvent
{
    protected string $eventType = EventTypesEnum::DELETED->value;
}
