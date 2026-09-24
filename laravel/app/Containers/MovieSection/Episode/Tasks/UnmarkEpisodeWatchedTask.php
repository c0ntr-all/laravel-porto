<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\Tasks;

use App\Containers\MovieSection\Episode\Models\Episode;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UnmarkEpisodeWatchedTask extends ParentTask
{
    public function run(Episode $episode, int $userId): bool
    {
        return (bool) $episode->watches()
            ->where('user_id', $userId)
            ->delete();
    }
}
