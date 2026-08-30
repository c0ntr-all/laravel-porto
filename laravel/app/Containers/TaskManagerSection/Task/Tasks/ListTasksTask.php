<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Tasks;

use App\Containers\TaskManagerSection\Task\Data\Repositories\TaskRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListTasksTask extends ParentTask
{
    public function __construct(
        private readonly TaskRepository $taskRepository
    ) {
    }

    /**
     * @param array{task_list_id?: int|null, unlisted?: bool} $filters
     * @param list<string> $with
     */
    public function run(array $filters = [], array $with = []): Collection
    {
        return $this->taskRepository->list($filters, $with);
    }
}
