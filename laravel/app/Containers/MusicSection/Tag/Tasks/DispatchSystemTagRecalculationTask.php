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
            RecalculateAlbumAggregatedTagsJob::dispatchSync((int) $track->album_id);
        }

        foreach ($track->artists()->pluck('music_artists.id') as $artistId) {
            RecalculateArtistAggregatedTagsJob::dispatchSync((int) $artistId);
        }
    }

    public function forAlbum(Album $album): void
    {
        RecalculateAlbumAggregatedTagsJob::dispatchSync((int) $album->id);

        $artistIds = DB::table('music_track_artist')
            ->join('music_tracks', 'music_tracks.id', '=', 'music_track_artist.track_id')
            ->where('music_tracks.album_id', $album->id)
            ->whereNull('music_tracks.deleted_at')
            ->distinct()
            ->pluck('music_track_artist.artist_id');

        foreach ($artistIds as $artistId) {
            RecalculateArtistAggregatedTagsJob::dispatchSync((int) $artistId);
        }
    }

    public function forArtist(Artist $artist): void
    {
        $artistId = (int) $artist->id;
        RecalculateArtistAggregatedTagsJob::dispatchSync($artistId);

        $trackIds = DB::table('music_track_artist')
            ->join('music_tracks', 'music_tracks.id', '=', 'music_track_artist.track_id')
            ->where('music_track_artist.artist_id', $artistId)
            ->whereNull('music_tracks.deleted_at')
            ->pluck('music_tracks.id');

        $albumIds = DB::table('music_tracks')
            ->whereIn('id', $trackIds)
            ->whereNotNull('album_id')
            ->distinct()
            ->pluck('album_id');

        foreach ($albumIds as $albumId) {
            RecalculateAlbumAggregatedTagsJob::dispatchSync((int) $albumId);
        }

        $relatedArtistIds = DB::table('music_track_artist')
            ->whereIn('track_id', $trackIds)
            ->where('artist_id', '!=', $artistId)
            ->distinct()
            ->pluck('artist_id');

        foreach ($relatedArtistIds as $relatedArtistId) {
            RecalculateArtistAggregatedTagsJob::dispatchSync((int) $relatedArtistId);
        }
    }
}
