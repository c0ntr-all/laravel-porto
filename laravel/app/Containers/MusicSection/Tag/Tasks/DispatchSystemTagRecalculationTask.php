<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Tag\Jobs\RecalculateAlbumAggregatedTagsJob;
use App\Containers\MusicSection\Tag\Jobs\RecalculateArtistAggregatedTagsJob;
use App\Containers\MusicSection\Track\Models\Track;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;

class DispatchSystemTagRecalculationTask extends ParentTask
{
    public function forTrack(Track $track): void
    {
        if ($track->album_id) {
            RecalculateAlbumAggregatedTagsJob::dispatch((int) $track->album_id);
        }

        foreach ($track->artists()->pluck('music_artists.id') as $artistId) {
            RecalculateArtistAggregatedTagsJob::dispatch((int) $artistId);
        }
    }

    public function forAlbum(Album $album): void
    {
        RecalculateAlbumAggregatedTagsJob::dispatch((int) $album->id);

        $artistIds = DB::table('music_track_artist')
            ->join('music_tracks', 'music_tracks.id', '=', 'music_track_artist.track_id')
            ->where('music_tracks.album_id', $album->id)
            ->whereNull('music_tracks.deleted_at')
            ->distinct()
            ->pluck('music_track_artist.artist_id');

        foreach ($artistIds as $artistId) {
            RecalculateArtistAggregatedTagsJob::dispatch((int) $artistId);
        }
    }

    public function forArtist(Artist $artist): void
    {
        RecalculateArtistAggregatedTagsJob::dispatch((int) $artist->id);

        $albumIds = DB::table('music_track_artist')
            ->join('music_tracks', 'music_tracks.id', '=', 'music_track_artist.track_id')
            ->where('music_track_artist.artist_id', $artist->id)
            ->whereNull('music_tracks.deleted_at')
            ->whereNotNull('music_tracks.album_id')
            ->distinct()
            ->pluck('music_tracks.album_id');

        foreach ($albumIds as $albumId) {
            RecalculateAlbumAggregatedTagsJob::dispatch((int) $albumId);
        }
    }
}
