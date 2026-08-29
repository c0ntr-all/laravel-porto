<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ApplySystemTagsToTracksTask extends ParentTask
{
    /**
     * @param Collection<int, int|string>|array<int, int|string> $trackIds
     * @param array<int, int|string> $tagIds
     */
    public function run(Collection|array $trackIds, array $tagIds): void
    {
        $trackIds = Collection::wrap($trackIds)->filter()->unique()->values();
        $tagIds = Collection::wrap($tagIds)->filter()->unique()->values();

        if ($trackIds->isEmpty() || $tagIds->isEmpty()) {
            return;
        }

        $now = Carbon::now()->toDateTimeString();
        $rows = [];

        foreach ($trackIds as $trackId) {
            foreach ($tagIds as $tagId) {
                $rows[] = [
                    'track_id' => (int) $trackId,
                    'tag_id' => (int) $tagId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('music_track_tag')->insertOrIgnore($chunk);
        }
    }
}
