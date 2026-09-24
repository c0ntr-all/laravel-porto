<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\Tasks;

use App\Containers\MovieSection\Season\Jobs\UnmarkSeasonEpisodesWatchedJob;
use App\Containers\MovieSection\Season\Models\Season;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UnmarkSeasonWatchedTask extends ParentTask
{
    public function run(Season $season, int $userId): bool
    {
        $deleted = (bool) $season->watches()
            ->where('user_id', $userId)
            ->delete();

        UnmarkSeasonEpisodesWatchedJob::dispatch((int) $season->id, $userId);

        return $deleted;
    }
}
