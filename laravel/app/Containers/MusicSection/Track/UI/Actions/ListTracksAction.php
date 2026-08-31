<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\Actions;

use App\Containers\MusicSection\Track\Data\Repositories\TrackRepository;
use App\Containers\MusicSection\Track\UI\API\Requests\IndexRequest;
use App\Containers\MusicSection\Track\UI\API\Transformers\TrackTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use League\Fractal\Pagination\Cursor;

class ListTracksAction extends BaseAction
{
    public function __construct(
        private readonly TrackRepository $trackRepository
    ) {
    }

    public function handle(): CursorPaginator
    {
        return $this->trackRepository->getWithCursor();
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $tracks = $this->handle();

        return fractal($tracks, new TrackTransformer())
            ->withResourceName('tracks')
            ->parseIncludes(['artists', 'album', 'tags'])
            ->withCursor($this->cursorFromPaginator($tracks))
            ->addMeta($this->cursorMeta($tracks))
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
