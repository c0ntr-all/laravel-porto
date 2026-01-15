<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Observers;

use App\Containers\GallerySection\Video\Events\CreatedEvent;
use App\Containers\GallerySection\Video\Events\DeletedEvent;
use App\Containers\GallerySection\Video\Events\UpdatedEvent;
use App\Containers\GallerySection\Video\Models\Video;
use Illuminate\Support\Facades\Event;

class VideoObserver
{
    public function created(Video $post): void
    {
        Event::dispatch(new CreatedEvent($post));
    }

    public function updated(Video $post): void
    {
        Event::dispatch(new UpdatedEvent($post));
    }

    public function deleted(Video $post): void
    {
        Event::dispatch(new DeletedEvent($post));
    }
}
