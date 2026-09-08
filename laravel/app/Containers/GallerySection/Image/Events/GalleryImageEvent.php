<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Events;

use App\Containers\GallerySection\Image\Models\Image;
use App\Ship\Events\DomainActivityEvent;
use Illuminate\Database\Eloquent\Model;

abstract class GalleryImageEvent extends DomainActivityEvent
{
    public function __construct(
        protected Image $image
    ) {
    }

    public function getImage(): Image
    {
        return $this->image;
    }

    public function activityMainType(): string
    {
        return $this->image->getLoggableType();
    }

    public function activityMainId(): string
    {
        return (string) $this->image->id;
    }

    public function activityMetadata(): array
    {
        return $this->snapshot(['source', 'album_id', 'extension']);
    }

    protected function activitySubject(): Model
    {
        return $this->image;
    }
}
