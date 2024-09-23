<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\UI\Actions;

use App\Containers\MusicSection\Playlist\Models\Playlist;
use App\Containers\MusicSection\Playlist\UI\API\Transformers\PlaylistTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPlaylistAction
{
    use AsAction;

    public function handle(Playlist $playlist): Playlist
    {
        return $playlist->load(['tracks']);
    }

    public function asController(Playlist $playlist): JsonResponse
    {
        $playlist = $this->handle($playlist);

        return fractal($playlist, new PlaylistTransformer())
            ->withResourceName('playlists')
            ->parseIncludes(['tracks'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
