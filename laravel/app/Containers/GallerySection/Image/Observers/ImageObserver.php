<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Observers;

use App\Containers\GallerySection\Image\Events\CreatedEvent;
use App\Containers\GallerySection\Image\Events\DeletedEvent;
use App\Containers\GallerySection\Image\Events\UpdatedEvent;
use App\Containers\GallerySection\Image\Models\Image;
use Illuminate\Support\Facades\Event;

class ImageObserver
{
    public function created(Image $image): void
    {
        if ($image->album_id) {
            $image->albums()->syncWithoutDetaching([(int) $image->album_id]);
        }

        Event::dispatch(new CreatedEvent($image));
    }

    public function updated(Image $image): void
    {
        Event::dispatch(new UpdatedEvent($image));
    }

    public function deleted(Image $image): void
    {
        Event::dispatch(new DeletedEvent($image));
    }
}
