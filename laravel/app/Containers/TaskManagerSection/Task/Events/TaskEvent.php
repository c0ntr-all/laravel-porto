<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Events;

use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Events\DomainActivityEvent;
use Illuminate\Database\Eloquent\Model;

abstract class TaskEvent extends DomainActivityEvent
{
    public function __construct(
        protected Task $task
    ) {
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function activityMainType(): string
    {
        return $this->task->getLoggableType();
    }

    public function activityMainId(): string
    {
        return (string) $this->task->id;
    }

    public function activityMetadata(): array
    {
        $metadata = $this->snapshot(['title', 'content', 'finished_at', 'is_declined']);

        if ($this->eventType === EventTypesEnum::UPDATED->value) {
            return ['changes' => $this->task->getChanges()];
        }

        return $metadata;
    }

    protected function activitySubject(): Model
    {
        return $this->task;
    }
}
