<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\UI\Actions;

use App\Containers\MovieSection\Movie\Tasks\ListMoviesTask;
use App\Containers\MovieSection\Movie\UI\API\Requests\IndexRequest;
use App\Containers\MovieSection\Movie\UI\API\Transformers\MovieTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use League\Fractal\Pagination\Cursor;

class ListMoviesAction extends BaseAction
{
    public function __construct(
        private readonly ListMoviesTask $listMoviesTask,
    ) {
    }

    public function handle(): CursorPaginator
    {
        return $this->listMoviesTask->run();
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $movies = $this->handle();

        return fractal($movies, new MovieTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE->value)
            ->parseIncludes(['genres', 'countries'])
            ->withCursor($this->cursorFromPaginator($movies))
            ->addMeta($this->cursorMeta($movies))
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
