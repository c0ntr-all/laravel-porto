<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\Tasks;

use App\Containers\MovieSection\Folder\Tasks\AddMovieToWatchedFolderByMovieIdTask;
use App\Containers\MovieSection\Season\Jobs\MarkSeasonEpisodesWatchedJob;
use App\Containers\MovieSection\Season\Models\Season;
use App\Containers\MovieSection\Season\Models\SeasonWatch;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Carbon;

class MarkSeasonWatchedTask extends ParentTask
{
    public function __construct(
        private readonly AddMovieToWatchedFolderByMovieIdTask $addMovieToWatchedFolderByMovieIdTask,
    ) {
    }

    public function run(Season $season, int $userId, ?Carbon $watchedAt = null): SeasonWatch
    {
        $watch = SeasonWatch::query()->firstOrNew([
            'user_id' => $userId,
            'season_id' => $season->id,
        ]);

        $watchedAt ??= now();

        if (!$watch->exists) {
            $watch->watched_at = $watchedAt;
            $watch->save();
        }

        $this->addMovieToWatchedFolderByMovieIdTask->run($userId, (int) $season->movie_id);

        MarkSeasonEpisodesWatchedJob::dispatch(
            (int) $season->id,
            $userId,
            $watchedAt->toISOString(),
        );

        return $watch->refresh();
    }
}
