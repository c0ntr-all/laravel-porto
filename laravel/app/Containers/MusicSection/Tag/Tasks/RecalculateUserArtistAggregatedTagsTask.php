<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Containers\MusicSection\Tag\Models\MusicUserArtistAggregatedTag;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;

class RecalculateUserArtistAggregatedTagsTask extends ParentTask
{
    public function run(int $userId, int $artistId): void
    {
        $total = (int) DB::table('music_track_artist')
            ->join('music_tracks', 'music_tracks.id', '=', 'music_track_artist.track_id')
            ->where('music_track_artist.artist_id', $artistId)
            ->whereNull('music_tracks.deleted_at')
            ->count();

        $counts = DB::table('music_user_track_tag')
            ->join('music_tracks', 'music_tracks.id', '=', 'music_user_track_tag.track_id')
            ->join('music_track_artist', 'music_track_artist.track_id', '=', 'music_tracks.id')
            ->where('music_user_track_tag.user_id', $userId)
            ->where('music_track_artist.artist_id', $artistId)
            ->whereNull('music_tracks.deleted_at')
            ->groupBy('music_user_track_tag.user_tag_id')
            ->get([
                'music_user_track_tag.user_tag_id as tag_id',
                DB::raw('COUNT(DISTINCT music_tracks.id) as tracks_count'),
            ]);

        $keep = [];

        foreach ($counts as $row) {
            $tagId = (int) $row->tag_id;
            $keep[] = $tagId;
            $tracksCount = (int) $row->tracks_count;

            MusicUserArtistAggregatedTag::query()->updateOrCreate(
                ['user_id' => $userId, 'artist_id' => $artistId, 'tag_id' => $tagId],
                [
                    'tracks_count' => $tracksCount,
                    'percentage' => $this->percentage($tracksCount, $total),
                ],
            );
        }

        $stale = MusicUserArtistAggregatedTag::query()
            ->where('user_id', $userId)
            ->where('artist_id', $artistId);
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
