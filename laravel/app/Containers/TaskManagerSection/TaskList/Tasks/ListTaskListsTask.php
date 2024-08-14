<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\Tasks;

use App\Containers\TaskManagerSection\TaskList\Data\Repositories\TaskListRepository;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Database\Eloquent\Collection;

class ListTaskListsTask extends Task
{
    public function __construct(
        private readonly TaskListRepository $listRepository
    )
    {
    }

    public function run(): Collection
    {
        return $this->listRepository->getTaskLists();
    }
}
