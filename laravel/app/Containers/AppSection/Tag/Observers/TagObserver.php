<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Observers;

use App\Containers\AppSection\Tag\Events\CreatedEvent;
use App\Containers\AppSection\Tag\Events\DeletedEvent;
use App\Containers\AppSection\Tag\Events\UpdatedEvent;
use App\Containers\AppSection\Tag\Models\Tag;
use Illuminate\Support\Facades\Event;

class TagObserver
{
    public function created(Tag $tag): void
    {
        Event::dispatch(new CreatedEvent($tag));
    }

    public function updated(Tag $tag): void
    {
        Event::dispatch(new UpdatedEvent($tag));
    }

    public function deleted(Tag $tag): void
    {
        Event::dispatch(new DeletedEvent($tag));
    }
}
