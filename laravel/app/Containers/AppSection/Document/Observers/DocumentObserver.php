<?php declare(strict_types=1);

namespace App\Containers\AppSection\Document\Observers;

use App\Containers\AppSection\Document\Events\CreatedEvent;
use App\Containers\AppSection\Document\Events\DeletedEvent;
use App\Containers\AppSection\Document\Events\UpdatedEvent;
use App\Containers\AppSection\Document\Models\Document;
use Illuminate\Support\Facades\Event;

class DocumentObserver
{
    public function created(Document $document): void
    {
        Event::dispatch(new CreatedEvent($document));
    }

    public function updated(Document $document): void
    {
        Event::dispatch(new UpdatedEvent($document));
    }

    public function deleted(Document $document): void
    {
        Event::dispatch(new DeletedEvent($document));
    }
}
