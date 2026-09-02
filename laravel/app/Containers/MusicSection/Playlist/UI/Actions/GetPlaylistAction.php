<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\UI\Actions;

use App\Containers\MusicSection\Playlist\Models\Playlist;
use App\Containers\MusicSection\Playlist\UI\API\Requests\GetRequest;
use App\Containers\MusicSection\Playlist\UI\API\Transformers\PlaylistTransformer;
use Illuminate\Http\JsonResponse;
use App\Ship\Parents\Actions\BaseAction;

class GetPlaylistAction extends BaseAction
{

    public function handle(Playlist $playlist): Playlist
    {
        return $playlist->load(['tracks.artists', 'tracks.album.albumType']);
    }

    public function asController(Playlist $playlist, GetRequest $request): JsonResponse
    {
        $playlist = $this->handle($playlist);

        return fractal($playlist, new PlaylistTransformer())
            ->withResourceName('playlists')
            ->parseIncludes(['tracks', 'tracks.artists', 'tracks.album'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
