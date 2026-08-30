<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\Tasks;

use App\Containers\TaskManagerSection\TaskList\Data\Repositories\TaskListRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListTaskListsTask extends ParentTask
{
    public function __construct(
        private readonly TaskListRepository $taskListRepository
    ) {
    }

    /**
     * @param list<string> $with
     */
    public function run(array $with = []): Collection
    {
        return $this->taskListRepository->list($with);
    }
}
