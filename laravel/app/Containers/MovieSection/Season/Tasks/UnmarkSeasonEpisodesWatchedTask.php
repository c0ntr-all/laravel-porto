<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\Tasks;

use App\Containers\MovieSection\Episode\Models\EpisodeWatch;
use App\Containers\MovieSection\Season\Models\Season;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UnmarkSeasonEpisodesWatchedTask extends ParentTask
{
    public function run(Season $season, int $userId): int
    {
        $episodeIds = $season->episodes()->pluck('id');

        if ($episodeIds->isEmpty()) {
            return 0;
        }

        return EpisodeWatch::query()
            ->where('user_id', $userId)
            ->whereIn('episode_id', $episodeIds)
            ->delete();
    }
}
