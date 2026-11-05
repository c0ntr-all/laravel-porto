<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Containers\MusicSection\Tag\Models\MusicUserTag;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ApplyUserTagsToTracksTask extends ParentTask
{
    /**
     * @param Collection<int, int|string>|array<int, int|string> $trackIds
     * @param array<int, int|string> $userTagIds
     * @return array{attached: list<int>, detached: list<int>}
     */
    public function syncUnion(int $userId, Collection|array $trackIds, array $userTagIds): array
    {
        $trackIds = Collection::wrap($trackIds)->filter()->unique()->values();
        $desired = Collection::wrap($userTagIds)
            ->filter()
            ->unique()
            ->map(static fn (mixed $id): int => (int) $id)
            ->values();

        if ($trackIds->isEmpty()) {
            return ['attached' => [], 'detached' => []];
        }

        $current = DB::table('music_user_track_tag')
            ->where('user_id', $userId)
            ->whereIn('track_id', $trackIds)
            ->distinct()
            ->pluck('user_tag_id')
            ->map(static fn (mixed $id): int => (int) $id);

        $detach = $current->diff($desired)->values();
        if ($detach->isNotEmpty()) {
            DB::table('music_user_track_tag')
                ->where('user_id', $userId)
                ->whereIn('track_id', $trackIds)
                ->whereIn('user_tag_id', $detach->all())
                ->delete();
        }

        $attach = $desired->diff($current)->values();
        $this->run($userId, $trackIds, $attach->all());

        return [
            'attached' => $attach->all(),
            'detached' => $detach->map(static fn (mixed $id): int => (int) $id)->all(),
        ];
    }

    /**
     * @param Collection<int, int|string>|array<int, int|string> $trackIds
     * @param array<int, int|string> $userTagIds
     */
    public function run(int $userId, Collection|array $trackIds, array $userTagIds): void
    {
        $trackIds = Collection::wrap($trackIds)->filter()->unique()->values();
        $userTagIds = MusicUserTag::query()
            ->where('user_id', $userId)
            ->whereIn('id', Collection::wrap($userTagIds)->filter()->unique()->all())
            ->pluck('id');

        if ($trackIds->isEmpty() || $userTagIds->isEmpty()) {
            return;
        }

        $now = Carbon::now()->toDateTimeString();
        $rows = [];

        foreach ($trackIds as $trackId) {
            foreach ($userTagIds as $userTagId) {
                $rows[] = [
                    'user_tag_id' => (int) $userTagId,
                    'track_id' => (int) $trackId,
                    'user_id' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('music_user_track_tag')->insertOrIgnore($chunk);
        }
    }
}
