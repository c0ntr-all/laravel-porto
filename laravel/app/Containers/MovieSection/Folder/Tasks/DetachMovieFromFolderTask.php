<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\Tasks;

use App\Containers\MovieSection\Folder\Data\Repositories\FolderRepository;
use App\Containers\MovieSection\Folder\Models\Folder;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DetachMovieFromFolderTask extends ParentTask
{
    public function __construct(
        private readonly FolderRepository $folderRepository,
    ) {
    }

    public function run(Folder $folder, Movie $movie): Folder
    {
        return $this->folderRepository->detachMovie($folder, $movie);
    }
}
