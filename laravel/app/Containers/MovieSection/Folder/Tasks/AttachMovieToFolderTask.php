<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\Tasks;

use App\Containers\MovieSection\Folder\Data\Repositories\FolderRepository;
use App\Containers\MovieSection\Folder\Models\Folder;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Carbon;

class AttachMovieToFolderTask extends ParentTask
{
    public function __construct(
        private readonly FolderRepository $folderRepository,
    ) {
    }

    public function run(Folder $folder, Movie $movie, ?Carbon $addedAt = null): Folder
    {
        return $this->folderRepository->attachMovie($folder, $movie, $addedAt);
    }
}
