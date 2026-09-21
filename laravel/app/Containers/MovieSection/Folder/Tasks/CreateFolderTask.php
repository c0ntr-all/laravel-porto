<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\Tasks;

use App\Containers\MovieSection\Folder\Data\DTO\FolderCreateData;
use App\Containers\MovieSection\Folder\Data\Repositories\FolderRepository;
use App\Containers\MovieSection\Folder\Models\Folder;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateFolderTask extends ParentTask
{
    public function __construct(
        private readonly FolderRepository $folderRepository,
    ) {
    }

    public function run(FolderCreateData $dto): Folder
    {
        return $this->folderRepository->create($dto);
    }
}
