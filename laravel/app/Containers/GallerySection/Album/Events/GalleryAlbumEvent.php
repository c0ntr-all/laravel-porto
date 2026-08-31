<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Events;

use App\Containers\GallerySection\Album\Models\Album;
use Illuminate\Queue\SerializesModels;

abstract class GalleryAlbumEvent
{
    use SerializesModels;

    protected string $eventType = 'unknown';

    public function __construct(
        protected Album $album
    ) {
    }

    public function getAlbum(): Album
    {
        return $this->album;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }
}
