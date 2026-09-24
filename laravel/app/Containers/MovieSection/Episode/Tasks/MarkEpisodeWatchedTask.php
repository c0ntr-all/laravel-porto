<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\Tasks;

use App\Containers\MovieSection\Episode\Models\Episode;
use App\Containers\MovieSection\Episode\Models\EpisodeWatch;
use App\Containers\MovieSection\Folder\Tasks\AddMovieToWatchedFolderByMovieIdTask;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Carbon;

class MarkEpisodeWatchedTask extends ParentTask
{
    public function __construct(
        private readonly AddMovieToWatchedFolderByMovieIdTask $addMovieToWatchedFolderByMovieIdTask,
    ) {
    }

    public function run(Episode $episode, int $userId, ?Carbon $watchedAt = null): EpisodeWatch
    {
        $watch = EpisodeWatch::query()->firstOrNew([
            'user_id' => $userId,
            'episode_id' => $episode->id,
        ]);

        if (!$watch->exists) {
            $watch->watched_at = $watchedAt ?? now();
            $watch->save();
        }

        $episode->loadMissing('season');
        $movieId = (int) ($episode->season?->movie_id ?? 0);

        if ($movieId > 0) {
            $this->addMovieToWatchedFolderByMovieIdTask->run($userId, $movieId);
        }

        return $watch->refresh();
    }
}
