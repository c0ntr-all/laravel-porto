<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Observers;

use App\Containers\LifelogSection\Preset\Events\CreatedEvent;
use App\Containers\LifelogSection\Preset\Events\DeletedEvent;
use App\Containers\LifelogSection\Preset\Events\UpdatedEvent;
use App\Containers\LifelogSection\Preset\Models\Preset;
use Illuminate\Support\Facades\Event;

class PresetObserver
{
    public function created(Preset $preset): void
    {
        Event::dispatch(new CreatedEvent($preset));
    }

    public function updated(Preset $preset): void
    {
        Event::dispatch(new UpdatedEvent($preset));
    }

    public function deleted(Preset $preset): void
    {
        Event::dispatch(new DeletedEvent($preset));
    }
}
