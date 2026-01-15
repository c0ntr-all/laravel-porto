<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Events;

use App\Containers\GallerySection\Video\Models\Video;
use Illuminate\Queue\SerializesModels;

abstract class GalleryVideoEvent
{
    use SerializesModels;

    protected string $eventType = 'unknown';

    public function __construct(
        protected Video $video
    )
    {
    }

    public function getVideo(): Video
    {
        return $this->video;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }
}
