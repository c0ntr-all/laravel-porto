<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\Actions;

use App\Containers\MusicSection\Artist\Data\Repositories\ArtistRepository;
use App\Containers\MusicSection\Artist\UI\API\Requests\IndexRequest;
use App\Containers\MusicSection\Artist\UI\API\Transformers\ArtistTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use League\Fractal\Pagination\Cursor;

class ListArtistsAction extends BaseAction
{
    public function __construct(
        private readonly ArtistRepository $artistRepository
    ) {
    }

    public function handle(IndexRequest $request): CursorPaginator
    {
        return $this->artistRepository->getWithCursor($request->perPage());
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $artists = $this->handle($request);

        return fractal($artists, new ArtistTransformer())
            ->withResourceName('artists')
            ->parseIncludes(['tags'])
            ->withCursor($this->cursorFromPaginator($artists))
            ->addMeta($this->cursorMeta($artists))
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
