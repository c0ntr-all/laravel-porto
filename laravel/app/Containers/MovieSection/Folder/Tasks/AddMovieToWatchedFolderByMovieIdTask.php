<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\Tasks;

use App\Containers\MovieSection\Folder\Enums\SystemMovieFolderEnum;
use App\Containers\MovieSection\Folder\Models\Folder;
use App\Containers\MovieSection\Movie\Tasks\FindMovieByIdTask;
use App\Ship\Parents\Tasks\Task as ParentTask;

class AddMovieToWatchedFolderByMovieIdTask extends ParentTask
{
    public function __construct(
        private readonly EnsureSystemMovieFoldersTask $ensureSystemMovieFoldersTask,
        private readonly FindMovieByIdTask $findMovieByIdTask,
        private readonly AttachMovieToFolderTask $attachMovieToFolderTask,
    ) {
    }

    public function run(int $userId, int $movieId): void
    {
        $this->ensureSystemMovieFoldersTask->run($userId);

        $folder = Folder::query()
            ->where('user_id', $userId)
            ->where('slug', SystemMovieFolderEnum::WATCHED->value)
            ->firstOrFail();

        $movie = $this->findMovieByIdTask->run($movieId);

        $this->attachMovieToFolderTask->run($folder, $movie);
    }
}
