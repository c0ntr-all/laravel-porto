<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Observers;

use App\Containers\AppSection\Tag\Events\AttachedEvent;
use App\Containers\AppSection\Tag\Events\DetachedEvent;
use App\Containers\AppSection\Tag\Models\Taggable;
use Illuminate\Support\Facades\Event;

class TaggableObserver
{
    public function created(Taggable $taggable): void
    {
        Event::dispatch(new AttachedEvent($taggable));
    }

    public function deleted(Taggable $taggable): void
    {
        Event::dispatch(new DetachedEvent($taggable));
    }
}
