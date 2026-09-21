<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\UI\Actions;

use App\Containers\MovieSection\Person\Tasks\ListPersonsTask;
use App\Containers\MovieSection\Person\UI\API\Requests\IndexRequest;
use App\Containers\MovieSection\Person\UI\API\Transformers\PersonTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use League\Fractal\Pagination\Cursor;

class ListPersonsAction extends BaseAction
{
    public function __construct(
        private readonly ListPersonsTask $listPersonsTask,
    ) {
    }

    public function handle(): CursorPaginator
    {
        return $this->listPersonsTask->run();
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $persons = $this->handle();
        $includes = (string) $request->query('include', 'profession');

        return fractal($persons, new PersonTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_PERSON->value)
            ->parseIncludes($includes)
            ->withCursor($this->cursorFromPaginator($persons))
            ->addMeta($this->cursorMeta($persons))
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
