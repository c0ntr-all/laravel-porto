<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Events;

use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Queue\SerializesModels;

abstract class TaskEvent
{
    use SerializesModels;

    protected string $eventType = 'unknown';

    public function __construct(
        protected Task $task
    )
    {
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }
}
