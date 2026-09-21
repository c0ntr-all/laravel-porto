<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\Tasks;

use App\Containers\MovieSection\Folder\Data\Repositories\FolderRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListFoldersTask extends ParentTask
{
    public function __construct(
        private readonly EnsureSystemMovieFoldersTask $ensureSystemMovieFoldersTask,
        private readonly FolderRepository $folderRepository,
    ) {
    }

    public function run(int $userId): Collection
    {
        $this->ensureSystemMovieFoldersTask->run($userId);

        return $this->folderRepository->listForUser($userId);
    }
}
