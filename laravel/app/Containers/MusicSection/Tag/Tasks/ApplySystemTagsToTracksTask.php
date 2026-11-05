<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ApplySystemTagsToTracksTask extends ParentTask
{
    /**
     * Album/artist tag lists are the union of track tags.
     * Tags newly added to that union are attached to every track.
     * Tags removed from that union are detached from every track.
     * Tags that stay in the union keep their current per-track assignment.
     *
     * @param Collection<int, int|string>|array<int, int|string> $trackIds
     * @param array<int, int|string> $tagIds
     * @return array{attached: list<int>, detached: list<int>}
     */
    public function syncUnion(Collection|array $trackIds, array $tagIds): array
    {
        $trackIds = Collection::wrap($trackIds)->filter()->unique()->values();
        $desired = Collection::wrap($tagIds)
            ->filter()
            ->unique()
            ->map(static fn (mixed $id): int => (int) $id)
            ->values();

        if ($trackIds->isEmpty()) {
            return ['attached' => [], 'detached' => []];
        }

        $current = DB::table('music_track_tag')
            ->whereIn('track_id', $trackIds)
            ->distinct()
            ->pluck('tag_id')
            ->map(static fn (mixed $id): int => (int) $id);

        $detach = $current->diff($desired)->values();
        if ($detach->isNotEmpty()) {
            DB::table('music_track_tag')
                ->whereIn('track_id', $trackIds)
                ->whereIn('tag_id', $detach->all())
                ->delete();
        }

        $attach = $desired->diff($current)->values();
        $this->run($trackIds, $attach->all());

        return [
            'attached' => $attach->all(),
            'detached' => $detach->map(static fn (mixed $id): int => (int) $id)->all(),
        ];
    }

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
