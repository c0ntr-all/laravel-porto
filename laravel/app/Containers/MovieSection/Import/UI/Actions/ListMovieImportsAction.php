<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\UI\Actions;

use App\Containers\MovieSection\Import\Tasks\ListMovieImportsTask;
use App\Containers\MovieSection\Import\UI\API\Requests\IndexRequest;
use App\Containers\MovieSection\Import\UI\API\Transformers\MovieImportTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use League\Fractal\Pagination\Cursor;

class ListMovieImportsAction extends BaseAction
{
    public function __construct(
        private readonly ListMovieImportsTask $listMovieImportsTask,
    ) {
    }

    public function handle(int $userId): CursorPaginator
    {
        return $this->listMovieImportsTask->run($userId);
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $imports = $this->handle((int) auth()->id());

        return fractal($imports, new MovieImportTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_IMPORT->value)
            ->parseIncludes(['movie'])
            ->withCursor($this->cursorFromPaginator($imports))
            ->addMeta($this->cursorMeta($imports))
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
