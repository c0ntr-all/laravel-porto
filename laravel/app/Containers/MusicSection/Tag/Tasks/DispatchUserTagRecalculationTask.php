<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Tag\Jobs\RecalculateUserAlbumAggregatedTagsJob;
use App\Containers\MusicSection\Tag\Jobs\RecalculateUserArtistAggregatedTagsJob;
use App\Containers\MusicSection\Track\Models\Track;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;

class DispatchUserTagRecalculationTask extends ParentTask
{
    public function forTrack(int $userId, Track $track): void
    {
        if ($track->album_id) {
            RecalculateUserAlbumAggregatedTagsJob::dispatch($userId, (int) $track->album_id);
        }

        foreach ($track->artists()->pluck('music_artists.id') as $artistId) {
            RecalculateUserArtistAggregatedTagsJob::dispatch($userId, (int) $artistId);
        }
    }

    public function forAlbum(int $userId, Album $album): void
    {
        RecalculateUserAlbumAggregatedTagsJob::dispatch($userId, (int) $album->id);

        $artistIds = DB::table('music_track_artist')
            ->join('music_tracks', 'music_tracks.id', '=', 'music_track_artist.track_id')
            ->where('music_tracks.album_id', $album->id)
            ->whereNull('music_tracks.deleted_at')
            ->distinct()
            ->pluck('music_track_artist.artist_id');

        foreach ($artistIds as $artistId) {
            RecalculateUserArtistAggregatedTagsJob::dispatch($userId, (int) $artistId);
        }
    }

    public function forArtist(int $userId, Artist $artist): void
    {
        $artistId = (int) $artist->id;
        RecalculateUserArtistAggregatedTagsJob::dispatch($userId, $artistId);

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
            RecalculateUserAlbumAggregatedTagsJob::dispatch($userId, (int) $albumId);
        }

        $relatedArtistIds = DB::table('music_track_artist')
            ->whereIn('track_id', $trackIds)
            ->where('artist_id', '!=', $artistId)
            ->distinct()
            ->pluck('artist_id');

        foreach ($relatedArtistIds as $relatedArtistId) {
            RecalculateUserArtistAggregatedTagsJob::dispatch($userId, (int) $relatedArtistId);
        }
    }
}
