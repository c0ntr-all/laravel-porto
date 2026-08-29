<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Containers\MusicSection\Tag\Models\MusicArtistAggregatedTag;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;

class RecalculateArtistAggregatedTagsTask extends ParentTask
{
    public function run(int $artistId): void
    {
        $total = (int) DB::table('music_track_artist')
            ->join('music_tracks', 'music_tracks.id', '=', 'music_track_artist.track_id')
            ->where('music_track_artist.artist_id', $artistId)
            ->whereNull('music_tracks.deleted_at')
            ->count();

        $counts = DB::table('music_track_tag')
            ->join('music_tracks', 'music_tracks.id', '=', 'music_track_tag.track_id')
            ->join('music_track_artist', 'music_track_artist.track_id', '=', 'music_tracks.id')
            ->where('music_track_artist.artist_id', $artistId)
            ->whereNull('music_tracks.deleted_at')
            ->groupBy('music_track_tag.tag_id')
            ->get([
                'music_track_tag.tag_id',
                DB::raw('COUNT(DISTINCT music_tracks.id) as tracks_count'),
            ]);

        $keep = [];

        foreach ($counts as $row) {
            $tagId = (int) $row->tag_id;
            $keep[] = $tagId;
            $tracksCount = (int) $row->tracks_count;

            MusicArtistAggregatedTag::query()->updateOrCreate(
                ['artist_id' => $artistId, 'tag_id' => $tagId],
                [
                    'tracks_count' => $tracksCount,
                    'percentage' => $this->percentage($tracksCount, $total),
                ],
            );
        }

        $stale = MusicArtistAggregatedTag::query()->where('artist_id', $artistId);
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
