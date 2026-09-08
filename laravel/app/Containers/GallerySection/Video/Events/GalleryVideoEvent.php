<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Events;

use App\Containers\GallerySection\Video\Models\Video;
use App\Ship\Events\DomainActivityEvent;
use Illuminate\Database\Eloquent\Model;

abstract class GalleryVideoEvent extends DomainActivityEvent
{
    public function __construct(
        protected Video $video
    ) {
    }

    public function getVideo(): Video
    {
        return $this->video;
    }

    public function activityMainType(): string
    {
        return $this->video->getLoggableType();
    }

    public function activityMainId(): string
    {
        return (string) $this->video->id;
    }

    public function activityMetadata(): array
    {
        return $this->snapshot(['source', 'album_id', 'extension', 'original_name']);
    }

    protected function activitySubject(): Model
    {
        return $this->video;
    }
}
