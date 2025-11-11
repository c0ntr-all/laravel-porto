<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Observers;

use App\Containers\GallerySection\Image\Events\CreatedEvent;
use App\Containers\GallerySection\Image\Events\DeletedEvent;
use App\Containers\GallerySection\Image\Events\UpdatedEvent;
use App\Containers\GallerySection\Image\Models\Image;
use Illuminate\Support\Facades\Event;

class ImageObserver
{
    public function created(Image $post): void
    {
        Event::dispatch(new CreatedEvent($post));
    }

    public function updated(Image $post): void
    {
        Event::dispatch(new UpdatedEvent($post));
    }

    public function deleted(Image $post): void
    {
        Event::dispatch(new DeletedEvent($post));
    }
}
