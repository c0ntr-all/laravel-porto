<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Containers\MusicSection\Tag\Models\MusicUserAlbumAggregatedTag;
use App\Containers\MusicSection\Track\Models\Track;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;

class RecalculateUserAlbumAggregatedTagsTask extends ParentTask
{
    public function run(int $userId, int $albumId): void
    {
        $total = Track::query()->where('album_id', $albumId)->count();

        $counts = DB::table('music_user_track_tag')
            ->join('music_tracks', 'music_tracks.id', '=', 'music_user_track_tag.track_id')
            ->where('music_user_track_tag.user_id', $userId)
            ->where('music_tracks.album_id', $albumId)
            ->whereNull('music_tracks.deleted_at')
            ->groupBy('music_user_track_tag.user_tag_id')
            ->get([
                'music_user_track_tag.user_tag_id as tag_id',
                DB::raw('COUNT(*) as tracks_count'),
            ]);

        $keep = [];

        foreach ($counts as $row) {
            $tagId = (int) $row->tag_id;
            $keep[] = $tagId;
            $tracksCount = (int) $row->tracks_count;

            MusicUserAlbumAggregatedTag::query()->updateOrCreate(
                ['user_id' => $userId, 'album_id' => $albumId, 'tag_id' => $tagId],
                [
                    'tracks_count' => $tracksCount,
                    'percentage' => $this->percentage($tracksCount, $total),
                ],
            );
        }

        $stale = MusicUserAlbumAggregatedTag::query()
            ->where('user_id', $userId)
            ->where('album_id', $albumId);
        if ($keep !== []) {
            $stale->whereNotIn('tag_id', $keep);
        }
        $stale->delete();
    }

    private function percentage(int $tracksCount, int $total): ?string
    {
        if ($total <= 0) {
            return null;
        }

        return number_format(($tracksCount / $total) * 100, 2, '.', '');
    }
}
