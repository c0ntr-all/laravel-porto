<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\UI\Actions;

use App\Containers\MusicSection\Playlist\Data\Repositories\PlaylistRepository;
use App\Containers\MusicSection\Playlist\UI\API\Requests\IndexRequest;
use App\Containers\MusicSection\Playlist\UI\API\Transformers\PlaylistTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use League\Fractal\Pagination\Cursor;

class ListPlaylistsAction extends BaseAction
{
    public function __construct(
        private readonly PlaylistRepository $playlistRepository
    ) {
    }

    public function handle($userId = null): CursorPaginator
    {
        return $this->playlistRepository->getWithCursor($userId);
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $userId = auth()->user()->id;
        $playlists = $this->handle($userId);

        return fractal($playlists, new PlaylistTransformer())
            ->withResourceName('playlists')
            ->withCursor($this->cursorFromPaginator($playlists))
            ->addMeta($this->cursorMeta($playlists))
            ->respond(200, [], JSON_PRETTY_PRINT);
    }

    private function cursorFromPaginator(CursorPaginator $paginator): Cursor
    {
        return new Cursor(
            $paginator->cursor()?->encode(),
            $paginator->previousCursor()?->encode(),
            $paginator->nextCursor()?->encode(),
            $paginator->count(),
        );
    }

    /**
     * @return array{
     *     per_page: int,
     *     has_more: bool,
     *     next_cursor: string|null,
     *     prev_cursor: string|null,
     *     next_page_url: string|null,
     *     prev_page_url: string|null
     * }
     */
    private function cursorMeta(CursorPaginator $paginator): array
    {
        return [
            'per_page' => $paginator->perPage(),
            'has_more' => $paginator->hasMorePages(),
            'next_cursor' => $paginator->nextCursor()?->encode(),
            'prev_cursor' => $paginator->previousCursor()?->encode(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
        ];
    }
}
