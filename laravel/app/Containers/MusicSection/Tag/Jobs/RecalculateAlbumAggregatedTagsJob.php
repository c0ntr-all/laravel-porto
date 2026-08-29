<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Jobs;

use App\Containers\MusicSection\Tag\Tasks\RecalculateAlbumAggregatedTagsTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateAlbumAggregatedTagsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $albumId,
    ) {
    }

    public function handle(RecalculateAlbumAggregatedTagsTask $task): void
    {
        $task->run($this->albumId);
    }
}
