<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Period\Observers;

use App\Containers\LifelogSection\Period\Events\CreatedEvent;
use App\Containers\LifelogSection\Period\Events\DeletedEvent;
use App\Containers\LifelogSection\Period\Events\UpdatedEvent;
use App\Containers\LifelogSection\Period\Models\Period;
use Illuminate\Support\Facades\Event;

class PeriodObserver
{
    public function created(Period $period): void
    {
        Event::dispatch(new CreatedEvent($period));
    }

    public function updated(Period $period): void
    {
        Event::dispatch(new UpdatedEvent($period));
    }

    public function deleted(Period $period): void
    {
        Event::dispatch(new DeletedEvent($period));
    }
}
