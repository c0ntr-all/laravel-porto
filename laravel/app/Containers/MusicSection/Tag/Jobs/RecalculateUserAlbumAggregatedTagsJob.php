<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Jobs;

use App\Containers\MusicSection\Tag\Tasks\RecalculateUserAlbumAggregatedTagsTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateUserAlbumAggregatedTagsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $userId,
        public readonly int $albumId,
    ) {
    }

    public function handle(RecalculateUserAlbumAggregatedTagsTask $task): void
    {
        $task->run($this->userId, $this->albumId);
    }
}
