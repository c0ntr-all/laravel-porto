<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Events;

use App\Containers\GallerySection\Album\Models\Album;
use App\Ship\Events\DomainActivityEvent;
use Illuminate\Database\Eloquent\Model;

abstract class GalleryAlbumEvent extends DomainActivityEvent
{
    public function __construct(
        protected Album $album
    ) {
    }

    public function getAlbum(): Album
    {
        return $this->album;
    }

    public function activityMainType(): string
    {
        return $this->album->getLoggableType();
    }

    public function activityMainId(): string
    {
        return (string) $this->album->id;
    }

    public function activityMetadata(): array
    {
        return $this->snapshot(['name', 'description']);
    }

    protected function activitySubject(): Model
    {
        return $this->album;
    }
}
