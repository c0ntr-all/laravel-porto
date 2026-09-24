<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\Tasks;

use App\Containers\MovieSection\Episode\Models\EpisodeWatch;
use App\Containers\MovieSection\Season\Models\Season;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Carbon;

class MarkSeasonEpisodesWatchedTask extends ParentTask
{
    public function run(Season $season, int $userId, ?Carbon $watchedAt = null): int
    {
        $watchedAt ??= now();
        $episodeIds = $season->episodes()->pluck('id');

        if ($episodeIds->isEmpty()) {
            return 0;
        }

        $existingIds = EpisodeWatch::query()
            ->where('user_id', $userId)
            ->whereIn('episode_id', $episodeIds)
            ->pluck('episode_id')
            ->all();

        $missingIds = $episodeIds->diff($existingIds)->values();
        if ($missingIds->isEmpty()) {
            return 0;
        }

        $now = now();
        $rows = $missingIds->map(static fn (int $episodeId): array => [
            'user_id' => $userId,
            'episode_id' => $episodeId,
            'watched_at' => $watchedAt,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        EpisodeWatch::query()->insert($rows);

        return count($rows);
    }
}
