<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Events;

use App\Containers\GallerySection\Image\Models\Image;
use Illuminate\Queue\SerializesModels;

abstract class GalleryImageEvent
{
    use SerializesModels;

    protected string $eventType = 'unknown';

    public function __construct(
        protected Image $image
    )
    {
    }

    public function getImage(): Image
    {
        return $this->image;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }
}
