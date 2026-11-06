<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Handlers;

use App\Containers\LifelogSection\Post\Contracts\PostContentHandlerInterface;
use App\Containers\LifelogSection\Post\Data\DTO\PostContentAttachDto;
use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\Task\Tasks\FindTaskByIdForUserTask;
use App\Ship\Enums\ContainerAliasEnum;
use InvalidArgumentException;

class TaskPostContentHandler implements PostContentHandlerInterface
{
    public function __construct(
        private readonly FindTaskByIdForUserTask $findTaskByIdForUserTask,
    ) {
    }

    public function supports(PostContentTypeEnum $contentType): bool
    {
        return false;
    }

    public function attach(Post $post, PostContentAttachDto $dto): void
    {
        $task = $this->resolveTask($post, $dto);
        $post->attachTask($task);
    }

    public function subjectableType(): ContainerAliasEnum
    {
        return ContainerAliasEnum::TM_TASK;
    }

    public function eagerLoadRelations(): array
    {
        return ['tasks.taskList'];
    }

    private function resolveTask(Post $post, PostContentAttachDto $dto): Task
    {
        if ($dto->task_id !== null) {
            return $this->findTaskByIdForUserTask->run($dto->task_id, (int) $post->user_id);
        }

        $existing = $this->currentTask($post);

        if ($existing) {
            return $existing;
        }

        throw new InvalidArgumentException('Task post requires task_id.');
    }

    private function currentTask(Post $post): ?Task
    {
        return $post->relationLoaded('tasks')
            ? $post->tasks->first()
            : $post->tasks()->first();
    }
}
