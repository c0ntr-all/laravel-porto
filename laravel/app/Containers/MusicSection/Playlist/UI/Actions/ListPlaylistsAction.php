<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\UI\Actions;

use App\Containers\MusicSection\Playlist\Data\Repositories\PlaylistRepository;
use App\Containers\MusicSection\Playlist\UI\API\Transformers\PlaylistTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use Lorisleiva\Actions\Concerns\AsAction;

class ListPlaylistsAction
{
    use AsAction;

    public function __construct(
        private readonly PlaylistRepository $playlistRepository
    )
    {
    }

    public function handle($userId = null): CursorPaginator
    {
        return $this->playlistRepository->getWithCursor($userId);
    }

    public function asController(): JsonResponse
    {
        $userId = auth()->user()->id;
        $playlists = $this->handle($userId);

        return fractal($playlists, new PlaylistTransformer())
            ->withResourceName('playlists')
            ->addMeta(['playlists_count' => $playlists->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
