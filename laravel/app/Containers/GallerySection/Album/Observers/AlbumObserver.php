<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Observers;

use App\Containers\GallerySection\Album\Events\CreatedEvent;
use App\Containers\GallerySection\Album\Events\DeletedEvent;
use App\Containers\GallerySection\Album\Events\UpdatedEvent;
use App\Containers\GallerySection\Album\Models\Album;
use Illuminate\Support\Facades\Event;

class AlbumObserver
{
    public function created(Album $album): void
    {
        Event::dispatch(new CreatedEvent($album));
    }

    public function updated(Album $album): void
    {
        Event::dispatch(new UpdatedEvent($album));
    }

    public function deleted(Album $album): void
    {
        Event::dispatch(new DeletedEvent($album));
    }
}
