<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\Jobs;

use App\Containers\MovieSection\Season\Models\Season;
use App\Containers\MovieSection\Season\Tasks\UnmarkSeasonEpisodesWatchedTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UnmarkSeasonEpisodesWatchedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $seasonId,
        public readonly int $userId,
    ) {
    }

    public function handle(UnmarkSeasonEpisodesWatchedTask $task): void
    {
        $season = Season::query()->find($this->seasonId);
        if ($season === null) {
            return;
        }

        $task->run($season, $this->userId);
    }
}
