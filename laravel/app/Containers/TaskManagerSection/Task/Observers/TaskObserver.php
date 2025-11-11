<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Observers;

use App\Containers\TaskManagerSection\Task\Events\CreatedEvent;
use App\Containers\TaskManagerSection\Task\Events\DeletedEvent;
use App\Containers\TaskManagerSection\Task\Events\UpdatedEvent;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Support\Facades\Event;

class TaskObserver
{
    public function created(Task $task): void
    {
        Event::dispatch(new CreatedEvent($task));
    }

    public function updated(Task $task): void
    {
        Event::dispatch(new UpdatedEvent($task));
    }

    public function deleted(Task $task): void
    {
        Event::dispatch(new DeletedEvent($task));
    }
}
