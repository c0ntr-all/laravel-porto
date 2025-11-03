<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Observers;

use App\Containers\LifelogSection\Post\Events\CreatedEvent;
use App\Containers\LifelogSection\Post\Events\DeletedEvent;
use App\Containers\LifelogSection\Post\Events\UpdatedEvent;
use App\Containers\LifelogSection\Post\Models\Post;
use Illuminate\Support\Facades\Event;

class PostObserver
{
    public function created(Post $post): void
    {
        Event::dispatch(new CreatedEvent($post));
    }

    public function updated(Post $post): void
    {
        Event::dispatch(new UpdatedEvent($post));
    }

    public function deleted(Post $post): void
    {
        Event::dispatch(new DeletedEvent($post));
    }
}
